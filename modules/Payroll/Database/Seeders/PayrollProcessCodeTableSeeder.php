<?php

namespace Modules\Payroll\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Payroll\Models\PayrollInstructionDegree;
use Modules\Payroll\Models\PayrollProcessCode;

/**
 * @class PayrollProcessCodeTableSeeder
 * @brief Inicializar los códigos de procesos
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollProcessCodeTableSeeder extends Seeder
{
    /**
     * Método que registra los códigos de procesos
     *
     * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $payrollProcessCodes = [
            ['code' => '000', 'name' =>  'Interes por Transferencias'],
            ['code' => '001', 'name' =>  'Aporte Inicial'],
            ['code' => '002', 'name' =>  'Aporte Regular'],
            ['code' => '003', 'name' =>  'Amortización Préstamo'],
            ['code' => '004', 'name' =>  'Préstamo Capital'],
            ['code' => '005', 'name' =>  'Retiro Parcial'],
            ['code' => '006', 'name' =>  'Anticipo'],
            ['code' => '008', 'name' =>  'Amortización de Anticipo'],
            ['code' => '009', 'name' =>  'Aportes Días Adicionales'],
            ['code' => '012', 'name' =>  'Aporte Regular por Transferencia'],
            ['code' => '014', 'name' =>  'Préstamo Capital por Transferencia'],
            ['code' => '015', 'name' =>  'Retiro por Transferencia'],
            ['code' => '016', 'name' =>  'Anticipo por Transferencia'],
            ['code' => '102', 'name' =>  'Reverso Aporte Regular'],
        ];

        DB::transaction(function () use ($payrollProcessCodes) {
            foreach ($payrollProcessCodes as $payrollProcessCode) {
                PayrollProcessCode::updateOrCreate(
                    ['code' => $payrollProcessCode['code']],
                    [
                        'name' => $payrollProcessCode['name']
                    ]
                );
            }
        });
    }
}
