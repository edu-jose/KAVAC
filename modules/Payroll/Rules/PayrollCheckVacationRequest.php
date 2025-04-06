<?php

namespace Modules\Payroll\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;
use Modules\Payroll\Models\PayrollVacationRequest;
use Illuminate\Contracts\Validation\ValidatorAwareRule;

/**
 * @class PayrollCheckVacationRequest
 * @brief Clase para la regla de validación con solicitudes de vacaciones anteriores
 *
 * Clase para la regla de validación con solicitudes de vacaciones anteriores
 *
 * @author Fabián Palmera <fapalmera@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollCheckVacationRequest implements Rule, ValidatorAwareRule
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
        $start_date = Carbon::parse($datos['start_date']);
        $end_date = $datos['end_date'] ? Carbon::parse($datos['end_date']) : null;
        $vacation_request_id = $datos['id'];

        $payrollVacationRequests = PayrollVacationRequest::where('payroll_staff_id', $datos["payroll_staff_id"])
            ->whereIn('status', ['approved', 'suspended'])
            ->where('id', '<>', $vacation_request_id)->get();

        // Verificar cada solicitud vacacional anterior para que tengas fecha distinta con la solicitud actual
        if ($payrollVacationRequests->isNotEmpty()) {
            foreach ($payrollVacationRequests as $vacation_request) {
                $vacation_date_start = Carbon::parse($vacation_request->start_date);
                $vacation_date_end = $vacation_request->end_date ? Carbon::parse($vacation_request->end_date) : null;
                if ($attribute == "start_date") {
                    $validate_date = Carbon::parse($value);
                    if (!$vacation_date_end && $end_date) {
                        if ($validate_date->gte($vacation_date_start) || ($validate_date->lt($vacation_date_start) && $end_date->gte($vacation_date_start))) {
                            return false;
                        }
                    // Si ambos ajustes salariales (el de prueba y el de la lista) no tienen fecha de culminacion
                    } elseif (!$end_date && !$vacation_date_end) {
                        if ($validate_date->gte($vacation_date_start)) {
                            return false;
                        }
                    // Si ambos ajustes salariales (el de prueba y el de la lista) tienen fecha de culminacion
                    } elseif ($end_date && $vacation_date_end) {
                        if (($validate_date->lt($vacation_date_start) && $end_date->gt($vacation_date_end)) || ($validate_date->gte($vacation_date_start) && $end_date->lte($vacation_date_end)) || ($validate_date->gte($vacation_date_start) && $validate_date->lte($vacation_date_end) && $end_date->gt($vacation_date_end))) {
                            return false;
                        }
                    }
                }

                if ($attribute == "end_date") {
                    $validate_date = Carbon::parse($value);
                    if ($vacation_date_end) {
                        if (($start_date->lt($vacation_date_start) && $validate_date->gt($vacation_date_end)) || ($start_date->gte($vacation_date_start) && $validate_date->lte($vacation_date_end)) || ($start_date->gte($vacation_date_start) && $start_date->lte($vacation_date_end) && $validate_date->gt($vacation_date_end))) {
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
        return 'La :attribute no puede coincidir con otros periodos de solicitudes vacacionales anteriores.';
    }
}
