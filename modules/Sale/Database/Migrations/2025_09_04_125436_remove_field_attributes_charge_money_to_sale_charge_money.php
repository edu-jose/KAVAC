<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class RemoveFieldAttributesChargeMoney
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Tsu. Miguel Narvaez <mnarvaez@cenditel.gob.ve> | <miguelnarvaez31@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class RemoveFieldAttributesChargeMoneyToSaleChargeMoney extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_charge_money', function (Blueprint $table) {
            if (Schema::hasColumn('sale_charge_money', 'attributes_charge_money')) {
                $table->dropColumn('attributes_charge_money');
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
            if (!Schema::hasColumn('sale_charge_money', 'attributes_charge_money')) {
                $table->json('attributes_charge_money')->nullable()->comment('Atributos del tipo de método');
            }
        });

    }
}
