<?php

/** [descripción del namespace] */

namespace Modules\Payroll\Rules;

use Illuminate\Support\Arr;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\DataAwareRule;
use Modules\Payroll\Models\PayrollVacationRequest;

/**
 * @class PayrollVacationalPeriods
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollVacationalPeriods implements Rule, DataAwareRule
{
    /** Datos bajo validación */
    protected array $data;

    /** Mensaje de error */
    protected string $errorMessage;

    public function __construct(protected $currentFiscalYear)
    {
        $this->currentFiscalYear = $currentFiscalYear;
    }

    /**
     * Set the data under validation.
     *
     * @method setData
     *
     * @param  array  $data
     *
     * @return $this
     */
    public function setData($data): self
    {
        $this->data = Arr::collapse($data);

        return $this;
    }

    public function getPayrollStaffVacationsRequest(): ?PayrollVacationRequest
    {
        return PayrollVacationRequest::query()
            ->where('payroll_staff_id', $this->data['payroll_staff_id'])
            ->first();
    }

    /**
     * Valida los periodos de vacaciones solicitados (En base de datos y en los solicitados en la carga masiva)*
     * @method validateVacationsPeriods
     * @param  PayrollVacationRequest  $vacationsRequest
     * @return bool
     */
    public function validateVacationsPeriods(PayrollVacationRequest $vacationsRequest): bool
    {
        $codeArray = [
            $vacationsRequest->vacation_period_year,
            $this->data['vacation_period_year']
        ];
        // Parse JSON strings into associative arrays
        $data = array_map(function ($item) {
            return json_decode($item, true);
        }, $codeArray);

        // Group arrays by "id" and calculate sum of pending days
        $sumPendingDays = 0;
        $groupedData = [];
        foreach ($data as $item) {
            foreach ($item as $i) {
                $id = $i['id'];
                $groupedData[$id][] = $item;
            }
        }

        foreach ($groupedData as $id => $group) {
            if (count($group) > 1) {
                // If there are coincidences, sum the "pending_days"
                foreach ($group as $item) {
                    foreach ($item as $i) {
                        if (isset($i['pending_days'])) {
                            $sumPendingDays += $i['pending_days'];
                        }
                    }
                }
            }
        }

        $sumPendingDays > 0 ? session()->put('pending_days', $sumPendingDays) : session()->forget('pending_days');

        dump(session()->get('pending_days'));

        return $sumPendingDays > 0;
    }

    /**
     * Determina si pasa la regla de validación.
     *
     * @method passes
     *
     * @param  string  $attribute   Nombre del atributo
     * @param  mixed   $value       Valor del atributo a evaluar
     *
     * @return bool    Devuelve verdadero si la regla se cumple de lo contrario devuelve falso
     */
    public function passes($attribute, $value): bool
    {
        $vacationsRequest = $this->getPayrollStaffVacationsRequest();

        if (!$vacationsRequest) {
            return true;
        }
        return $this->validateVacationsPeriods($vacationsRequest);
    }

    /**
     * Obtiene el mensaje de validación.
     *
     * @method message
     *
     * @return string    Devuelve una cadena de texto con el mensaje de error si la validación no es exitosa
     */
    public function message(): string
    {
        return $this->errorMessage;
    }
}
