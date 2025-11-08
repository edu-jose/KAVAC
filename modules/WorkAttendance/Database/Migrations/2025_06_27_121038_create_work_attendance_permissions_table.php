<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateWorkAttendancePermissionsTable
 * @brief Crea la tabla work_attendance_permissions
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateWorkAttendancePermissionsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_attendance_permissions', function (Blueprint $table) {
            $table->id();
            $table->date('start_date_at')
                ->comment('Fecha de inicio del permiso');
            $table->time('start_time_at')->nullable()
                ->comment('Hora de inicio del permiso en formato 12 horas (ej. 04:00 PM)');
            $table->date('end_date_at')
                ->comment('Fecha de finalización del permiso');
            $table->time('end_time_at')->nullable()
                ->comment('Hora de finalización del permiso en formato 12 horas (ej. 04:00 PM)');
            $table->text('reason')
                ->comment('Motivo del permiso');
            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending')
                ->comment('Estado del permiso: pendiente, aprobado o rechazado');
            $table->text('comments')
                ->nullable()
                ->comment('Comentarios adicionales sobre el permiso');
            $table->foreignId('payroll_staff_id')
                ->constrained('payroll_staffs')
                ->onDelete('cascade')
                ->comment('ID del personal al que se le otorga el permiso');
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
        Schema::dropIfExists('work_attendance_permissions');
    }
}
