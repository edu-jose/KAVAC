<?php

namespace Modules\CitizenService\Database\Seeders;

use App\Roles\Models\Role;
use Illuminate\Database\Seeder;
use App\Roles\Models\Permission;
use Illuminate\Database\Eloquent\Model;
use Modules\CitizenService\Models\CitizenServiceRequest;
use Modules\CitizenService\Models\CitizenServiceRegister;
use Modules\CitizenService\Models\CitizenServiceCommunity;
use Modules\CitizenService\Models\CitizenServiceIndicator;
use Modules\CitizenService\Models\CitizenServiceProcedure;
use Modules\CitizenService\Models\CitizenServiceDepartment;
use Modules\CitizenService\Models\CitizenServiceEffectType;
use Modules\CitizenService\Models\CitizenServiceContactBook;
use Modules\CitizenService\Models\CitizenServiceRequestTeam;
use Modules\CitizenService\Models\CitizenServiceRequestType;
use Modules\CitizenService\Models\CitizenServiceProcedureType;
use Modules\CitizenService\Models\CitizenServiceTransactionType;
use Modules\CitizenService\Models\CitizenServiceServedInstitution;
use Modules\CitizenService\Models\CitizenServiceCommunityProfiling;

/**
 * @class CitizenServiceRoleAndPermissionsTableSeeder
 * @brief Inicializa los roles y permisos del módulo de atención al ciudadano
 *
 * @author Ing. Yennifer Ramirez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceRoleAndPermissionsTableSeeder extends Seeder
{
    protected $settingPermissions = [
        [
            'name' => 'Configuración del módulo de atención al ciudadano',
            'slug' => 'citizenservice.setting.index',
            'description' => 'Acceso a la configuración del módulo de atención al ciudadano',
            'model' => '', 'model_prefix' => 'OAC',
            'slug_alt' => 'configuracion.ver'
        ],
        /* transaction-types (Tipo de transacción) */
        [
            'name' => 'Crear tipo de transacción',
            'slug' => 'citizenservice.transaction.types.create',
            'description' => 'Acceso para crear un tipo de transacción',
            'model' => CitizenServiceTransactionType::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'tipo_de_transaccion.crear'
        ],
        [
            'name' => 'Editar tipo de transacción',
            'slug' => 'citizenservice.transaction.types.edit',
            'description' => 'Acceso para editar un tipo de transacción',
            'model' => CitizenServiceTransactionType::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'tipo_de_transaccion.editar'
        ],
        [
            'name' => 'Eliminar tipo de transacción',
            'slug' => 'citizenservice.transaction.types.delete',
            'description' => 'Acceso para eliminar un tipo de transacción',
            'model' => CitizenServiceTransactionType::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'tipo_de_transaccion.eliminar'
        ],
        /* Register (Cronograma) */
        [
            'name' => 'Ver gestión de cronograma de actividades',
            'slug' => 'citizenservice.registers.list',
            'description' => 'Acceso para ver cronograma de actividades',
            'model' => CitizenServiceRegister::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'cronograma.ver'
        ],
        [
            'name' => 'Crear cronograma de actividades',
            'slug' => 'citizenservice.registers.create',
            'description' => 'Acceso para crear cronograma de actividades',
            'model' => CitizenServiceRegister::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'cronograma.crear'
        ],
        [
            'name' => 'Editar cronograma de actividades',
            'slug' => 'citizenservice.registers.edit',
            'description' => 'Acceso para editar cronograma de actividades',
            'model' => CitizenServiceRegister::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'cronograma.editar'
        ],
        [
            'name' => 'Eliminar cronograma de actividades',
            'slug' => 'citizenservice.registers.delete',
            'description' => 'Acceso para eliminar cronograma de actividades',
            'model' => CitizenServiceRegister::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'cronograma.eliminar'
        ],
        /* departament (Departamento) */
        [
            'name' => 'Crear departamento',
            'slug' => 'citizenservice.departaments.create',
            'description' => 'Acceso para crear un departamento',
            'model' => CitizenServiceDepartment::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'departamento.crear'
        ],
        [
            'name' => 'Editar departamento',
            'slug' => 'citizenservice.departaments.edit',
            'description' => 'Acceso para editar un departamento',
            'model' => CitizenServiceDepartment::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'departamento.editar'
        ],
        [
            'name' => 'Eliminar departamento',
            'slug' => 'citizenservice.departaments.delete',
            'description' => 'Acceso para eliminar un departamento',
            'model' => CitizenServiceDepartment::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'departamento.eliminar'
        ],
        /* effect-types (Tipo de impacto) */
        [
            'name' => 'Crear tipo de impacto',
            'slug' => 'citizenservice.effect.types.create',
            'description' => 'Acceso para crear un tipo de impacto',
            'model' => CitizenServiceEffectType::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'tipo_de_impacto.crear'
        ],
        [
            'name' => 'Editar tipo de impacto',
            'slug' => 'citizenservice.effect.types.edit',
            'description' => 'Acceso para editar un tipo de impacto',
            'model' => CitizenServiceEffectType::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'tipo_de_impacto.editar'
        ],
        [
            'name' => 'Eliminar tipo de impacto',
            'slug' => 'citizenservice.effect.types.delete',
            'description' => 'Acceso para eliminar un tipo de impacto',
            'model' => CitizenServiceEffectType::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'tipo_de_impacto.eliminar'
        ],
        /* indicators (Indicador) */
        [
            'name' => 'Crear indicador',
            'slug' => 'citizenservice.indicators.create',
            'description' => 'Acceso para crear un indicador',
            'model' => CitizenServiceIndicator::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'indicador.crear'
        ],
        [
            'name' => 'Editar indicador',
            'slug' => 'citizenservice.indicators.edit',
            'description' => 'Acceso para editar un indicador',
            'model' => CitizenServiceIndicator::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'indicador.editar'
        ],
        [
            'name' => 'Eliminar indicador',
            'slug' => 'citizenservice.indicators.delete',
            'description' => 'Acceso para eliminar un indicador',
            'model' => CitizenServiceIndicator::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'indicador.eliminar'
        ],
        /* Dashboard */
        [
            'name'              => 'Vista principal del dashboard del módulo de OAC',
            'slug'              => 'citizenservice.dashboard',
            'description'       => 'Acceso para visualizar el dashboard del módulo',
            'model'             => '',
            'model_prefix'      => 'OAC',
            'slug_alt'          => 'panel_de_control.ver',
            'short_description' => 'Visualizar panel de control del módulo de OAC'
        ],
    ];

    protected $requestPermissions = [
        /* Request (Solicitudes) */
        [
            'name' => 'Ver gestión de atención al ciudadano',
            'slug' => 'citizenservice.requests.list',
            'description' => 'Acceso para ver solicitud',
            'model' => CitizenServiceRequest::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'solicitud.ver'
        ],
        [
            'name' => 'Crear solicitud',
            'slug' => 'citizenservice.requests.create',
            'description' => 'Acceso para crear solicitud',
            'model' => CitizenServiceRequest::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'solicitud.crear'
        ],
        [
            'name' => 'Editar solicitud',
            'slug' => 'citizenservice.requests.edit',
            'description' => 'Acceso para editar solicitud',
            'model' => CitizenServiceRequest::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'solicitud.editar'
        ],
        [
            'name' => 'Eliminar solicitud',
            'slug' => 'citizenservice.requests.delete',
            'description' => 'Acceso para eliminar solicitud',
            'model' => CitizenServiceRequest::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'solicitud.eliminar'
        ],
        [
            'name' => 'Aprobar solicitud',
            'slug' => 'citizenservice.requests.approved',
            'description' => 'Acceso para aprobar solicitud',
            'model' => CitizenServiceRequest::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'solicitud.aprobar'
        ],
        [
            'name' => 'Rechazar solicitud',
            'slug' => 'citizenservice.requests.rejected',
            'description' => 'Acceso para rechazar solicitud',
            'model' => CitizenServiceRequest::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'solicitud.rechazar'
        ],
        [
            'name' => 'Agregar indicador a la solicitud',
            'slug' => 'citizenservice.requests.addindicator',
            'description' => 'Acceso para agragar un indicador a la solicitud',
            'model' => CitizenServiceRequest::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'solicitud.indicador'
        ],
        [
            'name' => 'Ver información de la solicitud',
            'slug' => 'citizenservice.requests.info',
            'description' => 'Acceso para ver la información de la solicitud',
            'model' => CitizenServiceRequest::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'solicitud.ver-info'
        ],
        /* request-type (Tipo de solicitud) */
        [
            'name' => 'Crear tipo de solicitud',
            'slug' => 'citizenservice.request.types.create',
            'description' => 'Acceso para crear tipo de solicitud',
            'model' => CitizenServiceRequestType::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'tipo_de_solicitud.crear'
        ],
        [
            'name' => 'Editar tipo de solicitud',
            'slug' => 'citizenservice.request.types.edit',
            'description' => 'Acceso para editar tipo de solicitud',
            'model' => CitizenServiceRequestType::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'tipo_de_solicitud.editar'
        ],
        [
            'name' => 'Eliminar tipo de solicitud',
            'slug' => 'citizenservice.request.types.delete',
            'description' => 'Acceso para eliminar tipo de solicitud',
            'model' => CitizenServiceRequestType::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'tipo_de_solicitud.eliminar'
        ],
        /* request-close (Cierre de solicitud) */
        [
            'name' => 'Ver cierre de solicitudes',
            'slug' => 'citizenservice.requests.close.list',
            'description' => 'Acceso para ver cierre de solicitud',
            'model' => CitizenServiceRequest::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'solicitud.ver-cierre'
        ],
        [
            'name' => 'Cerrar solicitudes',
            'slug' => 'citizenservice.requests.close',
            'description' => 'Acceso para cerrar solicitud',
            'model' => CitizenServiceRequest::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'solicitud.cerrar'
        ],
        /** Permisos para la asignación de equipos a solicitudes de trámites */
        [
            'name' => 'Ver asignaciones de personal a solicitudes',
            'slug' => 'citizenservice.request.teams.list',
            'description' => 'Acceso para ver asignaciones de personal',
            'model' => CitizenServiceRequestTeam::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'solicitud.personal.ver'
        ],
        [
            'name' => 'Crear asignación de personal a solicitudes',
            'slug' => 'citizenservice.request.teams.create',
            'description' => 'Acceso para crear asignación de personal',
            'model' => CitizenServiceRequestTeam::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'solicitud.personal.crear'
        ],
        [
            'name' => 'Editar asignación de personal a solicitudes',
            'slug' => 'citizenservice.request.teams.edit',
            'description' => 'Acceso para editar asignación de personal',
            'model' => CitizenServiceRequestTeam::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'solicitud.personal.editar'
        ],
        [
            'name' => 'Eliminar asignación de personal a solicitudes',
            'slug' => 'citizenservice.request.teams.delete',
            'description' => 'Acceso para eliminar asignación de personal',
            'model' => CitizenServiceRequestTeam::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'solicitud.personal.eliminar'
        ],
    ];

    protected $reportPermissions = [
        /* Report (Reportes)*/
        [
            'name' => 'Crear reporte de atención al ciudadano',
            'slug' => 'citizenservice.report.create',
            'description' => 'Acceso para crear reportes de atención al ciudadano',
            'model' => '',
            'model_prefix' => 'OAC',
            'slug_alt' => 'reporte.crear',
            'short_description' => 'generar reporte de atención al ciudadano'
        ],
        [
            'name' => 'Ver reporte de atención al ciudadano',
            'slug' => 'citizenservice.report.list',
            'description' => 'Acceso para ver reportes de atención al ciudadano',
            'model' => '', 'model_prefix' => 'OAC',
            'slug_alt' => 'reporte.ver',
            'short_description' => 'generar reporte de atención al ciudadano'
        ]
    ];

    protected $procedurePermissions = [
        /* Permisos para la gestión de tipos de trámites */
        [
            'name' => 'Crear tipo de trámite',
            'slug' => 'citizenservice.procedure.type.create',
            'description' => 'Acceso para crear tipo de trámite',
            'model' => CitizenServiceProcedureType::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'tipo.tramite.crear',
            'short_description' => 'agregar tipo de trámite',
        ],
        [
            'name' => 'Editar tipo de trámite',
            'slug' => 'citizenservice.procedure.type.edit',
            'description' => 'Acceso para editar tipo de trámite',
            'model' => CitizenServiceProcedureType::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'tipo.tramite.editar',
            'short_description' => 'editar tipo de trámite',
        ],
        [
            'name' => 'Eliminar tipo de trámite',
            'slug' => 'citizenservice.procedure.type.delete',
            'description' => 'Acceso para eliminar tipo de trámite',
            'model' => CitizenServiceProcedureType::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'tipo.tramite.eliminar',
            'short_description' => 'eliminar tipo de trámite',
        ],
        [
            'name' => 'Ver tipos de trámites',
            'slug' => 'citizenservice.procedure.type.list',
            'description' => 'Acceso para ver tipos de trámites',
            'model' => CitizenServiceProcedureType::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'tipo.tramite.ver',
            'short_description' => 'ver tipo de trámites',
        ],
        /* Permisos para la gestión de trámites */
        [
            'name' => 'Crear trámite',
            'slug' => 'citizenservice.procedure.create',
            'description' => 'Acceso para crear trámite',
            'model' => CitizenServiceProcedure::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'tramite.crear',
            'short_description' => 'agregar trámite',
        ],
        [
            'name' => 'Editar trámite',
            'slug' => 'citizenservice.procedure.edit',
            'description' => 'Acceso para editar trámite',
            'model' => CitizenServiceProcedure::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'tramite.editar',
            'short_description' => 'editar trámite',
        ],
        [
            'name' => 'Eliminar trámite',
            'slug' => 'citizenservice.procedure.delete',
            'description' => 'Acceso para eliminar trámite',
            'model' => CitizenServiceProcedure::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'tramite.eliminar',
            'short_description' => 'eliminar trámite',
        ],
        [
            'name' => 'Ver trámites',
            'slug' => 'citizenservice.procedure.list',
            'description' => 'Acceso para ver trámites',
            'model' => CitizenServiceProcedure::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'tramite.ver',
            'short_description' => 'ver trámites',
        ],
    ];

    protected $communityPermissions = [
        /* Permisos para la gestión de comunidades */
        [
            'name' => 'Crear comunidad',
            'slug' => 'citizenservice.community.create',
            'description' => 'Acceso para crear comunidad',
            'model' => CitizenServiceCommunity::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'comunidad.crear',
            'short_description' => 'agregar comunidad',
        ],
        [
            'name' => 'Editar comunidad',
            'slug' => 'citizenservice.community.edit',
            'description' => 'Acceso para editar comunidad',
            'model' => CitizenServiceCommunity::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'comunidad.editar',
            'short_description' => 'editar comunidad',
        ],
        [
            'name' => 'Eliminar comunidad',
            'slug' => 'citizenservice.community.delete',
            'description' => 'Acceso para eliminar comunidad',
            'model' => CitizenServiceCommunity::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'comunidad.eliminar',
            'short_description' => 'eliminar comunidad',
        ],
        [
            'name' => 'Ver comunidades',
            'slug' => 'citizenservice.community.list',
            'description' => 'Acceso para ver comunidades',
            'model' => CitizenServiceCommunity::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'comunidad.ver',
            'short_description' => 'ver comunidades',
        ],
    ];

    protected $institutionPermissions = [
        /* Permisos para la gestión de instituciones atendidas */
        [
            'name' => 'Crear institución',
            'slug' => 'citizenservice.institution.create',
            'description' => 'Acceso para crear institución',
            'model' => CitizenServiceServedInstitution::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'institucion.crear',
            'short_description' => 'agregar institución',
        ],
        [
            'name' => 'Editar institución',
            'slug' => 'citizenservice.institution.edit',
            'description' => 'Acceso para editar institución',
            'model' => CitizenServiceServedInstitution::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'institucion.editar',
            'short_description' => 'editar institución',
        ],
        [
            'name' => 'Eliminar institución',
            'slug' => 'citizenservice.institution.delete',
            'description' => 'Acceso para eliminar institución',
            'model' => CitizenServiceServedInstitution::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'institucion.eliminar',
            'short_description' => 'eliminar institución',
        ],
        [
            'name' => 'Ver instituciones',
            'slug' => 'citizenservice.institution.list',
            'description' => 'Acceso para ver instituciones',
            'model' => CitizenServiceServedInstitution::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'institucion.ver',
            'short_description' => 'ver instituciones',
        ],
    ];

    protected $contactBookPermissions = [
        /* Permisos para la gestión de comunidades */
        [
            'name' => 'Crear contacto',
            'slug' => 'citizenservice.contact.book.create',
            'description' => 'Acceso para crear contacto',
            'model' => CitizenServiceContactBook::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'agenda_contacto.crear',
            'short_description' => 'agregar contacto',
        ],
        [
            'name' => 'Editar contacto',
            'slug' => 'citizenservice.contact.book.edit',
            'description' => 'Acceso para editar contacto',
            'model' => CitizenServiceContactBook::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'agenda_contacto.editar',
            'short_description' => 'editar contacto',
        ],
        [
            'name' => 'Eliminar contacto',
            'slug' => 'citizenservice.contact.book.delete',
            'description' => 'Acceso para eliminar contacto',
            'model' => CitizenServiceContactBook::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'agenda_contacto.eliminar',
            'short_description' => 'eliminar contacto',
        ],
        [
            'name' => 'Ver contactos',
            'slug' => 'citizenservice.contact.book.list',
            'description' => 'Acceso para ver contactos',
            'model' => CitizenServiceContactBook::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'agenda_contacto.ver',
            'short_description' => 'ver contactos',
        ],
    ];

    protected $communityProfilingPermissions = [
        /* Permisos para la gestión de caracterización de comunidades */
        [
            'name' => 'Crear caracterización de comunidades',
            'slug' => 'citizenservice.community.profiling.create',
            'description' => 'Acceso para crear caracterización de comunidades',
            'model' => CitizenServiceCommunityProfiling::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'caracterizacion_comunidad.crear',
            'short_description' => 'agregar caracterización de comunidades',
        ],
        [
            'name' => 'Editar caracterización de comunidades',
            'slug' => 'citizenservice.community.profiling.edit',
            'description' => 'Acceso para editar caracterización de comunidades',
            'model' => CitizenServiceCommunityProfiling::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'caracterizacion_comunidad.editar',
            'short_description' => 'editar caracterización de comunidades',
        ],
        [
            'name' => 'Eliminar caracterización de comunidades',
            'slug' => 'citizenservice.community.profiling.delete',
            'description' => 'Acceso para eliminar caracterización de comunidades',
            'model' => CitizenServiceCommunityProfiling::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'caracterizacion_comunidad.eliminar',
            'short_description' => 'eliminar caracterización de comunidades',
        ],
        [
            'name' => 'Ver caracterización de comunidades',
            'slug' => 'citizenservice.community.profiling.list',
            'description' => 'Acceso para ver caracterización de comunidades',
            'model' => CitizenServiceCommunityProfiling::class,
            'model_prefix' => 'OAC',
            'slug_alt' => 'caracterizacion_comunidad.ver',
            'short_description' => 'ver caracterización de comunidades',
        ],
    ];

    /**
     * Ejecuta los seeds de la base de datos
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $adminRole = Role::where('slug', 'admin')->first();

        $citizenServiceRole = Role::updateOrCreate(
            ['slug' => 'citizenservice'],
            ['name' => 'OAC', 'description' => 'Coordinador de atención al ciudadano']
        );

        $permissions = [
            ...$this->settingPermissions,
            ...$this->requestPermissions,
            ...$this->reportPermissions,
            ...$this->procedurePermissions,
            ...$this->communityPermissions,
            ...$this->institutionPermissions,
            ...$this->contactBookPermissions,
            ...$this->communityProfilingPermissions
        ];

        $citizenServiceRole->detachAllPermissions();

        foreach ($permissions as $permission) {
            $per = Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                [
                    'name' => $permission['name'], 'description' => $permission['description'],
                    'model' => $permission['model'], 'model_prefix' => $permission['model_prefix'],
                    'slug_alt' => $permission['slug_alt']
                ]
            );

            $citizenServiceRole->attachPermission($per);

            if ($adminRole) {
                $adminRole->attachPermission($per);
            }
        }
    }
}
