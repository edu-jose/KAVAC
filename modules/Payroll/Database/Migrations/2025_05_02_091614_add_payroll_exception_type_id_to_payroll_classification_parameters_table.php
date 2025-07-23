<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddPayrollExceptionTypeIdToPayrollClassificationParametersTable
 * @brief Agrega llave foranea payroll_exception_type_id en la tabla payroll_classification_parameters
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddPayrollExceptionTypeIdToPayrollClassificationParametersTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_classification_parameters', function (Blueprint $table) {
            $table->foreignId('payroll_exception_type_id')
                ->nullable()
                ->constrained('payroll_exception_types')
                ->onDelete('cascade');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_classification_parameters', function (Blueprint $table) {
            $table->dropForeign(['payroll_exception_type_id']);
            $table->dropColumn('payroll_exception_type_id');
        });
    }
}
