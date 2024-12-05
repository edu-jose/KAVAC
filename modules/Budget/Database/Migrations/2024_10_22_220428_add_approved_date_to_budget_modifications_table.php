<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Budget\Models\BudgetModification;

/**
 * @class AddApprovedDateToBudgetModificationsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddApprovedDateToBudgetModificationsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('budget_modifications', function (Blueprint $table) {
            $table->date('approved_date')
                ->nullable()
                ->comment('Fecha de aprobración de las modificaciones');
        });

        BudgetModification::where('status', 'AP')
            ->update(['approved_date' => DB::raw('approved_at')]);
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('budget_modifications', function (Blueprint $table) {
            if (Schema::hasColumn('budget_modifications', 'approved_date')) {
                $table->dropColumn('approved_date');
            }
        });
    }
}
