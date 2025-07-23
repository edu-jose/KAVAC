<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddNewFieldsToPayrollStaffsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddNewFieldsToPayrollStaffsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('payroll_staffs')) {
            Schema::table('payroll_staffs', function (Blueprint $table) {
                if (
                    !Schema::hasColumn('payroll_staffs', 'payroll_age_group_id') &&
                    Schema::hasTable('payroll_age_groups')
                ) {
                    $table
                        ->foreignId('payroll_age_group_id')
                        ->nullable()
                        ->comment('Identificador del grupo etario.')
                        ->constrained()
                        ->onUpdate('cascade')
                        ->onDelete('restrict');
                }
                if (!Schema::hasColumn('payroll_staffs', 'locality_id')) {
                    $table
                        ->foreignId('locality_id')
                        ->nullable()
                        ->comment('Identificador de la localidad.')
                        ->constrained()
                        ->onUpdate('cascade')
                        ->onDelete('restrict');
                }
                if (!Schema::hasColumn('payroll_staffs', 'region_id')) {
                    $table
                        ->foreignId('region_id')
                        ->nullable()
                        ->comment('Identificador de la region.')
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
        if (Schema::hasTable('payroll_staffs')) {
            Schema::table('payroll_staffs', function (Blueprint $table) {
                if (Schema::hasColumn('payroll_staffs', 'payroll_age_group_id')) {
                    $table->dropForeign(['payroll_age_group_id']);
                    $table->dropColumn('payroll_age_group_id');
                }
                if (Schema::hasColumn('payroll_staffs', 'locality_id')) {
                    $table->dropForeign(['locality_id']);
                    $table->dropColumn('locality_id');
                }
                if (Schema::hasColumn('payroll_staffs', 'region_id')) {
                    $table->dropForeign(['region_id']);
                    $table->dropColumn('region_id');
                }
            });
        }
    }
}
