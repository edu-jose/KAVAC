<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use App\Notifications\System as AppNotification;

class NotificationTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notification-test
                            {--e|email= : The email address to send the test notification}
                            {--d|dispatch : Indicates whether the process is performed through a work queue}
                            {--u|username= : The username to send the test notification}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test sending notifications';

    protected $data = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->data['title'] = 'Notificación de prueba';
        $this->data['description'] = 'A través de este correo se esta probando las notificaciones del sistema KAVAC';
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $email = $this->option('email') ?? null;
        $dispatch = $this->option('dispatch');
        $username = $this->option('username');

        $optionEmail = $email ? '--e ' . $email : '';
        $optionDispatch = $dispatch ? '--d' : '';
        $optionUsername = $username ? '--u ' . $username : '';
        Log::info(
            'Inicia proceso de ejecución de prueba para el envío de correo con el siguiente comando: ' .
            '"php artisan app:send-email-test ' . $optionEmail . ' ' . $optionDispatch . ' ' . $optionUsername . '".'
        );
        try {
            if ($email) {
                $this->data['to'] = $email;
                if ($dispatch) {
                    Log::info('Agregada la tarea de envío de correo a la cola de trabajo con Queue::push.');
                    Queue::push(function () {
                        $this->data['description'] .= " mediante el uso de colas de trabajo";
                        $this->sendEmail();
                    });
                } else {
                    Log::info('Envío de correo a través del uso directo de envío con Mail::raw.');
                    $this->data['description'] .= " mediante el uso directo de envío de correo";
                    $this->sendEmail();
                }
            }
            if ($username) {
                Log::info('Envío de correo a través del uso del gestor de notificaciones a usuarios con User::notify.');
                $this->data['description'] .= " mediante el uso del gestor de notificaciones a usuarios";
                User::where('username', $username)->first()->notify(
                    new AppNotification(
                        $this->data['title'],
                        '',
                        $this->data['description'],
                        true
                    )
                );
            }
            Log::info('Finalizo proceso de ejecución de prueba para el envío de correo.');
        } catch (\Exception $e) {
            Log::error('Ha ocurrido un error en el proceso de ejecución de prueba para el envío de la notificación.');
            Log::error($e->getMessage());
        }

        return 0;
    }

    private function sendEmail()
    {
        Mail::raw(
            $this->data['description'],
            function ($msg) {
                $msg->to($this->data['to'])->subject($this->data['title']);
            }
        );
    }
}
