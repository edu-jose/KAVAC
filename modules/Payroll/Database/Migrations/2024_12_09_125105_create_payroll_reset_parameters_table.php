<?php

use App\Models\Parameter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Payroll\Models\Payroll;
use Modules\Payroll\Models\PayrollConcept;
use Modules\Payroll\Models\PayrollResetParameter;

/**
 * @class CreatePayrollResetParametersTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollResetParametersTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_reset_parameters', function (Blueprint $table) {
            $table->id();
            $table->string('name', 500)
                ->comment('Nombre del parámetro reiniciable a cero');
            $table->string('value', 100)
                ->comment('Valor del parámetro reiniciable a cero');
            $table->foreignId('payroll_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('restrict')
                ->comment('Nómina asociada al parámetro reiniciable a cero');
            $table->foreignId('payroll_concept_id')
                ->nullable()
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('restrict')
                ->comment('Concepto asociado al parámetro reiniciable a cero');
            $table->foreignId('payroll_staff_id')
                ->nullable()
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('restrict')
                ->comment('Trabajador asociado al parámetro reiniciable a cero');
            $table->timestamps();
            $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
        });

        $payrolls = Payroll::all();

        foreach ($payrolls as $payroll) {
            $payrollConceptIds = [];

            if (!empty($payroll->concept_types) && !empty($payroll->payroll_parameters)) {
                foreach ($payroll->concept_types as $conceptType) {
                    foreach ($conceptType as $concept) {
                        array_push($payrollConceptIds, $concept['id']);
                    }
                }
    
                $payrollParameters = $this->getPayrollParameters($payrollConceptIds);
    
                foreach (json_decode($payroll->payroll_parameters) as $parameter) {
                    foreach ($payrollParameters as $param) {
                        if ($param['id'] == $parameter->id) {
                            PayrollResetParameter::create([
                                'name' => $parameter->name,
                                'value' =>  $parameter->value,
                                'payroll_id' => $payroll->id,
                                'payroll_concept_id' => $param['unique'] == true ? $param['concept_id'] : null,
                                'payroll_staff_id' => $parameter->staff_id
                            ]);
                        }
                    }
                }
            }
        }
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payroll_reset_parameters');
    }

    /**
     * Obtiene los parámetros globales de nómina registrados
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param     array    $payrollConceptIds    Ids de conceptos
     *
     * @return    array    Listado de los registros a mostrar
     */
    public function getPayrollParameters($payrollConceptIds)
    {
        $payrollParameters = [];
        foreach ($payrollConceptIds as $payrollConceptId) {
            $payrollConcept = PayrollConcept::find($payrollConceptId);
            if ($payrollConcept) {
                $exploded = multiexplode(
                    [
                        'if',
                        '(',
                        ')',
                        '{',
                        '}',
                        '==',
                        '<=',
                        '>=',
                        '<',
                        '>',
                        '!=',
                        '+',
                        '-',
                        '*',
                        '/'
                    ],
                    $payrollConcept->translate_formula
                );

                foreach ($exploded as $explod) {
                    $parameters = Parameter::where(
                        [
                            'required_by' => 'payroll',
                            'active'      => true,
                        ]
                    )->where('p_value', 'like', '%' . $explod . '%')->get();

                    if ($parameters) {
                        foreach ($parameters as $parameter) {
                            $jsonValue = json_decode($parameter->p_value);
                            if (isset($jsonValue->name)) {
                                if ($jsonValue->name == $explod) {
                                    if ($jsonValue->parameter_type == 'resettable_variable') {
                                        if (!array_key_exists($jsonValue->id, $payrollParameters)) {
                                            $payrollParameters[$jsonValue->id] = [
                                                'id' => $jsonValue->id,
                                                'concept_id' => $payrollConceptId,
                                                'unique' => true
                                            ];
                                        } else {
                                            $payrollParameters[$jsonValue->id]['unique'] = false;
                                        }
                                    } elseif ($jsonValue->parameter_type == 'global_value') {
                                        if (!array_key_exists($jsonValue->id, $payrollParameters)) {
                                            $payrollParameters[$jsonValue->id] = [
                                                'id' => $jsonValue->id,
                                                'concept_id' => $payrollConceptId,
                                                'unique' => true
                                            ];
                                        } else {
                                            $payrollParameters[$jsonValue->id]['unique'] = false;
                                        }
                                    } elseif ($jsonValue->parameter_type == 'processed_variable') {
                                        if (!array_key_exists($jsonValue->id, $payrollParameters)) {
                                            $payrollParameters[$jsonValue->id] = [
                                                'id' => $jsonValue->id,
                                                'concept_id' => $payrollConceptId,
                                                'unique' => true
                                            ];
                                        } else {
                                            $payrollParameters[$jsonValue->id]['unique'] = false;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $payrollParameters;
    }
}
