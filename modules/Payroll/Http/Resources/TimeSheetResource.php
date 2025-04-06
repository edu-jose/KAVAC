<?php

declare(strict_types=1);

namespace Modules\Payroll\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Payroll\Models\PayrollSupervisedGroup;

/**
 * @class TimeSheetResource
 * @brief Representa un recurso para la hoja de tiempo
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class TimeSheetResource extends JsonResource
{
    /**
     * Transforma el recurso a un arreglo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $payrollSuperviedGroup = $this->getPayrollSuperviedGroup();
        return [
            'id' => $this->resource->id,
            'institution_id' => $this->resource->institution_id,
            'institution' => $this->resource->institution,
            'document_status_id' => $this->resource->document_status_id,
            'document_status' => $this->resource->documentStatus,
            'date' => Carbon::parse($this->resource->from_date)->format('d/m/Y') . ' - ' . Carbon::parse($this->resource->to_date)->format('d/m/Y'),
            'from_date' => $this->resource->from_date,
            'to_date' => $this->resource->to_date,
            'payroll_supervised_group_id' => $payrollSuperviedGroup->id,
            'payroll_supervised_group' => !empty($payrollSuperviedGroup)
                ? [
                    'code' => $payrollSuperviedGroup->code,
                    'supervisor_id' => $payrollSuperviedGroup->supervisor_id,
                    'supervisor' => !empty($payrollSuperviedGroup->supervisor)
                        ? (($payrollSuperviedGroup->supervisor->id_number ?? $payrollSuperviedGroup->supervisor->passport) .
                        ' - ' . $payrollSuperviedGroup->supervisor->first_name . ' ' . $payrollSuperviedGroup->supervisor->last_name)
                        : null,
                    'approver_id' => $payrollSuperviedGroup->approver_id,
                    'approver' => !empty($payrollSuperviedGroup->approver)
                        ? (($payrollSuperviedGroup->approver->id_number ?? $payrollSuperviedGroup->approver->passport) .
                        ' - ' . $payrollSuperviedGroup->approver->first_name . ' ' . $payrollSuperviedGroup->approver->last_name)
                        : null,
                ]
                : null,
            'payroll_time_sheet_parameter_id' => $this->resource->payroll_time_sheet_parameter_id,
            'payroll_time_sheet_parameter' => $this->resource->payrollTimeSheetParameter,
            'time_sheet_data' => $this->resource->time_sheet_data,
            'time_sheet_columns' => $this->resource->time_sheet_columns,
            'observations' => $this->resource->observations,
            'updated_at' => $this->resource->updated_at,
        ];
    }

    /**
     * Obtiene el grupo supervisado de la hoja de tiempo
     *
     * @return array|object
     */
    protected function getPayrollSuperviedGroup(): ?PayrollSupervisedGroup
    {
        $data = $this->resource->payrollSupervisedGroup;
        if ("CE" == $this->resource->documentStatus->action) {
            $lastUpdate = $this->resource->updated_at;
            $audit = $this->resource->payrollSupervisedGroup->audits()
                ->where('created_at', '>=', $lastUpdate)
                ->latest()
                ->get();
            foreach ($audit as $a) {
                $data = $data->transitionTo($a, true);
            }
        }
        return $data;
    }
}
