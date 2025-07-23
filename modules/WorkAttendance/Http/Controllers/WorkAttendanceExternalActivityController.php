<?php

namespace Modules\WorkAttendance\Http\Controllers;

use Carbon\Carbon;
use App\Models\Document;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Modules\WorkAttendance\Models\PayrollStaff;
use Modules\WorkAttendance\Models\WorkAttendance;
use Modules\WorkAttendance\Services\WorkAttendanceService;
use Modules\WorkAttendance\Models\WorkAttendanceExternalActivity;

/**
 * @class WorkAttendanceExternalActivityController
 * @brief Gestiona los datos de las actividades externas
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WorkAttendanceExternalActivityController extends Controller
{
    protected $rules;

    protected $errorMessages;

    protected $withoutStaffRelations = [
        'payrollBloodType',
        'payrollDisability',
        'payrollEmployment',
        'payrollFinancial',
        'payrollGender',
        'payrollLicenseDegree',
        'payrollNationality',
        'payrollProfessional',
        'payrollResponsibility',
        'payrollSocioeconomic',
        'payrollStaffUniformSize'
    ];

    protected $withoutEmploymentRelations = [
        'payrollCoordination',
        'payrollInactivityType',
        'payrollPositionType',
        'payrollPositions',
        'payrollPreviousJob',
        'payrollStaffType'
    ];

    public function __construct()
    {
        $this->rules = [
            'start_date_at' => ['required', 'date'],
            'start_time_at' => ['required', 'regex:/^(0[1-9]|1[0-2]):[0-5][0-9] (am|pm)$/i', 'before:end_time_at'],
            'end_time_at' => ['required', 'regex:/^(0[1-9]|1[0-2]):[0-5][0-9] (am|pm)$/i', 'after:start_time_at'],
            'reason' => ['required', 'string'],
            'payroll_staffs' => ['required'],
        ];

        $this->errorMessages = [
            'start_date_at.required' => 'La fecha de inicio de la actividad es obligatoria.',
            'start_time_at.required' => 'La hora de inicio de la actividad es obligatoria.',
            'start_time_at.regex' => 'El formato de la hora de inicio de la actividad es incorrecto.',
            'start_time_at.before' => 'La hora de inicio de la actividad debe ser menor a la hora de finalización.',
            'end_time_at.required' => 'La hora de finalización de la actividad es obligatoria.',
            'end_time_at.regex' => 'El formato de la hora de finalización de la actividad es incorrecto.',
            'end_time_at.after' => 'La hora de finalización de la actividad debe ser mayor a la hora de inicio.',
            'reason.required' => 'El mótivo de la actividad es obligatorio.',
            'payroll_staffs.required' => 'El(los) trabajador(es) que participa(n) en la actividad es obligatorio.',
        ];

        /* Establece permisos de acceso para cada método del controlador */
        $this->middleware(
            'permission:workattendance.external.activity.index',
            ['only' => ['index', 'show']]
        );
        $this->middleware(
            'permission:workattendance.external.activity.store',
            ['only' => ['create', 'store', 'getStaffs']]
        );
        $this->middleware(
            'permission:workattendance.external.activity.update',
            ['only' => ['edit', 'update', 'getStaffs']]
        );
        $this->middleware(
            'permission:workattendance.external.activity.delete',
            ['only' => 'destroy']
        );
    }

    /**
     * Obtiene un listado de las actividades externas
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    \Illuminate\View\View
     */
    public function index(): View
    {
        return view('workattendance::external-activity.index');
    }

    public function create(): View
    {
        return view('workattendance::external-activity.create');
    }

    /**
     * Registra una nueva actividad externa
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $this->validate($request, $this->rules, $this->errorMessages);

        $startTime = Carbon::parse($request->start_time_at)->format('H:i');
        $endTime = Carbon::parse($request->end_time_at)->format('H:i');

        $request->merge([
            'start_at' => $request->start_date_at . ' ' . $startTime,
            'end_at' => $request->start_date_at . ' ' . $endTime
        ]);

        DB::transaction(function () use ($request, $startTime, $endTime) {
            $staffs = array_column($request->payroll_staffs, 'id');
            $externalActivity = WorkAttendanceExternalActivity::create($request->all());
            foreach ($staffs as $staff) {
                $workAttendance = (new WorkAttendanceService())->createWorkAttendance([
                    'date_at' => $request->start_date_at,
                    'entry_time' => $startTime,
                    'exit_time' => $endTime,
                    'payroll_staff_id' => $staff
                ]);
                $externalActivity->staffs()->create([
                    'payroll_staff_id' => $staff,
                    'work_attendance_id' => $workAttendance->id
                ]);
            }
            if ($request->documentFiles) {
                //Verifica si tiene documentos para establecer la relación
                foreach ($request->documentFiles as $file) {
                    $doc = Document::find($file);
                    $doc->documentable_id = $externalActivity->id;
                    $doc->documentable_type = WorkAttendanceExternalActivity::class;
                    $doc->save();
                }
            }
        });
        $request->session()->flash('message', ['type' => 'store']);
        return response()->json([
            'result' => true,
            'redirect' => route('workattendance.external.activity.index')
        ], 200);
    }

    /**
     * Obtiene los datos de una actividad externa
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     WorkAttendanceExternalActivity $externalActivity    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function show(WorkAttendanceExternalActivity $externalActivity): JsonResponse
    {
        return response()->json(['record' => $externalActivity], 200);
    }

    public function edit(WorkAttendanceExternalActivity $externalActivity): View
    {
        return view(
            'workattendance::external-activity.create',
            compact('externalActivity')
        );
    }

    /**
     * Actualiza la información de una actividad externa
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, WorkAttendanceExternalActivity $externalActivity): JsonResponse
    {
        $this->validate($request, $this->rules, $this->errorMessages);

        $startTime = Carbon::parse($request->start_time_at)->format('H:i');
        $endTime = Carbon::parse($request->end_time_at)->format('H:i');

        $request->merge([
            'start_at' => $request->start_date_at . ' ' . $startTime,
            'end_at' => $request->start_date_at . ' ' . $endTime,
        ]);

        DB::transaction(function () use ($request, $externalActivity, $startTime, $endTime) {
            $externalActivity->update($request->all());
            $staffs = array_column($request->payroll_staffs, 'id');
            foreach ($externalActivity->staffs()->get() as $staff) {
                WorkAttendance::where('id', $staff->work_attendance_id)->delete();
            }
            $externalActivity->staffs()->delete();
            foreach ($staffs as $staff) {
                $workAttendance = (new WorkAttendanceService())->createWorkAttendance([
                    'date_at' => $request->start_date_at,
                    'entry_time' => $startTime,
                    'exit_time' => $endTime,
                    'payroll_staff_id' => $staff
                ]);
                $externalActivity->staffs()->create([
                    'payroll_staff_id' => $staff,
                    'work_attendance_id' => $workAttendance->id
                ]);
            }
            if ($request->documentFiles) {
                //Verifica si tiene documentos para establecer la relación
                $externalActivity->document()->delete();
                foreach ($request->documentFiles as $file) {
                    $doc = Document::find($file);
                    $doc->documentable_id = $externalActivity->id;
                    $doc->documentable_type = WorkAttendanceExternalActivity::class;
                    $doc->save();
                }
            }
        });

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json([
            'result' => true,
            'redirect' => route('workattendance.external.activity.index')
        ], 200);
    }

    /**
     * Elimina una actividad externa
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     WorkAttendanceExternalActivity $externalActivity    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy(WorkAttendanceExternalActivity $externalActivity): JsonResponse
    {
        $externalActivity->staffs()->delete();
        $externalActivity->delete();
        return response()->json(['result' => true], 200);
    }

    public function getList(Request $request): JsonResponse
    {
        $workAttendanceExternalActivities = WorkAttendanceExternalActivity::orderBy('start_at', 'desc');
        if ($request->payroll_employment_id || $request->from_date || $request->to_date) {
            if ($request->from_date && $request->to_date) {
                $workAttendanceExternalActivities->whereBetween('start_at', [$request->from_date, $request->to_date]);
            } elseif ($request->from_date) {
                $workAttendanceExternalActivities->where('start_at', '>=', $request->from_date);
            } elseif ($request->to_date) {
                $workAttendanceExternalActivities->where('start_at', '<=', $request->to_date);
            }
        }
        $workAttendanceExternalActivities = $workAttendanceExternalActivities->paginate((int) $request->limit);

        return response()->json([
            'data' => $workAttendanceExternalActivities->items(),
            'count' => $workAttendanceExternalActivities->total(),
            'last_page' => $workAttendanceExternalActivities->lastPage(),
            'tableRef' => 'tableResults',
        ], 200);
    }

    public function getStaffs(Request $request): JsonResponse
    {
        $payrollStaffs = PayrollStaff::select(
            'first_name',
            'last_name',
            'id'
        )->orderBy('first_name')->orderBy('last_name')->get()->map(function ($staff) {
            $staff->full_name = $staff->first_name . ' ' . $staff->last_name;
            return [
                'id' => $staff->id,
                'full_name' => $staff->full_name,
            ];
        });
        return response()->json([
            'staffs' => $payrollStaffs,
        ], 200);
    }
}
