<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * @class ModifyPayrollStaffFilterParametersView
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ModifyPayrollStaffFilterParametersView extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('DROP FUNCTION IF EXISTS public.compare_parameter_function;');
        DB::statement('DROP VIEW IF EXISTS public.payroll_staff_filter_parameters_view;');

        DB::statement("
            CREATE OR REPLACE VIEW public.payroll_staff_filter_parameters_view
            AS SELECT
                employment.payroll_staff_id AS payroll_staff_id,
                workload.hours AS workload_value,
                COUNT(DISTINCT lang.id) AS professional_language_count,
                COUNT(DISTINCT family_burden.id) AS children_count,
                EXTRACT(YEAR FROM AGE(CURRENT_DATE, employment.start_date))::numeric AS employment_start_date_years,
                professional.payroll_instruction_degree_id AS instruction_degree,
                employment.payroll_position_type_id AS position_type,
                staff.payroll_license_degree_id AS license_degree,
                study.profession_id AS profession,
                socioeconomic.marital_status_id AS marital_status,
                employment_position.payroll_position_id AS position,
                employment.department_id AS department,
                employment.payroll_staff_type_id AS staff_type,
                employment.payroll_contract_type_id AS contract_type
            FROM
                payroll_employments AS employment
            JOIN payroll_employment_payroll_position AS employment_position
                ON employment_position.payroll_employment_id = employment.id AND employment_position.active = TRUE
            LEFT JOIN payroll_workload_positions AS workload_position
                ON workload_position.payroll_position_id = employment_position.payroll_position_id
            LEFT JOIN payroll_workloads AS workload
                ON workload.id = workload_position.payroll_workload_id
            LEFT JOIN payroll_professionals AS professional
                ON professional.payroll_staff_id = employment.payroll_staff_id
            LEFT JOIN payroll_lang_prof AS lang
                ON lang.payroll_prof_id = professional.id
            LEFT JOIN payroll_staffs AS staff
                ON staff.id = employment.payroll_staff_id
            LEFT JOIN payroll_socioeconomics AS socioeconomic
                ON socioeconomic.payroll_staff_id = employment.payroll_staff_id
            LEFT JOIN payroll_family_burdens AS family_burden
                ON family_burden.payroll_socioeconomic_id = socioeconomic.id
                AND family_burden.payroll_relationships_id = (
                    SELECT id
                    FROM payroll_relationships
                    WHERE name = 'Hijo(a)'
                    LIMIT 1
                )
                AND family_burden.deleted_at IS NULL
            LEFT JOIN payroll_studies AS study
                ON study.payroll_professional_id = professional.id
            GROUP BY
                employment.payroll_staff_id, workload.hours, employment.start_date, instruction_degree,position_type,license_degree,profession,marital_status,position,department,staff_type,contract_type;
        ");

        DB::statement("
            CREATE OR REPLACE FUNCTION public.compare_parameter_function(
                p_group_type VARCHAR,
                p_parameter_value VARCHAR,
                p_parameter_type VARCHAR,
                p_staff_row payroll_staff_filter_parameters_view,
                p_staff_years INT,
                p_staff_apn_years INT
            ) RETURNS BOOLEAN AS $$
            DECLARE
                v_clean_value TEXT;
                v_field_name TEXT;
                v_staff_value TEXT;
                v_json_value JSONB;
                v_from_value TEXT;
                v_to_value TEXT;        
            BEGIN
                v_clean_value := REGEXP_REPLACE(p_parameter_value, '^\"|\"$', '', 'g');
                v_field_name := LOWER(p_group_type);

                -- Si el group_by es 'range', procesamos el valor como JSON
                IF p_parameter_type = 'range' THEN
                    -- Convertir el valor del parámetro a JSONB y Extraer 'from' y 'to' del JSON
                    BEGIN
                        v_json_value := p_parameter_value::JSONB;
                        v_from_value := (jsonb_extract_path_text(v_json_value, 'from'))::TEXT;
                        v_to_value := (jsonb_extract_path_text(v_json_value, 'to'))::TEXT;
                        IF v_field_name = 'start_date' THEN
                            v_staff_value := p_staff_years::TEXT;
                        ELSEIF v_field_name = 'start_apn' THEN
                            v_staff_value := p_staff_apn_years::TEXT;
                        ELSE
                            BEGIN
                                EXECUTE format('SELECT ($1).%I::text', v_field_name)
                                USING p_staff_row
                                INTO v_staff_value;
                            EXCEPTION
                                WHEN undefined_column THEN
                                    RETURN FALSE;
                                WHEN OTHERS THEN
                                    RETURN FALSE;
                            END;
                        END IF;
                    END;
                ELSE
                    BEGIN
                        IF v_field_name = 'start_date' THEN
                            v_staff_value := p_staff_years::TEXT;
                        ELSEIF v_field_name = 'start_apn' THEN
                            v_staff_value := p_staff_apn_years::TEXT;
                        ELSE
                            BEGIN
                                EXECUTE format('SELECT ($1).%I::text', v_field_name)
                                USING p_staff_row
                                INTO v_staff_value;
                            EXCEPTION
                                WHEN undefined_column THEN
                                    RETURN FALSE;
                                WHEN OTHERS THEN
                                    RETURN FALSE;
                            END;
                        END IF;
                    END;
                END IF;

                IF p_parameter_type = 'range' THEN
                    IF v_staff_value::NUMERIC BETWEEN v_from_value::NUMERIC AND v_to_value::NUMERIC THEN
                        RETURN TRUE;
                    ELSE
                        RETURN FALSE;
                    END IF;
                ELSE
                    RETURN v_clean_value = v_staff_value;
                END IF;
            END;
            $$ LANGUAGE plpgsql IMMUTABLE;
        ");
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP FUNCTION IF EXISTS public.compare_parameter_function;');
        DB::statement('DROP VIEW IF EXISTS public.payroll_staff_filter_parameters_view;');

        DB::statement("
            CREATE OR REPLACE VIEW public.payroll_staff_filter_parameters_view
            AS SELECT
                employment.payroll_staff_id AS payroll_staff_id,
                workload.hours AS workload_value,
                COUNT(DISTINCT lang.id) AS professional_language_count,
                COUNT(DISTINCT family_burden.id) AS children_count,
                EXTRACT(YEAR FROM AGE(CURRENT_DATE, employment.start_date))::numeric AS employment_start_date_years,
                professional.payroll_instruction_degree_id AS instruction_degree,
                employment.payroll_position_type_id AS position_type
            FROM
                payroll_employments AS employment
            JOIN payroll_employment_payroll_position AS employment_position
                ON employment_position.payroll_employment_id = employment.id AND employment_position.active = TRUE
            LEFT JOIN payroll_workload_positions AS workload_position
                ON workload_position.payroll_position_id = employment_position.payroll_position_id
            LEFT JOIN payroll_workloads AS workload
                ON workload.id = workload_position.payroll_workload_id
            LEFT JOIN payroll_professionals AS professional
                ON professional.payroll_staff_id = employment.payroll_staff_id
            LEFT JOIN payroll_lang_prof AS lang
                ON lang.payroll_prof_id = professional.id
            LEFT JOIN payroll_socioeconomics AS socioeconomic
                ON socioeconomic.payroll_staff_id = employment.payroll_staff_id
            LEFT JOIN payroll_family_burdens AS family_burden
                ON family_burden.payroll_socioeconomic_id = socioeconomic.id
                AND family_burden.payroll_relationships_id = (
                    SELECT id
                    FROM payroll_relationships
                    WHERE name = 'Hijo(a)'
                    LIMIT 1
                )
            GROUP BY
                employment.payroll_staff_id, workload.hours, employment.start_date, instruction_degree, position_type;
        ");

        DB::statement("
            CREATE OR REPLACE FUNCTION public.compare_parameter_function(
                p_group_type VARCHAR,
                p_parameter_value VARCHAR,
                p_parameter_type VARCHAR,
                p_staff_row payroll_staff_filter_parameters_view,
                p_staff_years INT,
                p_staff_apn_years INT
            ) RETURNS BOOLEAN AS $$
            DECLARE
                v_clean_value TEXT;
                v_field_name TEXT;
                v_staff_value TEXT;
                v_json_value JSONB;
                v_from_value TEXT;
                v_to_value TEXT;        
            BEGIN
                v_clean_value := REGEXP_REPLACE(p_parameter_value, '^\"|\"$', '', 'g');
                v_field_name := LOWER(p_group_type);

                -- Si el group_by es 'range', procesamos el valor como JSON
                IF p_parameter_type = 'range' THEN
                    -- Convertir el valor del parámetro a JSONB y Extraer 'from' y 'to' del JSON
                    BEGIN
                        v_json_value := p_parameter_value::JSONB;
                        v_from_value := (jsonb_extract_path_text(v_json_value, 'from'))::TEXT;
                        v_to_value := (jsonb_extract_path_text(v_json_value, 'to'))::TEXT;
                        IF v_field_name = 'start_date' THEN
                            v_staff_value := p_staff_years::TEXT;
                        ELSEIF v_field_name = 'start_apn' THEN
                            v_staff_value := p_staff_apn_years::TEXT;
                        ELSE
                            BEGIN
                                EXECUTE format('SELECT ($1).%I::text', v_field_name)
                                USING p_staff_row
                                INTO v_staff_value;
                            EXCEPTION
                                WHEN undefined_column THEN
                                    RETURN FALSE;
                                WHEN OTHERS THEN
                                    RETURN FALSE;
                            END;
                        END IF;
                    END;
                ELSE
                    BEGIN
                        IF v_field_name = 'start_date' THEN
                            v_staff_value := p_staff_years::TEXT;
                        ELSEIF v_field_name = 'start_apn' THEN
                            v_staff_value := p_staff_apn_years::TEXT;
                        ELSE
                            BEGIN
                                EXECUTE format('SELECT ($1).%I::text', v_field_name)
                                USING p_staff_row
                                INTO v_staff_value;
                            EXCEPTION
                                WHEN undefined_column THEN
                                    RETURN FALSE;
                                WHEN OTHERS THEN
                                    RETURN FALSE;
                            END;
                        END IF;
                    END;
                END IF;

                IF p_parameter_type = 'range' THEN
                    IF v_staff_value::NUMERIC BETWEEN v_from_value::NUMERIC AND v_to_value::NUMERIC THEN
                        RETURN TRUE;
                    ELSE
                        RETURN FALSE;
                    END IF;
                ELSE
                    RETURN v_clean_value = v_staff_value;
                END IF;
            END;
            $$ LANGUAGE plpgsql IMMUTABLE;
        ");
    }
}