<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateWorkAttendanceSchedulesTable
 * @brief Crea la estructura de la tabla para la configuración de horarios laborales
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateWorkAttendanceSchedulesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_attendance_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('day_name')->comment('Día de la semana');
            $table->integer('day_number')->comment('Número del día');
            $table->string('description')->nullable()->comment('Descripción');
            $table->time('start_time')->comment('Hora de inicio');
            $table->time('end_time')->comment('Hora de finalización');
            $table->time('break_time')->nullable()->comment('Tiempo de descanso');
            $table->time('lunch_time')->nullable()->comment('Tiempo de almuerzo');
            $table->integer('work_time')->comment('Tiempo de trabajo en minutos');
            $table->boolean('active')->default(true)->comment('Horario activo');
            $table->boolean('is_extended')->default(false)->comment('Horario extendido que supera las 24 horas');
            $table->timestamps();
            $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            $table->unique(['day_name', 'day_number', 'start_time', 'end_time']);
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_attendance_schedules');
    }
}
