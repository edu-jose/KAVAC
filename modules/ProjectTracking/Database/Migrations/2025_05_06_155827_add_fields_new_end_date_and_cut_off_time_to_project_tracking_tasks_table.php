<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsNewEndDateAndCutOffTimeToProjectTrackingTasksTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsNewEndDateAndCutOffTimeToProjectTrackingTasksTable extends Migration
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
                $table->date('new_end_date')
                    ->comment('Nueva fecha de culminación')
                    ->nullable();
                $table->string('cut_off_time', 8)
                    ->comment('Hora límite')
                    ->nullable();
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
                if (Schema::hasColumn('project_tracking_tasks', 'new_end_date')) {
                    $table->dropColumn('new_end_date');
                }
                if (Schema::hasColumn('project_tracking_tasks', 'cut_off_time')) {
                    $table->dropColumn('cut_off_time');
                }
            });
        }
    }
}
