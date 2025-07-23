<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePayrollClassificationParameterTimeSheetOrdersTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollClassificationParameterTimeSheetOrdersTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_classification_parameter_time_sheet_orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payroll_classification_parameter_payroll_time_sheet_parameter_id')
                ->nullable()
                ->constrained('payroll_classification_parameter_payroll_time_sheet_parameter')
                ->onDelete('cascade');

            $table->foreignId("parameter_id")
                ->nullable()
                ->constrained('parameters')
                ->onDelete('cascade');

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
        Schema::dropIfExists('payroll_classification_parameter_time_sheet_orders');
    }
}
