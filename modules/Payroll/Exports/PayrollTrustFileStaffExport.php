<?php

namespace Modules\Payroll\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

/**
 * @class PayrollTrustFileStaffExport
 * @brief Clase que exporta el TXT de fideicomiso de banco BNC
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollTrustFileStaffExport implements FromArray, ShouldAutoSize, WithCustomCsvSettings
{
    use Exportable;

    /**
     * Método constructor de la clase
     *
     * @return void
     */
    public function __construct(
        protected array $data = [],
    ) {
    }

    /**
     * Establece las columnas que van a ser exportadas
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
     *
     * @return    array                       Arreglo con los campos estrictamente a ser exportados
     */
    public function array(): array
    {
        return $this->data;
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => "\t", // Usar tabulador como delimitador
            'enclosure' => "",   // Dejar el enclosure vacío para no agregar comillas
        ];
    }
}
