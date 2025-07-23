<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateCitizenServiceRequestTeamsTable
 * @brief Crea la tabla citizen_service_request_teams
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateCitizenServiceRequestTeamsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('citizen_service_request_teams', function (Blueprint $table) {
            $table->id();
            $table->date('start_at')->comment('Fecha de inicio');
            $table->text('tasks')->comment('Tareas a realizar');
            $table->foreignId('payroll_employee_id')
                  ->constrained('payroll_employments')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            $table->foreignId('citizen_service_request_id')
                  ->constrained('citizen_service_requests')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
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
        Schema::dropIfExists('citizen_service_request_teams');
    }
}
