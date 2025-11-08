<?php

namespace Modules\Budget\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @class BudgetBudgetAvailabilityResource
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetBudgetAvailabilityResource extends JsonResource
{
    /**
     * Transforma el recurso en un arreglo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $key = array_key_last($this->resource->relatable->toArray());
        return [
            'id' => $this->resource->id,
            'budgetary_availability_code' => $this->resource->budgetCommonBudgetaryAvailability?->code ?? '',
            'code' => $this->resource->relatable[$key]['purchaseRequirementItem']['purchaseRequirement']['code'] ?? '',
            'description' => $this->resource->relatable[$key]['purchaseRequirementItem']['purchaseRequirement']['description'] ?? '',
            'currency_name' => $this->currency->name,
            'available' => $this->availability,
            'module' => 'Purchase'
        ];
    }
}
