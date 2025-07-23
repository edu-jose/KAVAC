<?php

namespace Modules\Payroll\Imports\Payroll;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Modules\Payroll\Models\PayrollConcept;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PayrollParameterImport implements WithMultipleSheets
{
    public function __construct(protected string $filePath, protected int $payrollId)
    {
    }

    public function sheets(): array
    {
        $spreadsheet = IOFactory::load($this->filePath);
        $sheetNames = $spreadsheet->getSheetNames();
        $conceptIds = PayrollConcept::toBase()
            ->select('id', 'name')
            ->whereIn('name', $sheetNames)
            ->pluck('id', 'name')->toArray();

        $sheets = [];
        foreach ($sheetNames as $name) {
            $sheets[$name] = (new PayrollParameterSheetImport())
                ->setTitle($name)
                ->setPayrollId($this->payrollId)
                ->setConceptId($conceptIds[$name] ?? null);
        }

        return $sheets;
    }
}
