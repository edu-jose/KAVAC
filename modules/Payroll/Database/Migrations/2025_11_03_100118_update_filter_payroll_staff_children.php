<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * @class UpdateFilterPayrollStaffChildren
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFilterPayrollStaffChildren extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        // Obtener todas las funciones con el nombre 'filter_payroll_staff_children'
        $functions = DB::select(
            "SELECT proname, oidvectortypes(proargtypes) AS args
            FROM pg_proc
            WHERE proname = 'filter_payroll_staff_children'"
        );

        // Generar y ejecutar los comandos DROP FUNCTION
        foreach ($functions as $function) {
            $dropStatement = "DROP FUNCTION IF EXISTS filter_payroll_staff_children({$function->args})";
            // Imprimir el comando DROP para depuración
            echo "\n" . $dropStatement . "\n";
            DB::statement($dropStatement);
        }

        DB::statement(
            <<<'SQL'
                CREATE OR REPLACE FUNCTION filter_payroll_staff_children(
                    current_staff_id int, 
                    concept_id int, 
                    period_end date
                )
                    RETURNS TABLE(children_count int)
                AS $$
                DECLARE
                    _son_relationship_id int;
                    _option_max_date date;
                    _option_min_date date;
                    _option_min_val numeric;
                    _option_max_val numeric;
                    _value jsonb;
                BEGIN
                    _son_relationship_id := (
                        SELECT id 
                        FROM payroll_relationships 
                        WHERE name = 'Hijo(a)' 
                        LIMIT 1
                    );

                    _value := (
                        SELECT value 
                        FROM payroll_concept_assign_options 
                        WHERE value IS NOT NULL 
                            AND applicable_id = concept_id 
                            AND applicable_type = 'Modules\Payroll\Models\PayrollConcept'
                    );

                    _option_min_val := _value->>'minimum';
                    _option_max_val := _value->>'maximum';

                    _option_max_date := period_end - make_interval(years => _option_min_val::int);
                    _option_min_date := period_end - make_interval(years => _option_max_val::int + 1) + interval '1 day';

                    RETURN QUERY SELECT COUNT(DISTINCT pfb.id)::integer AS children_count
                    FROM payroll_socioeconomics psec
                    JOIN payroll_family_burdens pfb
                    ON psec.id = pfb.payroll_socioeconomic_id
                    WHERE psec.payroll_staff_id = current_staff_id
                        AND pfb.payroll_relationships_id = _son_relationship_id
                        AND pfb.birthdate IS NOT NULL
                        AND pfb.birthdate
                            BETWEEN _option_min_date::date AND _option_max_date::date
                        AND psec.deleted_at IS NULL
                        AND pfb.deleted_at IS NULL;
                END;
                $$ LANGUAGE plpgsql;
            SQL
        );
    }


    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        // Obtener todas las funciones con el nombre 'filter_payroll_staff_children'
        $functions = DB::select(
            "SELECT proname, oidvectortypes(proargtypes) AS args
            FROM pg_proc
            WHERE proname = 'filter_payroll_staff_children'"
        );

        // Generar y ejecutar los comandos DROP FUNCTION
        foreach ($functions as $function) {
            $dropStatement = "DROP FUNCTION IF EXISTS filter_payroll_staff_children({$function->args})";
            // Imprimir el comando DROP para depuración
            echo "\n" . $dropStatement . "\n";
            DB::statement($dropStatement);
        }
    }
}
