<?php

namespace Modules\Budget\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

/**
 * @class BudgetSubSpecificFormulationXlsExport
 * @brief Clase que exporta el listado de ajustes salariales
 *
 * @author fjescala
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetSubSpecificFormulationXlsExport implements
    FromArray,
    ShouldQueue,
    WithHeadings,
    ShouldAutoSize,
    WithStrictNullComparison
{
    use Exportable;



    /**
     * Clase del modelo del cual exportar datos
     *
     * @var string|object|BudgetSubSpecificFormulation $model
     */
    protected $array;

    public function __construct(array $array)
    {
        $this->array = $array;
    }


    /**
     * Encabezados de la hoja
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'codigo',
            'total_real',
            'total_estimado',
            'total_anho',
            'ene',
            'feb',
            'mar',
            'abr',
            'may',
            'jun',
            'jul',
            'ago',
            'sep',
            'oct',
            'nov',
            'dic'
        ];
    }

    public function array(): array
    {
        return $this->array;
    }
}
