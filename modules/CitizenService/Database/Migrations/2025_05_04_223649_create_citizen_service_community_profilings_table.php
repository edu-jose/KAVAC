<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateCitizenServiceCommunityProfilingsTable
 * @brief Migración para la creación de la tabla de caracterizaciones de comunidades
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateCitizenServiceCommunityProfilingsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('citizen_service_community_profilings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_communal_council')
                ->default(false)
                ->comment('Indica si la comunidad tiene consejo comunal');
            $table->boolean('is_commune')
                ->default(false)
                ->comment('Indica si la comunidad tiene comuna');
            $table->boolean('has_tic_committe')
                ->default(false)
                ->comment('Indica si la comunidad tiene comité de Ciencia y Tecnología');
            $table->string('tic_committe_name')
                ->nullable()
                ->comment('Nombre del comité de Ciencia y Tecnología');
            $table->longText('main_needs')
                ->comment('Necesidades principales de la comunidad');
            $table->foreignId('citizen_service_community_id')
                ->constrained('citizen_service_communities')
                ->onDelete('cascade')
                ->comment('Llave foránea que referencia a la comunidad');
            $table->timestamps();
            $table->softDeletes()
                ->comment('Fecha y hora en la que el registro fue eliminado');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('citizen_service_community_profilings');
    }
}
