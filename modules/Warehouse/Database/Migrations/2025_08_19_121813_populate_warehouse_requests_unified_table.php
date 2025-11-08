<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Modules\Warehouse\Models\WarehouseExternalRequest;
use Modules\Warehouse\Models\WarehouseRequest;

class PopulateWarehouseRequestsUnifiedTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('warehouse_requests_unified')) {
            WarehouseRequest::all()->each(function (WarehouseRequest $request): void {
                DB::table('warehouse_requests_unified')->insert([
                    'requestable_id'   => $request->id,
                    'requestable_type' => WarehouseRequest::class,
                    'created_at'       => $request->created_at,
                    'updated_at'       => $request->updated_at,
                ]);
            });

            WarehouseExternalRequest::all()->each(function (WarehouseExternalRequest $request): void {
                DB::table('warehouse_requests_unified')->insert([
                    'requestable_id'   => $request->id,
                    'requestable_type' => WarehouseExternalRequest::class,
                    'created_at'       => $request->created_at,
                    'updated_at'       => $request->updated_at,
                ]);
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
        //
    }
}
