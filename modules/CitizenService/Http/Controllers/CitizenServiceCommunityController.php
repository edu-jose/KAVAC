<?php

namespace Modules\CitizenService\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;
use Modules\CitizenService\Models\CitizenServiceCommunity;

/**
 * @class CitizenServiceCommunityController
 * @brief Gestiona los datos de las comunidades
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceCommunityController extends Controller
{
    protected $rules;
    protected $errorMessages;

    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware(
            'permission:citizenservice.community.list',
            ['only' => ['index', 'vueList']]
        );
        $this->middleware(
            'permission:citizenservice.community.create',
            ['only' => ['create', 'store']]
        );
        $this->middleware(
            'permission:citizenservice.community.edit',
            ['only' => ['edit', 'update']]
        );
        $this->middleware(
            'permission:citizenservice.community.delete',
            ['only' => 'destroy']
        );

        $this->rules = [
            'name' => ['required', 'string', 'unique:citizen_service_communities'],
            'country_id' => ['required'],
            'estate_id' => ['required'],
            'city_id' => ['required'],
            'municipality_id' => ['required'],
            'parish_id' => ['required'],
            'location' => ['nullable'],
            'population' => ['nullable']
        ];

        $this->errorMessages = [
            'name.required' => 'El nombre de la comunidad es obligatorio.',
            'name.unique' => 'Ya ha sido registrado una comunidad con ese nombre.',
            'country_id.required' => 'El Pais es obligatorio.',
            'estate_id.required' => 'El Estado es obligatorio.',
            'city_id.required' => 'La Ciudad es obligatoria.',
            'municipality_id.required' => 'El Municipio es obligatorio.',
            'parish_id.required' => 'La Parroquia es obligatoria.'
        ];
    }

    /**
     * Obtiene un listado de comunidades
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $communities = CitizenServiceCommunity::with([
            'parish.municipality.estate.country',
            'city'
        ])->get()->map(function ($community) {
            $community->location = $community->location === null ? '' : $community->location;
            $community->population = $community->population === null ? '' : $community->population;
            return [
                ...$community->toArray(),
                'municipality_id' => $community->parish->municipality_id,
                'estate_id' => $community->parish->municipality->estate_id,
                'country_id' => $community->parish->municipality->estate->country_id
            ];
        });
        return response()->json(['records' => $communities], 200);
    }

    /**
     * Registra la información de nuevas comunidades
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
            CitizenServiceCommunity::create($request->all());
        });
        return response()->json(['result' => true], 200);
    }

    /**
     * Obtiene la información de una comunidad
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     CitizenServiceCommunity $community    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function show(CitizenServiceCommunity $community)
    {
        return response()->json(['record' => $community], 200);
    }

    /**
     * Actualiza la información de una comunidad
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, CitizenServiceCommunity $community)
    {
        $this->rules['name'] = ['required', 'string', 'unique:citizen_service_communities,name,' . $community->id];
        $this->validate($request, $this->rules, $this->errorMessages);
        DB::transaction(function () use ($request, $community) {
            $community->update($request->all());
        });
        return response()->json(['record' => $community], 200);
    }

    /**
     * Elimina una comunidad
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     CitizenServiceCommunity $community    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy(CitizenServiceCommunity $community)
    {
        $community->delete();
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
