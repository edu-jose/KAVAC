<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePayrollSenioritiesTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollSenioritiesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_seniorities', function (Blueprint $table) {
            $table->id();
            
            $table->string('name', 100)->comment('Nombre de la antiguedad laboral');
            $table->string('code', 100)->unique()->comment('Código de la antiguedad laboral');
            $table->string('description', 200)->nullable()->comment('Descripción de la antiguedad laboral');
            $table->integer('minimum_age')->nullable()->comment('Edad minima');
            $table->integer('maximum_age')->nullable()->comment('Edad maxima');
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
        Schema::dropIfExists('payroll_seniorities');
    }
}
