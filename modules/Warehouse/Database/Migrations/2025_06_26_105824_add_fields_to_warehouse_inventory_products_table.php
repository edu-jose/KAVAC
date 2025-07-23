<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsToWarehouseInventoryProductsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsToWarehouseInventoryProductsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('warehouse_inventory_products')) {
            Schema::table('warehouse_inventory_products', function (Blueprint $table) {
                if (!Schema::hasColumn('warehouse_inventory_products', 'expiration_date')) {
                    $table->date('expiration_date')->nullable()
                          ->comment('Fecha de vencimiento del producto en el inventario');
                };
                if (!Schema::hasColumn('warehouse_inventory_products', 'batch_number')) {
                    $table->string('batch_number')->nullable()
                          ->comment('Número de lote del producto en el inventario');
                };
            });
        };
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('warehouse_inventory_products')) {
            Schema::table('warehouse_inventory_products', function (Blueprint $table) {
                if (Schema::hasColumn('warehouse_inventory_products', 'expiration_date')) {
                    $table->dropColumn('expiration_date');
                };
                if (Schema::hasColumn('warehouse_inventory_products', 'batch_number')) {
                    $table->dropColumn('batch_number');
                };
            });
        };
    }
}
