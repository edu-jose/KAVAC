<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateWarehouseExternalRequestsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateWarehouseExternalRequestsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouse_external_requests', function (Blueprint $table) {
            $table->id();

            $table->string('code', 20)->unique()->comment('Código de la solicitud');
            $table->date('date')->comment('Fecha de la solicitud');
            $table->string('first_name')->comment('Nombres del solicitante');
            $table->string('last_name')->comment('Apellidos del solicitante');
            $table->text('institution_name')->nullable()->comment('Organización solicitante');
            $table->unsignedBigInteger('warehouse_id')->comment('Almacén asociado a la solicitud');
            $table->text('general_observations')->nullable()->comment('Observaciones generales');

            $table->string('state')->comment('Estado de la solicitud');
            $table->boolean('delivered')->default(false)
                ->comment('Define si el almacén hizo entrega de la solicitud. (true)Si, (false)No');
            $table->date('delivery_date')->nullable()
                ->comment('Fecha en la que se hizo la entrega de la solicitud');
            $table->text('observations')->nullable()->comment('Observaciones de la solicitud');

            $table->timestamps();
            $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');

            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->onDelete('restrict')
                ->onUpdate('cascade');;
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('warehouse_external_requests');
    }
}
