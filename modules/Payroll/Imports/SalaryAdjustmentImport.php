<?php

namespace Modules\Payroll\Imports;

use Carbon\Carbon;
use Maatwebsite\Excel\Row;
use App\Notifications\System;
use Illuminate\Support\Facades\Log;
use App\Mail\FailImportNotification;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Notifications\SystemNotification;
use Maatwebsite\Excel\Concerns\OnEachRow;
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
use Modules\Payroll\Models\PayrollSalaryTabulator;
use Modules\Payroll\Models\PayrollSalaryAdjustment;
use Modules\Payroll\Exports\FailRegisterImportExport;

/**
 * @class SalaryAdjustmentImport
 * @brief Importa un archivo de ajuste salarial del personal
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 * @author Fabián Palmera <fapalmera@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SalaryAdjustmentImport implements
    OnEachRow,
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
            'fecha_de_generacion'           =>  'Fecha de Generación de Ajuste',
            'fecha_de_aumento'              =>  'Fecha Inicio del Aumento',
            'fecha_fin_de_aumento'          =>  'Fecha Fin del Aumento',
            'valor'                         =>  'Valor',
            'tabulador_salarial'            =>  'Tabulador Salarial',
        ];
    }

    /**
     * Metodo que se encarga de importar el archivo
     *
     * @param array $row Fila de datos a importar
     *
     * @return void
     */
    public function onRow(Row $row)
    {
        $payrollSalaryTabulator =
            PayrollSalaryTabulator::query()->where('name', $row['tabulador_salarial'])->toBase()->first();

        $created_date =
            isset($row['fecha_de_generacion']) ?
                (is_string($row['fecha_de_generacion']) ?
                    $row['fecha_de_generacion'] :
                    Carbon::instance(
                        \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['fecha_de_generacion'])
                    )) : null;

        $start_increase_date =
            isset($row['fecha_de_aumento']) ?
                (is_string($row['fecha_de_aumento']) ?
                    $row['fecha_de_aumento'] :
                    Carbon::instance(
                        \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['fecha_de_aumento'])
                    )) : null;

        $end_increase_date =
            isset($row['fecha_fin_de_aumento']) ?
                (is_string($row['fecha_fin_de_aumento']) ?
                    $row['fecha_fin_de_aumento'] :
                    Carbon::instance(
                        \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['fecha_fin_de_aumento'])
                    )) : null;

        $type_array = ['different', 'absolute_value', 'percentage'];

        if ($row['tipo_de_aumento'] == 'Diferente') {
            $type_name = $type_array[0];
        } elseif ($row['tipo_de_aumento'] == 'Porcentual') {
            $type_name = $type_array[2];
        } else {
            $type_name = $type_array[1];
        }

        if ($payrollSalaryTabulator) {
                $payrollSalaryAdjustment = PayrollSalaryAdjustment::firstOrCreate([
                    'increase_of_type'                   => $type_name,
                    'value'                              => $row['valor'],
                    'payroll_salary_tabulator_id'        => $payrollSalaryTabulator->id,
                    'start_increase_date'                => $start_increase_date,
                    'end_increase_date'                  => $end_increase_date,
                ]);

                $payrollSalaryAdjustment->created_at = $created_date;
                $payrollSalaryAdjustment->save();
        }
    }

    /**
     * Reglas de validación
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            "fecha_de_generacion" => ['date', 'required'],
            "fecha_de_aumento" => ['date', 'required'],
            "fecha_fin_de_aumento" => ['date', 'nullable'],
            "valor" => ['required'],
            "tabulador_salarial" => ['required'],
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
            'valor.required'                => 'El valor de aumento es obligatorio.',
            'fecha_de_generacion.required'  => 'La fecha de generación es obligatoria.',
            'fecha_de_generacion.date'      => 'La fecha de generación debe ser una fecha.',
            'fecha_de_aumento.required'     => 'La fecha de aumento es obligatoria.',
            'fecha_de_aumento.date'         => 'La fecha de aumento debe ser una fecha.',
            'fecha_fin_de_aumento.date'     => 'La fecha fin de aumento debe ser una fecha.',
            'tabulador_salarial.required'   => 'El tabulador salarial es obligatorio.',
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
                'sheetName' => 'Registros de Ajuste en Salario',
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
                    $importNotificationMessage = 'Alguno de los registros que trataste de importar fallaron. . Se ha enviado a su correo electrónico un archivo con la información.';
                    $sendEmailMessage = '';
                    $errorExcelFiles = [
                        [
                            'file' => Excel::raw(
                                new FailRegisterImportExport($errors),
                                \Maatwebsite\Excel\Excel::XLSX
                            ),
                            'fileName' => 'Errores_de_importacion_ajuste_salarial.xlsx',
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
                            'Fallos de Importacion de registros - Datos de Ajustes en Tabla Salarial',
                            $importNotificationMessage . ' ' . $sendEmailMessage
                        )
                    );
                } else {
                    $this->user->notify(new SystemNotification('Éxito - Datos de Ajustes en Tabla Salarial', 'Ha finalizado la importación de los datos personales, el archivo ha sido cargado con éxito. Se ha enviado a correo electrónico la notificación'));
                    $this->user->notify(
                        new System(
                            'Éxito - Datos de Ajustes en Tabla Salarial',
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
