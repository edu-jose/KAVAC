<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldToSaleDiscountsTable
 * @brief Se quita el valor unico de porcentaje
 *
 * 
 * @author Tsu. Miguel Narvaez <mnarvaez@cenditel.gob.ve> | <miguelnarvaez31@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldToSaleDiscountsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_discounts', function (Blueprint $table) {
            if (Schema::hasColumn('sale_discounts', 'percent')) {
                // Eliminar la restricción única si existe
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('sale_discounts');
                
                if (array_key_exists('sale_discounts_percent_unique', $indexes)) {
                    $table->dropUnique('sale_discounts_percent_unique');
                }
                
                // Luego cambiar el campo
                $table->string('percent', 100)->comment('Porcentaje')->change();
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
        Schema::table('sale_discounts', function (Blueprint $table) {
            if (Schema::hasColumn('sale_discounts', 'percent')) {
                // Cambiar el campo
                $table->string('percent', 100)->comment('Porcentaje')->change();
                
                // Agregar la restricción única solo si no existe
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('sale_discounts');
                
                if (!array_key_exists('sale_discounts_percent_unique', $indexes)) {
                    $table->unique('percent', 'sale_discounts_percent_unique');
                }
            }            
        });
    }
}