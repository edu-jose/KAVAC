<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * @class PayrollCerateGetStaffStartApnFunction
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollCreateGetStaffStartApnFunction extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE OR REPLACE FUNCTION public.get_staff_start_apn_function(
                payroll_staff_ids INT[],
                period_start DATE,
                period_end DATE
            ) 
            RETURNS INTEGER[][] AS $$
            BEGIN
                RETURN ARRAY(
                    SELECT ARRAY[
                        employment.payroll_staff_id,
                        -- Sumar los años de servicio en la institución actual y en otras instituciones públicas
                        EXTRACT(YEAR FROM AGE(period_start, employment.start_date))::INTEGER +
                        COALESCE((
                            SELECT SUM(EXTRACT(YEAR FROM AGE(
                                COALESCE(previous_jobs.end_date, period_start), -- Usar period_start si end_date es NULL
                                previous_jobs.start_date
                            )))::INTEGER
                            FROM payroll_previous_jobs AS previous_jobs
                            JOIN payroll_sector_types AS sector_types
                                ON previous_jobs.payroll_sector_type_id = sector_types.id
                            WHERE previous_jobs.payroll_employment_id = employment.id
                            AND previous_jobs.deleted_at IS NULL
                            AND sector_types.name = 'Público'
                        ), 0) -- Si no hay registros, sumar 0
                    ]
                    FROM payroll_employments AS employment
                    WHERE employment.payroll_staff_id = ANY (payroll_staff_ids)
                );
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
        DB::statement('DROP FUNCTION IF EXISTS get_staff_start_apn_function');
    }
}