<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldsToPayrollSalaryAdjustmentsTable
 * @brief Ejecución del proceso de migración para la estructura de base de datos
 *
 * Se agregan los campos start_date, end_date y salary_values a la tabla payroll_salary_adjustments
 *
 * @author Fabián Palmera <fapalmera@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldsToPayrollSalaryAdjustmentsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('payroll_salary_adjustments')) {
            if (!Schema::hasColumn('payroll_salary_adjustments', 'start_increase_date')) {
                Schema::table('payroll_salary_adjustments', function (Blueprint $table) {
                    $table->date('start_increase_date')->nullable()->comment('Fecha de entrada en vigencia del ajuste salarial');
                });
            }

            if (!Schema::hasColumn('payroll_salary_adjustments', 'end_increase_date')) {
                Schema::table('payroll_salary_adjustments', function (Blueprint $table) {
                    $table->date('end_increase_date')->nullable()->comment('Fecha de culminación del ajuste salarial');
                });
            }

            if (!Schema::hasColumn('payroll_salary_adjustments', 'salary_values')) {
                Schema::table('payroll_salary_adjustments', function (Blueprint $table) {
                    $table->longText('salary_values')->nullable()
                        ->comment('Valores asignados al tabulador de nómina para el ajuste salarial');
                });
            }
        }
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('payroll_salary_adjustments')) {
            if (Schema::hasColumn('payroll_salary_adjustments', 'start_increase_date')) {
                Schema::table('payroll_salary_adjustments', function (Blueprint $table) {
                    $table->dropColumn('start_increase_date');
                });
            }

            if (Schema::hasColumn('payroll_salary_adjustments', 'end_increase_date')) {
                Schema::table('payroll_salary_adjustments', function (Blueprint $table) {
                    $table->dropColumn('end_increase_date');
                });
            }

            if (Schema::hasColumn('payroll_salary_adjustments', 'salary_values')) {
                Schema::table('payroll_salary_adjustments', function (Blueprint $table) {
                    $table->dropColumn('salary_values');
                });
            }
        }
    }
}
