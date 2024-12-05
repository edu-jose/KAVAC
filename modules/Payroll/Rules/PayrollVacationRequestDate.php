<?php

/** [descripción del namespace] */

namespace Modules\Payroll\Rules;

use Illuminate\Support\Arr;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\DataAwareRule;

/**
 * @class PayrollVacationRequestDate
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollVacationRequestDate implements Rule, DataAwareRule
{
     /** Datos bajo validación */
    protected array $data;

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
        $startDate = strtotime($this->data["start_date"]);

        $requestDate = strtotime($this->data["request_date"]);

        return $startDate > $requestDate;
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
        return 'La fecha de la solicitud de vacaciones debe ser menor a la fecha de inicio del periodo vacacional.';
    }
}
