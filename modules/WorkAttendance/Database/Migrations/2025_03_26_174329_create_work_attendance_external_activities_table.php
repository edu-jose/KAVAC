<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateWorkAttendanceExternalActivitiesTable
 * @brief Establece la estructura de la tabla para el registro de actividades externas
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateWorkAttendanceExternalActivitiesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_attendance_external_activities', function (Blueprint $table) {
            $table->id();
            $table->dateTime('start_at')->comment('Fecha y hora de inicio de la actividad');
            $table->dateTime('end_at')->comment('Fecha y hora de finalización de la actividad');
            $table->longText('reason')->comment('Motivo de la actividad');
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
        Schema::dropIfExists('work_attendance_external_activities');
    }
}
