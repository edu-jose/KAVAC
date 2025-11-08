<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateSaleChargeMoney
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Tsu. Miguel Narvaez <mnarvaez@cenditel.gob.ve> | <miguelnarvaez31@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateSaleChargeMoney extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_charge_money', function (Blueprint $table) {
            // Verificar si la columna ya existe antes de agregarla
            if (!Schema::hasColumn('sale_charge_money', 'description_charge_money')) {
                $table->string('description_charge_money')->nullable();
            } else {
                // Si ya existe, modificarla
                $table->string('description_charge_money')->comment('Descripción del tipo de método')->nullable()->change();
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
        Schema::table('sale_charge_money', function (Blueprint $table) {
            if (Schema::hasColumn('sale_charge_money', 'description_charge_money')) {
                $table->string('description_charge_money')->comment('Descripción del tipo de método')->nullable()->change();
            }            
        });
    }
}
