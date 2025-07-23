<?php

namespace Modules\Payroll\Models;

use App\Models\Parameter as BaseParameter;
use Modules\Payroll\Repositories\PayrollAssociatedParametersRepository;
use Illuminate\Support\Str;

/**
 * @class Parameter
 * @brief Datos de configuración de parámetros de la aplicación
 *
 * Gestiona la configuración de parámetros de la aplicación
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @property string $translate_formula Traduce el nombre del paraméetro asocido a una fórmula
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Parameter extends BaseParameter
{
    /**
     * Lista de atributos personalizados para mostrar en consultas
     *
     * @var array $appends
     */
    protected $appends = ['translate_formula', 'parameter_options'];

    /**
     * Método traduce el nombre del paramétro asocido a una fórmula
     *
     * @author Francisco J. P. Ruiz <javierrupe19@gmail.com>
     *
     * @return string
     */
    public function getTranslateFormulaAttribute()
    {
        $p_value = json_decode($this->p_value);
        $formula = '';

        if ($p_value) {
            $formula = str_replace('if', 'Si', $p_value->formula ?? '');
            $types = Parameter::where(
                [
                    'required_by' => 'payroll',
                    'active' => true,
                ]
            )->where('p_key', 'like', 'global_parameter_%')->get();
            foreach ($types as $type) {
                $jsonValue = json_decode($type->p_value);
                $formula = str_replace(
                    'parameter(' . $jsonValue->id . ')',
                    $jsonValue->name,
                    $formula
                );
            }

            $parameters = new PayrollAssociatedParametersRepository();
            $typesParameters = [
                'associatedVacation',
                'associatedWorkerFile',
                'associatedBenefit'
            ];
            foreach ($typesParameters as $typeParameter) {
                $types = $parameters->loadData($typeParameter);
                foreach ($types as $type) {
                    if (empty($type['children'])) {
                        $formula = str_replace(
                            $type['id'],
                            $type['name'],
                            $formula
                        );
                    } else {
                        foreach ($type['children'] as $children) {
                            $formula = str_replace(
                                $children['id'],
                                $children['name'],
                                $formula
                            );
                        }
                    }
                }
            }
        }

        return $formula;
    }

    public function getParameterOptionsAttribute()
    {
        $parameters = new PayrollAssociatedParametersRepository();
        $options = [];
        $typesParameters = ['associatedBenefit', 'associatedVacation', 'associatedWorkerFile', 'parameter', 'concept', 'tabulator', 'ari_register'];
        $options['if'] = 'Si';
        foreach ($typesParameters as $typeParameter) {
            $jsonValue = json_decode($this->p_value);
            $formula = $jsonValue->formula ?? '';
            if (empty($formula)) {
                return $options;
            }
            if (in_array($typeParameter, ['parameter', 'concept', 'tabulator', 'ari_register'])) {
                if ($typeParameter == 'parameter') {
                    $types = Parameter::where(
                        [
                            'required_by' => 'payroll',
                            'active'      => true,
                        ]
                    )->where('p_key', 'like', 'global_parameter_%')->get();
                    foreach ($types as $type) {
                        $jsonValue = json_decode($type->p_value);
                        if (Str::contains($formula, 'parameter(' . $jsonValue->id . ')')) {
                            $options['parameter(' . $jsonValue->id . ')'] = $jsonValue->name;
                        }
                    }
                } elseif ($typeParameter == 'concept') {
                    $types = PayrollConcept::all();
                    foreach ($types as $type) {
                        if (Str::contains($formula, 'concept(' . $type['id'] . ')')) {
                            $options['concept(' . $type['id'] . ')'] = $type['name'];
                        }
                    }
                } elseif ($typeParameter == 'tabulator') {
                    $types = PayrollSalaryTabulator::all();
                    foreach ($types as $type) {
                        if (Str::contains($formula, 'tabulator(' . $type['id'] . ')')) {
                            $options['tabulator(' . $type['id'] . ')'] = $type['name'];
                        }
                    }
                } elseif ($typeParameter == 'ari_register') {
                    if (Str::contains($formula, 'ari_register')) {
                        $options['ari_register'] = 'Registro ARI';
                    }
                }
            } else {
                $types = $parameters->loadData($typeParameter);
                foreach ($types as $type) {
                    if (empty($type['children'])) {
                        if (Str::contains($formula, $type['id'])) {
                            $options[$type['id']] = $type['name'];
                        }
                    } else {
                        foreach ($type['children'] as $children) {
                            if (Str::contains($formula, $children['id'])) {
                                $options[$children['id']] = $children['name'];
                            }
                        }
                    }
                }
            }
        }

        return $options;
    }
}
