<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsToCitizenServiceRequestsTable
 * @brief Agrega campos a la tabla de solicitudes de servicio
 *
 * @author Yenifer Ramírez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsToCitizenServiceRequestsTable extends Migration
{
    protected $stringFields = [
        ['name' => 'type_team', 'length' => 200, 'comment' => 'Tipo de equipo'],
        ['name' => 'brand', 'length' => 100, 'comment' => 'Marca'],
        ['name' => 'model', 'length' => 100, 'comment' => 'Modelo'],
        ['name' => 'serial', 'length' => 100, 'comment' => 'Serial'],
        ['name' => 'color', 'length' => 100, 'comment' => 'Color'],
        ['name' => 'transfer', 'length' => 200, 'comment' => 'Motivo de traslado'],
        ['name' => 'code', 'length' => 100, 'comment' => 'Codigo de inventario'],
        ['name' => 'entryhour', 'length' => 100, 'comment' => 'Hora de entrada'],
        ['name' => 'exithour', 'length' => 100, 'comment' => 'Hora de salida'],
        ['name' => 'informationteam', 'length' => 200, 'comment' => 'Información adicional del equipo'],
    ];

    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('citizen_service_requests', function (Blueprint $table) {
            foreach ($this->stringFields as $field) {
                if (!Schema::hasColumn('citizen_service_requests', $field['name'])) {
                    $table->string($field['name'], $field['length'])->nullable()->comment($field['comment']);
                }
            }

            if (!Schema::hasColumn('citizen_service_requests', 'type_institution')) {
                $table->boolean('type_institution')->default(false)->comment('Establece si es institución o no');
            }
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('citizen_service_requests', function (Blueprint $table) {
            foreach ($this->stringFields as $field) {
                if (Schema::hasColumn('citizen_service_requests', $field['name'])) {
                    $table->dropColumn([$field['name']]);
                }
            }
            if (Schema::hasColumn('citizen_service_requests', 'type_institution')) {
                $table->dropColumn(['type_institution']);
            }
        });
    }
}
