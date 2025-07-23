<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * @class ChangePayrollStaffFilterParametersView
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ChangePayrollStaffFilterParametersView extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE OR REPLACE VIEW public.payroll_staff_filter_parameters_view
            AS SELECT
                employment.payroll_staff_id AS payroll_staff_id,
                workload.hours AS workload_value,
                COUNT(DISTINCT lang.id) AS professional_language_count,
                COUNT(DISTINCT family_burden.id) AS children_count,
                EXTRACT(YEAR FROM AGE(CURRENT_DATE, employment.start_date)) AS employment_start_date_years,
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
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("
            CREATE OR REPLACE VIEW public.payroll_staff_filter_parameters_view
            AS SELECT
                employment.payroll_staff_id AS payroll_staff_id,
                workload.hours AS workload_value,
                COUNT(lang) AS professional_language_count,
                COUNT(family_burden) AS children_count,
                EXTRACT(YEAR FROM AGE(CURRENT_DATE, employment.start_date)) AS employment_start_date_years,
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
    }
}