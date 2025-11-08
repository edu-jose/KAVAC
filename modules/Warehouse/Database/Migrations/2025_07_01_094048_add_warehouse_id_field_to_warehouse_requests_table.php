<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Warehouse\Models\Warehouse;

/**
 * @class AddWarehouseIdFieldToWarehouseRequestsTable
 * @brief Agrega la columna warehouse_id a la tabla warehouse_requests
 *
 * Clase que agrega la columna warehouse_id a la tabla warehouse_requests
 *
 * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddWarehouseIdFieldToWarehouseRequestsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('warehouse_requests', 'warehouse_id')) {
                    Schema::table('warehouse_requests', function (Blueprint $table) {
            $table->foreignIdFor(Warehouse::class)
                ->comment('Identificador del almacen')
                ->nullable()
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
        }
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('warehouse_requests', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
        });
    }
}
