<?php

namespace Modules\Budget\Models;

use App\Models\CodeSetting as BaseCodeSetting;

/**
 * @class CodeSetting
 * @brief Modelo que extiende las funcionalidades del modelo base CodeSetting
 *
 * Modelo que extiende las funcionalidades del modelo base CodeSetting
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 * @license <a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *              LICENCIA DE SOFTWARE CENDITEL
 *          </a>
 */
class CodeSetting extends BaseCodeSetting
{
    /**
     * Método que permite dividir el formato del código
     *
     * @method  divideCode
     *
     * @author  Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  string $code Formato del código a configurar
     * @param  string $key Formato del código a configurar
     *
     * @return array       Arreglo con las partes que conforman el código
     */
    public static function divideCode($code, $key = '')
    {
        if (config('budget.budget_availability.active') && 'budgetary_availabilities_code' === $key) {
            $separator = config('budget.budget_availability.separator');
            $segments = explode($separator, $code);
            $position = array_search(true, array_map(fn($segment) => ctype_digit($segment) && $segment == str_repeat('0', strlen($segment)), $segments));

            $prefix = implode($separator, array_slice($segments, 0, $position));
            $digits = $segments[$position];
            $sufix = implode($separator, array_slice($segments, $position + 1));

            return list($prefix, $digits, $sufix) = array($prefix, $digits, $sufix);
        }

        return list($prefix, $digits, $sufix) = explode('-', $code);
    }

    /**
     * Método que permite obtener el formato configurado para el código
     *
     * @author  Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return string Retorna el formato del código configurado
     */
    public function getFormatCodeAttribute()
    {
        $separator = config('budget.budget_availability.separator');

        return "{$this->format_prefix}{$separator}{$this->format_digits}" . ($this->format_year ? "{$separator}{$this->format_year}" : '');
    }
}
