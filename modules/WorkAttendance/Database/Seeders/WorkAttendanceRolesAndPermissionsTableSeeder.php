<?php

namespace Modules\WorkAttendance\Database\Seeders;

use App\Roles\Models\Role;
use Illuminate\Database\Seeder;
use App\Roles\Models\Permission;
use Illuminate\Database\Eloquent\Model;
use Modules\WorkAttendance\Models\WorkAttendance;
use Modules\WorkAttendance\Models\WorkAttendanceCustomSchedule;
use Modules\WorkAttendance\Models\WorkAttendanceExternalActivity;

/**
 * @class WorkAttendanceRolesAndPermissionsTableSeeder
 * @brief Ejecuta los seeds de Roles y Permisos del módulo de Asistencia
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WorkAttendanceRolesAndPermissionsTableSeeder extends Seeder
{
    protected $moduleDescription = 'Gestión de Asistencia';

    protected $externalActivityPermissions = [
        [
            'name' => 'Ver gestión de actividades externas',
            'slug' => 'workattendance.external.activity.index',
            'description' => 'Acceso para ver actividades externas',
            'model' => WorkAttendanceExternalActivity::class,
            'model_prefix' => 'asistencia',
            'slug_alt' => 'asistencia.externa.ver'
        ],
        [
            'name' => 'Crear actividades externas',
            'slug' => 'workattendance.external.activity.store',
            'description' => 'Acceso para crear actividades externas',
            'model' => WorkAttendanceExternalActivity::class,
            'model_prefix' => 'asistencia',
            'slug_alt' => 'asistencia.externa.crear'
        ],
        [
            'name' => 'Editar actividades externas',
            'slug' => 'workattendance.external.activity.update',
            'description' => 'Acceso para editar actividades externas',
            'model' => WorkAttendanceExternalActivity::class,
            'model_prefix' => 'asistencia',
            'slug_alt' => 'asistencia.externa.editar'
        ],
        [
            'name' => 'Eliminar actividades externas',
            'slug' => 'workattendance.external.activity.delete',
            'description' => 'Acceso para eliminar actividades externas',
            'model' => WorkAttendanceExternalActivity::class,
            'model_prefix' => 'asistencia',
            'slug_alt' => 'asistencia.externa.eliminar'
        ],
    ];

    protected $customSchedulePermissions = [
        [
            'name' => 'Ver gestión de horarios personalizados',
            'slug' => 'workattendance.custom.schedule.index',
            'description' => 'Acceso para ver horarios personalizados',
            'model' => WorkAttendanceCustomSchedule::class,
            'model_prefix' => 'asistencia',
            'slug_alt' => 'asistencia.horario.personalizado.ver'
        ],
        [
            'name' => 'Crear horarios personalizados',
            'slug' => 'workattendance.custom.schedule.store',
            'description' => 'Acceso para crear horarios personalizados',
            'model' => WorkAttendanceCustomSchedule::class,
            'model_prefix' => 'asistencia',
            'slug_alt' => 'asistencia.horario.personalizado.crear'
        ],
        [
            'name' => 'Editar horarios personalizados',
            'slug' => 'workattendance.custom.schedule.update',
            'description' => 'Acceso para editar horarios personalizados',
            'model' => WorkAttendanceCustomSchedule::class,
            'model_prefix' => 'asistencia',
            'slug_alt' => 'asistencia.horario.personalizado.editar'
        ],
        [
            'name' => 'Eliminar horarios personalizados',
            'slug' => 'workattendance.custom.schedule.delete',
            'description' => 'Acceso para eliminar horarios personalizados',
            'model' => WorkAttendanceCustomSchedule::class,
            'model_prefix' => 'asistencia',
            'slug_alt' => 'asistencia.horario.personalizado.eliminar',
        ],
    ];

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
                'model' => '', 'model_prefix' => 'asistencia',
                'slug_alt' => 'configuracion.ver',
            ],
            [
                'name' => 'Ver asistencia',
                'slug' => 'workattendance.history.index',
                'description' => 'Acceso para ver asistencia del personal',
                'model' => WorkAttendance::class,
                'model_prefix' => 'asistencia',
                'slug_alt' => 'asistencias.ver',
                'short_description' => 'Ver asistencias',
            ],
            ...$this->externalActivityPermissions,
            ...$this->customSchedulePermissions
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
