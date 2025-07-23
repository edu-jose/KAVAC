<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @class UpdateFormatJsonFieldInPayrollVacationRequestsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFormatJsonFieldInPayrollVacationRequestsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        $tableName = 'payroll_vacation_requests';
        $fieldName = 'vacation_period_year';
        $records = DB::table($tableName)->select('id', $fieldName)->get();

        foreach ($records as $record) {
            $jsonData = json_decode($record->{$fieldName}, true);

            if (gettype($jsonData) != 'array') {
                DB::table($tableName)
                    ->where('id', $record->id)
                    ->update([
                        $fieldName => $jsonData
                    ]);
            }
        }
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
    }
}
