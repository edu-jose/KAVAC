<?php

namespace Modules\Payroll\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;
use Modules\Payroll\Models\PayrollSalaryAdjustment;
use Illuminate\Contracts\Validation\ValidatorAwareRule;

/**
 * @class PayrollCheckSalaryAdjustments
 * @brief Clase para la regla de validación con ajustes salariales anteriores
 *
 * Clase para la regla de validación con ajustes salariales anteriores
 *
 * @author Fabián Palmera <fapalmera@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollCheckSalaryAdjustments implements Rule, ValidatorAwareRule
{
    /**
     * The validator instance.
     *
     * @var \Illuminate\Validation\Validator
     */
    protected $validator;

    /**
     * Crea una nueva instancia de la regla
     *
     * @return void     [descripción de los datos devueltos]
     */
    public function __construct()
    {
        //
    }

    /**
     * Obtener datos correspondientes a la validación.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return $this
     */
    public function setValidator($validator)
    {
        $this->validator = $validator;
        return $this;
    }

    /**
     * Determina si pasa la regla de validación.
     *
     * @param  string  $attribute   Nombre del atributo
     * @param  mixed   $value       Valor del atributo a evaluar
     *
     * @return bool    Devuelve verdadero si la regla se cumple de lo contrario devuelve falso
     */
    public function passes($attribute, $value)
    {
        $datos = $this->validator->getData();

        $start_date = Carbon::parse($datos['start_increase_date']);
        $end_date = $datos['end_increase_date'] ? Carbon::parse($datos['end_increase_date']) : null;
        $salary_tabulator = $datos['payroll_salary_tabulator_id'];
        $salary_adjustment_id = $datos['id'];

        $payrollSalaryAdjustments = PayrollSalaryAdjustment::where('payroll_salary_tabulator_id', $salary_tabulator)
            ->where('id', '<>', $salary_adjustment_id)->get();

        if ($payrollSalaryAdjustments->isNotEmpty()) {
            foreach ($payrollSalaryAdjustments as $salary_adjustment) {
                $adjust_start = $salary_adjustment->start_increase_date;
                $adjust_end = $salary_adjustment->end_increase_date ?? null;

                if ($attribute == "start_increase_date") {
                    $validate_date = Carbon::parse($value);
                    // Si el ajuste salarial de la prueba no tiene fecha de culminacion
                    if (!$end_date && $adjust_end) {
                        if ($validate_date->lt($adjust_start) || ($validate_date->gte($adjust_start) && $validate_date->lt($adjust_end))) {
                            return false;
                        }
                    // Si ambos ajustes salariales (el de prueba y el de la lista) no tienen fecha de culminacion
                    } elseif (!$end_date && !$adjust_end) {
                        if ($validate_date->gte($adjust_start)) {
                            return false;
                        }
                    // Si ambos ajustes salariales (el de prueba y el de la lista) tienen fecha de culminacion
                    } elseif ($end_date && $adjust_end) {
                        if (($validate_date->lt($adjust_start) && $end_date->gt($adjust_end)) || ($validate_date->gte($adjust_start) && $end_date->lte($adjust_end)) || ($validate_date->gte($adjust_start) && $validate_date->lte($adjust_end) && $end_date->gt($adjust_end))) {
                            return false;
                        }
                    }
                }

                if ($attribute == "end_increase_date") {
                    $validate_date = Carbon::parse($value);
                    // Si el ajuste salarial de la lista no tiene fecha de culminacion
                    if (!$adjust_end) {
                        if ($start_date->gte($adjust_start) || ($start_date->lt($adjust_start) && $validate_date->gte($adjust_start))) {
                            return false;
                        }
                    }
                    // Si el ajuste salarial de la lista tiene fecha de culminacion
                    if ($adjust_end) {
                        if (($start_date->lt($adjust_start) && $validate_date->gt($adjust_end)) || ($start_date->gte($adjust_start) && $validate_date->lte($adjust_end)) || ($validate_date->gte($adjust_start) && $validate_date->lte($adjust_end) && $start_date->lt($adjust_start))) {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Obtiene el mensaje de validación.
     *
     * @return string    Devuelve una cadena de texto con el mensaje de error si la validación no es exitosa
     */
    public function message()
    {
        return 'La :attribute no puede coincidir con otros periodos de ajustes salariales anteriores.';
    }
}
