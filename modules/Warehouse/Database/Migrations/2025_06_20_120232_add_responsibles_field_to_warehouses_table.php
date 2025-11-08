<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddResponsiblesFieldToWarehousesTable
 * @brief Agrega el campo responsable a la tabla de almacenes
 *
 * Clase que agrega el campo responsable a la tabla de almacenes
 *
 * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddResponsiblesFieldToWarehousesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('warehouses', 'responsibles')) {
            Schema::table('warehouses', function (Blueprint $table) {
                $table->json('responsibles')
                    ->nullable()
                    ->default(null);
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
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn('responsibles');
        });
    }
}
