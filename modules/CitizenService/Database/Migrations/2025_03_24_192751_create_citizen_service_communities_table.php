<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateCitizenServiceCommunitiesTable
 * @brief Establece la estructura de la tabla de comunidades
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateCitizenServiceCommunitiesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('citizen_service_communities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nombre de la comunidad');
            $table->text('location')->nullable()->comment('Ubicación de la comunidad');
            $table->text('population')->nullable()->comment('Población de la comunidad');
            $table->foreignId('parish_id')
                  ->constrained('parishes')
                  ->onDelete('restrict')
                  ->onUpdate('cascade')
                  ->comment('Parroquia');
            $table->foreignId('city_id')
                  ->constrained('cities')
                  ->onDelete('restrict')
                  ->onUpdate('cascade')
                  ->comment('Ciudad');
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
        Schema::dropIfExists('citizen_service_communities');
    }
}
