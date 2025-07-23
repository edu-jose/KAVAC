<?php

namespace Modules\Payroll\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Payroll\Models\PayrollExceptionType;
use Modules\Payroll\Models\PayrollClassificationParameter;

/**
  * @class PayrollClassificationParameterTableSeeder
 * @brief Inicializar los tipos de clkasificación de parametros
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollClassificationParameterTableSeeder extends Seeder
{
    /**
     * Ejecuta los seeds de la base de datos
     *
     * @return void     [descripción de los datos devueltos]
     */
    public function run()
    {
        DB::transaction(function () {
            $exceptionType = PayrollExceptionType::firstOrCreate(
                ['name' => strtoupper('Turnos')],
                ['value_max' => 31]
            );

            $classifications = [
                [
                    'name' => 'DESCANSOS',
                    'payroll_exception_type_id' => $exceptionType->id
                ],
                [
                    'name' => 'DOMINGOS',
                    'payroll_exception_type_id' => $exceptionType->id
                ],
                [
                    'name' => 'FERIADOS',
                    'payroll_exception_type_id' => $exceptionType->id
                ],
                [
                    'name' => 'TURNOS SENCILLOS',
                    'payroll_exception_type_id' => $exceptionType->id
                ]
            ];

            foreach ($classifications as $classification) {
                PayrollClassificationParameter::updateOrCreate(
                    [
                        'name' => strtoupper($classification['name']),
                        'payroll_exception_type_id' => $classification['payroll_exception_type_id']
                    ],
                    $classification
                );
            }
        });
    }
}
