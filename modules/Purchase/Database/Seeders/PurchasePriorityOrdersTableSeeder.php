<?php

namespace Modules\Purchase\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Purchase\Models\PurchasePriorityOrder;

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
class PurchasePriorityOrdersTableSeeder extends Seeder
{
    /**
     * Contador de ordenes de prioridad de compra
     *
     * @var int $count
     */
    protected $count = 0;

    /**
     * Ejecuta los seeds de la base de datos
     *
     * @return void     [descripción de los datos devueltos]
     */
    public function run()
    {
        Model::unguard();

        $priorityOrders = [
            [
                'order' => 1,
                'description' => 'La necesidad debe ser atendida de inmediato porque su ausencia ' .
                                'afecta gravemente la operatividad, seguridad, continuidad o cumplimiento ' .
                                'de funciones esenciales. No puede postergarse.',
            ],
            [
                'order' => 2,
                'description' => 'La solicitud es muy importante y debe procesarse en el corto plazo, ' .
                                'ya que su retraso podría generar interrupciones y atrasos operativos ' .
                                'en el desarrollo de las actividades.',
            ],
            [
                'order' => 3,
                'description' => 'La solicitud responde a un requerimiento funcional importante, ' .
                                'pero puede programarse para ser atendida en un plazo cercano ' .
                                'sin afectar los procesos.',
            ],
            [
                'order' => 4,
                'description' => 'No hay afectación inmediata, pero es recomendable atenderla pronto ' .
                                'para evitar riesgos operativos.',
            ],
            [
                'order' => 5,
                'description' => 'Puede ser gestionada como parte de las actividades regulares. ' .
                                'No compromete procesos críticos pero mejora la eficiencia o la ' .
                                'calidad de las actividades.',
            ],
            [
                'order' => 6,
                'description' => 'Se puede incorporar en el cronograma de adquisiciones, ' .
                                'mantenimientos o contrataciones sin urgencia. Su impacto es menor y controlado.',
            ],
            [
                'order' => 7,
                'description' => 'Aporta valor, pero su postergación no afecta el funcionamiento ' .
                                'de la organización. Puede esperarse sin inconvenientes.',
            ],
            [
                'order' => 8,
                'description' => 'Es conveniente, pero no urgente. Su atención puede depender de la ' .
                                'disponibilidad de recursos presupuestarios o logísticos.',
            ],
            [
                'order' => 9,
                'description' => 'Tiene un carácter complementario. Su atención se puede dar en tiempos ' .
                                'de baja carga operativa o junto con otras actividades.',
            ],
            [
                'order' => 10,
                'description' => 'La solicitud puede considerarse solo si existen recursos sobrantes. ' .
                                'No tiene impacto en los procesos operativos ni administrativos actuales.',
            ],
        ];

        $this->command->line("");
        $this->command->info("<fg=yellow>Cargando las ordenes de prioridades de compra</>");
        $this->command->line("");

        foreach ($priorityOrders as $priorityOrder) {
            PurchasePriorityOrder::withTrashed()->updateOrCreate(
                ['order' => $priorityOrder['order']],
                [
                    'description' => $priorityOrder['description'],
                    'deleted_at' => null
                ]
            );
            $this->count++;
        }

        $this->command->info(
            "<fg=green>Se cargó y/o actualizó un total de</>" .
            "<fg=yellow> $this->count </><fg=green>Ordenes de Prioridades de compra</>"
        );
        $this->command->line("");
    }
}
