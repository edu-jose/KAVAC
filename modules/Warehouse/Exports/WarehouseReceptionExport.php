<?php

namespace Modules\Warehouse\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Modules\Warehouse\Models\Warehouse;
use Modules\Warehouse\Models\WarehouseProduct;
use Modules\Warehouse\Models\WarehouseInstitutionWarehouse;
use App\Models\Currency;
use App\Models\Institution;

/**
 * @class WarehouseReceptionExport
 * @brief Gestiona la exportaación de archivo xlsx con los datos de los productos para carga en masa
 *
 * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve miguelnarvaez31@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WarehouseReceptionExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $warehouse;
    protected $institution;
    protected $products;
    protected $currencies;

    public function __construct(Warehouse $warehouse, Institution $institution)
    {
        $this->warehouse = $warehouse;
        $this->institution = $institution;

        // Obtener todos los productos disponibles (no solo los que tienen inventario)
        $this->products = WarehouseProduct::with('measurementUnit')
            ->orderBy('name')
            ->get();

        // Obtener todas las monedas
        $this->currencies = Currency::all();

        //dd($this->currencies);
    }

    public function collection()
    {
        // Creamos una colección con una fila vacía para que se generen las validaciones
        return collect([['']]);
    }

    public function headings(): array
    {
        return [
            'Nombre del insumo',
            'Cantidad',
            'Valor unitario',
            'Moneda',
            'Lote',
            'Fecha de vencimiento',
            'Mínimo',
            'Máximo'
        ];
    }

    public function map($row): array
    {
        return []; // Mapeo vacío para plantilla
    }


    public function styles(Worksheet $sheet)
    {
        // Configurar validación para productos (columna A)
        if ($this->products->isNotEmpty()) {
            $productsList = $this->products->pluck('name')->toArray();

            // Crear una nueva hoja para la lista de productos
            $productsSheet = $sheet->getParent()->createSheet();
            $productsSheet->setTitle('Productos');
            $productsSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

            // Escribir la lista de productos en la hoja oculta
            foreach ($productsList as $index => $product) {
                $productsSheet->setCellValue('A' . ($index + 1), $product);
            }

            // Definir un rango con nombre para la lista de productos
            $spreadsheet = $sheet->getParent();
            $spreadsheet->addNamedRange(
                new \PhpOffice\PhpSpreadsheet\NamedRange(
                    'ProductosLista',
                    $productsSheet,
                    'A1:A' . count($productsList)
                ) // Este paréntesis estaba faltando
            );

            // Aplicar validación desde la fila 2 hasta la 1000
            for ($i = 2; $i <= 10000; $i++) {
                $validation = $sheet->getCell('A' . $i)->getDataValidation();
                $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
                $validation->setAllowBlank(true);
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setShowDropDown(true);
                $validation->setErrorTitle('Error en entrada');
                $validation->setError('El valor no está en la lista');
                $validation->setPromptTitle('Seleccione un insumo');
                $validation->setPrompt('Por favor seleccione un insumo de la lista');

                // Referenciar el rango con nombre
                $validation->setFormula1('ProductosLista');
            }

            // Configurar fórmula para mostrar unidad de medida automáticamente
            //$sheet->setCellValue('B2', '=IF(A2="","",VLOOKUP(A2,ProductsTable,2,FALSE))');
        }

        // Configurar validación para monedas (columna E)
        if ($this->currencies->isNotEmpty()) {
            $currencyList = $this->currencies->pluck('name')->toArray();

            for ($i = 2; $i <= 10000; $i++) {
                $validation = $sheet->getCell('D' . $i)->getDataValidation();
                $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
                $validation->setAllowBlank(true);
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setShowDropDown(true);
                $validation->setErrorTitle('Error en entrada');
                $validation->setError('El valor no está en la lista');
                $validation->setPromptTitle('Seleccione una moneda');
                $validation->setPrompt('Por favor seleccione una moneda de la lista');
                $validation->setFormula1('"' . implode(',', $currencyList) . '"');
            }
        }

        // Establecer formatos
        $sheet->getStyle('F2:F10000')->getNumberFormat()->setFormatCode('dd/mm/yyyy'); // Fechas
        $sheet->getStyle('B2:B10000')->getNumberFormat()->setFormatCode('0.00'); // Cantidad y valor
        $sheet->getStyle('G2:G10000')->getNumberFormat()->setFormatCode('0.00'); // Mínimo y máximo

        // Estilo para la fila de encabezados
        return [
           1 => ['font' => ['bold' => true]],
        ];
    }
}
