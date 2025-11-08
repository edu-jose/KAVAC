<?php

namespace Modules\Warehouse\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CodeSetting;
use App\Models\FiscalYear;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Modules\Warehouse\Models\WarehouseExternalRequest;
use Modules\Warehouse\Models\WarehouseExternalRequestInventoryProduct;
use Modules\Warehouse\Models\WarehouseInventoryProduct;

/**
 * @class WarehouseExternalRequestController
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WarehouseExternalRequestController extends Controller
{
    use ValidatesRequests;

    /**
     * Reglas de validación
     *
     * @var array $validationRules
     */
    protected $validateRules;

    /**
     * Mensajes de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:warehouse.external.request.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:warehouse.external.request.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:warehouse.external.request.delete', ['only' => 'destroy']);


        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'date' => ['required'],
            'first_name' => ['required'],
            'last_name' => ['required'],
            'warehouse_id' => ['required']
        ];


        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'date.required' => 'El campo: "Fecha de la solicitud", es obligatorio.',
            'first_name.required' => 'El campo: "Nombre del solicitante", es obligatorio.',
            'last_name.required' => 'El campo: "Apellidos del solicitante", es obligatorio.',
            'warehouse_id.required' => 'El campo: "Almacén", es obligatorio.'
        ];
    }

    /**
     * [descripción del método]
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function index()
    {
        return view('warehouse::index');
    }

    /**
     * [descripción del método]
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function create()
    {
        $externalRequest = true;
        return view('warehouse::requests.create', compact('externalRequest'));
    }

    /**
     * [descripción del método]
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function store(Request $request)
    {
        $validateRules = $this->validateRules;
        $messages = $this->messages;
        for ($i = 0; $i < count($request->warehouse_products); $i++) {
            $validateRules = array_merge($validateRules, [
                'warehouse_products.' . $i . '.requested' => ['required', 'max:' . WarehouseInventoryProduct::find($request->warehouse_products[$i]['id'])->real],
            ]);

            $products = WarehouseInventoryProduct::where('id', $request->warehouse_products[$i]['id'])->with('warehouseProduct')->first();

            $messages = array_merge($messages, [
                'warehouse_products.' . $i . '.requested.max'
                => 'El producto "' . $products->warehouseProduct->name . '" no posee suficiente existencia en almacén',
                'warehouse_products.' . $i . '.requested.required'
                => 'La cantidad solicitada de "' . $products->warehouseProduct->name . '" es requerida',
            ]);
        }

        $this->validate($request, $validateRules, $messages);

        $codeSetting = CodeSetting::where(['table' => 'warehouse_external_requests'])->first();

        if (is_null($codeSetting)) {
            $request->session()->flash('message', [
                'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                'text' => 'Debe configurar previamente el formato para el código a generar'
            ]);
            return response()->json(['result' => false, 'redirect' => route('warehouse.setting.index')], 200);
        }

        $currentFiscalYear = FiscalYear::select('year')
            ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

        $code = generate_registration_code(
            $codeSetting->format_prefix,
            strlen($codeSetting->format_digits),
            (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
                $currentFiscalYear->year : date('Y')),
            $codeSetting->model,
            $codeSetting->field
        );

        DB::transaction(function () use ($request, $code) {
            $data_request = WarehouseExternalRequest::create([
                'code' => $code,
                'date' => $request->date,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'institution_name' => $request->institution_name,
                'warehouse_id' => $request->warehouse_id,
                'general_observations' => $request->general_observations,
                'state' => 'Pendiente',
            ]);

            /* Registra la solicitud en la tabla unificada de solicitudes de almacén */
            $data_request->registerUnified();

            foreach ($request->warehouse_products as $product) {
                $inventory_product = WarehouseInventoryProduct::find($product['id']);

                if (!is_null($inventory_product)) {
                    $exist_real = $inventory_product->exist - $inventory_product->reserved;

                    if ($exist_real >= $product['requested']) {
                        WarehouseExternalRequestInventoryProduct::create([
                            'warehouse_external_request_id' => $data_request->id,
                            'warehouse_inventory_product_id' => $inventory_product->id,
                            'quantity' => $product['requested'],
                            'new_exist' => $exist_real - $product['requested'],
                        ]);
                    } else {
                        /* Si la exitencia del producto es menor que lo que se solicita se revierten los cambios */
                        DB::rollback();
                    }
                } else {
                    /* Si no existe el registro se revierten los cambios */
                    DB::rollback();
                }
            }
        });

        $warehouse_request = WarehouseExternalRequest::where('code', $code)->first();

        if (is_null($warehouse_request)) {
            $request->session()->flash(
                'message',
                [
                    'type' => 'other',
                    'title' => 'Alerta',
                    'icon' => 'screen-error',
                    'class' => 'growl-danger',
                    'text' => 'No se pudo completar la operación.'
                ]
            );
        } else {
            $request->session()->flash('message', ['type' => 'store']);
        }

        return response()->json(['result' => true, 'redirect' => route('warehouse.request.index')], 200);
    }

    /**
     * [descripción del método]
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function show($id)
    {
        $request = WarehouseExternalRequest::findOrFail($id);
        return view('warehouse.external-request::show', compact('request'));
    }

    /**
     * [descripción del método]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function edit($id)
    {
        $externalRequest = true;
        $warehouse_ext_req_id = WarehouseExternalRequest::find($id)->id;

        return view('warehouse::requests.create', compact('warehouse_ext_req_id', 'externalRequest'));
    }

    /**
     * [descripción del método]
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function update(Request $request, $id)
    {
        $validateRules = $this->validateRules;
        $messages = $this->messages;

        for ($i = 0; $i < count($request->warehouse_products); $i++) {
            $validateRules = array_merge($validateRules, [
                'warehouse_products.' . $i . '.requested' => ['required', 'max:' . WarehouseInventoryProduct::find($request->warehouse_products[$i]['id'])->real],
            ]);

            $products = WarehouseInventoryProduct::where('id', $request->warehouse_products[$i]['id'])->with('warehouseProduct')->first();

            $messages = array_merge($messages, [
                'warehouse_products.' . $i . '.requested.max'
                => 'El producto "' . $products->warehouseProduct->name . '" no posee suficiente existencia en almacén',
                'warehouse_products.' . $i . '.requested.required'
                => 'La cantidad solicitada de "' . $products->warehouseProduct->name . '" es requerida',
            ]);
        }

        $this->validate($request, $validateRules, $messages);

        DB::transaction(function () use ($request, $id) {
            /* Actualiza la solicitud */
            WarehouseExternalRequest::findOrFail($id)->update([
                'date' => $request->date,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'institution_name' => $request->institution_name,
                'warehouse_id' => $request->warehouse_id,
                'general_observations' => $request->general_observations,
            ]);

            /* Elimina los productos asociados a la solicitud */
            WarehouseExternalRequestInventoryProduct::where('warehouse_external_request_id', '=', $id)->delete();

            /* Registra los nuevos productos asociados a la solicitud */
            foreach ($request->warehouse_products as $product) {
                WarehouseExternalRequestInventoryProduct::create([
                    'warehouse_external_request_id' => $id,
                    'warehouse_inventory_product_id' => $product['id'],
                    'quantity' => $product['requested'],
                    'new_exist' =>
                        WarehouseInventoryProduct::find($product['id'])->exist -
                        WarehouseInventoryProduct::find($product['id'])->reserved - $product['requested'],
                ]);
            }
        });

        if (WarehouseExternalRequest::findOrFail($id)) {
            $request->session()->flash('message', ['type' => 'update']);
            return response()->json(['result' => true, 'redirect' => route('warehouse.request.index')], 200);
        }
    }

    /**
     * [descripción del método]
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function destroy($id)
    {
        $request = WarehouseExternalRequest::with('warehouseExternalRequestInventoryProducts')
            ->findOrFail($id);

        if (!$request) {
            return response()->json(['error' => 'Solicitud no encontrada.'], 404);
        }
        if ($request->state !== 'Pendiente') {
            return response()->json(['error' => 'Solo se pueden eliminar solicitudes en estado Pendiente.'], 403);
        }

        foreach ($request->warehouseExternalRequestInventoryProducts as $productRequest) {
            $productRequest->delete();
        }

        $request->delete();
        return response()->json(['message' => 'Solicitud eliminada correctamente.'], 200);
    }

    /**
     * Obtiene un listado de las solicitudes externas de almacén registradas
     *
     * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse Objeto con los registros a mostrar
     */
    public function vueList()
    {
        $warehouse_external_requests = WarehouseExternalRequest::with([
            'warehouse'
        ])->get();

        return response()->json(['records' => $warehouse_external_requests], 200);
    }

    /**
     * Obtiene la información de la solicitud externa de almacén solcitada
     *
     * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse Objeto con los registros a mostrar
     */
    public function vueInfo($id): JsonResponse
    {
        return response()->json([
            'records' => WarehouseExternalRequest::query()
                ->where('id', $id)
                ->with([
                'warehouse' => function ($query): void {
                    $query->select(['id', 'name']);
                },
                'warehouseExternalRequestInventoryProducts' => function ($query) {
                    $query->with(['warehouseInventoryProduct' => function ($query) {
                        $query->with(['warehouseProduct' => function ($query) {
                            $query->with('measurementUnit');
                        }, 'currency']);
                    }]);
                }
            ])->first()
        ], JsonResponse::HTTP_OK);
    }
}
