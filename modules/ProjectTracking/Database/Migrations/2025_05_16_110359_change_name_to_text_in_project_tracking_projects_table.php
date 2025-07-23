<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class ChangeNameToTextInProjectTrackingProjectsTable
 * Cambia el tipo de datos de la columna "name" de string a text en la tabla project_tracking_projects
 *
 * Cambia el tipo de datos de la columna "name" de string a text en la tabla project_tracking_projects
 *
 * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ChangeNameToTextInProjectTrackingProjectsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_tracking_projects', function (Blueprint $table) {
            $table->text('name')->change();
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_tracking_projects', function (Blueprint $table) {
            $table->string('name')->change();
        });
    }
}
