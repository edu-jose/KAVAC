<?php

namespace Modules\Payroll\Imports\Staff;

use App\Models\User;
use Illuminate\Support\Str;
use App\Notifications\System;
use Illuminate\Support\Facades\Log;
use App\Mail\FailImportNotification;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Modules\Payroll\Imports\StaffImport;
use App\Notifications\SystemNotification;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\ImportFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Modules\Payroll\Imports\SocioStaffImport;
use Modules\Payroll\Imports\StaffAccountImport;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Modules\Payroll\Imports\FinancialStaffImport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Modules\Payroll\Imports\EmploymentStaffImport;
use Modules\Payroll\Imports\ProfessionalStaffImport;
use Modules\Payroll\Exports\FailRegisterImportExport;

/**
 * @class RegisterStaffImport
 * @brief Importa un archivo de expediente de personal
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class RegisterStaffImport implements
    WithMultipleSheets,
    WithChunkReading,
    ShouldQueue,
    WithEvents
{
    use Importable;
    use SkipsFailures;

    /**
     * Método constructor de la clase
     *
     * @param string  $filePath Ruta del archivo a exportar
     * @param string  $disk Sistema de archivos a implementar
     * @param integer $userId Id del usuario
     * @param string $fileErrosPath Ruta del archivo de errores
     * @param array $sheets Arreglo de hojas a importar
     *
     * @throws \Exception
     *
     * @return void
     */
    public function __construct(
        protected string $filePath,
        protected string $disk,
        protected int $userId,
        protected string $fileErrosPath,
        protected array $sheets = [],
        protected string $titleSheet = ''
    ) {
        $spreadsheet = IOFactory::load(storage_path($disk . '/' . $filePath));

        foreach ($spreadsheet->getSheetNames() ?? [] as $sheetName) {
            $match = match ($sheetName) {
                'Datos Personales' => new StaffImport($fileErrosPath),
                'Datos Profesionales' => new ProfessionalStaffImport($fileErrosPath),
                'Datos Socioeconomicos' => new SocioStaffImport($fileErrosPath),
                'Datos Laborales' => new EmploymentStaffImport($fileErrosPath),
                'Datos Financieros' => new FinancialStaffImport($fileErrosPath),
                'Datos Contables' => new StaffAccountImport($fileErrosPath),
                default => null
            };
            if ($match) {
                $this->sheets[$sheetName] = $match;
                $this->titleSheet = $sheetName;
            }
        }
        if (empty($this->sheets)) {
            throw new \Exception('No existen hojas de importación validas');
        }
    }

    /**
     * Tamaño de los trozos de datos a importar
     *
     * @return integer
     */
    public function chunkSize(): int
    {
        return 200;
    }

    /**
     * Hojas a importar
     *
     * @return array
     */
    public function sheets(): array
    {
        return $this->sheets;
    }

    /**
     * Registro de eventos de importación
     *
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            ImportFailed::class => function (ImportFailed $event) {
                $user = User::without(['roles', 'permissions'])->where('id', $this->userId)->first();
                $exception = $event->getException();
                Storage::disk('temporary')->delete($this->filePath);

                if ($exception instanceof QueryException) {
                    if ($user->hasRole('admin')) {
                        $user->notify(new SystemNotification('Error - ' . $this->titleSheet, 'Importación fallida. No fue posible registrar la información en la base de datos. Por favor, consulte los logs del sistema para más detalles.'));
                        $user->notify(new System('Error - ' . $this->titleSheet, 'Talento Humano', 'Importación fallida. No fue posible registrar la información en la base de datos. Por favor, consulte los logs del sistema para más detalles.', true));
                    } else {
                        $user->notify(new SystemNotification('Error - ' . $this->titleSheet, 'Importación fallida. No fue posible registrar la información suministrada. Para obtener más detalles, por favor, contacte al administrador.'));
                        $user->notify(new System('Error - ' . $this->titleSheet, 'Talento Humano', 'Importación fallida. No fue posible registrar la información suministrada. Para obtener más detalles, por favor, contacte al administrador.', true));
                    }
                } else {
                    Log::error($event->getException());
                    $user->notify(new SystemNotification('Error - ' . $this->titleSheet, 'Importación fallida. No fue posible registrar la información suministrada. Para obtener más detalles, por favor, contacte al administrador.'));
                    $user->notify(new System('Error - ' . $this->titleSheet, 'Talento Humano', 'Importación fallida. No fue posible registrar la información suministrada. Para obtener más detalles, por favor, contacte al administrador.', true));
                }
            },
            AfterImport::class => function (AfterImport $event) {
                Storage::disk('temporary')->delete($this->filePath);
                $fileErros = Storage::disk('temporary')->get($this->fileErrosPath);
                $lines = explode("\n", $fileErros);
                $errors = [];
                foreach ($lines as $line) {
                    if (!empty($line)) {
                        array_push($errors, json_decode($line, true));
                    }
                }
                $user = User::without(['roles', 'permissions'])->where('id', $this->userId)->first();

                if (count($errors) > 0) {
                    $importNotificationMessage = 'Alguno de los registros que trataste de importar fallaron. Se ha enviado a su correo electrónico un archivo con la información.';
                    $sendEmailMessage = '';

                    $errorExcelFiles = [
                        [
                            'file' => Excel::raw(new FailRegisterImportExport($errors), \Maatwebsite\Excel\Excel::XLSX),
                            'fileName' => 'Errores_de_importacion.xlsx',
                        ]
                    ];

                    if ($user->email) {
                        try {
                            Mail::to($user->email)->send(new FailImportNotification($errorExcelFiles));
                        } catch (\Exception $e) {
                            Log::info($e);
                            $sendEmailMessage = 'No se pudo enviar el correo de importación. ';
                        }
                    }
                    $user->notify(new SystemNotification('Fallos de Importacion de registros - ' . $this->titleSheet, $importNotificationMessage . ' ' . $sendEmailMessage));
                } else {
                    $user->notify(new SystemNotification('Éxito - ' . $this->titleSheet, 'Ha finalizado la importación de los datos personales, el archivo ha sido cargado con éxito. Se ha enviado a su correo electrónico la notificación'));
                    $user->notify(
                        new System(
                            'Éxito - ' . $this->titleSheet,
                            'Talento Humano',
                            'Ha finalizado la importación de los datos personales, el archivo ha sido cargado con éxito.',
                            true,
                        )
                    );
                }
                Storage::disk('temporary')->delete($this->fileErrosPath);
            },
        ];
    }
}
