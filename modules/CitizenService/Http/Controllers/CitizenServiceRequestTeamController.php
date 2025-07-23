<?php

namespace Modules\CitizenService\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Modules\CitizenService\Models\CitizenServiceRequestTeam;

/**
 * @class CitizenServiceRequestTeamController
 * @brief Gestiona los datos de los equipos asignados a solicitudes de trámites
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceRequestTeamController extends Controller
{
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware(
            'permission:citizenservice.request.teams.list',
            ['only' => ['index', 'vueList']]
        );
        $this->middleware(
            'permission:citizenservice.request.teams.create',
            ['only' => ['create', 'store']]
        );
        $this->middleware(
            'permission:citizenservice.request.teams.edit',
            ['only' => ['edit', 'update']]
        );
        $this->middleware(
            'permission:citizenservice.request.teams.delete',
            ['only' => 'destroy']
        );
    }

    /**
     * Obtiene la lista de equipos asignados a solicitudes de trámites
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json(['records' => CitizenServiceRequestTeam::all()], 200);
    }

    /**
     * Registra información de equipos asignados a solicitudes de trámites
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        /** Elimina las personas que no están en la lista y que previamente fueron asignadas */
        CitizenServiceRequestTeam::where(
            'citizen_service_request_id',
            array_column($request->all(), 'payroll_employee_id')
        )->whereNotIn(
            'payroll_employee_id',
            array_column($request->all(), 'payroll_employee_id')
        )->delete();
        DB::transaction(function () use ($request) {
            foreach ($request->all() as $team) {
                $team['start_at'] = Carbon::parse($team['start_at'])->format('Y-m-d');
                CitizenServiceRequestTeam::updateOrCreate(
                    [
                        'payroll_employee_id' => $team['payroll_employee_id'],
                        'citizen_service_request_id' => $team['citizen_service_request_id']
                    ],
                    [
                        'start_at' => $team['start_at'],
                        'tasks' => $team['tasks']
                    ]
                );
            }
        });
        return response()->json(['result' => true], 200);
    }

    /**
     * Muestra la información de un equipo asignado a una solicitud de trámite
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     CitizenServiceRequestTeam $team   Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function show(CitizenServiceRequestTeam $team): JsonResponse
    {
        return response()->json(['record' => $team], 200);
    }

    /**
     * Actualiza la información de un equipo asignado a una solicitud de trámite
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     CitizenServiceRequestTeam $team        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, CitizenServiceRequestTeam $team): JsonResponse
    {
        return response()->json(['result' => true], 200);
    }

    /**
     * Elimina un equipo asignado a una solicitud de trámite
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     CitizenServiceRequestTeam $team    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy(CitizenServiceRequestTeam $team): JsonResponse
    {
        $team->delete();
        return response()->json(['result' => true], 200);
    }
}
