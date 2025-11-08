<?php

namespace Modules\Payroll\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Payroll\Models\PayrollSavingsFund;
use PhpOffice\PhpSpreadsheet\Style\Protection;

/**
 * @class PayrollSavingsFundExport
 * @brief Clase que exporta el listado de registros de la planilla ARI
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSavingsFundExport implements FromCollection, ShouldQueue, WithHeadings, ShouldAutoSize, WithMapping, WithEvents
{
    use Exportable;

    /**
     * Obtiene el listado de registros de la planilla ARI
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function collection()
    {
        $columns = ['payroll_staff_id', 'percetage', 'from_date', 'to_date', 'id'];

        return PayrollSavingsFund::with('payrollStaff')->get($columns);
    }

    /**
     * Encabezados de las columnas de la hoja a exportar
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Cédula',
            'Porcentaje',
            'Desde',
            'Hasta',
            'Código'
        ];
    }

    /**
     * Mapeo de los datos de la hoja a exportar
     *
     * @param object|array $row datos de la fila
     *
     * @return array
     */
    public function map($row): array
    {
        $code = 'SVF-' . $row->id;

        return [
            $row->payrollStaff->id_number,
            $row->percetage * 100,
            $row->from_date,
            $row->to_date,
            $code
        ];
    }

    /**
     * Registra los eventos para la hoja de cálculo
     *
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Activar protección de hoja
                $sheet->getProtection()->setSheet(true);
                $sheet->getProtection()->setPassword('pwd');

                // Desbloquear toda la hoja (hasta el límite de Excel)
                $sheet->getStyle('A1:Z1048576')
                      ->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);

                // Bloquear toda la columna E (Código)
                $sheet->getStyle('E1:E1048576')
                      ->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);
            },
        ];
    }
}
