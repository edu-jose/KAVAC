<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePayrollClassificationParameterPayrollTimeSheetParameterTable
 * @brief Tabla Pivote entre los parámetros de clasificación de parametros y los parámetros de hoja de tiempo
 * con su respectivo Orden de evaluación de parámetros para la resta de excedentes
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollClassificationParameterPayrollTimeSheetParameterTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_classification_parameter_payroll_time_sheet_parameter', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('payroll_classification_parameter_id')
                ->onDelete('cascade')
                ->comment('Llave foránea que referencia a la tabla de parámetros de clasificación de nómina');

            $table->foreignId('payroll_time_sheet_parameter_id')
                ->onDelete('cascade')
                ->comment('Llave foránea que referencia a la tabla de parámetros de hoja de tiempo');

            $table->integer('order')->nullable()->comment('Orden de los parámetros de clasificación');
            $table->timestamps();
            $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payroll_classification_parameter_payroll_time_sheet_parameter');
    }
}
