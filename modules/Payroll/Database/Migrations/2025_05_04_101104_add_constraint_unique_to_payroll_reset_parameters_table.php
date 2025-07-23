<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Payroll\Models\PayrollResetParameter;

/**
 * @class AddConstraintUniqueToPayrollResetParametersTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddConstraintUniqueToPayrollResetParametersTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function () {
            $duplicates = PayrollResetParameter::query()
                ->select('payroll_id', 'payroll_staff_id', 'payroll_concept_id', 'name')
                ->groupBy('payroll_id', 'payroll_staff_id', 'payroll_concept_id', 'name')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            foreach ($duplicates as $duplicate) {
                $recordToKeep = PayrollResetParameter::query()
                    ->where('payroll_id', $duplicate->payroll_id)
                    ->where('payroll_staff_id', $duplicate->payroll_staff_id)
                    ->where('payroll_concept_id', $duplicate->payroll_concept_id)
                    ->where('name', $duplicate->name)
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                PayrollResetParameter::query()
                    ->where('payroll_id', $duplicate->payroll_id)
                    ->where('payroll_staff_id', $duplicate->payroll_staff_id)
                    ->where('payroll_concept_id', $duplicate->payroll_concept_id)
                    ->where('name', $duplicate->name)
                    ->where('id', '!=', $recordToKeep->id)
                    ->forceDelete();
            }

            $remainingDuplicates = PayrollResetParameter::query()
                ->select('payroll_id', 'payroll_staff_id', 'payroll_concept_id', 'name')
                ->groupBy('payroll_id', 'payroll_staff_id', 'payroll_concept_id', 'name')
                ->havingRaw('COUNT(*) > 1')
                ->count();

            if ($remainingDuplicates > 0) {
                throw new Exception("Aún hay duplicados en la tabla. No se puede aplicar la restricción única.");
            }
        });


        Schema::table('payroll_reset_parameters', function (Blueprint $table) {
            $table->unique(['payroll_id', 'payroll_staff_id', 'payroll_concept_id', 'name'], 'unique_parameter_per_staff');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_reset_parameters', function (Blueprint $table) {
            $table->dropUnique('unique_parameter_per_staff');
        });
    }
}
