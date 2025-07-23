<?php

namespace Modules\ProjectTracking\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Modules\ProjectTracking\Models\ProjectTrackingTask;
use Modules\ProjectTracking\Mail\TaskOverdueMail;
use Carbon\Carbon;

/**
 * @class CheckOverdueTasks
 * @brief Comando para realizar el envio de correos por tareas atradas al responsable asignado
 *
 * Realiza el envio de correos por tareas atradas al responsable asignado
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CheckOverdueTasks extends Command
{
    /**
     * El nombre del comando.
     *
     * @var string
     */
    protected $name = 'project-tracking:tasks:check-overdue';

    /**
     * La descripción del comando.
     *
     * @var string
     */
    protected $description = 'Verifique tareas vencidas y envíe notificaciones.';

    /**
     * Crea una nueva instancia del comando.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Ejecuta la consola de comandos.
     *
     * @return mixed [descripción sobre los datos devueltos por el método]
     */
    public function handle()
    {
        $today = now();

        ProjectTrackingTask::with(['responsable.projectTrackingPersonalRegister', 'activityStatus'])
            ->where('end_date', '<', $today)
            ->whereHas('activityStatus', function ($query) {
                $query->where('name', '!=', 'Cerrada')
                      ->where('name', '!=', 'Pausada');
            })
            ->chunk(50, function ($tasks) use ($today) {
                foreach ($tasks as $task) {
                    try {
                        // Verificar si la tarea tiene responsable asociado
                        if ($task->responsable && $task->responsable->projectTrackingPersonalRegister) {
                            $email = $task->responsable->projectTrackingPersonalRegister->email;

                            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                $delay = Carbon::parse($task->end_date)->diffInDays($today);

                                Mail::to($email)->queue(new TaskOverdueMail($task, $delay));

                                $this->info("Correo enviado para tarea ID: {$task->id} a {$email}");
                            } else {
                                $this->warn("Email inválido para tarea ID: {$task->id}");
                            }
                        } else {
                            $this->warn("Tarea ID: {$task->id} no tiene responsable con email asociado");
                        }
                    } catch (\Exception $e) {
                        Log::error("Error enviando correo para tarea ID: {$task->id}: " . $e->getMessage());
                        $this->error("Error en tarea ID: {$task->id}");
                    }
                }
            });

        $this->info('Proceso de notificación de tareas atrasadas completado.');
    }
}
