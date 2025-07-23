<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateCitizenServiceCommunalCouncilsTable
 * @brief Migración para la creación de la tabla de consejos comunales
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateCitizenServiceCommunalCouncilsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('citizen_service_communal_councils', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nombre del consejo comunal');
            $table->foreignId('citizen_service_community_profiling_id')
                ->constrained('citizen_service_community_profilings')
                ->onDelete('cascade')
                ->comment('Llave foránea que referencia a la caracterización de la comunidad');
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
        Schema::dropIfExists('citizen_service_communal_councils');
    }
}
