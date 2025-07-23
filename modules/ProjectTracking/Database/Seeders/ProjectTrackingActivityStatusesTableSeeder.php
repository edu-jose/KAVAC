<?php

namespace Modules\ProjectTracking\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\ProjectTracking\Models\ProjectTrackingActivityStatus;

/**
 * @class $CLASS$
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingActivityStatusesTableSeeder extends Seeder
{
    /**
     * Ejecuta los seeds de la base de datos
     *
     * @return void     [descripción de los datos devueltos]
     */
    public function run()
    {
        Model::unguard();

        $projectTrackingActivityStatuses = [
            [
                'color' => '#5EBC33',
                'name' => 'Abierta',
                'description' => 'La actividad se encuentra abierta',
            ],
            [
                'color' => '#BBBBBB',
                'name' => 'Pausada',
                'description' => 'La actividad se encuentra pausada',
            ],
            [
                'color' => '#7E7E7E',
                'name' => 'Cerrada',
                'description' => 'La actividad se encuentra cerrada'
            ]
        ];

        DB::transaction(function () use ($projectTrackingActivityStatuses) {
            foreach ($projectTrackingActivityStatuses as $projectTrackingActivityStatus) {
                ProjectTrackingActivityStatus::updateOrCreate(
                    [
                        'name' => $projectTrackingActivityStatus['name']

                    ],
                    [
                        'color' => $projectTrackingActivityStatus['color'],
                        'description' => $projectTrackingActivityStatus['description']
                    ]
                );
            }
        });
    }
}
