<?php

namespace Modules\Budget\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BudgetConsolidatedSheetExport implements
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
        return view('budget::xlsx.consolidatedReport', [
            'headers' => $this->data['headers'],
            'months' => $this->data['months'],
            'date' => $this->data['date'],
            'monthFrom' => $this->data['monthFrom'],
            'monthTo' => $this->data['monthTo'],
            'records' => $this->data['records'],
            'institution' => $this->data['institution'],
            'budgetCategory' => $this->data['budgetCategory'],
        ]);
    }
    public function styles(Worksheet $sheet)
    {
        $highestColumn = $sheet->getHighestDataColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        for ($i = 3; $i <= $highestColumnIndex; $i++) {
            $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($column)->setWidth(40);
        }

        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(60);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getStyle('A1:A8')->getFont()->setBold(true);
        $sheet->getStyle('A1:A5')->getFont()->setSize(7.5);
        $sheet->getStyle('A6:A8')->getFont()->setSize(11);
        //$sheet->getStyle('A2:E2')->getAlignment()->setHorizontal('center');
        //$sheet->getStyle('B1')->getFont()->setSize(7);
        //$sheet->mergeCells('G6:H6');
    }
}
