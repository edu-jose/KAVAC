<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddOrdinaryPaymentColumnToPayrollPaymentTypesTable
 * @brief Agrega columna de pago ordinario a la tabla de tipos de pago de nómina
 *
 * Gestiona la creación o eliminación de la columna de pago ordinario a la tabla de tipos de pago de nómina
 *
 * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddOrdinaryPaymentColumnToPayrollPaymentTypesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('payroll_payment_types', 'ordinary_payment')) {
            Schema::table('payroll_payment_types', function (Blueprint $table) {
                $table->boolean('ordinary_payment')->default(false);
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
        Schema::table('payroll_payment_types', function (Blueprint $table) {
            $table->dropColumn('ordinary_payment');
        });
    }
}
