<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePayrollVacationPolicyPaymentConceptsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollVacationPolicyPaymentConceptsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('payroll_vacation_policy_payment_concepts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_vacation_policy_payment_id')
                ->constrained()->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('payroll_concept_id')
                ->constrained()->onUpdate('cascade')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_vacation_policy_payment_concepts');
    }
}
