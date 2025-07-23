<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddNewFieldsToPayrollEmploymentsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddNewFieldsToPayrollEmploymentsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('payroll_employments')) {
            Schema::table('payroll_employments', function (Blueprint $table) {
                if (!Schema::hasColumn('payroll_employments', 'workers_union')) {
                    $table
                        ->boolean('workers_union')
                        ->default(false)
                        ->nullable()
                        ->comment('Indica si el trabajador pertenece al sindicato de trabajadore.');
                }
                if (!Schema::hasColumn('payroll_employments', 'savings_fund')) {
                    $table
                        ->boolean('savings_fund')
                        ->default(false)
                        ->nullable()
                        ->comment('Indica si es fondo de ahorro.');

                }
                if (!Schema::hasColumn('payroll_employments', 'payroll_salary_tabulator_id')) {
                    $table
                        ->foreignId('payroll_salary_tabulator_id')
                        ->nullable()
                        ->comment('Identificador del salario basico. (tabulador de nomina)')
                        ->constrained()
                        ->onUpdate('cascade')
                        ->onDelete('restrict');
                }
                if (!Schema::hasColumn('payroll_employments', 'payroll_payment_type_id')) {
                    $table
                        ->foreignId('payroll_payment_type_id')
                        ->nullable()
                        ->comment('Identificador de la frecuencia de nomina. (tipo de nomina)')
                        ->constrained()
                        ->onUpdate('cascade')
                        ->onDelete('restrict');
                }
                if (
                    !Schema::hasColumn('payroll_employments', 'payroll_seniority_id') &&
                    Schema::hasTable('payroll_seniorities')
                ) {
                    $table
                        ->foreignId('payroll_seniority_id')
                        ->nullable()
                        ->comment('Identificador de la antiguedad.')
                        ->constrained()
                        ->onUpdate('cascade')
                        ->onDelete('restrict');
                }
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
        if (Schema::hasTable('payroll_employments')) {
            Schema::table('payroll_employments', function (Blueprint $table) {
                if (Schema::hasColumn('payroll_employments', 'workers_union')) {
                    $table->dropColumn('workers_union');
                }
                if (Schema::hasColumn('payroll_employments', 'savings_fund')) {
                    $table->dropColumn('savings_fund');
                }
                if (Schema::hasColumn('payroll_employments', 'payroll_salary_tabulator_id')) {
                    $table->dropForeign(['payroll_salary_tabulator_id']);
                    $table->dropColumn('payroll_salary_tabulator_id');
                }
                if (Schema::hasColumn('payroll_employments', 'payroll_payment_type_id')) {
                    $table->dropForeign(['payroll_payment_type_id']);
                    $table->dropColumn('payroll_payment_type_id');
                }
                if (Schema::hasColumn('payroll_employments', 'payroll_seniority_id')) {
                    $table->dropForeign(['payroll_seniority_id']);
                    $table->dropColumn('payroll_seniority_id');
                }
            });
        }
    }
}
