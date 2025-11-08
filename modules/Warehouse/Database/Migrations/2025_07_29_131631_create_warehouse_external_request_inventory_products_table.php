<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateWarehouseExternalRequestInventoryProductsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateWarehouseExternalRequestInventoryProductsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouse_external_request_inventory_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_external_request_id')
                ->nullable()
                ->comment('Identificador único de la solicitud');
            $table->unsignedBigInteger('warehouse_inventory_product_id')
                ->nullable()
                ->comment('Identificador único de la solicitud');
            $table->float('quantity')
                ->nullable()
                ->comment('Cantidad solicitada del producto en el inventario');
            $table->integer('new_exist')
                ->nullable()
                ->comment('Nueva existencia');

            $table->timestamps();
            $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');

            $table->foreign('warehouse_external_request_id')
                ->references('id')
                ->on('warehouse_external_requests')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->foreign('warehouse_inventory_product_id')
                ->references('id')
                ->on('warehouse_inventory_products')
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
        Schema::dropIfExists('warehouse_external_request_inventory_products');
    }
}
