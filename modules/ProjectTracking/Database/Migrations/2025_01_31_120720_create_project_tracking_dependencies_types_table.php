<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateProjectTrackingDependenciesTypeTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [Mauricio Araujo] [maraujo@cenditel.gob.ve]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateProjectTrackingDependenciesTypesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_tracking_dependencies_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('Nombre del tipo de dependencia');
            $table->string('description', 500)->comment('Descripción del tipo de dependencia');
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
        Schema::dropIfExists('project_tracking_dependencies_types');
    }
}
