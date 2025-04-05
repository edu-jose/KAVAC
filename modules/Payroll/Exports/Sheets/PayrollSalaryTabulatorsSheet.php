<?php

namespace Modules\Payroll\Exports\Sheets;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Modules\Payroll\Models\PayrollSalaryTabulator;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Modules\Payroll\Models\PayrollSalaryTabulatorScale;

/**
 * @class PayrollSalaryTabulatorsSheet
 *
 * Clase que gestiona los objetos exportados del modelo de tabuladores salariales como hoja anexa al reporte de nómina
 *
 * @author Francisco J. P. Ruiz <javierrupe19@gmail.com | fjpenya@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSalaryTabulatorsSheet implements
    FromCollection,
    WithHeadings,
    ShouldAutoSize,
    WithTitle,
    WithStyles,
    WithEvents,
    WithCustomStartCell
{
    /**
     * Tabulador salarial
     *
     * @var  array $payrollSalaryTabulator
     */
    protected $payrollSalaryTabulator;

    /**
     * Fecha del periodo del pago de nomina
     *
     * @var  string $period_date
     */
    protected $period_date;

    /**
     * Información del salario
     *
     * @var array $info
     */
    protected $info;

    /**
     * Encabezado de la hoja
     *
     * @var array $headers
     */
    protected $headers;

    /**
     * Título de la hoja
     *
     * @var string $title
     */
    protected $title;

    /**
     * Método constructor de la clase.
     *
     * @param array $payrollSalaryTabulator Arreglo con la información del tabulador salarial
     *
     * @return void
     */
    public function __construct(array $payrollSalaryTabulator = [])
    {
        $this->info = array_pop($payrollSalaryTabulator);
        $this->headers = array_pop($payrollSalaryTabulator);
        $this->payrollSalaryTabulator = $payrollSalaryTabulator;
        $this->title = $this->info['name'] . ' ' . $this->info['code'];
    }

    /**
     * Establece la fecha del período del pago de nómina
     *
     * @return string
     */
    public function setPayrollPaymentPeriod($payrollPaymentPeriod): string
    {
        return $this->period_date = $payrollPaymentPeriod;
    }

    /**
     * Establece el título de la hoja
     *
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * Establece la celda en la que se debe comenzar a escribir el archivo a exportar
     *
     * @return string Celda de inicio de escritura
     */
    public function startCell(): string
    {
        return 'B5';
    }

    /**
     * Colección de datos a exportar
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function collection(): Collection
    {
        $period_end = $this->period_date;
        $payrollSalaryTabulator = PayrollSalaryTabulator::find($this->info['id']);
        $payrollSalaryAdjustment = null;

        if ($payrollSalaryTabulator) {
            /* Revisar si el tabulador salarial tiene ajustes por la fecha del periodo final */
            $salaryAdjustmentWithEndDate = $payrollSalaryTabulator->payrollSalaryAdjustments()
                ->whereNotNull('end_increase_date')
                ->whereDate('start_increase_date', '<=', $period_end)
                ->whereDate('end_increase_date', '>=', $period_end);

            if ($salaryAdjustmentWithEndDate->get()->isNotEmpty()) {
                $payrollSalaryAdjustment = $salaryAdjustmentWithEndDate->first();
            }

            $salaryAdjustmentWithoutEndDate = $payrollSalaryTabulator->payrollSalaryAdjustments()
                ->whereNull('end_increase_date')
                ->whereDate('start_increase_date', '<=', $period_end);

            if ($salaryAdjustmentWithoutEndDate->get()->isNotEmpty()) {
                $payrollSalaryAdjustment = $salaryAdjustmentWithoutEndDate->first();
            }
        }

        $fields  = [];
        $records = [];
        if ($payrollSalaryTabulator) {
            $payrollSalaryTabulatorScales = PayrollSalaryTabulatorScale::where([
                'payroll_salary_tabulator_id' => $payrollSalaryTabulator->id
            ])->with([
                'payrollSalaryTabulator',
                'payrollHorizontalScale',
                'payrollVerticalScale'
            ])->get();

            $salary_values = $payrollSalaryAdjustment?->salary_values ? json_decode($payrollSalaryAdjustment->salary_values) :  null;
            $count = 0;

            foreach ($payrollSalaryTabulatorScales as $payrollSalaryTabulatorScale) {
                if (($payrollSalaryTabulator->payroll_horizontal_salary_scale_id > 0) && ($payrollSalaryTabulator->payroll_vertical_salary_scale_id > 0)) {
                    $horizontalScale = $payrollSalaryTabulatorScale->payrollHorizontalScale;
                    $verticalScale = $payrollSalaryTabulatorScale->payrollVerticalScale;
                    if ($payrollSalaryAdjustment) {
                        if ($payrollSalaryAdjustment->increase_of_type == 'absolute_value') {
                            $fields[$horizontalScale->name . '-' . $verticalScale->name] =
                                $payrollSalaryTabulatorScale->value + $payrollSalaryAdjustment->value;
                        } elseif ($payrollSalaryAdjustment->increase_of_type == 'percentage') {
                            $fields[$horizontalScale->name . '-' . $verticalScale->name] =
                                $payrollSalaryTabulatorScale->value  * $payrollSalaryAdjustment->value / 100;
                        } else {
                            $fields[$horizontalScale->name . '-' . $verticalScale->name] =
                                $salary_values ? $salary_values[$count]->value : $payrollSalaryTabulatorScale->value;
                        }
                    } else {
                        $fields[$horizontalScale->name . '-' . $verticalScale->name] =
                            $payrollSalaryTabulatorScale->value;
                    }
                } elseif ($payrollSalaryTabulator->payroll_horizontal_salary_scale_id > 0) {
                    $horizontalScale = $payrollSalaryTabulatorScale->payrollHorizontalScale;
                    if ($payrollSalaryAdjustment) {
                        if ($payrollSalaryAdjustment->increase_of_type == 'absolute_value') {
                            $fields[$horizontalScale->name] =
                                $payrollSalaryTabulatorScale->value + $payrollSalaryAdjustment->value;
                        } elseif ($payrollSalaryAdjustment->increase_of_type == 'percentage') {
                            $fields[$horizontalScale->name] =
                                $payrollSalaryTabulatorScale->value * $payrollSalaryAdjustment->value / 100;
                        } else {
                            $fields[$horizontalScale->name] =
                                $salary_values ? $salary_values[$count]->value : $payrollSalaryTabulatorScale->value;
                        }
                    } else {
                        $fields[$horizontalScale->name] = $payrollSalaryTabulatorScale->value;
                    }
                } elseif ($payrollSalaryTabulator->payroll_vertical_salary_scale_id > 0) {
                    $verticalScale = $payrollSalaryTabulatorScale->payrollVerticalScale;
                    if ($payrollSalaryAdjustment) {
                        if ($payrollSalaryAdjustment->increase_of_type == 'absolute_value') {
                            $fields[$verticalScale->name] =
                                $payrollSalaryTabulatorScale->value + $payrollSalaryAdjustment->value;
                        } elseif ($payrollSalaryAdjustment->increase_of_type == 'percentage') {
                            $fields[$verticalScale->name] =
                                $payrollSalaryTabulatorScale->value * $payrollSalaryAdjustment->value / 100;
                        } else {
                            $fields[$verticalScale->name] =
                                $salary_values && isset($salary_values[$count])
                                ? $salary_values[$count]->value
                                : $payrollSalaryTabulatorScale->value;
                        }
                    } else {
                        $fields[$verticalScale->name] = $payrollSalaryTabulatorScale->value;
                    }
                }
                $count++;
            }

            if (($payrollSalaryTabulator->payroll_horizontal_salary_scale_id > 0) && ($payrollSalaryTabulator->payroll_vertical_salary_scale_id > 0)) {
                $payrollHorizontalSalaryScale = $payrollSalaryTabulator->payrollHorizontalSalaryScale;
                $payrollVerticalSalaryScale = $payrollSalaryTabulator->payrollVerticalSalaryScale;

                foreach ($payrollVerticalSalaryScale->payrollScales as $payrollVerticalScale) {
                    array_push($records, $payrollVerticalScale->name);
                    foreach ($payrollHorizontalSalaryScale->payrollScales as $payrollHorizontalScale) {
                        array_push(
                            $records,
                            $fields[$payrollHorizontalScale->name . '-' . $payrollVerticalScale->name]
                        );
                    }
                }
                $records = array_chunk($records, count($payrollHorizontalSalaryScale->payrollScales) + 1);
            } elseif ($payrollSalaryTabulator->payroll_horizontal_salary_scale_id > 0) {
                $payrollHorizontalSalaryScale = $payrollSalaryTabulator->payrollHorizontalSalaryScale;
                array_push($records, 'Incidencia');
                foreach ($payrollHorizontalSalaryScale->payrollScales as $payrollHorizontalScale) {
                    array_push($records, $fields[$payrollHorizontalScale->name]);
                }
                $records = array_chunk($records, count($payrollHorizontalSalaryScale->payrollScales) + 1);
            } elseif ($payrollSalaryTabulator->payroll_vertical_salary_scale_id > 0) {
                $payrollVerticalSalaryScale = $payrollSalaryTabulator->payrollVerticalSalaryScale;
                foreach ($payrollVerticalSalaryScale->payrollScales as $payrollVerticalScale) {
                    array_push($records, $payrollVerticalScale->name);
                    array_push($records, $fields[$payrollVerticalScale->name]);
                }
                $records = array_chunk($records, 2);
            }
            return new Collection($records);
        }
    }

    /**
     * Establece la cabecera del archivo exportado
     *
     * @return array Arreglo que contiene la estructura de cabecera del archivo exportado
     */
    public function headings(): array
    {
        return [
            ['text' => $this->title],
            [''],
            $this->headers
        ];
    }

    /**
     * Estilos de la hoja
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet Objeto con los datos de la hoja
     *
     * @return void
     */
    public function styles(Worksheet $sheet)
    {
        // Combinar celdas para el título y centrarlo
        $sheet->mergeCells('B5:' . $sheet->getHighestDataColumn() . '5');
        $sheet->getStyle('B5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    /**
     * Registro de eventos de la hoja
     *
     * @return array
     */
    public function registerEvents(): array
    {
        $payrollSalaryTabulator = PayrollSalaryTabulator::where('id', $this->info['id'])
            ->with(['institution' => function ($query) {
                $query->with('logo')->get();
            }])->first();
        $payrollSalaryTabulator_logo = $payrollSalaryTabulator->institution->logo->file ?? '';
        $data = [
            AfterSheet::class => function (AfterSheet $event) use ($payrollSalaryTabulator_logo) {
                $logo = storage_path() . '/pictures/' . $payrollSalaryTabulator_logo;
                if (file_exists($logo)) {
                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                    $drawing->setName('institution_logo');
                    $drawing->setDescription('Logo Institucional');
                    $drawing->setPath($logo);
                    $drawing->setCoordinates('A1');
                    $drawing->setHeight(45);
                    $drawing->setWorksheet($event->sheet->getDelegate());
                }
            }
        ];
        return $data;
    }
}
