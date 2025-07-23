<?php

namespace Modules\WorkAttendance\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

/**
 * @class WorkAttendanceDatabaseSeeder
 * @brief Gestiona la carga inicial de datos del módulo de asistencia laboral
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WorkAttendanceDatabaseSeeder extends Seeder
{
    /**
     * Ejecuta los seeds de la base de datos
     *
     * @return void     [descripción de los datos devueltos]
     */
    public function run()
    {
        Model::unguard();

        $this->call(WorkAttendanceRolesAndPermissionsTableSeeder::class);
    }
}
