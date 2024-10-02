<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldTimeSheetColumnsToPayrollTimeSheetsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldTimeSheetColumnsToPayrollTimeSheetsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_time_sheets', function (Blueprint $table) {
            $table
                ->json('time_sheet_columns')
                ->comment('Columnas de la hoja de tiempo')
                ->nullable();
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_time_sheets', function (Blueprint $table) {
            $table->dropColumn('time_sheet_columns');
        });
    }
}
