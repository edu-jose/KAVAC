<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateWarehouseRequestsUnifiedTable
 * @brief Migración para crear la tabla unificada de solicitudes de almacén.
 *
 * Esta migración define la estructura de la tabla `warehouse_requests_unified`,
 * que permite almacenar referencias polimórficas a solicitudes internas
 * (WarehouseRequest) y externas (WarehouseExternalRequest). La tabla utiliza
 * los campos `requestable_id` y `requestable_type` para vincular dinámicamente
 * el modelo correspondiente.
 *
 * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateWarehouseRequestsUnifiedTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouse_requests_unified', function (Blueprint $table) {
            $table->id();

            $table->morphs('requestable');

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
        Schema::dropIfExists('warehouse_requests_unified');
    }
}
