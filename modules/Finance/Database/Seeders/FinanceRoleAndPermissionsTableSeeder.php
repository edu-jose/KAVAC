<?php

namespace Modules\Finance\Database\Seeders;

use App\Roles\Models\Permission;
use App\Roles\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

/**
 * @class FinanceRoleAndPermissionsTableSeeder
 * @brief Carga de datos de roles y permisos del módulo de finanzas
 *
 * Clase seeder para cargar datos de roles y permisos del módulo de finanzas
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinanceRoleAndPermissionsTableSeeder extends Seeder
{
    /**
     * Método que ejecuta el seeder e inserta los datos en la base de datos.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $adminRole = Role::where('slug', 'admin')->first();

        $financeRole = Role::updateOrCreate(
            ['slug' => 'finance'],
            ['name' => 'Finanza', 'description' => 'Coordinador de finanza']
        );

        $permissions = [
            [
                'name' => 'Configuración del módulo de finanzas',
                'slug' => 'finance.setting.create',
                'description' => 'Acceso a la configuración del módulo de finanzas',
                'model' => '',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'finanza.configuracion.crear',
                'short_description' => 'Configuración de finanzas',
            ],
            [
                'name' => 'Crear Registro banco',
                'slug' => 'finance.bank.create',
                'description' => 'Acceso para crear registro banco',
                'model' => 'Modules\Finance\Models\FinanceBank',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'banco.crear',
                'short_description' => 'Agregar banco',
            ],
            [
                'name' => 'Modificar banco',
                'slug' => 'finance.bank.edit',
                'description' => 'Acceso para modificar banco',
                'model' => 'Modules\Finance\Models\FinanceBank',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'banco.modificar',
                'short_description' => 'modificar banco',
            ],
            [
                'name' => 'Eliminar banco',
                'slug' => 'finance.bank.delete',
                'description' => 'Acceso para eliminar banco',
                'model' => 'Modules\Finance\Models\FinanceBank',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'banco.eliminar',
                'short_description' => 'Eliminar banco',
            ],
            [
                'name' => 'Visualizar registro bancos',
                'slug' => 'finance.bank.list',
                'description' => 'Acceso para ver bancos',
                'model' => 'Modules\Finance\Models\FinanceBank',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'banco.ver',
                'short_description' => 'Ver banco',
            ],
            [
                'name' => 'Crear Registro agencia bancaria',
                'slug' => 'finance.bankagency.create',
                'description' => 'Acceso para crear registro agencia bancaria',
                'model' => 'Modules\Finance\Models\FinanceBankAgency',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'agencia_bancaria.crear',
                'short_description' => 'Agregar agencia bancaria',
            ],
            [
                'name' => 'Modificar agencia bancaria',
                'slug' => 'finance.bankagency.edit',
                'description' => 'Acceso para modificar agencia bancaria',
                'model' => 'Modules\Finance\Models\FinanceBankAgency',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'agencia_bancaria.modificar',
                'short_description' => 'modificar agencia bancaria',
            ],
            [
                'name' => 'Eliminar agencia bancaria',
                'slug' => 'finance.bankagency.delete',
                'description' => 'Acceso para eliminar agencia bancaria',
                'model' => 'Modules\Finance\Models\FinanceBankAgency',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'agencia_bancaria.eliminar',
                'short_description' => 'Eliminar agencia bancaria',
            ],
            [
                'name' => 'Visualizar registro agencia bancarias',
                'slug' => 'finance.bankagency.list',
                'description' => 'Acceso para ver agencia bancarias',
                'model' => 'Modules\Finance\Models\FinanceBankAgency',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'agencia_bancaria.ver',
                'short_description' => 'Ver agencia bancaria',
            ],
            [
                'name' => 'Crear Registro tipo de cuenta',
                'slug' => 'finance.accounttype.create',
                'description' => 'Acceso para crear registro tipo de cuenta',
                'model' => 'Modules\Finance\Models\FinanceAccountType',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'tipo_cuenta.crear',
                'short_description' => 'Agregar tipo de cuenta',
            ],
            [
                'name' => 'Modificar tipo de cuenta',
                'slug' => 'finance.accounttype.edit',
                'description' => 'Acceso para modificar tipo de cuenta',
                'model' => 'Modules\Finance\Models\FinanceAccountType',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'tipo_cuenta.modificar',
                'short_description' => 'modificar tipo de cuenta',
            ],
            [
                'name' => 'Eliminar tipo de cuenta',
                'slug' => 'finance.accounttype.delete',
                'description' => 'Acceso para eliminar tipo de cuenta',
                'model' => 'Modules\Finance\Models\FinanceAccountType',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'tipo_cuenta.eliminar',
                'short_description' => 'Eliminar tipo de cuenta',
            ],
            [
                'name' => 'Visualizar registro tipo de cuentas',
                'slug' => 'finance.accounttype.list',
                'description' => 'Acceso para ver tipo de cuentas',
                'model' => 'Modules\Finance\Models\FinanceAccountType',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'tipo_cuenta.ver',
                'short_description' => 'Ver tipo de cuenta',
            ],
            [
                'name' => 'Crear Registro cuenta bancaria',
                'slug' => 'finance.bankaccount.create',
                'description' => 'Acceso para crear registro cuenta bancaria',
                'model' => 'Modules\Finance\Models\FinanceBankAccount',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'cuenta_bancaria.crear',
                'short_description' => 'Agregar cuenta bancaria',
            ],
            [
                'name' => 'Modificar cuenta bancaria',
                'slug' => 'finance.bankaccount.edit',
                'description' => 'Acceso para modificar cuenta bancaria',
                'model' => 'Modules\Finance\Models\FinanceBankAccount',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'cuenta_bancaria.modificar',
                'short_description' => 'modificar cuenta bancaria',
            ],
            [
                'name' => 'Eliminar cuenta bancaria',
                'slug' => 'finance.bankaccount.delete',
                'description' => 'Acceso para eliminar cuenta bancaria',
                'model' => 'Modules\Finance\Models\FinanceBankAccount',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'cuenta_bancaria.eliminar',
                'short_description' => 'Eliminar cuenta bancaria',
            ],
            [
                'name' => 'Visualizar registro cuenta bancarias',
                'slug' => 'finance.bankaccount.list',
                'description' => 'Acceso para ver cuenta bancarias',
                'model' => 'Modules\Finance\Models\FinanceBankAccount',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'cuenta_bancaria.ver',
                'short_description' => 'Ver cuenta bancaria',
            ],
                   /* conciliacion bancarias. */
            [
                'name' => 'Crear Registro conciliacion bancaria',
                'slug' => 'finance.bankreconciliation.create',
                'description' => 'Acceso para crear registro conciliacion bancaria',
                'model' => '',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'bankreconciliation.crear',
                'short_description' => 'Agregar conciliacion bancaria',
            ],
            [
                'name' => 'Modificar conciliacion bancaria',
                'slug' => 'finance.bankreconciliation.edit',
                'description' => 'Acceso para modificar conciliacion bancaria',
                'model' => '',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'bankreconciliation.modificar',
                'short_description' => 'modificar conciliacion bancaria',
            ],
            [
                'name' => 'Visualizar registro conciliacion bancaria',
                'slug' => 'finance.bankreconciliation.list',
                'description' => 'Acceso para ver órdenes de pago',
                'model' => '',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'bankreconciliation.ver',
                'short_description' => 'Ver conciliacion bancaria',
            ],
            [
                'name' => 'Eliminar conciliacion bancaria',
                'slug' => 'finance.bankreconciliation.delete',
                'description' => 'Acceso para eliminar la conciliacion bancaria',
                'model' => '',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'bankreconciliation.eliminar',
                'short_description' => 'Eliminar conciliacion bancaria',
            ],
            /* Orden de pagos. */
            [
                'name' => 'Crear Registro orden de pago',
                'slug' => 'finance.payorder.create',
                'description' => 'Acceso para crear registro orden de pago',
                'model' => '',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'orden_pago.crear',
                'short_description' => 'Agregar orden de pago',
            ],
            [
                'name' => 'Modificar orden de pago',
                'slug' => 'finance.payorder.edit',
                'description' => 'Acceso para modificar orden de pago',
                'model' => '',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'orden_pago.modificar',
                'short_description' => 'modificar orden de pago',
            ],
            [
                'name' => 'Visualizar registro orden de pago',
                'slug' => 'finance.payorder.list',
                'description' => 'Acceso para ver órdenes de pago',
                'model' => '',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'orden_pago.ver',
                'short_description' => 'Ver orden de pago',
            ],
            [
                'name' => 'Eliminar orden de pago',
                'slug' => 'finance.payorder.delete',
                'description' => 'Acceso para eliminar la orden de pago',
                'model' => '',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'orden_pago.eliminar',
                'short_description' => 'Eliminar orden de pago',
            ],
            [
                'name' => 'Aprobar orden de pago',
                'slug' => 'finance.payorder.approve',
                'description' => 'Acceso para aprobar la orden de pago',
                'model' => 'Modules\Finance\Models\FinancePayOrder',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'orden_pago.aprobar',
                'short_description' => 'Aprobar orden de pago',
            ],
            [
                'name' => 'Anular órdenes de pago',
                'slug' => 'finance.payorder.cancel',
                'description' => 'Acceso para anular una orden de pago',
                'model' => 'Modules\Finance\Models\FinancePayOrder',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'orden_pago.anular',
                'short_description' => 'Anular órdenes de pago',
            ],
            [
                'name' => 'Generar reporte de órdenes de pago',
                'slug' => 'finance.payorder.report',
                'description' => 'Acceso para generar reporte de una orden de pago',
                'model' => 'Modules\Finance\Models\FinancePayOrder',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'orden_pago.reporte',
                'short_description' => 'generar reporte de órdenes de pago',
            ],
            [
                'name' => 'Generar un metodo de pago bancario',
                'slug' => 'finance.payment.methods.create',
                'description' => 'Acceso para Generar un metodo de pago bancario',
                'model' => '',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'payment.methods.crear',
                'short_description' => 'Agregar  un metodo de pago',
            ],
            [
                'name' => 'Modificar un metodo de pago bancario',
                'slug' => 'finance.payment.methods.edit',
                'description' => 'Acceso para Modificar  un metodo de pago',
                'model' => '',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'payment.methods.Modificar',
                'short_description' => 'Modificar  un metodo de pago',
            ],
            [
                'name' => 'Visualizar registro metodo de pago bancario',
                'slug' => 'finance.payment.methods.list',
                'description' => 'Acceso para ver los metodo bancario',
                'model' => '',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'payment.methods.ver',
                'short_description' => 'Ver los metodo bancario',
            ],
            [
                'name' => 'Eliminar  un metodo de pago bancario',
                'slug' => 'finance.payment.methods.delete',
                'description' => 'Acceso para eliminar  un metodo de pago',
                'model' => '',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'payment.methods.eliminar',
                'short_description' => 'Eliminar  un metodo de pago',
            ],
            [
                'name' => 'Crear Registro movimiento bancario',
                'slug' => 'finance.movements.create',
                'description' => 'Acceso para crear registro un movimiento bancario',
                'model' => '',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'movimiento_bancario.crear',
                'short_description' => 'Agregar un movimiento bancario',
            ],
            [
                'name' => 'Modificar un movimiento bancario',
                'slug' => 'finance.movements.edit',
                'description' => 'Acceso para modificar un movimiento bancario',
                'model' => '',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'movimiento_bancario.modificar',
                'short_description' => 'modificar un movimiento bancario',
            ],
            [
                'name' => 'Visualizar registro movimientos bancario',
                'slug' => 'finance.movements.list',
                'description' => 'Acceso para ver los movimientos bancario',
                'model' => '',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'movimiento_bancario.ver',
                'short_description' => 'Ver los movimientos bancario',
            ],
            [
                'name' => 'Eliminar registro movimiento bancario',
                'slug' => 'finance.movements.delete',
                'description' => 'Acceso para Eliminar registro movimiento bancario',
                'model' => '',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'movimiento_bancario.eliminar',
                'short_description' => 'Eliminar un movimiento bancario',
            ],
            [
                'name' => 'Aprobar movimientos bancarios',
                'slug' => 'finance.movements.approve',
                'description' => 'Acceso para aprobar movimientos bancarios',
                'model' => 'Modules\Finance\Models\FinanceBankingMovement',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'movimiento_bancario.aprobar',
                'short_description' => 'Aprobar movimientos bancarios',
            ],
            [
                'name' => 'Anular movimientos bancarios',
                'slug' => 'finance.movements.cancel',
                'description' => 'Acceso para anular un movimiento bancario',
                'model' => 'Modules\Finance\Models\FinanceBankingMovement',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'movimiento_bancario.anular',
                'short_description' => 'Anular movimientos bancarios',
            ],
            [
                'name' => 'Generar reporte de movimientos bancarios',
                'slug' => 'finance.movements.report',
                'description' => 'Acceso para generar reporte de movimientos bancarios',
                'model' => 'Modules\Finance\Models\FinanceBankingMovement',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'orden_pago.reporte',
                'short_description' => 'generar reporte de movimientos bancarios',
            ],
            /* Emisión de pagos. */
            [
                'name' => 'Obtener listado de emisión de pagos',
                'slug' => 'finance.paymentexecute.index',
                'description' => 'Acceso para obtener listado de emisión de pagos',
                'model' => 'Modules\Finance\Models\FinancePaymentExecute',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'emision_pagos.listado',
                'short_description' => 'Acceder al listado de emisión de pagos',
            ],
            [
                'name' => 'Registrar una emisión de pagos',
                'slug' => 'finance.paymentexecute.store',
                'description' => 'Acceso para registrar una emisión de pagos',
                'model' => 'Modules\Finance\Models\FinancePaymentExecute',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'emision_pagos.crear',
                'short_description' => 'Registrar una emisión de pagos',
            ],
            [
                'name' => 'Actualizar una emisión de pagos',
                'slug' => 'finance.paymentexecute.update',
                'description' => 'Acceso para actualizar una emisión de pagos',
                'model' => 'Modules\Finance\Models\FinancePaymentExecute',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'emision_pago.actualizar',
                'short_description' => 'Actualizar una emisión de pagos',
            ],
            [
                'name' => 'Eliminar registro emisión de pagos',
                'slug' => 'finance.paymentexecute.destroy',
                'description' => 'Acceso para eliminar una emisión de pagos',
                'model' => 'Modules\Finance\Models\FinancePaymentExecute',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'emision_pago.eliminar',
                'short_description' => 'Eliminar una emisión de pagos',
            ],
            [
                'name' => 'Aprobar una emisión de pagos',
                'slug' => 'finance.paymentexecute.approve',
                'description' => 'Acceso para aprobar una emisión de pagos',
                'model' => 'Modules\Finance\Models\FinancePaymentExecute',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'emision_pago.aprobar',
                'short_description' => 'Aprobar una emisión de pagos',
            ],
            [
                'name' => 'Anular emisiones de pagos',
                'slug' => 'finance.paymentexecute.cancel',
                'description' => 'Acceso para anular una emisión de pago',
                'model' => 'Modules\Finance\Models\FinancePaymentExecute',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'emision_pago.anular',
                'short_description' => 'Anular emisiones de pagos',
            ],
            [
                'name' => 'Generar reporte de emisiones de pago',
                'slug' => 'finance.paymentexecute.report',
                'description' => 'Acceso para generar reporte de una orden de pago',
                'model' => 'Modules\Finance\Models\FinancePaymentExecute',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'orden_pago.reporte',
                'short_description' => 'generar reporte de emisiones de pago',
            ],
            /* Reporte iva */
            [
                'name' => 'Generar reporte Iva',
                'slug' => 'finance.paymentexecute.iva',
                'description' => 'Acceso para Generar reporte Iva',
                'model' => 'Modules\Finance\Models\FinancePaymentExecute',
                'model_prefix' => 'finanzas',
                'slug_alt' => 'emision_pago.iva',
                'short_description' => 'Acceso para Generar reporte Iva',
            ],
            /* Dashboard */
            [
                'name'              => 'Vista principal del dashboard del módulo de finanzas',
                'slug'              => 'finance.dashboard',
                'description'       => 'Acceso para visualizar el dashboard del módulo',
                'model'             => '',
                'model_prefix'      => 'finanzas',
                'slug_alt'          => 'panel_de_control.ver',
                'short_description' => 'Visualizar panel de control del módulo de finanza'
            ],
        ];

        $financeRole->detachAllPermissions();

        foreach ($permissions as $permission) {
            $per = Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                [
                    'name' => $permission['name'],
                    'description' => $permission['description'],
                    'model' => $permission['model'],
                    'model_prefix' => $permission['model_prefix'],
                    'slug_alt' => $permission['slug_alt'],
                    'short_description' => $permission['short_description'],
                ]
            );

            $financeRole->attachPermission($per);

            if ($adminRole) {
                $adminRole->attachPermission($per);
            }
        }
    }
}
