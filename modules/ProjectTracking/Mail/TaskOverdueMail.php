<?php

namespace Modules\ProjectTracking\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\ProjectTracking\Models\ProjectTrackingTask;

/**
 * @class TaskOverdueMail
 * @brief Correo electronico para tarea atrasada
 *
 * Correo electronico para tarea atrasada
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class TaskOverdueMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $task;
    public $delayDays;
    public $fromEmail;

    /**
     * Crea una nueva instancia del mensaje.
     *
     * @return void
     */
    public function __construct(ProjectTrackingTask $task, $delayDays)
    {
        $this->task = $task;
        $this->delayDays = $delayDays;
        $this->fromEmail = config('mail.from.address');
    }

    /**
     * Construye el mensaje.
     *
     * @return View     Retorna la vista del correo
     */
    public function build()
    {
        return $this->from($this->fromEmail)->subject("(Urgente) Tarea Atrasada: {$this->task->name}")
        ->markdown("projecttracking::emails.task-overdue")
            ->with([
                'task' => $this->task,
                'delayDays' => $this->delayDays,
                'fromEmail' => $this->fromEmail
            ]);
    }
}
