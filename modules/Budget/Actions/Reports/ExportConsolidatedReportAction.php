<?php

declare(strict_types=1);

namespace Modules\Budget\Actions\Reports;

use App\Exports\MultiSheetExport;
use App\Models\Institution;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Budget\Exports\BudgetConsolidatedSheetExport;

final class ExportConsolidatedReportAction
{
    public function __construct(
        private MultiSheetExport $worksheet,
    ) {
    }

    public function invoke(
        array $data,
        string $monthFrom,
        string $monthTo,
        string $date,
        string $file = 'data'
    ) {
        $institution = auth()->user()->institution ?? Institution::query()
            ->toBase()
            ->select(['onapre_code', 'acronym', 'name'])
            ->where('active', true)
            ->where('default', true)
            ->first();
        $months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $headers = [
            ['name' => 'PARTIDAS ‐ SUBPARTIDAS (2)'],
            ['name' => 'DENOMINACIÓN (3)'],
            ['name' => 'PRESUPUESTO APROBADO (4)'],
            ['months' => [
                ['name' => 'MODIFICACIONES DEL MES (5)'],
                ['name' => 'PRESUPUESTO MODIFICADO (6)'],
                ['name' => 'PROGRAMADO MENSUAL (7)'],
                ['name' => 'EJECUCIÓN MENSUAL (8)', 'childs' => [
                    ['name' => 'COMPROMISO'],
                    ['name' => 'CAUSADO'],
                    ['name' => 'PAGADO'],
                ]],
                ['name' => 'VARIACIÓN ABSOLUTA CAUSADO Vs PROGRAMADO (9)'],
                ['name' => 'PROGRAMADO ACUMULADO (10)'],
                ['name' => 'ACUMULADO (11)', 'childs' => [
                    ['name' => 'COMPROMETIDO ACUMULADO'],
                    ['name' => 'CAUSADO ACUMULADO'],
                    ['name' => 'PAGADO ACUMULADO'],
                ]],
                ['name' => 'VARIACIÓN ABSOLUTA ACUMULADO CAUSADO Vs PROGRAMADO (12)'],
                ['name' => 'DISPONIBILIDAD PRESUPUESTARIA COMPROMISO (13)'],
                ['name' => 'DISPONIBILIDAD PRESUPUESTARIA CAUSADO (14)'],
            ]],
        ];
        $sheets = [];
        foreach ($data as $key => $value) {
            $sheets[$key] =  new BudgetConsolidatedSheetExport(
                [
                    'headers' => $headers,
                    'months' => $months,
                    'date' => $date,
                    'monthFrom' => $monthFrom,
                    'monthTo' => $monthTo,
                    'institution' => $institution,
                    'budgetCategory' => $key,
                    'records' => $value,
                ],
                $key
            );
        }
        $this->worksheet->setSheets($sheets);

        return Excel::download($this->worksheet, $file . '.xlsx');
    }
}
