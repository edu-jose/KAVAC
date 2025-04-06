<?php

namespace Modules\Accounting\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Accounting\Models\AccountingTypeActivity;

/**
 * @class AccountingTypeActivitiesTableSeeder
 * @brief Información por defecto para Actividades del módulo de contabilidad
 *
 * Gestiona la información por defecto a registrar inicialmente para los Actividades del módulo de contabilidad
 *
 * @author Juan Rosas <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AccountingTypeActivitiesTableSeeder extends Seeder
{
    /**
     * Ejecuta los seeds de la base de datos
     *
     * @return void     [descripción de los datos devueltos]
     */
    public function run()
    {
        $activities = [
            [
                'name' => 'Actividad Operativa',
                'slug' => 'actividad-operativa'
            ],
            [
                'name' => 'Actividad de Inversión',
                'slug' => 'actividad-de-inversion'
            ],
            [
                'name' => 'Actividad de Financiamiento',
                'slug' => 'actividad-de-financiamiento'
            ]
        ];

        foreach ($activities as $activity) {
            AccountingTypeActivity::updateOrCreate(
                ['slug' => $activity['slug']],
                ['name' => $activity['name'], 'slug' => $activity['slug']]
            );
        }
    }
}
