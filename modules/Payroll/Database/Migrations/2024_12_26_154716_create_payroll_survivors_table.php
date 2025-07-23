<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddNewFieldsToPayrollStaffsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollSurvivorsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('payroll_survivors')) {
            Schema::create('payroll_survivors', function (Blueprint $table) {
                $table->id();
                $table->string('first_name', 100)->comment('Nombres del personal');
                $table->string('last_name', 100)->comment('Apellidos del personal');
                $table->string('id_number', 12)->unique()->comment('Cédula de identidad del personal');
                $table->foreignId('payroll_staff_id')->unique()->constrained()
                ->onDelete('restrict')->onUpdate('cascade');
                $table->foreignId('finance_bank_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
                $table->foreignId('finance_account_type_id')->nullable()->constrained()
                    ->onDelete('restrict')->onUpdate('cascade');
                $table->string('payroll_account_number', 20)->comment('Número de cuenta');
                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
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
        Schema::dropIfExists('payroll_survivor');
    }
}
