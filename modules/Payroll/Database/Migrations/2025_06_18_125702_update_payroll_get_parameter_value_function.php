<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * @class UpdatePayrollGetParameterValueFunction
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdatePayrollGetParameterValueFunction extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('DROP FUNCTION IF EXISTS get_parameter_value;');
        DB::statement('DROP FUNCTION IF EXISTS process_formula;');
        DB::statement('DROP FUNCTION IF EXISTS process_parameter_formula(TEXT, INT, DATE, DATE, INT, INT, JSONB);');

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

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION process_parameter_formula(
                formula_text TEXT,
                current_staff_id INT,
                period_start DATE,
                period_end DATE,
                institution_id INT,
                payroll_id INT,
                resettable_vars JSONB DEFAULT '{}'::JSONB,
                concept_id INT DEFAULT 0
            ) RETURNS NUMERIC AS $$
            DECLARE
                parameter RECORD;
                match TEXT;
                matches TEXT[];
                result NUMERIC := 0;
                staff_start_date_years INT[];
                staff_apn_years INT[];
            BEGIN
                -- Obtener los start_date de los trabajadores a partir de la funcion get_staff_start_date_function
                SELECT * INTO staff_start_date_years
                FROM get_staff_start_date_function(ARRAY[current_staff_id], period_start, period_end);

                -- Obtener los start_date de los trabajadores a partir de la funcion get_staff_start_apn_function
                SELECT * INTO staff_apn_years
                FROM get_staff_start_apn_function(ARRAY[current_staff_id], period_start, period_end);

                -- Reemplazar parámetros tipo parameter(ID)
                matches := ARRAY(SELECT regexp_matches(formula_text, 'parameter\(([^\)]+)\)', 'g'))::TEXT[];

                IF matches IS NULL THEN
                    matches := ARRAY[]::TEXT[];
                END IF;

                FOREACH match IN ARRAY matches LOOP
                    -- Obtener el nombre del parámetro dentro de parameter(...)
                    formula_text := REPLACE(
                        formula_text,
                        'parameter(' || match || ')',
                        get_parameter_value('global_parameter_' || match, current_staff_id, period_start, period_end, institution_id, payroll_id, resettable_vars, concept_id)::TEXT
                    );
                END LOOP;

                -- Reemplazar WORKLOAD con el valor desde payroll_workload_positions
                IF POSITION('WORKLOAD' IN formula_text) > 0 THEN
                    DECLARE
                        w_value NUMERIC;
                    BEGIN
                        -- Obtener el id de workload desde payroll_workload_positions
                        SELECT workload_value
                        INTO w_value
                        FROM payroll_staff_filter_parameters_view
                        WHERE payroll_staff_id = current_staff_id;

                        -- Reemplazar WORKLOAD en la fórmula
                        formula_text := REPLACE(
                            formula_text,
                            'WORKLOAD',
                            COALESCE(w_value, 0)::TEXT
                        );
                    END;
                END IF;

                -- Reemplazar NUMBER_LANG con el valor desde payroll_staff_filter_parameters_view
                IF POSITION('NUMBER_LANG' IN formula_text) > 0 THEN
                    DECLARE
                        number_lang_value NUMERIC;
                    BEGIN
                        -- Obtener el valor de NUMBER_LANG desde payroll_staff_filter_parameters_view
                        SELECT professional_language_count
                        INTO number_lang_value
                        FROM payroll_staff_filter_parameters_view
                        WHERE payroll_staff_id = current_staff_id;

                        -- Reemplazar NUMBER_LANG en la fórmula
                        formula_text := REPLACE(
                            formula_text,
                            'NUMBER_LANG',
                            COALESCE(number_lang_value, 0)::TEXT
                        );
                    END;
                END IF;

                -- Reemplazar NUMBER_CHILDREN con el valor desde payroll_staff_filter_parameters_view
                IF POSITION('NUMBER_CHILDREN' IN formula_text) > 0 THEN
                    DECLARE
                        number_children_value NUMERIC;
                    BEGIN
                        -- Obtener el valor de NUMBER_CHILDREN desde payroll_staff_filter_parameters_view
                        SELECT children_count
                        INTO number_children_value
                        FROM payroll_staff_filter_parameters_view
                        WHERE payroll_staff_id = current_staff_id;

                        -- Reemplazar NUMBER_CHILDREN en la fórmula
                        formula_text := REPLACE(
                            formula_text,
                            'NUMBER_CHILDREN',
                            COALESCE(number_children_value, 0)::TEXT
                        );
                    END;
                END IF;

                -- Obtener el valor de START_DATE desde staff_start_date_years
                IF POSITION('START_DATE' IN formula_text) > 0 THEN
                    DECLARE
                        staff_start_dates INTEGER[][];
                        pair INTEGER[];
                        staff_id INT;
                        years INT;
                        start_date_value NUMERIC := 0; -- Inicializar el valor como 0
                    BEGIN
                        -- Iterar sobre cada subarreglo usando FOREACH con SLICE 1
                        IF staff_start_date_years IS NOT NULL AND array_length(staff_start_date_years, 1) > 0 THEN
                            FOREACH pair SLICE 1 IN ARRAY staff_start_date_years LOOP
                                staff_id := pair[1];
                                years := pair[2];

                                -- Realizar la validación deseada
                                IF staff_id = current_staff_id THEN
                                    start_date_value := years;
                                END IF;
                            END LOOP;
                        END IF;

                        -- Reemplazar START_DATE en la fórmula
                        formula_text := REPLACE(
                            formula_text,
                            'START_DATE',
                            COALESCE(start_date_value, 0)::TEXT
                        );
                    END;
                END IF;

                -- Reemplazar START_APN con el valor desde get_staff_start_apn_function
                -- TODO: hacer funcion para obtener el valor de START_APN
                IF POSITION('START_APN' IN formula_text) > 0 THEN
                    DECLARE
                        staff_start_dates INTEGER[][];
                        pair INTEGER[];
                        staff_id INT;
                        years INT;
                        start_apn_value NUMERIC := 0; -- Inicializar el valor como 0
                    BEGIN
                        -- Iterar sobre cada subarreglo usando FOREACH con SLICE 1
                        IF staff_apn_years IS NOT NULL AND array_length(staff_apn_years, 1) > 0 THEN
                            FOREACH pair SLICE 1 IN ARRAY staff_apn_years LOOP
                                staff_id := pair[1];
                                years := pair[2];

                                -- Realizar la validación deseada
                                IF staff_id = current_staff_id THEN
                                    start_apn_value := years;
                                END IF;
                            END LOOP;
                        END IF;

                        -- Reemplazar START_DATE en la fórmula
                        formula_text := REPLACE(
                            formula_text,
                            'START_APN',
                            COALESCE(start_apn_value, 0)::TEXT
                        );
                    END;
                END IF;

                -- Reemplazar VACATION_DAYS
                IF POSITION('VACATION_DAYS' IN formula_text) > 0 OR POSITION('ADDITIONAL_DAYS_PER_YEAR' IN formula_text) > 0 THEN
                    DECLARE
                        additional_days_per_year_value NUMERIC;
                        vacation_days_value NUMERIC;
                        assign_to_value JSONB;
                    BEGIN
                        -- Obtener el valor de VACATION_DAYS desde payroll_vacation_policies
                        SELECT additional_days_per_year, vacation_days, assign_to
                        INTO additional_days_per_year_value, vacation_days_value, assign_to_value
                        FROM payroll_vacation_policies
                        WHERE EXISTS (
                            SELECT 1
                            FROM payroll_staff_assign_to_function(
                                concept_id,
                                (assign_to)::JSONB,
                                period_start,
                                period_end
                            ) psatf
                            WHERE psatf.staff_id = current_staff_id
                        );

                        -- Reemplazar ADDITIONAL_DAYS_PER_YEAR
                        IF POSITION('ADDITIONAL_DAYS_PER_YEAR' IN formula_text) > 0 THEN
                            -- Reemplazar ADDITIONAL_DAYS_PER_YEAR en la fórmula
                            formula_text := REPLACE(
                                formula_text,
                                'ADDITIONAL_DAYS_PER_YEAR',
                                COALESCE(additional_days_per_year_value, 0)::TEXT
                            );    
                        END IF;

                        IF POSITION('VACATION_DAYS' IN formula_text) > 0 THEN
                            -- Reemplazar VACATION_DAYS en la fórmula
                            formula_text := REPLACE(
                                formula_text,
                                'VACATION_DAYS',
                                COALESCE(vacation_days_value, 0)::TEXT
                            );
                        END IF;
                    END;
                END IF;

                -- Reemplazar DAYS_REQUESTED
                IF POSITION('DAYS_REQUESTED' IN formula_text) > 0 THEN
                    DECLARE
                        days_requested_value NUMERIC;
                    BEGIN
                        -- Obtener el valor de DAYS_REQUESTED desde payroll_vacation_requests
                        SELECT days_requested
                        INTO days_requested_value
                        FROM payroll_vacation_requests
                        WHERE payroll_staff_id = current_staff_id;

                        -- Reemplazar DAYS_REQUESTED en la fórmula
                        formula_text := REPLACE(
                            formula_text,
                            'DAYS_REQUESTED',
                            COALESCE(days_requested_value, 0)::TEXT
                        );
                    END;
                END IF;

                -- Reemplazar BENEFIT_DAYS
                IF POSITION('BENEFIT_DAYS' IN formula_text) > 0 THEN
                    DECLARE
                        benefit_days_value NUMERIC;
                    BEGIN
                        -- Obtener el valor de BENEFIT_DAYS desde payroll_benefits_policies
                        SELECT benefit_days
                        INTO benefit_days_value
                        FROM payroll_benefits_policies
                        WHERE active = TRUE
                        AND start_date >= period_start
                        AND (end_date IS NULL OR end_date <= period_end)
                        AND institution_id = institution_id;

                        -- Reemplazar BENEFIT_DAYS en la fórmula
                        formula_text := REPLACE(
                            formula_text,
                            'BENEFIT_DAYS',
                            COALESCE(benefit_days_value, 0)::TEXT
                        );
                    END;
                END IF;

                -- Reemplazar BENEFIT_ADDITIONAL_DAYS_PER_YEAR
                IF POSITION('BENEFIT_ADDITIONAL_DAYS_PER_YEAR' IN formula_text) > 0 THEN
                    DECLARE
                        benefit_additional_days_per_year_value NUMERIC;
                    BEGIN
                        -- Obtener el valor de BENEFIT_ADDITIONAL_DAYS_PER_YEAR desde payroll_benefits_policies
                        SELECT additional_days_per_year
                        INTO benefit_additional_days_per_year_value
                        FROM payroll_benefits_policies
                        WHERE active = TRUE
                        AND start_date >= period_start
                        AND (end_date IS NULL OR end_date <= period_end)
                        AND institution_id = institution_id;

                        -- Reemplazar BENEFIT_ADDITIONAL_DAYS_PER_YEAR en la fórmula
                        formula_text := REPLACE(
                            formula_text,
                            'BENEFIT_ADDITIONAL_DAYS_PER_YEAR',
                            COALESCE(benefit_additional_days_per_year_value, 0)::TEXT
                        );
                    END;
                END IF;

                -- Reemplazar WORK_INTERRUPTION_DAYS
                IF POSITION('WORK_INTERRUPTION_DAYS' IN formula_text) > 0 THEN
                    DECLARE
                        work_interruption_days_value NUMERIC;
                    BEGIN
                        -- Obtener el valor de WORK_INTERRUPTION_DAYS desde payroll_benefits_policies
                        SELECT work_interruption_days
                        INTO work_interruption_days_value
                        FROM payroll_benefits_policies
                        WHERE active = TRUE
                        AND start_date >= period_start
                        AND (end_date IS NULL OR end_date <= period_end)
                        AND institution_id = institution_id;

                        -- Reemplazar WORK_INTERRUPTION_DAYS en la fórmula
                        formula_text := REPLACE(
                            formula_text,
                            'WORK_INTERRUPTION_DAYS',
                            COALESCE(work_interruption_days_value, 0)::TEXT
                        );
                    END;
                END IF;

                -- Reemplazar MONTH_WORKED_DAYS
                IF POSITION('MONTH_WORKED_DAYS' IN formula_text) > 0 THEN
                    DECLARE
                        month_worked_days_value NUMERIC;
                    BEGIN
                        -- Obtener el valor de MONTH_WORKED_DAYS desde payroll_benefits_policies
                        SELECT month_worked_days
                        INTO month_worked_days_value
                        FROM payroll_benefits_policies
                        WHERE active = TRUE
                        AND start_date >= period_start
                        AND (end_date IS NULL OR end_date <= period_end)
                        AND institution_id = institution_id;

                        -- Reemplazar MONTH_WORKED_DAYS en la fórmula
                        formula_text := REPLACE(
                            formula_text,
                            'MONTH_WORKED_DAYS',
                            COALESCE(month_worked_days_value, 0)::TEXT
                        );
                    END;
                END IF;

                -- Reemplazar ari_register
                IF POSITION('ari_register' IN formula_text) > 0 THEN
                    DECLARE
                        ari_register_value NUMERIC;
                    BEGIN
                        -- Obtener el valor de ari_register desde payroll_benefits_policies
                        SELECT percetage
                        INTO ari_register_value
                        FROM payroll_ari_registers
                        WHERE payroll_staff_id = current_staff_id
                        AND from_date >= period_start
                        AND (to_date IS NULL OR to_date <= period_end);

                        -- Reemplazar ari_register en la fórmula
                        formula_text := REPLACE(
                            formula_text,
                            'ari_register',
                            COALESCE(ari_register_value, 0)::TEXT
                        );
                    END;
                END IF;

                -- Reemplazar savings_fund
                IF POSITION('savings_fund' IN formula_text) > 0 THEN
                    DECLARE
                        savings_fund_value NUMERIC;
                    BEGIN
                        -- Obtener el valor de savings_fund desde payroll_benefits_policies
                        SELECT percetage
                        INTO savings_fund_value
                        FROM payroll_savings_funds
                        WHERE payroll_staff_id = current_staff_id
                        AND from_date >= period_start
                        AND (to_date IS NULL OR to_date <= period_end);

                        -- Reemplazar savings_fund en la fórmula
                        formula_text := REPLACE(
                            formula_text,
                            'savings_fund',
                            COALESCE(savings_fund_value, 0)::TEXT
                        );
                    END;
                END IF;

                -- Reemplazar wage_garnishment
                IF POSITION('wage_garnishment' IN formula_text) > 0 THEN
                    DECLARE
                        wage_garnishment_value NUMERIC;
                    BEGIN
                        -- Obtener el valor de wage_garnishment desde payroll_benefits_policies
                        SELECT percetage
                        INTO wage_garnishment_value
                        FROM payroll_wage_garnishments
                        WHERE payroll_staff_id = current_staff_id
                        AND from_date >= period_start
                        AND (to_date IS NULL OR to_date <= period_end);

                        -- Reemplazar wage_garnishment en la fórmula
                        formula_text := REPLACE(
                            formula_text,
                            'wage_garnishment',
                            COALESCE(wage_garnishment_value, 0)::TEXT
                        );
                    END;
                END IF;

                -- Evaluar fórmula final con manejo de división por cero
                BEGIN
                    EXECUTE 'SELECT ' || formula_text INTO result;
                EXCEPTION
                    WHEN OTHERS THEN
                        -- Capturar cualquier excepción y asignar 0 al resultado
                        result := 0;
                END;

                -- Devolver resultado
                RETURN result;
            END;
            $$ LANGUAGE plpgsql;
            SQL
        );

        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION process_formula(
                staff_ids INT[], -- Cambiado para recibir un array de IDs
                concept_id INT,
                period_start DATE,
                period_end DATE,
                institution_id INT,
                payroll_id INT,
                processed_concepts JSONB DEFAULT '{}'::JSONB,
                resettable_vars JSONB DEFAULT '{}'::JSONB
            ) RETURNS TABLE (
                staff_id INT, -- Agregado para incluir el ID del empleado en los resultados
                staff_full_name TEXT,
                staff_id_number TEXT,
                instruction_degree TEXT,
                staff_position TEXT,
                staff_start_date DATE,
                institution_years INT,
                concept_type TEXT,
                concept_name TEXT,
                calculated_value NUMERIC,
                concept_formula TEXT
            ) LANGUAGE plpgsql AS $$
            DECLARE
                formula_text TEXT;
                parameter RECORD;
                match TEXT;
                matches TEXT[];
                p_tabulator_id INT;
                tabulator_value NUMERIC;
                result NUMERIC := 0;
                recursive_result RECORD;
                current_staff_id INT;
                staff_start_date_years INT[];
                staff_apn_years INT[];
                start_apn_value INT := 0;
                start_date_value INT := 0;
            BEGIN
                -- Obtener los start_date de los trabajadores a partir de la funcion get_staff_start_date_function
                SELECT * INTO staff_start_date_years
                FROM get_staff_start_date_function(staff_ids, period_start, period_end);

                -- Obtener los start_date de los trabajadores a partir de la funcion get_staff_start_apn_function
                SELECT * INTO staff_apn_years
                FROM get_staff_start_apn_function(staff_ids, period_start, period_end);

                -- Iterar sobre cada staff_id en el array
                FOREACH current_staff_id IN ARRAY staff_ids LOOP
                    -- Obtener el nombre completo del empleado
                    SELECT CONCAT_WS(' ', first_name, last_name) INTO staff_full_name
                    FROM payroll_staffs
                    WHERE id = current_staff_id;

                    -- Obtener el nombre completo del empleado
                    SELECT id_number INTO staff_id_number
                    FROM payroll_staffs
                    WHERE id = current_staff_id;

                    -- Obtener el grado de instrucción del empleado
                    SELECT name INTO instruction_degree
                    FROM payroll_instruction_degrees
                    WHERE id = (
                        SELECT payroll_instruction_degree_id
                        FROM payroll_professionals
                        WHERE payroll_staff_id = current_staff_id
                    );

                    -- Obtener el cargo del empleado
                    SELECT name INTO staff_position
                    FROM payroll_positions
                    WHERE id = (
                        SELECT payroll_position_id
                        FROM payroll_employment_payroll_position
                        WHERE payroll_employment_id = (
                            SELECT id FROM payroll_employments
                            WHERE payroll_staff_id = current_staff_id
                        )
                    );

                    -- Obtener la fecha de inicio en las insitución del empleado
                    SELECT start_date INTO staff_start_date
                    FROM payroll_employments
                    WHERE payroll_staff_id = current_staff_id;

                    -- Obtener fórmula
                    SELECT formula INTO formula_text
                    FROM payroll_concepts
                    WHERE id = concept_id;

                    -- Procesar llamadas a concept(...)
                    matches := ARRAY(SELECT regexp_matches(formula_text, 'concept\((\d+)\)', 'g'))::TEXT[];

                    IF matches IS NULL THEN
                        matches := ARRAY[]::TEXT[];
                    END IF;

                    FOREACH match IN ARRAY matches LOOP
                        SELECT * INTO recursive_result FROM process_formula(
                            ARRAY[current_staff_id], -- Pasar el ID actual como un array de un solo elemento
                            match::INT,
                            period_start,
                            period_end,
                            institution_id,
                            payroll_id,
                            processed_concepts,
                            resettable_vars
                        );

                        processed_concepts := jsonb_set(
                            processed_concepts,
                            ARRAY[match],
                            to_jsonb(recursive_result.calculated_value),
                            true
                        );

                        formula_text := REPLACE(
                            formula_text,
                            'concept(' || match || ')',
                            recursive_result.calculated_value::TEXT
                        );
                    END LOOP;

                    -- Obtener el valor de START_DATE desde staff_start_date_years
                    DECLARE
                        staff_start_dates INTEGER[][];
                        pair INTEGER[];
                        staff_id INT;
                        years INT;
                    BEGIN
                        start_date_value  := 0;
                        -- Iterar sobre cada subarreglo usando FOREACH con SLICE 1
                        IF staff_start_date_years IS NOT NULL AND array_length(staff_start_date_years, 1) > 0 THEN

                            FOREACH pair SLICE 1 IN ARRAY staff_start_date_years LOOP
                                staff_id := pair[1];
                                years := pair[2];

                                -- Realizar la validación deseada
                                IF staff_id = current_staff_id THEN
                                    start_date_value := years;
                                END IF;
                            END LOOP;
                        END IF;

                        IF POSITION('START_DATE' IN formula_text) > 0 THEN
                            -- Reemplazar START_DATE en la fórmula
                            formula_text := REPLACE(
                                formula_text,
                                'START_DATE',
                                COALESCE(start_date_value, 0)::TEXT
                            );
                        END IF;

                        institution_years := COALESCE(start_date_value, 0)::INT;
                    END;

                    -- Reemplazar START_APN con el valor desde get_staff_start_apn_function
                    -- TODO: hacer funcion para obtener el valor de START_APN
                    DECLARE
                        staff_start_dates INTEGER[][];
                        pair INTEGER[];
                        staff_id INT;
                        years INT;
                    BEGIN
                        -- Iterar sobre cada subarreglo usando FOREACH con SLICE 1
                        IF staff_apn_years IS NOT NULL AND array_length(staff_apn_years, 1) > 0 THEN
                            FOREACH pair SLICE 1 IN ARRAY staff_apn_years LOOP
                                staff_id := pair[1];
                                years := pair[2];

                                -- Realizar la validación deseada
                                IF staff_id = current_staff_id THEN
                                    start_apn_value := years;
                                END IF;
                            END LOOP;
                        END IF;

                        IF POSITION('START_APN' IN formula_text) > 0 THEN
                            -- Reemplazar START_DATE en la fórmula
                            formula_text := REPLACE(
                                formula_text,
                                'START_APN',
                                COALESCE(start_apn_value, 0)::TEXT
                            );
                        END IF;
                    END;

                    -- Reemplazar tabuladores
                    matches := ARRAY(SELECT regexp_matches(formula_text, 'tabulator\((\d+)\)', 'g'))::TEXT[];

                    IF matches IS NULL THEN
                        matches := ARRAY[]::TEXT[];
                    END IF;

                    FOREACH match IN ARRAY matches LOOP
                        p_tabulator_id := match::INT;

                        SELECT scale_value INTO tabulator_value
                        FROM payroll_staff_tabulators_function(
                            current_staff_id,
                            p_tabulator_id,
                            start_date_value,
                            start_apn_value,
                            period_start,
                            period_end
                        );

                        formula_text := REPLACE(
                            formula_text,
                            'tabulator(' || p_tabulator_id || ')',
                            COALESCE(tabulator_value, 0)::TEXT
                        );
                    END LOOP;

                    -- Reemplazar parámetros tipo parameter(ID)
                    matches := ARRAY(SELECT regexp_matches(formula_text, 'parameter\(([^\)]+)\)', 'g'))::TEXT[];

                    IF matches IS NULL THEN
                        matches := ARRAY[]::TEXT[];
                    END IF;

                    FOREACH match IN ARRAY matches LOOP
                        -- Obtener el nombre del parámetro dentro de parameter(...)
                        formula_text := REPLACE(
                            formula_text,
                            'parameter(' || match || ')',
                            get_parameter_value('global_parameter_' || match, current_staff_id, period_start, period_end, institution_id, payroll_id, resettable_vars, concept_id)::TEXT
                        );
                    END LOOP;

                    -- Reemplazar WORKLOAD con el valor desde payroll_workload_positions
                    IF POSITION('WORKLOAD' IN formula_text) > 0 THEN
                        DECLARE
                            w_value NUMERIC;
                        BEGIN
                            -- Obtener el id de workload desde payroll_workload_positions
                            SELECT workload_value
                            INTO w_value
                            FROM payroll_staff_filter_parameters_view
                            WHERE payroll_staff_id = current_staff_id;

                            -- Reemplazar WORKLOAD en la fórmula
                            formula_text := REPLACE(
                                formula_text,
                                'WORKLOAD',
                                COALESCE(w_value, 0)::TEXT
                            );
                        END;
                    END IF;

                    -- Reemplazar NUMBER_LANG con el valor desde payroll_staff_filter_parameters_view
                    IF POSITION('NUMBER_LANG' IN formula_text) > 0 THEN
                        DECLARE
                            number_lang_value NUMERIC;
                        BEGIN
                            -- Obtener el valor de NUMBER_LANG desde payroll_staff_filter_parameters_view
                            SELECT professional_language_count
                            INTO number_lang_value
                            FROM payroll_staff_filter_parameters_view
                            WHERE payroll_staff_id = current_staff_id;

                            -- Reemplazar NUMBER_LANG en la fórmula
                            formula_text := REPLACE(
                                formula_text,
                                'NUMBER_LANG',
                                COALESCE(number_lang_value, 0)::TEXT
                            );
                        END;
                    END IF;

                    -- Reemplazar NUMBER_CHILDREN con el valor desde payroll_staff_filter_parameters_view
                    IF POSITION('NUMBER_CHILDREN' IN formula_text) > 0 THEN
                        DECLARE
                            number_children_value NUMERIC;
                        BEGIN
                            -- Obtener el valor de NUMBER_CHILDREN desde payroll_staff_filter_parameters_view
                            SELECT children_count
                            INTO number_children_value
                            FROM payroll_staff_filter_parameters_view
                            WHERE payroll_staff_id = current_staff_id;

                            -- Reemplazar NUMBER_CHILDREN en la fórmula
                            formula_text := REPLACE(
                                formula_text,
                                'NUMBER_CHILDREN',
                                COALESCE(number_children_value, 0)::TEXT
                            );
                        END;
                    END IF;

                    -- Reemplazar VACATION_DAYS
                    IF POSITION('VACATION_DAYS' IN formula_text) > 0 OR POSITION('ADDITIONAL_DAYS_PER_YEAR' IN formula_text) > 0 THEN
                        DECLARE
                            additional_days_per_year_value NUMERIC;
                            vacation_days_value NUMERIC;
                            assign_to_value JSONB;
                        BEGIN
                            -- Obtener el valor de VACATION_DAYS desde payroll_vacation_policies
                            SELECT additional_days_per_year, vacation_days, assign_to
                            INTO additional_days_per_year_value, vacation_days_value, assign_to_value
                            FROM payroll_vacation_policies
                            WHERE EXISTS (
                                SELECT 1
                                FROM payroll_staff_assign_to_function(
                                    concept_id,
                                    (assign_to)::JSONB,
                                    period_start,
                                    period_end
                                ) psatf
                                WHERE psatf.staff_id = current_staff_id
                            );

                            -- Reemplazar ADDITIONAL_DAYS_PER_YEAR
                            IF POSITION('ADDITIONAL_DAYS_PER_YEAR' IN formula_text) > 0 THEN
                                -- Reemplazar ADDITIONAL_DAYS_PER_YEAR en la fórmula
                                formula_text := REPLACE(
                                    formula_text,
                                    'ADDITIONAL_DAYS_PER_YEAR',
                                    COALESCE(additional_days_per_year_value, 0)::TEXT
                                );    
                            END IF;

                            IF POSITION('VACATION_DAYS' IN formula_text) > 0 THEN
                                -- Reemplazar VACATION_DAYS en la fórmula
                                formula_text := REPLACE(
                                    formula_text,
                                    'VACATION_DAYS',
                                    COALESCE(vacation_days_value, 0)::TEXT
                                );
                            END IF;
                        END;
                    END IF;

                    -- Reemplazar DAYS_REQUESTED
                    IF POSITION('DAYS_REQUESTED' IN formula_text) > 0 THEN
                        DECLARE
                            days_requested_value NUMERIC;
                        BEGIN
                            -- Obtener el valor de DAYS_REQUESTED desde payroll_vacation_requests
                            SELECT days_requested
                            INTO days_requested_value
                            FROM payroll_vacation_requests
                            WHERE payroll_staff_id = current_staff_id;

                            -- Reemplazar DAYS_REQUESTED en la fórmula
                            formula_text := REPLACE(
                                formula_text,
                                'DAYS_REQUESTED',
                                COALESCE(days_requested_value, 0)::TEXT
                            );
                        END;
                    END IF;

                    -- Reemplazar BENEFIT_DAYS
                    IF POSITION('BENEFIT_DAYS' IN formula_text) > 0 THEN
                        DECLARE
                            benefit_days_value NUMERIC;
                        BEGIN
                            -- Obtener el valor de BENEFIT_DAYS desde payroll_benefits_policies
                            SELECT benefit_days
                            INTO benefit_days_value
                            FROM payroll_benefits_policies
                            WHERE active = TRUE
                            AND start_date >= period_start
                            AND (end_date IS NULL OR end_date <= period_end)
                            AND institution_id = institution_id;

                            -- Reemplazar BENEFIT_DAYS en la fórmula
                            formula_text := REPLACE(
                                formula_text,
                                'BENEFIT_DAYS',
                                COALESCE(benefit_days_value, 0)::TEXT
                            );
                        END;
                    END IF;

                    -- Reemplazar BENEFIT_ADDITIONAL_DAYS_PER_YEAR
                    IF POSITION('BENEFIT_ADDITIONAL_DAYS_PER_YEAR' IN formula_text) > 0 THEN
                        DECLARE
                            benefit_additional_days_per_year_value NUMERIC;
                        BEGIN
                            -- Obtener el valor de BENEFIT_ADDITIONAL_DAYS_PER_YEAR desde payroll_benefits_policies
                            SELECT additional_days_per_year
                            INTO benefit_additional_days_per_year_value
                            FROM payroll_benefits_policies
                            WHERE active = TRUE
                            AND start_date >= period_start
                            AND (end_date IS NULL OR end_date <= period_end)
                            AND institution_id = institution_id;

                            -- Reemplazar BENEFIT_ADDITIONAL_DAYS_PER_YEAR en la fórmula
                            formula_text := REPLACE(
                                formula_text,
                                'BENEFIT_ADDITIONAL_DAYS_PER_YEAR',
                                COALESCE(benefit_additional_days_per_year_value, 0)::TEXT
                            );
                        END;
                    END IF;

                    -- Reemplazar WORK_INTERRUPTION_DAYS
                    IF POSITION('WORK_INTERRUPTION_DAYS' IN formula_text) > 0 THEN
                        DECLARE
                            work_interruption_days_value NUMERIC;
                        BEGIN
                            -- Obtener el valor de WORK_INTERRUPTION_DAYS desde payroll_benefits_policies
                            SELECT work_interruption_days
                            INTO work_interruption_days_value
                            FROM payroll_benefits_policies
                            WHERE active = TRUE
                            AND start_date >= period_start
                            AND (end_date IS NULL OR end_date <= period_end)
                            AND institution_id = institution_id;

                            -- Reemplazar WORK_INTERRUPTION_DAYS en la fórmula
                            formula_text := REPLACE(
                                formula_text,
                                'WORK_INTERRUPTION_DAYS',
                                COALESCE(work_interruption_days_value, 0)::TEXT
                            );
                        END;
                    END IF;

                    -- Reemplazar MONTH_WORKED_DAYS
                    IF POSITION('MONTH_WORKED_DAYS' IN formula_text) > 0 THEN
                        DECLARE
                            month_worked_days_value NUMERIC;
                        BEGIN
                            -- Obtener el valor de MONTH_WORKED_DAYS desde payroll_benefits_policies
                            SELECT month_worked_days
                            INTO month_worked_days_value
                            FROM payroll_benefits_policies
                            WHERE active = TRUE
                            AND start_date >= period_start
                            AND (end_date IS NULL OR end_date <= period_end)
                            AND institution_id = institution_id;

                            -- Reemplazar MONTH_WORKED_DAYS en la fórmula
                            formula_text := REPLACE(
                                formula_text,
                                'MONTH_WORKED_DAYS',
                                COALESCE(month_worked_days_value, 0)::TEXT
                            );
                        END;
                    END IF;

                    -- Reemplazar ari_register
                    IF POSITION('ari_register' IN formula_text) > 0 THEN
                        DECLARE
                            ari_register_value NUMERIC;
                        BEGIN
                            -- Obtener el valor de ari_register desde payroll_benefits_policies
                            SELECT percetage
                            INTO ari_register_value
                            FROM payroll_ari_registers
                            WHERE payroll_staff_id = current_staff_id
                            AND from_date >= period_start
                            AND (to_date IS NULL OR to_date <= period_end);

                            -- Reemplazar ari_register en la fórmula
                            formula_text := REPLACE(
                                formula_text,
                                'ari_register',
                                COALESCE(ari_register_value, 0)::TEXT
                            );
                        END;
                    END IF;

                    -- Reemplazar savings_fund
                    IF POSITION('savings_fund' IN formula_text) > 0 THEN
                        DECLARE
                            savings_fund_value NUMERIC;
                        BEGIN
                            -- Obtener el valor de savings_fund desde payroll_benefits_policies
                            SELECT percetage
                            INTO savings_fund_value
                            FROM payroll_savings_funds
                            WHERE payroll_staff_id = current_staff_id
                            AND from_date >= period_start
                            AND (to_date IS NULL OR to_date <= period_end);

                            -- Reemplazar savings_fund en la fórmula
                            formula_text := REPLACE(
                                formula_text,
                                'savings_fund',
                                COALESCE(savings_fund_value, 0)::TEXT
                            );
                        END;
                    END IF;

                    -- Reemplazar wage_garnishment
                    IF POSITION('wage_garnishment' IN formula_text) > 0 THEN
                        DECLARE
                            wage_garnishment_value NUMERIC;
                        BEGIN
                            -- Obtener el valor de wage_garnishment desde payroll_benefits_policies
                            SELECT percetage
                            INTO wage_garnishment_value
                            FROM payroll_wage_garnishments
                            WHERE payroll_staff_id = current_staff_id
                            AND from_date >= period_start
                            AND (to_date IS NULL OR to_date <= period_end);

                            -- Reemplazar wage_garnishment en la fórmula
                            formula_text := REPLACE(
                                formula_text,
                                'wage_garnishment',
                                COALESCE(wage_garnishment_value, 0)::TEXT
                            );
                        END;
                    END IF;

                    BEGIN
                        EXECUTE 'SELECT ' || formula_text INTO result;
                        -- Redondear el resultado a dos decimales
                        result := ROUND(result, 2);
                    EXCEPTION
                        WHEN OTHERS THEN
                            -- Capturar cualquier excepción y asignar 0 al resultado
                            result := 0;
                    END;

                    -- Redondear los valores numéricos en formula_text a dos decimales
                    formula_text := regexp_replace(
                        formula_text,
                        '(\d+\.\d{2})\d*',
                        '\1',
                        'g'
                    );

                    -- Devolver resultado para el staff_id actual
                    RETURN QUERY SELECT 
                        current_staff_id,
                        staff_full_name,
                        staff_id_number,
                        instruction_degree,
                        staff_position,
                        staff_start_date,
                        institution_years,
                        (SELECT pt.name::TEXT FROM payroll_concepts pc JOIN payroll_concept_types pt ON pc.payroll_concept_type_id = pt.id WHERE pc.id = concept_id),
                        (SELECT name::TEXT FROM payroll_concepts WHERE id = concept_id),
                        result,
                        formula_text::TEXT;
                END LOOP;
            END;
            $$;
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
        DB::statement('DROP FUNCTION IF EXISTS process_formula;');
        DB::statement('DROP FUNCTION IF EXISTS process_parameter_formula(TEXT, INT, DATE, DATE, INT, INT, JSONB);');

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
                            SELECT (param->>'value')::NUMERIC INTO result
                            FROM jsonb_array_elements(
                                (SELECT payroll_parameters
                                FROM payrolls
                                WHERE id = payroll_id)::JSONB
                            ) AS param
                            WHERE (param->>'concept_id')::INT = concept_id;

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
        
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION process_parameter_formula(
                formula_text TEXT,
                current_staff_id INT,
                period_start DATE,
                period_end DATE,
                institution_id INT,
                payroll_id INT,
                resettable_vars JSONB DEFAULT '{}'::JSONB,
                concept_id INT DEFAULT 0
            ) RETURNS NUMERIC AS $$
            DECLARE
                parameter RECORD;
                match TEXT;
                matches TEXT[];
                result NUMERIC := 0;
                staff_start_date_years INT[];
                staff_apn_years INT[];
            BEGIN
                -- Obtener los start_date de los trabajadores a partir de la funcion get_staff_start_date_function
                SELECT * INTO staff_start_date_years
                FROM get_staff_start_date_function(ARRAY[current_staff_id], period_start, period_end);

                -- Obtener los start_date de los trabajadores a partir de la funcion get_staff_start_apn_function
                SELECT * INTO staff_apn_years
                FROM get_staff_start_apn_function(ARRAY[current_staff_id], period_start, period_end);

                -- Reemplazar parámetros tipo parameter(ID)
                matches := ARRAY(SELECT regexp_matches(formula_text, 'parameter\(([^\)]+)\)', 'g'))::TEXT[];

                IF matches IS NULL THEN
                    matches := ARRAY[]::TEXT[];
                END IF;

                FOREACH match IN ARRAY matches LOOP
                    -- Obtener el nombre del parámetro dentro de parameter(...)
                    formula_text := REPLACE(
                        formula_text,
                        'parameter(' || match || ')',
                        get_parameter_value('global_parameter_' || match, current_staff_id, period_start, period_end, institution_id, payroll_id, resettable_vars, concept_id)::TEXT
                    );
                END LOOP;

                -- Reemplazar WORKLOAD con el valor desde payroll_workload_positions
                IF POSITION('WORKLOAD' IN formula_text) > 0 THEN
                    DECLARE
                        w_value NUMERIC;
                    BEGIN
                        -- Obtener el id de workload desde payroll_workload_positions
                        SELECT workload_value
                        INTO w_value
                        FROM payroll_staff_filter_parameters_view
                        WHERE payroll_staff_id = current_staff_id;

                        -- Reemplazar WORKLOAD en la fórmula
                        formula_text := REPLACE(
                            formula_text,
                            'WORKLOAD',
                            COALESCE(w_value, 0)::TEXT
                        );
                    END;
                END IF;

                -- Reemplazar NUMBER_LANG con el valor desde payroll_staff_filter_parameters_view
                IF POSITION('NUMBER_LANG' IN formula_text) > 0 THEN
                    DECLARE
                        number_lang_value NUMERIC;
                    BEGIN
                        -- Obtener el valor de NUMBER_LANG desde payroll_staff_filter_parameters_view
                        SELECT professional_language_count
                        INTO number_lang_value
                        FROM payroll_staff_filter_parameters_view
                        WHERE payroll_staff_id = current_staff_id;

                        -- Reemplazar NUMBER_LANG en la fórmula
                        formula_text := REPLACE(
                            formula_text,
                            'NUMBER_LANG',
                            COALESCE(number_lang_value, 0)::TEXT
                        );
                    END;
                END IF;

                -- Reemplazar NUMBER_CHILDREN con el valor desde payroll_staff_filter_parameters_view
                IF POSITION('NUMBER_CHILDREN' IN formula_text) > 0 THEN
                    DECLARE
                        number_children_value NUMERIC;
                    BEGIN
                        -- Obtener el valor de NUMBER_CHILDREN desde payroll_staff_filter_parameters_view
                        SELECT children_count
                        INTO number_children_value
                        FROM payroll_staff_filter_parameters_view
                        WHERE payroll_staff_id = current_staff_id;

                        -- Reemplazar NUMBER_CHILDREN en la fórmula
                        formula_text := REPLACE(
                            formula_text,
                            'NUMBER_CHILDREN',
                            COALESCE(number_children_value, 0)::TEXT
                        );
                    END;
                END IF;

                -- Obtener el valor de START_DATE desde staff_start_date_years
                IF POSITION('START_DATE' IN formula_text) > 0 THEN
                    DECLARE
                        staff_start_dates INTEGER[][];
                        pair INTEGER[];
                        staff_id INT;
                        years INT;
                        start_date_value NUMERIC := 0; -- Inicializar el valor como 0
                    BEGIN
                        -- Iterar sobre cada subarreglo usando FOREACH con SLICE 1
                        IF staff_start_date_years IS NOT NULL AND array_length(staff_start_date_years, 1) > 0 THEN
                            FOREACH pair SLICE 1 IN ARRAY staff_start_date_years LOOP
                                staff_id := pair[1];
                                years := pair[2];

                                -- Realizar la validación deseada
                                IF staff_id = current_staff_id THEN
                                    start_date_value := years;
                                END IF;
                            END LOOP;
                        END IF;

                        -- Reemplazar START_DATE en la fórmula
                        formula_text := REPLACE(
                            formula_text,
                            'START_DATE',
                            COALESCE(start_date_value, 0)::TEXT
                        );
                    END;
                END IF;

                -- Reemplazar START_APN con el valor desde get_staff_start_apn_function
                -- TODO: hacer funcion para obtener el valor de START_APN
                IF POSITION('START_APN' IN formula_text) > 0 THEN
                    DECLARE
                        staff_start_dates INTEGER[][];
                        pair INTEGER[];
                        staff_id INT;
                        years INT;
                        start_apn_value NUMERIC := 0; -- Inicializar el valor como 0
                    BEGIN
                        -- Iterar sobre cada subarreglo usando FOREACH con SLICE 1
                        IF staff_apn_years IS NOT NULL AND array_length(staff_apn_years, 1) > 0 THEN
                            FOREACH pair SLICE 1 IN ARRAY staff_apn_years LOOP
                                staff_id := pair[1];
                                years := pair[2];

                                -- Realizar la validación deseada
                                IF staff_id = current_staff_id THEN
                                    start_apn_value := years;
                                END IF;
                            END LOOP;
                        END IF;

                        -- Reemplazar START_DATE en la fórmula
                        formula_text := REPLACE(
                            formula_text,
                            'START_APN',
                            COALESCE(start_apn_value, 0)::TEXT
                        );
                    END;
                END IF;

                -- Reemplazar VACATION_DAYS
                IF POSITION('VACATION_DAYS' IN formula_text) > 0 OR POSITION('ADDITIONAL_DAYS_PER_YEAR' IN formula_text) > 0 THEN
                    DECLARE
                        additional_days_per_year_value NUMERIC;
                        vacation_days_value NUMERIC;
                        assign_to_value JSONB;
                    BEGIN
                        -- Obtener el valor de VACATION_DAYS desde payroll_vacation_policies
                        SELECT additional_days_per_year, vacation_days, assign_to
                        INTO additional_days_per_year_value, vacation_days_value, assign_to_value
                        FROM payroll_vacation_policies
                        WHERE EXISTS (
                            SELECT 1
                            FROM payroll_staff_assign_to_function(
                                concept_id,
                                (assign_to)::JSONB,
                                period_start,
                                period_end
                            ) psatf
                            WHERE psatf.staff_id = current_staff_id
                        );

                        -- Reemplazar ADDITIONAL_DAYS_PER_YEAR
                        IF POSITION('ADDITIONAL_DAYS_PER_YEAR' IN formula_text) > 0 THEN
                            -- Reemplazar ADDITIONAL_DAYS_PER_YEAR en la fórmula
                            formula_text := REPLACE(
                                formula_text,
                                'ADDITIONAL_DAYS_PER_YEAR',
                                COALESCE(additional_days_per_year_value, 0)::TEXT
                            );    
                        END IF;

                        IF POSITION('VACATION_DAYS' IN formula_text) > 0 THEN
                            -- Reemplazar VACATION_DAYS en la fórmula
                            formula_text := REPLACE(
                                formula_text,
                                'VACATION_DAYS',
                                COALESCE(vacation_days_value, 0)::TEXT
                            );
                        END IF;
                    END;
                END IF;

                -- Reemplazar DAYS_REQUESTED
                IF POSITION('DAYS_REQUESTED' IN formula_text) > 0 THEN
                    DECLARE
                        days_requested_value NUMERIC;
                    BEGIN
                        -- Obtener el valor de DAYS_REQUESTED desde payroll_vacation_requests
                        SELECT days_requested
                        INTO days_requested_value
                        FROM payroll_vacation_requests
                        WHERE payroll_staff_id = current_staff_id;

                        -- Reemplazar DAYS_REQUESTED en la fórmula
                        formula_text := REPLACE(
                            formula_text,
                            'DAYS_REQUESTED',
                            COALESCE(days_requested_value, 0)::TEXT
                        );
                    END;
                END IF;

                -- Reemplazar BENEFIT_DAYS
                IF POSITION('BENEFIT_DAYS' IN formula_text) > 0 THEN
                    DECLARE
                        benefit_days_value NUMERIC;
                    BEGIN
                        -- Obtener el valor de BENEFIT_DAYS desde payroll_benefits_policies
                        SELECT benefit_days
                        INTO benefit_days_value
                        FROM payroll_benefits_policies
                        WHERE active = TRUE
                        AND start_date >= period_start
                        AND (end_date IS NULL OR end_date <= period_end)
                        AND institution_id = institution_id;

                        -- Reemplazar BENEFIT_DAYS en la fórmula
                        formula_text := REPLACE(
                            formula_text,
                            'BENEFIT_DAYS',
                            COALESCE(benefit_days_value, 0)::TEXT
                        );
                    END;
                END IF;

                -- Reemplazar BENEFIT_ADDITIONAL_DAYS_PER_YEAR
                IF POSITION('BENEFIT_ADDITIONAL_DAYS_PER_YEAR' IN formula_text) > 0 THEN
                    DECLARE
                        benefit_additional_days_per_year_value NUMERIC;
                    BEGIN
                        -- Obtener el valor de BENEFIT_ADDITIONAL_DAYS_PER_YEAR desde payroll_benefits_policies
                        SELECT additional_days_per_year
                        INTO benefit_additional_days_per_year_value
                        FROM payroll_benefits_policies
                        WHERE active = TRUE
                        AND start_date >= period_start
                        AND (end_date IS NULL OR end_date <= period_end)
                        AND institution_id = institution_id;

                        -- Reemplazar BENEFIT_ADDITIONAL_DAYS_PER_YEAR en la fórmula
                        formula_text := REPLACE(
                            formula_text,
                            'BENEFIT_ADDITIONAL_DAYS_PER_YEAR',
                            COALESCE(benefit_additional_days_per_year_value, 0)::TEXT
                        );
                    END;
                END IF;

                -- Reemplazar WORK_INTERRUPTION_DAYS
                IF POSITION('WORK_INTERRUPTION_DAYS' IN formula_text) > 0 THEN
                    DECLARE
                        work_interruption_days_value NUMERIC;
                    BEGIN
                        -- Obtener el valor de WORK_INTERRUPTION_DAYS desde payroll_benefits_policies
                        SELECT work_interruption_days
                        INTO work_interruption_days_value
                        FROM payroll_benefits_policies
                        WHERE active = TRUE
                        AND start_date >= period_start
                        AND (end_date IS NULL OR end_date <= period_end)
                        AND institution_id = institution_id;

                        -- Reemplazar WORK_INTERRUPTION_DAYS en la fórmula
                        formula_text := REPLACE(
                            formula_text,
                            'WORK_INTERRUPTION_DAYS',
                            COALESCE(work_interruption_days_value, 0)::TEXT
                        );
                    END;
                END IF;

                -- Reemplazar MONTH_WORKED_DAYS
                IF POSITION('MONTH_WORKED_DAYS' IN formula_text) > 0 THEN
                    DECLARE
                        month_worked_days_value NUMERIC;
                    BEGIN
                        -- Obtener el valor de MONTH_WORKED_DAYS desde payroll_benefits_policies
                        SELECT month_worked_days
                        INTO month_worked_days_value
                        FROM payroll_benefits_policies
                        WHERE active = TRUE
                        AND start_date >= period_start
                        AND (end_date IS NULL OR end_date <= period_end)
                        AND institution_id = institution_id;

                        -- Reemplazar MONTH_WORKED_DAYS en la fórmula
                        formula_text := REPLACE(
                            formula_text,
                            'MONTH_WORKED_DAYS',
                            COALESCE(month_worked_days_value, 0)::TEXT
                        );
                    END;
                END IF;

                -- Reemplazar ari_register
                IF POSITION('ari_register' IN formula_text) > 0 THEN
                    DECLARE
                        ari_register_value NUMERIC;
                    BEGIN
                        -- Obtener el valor de ari_register desde payroll_benefits_policies
                        SELECT percetage
                        INTO ari_register_value
                        FROM payroll_ari_registers
                        WHERE payroll_staff_id = current_staff_id
                        AND from_date >= period_start
                        AND (to_date IS NULL OR to_date <= period_end);

                        -- Reemplazar ari_register en la fórmula
                        formula_text := REPLACE(
                            formula_text,
                            'ari_register',
                            COALESCE(ari_register_value, 0)::TEXT
                        );
                    END;
                END IF;

                -- Reemplazar savings_fund
                IF POSITION('savings_fund' IN formula_text) > 0 THEN
                    DECLARE
                        savings_fund_value NUMERIC;
                    BEGIN
                        -- Obtener el valor de savings_fund desde payroll_benefits_policies
                        SELECT percetage
                        INTO savings_fund_value
                        FROM payroll_savings_funds
                        WHERE payroll_staff_id = current_staff_id
                        AND from_date >= period_start
                        AND (to_date IS NULL OR to_date <= period_end);

                        -- Reemplazar savings_fund en la fórmula
                        formula_text := REPLACE(
                            formula_text,
                            'savings_fund',
                            COALESCE(savings_fund_value, 0)::TEXT
                        );
                    END;
                END IF;

                -- Reemplazar wage_garnishment
                IF POSITION('wage_garnishment' IN formula_text) > 0 THEN
                    DECLARE
                        wage_garnishment_value NUMERIC;
                    BEGIN
                        -- Obtener el valor de wage_garnishment desde payroll_benefits_policies
                        SELECT percetage
                        INTO wage_garnishment_value
                        FROM payroll_wage_garnishments
                        WHERE payroll_staff_id = current_staff_id
                        AND from_date >= period_start
                        AND (to_date IS NULL OR to_date <= period_end);

                        -- Reemplazar wage_garnishment en la fórmula
                        formula_text := REPLACE(
                            formula_text,
                            'wage_garnishment',
                            COALESCE(wage_garnishment_value, 0)::TEXT
                        );
                    END;
                END IF;

                -- Evaluar fórmula final con manejo de división por cero
                BEGIN
                    EXECUTE 'SELECT ' || formula_text INTO result;
                EXCEPTION
                    WHEN OTHERS THEN
                        -- Capturar cualquier excepción y asignar 0 al resultado
                        result := 0;
                END;

                -- Devolver resultado
                RETURN result;
            END;
            $$ LANGUAGE plpgsql;
            SQL
        );

        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION process_formula(
                staff_ids INT[], -- Cambiado para recibir un array de IDs
                concept_id INT,
                period_start DATE,
                period_end DATE,
                institution_id INT,
                payroll_id INT,
                processed_concepts JSONB DEFAULT '{}'::JSONB,
                resettable_vars JSONB DEFAULT '{}'::JSONB
            ) RETURNS TABLE (
                staff_id INT, -- Agregado para incluir el ID del empleado en los resultados
                staff_full_name TEXT,
                staff_id_number TEXT,
                instruction_degree TEXT,
                staff_position TEXT,
                staff_start_date DATE,
                institution_years INT,
                concept_type TEXT,
                concept_name TEXT,
                calculated_value NUMERIC,
                concept_formula TEXT
            ) LANGUAGE plpgsql AS $$
            DECLARE
                formula_text TEXT;
                parameter RECORD;
                match TEXT;
                matches TEXT[];
                p_tabulator_id INT;
                tabulator_value NUMERIC;
                result NUMERIC := 0;
                recursive_result RECORD;
                current_staff_id INT;
                staff_start_date_years INT[];
                staff_apn_years INT[];
                start_apn_value INT := 0;
                start_date_value INT := 0;
            BEGIN
                -- Obtener los start_date de los trabajadores a partir de la funcion get_staff_start_date_function
                SELECT * INTO staff_start_date_years
                FROM get_staff_start_date_function(staff_ids, period_start, period_end);

                -- Obtener los start_date de los trabajadores a partir de la funcion get_staff_start_apn_function
                SELECT * INTO staff_apn_years
                FROM get_staff_start_apn_function(staff_ids, period_start, period_end);

                -- Iterar sobre cada staff_id en el array
                FOREACH current_staff_id IN ARRAY staff_ids LOOP
                    -- Obtener el nombre completo del empleado
                    SELECT CONCAT_WS(' ', first_name, last_name) INTO staff_full_name
                    FROM payroll_staffs
                    WHERE id = current_staff_id;

                    -- Obtener el nombre completo del empleado
                    SELECT id_number INTO staff_id_number
                    FROM payroll_staffs
                    WHERE id = current_staff_id;

                    -- Obtener el grado de instrucción del empleado
                    SELECT name INTO instruction_degree
                    FROM payroll_instruction_degrees
                    WHERE id = (
                        SELECT payroll_instruction_degree_id
                        FROM payroll_professionals
                        WHERE payroll_staff_id = current_staff_id
                    );

                    -- Obtener el cargo del empleado
                    SELECT name INTO staff_position
                    FROM payroll_positions
                    WHERE id = (
                        SELECT payroll_position_id
                        FROM payroll_employment_payroll_position
                        WHERE payroll_employment_id = (
                            SELECT id FROM payroll_employments
                            WHERE payroll_staff_id = current_staff_id
                        )
                    );

                    -- Obtener la fecha de inicio en las insitución del empleado
                    SELECT start_date INTO staff_start_date
                    FROM payroll_employments
                    WHERE payroll_staff_id = current_staff_id;

                    -- Obtener fórmula
                    SELECT formula INTO formula_text
                    FROM payroll_concepts
                    WHERE id = concept_id;

                    -- Procesar llamadas a concept(...)
                    matches := ARRAY(SELECT regexp_matches(formula_text, 'concept\((\d+)\)', 'g'))::TEXT[];

                    IF matches IS NULL THEN
                        matches := ARRAY[]::TEXT[];
                    END IF;

                    FOREACH match IN ARRAY matches LOOP
                        SELECT * INTO recursive_result FROM process_formula(
                            ARRAY[current_staff_id], -- Pasar el ID actual como un array de un solo elemento
                            match::INT,
                            period_start,
                            period_end,
                            institution_id,
                            payroll_id,
                            processed_concepts,
                            resettable_vars
                        );

                        processed_concepts := jsonb_set(
                            processed_concepts,
                            ARRAY[match],
                            to_jsonb(recursive_result.calculated_value),
                            true
                        );

                        formula_text := REPLACE(
                            formula_text,
                            'concept(' || match || ')',
                            recursive_result.calculated_value::TEXT
                        );
                    END LOOP;

                    -- Obtener el valor de START_DATE desde staff_start_date_years
                    DECLARE
                        staff_start_dates INTEGER[][];
                        pair INTEGER[];
                        staff_id INT;
                        years INT;
                    BEGIN
                        start_date_value  := 0;
                        -- Iterar sobre cada subarreglo usando FOREACH con SLICE 1
                        IF staff_start_date_years IS NOT NULL AND array_length(staff_start_date_years, 1) > 0 THEN

                            FOREACH pair SLICE 1 IN ARRAY staff_start_date_years LOOP
                                staff_id := pair[1];
                                years := pair[2];

                                -- Realizar la validación deseada
                                IF staff_id = current_staff_id THEN
                                    start_date_value := years;
                                END IF;
                            END LOOP;
                        END IF;

                        IF POSITION('START_DATE' IN formula_text) > 0 THEN
                            -- Reemplazar START_DATE en la fórmula
                            formula_text := REPLACE(
                                formula_text,
                                'START_DATE',
                                COALESCE(start_date_value, 0)::TEXT
                            );
                        END IF;

                        institution_years := COALESCE(start_date_value, 0)::INT;
                    END;

                    -- Reemplazar START_APN con el valor desde get_staff_start_apn_function
                    -- TODO: hacer funcion para obtener el valor de START_APN
                    DECLARE
                        staff_start_dates INTEGER[][];
                        pair INTEGER[];
                        staff_id INT;
                        years INT;
                    BEGIN
                        -- Iterar sobre cada subarreglo usando FOREACH con SLICE 1
                        IF staff_apn_years IS NOT NULL AND array_length(staff_apn_years, 1) > 0 THEN
                            FOREACH pair SLICE 1 IN ARRAY staff_apn_years LOOP
                                staff_id := pair[1];
                                years := pair[2];

                                -- Realizar la validación deseada
                                IF staff_id = current_staff_id THEN
                                    start_apn_value := years;
                                END IF;
                            END LOOP;
                        END IF;

                        IF POSITION('START_APN' IN formula_text) > 0 THEN
                            -- Reemplazar START_DATE en la fórmula
                            formula_text := REPLACE(
                                formula_text,
                                'START_APN',
                                COALESCE(start_apn_value, 0)::TEXT
                            );
                        END IF;
                    END;

                    -- Reemplazar tabuladores
                    matches := ARRAY(SELECT regexp_matches(formula_text, 'tabulator\((\d+)\)', 'g'))::TEXT[];

                    IF matches IS NULL THEN
                        matches := ARRAY[]::TEXT[];
                    END IF;

                    FOREACH match IN ARRAY matches LOOP
                        p_tabulator_id := match::INT;

                        SELECT scale_value INTO tabulator_value
                        FROM payroll_staff_tabulators_function(
                            current_staff_id,
                            p_tabulator_id,
                            start_date_value,
                            start_apn_value,
                            period_start,
                            period_end
                        );

                        formula_text := REPLACE(
                            formula_text,
                            'tabulator(' || p_tabulator_id || ')',
                            COALESCE(tabulator_value, 0)::TEXT
                        );
                    END LOOP;

                    -- Reemplazar parámetros tipo parameter(ID)
                    matches := ARRAY(SELECT regexp_matches(formula_text, 'parameter\(([^\)]+)\)', 'g'))::TEXT[];

                    IF matches IS NULL THEN
                        matches := ARRAY[]::TEXT[];
                    END IF;

                    FOREACH match IN ARRAY matches LOOP
                        -- Obtener el nombre del parámetro dentro de parameter(...)
                        formula_text := REPLACE(
                            formula_text,
                            'parameter(' || match || ')',
                            get_parameter_value('global_parameter_' || match, current_staff_id, period_start, period_end, institution_id, payroll_id, resettable_vars, concept_id)::TEXT
                        );
                    END LOOP;

                    -- Reemplazar WORKLOAD con el valor desde payroll_workload_positions
                    IF POSITION('WORKLOAD' IN formula_text) > 0 THEN
                        DECLARE
                            w_value NUMERIC;
                        BEGIN
                            -- Obtener el id de workload desde payroll_workload_positions
                            SELECT workload_value
                            INTO w_value
                            FROM payroll_staff_filter_parameters_view
                            WHERE payroll_staff_id = current_staff_id;

                            -- Reemplazar WORKLOAD en la fórmula
                            formula_text := REPLACE(
                                formula_text,
                                'WORKLOAD',
                                COALESCE(w_value, 0)::TEXT
                            );
                        END;
                    END IF;

                    -- Reemplazar NUMBER_LANG con el valor desde payroll_staff_filter_parameters_view
                    IF POSITION('NUMBER_LANG' IN formula_text) > 0 THEN
                        DECLARE
                            number_lang_value NUMERIC;
                        BEGIN
                            -- Obtener el valor de NUMBER_LANG desde payroll_staff_filter_parameters_view
                            SELECT professional_language_count
                            INTO number_lang_value
                            FROM payroll_staff_filter_parameters_view
                            WHERE payroll_staff_id = current_staff_id;

                            -- Reemplazar NUMBER_LANG en la fórmula
                            formula_text := REPLACE(
                                formula_text,
                                'NUMBER_LANG',
                                COALESCE(number_lang_value, 0)::TEXT
                            );
                        END;
                    END IF;

                    -- Reemplazar NUMBER_CHILDREN con el valor desde payroll_staff_filter_parameters_view
                    IF POSITION('NUMBER_CHILDREN' IN formula_text) > 0 THEN
                        DECLARE
                            number_children_value NUMERIC;
                        BEGIN
                            -- Obtener el valor de NUMBER_CHILDREN desde payroll_staff_filter_parameters_view
                            SELECT children_count
                            INTO number_children_value
                            FROM payroll_staff_filter_parameters_view
                            WHERE payroll_staff_id = current_staff_id;

                            -- Reemplazar NUMBER_CHILDREN en la fórmula
                            formula_text := REPLACE(
                                formula_text,
                                'NUMBER_CHILDREN',
                                COALESCE(number_children_value, 0)::TEXT
                            );
                        END;
                    END IF;

                    -- Reemplazar VACATION_DAYS
                    IF POSITION('VACATION_DAYS' IN formula_text) > 0 OR POSITION('ADDITIONAL_DAYS_PER_YEAR' IN formula_text) > 0 THEN
                        DECLARE
                            additional_days_per_year_value NUMERIC;
                            vacation_days_value NUMERIC;
                            assign_to_value JSONB;
                        BEGIN
                            -- Obtener el valor de VACATION_DAYS desde payroll_vacation_policies
                            SELECT additional_days_per_year, vacation_days, assign_to
                            INTO additional_days_per_year_value, vacation_days_value, assign_to_value
                            FROM payroll_vacation_policies
                            WHERE EXISTS (
                                SELECT 1
                                FROM payroll_staff_assign_to_function(
                                    concept_id,
                                    (assign_to)::JSONB,
                                    period_start,
                                    period_end
                                ) psatf
                                WHERE psatf.staff_id = current_staff_id
                            );

                            -- Reemplazar ADDITIONAL_DAYS_PER_YEAR
                            IF POSITION('ADDITIONAL_DAYS_PER_YEAR' IN formula_text) > 0 THEN
                                -- Reemplazar ADDITIONAL_DAYS_PER_YEAR en la fórmula
                                formula_text := REPLACE(
                                    formula_text,
                                    'ADDITIONAL_DAYS_PER_YEAR',
                                    COALESCE(additional_days_per_year_value, 0)::TEXT
                                );    
                            END IF;

                            IF POSITION('VACATION_DAYS' IN formula_text) > 0 THEN
                                -- Reemplazar VACATION_DAYS en la fórmula
                                formula_text := REPLACE(
                                    formula_text,
                                    'VACATION_DAYS',
                                    COALESCE(vacation_days_value, 0)::TEXT
                                );
                            END IF;
                        END;
                    END IF;

                    -- Reemplazar DAYS_REQUESTED
                    IF POSITION('DAYS_REQUESTED' IN formula_text) > 0 THEN
                        DECLARE
                            days_requested_value NUMERIC;
                        BEGIN
                            -- Obtener el valor de DAYS_REQUESTED desde payroll_vacation_requests
                            SELECT days_requested
                            INTO days_requested_value
                            FROM payroll_vacation_requests
                            WHERE payroll_staff_id = current_staff_id;

                            -- Reemplazar DAYS_REQUESTED en la fórmula
                            formula_text := REPLACE(
                                formula_text,
                                'DAYS_REQUESTED',
                                COALESCE(days_requested_value, 0)::TEXT
                            );
                        END;
                    END IF;

                    -- Reemplazar BENEFIT_DAYS
                    IF POSITION('BENEFIT_DAYS' IN formula_text) > 0 THEN
                        DECLARE
                            benefit_days_value NUMERIC;
                        BEGIN
                            -- Obtener el valor de BENEFIT_DAYS desde payroll_benefits_policies
                            SELECT benefit_days
                            INTO benefit_days_value
                            FROM payroll_benefits_policies
                            WHERE active = TRUE
                            AND start_date >= period_start
                            AND (end_date IS NULL OR end_date <= period_end)
                            AND institution_id = institution_id;

                            -- Reemplazar BENEFIT_DAYS en la fórmula
                            formula_text := REPLACE(
                                formula_text,
                                'BENEFIT_DAYS',
                                COALESCE(benefit_days_value, 0)::TEXT
                            );
                        END;
                    END IF;

                    -- Reemplazar BENEFIT_ADDITIONAL_DAYS_PER_YEAR
                    IF POSITION('BENEFIT_ADDITIONAL_DAYS_PER_YEAR' IN formula_text) > 0 THEN
                        DECLARE
                            benefit_additional_days_per_year_value NUMERIC;
                        BEGIN
                            -- Obtener el valor de BENEFIT_ADDITIONAL_DAYS_PER_YEAR desde payroll_benefits_policies
                            SELECT additional_days_per_year
                            INTO benefit_additional_days_per_year_value
                            FROM payroll_benefits_policies
                            WHERE active = TRUE
                            AND start_date >= period_start
                            AND (end_date IS NULL OR end_date <= period_end)
                            AND institution_id = institution_id;

                            -- Reemplazar BENEFIT_ADDITIONAL_DAYS_PER_YEAR en la fórmula
                            formula_text := REPLACE(
                                formula_text,
                                'BENEFIT_ADDITIONAL_DAYS_PER_YEAR',
                                COALESCE(benefit_additional_days_per_year_value, 0)::TEXT
                            );
                        END;
                    END IF;

                    -- Reemplazar WORK_INTERRUPTION_DAYS
                    IF POSITION('WORK_INTERRUPTION_DAYS' IN formula_text) > 0 THEN
                        DECLARE
                            work_interruption_days_value NUMERIC;
                        BEGIN
                            -- Obtener el valor de WORK_INTERRUPTION_DAYS desde payroll_benefits_policies
                            SELECT work_interruption_days
                            INTO work_interruption_days_value
                            FROM payroll_benefits_policies
                            WHERE active = TRUE
                            AND start_date >= period_start
                            AND (end_date IS NULL OR end_date <= period_end)
                            AND institution_id = institution_id;

                            -- Reemplazar WORK_INTERRUPTION_DAYS en la fórmula
                            formula_text := REPLACE(
                                formula_text,
                                'WORK_INTERRUPTION_DAYS',
                                COALESCE(work_interruption_days_value, 0)::TEXT
                            );
                        END;
                    END IF;

                    -- Reemplazar MONTH_WORKED_DAYS
                    IF POSITION('MONTH_WORKED_DAYS' IN formula_text) > 0 THEN
                        DECLARE
                            month_worked_days_value NUMERIC;
                        BEGIN
                            -- Obtener el valor de MONTH_WORKED_DAYS desde payroll_benefits_policies
                            SELECT month_worked_days
                            INTO month_worked_days_value
                            FROM payroll_benefits_policies
                            WHERE active = TRUE
                            AND start_date >= period_start
                            AND (end_date IS NULL OR end_date <= period_end)
                            AND institution_id = institution_id;

                            -- Reemplazar MONTH_WORKED_DAYS en la fórmula
                            formula_text := REPLACE(
                                formula_text,
                                'MONTH_WORKED_DAYS',
                                COALESCE(month_worked_days_value, 0)::TEXT
                            );
                        END;
                    END IF;

                    -- Reemplazar ari_register
                    IF POSITION('ari_register' IN formula_text) > 0 THEN
                        DECLARE
                            ari_register_value NUMERIC;
                        BEGIN
                            -- Obtener el valor de ari_register desde payroll_benefits_policies
                            SELECT percetage
                            INTO ari_register_value
                            FROM payroll_ari_registers
                            WHERE payroll_staff_id = current_staff_id
                            AND from_date >= period_start
                            AND (to_date IS NULL OR to_date <= period_end);

                            -- Reemplazar ari_register en la fórmula
                            formula_text := REPLACE(
                                formula_text,
                                'ari_register',
                                COALESCE(ari_register_value, 0)::TEXT
                            );
                        END;
                    END IF;

                    -- Reemplazar savings_fund
                    IF POSITION('savings_fund' IN formula_text) > 0 THEN
                        DECLARE
                            savings_fund_value NUMERIC;
                        BEGIN
                            -- Obtener el valor de savings_fund desde payroll_benefits_policies
                            SELECT percetage
                            INTO savings_fund_value
                            FROM payroll_savings_funds
                            WHERE payroll_staff_id = current_staff_id
                            AND from_date >= period_start
                            AND (to_date IS NULL OR to_date <= period_end);

                            -- Reemplazar savings_fund en la fórmula
                            formula_text := REPLACE(
                                formula_text,
                                'savings_fund',
                                COALESCE(savings_fund_value, 0)::TEXT
                            );
                        END;
                    END IF;

                    -- Reemplazar wage_garnishment
                    IF POSITION('wage_garnishment' IN formula_text) > 0 THEN
                        DECLARE
                            wage_garnishment_value NUMERIC;
                        BEGIN
                            -- Obtener el valor de wage_garnishment desde payroll_benefits_policies
                            SELECT percetage
                            INTO wage_garnishment_value
                            FROM payroll_wage_garnishments
                            WHERE payroll_staff_id = current_staff_id
                            AND from_date >= period_start
                            AND (to_date IS NULL OR to_date <= period_end);

                            -- Reemplazar wage_garnishment en la fórmula
                            formula_text := REPLACE(
                                formula_text,
                                'wage_garnishment',
                                COALESCE(wage_garnishment_value, 0)::TEXT
                            );
                        END;
                    END IF;

                    BEGIN
                        EXECUTE 'SELECT ' || formula_text INTO result;
                        -- Redondear el resultado a dos decimales
                        result := ROUND(result, 2);
                    EXCEPTION
                        WHEN OTHERS THEN
                            -- Capturar cualquier excepción y asignar 0 al resultado
                            result := 0;
                    END;

                    -- Redondear los valores numéricos en formula_text a dos decimales
                    formula_text := regexp_replace(
                        formula_text,
                        '(\d+\.\d{2})\d*',
                        '\1',
                        'g'
                    );

                    -- Devolver resultado para el staff_id actual
                    RETURN QUERY SELECT 
                        current_staff_id,
                        staff_full_name,
                        staff_id_number,
                        instruction_degree,
                        staff_position,
                        staff_start_date,
                        institution_years,
                        (SELECT pt.name::TEXT FROM payroll_concepts pc JOIN payroll_concept_types pt ON pc.payroll_concept_type_id = pt.id WHERE pc.id = concept_id),
                        (SELECT name::TEXT FROM payroll_concepts WHERE id = concept_id),
                        result,
                        formula_text::TEXT;
                END LOOP;
            END;
            $$;
            SQL
        );
    }
}
