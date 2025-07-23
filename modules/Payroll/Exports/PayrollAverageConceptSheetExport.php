<?php

namespace Modules\Payroll\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PayrollAverageConceptSheetExport implements
    FromView,
    WithStyles,
    WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct(
        protected array $data,
        protected string $title
    ) {
    }

    public function title(): string
    {
        return $this->title;
    }

    public function view(): View
    {
        return view('payroll::xlsx.averageConceptsReport', [
            'headers' => [
                'worksheet_code' => "Ficha",
                'id_number' => "Cédula de Identidad",
                'payroll_staff' => "Trabajador",
                'position' => "Cargo",
                'payroll_concept' => "Concepto",
                'average' => "Cantidad (promedio)",
                'concept_count' => "Cantidad",
            ],
            'records' => $this->data['records'],
            'institution' => $this->data['institution'],
            'period' => $this->data['period'],
        ]);
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->getColumnDimension('A')->setWidth(40);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(45);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(45);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getStyle('A1:F1')->getFont()->setSize(11);
    }
}
