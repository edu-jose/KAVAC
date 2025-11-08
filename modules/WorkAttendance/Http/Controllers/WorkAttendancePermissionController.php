<?php

namespace Modules\WorkAttendance\Http\Controllers;

use Nette\Utils\Json;
use App\Models\Document;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Modules\WorkAttendance\Models\WorkAttendancePermission;

/**
 * @class WorkAttendancePermissionController
 * @brief Controlador de permisos del módulo de asistencia laboral
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WorkAttendancePermissionController extends Controller
{
    protected $rules;
    protected $ruleMessages;

    public function __construct()
    {
        $this->rules = [
            'start_date_at' => ['required', 'date', 'before_or_equal:end_date_at'],
            'start_time_at' => ['nullable', 'regex:/^(0[1-9]|1[0-2]):[0-5][0-9] (am|pm)$/i', 'before:end_time_at'],
            'end_date_at' => ['required', 'date', 'after_or_equal:start_date_at'],
            'end_time_at' => ['nullable', 'regex:/^(0[1-9]|1[0-2]):[0-5][0-9] (am|pm)$/i', 'after:start_time_at'],
            'reason' => ['required'],
            'status' => ['required', 'in:pending,approved,rejected'],
            'payroll_staff_id' => ['required', 'exists:payroll_staffs,id']
        ];

        $this->ruleMessages = [
            'start_date_at.required' => 'La fecha de inicio es requerida.',
            'start_date_at.date' => 'La fecha de inicio debe tener un formato de fecha.',
            'start_date_at.before_or_equal' => 'La fecha de inicio debe ser menor o igual a la fecha de finalización.',
            'start_time_at.regex' => 'El formato de la hora de inicio de la solicitud de permiso es incorrecto.',
            'start_time_at.before' => 'La hora de inicio de la solicitud de permiso ' .
                                      'debe ser menor a la hora de finalización.',
            'end_date_at.required' => 'La fecha de finalización es requerida.',
            'end_date_at.date' => 'La fecha de finalización debe tener un formato de fecha.',
            'end_date_at.after_or_equal' => 'La fecha de finalización debe ser mayor o igual a la fecha de inicio.',
            'end_time_at.regex' => 'El formato de la hora de finalización de la solicitud de permiso es incorrecto.',
            'end_time_at.after' => 'La hora de finalización de la solicitud de permiso ' .
                                   'debe ser mayor a la hora de inicio.',
            'reason.required' => 'El motivo del permiso es requerido.',
            'status.required' => 'El estado del permiso es requerido.',
            'status.in' => 'El estado del permiso debe ser uno de los siguientes: pendiente, aprobado, rechazado.',
            'payroll_staff_id.required' => 'La persona solicitante del permiso es requerida.',
            'payroll_staff_id.exists' => 'La persona indicada no existe en el sistema.'
        ];

        /* Establece permisos de acceso para cada método del controlador */
        $this->middleware(
            'permission:workattendance.permissions.index',
            ['only' => ['index', 'show', 'getList']]
        );
        $this->middleware(
            'permission:workattendance.permissions.store',
            ['only' => ['create', 'store']]
        );
        $this->middleware(
            'permission:workattendance.permissions.update',
            ['only' => ['edit', 'update', 'setApproved', 'setRejected']]
        );
        $this->middleware(
            'permission:workattendance.permissions.delete',
            ['only' => 'destroy']
        );
    }

    /**
     * Muestra una lista de los permisos de asistencia laboral
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    View
     */
    public function index(): View
    {
        return view('workattendance::permissions.index');
    }

    /**
     * Muestra el formulario para crear un nuevo permiso de asistencia laboral
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    View
     */
    public function create(): View
    {
        return view('workattendance::permissions.create-edit');
    }

    /**
     * Registra un nuevo permiso de asistencia laboral
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
        DB::transaction(function () use ($request) {
            $requestPermission = WorkAttendancePermission::create($request->all());
            if ($request->documentFiles) {
                //Verifica si tiene documentos para establecer la relación
                foreach ($request->documentFiles as $file) {
                    $doc = Document::find($file);
                    $doc->documentable_id = $requestPermission->id;
                    $doc->documentable_type = WorkAttendancePermission::class;
                    $doc->save();
                }
            }
        });
        $request->session()->flash('message', ['type' => 'store']);
        return response()->json([
            'result' => true,
            'redirect' => route('workattendance.permissions.index')
        ], 200);
    }

    /**
     * Muestra los detalles de un permiso de asistencia laboral específico
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     WorkAttendancePermission    $permission    Objeto con información del permiso solicitado
     *
     * @return    JsonResponse
     */
    public function show(WorkAttendancePermission $permission): JsonResponse
    {
        return response()->json(['message' => 'Success', 'requestPermission' => $permission], 200);
    }

    /**
     * Muestra el formulario para editar un permiso de asistencia laboral existente
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     WorkAttendancePermission    $permission    Objeto con información del permiso solicitado
     *
     * @return    View
     */
    public function edit(WorkAttendancePermission $permission): View
    {
        return view('workattendance::permissions.create-edit', ['requestPermission' => $permission]);
    }

    /**
     * Actualiza un permiso de asistencia laboral existente
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     WorkAttendancePermission    $permission    Objeto con información del permiso solicitado
     *
     * @return    JsonResponse
     */
    public function update(Request $request, WorkAttendancePermission $permission): JsonResponse
    {
        $this->validate($request, $this->rules, $this->ruleMessages);
        DB::transaction(function () use ($request, $permission) {
            $permission->update($request->all());
            if ($request->documentFiles) {
                //Verifica si tiene documentos para establecer la relación
                $permission->document()->delete();
                foreach ($request->documentFiles as $file) {
                    $doc = Document::find($file);
                    $doc->documentable_id = $permission->id;
                    $doc->documentable_type = WorkAttendancePermission::class;
                    $doc->save();
                }
            }
        });
        $request->session()->flash('message', ['type' => 'update']);
        return response()->json([
            'result' => true,
            'redirect' => route('workattendance.permissions.index')
        ], 200);
    }

    /**
     * Elimina un permiso de asistencia laboral existente
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     WorkAttendancePermission    $permission    Objeto con información del permiso solicitado
     *
     * @return    JsonResponse
     */
    public function destroy(WorkAttendancePermission $permission): JsonResponse
    {
        $permission->delete();
        return response()->json(['result' => true], 200);
    }

    /**
     * Obtiene una lista de permisos de solicitudes de permisos de ausencia laboral
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return JsonResponse
     */
    public function getList(Request $request): JsonResponse
    {
        $workAttendancePermissions = WorkAttendancePermission::with([
            'payrollStaff' => function ($query) {
                $query->select('id', 'first_name', 'last_name');
            }
        ])->orderBy('start_date_at', 'desc');
        if ($request->payroll_employment_id || $request->from_date || $request->to_date) {
            if ($request->from_date && $request->to_date) {
                $workAttendancePermissions->where(
                    'start_date_at',
                    '>=',
                    $request->from_date
                )->where('end_date_at', '<=', $request->to_date);
            } elseif ($request->from_date) {
                $workAttendancePermissions->where('start_date_at', '>=', $request->from_date);
            } elseif ($request->to_date) {
                $workAttendancePermissions->where('start_date_at', '<=', $request->to_date);
            }
        }
        $workAttendancePermissions = $workAttendancePermissions->paginate((int) $request->limit);

        return response()->json([
            'data' => $workAttendancePermissions->items(),
            'count' => $workAttendancePermissions->total(),
            'last_page' => $workAttendancePermissions->lastPage(),
            'tableRef' => 'tableResults',
        ], 200);
    }

    /**
     * Aprueba una solicitud de ausencia laboral
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param \Illuminate\Http\Request $request
     * @param \Modules\WorkAttendance\Models\WorkAttendancePermission $permission
     *
     * @return JsonResponse|mixed
     */
    public function setApproved(Request $request, WorkAttendancePermission $permission): JsonResponse
    {
        $permission->status = 'approved';
        $permission->save();
        return response()->json(['result' => true], 200);
    }

    /**
     * Rechaza una solicitud de ausencia laboral
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param \Illuminate\Http\Request $request
     * @param \Modules\WorkAttendance\Models\WorkAttendancePermission $permission
     *
     * @return JsonResponse|mixed
     */
    public function setRejected(Request $request, WorkAttendancePermission $permission): JsonResponse
    {
        $permission->status = 'rejected';
        $permission->save();
        return response()->json(['result' => true], 200);
    }
}
