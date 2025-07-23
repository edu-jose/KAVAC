<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateCitizenServiceCommunityInstitutionsTable
 * @brief Migración para la creación de la tabla de instituciones de las comunidates
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateCitizenServiceCommunityInstitutionsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('citizen_service_community_institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nombre de la institución');
            $table->string('rif', 10)->comment('Dirección de la institución');
            $table->foreignId('institution_type_id')
                ->constrained('institution_types')
                ->onDelete('cascade')
                ->comment('Llave foránea que referencia al tipo de institución');
            $table->foreignId('institution_sector_id')
                ->constrained('institution_sectors')
                ->onDelete('cascade')
                ->comment('Llave foránea que referencia al sector de la institución');
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
        Schema::dropIfExists('citizen_service_community_institutions');
    }
}
