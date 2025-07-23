<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateWorkAttendanceExternalActivityStaffsTable
 * @brief Establece la estructura para la tabla work_attendance_external_activity_staffs
 *
 * @author Ing. Roldan Vargas <roldandvg@gmail.com> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateWorkAttendanceExternalActivityStaffsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_attendance_external_activity_staffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_staff_id')
                    ->constrained('payroll_staffs')
                    ->references('id')
                    ->onUpdate('cascade')
                    ->onDelete('restrict');
            $table->foreignId('work_attendance_external_activity_id')
                    ->constrained('work_attendance_external_activities')
                    ->references('id')
                    ->onUpdate('cascade')
                    ->onDelete('restrict');
            $table->foreignId('work_attendance_id')
                    ->constrained('work_attendances')
                    ->references('id')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
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
        Schema::dropIfExists('work_attendance_external_activity_staffs');
    }
}
