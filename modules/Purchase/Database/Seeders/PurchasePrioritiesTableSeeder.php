<?php

namespace Modules\Purchase\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Purchase\Models\PurchasePriority;

/**
 * @class PurchasePrioritiesTableSeeder
 * @brief Carga los datos de prioridades de compra
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchasePrioritiesTableSeeder extends Seeder
{
    /**
     * Contador de estatus de documentos cargados
     *
     * @var int $count
     */
    protected $count = 0;

    /**
     * Ejecuta los seeers de base de datos
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $priorities = [
            [
                'name' => 'Alta',
                'description' => 'Corresponde a situaciones criticas o urgentes que requieren atención inmediata ' .
                                'para evitar impactos mayores en los procesos. ' .
                                'Estas tareas deben ser atendidas de forma prioritaria.',
                'color' => '#E51300'
            ],
            [
                'name' => 'Media',
                'description' => 'Se refiere a actividades importantes que deben ser atendidas en un plazo ' .
                                'razonable, pero que no representan una amenza inmediata para la continuidad ' .
                                'de los procesos. Su ejecución puede ser programada en el corto plazo.',
                'color' => '#F0A30A'
            ],
            [
                'name' => 'Baja',
                'description' => 'Incluye tareas que no son urgentes ni criticas. Pueden ser atendidas de forma ' .
                                'diferida sin afectar el funcionamiento normal del sistema o proceso. ' .
                                'Son generalmente mejoras o actividades que se realizan con frecuencia.',
                'color' => '#647687'
            ],
        ];

        $this->command->line("");
        $this->command->info("<fg=yellow>Cargando las prioridades de compra</>");
        $this->command->line("");

        foreach ($priorities as $priority) {
            PurchasePriority::withTrashed()->updateOrCreate(
                ['name' => $priority['name']],
                [
                    'description' => $priority['description'],
                    'color' => $priority['color'],
                    'deleted_at' => null
                ]
            );
            $this->count++;
        }

        $this->command->info(
            "<fg=green>Se cargó y/o actualizó un total de</>" .
            "<fg=yellow> $this->count </><fg=green>Prioridades de compra</>"
        );
        $this->command->line("");
    }
}
