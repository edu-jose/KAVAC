<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePurchaseActivityTypesTable
 * @brief Clase que crea la tabla de tipos de actividades de compra
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePurchaseActivityTypesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('purchase_activity_types')) {
            Schema::create('purchase_activity_types', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100)->unique()
                      ->comment('Nombre del tipo de actividad de compra');
                $table->text('description')->nullable()
                      ->comment('Descripción del tipo de actividad de compra');
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
        if (Schema::hasTable('purchase_activity_types')) {
            Schema::dropIfExists('purchase_activity_types');
        }
    }
}
