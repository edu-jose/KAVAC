<?php

namespace Modules\Budget\Exports;

use App\Models\Institution;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BudgetFormulatedSheetExport implements WithDrawings, FromView, WithStyles
{
    protected $data;

    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct(array $view_data)
    {
        $this->data = $view_data;
    }
    public function drawings()
    {
        $profileUser = Auth()->user()->profile;
        if ($profileUser && $profileUser->institution_id !== null) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('This is my logo');
        if (file_exists(storage_path('pictures') . '/' . $institution->logo->file)) {
            $drawing->setPath(storage_path('pictures') . '/' . $institution->logo->file);
        }
        $drawing->setHeight(90);
        $drawing->setOffsetX(90);
        $drawing->setCoordinates('A1');

        return $drawing;
    }
    public function view(): View
    {
        return view('budget::xlsx.formulations', [
            'pdf' => $this->data['pdf'],
            'formulations' => $this->data['formulations'],
            'totalFormulations' => $this->data['totalFormulations'],
            'institution' => $this->data['institution'],
            'currencySymbol' => $this->data['currencySymbol'],
            'fiscal_year' => $this->data['fiscal_year'],
            'profile' => $this->data['profile'],
            'currency' => $this->data['currency'],
        ]);
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(100);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(30);
        $sheet->getColumnDimension('F')->setWidth(30);
        $sheet->getColumnDimension('G')->setWidth(30);
        $sheet->getColumnDimension('H')->setWidth(30);
        $sheet->getColumnDimension('I')->setWidth(30);
        $sheet->getColumnDimension('J')->setWidth(30);
        $sheet->getColumnDimension('K')->setWidth(30);
        $sheet->getColumnDimension('L')->setWidth(30);
        $sheet->getColumnDimension('M')->setWidth(30);
        $sheet->getColumnDimension('N')->setWidth(30);
        $sheet->getColumnDimension('O')->setWidth(30);
        $sheet->getColumnDimension('P')->setWidth(30);
        $sheet->getColumnDimension('Q')->setWidth(30);
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A2:E2')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A3')->getFont()->setBold(true);
        $sheet->getStyle('A3:E3')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('A1:E1')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('B10')->getAlignment()->setHorizontal('left');
        $sheet->getStyle('B8:O8')->getAlignment()->setHorizontal('left');
        $sheet->getStyle('B11:O11')->getAlignment()->setHorizontal('left');
        $sheet->mergeCells('A1:E1');
        $sheet->mergeCells('A2:E2');
        $sheet->mergeCells('A3:E3');
        $sheet->mergeCells('B8:O8');
        $sheet->mergeCells('B11:O11');
    }
}
