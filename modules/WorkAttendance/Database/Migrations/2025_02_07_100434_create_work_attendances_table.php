<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateWorkAttendancesTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateWorkAttendancesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('work_attendances')) {
            Schema::create('work_attendances', function (Blueprint $table) {
                $table->id();
                $table->date('date_at')->comment('Fecha del registro');
                $table->time('entry_time')->nullable()->comment('Hora de entrada al trabajo');
                $table->time('exit_time')->nullable()->comment('Hora de salida del trabajo');
                $table->foreignId('payroll_staff_id')
                    ->nullable()
                    ->constrained('payroll_staffs')
                    ->references('id')
                    ->onUpdate('cascade')
                    ->onDelete('restrict');
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
        Schema::dropIfExists('work_attendances');
    }
}
