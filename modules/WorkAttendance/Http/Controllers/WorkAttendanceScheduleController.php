<?php

namespace Modules\WorkAttendance\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Modules\WorkAttendance\Models\WorkAttendanceSchedule;

/**
 * @class WorkAttendanceScheduleController
 * @brief Gestiona el horario laboral
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WorkAttendanceScheduleController extends Controller
{
    protected $rules;
    protected $ruleMessages;
    protected $formatTime = 'H:i';
    protected $formatTimeSeconds = 'H:i:s';
    protected $initHour = "00:00";

    public function __construct()
    {
        $this->middleware('permission:workattendance.setting.index');

        $this->rules = [
            'day_name' => ['required'],
            'description' => ['nullable', 'max:250'],
            'start_time' => ['required', 'before:end_time'],
            'end_time' => ['required', 'after:start_time'],
            'break_time' => ['nullable', 'min:5'],
            'lunch_time' => ['nullable', 'min:5'],
        ];

        $this->ruleMessages = [
            'day_name.required' => 'El dia de la semana es requerido',
            'start_time.required' => 'La hora de inicio es requerida',
            'start_time.before' => 'La hora de inicio debe ser anterior a la hora de fin',
            'end_time.required' => 'La hora de fin es requerida',
            'end_time.after' => 'La hora de fin debe ser posterior a la hora de inicio',
        ];
    }

    /**
     * Listado de horarios laborales
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json([
            'result' => true,
            'records' => WorkAttendanceSchedule::get()->map(function ($schedule) {
                return $schedule->toArray();
            })
        ], 200);
    }

    /**
     * Registra los datos del horario laboral
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->rules['break_time'] = [
            ...$this->rules['break_time'],
            function ($attribute, $value, $fail) use ($request) {
                $formatStart = strlen($request->start_time) > 5 ? $this->formatTimeSeconds : $this->formatTime;
                $formatEnd = strlen($request->end_time) > 5 ? $this->formatTimeSeconds : $this->formatTime;
                $formatValue = strlen($value) > 5 ? $this->formatTimeSeconds : $this->formatTime;
                $startTime = Carbon::createFromFormat($formatStart, $request->start_time);
                $endTime = Carbon::createFromFormat($formatEnd, $request->end_time);

                $totalSeconds = $endTime->diffInSeconds($startTime);
                $breakSeconds = Carbon::createFromFormat($formatValue, $value)->diffInSeconds(
                    Carbon::createFromFormat($this->formatTime, $this->initHour)
                );

                if ($breakSeconds >= $totalSeconds) {
                    $fail('El tiempo de descanso debe ser menor al total de tiempo entre la hora de inicio y fin');
                }
            },
        ];
        $this->rules['lunch_time'] = [
            ...$this->rules['lunch_time'],
            function ($attribute, $value, $fail) use ($request) {
                $formatStart = strlen($request->start_time) > 5 ? $this->formatTimeSeconds : $this->formatTime;
                $formatEnd = strlen($request->end_time) > 5 ? $this->formatTimeSeconds : $this->formatTime;
                $formatValue = strlen($value) > 5 ? $this->formatTimeSeconds : $this->formatTime;
                $startTime = Carbon::createFromFormat($formatStart, $request->start_time);
                $endTime = Carbon::createFromFormat($formatEnd, $request->end_time);

                $totalSeconds = $endTime->diffInSeconds($startTime);
                $lunchSeconds = Carbon::createFromFormat($formatValue, $value)->diffInSeconds(
                    Carbon::createFromFormat('H:i', $this->initHour)
                );

                if ($lunchSeconds >= $totalSeconds) {
                    $fail('El tiempo de almuerzo debe ser menor al total de tiempo entre la hora de inicio y fin');
                }
            },
        ];
        $this->validate($request, $this->rules, $this->ruleMessages);
        $schedule = WorkAttendanceSchedule::where([
            'day_name' => $request->day_name,
            'day_number' => $request->day_number,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time
        ])->first();
        if ($schedule) {
            $this->validate($request, [
                'day_name' => ['unique:work_attendance_schedules,day_name']
            ], [
                'day_name.unique' => 'Ya se encuentra registrada una jornada laboral con la misma información',
            ]);
        }
        $workAttendance = DB::transaction(function () use ($request) {
            return WorkAttendanceSchedule::create($request->all());
        });
        return response()->json([
            'result' => true,
            'record' => $workAttendance
        ], 200);
    }

    /**
     * Actualiza los datos del horario laboral
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     \Modules\WorkAttendance\Models\WorkAttendanceSchedule   $schedule        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, WorkAttendanceSchedule $schedule)
    {
        $this->rules['break_time'] = [
            ...$this->rules['break_time'],
            function ($attribute, $value, $fail) use ($request) {
                $formatStart = strlen($request->start_time) > 5 ? $this->formatTimeSeconds : $this->formatTime;
                $formatEnd = strlen($request->end_time) > 5 ? $this->formatTimeSeconds : $this->formatTime;
                $formatValue = strlen($value) > 5 ? $this->formatTimeSeconds : $this->formatTime;
                $startTime = Carbon::createFromFormat($formatStart, $request->start_time);
                $endTime = Carbon::createFromFormat($formatEnd, $request->end_time);

                $totalSeconds = $endTime->diffInSeconds($startTime);
                $breakSeconds = Carbon::createFromFormat($formatValue, $value)->diffInSeconds(
                    Carbon::createFromFormat('H:i', $this->initHour)
                );

                if ($breakSeconds >= $totalSeconds) {
                    $fail('El tiempo de descanso debe ser menor al total de tiempo entre la hora de inicio y fin');
                }
            },
        ];
        $this->rules['lunch_time'] = [
            ...$this->rules['lunch_time'],
            function ($attribute, $value, $fail) use ($request) {
                $formatStart = strlen($request->start_time) > 5 ? $this->formatTimeSeconds : $this->formatTime;
                $formatEnd = strlen($request->end_time) > 5 ? $this->formatTimeSeconds : $this->formatTime;
                $formatValue = strlen($value) > 5 ? $this->formatTimeSeconds : $this->formatTime;
                $startTime = Carbon::createFromFormat($formatStart, $request->start_time);
                $endTime = Carbon::createFromFormat($formatEnd, $request->end_time);

                $totalSeconds = $endTime->diffInSeconds($startTime);
                $lunchSeconds = Carbon::createFromFormat($formatValue, $value)->diffInSeconds(
                    Carbon::createFromFormat('H:i', $this->initHour)
                );

                if ($lunchSeconds >= $totalSeconds) {
                    $fail('El tiempo de almuerzo debe ser menor al total de tiempo entre la hora de inicio y fin');
                }
            },
        ];
        $this->validate($request, $this->rules, $this->ruleMessages);

        $workAttendance = DB::transaction(function () use ($request, $schedule) {
            return $schedule->update($request->all());
        });
        return response()->json([
            'result' => true,
            'record' => $workAttendance
        ], 200);
    }

    /**
     * Elimina los datos del horario laboral
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     \Modules\WorkAttendance\Models\WorkAttendanceSchedule    $schedule    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy(WorkAttendanceSchedule $schedule)
    {
        $schedule->delete();
        return response()->json([
            'result' => true,
            'record' => $schedule
        ], 200);
    }
}
