<?php

namespace Modules\Payroll\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @class PayrollSupervisedGroupResource
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSupervisedGroupResource extends JsonResource
{
    /**
     * Transforma el recurso de colección en un arreglo.
     *
     * @param  \Illuminate\Http\Request     Objeto con datos de la petición
     *
     * @return array    Devuelve un arreglo con los datos de la colección
     */
    public function toArray($request)
    {

        return [
            'code' => $this->code,
            'id' => $this->id,
            'supervisor_id' => $this->supervisor_id,
            'approver_id' => $this->approver_id,
            'supervisor' => [
                'id' => $this->supervisor->id,
                'first_name' => $this->supervisor->first_name,
                'last_name' => $this->supervisor->last_name,
            ],
            'approver' => [
                'id' => $this->approver->id,
                'first_name' => $this->approver->first_name,
                'last_name' => $this->approver->last_name,
            ],
            'payroll_supervised_group_staff' => $this->payrollSupervisedGroupStaff->map(function ($staff) {
                return [
                    'id' => $staff->id,
                    'payroll_staff_id' => $staff->payroll_staff_id,
                    'payroll_staff' => [
                        'payroll_employment' => [
                            'department' => [
                                'name' => $staff->payrollStaff?->payrollEmployment?->department?->name,
                            ],
                        ],
                        'id_number' => $staff->payrollStaff?->id_number,
                        'first_name' => $staff->payrollStaff?->first_name,
                        'last_name' => $staff->payrollStaff?->last_name,
                    ]

                ];
            }),
        ];
    }
}
