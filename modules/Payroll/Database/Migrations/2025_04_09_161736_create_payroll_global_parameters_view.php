<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * @class CreatePayrollGlobalParametersView
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollGlobalParametersView extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE OR REPLACE VIEW public.payroll_global_parameters_view
            AS SELECT 
                (p.p_value::jsonb->>'id') AS id,
                (p.p_value::jsonb)->>'parameter_type' AS type,
                (p.p_value::jsonb)->>'name' AS name,
                (p.p_value::jsonb)->>'value' AS value
            FROM 
                parameters p
            WHERE 
                p.required_by = 'payroll'
                AND p.active = true
                AND p.p_key LIKE 'global_parameter_' || '%';
        ");
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS payroll_global_parameters_view');
    }
}
