<?php

namespace Modules\WorkAttendance\Http\Controllers;

use App\Models\Document;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Modules\WorkAttendance\Models\WorkAttendanceCustomSchedule;

/**
 * @class WorkAttendanceCustomScheduleController
 * @brief Controlador de la gestión de horarios personalizados
 *
 * @author Ing. Roldan Vargas <roldandvg@gmail.com> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WorkAttendanceCustomScheduleController extends Controller
{
    protected $rules;
    protected $ruleMesssages;

    public function __construct()
    {
        /* Establece permisos de acceso para cada método del controlador */
        $this->middleware(
            'permission:workattendance.custom.schedule.index',
            ['only' => ['index', 'show']]
        );
        $this->middleware(
            'permission:workattendance.custom.schedule.store',
            ['only' => ['create', 'store', 'getStaffs']]
        );
        $this->middleware(
            'permission:workattendance.custom.schedule.update',
            ['only' => ['edit', 'update', 'getStaffs']]
        );
        $this->middleware(
            'permission:workattendance.custom.schedule.delete',
            ['only' => 'destroy']
        );
        $this->rules = [
            'start_date_at' => ['required', 'date', 'before:end_date_at'],
            'end_date_at' => ['required', 'date', 'after:start_date_at'],
            'custom_schedule_type' => ['required', 'in:O,D,E'],
            'reason' => ['required'],
            'payroll_staff_id' => ['required', 'exists:payroll_staffs,id'],
            'authorized_payroll_staff_id' => ['required', 'exists:payroll_staffs,id'],
            'documents' => ['required', 'array', 'min:1'],
            'schedule' => ['required', 'array', 'min:1'],
        ];
        $this->ruleMesssages = [
            'start_date_at.required' => 'La fecha de inicio es obligatoria',
            'start_date_at.date' => 'La fecha de inicio debe ser una fecha válida',
            'start_date_at.before' => 'La fecha de inicio debe ser menor a la fecha de finalización',
            'end_date_at.required' => 'La fecha de finalización es obligatoria',
            'end_date_at.date' => 'La fecha de finalización debe ser una fecha válida',
            'end_date_at.after' => 'La fecha de finalización debe ser mayor a la fecha de inicio',
            'custom_schedule_type.required' => 'El tipo de horario personalizado es obligatoria',
            'custom_schedule_type.in' => 'El tipo de horario personalizado es inválido',
            'reason.required' => 'El motivo del horario personalizado es obligatorio',
            'payroll_staff_id.required' => 'La persona a la cual asignar el horario personalizado es obligatoria',
            'payroll_staff_id.exists' => 'La persona a la cual asignar el horario personalizado es inválida',
            'authorized_payroll_staff_id.required' => 'La persona que autoriza el horario personalizado es obligatoria',
            'authorized_payroll_staff_id.exists' => 'La persona que autoriza el horario personalizado es inválida',
            'documents.required' => 'El documento que avala el horario personalizado es obligatorio',
            'documents.array' => 'Debe indicar un documento que avale el horario personalizado',
            'documents.min' => 'Debe indicar un documento que avale el horario personalizado',
            'schedule.required' => 'El horario personalizado es obligatorio',
            'schedule.array' => 'Debe indicar un horario personalizado',
            'schedule.min' => 'Debe indicar un horario personalizado',
        ];
    }

    /**
     * Listado de horarios personalizados
     *
     * @author    Ing. Roldan Vargas <roldandvg@gmail.com> | <roldandvg@gmail.com>
     *
     * @return    \Illuminate\View\View
     */
    public function index(): View
    {
        return view('workattendance::custom-schedule.index');
    }

    /**
     * Formulario para el registro de horarios personalizados
     *
     * @author    Ing. Roldan Vargas <roldandvg@gmail.com> | <roldandvg@gmail.com>
     *
     * @return    \Illuminate\View\View
     */
    public function create(): View
    {
        return view('workattendance::custom-schedule.create');
    }

    /**
     * Registra un nuevo horario personalizado
     *
     * @author    Ing. Roldan Vargas <roldandvg@gmail.com> | <roldandvg@gmail.com>
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $this->validate($request, $this->rules, $this->ruleMesssages);
        $request->merge([
            'is_teacher' => $request->custom_schedule_type === 'D',
            'is_student' => $request->custom_schedule_type === 'E',
            'is_other' => $request->custom_schedule_type === 'O',
            'active' => $request->active ? true : false,
        ]);
        DB::transaction(function () use ($request) {
            $workAttendanceCustomSchedule = WorkAttendanceCustomSchedule::create($request->all());
            if ($request->documents) {
                //Verifica si tiene documentos para establecer la relación
                foreach ($request->documents as $file) {
                    $doc = Document::find($file);
                    $doc->documentable_id = $workAttendanceCustomSchedule->id;
                    $doc->documentable_type = WorkAttendanceCustomSchedule::class;
                    $doc->save();
                }
            }
        });
        $request->session()->flash('message', ['type' => 'store']);
        return response()->json([
            'result' => true,
            'redirect' => route('workattendance.custom.schedule.index')
        ], 200);
    }

    /**
     * Muestra información de un horario personalizado
     *
     * @author    Ing. Roldan Vargas <roldandvg@gmail.com> | <roldandvg@gmail.com>
     *
     * @param     WorkAttendanceCustomSchedule $customSchedule Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function show(WorkAttendanceCustomSchedule $customSchedule): JsonResponse
    {
        return response()->json(['result' => true, 'record' => []], 200);
    }

    /**
     * Formulario para la edición de un horario personalizado
     *
     * @param     WorkAttendanceCustomSchedule $customSchedule Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function edit(WorkAttendanceCustomSchedule $customSchedule): View
    {
        return view(
            'workattendance::custom-schedule.create',
            compact('customSchedule')
        );
    }

    /**
     * Actualiza un horario personalizado
     *
     * @author    Ing. Roldan Vargas <roldandvg@gmail.com> | <roldandvg@gmail.com>
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, WorkAttendanceCustomSchedule $customSchedule): JsonResponse
    {
        if ($request?->documentUrl) {
            // Si ya tenía previamente un documento, no es obligatorio
            $this->rules['documents'] = ['nullable'];
        }
        $this->validate($request, $this->rules, $this->ruleMesssages);
        $request->merge([
            'is_teacher' => $request->custom_schedule_type === 'D',
            'is_student' => $request->custom_schedule_type === 'E',
            'is_other' => $request->custom_schedule_type === 'O',
            'active' => $request->active ? true : false,
        ]);
        DB::transaction(function () use ($request, $customSchedule) {
            $customSchedule->update($request->all());
            if ($request->documents) {
                Document::where([
                    'documentable_id' => $customSchedule->id,
                    'documentable_type' => WorkAttendanceCustomSchedule::class
                ])->delete();
                //Verifica si tiene documentos para establecer la relación
                foreach ($request->documents as $file) {
                    $doc = Document::find($file);
                    $doc->documentable_id = $customSchedule->id;
                    $doc->documentable_type = WorkAttendanceCustomSchedule::class;
                    $doc->save();
                }
            }
        });
        $request->session()->flash('message', ['type' => 'update']);
        return response()->json([
            'result' => true,
            'redirect' => route('workattendance.custom.schedule.index')
        ], 200);
    }

    /**
     * Elimina un horario personalizado
     *
     * @author    Ing. Roldan Vargas <roldandvg@gmail.com> | <roldandvg@gmail.com>
     *
     * @param     WorkAttendanceCustomSchedule $customSchedule Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy(WorkAttendanceCustomSchedule $customSchedule): JsonResponse
    {
        $customSchedule->delete();
        return response()->json(['result' => true], 200);
    }

    public function getList(Request $request): JsonResponse
    {
        $customSchedules = WorkAttendanceCustomSchedule::orderBy('start_date_at', 'desc');
        if ($request->payroll_staff_id || $request->from_date || $request->to_date) {
            if ($request->from_date && $request->to_date) {
                $customSchedules->whereBetween('start_date_at', [$request->from_date, $request->to_date]);
            } elseif ($request->from_date) {
                $customSchedules->where('start_date_at', '>=', $request->from_date);
            } elseif ($request->to_date) {
                $customSchedules->where('start_date_at', '<=', $request->to_date);
            }
            if ($request->payroll_staff_id) {
                $customSchedules->where('payroll_staff_id', $request->payroll_staff_id);
            }
        }
        return response()->json(['result' => true, 'records' => $customSchedules->get()], 200);
    }
}
