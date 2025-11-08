<?php

namespace Modules\ProjectTracking\Database\Seeders;

use App\Roles\Models\Role;
use Illuminate\Database\Seeder;
use App\Roles\Models\Permission;
use Illuminate\Database\Eloquent\Model;
use Modules\ProjectTracking\Models\ProjectTrackingTags;
use Modules\ProjectTracking\Models\ProjectTrackingTask;
use Modules\ProjectTracking\Models\ProjectTrackingProduct;
use Modules\ProjectTracking\Models\ProjectTrackingProject;
use Modules\ProjectTracking\Models\ProjectTrackingWorkDay;
use Modules\ProjectTracking\Models\ProjectTrackingActivity;
use Modules\ProjectTracking\Models\ProjectTrackingPriority;
use Modules\ProjectTracking\Models\ProjectTrackingTaskTypes;
use Modules\ProjectTracking\Models\ProjectTrackingDependency;
use Modules\ProjectTracking\Models\ProjectTrackingSubProject;
use Modules\ProjectTracking\Models\ProjectTrackingProjectType;
use Modules\ProjectTracking\Models\ProjectTrackingActivityPlan;
use Modules\ProjectTracking\Models\ProjectTrackingActivityType;
use Modules\ProjectTracking\Models\ProjectTrackingTypeProducts;
use Modules\ProjectTracking\Models\ProjectTrackingActivityStatus;
use Modules\ProjectTracking\Models\ProjectTrackingDeliveryStatus;
use Modules\ProjectTracking\Models\ProjectTrackingDependenciesType;
use Modules\ProjectTracking\Models\ProjectTrackingStaffClassification;

