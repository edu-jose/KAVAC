<?php

namespace Modules\WorkAttendance\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Support\Renderable;
use Modules\WorkAttendance\Models\WorkAttendance;
use Modules\WorkAttendance\Models\WorkAttendanceSettingNotification;

/**
 * @class WorkAttendanceSettingNotificationController
 * @brief Controlador dedicado a la configuración de notificaciones
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WorkAttendanceSettingNotificationController extends Controller
{
    protected $rules;
    protected $ruleMessages;

    public function __construct()
    {
        $this->rules = [
            'position_id' => ['required', 'exists:payroll_positions,id'],
            'payroll_employment_id' => ['required', 'exists:payroll_employments,id'],
            'periodicity' => ['required', 'in:S'], //in:D,S,Q,M,B,T,A
        ];
        $this->ruleMessages = [
            'position_id.required' => 'El cargo es obligatorio.',
            'position_id.exists' => 'El cargo no existe.',
            'payroll_employment_id.required' => 'La persona es obligatoria.',
            'payroll_employment_id.exists' => 'La persona no existe.',
            'periodicity.required' => 'La periodicidad de la notificación es obligatoria.',
            'periodicity.in' => 'El periodo debe ser una de las siguientes opciones: ' .
                                'diario, semanal, quincenal, mensual, bimestral, trimestral, anual.'
        ];
    }

    /**
     * Obtiene los registros de notificaciones
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        return $this->notifyList($request);
    }

    /**
     * Registra una nueva notificación
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->rules, $this->ruleMessages);

        $notify = DB::transaction(function () use ($request) {
            return WorkAttendanceSettingNotification::create($request->all());
        });

        return response()->json(['record' => $notify, 'message' => 'Success'], 200);
    }

    /**
     * Actualiza información de una notificación
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, WorkAttendanceSettingNotification $setting)
    {
        $this->validate($request, $this->rules, $this->ruleMessages);

        DB::transaction(function () use ($request, $setting) {
            $setting->update($request->all());
        });

        return response()->json(['record' => $setting, 'message' => 'Success'], 200);
    }

    /**
     * Elimina una notificación
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy(WorkAttendanceSettingNotification $setting)
    {
        $setting->delete();
        return response()->json(['message' => 'Success'], 200);
    }

    public function notifyList(Request $request)
    {
        $notifications = WorkAttendanceSettingNotification::with([
            'payrollEmployment' => function ($query) {
                $query->without(
                    'department',
                    'payrollContractType',
                    'payrollCoordination',
                    'payrollInactivityType',
                    'payrollPositionType',
                    'payrollPositions',
                    'payrollPreviousJob',
                    'payrollStaffType'
                )->with(['payrollStaff' => function ($query) {
                    $query->without([
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
                    ])->select('id', 'id_number', 'first_name', 'last_name', 'email');
                }])->select(
                    'id',
                    'payroll_staff_id',
                    'institution_email'
                );
            },
        ])->select('id', 'notify', 'periodicity', 'position_id', 'payroll_employment_id')->get();
        return response()->json(['records' => $notifications], 200);
    }
}
