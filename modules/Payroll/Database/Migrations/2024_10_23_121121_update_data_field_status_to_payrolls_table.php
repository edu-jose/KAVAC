<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Payroll\Models\DocumentStatus;
use Modules\Payroll\Models\Payroll;

/**
 * @class UpdateDataFieldStatusToPayrollTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateDataFieldStatusToPayrollsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['status']);
            $table->foreignId('document_status_id')->nullable()
                ->comment('Identificador único asociado al estatus del documento')
                ->constrained('document_status')
                ->onDelete('restrict')->onUpdate('cascade');
        });

        /** Estatus de Documentos */
        $documentStatus = DocumentStatus::query()->get()->pluck('id', 'action');

        /** Registros de nomina elaborados */
        $payrollsEL = Payroll::with(['payrollPaymentPeriod' => function ($query) {
            $query->without('payrollPaymentType');
        }])->whereHas('payrollPaymentPeriod', function ($query) {
            $query->where('payment_status', 'pending')
                ->whereNull('availability_status');
        })->get()->pluck('id');

        /** Registros de nomina aprobados */
        $payrollsAP = Payroll::with(['payrollPaymentPeriod' => function ($query) {
            $query->without('payrollPaymentType');
        }])->whereHas('payrollPaymentPeriod', function ($query) {
            $query->where('payment_status', 'approved');
        })->get()->pluck('id');

        /** Registros de nomina cerrados */
        $payrollsCE = Payroll::with(['payrollPaymentPeriod' => function ($query) {
            $query->without('payrollPaymentType');
        }])->whereHas('payrollPaymentPeriod', function ($query) {
            $query->where('payment_status', 'generated');
        })->get()->pluck('id');
        
        Payroll::query()
            ->whereIn('id', $payrollsEL)
            ->update(['document_status_id' => $documentStatus->get('EL')]);
        Payroll::query()
            ->whereIn('id', $payrollsAP)
            ->update(['document_status_id' => $documentStatus->get('AP')]);
        Payroll::query()
            ->whereIn('id', $payrollsCE)
            ->update(['document_status_id' => $documentStatus->get('CE')]);
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->string('status')->nullable()->comment('Estatus de la nomina referente a Disponibilidad');
            $table->dropForeign(['document_status_id']);
            $table->dropColumn(['document_status_id']);
        });
    }
}
