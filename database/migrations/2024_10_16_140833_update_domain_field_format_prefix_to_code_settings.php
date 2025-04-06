<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateDomainFieldFormatPrefixToCodeSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('code_settings', function (Blueprint $table) {
            $table->string('format_prefix', 10)->comment('Formato del prefijo configurado')->change();
            $table->string('format_year')->nullable()->comment('Formato del año configurado')->change();
        });

        DB::statement("ALTER TABLE code_settings DROP CONSTRAINT code_settings_format_year_check;");
        DB::statement("
            ALTER TABLE code_settings
            ADD CONSTRAINT code_settings_format_year_check
            CHECK (format_year IN ('', 'YY', 'YYYY'));
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('code_settings', function (Blueprint $table) {
            $table->string('format_prefix', 3)->comment('Formato del prefijo configurado')->change();
            $table->string('format_year')->comment('Formato del año configurado')->change();
        });

        DB::statement("ALTER TABLE code_settings DROP CONSTRAINT code_settings_format_year_check;");
        DB::statement("
            ALTER TABLE code_settings
            ADD CONSTRAINT code_settings_format_year_check
            CHECK (format_year IN ('YY', 'YYYY'));
        ");
    }
}
