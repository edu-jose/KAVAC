<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldOperationProcessToPayrollPositionsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * Migración para crear nuevo campo Tipo de proceso en la tabla de Cargo (payroll_positions)
 *
 * @author Fabián Palmera <fapalmera@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldProcessTypeToPayrollPositionsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('payroll_positions')) {
            if (!Schema::hasColumn('payroll_positions', 'process_type')) {
                Schema::table('payroll_positions', function (Blueprint $table) {
                    $table->string('process_type')->nullable()
                    ->comment('Tipo de proceso: Apoyo, Operación');
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
        if (Schema::hasTable('payroll_positions')) {
            if (Schema::hasColumn('payroll_positions', 'process_type')) {
                Schema::table('payroll_positions', function (Blueprint $table) {
                    $table->dropColumn('process_type');
                });
            }
        }
    }
}
