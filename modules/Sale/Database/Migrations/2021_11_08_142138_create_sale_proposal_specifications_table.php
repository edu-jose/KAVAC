<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateSaleProposalSpecificationsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateSaleProposalSpecificationsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('sale_proposal_specifications')) {
            Schema::create('sale_proposal_specifications', function (Blueprint $table) {
                $table->id();

                $table->string('name', 400)->nullable()->comment('Especificaciones de la propuesta técnica');
                $table->foreignId('sale_technical_proposal_id')->nullable()
                      ->comment('Propuesta técnica')->constrained()
                      ->onUpdate('cascade')->onDelete('restrict');

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
        Schema::dropIfExists('sale_proposal_specifications');
    }
}
