<?php

namespace Modules\Payroll\Exports\Payroll;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithTitle;
use Modules\Payroll\Models\Payroll;
use Modules\Payroll\Models\PayrollResetParameter;

/**
 * @class PayrollParameterExport
 * @brief Clase que exporta el listado de los parametros de nomina
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollParameterExport implements
    FromCollection,
    WithHeadings,
    WithTitle,
    ShouldAutoSize,
    WithCustomStartCell
{
    /**
     * Encabezados de la hoja
     *
     * @var array $headers
     */
    protected $headers;

    /**
     * Colección de datos a exportar
     *
     * @var array $records
     */
    protected $records;

    /**
     * Identificador de la nómina
     *
     * @var int $payrollId
     */
    protected $payrollId;

    /**
     * Identificador del concepto
     *
     * @var int $conceptId
     */
    protected $conceptId;

    /**
     * Titulo de la hoja
     *
     * @var string $title
     */
    protected $title;

    /**
     * Método constructor de la clase
     *
     * @param array $collection Colección de datos
     *
     * @return void
     */
    public function __construct(array $data, string $title)
    {
        $this->headers = $data['headers'];
        $this->records = $data['records'];
        $this->payrollId = $data['payrollId'];
        $this->conceptId = $data['conceptId'];
        $this->title = $title;
    }

    public function title(): string
    {
        return $this->title;
    }

    /**
     * Genera el listado de registros de los parametros de nomina
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = [];

        $payrollId = $this->payrollId;
        $conceptId = $this->conceptId;
        $staffs = $this->records['staffs'];
        $globalParams = $this->records['parameters']->filter(fn ($item) => (
            ($item['type'] == 'global_value' || $item['name'] == 'Numero de lunes del mes')
        ));

        $parameters = PayrollResetParameter::toBase()
            ->where('payroll_id', $payrollId)
            ->where(function ($query) use ($conceptId) {
                return $query->when($conceptId, function ($query) use ($conceptId) {
                    // Filtrar por concepto si se proporciona, o incluir los que no tienen concepto
                    return $query->where('payroll_concept_id', $conceptId)
                        ->orWhere('payroll_concept_id', null);
                });
            })
            ->get()
            ->groupBy('payroll_staff_id');

        foreach ($staffs as $index => $staff) {
            $row = [
                'N°' => $index + 1,
                'Cédula' => $staff['id_number'],
                'Nombre' => $staff['name'],
            ];

            foreach ($this->headers as $paramName) {
                $value = $globalParams->firstWhere('name', $paramName)['value'] ?? 0;
                $row[$paramName] = isset($parameters[$staff['id']])
                    ? $parameters[$staff['id']]->firstWhere('name', $paramName)->value ?? $value
                    : $value;
            }

            $data[] = $row;
        }

        return collect($data);
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
     *
     * @return    array    Arreglo con las cabeceras de los datos a exportar
     */
    public function headings(): array
    {
        return array_merge(['N°', 'Cédula', 'Nombre'], $this->headers);
    }
}
