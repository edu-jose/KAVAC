<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * @class CreatePayrollConceptFormulaView
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollConceptParametersView extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE OR REPLACE FUNCTION public.get_expanded_formula(f text)
                RETURNS text
                LANGUAGE plpgsql
                AS $$
                DECLARE
                    nueva_formula text := f;
                    coincidencia text[];
                    concepto_id int;
                    parametro_id text;
                    subformula text;
                    param_value text;
                BEGIN
                    -- Primero expandimos todos los concept(ID)
                    LOOP
                        SELECT regexp_match(nueva_formula, 'concept\((\d+)\)') INTO coincidencia;
                        EXIT WHEN coincidencia IS NULL;

                        concepto_id := coincidencia[1]::int;

                        SELECT formula INTO subformula
                        FROM payroll_concepts
                        WHERE id = concepto_id;

                        IF subformula IS NULL THEN
                            EXIT;
                        END IF;

                        subformula := get_expanded_formula(subformula);

                        nueva_formula := regexp_replace(
                            nueva_formula,
                            'concept\(' || concepto_id || '\)',
                            '(' || subformula || ')',
                            'g'
                        );
                    END LOOP;

                    RETURN nueva_formula;
                END;
                $$;
        ");

        DB::statement("
            CREATE OR REPLACE VIEW public.payroll_concept_parameters_view
            AS SELECT
                concepts.id AS id,
                concepts.name AS name,
                parameters.id AS parameter_id,
                parameters.type AS parameter_type,
                parameters.name AS parameter_name,
                parameters.value AS parameter_value
            FROM payroll_concepts AS concepts
            JOIN
                payroll_global_parameters_view AS parameters
            ON get_expanded_formula(formula) LIKE '%parameter(' || parameters.id || ')%'
        ");
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS payroll_concept_parameters_view');
        DB::statement('DROP FUNCTION IF EXISTS public.get_expanded_formula');
    }
}
