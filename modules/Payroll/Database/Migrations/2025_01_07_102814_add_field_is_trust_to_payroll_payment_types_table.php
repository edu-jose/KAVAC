<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldIsTrustToPayrollPaymentTypesTable
 * @brief Se agrega un campo para verificar si el tipo de nómina corresponde a fideicomiso
 *
 * Se agrega un campo para verificar si el tipo de nómina corresponde a fideicomiso
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldIsTrustToPayrollPaymentTypesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_payment_types', function (Blueprint $table) {
            $table
                ->boolean('is_trust')
                ->default(false)
                ->comment('Indica si el tipo de nomina corresponde a fideicomiso');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_payment_types', function (Blueprint $table) {
            $table->dropColumn('is_trust');
        });
    }
}
