<?php

namespace Modules\Purchase\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\DB;
use Modules\Purchase\Models\PurchasePriority;

/**
 * @class PurchasePriorityController
 * @brief Controlador de prioridades de compra
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchasePriorityController extends Controller
{
    protected $maxName = 'max:5';

    protected $maxColor = 'max:8';

    protected $rules;

    protected $ruleMessages;

    public function __construct()
    {
        $this->middleware('permission:purchase.priorities.index', ['only' => ['index', 'show']]);
        $this->middleware('permission:purchase.priorities.store', ['only' => ['store']]);
        $this->middleware('permission:purchase.priorities.update', ['only' => ['update']]);
        $this->middleware('permission:purchase.priorities.delete', ['only' => ['destroy']]);

        $this->rules = [
            'name' => ['required', $this->maxName, 'unique:purchase_priorities,name'],
            'description' => ['required', 'unique:purchase_priorities,description'],
            'color' => [
                'required', 'min:4', $this->maxColor, 'unique:purchase_priorities,color',
                'not_in:#FFFFFF,#000000'
            ]
        ];
        $this->ruleMessages = [
            'name.required' => 'El campo Nombre es obligatorio.',
            'name.unique' => 'El campo Nombre ya ha sido registrado.',
            'name.max' => 'El campo Nombre no debe superar los 5 caracteres.',
            'description.required' => 'El campo Descripción es obligatorio.',
            'description.unique' => 'El campo Descripción ya ha sido registrado.',
            'color.required' => 'El campo Color es obligatorio.',
            'color.unique' => 'El campo Color ya ha sido registrado.',
            'color.min' => 'El campo Color es inválido.',
            'color.max' => 'El campo Color es inválido.',
            'color.not_in' => 'El campo Color es inválido, seleccione un color distinto a blanco o negro'
        ];
    }

    /**
     * Listado con todas las prioridades de compra
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    JsonResponse      JSON con información de todas las prioridades de compra
     */
    public function index(): JsonResponse
    {
        $priorities = PurchasePriority::all();
        return response()->json(['records' => $priorities], 200);
    }

    /**
     * Registra una nueva prioridad de compra
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    JsonResponse     JSON con datos de respuesta a la petición
     */
    public function store(Request $request): JsonResponse
    {
        $this->validate($request, $this->rules, $this->ruleMessages);

        $priority = DB::transaction(function () use ($request) {
            return PurchasePriority::create($request->all());
        });

        return response()->json(['record' => $priority, 'message' => 'Success'], 200);
    }

    /**
     * Muestra la información de una prioridad de compra
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     PurchasePriority $priority    Identificador del registro
     *
     * @return    JsonResponse    JSON con información de la prioridad de compra
     */
    public function show(PurchasePriority $priority): JsonResponse
    {
        return response()->json(['record' => $priority], 200);
    }

    /**
     * Actualiza la información de una prioridad de compra
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     PurchasePriority $priority        Identificador del registro
     *
     * @return    JsonResponse     JSON con datos de respuesta a la petición
     */
    public function update(Request $request, PurchasePriority $priority): JsonResponse
    {
        $this->rules = [
            'name' => ['required', $this->maxName, 'unique:purchase_priorities,name,' . $priority->id],
            'description' => ['required', 'unique:purchase_priorities,description,' . $priority->id],
            'color' => [
                'required', 'min:4', $this->maxColor, 'unique:purchase_priorities,color,' . $priority->id,
                'not_in:#FFFFFF,#000000'
            ]
        ];
        $this->validate($request, $this->rules, $this->ruleMessages);

        $priority = DB::transaction(function () use ($request, $priority) {
            return $priority->update($request->all());
        });

        return response()->json(['record' => $priority, 'message' => 'Success'], 200);
    }

    /**
     * Elimina una prioridad de compra
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     PurchasePriority $priority    Identificador del registro
     *
     * @return    JsonResponse     JSON con datos de respuesta a la petición
     */
    public function destroy(PurchasePriority $priority): JsonResponse
    {
        $priority->delete();
        return response()->json(['record' => $priority, 'message' => 'Success'], 200);
    }
}
