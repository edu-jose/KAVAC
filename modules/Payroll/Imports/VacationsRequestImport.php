<?php

namespace Modules\Payroll\Imports;

use App\Models\CodeSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Mail\FailImportNotification;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToModel;
use Modules\Payroll\Models\PayrollStaff;
use Modules\Payroll\Rules\DaysRequested;
use App\Notifications\SystemNotification;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithEvents;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Modules\Payroll\Models\PayrollVacationRequest;
use Modules\Payroll\Rules\PayrollVacationStartDate;
use Modules\Payroll\Exports\FailRegisterImportExport;
use Modules\Payroll\Rules\PayrollVacationRequestDate;

/**
 * @class VacationsRequestImport
 * @brief Importa un archivo de solicitudes de vacaciones
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class VacationsRequestImport implements
    ToModel,
    WithValidation,
    SkipsEmptyRows,
    WithHeadingRow,
    WithChunkReading,
    SkipsOnFailure,
    ShouldQueue,
    WithEvents
{
    protected array $attributes;

    protected $daysRequested;

    public function __construct(
        protected string $errorsFilePath,
        protected object $currentFiscalYear,
        protected object $user,
        protected int $institutionId,
    ) {
        $this->attributes = [
            'code'                  =>  'Código',
            'payroll_staff_id'      =>  'Id del trabajador',
            'vacation_period_year'  =>  'Años del Periodo Vacacional',
            'days_requested'        =>  'Dias solicitados',
            'vacational_policy'     =>  'Política',
            'end_date'              =>  'Fecha de Culminación de Vacaciones',
            'start_date'            =>  'Fecha de Inicio de Vacaciones',
            'request_date'          =>  'Fecha de la Solicitud',
        ];
    }

    /**
     * Tamaño de los trozos de datos
     *
     * @return integer
     */
    public function chunkSize(): int
    {
        return 25;
    }

    /**
     * Fila del encabezado
     *
     * @return integer
     */
    public function headingRow(): int
    {
        return 1;
    }

    public function model(array $row): PayrollVacationRequest
    {
        return PayrollVacationRequest::create([
            'code'                 => $row["code"],
            'status'               => 'approved',
            'days_requested'       => $row['days_requested'],
            'vacation_period_year' => $this->setVacationPeriodYear($row['days_requested']),
            'start_date'           => $row['start_date'],
            'end_date'             => $row['end_date'],
            'payroll_staff_id'     => $row['payroll_staff_id'],
            'institution_id'       => $this->institutionId,
            'status_parameters'    => '"' . $this->getReinstatementDate($row['end_date']) . '"',
            'is_from_xlsx_file'    => true,
            'created_at'           => $row['request_date'],
        ]);
    }

    public function getReinstatementDate(string $endDate): string
    {
        $endDate = Carbon::parse($endDate);

        if ($endDate->dayOfWeek === Carbon::FRIDAY) {
            return $endDate->addDays(3)->format("Y-m-d");
        }
        return $endDate->addDays(1)->format("Y-m-d");
    }

    public function getDaysByAntiquity(int $yearId): int
    {
        $day = 0;

        if ($yearId >= $this->daysRequested->vacationPolicy["from_year"]) {
            if ($yearId % $this->daysRequested->vacationPolicy["years_for_additional_days"] == 0) {
                $day = ($yearId - $this->daysRequested->vacationPolicy["from_year"]) + $this->daysRequested->vacationPolicy["additional_days_per_year"];
            }
        }
        return $day;
    }

    public function setVacationPeriodYear(int $daysRequested): string
    {
        $finalVacationPeriodYear = [];

        collect(json_decode($this->daysRequested->vacationPeriodYear, true))
            ->sortBy('yearId')
            ->map(function ($year) use (&$daysRequested, &$finalVacationPeriodYear) {
                $totalDaysForThisYear = $this->getDaysByAntiquity($year["yearId"]) +
                    $this->daysRequested->vacationPolicyVacationDays +
                    $this->daysRequested->daysByOldJobs;

                if (array_key_exists("pending_days", $year)) {
                    if ($daysRequested > $year["pending_days"]) {
                        $daysRequested -= $year["pending_days"];
                        $year["pending_days"] = 0;
                    } else {
                        $year["pending_days"] -= $daysRequested;
                        $daysRequested = 0;
                    }
                } else {
                    if ($totalDaysForThisYear < $daysRequested) {
                        $daysRequested -= $totalDaysForThisYear;
                    } else {
                        $year["pending_days"] = $totalDaysForThisYear - $daysRequested;
                        $daysRequested = 0;
                    }
                }
                $year["vacation_days"] = $totalDaysForThisYear - $daysRequested;

                $finalVacationPeriodYear[] = [
                    "id" => $year["id"],
                    "text" => $year["text"],
                    "yearId" => $year["yearId"],
                    "pending_days" => $year["pending_days"] ?? 0,
                    ...(isset($year["vacation_days"]) ? ["vacation_days" => $year["vacation_days"]] : []),
                ];

                return $finalVacationPeriodYear;
            })
            ->values()
            ->toArray();

        return json_encode($finalVacationPeriodYear);
    }

    public function generateRegistrationCode(): string
    {
        /** Codigo de configuracion */
        $codeSetting = CodeSetting::where('table', 'payroll_vacation_requests')->first();

        $code = generate_registration_code(
            $codeSetting->format_prefix,
            strlen($codeSetting->format_digits),
            (strlen($codeSetting->format_year) == 2) ? (isset($this->currentFiscalYear) ?
                substr($this->currentFiscalYear->year, 2, 2) : date('y')) : (isset($this->currentFiscalYear) ?
                $this->currentFiscalYear->year : date('Y')),
            PayrollVacationRequest::class,
            $codeSetting->field
        );

        return $code;
    }

    public function prepareForValidation($data): array
    {
        /* Fila del excel bajo procesamiento */
        $row = [];

        /** Código del periodo */
        $row["code"] = $this->generateRegistrationCode();

        /** Hallar el id del trabajador */
        $payrollStaff = isset($data['cedula_del_trabajador']) ? PayrollStaff::query()
            ->withOnly(['payrollEmploymentNoAppends'])
            ->where('id_number', $data['cedula_del_trabajador'])
            ->firstOrFail() : null;

        /** Fecha de la solicitud */
        $row['request_date'] = isset($data['fecha_de_la_solicitud']) ?
            Carbon::parse(Date::excelToDateTimeObject($data['fecha_de_la_solicitud'])) : null;

        /** Hallar el id del trabajador */
        $row['payroll_staff_id'] = $payrollStaff?->id ?? null;

        /** Hallar la fecha de inicio de actividades en la institución */
        $startDateYear = $payrollStaff?->payrollEmploymentNoAppends?->start_date ?
            Carbon::parse($payrollStaff->payrollEmploymentNoAppends->start_date)->year : null;
        /** Encuentra los años para los periodos solicitados y los codifica en json */
        $row['vacation_period_year'] = isset($data['anos_del_periodo_vacacional']) ? json_encode(
            collect(
                explode(',', $data['anos_del_periodo_vacacional'])
            )->map(function ($year) use ($startDateYear) {
                return [
                    "id" => (int) $year,
                    "text" => (int) $year,
                    "yearId" => (int) $year - $startDateYear,
                ];
            })
        ) : null;

        /** Cantidad de dias solicitados */
        $row['days_requested'] = $data['dias_solicitados'] ?? null;

        /** Fecha de inicio de vacaciones */
        $row['start_date'] = isset($data['fecha_de_inicio_de_vacaciones']) ? Carbon::parse(
            Date::excelToDateTimeObject($data['fecha_de_inicio_de_vacaciones'])
        )->format('d-m-Y') : null;

        /** Fecha de culminación de vacaciones */
        $row['end_date'] = isset($data['fecha_de_culminacion_de_vacaciones']) ? Carbon::parse(
            Date::excelToDateTimeObject($data['fecha_de_culminacion_de_vacaciones'])
        )->format('d-m-Y') : null;

        /** Nombre de la politica de vacaciones usarada para el trabajador */
        $row['vacational_policy'] = $data['politica'] ?? null;

        return $row;
    }

    /**
     * Reglas de validación
     *
     * @return array
     */
    public function rules(): array
    {
        $this->daysRequested = new DaysRequested($this->institutionId);

        return [
            'code'                  =>  ['required', 'string', 'max:255', 'unique:payroll_vacation_requests,code'],
            'days_requested'        =>  ['required', $this->daysRequested],
            'vacation_period_year'  =>  ['required'],
            'payroll_staff_id'      =>  ['required', 'exists:payroll_staffs,id'],
            'request_date'          =>  ['required', 'date', new PayrollVacationRequestDate()],
            'vacational_policy'     =>  ['required', 'exists:payroll_vacation_policies,name'],
            'start_date'            =>  ['required', 'date', new PayrollVacationStartDate()],
            'end_date'              =>  ['required', 'date'],
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
            'payroll_staff_id.required'     => 'La cédula del trabajador es obligatorio.',
            'payroll_staff_id.exists'       => 'La cédula del trabajador no existe.',
            'vacation_period_year.required' => 'Los años del periodo vacacional son obligatorios.',
            'days_requested.required'       => 'Los dias solicitados son obligatorios.',
            'start_date.required'           => 'La fecha de inicio del periodo vacacional es obligatoria.',
            'start_date.date'               => 'La fecha de inicio del periodo vacacional debe ser una fecha.',
            'start_date.unique'             => 'La fecha de inicio del periodo vacacional ya se registro en otro periodo.',
            'end_date.required'             => 'La fecha de culminación del periodo vacacional es obligatoria.',
            'end_date.date'                 => 'La fecha de culminación del periodo vacacional debe ser una fecha.',
            'end_date.unique'               => 'La fecha de culminación del periodo vacacional ya se registro en otro periodo.',
            'vacational_policy.required'    => 'La politica de vacaciones es obligatoria.',
            'vacational_policy.exists'      => 'La politica de vacaciones no existe.',
            'request_date.required'         => 'La fecha de la solicitud de vacaciones es obligatoria.',

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
                'sheetName' => 'Historial de vacaciones',
            ];
            $jsonErrors = json_encode($validationErrors);

            \Illuminate\Support\Facades\Storage::disk('temporary')->append($this->errorsFilePath, $jsonErrors);
        }
    }

    /**
     * Registros de eventos de importación
     *
     * @return array
     */
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
                    $importNotificationMessage = 'Alguno de los registros que trataste de importar fallaron.';
                    $sendEmailMessage = '';
                    $errorExcelFiles = [
                        [
                            'file' => Excel::raw(
                                new FailRegisterImportExport($errors),
                                \Maatwebsite\Excel\Excel::XLSX
                            ),
                            'fileName' => 'Errores_de_importacion_vacaciones.xlsx',
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
                            'Fallos de Importacion de registros',
                            $importNotificationMessage . ' ' . $sendEmailMessage
                        )
                    );
                } else {
                    $this->user->notify(new SystemNotification('Éxito', 'Importación exitosa.'));
                }
                Storage::disk('temporary')->delete($this->errorsFilePath);
            },
        ];
    }
}
