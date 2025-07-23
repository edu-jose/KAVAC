<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


/**
 * @class UpdateFieldDependencyIdToProjectTrackingProjectsTable
 * @brief [descripción detallada]
 *
 * [cambio de tabla a la que hace referencia la llave foranea]
 *
 * @author [Miguel Narvaez] [mnarvaez@gmail.com]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldDependencyIdToProjectTrackingProjectsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('project_tracking_projects')) {
            Schema::table('project_tracking_projects', function (Blueprint $table) {
                if (Schema::hasColumn('project_tracking_projects', 'dependency_id')) {
                    $table->dropForeign('project_tracking_projects_dependency_id_foreign');
                    $table->dropColumn('dependency_id');
                }
            });
        }

        if (Schema::hasTable('project_tracking_projects')) {
            Schema::table('project_tracking_projects', function (Blueprint $table) {
                if (!Schema::hasColumn('project_tracking_projects', 'dependency_id')) {
                    $table->foreignId('dependency_id')->nullable()->references('id')->on('departments')->onDelete('cascade')->onUpdate('cascade')->comment('Dependencias');
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
        if (Schema::hasTable('project_tracking_projects')) {
            Schema::table('project_tracking_projects', function (Blueprint $table) {
                $table->dropForeign(['dependency_id']);
                $table->dropColumn('dependency_id');
            });
        }
    }
}
