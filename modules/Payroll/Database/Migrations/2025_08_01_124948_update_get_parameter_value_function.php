<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * @class UpdateGetParameterValueFunction
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateGetParameterValueFunction extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('DROP FUNCTION IF EXISTS get_parameter_value;');

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION get_parameter_value(
                param_name TEXT,
                payroll_staff_id INT,
                period_start DATE,
                period_end DATE,
                institution_id INT,
                payroll_id INT,
                resettable_vars JSONB DEFAULT '{}'::JSONB,
                concept_id INT DEFAULT 0
            ) RETURNS NUMERIC AS $$
            DECLARE
                param_json JSONB;
                param_type TEXT;
                result NUMERIC := 0;
                formula_text TEXT;
                key TEXT;
                time_data JSONB;
            BEGIN
                SELECT p_value::jsonb INTO param_json
                FROM parameters
                WHERE p_key = param_name;

                IF NOT FOUND THEN
                    RETURN 0;
                END IF;

                param_type := param_json ->> 'parameter_type';

                CASE param_type
                    WHEN 'global_value' THEN
                        -- Verificar si el campo "percentage" es true
                        IF (param_json ->> 'percentage')::BOOLEAN THEN
                            result := COALESCE((param_json ->> 'value')::NUMERIC, 0) / 100;
                        ELSE
                            result := COALESCE((param_json ->> 'value')::NUMERIC, 0);
                        END IF;

                    WHEN 'resettable_variable' THEN
                        BEGIN
                            -- Buscar el valor en la columna payroll_parameters
                            SELECT COALESCE(
                                (SELECT (param->>'value')::NUMERIC
                                FROM (
                                    -- Prioridad 1: concept_id = INT y staff_id = INT (evitando cadenas vacías)
                                    SELECT param
                                    FROM jsonb_array_elements(
                                        (SELECT payroll_parameters
                                        FROM payrolls
                                        WHERE id = payroll_id)::JSONB
                                    ) AS param
                                    WHERE NULLIF(param->>'concept_id', '')::INT = concept_id
                                    AND NULLIF(param->>'staff_id', '')::INT = payroll_staff_id

                                    UNION ALL
                                    -- Prioridad 2: concept_id ("" o no definido)
                                    SELECT param
                                    FROM jsonb_array_elements(
                                        (SELECT payroll_parameters
                                        FROM payrolls
                                        WHERE id = payroll_id)::JSONB
                                    ) AS param
                                    WHERE NULLIF(param->>'staff_id', '')::INT = payroll_staff_id
                                    AND (param->>'concept_id' IS NULL OR param->>'concept_id' = '')

                                    UNION ALL
                                    -- Prioridad 3: payroll_id ("" o no definido)
                                    SELECT param
                                    FROM jsonb_array_elements(
                                        (SELECT payroll_parameters
                                        FROM payrolls
                                        WHERE id = payroll_id)::JSONB
                                    ) AS param
                                    WHERE NULLIF(param->>'concept_id', '')::INT = concept_id
                                    AND (param->>'staff_id' IS NULL OR param->>'staff_id' = '')

                                    UNION ALL
                                    -- Prioridad 4: Sin concept_id ni staff_id (NULL/cadena vacía)
                                    SELECT param
                                    FROM jsonb_array_elements(
                                        (SELECT payroll_parameters
                                        FROM payrolls
                                        WHERE id = payroll_id)::JSONB
                                    ) AS param
                                    WHERE (param->>'concept_id' IS NULL OR param->>'concept_id' = '')
                                    AND (param->>'staff_id' IS NULL OR param->>'staff_id' = '')
                                ) AS filtered_params
                                LIMIT 1
                                ),
                                0
                            ) INTO result;

                            -- Si no se encuentra el valor, asignar 0
                            IF NOT FOUND THEN
                                result := 0;
                            END IF;
                        END;

                    WHEN 'processed_variable' THEN
                        formula_text := param_json ->> 'formula';
                        result := process_parameter_formula(formula_text, payroll_staff_id, period_start, period_end, institution_id, payroll_id, resettable_vars);

                    WHEN 'time_parameter' THEN
                        DECLARE
                            acronym TEXT;
                            param_name TEXT;
                            search_key TEXT;
                            time_sheet_value NUMERIC;
                        BEGIN
                            -- Obtener el acronym y el name del parámetro desde param_json
                            acronym := param_json ->> 'acronym';
                            param_name := param_json ->> 'name';

                            -- Construir la clave que se buscará en el JSON
                            search_key := acronym || ' - ' || param_name || '-' || payroll_staff_id;

                            DECLARE
                                row RECORD; -- Variable para almacenar cada fila devuelta por la consulta
                                time_sheet_value NUMERIC := 0; -- Inicializar el valor como 0
                            BEGIN
                                -- Iterar sobre cada fila devuelta por la consulta
                                FOR row IN
                                    SELECT time_sheet_data
                                    FROM payroll_time_sheets
                                    WHERE from_date >= period_start
                                    AND (to_date IS NULL OR to_date <= period_end)
                                    AND document_status_id = (
                                        SELECT id
                                        FROM document_status
                                        WHERE action = 'CE'
                                    )
                                LOOP
                                    -- Convertir time_sheet_data a JSONB y buscar la clave
                                    IF row.time_sheet_data::JSONB ? search_key THEN
                                        time_sheet_value := (row.time_sheet_data::JSONB ->> search_key)::NUMERIC;
                                        EXIT; -- Salir del bucle una vez encontrado
                                    END IF;
                                END LOOP;

                                -- Asignar el valor encontrado a result
                                result := COALESCE(time_sheet_value, 0);
                            END;
                        END;
                    ELSE
                        result := 0;
                END CASE;

                BEGIN
                    EXECUTE 'SELECT ' || result;
                EXCEPTION
                    WHEN OTHERS THEN
                        -- Capturar cualquier excepción y asignar 0 al resultado
                        result := 0;
                END;

                RETURN result;
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
        DB::statement('DROP FUNCTION IF EXISTS get_parameter_value;');

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION get_parameter_value(
                param_name TEXT,
                payroll_staff_id INT,
                period_start DATE,
                period_end DATE,
                institution_id INT,
                payroll_id INT,
                resettable_vars JSONB DEFAULT '{}'::JSONB,
                concept_id INT DEFAULT 0
            ) RETURNS NUMERIC AS $$
            DECLARE
                param_json JSONB;
                param_type TEXT;
                result NUMERIC := 0;
                formula_text TEXT;
                key TEXT;
                time_data JSONB;
            BEGIN
                SELECT p_value::jsonb INTO param_json
                FROM parameters
                WHERE p_key = param_name;

                IF NOT FOUND THEN
                    RETURN 0;
                END IF;

                param_type := param_json ->> 'parameter_type';

                CASE param_type
                    WHEN 'global_value' THEN
                        -- Verificar si el campo "percentage" es true
                        IF (param_json ->> 'percentage')::BOOLEAN THEN
                            result := COALESCE((param_json ->> 'value')::NUMERIC, 0) / 100;
                        ELSE
                            result := COALESCE((param_json ->> 'value')::NUMERIC, 0);
                        END IF;

                    WHEN 'resettable_variable' THEN
                        BEGIN
                            -- Buscar el valor en la columna payroll_parameters
                            SELECT COALESCE(
                                (SELECT (param->>'value')::NUMERIC
                                FROM (
                                    -- Prioridad 1: concept_id = INT y staff_id = INT (evitando cadenas vacías)
                                    SELECT param
                                    FROM jsonb_array_elements(
                                        (SELECT payroll_parameters
                                        FROM payrolls
                                        WHERE id = payroll_id)::JSONB
                                    ) AS param
                                    WHERE NULLIF(param->>'concept_id', '')::INT = concept_id
                                    AND NULLIF(param->>'staff_id', '')::INT = payroll_staff_id

                                    UNION ALL
                                    -- Prioridad 2: concept_id ("" o no definido)
                                    SELECT param
                                    FROM jsonb_array_elements(
                                        (SELECT payroll_parameters
                                        FROM payrolls
                                        WHERE id = payroll_id)::JSONB
                                    ) AS param
                                    WHERE NULLIF(param->>'staff_id', '')::INT = payroll_staff_id
                                    AND (param->>'concept_id' IS NULL OR param->>'concept_id' = '')

                                    UNION ALL
                                    -- Prioridad 3: Sin concept_id ni staff_id (NULL/cadena vacía)
                                    SELECT param
                                    FROM jsonb_array_elements(
                                        (SELECT payroll_parameters
                                        FROM payrolls
                                        WHERE id = payroll_id)::JSONB
                                    ) AS param
                                    WHERE (param->>'concept_id' IS NULL OR param->>'concept_id' = '')
                                    AND (param->>'staff_id' IS NULL OR param->>'staff_id' = '')
                                ) AS filtered_params
                                LIMIT 1
                                ),
                                0
                            ) INTO result;

                            -- Si no se encuentra el valor, asignar 0
                            IF NOT FOUND THEN
                                result := 0;
                            END IF;
                        END;

                    WHEN 'processed_variable' THEN
                        formula_text := param_json ->> 'formula';
                        result := process_parameter_formula(formula_text, payroll_staff_id, period_start, period_end, institution_id, payroll_id, resettable_vars);

                    WHEN 'time_parameter' THEN
                        DECLARE
                            acronym TEXT;
                            param_name TEXT;
                            search_key TEXT;
                            time_sheet_value NUMERIC;
                        BEGIN
                            -- Obtener el acronym y el name del parámetro desde param_json
                            acronym := param_json ->> 'acronym';
                            param_name := param_json ->> 'name';

                            -- Construir la clave que se buscará en el JSON
                            search_key := acronym || ' - ' || param_name || '-' || payroll_staff_id;

                            DECLARE
                                row RECORD; -- Variable para almacenar cada fila devuelta por la consulta
                                time_sheet_value NUMERIC := 0; -- Inicializar el valor como 0
                            BEGIN
                                -- Iterar sobre cada fila devuelta por la consulta
                                FOR row IN
                                    SELECT time_sheet_data
                                    FROM payroll_time_sheets
                                    WHERE from_date >= period_start
                                    AND (to_date IS NULL OR to_date <= period_end)
                                    AND document_status_id = (
                                        SELECT id
                                        FROM document_status
                                        WHERE action = 'CE'
                                    )
                                LOOP
                                    -- Convertir time_sheet_data a JSONB y buscar la clave
                                    IF row.time_sheet_data::JSONB ? search_key THEN
                                        time_sheet_value := (row.time_sheet_data::JSONB ->> search_key)::NUMERIC;
                                        EXIT; -- Salir del bucle una vez encontrado
                                    END IF;
                                END LOOP;

                                -- Asignar el valor encontrado a result
                                result := COALESCE(time_sheet_value, 0);
                            END;
                        END;
                    ELSE
                        result := 0;
                END CASE;

                BEGIN
                    EXECUTE 'SELECT ' || result;
                EXCEPTION
                    WHEN OTHERS THEN
                        -- Capturar cualquier excepción y asignar 0 al resultado
                        result := 0;
                END;

                RETURN result;
            END;
            $$ LANGUAGE plpgsql;
            SQL
        );
    }
}
