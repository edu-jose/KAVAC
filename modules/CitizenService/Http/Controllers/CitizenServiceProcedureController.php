<?php

namespace Modules\CitizenService\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Modules\CitizenService\Models\CitizenServiceProcedure;

/**
 * @class CitizenServiceProcedureController
 * @brief Gestiona los datos de los trámites del módulo de la OAC
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceProcedureController extends Controller
{
    protected $rules;
    protected $errorMessages;

    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware(
            'permission:citizenservice.procedure.list',
            ['only' => ['index', 'vueList']]
        );
        $this->middleware(
            'permission:citizenservice.procedure.create',
            ['only' => ['create', 'store']]
        );
        $this->middleware(
            'permission:citizenservice.procedure.edit',
            ['only' => ['edit', 'update']]
        );
        $this->middleware(
            'permission:citizenservice.procedure.delete',
            ['only' => 'destroy']
        );

        $this->rules = [
            'name' => ['required', 'string', 'unique:citizen_service_procedures'],
            'description' => ['nullable'],
            'citizen_service_procedure_type_id' => ['required']
        ];

        $this->errorMessages = [
            'name.required' => 'El nombre del trámite es obligatorio.',
            'name.unique' => 'Ya ha sido registrado un trámite con ese nombre.',
            'citizen_service_procedure_type_id.required' => 'El tipo de trámite es obligatorio.'
        ];
    }

    /**
     * Listado de trámites
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $procedures = CitizenServiceProcedure::all();
        return response()->json(['records' => $procedures], 200);
    }

    /**
     * Registra la información de nuevos trámites
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
            CitizenServiceProcedure::create($request->all());
        });
        return response()->json(['result' => true], 200);
    }

    /**
     * Obtiene información de un trámite
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     CitizenServiceProcedure $procedure    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function show(CitizenServiceProcedure $procedure)
    {
        return response()->json(['record' => $procedure], 200);
    }

    /**
     * Actuializa la información de un trámite
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     CitizenServiceProcedure $procedure        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, CitizenServiceProcedure $procedure)
    {
        $this->rules['name'] = ['required', 'string', 'unique:citizen_service_procedures,name,' . $procedure->id];
        $this->validate($request, $this->rules, $this->errorMessages);
        DB::transaction(function () use ($request, $procedure) {
            $procedure->update($request->all());
        });
        return response()->json(['record' => $procedure], 200);
    }

    /**
     * Elimina un trámite
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     CitizenServiceProcedure $procedure    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy(CitizenServiceProcedure $procedure)
    {
        $procedure->delete();
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
