<?php

namespace Modules\Purchase\Database\Seeders;

use App\Roles\Models\Role;
use Illuminate\Database\Seeder;
use App\Models\RequiredDocument;
use App\Roles\Models\Permission;
use Illuminate\Database\Eloquent\Model;
use Modules\Purchase\Models\PurchasePriorityOrder;
use Modules\Purchase\Models\PurchasePlan;
use Modules\Purchase\Models\PurchaseSupplier;
use Modules\Purchase\Models\PurchaseQuotation;
use Modules\Purchase\Models\PurchaseDirectHire;
use Modules\Purchase\Models\PurchaseRequirement;
use Modules\Purchase\Models\PurchaseActivityType;
use Modules\Purchase\Models\PurchaseSupplierType;
use Modules\Purchase\Models\PurchaseSupplierSpecialty;
use Modules\Purchase\Models\PurchaseBudgetaryAvailability;
use Modules\Purchase\Models\PurchasePriority;

/**
 * @class PurchaseRoleAndPermissionsTableSeeder
 * @brief Información por defecto para Roles y Permisos del módulo de compras
 *
 * Gestiona la información por defecto a registrar inicialmente para los Roles y Permisos del módulo de compras
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseRoleAndPermissionsTableSeeder extends Seeder
{
    protected $priorityOrderPermissions = [
        [
            'name' => 'Ver orden de prioridades de compra',
            'slug' => 'purchase.priority.orders.index',
            'description' => 'Acceso para ver orden de prioridades de compra',
            'model' => PurchasePriorityOrder::class,
            'model_prefix' => 'compras',
            'slug_alt' => 'compras.orden.prioridad.ver',
            'short_description' => 'ver orden de prioridad de compra'
        ],
        [
            'name' => 'Crear orden de prioridades de compra',
            'slug' => 'purchase.priority.orders.store',
            'description' => 'Acceso para crear orden de prioridades de compra',
            'model' => PurchasePriorityOrder::class,
            'model_prefix' => 'compras',
            'slug_alt' => 'compras.orden.prioridad.crear',
            'short_description' => 'crear orden de prioridad de compra'
        ],
        [
            'name' => 'Editar orden de prioridades de compra',
            'slug' => 'purchase.priority.orders.update',
            'description' => 'Acceso para editar orden de prioridades de compra',
            'model' => PurchasePriorityOrder::class,
            'model_prefix' => 'compras',
            'slug_alt' => 'compras.orden.prioridad.editar',
            'short_description' => 'actualizar orden de prioridad de compra'
        ],
        [
            'name' => 'Eliminar orden de prioridades de compra',
            'slug' => 'purchase.priority.orders.delete',
            'description' => 'Acceso para eliminar orden de prioridades de compra',
            'model' => PurchasePriorityOrder::class,
            'model_prefix' => 'compras',
            'slug_alt' => 'compras.orden.prioridad.eliminar',
            'short_description' => 'eliminar orden de prioridad de compra'
        ]
    ];

    protected $supplierPermissions = [
        /* Especialidades de proveedores */
        [
            'name' => 'Crear especialidad de proveedor',
            'slug' => 'purchase.supplierspecialty.create',
            'description' => 'Acceso para crear especialidad de proveedor',
            'model' => PurchaseSupplierSpecialty::class, 'model_prefix' => 'compras',
            'slug_alt' => 'especialidad.proveedor.crear',
            'short_description' => 'agregar especialidad de proveedor'
        ],
        [
            'name' => 'Editar especialidad de proveedor',
            'slug' => 'purchase.supplierspecialty.edit',
            'description' => 'Acceso para editar especialidad de proveedor',
            'model' => PurchaseSupplierSpecialty::class, 'model_prefix' => 'compras',
            'slug_alt' => 'especialidad.proveedor.editar',
            'short_description' => 'editar especialidad de proveedor'
        ],
        [
            'name' => 'Eliminar especialidad de proveedor',
            'slug' => 'purchase.supplierspecialty.delete',
            'description' => 'Acceso para eliminar especialidad de proveedor',
            'model' => PurchaseSupplierSpecialty::class, 'model_prefix' => 'compras',
            'slug_alt' => 'especialidad.proveedor.eliminar',
            'short_description' => 'eliminar especialidad de proveedor'
        ],
        [
            'name' => 'Ver especialidades de proveedores',
            'slug' => 'purchase.supplierspecialty.list',
            'description' => 'Acceso para ver especialidades de proveedores',
            'model' => PurchaseSupplierSpecialty::class, 'model_prefix' => 'compras',
            'slug_alt' => 'especialidad.proveedor.ver',
            'short_description' => 'ver especialidad de proveedor'
        ],
        /* Tipos de proveedores */
        [
            'name' => 'Crear tipo de proveedor',
            'slug' => 'purchase.suppliertype.create',
            'description' => 'Acceso para crear tipo de proveedor',
            'model' => PurchaseSupplierType::class, 'model_prefix' => 'compras',
            'slug_alt' => 'tipo.proveedor.crear',
            'short_description' => 'Agregar tipo de proveedor'
        ],
        [
            'name' => 'Editar tipo de proveedor',
            'slug' => 'purchase.suppliertype.edit',
            'description' => 'Acceso para editar tipo de proveedor',
            'model' => PurchaseSupplierType::class, 'model_prefix' => 'compras',
            'slug_alt' => 'tipo.proveedor.editar',
            'short_description' => 'Editar tipo de proveedor'
        ],
        [
            'name' => 'Eliminar tipo de proveedor',
            'slug' => 'purchase.suppliertype.delete',
            'description' => 'Acceso para eliminar tipo de proveedor',
            'model' => PurchaseSupplierType::class, 'model_prefix' => 'compras',
            'slug_alt' => 'tipo.proveedor.eliminar',
            'short_description' => 'Eliminar tipo de proveedor'
        ],
        [
            'name' => 'Ver tipos de proveedores',
            'slug' => 'purchase.suppliertype.list',
            'description' => 'Acceso para ver tipos de proveedores',
            'model' => PurchaseSupplierType::class, 'model_prefix' => 'compras',
            'slug_alt' => 'tipo.proveedor.ver',
            'short_description' => 'Ver tipo de proveedor'
        ],
        /* Proveedores */
        [
            'name' => 'Crear proveedor',
            'slug' => 'purchase.supplier.create',
            'description' => 'Acceso para crear proveedor',
            'model' => PurchaseSupplier::class, 'model_prefix' => 'compras',
            'slug_alt' => 'proveedor.crear',
            'short_description' => 'Agregar proveedor'
        ],
        [
            'name' => 'Editar proveedor',
            'slug' => 'purchase.supplier.edit',
            'description' => 'Acceso para editar proveedor',
            'model' => PurchaseSupplier::class, 'model_prefix' => 'compras',
            'slug_alt' => 'proveedor.editar',
            'short_description' => 'Editar proveedor'
        ],
        [
            'name' => 'Eliminar proveedor',
            'slug' => 'purchase.supplier.delete',
            'description' => 'Acceso para eliminar proveedor',
            'model' => PurchaseSupplier::class, 'model_prefix' => 'compras',
            'slug_alt' => 'proveedor.eliminar',
            'short_description' => 'Eliminar proveedor'
        ],
        [
            'name' => 'Ver proveedores',
            'slug' => 'purchase.supplier.list',
            'description' => 'Acceso para ver tipos de proveedores',
            'model' => PurchaseSupplier::class, 'model_prefix' => 'compras',
            'slug_alt' => 'proveedor.ver',
            'short_description' => 'Ver proveedor'
        ],
    ];

    protected $requirementPermissions = [
        [
            'name' => 'Crear requerimiento',
            'slug' => 'purchase.requirements.create',
            'description' => 'Acceso para crear requerimiento',
            'model' => PurchaseRequirement::class, 'model_prefix' => 'compras',
            'slug_alt' => 'requerimiento.crear',
            'short_description' => 'Ggregar requerimiento'
        ],
        [
            'name' => 'Editar requerimiento',
            'slug' => 'purchase.requirements.edit',
            'description' => 'Acceso para editar requerimiento',
            'model' => PurchaseRequirement::class, 'model_prefix' => 'compras',
            'slug_alt' => 'requerimiento.editar',
            'short_description' => 'Editar requerimiento'
        ],
        [
            'name' => 'Eliminar requerimiento',
            'slug' => 'purchase.requirements.delete',
            'description' => 'Acceso para eliminar requerimiento',
            'model' => PurchaseRequirement::class, 'model_prefix' => 'compras',
            'slug_alt' => 'requerimiento.eliminar',
            'short_description' => 'Eliminar requerimiento'
        ],
        [
            'name' => 'Ver requerimiento',
            'slug' => 'purchase.requirements.list',
            'description' => 'Acceso para ver requerimientos',
            'model' => PurchaseRequirement::class, 'model_prefix' => 'compras',
            'slug_alt' => 'requerimiento.ver',
            'short_description' => 'Ver requerimiento'
        ],
    ];

    protected $quotationPermissions = [
        [
            'name' => 'Crear cotizaciones',
            'slug' => 'purchase.quotations.create',
            'description' => 'Acceso para crear cotizacion',
            'model' => PurchaseQuotation::class, 'model_prefix' => 'compras',
            'slug_alt' => 'cotizacion.crear',
            'short_description' => 'Ggregar cotizacion'
        ],
        [
            'name' => 'Editar cotización',
            'slug' => 'purchase.quotations.edit',
            'description' => 'Acceso para editar cotización',
            'model' => PurchaseQuotation::class, 'model_prefix' => 'compras',
            'slug_alt' => 'cotizacion.editar',
            'short_description' => 'Editar cotización'
        ],
        [
            'name' => 'Eliminar cotización',
            'slug' => 'purchase.quotations.delete',
            'description' => 'Acceso para eliminar cotización',
            'model' => PurchaseQuotation::class, 'model_prefix' => 'compras',
            'slug_alt' => 'cotizacion.eliminar',
            'short_description' => 'Eliminar cotización'
        ],
        [
            'name' => 'Ver cotización',
            'slug' => 'purchase.quotations.list',
            'description' => 'Acceso para ver cotizacion',
            'model' => PurchaseQuotation::class, 'model_prefix' => 'compras',
            'slug_alt' => 'cotizacion.ver',
            'short_description' => 'Ver cotización'
        ],
    ];

    protected $directHirePermissions = [
        [
            'name' => 'Crear Orden de compra ',
            'slug' => 'purchase.directhire.create',
            'description' => 'Acceso para crear Orden de compra ',
            'model' => PurchaseDirectHire::class, 'model_prefix' => 'compras',
            'slug_alt' => 'orden.crear',
            'short_description' => 'Agregar Orden de compra '
        ],
        [
            'name' => 'Editar Orden de compra ',
            'slug' => 'purchase.directhire.edit',
            'description' => 'Acceso para editar Orden de compra ',
            'model' => PurchaseDirectHire::class, 'model_prefix' => 'compras',
            'slug_alt' => 'orden.editar',
            'short_description' => 'editar Orden de compra '
        ],
        [
            'name' => 'Eliminar Orden de compra ',
            'slug' => 'purchase.directhire.delete',
            'description' => 'Acceso para eliminar Orden de compra ',
            'model' => PurchaseDirectHire::class, 'model_prefix' => 'compras',
            'slug_alt' => 'orden.eliminar',
            'short_description' => 'Eliminar Orden de compra '
        ],
        [
            'name' => 'Ver Orden de compra ',
            'slug' => 'purchase.directhire.list',
            'description' => 'Acceso para ver Orden de compra ',
            'model' => PurchaseDirectHire::class, 'model_prefix' => 'compras',
            'slug_alt' => 'orden.ver',
            'short_description' => 'Ver Orden de compra'
        ],
    ];

    protected $planPermissions = [
        [
            'name' => 'Crear Plan de compra ',
            'slug' => 'purchase.purchaseplans.create',
            'description' => 'Acceso para Plan de compra ',
            'model' => PurchasePlan::class, 'model_prefix' => 'compras',
            'slug_alt' => 'plan.crear',
            'short_description' => 'Agregar Plan de compra  '
        ],
        [
            'name' => 'Editar Plan de compra  ',
            'slug' => 'purchase.purchaseplans.edit',
            'description' => 'Acceso para editar Plan de compra  ',
            'model' => PurchasePlan::class, 'model_prefix' => 'compras',
            'slug_alt' => 'plan.editar',
            'short_description' => 'Editar Plan de compra  '
        ],
        [
            'name' => 'Eliminar Plan de compra  ',
            'slug' => 'purchase.purchaseplans.delete',
            'description' => 'Acceso para eliminar Plan de compra  ',
            'model' => PurchasePlan::class, 'model_prefix' => 'compras',
            'slug_alt' => 'plan.eliminar',
            'short_description' => 'Eliminar Plan de compra  '
        ],
        [
            'name' => 'Ver Plan de compra ',
            'slug' => 'purchase.purchaseplans.list',
            'description' => 'Acceso para ver Plan de compra ',
            'model' => PurchasePlan::class, 'model_prefix' => 'compras',
            'slug_alt' => 'plan.ver',
            'short_description' => 'Ver Plan de compra'
        ],
    ];

    protected $activityTypePermissions = [
        [
            'name' => 'Ver tipos de actividad de compra',
            'slug' => 'purchase.activity.types.index',
            'description' => 'Acceso para ver tipos de actividad de compra',
            'model' => PurchaseActivityType::class,
            'model_prefix' => 'compras',
            'slug_alt' => 'compras.tipo.actividad.ver',
            'short_description' => 'ver tipo de actividad de compra'
        ],
        [
            'name' => 'Crear tipos de actividad de compra',
            'slug' => 'purchase.activity.types.store',
            'description' => 'Acceso para crear tipos de actividad de compra',
            'model' => PurchaseActivityType::class,
            'model_prefix' => 'compras',
            'slug_alt' => 'compras.tipo.actividad.crear',
            'short_description' => 'crear tipo de actividad de compra'
        ],
        [
            'name' => 'Editar tipos de actividad de compra',
            'slug' => 'purchase.activity.types.update',
            'description' => 'Acceso para editar tipos de actividad de compra',
            'model' => PurchaseActivityType::class,
            'model_prefix' => 'compras',
            'slug_alt' => 'compras.tipo.actividad.editar',
            'short_description' => 'actualizar tipo de actividad de compra'
        ],
        [
            'name' => 'Eliminar tipos de actividad de compra',
            'slug' => 'purchase.activity.types.delete',
            'description' => 'Acceso para eliminar tipos de actividad de compra',
            'model' => PurchaseActivityType::class,
            'model_prefix' => 'compras',
            'slug_alt' => 'compras.tipo.actividad.eliminar',
            'short_description' => 'eliminar tipo de actividad de compra'
        ],
    ];

    protected $priorityPermissions = [
        [
            'name' => 'Ver prioridades de compra',
            'slug' => 'purchase.priorities.index',
            'description' => 'Acceso para ver prioridades de compra',
            'model' => PurchasePriority::class,
            'model_prefix' => 'compras',
            'slug_alt' => 'compras.prioridad.ver',
            'short_description' => 'ver prioridad de compra'
        ],
        [
            'name' => 'Crear prioridades de compra',
            'slug' => 'purchase.priorities.store',
            'description' => 'Acceso para crear prioridades de compra',
            'model' => PurchasePriority::class,
            'model_prefix' => 'compras',
            'slug_alt' => 'compras.prioridad.crear',
            'short_description' => 'crear prioridad de compra'
        ],
        [
            'name' => 'Editar prioridades de compra',
            'slug' => 'purchase.priorities.update',
            'description' => 'Acceso para editar prioridades de compra',
            'model' => PurchasePriority::class,
            'model_prefix' => 'compras',
            'slug_alt' => 'compras.prioridad.editar',
            'short_description' => 'actualizar prioridad de compra'
        ],
        [
            'name' => 'Eliminar prioridades de compra',
            'slug' => 'purchase.priorities.delete',
            'description' => 'Acceso para eliminar prioridades de compra',
            'model' => PurchasePriority::class,
            'model_prefix' => 'compras',
            'slug_alt' => 'compras.prioridad.eliminar',
            'short_description' => 'eliminar prioridad de compra'
        ],
    ];

    /**
     * Método que ejecuta el seeder e inserta los datos en la base de datos.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $adminRole = Role::where('slug', 'admin')->first();

        $purchaseRole = Role::updateOrCreate(
            ['slug' => 'purchase'],
            ['name' => 'Compras', 'description' => 'Coordinador de compras']
        );

        $permissions = [
            [
                'name' => 'Inicio del módulo de compras',
                'slug' => 'purchase.home',
                'description' => 'Acceso a descripción del módulo de compras',
                'model' => '',
                'model_prefix' => 'compras',
                'slug_alt' => 'compras.inicio',
                'short_description' => 'página de inicio'
            ],
            [
                'name' => 'Configuración del módulo de compras',
                'slug' => 'purchase.setting.create',
                'description' => 'Acceso a la configuración del módulo de compras',
                'model' => '',
                'model_prefix' => 'compras',
                'slug_alt' => 'configuracion.crear',
                'short_description' => 'agregar configuración'
            ],
            [
                'name' => 'Editar configuración del módulo de compras',
                'slug' => 'purchase.setting.edit',
                'description' => 'Acceso para editar la configuración del módulo de compras',
                'model' => '',
                'model_prefix' => 'compras',
                'slug_alt' => 'configuracion.editar',
                'short_description' => 'editar configuración'
            ],
            [
                'name' => 'Ver configuración del módulo de compras',
                'slug' => 'purchase.setting.list',
                'description' => 'Acceso para ver la configuración del módulo de compras',
                'model' => '',
                'model_prefix' => 'compras',
                'slug_alt' => 'configuracion.ver',
                'short_description' => 'ver configuración'
            ],
            [
                'name' => 'Eliminar configuración del módulo de compras',
                'slug' => 'purchase.setting.delete',
                'description' => 'Acceso para eliminar la configuración del módulo de compras',
                'model' => '',
                'model_prefix' => 'compras',
                'slug_alt' => 'configuracion.eliminar',
                'short_description' => 'eliminar configuración'
            ],
            ...$this->supplierPermissions,
            ...$this->requirementPermissions,
            ...$this->quotationPermissions,
            ...$this->directHirePermissions,
            ...$this->planPermissions,
            [
                'name' => 'Solicitar disponibilidad presupuestaria',
                'slug' => 'purchase.availability.request',
                'description' => 'Acceso para solicitar disponibilidad presupuestaria ',
                'model' => PurchaseBudgetaryAvailability::class,
                'model_prefix' => 'compras',
                'slug_alt' => 'compras.disponibilidad.solicitar',
                'short_description' => 'Solicitar disponibilidad presupuestaria'
            ],
            ...$this->priorityOrderPermissions,
            ...$this->activityTypePermissions,
            ...$this->priorityPermissions,
            /* Dashboard */
            [
                'name'              => 'Vista principal del dashboard del módulo de compras',
                'slug'              => 'purchase.dashboard',
                'description'       => 'Acceso para visualizar el dashboard del módulo',
                'model'             => '',
                'model_prefix'      => 'compras',
                'slug_alt'          => 'panel_de_control.ver',
                'short_description' => 'Visualizar panel de control del módulo de compras'
            ],
        ];

        $purchaseRole->detachAllPermissions();

        foreach ($permissions as $permission) {
            $per = Permission::updateOrCreate(
                ['slug' => strtolower($permission['slug'])],
                [
                    'name' => $permission['name'], 'description' => $permission['description'],
                    'model' => $permission['model'], 'model_prefix' => $permission['model_prefix'],
                    'slug_alt' => $permission['slug_alt'],
                    'short_description' => $permission['short_description']
                ]
            );

            $purchaseRole->attachPermission($per);

            if ($adminRole) {
                $adminRole->attachPermission($per);
            }
        }

        $reqDoc = Permission::where('model', RequiredDocument::class)->first();

        if ($reqDoc) {
            $purchaseRole->attachPermission($reqDoc);
        }
    }
}
