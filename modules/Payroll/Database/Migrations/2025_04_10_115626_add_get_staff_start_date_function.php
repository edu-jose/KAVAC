<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * @class AddGetStaffStartDateFunction
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddGetStaffStartDateFunction extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE OR REPLACE FUNCTION public.get_staff_start_date_function(
                payroll_staff_ids INT[],
                period_start DATE,
                period_end DATE
            ) 
            RETURNS INTEGER[][] AS $$
            BEGIN
                RETURN ARRAY(
                    SELECT ARRAY[
                        employment.payroll_staff_id,
                        EXTRACT(YEAR FROM AGE(period_start, employment.start_date))::INTEGER
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
        DB::statement('DROP FUNCTION IF EXISTS get_staff_start_date_function');
    }
}