<?php

namespace Modules\Payroll\Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Modules\Payroll\Models\PayrollInactivityType;

/**
 * @class PayrollInactivityTipesTableSeeder
 * @brief Inicializar los tipos de inactividad
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollInactivityTypesTableSeeder extends Seeder
{
    /**
     * Método que registra los valores de los tipos de inactividad
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $payrollInactivityTypes = [
            [
                'name' => 'Permiso no remunerado'
            ],
            [
                'name' => 'Comisión de servicio'
            ],
            [
                'name' => 'Año sabático'
            ],
            [
                'name' => 'Renuncia'
            ],
            [
                'name' => 'Jubilado'
            ]
        ];

        DB::transaction(function () use ($payrollInactivityTypes) {
            foreach ($payrollInactivityTypes as $payrollInactivityType) {
                // 1. Busca el registro, incluyendo los soft-deleted
                $existingType = PayrollInactivityType::withTrashed()
                    ->where('name', $payrollInactivityType['name'])
                    ->first();

                if (empty($existingType)) {
                    PayrollInactivityType::create([
                        'name' => $payrollInactivityType['name']
                    ]);
                } elseif ($existingType && $existingType->trashed()) {
                    $existingType->restore();
                }
            }
        });
    }
}
