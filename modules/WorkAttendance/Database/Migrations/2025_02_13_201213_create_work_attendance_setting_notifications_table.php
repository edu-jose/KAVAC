<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateWorkAttendanceSettingNotificationsTable
 * @brief Crea la estructura de la tabla para la configuración de notificaciones
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateWorkAttendanceSettingNotificationsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('work_attendance_setting_notifications')) {
            Schema::create('work_attendance_setting_notifications', function (Blueprint $table) {
                $table->id();
                $table->boolean('notify')->default(true)->comment(
                    'Indica si se notifica al encargado o supervisor configurado para recibir reportes de la asistencia del personal'
                );
                $table->char('periodicity', 1)->default('S')->comment(
                    'Indica la periodicidad con la que se notificará al encargado o supervisor configurado ' .
                    'para recibir reportes de la asistencia del personal. Las opciones disponibles son: ' .
                    'D = Diario, S = Semanal, Q = Quincenal, M = Mensual, B = Bimestral, T = Trimestral, A = Anual'
                );
                $table->foreignId('position_id')
                      ->constrained('payroll_positions')
                      ->references('id')
                      ->on('payroll_positions')
                      ->onUpdate('cascade')
                      ->onDelete('cascade');
                $table->foreignId('payroll_employment_id')
                      ->constrained('payroll_employments')
                      ->references('id')
                      ->on('payroll_employments')
                      ->onUpdate('cascade')
                      ->onDelete('cascade');
                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
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
        Schema::dropIfExists('work_attendance_setting_notifications');
    }
}
