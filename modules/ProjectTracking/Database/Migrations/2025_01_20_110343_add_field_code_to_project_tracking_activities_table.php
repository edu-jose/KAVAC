<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


/**
 * @class AddFieldCodeToProjectTrackingActivitiesTable
 * @brief Agregando columna code a la tabla project_tracking_activities
 *
 * Agregando columna code a la tabla project_tracking_activities
 *
 * @author Mauricio Araujo araujoperezme20@gmail.com
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldCodeToProjectTrackingActivitiesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
       if (Schema::hasTable('project_tracking_activities')) {
            Schema::table('project_tracking_activities', function (Blueprint $table) {
                if (!Schema::hasColumn('project_tracking_activities', 'code')) {
                    $table->string('code')->nullable()->comment(
                        'Código asociado a la actividad'
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
        if (Schema::hasTable('project_tracking_activities')) {
            Schema::table('project_tracking_activities', function (Blueprint $table) {
                $table->dropColumn('code');
            });
        }
    }
}
