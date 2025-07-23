<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePayrollAgeGroupsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollAgeGroupsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_age_groups', function (Blueprint $table) {
            $table->id();
            
            $table->string('name', 100)->comment('Nombre del grupo etario');
            $table->string('code', 100)->unique()->comment('Código del grupo etario');
            $table->string('description', 200)->nullable()->comment('Descripción del grupo etario');
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
        Schema::dropIfExists('payroll_age_groups');
    }
}
