<?php

namespace Modules\ProjectTracking\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

/**
 * @class ProjectTrackingTaskScheduleServiceProvider
 * @brief Proveedor de servicios para la programación de tareas del módulo ProjectTracking
 *
 * Proveedor de servicios dedicado a gestionar la programación de comandos automatizados
 * para el módulo de seguimiento, incluyendo notificaciones de tareas atrasadas
 * y otras operaciones periódicas.
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingTaskScheduleServiceProvider extends ServiceProvider
{
    /**
     * Inicia los eventos programados del módulo en la aplicación.
     *
     * @return void
     */
    public function boot()
    {
        $this->commands([
            \Modules\ProjectTracking\Console\Commands\CheckOverdueTasks::class,
        ]);

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('project-tracking:tasks:check-overdue')
                ->dailyAt('08:00')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/task-scheduler.log'));
        });
    }
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
     * @return array    [descripción de los datos devueltos]
     */
    public function provides()
    {
        return [];
    }
}
