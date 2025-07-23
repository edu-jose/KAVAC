<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateCitizenServiceTransactionTypeMethodsTable
 * @brief Agrega la tabla de tipo de transacción 
 *
 * @author Tsu. Miguel Narvaez
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateCitizenServiceTransactionTypesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('citizen_service_transaction_types')) {
            Schema::create('citizen_service_transaction_types', function (Blueprint $table) {
                $table->id()->comment('Identificador único del registro');

                $table->string('name')->comment('Nombre del tipo de transaccion');
                $table->string('description')->nullable()->comment('Descripción del tipo de transaccion');

                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            });
        };
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('citizen_service_transaction_types');
    }
}
