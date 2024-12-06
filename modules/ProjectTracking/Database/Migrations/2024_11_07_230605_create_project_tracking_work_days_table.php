<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateProjectTrackingWorkDaysTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateProjectTrackingWorkDaysTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('project_tracking_work_days')) {
            Schema::create('project_tracking_work_days', function (Blueprint $table) {
                $table->id();
                $table->string('from')
                    ->comment('Fecha de inicio');
                $table->string('to')
                    ->comment('Fecha de culminación');
                $table->longText('working_days')
                    ->comment('Dias de trabajo');
                $table->decimal('working_hours')
                    ->default(0)
                    ->comment('Horas de trabajo');
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
        Schema::dropIfExists('project_tracking_work_days');
    }
}
