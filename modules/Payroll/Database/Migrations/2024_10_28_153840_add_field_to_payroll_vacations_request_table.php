<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldToPayrollVacationsRequestTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldToPayrollVacationsRequestTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_vacation_requests', function (Blueprint $table) {
            if(!Schema::hasColumn('payroll_vacation_requests', 'is_from_xlsx_file')){
                $table->boolean('is_from_xlsx_file')->default(false);
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
        Schema::table('', function (Blueprint $table) {
            if(Schema::hasColumn('payroll_vacation_requests', 'is_from_xlsx_file')){
                $table->dropColumn('is_from_xlsx_file');
            }
        });
    }
}
