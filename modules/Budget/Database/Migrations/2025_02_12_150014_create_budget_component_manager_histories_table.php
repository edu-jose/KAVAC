<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * @class CreateBudgetComponentManagerHistoriesTable
 * 
 * @brief Esta tabla almacena un historial de los responasbles de los Proyectos
 * y Acciones centralizadas del sistema cuando estos son guardados o
 * actualizados.
 *
 * Gestión de campos de la tabla budget_component_manager_histories.
 *
 * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateBudgetComponentManagerHistoriesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('budget_component_manager_histories')) {
            Schema::create('budget_component_manager_histories', function (Blueprint $table) {
                $table->id();

                // Relaciones con Empleados.
                $table->morphs('managerable');

                // Relaciones con Proyectos y AC.
                $table->morphs('componentable');

                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            });
        }

        /**
         * Lógica para migrar los datos de los responsables de los Proyectos y
         * Acciones Centralizadas a la tabla budget_component_manager_histories.
         */

        /* Obtener todos los registros de la tabla budget_projects. */
        $projects = DB::table('budget_projects')->get();

        /* Definir el modelo para managerable_type. */
        $managerableModel = \Modules\Payroll\Models\PayrollStaff::class;

        /* Definir el modelo para componentable_type. */
        $componentableBudgetModel = \Modules\Budget\Models\BudgetProject::class;

        /* Definir el modelo para componentable_type. */
        $componentableCentralizedActionModel = \Modules\Budget\Models\BudgetCentralizedAction::class;

        /* Recorrer cada registro de la tabla budget_projects. */
        foreach ($projects as $x) {
            // Obtener el ID del responsable.
            $payrollStaffID = $x->payroll_staff_id;
            // Obtener el ID del Proyecto.
            $projectID = $x->id;
            // Obtener la fecha de inicio del proyecto.
            $fromDate = $x->from_date;

            // Insertar registros en la tabla budget_component_manager_histories.
            DB::table('budget_component_manager_histories')->insert([
                'managerable_type' => $managerableModel,
                'managerable_id' => $payrollStaffID,
                'componentable_type' => $componentableBudgetModel,
                'componentable_id' => $projectID,
                'created_at' => $fromDate,
            ]);
        }

        /* Obtener todos los registros de la tabla centralized_actions */
        $centralized_actions = DB::table('budget_centralized_actions')->get();

        /* Recorrer cada registro de la tabla budget_projects. */
        foreach ($centralized_actions as $x) {
            // Obtener el ID del responsable.
            $payrollStaffID = $x->payroll_staff_id;
            // Obtener el ID de la AC.
            $centralizedActionsID = $x->id;
            // Obtener la fecha de inicio de la AC.
            $fromDate = $x->from_date;

            /* Insertar registros en la tabla budget_component_manager_histories. */
            DB::table('budget_component_manager_histories')->insert([
                'managerable_type' => $managerableModel,
                'managerable_id' => $payrollStaffID,
                'componentable_type' => $componentableCentralizedActionModel,
                'componentable_id' => $centralizedActionsID,
                'created_at' => $fromDate,
            ]);
        }
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('budget_component_manager_histories');
    }
}
