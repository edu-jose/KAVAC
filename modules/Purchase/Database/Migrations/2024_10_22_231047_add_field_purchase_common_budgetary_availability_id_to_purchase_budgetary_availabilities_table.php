<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldPurchaseCommonBudgetaryAvailabilityIdToPurchaseBudgetaryAvailabilitiesTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author  Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldPurchaseCommonBudgetaryAvailabilityIdToPurchaseBudgetaryAvailabilitiesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_budgetary_availabilities', function (Blueprint $table) {
            $table->foreignId('purchase_common_budgetary_availability_id')
                ->nullable()->constrained()
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
        Schema::table('purchase_budgetary_availabilities', function (Blueprint $table) {
            $table->dropForeign(['purchase_common_budgetary_availability_id']);
            $table->dropColumn(['purchase_common_budgetary_availability_id']);
        });
    }
}
