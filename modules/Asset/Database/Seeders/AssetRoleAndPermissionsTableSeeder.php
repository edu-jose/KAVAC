<?php

namespace Modules\Asset\Database\Seeders;

use App\Roles\Models\Permission;
use App\Roles\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Nwidart\Modules\Facades\Module;

/**
 * @class AssetRoleAndPermissionsTableSeeder
 * @brief Inicializa los roles y permisos del módulo de bienes
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetRoleAndPermissionsTableSeeder extends Seeder
{
    /**
     * Método que registra los valores iniciales de los roles y permisos del módulo
     *
     * @author  Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $adminRole = Role::where('slug', 'admin')->first();
        $accountingRole = Role::where('slug', 'accounting')->first();

        $assetRole = Role::updateOrCreate(
            ['slug' => 'asset'],
            ['name' => 'Bienes', 'description' => 'Coordinador de bienes']
        );

        $permissions = [
            /* Panel de Control */
            [
                'name' => 'Acceso al panel de control de bienes',
                'slug' => 'asset.dashboard',
                'description' => 'Acceso al panel de control del módulo de bienes',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'panel.control.ver', 'short_description' => 'panel de control de bienes',
            ],
            /* Configuración General de Bienes */
            [
                'name' => 'Configuración General del módulo de bienes',
                'slug' => 'asset.setting',
                'description' => 'Acceso a la configuración general del módulo de bienes',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'configuracion.ver', 'short_description' => 'configuración general de bienes',
            ],
            /* Configuración de Tipos de Bienes */
            [
                'name' => 'Configuración de los tipos de bienes',
                'slug' => 'asset.setting.type',
                'description' => 'Acceso a la configuración de los tipos de bienes',
                'model' => 'Modules\Asset\Models\AssetType', 'model_prefix' => 'bienes',
                'slug_alt' => 'configuracion.bienes.tipo', 'short_description' => 'configuración de los tipos de bienes',
            ],
            /* Configuración de las Categorías Generales de Bienes */
            [
                'name' => 'Configuración de las Categorías Generales de bienes',
                'slug' => 'asset.setting.category',
                'description' => 'Acceso a la configuración de las categorías de bienes',
                'model' => 'Modules\Asset\Models\AssetCategory', 'model_prefix' => 'bienes',
                'slug_alt' => 'configuracion.bienes.categoria',
                'short_description' => 'configuración de las categorías de bienes',
            ],
            /* Configuración de las Subcategorías de Bienes */
            [
                'name' => 'Configuración de las Subcategorías de bienes',
                'slug' => 'asset.setting.subcategory',
                'description' => 'Acceso a la configuración de las subcategorías de bienes',
                'model' => 'Modules\Asset\Models\AssetSubcategory', 'model_prefix' => 'bienes',
                'slug_alt' => 'configuracion.bienes.subcategoria',
                'short_description' => 'configuración de las subcategorías de bienes',
            ],
            /* Configuración de las Categorías Específicas de Bienes */
            [
                'name' => 'Configuración de las categorías específicas de bienes',
                'slug' => 'asset.setting.specific',
                'description' => 'Acceso a la configuración de las categorías específicas de bienes',
                'model' => 'Modules\Asset\Models\AssetSpecificCategory', 'model_prefix' => 'bienes',
                'slug_alt' => 'configuracion.bienes.categoria.especifica',
                'short_description' => 'configuración de las categorías específicas de bienes',
            ],
            /* Configuración de las Edificaciones */
            [
                'name' => 'Crear registro de  edificaciones',
                'slug' => 'asset.setting.building.create',
                'description' => 'Acceso al registro de edificaciones',
                'model' => 'Modules\Asset\Models\Asset', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.building.crear', 'short_description' => 'agregar bienes',
            ],
            [
                'name' => 'Modificar registro de edificaciones',
                'slug' => 'asset.setting.building.edit',
                'description' => 'Acceso para editar edificaciones',
                'model' => 'Modules\Asset\Models\Asset', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.building.editar', 'short_description' => 'editar bienes',
            ],
            [
                'name' => 'Eliminar registro de edificaciones',
                'slug' => 'asset.setting.building.delete',
                'description' => 'Acceso para eliminar edificaciones',
                'model' => 'Modules\Asset\Models\Asset', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.building.eliminar', 'short_description' => 'eliminar bienes',
            ],
            /* Configuración de los niveles */
            [
                'name' => 'Crear registro de  niveles',
                'slug' => 'asset.setting.floor.create',
                'description' => 'Acceso al registro de niveles',
                'model' => 'Modules\Asset\Models\Asset', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.floor.crear', 'short_description' => 'agregar bienes',
            ],
            [
                'name' => 'Modificar registro de niveles',
                'slug' => 'asset.setting.floor.edit',
                'description' => 'Acceso para editar niveles',
                'model' => 'Modules\Asset\Models\Asset', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.floor.editar', 'short_description' => 'editar bienes',
            ],
            [
                'name' => 'Eliminar registro de niveles',
                'slug' => 'asset.setting.floor.delete',
                'description' => 'Acceso para eliminar niveles',
                'model' => 'Modules\Asset\Models\Asset', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.floor.eliminar', 'short_description' => 'eliminar bienes',
            ],
            /* Configuración de las secciones */
            [
                'name' => 'Crear registro de  secciones',
                'slug' => 'asset.setting.section.create',
                'description' => 'Acceso al registro de secciones',
                'model' => 'Modules\Asset\Models\Asset', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.section.crear', 'short_description' => 'agregar bienes',
            ],
            [
                'name' => 'Modificar registro de secciones',
                'slug' => 'asset.setting.section.edit',
                'description' => 'Acceso para editar secciones',
                'model' => 'Modules\Asset\Models\Asset', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.section.editar', 'short_description' => 'editar bienes',
            ],
            [
                'name' => 'Eliminar registro de secciones',
                'slug' => 'asset.setting.section.delete',
                'description' => 'Acceso para eliminar secciones',
                'model' => 'Modules\Asset\Models\Asset', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.section.eliminar', 'short_description' => 'eliminar bienes',
            ],
            /* Configuración de condicion fisica*/
            [
                'name' => 'Crear registro de  condición física',
                'slug' => 'asset.condition.create',
                'description' => 'Acceso al registro de condición física',
                'model' => 'Modules\Asset\Models\Asset', 'model_prefix' => 'bienes',
                'slug_alt' => 'condition.crear', 'short_description' => 'agregar condición física',
            ],
            [
                'name' => 'Modificar registro de condición física',
                'slug' => 'asset.condition.edit',
                'description' => 'Acceso para editar condición física',
                'model' => 'Modules\Asset\Models\Asset', 'model_prefix' => 'bienes',
                'slug_alt' => 'condition.editar', 'short_description' => 'editar condición física',
            ],
            [
                'name' => 'Eliminar registro de condición física',
                'slug' => 'asset.condition.delete',
                'description' => 'Acceso para eliminar condición física',
                'model' => 'Modules\Asset\Models\Asset', 'model_prefix' => 'bienes',
                'slug_alt' => 'condition.eliminar', 'short_description' => 'eliminar condición física',
            ],
            /* Configuración de  Método de Depreciación*/
            [
                'name' => 'Crear registro de Método de Depreciación',
                'slug' => 'asset.depreciation.method.create',
                'description' => 'Acceso al registro de  Método de Depreciación',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'depreciation.method.crear', 'short_description' => 'agregar  Método de Depreciación',
            ],
            [
                'name' => 'Modificar registro de  Método de Depreciación',
                'slug' => 'asset.depreciation.method.edit',
                'description' => 'Acceso para editar  Método de Depreciación',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'depreciation.method.editar', 'short_description' => 'editar  Método de Depreciación',
            ],
            [
                'name' => 'Eliminar registro de Método de Depreciación',
                'slug' => 'asset.depreciation.method.delete',
                'description' => 'Acceso para eliminar  Método de Depreciación',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'depreciation.method.eliminar', 'short_description' => 'eliminar  Método de Depreciación',
            ],
            /* Configuración de Ajustes de bienes*/
            [
                'name' => 'Configuración de Ajustes de bienes',
                'slug' => 'asset.adjustment.index',
                'description' => 'Acceso a la configuración de Ajustes de bienes',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'configuracion.bienes.adjustment',
                'short_description' => 'configuración de Ajustes de bienes',
            ],
            [
                'name' => 'Crear registro de  Ajustes de bienes',
                'slug' => 'asset.adjustment.create',
                'description' => 'Acceso al registro de Ajustes de bienes',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'adjustment.crear', 'short_description' => 'agregar Ajustes de bienes',
            ],
            [
                'name' => 'Modificar registro de Ajustes de bienes',
                'slug' => 'asset.adjustment.edit',
                'description' => 'Acceso para editar Ajustes de bienes',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'adjustment.editar', 'short_description' => 'editar Ajustes de bienes',
            ],
            [
                'name' => 'Eliminar registro de Ajustes de bienes',
                'slug' => 'asset.adjustment.delete',
                'description' => 'Acceso para eliminar Ajustes de bienes',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'adjustment.eliminar', 'short_description' => 'eliminar Ajustes de bienes',
            ],
            /* Configuración de  Tipos de Adquisición*/
            [
                'name' => 'Crear registro de Tipos de Adquisición',
                'slug' => 'asset.acquisition.type.create',
                'description' => 'Acceso al registro de  Tipos de Adquisición',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'acquisition.type.crear', 'short_description' => 'agregar  Tipos de Adquisición',
            ],
            [
                'name' => 'Modificar registro de  Tipos de Adquisición',
                'slug' => 'asset.acquisition.type.edit',
                'description' => 'Acceso para editar  Tipos de Adquisición',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'acquisition.type.editar', 'short_description' => 'editar  Tipos de Adquisición',
            ],
            [
                'name' => 'Eliminar registro de  Tipos de Adquisición',
                'slug' => 'asset.acquisition.type.delete',
                'description' => 'Acceso para eliminar  Tipos de Adquisición',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'acquisition.type.eliminar', 'short_description' => 'eliminar  Tipos de Adquisición',
            ],
            /* Configuración de Funciones de uso*/
            [
                'name' => 'Crear registro de  Funciones de uso',
                'slug' => 'asset.usefunction.create',
                'description' => 'Acceso al registro de Funciones de uso',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'usefunction.crear', 'short_description' => 'agregar Funciones de uso',
            ],
            [
                'name' => 'Modificar registro de Funciones de uso',
                'slug' => 'asset.usefunction.edit',
                'description' => 'Acceso para editar Funciones de uso',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'usefunction.editar', 'short_description' => 'editar Funciones de uso',
            ],
            [
                'name' => 'Eliminar registro de Funciones de uso',
                'slug' => 'asset.usefunction.delete',
                'description' => 'Acceso para eliminar Funciones de uso',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'usefunction.eliminar', 'short_description' => 'eliminar Funciones de uso',
            ],
            /* Configuración de Estatus de Uso*/
            [
                'name' => 'Crear registro de  estatus de Uso',
                'slug' => 'asset.status.create',
                'description' => 'Acceso al registro de estatus de Uso',
                'model' => 'Modules\Asset\Models\Asset', 'model_prefix' => 'bienes',
                'slug_alt' => 'status.crear', 'short_description' => 'agregar estatus de Uso',
            ],
            [
                'name' => 'Modificar registro de estatus de Uso',
                'slug' => 'asset.status.edit',
                'description' => 'Acceso para editar estatus de Uso',
                'model' => 'Modules\Asset\Models\Asset', 'model_prefix' => 'bienes',
                'slug_alt' => 'status.editar', 'short_description' => 'editar estatus de Uso',
            ],
            [
                'name' => 'Eliminar registro de estatus de Uso',
                'slug' => 'asset.status.delete',
                'description' => 'Acceso para eliminar estatus de Uso',
                'model' => 'Modules\Asset\Models\Asset', 'model_prefix' => 'bienes',
                'slug_alt' => 'status.eliminar', 'short_description' => 'eliminar estatus de Uso',
            ],
            /* Ingreso de Bienes */
            [
                'name' => 'Visualizar registro de bienes',
                'slug' => 'asset.list',
                'description' => 'Acceso a descripción del módulo de bienes',
                'model' => 'Modules\Asset\Models\Asset', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.ver', 'short_description' => 'ver bienes',
            ],
            [
                'name' => 'Crear registro de  bienes',
                'slug' => 'asset.create',
                'description' => 'Acceso al registro de bienes',
                'model' => 'Modules\Asset\Models\Asset', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.crear', 'short_description' => 'agregar bienes',
            ],
            [
                'name' => 'Modificar registro de bienes',
                'slug' => 'asset.edit',
                'description' => 'Acceso para editar bienes',
                'model' => 'Modules\Asset\Models\Asset', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.editar', 'short_description' => 'editar bienes',
            ],
            [
                'name' => 'Eliminar registro de bienes',
                'slug' => 'asset.delete',
                'description' => 'Acceso para eliminar bienes',
                'model' => 'Modules\Asset\Models\Asset', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.eliminar', 'short_description' => 'eliminar bienes',
            ],
            [
                'name' => 'Importar registro',
                'slug' => 'asset.import',
                'description' => 'Acceso para importar registro',
                'model' => 'Modules\Asset\Models\Asset', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.importar', 'short_description' => 'importar registro',
            ],
            [
                'name' => 'Exportar registro',
                'slug' => 'asset.export',
                'description' => 'Acceso para exportar registro',
                'model' => 'Modules\Asset\Models\Asset', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.exportar', 'short_description' => 'exportar registro',
            ],
            /* Asignación de Bienes */
            [
                'name' => 'Visualizar registro de asignación de bienes',
                'slug' => 'asset.asignation.list',
                'description' => 'Acceso para ver las asignaciones de bienes',
                'model' => 'Modules\Asset\Models\AssetAsignation', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.asignacion.ver', 'short_description' => 'ver asignación de bienes',
            ],
            [
                'name' => 'Crear registro de  asignación de bienes',
                'slug' => 'asset.asignation.create',
                'description' => 'Acceso para crear asignación de bienes',
                'model' => 'Modules\Asset\Models\AssetAsignation', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.asignacion.crear', 'short_description' => 'agregar asignacion de bienes',
            ],
            [
                'name' => 'Modificar registro de asignación de bienes',
                'slug' => 'asset.asignation.edit',
                'description' => 'Acceso para editar asignación de bienes',
                'model' => 'Modules\Asset\Models\AssetAsignation', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.asignacion.editar', 'short_description' => 'editar asignación de bienes',
            ],
            [
                'name' => 'Eliminar registro de asignación de bienes',
                'slug' => 'asset.asignation.delete',
                'description' => 'Acceso para eliminar asignación de bienes',
                'model' => 'Modules\Asset\Models\AssetAsignation', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.asignacion.eliminar', 'short_description' => 'eliminar asignación de bienes',
            ],
            [
                'name' => 'Imprimir acta de asignación de bienes',
                'slug' => 'asset.download',
                'description' => 'Acceso para imprimir acta de asignación de bienes',
                'model' => 'Modules\Asset\Models\AssetAsignation', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.imprimir.actas', 'short_description' => 'Imprimir acta de asignación de bienes',
            ],
            [
                'name' => 'Aprobar y rechazar asignación de bienes',
                'slug' => 'asset.asignation.approvereject',
                'description' => 'Acceso para aprobar y rechazar asignación de bienes',
                'model' => 'Modules\Asset\Models\AssetAsignationDelivery', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.asignacion.aprobar_rechazar', 'short_description' => 'aprobar y rechazar asignación de bienes',
            ],
            /* Desincorporación de Bienes */
            [
                'name' => 'Visualizar registro de desincorporación de bienes',
                'slug' => 'asset.disincorporation.list',
                'description' => 'Acceso para ver las desincorporaciones de bienes',
                'model' => 'Modules\Asset\Models\AssetDisincorporation', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.desincorporacion.ver', 'short_description' => 'ver desincorporación de bienes',
            ],
            [
                'name' => 'Crear registro de  desincorporación de bienes',
                'slug' => 'asset.disincorporation.create',
                'description' => 'Acceso para crear desincorporación de bienes',
                'model' => 'Modules\Asset\Models\AssetDisincorporation', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.desincorporacion.crear',
                'short_description' => 'agregar desincorporación de bienes',
            ],
            [
                'name' => 'Modificar registro de desincorporación de bienes',
                'slug' => 'asset.disincorporation.edit',
                'description' => 'Acceso para editar desincorporación de bienes',
                'model' => 'Modules\Asset\Models\AssetDisincorporation', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.desincorporacion.editar',
                'short_description' => 'editar desincorporación de bienes',
            ],
            [
                'name' => 'Eliminar registro de desincorporación de bienes',
                'slug' => 'asset.disincorporation.delete',
                'description' => 'Acceso para eliminar desincorporación de bienes',
                'model' => 'Modules\Asset\Models\AssetDisincorporation', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.desincorporacion.eliminar',
                'short_description' => 'eliminar desincorporación de bienes',
            ],
            [
                'name' => 'Imprimir acta de desincorporación de bienes',
                'slug' => 'asset.desincorporation.download',
                'description' => 'Acceso para imprimir acta de desincorporación de bienes',
                'model' => 'Modules\Asset\Models\AssetDisincorporation', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.desincorporacion.actas', 'short_description' => 'Imprimir acta de desincorporación de bienes',
            ],
            /* Registro de Bienes */
            [
                'name' => 'Visualizar registro de listado de bienes',
                'slug' => 'asset.request.register',
                'description' => 'Acceso para ver los bienes registrados',
                'model' => 'Modules\Asset\Models\AssetRequest', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.registro.ver', 'short_description' => 'ver registro de bienes',
            ],
            /* Solicitudes de Bienes */
            [
                'name' => 'Visualizar registro de solicitud de bienes',
                'slug' => 'asset.request.list',
                'description' => 'Acceso para ver las solicitudes de bienes',
                'model' => 'Modules\Asset\Models\AssetRequest', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.solicitud.ver', 'short_description' => 'ver solicitud de bienes',
            ],
            [
                'name' => 'Crear registro de  solicitud de bienes',
                'slug' => 'asset.request.create',
                'description' => 'Acceso para crear solicitud de bienes',
                'model' => 'Modules\Asset\Models\AssetRequest', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.solicitud.crear', 'short_description' => 'agregar solicitud de bienes',
            ],
            [
                'name' => 'Modificar registro de solicitud de bienes',
                'slug' => 'asset.request.edit',
                'description' => 'Acceso para editar solicitud de bienes',
                'model' => 'Modules\Asset\Models\AssetRequest', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.solicitud.editar', 'short_description' => 'editar solicitud de bienes',
            ],
            [
                'name' => 'Eliminar registro de solicitud de bienes',
                'slug' => 'asset.request.delete',
                'description' => 'Acceso para eliminar solicitud de bienes',
                'model' => 'Modules\Asset\Models\AssetRequest', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.solicitud.eliminar', 'short_description' => 'eliminar solicitud de bienes',
            ],
            [
                'name' => 'Aprobar solicitud de bienes',
                'slug' => 'asset.request.approve',
                'description' => 'Acceso para aprobar solicitud de bienes',
                'model' => 'Modules\Asset\Models\AssetRequest', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.solicitud.aprobar', 'short_description' => 'aprobar solicitud de bienes',
            ],
            [
                'name' => 'Rechazar solicitud de bienes',
                'slug' => 'asset.request.reject',
                'description' => 'Acceso para rechazar solicitud de bienes',
                'model' => 'Modules\Asset\Models\AssetRequest', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.solicitud.rechazar', 'short_description' => 'rechazar solicitud de bienes',
            ],
            [
                'name' => 'Crear registro de  solicitud de prórroga',
                'slug' => 'asset.request.extension',
                'description' => 'Acceso para crear solicitud de prórroga',
                'model' => 'Modules\Asset\Models\AssetRequest', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.solicitud.prorroga', 'short_description' => 'crear solicitud de prórroga',
            ],
            [
                'name' => 'Aprobar solicitud de prórroga',
                'slug' => 'asset.request.extension.approved',
                'description' => 'Acceso para aprobar solicitud de prórroga',
                'model' => 'Modules\Asset\Models\AssetRequest', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.solicitud.prorroga.aprobar', 'short_description' => 'aprobar solicitud de prórroga',
            ],
            [
                'name' => 'Rechazar solicitud de prórroga',
                'slug' => 'asset.request.extension.rejected',
                'description' => 'Acceso para rechazar solicitud de prórroga',
                'model' => 'Modules\Asset\Models\AssetRequest', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.solicitud.prorroga.rechazar', 'short_description' => 'Rechazar solicitud de prórroga',
            ],
            [
                'name' => 'Entregar equipos prestados',
                'slug' => 'asset.request.deliver',
                'description' => 'Acceso para entrega de equipos prestados',
                'model' => 'Modules\Asset\Models\AssetRequestDelivery', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.solicitud.entrega.equipo.prestado', 'short_description' => 'Entregar equipos prestados',
            ],
            [
                'name' => 'Aprobar y rechazar entrega de equipos prestados',
                'slug' => 'asset.request.delivery.approvereject',
                'description' => 'Acceso para aprobar y rechazar entrega de equipos prestados',
                'model' => 'Modules\Asset\Models\AssetRequestDelivery', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.solicitud.entrega.aprobar_rechazar', 'short_description' => 'aprobar y rechazar entrega de equipos prestados',
            ],
            [
                'name' => 'Eliminar registro de entrega de equipos prestados',
                'slug' => 'asset.request.delivery.delete',
                'description' => 'Acceso para eliminar entrega de equipos prestados',
                'model' => 'Modules\Asset\Models\AssetRequestDelivery', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.solicitud.entrega.eliminar', 'short_description' => 'eliminar entrega de equipos prestados',
            ],
            [
                'name' => 'Registrar evento',
                'slug' => 'asset.request.event.create',
                'description' => 'Acceso para registrar evento',
                'model' => 'Modules\Asset\Models\AssetRequestEvent', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.solicitud.evento.crear', 'short_description' => 'registrar evento',
            ],
            [
                'name' => 'Eliminar registro de evento',
                'slug' => 'asset.request.event.delete',
                'description' => 'Acceso para eliminar evento',
                'model' => 'Modules\Asset\Models\AssetRequestEvent', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.solicitud.evento.eliminar', 'short_description' => 'eliminar evento',
            ],
            /* inventario de bienes */
            [
                'name' => 'Vista inventario de bienes',
                'slug' => 'asset.inventory.history.index',
                'description' => 'Acceso a la vista de inventario de bienes',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.inventory-history.view', 'short_description' => 'vista inventario de bienes',
            ],
            [
                'name' => 'Crear registro de inventario de bienes',
                'slug' => 'asset.inventory.history.create',
                'description' => 'Acceso para crear inventario de bienes',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.inventory-history.crear', 'short_description' => 'Crear inventario de bienes',
            ],
            [
                'name' => 'Modificar inventario de bienes',
                'slug' => 'asset.inventory.history.edit',
                'description' => 'Acceso para modificar inventario de bienes',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.inventory-history.modificar', 'short_description' => 'Modificar inventario de bienes',
            ],
            [
                'name' => 'Eliminar registro de inventario de bienes',
                'slug' => 'asset.inventory.history.delete',
                'description' => 'Acceso para eliminar inventario de bienes',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.inventory-history.eliminar', 'short_description' => 'Eliminar inventario de bienes',
            ],
            /* disincorporations */
            [
                'name' => 'Vista de desincorporación de Bienes',
                'slug' => 'asset.disincorporation.index',
                'description' => 'Acceso a la vista de desincorporación de Bienes',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.disincorporation.index', 'short_description' => 'vista Desincorporación de Bienes',
            ],
            [
                'name' => 'Crear registro de  una desincorporación de Bienes',
                'slug' => 'asset.disincorporation.create',
                'description' => 'crear una desincorporación de Bienes',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.disincorporation.create', 'short_description' => 'crear una Desincorporación de Bienes',
            ],
            /* Reportes de Bienes */
            [
                'name' => 'Vista de reporte de bienes',
                'slug' => 'asset.report.view',
                'description' => 'Acceso a la vista de reportes de bienes',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.reporte.view', 'short_description' => 'vista reporte de bienes',
            ],
            [
                'name' => 'Generar reporte de bienes',
                'slug' => 'asset.report.create',
                'description' => 'Acceso para crear reportes de bienes',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.reporte.crear', 'short_description' => 'generar reporte de bienes',
            ],
            [
                'name' => 'Imprimir reporte de bienes',
                'slug' => 'asset.report.print',
                'description' => 'Acceso para imprimir reportes de bienes',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.reporte.imprimir', 'short_description' => 'imprimir reporte de bienes',
            ],
            [
                'name' => 'Descargar reporte de bienes',
                'slug' => 'asset.report.download',
                'description' => 'Acceso para descargar reportes de bienes',
                'model' => '', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.reporte.descargar', 'short_description' => 'descargar reporte de bienes',
            ],
            /* Entrega de bienes */
            [
                'name' => 'Vista de equipos asignados',
                'slug' => 'asset.asignations.view',
                'description' => 'Acceso a la vista de equipos asignados',
                'model' => 'Modules\Asset\Models\AssetAsignation', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.asignacion.equipos', 'short_description' => 'vista de equipos asignados',
            ],
            [
                'name' => 'Vista de creación de equipos asignados',
                'slug' => 'asset.asignations.create',
                'description' => 'Acceso a la vista de creación de  equipos asignados',
                'model' => 'Modules\Asset\Models\AssetAsignation', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.asignacion.creacion', 'short_description' => 'vista de creacion de equipos asignados',
            ],
            [
                'name' => 'Entregar equipos asignados',
                'slug' => 'asset.deliver',
                'description' => 'Acceso para entregar equipos asignados',
                'model' => 'Modules\Asset\Models\AssetAsignation', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.entregar.equipos', 'short_description' => 'Entregar equipos asignados',
            ],
            /* Depósito de bienes */
            [
                'name' => 'Crear registro de depósitos de bienes',
                'slug' => 'asset.setting.storage.create',
                'description' => 'Acceso para crear depósitos de bienes',
                'model' => 'Modules\Asset\Models\AssetStorage', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.depósitos.crear', 'short_description' => 'Crear depósitos',
            ],
            [
                'name' => 'Modificar depósitos de bienes',
                'slug' => 'asset.setting.storage.edit',
                'description' => 'Acceso para modificar depósitos de bienes',
                'model' => 'Modules\Asset\Models\AssetStorage', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.depósitos.modificar', 'short_description' => 'Modificar depósitos',
            ],
            [
                'name' => 'Eliminar registro de depósitos de bienes',
                'slug' => 'asset.setting.storage.delete',
                'description' => 'Acceso para eliminar depósitos de bienes',
                'model' => 'Modules\Asset\Models\AssetStorage', 'model_prefix' => 'bienes',
                'slug_alt' => 'bienes.depósitos.eliminar', 'short_description' => 'Eliminar depósitos',
            ],
        ];

        $depreciationPermissions = [
            /* Depreciación */
            [
                'name'              => 'Visualizar registro de depreciación de bienes',
                'slug'              => 'asset.depreciation.list',
                'description'       => 'Acceso para ver depreciación de bienes',
                'model'             => 'Modules\Asset\Models\AssetDepreciation',
                'model_prefix'      => 'contabilidad',
                'slug_alt'          => 'bienes.depreciacion.ver',
                'short_description' => 'listar depreciación de bienes'
            ],
            [
                'name'              => 'Crear registro de  depreciación de bienes',
                'slug'              => 'asset.depreciation.create',
                'description'       => 'Acceso para crear depreciación de bienes',
                'model'             => 'Modules\Asset\Models\AssetDepreciation',
                'model_prefix'      => 'contabilidad',
                'slug_alt'          => 'bienes.depreciacion.crear',
                'short_description' => 'agregar depreciación de bienes'
            ],
            [
                'name'              => 'Anular depreciación de bienes',
                'slug'              => 'asset.depreciation.cancel',
                'description'       => 'Acceso para anular depreciación de bienes',
                'model'             => 'Modules\Asset\Models\AssetDepreciation',
                'model_prefix'      => 'contabilidad',
                'slug_alt'          => 'bienes.depreciacion.anular',
                'short_description' => 'anular depreciación de bienes'
            ],
            [
                'name'              => 'Generar reportes de depreciación de bienes',
                'slug'              => 'asset.depreciation.report',
                'description'       => 'Acceso para generar reportes de depreciación de bienes',
                'model'             => 'Modules\Asset\Models\AssetDepreciation',
                'model_prefix'      => 'contabilidad',
                'slug_alt'          => 'bienes.depreciacion.reportes',
                'short_description' => 'Generar reportes de depreciación de bienes'
            ]
        ];

        $suppliersPermissions = [
            /* Proveedores */
            [
                'name' => 'Crear registro de  especialidad de proveedor',
                'slug' => 'asset.supplierspecialty.create',
                'description' => 'Acceso para crear especialidad de proveedor',
                'model' => 'Modules\Asset\Models\AssetSupplierSpecialty', 'model_prefix' => 'Bienes',
                'slug_alt' => 'especialidad.proveedor.crear',
                'short_description' => 'agregar especialidad de proveedor'
            ],
            [
                'name' => 'Modificar registro de especialidad de proveedor',
                'slug' => 'asset.supplierspecialty.edit',
                'description' => 'Acceso para editar especialidad de proveedor',
                'model' => 'Modules\Asset\Models\AssetSupplierSpecialty', 'model_prefix' => 'Bienes',
                'slug_alt' => 'especialidad.proveedor.editar',
                'short_description' => 'editar especialidad de proveedor'
            ],
            [
                'name' => 'Eliminar registro de especialidad de proveedor',
                'slug' => 'asset.supplierspecialty.delete',
                'description' => 'Acceso para eliminar especialidad de proveedor',
                'model' => 'Modules\Asset\Models\AssetSupplierSpecialty', 'model_prefix' => 'Bienes',
                'slug_alt' => 'especialidad.proveedor.eliminar',
                'short_description' => 'eliminar especialidad de proveedor'
            ],
            [
                'name' => 'Visualizar registro de especialidades de proveedores',
                'slug' => 'asset.supplierspecialty.list',
                'description' => 'Acceso para ver especialidades de proveedores',
                'model' => 'Modules\Asset\Models\AssetSupplierSpecialty', 'model_prefix' => 'Bienes',
                'slug_alt' => 'especialidad.proveedor.ver',
                'short_description' => 'ver especialidad de proveedor'
            ],
            [
                'name' => 'Crear registro de tipo de proveedor',
                'slug' => 'asset.suppliertype.create',
                'description' => 'Acceso para crear tipo de proveedor',
                'model' => 'Modules\Asset\Models\AssetSupplierType', 'model_prefix' => 'Bienes',
                'slug_alt' => 'tipo.proveedor.crear',
                'short_description' => 'Agregar tipo de proveedor'
            ],
            [
                'name' => 'Modificar registro de tipo de proveedor',
                'slug' => 'asset.suppliertype.edit',
                'description' => 'Acceso para editar tipo de proveedor',
                'model' => 'Modules\Asset\Models\AssetSupplierType', 'model_prefix' => 'Bienes',
                'slug_alt' => 'tipo.proveedor.editar',
                'short_description' => 'Editar tipo de proveedor'
            ],
            [
                'name' => 'Eliminar registro de tipo de proveedor',
                'slug' => 'asset.suppliertype.delete',
                'description' => 'Acceso para eliminar tipo de proveedor',
                'model' => 'Modules\Asset\Models\AssetSupplierType', 'model_prefix' => 'Bienes',
                'slug_alt' => 'tipo.proveedor.eliminar',
                'short_description' => 'Eliminar tipo de proveedor'
            ],
            [
                'name' => 'Visualizar registro de tipos de proveedores',
                'slug' => 'asset.suppliertype.list',
                'description' => 'Acceso para ver tipos de proveedores',
                'model' => 'Modules\Asset\Models\AssetSupplierType', 'model_prefix' => 'Bienes',
                'slug_alt' => 'tipo.proveedor.ver',
                'short_description' => 'Ver tipo de proveedor'
            ],
            [
                'name' => 'Crear registro de proveedor',
                'slug' => 'asset.supplier.create',
                'description' => 'Acceso para crear proveedor',
                'model' => 'Modules\Asset\Models\AssetSupplier', 'model_prefix' => 'Bienes',
                'slug_alt' => 'proveedor.crear',
                'short_description' => 'Agregar proveedor'
            ],
            [
                'name' => 'Modificar registro de proveedor',
                'slug' => 'asset.supplier.edit',
                'description' => 'Acceso para editar proveedor',
                'model' => 'Modules\Asset\Models\AssetSupplier', 'model_prefix' => 'Bienes',
                'slug_alt' => 'proveedor.editar',
                'short_description' => 'Editar proveedor'
            ],
            [
                'name' => 'Eliminar registro de proveedor',
                'slug' => 'asset.supplier.delete',
                'description' => 'Acceso para eliminar proveedor',
                'model' => 'Modules\Asset\Models\AssetSupplier', 'model_prefix' => 'Bienes',
                'slug_alt' => 'proveedor.eliminar',
                'short_description' => 'Eliminar proveedor'
            ],
            [
                'name' => 'Visualizar registro de tipos de proveedores',
                'slug' => 'asset.supplier.list',
                'description' => 'Acceso para ver tipos de proveedores',
                'model' => 'Modules\Asset\Models\AssetSupplier', 'model_prefix' => 'Bienes',
                'slug_alt' => 'proveedor.ver',
                'short_description' => 'Ver proveedor'
            ],
        ];

        $assetRole->detachAllPermissions();
        Permission::where('slug', 'like', 'asset.%')->delete();
        foreach ($permissions as $permission) {
            $per = Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                [
                    'name' => $permission['name'], 'description' => $permission['description'],
                    'model' => $permission['model'], 'model_prefix' => $permission['model_prefix'],
                    'slug_alt' => $permission['slug_alt'], 'short_description' => $permission['short_description'],
                ]
            );

            $assetRole->attachPermission($per);

            if ($adminRole) {
                $adminRole->attachPermission($per);
            }
        }

        foreach ($depreciationPermissions as $depreciationPermission) {
            $per = Permission::updateOrCreate(
                ['slug' => $depreciationPermission['slug']],
                [
                    'name' => $depreciationPermission['name'], 'description' => $depreciationPermission['description'],
                    'model' => $depreciationPermission['model'], 'model_prefix' => $depreciationPermission['model_prefix'],
                    'slug_alt' => $depreciationPermission['slug_alt'], 'short_description' => $depreciationPermission['short_description'],
                ]
            );

            if ($accountingRole) {
                $accountingRole->attachPermission($per);
            }
        }

        if (!Module::has('Purchase') && !Module::isEnabled('Purchase')) {
            foreach ($suppliersPermissions as $suppliersPermission) {
                $per = Permission::updateOrCreate(
                    ['slug' => $permission['slug']],
                    [
                        'name' => $suppliersPermission['name'], 'description' => $suppliersPermission['description'],
                        'model' => $suppliersPermission['model'], 'model_prefix' => $suppliersPermission['model_prefix'],
                        'slug_alt' => $suppliersPermission['slug_alt'], 'short_description' => $suppliersPermission['short_description'],
                    ]
                );

                $assetRole->attachPermission($per);

                if ($adminRole) {
                    $adminRole->attachPermission($per);
                }
            }
        }
    }
}
