<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsToProjectTrackingTasksTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsToProjectTrackingTasksTable extends Migration
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
                if (!Schema::hasColumn('project_tracking_tasks', 'percentage')) {
                    $table->string('percentage')
                        ->nullable()
                        ->comment('Porcentaje de la tarea');
                }
                if (!Schema::hasColumn('project_tracking_tasks', 'reviewer_id')) {
                    $table->foreignId('reviewer_id')
                        ->nullable()
                        ->constrained()
                        ->references('id')
                        ->on('project_tracking_activity_plan_teams')
                        ->onDelete('restrict')
                        ->onUpdate('cascade')
                        ->comment('Revisor de la tarea');
                }
                if (!Schema::hasColumn('project_tracking_tasks', 'approver_id')) {
                    $table->foreignId('approver_id')
                        ->nullable()
                        ->constrained()
                        ->references('id')
                        ->on('project_tracking_activity_plan_teams')
                        ->onDelete('restrict')
                        ->onUpdate('cascade')
                        ->comment('Aprovador de la tarea');
                }
                if (!Schema::hasColumn('project_tracking_tasks', 'is_private')) {
                    $table->boolean('is_private')
                        ->default(false)
                        ->comment('¿La tarea es privada?');
                }
                if (!Schema::hasColumn('project_tracking_tasks', 'payroll_staffs')) {
                    $table->json('payroll_staffs')
                        ->nullable()
                        ->comment('Si la tarea es privada, selecciona las personas que pueden verla');
                }
                if (!Schema::hasColumn('project_tracking_tasks', 'dependency_type_id')) {
                    $table->foreignId('dependency_type_id')
                        ->nullable()
                        ->constrained()
                        ->references('id')
                        ->on('project_tracking_dependencies_types')
                        ->onDelete('restrict')
                        ->onUpdate('cascade')
                        ->comment('Tipo de dependencia');
                }
                if (!Schema::hasColumn('project_tracking_tasks', 'task_type_id')) {
                    $table->foreignId('task_type_id')
                        ->nullable()
                        ->constrained()
                        ->references('id')
                        ->on('project_tracking_task_types')
                        ->onDelete('restrict')
                        ->onUpdate('cascade')
                        ->comment('Tipo de tarea');
                }
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
                if (Schema::hasColumn('project_tracking_tasks', 'percentage')) {
                    $table->dropColumn('percentage');
                }
                if (Schema::hasColumn('project_tracking_tasks', 'reviewer_id')) {
                    $table->dropForeign(['reviewer_id']);
                    $table->dropColumn('reviewer_id');
                }
                if (Schema::hasColumn('project_tracking_tasks', 'approver_id')) {
                    $table->dropForeign(['approver_id']);
                    $table->dropColumn('approver_id');
                }
                if (Schema::hasColumn('project_tracking_tasks', 'is_private')) {
                    $table->dropColumn('is_private');
                }
                if (Schema::hasColumn('project_tracking_tasks', 'payroll_staffs')) {
                    $table->dropColumn('payroll_staffs');
                }
                if (Schema::hasColumn('project_tracking_tasks', 'dependency_type_id')) {
                    $table->dropForeign(['dependency_type_id']);
                    $table->dropColumn('dependency_type_id');
                }
                if (Schema::hasColumn('project_tracking_tasks', 'task_type_id')) {
                    $table->dropForeign(['task_type_id']);
                    $table->dropColumn('task_type_id');
                }
                if (Schema::hasColumn('project_tracking_tasks', 'tags')) {
                    $table->dropColumn('tags');
                }
            });
        }
    }
}