<?php

namespace Modules\Payroll\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Modules\Payroll\Models\PayrollSalaryAdjustment;
use Modules\Payroll\Models\PayrollSalaryTabulatorScale;

/**
 * @class PayrollSalaryAdjustmentTabulatorExport
 * @brief Clase que exporta el tabulador con el ajuste salarial
 *
 * @author Fabián Palmera <fapalmera@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSalaryAdjustmentTabulatorExport extends \App\Exports\DataExport implements
    WithHeadings,
    ShouldAutoSize
{
    use Exportable;

    /**
     * Identificador del tabulador salarial
     *
     * @var integer $payrollSalaryAdjustmentId
     */
    protected $payrollSalaryAdjustmentId;

    /**
     * Método constructor de la clase
     *
     * @param mixed $model
     */
    public function __construct($model = null)
    {
        $this->model = $model;
    }

    /**
     * Establece el identificador del ajuste en tablas salariales
     *
     * @param integer $salaryAdjustmentId Identificador único del tabuldor salarial
     */
    public function setSalaryAdjustmentId(int $salaryAdjustmentId)
    {
        $this->payrollSalaryAdjustmentId = $salaryAdjustmentId;
    }

    /**
     * Genera el listado de tabuladores salariales con el ajuste
     *
     * @return \Illuminate\Support\Collection|void
     */
    public function collection()
    {
        $payrollSalaryAdjustment = PayrollSalaryAdjustment::find($this->payrollSalaryAdjustmentId);
        $payrollSalaryTabulator = $payrollSalaryAdjustment->payrollSalaryTabulator()->first();

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

            $salary_values = $payrollSalaryAdjustment->salary_values ? json_decode($payrollSalaryAdjustment->salary_values) :  null;
            $count = 0;

            foreach ($payrollSalaryTabulatorScales as $payrollSalaryTabulatorScale) {
                if (($payrollSalaryTabulator->payroll_horizontal_salary_scale_id > 0) && ($payrollSalaryTabulator->payroll_vertical_salary_scale_id > 0)) {
                    $horizontalScale = $payrollSalaryTabulatorScale->payrollHorizontalScale;
                    $verticalScale = $payrollSalaryTabulatorScale->payrollVerticalScale;
                    $fields[$horizontalScale->name . '-' . $verticalScale->name] =
                        $salary_values ? $salary_values[$count]->value : $payrollSalaryTabulatorScale->value;
                } elseif ($payrollSalaryTabulator->payroll_horizontal_salary_scale_id > 0) {
                    $horizontalScale = $payrollSalaryTabulatorScale->payrollHorizontalScale;
                    $fields[$horizontalScale->name] =
                        $salary_values ? $salary_values[$count]->value : $payrollSalaryTabulatorScale->value;
                } elseif ($payrollSalaryTabulator->payroll_vertical_salary_scale_id > 0) {
                    $verticalScale = $payrollSalaryTabulatorScale->payrollVerticalScale;
                    $fields[$verticalScale->name] =
                        $salary_values ? $salary_values[$count]->value : $payrollSalaryTabulatorScale->value;
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
        $payrollSalaryAdjustment = PayrollSalaryAdjustment::find($this->payrollSalaryAdjustmentId);
        $payrollSalaryTabulator = $payrollSalaryAdjustment->payrollSalaryTabulator()->first();
        $fields = [];
        if ($payrollSalaryTabulator) {
            if ($payrollSalaryTabulator->payroll_horizontal_salary_scale_id > 0) {
                array_push($fields, 'Nombre');
                $payrollHorizontalSalaryScale = $payrollSalaryTabulator->payrollHorizontalSalaryScale;
                foreach ($payrollHorizontalSalaryScale->payrollScales as $payrollScale) {
                    array_push($fields, $payrollScale->name);
                }
            } elseif ($payrollSalaryTabulator->payroll_vertical_salary_scale_id > 0) {
                array_push($fields, 'Nombre');
                array_push($fields, 'Incidencia');
            }
            return $fields;
        } else {
            return [];
        }
    }
}
