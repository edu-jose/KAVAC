<?php

namespace Modules\Warehouse\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Purchase\Models\PurchaseProduct;
use Nwidart\Modules\Facades\Module;

class WarehousePurchaseProductsExport implements FromCollection, WithTitle, WithEvents
{
    public function collection()
    {
        if (Module::has('Purchase') && Module::isEnabled('Purchase')) {
            return \Modules\Purchase\Models\PurchaseProduct::select('code', 'name')->cursor()->map(function ($product) {
                return ["{$product->code} - {$product->name}"];
            });
        }
    }

    public function title(): string
    {
        return 'Catálogo SNC';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Establece el ancho de columna (aprox. 120 caracteres)
                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(120);
            },
        ];
    }
}
