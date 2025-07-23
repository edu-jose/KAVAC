<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * @class ChangePayrollStaffTabulatorsFunction
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ChangePayrollStaffTabulatorsFunction extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('DROP FUNCTION IF EXISTS payroll_tabulators_function');

        DB::statement("
            CREATE OR REPLACE FUNCTION public.payroll_tabulators_function(
                period_start DATE,
                period_end DATE
            ) RETURNS TABLE (
                tabulator_id INT,
                tabulator_name VARCHAR,
                tabulator_type VARCHAR,
                percentage BOOLEAN,
                horizontal_scale_name VARCHAR,
                horizontal_scale_type VARCHAR,
                horizontal_scale_group_by VARCHAR,
                vertical_scale_name VARCHAR,
                vertical_scale_type VARCHAR,
                vertical_scale_group_by VARCHAR,
                institution_id INT,
                institution_acronym VARCHAR,
                institution_name VARCHAR,
                currency_name VARCHAR,
                currency_decimal_places INT,
                currency_symbol VARCHAR,
                scale_id INT,
                scale_value NUMERIC,
                parameter_horizontal_name VARCHAR,
                parameter_horizontal_value TEXT,
                parameter_vertical_name VARCHAR,
                parameter_vertical_value TEXT
            ) AS $$
            BEGIN
                RETURN QUERY
                SELECT
                    tabulators.id::INT AS tabulator_id,
                    tabulators.name AS tabulator_name,
                    tabulators.payroll_salary_tabulator_type AS tabulator_type,
                    tabulators.percentage AS percentage,
                    horizontal_scales.name AS horizontal_scale_name,
                    horizontal_scales.type AS horizontal_scale_type,
                    horizontal_scales.group_by AS horizontal_scale_group_by,
                    vertical_scales.name AS vertical_scale_name,
                    vertical_scales.type AS vertical_scale_type,
                    vertical_scales.group_by AS vertical_scale_group_by,
                    institutions.id::INT AS institution_id,
                    institutions.acronym AS institution_acronym,
                    institutions.name AS institution_name,
                    currencies.name AS currency_name,
                    currencies.decimal_places AS currency_decimal_places,
                    currencies.symbol AS currency_symbol,
                    scales.id::INT AS scale_id,
                    CASE
                        WHEN psa.increase_of_type = 'absolute_value' AND tabulators.percentage IS FALSE THEN (scales.value + psa.value)::NUMERIC
                        WHEN psa.increase_of_type = 'percentage' AND tabulators.percentage IS FALSE THEN (scales.value + ((scales.value * psa.value) / 100))::NUMERIC
                        WHEN psa.increase_of_type = 'different' AND tabulators.percentage IS FALSE THEN (
                            SELECT 
                                CAST(NULLIF(salary->>'value', '') AS NUMERIC)
                            FROM jsonb_array_elements(psa.salary_values::JSONB) AS salary
                            WHERE salary->>'id' = scales.id::TEXT
                        )::NUMERIC
                        WHEN psa.increase_of_type = 'absolute_value' AND tabulators.percentage IS TRUE THEN ((scales.value + psa.value) / 100)::NUMERIC
                        WHEN psa.increase_of_type = 'percentage' AND tabulators.percentage IS TRUE THEN ((scales.value + ((scales.value * psa.value) / 100)) / 100)::NUMERIC
                        WHEN psa.increase_of_type = 'different' AND tabulators.percentage IS TRUE THEN (
                            SELECT 
                                CAST(NULLIF(salary->>'value', '') AS NUMERIC) / 100
                            FROM jsonb_array_elements(psa.salary_values::JSONB) AS salary
                            WHERE salary->>'id' = scales.id::TEXT
                        )::NUMERIC
                        ELSE
                            CASE
                                WHEN tabulators.percentage IS FALSE THEN scales.value::NUMERIC
                                ELSE (scales.value / 100)::NUMERIC
                            END
                    END AS scale_value,
                    psh.name AS parameter_horizontal_name,
                    psh.value AS parameter_horizontal_value,
                    psv.name AS parameter_vertical_name,
                    psv.value AS parameter_vertical_value
                FROM
                    payroll_salary_tabulators AS tabulators
                JOIN
                    payroll_salary_tabulator_scales AS scales
                    ON scales.payroll_salary_tabulator_id = tabulators.id
                    AND scales.deleted_at IS NULL
                JOIN
                    institutions
                    ON institutions.id = tabulators.institution_id AND institutions.deleted_at IS NULL
                LEFT JOIN
                    currencies
                    ON currencies.id = tabulators.currency_id AND currencies.deleted_at IS NULL
                LEFT JOIN
                    payroll_salary_scales AS horizontal_scales
                    ON horizontal_scales.id = tabulators.payroll_horizontal_salary_scale_id
                    AND horizontal_scales.deleted_at IS NULL
                LEFT JOIN
                    payroll_scales AS psh
                    ON psh.id = scales.payroll_horizontal_scale_id
                    AND psh.deleted_at is NULL
                LEFT JOIN payroll_salary_scales AS vertical_scales
                    ON vertical_scales.id = tabulators.payroll_vertical_salary_scale_id
                    AND vertical_scales.deleted_at IS NULL
                LEFT JOIN
                    payroll_scales AS psv
                    ON psv.id = scales.payroll_vertical_scale_id
                    AND psv.deleted_at is NULL
                LEFT JOIN
                    payroll_salary_adjustments AS psa
                    ON psa.payroll_salary_tabulator_id = tabulators.id
                    AND start_increase_date >= period_start
                    AND (end_increase_date <= period_end OR end_increase_date IS NULL)
                WHERE
                    tabulators.deleted_at IS NULL
                    AND tabulators.active = TRUE;
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
        DB::statement('DROP FUNCTION IF EXISTS payroll_tabulators_function');
 
        DB::statement("
            CREATE OR REPLACE FUNCTION public.payroll_tabulators_function(
                period_start DATE,
                period_end DATE
            ) RETURNS TABLE (
                tabulator_id INT,
                tabulator_name VARCHAR,
                tabulator_type VARCHAR,
                percentage BOOLEAN,
                horizontal_scale_name VARCHAR,
                horizontal_scale_type VARCHAR,
                horizontal_scale_group_by VARCHAR,
                vertical_scale_name VARCHAR,
                vertical_scale_type VARCHAR,
                vertical_scale_group_by VARCHAR,
                institution_id INT,
                institution_acronym VARCHAR,
                institution_name VARCHAR,
                currency_name VARCHAR,
                currency_decimal_places INT,
                currency_symbol VARCHAR,
                scale_id INT,
                scale_value NUMERIC,
                parameter_horizontal_name VARCHAR,
                parameter_horizontal_value TEXT,
                parameter_vertical_name VARCHAR,
                parameter_vertical_value TEXT
            ) AS $$
            BEGIN
                RETURN QUERY
                SELECT
                    tabulators.id::INT AS tabulator_id,
                    tabulators.name AS tabulator_name,
                    tabulators.payroll_salary_tabulator_type AS tabulator_type,
                    tabulators.percentage AS percentage,
                    horizontal_scales.name AS horizontal_scale_name,
                    horizontal_scales.type AS horizontal_scale_type,
                    horizontal_scales.group_by AS horizontal_scale_group_by,
                    vertical_scales.name AS vertical_scale_name,
                    vertical_scales.type AS vertical_scale_type,
                    vertical_scales.group_by AS vertical_scale_group_by,
                    institutions.id::INT AS institution_id,
                    institutions.acronym AS institution_acronym,
                    institutions.name AS institution_name,
                    currencies.name AS currency_name,
                    currencies.decimal_places AS currency_decimal_places,
                    currencies.symbol AS currency_symbol,
                    scales.id::INT AS scale_id,
                    CASE
                        WHEN psa.increase_of_type = 'absolute' AND tabulators.percentage IS FALSE THEN (scales.value + psa.value)::NUMERIC
                        WHEN psa.increase_of_type = 'percentage' AND tabulators.percentage IS FALSE THEN (scales.value + ((scales.value * psa.value) / 100))::NUMERIC
                        WHEN psa.increase_of_type = 'different' AND tabulators.percentage IS FALSE THEN (
                            SELECT 
                                CAST(NULLIF(salary->>'value', '') AS NUMERIC)
                            FROM jsonb_array_elements(psa.salary_values::JSONB) AS salary
                            WHERE salary->>'id' = scales.id::TEXT
                        )::NUMERIC
                        WHEN psa.increase_of_type = 'absolute' AND tabulators.percentage IS TRUE THEN ((scales.value + psa.value) / 100)::NUMERIC
                        WHEN psa.increase_of_type = 'percentage' AND tabulators.percentage IS TRUE THEN ((scales.value + ((scales.value * psa.value) / 100)) / 100)::NUMERIC
                        WHEN psa.increase_of_type = 'different' AND tabulators.percentage IS TRUE THEN (
                            SELECT 
                                CAST(NULLIF(salary->>'value', '') AS NUMERIC) / 100
                            FROM jsonb_array_elements(psa.salary_values::JSONB) AS salary
                            WHERE salary->>'id' = scales.id::TEXT
                        )::NUMERIC
                        ELSE
                            CASE
                                WHEN tabulators.percentage IS FALSE THEN scales.value::NUMERIC
                                ELSE (scales.value / 100)::NUMERIC
                            END
                    END AS scale_value,
                    psh.name AS parameter_horizontal_name,
                    psh.value AS parameter_horizontal_value,
                    psv.name AS parameter_vertical_name,
                    psv.value AS parameter_vertical_value
                FROM
                    payroll_salary_tabulators AS tabulators
                JOIN
                    payroll_salary_tabulator_scales AS scales
                    ON scales.payroll_salary_tabulator_id = tabulators.id
                    AND scales.deleted_at IS NULL
                JOIN
                    institutions
                    ON institutions.id = tabulators.institution_id AND institutions.deleted_at IS NULL
                LEFT JOIN
                    currencies
                    ON currencies.id = tabulators.currency_id AND currencies.deleted_at IS NULL
                LEFT JOIN
                    payroll_salary_scales AS horizontal_scales
                    ON horizontal_scales.id = tabulators.payroll_horizontal_salary_scale_id
                    AND horizontal_scales.deleted_at IS NULL
                LEFT JOIN
                    payroll_scales AS psh
                    ON psh.id = scales.payroll_horizontal_scale_id
                    AND psh.deleted_at is NULL
                LEFT JOIN payroll_salary_scales AS vertical_scales
                    ON vertical_scales.id = tabulators.payroll_vertical_salary_scale_id
                    AND vertical_scales.deleted_at IS NULL
                LEFT JOIN
                    payroll_scales AS psv
                    ON psv.id = scales.payroll_vertical_scale_id
                    AND psv.deleted_at is NULL
                LEFT JOIN
                    payroll_salary_adjustments AS psa
                    ON psa.payroll_salary_tabulator_id = tabulators.id
                    AND start_increase_date >= period_start
                    AND (end_increase_date <= period_end OR end_increase_date IS NULL)
                WHERE
                    tabulators.deleted_at IS NULL
                    AND tabulators.active = TRUE;
            END;
            $$ LANGUAGE plpgsql IMMUTABLE;
        ");
    }
}