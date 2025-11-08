<?php

namespace Modules\Budget\Http\Resources;

use Nwidart\Modules\Facades\Module;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @class BudgetBudgetAvailabilityPayrollResource
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetBudgetAvailabilityPayrollResource extends JsonResource
{
    /**
     * Transforma el recurso de colección en un arreglo.
     *
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     *
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if (!Module::has('Payroll') || !Module::isEnabled('Payroll')) {
            return [];
        }
        $currency = $this->resource->payrollPaymentPeriod->payrollPaymentType->payrollConcepts->first()->currency;

        return [
            'id' => $this->resource->id,
            'budgetary_availability_code' => $this->resource->budgetCommonBudgetaryAvailability?->code ?? '',
            'code' => $this->resource->code ?? '',
            'description' => $this->resource->name ?? '',
            'currency_name' => $currency->name ?? '',
            'available' => $this->resource->payrollPaymentPeriod?->availability_status ?? '',
            'module' => 'Payroll'
        ];
    }
}
