<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldTransactionTypeIdToCitizenServiceRequestsTable
 * @brief Agrega la el campo tipo de transaccion.
 *
 * [descripción corta]
 *
 * @author Tsu. Miguel Narvaez
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldTransactionTypeIdToCitizenServiceRequestsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('citizen_service_requests')) {
            Schema::table('citizen_service_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('citizen_service_requests', 'citizen_transaction_type_id')) {
                    $table->foreignId('citizen_transaction_type_id')->nullable()->references('id')->on('citizen_service_transaction_types')->onDelete('cascade')->onUpdate('cascade')->comment('Tipo de Transacción');
                }              
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
         if (Schema::hasTable('citizen_service_requests')) {
            Schema::table('citizen_service_requests', function (Blueprint $table) {
                if (Schema::hasColumn('citizen_service_requests', 'citizen_transaction_type_id')) {
                    $table->dropColumn('citizen_transaction_type_id');
                }                
            });
        }
    }
}