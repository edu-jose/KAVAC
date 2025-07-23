<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * @class CreatePayrollConceptTabulatorView
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollConceptTabulatorsView extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE OR REPLACE VIEW public.payroll_concept_tabulators_view
            AS SELECT
                concepts.id AS id,
                concepts.name AS name,
                tabulator.id AS tabulator_id,
                tabulator.name AS tabulator_name
            FROM payroll_concepts AS concepts
            JOIN
                payroll_salary_tabulators AS tabulator
            ON get_expanded_formula(formula) LIKE '%tabulator(' || tabulator.id || ')%'
        ");
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS payroll_concept_tabulators_view');
    }
}
