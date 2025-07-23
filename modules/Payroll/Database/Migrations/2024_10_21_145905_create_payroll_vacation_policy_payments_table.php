<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePayrollVacationPolicyPaymentsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollVacationPolicyPaymentsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('payroll_vacation_policy_payments', function (Blueprint $table) {
            $table->id();
            $table->integer('base_days')->nullable();
            $table->integer('additional_days')->nullable();
            $table->integer('additional_max_days')->nullable();
            $table->integer('time_number_concepts')->nullable();
            $table->integer('sweep_time')->nullable();
            $table->integer('time_anticipation_months')->nullable();
            $table->foreignId('payroll_vacation_policy_id')
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
        Schema::dropIfExists('payroll_vacation_policy_payments');
    }
}
