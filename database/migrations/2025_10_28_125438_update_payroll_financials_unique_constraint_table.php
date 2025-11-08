<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePayrollFinancialsUniqueConstraintTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_financials', function (Blueprint $table) {

            // nuevo índice único compuesto
            $table->unique([
                'payroll_account_number',
                'payroll_staff_id',
                'deleted_at'
            ], 'unique_active_payroll_data');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_financials', function (Blueprint $table) {
            $table->dropUnique('unique_active_payroll_data');
        });
    }
}
