<?php

namespace Modules\WorkAttendance\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Modules\Payroll\Models\PayrollStaff;
use Modules\Payroll\Models\PayrollPosition;
use Illuminate\Contracts\Support\Renderable;
use Modules\WorkAttendance\Models\WorkAttendance;
use Modules\WorkAttendance\Services\WorkAttendanceService;

/**
 * @class WorkAttendanceController
 * @brief Gestiona el control de acceso del personal
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WorkAttendanceController extends Controller
{
    /**
     * Muestra la página de control de acceso
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    \Illuminate\View\View
     */
    public function index()
    {
        $monthsDict = [
            'January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo', 'April' => 'Abril',
            'May' => 'Mayo', 'June' => 'Junio', 'July' => 'Julio', 'August' => 'Agosto',
            'September' => 'Septiembre', 'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre'
        ];
        $currentMonth = date('n');
        $currentYear = date('Y');
        $monthDaysNumber = date('t', mktime(0, 0, 0, $currentMonth, 1, $currentYear));
        $weekDay = date('w', mktime(0, 0, 0, $currentMonth, 1, $currentYear));

        if ($weekDay == 0) {
            $weekDay = 7;
        }

        $weekDaysName = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
        $monthDays = [];
        for ($i = 1; $i <= $monthDaysNumber; $i++) {
            $monthDays[] = $i;
        }
        $rows = 5;
        return view(
            'workattendance::index',
            compact(
                'monthsDict',
                'currentMonth',
                'currentYear',
                'monthDaysNumber',
                'weekDay',
                'weekDaysName',
                'monthDays',
                'rows'
            )
        );
    }

    /**
     * Registra la asistencia del personal
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'identity_card' => 'required',
        ]);
        $staff = PayrollStaff::where('id_number', $request->identity_card)->first();
        $data = [];
        $now = now();
        if ($request->type_mark == 'entry') {
            $data['entry_time'] = $now->format('H:i:s');
        } elseif ($request->type_mark == 'exit') {
            $data['exit_time'] = $now->format('H:i:s');
        } else {
            $request->session()->flash('message', [
                'type' => 'error',
                'message' => 'No se ha indicado los datos requeridos para el registro'
            ]);
            return redirect()->back();
        }

        if (!empty($data)) {
            $workAttendance = (new WorkAttendanceService())->getWorkAttendance($now, $staff->id);

            if ($workAttendance) {
                $workAttendance->update($data);
            } else {
                $data['date_at'] = $now->format('Y-m-d');
                $data['payroll_staff_id'] = $staff->id;
                WorkAttendance::create($data);
            }
        }
        $request->session()->flash('success', 'Asistencia registrada a las ' . $now->format('h:i:s A'));
        return redirect()->route('workattendance.index');
    }

    /**
     * Busca un trabajador por su número de cédula de identidad
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     * @param string $id_card Número de cédula de identidad
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchIdCard(Request $request, $id_card)
    {
        $staff = PayrollStaff::where('id_number', $id_card)->first();
        $workAttendance = WorkAttendance::select('date_at', 'entry_time', 'exit_time')->whereMonth(
            'date_at',
            date('m')
        )->whereYear(
            'date_at',
            date('Y')
        )->where('payroll_staff_id', $staff->id)->get();
        return response()->json(['result' => true, 'staff' => $staff, 'workAttendance' => $workAttendance], 200);
    }

    public function getPositions(Request $request)
    {
        $records = PayrollPosition::select('id', 'name as text')->with(['payrollEmployments' => function ($query) {
            $query->select(
                'payroll_employments.active',
                'payroll_employments.payroll_staff_id',
                'payroll_employments.id',
                'institution_email',
                'department_id'
            )->without([
                'payrollPositions', 'payrollPositionType', 'payrollCoordination', 'department',
                'payrollStaffType', 'payrollInactivityType', 'payrollContractType', 'payrollPreviousJob'
            ])->with([
                'payrollStaff' => function ($query) {
                    $query->select('id', 'first_name', 'last_name', 'id_number', 'email')->without([
                        'payrollFinancial', 'payrollEmployment', 'payrollSocioeconomic', 'payrollProfessional',
                        'payrollNationality', 'payrollGender', 'payrollBloodType', 'payrollDisability',
                        'payrollLicenseDegree', 'payrollStaffUniformSize', 'payrollResponsibility'
                    ]);
                }
            ])->where([
                'payroll_employments.active' => true,
                'payroll_employment_payroll_position.active' => true
            ]);
        }])->orderBy('name', 'asc')->get();

        return response()->json([
            'records' => $records->map(function ($record) {
                if ($record->payrollEmployments->count() > 0) {
                    foreach ($record->payrollEmployments as $payrollEmployment) {
                        $payrollEmployment->setAppends([]);
                        unset($payrollEmployment->pivot);
                    }
                }
                return $record;
            })
        ], 200);
    }

    /**
     * Obtiene un listado de departamentos
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  Request  $request        Objeto con los datos de la petición
     * @param  integer  $institution_id Identificador de la organización
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDepartments(Request $request)
    {
        return response()->json(
            template_choices(Department::class, 'name', [], true)
        );
    }
}
