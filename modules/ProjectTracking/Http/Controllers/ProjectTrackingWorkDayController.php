<?php

namespace Modules\ProjectTracking\Http\Controllers;

use App\Models\CodeSetting;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ProjectTracking\Models\ProjectTrackingWorkDay;

/**
 * @class ProjectTrackingWorkDayController
 * @brief Controlador que maneja las operaciones para las jornadas laborales
 *
 * Controlador que maneja las operaciones para las jornadas laborales
 *
 * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingWorkDayController extends Controller
{
    use ValidatesRequests;

    /**
     * Arreglo con las reglas de validación sobre los datos de un formulario
     *
     * @var array $validateRules
     */
    protected $validateRules;

    /**
     * Arreglo con los mensajes para las reglas de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return void
     */
    public function __construct()
    {
        /** Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:project.tracking.work.day.create', ['only' => ['store']]);
        $this->middleware('permission:project.tracking.work.day.edit', ['only' => ['update']]);
    }

    /**
     * Devuelve el primer registro de la jornada laboral
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return    Renderable    Primer registro e las jornadas laborales
     */
    public function index(): JsonResponse
    {
        $workingSchedules = ProjectTrackingWorkDay::all();

        return response()->json([
            'records' => $workingSchedules,
        ], JsonResponse::HTTP_OK);
    }

    /**
     * [descripción del método]
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function create()
    {
        return view('projecttracking::create');
    }

    /**
     * Crea una nueva jornada laboral
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    Renderable    JSON con los datos creados
     */
    public function store(Request $request): JsonResponse
    {
        $firstWorkingSchedule = ProjectTrackingWorkDay::query()
        ->first();

        if (isset($firstWorkingSchedule)) {
            return $this->update($request, $firstWorkingSchedule);
        }
        $newWorkingSchedule = ProjectTrackingWorkDay::query()
        ->create([
            'from' => $request->from,
            'to' => $request->to,
            'working_days' => json_encode($request->working_days),
            'working_hours' => $request->working_hours,
        ]);

        return response()->json([
            'record' => $newWorkingSchedule,
            'message' => 'Success',
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Devuelve un registro especifico de la jornada laboral
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    JSON con los datos devueltos
     */
    public function show(ProjectTrackingWorkDay $working_schedule): JsonResponse
    {
        return response()->json([
            'record' => $working_schedule
        ], JsonResponse::HTTP_OK);
    }

    /**
     * [descripción del método]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function edit($id)
    {
        return view('projecttracking::edit');
    }

    /**
     * Actualiza un registro de la jornada laboral
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    Renderable    JSON con los datos actualizados
     */
    public function update(Request $request, ProjectTrackingWorkDay $work_day): JsonResponse
    {
        $work_day->update([
            'from' => $request->from,
            'to' => $request->to,
            'working_days' => json_encode($request->working_days),
            'working_hours' => $request->working_hours,
        ]);

        return response()->json([
            'record' => $work_day,
            'message' => 'Success',
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Elimina un registro de la jornada laboral
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function destroy(ProjectTrackingWorkDay $working_schedule): JsonResponse
    {
        $working_schedule->delete();

        return response()->json([
            'record' => $working_schedule
        ], JsonResponse::HTTP_OK);
    }
}
