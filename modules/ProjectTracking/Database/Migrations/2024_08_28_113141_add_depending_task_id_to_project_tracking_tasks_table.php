<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddDependingTaskIdToProjectTrackingTasksTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddDependingTaskIdToProjectTrackingTasksTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('project_tracking_tasks')) {
            Schema::table('project_tracking_tasks', function (Blueprint $table) {
                $table->foreignId('depending_task_id')
                    ->nullable()
                    ->references('id')
                    ->on('project_tracking_tasks')
                    ->onDelete('cascade')
                    ->onUpdate('cascade')
                    ->comment('Depende de');
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
        if (Schema::hasTable('project_tracking_tasks')) {
            Schema::table('project_tracking_tasks', function (Blueprint $table) {
                $table->dropForeign(['depending_task_id']);
                $table->dropColumn('depending_task_id');
            });
        }
    }
}
