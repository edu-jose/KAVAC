<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateProjectTrackingActivityTypesTable
 * @brief  Ejecuta el proceso de migración de los tipos de actividades
 *
 *
 *
 * @author Mauricio Araujo <araujoperezme20@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateProjectTrackingActivityTypesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('project_tracking_activity_types')) {
            Schema::create('project_tracking_activity_types', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100)->comment('Nombre del tipo de actividad');
                $table->string('description', 200)->nullable()->comment('Descripción del tipo de actividad');
                $table->string('color', 100)->comment('Color del tipo de actividad');


                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            });
        }
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_tracking_activity_types');
    }
}
