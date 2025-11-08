<?php

namespace Modules\Warehouse\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\DB;
use App\Models\CodeSetting;
use App\Models\FiscalYear;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Modules\Warehouse\Models\WarehouseInventoryProduct;
use Modules\Warehouse\Models\WarehouseInventoryProductRequest;
use Modules\Warehouse\Models\WarehouseRequest;

/**
 * @class WarehouseRequestStaffController
 * @brief Controlador de solicitudes de trabajadores al almacén
 *
 * Clase que gestiona las solicitudes de los trabajadores al almacén
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WarehouseRequestStaffController extends Controller
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
        $this->middleware('permission:warehouse.request.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:warehouse.request.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:warehouse.request.delete', ['only' => 'destroy']);


        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'warehouse_products.*' => ['required'],
            'department_id'        => ['required'],
            'payroll_position_id'  => ['required'],
            'payroll_staff_id'     => ['required'],
            'motive'               => ['required'],
            'request_date'         => ['required'],
            'institution_id' => ['required', 'integer'],
            'warehouse_id' => ['required', 'integer'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [

            'department_id.required'       => 'El campo "Departamento" es obligatorio',
            'payroll_position_id.required' => 'El campo "Cargo" es obligatorio',
            'payroll_staff_id.required'    => 'El campo "Solicitante" es obligatorio',
            'motive.required'              => 'El campo "Motivo de la solicitud" es obligatorio',
            'request_date.required'        => 'El campo "Fecha de la solicitud" es obligatorio',
            'institution_id.required'              => 'El campo institución es obligatorio',
            'warehouse_id.required'              => 'El campo nombre del almacén es obligatorio',
        ];
    }

    /**
     * Muestra el formulario para registrar una nueva solicitud de almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $staff = true;
        return view('warehouse::requests.create', compact('staff'));
    }

    /**
     * Valida y registra una nueva solicitud de almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
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

        $codeFilter = 'warehouse.requestStaff';
        $codeSetting = CodeSetting::where([
            'table' => 'warehouse_requests',
            'type'  => $codeFilter
        ])->first();

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
            $data_request = WarehouseRequest::create([
                'code' => $code,
                'request_date' => $request->input('request_date'),
                'state' => 'Pendiente',
                'department_id' => $request->input('department_id'),
                'motive' => $request->input('motive'),
                'payroll_staff_id' => $request->input('payroll_staff_id'),
                'institution_id' => $request->input('institution_id'),
                'warehouse_id' => $request->input('warehouse_id'),
            ]);

            /* Registra la solicitud en la tabla unificada de solicitudes de almacén */
            $data_request->registerUnified();

            foreach ($request->warehouse_products as $product) {
                $inventory_product = WarehouseInventoryProduct::find($product['id']);
                if (!is_null($inventory_product)) {
                    $exist_real = $inventory_product->exist - $inventory_product->reserved;
                    if ($exist_real >= $product['requested']) {
                        WarehouseInventoryProductRequest::create([
                            'warehouse_inventory_product_id' => $inventory_product->id,
                            'warehouse_request_id' => $data_request->id,
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
        $warehouse_request = WarehouseRequest::where('code', $code)->first();
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
     * Muestra el formulario para editar una solicitud de  almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  integer $id Identificador único del ingreso de almacén
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $staff = true;
        $warehouse_request = WarehouseRequest::find($id);
        return view('warehouse::requests.create', compact('warehouse_request', 'staff'));
    }

    /**
     * Actualiza la información de las solicitudes de almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     * @param  integer $id Identificador único de la solicitud de almacén
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $warehouse_request = WarehouseRequest::find($id);

        $validateRules = $this->validateRules;
        $messages = $this->messages;
        for ($i = 0; $i < count($request->warehouse_products); $i++) {
            $validateRules = array_merge($validateRules, [
                'warehouse_products.' . $i . '.requested' => ['required', 'max:' . WarehouseInventoryProduct::find($request->warehouse_products[$i]['id'])->real],
            ]);

            $products = WarehouseInventoryProduct::where('id', $request->warehouse_products[$i]['id'])->with('warehouseProduct')->first();

            $messages = array_merge($messages, [
                'warehouse_products.' . $i . '.requested.max' =>
                'El producto "' . $products->warehouseProduct->name . '" no posee suficiente existencia en almacén',
                'La cantidad solicitada de "' . $products->warehouseProduct->name . '" es requerida'
            ]);
        }

        $this->validate($request, $validateRules, $this->messages);

        DB::transaction(function () use ($request, $warehouse_request) {
            $warehouse_request->request_date = $request->input('request_date');
            $warehouse_request->motive = $request->input('motive');
            $warehouse_request->department_id = $request->input('department_id');
            $warehouse_request->payroll_staff_id = $request->input('payroll_staff_id');
            $warehouse_request->institution_id = $request->input('institution_id');
            $warehouse_request->warehouse_id = $request->input('warehouse_id');
            $warehouse_request->save();

            $update = now();

            /* Se agregan los nuevos elementos a la solicitud */
            foreach ($request->warehouse_products as $product) {
                $inventory_product = WarehouseInventoryProduct::find($product['id']);
                if (!is_null($inventory_product)) {
                    $exist_real = $inventory_product->exist - $inventory_product->reserved;
                    if ($exist_real >= $product['requested']) {
                        $old_request = WarehouseInventoryProductRequest::where(
                            'warehouse_request_id',
                            $warehouse_request->id
                        )->where('warehouse_inventory_product_id', $inventory_product->id)->first();
                        if (!is_null($old_request)) {
                            $old_request->quantity = $product['requested'];
                            $old_request->updated_at = $update;
                            $old_request->new_exist = $exist_real - $product['requested'];
                            $old_request->save();
                        } else {
                            WarehouseInventoryProductRequest::create([
                                'warehouse_inventory_product_id' => $inventory_product->id,
                                'warehouse_request_id' => $warehouse_request->id,
                                'quantity' => $product['requested'],
                                'updated_at' => $update,
                                'new_exist' => $exist_real - $product['requested'],
                            ]);
                        }
                    } else {
                        /* Si la exitencia del producto es menor que lo que se solicita se revierten los cambios */
                        DB::rollback();
                    }
                } else {
                    /* Si no existe el registro en inventario se revierten los cambios */
                    DB::rollback();
                }
            };

            /* Se eliminan los demas elementos de la solicitud */
            $warehouse_request_products = WarehouseInventoryProductRequest::where(
                'warehouse_request_id',
                $warehouse_request->id
            )->where('updated_at', '!=', $update)->get();

            foreach ($warehouse_request_products as $warehouse_request_product) {
                $warehouse_request_product->delete();
            }
        });
        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('warehouse.request.index')], 200);
    }

    /**
     * Elimina una solicitud de almacén del personal
     *
     * @author Pablo Sulbarán <psulbaran@cenditel.gob.ve>
     *
     * @param  integer $id Identificador único de la solicitud de almacén
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $request = WarehouseRequest::find($id);

        if (!$request) {
            return response()->json(['error' => 'Solicitud no encontrada.'], 404);
        }
        if ($request->state !== 'Pendiente') {
            return response()->json(['error' => 'Solo se pueden eliminar solicitudes en estado Pendiente.'], 403);
        }
        try {
            DB::transaction(function () use ($request) {
                foreach ($request->warehouseInventoryProductRequests as $productRequest) {
                    $inventoryProduct = $productRequest->warehouseInventoryProduct;
                    if ($inventoryProduct) {
                        $inventoryProduct->exist += $productRequest->quantity;
                        $inventoryProduct->save();
                    }
                    $productRequest->delete();
                }
                $request->forceDelete();
            });

            return response()->json(['success' => 'Solicitud eliminada correctamente.']);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error' => 'No se pudo eliminar la solicitud.',
                'exception' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vizualiza información de una solicitud de almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  integer $id Identificador único de la solicitud de almacén
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueInfo($id): JsonResponse
    {
        return response()->json([
            'records' => WarehouseRequest::query()
                ->where('id', $id)
                ->with([
                'payrollStaff',
                'department',
                'institution' => function ($query): void {
                    $query->select(['id', 'name']);
                },
            'warehouse' => function ($query): void {
                $query->select(['id', 'name']);
            },
                'warehouseInventoryProductRequests' => function ($query) {
                    $query->with(['warehouseInventoryProduct' => function ($query) {
                        $query->with(['warehouseProduct' => function ($query) {
                            $query->with('measurementUnit');
                        }, 'currency']);
                    }]);
                }
            ])->first()], JsonResponse::HTTP_OK);
    }

    /**
     * Obtiene un listado de las solicitudes de almacén registradas
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse Objeto con los registros a mostrar
     */
    public function vueList()
    {
        $warehouse_requests = WarehouseRequest::query()
            ->with([
                'department',
                'payrollStaff',
                            'institution' => function ($query): void {
                                $query->select(['id', 'name']);
                            },
            'warehouse' => function ($query): void {
                $query->select(['id', 'name']);
            },
            ])->whereNotNull('payroll_staff_id')
            ->orWhere('state', 'Rechazado')
            ->get();
        return response()->json(['records' => $warehouse_requests], JsonResponse::HTTP_OK);
    }
}
