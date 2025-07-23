<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldDescriptionToProjectTrackingDependenciesTypesTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldDescriptionToProjectTrackingDependenciesTypesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('project_tracking_dependencies_types')) {
            Schema::table('project_tracking_dependencies_types', function (Blueprint $table) {
                $table->text('description')->nullable()->comment('Descripción del tipo de dependencia')->change();
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
        if (Schema::hasTable('project_tracking_dependencies_types')) {
            Schema::table('project_tracking_dependencies_types', function (Blueprint $table) {
                $table->string('description', 500)->comment('Descripción del tipo de dependencia')->change();
            });
        }
    }
}
