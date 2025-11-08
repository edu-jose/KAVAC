<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


/**
 * @class CreateSaleCustomerPhonesTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateSaleCustomerPhonesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_customer_phones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_customer_id')->constrained('sale_customer_management')->onDelete('cascade');
            $table->enum('type', ['mobile', 'phone', 'fax'])->comment('Tipo de teléfono');
            $table->string('area_code', 5)->nullable()->comment('Código de área');
            $table->string('number', 15)->comment('Número telefónico');
            $table->string('extension', 10)->nullable()->comment('Extensión telefónica');
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
        Schema::dropIfExists('sale_customer_phones');
    }
}
