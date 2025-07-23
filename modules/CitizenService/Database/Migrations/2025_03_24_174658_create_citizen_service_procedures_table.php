<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateCitizenServiceProceduresTable
 * @brief Establece la estructura de la tabla de trámites del módulo de la OAC
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateCitizenServiceProceduresTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('citizen_service_procedures', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nombre del trámite');
            $table->text('description')->nullable()->comment('Descripción del trámite');
            $table->foreignId('citizen_service_procedure_type_id')
                  ->constrained('citizen_service_procedure_types')
                  ->onDelete('restrict')
                  ->onUpdate('cascade')
                  ->comment('Tipo de trámite');
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
        Schema::dropIfExists('citizen_service_procedures');
    }
}
