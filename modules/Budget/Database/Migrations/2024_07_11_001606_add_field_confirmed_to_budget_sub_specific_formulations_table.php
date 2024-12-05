<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Budget\Models\BudgetSubSpecificFormulation;

/**
 * @class AddFieldConfirmedToBudgetSubSpecificFormulationsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldConfirmedToBudgetSubSpecificFormulationsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('budget_sub_specific_formulations', function (Blueprint $table) {
            $table->boolean('confirmed')->default(false)->comment('Indica si la formulación ha sido confirmada');
        });

        BudgetSubSpecificFormulation::query()
            ->where('assigned', true)
            ->update(['confirmed' => true]);
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('budget_sub_specific_formulations', function (Blueprint $table) {
            $table->dropColumn('confirmed');
        });
    }
}
