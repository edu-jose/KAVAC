<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldPurchaseProductIdToWarehouseProductsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldPurchaseProductIdToWarehouseProductsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('warehouse_products', function (Blueprint $table) {
            $table->unsignedBigInteger('purchase_product_id')->nullable();

            $table->foreign('purchase_product_id')
                ->references('id')
                ->on('purchase_products')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('warehouse_products', function (Blueprint $table) {
            $table->dropForeign(['purchase_product_id']);
            $table->dropColumn('purchase_product_id');
        });
    }
}
