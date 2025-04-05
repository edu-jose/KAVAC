<?php

namespace Modules\WorkAttendance\Database\Seeders;

use App\Roles\Models\Role;
use Illuminate\Database\Seeder;
use App\Roles\Models\Permission;
use Illuminate\Database\Eloquent\Model;

/**
 * @class $CLASS$
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WorkAttendanceRolesAndPermissionsTableSeeder extends Seeder
{
    protected $moduleDescription = 'Gestión de Asistencia';

    /**
     * Ejecuta los seeds de la base de datos
     *
     * @return void     [descripción de los datos devueltos]
     */
    public function run()
    {
        Model::unguard();

        $adminRole = Role::where('slug', 'admin')->first();

        $workAttendanceRole = Role::updateOrCreate(
            ['slug' => 'workattendance'],
            ['name' => $this->moduleDescription, 'description' => 'Encargado de la asistencia del personal']
        );

        $permissions = [
            [
                'name' => 'Configuración del módulo de Gestión de Asistencia',
                'slug' => 'workattendance.setting.index',
                'description' => 'Acceso a la configuración del módulo de Gestión de Asistencia',
                'model' => '', 'model_prefix' => $this->moduleDescription,
                'slug_alt' => 'configuracion.ver',
            ],
            [
                'name' => 'Ver asistencia',
                'slug' => 'workattendance.history.index',
                'description' => 'Acceso para ver asistencia del personal',
                'model' => 'Modules\WorkAttendance\Models\WorkAttendance',
                'model_prefix' => $this->moduleDescription,
                'slug_alt' => 'asistencias.ver',
                'short_description' => 'Ver asistencias',
            ],
        ];

        $workAttendanceRole->detachAllPermissions();

        foreach ($permissions as $permission) {
            $per = Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                [
                    'name' => $permission['name'], 'description' => $permission['description'],
                    'model' => $permission['model'], 'model_prefix' => $permission['model_prefix'],
                    'slug_alt' => $permission['slug_alt'],
                ]
            );

            $workAttendanceRole->attachPermission($per);

            if ($adminRole) {
                $adminRole->attachPermission($per);
            }
        }
    }
}
