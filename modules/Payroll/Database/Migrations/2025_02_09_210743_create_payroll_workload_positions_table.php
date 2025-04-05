<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePayrollWorkloadPositionsTable
 * @brief Creación de tabla los trabajadores asociados a una carga horaria
 *
 * Creación de tabla los trabajadores asociados a una carga horaria
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollWorkloadPositionsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_workload_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_workload_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('payroll_position_id')->constrained()->onDelete('restrict')->onUpdate('cascade');            
            $table->timestamps();
            $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payroll_workload_positions');
    }
}
