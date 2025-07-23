<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateProjectTrackingTaskCommentsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateProjectTrackingTaskCommentsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_tracking_task_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_comment_id')->references('id')->on('project_tracking_tasks')
                ->comment('Identificador de la actividad que se comenta');
            $table->foreignId('user_id')->references('id')->on('users')
                ->comment('Identificador del usuario que comenta');
            $table->longText('comment')->comment('Comentario');

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
        Schema::table('project_tracking_task_comments', function (Blueprint $table) {
            if (Schema::hasColumn('project_tracking_task_comments', 'task_comment_id')) {
                $table->dropForeign(['task_comment_id']);
                $table->dropColumn('task_comment_id');
            }
            if (Schema::hasColumn('project_tracking_task_comments', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    
        Schema::dropIfExists('project_tracking_task_comments');
    }
}
