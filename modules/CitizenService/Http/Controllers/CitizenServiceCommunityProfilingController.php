<?php

namespace Modules\CitizenService\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Repositories\UploadImageRepository;
use Modules\CitizenService\Models\CitizenServiceCommunityProfiling;
use Modules\CitizenService\Models\CitizenServiceCommunityInstitution;

/**
 * @class CitizenServiceCommunityProfilingController
 * @brief Controlador para la gestión de las caracterizaciones de comunidades
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceCommunityProfilingController extends Controller
{
    protected $rules;
    protected $ruleMessages;

    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware(
            'permission:citizenservice.community.profiling.list',
            ['only' => ['index', 'vueList']]
        );
        $this->middleware(
            'permission:citizenservice.community.profiling.create',
            ['only' => ['create', 'store']]
        );
        $this->middleware(
            'permission:citizenservice.community.profiling.edit',
            ['only' => ['edit', 'update']]
        );
        $this->middleware(
            'permission:citizenservice.community.profiling.delete',
            ['only' => 'destroy']
        );

        $this->rules = [
            'citizen_service_community_id' => ['required'],
            'tic_committee_name' => ['nullable', 'required_if:has_tic_committee,true'],
            'main_needs' => ['required'],
            'communal_councils' => ['nullable', 'required_if:is_communal_council,true', 'array'],
            'communes' => ['nullable', 'required_if:is_commune,true', 'array'],
            'institutions' => ['required', 'array', 'min:1'],
        ];

        $this->ruleMessages = [
            'citizen_service_community_id.required' => 'El campo comunidad es obligatorio.',
            'tic_committee_name.required_if' => 'El campo nombre del comité de ciencia y tecnología es obligatorio.',
            'main_needs.required' => 'El campo principales necesidades de la comunidad es obligatorio.',
            'communal_councils.required_if' => 'El campo nombre del concejo comunal es obligatorio.',
            'communal_councils.array' => 'El campo nombre de los concejos comunales es obligatorio.',
            'communes.required_if' => 'El campo nombre de la comuna es obligatorio.',
            'communes.array' => 'El campo nombre de las comunas es obligatorio.',
            'institutions.required' => 'Los campos de instituciones son obligatorios.',
            'institutions.min' => 'Debe tener al menos una institución.',
        ];
    }

    /**
     * Muestra el listado de caracterizaciones de comunidades
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    \Illuminate\View\View
     */
    public function index(): View
    {
        return view('citizenservice::community-profilings.list');
    }

    /**
     * Muestra el formulario para crear una nueva caracterización de comunidad
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    \Illuminate\View\View
     */
    public function create(): View
    {
        return view('citizenservice::community-profilings.create');
    }

    /**
     * Almacena una nueva caracterización de comunidad
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, UploadImageRepository $up): JsonResponse
    {
        $request->merge([
            'is_communal_council' => $request->is_communal_council ?? false,
            'is_commune' => $request->is_commune ?? false,
            'has_tic_committe' => $request->has_tic_committe ?? false,
        ]);
        DB::transaction(function () use ($request, $up): void {
            $communityProfiling = CitizenServiceCommunityProfiling::create($request->all());
            if ($request->is_communal_council && $request->communal_councils) {
                foreach ($request->communal_councils as $communalCouncil) {
                    $communityProfiling->communalCouncils()->create($communalCouncil);
                }
            }
            if ($request->is_commune && $request->communes) {
                foreach ($request->communes as $commune) {
                    $communityProfiling->communes()->create($commune);
                }
            }
            foreach ($request->institutions as $institution) {
                $inst = $communityProfiling->institutions()->create($institution);
                foreach ($institution['images'] as $image) {
                    $up->uploadImage(
                        $image,
                        'pictures',
                        CitizenServiceCommunityInstitution::class,
                        $inst->id
                    );
                }
            }
        });
        return response()->json([
            'result' => true,
            'redirect' => route('citizenservice.community-profilings.index')
        ], 200);
    }

    /**
     * Muestra información de una caracterización de comunidad
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     CitizenServiceCommunityProfiling $communityProfiling    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function show(CitizenServiceCommunityProfiling $communityProfiling): View
    {
        return view('citizenservice::show');
    }

    /**
     * Muestra el formulario para editar una caracterización de comunidad
     *
     * @param     CitizenServiceCommunityProfiling $communityProfiling    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function edit(CitizenServiceCommunityProfiling $communityProfiling): View
    {
        return view('citizenservice::community-profilings.create', compact('communityProfiling'));
    }

    /**
     * Actualiza una caracterización de comunidad
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     CitizenServiceCommunityProfiling $communityProfiling        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(
        Request $request,
        UploadImageRepository $up,
        CitizenServiceCommunityProfiling $communityProfiling
    ): JsonResponse {
        $request->merge([
            'is_communal_council' => $request->is_communal_council ?? false,
            'is_commune' => $request->is_commune ?? false,
            'has_tic_committe' => $request->has_tic_committe ?? false,
        ]);
        DB::transaction(function () use ($request, $communityProfiling, $up): void {
            $communityProfiling->update($request->all());
            if ($request->communal_councils) {
                foreach ($request->communal_councils as $communalCouncil) {
                    $communityProfiling->communalCouncils()->updateOrCreate(
                        ['id' => $communalCouncil['id'] ?? null],
                        $communalCouncil
                    );
                }
            }
            if ($request->communes) {
                foreach ($request->communes as $commune) {
                    $communityProfiling->communes()->updateOrCreate(
                        ['id' => $commune['id'] ?? null],
                        $commune
                    );
                }
            }
            /* Elimina las instituciones que hayan sido borradas del arreglo */
            $communityProfiling->institutions()->whereNotIn(
                'id',
                array_column($request->institutions, 'id')
            )->delete();
            foreach ($request->institutions as $institution) {
                $inst = $communityProfiling->institutions()->updateOrCreate(
                    ['id' => $institution['id'] ?? null],
                    $institution
                );
                foreach ($institution['images'] as $image) {
                    if (str_contains($image, 'http')) {
                        continue;
                    }
                    $up->uploadImage(
                        $image,
                        'pictures',
                        CitizenServiceCommunityInstitution::class,
                        $inst->id
                    );
                }
            }
        });
        return response()->json([
            'result' => true,
            'redirect' => route('citizenservice.community-profilings.index')
        ], 200);
    }

    /**
     * Elimina una caracterización de comunidad
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     CitizenServiceCommunityProfiling $communityProfiling    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy(CitizenServiceCommunityProfiling $communityProfiling): JsonResponse
    {
        $communityProfiling->communalCouncils()->delete();
        $communityProfiling->communes()->delete();
        foreach ($communityProfiling->institutions as $institution) {
            Image::where([
                'imageable_id' => $institution->id,
                'imageable_type' => CitizenServiceCommunityInstitution::class
            ])->delete();
        }
        $communityProfiling->institutions()->delete();
        $communityProfiling->delete();
        return response()->json(['result' => true], 200);
    }

    /**
     * Muestra el listado de caracterizaciones de comunidades
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function vueList(): JsonResponse
    {
        $communityProfiling = CitizenServiceCommunityProfiling::get();
        return response()->json([
            'records' => $communityProfiling
        ], 200);
    }
}
