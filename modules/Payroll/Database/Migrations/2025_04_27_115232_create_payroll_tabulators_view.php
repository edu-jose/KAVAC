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
class CreatePayrollTabulatorsView extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE OR REPLACE VIEW public.payroll_tabulators_view
            AS SELECT
                tabulators.id AS tabulator_id,
                tabulators.name AS tabulator_name,
                tabulators.payroll_salary_tabulator_type AS tabulator_type,
                tabulators.percentage AS percentage,
                horizontal_scales.name AS horizontal_scale_name,
                horizontal_scales.type AS horizontal_scale_type,
                horizontal_scales.group_by AS horizontal_scale_group_by,
                vertical_scales.name AS vertical_scale_name,
                vertical_scales.type AS vertical_scale_type,
                vertical_scales.group_by AS vertical_scale_group_by,
                institutions.id AS institution_id,
                institutions.acronym AS institution_acronym,
                institutions.name AS institution_name,
                currencies.name AS currency_name,
                currencies.decimal_places AS currency_decimal_places,
                currencies.symbol AS currency_symbol,
                scales.id AS scale_id,
                CASE
                    WHEN tabulators.percentage = TRUE THEN scales.value / 100.0
                    ELSE scales.value
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
            JOIN
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
            WHERE
                tabulators.deleted_at IS NULL
                AND tabulators.active = TRUE;
        ");
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS payroll_tabulators_view');
    }
}
