<?php

/** [descripción del namespace] */

namespace Modules\Payroll\Rules;

use Illuminate\Support\Arr;
use Illuminate\Contracts\Validation\Rule;
use Modules\Payroll\Models\PayrollPosition;

/**
 * @class PositionAvailable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PositionAvailable implements Rule
{
    protected $data;

    /**
     * Crea una nueva instancia de la regla
     *
     * @method __construct
     *
     * @return void     [descripción de los datos devueltos]
     */
    public function __construct()
    {
        //
    }

    /**
     * Set the data under validation.
     *
     * @method setData
     *
     * @param  array  $data
     * @return $this
     */
    public function setData($data)
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
    public function passes($attribute, $value)
    {
        /* Obtener el valor de number_positions_assigned del PayrollPosition
        relacionado */
        $position = PayrollPosition::find($value);
        $numberPositionsAssigned = $position->number_positions_assigned
            ? $position->number_positions_assigned : 0;

        /* Contar cuántos registros de PayrollEmployment ya están relacionados
        con el cargo y tienen active true */
        $existingEmploymentsCount = $position->payrollEmployments()
            ->wherePivot('active', true)
            ->count();

        return !($numberPositionsAssigned - $existingEmploymentsCount <= 0);
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
        return 'No hay disponibilidad de asignación para el cargo seleccionado';
    }
}
