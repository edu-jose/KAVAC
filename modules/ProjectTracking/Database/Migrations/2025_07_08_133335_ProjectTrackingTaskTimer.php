<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class ProjectTrackingTaskTimer
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingTaskTimer extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('project_tracking_task_timers')) {
            Schema::create('project_tracking_task_timers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('project_tracking_task_id');
                $table->datetime('start_time');
                $table->datetime('end_time')->nullable();
                $table->unsignedBigInteger('initial_status_id')->nullable();
                $table->unsignedBigInteger('final_status_id')->nullable();
                $table->string('time_spent')->nullable();
                $table->foreign('project_tracking_task_id')->references('id')->on('project_tracking_tasks');
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
        Schema::dropIfExists('project_tracking_task_timers');
    }
}
