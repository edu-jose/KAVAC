<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToHeadquartersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('headquarters')) {
            Schema::table('headquarters', function (Blueprint $table) {
                $table->string('rif', 10)->nullable()->comment('Número de registro fiscal');
                $table->text('address')->nullable()->comment('Dirección fiscal');
                $table->foreignId('city_id')->nullable()
                    ->constrained('cities')
                    ->onDelete('restrict')->onUpdate('cascade');
                $table->foreignId('municipality_id')->nullable()
                    ->constrained('municipalities')
                    ->onDelete('restrict')->onUpdate('cascade');
                $table->foreignId('region_id')->nullable()
                    ->constrained('regions')
                    ->onDelete('restrict')->onUpdate('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('headquarters')) {
            Schema::table('headquarters', function (Blueprint $table) {
                if (Schema::hasColumn('headquarters', 'city_id')) {
                    Schema::table('headquarters', function (Blueprint $table) {
                        $table->dropForeign('headquarters_city_id_foreign');
                        $table->dropColumn('city_id');
                    });
                }
                if (Schema::hasColumn('headquarters', 'municipality_id')) {
                    Schema::table('headquarters', function (Blueprint $table) {
                        $table->dropForeign('headquarters_municipality_id_foreign');
                        $table->dropColumn('municipality_id');
                    });
                }
                if (Schema::hasColumn('headquarters', 'region_id')) {
                    Schema::table('headquarters', function (Blueprint $table) {
                        $table->dropForeign('headquarters_region_id_foreign');
                        $table->dropColumn('region_id');
                    });
                }
                $table->dropColumn(['rif', 'address']);
            });
        }
    }
}
