<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePurchasePrioritiesTable
 * @brief Estructura de datos para las prioridades de compra
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePurchasePrioritiesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('purchase_priorities')) {
            Schema::create('purchase_priorities', function (Blueprint $table) {
                $table->id();
                $table->string('name', 5)->unique()->comment('Nombre de la prioridad de compra');
                $table->text('description')->comment('Descripción de la prioridad de compra');
                $table->string('color', 10)->unique()->comment('Color de la prioridad de compra');
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
        Schema::dropIfExists('purchase_priorities');
    }
}
