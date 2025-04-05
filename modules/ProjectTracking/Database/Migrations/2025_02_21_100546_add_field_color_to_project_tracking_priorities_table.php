<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldColorToProjectTrackingPrioritiesTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldColorToProjectTrackingPrioritiesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('project_tracking_priorities')) {
            Schema::table('project_tracking_priorities', function (Blueprint $table) {
                if (!Schema::hasColumn('project_tracking_priorities', 'color')) {
                    $table->string('color')->nullable()->comment(
                        'Color de la prioridad'
                    );
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
        if (Schema::hasTable('project_tracking_priorities')) {
            Schema::table('project_tracking_priorities', function (Blueprint $table) {
                $table->dropColumn('color');
            });
        }
    }

}
