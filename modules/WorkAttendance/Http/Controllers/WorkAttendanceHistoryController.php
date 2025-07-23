<?php

namespace Modules\WorkAttendance\Http\Controllers;

use Carbon\Carbon;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Repositories\ReportRepository;
use Illuminate\Support\Facades\Storage;
use Modules\Payroll\Models\PayrollStaff;
use Illuminate\Contracts\Support\Renderable;
use Modules\Payroll\Models\PayrollEmployment;
use Modules\WorkAttendance\Models\WorkAttendance;
use Modules\WorkAttendance\Models\WorkAttendanceSchedule;
use Modules\ProjectTracking\Models\ProjectTrackingWorkDay;
use Modules\WorkAttendance\Models\WorkAttendanceCustomSchedule;
use Modules\WorkAttendance\Services\WorkAttendanceService;

/**
 * @class WorkAttendanceHistoryController
 * @brief Gestiona el histórico de asistencia del personal
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WorkAttendanceHistoryController extends Controller
{
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

    protected $formatDateTime = 'Y-m-d H:i:s';

    protected $saveGraphPattern = '#^data:image/\w+;base64,#i';

    public function __construct()
    {
        $this->middleware('permission:workattendance.history.index');
    }

    /**
     * Muestra el histórico de asistencia del personal
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    \Illuminate\View\View
     */
    public function index()
    {
        return view('workattendance::reports.history.general');
    }

    /**
     * Reporte individual de asistencia
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\View\View
     */
    public function individualIndex(Request $request)
    {
        return view('workattendance::reports.history.individual');
    }

    public function byDepartmentIndex(Request $request)
    {
        return view('workattendance::reports.history.by-department');
    }

    /**
     * Obtiene el listado de asistencias del personal
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getList(Request $request)
    {
        $workAttendance = WorkAttendance::with([
            'payrollStaff' => function ($query) {
                $query->without($this->withoutStaffRelations)->with([
                    'payrollEmployment' => function ($query) {
                        $query->without($this->withoutEmploymentRelations)->select('id', 'payroll_staff_id');
                    }
                ])->select('id', 'first_name', 'last_name');
            },
        ])->select(
            'id',
            'date_at',
            'entry_time',
            'exit_time',
            'payroll_staff_id'
        )->orderBy('date_at', 'desc')->orderBy('entry_time', 'desc');
        if ($request->position_id || $request->payroll_employment_id || $request->from_date || $request->to_date) {
            if ($request->position_id) {
                $workAttendance->filterByPosition($request->position_id);
            }
            if ($request->payroll_employment_id) {
                $workAttendance->filterByEmployment($request->payroll_employment_id);
            }
            if ($request->from_date && $request->to_date) {
                $workAttendance->whereBetween('date_at', [$request->from_date, $request->to_date]);
            } elseif ($request->from_date) {
                $workAttendance->where('date_at', '>=', $request->from_date);
            } elseif ($request->to_date) {
                $workAttendance->where('date_at', '<=', $request->to_date);
            }
        }
        $workAttendance = $workAttendance->paginate((int) $request->limit);

        return response()->json([
            'data' => $workAttendance->items(),
            'count' => $workAttendance->total(),
            'last_page' => $workAttendance->lastPage(),
            'tableRef' => 'tableResults',
        ], 200);
    }

    /**
     * Obtiene los datos de asistencia del personal
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getAttendanceData(Request $request)
    {
        return response()->json([
            'records' => $this->search($request)
        ], 200);
    }

    public function search(Request $request)
    {
        $workAttendance = WorkAttendance::select(
            'id',
            'date_at',
            'entry_time',
            'exit_time',
            'payroll_staff_id'
        )->orderBy('date_at', 'desc')->orderBy('entry_time', 'desc');
        if ($request->position_id) {
            $workAttendance->filterByPosition($request->position_id);
        }
        if ($request->payroll_employment_id) {
            $workAttendance->filterByEmployment($request->payroll_employment_id);
            //Agrega información de calendario personalizado para verificar el cumplimiento del mismo
            //(new WorkAttendanceService())->getCustomSchedule(Carbon::now()->format('Y-m-d'), $request->payroll_employment_id);
        }
        if ($request->department_id) {
            $workAttendance->with([
                'payrollStaff' => function ($query) {
                    $query->without($this->withoutStaffRelations)->with([
                        'payrollEmployment' => function ($query) {
                            $query->without($this->withoutEmploymentRelations)->select('id', 'payroll_staff_id');
                        }
                    ])->select('id', 'first_name', 'last_name', 'id_number');
                },
            ])->filterByDepartment($request->department_id);
        }
        if ($request->from_date || $request->to_date) {
            if ($request->from_date && $request->to_date) {
                $workAttendance->whereBetween('date_at', [$request->from_date, $request->to_date]);
            } elseif ($request->from_date) {
                $workAttendance->where('date_at', '>=', $request->from_date);
            } elseif ($request->to_date) {
                $workAttendance->where('date_at', '<=', $request->to_date);
            }
        } else {
            $workAttendance->whereMonth(
                'date_at',
                Carbon::now()->format('m')
            )->whereYear(
                'date_at',
                Carbon::now()->format('Y')
            );
        }

        $workAttendance = $workAttendance->get()->map(function ($workAttendance) use ($request) {
            $workAttendance['work_time'] = 0;
            $workAttendance['work_percent'] = 0;
            $schedule = WorkAttendanceSchedule::where([
                'day_number' => Carbon::createFromFormat('Y-m-d', $workAttendance->date_at)->format('N'),
                'active' => true,
                'is_extended' => false
            ])->first();

            $workAttendance['work_time'] = (float)number_format(Carbon::parse(
                Carbon::parse(
                    $workAttendance->date_at . " " . Carbon::parse(
                        $workAttendance->exit_time ?? $workAttendance->entry_time ?? '00:00:00'
                    )->format('H:i:s')
                )->format($this->formatDateTime)
            )->diffInSeconds(Carbon::parse(
                Carbon::parse(
                    $workAttendance->date_at . " " . Carbon::parse(
                        $workAttendance->entry_time ?? $workAttendance->exit_time ?? '00:00:00'
                    )->format('H:i:s')
                )->format($this->formatDateTime)
            )) / 60, 2);

            $workAttendance['work_percent'] = (float)number_format(100, 2);

            if ($schedule) {
                $workAttendance['work_percent'] = (float)number_format(
                    ($workAttendance['work_time'] * 100) / $schedule->native_work_time,
                    2
                );
            }

            if ($request->department_id) {
                $employee = $workAttendance->payrollStaff->toArray();
                $workAttendance['employee'] = [
                    'id_number' => $employee['id_number'],
                    'full_name' => $employee['first_name'] . ' ' . $employee['last_name'],
                    'position' => $employee['payroll_employment']['payrollPosition']['name']
                ];
                unset($workAttendance->payrollStaff);
                unset($workAttendance->payroll_staff_id);
            }
            return $workAttendance;
        });

        return $workAttendance;
    }

    /**
     * Obtiene los dias y horas laborables
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function getWorkingDays($day = null)
    {
        $workingDays = ProjectTrackingWorkDay::select(
            'id',
            'from',
            'to',
            'working_days',
            'working_hours'
        )->get();

        if ($day) {
            $workingDays = $workingDays->filter(function ($workDay) use ($day) {
                return in_array(
                    $day,
                    array_column(
                        json_decode($workDay->working_days),
                        'day'
                    )
                );
            });
        }

        return $workingDays->map(function ($workDay) {
            $workDay->working_days = array_column(
                json_decode($workDay->working_days),
                'day'
            );
            return $workDay;
        });
    }

    public function printIndividual(Request $request)
    {
        $workAttendance = $this->search($request);
        $employment = PayrollEmployment::without([
            'payrollCoordination',
            'payrollInactivityType',
            'payrollPositionType',
            'payrollPositions',
            'payrollPreviousJob',
            'payrollStaffType'
        ])->with(['payrollStaff' => function ($query) {
            $query->without($this->withoutStaffRelations)->select('id', 'first_name', 'last_name', 'id_number');
        }])->find($request->payroll_employment_id);

        $date = Carbon::parse($workAttendance[0]->date_at);
        $periodConsulted = months_dictionary()[$date->format('F')] . ' ' . $date->format('Y');
        if ($request->from_date && $request->to_date) {
            $fromDate = Carbon::parse($request->from_date);
            $toDate = Carbon::parse($request->to_date);
            $periodConsulted = $fromDate->format('d-m-Y') . ' al ' . $toDate->format('d-m-Y');
        } elseif ($request->from_date && !$request->to_date) {
            $periodConsulted = 'Desde el ' . Carbon::parse($request->from_date)->format('d-m-Y');
        } elseif (!$request->from_date && $request->to_date) {
            $periodConsulted = 'Hasta el ' . Carbon::parse($request->to_date)->format('d-m-Y');
        }

        $weekDaysName = [
            'Monday' => 'Lunes',
            'Tuesday' => 'Martes',
            'Wednesday' => 'Miercoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sabado',
            'Sunday' => 'Domingo'
        ];

        $pdf = new ReportRepository();
        $filename = 'work-attendance-' . $request->payroll_employment_id . '.pdf';
        $pdf->setConfig(
            [
                'institution' => get_institution(),
                'urlVerify'   => url(''),
                'orientation' => 'P',
                'filename'    => $filename
            ]
        );
        $pdf->setHeader("Asistencia de Personal", "Reporte Individual");
        $pdf->setFooter();
        $pdf->setBody(
            'workattendance::reports.history.print-individual',
            true,
            compact(
                'workAttendance',
                'employment',
                'periodConsulted',
                'weekDaysName'
            ),
            'I',
            [
                [
                    'fileName' => storage_path('app/public/workattendance_graph_' . $employment->id . '.png'),
                    'width' => 190,
                    'height' => 95
                ]
            ]
        );
        $file = storage_path() . '/reports/' . $filename;
        return response()->download($file, $filename, [], 'inline');
    }

    public function printByDepartment(Request $request)
    {
        $workAttendance = $this->search($request);
        $date = Carbon::parse($workAttendance[0]->date_at);
        $periodConsulted = months_dictionary()[$date->format('F')] . ' ' . $date->format('Y');
        $department = Department::select('name')->find($request->department_id);

        if ($request->from_date && $request->to_date) {
            $fromDate = Carbon::parse($request->from_date);
            $toDate = Carbon::parse($request->to_date);
            $periodConsulted = $fromDate->format('d-m-Y') . ' al ' . $toDate->format('d-m-Y');
        } elseif ($request->from_date && !$request->to_date) {
            $periodConsulted = 'Desde el ' . Carbon::parse($request->from_date)->format('d-m-Y');
        } elseif (!$request->from_date && $request->to_date) {
            $periodConsulted = 'Hasta el ' . Carbon::parse($request->to_date)->format('d-m-Y');
        }

        $weekDaysName = [
            'Monday' => 'Lunes',
            'Tuesday' => 'Martes',
            'Wednesday' => 'Miercoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sabado',
            'Sunday' => 'Domingo'
        ];

        $pdf = new ReportRepository();
        $filename = 'work-attendance-' . $request->department_id . '.pdf';
        $pdf->setConfig(
            [
                'institution' => get_institution(),
                'urlVerify'   => url(''),
                'orientation' => 'P',
                'filename'    => $filename
            ]
        );
        $pdf->setHeader(
            $department->name,
            "Asistencia General de Personal Adscrito"
        );
        $pdf->setFooter();
        $pdf->setBody(
            'workattendance::reports.history.print-by-department',
            true,
            compact(
                'workAttendance',
                'department',
                'periodConsulted',
                'weekDaysName'
            ),
            'I',
            [
                [
                    'fileName' => storage_path(
                        'app/public/workattendance_graph_department_' . $request->department_id . '.png'
                    ),
                    'width' => 190,
                    'height' => 95
                ]
            ]
        );
        $file = storage_path() . '/reports/' . $filename;
        return response()->download($file, $filename, [], 'inline');
    }

    public function saveGraph(Request $request)
    {
        $image = base64_decode(preg_replace($this->saveGraphPattern, '', $request->image));
        $fileName = 'workattendance_graph_' . $request->employee_id . '.png';
        Storage::disk('public')->put($fileName, $image);

        return response()->json(['fileName' => $fileName]);
    }

    public function saveGraphByDepartment(Request $request)
    {
        $image = base64_decode(preg_replace($this->saveGraphPattern, '', $request->image));
        $fileName = 'workattendance_graph_department_' . $request->department_id . '.png';
        Storage::disk('public')->put($fileName, $image);

        return response()->json(['fileName' => $fileName]);
    }
}
