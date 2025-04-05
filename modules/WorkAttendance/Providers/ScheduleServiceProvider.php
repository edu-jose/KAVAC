<?php

namespace Modules\WorkAttendance\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Modules\WorkAttendance\Models\WorkAttendanceSettingNotification;

/**
 * @class ScheduleServiceProvider
 * @brief Proveedor de servicios para la ejecución de tareas programadas del módulo
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ScheduleServiceProvider extends ServiceProvider
{
    /**
     * Registra el proveedor de servicios.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Obtiene los servicios proporcionados por el proveedor.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    public function boot()
    {
        $this->app->booted(function () {
            $schedule = app(Schedule::class);

            // Define la tarea programada directamente aquí
            $schedule->call(function () {
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

                if ($notifications->isNotEmpty()) {
                    $emails = [];
                    foreach ($notifications as $notification) {
                        if ($notification->payrollEmployment->institution_email) {
                            $emails[] = $notification->payrollEmployment->institution_email;
                        } elseif ($notification->payrollEmployment->payrollStaff->email) {
                            $emails[] = $notification->payrollEmployment->payrollStaff->email;
                        }
                    }
                }
                // Lógica de la tarea programada
                // Envío de correo electrónico a las cuentas indicadas con el reporte de asistencia
                // según las configuraciones establecidas
            })->weeklyOn(6, '08:00')->timezone(config('app.timezone'))->runInBackground();
        });
    }
}
