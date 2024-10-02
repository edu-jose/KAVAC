<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldDeletedAtToAssetRequestDeliveriesTable
 * @brief Agrega el campo deleted_at a la tabla asset_request_deliveries
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldDeletedAtToAssetRequestDeliveriesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('asset_request_deliveries', function (Blueprint $table) {
            if (!Schema::hasColumn('asset_request_deliveries', 'deleted_at')) {
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            }
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('asset_request_deliveries', function (Blueprint $table) {
            if (Schema::hasColumn('asset_request_deliveries', 'deleted_at')) {
                $table->dropColumn('deleted_at');
            }
        });
    }
}
