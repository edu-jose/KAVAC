<?php

namespace Modules\ProjectTracking\Database\Seeders;

use App\Roles\Models\Role;
use Illuminate\Database\Seeder;
use App\Roles\Models\Permission;
use Illuminate\Database\Eloquent\Model;
use Modules\ProjectTracking\Models\ProjectTrackingActivityType;

/**
 * @class ProjectTrackingRoleAndPermissionsTableSeeder
 * @brief Gestiona la inserción de permisos en la base de datos
 *
 *          Clase que gestiona la inserción de permisos en la base de datos.
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingRoleAndPermissionsTableSeeder extends Seeder
{
    /**
     * Ejecute el seeder de la base de datos.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $adminRole = Role::where('slug', 'admin')->first();

        $projectTrackingRole = Role::updateOrCreate(
            [
                'slug' => 'project.tracking',
            ],
            [
                'name' => 'Seguimiento',
                'description' => 'Coordinador de Seguimiento',
            ]
        );

        $permissions = [
            /**
             * Permisos para acceder a la configuración del módulo
             */
            [
                'name' => 'Configuración del módulo de Seguimiento',
                'slug' => 'project.tracking.setting.index',
                'description' => 'Acceso a la configuración del módulo de Seguimiento',
                'model' => '',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'configuracion.ver',
            ],
            /**
             * Roles permissions
             */
            [
                'name' => 'Crear roles',
                'slug' => 'project.tracking.staff.classification.create',
                'description' => 'Acceso para crear roles',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingStaffClassification',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'rol.crear',
            ],
            [
                'name' => 'Editar roles',
                'slug' => 'project.tracking.staff.classification.edit',
                'description' => 'Acceso para editar roles',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingStaffClassification',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'rol.editar',
            ],
            [
                'name' => 'Eliminar roles',
                'slug' => 'project.tracking.staff.classification.delete',
                'description' => 'Acceso para eliminar roles',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingStaffClassification',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'rol.eliminar',
            ],
            /**
             * Project Type permissions
             */
            [
                'name' => 'Crear tipos de proyecto',
                'slug' => 'project.tracking.project.type.create',
                'description' => 'Acceso para crear tipos de proyecto',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingProjectType',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'tipo.proyecto.crear',
            ],
            [
                'name' => 'Editar tipos de proyecto',
                'slug' => 'project.tracking.project.type.edit',
                'description' => 'Acceso para editar tipos de proyecto',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingProjectType',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'tipo.proyecto.editar',
            ],
            [
                'name' => 'Eliminar tipos de proyecto',
                'slug' => 'project.tracking.project.type.delete',
                'description' => 'Acceso para eliminar tipos de proyecto',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingProjectType',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'tipo.proyecto.eliminar',
            ],
            /**
             * Product Type permissions
             */
            [
                'name' => 'Crear tipos de producto',
                'slug' => 'project.tracking.product.type.create',
                'description' => 'Acceso para crear tipos de proyecto',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingTypeProducts',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'tipos.proyecto.crear',
            ],
            [
                'name' => 'Editar tipos de proyecto',
                'slug' => 'project.tracking.product.type.edit',
                'description' => 'Acceso para editar tipos de proyecto',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingTypeProducts',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'tipos.proyecto.editar',
            ],
            [
                'name' => 'Eliminar tipos de proyecto',
                'slug' => 'project.tracking.product.type.delete',
                'description' => 'Acceso para eliminar tipos de proyecto',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingTypeProducts',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'tipos.proyecto.eliminar',
            ],
            /**
             * Dependency Permissions
             */
            [
                'name' => 'Crear dependencias',
                'slug' => 'project.tracking.dependency.create',
                'description' => 'Acceso para crear dependencias',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingDependency',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'dependencia.crear',
            ],
            [
                'name' => 'Editar dependencias',
                'slug' => 'project.tracking.dependency.edit',
                'description' => 'Acceso para editar dependencias',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingDependency',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'dependencia.editar',
            ],
            [
                'name' => 'Eliminar dependencias',
                'slug' => 'project.tracking.dependency.delete',
                'description' => 'Acceso para eliminar dependencias',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingDependency',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'dependencia.eliminar',
            ],
            /**
             * Priority Permissions
             */
            [
                'name' => 'Crear prioridades',
                'slug' => 'project.tracking.priority.create',
                'description' => 'Acceso para crear prioridades',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingPriority',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'prioridad.crear',
            ],
            [
                'name' => 'Editar prioridades',
                'slug' => 'project.tracking.priority.edit',
                'description' => 'Acceso para editar prioridades',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingPriority',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'prioridad.editar',
            ],
            [
                'name' => 'Eliminar prioridades',
                'slug' => 'project.tracking.priority.delete',
                'description' => 'Acceso para eliminar prioridades',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingPriority',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'prioridad.eliminar',
            ],
            /**
             * Project Permissions
             */
            [
                'name' => 'Crear proyectos',
                'slug' => 'project.tracking.project.create',
                'description' => 'Acceso para crear proyectos',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingProject',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'proyecto.crear',
            ],
            [
                'name' => 'Editar proyectos',
                'slug' => 'project.tracking.project.edit',
                'description' => 'Acceso para editar proyectos',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingProject',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'proyecto.editar',
            ],
            [
                'name' => 'Eliminar proyectos',
                'slug' => 'project.tracking.project.delete',
                'description' => 'Acceso para eliminar proyectos',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingProject',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'proyecto.eliminar',
            ],
            /**
             * Subproject Permissions
             */
            [
                'name' => 'Crear subproyectos',
                'slug' => 'project.tracking.subproject.create',
                'description' => 'Acceso para crear subproyectos',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingSubproject',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'subproyecto.crear',
            ],
            [
                'name' => 'Editar subproyectos',
                'slug' => 'project.tracking.subproject.edit',
                'description' => 'Acceso para editar subproyectos',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingSubproject',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'subproyecto.editar',
            ],
            [
                'name' => 'Eliminar subproyectos',
                'slug' => 'project.tracking.subproject.delete',
                'description' => 'Acceso para eliminar subproyectos',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingSubproject',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'subproyecto.eliminar',
            ],
            /**
             * Product Permissions
             */
            [
                'name' => 'Crear productos',
                'slug' => 'project.tracking.product.create',
                'description' => 'Acceso para crear productos',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingProduct',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'producto.crear',
            ],
            [
                'name' => 'Editar productos',
                'slug' => 'project.tracking.product.edit',
                'description' => 'Acceso para editar productos',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingProduct',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'producto.editar',
            ],
            [
                'name' => 'Eliminar productos',
                'slug' => 'project.tracking.product.delete',
                'description' => 'Acceso para eliminar productos',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingProduct',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'producto.eliminar',
            ],
            /**
             * Activities Permissions
             */
            [
                'name' => 'Crear actividades',
                'slug' => 'project.tracking.activity.create',
                'description' => 'Acceso para crear actividades',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingActivity',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'actividad.crear',
            ],
            [
                'name' => 'Editar actividades',
                'slug' => 'project.tracking.activity.edit',
                'description' => 'Acceso para editar actividades',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingActivity',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'actividad.editar',
            ],
            [
                'name' => 'Eliminar actividades',
                'slug' => 'project.tracking.activity.delete',
                'description' => 'Acceso para eliminar actividades',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingActivity',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'actividad.eliminar',
            ],
            /**
             * Status Activity Permissions
             */
            [
                'name' => 'Crear status de actividad',
                'slug' => 'project.tracking.status.activity.create',
                'description' => 'Acceso para crear status de actividad',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingActivityStatus',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'estado.actividad.crear',
            ],
            [
                'name' => 'Editar status de actividad',
                'slug' => 'project.tracking.status.activity.edit',
                'description' => 'Acceso para editar status de actividad',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingActivityStatus',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'estado.actividad.editar',
            ],
            [
                'name' => 'Eliminar status de actividad',
                'slug' => 'project.tracking.status.activity.delete',
                'description' => 'Acceso para eliminar status de actividad',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingActivityStatus',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'estado.actividad.eliminar',
            ],
            /**
             * Delivery Status Permissions
             */
            [
                'name' => 'Crear status de entrega',
                'slug' => 'project.tracking.delivery.status.create',
                'description' => 'Acceso para crear status de entrega',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingDeliveryStatus',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'status.entrega.crear',
            ],
            [
                'name' => 'Editar status  de entrega',
                'slug' => 'project.tracking.delivery.status.edit',
                'description' => 'Acceso para editar status de entrega',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingDeliveryStatus',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'status.entrega.editar',
            ],
            [
                'name' => 'Eliminar status de entrega',
                'slug' => 'project.tracking.delivery.status.delete',
                'description' => 'Acceso para eliminar status de entrega',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingDeliveryStatus',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'status.entrega.eliminar',
            ],
            /**
             * Work Day Permissions
             */
            [
                'name' => 'Crear jornada de trabajo',
                'slug' => 'project.tracking.work.day.create',
                'description' => 'Acceso para crear jornadas de trabajo',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingWorkDay ',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'jornada.trabajo.crear',
            ],
            [
                'name' => 'Editar jornada de trabajo',
                'slug' => 'project.tracking.work.day.edit',
                'description' => 'Acceso para editar jornadas de trabajo',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingWorkDay ',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'jornada.trabajo.editar',
            ],
            [
                'name' => 'Eliminar jornada de trabajo',
                'slug' => 'project.tracking.work.day.delete',
                'description' => 'Acceso para eliminar jornadas de trabajo',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingWorkDay ',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'jornada.trabajo.eliminar',
            ],
            /**
             * Tasks Permissions
             */
            [
                'name' => 'Vista de tareas',
                'slug' => 'project.tracking.task.index',
                'description' => 'Acceso a la vista general de tareas',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingTask',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'tareas.ver',
            ],
            [
                'name' => 'Crear tareas',
                'slug' => 'project.tracking.task.create',
                'description' => 'Acceso para crear tareas',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingTask',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'tareas.crear',
            ],
            [
                'name' => 'Editar tareas',
                'slug' => 'project.tracking.task.edit',
                'description' => 'Acceso para editar tareas',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingTask',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'tareas.editar',
            ],
            [
                'name' => 'Eliminar tareas',
                'slug' => 'project.tracking.task.delete',
                'description' => 'Acceso para eliminar tareas',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingTask',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'tareas.eliminar',
            ],
            /**
             * Activity Plan Permissions
             */
            [
                'name' => 'Vista de planes de actividad',
                'slug' => 'project.tracking.activity.plan.index',
                'description' => 'Acceso a la vista general de planes de actividad',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingActivityPlan',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'plan.actividad.ver',
            ],
            [
                'name' => 'Crear planes de actividad',
                'slug' => 'project.tracking.activity.plan.create',
                'description' => 'Acceso para crear planes de actividad',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingActivityPlan',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'plan.actividad.crear',
            ],
            [
                'name' => 'Editar planes de actividad',
                'slug' => 'project.tracking.activity.plan.edit',
                'description' => 'Acceso para editar planes de actividad',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingActivityPlan',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'plan.actividad.editar',
            ],
            [
                'name' => 'Eliminar planes de actividad',
                'slug' => 'project.tracking.activity.plan.delete',
                'description' => 'Acceso para eliminar planes de actividad',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingActivityPlan',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'plan.actividad.eliminar',
            ],
            /**
             * Activity Type Permissions
             */
            [
                'name' => 'Crear tipo de actividad',
                'slug' => 'project.tracking.activity.type.create',
                'description' => 'Acceso para crear tipo de actividad',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingActivityType',
                'model_prefix' => 'Seguimiento',
                'slug_alt' => 'tipo-de-actividad.crear',
                'short_description' => 'Crear tipo de actividad',
            ],
            [
                'name' => 'Editar tipo de actividad',
                'slug' => 'project.tracking.activity.type.update',
                'description' => 'Acceso para editar tipo de actividad',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingActivityType', 'model_prefix' => 'Seguimiento',
                'slug_alt' => 'tipo-de-actividad.editar',
                'short_description' => 'Editar tipo de actividad',
            ],
            [
                'name' => 'Eliminar tipo de actividad',
                'slug' => 'project.tracking.activity.type.delete',
                'description' => 'Acceso para eliminar tipo de actividad',
                'model' => 'Modules\ProjectTracking\Models\ProjectTrackingActivityType', 'model_prefix' => 'Seguimiento',
                'slug_alt' => 'tipo-de-actividad.eliminar',
                'short_description' => 'Eliminar tipo de actividad',
            ],
        ];
        $projectTrackingRole->detachAllPermissions();

        foreach ($permissions as $permission) {
            $per = Permission::updateOrCreate(
                [
                    'slug' => $permission['slug']
                ],
                [
                    'name' => $permission['name'],
                    'description' => $permission['description'],
                    'model' => $permission['model'],
                    'model_prefix' => $permission['model_prefix'],
                    'slug_alt' => $permission['slug_alt'],
                ]
            );

            $projectTrackingRole->attachPermission($per);

            if ($adminRole) {
                $adminRole->attachPermission($per);
            }
        }
    }
}
