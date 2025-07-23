<?php

namespace Modules\Payroll\Imports\Payroll;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Modules\Payroll\Models\PayrollResetParameter;
use Modules\Payroll\Models\PayrollStaff;

class PayrollParameterSheetImport implements ToCollection, WithTitle
{
    protected int $payrollId;
    protected string $title = '';
    protected ?int $conceptId = null;

    public function title(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function setConceptId(?int $conceptId): self
    {
        $this->conceptId = $conceptId;
        return $this;
    }

    public function setPayrollId(int $payrollId): self
    {
        $this->payrollId = $payrollId;
        return $this;
    }

    public function collection(Collection $rows): void
    {
        DB::transaction(function () use ($rows) {
            PayrollResetParameter::where('payroll_id', $this->payrollId)
                ->where('payroll_concept_id', $this->conceptId)
                ->forceDelete();

            if ($rows->isEmpty()) {
                return;
            }

            $header = $rows->first();
            $idNumbers = $rows->skip(1)->pluck(1)->filter()->unique();

            $staffMap = PayrollStaff::toBase()
                ->select('id', 'id_number')
                ->whereIn('id_number', $idNumbers)
                ->pluck('id', 'id_number');

            $now = now();
            $insertData = [];

            $rows->skip(1)->chunk(500)->each(function ($chunk) use ($header, $staffMap, $now, &$insertData) {
                foreach ($chunk as $row) {
                    $idNumber = $row[1] ?? null;
                    $staffId = $staffMap[$idNumber] ?? null;

                    if (!$staffId) {
                        continue;
                    }

                    for ($i = 3; $i < count($row); $i++) {
                        $paramName = $header[$i] ?? null;
                        $value = $row[$i] ?? 0;

                        if (!$paramName) {
                            continue;
                        }

                        $insertData[] = [
                            'payroll_id' => $this->payrollId,
                            'payroll_staff_id' => $staffId,
                            'payroll_concept_id' => $this->conceptId,
                            'name' => $paramName,
                            'value' => $value,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                PayrollResetParameter::upsert(
                    $insertData,
                    ['payroll_id', 'payroll_staff_id', 'payroll_concept_id', 'name'],
                    ['value', 'updated_at']
                );
                $insertData = [];
            });
        });
    }
}
