<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


/**
 * @class UpdateCompareParameterFunction
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateCompareParameterFunction extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('DROP FUNCTION IF EXISTS public.compare_parameter_function;');

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
    }
}
