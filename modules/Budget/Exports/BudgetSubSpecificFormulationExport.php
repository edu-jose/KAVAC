<?php

namespace Modules\Budget\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Budget\Models\BudgetSubSpecificFormulation;

/**
 * @class BudgetSubSpecificFormulationExport
 * @brief Exporta datos de la formulación de presupuesto
 *
 * Gestiona la exportación de datos de la formulación de presupuesto
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetSubSpecificFormulationExport implements WithHeadings, ShouldAutoSize, WithMapping, FromCollection, WithEvents
{
    use Exportable;

    /**
     * Identificador de la formulación
     *
     * @var integer $budgetFormulationId
     */
    protected $budgetFormulationId;

    /**
     * Moneda del presupuesto
     *
     * @var object $currency
     */
    protected $currency;

    /**
     * Clase del modelo del cual exportar datos
     *
     * @var string|object|BudgetSubSpecificFormulation $model
     */
    protected $model;

    /**
     * Historico de conversiones
     *
     * @var array
     */
    protected $conversion_history;

    public function __construct($model, $currency)
    {
        $this->model = $model;
        $this->currency = $currency;
    }

    /**
     * Establece el identificador del registro de nómina
     *
     * @param integer $budgetFormulationId Identificador único del tabuldor salarial
     *
     * @return void
     */
    public function setbudgetFormulationId(int $budgetFormulationId)
    {
        $this->budgetFormulationId = $budgetFormulationId;
        $this->model = BudgetSubSpecificFormulation::query()->where('id', $budgetFormulationId)->get();
    }

    /**
     * Obtiene la colección de registros a exportar
     *
     * @return    \Illuminate\Support\Collection
     */
    public function collection()
    {
        $formulation = BudgetSubSpecificFormulation::query()->with([
            'currency',
            'institution'
        ])->where('id', $this->budgetFormulationId)
        ->get();

        $this->conversion_history = \Modules\Budget\Facades\CurrencyHistory::getExchangeRateHistory($formulation[0]->date, $formulation[0]->date, $formulation[0]->currency, $this->currency);

        return $formulation;
    }

    /**
     * Establece las cabeceras de los datos en el archivo a exportar
     *
     * @author    Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @return    array    Arreglo con las cabeceras de los datos a exportar
     */
    public function headings(): array
    {
        $headings = [[
            'Fecha de generación'
            ]
        ];
        $headings[] = [
            'Presupuesto asignado'
        ];
        $headings[] = [
            'Institución'
        ];
        $headings[] = [
            'Moneda'
        ];
        $headings[] = [
            'Presupuesto'
        ];
        $headings[] = [
            'Acción centralizada'
        ];
        $headings[] = [
            'Acción específica'
        ];
        $headings[] = [
            'Fuente de financiamiento'
        ];
        $headings[] = [
            'Tipo de financiamiento'
        ];
        $headings[] = [
            'Monto del financiamiento'
        ];
        $headings[] = [
            'Total formulado'
        ];
        $headings[] = [' ', ' ', ' '];
        $headings[] = ['Código', 'Denominación', 'Total año'];


        return $headings;
    }

    /**
     * Mapea los registros a las columnas del archivo a exportar
     *
     * @param mixed $row Datos del registro a exportar
     *
     * @return array
     */
    public function map($row): array
    {
        $array = [];

        foreach ($row->accountOpens as $accountOpen) {
            $code = $accountOpen?->budgetAccount?->code;
            $denomination = $accountOpen?->budgetAccount?->denomination;
            $total_year = $this->convertCurrency(
                $this->conversion_history,
                $accountOpen->total_year_amount,
                $row->date,
                $this->currency->decimal_places,
                ",",
                "."
            );

            $array[] = [$code, $denomination, $total_year];
        }

        $total_formulated = $this->convertCurrency(
            $this->conversion_history,
            $row->total_formulated,
            $row->date,
            $this->currency->decimal_places,
            ",",
            "."
        );

        $array[] = ['Total Formulado', '', $total_formulated];

        array_multisort(
            array_column($array, 0),
            SORT_ASC,
            array_column($array, 1),
            SORT_ASC,
            array_column($array, 2),
            SORT_ASC,
            $array
        );

        return $array;
    }

    /**
     * Establece los eventos de la exportación de datos
     *
     * @return array
     */
    public function registerEvents(): array
    {
        $records = $this->model;

        $data = [
            AfterSheet::class => function (AfterSheet $event) use ($records) {
                $sheet = $event->sheet;

                $sheet->setCellValue('B1', $records[0]->date ? date("d/m/Y", strtotime($records[0]->date)) : 'Sin fecha asignada');
                $sheet->setCellValue('B2', ($records[0]?->assigned || $records[0]?->assigned === '1') ? 'Sí' : 'No');
                $sheet->setCellValue('B3', $records[0]?->specificAction ? $records[0]?->specificAction?->institution : 'N/A');
                $sheet->setCellValue('B4', $this->currency ? "{$this->currency->symbol} - {$this->currency->name}" : 'N/A');
                $sheet->setCellValue('B5', $records[0]?->year ? $records[0]?->year : 'N/A');
                $sheet->setCellValue('B6', $records[0]?->specificAction->specificable->code . ' - ' .
                    $records[0]?->specificAction?->specificable?->name ??
                    'N/A');
                $sheet->setCellValue('B7', $records[0]?->specificAction?->code . ' - ' .
                    $records[0]?->specificAction?->name ??
                    'N/A');
                $sheet->setCellValue('B8', $records[0]?->budgetFinancementType ?
                    $records[0]?->budgetFinancementType?->name :
                    'N/A');
                $sheet->setCellValue('B9', $records[0]?->budgetFinancementSource ?
                    $records[0]?->budgetFinancementSource?->name :
                    'N/A');
                $sheet->setCellValue('B10', $this->currency->symbol . ' ' .
                    $this->convertCurrency(
                        $this->conversion_history,
                        $records[0]->financement_amount,
                        $records[0]->date,
                        $this->currency->decimal_places,
                        ',',
                        '.'
                    ));

                $sheet->setCellValue('B11', $this->currency->symbol . ' ' .
                    $this->convertCurrency(
                        $this->conversion_history,
                        $records[0]->total_formulated,
                        $records[0]->date,
                        $this->currency->decimal_places,
                        ',',
                        '.'
                    ));

                $sheet->getStyle('B5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

                $sheet->getStyle('A1')->getFont()->setBold(true);
                $sheet->getStyle('A2')->getFont()->setBold(true);
                $sheet->getStyle('A3')->getFont()->setBold(true);
                $sheet->getStyle('A4')->getFont()->setBold(true);
                $sheet->getStyle('A5')->getFont()->setBold(true);
                $sheet->getStyle('A6')->getFont()->setBold(true);
                $sheet->getStyle('A7')->getFont()->setBold(true);
                $sheet->getStyle('A8')->getFont()->setBold(true);
                $sheet->getStyle('A9')->getFont()->setBold(true);
                $sheet->getStyle('A10')->getFont()->setBold(true);
                $sheet->getStyle('A11')->getFont()->setBold(true);
                $sheet->getStyle('A13')->getFont()->setBold(true);
                $sheet->getStyle('B13')->getFont()->setBold(true);
                $sheet->getStyle('C13')->getFont()->setBold(true);

                $highestRow = $sheet->getHighestRow();

                for ($row = 1; $row <= $highestRow; $row++) {
                    $cellValue = $sheet->getCellByColumnAndRow(1, $row)->getValue();
                    if ($cellValue === 'Total Formulado') {
                        $sheet->setCellValueByColumnAndRow(
                            1,
                            $row,
                            $cellValue . ' ' . $this->currency->symbol
                        );
                        $sheet->getStyleByColumnAndRow(1, $row)->getFont()->setBold(true);
                        $sheet->getStyleByColumnAndRow(3, $row)->getFont()->setBold(true);
                    }
                }
            },
        ];

        return $data;
    }

    /**
     * Aplica una conversión de moneda
     *
     * @param array $conversion_history Historial de tasas de cambio
     * @param float $amount Monto del financiamiento
     * @param string $date Fecha del financiamiento
     * @param int $decimal_places Cantidad de decimales
     * @param string $separator Separador de miles
     * @param string $decimal_separator Separador de decimales
     *
     * @return string Monto convertido
     */
    private function convertCurrency(
        array $conversion_history,
        float $amount,
        string $date,
        int $decimal_places,
        string $separator,
        string $decimal_separator
    ): string {
        return number_format(($conversion_history[$date] * $amount), $decimal_places, $decimal_separator, $separator);
    }
}
