<?php

/** [descripción del namespace] */

namespace Modules\Payroll\Rules;

use Illuminate\Support\Arr;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\DataAwareRule;
use Modules\Payroll\Models\PayrollVacationRequest;

/**
 * @class PayrollVacationStartDate
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollVacationStartDate implements Rule, DataAwareRule
{
    /** Mensaje de error */
    protected string $errorMessage;

    /** Datos bajo validación */
    protected array $data;

    public function __construct()
    {
        $this->errorMessage = '';
    }

    /**
     * Set the data under validation.
     *
     * @method setData
     *
     * @param  array  $data
     * @return $this
     */
    public function setData($data): self
    {
        $this->data = Arr::collapse($data);

        return $this;
    }

    public function beforeEndDate(): bool
    {
        $startDate = strtotime($this->data["start_date"]);

        $endDate = strtotime($this->data["end_date"]);

        if ($startDate > $endDate) {
            $this->errorMessage = 'La fecha de inicio del periodo vacacional debe ser menor a la fecha de culminación del mismo.';

            return false;
        }

        $validPeriod = PayrollVacationRequest::query()
            ->where('payroll_staff_id', $this->data['payroll_staff_id'])
            ->where('start_date', '=', $this->data['start_date'])
            ->where('end_date', '=', $this->data['end_date'])
            ->exists();

        if ($validPeriod) {
            $this->errorMessage = 'La fecha de inicio y la fecha de culminación del periodo vacacional ya se encuentra registrada.';

            return false;
        }

        return true;
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
        return $this->beforeEndDate();
    }

    /**
     * Obtiene el mensaje de validación.
     *
     * @method message
     *
     * @return string    Devuelve una cadena de texto con el mensaje de error si la validación no es exitosa
     */
    public function message()
    {
        return $this->errorMessage;
    }
}
