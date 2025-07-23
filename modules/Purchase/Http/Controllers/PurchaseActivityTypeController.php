<?php

namespace Modules\Purchase\Http\Controllers;

use Nette\Utils\Json;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;
use Modules\Purchase\Models\PurchaseActivityType;

/**
 * @class PurchaseActivityTypeController
 * @brief Clase que administra los tipos de actividades de compra
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseActivityTypeController extends Controller
{
    protected $rules;

    protected $ruleMessages;

    public function __construct()
    {
        $this->middleware(
            'permission:purchase.activity.types.index',
            ['only' => ['index', 'show']]
        );
        $this->middleware(
            'permission:purchase.activity.types.store',
            ['only' => ['store']]
        );
        $this->middleware(
            'permission:purchase.activity.types.update',
            ['only' => ['update']]
        );
        $this->middleware(
            'permission:purchase.activity.types.delete',
            ['only' => ['destroy']]
        );

        $this->rules = [
            'name' => 'required|string|max:100|unique:purchase_activity_types,name',
            'description' => 'nullable',
        ];

        $this->ruleMessages = [
            'name.required' => 'El campo nombre es obligatorio.',
            'name.string' => 'El campo nombre debe ser una cadena de texto.',
            'name.max' => 'El campo nombre no puede tener más de 100 caracteres.',
            'name.unique' => 'Ya existe un tipo de actividad con el mismo nombre.',
        ];
    }

    /**
     * Listado de tipos de actividades de compra
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json(['records' => PurchaseActivityType::get()], 200);
    }

    /**
     * Registra un nuevo tipo de actividad de compra
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

        $activityType = DB::transaction(function () use ($request) {
            return PurchaseActivityType::create($request->all());
        });

        return response()->json(['record' => $activityType, 'message' => 'Success'], 200);
    }

    /**
     * Muestra la información de un tipo de actividad de compra
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     PurchaseActivityType $activityType    Identificador del registro
     *
     * @return    JsonResponse
     */
    public function show(PurchaseActivityType $activityType): JsonResponse
    {
        return response()->json(['record' => $activityType, 'message' => 'Success'], 200);
    }

    /**
     * [descripción del método]
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     PurchaseActivityType $activityType        Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function update(Request $request, PurchaseActivityType $activityType)
    {
        $this->rules['name'] = [
            'required',
            'string',
            'max:100',
            'unique:purchase_activity_types,name,' . $activityType->id
        ];
        $this->validate($request, $this->rules, $this->ruleMessages);

        $activityType = DB::transaction(function () use ($request, $activityType) {
            return $activityType->update($request->all());
        });

        return response()->json(['record' => $activityType, 'message' => 'Success'], 200);
    }

    /**
     * Elimina un tipo de actividad de compra
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     PurchaseActivityType $activityType    Identificador del registro
     *
     * @return    JsonResponse
     */
    public function destroy(PurchaseActivityType $activityType): JsonResponse
    {
        $activityType->delete();
        return response()->json(['record' => $activityType, 'message' => 'Success'], 200);
    }
}
