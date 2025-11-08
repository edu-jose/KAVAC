<?php

namespace Modules\Warehouse\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Institution;
use App\Models\CodeSetting;
use App\Models\FiscalYear;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Modules\Warehouse\Models\Warehouse;
use Modules\Warehouse\Models\WarehouseInventoryProductMovement;
use Modules\Warehouse\Models\WarehouseInstitutionWarehouse;
use Modules\Warehouse\Models\WarehouseInventoryProduct;
use Modules\Warehouse\Models\WarehouseProductAttribute;
use Modules\Warehouse\Models\WarehouseInventoryRule;
use Modules\Warehouse\Models\WarehouseProductValue;
use Modules\Warehouse\Models\WarehouseMovement;
use Nwidart\Modules\Facades\Module;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Warehouse\Exports\WarehouseReceptionExport;
use Modules\Warehouse\Imports\WarehouseReceptionImport;

/**
 * @class WarehouseReceptionController
 * @brief Controlador de recepciones de almacén
 *
 * Clase que gestiona las recepciones de los productos al almacén
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WarehouseReceptionController extends Controller
{
    use ValidatesRequests;

    /**
     * Reglas de validación
     *
     * @var array $validateRules
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
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {

        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:warehouse.inventory.show', ['only' => ['index', 'vueInfo']]);
        $this->middleware('permission:warehouse.inventory.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:warehouse.inventory.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:warehouse.inventory.delete', ['only' => 'destroy']);
        $this->middleware('permission:warehouse.inventory.approve', ['only' => 'approvedReception']);
        $this->middleware('permission:warehouse.inventory.decline', ['only' => 'rejectedReception']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'warehouse_inventory_products' => ['required'],
            'warehouse_id' => ['required'],
            'institution_id' => ['required'],
            'warehouse_inventory_products.*.warehouse_product_id' => ['sometimes', 'required'],
            'warehouse_inventory_products.*.quantity' => ['sometimes', 'required'],
            'warehouse_inventory_products.*.currency_id' => ['sometimes', 'required'],
            'reception_date' => ['required'],
            'purchase_direct_hire_id' => 'required_without:direct_hire',
            'direct_hire' => 'required_without:purchase_direct_hire_id',

        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'institution_id.required' => 'El campo nombre de la organización es obligatorio.',
            'warehouse_id.required' => 'El campo nombre del almacén es obligatorio.',
            'warehouse_inventory_products.required' => 'Ingrese al menos un insumo a la solicitud.',
            'warehouse_inventory_products.*.warehouse_product_id.required' => 'El campo nombre del insumo es obligatorio.',
            'warehouse_inventory_products.*.quantity.required' => 'El campo cantidad es obligatorio.',
            'warehouse_inventory_products.*.currency_id.required' => 'El campo moneda es obligatorio.',
            'reception_date.required' => 'El campo "Fecha de ingreso" es obligatorio',
            'purchase_direct_hire_id.required_without' => 'Si estás trayendo la información de compras, selecciona una orden de compra o servicio.',
            'direct_hire.required_without' => 'Debe ingresar el código de la orden de compra o servicio.',
        ];
    }

    /**
     * Muestra un listado de las Recepciones o Ingresos de Almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('warehouse::receptions.list');
    }

    /**
     * Muestra el formulario para registrar un nuevo Ingreso de Almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        if (isset(auth()->user()->profile) && isset(auth()->user()->profile->institution_id)) {
            $institution = Institution::where(['id' => auth()->user()->profile->institution_id])->first();
        } else {
            $institution = Institution::where(['active' => true, 'default' => true])->first();
        }

        $purchase_existing = Module::has('Purchase') && Module::isEnabled('Purchase');

        if (
            $purchase_existing
        ) {
            $purchase_suppliers = template_choices(
                'Modules\Purchase\Models\PurchaseSupplier',
                ['rif', '-', 'name'],
                [],
                true
            );
            $purchase_direct_hires = template_choices(
                'Modules\Purchase\Models\PurchaseDirectHire',
                ['code'],
                [],
                true
            );
        } else {
            $purchase_suppliers = [];
            $purchase_direct_hires = [];
        }

        return view('warehouse::receptions.create', compact('institution', 'purchase_suppliers', 'purchase_direct_hires', 'purchase_existing'));
    }

    /**
     * Valida y Registra un nuevo Ingreso de Almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        $warehouse = Warehouse::find($request->warehouse_id);
        if (!$this->approveWarehouseReception(auth()->user(), $warehouse)) {
            return response()->json([
                'result' => false,
                'message' => 'No posee permisos para hacer ingresos a este almacén',
            ], JsonResponse::HTTP_FORBIDDEN);
        }

        $codeSetting = CodeSetting::where('table', 'warehouse_receptions')->first();
        if (is_null($codeSetting)) {
            $request->session()->flash('message', [
                'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                'text' => 'Debe configurar previamente el formato para el código a generar'
            ]);
            return response()->json(['result' => false, 'redirect' => route('warehouse.setting.index')], 200);
        }


        $currentFiscalYear = FiscalYear::select('year')
            ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

        $code  = generate_registration_code(
            $codeSetting->format_prefix,
            strlen($codeSetting->format_digits),
            (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
                $currentFiscalYear->year : date('Y')),
            $codeSetting->model,
            $codeSetting->field
        );

        $codeSetting = CodeSetting::where('table', 'warehouse_inventory_products')->first();
        if (is_null($codeSetting)) {
            $request->session()->flash('message', [
                'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                'text' => 'Debe configurar previamente el formato para el código a generar'
            ]);
            return response()->json(['result' => false, 'redirect' => route('warehouse.setting.index')], 200);
        }

        $inst_ware = WarehouseInstitutionWarehouse::where('warehouse_id', $request->warehouse_id)
            ->where('institution_id', $request->institution_id)->first();

        DB::transaction(function () use ($request, $inst_ware, $code) {
            $movement = WarehouseMovement::create([
                'code' => $code,
                'type' => 'C',
                'state' => 'Pendiente',
                'description' => 'Registro manual de productos en el inventario del almacén',
                'warehouse_institution_warehouse_end_id' => $inst_ware->id,
                'reception_date' => $request->input('reception_date'),
                'general_observations' => $request->general_observations ?? null,
                'direct_hire' => $request->direct_hire ?? null,
                'purchase_direct_hire_id' => $request->purchase_direct_hire_id ?? null,
                'supplier' => $request->supplier ?? null,
                'purchase_supplier_id' => $request->purchase_supplier_id ?? null,
                'user_id' => Auth::id(),
            ]);
            $equal = null;

            foreach ($request->warehouse_inventory_products as $product) {
                $product_id = $product['warehouse_product_id'];
                $currency = $product['currency_id'];
                $quantity = $product['quantity'];
                $value = $product['unit_value'];
                $minimum = $product['minimum'];
                $maximum = $product['maximum'];
                $expiration_date = $product['expiration_date'] ?? null;
                $batch_number = $product['batch_number'] ?? null;

                /* Se busca en el inventario por producto y unidad si existe un registro previo */

                $inventory = WarehouseInventoryProduct::where('warehouse_product_id', $product_id)
                    ->where('warehouse_institution_warehouse_id', $inst_ware->id)
                    ->where('unit_value', $value)->get();

                /* Si existe un registro previo se verifican los atributos del nuevo ingreso */
                if (count($inventory) > 0) {
                    foreach ($inventory as $product_inventory) {
                        /* Define si los atributos coinciden con los registrados */
                        $equal = true;

                        foreach ($product['warehouse_product_attributes'] as $attribute) {
                            $name = $attribute['name'];
                            $val = $attribute['value'];

                            $product_att = WarehouseProductAttribute::where('warehouse_product_id', $product_id)
                                ->where('name', $name)->first();

                            if (!is_null($product_att)) {
                                $product_value = WarehouseProductValue::where('value', $val)
                                    ->where('warehouse_product_attribute_id', $product_att->id)
                                    ->where('warehouse_inventory_product_id', $product_inventory->id)->first();

                                if (is_null($product_value)) {
                                    /* si el valor de este atributo no existe, son diferentes */
                                    $equal = false;
                                    break;
                                }
                            } else {
                                $equal = false;
                                break;
                            }
                        }
                        if ($equal === true) {
                            /* Se actualiza la regla de abastecimiento */
                            if (isset($minimum)) {
                                $rule = WarehouseInventoryRule::updateOrCreate([
                                    'warehouse_inventory_product_id' => $product_inventory->id,
                                ], [
                                    'minimum' => $minimum,
                                    'maximum' => $maximum,
                                    'user_id' => Auth::id(),
                                ]);
                            }
                            /* Se genera el movimiento, para su posterior aprobación */
                            $inventory_movement = WarehouseInventoryProductMovement::create([
                                'quantity' => $quantity,
                                'new_value' => $value,
                                'batch_number' => $batch_number,
                                'expiration_date' => $expiration_date,
                                'warehouse_movement_id' => $movement->id,
                                'warehouse_inventory_product_id' => $product_inventory->id,
                            ]);
                        }
                    }
                }
                if ((count($inventory) == 0) || ($equal == false)) {
                    /*
                     | Si no existe un registro previo de ese producto en inventario o algún atributo es diferente
                     | se genera un nuevo registro
                     */
                    $codeSetting = CodeSetting::where('table', 'warehouse_inventory_products')->first();

                    $codep  = generate_registration_code(
                        $codeSetting->format_prefix,
                        strlen($codeSetting->format_digits),
                        (strlen($codeSetting->format_year) == 2) ? date('y') : date('Y'),
                        $codeSetting->model,
                        $codeSetting->field
                    );

                    $product_inventory = WarehouseInventoryProduct::create([
                        'code' => $codep,
                        'warehouse_product_id' => $product_id,
                        'batch_number' => $batch_number ?? null,
                        'expiration_date' => $expiration_date ?? null,
                        'currency_id' => $currency,
                        'unit_value' => $value,
                        'warehouse_institution_warehouse_id' => $inst_ware->id,
                    ]);

                    if (isset($minimum)) {
                        $rule = WarehouseInventoryRule::create([
                            'minimum' => $minimum,
                            'maximum' => $maximum,
                            'warehouse_inventory_product_id' => $product_inventory->id,
                            'user_id' => Auth::id(),
                        ]);
                    }


                    $inventory_movement = WarehouseInventoryProductMovement::create([
                        'quantity' => $quantity,
                        'new_value' => $value,
                        'batch_number' => $batch_number,
                        'expiration_date' => $expiration_date,
                        'warehouse_movement_id' => $movement->id,
                        'warehouse_inventory_product_id' => $product_inventory->id,
                    ]);


                    foreach ($product['warehouse_product_attributes'] as $attribute) {
                        $name = $attribute['name'];
                        $value = $attribute['value'];

                        $field = WarehouseProductAttribute::where('warehouse_product_id', $product_id)
                            ->where('name', $name)->first();


                        WarehouseProductValue::create([
                            'value' => $value,
                            'warehouse_product_attribute_id' => $field->id,
                            'warehouse_inventory_product_id' => $product_inventory->id,
                        ]);
                    }
                }
            }
        });
        $warehouse_movement = WarehouseMovement::where('code', $code)->first();
        if (is_null($warehouse_movement)) {
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
        return response()->json(['result' => true, 'redirect' => route('warehouse.reception.index')], 200);
    }

    /**
     * Muestra el formulario para editar un Ingreso de Almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  integer $id Identificador único del ingreso de almacén
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $reception = WarehouseMovement::find($id);
        $purchase_existing = false;

        if (
            Module::has('Purchase') && Module::isEnabled('Purchase')
        ) {
            $purchase_existing = true;

            $purchase_suppliers = template_choices(
                'Modules\Purchase\Models\PurchaseSupplier',
                ['rif', '-', 'name'],
                [],
                true
            );
            $purchase_direct_hires = template_choices(
                'Modules\Purchase\Models\PurchaseDirectHire',
                ['code'],
                [],
                true
            );
        } else {
            $purchase_suppliers = [];
            $purchase_direct_hires = [];
        }
        return view('warehouse::receptions.create', compact('reception', 'purchase_suppliers', 'purchase_direct_hires', 'purchase_existing'));
    }

    /**
     * Actualiza la información de los Ingresos de Almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     * @param  integer $id Identificador único del ingreso de almacén
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $warehouse_movement = WarehouseMovement::find($id);

        $this->validate($request, $this->validateRules, $this->messages);

        $product_movements = WarehouseInventoryProductMovement::where(
            'warehouse_movement_id',
            $warehouse_movement->id
        )->get();

        $inst_ware = WarehouseInstitutionWarehouse::where('warehouse_id', $request->warehouse_id)
            ->where('institution_id', $request->institution_id)->first();

        DB::transaction(function () use ($request, $warehouse_movement, $inst_ware, $product_movements) {
            $warehouse_movement->warehouse_institution_warehouse_end_id = $inst_ware->id;
            $warehouse_movement->reception_date = $request->reception_date;
            $warehouse_movement->direct_hire = $request->direct_hire ?? null;
            $warehouse_movement->purchase_direct_hire_id = $request->purchase_direct_hire_id ?? null;
            $warehouse_movement->supplier = $request->supplier ?? null;
            $warehouse_movement->purchase_supplier_id = $request->purchase_supplier_id ?? null;
            $warehouse_movement->general_observations = $request->general_observations ?? null;
            $warehouse_movement->user_id = Auth::id();
            $warehouse_movement->save();
            $equal = null;

            $update = now();
            /* Se agregan los nuevos elementos a la solicitud */

            foreach ($request->warehouse_inventory_products as $product) {
                $product_id = $product['warehouse_product_id'];
                $currency = $product['currency_id'];
                $quantity = $product['quantity'];
                $value = $product['unit_value'];
                $minimum = $product['minimum'];
                $maximum = $product['maximum'];
                $expiration_date = $product['expiration_date'] ?? null;
                $batch_number = $product['batch_number'] ?? null;

                /* Se busca en el inventario por producto y unidad si existe un registro previo */

                $inventory = WarehouseInventoryProduct::where('warehouse_product_id', $product_id)
                    ->where('warehouse_institution_warehouse_id', $inst_ware->id)
                    ->where('unit_value', $value)
                    ->where('batch_number', $batch_number)
                    ->where('expiration_date', $expiration_date)
                    ->get();

                /* Si existe un registro previo se verifican los atributos del nuevo ingreso */
                if (count($inventory) > 0) {
                    foreach ($inventory as $product_inventory) {
                        $old_inventory = $product_movements->where(
                            'warehouse_inventory_product_id',
                            $product_inventory->id
                        )->first();

                        $equal =  (is_null($old_inventory)) ? false : true;
                        if ($equal == true) {
                            /* Verificamos que tengan los mismos atributos */

                            foreach ($product['warehouse_product_attributes'] as $attribute) {
                                $name = $attribute['name'];
                                $val = $attribute['value'];

                                $product_att = WarehouseProductAttribute::where('warehouse_product_id', $product_id)
                                    ->where('name', $name)->first();

                                if (!is_null($product_att)) {
                                    $product_value = WarehouseProductValue::where('value', $val)
                                        ->where('warehouse_product_attribute_id', $product_att->id)
                                        ->where('warehouse_inventory_product_id', $product_inventory->id)->first();

                                    #si el valor de este atributo no existe, son diferentes
                                    if (is_null($product_value)) {
                                        $equal = false;
                                        break;
                                    }
                                } else {
                                    $equal = false;
                                    break;
                                }
                            }
                            /* Si todos los atributos de este producto son iguales ajustamos la existencia */
                            if ($equal == true) {
                                $old_inventory->quantity = $quantity;
                                $old_inventory->new_value = $value;
                                $old_inventory->batch_number = $batch_number;
                                $old_inventory->expiration_date = $expiration_date;
                                $old_inventory->updated_at = $update;
                                $old_inventory->save();

                                /** Se actualiza la regla de abastecimiento */
                                if (isset($minimum)) {
                                    $rule = WarehouseInventoryRule::updateOrCreate([
                                        'warehouse_inventory_product_id' => $product_inventory->id,
                                    ], [
                                        'minimum' => $minimum,
                                        'maximum' => $maximum,
                                        'user_id' => Auth::id(),
                                    ]);
                                }
                            }
                        }
                    }
                }
                /* Si no existe un registro previo de ese producto en inventario ó algún atributo es diferente (se genera un nuevo registro) */
                if ((count($inventory) == 0) || ($equal == false)) {
                    $codeSetting = CodeSetting::where('table', 'warehouse_inventory_products')->first();

                    $codep  = generate_registration_code(
                        $codeSetting->format_prefix,
                        strlen($codeSetting->format_digits),
                        (strlen($codeSetting->format_year) == 2) ? date('y') : date('Y'),
                        $codeSetting->model,
                        $codeSetting->field
                    );

                    $product_inventory = WarehouseInventoryProduct::create([
                        'code' => $codep,
                        'warehouse_product_id' => $product_id,
                        'expiration_date' => $expiration_date,
                        'batch_number' => $batch_number,
                        'currency_id' => $currency,
                        'unit_value' => $value,
                        'warehouse_institution_warehouse_id' => $inst_ware->id,
                    ]);

                    if (isset($minimum)) {
                        $rule = WarehouseInventoryRule::create([
                            'minimum' => $minimum,
                            'maximum' => $maximum,
                            'warehouse_inventory_product_id' => $product_inventory->id,
                            'user_id' => Auth::id(),
                        ]);
                    }

                    $inventory_movement = WarehouseInventoryProductMovement::create([
                        'quantity' => $quantity,
                        'new_value' => $value,
                        'batch_number' => $batch_number,
                        'expiration_date' => $expiration_date,
                        'warehouse_movement_id' => $warehouse_movement->id,
                        'warehouse_inventory_product_id' => $product_inventory->id,
                        'updated_at' => $update,
                    ]);

                    foreach ($product['warehouse_product_attributes'] as $attribute) {
                        $name = $attribute['name'];
                        $value = $attribute['value'];

                        $field = WarehouseProductAttribute::where('warehouse_product_id', $product_id)
                            ->where('name', $name)->first();

                        WarehouseProductValue::create([
                            'value' => $value,
                            'warehouse_product_attribute_id' => $field->id,
                            'warehouse_inventory_product_id' => $product_inventory->id,
                        ]);
                    }
                }
            }

            /* Se eliminan los demas elementos de la solicitud */
            $warehouse_inventory_product_movements = WarehouseInventoryProductMovement::where(
                'warehouse_movement_id',
                $warehouse_movement->id
            )->where('updated_at', '!=', $update)->get();

            foreach ($warehouse_inventory_product_movements as $warehouse_inventory_product_movement) {
                $warehouse_inventory_product_movement->delete();
            }
        });
        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('warehouse.reception.index')], 200);
    }

    /**
     * Elimina un ingreso de almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  integer $id Identificador único del ingreso de almacén
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $reception = WarehouseMovement::find($id);
        $reception->delete();
        return response()->json(['message' => 'destroy'], 200);
    }

    /**
     * Vizualiza la información de una recepción o ingreso de almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  integer $id Identificador único del movimiento de almacén
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function vueInfo($id)
    {
        return response()->json(['records' => WarehouseMovement::where('id', $id)
            ->with(
                [
                    'warehouseInventoryProductMovements' => function ($query) {
                        $query->with(['warehouseInventoryProduct' => function ($query) {
                            $query->with(['warehouseProduct' => function ($query) {
                                $query->with('measurementUnit');
                            }, 'warehouseProductValues' => function ($query) {
                                $query->with('warehouseProductAttribute');
                            }, 'currency', 'warehouseInventoryRule']);
                        }]);
                    }, 'warehouseInstitutionWarehouseInitial', 'warehouseInstitutionWarehouseEnd',
                        'user', 'purchaseDirectHire', 'purchaseSupplier'
                ]
            )->first()], 200);
    }

    /**
     * Obtiene un listado de los ingresos de almacén registrados
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse Objeto con los registros a mostrar
     */
    public function vueList()
    {
        $warehouseMovement = WarehouseMovement::whereNull('warehouse_institution_warehouse_initial_id')
            ->with(
                'warehouseInstitutionWarehouseInitial',
                'warehouseInstitutionWarehouseEnd',
                'user',
                'purchaseDirectHire',
                'purchaseSupplier'
            )->get();
        if ($warehouseMovement) {
            $records = [];
            for ($i = 0; $i < count($warehouseMovement); $i++) {
                if (
                    $warehouseMovement[$i]['warehouseInstitutionWarehouseEnd'] &&
                    $warehouseMovement[$i]['warehouseInstitutionWarehouseEnd']['warehouse'] &&
                    $warehouseMovement[$i]['warehouseInstitutionWarehouseEnd']['warehouse']['active'] == true
                ) {
                    array_push($records, $warehouseMovement[$i]);
                }
            }
        }
        return response()->json([
            'records' => $records
        ], 200);
    }

    /**
     * Rechaza el ingreso de almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  integer $id Identificador único del ingreso de almacén
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function rejectedReception(Request $request, $id)
    {
        $warehouse_reception = WarehouseMovement::find($id);
        $warehouse_reception->state = 'Rechazado';
        $warehouse_reception->save();

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('warehouse.reception.index')], 200);
    }

    /**
     * Aprueba el ingreso de almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  integer $id Identificador único del ingreso de almacén
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function approvedReception(Request $request, $id)
    {
        $warehouse_reception = WarehouseMovement::find($id);
        $warehouse_reception->state = 'Aprobado';
        $warehouse_reception->save();

        $warehouse_inventory_product_movements = $warehouse_reception->WarehouseInventoryProductMovements;
        foreach ($warehouse_inventory_product_movements as $warehouse_inventory_product_movement) {
            $warehouse_inventory_product = $warehouse_inventory_product_movement->WarehouseInventoryProduct;
            if ($warehouse_inventory_product->exist > 0) {
                $warehouse_inventory_product->exist += $warehouse_inventory_product_movement->quantity;
                $warehouse_inventory_product->save();
            } else {
                $warehouse_inventory_product->exist = $warehouse_inventory_product_movement->quantity;
                $warehouse_inventory_product->save();
            }
        }
        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('warehouse.reception.index')], 200);
    }

    public function getPurchaseDirectHireSupplier($id)
    {
        if (
            Module::has('Purchase') && Module::isEnabled('Purchase')
        ) {
            $directHire = \Modules\Purchase\Models\PurchaseDirectHire::find($id);

            return response()->json([
                'records' => [
                    'id' => $directHire->purchase_supplier_id,
                ]
            ]);
        }
    }

    /**
     * Importar una plantilla Excel con los insumos o productos disponibles en el almacén
     *
     * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|mimes:xlsx,xls',
            'institution_id' => 'required|exists:institutions,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'direct_hire' => 'required',
            'supplier' => 'nullable',
            'reception_date' => 'required|date',
            'general_observations' => 'nullable|string'
        ], [
            'file.required' => 'El campo archivo es obligatorio.',
            'file.mimes' => 'El archivo debe ser un documento Excel (xlsx, xls).',
            'institution_id.required' => 'El campo institución es obligatorio.',
            'institution_id.exists' => 'La institución seleccionada no existe.',
            'warehouse_id.required' => 'El campo almacén es obligatorio.',
            'warehouse_id.exists' => 'El almacén seleccionado no existe.',
            'direct_hire.required' => 'El campo código es obligatorio.',
            'reception_date.required' => 'El campo fecha de ingreso es obligatorio.',
            'reception_date.date' => 'El campo fecha de ingreso debe ser una fecha válida.'
        ]);

        try {
            $import = new WarehouseReceptionImport(
                $validated['institution_id'],
                $validated['warehouse_id'],
                $validated['direct_hire'],
                $validated['supplier'] ?? null,
                $validated['reception_date'],
                $validated['general_observations'] ?? null
            );

            Excel::import($import, $request->file('file'));

            $errors = $import->getErrors();

            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'errors' => $errors,
                    'records' => $import->getValidRecords(),
                    'message' => 'Errores encontrados durante la importación'
                ], 422);
            }
            return response()->json([
                'success' => true,
                'records' => $import->getValidRecords(),
                'message' => 'Importación completada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo: ' . $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTrace() : []
            ], 500);
        }
    }

    /**
     * Exporta una plantilla Excel con los insumos o productos disponibles en el almacén
     *
     * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function exportTemplate(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|integer|exists:warehouses,id',
            'institution_id' => 'required|integer|exists:institutions,id'
        ]);

        $warehouse = Warehouse::find($request->warehouse_id);
        $institution = Institution::find($request->institution_id);

        // Generar el Excel
        $export = new WarehouseReceptionExport($warehouse, $institution);

        return Excel::download($export, 'plantilla_recepcion_almacen.xlsx');
    }

    /**
     * Define si el usuario actual tiene la capacidad de aprobar un ingreso a un almacén
     *
     * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     * @param \App\Models\User $user
     * @param \Modules\Warehouse\Models\Warehouse $warehouse
     * @return bool
     */
    private function approveWarehouseReception(User $user, Warehouse $warehouse): bool
    {
        if (auth()->user()->isAdmin()) {
            return true;
        } else {
            if (Module::has('Payroll') && Module::isEnabled('Payroll')) {
                $payrollStaff = \Modules\Payroll\Models\PayrollEmployment::query()
                    ->has('profile')
                    ->whereHas('profile', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })->first()
                    ->payrollStaff;

                foreach ($warehouse->responsibles as $responsible) {
                    if ($payrollStaff->id === $responsible['id']) {
                        return true;
                    }
                }

                return false;
            }
        }

        return false;
    }
}
