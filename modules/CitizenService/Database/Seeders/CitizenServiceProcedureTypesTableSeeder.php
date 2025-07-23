<?php

namespace Modules\CitizenService\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\CitizenService\Models\CitizenServiceProcedureType;

/**
 * @class CitizenServiceRequestTypesTableSeeder
 * @brief Ejecuta las migraciones de los tipos de solicitudes
 *
 * @author Ing. Yennifer Ramirez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceProcedureTypesTableSeeder extends Seeder
{
    /**
     * Método que ejecuta el seeder e inserta los datos en la base de datos.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $citizenServiceProcedureTypes = [
            ['name' => 'Soporte técnico'],
            ['name' => 'Migración a software libre'],
            ['name' => 'Talleres de formación - asesorias'],
            ['name' => 'Desarrollo de software libre']

        ];



        foreach ($citizenServiceProcedureTypes as $citizenServiceProcedureType) {
            CitizenServiceProcedureType::updateOrCreate(
                ['name' => $citizenServiceProcedureType['name']]
            );
        }
    }
}
