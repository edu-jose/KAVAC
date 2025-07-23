<?php

declare(strict_types=1);

namespace Modules\Payroll\Actions\Payroll;

use App\Exports\MultiSheetExport;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Payroll\Exports\Payroll\PayrollParameterExport;
use Modules\Payroll\Models\PayrollResetParameter;

final class PayrollParametersExportAction
{
    public function __construct(
        private MultiSheetExport $worksheet,
    ) {
    }

    public function invoke(
        ?int $payrollId = null,
        array $data,
        array $allParameters = [],
        string $file = 'data'
    ) {
        $sheets = [];

        /*PayrollResetParameter::toBase()
            ->where('payroll_id', $payrollId)
            ->whereNotIn('name', $allParameters)
            ->delete();*/
        foreach ($data as $key => $value) {
            $sheets[$key] =  new PayrollParameterExport(
                [
                    'headers' => $value['parameters']?->pluck('name')->toArray(),
                    'records' => $value,
                    'payrollId' => $payrollId,
                    'conceptId' => $value['concept_id'],
                ],
                $key
            );
        }
        $this->worksheet->setSheets($sheets);

        return Excel::download($this->worksheet, $file . '.xlsx');
    }
}
