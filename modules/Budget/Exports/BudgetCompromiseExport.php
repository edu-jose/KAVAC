<?php

namespace Modules\Budget\Exports;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Modules\Payroll\Models\PayrollSalaryAdjustment;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Modules\Budget\Models\BudgetCompromise;

/**
 * @class BudgetCompromiseExport
 * @brief Clase que exporta el listado de compromisos
 *
 * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetCompromiseExport implements
    FromCollection,
    ShouldQueue,
    WithHeadings,
    ShouldAutoSize,
    WithMapping,
    WithStrictNullComparison
{
    use Exportable;

    private $data;
    private $currency;
    private $total;

    /**
     * Método constructor de la clase
     *
     * @return void
     */
    public function __construct($data, $currency)
    {
        $this->data = $data;
        $this->currency = $currency;
        $this->total = 0;

        foreach ($data as $record) {
            if ($record->sourceable_type === 'Modules\Purchase\Models\PurchaseDirectHire') {
                foreach ($record->budgetCompromiseDetails as $budgetCompromiseDetail) {
                    $this->total += \Modules\Budget\Facades\CurrencyConverter::convert(
                        $budgetCompromiseDetail->amount,
                        $budgetCompromiseDetail->created_at,
                        $budgetCompromiseDetail['budgetSubSpecificFormulation']['currency'],
                        $currency
                    );
                    //$this->total += $budgetCompromiseDetail->amount;
                }
            } else {
                foreach ($record->budgetCompromiseDetails as $budgetCompromiseDetail) {
                    $this->total += \Modules\Budget\Facades\CurrencyConverter::convert(
                        $budgetCompromiseDetail->total,
                        $budgetCompromiseDetail->created_at,
                        $budgetCompromiseDetail['budgetSubSpecificFormulation']['currency'],
                        $currency
                    );
                    // $this->total += $budgetCompromiseDetail->total;
                }
            }
        }
    }

    /**
     * Genera el listado de compromisos
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function collection()
    {
        $this->data->push($this->total);
        return $this->data;
    }

    /**
     * Encabezados de la hoja
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Fecha de generación',
            'Código del compromiso',
            'Documento origen',
            'Código de la Acción Específica',
            'Descripción',
            'Estatus',
            'Monto',
        ];
    }

    /**
     * Mapeo de los datos de la hoja a exportar
     *
     * @param array|object $row datos de la fila
     *
     * @return array
     */
    public function map($row): array
    {
        $specificActionCode = '';
        $totalByCompromise = 0;
        $commitStatus = '';

        if (is_float($row)) {
            return [
                '',
                '',
                '',
                '',
                '',
                'Total:',
                number_format(
                    $this->total,
                    2,
                    ",",
                    "."
                ),
            ];
        }

        foreach ($row?->budgetCompromiseDetails as $budgetCompromiseDetail) {
            $specificActionCode = $budgetCompromiseDetail?->budgetSubSpecificFormulation?->specificAction?->code;
            break;
        }

        if ($row->sourceable_type === 'Modules\Purchase\Models\PurchaseDirectHire') {
            foreach ($row->budgetCompromiseDetails as $budgetCompromiseDetail) {
                $totalByCompromise += $budgetCompromiseDetail->amount;
            }
        } else {
            foreach ($row->budgetCompromiseDetails as $budgetCompromiseDetail) {
                $totalByCompromise += $budgetCompromiseDetail->total;
            }
        }


        if ($row->status === 'CAU') {
            $commitStatus = 'Causado(a)';
        } elseif ($row->status === 'PA') {
            $commitStatus = 'Pagado(a)';
        } elseif ($row->status === 'PE') {
            $commitStatus = 'Pendiente';
        } else {
            $commitStatus = $row?->documentStatus?->name;
        }

        return [
            date('d-m-Y', strtotime($row->compromised_at)),
            $row->code,
            $row->document_number,
            $specificActionCode,
            preg_replace(
                '/&[a-zA-Z0-9#]+;/',
                '',
                strip_tags($row->description)
            ),
            $commitStatus,
            number_format(
                \Modules\Budget\Facades\CurrencyConverter::convert(
                    $totalByCompromise,
                    $budgetCompromiseDetail->created_at,
                    $budgetCompromiseDetail['budgetSubSpecificFormulation']['currency'],
                    $this->currency
                ),
                $row['budgetCompromiseDetails'][0]['budgetSubSpecificFormulation']['currency']['decimal_places'],
            ),
        ];
    }
}
