<?php

namespace Modules\Warehouse\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Illuminate\Support\Collection;
use Modules\Warehouse\Models\WarehouseRequest;

/**
 * @class WarehouseConsumptionReportExport
 * @brief Clase que exporta el reporte de consumo del almacén
 *
 * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
 *
 * @license
 * [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WarehouseConsumptionReportExport implements
    FromCollection,
    ShouldAutoSize,
    WithHeadings,
    WithMapping,
    WithStrictNullComparison
{
    use Exportable;

    protected bool $isSingleWarehouse;

    /**
     * @method  __construct
     *
     * Método constructor de la clase
     *
     * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @param  array $filters Arreglo con los filtros a aplicar
     */
    public function __construct(protected array $filters)
    {
        $this->isSingleWarehouse = !is_null($this->filters['warehouse_id']);
    }

    /**
     * Genera el listado de consumo de productos
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection(): Collection
    {
        $requests = WarehouseRequest::query()
            ->with('warehouseInventoryProductRequests.warehouseInventoryProduct.warehouseInstitutionWarehouse')
            ->filterDataToConsumptionReport($this->filters)
            ->get();

        return $this->getFormatedData($requests);
    }

    /**
     * Encabezados de la hoja
     *
     * @return array
     */
    public function headings(): array
    {
        $headings = [
            'Producto',
            'Cantidad consumida',
            'Unidad de medida',
        ];

        if (!$this->isSingleWarehouse) {
            $headings[] = 'Almacén';
        }

        return $headings;
    }

    /**
     * Mapeo de los datos de la hoja a exportar
     *
     * @param  array $row datos de la fila
     *
     * @return array
     */
    public function map($row): array
    {
        $mappedData = [
            $row['product_name'],
            $row['consumed_amount'],
            $row['unit_of_measure'],
        ];

        if (!$this->isSingleWarehouse) {
            $mappedData[] = $row['warehouse'];
        }

        return $mappedData;
    }

    /**
     * @param Collection $records
     * @return Collection
     */
    private function getFormatedData($records)
    {
        $consumptions = [];
        $hasDesiredProducts = count($this->filters['product_ids']) > 0;

        foreach ($records as $record) {
            foreach ($record->warehouseInventoryProductRequests as $productRequest) {
                $warehouseProduct = $productRequest->warehouseInventoryProduct?->warehouseProduct;

                if (!$warehouseProduct) {
                    continue;
                }

                $productId = $warehouseProduct->id;
                $departmentId = $record->department_id;
                $institutionId = $record->institution_id;
                $warehouseId = $productRequest->warehouseInventoryProduct?->warehouseInstitutionWarehouse?->warehouse_id;

                $isFilteredByWarehouse = !is_null($this->filters['warehouse_id']);
                $matchesInstitution = ($institutionId == $this->filters['institution_id']);
                $matchesDepartment = ($departmentId == $this->filters['department_id']);
                $matchesWarehouse = (!$isFilteredByWarehouse || $warehouseId == $this->filters['warehouse_id']);
                $isDesiredProduct = (!$hasDesiredProducts || in_array($productId, $this->filters['warehouse_product_ids']));

                if ($matchesInstitution && $matchesDepartment && $matchesWarehouse && $isDesiredProduct) {
                    $key = $isFilteredByWarehouse ?
                        $productId
                        : $productId . '-' . $warehouseId;

                    if (!isset($consumptions[$key])) {
                        $consumptions[$key] = [
                            'id'                => $productId,
                            'product_name'      => $warehouseProduct->name,
                            'consumed_amount'   => 0,
                            'unit_of_measure'   => $warehouseProduct->measurementUnit->name ?? '',
                            'warehouse'         => $record->warehouse->name,
                        ];
                    }

                    $consumptions[$key]['consumed_amount'] += $productRequest->quantity;
                }
            }
        }

        return collect($consumptions);
    }
}
