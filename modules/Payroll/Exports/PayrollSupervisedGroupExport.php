<?php

namespace Modules\Payroll\Exports;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

/**
 * @class PayrollSupervisedGroupExport
 * @brief Clase que exporta el listado de registros de grupo de supervisados
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSupervisedGroupExport extends \App\Exports\DataExport implements
    WithHeadings,
    ShouldAutoSize,
    WithCustomStartCell
{
    /**
     * Encabezados de la hoja
     *
     * @var array $headings
     */
    protected $headings;

    /**
     * Colección de datos a exportar
     *
     * @var array $collection
     */
    protected $collection;

    /**
     * Método constructor de la clase
     *
     * @param array $collection Colección de datos
     *
     * @return void
     */
    public function __construct(array $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Genera el listado de registros de la hoja de tiempo
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = [];

        foreach ($this->collection['params'] as $row) {
            $idNumber = explode('-', $row['text']);

            $data[] = [
                'cedula' => $idNumber[0],
            ];
        }

        return collect($data)->values();
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
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    array    Arreglo con las cabeceras de los datos a exportar
     */
    public function headings(): array
    {
        $headings = [
            'cedulas',
        ];

        return $headings;
    }
}