/**
 * @class ProjectTrackingRoleAndPermissionsTableSeeder
 * @brief Gestiona la inserción de permisos en la base de datos
 *
 * Clase que gestiona la inserción de permisos en la base de datos.
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingRoleAndPermissionsTableSeeder extends Seeder
{
    protected $projectPermissions = [
        /**
         * Project Type permissions
         */
        [
            'name' => 'Crear tipos de proyecto',
            'slug' => 'project.tracking.project.type.create',
            'description' => 'Acceso para crear tipos de proyecto',
            'model' => ProjectTrackingProjectType::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'tipo.proyecto.crear',
        ],
        [
            'name' => 'Editar tipos de proyecto',
            'slug' => 'project.tracking.project.type.edit',
            'description' => 'Acceso para editar tipos de proyecto',
            'model' => ProjectTrackingProjectType::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'tipo.proyecto.editar',
        ],
        [
            'name' => 'Eliminar tipos de proyecto',
            'slug' => 'project.tracking.project.type.delete',
            'description' => 'Acceso para eliminar tipos de proyecto',
            'model' => ProjectTrackingProjectType::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'tipo.proyecto.eliminar',
        ],
        /**
         * Project Permissions
         */
        [
            'name' => 'Crear proyectos',
            'slug' => 'project.tracking.project.create',
            'description' => 'Acceso para crear proyectos',
            'model' => ProjectTrackingProject::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'proyecto.crear',
        ],
        [
            'name' => 'Editar proyectos',
            'slug' => 'project.tracking.project.edit',
            'description' => 'Acceso para editar proyectos',
            'model' => ProjectTrackingProject::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'proyecto.editar',
        ],
        [
            'name' => 'Eliminar proyectos',
            'slug' => 'project.tracking.project.delete',
            'description' => 'Acceso para eliminar proyectos',
            'model' => ProjectTrackingProject::class,
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
            'model' => ProjectTrackingSubProject::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'subproyecto.crear',
        ],
        [
            'name' => 'Editar subproyectos',
            'slug' => 'project.tracking.subproject.edit',
            'description' => 'Acceso para editar subproyectos',
            'model' => ProjectTrackingSubProject::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'subproyecto.editar',
        ],
        [
            'name' => 'Eliminar subproyectos',
            'slug' => 'project.tracking.subproject.delete',
            'description' => 'Acceso para eliminar subproyectos',
            'model' => ProjectTrackingSubProject::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'subproyecto.eliminar',
        ],
    ];

    protected $dependenciesPermissions = [
        /**
         * Dependency Permissions
         */
        [
            'name' => 'Crear dependencias',
            'slug' => 'project.tracking.dependency.create',
            'description' => 'Acceso para crear dependencias',
            'model' => ProjectTrackingDependency::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'dependencia.crear',
        ],
        [
            'name' => 'Editar dependencias',
            'slug' => 'project.tracking.dependency.edit',
            'description' => 'Acceso para editar dependencias',
            'model' => ProjectTrackingDependency::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'dependencia.editar',
        ],
        [
            'name' => 'Eliminar dependencias',
            'slug' => 'project.tracking.dependency.delete',
            'description' => 'Acceso para eliminar dependencias',
            'model' => ProjectTrackingDependency::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'dependencia.eliminar',
        ],
        /**
         * Dependencies Type Permissions
         */
        [
            'name' => 'Crear tipos de dependencias',
            'slug' => 'project.tracking.dependencies.type.create',
            'description' => 'Acceso para crear tipos de dependencias',
            'model' => ProjectTrackingDependenciesType::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'tipos.dependencias.crear',
        ],
        [
            'name' => 'Editar tipos de dependencias',
            'slug' => 'project.tracking.dependencies.type.edit',
            'description' => 'Acceso para editar tipos de dependencias',
            'model' => ProjectTrackingDependenciesType::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'tipos.dependencias.editar',
        ],
        [
            'name' => 'Eliminar tipos de dependencias',
            'slug' => 'project.tracking.dependencies.type.delete',
            'description' => 'Acceso para eliminar tipos de dependencias',
            'model' => ProjectTrackingDependenciesType::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'tipos.dependencias.eliminar',
        ],
    ];

    protected $activitiesPermissions = [
        /**
         * Activities Permissions
         */
        [
            'name' => 'Crear actividades',
            'slug' => 'project.tracking.activity.create',
            'description' => 'Acceso para crear actividades',
            'model' => ProjectTrackingActivity::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'actividad.crear',
        ],
        [
            'name' => 'Editar actividades',
            'slug' => 'project.tracking.activity.edit',
            'description' => 'Acceso para editar actividades',
            'model' => ProjectTrackingActivity::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'actividad.editar',
        ],
        [
            'name' => 'Eliminar actividades',
            'slug' => 'project.tracking.activity.delete',
            'description' => 'Acceso para eliminar actividades',
            'model' => ProjectTrackingActivity::class,
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
            'model' => ProjectTrackingActivityStatus::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'estado.actividad.crear',
        ],
        [
            'name' => 'Editar status de actividad',
            'slug' => 'project.tracking.status.activity.edit',
            'description' => 'Acceso para editar status de actividad',
            'model' => ProjectTrackingActivityStatus::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'estado.actividad.editar',
        ],
        [
            'name' => 'Eliminar status de actividad',
            'slug' => 'project.tracking.status.activity.delete',
            'description' => 'Acceso para eliminar status de actividad',
            'model' => ProjectTrackingActivityStatus::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'estado.actividad.eliminar',
        ],
        /**
         * Activity Plan Permissions
         */
        [
            'name' => 'Vista de planes de actividad',
            'slug' => 'project.tracking.activity.plan.index',
            'description' => 'Acceso a la vista general de planes de actividad',
            'model' => ProjectTrackingActivityPlan::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'plan.actividad.ver',
        ],
        [
            'name' => 'Crear planes de actividad',
            'slug' => 'project.tracking.activity.plan.create',
            'description' => 'Acceso para crear planes de actividad',
            'model' => ProjectTrackingActivityPlan::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'plan.actividad.crear',
        ],
        [
            'name' => 'Editar planes de actividad',
            'slug' => 'project.tracking.activity.plan.edit',
            'description' => 'Acceso para editar planes de actividad',
            'model' => ProjectTrackingActivityPlan::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'plan.actividad.editar',
        ],
        [
            'name' => 'Eliminar planes de actividad',
            'slug' => 'project.tracking.activity.plan.delete',
            'description' => 'Acceso para eliminar planes de actividad',
            'model' => ProjectTrackingActivityPlan::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'plan.actividad.eliminar',
        ],
    ];

    protected $productsPermissions = [
        /**
         * Product Type permissions
         */
        [
            'name' => 'Crear tipos de producto',
            'slug' => 'project.tracking.product.type.create',
            'description' => 'Acceso para crear tipos de proyecto',
            'model' => ProjectTrackingTypeProducts::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'tipos.proyecto.crear',
        ],
        [
            'name' => 'Editar tipos de proyecto',
            'slug' => 'project.tracking.product.type.edit',
            'description' => 'Acceso para editar tipos de proyecto',
            'model' => ProjectTrackingTypeProducts::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'tipos.proyecto.editar',
        ],
        [
            'name' => 'Eliminar tipos de proyecto',
            'slug' => 'project.tracking.product.type.delete',
            'description' => 'Acceso para eliminar tipos de proyecto',
            'model' => ProjectTrackingTypeProducts::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'tipos.proyecto.eliminar',
        ],
        /**
         * Product Permissions
         */
        [
            'name' => 'Crear productos',
            'slug' => 'project.tracking.product.create',
            'description' => 'Acceso para crear productos',
            'model' => ProjectTrackingProduct::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'producto.crear',
        ],
        [
            'name' => 'Editar productos',
            'slug' => 'project.tracking.product.edit',
            'description' => 'Acceso para editar productos',
            'model' => ProjectTrackingProduct::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'producto.editar',
        ],
        [
            'name' => 'Eliminar productos',
            'slug' => 'project.tracking.product.delete',
            'description' => 'Acceso para eliminar productos',
            'model' => ProjectTrackingProduct::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'producto.eliminar',
        ],
    ];

    protected $tasksPermissions = [
        /**
         * Tasks Permissions
         */
        [
            'name' => 'Vista de tareas',
            'slug' => 'project.tracking.task.index',
            'description' => 'Acceso a la vista general de tareas',
            'model' => ProjectTrackingTask::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'tareas.ver',
        ],
        [
            'name' => 'Crear tareas',
            'slug' => 'project.tracking.task.create',
            'description' => 'Acceso para crear tareas',
            'model' => ProjectTrackingTask::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'tareas.crear',
        ],
        [
            'name' => 'Editar tareas',
            'slug' => 'project.tracking.task.edit',
            'description' => 'Acceso para editar tareas',
            'model' => ProjectTrackingTask::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'tareas.editar',
        ],
        [
            'name' => 'Eliminar tareas',
            'slug' => 'project.tracking.task.delete',
            'description' => 'Acceso para eliminar tareas',
            'model' => ProjectTrackingTask::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'tareas.eliminar',
        ],
        /**
         * Task Types Permissions
         */
        [
            'name' => 'Crear tipo de tareas',
            'slug' => 'project.tracking.task.types.create',
            'description' => 'Acceso para crear tipo de tareas',
            'model' => ProjectTrackingTaskTypes::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'tipo-de-actividad.crear',
            'short_description' => 'Crear tipo de tareas',
        ],
        [
            'name' => 'Editar tipo de tareas',
            'slug' => 'project.tracking.task.types.update',
            'description' => 'Acceso para editar tipo de tareas',
            'model' => ProjectTrackingTaskTypes::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'tipo-de-actividad.editar',
            'short_description' => 'Editar tipo de tareas',
        ],
        [
            'name' => 'Eliminar tipo de tareas',
            'slug' => 'project.tracking.task.types.delete',
            'description' => 'Acceso para eliminar tipo de tareas',
            'model' => ProjectTrackingTaskTypes::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'tipo-de-actividad.eliminar',
            'short_description' => 'Eliminar tipo de tareas',
        ],
    ];

    protected $staffsPermissions = [
        /**
         * Roles permissions
         */
        [
            'name' => 'Crear roles',
            'slug' => 'project.tracking.staff.classification.create',
            'description' => 'Acceso para crear roles',
            'model' => ProjectTrackingStaffClassification::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'rol.crear',
        ],
        [
            'name' => 'Editar roles',
            'slug' => 'project.tracking.staff.classification.edit',
            'description' => 'Acceso para editar roles',
            'model' => ProjectTrackingStaffClassification::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'rol.editar',
        ],
        [
            'name' => 'Eliminar roles',
            'slug' => 'project.tracking.staff.classification.delete',
            'description' => 'Acceso para eliminar roles',
            'model' => ProjectTrackingStaffClassification::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'rol.eliminar',
        ]
    ];

    protected $prioritiesPermissions = [
        /**
         * Priority Permissions
         */
        [
            'name' => 'Crear prioridades',
            'slug' => 'project.tracking.priority.create',
            'description' => 'Acceso para crear prioridades',
            'model' => ProjectTrackingPriority::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'prioridad.crear',
        ],
        [
            'name' => 'Editar prioridades',
            'slug' => 'project.tracking.priority.edit',
            'description' => 'Acceso para editar prioridades',
            'model' => ProjectTrackingPriority::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'prioridad.editar',
        ],
        [
            'name' => 'Eliminar prioridades',
            'slug' => 'project.tracking.priority.delete',
            'description' => 'Acceso para eliminar prioridades',
            'model' => ProjectTrackingPriority::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'prioridad.eliminar',
        ],
    ];

    protected $deliveryPermissions = [
        /**
         * Delivery Status Permissions
         */
        [
            'name' => 'Crear status de entrega',
            'slug' => 'project.tracking.delivery.status.create',
            'description' => 'Acceso para crear status de entrega',
            'model' => ProjectTrackingDeliveryStatus::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'status.entrega.crear',
        ],
        [
            'name' => 'Editar status  de entrega',
            'slug' => 'project.tracking.delivery.status.edit',
            'description' => 'Acceso para editar status de entrega',
            'model' => ProjectTrackingDeliveryStatus::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'status.entrega.editar',
        ],
        [
            'name' => 'Eliminar status de entrega',
            'slug' => 'project.tracking.delivery.status.delete',
            'description' => 'Acceso para eliminar status de entrega',
            'model' => ProjectTrackingDeliveryStatus::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'status.entrega.eliminar',
        ],
    ];

    protected $workDaysPermissions = [
        /**
         * Work Day Permissions
         */
        [
            'name' => 'Crear jornada de trabajo',
            'slug' => 'project.tracking.work.day.create',
            'description' => 'Acceso para crear jornadas de trabajo',
            'model' => ProjectTrackingWorkDay::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'jornada.trabajo.crear',
        ],
        [
            'name' => 'Editar jornada de trabajo',
            'slug' => 'project.tracking.work.day.edit',
            'description' => 'Acceso para editar jornadas de trabajo',
            'model' => ProjectTrackingWorkDay::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'jornada.trabajo.editar',
        ],
        [
            'name' => 'Eliminar jornada de trabajo',
            'slug' => 'project.tracking.work.day.delete',
            'description' => 'Acceso para eliminar jornadas de trabajo',
            'model' => ProjectTrackingWorkDay::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'jornada.trabajo.eliminar',
        ],
    ];

    protected $tagsPermissions = [
        /**
         * Tags Permissions
         */
        [
            'name' => 'Crear tipo de etiqueta',
            'slug' => 'project.tracking.tags.create',
            'description' => 'Acceso para crear tipo de etiqueta',
            'model' => ProjectTrackingTags::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'tags.crear',
            'short_description' => 'Crear tipo de etiqueta',
        ],
        [
            'name' => 'Editar tipo de etiqueta',
            'slug' => 'project.tracking.tags.update',
            'description' => 'Acceso para editar tipo de etiqueta',
            'model' => ProjectTrackingTags::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'tags.editar',
            'short_description' => 'Editar tipo de etiqueta',
        ],
        [
            'name' => 'Eliminar tipo de etiqueta',
            'slug' => 'project.tracking.tags.delete',
            'description' => 'Acceso para eliminar tipo de etiqueta',
            'model' => ProjectTrackingTags::class,
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'tags.eliminar',
            'short_description' => 'Eliminar tipo de etiqueta',
        ],
    ];

    protected $reportPermissions = [
        /* Permisos de Ruta que permite generar el reporte de los empleados */
        [
            'name' => 'Crear reporte de trabajadores',
            'slug' => 'project.tracking.reports.create',
            'description' => 'Acceso para crear reporte de trabajadores',
            'model' => '',
            'model_prefix' => 'Seguimiento',
            'slug_alt' => 'bienes.reporte.crear',
            'short_description' => 'generar reporte de trabajadores',
        ],
    ];

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
            ...$this->staffsPermissions,
            ...$this->projectPermissions,
            ...$this->dependenciesPermissions,
            ...$this->activitiesPermissions,
            ...$this->productsPermissions,
            ...$this->tasksPermissions,
            ...$this->prioritiesPermissions,
            ...$this->deliveryPermissions,
            ...$this->workDaysPermissions,
            ...$this->tagsPermissions,
            ...$this->reportPermissions
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
