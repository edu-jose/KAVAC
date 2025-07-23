<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddProjectableToProjectTrackingProjectsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddProjectableToProjectTrackingProjectsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasColumn('project_tracking_projects', 'projectable_id')
            && !Schema::hasColumn('project_tracking_projects', 'projectable_type')) {
            Schema::table('project_tracking_projects', function (Blueprint $table) {
                $table->unsignedBigInteger('projectable_id')
                    ->nullable()
                    ->default(null);
                $table->string('projectable_type')
                    ->nullable()
                    ->default(null);
            });
        }
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('project_tracking_projects', function (Blueprint $table) {
            $table->dropColumn('projectable_id');
            $table->dropColumn('projectable_type');
        });
    }
}
