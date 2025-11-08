<?php

namespace Modules\Payroll\Imports;

use Carbon\Carbon;
use App\Notifications\System;
use Illuminate\Support\Facades\Log;
use App\Mail\FailImportNotification;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToModel;
use Modules\Payroll\Models\PayrollStaff;
use App\Notifications\SystemNotification;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Modules\Payroll\Models\PayrollAriRegister;
use Modules\Payroll\Exports\FailRegisterImportExport;

/**
 * @class AriRegisterImport
 * @brief Importa un archivo de registros ARI
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AriRegisterImport implements
    ToModel,
    WithValidation,
    WithHeadingRow,
    SkipsEmptyRows,
    SkipsOnError,
    SkipsOnFailure,
    WithEvents
{
    use Importable;
    use SkipsErrors;
    use SkipsFailures;

    protected array $attributes;

    public function __construct(
        protected string $errorsFilePath,
        protected object $user,
    ) {
        $this->attributes = [
            'cedula'         =>  'Numero de Cédula del Trabajador',
            'porcentaje'     =>  'Porcentaje de Aumento',
            'desde'          =>  'Fecha Comienzo del Aumento',
            'hasta'          =>  'Fecha Fin del Aumento',
            'unique_from_date' => 'Fecha de Comienzo Única',
            'overlap_period'   => 'Periodo de Aumento Solapado',
            'invalid_date_range' => 'Fecha Fin del Aumento'
        ];
    }

    /**
     * Modelo para importar datos
     *
     * @param array $row Arreglo de columnas a importar
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $payrollStaff = PayrollStaff::query()
            ->where('id_number', $row['cedula'])
            ->toBase()
            ->first();

        if (!$payrollStaff) {
            return null;
        }

        $from_date = isset($row['desde'])
            ? (is_string($row['desde']) ? Carbon::parse($row['desde']) : Carbon::instance(
                \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['desde'])
            ))
            : null;

        $to_date = isset($row['hasta'])
            ? (is_string($row['hasta']) ? Carbon::parse($row['hasta']) : Carbon::instance(
                \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['hasta'])
            ))
            : null;

        $id = null;

        if (!empty($row['codigo']) && preg_match('/-(\d+)$/', $row['codigo'], $matches)) {
            $id = $matches[1];
        }

        $payrollAriRegister = $id ? PayrollAriRegister::find($id) : null;

        if ($payrollAriRegister) {
            $payrollAriRegister->from_date = $from_date;
            $payrollAriRegister->to_date = $to_date;
            $payrollAriRegister->percetage = $row['porcentaje'] / 100;
            $payrollAriRegister->save();
            return null;
        }

        $lastRegister = PayrollAriRegister::query()
            ->where('payroll_staff_id', $payrollStaff->id)
            ->orderBy('from_date', 'desc')
            ->first();

        if ($lastRegister && $lastRegister->to_date === null && $from_date) {
            $lastRegister->to_date = $from_date->copy()->subDay()->format('Y-m-d');
            $lastRegister->save();
        }

        return new PayrollAriRegister([
            'payroll_staff_id' => $payrollStaff->id,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'percetage' => $row['porcentaje'] / 100,
        ]);
    }

    /**
     * Preparar los datos para ser importados (validaciones)
     *
     * @param array $data Arreglo con los datos
     * @param integer $index Indice de la fila
     *
     * @return array
     */
    public function prepareForValidation($data, $index)
    {
        $payrollStaff = PayrollStaff::query()
            ->where('id_number', $data['cedula'])
            ->toBase()
            ->first();

        $from_date = isset($data['desde'])
            ? (is_string($data['desde']) ? Carbon::parse($data['desde']) : Carbon::instance(
                \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($data['desde'])
            ))
            : null;

        $to_date = isset($data['hasta']) && $data['hasta'] !== ''
            ? (is_string($data['hasta']) ? Carbon::parse($data['hasta']) : Carbon::instance(
                \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($data['hasta'])
            ))
            : null;

        $id = null;
        if (!empty($data['codigo']) && preg_match('/ARI-(\d+)/', $data['codigo'], $matches)) {
            $id = $matches[1];
        }

        $data['unique_from_date'] = false;
        $data['overlap_period'] = false;
        $data['invalid_date_range'] = false;

        if ($payrollStaff && $from_date) {
            $allRegisters = PayrollAriRegister::query()
                ->where('payroll_staff_id', $payrollStaff->id)
                ->when($id, fn($q) => $q->where('id', '!=', $id))
                ->orderBy('from_date')
                ->get();

            foreach ($allRegisters as $reg) {
                $regFrom = Carbon::parse($reg->from_date);
                $regTo = $reg->to_date ? Carbon::parse($reg->to_date) : null;

                if ($from_date->lessThanOrEqualTo($regFrom) && !$id) {
                    $data['unique_from_date'] = true;
                    break;
                }

                if ($regTo && $from_date->between($regFrom, $regTo)) {
                    $data['overlap_period'] = true;
                    break;
                }

                if (!$regTo && $from_date->lessThanOrEqualTo($regFrom) && $allRegisters->count() > 1) {
                    $data['overlap_period'] = true;
                    break;
                }
            }
        }

        if ($from_date && $to_date && $to_date->lt($from_date)) {
            $data['invalid_date_range'] = true;
        }

        $data['desde'] = $from_date?->format('Y-m-d');
        $data['hasta'] = $to_date?->format('Y-m-d');

        return $data;
    }

    /**
     * Reglas de validación
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'cedula' => ['required'],
            'porcentaje' => ['required'],
            'desde' => ['required'],

            'unique_from_date' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure('La fecha de inicio debe ser posterior a la fecha de inicio anterior.');
                }
            },
            'overlap_period' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure('La fecha de inicio se encuentra dentro del periodo de un registro anterior.');
                }
            },
            'invalid_date_range' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure('La fecha de fin no puede ser anterior a la fecha de inicio.');
                }
            },
        ];
    }

    /**
     * Mensajes personalizados de validación
     *
     * @return array
     */
    public function customValidationMessages(): array
    {
        return [
            'cedula.required'               => 'El número de cédula es obligatorio.',
            'porcentaje.required'           => 'El porcentaje es obligatoria.',
            'desde.required'                => 'La fecha de comienzo del aumento es obligatoria.',
        ];
    }

    public function onFailure(Failure ...$failures): void
    {
        $failures = collect($failures);
        foreach ($failures as $failure) {
            $validationErrors = [
                'row' => $failure->row(),
                'attribute' => str_replace('_value', '', $this->attributes[$failure->attribute()]),
                'error' => $failure->errors()[0],
                'sheetName' => 'Registros ARI',
            ];
            $jsonErrors = json_encode($validationErrors);

            \Illuminate\Support\Facades\Storage::disk('temporary')->append($this->errorsFilePath, $jsonErrors);
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                $email = $this->user->email;
                $errorsFile = Storage::disk('temporary')->get($this->errorsFilePath);
                $lines = explode("\n", $errorsFile);
                $errors = [];

                foreach ($lines as $line) {
                    if (!empty($line)) {
                        array_push($errors, json_decode($line, true));
                    }
                }

                if (count($errors) > 0) {
                    $importNotificationMessage = 'Alguno de los registros que trataste de importar fallaron. Se ha enviado a su correo electrónico un archivo con la información.';
                    $sendEmailMessage = '';
                    $errorExcelFiles = [
                        [
                            'file' => Excel::raw(
                                new FailRegisterImportExport($errors),
                                \Maatwebsite\Excel\Excel::XLSX
                            ),
                            'fileName' => 'Errores_de_importacion_registro_ari.xlsx',
                        ]
                    ];

                    if ($email) {
                        try {
                            Mail::to($email)->send(new FailImportNotification($errorExcelFiles));
                        } catch (\Exception $e) {
                            Log::info($e);
                            $sendEmailMessage = 'No se pudo enviar el correo de importación. ';
                        }
                    }
                    $this->user->notify(
                        new SystemNotification(
                            'Fallos de Importacion de registros - Registro ARI',
                            $importNotificationMessage . ' ' . $sendEmailMessage
                        )
                    );
                } else {
                    $this->user->notify(new SystemNotification('Éxito - Registro ARI', 'Ha finalizado la importación de los datos personales, el archivo ha sido cargado con éxito. Se ha enviado a correo electrónico la notificación'));
                    $this->user->notify(
                        new System(
                            'Éxito - Registro ARI',
                            'Talento Humano',
                            'Ha finalizado la importación de los datos personales, el archivo ha sido cargado con éxito.',
                            true,
                        )
                    );
                }
                Storage::disk('temporary')->delete($this->errorsFilePath);
            },
        ];
    }
}
