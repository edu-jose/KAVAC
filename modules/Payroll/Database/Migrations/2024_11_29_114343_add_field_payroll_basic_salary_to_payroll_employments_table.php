<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldPayrollBasicSalaryToPayrollEmploymentsTable
 * @brief Agrega columna para mostrar el salario base de un trabajador
 *
 * Agregando columna payroll_basic_salary para mostrar el salario base de un trabajador
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldPayrollBasicSalaryToPayrollEmploymentsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_employments', function (Blueprint $table) {
            if (!Schema::hasColumn('payroll_employments', 'payroll_basic_salary')) {
                $table->string('payroll_basic_salary')->nullable();
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
        Schema::table('payroll_employments', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_employments', 'payroll_basic_salary')) {
                $table->dropColumn('payroll_basic_salary');
            }
        });
    }
}
