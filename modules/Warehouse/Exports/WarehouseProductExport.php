<?php

namespace Modules\Warehouse\Exports;

use App\Models\MeasurementUnit;
use App\Models\HistoryTax;
use Modules\Warehouse\Models\WarehouseProduct;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class WarehouseProductExport extends \App\Exports\DataExport implements
    WithHeadings,
    ShouldAutoSize,
    WithMapping,
    WithEvents,
    WithCustomStartCell
{
    /**
     * Metodo para obtener la colección de datos a exportar
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Cargar la relación measurementUnit con los productos
        return WarehouseProduct::with('measurementUnit')->get();
    }

    /**
     * Establece la celda en la que se debe comenzar a escribir el archivo a exportar
     *
     * @return string Celda de inicio de escritura
     */
    public function startCell(): string
    {
        return 'A1';
    }

    /**
     * Establece las cabeceras de los datos en el archivo a exportar
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return array Arreglo con las cabeceras de los datos a exportar
     */
    public function headings(): array
    {
        return [
            'Nombre del insumo',
            'Descripción del insumo',
            'Nombre de la unidad de medida',
        ];
    }

    /**
     * Establece las columnas que van a ser exportadas
     *
     * @param object $warehouseProduct Objeto con las propiedades del modelo a exportar
     *
     * @param     object    $warehouseProduct    Objeto con las propiedades del modelo a exportar
     * @param object $warehouseProduct Objeto con las propiedades del modelo a exportar
     *
     * @return array Arreglo con los campos estrictamente a ser exportados
     */
    public function map($warehouseProduct): array
    {
        return [
            $warehouseProduct->name,
            htmlspecialchars_decode(strip_tags($warehouseProduct->description)),
            $warehouseProduct->measurementUnit ? $warehouseProduct->measurementUnit->name : 'N/A',
        ];
    }

    /**
     * Registro de eventos al exportar datos
     *
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                // Configuración de validación para unidades de medida
                $validation = new DataValidation();
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                $validation->setAllowBlank(false);
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setShowDropDown(true);
                $validation->setErrorTitle('Error en datos');
                $validation->setError('Debe seleccionar un dato de la lista');
                $validation->setPrompt('Seleccione un elemento de la lista');

                $records = $this->getArraysSelect();

                // Validación para unidad de medida (columna C)
                $validation->setPromptTitle('Unidad de medida');
                $validation->setFormula1(json_encode($records['measurementUnit'], JSON_UNESCAPED_UNICODE));
                $sheet->setDataValidation('C2:C100000', clone $validation);

                // Estilos para la cabecera
                $styleArray = [
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ];

                $sheet->getDelegate()->getStyle('A1:D1')->applyFromArray($styleArray);
            },
        ];
    }

    /**
     * Obtiene los valores de los selectores
     *
     * @return array
     */
    public function getArraysSelect(): array
    {
        // Obtener todas las unidades de medida con sus nombres y acrónimos
        $measurementUnits = MeasurementUnit::all();

        $measurementUnitNames = $measurementUnits->pluck('name')->toArray();

        // Formatear para Excel (sin comas ni caracteres especiales)
        $measurementUnitFormated = implode(',', array_map(function ($item) {
            return str_replace([',', '.', '-'], '', $item);
        }, $measurementUnitNames));

        return [
            'measurementUnit' => $measurementUnitFormated,
        ];
    }
}
