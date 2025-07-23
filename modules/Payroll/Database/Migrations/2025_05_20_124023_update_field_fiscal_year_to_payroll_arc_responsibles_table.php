<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * @class UpdateFieldFiscalYearToPayrollArcResponsiblesTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldFiscalYearToPayrollArcResponsiblesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_arc_responsibles', function (Blueprint $table) {
            $table->integer('fiscal_year')->nullable();
        });

        $records = DB::table('payroll_arc_responsibles')->select('id', 'start_date')->get();

        foreach ($records as $record) {
            if ($record->start_date) {
                $year = date('Y', strtotime($record->start_date));
                DB::table('payroll_arc_responsibles')
                    ->where('id', $record->id)
                    ->update(['fiscal_year' => $year]);
            }
        }

        Schema::table('payroll_arc_responsibles', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date']);
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_arc_responsibles', function (Blueprint $table) {
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
        });

        DB::table('payroll_arc_responsibles')->update([
            'start_date' => DB::raw('(fiscal_year::text || \'-01-01\')::date')
        ]);
        
        Schema::table('payroll_arc_responsibles', function (Blueprint $table) {
            $table->dropColumn('fiscal_year');
        });
    }
}
