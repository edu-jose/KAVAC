<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * @class CreatePayrollStaffAssignToFunction
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollStaffAssignToFunction extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
            // CREATE OR REPLACE FUNCTION payroll_staff_assign_to_function(
            //     input_data JSONB -- Recibe un arreglo JSONB como parámetro
            // ) RETURNS TABLE (
            //     staff_id INT,
            //     staff_name VARCHAR
            // ) LANGUAGE plpgsql AS $$
            // DECLARE
            //     item JSONB; -- Variable para iterar sobre los elementos del arreglo JSONB
            // BEGIN
            //     -- Iterar sobre cada elemento del arreglo JSONB
            //     FOR item IN SELECT * FROM jsonb_array_elements(input_data) LOOP
            //         -- Manejar los casos según el 'id' del elemento
            //         CASE item ->> 'id'
            //             WHEN all THEN
            //                 -- Caso: Todos los trabajadores activos
            //                 RETURN QUERY EXECUTE 
            //                     SELECT id::INT AS staff_id, first_name AS staff_name
            //                     FROM payroll_staffs
            //                 ;
            //             WHEN 'all_active_staff' THEN
            //                 -- Caso: Todos los trabajadores activos
            //                 RETURN QUERY EXECUTE 
            //                     SELECT id::INT AS staff_id, first_name AS staff_name
            //                     FROM payroll_staffs
            //                     WHERE EXISTS (
            //                         SELECT 1
            //                         FROM payroll_employments
            //                         WHERE payroll_employments.payroll_staff_id = payroll_staffs.id
            //                         AND payroll_employments.active = TRUE
            //                     )
            //                 ;
            //         END CASE;
            //     END LOOP;

            //     RETURN;
            // END;
        DB::statement(
            <<<'SQL'
                CREATE OR REPLACE FUNCTION payroll_staff_assign_to_function(
                    p_concept_id INT,           -- ID del concepto actual
                    p_filters JSONB,            -- Array JSONB de filtros activos, ej: '[{"id": "rule1"}, ...]'
                    p_assign_to_rules JSONB,    -- Definiciones de todas las reglas posibles
                    p_period_start DATE,        -- Fecha de inicio del período
                    p_period_end DATE,          -- Fecha de fin del período
                    p_exceptions INT[]          -- Array de IDs de staff para la regla 'staff'
                ) RETURNS TABLE (
                    staff_id INT,
                    staff_name VARCHAR
                ) LANGUAGE plpgsql AS $$
                DECLARE
                    _is_restrict BOOLEAN;
                    -- options_record RECORD;
                    -- options_array RECORD;
                    _option_ids INT[];
                    _option_value JSONB;
                    _option_min_val NUMERIC;
                    _option_max_val NUMERIC;
                    _option_min_date DATE;
                    _option_max_date DATE;

                    _active_filters TEXT[];
                    -- _exceptions ahora viene por parámetro
                    _excluded_ids INT[]; -- Para 'staff_except_specified'

                    _sql_where_conditions TEXT[];
                    _final_where_clause TEXT := ''; -- Iniciar vacío para construir lógicamente
                    _base_sql TEXT;
                    _rule_condition TEXT;
                    _rule_id TEXT;
                    _rule_definition JSONB;
                    _rule_type TEXT;

                    _son_relationship_id INT;
                    _retired_inactivity_type_id INT;

                BEGIN
                    -- 1. Obtener 'is_restrict' del Concepto
                    SELECT pc.is_restrict INTO _is_restrict
                    FROM payroll_concepts pc
                    WHERE pc.id = p_concept_id AND pc.deleted_at IS NULL; -- Añadir chequeo Soft Delete

                    IF NOT FOUND THEN
                        RAISE EXCEPTION 'Concepto con ID % no encontrado o eliminado', p_concept_id;
                    END IF;

                    -- 2. Obtener opciones de asignación para este concepto (excluyendo soft deleted)
                    -- SELECT array_agg(t) INTO options_array FROM (
                    --     SELECT 'key', assignable_id, 'value'
                    --     FROM payroll_concept_assign_options
                    --     WHERE applicable_type = 'Modules\Payroll\Models\PayrollConcept' -- ¡Verifica namespace!
                    --     AND applicable_id = p_concept_id
                    --     AND deleted_at IS NULL -- Añadir chequeo Soft Delete
                    -- ) t;
                    -- options_array := COALESCE(options_array, ARRAY[]::RECORD[]);

                    -- 3. Extraer filtros activos del PARÁMETRO p_filters
                    SELECT array_agg(item->>'id') INTO _active_filters
                    FROM jsonb_array_elements(p_filters) item;
                    _active_filters := COALESCE(_active_filters, '{}');

                    -- 4. Extraer Exclusiones DESDE options_array, SI la regla está activa
                    -- IF 'staff_except_specified' = ANY(_active_filters) THEN
                    --     SELECT array_agg(r.assignable_id::INT) INTO _excluded_ids
                    --     FROM unnest(options_array) r WHERE r.key = 'staff_except_specified' AND r.assignable_id IS NOT NULL;
                    -- END IF;
                    -- _excluded_ids := COALESCE(_excluded_ids, '{}');
                    -- p_exceptions viene por parámetro

                    RAISE NOTICE 'Concept ID: %, Is Restrict: %, Filters Input: %, Exceptions Param: %',
                                p_concept_id, _is_restrict, _active_filters, p_exceptions;

                    -- 5. Manejo de Casos Especiales ('all', 'staff') si NO es restringido
                    IF NOT _is_restrict THEN
                        IF 'staff' = ANY(_active_filters) THEN
                            RAISE NOTICE 'Regla STAFF encontrada (no restringido), devolviendo excepciones.';
                            RETURN QUERY SELECT ps.id, CONCAT(ps.first_name, ' ', ps.last_name)
                                        FROM payroll_staffs ps WHERE ps.id = ANY(p_exceptions) AND ps.deleted_at IS NULL -- Aplicar soft delete
                                        ORDER BY ps.first_name, ps.last_name;
                            RETURN;
                        END IF;
                        IF 'all' = ANY(_active_filters) THEN
                            RAISE NOTICE 'Regla ALL encontrada (no restringido), devolviendo todos los activos.';
                            RETURN QUERY SELECT ps.id, CONCAT(ps.first_name, ' ', ps.last_name)
                                        FROM payroll_staffs ps
                                        WHERE EXISTS (SELECT 1 FROM payroll_employments pe WHERE pe.payroll_staff_id = ps.id AND pe.active = TRUE AND pe.deleted_at IS NULL) -- Aplicar soft delete
                                        AND ps.deleted_at IS NULL -- Aplicar soft delete
                                        ORDER BY ps.first_name, ps.last_name;
                            RETURN;
                        END IF;
                    END IF;

                    -- 6. Construir Cláusula WHERE para casos restringidos o no especiales
                    _sql_where_conditions := '{}'; -- Inicializar array

                    -- IDs comunes (asumiendo que estas tablas no usan soft delete o no es relevante)
                    _son_relationship_id := (SELECT id FROM payroll_relationships WHERE name = 'Hijo(a)' LIMIT 1);
                    IF _son_relationship_id IS NULL THEN _son_relationship_id := 3; END IF;
                    _retired_inactivity_type_id := (SELECT id FROM payroll_inactivity_types WHERE name ILIKE 'jubilado' LIMIT 1);

                    -- Iterar sobre los filtros ACTIVOS PASADOS COMO PARÁMETRO
                    FOREACH _rule_id IN ARRAY _active_filters LOOP
                        IF _rule_id IN ('all', 'staff', 'staff_except_specified') THEN CONTINUE; END IF;

                        _rule_definition := jsonb_path_query_first(p_assign_to_rules, format('$[*] ? (@.id == %L)', _rule_id)::jsonpath);
                        IF _rule_definition IS NULL THEN RAISE WARNING 'Definición para regla ID % no encontrada.', _rule_id; CONTINUE; END IF;
                        _rule_type := _rule_definition->>'type';

                        -- Resetear y extraer opciones para la regla actual desde options_array
                        _option_ids := NULL; _option_value := NULL; _option_min_val := NULL; _option_max_val := NULL; _option_min_date := NULL; _option_max_date := NULL;
                        _rule_condition := NULL;

                        IF _rule_type = 'list' THEN
                            -- SELECT array_agg(r.assignable_id::INT) INTO _option_ids
                            -- FROM unnest(options_array) r WHERE r.key = _rule_id AND r.assignable_id IS NOT NULL;
                            
                            SELECT array_agg(assignable_id::INT) INTO _option_ids
                            FROM payroll_concept_assign_options
                            WHERE key = _rule_id
                            AND assignable_id IS NOT NULL
                            AND applicable_type = 'Modules\Payroll\Models\PayrollConcept'
                            AND applicable_id = p_concept_id
                            AND deleted_at IS NULL; -- Añadir chequeo Soft Delete                                               
                            _option_ids := COALESCE(_option_ids, '{}');
                        ELSIF _rule_type = 'range' THEN
                            -- SELECT r.value INTO _option_value FROM unnest(options_array) r WHERE r.key = _rule_id AND r.value IS NOT NULL LIMIT 1;
                            SELECT value INTO _option_value
                            FROM payroll_concept_assign_options
                            WHERE key = _rule_id
                            AND applicable_type = 'Modules\Payroll\Models\PayrollConcept'
                            AND applicable_id = p_concept_id
                            AND value IS NOT NULL
                            AND deleted_at IS NULL
                            LIMIT 1;
                            _option_min_val := (_option_value->>'minimum')::NUMERIC;
                            _option_max_val := (_option_value->>'maximum')::NUMERIC;
                        END IF;

                        -- Generar el fragmento SQL (AÑADIENDO deleted_at IS NULL donde corresponda)
                        CASE _rule_id
                            WHEN 'all_active_staff' THEN
                                _rule_condition := 'EXISTS (SELECT 1 FROM payroll_employments pe WHERE pe.payroll_staff_id = ps.id AND pe.active = TRUE AND pe.deleted_at IS NULL)';

                            WHEN 'staff_with_sons_has_scholarships' THEN
                                IF array_length(_option_ids, 1) > 0 THEN
                                    _rule_condition := format(
                                        'EXISTS (SELECT 1 FROM payroll_socioeconomics psec JOIN payroll_family_burdens pfb ON psec.id = pfb.payroll_socioeconomic_id WHERE psec.payroll_staff_id = ps.id AND pfb.payroll_relationships_id = %L AND pfb.has_scholarships = TRUE AND pfb.payroll_scholarship_types_id = ANY(%L::int[]) AND psec.deleted_at IS NULL AND pfb.deleted_at IS NULL)',
                                        _son_relationship_id, _option_ids
                                    );
                                ELSE RAISE WARNING 'Regla % sin IDs.', _rule_id; END IF;

                            WHEN 'all_staff_with_sons' THEN
                                IF _option_min_val IS NOT NULL AND _option_max_val IS NOT NULL THEN
                                    _option_max_date := p_period_end - make_interval(years => _option_min_val::int);
                                    _option_min_date := p_period_end - make_interval(years => _option_max_val::int + 1) + interval '1 day';
                                    _rule_condition := format(
                                        'EXISTS (SELECT 1 FROM payroll_socioeconomics psec JOIN payroll_family_burdens pfb ON psec.id = pfb.payroll_socioeconomic_id WHERE psec.payroll_staff_id = ps.id AND pfb.payroll_relationships_id = %L AND pfb.birthdate IS NOT NULL AND pfb.birthdate BETWEEN %L::date AND %L::date AND psec.deleted_at IS NULL AND pfb.deleted_at IS NULL)',
                                        _son_relationship_id, _option_min_date, _option_max_date
                                    );
                                ELSE RAISE WARNING 'Regla % sin rango.', _rule_id; END IF;

                            WHEN 'all_staff_with_sons_studying' THEN
                                _rule_condition := format(
                                    'EXISTS (SELECT 1 FROM payroll_socioeconomics psec JOIN payroll_family_burdens pfb ON psec.id = pfb.payroll_socioeconomic_id WHERE psec.payroll_staff_id = ps.id AND pfb.payroll_relationships_id = %L AND pfb.is_student = TRUE AND psec.deleted_at IS NULL AND pfb.deleted_at IS NULL)',
                                    _son_relationship_id
                                );

                            WHEN 'all_survivor_staff' THEN
                                _rule_condition := format(
                                    'ps.has_died = TRUE AND EXISTS (SELECT 1 FROM payroll_survivors psv WHERE psv.payroll_staff_id = ps.id AND psv.first_name IS NOT NULL /*...otros not null...*/ AND psv.deleted_at IS NULL) AND EXISTS (SELECT 1 FROM payroll_employments pe WHERE pe.payroll_staff_id = ps.id AND pe.payroll_inactivity_type_id = %L AND pe.active = TRUE AND pe.deleted_at IS NULL)',
                                    _retired_inactivity_type_id
                                );

                            WHEN 'all_staff_who_belong_to_a_workers_union' THEN
                                _rule_condition := 'EXISTS (SELECT 1 FROM payroll_employments pe WHERE pe.payroll_staff_id = ps.id AND pe.workers_union = TRUE AND pe.active = TRUE AND pe.deleted_at IS NULL)';
                            WHEN 'all_staff_affiliated_with_the_savings_fund' THEN
                                _rule_condition := 'EXISTS (SELECT 1 FROM payroll_employments pe WHERE pe.payroll_staff_id = ps.id AND pe.savings_fund = TRUE AND pe.active = TRUE AND pe.deleted_at IS NULL)';

                            WHEN 'staff_according_position' THEN
                                IF array_length(_option_ids, 1) > 0 THEN
                                    _rule_condition := format(
                                        'EXISTS (SELECT 1 FROM payroll_employments pe JOIN payroll_employment_payroll_position pe_pp ON pe.id = pe_pp.payroll_employment_id JOIN payroll_positions pp ON pe_pp.payroll_position_id = pp.id WHERE pe.payroll_staff_id = ps.id AND pe.active = TRUE AND pe_pp.payroll_position_id = ANY(%L::int[]) AND pe.deleted_at IS NULL AND pp.deleted_at IS NULL)',
                                        _option_ids
                                    );
                                ELSE RAISE WARNING 'Regla % sin IDs.', _rule_id; END IF;

                            WHEN 'all_staff_not_in_vacation' THEN
                                IF p_period_start IS NOT NULL AND p_period_end IS NOT NULL THEN
                                    _rule_condition := format(
                                        'NOT EXISTS (SELECT 1 FROM payroll_vacation_requests pvr WHERE pvr.payroll_staff_id = ps.id AND pvr.status = ''approved'' AND pvr.start_date <= %L::date AND pvr.end_date >= %L::date AND pvr.deleted_at IS NULL)',
                                        p_period_end, p_period_start
                                    );
                                ELSE RAISE WARNING 'Regla % requiere fechas.', _rule_id; END IF;

                            WHEN 'all_staff_vacation_return' THEN
                                IF p_period_start IS NOT NULL AND p_period_end IS NOT NULL THEN
                                    _rule_condition := format(
                                        'EXISTS (SELECT 1 FROM payroll_vacation_requests pvr WHERE pvr.payroll_staff_id = ps.id AND pvr.status = ''approved'' AND pvr.end_date >= %L::date AND pvr.end_date <= %L::date AND pvr.deleted_at IS NULL)',
                                            p_period_start, p_period_end
                                        );
                                ELSE RAISE WARNING 'Regla % requiere fechas.', _rule_id; END IF;

                            WHEN 'all_disabled_staff' THEN _rule_condition := 'ps.has_disability = TRUE';
                            WHEN 'all_except_disabled_staff' THEN _rule_condition := 'ps.has_disability = FALSE';
                            WHEN 'all_studying_staff' THEN _rule_condition := 'EXISTS (SELECT 1 FROM payroll_professionals pp WHERE pp.payroll_staff_id = ps.id AND pp.is_student = TRUE AND pp.deleted_at IS NULL)';
                            WHEN 'staff_master_the_languages' THEN _rule_condition := '(SELECT count(*) FROM payroll_professionals pp JOIN payroll_language_payroll_professional pl_pp ON pp.id = pl_pp.payroll_professional_id WHERE pp.payroll_staff_id = ps.id AND pp.deleted_at IS NULL /* AND pl.deleted_at IS NULL si aplica */) > 1';

                            WHEN 'staff_according_contract_type' THEN IF array_length(_option_ids, 1) > 0 THEN _rule_condition := format('EXISTS (SELECT 1 FROM payroll_employments pe WHERE pe.payroll_staff_id = ps.id AND pe.active = TRUE AND pe.payroll_contract_type_id = ANY(%L::int[]) AND pe.deleted_at IS NULL)', _option_ids); ELSE RAISE WARNING 'Regla % sin IDs.', _rule_id; END IF;
                            WHEN 'staff_according_department' THEN IF array_length(_option_ids, 1) > 0 THEN _rule_condition := format('EXISTS (SELECT 1 FROM payroll_employments pe WHERE pe.payroll_staff_id = ps.id AND pe.active = TRUE AND pe.department_id = ANY(%L::int[]) AND pe.deleted_at IS NULL)', _option_ids); ELSE RAISE WARNING 'Regla % sin IDs.', _rule_id; END IF;
                            WHEN 'staff_according_position_type' THEN IF array_length(_option_ids, 1) > 0 THEN _rule_condition := format('EXISTS (SELECT 1 FROM payroll_employments pe WHERE pe.payroll_staff_id = ps.id AND pe.active = TRUE AND pe.payroll_position_type_id = ANY(%L::int[]) AND pe.deleted_at IS NULL)', _option_ids); ELSE RAISE WARNING 'Regla % sin IDs.', _rule_id; END IF;
                            WHEN 'staff_according_staff_type' THEN IF array_length(_option_ids, 1) > 0 THEN _rule_condition := format('EXISTS (SELECT 1 FROM payroll_employments pe WHERE pe.payroll_staff_id = ps.id AND pe.active = TRUE AND pe.payroll_staff_type_id = ANY(%L::int[]) AND pe.deleted_at IS NULL)', _option_ids); ELSE RAISE WARNING 'Regla % sin IDs.', _rule_id; END IF;

                            WHEN 'all_staff_according_start_date' THEN
                                _option_max_val := (_option_value->>'maximum')::NUMERIC;
                                IF _option_max_val IS NOT NULL THEN
                                    -- Calcular la fecha límite restando años de p_period_end
                                    _option_max_date := p_period_end - make_interval(years => _option_max_val::int);

                                    -- Construir la condición SQL
                                    _rule_condition := format(
                                        'EXISTS (
                                            SELECT 1
                                            FROM payroll_employments pe
                                            WHERE pe.payroll_staff_id = ps.id
                                            AND pe.active = TRUE
                                            AND pe.start_date <= %L::date
                                            AND (
                                                -- Caso 1: Período dentro del mismo año
                                                to_char(pe.start_date, ''MM-DD'') BETWEEN %L AND %L
                                                -- Caso 2: Período cruza el límite de un año
                                                OR to_char(pe.start_date, ''MM-DD'') BETWEEN %L AND ''12-31''
                                                OR to_char(pe.start_date, ''MM-DD'') BETWEEN ''01-01'' AND %L
                                            )
                                            AND pe.deleted_at IS NULL
                                        )',
                                        _option_max_date,
                                        to_char(p_period_start, 'MM-DD'),
                                        to_char(p_period_end, 'MM-DD'),
                                        to_char(p_period_start, 'MM-DD'),
                                        to_char(p_period_end, 'MM-DD')
                                    );
                                ELSE
                                    RAISE WARNING 'Regla % sin max (years).', _rule_id;
                                END IF;
                                WHEN 'staff_according_instruction_degree' THEN IF array_length(_option_ids, 1) > 0 THEN _rule_condition := format('EXISTS (SELECT 1 FROM payroll_professionals pp WHERE pp.payroll_staff_id = ps.id AND pp.payroll_instruction_degree_id = ANY(%L::int[]) AND pp.deleted_at IS NULL)', _option_ids); ELSE RAISE WARNING 'Regla % sin IDs.', _rule_id; END IF;
                                WHEN 'staff_according_gender' THEN IF array_length(_option_ids, 1) > 0 THEN _rule_condition := format('ps.payroll_gender_id = ANY(%L::int[])', _option_ids); ELSE RAISE WARNING 'Regla % sin IDs.', _rule_id; END IF;

                                ELSE RAISE WARNING 'Regla % no implementada.', _rule_id;
                            END CASE;

                        -- Añadir condición válida al array, asegurando chequeo de activo si es necesario
                        IF _rule_condition IS NOT NULL THEN
                            IF _rule_id NOT IN ('all_survivor_staff', 'all_disabled_staff', 'all_except_disabled_staff', 'staff_according_gender') AND _rule_condition NOT LIKE '%pe.active = TRUE%' THEN
                                _rule_condition := '(' || _rule_condition || ') AND EXISTS (SELECT 1 FROM payroll_employments pe WHERE pe.payroll_staff_id = ps.id AND pe.active = TRUE AND pe.deleted_at IS NULL)';
                            END IF;
                            _sql_where_conditions := array_append(_sql_where_conditions, '(' || _rule_condition || ')');
                        END IF;

                    END LOOP; -- Fin del bucle de filtros activos

                    -- 7. Combinar condiciones y añadir exclusiones/condición base activa
                    _base_sql := 'SELECT ps.id, CONCAT(ps.first_name, '' '', ps.last_name) FROM payroll_staffs ps WHERE ps.deleted_at IS NULL '; -- Base soft delete check

                    IF array_length(_sql_where_conditions, 1) > 0 THEN
                        IF _is_restrict THEN
                            _final_where_clause := ' AND ' || array_to_string(_sql_where_conditions, ' AND ');
                        ELSE
                            _final_where_clause := ' AND (' || array_to_string(_sql_where_conditions, ' OR ') || ')';
                        END IF;
                        _base_sql := _base_sql || _final_where_clause;

                    ELSE
                        -- Casos donde el bucle no generó condiciones (basado en filtros de entrada)
                        IF 'staff' = ANY(_active_filters) AND _is_restrict THEN
                            _base_sql := _base_sql || format(' AND ps.id = ANY(%L::int[])', p_exceptions);
                        ELSIF 'all' = ANY(_active_filters) AND _is_restrict THEN
                            _base_sql := _base_sql || ' AND EXISTS (SELECT 1 FROM payroll_employments pe WHERE pe.payroll_staff_id = ps.id AND pe.active = TRUE AND pe.deleted_at IS NULL)';
                        ELSIF NOT ('staff' = ANY(_active_filters) OR 'all' = ANY(_active_filters)) THEN
                            -- Si no hay reglas válidas (ni 'all' ni 'staff') y el bucle no generó nada, nadie cumple
                            _base_sql := _base_sql || ' AND FALSE ';
                        -- Si era 'staff' o 'all' pero _is_restrict=false, ya se retornó antes.
                        END IF;
                    END IF;

                    -- Aplicar exclusión 'staff_except_specified' si la regla estaba activa y se encontraron IDs
                    IF 'staff_except_specified' = ANY(_active_filters) AND array_length(p_exceptions, 1) > 0 THEN
                        _base_sql := _base_sql || format(' AND ps.id <> ALL (%L::int[]) ', p_exceptions);
                    END IF;

                    _base_sql := _base_sql || ' ORDER BY ps.first_name, ps.last_name;';

                    RAISE NOTICE 'SQL Final: %', _base_sql;

                    -- 8. Ejecutar y Devolver
                    RETURN QUERY EXECUTE _base_sql;
                    RETURN;

                EXCEPTION
                    WHEN others THEN
                        RAISE WARNING 'Error en payroll_staff_assign_to_function: % - SQLSTATE: %', SQLERRM, SQLSTATE;
                        RETURN;
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
        DB::statement("DROP FUNCTION IF EXISTS payroll_staff_assign_to_function(INT, JSONB, JSONB, DATE, DATE, INT[])");
    }
}
