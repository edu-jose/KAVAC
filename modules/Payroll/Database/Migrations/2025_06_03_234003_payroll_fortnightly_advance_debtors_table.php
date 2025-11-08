<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class PayrollFortnightlyAdvanceDebtorsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollFortnightlyAdvanceDebtorsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_fortnightly_advance_debtors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_staff_id')->constrained('payroll_staffs')->onDelete('cascade');
            $table->foreignId('payroll_payment_type_id')->constrained('payroll_payment_types')->onDelete('cascade');
            $table->foreignId('payroll_payment_period_id')->constrained('payroll_payment_periods')->onDelete('cascade');
            $table->foreignId('payroll_concept_id')->constrained('payroll_concepts')->onDelete('cascade');
            $table->text('filter_rule')->nullable();
            $table->boolean('in_debt')->default(false);
            $table->timestamps();

            // Add the unique constraint required for ON CONFLICT
            $table->unique(
                [
                    'payroll_concept_id',
                    'payroll_payment_type_id',
                    'payroll_payment_period_id',
                    'payroll_staff_id'
                ],
                'unique_payroll_debtor'
            );
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payroll_fortnightly_advance_debtors');
    }
}
