<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsHistoryToPayrollConceptsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsHistoryToPayrollConceptsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('payroll_concepts')) {
            Schema::table('payroll_concepts', function (Blueprint $table) {
                if (!Schema::hasColumn('payroll_concepts', 'formula_history')) {
                    $table->text('formula_history')->nullable()
                        ->comment('Historial de la fórmula empleada para el cálculo de incidencia');
                };

                if (!Schema::hasColumn('payroll_concepts', 'formula_show_history')) {
                    $table->text('formula_show_history')->nullable()
                        ->comment('Historial de la fórmula empleada para el cálculo de incidencia');
                };
            });
        }
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('payroll_concepts')) {
            Schema::table('payroll_concepts', function (Blueprint $table) {
                if (Schema::hasColumn('payroll_concepts', 'formula_history')) {
                    $table->dropColumn(['formula_history']);
                };

                if (Schema::hasColumn('payroll_concepts', 'formula_show_history')) {
                    if (Schema::hasColumn('payroll_concepts', 'formula_show_history')) {
                        $table->dropColumn(['formula_show_history']);
                    };
                };
            });
        }
    }
}
