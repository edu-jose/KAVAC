<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsToWarehouseMovementsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsToWarehouseMovementsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('warehouse_movements')) {
            Schema::table('warehouse_movements', function (Blueprint $table) {
                if (!Schema::hasColumn('warehouse_movements', 'direct_hire')) {
                    $table->string('direct_hire')->nullable()
                          ->comment('Código de la orden de compra o servicio asociado al movimiento de almacén');
                };
                if (!Schema::hasColumn('warehouse_movements', 'purchase_direct_hire_id')) {
                    $table->foreignId('purchase_direct_hire_id')->nullable()
                          ->comment('Identificador único asociado a la orden/servicio de compras')
                          ->constrained()->onDelete('restrict')->onUpdate('cascade');
                };
                if (!Schema::hasColumn('warehouse_movements', 'supplier')) {
                    $table->string('supplier')->nullable()
                          ->comment('Nombre del proveedor asociado al movimiento de almacén');
                };
                if (!Schema::hasColumn('warehouse_movements', 'purchase_supplier_id')) {
                    $table->foreignId('purchase_supplier_id')->nullable()
                          ->comment('Identificador único asociado a proveedor de compras')
                          ->constrained()->onDelete('restrict')->onUpdate('cascade');
                };
                if (!Schema::hasColumn('warehouse_movements', 'general_observations')) {
                    $table->text('general_observations')->nullable()->comment('Observaciones generales del movimiento de almacén');
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
        Schema::table('warehouse_movements', function (Blueprint $table) {
            if (Schema::hasColumn('warehouse_movements', 'direct_hire')) {
                $table->dropColumn('direct_hire');
            }
            if (Schema::hasColumn('warehouse_movements', 'purchase_direct_hire_id')) {
                $table->dropForeign(['purchase_direct_hire_id']);
                $table->dropColumn('purchase_direct_hire_id');
            }
            if (Schema::hasColumn('warehouse_movements', 'supplier')) {
                $table->dropColumn('supplier');
            }
            if (Schema::hasColumn('warehouse_movements', 'purchase_supplier_id')) {
                $table->dropForeign(['purchase_supplier_id']);
                $table->dropColumn('purchase_supplier_id');
            }
            if (Schema::hasColumn('warehouse_movements', 'general_observations')) {
                $table->dropColumn('general_observations');
            }
        });
    }
}
