<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateCitizenServiceContactBooksTable
 * @brief Clase de migración para la tabla de agenda de contactos de la OAC
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateCitizenServiceContactBooksTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('citizen_service_contact_books', function (Blueprint $table) {
            $table->id();
            $table->string('identity_card')->comment('Número de cédula del contacto');
            $table->string('name')->comment('Nombre del contacto');
            $table->string('surname')->comment('Apellido del contacto');
            $table->string('position')->nullable()->comment('Cargo del contacto');
            $table->text('description')->nullable()->comment('Descripción del contacto');
            $table->foreignId('citizen_service_served_institution_id')
                ->constrained('citizen_service_served_institutions')
                ->onDelete('cascade')
                ->nullable()
                ->comment('Institución a la que pertenece el contacto');
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
        Schema::dropIfExists('citizen_service_contact_books');
    }
}
