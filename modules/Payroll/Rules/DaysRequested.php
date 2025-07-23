<?php

namespace Modules\Payroll\Rules;

use Carbon\Carbon;
use App\Models\FiscalYear;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Validation\Rule;
use Modules\Payroll\Models\PayrollHoliday;
use Modules\Payroll\Models\PayrollVacationPolicy;
use Illuminate\Contracts\Validation\DataAwareRule;
use Modules\Payroll\Models\PayrollVacationRequest;
use Modules\Payroll\Models\PayrollEmploymentNoAppends;

/**
 * @class DaysRequested
 * @brief Gestiona las reglas de validación y la validación para determinar si cumple o no con el requerimiento
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DaysRequested implements Rule, DataAwareRule
{
    /**
     * Datos bajo validación
     *
     * @var array $data
     */
    protected array $data;

    /** Dias feriados */
    protected $payrollHolidays;

    /** Año fiscal */
    protected int $fiscalYear;

    /** Mensaje de error */
    protected string $errorMessage;

    /** Periodo de vacaciones valido */
    public string $vacationPeriodYear;

    /** Dias de vacaciones otorgados por la politica */
    public int $vacationPolicyVacationDays;

    /** Dias de vacaciones por antiguedad  */
    public int $daysByOldJobs;

    /** Politica de vacaciones */
    public $vacationPolicy;

    /**
     * Crea una nueva instancia de la regla
     *
     * @return void
     */
    public function __construct(protected int $institutionId)
    {
        $this->payrollHolidays = PayrollHoliday::query();

        $this->fiscalYear = (int) FiscalYear::where('active', true)->value('year');

        $this->errorMessage = '';
    }

    /**
     * Establece los datos para la validación
     *
     * @param  array  $data Arreglo con los datos
     *
     * @return $this
     */
    public function setData($data): self
    {
        $this->data = Arr::collapse($data);

        return $this;
    }

    /**
     * Obtiene la politica de vacaciones para validación
     *
     * @method getVacationPolicy
     *
     * @return object Objeto con la politica de vacaciones
     */
    public function getVacationPolicy(): object | null
    {
        $vacationPolicy = null;

        try {
            $requestDate = $this->data["request_date"];

            $vacationPolicy = PayrollVacationPolicy::where('name', $this->data["vacational_policy"])
                ->where('institution_id', $this->institutionId)
                ->where(function ($query) use ($requestDate) {
                    $query
                        ->where('end_date', '<=', $requestDate)
                        ->orWhereNull('end_date');
                })
                ->firstOrFail();
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return null;
            }
        }

        return $vacationPolicy;
    }

    /**
     * Obtiene el tiempo laborado en la institución
     * y en otras instituciones
     *
     * @method getPayrollStaffWorkingTime
     *
     * @return array Array que contiene el tiempo laborado en la institución y en otras instituciones
     */
    public function getPayrollStaffWorkingTime(): array
    {
        $payrollStaffEmployment = PayrollEmploymentNoAppends::query()
            ->where('payroll_staff_id', $this->data["payroll_staff_id"])
            ->select('start_date')
            ->selectRaw("COALESCE((substring(years_apn from 'Años: ([0-9]+)')::int), 0) as years_apn")
            ->first();

        $startDate = Carbon::parse($payrollStaffEmployment->start_date);

        $now = Carbon::now()->year($this->fiscalYear);

        $workingTime = [
            'institution' => $startDate->diffInYears($now),
            'old_jobs' => $payrollStaffEmployment->years_apn
        ];

        return $workingTime;
    }

    public function getPreviousProcessedVacationRequests(): Collection
    {
        $previousProcessedVacationRequests = collect([]);

        foreach (json_decode($this->data["vacation_period_year"], true) as $vacationPeriodYear) {
            $payrollVacationRequest = PayrollVacationRequest::query()
                ->without(['institution', 'payrollStaff'])
                ->where('payroll_staff_id', $this->data["payroll_staff_id"])
                ->where('status', 'approved')
                ->where('vacation_period_year', 'like', '%' . $vacationPeriodYear['text'] . '%')
                ->where('vacation_period_year', 'not like', '%old%')->first();

            if ($payrollVacationRequest) {
                $old_vacation_period_years = $payrollVacationRequest->vacation_period_year;
                foreach (json_decode($this->data["vacation_period_year"], true) as $request_period_year) {
                    foreach ($old_vacation_period_years as $old_year) {
                        if (stripos($old_year['id'], $request_period_year['text']) !== false) {
                            $old_year['old'] = 1;
                        }
                    }
                }
                $payrollVacationRequest->vacation_period_year = $old_vacation_period_years;
                $payrollVacationRequest->save();

                $previousProcessedVacationRequests[] = $payrollVacationRequest;
            }
        }

        return PayrollVacationRequest::query()
            ->where('payroll_staff_id', $this->data["payroll_staff_id"])
            ->where('status', 'approved')
            ->where('vacation_period_year', 'like', '%old%')
            ->get()
            ->map(function ($vacationRequest) {
                return $vacationRequest->vacation_period_year;
            });
    }

    public function checkForPendingDays(
        Collection $previousProcessedVacationRequests,
        array $requestedVacationPeriodYear
    ): int | null {
        $pendingDaysCount = 0;

        if (!$previousProcessedVacationRequests) {
            return null;
        }

        foreach ($previousProcessedVacationRequests as $previousProcessedVacationRequest) {
            foreach ($previousProcessedVacationRequest as $previousRequest) {
                if (
                    array_key_exists("pending_days", $previousRequest) &&
                    $requestedVacationPeriodYear["yearId"] == $previousRequest["yearId"]
                ) {
                    if ($previousRequest["pending_days"] == 0) {
                        $this->errorMessage = "El periodo " . $previousRequest["text"] . " ya no tiene dias pendientes";
                    }
                    $pendingDaysCount = $previousRequest["pending_days"];
                }
            }
        }

        return $pendingDaysCount > 0 ? $pendingDaysCount : null;
    }

    public function intersectVacationPeriods(
        Collection $previousProcessedVacationRequests,
        array $requestedVacationPeriodsYears
    ): Collection {
        foreach ($requestedVacationPeriodsYears as &$requestedVacationPeriodYear) {
            $pendingDaysForPeriodYear = $this->checkForPendingDays(
                $previousProcessedVacationRequests,
                $requestedVacationPeriodYear
            );

            if ($pendingDaysForPeriodYear) {
                $requestedVacationPeriodYear["pending_days"] = $pendingDaysForPeriodYear;
            }
        }
        return collect($requestedVacationPeriodsYears);
    }

    /**
     * Valida la politica de vacaciones
     *
     * @method validateVacationalPeriod
     *
     * @return bool
     */
    public function validateVacationalPeriod(): bool
    {
        /** Obtiene la politica vacacional */
        $this->vacationPolicy = $this->getVacationPolicy();

        if (!$this->vacationPolicy) {
            $this->errorMessage = "No se encontró la política de vacaciones para realizar la validación.";

            return false;
        }

        /** Dias de vacaciones otorgados por la politica de vacaciones */
        $this->vacationPolicyVacationDays = $this->vacationPolicy["vacation_days"];
        /** Cantidad de dias a disfrutar */
        $totalVacationDays  = 0;
        /** Dias otorgados por el tiempo laborado en otras instituciones públicas */
        $this->daysByOldJobs = 0;
        /** Cantidad de dias utilizados para calcular la fecha de final de vacaciones */
        $daysToCalculateFinalVacationsDate = 0;
        /** Obtiene el tiempo laborado en la institución y en otras instituciones públicas */
        $payrollStaffWorkingTime = $this->getPayrollStaffWorkingTime();


        /** Años de los periodos vacacionales solicitados */
        $requestedVacationPeriodsYears = json_decode(($this->data["vacation_period_year"]), true);
        /** Obtiene los periodos de vacaciones ya procesados para los periodos solicitados */
        $previousProcessedVacationRequests = $this->getPreviousProcessedVacationRequests();

        $vacationPeriodsYearsArrayForValidation = $this->intersectVacationPeriods(
            $previousProcessedVacationRequests,
            $requestedVacationPeriodsYears
        );

        $enjoyableDays = $this->calculateVacationDays(
            $vacationPeriodsYearsArrayForValidation,
            $payrollStaffWorkingTime,
            $this->vacationPolicy,
        );

        $totalVacationDays += $enjoyableDays;

        /** Cantidad de dias feriados fijos */
        $holidaysCount = 0;
        $holidaysData = $this->payrollHolidays
            ->whereBetween('date', [$this->data['start_date'], $this->data['end_date']])
            ->get();
        foreach ($holidaysData as $holyD) {
            if (date('N', strtotime($holyD->date)) < 6) {
                $holidaysCount++;
            }
        }

        $daysToCalculateFinalVacationsDate = ($totalVacationDays + $holidaysCount ?? 0);

        /** Obtiene los dias entre la fecha de inicio y la fecha de fin */
        $finalVacationsDate = $this->addWeekdaysToDate(
            $this->data['start_date'],
            $daysToCalculateFinalVacationsDate
        );


        $datesOk = (Carbon::parse($finalVacationsDate) >= Carbon::parse($this->data['end_date']));

        $totalDays = $totalVacationDays >= $this->data['days_requested'];
        if (!$totalDays) {
            $this->errorMessage = "La cantidad de dias solicitados excede la cantidad de dias disponible para los periodos solicitados." . " Dias solicitados: " . $this->data['days_requested'] . " Dias disponibles: " . $totalVacationDays;

            return false;
        }

        if ($datesOk === false) {
            $this->errorMessage = "La fecha final de vacaciones calculada segun la politica vacacional, no coincide con la fecha final de la solicitud." . " Fecha fin segun politica vacacional: " . $finalVacationsDate . " Fecha de fin de la solicitud: " . Carbon::parse($this->data['end_date'])->format('d-m-Y');

            return false;
        }

        if ($this->errorMessage) {
            return false;
        }

        if ($datesOk && $totalDays) {
            $this->vacationPeriodYear = $vacationPeriodsYearsArrayForValidation;

            return true;
        }


        return false;
    }

    public function getDaysByAntiquity(int $yearId, object $vacationPolicy): int
    {
        $day = 0;

        if ($yearId >= $vacationPolicy["from_year"]) {
            $periodsForAdditionalDays = intval($yearId / $vacationPolicy["years_for_additional_days"]);
            if ($periodsForAdditionalDays > 0) {
                $day = $periodsForAdditionalDays *  $vacationPolicy["additional_days_per_year"];
            }
        }
        return $day;
    }

    public function calculateVacationDays(
        Collection $vacationPeriodsYearsArrayForValidation,
        array $payrollStaffWorkingTime,
        object $vacationPolicy
    ): int {
        $pendingDays = 0;

        foreach ($vacationPeriodsYearsArrayForValidation as $vacationPeriodYear) {
            if (array_key_exists("pending_days", $vacationPeriodYear)) {
                $pendingDays += $vacationPeriodYear["pending_days"];
            } else {
                $pendingDays += $this->getDaysByAntiquity($vacationPeriodYear["yearId"], $vacationPolicy);

                $pendingDays += $vacationPolicy["vacation_days"];

                if ($vacationPolicy["old_jobs"]) {
                    $this->daysByOldJobs = (
                        $payrollStaffWorkingTime["old_jobs"] * $vacationPolicy["additional_days_per_year"]);
                }

                $pendingDays += $this->daysByOldJobs;
            }
        }

        return $pendingDays;
    }

    /**
     * Determina si pasa la regla de validación.
     *
     * @param  string  $attribute   Nombre del atributo
     * @param  mixed   $value       Valor del atributo a evaluar
     *
     * @return bool    Devuelve verdadero si la regla se cumple de lo contrario devuelve falso
     */
    public function passes($attribute, $value): bool
    {
        if ($this->data['payroll_staff_id'] == null) {
            $this->errorMessage = "No se encontró la cédula del trabajador. Por favor verifique.";

            return false;
        }

        return $this->validateVacationalPeriod();
    }

    /**
     * Obtiene el numero de dias entre la fecha de inicio y la fecha de fin.
     * Solo contempla los dias lunes, martes, miercoles, jueves y viernes y feriados.
     */
    public function addWeekdaysToDate(string $startDate, int $numberOfDays): string
    {
        $currentDate = strtotime($startDate);
        $weekdaysAdded = 0;

        while ($weekdaysAdded < $numberOfDays) {
            // Check if the current day is not a weekend (Saturday or Sunday)
            if (date('N', $currentDate) < 6) {
                $weekdaysAdded++;
            }
            $currentDate = strtotime('+1 day', $currentDate);
        }
        $currentDate = strtotime('-1 day', $currentDate);

        return date('d-m-Y', $currentDate);
    }


    /**
     * Obtiene el mensaje de validación.
     *
     * @return string    Devuelve una cadena de texto con el mensaje de error si la validación no es exitosa
     */
    public function message(): string
    {
        return $this->errorMessage;
    }
}
