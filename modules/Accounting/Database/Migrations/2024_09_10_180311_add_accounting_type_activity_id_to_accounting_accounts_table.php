<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddAccountingTypeActivityIdToAccountingAccountsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddAccountingTypeActivityIdToAccountingAccountsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounting_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('accounting_accounts', 'accounting_type_activity_id')) {
                $table->foreignId('accounting_type_activity_id')
                    ->nullable()
                    ->constrained('accounting_type_activities')
                    ->onUpdate('cascade');
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
        Schema::table('accounting_accounts', function (Blueprint $table) {
            $table->dropForeign(['accounting_type_activity_id']);
            $table->dropColumn('accounting_type_activity_id');
        });
    }
}
