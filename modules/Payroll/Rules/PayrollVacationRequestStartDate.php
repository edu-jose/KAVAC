<?php

declare(strict_types=1);

namespace Modules\Payroll\Rules;

use Illuminate\Support\Arr;
use Illuminate\Contracts\Validation\Rule;
use Carbon\Carbon;

/**
 * @class PayrollVacationRequestStartDate
 * @brief Verifica que la fecha seleccionada para la solicitud de vacaciones sea el segundo o cuarto lunes del mes
 *
 * Verifica que la fecha seleccionada para la solicitud de vacaciones sea el segundo o cuarto lunes del mes
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollVacationRequestStartDate implements Rule
{
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
        $startDate = Carbon::parse($value);

        // Obtener el primer lunes del mes
        $firstMonday = $startDate->copy()->firstOfMonth();
        $firstMonday = $firstMonday->dayOfWeek == 1 ? $firstMonday : $firstMonday->next(Carbon::MONDAY);

        // Contar los lunes y determinar si es el segundo o cuarto
        $mondayCount = 1;
        $isSecondOrFourth = false;

        while ($firstMonday->lessThanOrEqualTo($startDate)) {
            $firstMonday->addWeek();
            $mondayCount++;

            if ($firstMonday->isSameDay($startDate)) {
                $isSecondOrFourth = match ($mondayCount) {
                    2 => true,
                    4 => true,
                    default => false,
                };
                if ($isSecondOrFourth) {
                    break;
                }
            }
        }

        return $isSecondOrFourth;
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
        return 'La fecha de inicio de la solicitud debe ser el segundo o cuarto lunes del mes.';
    }
}
