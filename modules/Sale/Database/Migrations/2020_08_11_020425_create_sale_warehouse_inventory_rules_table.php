<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateSaleWarehouseInventoryRulesTable
 * @brief Crear tabla de las reglas de abastecimiento de inventario de productos
 *
 * Gestiona la creación o eliminación de la tabla de reglas de abastecimiento de inventario de productos
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateSaleWarehouseInventoryRulesTable extends Migration
{
    /**
     * Método que ejecuta las migraciones
     *
     * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('sale_warehouse_inventory_rules')) {
            Schema::create('sale_warehouse_inventory_rules', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Identificador único del registro');

                $table->integer('minimum')->unsigned()->nullable()
                      ->comment('Cantidad mínima permitida de un producto en el almacén');
                $table->integer('maximum')->unsigned()->nullable()
                      ->comment('Cantidad máxima permitida de un producto en el almacén');

                $table->unsignedBigInteger('sale_warehouse_inventory_product_id');
                $table->foreign('sale_warehouse_inventory_product_id', 'sale_warehouse_inventory_rules_products_id_fk')
                      ->references('id')->on('sale_warehouse_inventory_products')
                      ->onDelete('restrict')->onUpdate('cascade');

                $table->foreignId('user_id')->constrained()->onDelete('restrict')->onUpdate('cascade');

                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            });
        }
    }

    /**
     * Método que elimina las migraciones
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sale_warehouse_inventory_rules');
    }
}
