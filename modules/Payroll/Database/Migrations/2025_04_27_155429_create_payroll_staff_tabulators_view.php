<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * @class CreatePayrollStaffTabulatorsView
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollStaffTabulatorsView extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
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
                    v_json_value := p_parameter_value::JSONB;
                    v_from_value := (jsonb_extract_path_text(v_json_value, 'from'))::TEXT;
                    v_to_value := (jsonb_extract_path_text(v_json_value, 'to'))::TEXT;
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
                    IF v_staff_value BETWEEN v_from_value AND v_to_value THEN
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

        DB::statement("
            CREATE OR REPLACE FUNCTION public.payroll_staff_tabulators_function(
                p_staff_id INT,
                p_tabulator_id INT,
                staff_institution_years INT,
                staff_apn_years INT
            )
            RETURNS TABLE (
                staff_id INT,
                t_tabulator_id INT,
                vertical_scale_group_by TEXT,
                vertical_scale_type TEXT,
                horizontal_scale_group_by TEXT,
                horizontal_scale_type TEXT,
                scale_value NUMERIC,
                tipo_coincidencia TEXT
            ) AS $$
            BEGIN
                RETURN QUERY
                SELECT
                    psfpv.payroll_staff_id::INT,
                    ptv.tabulator_id::INT,
                    ptv.vertical_scale_group_by::TEXT,
                    ptv.vertical_scale_type::TEXT,
                    ptv.horizontal_scale_group_by::TEXT,
                    ptv.horizontal_scale_type::TEXT,
                    ptv.scale_value::NUMERIC,
                    CASE
                        WHEN ptv.tabulator_id IS NULL THEN 'Sin coincidencia'
                        WHEN compare_parameter_function(ptv.vertical_scale_group_by, ptv.parameter_vertical_value, ptv.vertical_scale_type, psfpv, staff_institution_years, staff_apn_years)
                            AND compare_parameter_function(ptv.horizontal_scale_group_by, ptv.parameter_horizontal_value, ptv.horizontal_scale_type, psfpv, staff_institution_years, staff_apn_years)
                            THEN 'Coincidencia completa'
                        WHEN compare_parameter_function(ptv.vertical_scale_group_by, ptv.parameter_vertical_value, ptv.vertical_scale_type, psfpv, staff_institution_years, staff_apn_years)
                            THEN 'Coincidencia vertical'
                        WHEN compare_parameter_function(ptv.horizontal_scale_group_by, ptv.parameter_horizontal_value, ptv.horizontal_scale_type, psfpv, staff_institution_years, staff_apn_years)
                            THEN 'Coincidencia horizontal'
                        ELSE CONCAT('Sin coincidencia (V:', COALESCE(ptv.vertical_scale_group_by, 'NULL'), ', H:', COALESCE(ptv.horizontal_scale_group_by, 'NULL'), ')')
                    END AS tipo_coincidencia
                FROM 
                    payroll_staff_filter_parameters_view AS psfpv
                LEFT JOIN
                    payroll_tabulators_view AS ptv ON TRUE
                WHERE
                    (compare_parameter_function(ptv.vertical_scale_group_by, ptv.parameter_vertical_value, ptv.vertical_scale_type, psfpv, staff_institution_years, staff_apn_years)
                    OR
                    compare_parameter_function(ptv.horizontal_scale_group_by, ptv.parameter_horizontal_value, ptv.horizontal_scale_type, psfpv, staff_institution_years, staff_apn_years))
                    AND p_staff_id = psfpv.payroll_staff_id
                    AND p_tabulator_id = ptv.tabulator_id;
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
        DB::statement('DROP FUNCTION IF EXISTS payroll_staff_tabulators_function(integer,integer,integer,integer)');
        DB::statement('DROP VIEW IF EXISTS payroll_staff_tabulators_view');
        DB::statement('DROP VIEW IF EXISTS payroll_staff_tabulators_function');
        DB::statement('DROP FUNCTION IF EXISTS compare_parameter_function');
    }
}