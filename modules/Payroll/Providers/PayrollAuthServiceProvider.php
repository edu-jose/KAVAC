<?php

namespace Modules\Payroll\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Modules\Payroll\Models\PayrollConcept;
use Modules\Payroll\Policies\PayrollConceptPolicy;

/**
 * @class PayrollAuthServiceProvider
 * @brief Proveedor de servicios de autenticación
 *
 * Gestiona los proveedores de servicios de autenticación
 */
class PayrollAuthServiceProvider extends ServiceProvider
{
    /**
     * Las asignaciones de políticas para la aplicación.
     *
     * @var array $policies
     */
    protected $policies = [
        PayrollConcept::class => PayrollConceptPolicy::class,
    ];

    /**
     * Registra cualquier servicio de autenticación/autorización.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
