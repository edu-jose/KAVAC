<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateWorkAttendanceCustomSchedulesTable
 * @brief Establece la estructura para la tabla work_attendance_custom_schedules
 *
 * @author Ing. Roldan Vargas <roldandvg@gmail.com> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateWorkAttendanceCustomSchedulesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_attendance_custom_schedules', function (Blueprint $table) {
            $table->id();
            $table->date('start_date_at')
                  ->comment('Fecha en la que se asigna el horario personalizado');
            $table->date('end_date_at')->nullable()
                  ->comment('Fecha en la que finaliza el horario personalizado');
            $table->char('custom_schedule_type', 1)
                  ->default('O')
                  ->comment('Tipo de horario personalizado: O=Otro, D=Docente, E=Estudiante');
            $table->text('reason')
                  ->comment('Motivo por el cual se asigna un horario personalizado');
            $table->boolean('active')
                  ->default(true)
                  ->comment('Indica si el registro está activo');
            $table->boolean('is_teacher')
                  ->default(false)
                  ->comment('Indica si el horario es para un docente');
            $table->boolean('is_student')
                  ->default(false)
                  ->comment('Indica si el horario es para un estudiante');
            $table->boolean('is_other')
                  ->default(false)
                  ->comment('Indica si el horario es para un usuario diferente a docente o estudiante');
            $table->json('schedule')
                  ->comment('Horario personalizado asignado');
            $table->foreignId('payroll_staff_id')
                  ->constrained('payroll_staffs')
                  ->references('id')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
            $table->foreignId('authorized_payroll_staff_id')
                  ->constrained('payroll_staffs')
                  ->references('id')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
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
        Schema::dropIfExists('work_attendance_custom_schedules');
    }
}
