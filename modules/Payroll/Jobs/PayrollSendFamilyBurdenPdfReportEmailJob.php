<?php

namespace Modules\Payroll\Jobs;

use App\Models\User;
use App\Mail\SystemMail;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\SystemNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\MaxAttemptsExceededException;

/**
 * @class PayrollSendFamilyBurdenPdfReportEmailJob
 * @brief Trabajo que se encarga de enviar el reporte de la carga familiar
 *
 * @author Fabian Palmera <fpalmera@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSendFamilyBurdenPdfReportEmailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Tiempo de espera de ejecución del trabajo.
     *
     * @var integer $timeout
     */
    public $timeout = 0;

    /**
     * Crea una nueva instancia de trabajo.
     *
     * @param User $user Usuario al cual enviar el correo
     * @param string $pdfPath Ruta del archivo a exportar
     *
     * @return void
     */
    public function __construct(
        protected object $user,
        protected string $filepath,
        protected string $filename,
    ) {
        //
    }

    /**
     * Ejecuta el trabajo.
     *
     * @return void
     */
    public function handle()
    {
        $email = $this->user->email;
        $attachmentFiles = [];
        $path = '';
        $pdfPath = storage_path() . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR;
        $text_files = file($this->filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($text_files as $fileName) {
            $path = $pdfPath . $fileName;
            array_push($attachmentFiles, [
                'path' => $path,
                'name' => $fileName,
                'mime' => 'application/pdf'
            ]);
        }
        $mailable = new SystemMail(
            'Reporte de carga familiar',
            'Se ha adjuntado el reporte de carga familiar',
            config('mail.from.address'),
            $attachmentFiles
        );

        Mail::to($email)->send($mailable);

        $this->user->notify(new SystemNotification('Se ha generado el archivo de reporte de carga familiar', 'Se ha enviado el archivo de reporte de carga a su correo electronico.'));
    }
    /**
     * Maneja el fallo del trabajo.
     *
     * @param \Throwable $exception Excepción generada al ocurrir un error en el trabajo
     *
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        $text_files = file($this->filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $pdfPath = storage_path() . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR;
        // Eliminar archivos después de enviar el correo
        foreach ($text_files as $fileName) {
            $path = $pdfPath . $fileName;
            unlink($path);
        }
        unlink($this->filepath);
    }
}
