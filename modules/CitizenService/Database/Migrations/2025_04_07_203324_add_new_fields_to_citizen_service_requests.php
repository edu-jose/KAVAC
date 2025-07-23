<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddBeneficiaryFieldsToCitizenServiceRequests
 * @brief Agrega campos de beneficiario a la tabla de solicitudes de servicio / trámites
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddNewFieldsToCitizenServiceRequests extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('citizen_service_requests', function (Blueprint $table) {
            $table->float('family_income', 20, 2)->nullable()
                  ->comment('Ingresos familiares del beneficiario / solicitante');
            $table->integer('family_burden')->nullable()
                  ->comment('Carga familiar del beneficiario / solicitante');
            $table->boolean('is_household_head')->default(false)
                  ->comment('Indica si el beneficiario / solicitante es el jefe de hogar');
            $table->boolean('has_work')->default(false)
                  ->comment('Indica si el beneficiario / solicitante tiene trabajo');
            $table->boolean(column: 'has_venapp_report')->default(false)
                  ->comment('Indica si el beneficiario / solicitante tiene reporte en el sistema VENAPP');
            $table->string('venapp_report_number')->nullable()
                  ->comment('Número de reporte en el sistema VENAPP del beneficiario / solicitante');
            $table->text('observations')->nullable()
                  ->comment('Observaciones del beneficiario / solicitante');
            $table->foreignId('sector_id')->nullable()
                  ->references('id')->on('institution_sectors')
                  ->onDelete('restrict')->onUpdate('cascade')
                  ->comment('Sector económico del beneficiario / solicitante');
            $table->foreignId('profession_id')->nullable()
                  ->references('id')->on('professions')
                  ->onDelete('restrict')->onUpdate('cascade')
                  ->comment('Profesión del beneficiario / solicitante');
            $table->foreignId('payroll_instruction_degree_id')->nullable()
                  ->references('id')->on('payroll_instruction_degrees')
                  ->onDelete('restrict')->onUpdate('cascade')
                  ->comment('Grado de instrucción del beneficiario / solicitante');
            $table->foreignId('citizen_service_procedure_id')->nullable()
                  ->references('id')->on('citizen_service_procedures')
                  ->onDelete('restrict')->onUpdate('cascade')
                  ->comment('Procedimiento o trámite del beneficiario / solicitante');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('citizen_service_requests', function (Blueprint $table) {
            $table->dropColumn('family_income');
            $table->dropColumn('family_burden');
            $table->dropColumn('is_household_head');
            $table->dropColumn('has_work');
            $table->dropColumn('has_venapp_report');
            $table->dropColumn('venapp_report_number');
            $table->dropColumn('observations');
            $table->dropForeign(['sector_id']);
            $table->dropColumn('sector_id');
            $table->dropForeign(['profession_id']);
            $table->dropColumn('profession_id');
            $table->dropForeign(['payroll_instruction_degree_id']);
            $table->dropColumn('payroll_instruction_degree_id');
            $table->dropForeign(['citizen_service_procedure_id']);
            $table->dropColumn('citizen_service_procedure_id');
        });
    }
}
