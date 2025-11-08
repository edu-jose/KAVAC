<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateSaleCustomerManagementTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */

class CreateSaleCustomerManagementTable extends Migration
{
    public function up()
    {
        Schema::create('sale_customer_management', function (Blueprint $table) {
            $table->id();
            $table->char('identifier_type', 1)->comment(
                'Tipo de persona:
                    (N)atural,
                    (J)urídica,
                    (G)ubernamental,
                    (E)Extranjero'
            ); 
            $table->string('identification_number', 10);
            $table->string('name', 100);
            $table->string('fiscal_address', 100);
            $table->timestamps();
            $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sale_customer_management');
    }
}