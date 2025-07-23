<?php

namespace Modules\CitizenService\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;
use Modules\CitizenService\Models\CitizenServiceProcedureType;

/**
 * @class CitizenServiceProcedureTypeController
 * @brief Gestiona las acciones para los tipos de trámites
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceProcedureTypeController extends Controller
{
    protected $rules;
    protected $errorMessages;

    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware(
            'permission:citizenservice.procedure.type.list',
            ['only' => ['index', 'vueList']]
        );
        $this->middleware(
            'permission:citizenservice.procedure.type.create',
            ['only' => ['create', 'store']]
        );
        $this->middleware(
            'permission:citizenservice.procedure.type.edit',
            ['only' => ['edit', 'update']]
        );
        $this->middleware(
            'permission:citizenservice.procedure.type.delete',
            ['only' => 'destroy']
        );

        $this->rules = [
            'name' => ['required', 'string', 'max:100', 'unique:citizen_service_procedure_types'],
            'description' => ['nullable'],
        ];

        $this->errorMessages = [
            'name.required' => 'El nombre del tipo de trámite es obligatorio.',
            'name.max' => 'El nombre del tipo de trámite no debe superar los 100 caracteres.',
            'name.unique' => 'Ya ha sido registrado un tipo de trámite con ese nombre.'
        ];
    }

    /**
     * [descripción del método]
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    Renderable
     */
    public function index()
    {
        $procedureTypes = CitizenServiceProcedureType::all();
        return response()->json(['records' => $procedureTypes], 200);
    }

    /**
     * [descripción del método]
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->rules, $this->errorMessages);

        DB::transaction(function () use ($request) {
            CitizenServiceProcedureType::create($request->all());
        });

        return response()->json(['result' => true], 200);
    }

    /**
     * [descripción del método]
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     CitizenServiceProcedureType    $procedureType    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function show(CitizenServiceProcedureType $procedureType)
    {
        return response()->json(['record' => $procedureType], 200);
    }

    /**
     * [descripción del método]
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     CitizenServiceProcedureType $procedureType        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, CitizenServiceProcedureType $procedureType)
    {
        $this->rules['name'] = [
            'required',
            'string',
            'max:100',
            'unique:citizen_service_procedure_types,name,' . $procedureType->id
        ];
        $this->validate($request, $this->rules, $this->errorMessages);

        DB::transaction(function () use ($request, $procedureType) {
            $procedureType->update($request->all());
        });

        return response()->json(['record' => $procedureType], 200);
    }

    /**
     * [descripción del método]
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     CitizenServiceProcedureType $procedureType    Identificador del registro
     *
     * @return    Renderable
     */
    public function destroy(CitizenServiceProcedureType $procedureType)
    {
        $procedureType->delete();
        return response()->json(['result' => true], 200);
    }

    /**
     * Summary of vueList
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function vueList(Request $request)
    {
        //
    }
}
