<?php

namespace Modules\CitizenService\Http\Controllers;

use Illuminate\Http\Request;
use App\Rules\Rif as RifRule;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Modules\CitizenService\Models\CitizenServiceServedInstitution;

/**
 * @class CitizenServiceServedInstitutionController
 * @brief Gestiona la información de las instituciones que se atienden
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceServedInstitutionController extends Controller
{
    protected $rules;
    protected $errorMessages;

    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware(
            'permission:citizenservice.institution.list',
            ['only' => ['index', 'vueList']]
        );
        $this->middleware(
            'permission:citizenservice.institution.create',
            ['only' => ['create', 'store']]
        );
        $this->middleware(
            'permission:citizenservice.institution.edit',
            ['only' => ['edit', 'update']]
        );
        $this->middleware(
            'permission:citizenservice.institution.delete',
            ['only' => 'destroy']
        );

        $this->rules = [
            'name' => ['required', 'string', 'unique:citizen_service_served_institutions'],
            'description' => ['nullable'],
            'rif' => [
                'required',
                'regex:/^[E, G, J, P, V, 0-9 ]+$/',
                'size:10',
                'unique:citizen_service_served_institutions,rif',
                new RifRule()
            ]
        ];

        $this->errorMessages = [
            'name.required' => 'El nombre de la institución es obligatorio.',
            'name.unique' => 'El nombre de la institución ya se encuentra registrado.',
            'rif.required' => 'El rif de la institución es obligatorio.',
            'rif.unique' => 'El rif de la institución ya se encuentra registrado.',
            'rif.regex' => 'El rif de la institución es inválido.',
            'rif.size' => 'El rif de la institución debe tener 10 caracteres.'
        ];
    }

    /**
     * Lista de instituciones que se atienden
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => CitizenServiceServedInstitution::all()], 200);
    }

    /**
     * Registra una nueva institución atendida por la OAC
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
            CitizenServiceServedInstitution::create($request->all());
        });
        return response()->json(['result' => true], 200);
    }

    /**
     * Obtiene información de una institución
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     CitizenServiceServedInstitution $institution    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function show(CitizenServiceServedInstitution $institution)
    {
        return response()->json(['record' => $institution], 200);
    }

    /**
     * Actualiza la información de una institución
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, CitizenServiceServedInstitution $institution)
    {
        $this->rules['name'] = Rule::unique('citizen_service_served_institutions', 'name')->ignore($institution->id);
        $this->rules['rif'] = Rule::unique('citizen_service_served_institutions', 'rif')->ignore($institution->id);
        $this->validate($request, $this->rules, $this->errorMessages);
        DB::transaction(function () use ($request, $institution) {
            $institution->update($request->all());
        });
        return response()->json(['result' => true], 200);
    }

    /**
     * Elimina una institución
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     CitizenServiceServedInstitution $institution    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy(CitizenServiceServedInstitution $institution)
    {
        $institution->delete();
        return response()->json(['result' => true], 200);
    }
}
