<?php

namespace Modules\Purchase\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;
use Modules\Purchase\Models\PurchasePriorityOrder;

/**
 * @class PurchasePriorityOrderController
 * @brief Controlador de ordenes de prioridad de compra
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchasePriorityOrderController extends Controller
{
    protected $rules;

    protected $ruleMessages;

    public function __construct()
    {
        $this->middleware('permission:purchase.priority.orders.index', ['only' => ['index', 'show']]);
        $this->middleware('permission:purchase.priority.orders.store', ['only' => ['store']]);
        $this->middleware('permission:purchase.priority.orders.update', ['only' => ['update']]);
        $this->middleware('permission:purchase.priority.orders.delete', ['only' => ['destroy']]);

        $this->rules = [
            'order' => ['required', 'unique:purchase_priority_orders,order'],
            'description' => ['required', 'unique:purchase_priority_orders,description'],
        ];
        $this->ruleMessages = [
            'order.required' => 'El campo Orden es obligatorio.',
            'order.unique' => 'El campo Orden ya ha sido registrado.',
            'description.required' => 'El campo Descripción es obligatorio.',
            'description.unique' => 'El campo Descripción ya ha sido registrado.',
        ];
    }

    /**
     * Listado de ordenes de prioridad de compra
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    JsonResponse
     */
    public function index(): JsonResponse
    {
        $priorities = PurchasePriorityOrder::all();
        return response()->json(['records' => $priorities], 200);
    }

    /**
     * Registrar un nuevo orden de prioridad de compra
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $this->validate($request, $this->rules, $this->ruleMessages);

        $priorityOrder = DB::transaction(function () use ($request) {
            return PurchasePriorityOrder::create($request->all());
        });

        return response()->json(['record' => $priorityOrder, 'message' => 'Success'], 200);
    }

    /**
     * Mostrar un orden de prioridad de compra
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     PurchasePriorityOrder $priorityOrder    Identificador del registro
     *
     * @return    JsonResponse
     */
    public function show(PurchasePriorityOrder $priorityOrder): JsonResponse
    {
        return response()->json(['record' => $priorityOrder], 200);
    }

    /**
     * Actualizar un orden de prioridad de compra
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     PurchasePriorityOrder $priorityOrder        Identificador del registro
     *
     * @return    JsonResponse
     */
    public function update(Request $request, PurchasePriorityOrder $priorityOrder): JsonResponse
    {
        $this->rules = [
            'order' => ['required', 'unique:purchase_priority_orders,order,' . $priorityOrder->id],
            'description' => ['required', 'unique:purchase_priority_orders,description,' . $priorityOrder->id],
        ];
        $this->validate($request, $this->rules, $this->ruleMessages);

        $priorityOrder = DB::transaction(function () use ($request, $priorityOrder) {
            return $priorityOrder->update($request->all());
        });

        return response()->json(['record' => $priorityOrder, 'message' => 'Success'], 200);
    }

    /**
     * Elimina un orden de prioridad de compra
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     PurchasePriorityOrder $priorityOrder    Identificador del registro
     *
     * @return    JsonResponse
     */
    public function destroy(PurchasePriorityOrder $priorityOrder): JsonResponse
    {
        $priorityOrder->delete();
        return response()->json(['record' => $priorityOrder, 'message' => 'Success'], 200);
    }
}
