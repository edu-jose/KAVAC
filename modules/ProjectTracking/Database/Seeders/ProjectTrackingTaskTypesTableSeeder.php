<?php

namespace Modules\ProjectTracking\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\ProjectTracking\Models\ProjectTrackingTaskTypes;

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
class ProjectTrackingTaskTypesTableSeeder extends Seeder
{
    /**
     * Ejecuta los seeds de la base de datos
     *
     * @return void     [descripción de los datos devueltos]
     */
    public function run()
    {
        Model::unguard();

        $projectTrackingTaskTypes = [
            [
                'name' => 'Tarea',
                'description' => '',
                'color' => '#0000ff'
            ],
            [
                'name' => 'Incidencia',
                'description' => '',
                'color' => '#ffff00'
            ],
            [
                'name' => 'Nota',
                'description' => '',
                'color' => '#CCFF00'
            ],
            [
                'name' => 'Retroalimentación',
                'description' => '',
                'color' => '#FF8000'
            ],
            [
                'name' => 'Problema/Error',
                'description' => '',
                'color' => '#FF0000',
            ],
            [
                'name' => 'Funcionalidad',
                'description' => '',
                'color' => '#008000',
            ],
            [
                'name' => 'Obstáculo',
                'description' => '',
                'color' => '#808080',
            ],
            [
                'name' => 'En Espera',
                'description' => '',
                'color' => '#ADD8E6'
            ],
            [
                'name' => 'Importante',
                'description' => '',
                'color' => '#FF00FF'
            ],
            [
                'name' => 'Mejora',
                'description' => '',
                'color' => '#DFFF00'
            ],
            [
                'name' => 'Característica',
                'description' => '',
                'color' => '#820000'
            ]
        ];

        DB::transaction(function () use ($projectTrackingTaskTypes) {
            foreach ($projectTrackingTaskTypes as $projectTrackingTaskType) {
                ProjectTrackingTaskTypes::updateOrCreate(
                    ['color' => $projectTrackingTaskType['color']], // Condiciones para buscar
                    [
                        'name' => $projectTrackingTaskType['name'],
                        'description' => $projectTrackingTaskType['description'],
                    ] // Valores para crear o actualizar
                );
            }
        });
    }
}
