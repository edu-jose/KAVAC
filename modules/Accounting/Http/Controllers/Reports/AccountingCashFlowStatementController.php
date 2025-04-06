<?php

namespace Modules\Accounting\Http\Controllers\Reports;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use DateTime;
use Carbon\Carbon;
use App\Models\Parameter;
use Illuminate\Http\JsonResponse;
use App\Repositories\ReportRepository;
use Modules\Accounting\Models\Currency;
use Modules\Accounting\Models\Institution;
use Modules\Accounting\Models\ExchangeRate;
use Modules\Accounting\Models\Profile;
use Modules\Accounting\Models\Setting;
use Modules\Accounting\Models\AccountingEntry;
use Modules\Accounting\Models\AccountingAccount;
use Modules\Accounting\Models\AccountingReportHistory;
use Modules\Accounting\Http\Controllers\AccountingBaseController;
use Modules\Accounting\Exports\AccountingCashflowSheetExport;
use Maatwebsite\Excel\Facades\Excel;

/**
 * @class AccountingCashFlowStatementController
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author juan rosas <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AccountingCashFlowStatementController extends AccountingBaseController
{
    /**
     * Define la configuración de la clase
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:accounting.report.cashflowstatement', [
            'only' => ['pdf', 'pdfVue'],
        ]);
    }

    /**
     * Obtiene el identificador de la moneda
     *
     * @return string|int
     */
    public function getCurrencyId()
    {
        return $this->currency->id;
    }

    /**
     * Obtiene la moneda
     *
     * @return Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Establece la moneda
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  Currency $currency
     *
     * @return void
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * Genera el reporte en pdf de balance general
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  integer $report id de reporte y su información
     * @param  boolean $xml si se requiere el reporte en xml
     *
     * @return mixed
     */
    public function pdf($report, $xml = false)
    {
        $stateOfResult = $this->calculateStateOfResult($report);
        $balanceSheetResult = $this->balanceSheetResult($report);

        $report = AccountingReportHistory::with('currency')->find($report);

        // Validar acceso para el registro
        if (!auth()->user()->isAdmin()) {
            $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();
            if ($report && $report->queryAccess($user_profile['institution']['id'])) {
                return view('errors.403');
            }
        }

        $endDate = explode('/', $report->url)[1];
        $level = explode('/', $report->url)[2];
        $zero = explode('/', $report->url)[3] == 'true' ? true : false;
        $date = count(explode('-', $endDate)) > 1 ?
        explode('-', $endDate)[0] . '-' . explode('-', $endDate)[1] :
        explode('-', $endDate)[0];

        $this->setCurrency($report->currency);

        $institution = null;

        $is_admin = auth()->user()->isAdmin();

        if ($is_admin) {
            $institution = Institution::where('default', true)->first();
        } else {
            $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();
            $institution = $user_profile['institution'] ?? null;
        }
        $institution_id = $institution->id;

        /* Configuración general de la apliación */
        $setting = Setting::all()->first();

        /* Base para generar el pdf */
        $pdf = new ReportRepository();

        if (count(explode('-', $endDate)) > 1) {
            $lastOfThePreviousMonth = date(
                'd',
                (mktime(0, 0, 0, explode('-', $date)[1], 1, explode('-', $date)[0]) - 1)
            );
            $last = $lastOfThePreviousMonth . '/' . (explode('-', $date)[1] - 1) . '/' . explode('-', $date)[0];
        } else {
            $last = '';
        }

        $institution = Institution::find($institution_id);

        $currency = $this->getCurrency();

        $balance = [];
        $operationalActivity = [];
        $investmentActivity = [];
        $financingActivity = [];
        $beginningBalance = 0;

        foreach ($balanceSheetResult['records'] as $bal) {
            foreach ($bal as $item) {
                $item['lastMonthBalance'] = $this->roundTotal($item['lastMonthBalance'], $currency->decimal_places);

                $item['balance'] = $this->roundTotal($item['balance'], $currency->decimal_places);

                if (str_contains($item['code'], '1.1.1.01')) {
                    $item['variation'] = '---';
                    $item['efe'] = '---';
                    array_push($balance, $item);

                    if (str_contains($item['code'], '1.1.1.01.00.00.000')) {
                        $beginningBalance = $item['lastMonthBalance'];
                    }
                    continue;
                }

                $item['variation'] = $this->roundTotal($this->calculateVariation($item['lastMonthBalance'], $item['balance']), $currency->decimal_places);

                if (explode('.', $item['code'])[0] == '1') {
                    $item['efe'] = -$item['variation'];
                } else {
                    $item['efe'] = $item['variation'];
                }

                array_push($balance, $item);

                if (array_key_exists('accountingTypeActivity', $item)) {
                    $it = [
                        'code' => $item['code'],
                        'denomination' => $item['denomination'],
                        'balance' => $item['efe'],
                    ];

                    if ($item['accountingTypeActivity'] == 'actividad-operativa') {
                        array_push($operationalActivity, $it);
                    } elseif ($item['accountingTypeActivity'] == 'actividad-de-inversion') {
                        array_push($investmentActivity, $it);
                    } elseif ($item['accountingTypeActivity'] == 'actividad-de-financiamiento') {
                        array_push($financingActivity, $it);
                    }
                }
            }
        }
        foreach ($stateOfResult['records'] as $item) {
            if (array_key_exists('accountingTypeActivity', $item)) {
                $it = [
                    'code' => $item['code'],
                    'denomination' => $item['denomination'],
                    'balance' => $item['balance'],
                ];

                if ($item['accountingTypeActivity'] == 'actividad-operativa') {
                    array_push($operationalActivity, $it);
                } elseif ($item['accountingTypeActivity'] == 'actividad-de-inversion') {
                    array_push($investmentActivity, $it);
                } elseif ($item['accountingTypeActivity'] == 'actividad-de-financiamiento') {
                    array_push($financingActivity, $it);
                }
            }
        }

        $balanceSheetResult['records'] = $balance;

        if ($xml) {
            return Excel::download(new AccountingCashflowSheetExport([
                'pdf'              => $pdf,
                'endDate'          => $endDate,
                'currency'         => $currency,
                'operational_activity' => $operationalActivity,
                'investment_activity' => $investmentActivity,
                'financing_activity' => $financingActivity,
                'beginning_balance' => $beginningBalance,
                'institution' => $institution,
            ]), now()->format('d-m-Y') . '_Estado_de_Flujo_de_Efectivo.xlsx');
        } else {
            $pdf->setConfig(['institution' => $institution, 'urlVerify' => url('report/cashFlowStatement/' . $report->id)]);
            $pdf->setHeader('Reporte de Contabilidad', 'Reporte de Estado de Flujo de Efectivo');
            $pdf->setFooter();
            $pdf->setBody('accounting::pdf.cash_flow_statement', true, [
                'pdf' => $pdf,
                'operational_activity' => $operationalActivity,
                'investment_activity' => $investmentActivity,
                'financing_activity' => $financingActivity,
                'beginning_balance' => $beginningBalance,
                'currency' => $currency,
                'endDate' => $endDate,
                'monthBefore' => $last,
                'institution' => $institution,
            ]);
        }
    }

    /**
     * Genera el reporte en pdf de balance general
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  string   $date     Fecha
     * @param  string   $level    Nivel de sub cuentas máximo a mostrar
     * @param  Currency $currency Moneda en que se expresará el reporte
     * @param  boolean  $zero     Indica si se tomaran cuentas con saldo cero
     *
     * @return JsonResponse
     */
    public function pdfVue($date, $level, Currency $currency, $zero = false)
    {
        /* Último día correspondiente al mes */
        $day = date('d', (mktime(0, 0, 0, explode('-', $date)[1] + 1, 1, explode('-', $date)[0]) - 1));
        /* Formatea la fecha final de búsqueda, (YYYY-mm-dd HH:mm:ss) */
        $endDate = $date . '-' . $day;

        $institution_id = null;

        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        $is_admin = auth()->user()->isAdmin();

        if (!$is_admin && $user_profile['institution']) {
            $institution_id = $user_profile['institution']['id'];
        }

        /*
         * consulta de cada cuenta y asiento que pertenezca a ACTIVO, PASIVO, PATRIMONIO y CUENTA DE ORDEN,
         * registros de las cuentas patrimoniales seleccionadas
         */
        $query = AccountingAccount::with('entryAccount.entries.currency')
            ->with(['entryAccount.entries' => function ($query) use ($endDate, $institution_id, $is_admin) {
                if ($institution_id) {
                    if (
                        $query->where('from_date', '<=', $endDate)->where('approved', true)
                        ->where('institution_id', $institution_id)
                    ) {
                        $query->where('from_date', '<=', $endDate)->where('approved', true)
                            ->where('institution_id', $institution_id);
                    }
                } else {
                    if ($is_admin) {
                        $query->where('from_date', '<=', $endDate)->where('approved', true)
                            ->where('institution_id', $institution_id);
                    }
                }
            }])
            ->whereHas('entryAccount.entries', function ($query) use ($endDate, $institution_id, $is_admin) {
                $query->where('from_date', '<=', $endDate)->where('approved', true)
                    ->where('institution_id', $institution_id);
            })
            ->whereIn('group', [1, 2, 3, 4])
            ->orderBy('group', 'ASC')
            ->orderBy('subgroup', 'ASC')
            ->orderBy('item', 'ASC')
            ->orderBy('generic', 'ASC')
            ->orderBy('specific', 'ASC')
            ->orderBy('subspecific', 'ASC')
            ->orderBy('denomination', 'ASC')->get();

        $convertions = [];

        /* Se recorre y evalúa la relación en las conversiones necesarias a realizar */
        foreach ($query as $record) {
            foreach ($record['entryAccount'] as $entryAccount) {
                $inRange = false;
                if ($entryAccount['entries']) {
                    if (
                        !array_key_exists($entryAccount['entries']['currency']['id'], $convertions)
                        && $entryAccount['entries']['currency']['id'] != $currency->id
                    ) {
                        $convertions = $this->calculateExchangeRates(
                            $convertions,
                            $entryAccount['entries'],
                            $currency->id
                        );
                    }

                    foreach ($convertions as $convertion) {
                        foreach ($convertion as $convert) {
                            if (
                                $entryAccount['entries']['from_date'] >= $convert['start_at'] &&
                                $entryAccount['entries']['from_date'] <= $convert['end_at']
                            ) {
                                $inRange = true;
                            }
                        }
                    }

                    if (
                        !array_key_exists($entryAccount['entries']['currency']['id'], $convertions)
                        && $entryAccount['entries']['currency']['id'] != $currency->id
                    ) {
                        return response()->json([
                            'result' => false,
                            'message' => 'Imposible expresar ' . $entryAccount['entries']['currency']['symbol']
                            . ' (' . $entryAccount['entries']['currency']['name'] . ')'
                            . ' en ' . $currency['symbol'] . '(' . $currency['name'] . ')' .
                            ', verificar tipos de cambio configurados. Para la fecha de ' .
                            $entryAccount['entries']['from_date'],
                        ], 200);
                    } elseif (!$inRange) {
                        if ($entryAccount['entries']['currency']['id'] != $currency->id) {
                            return response()->json([
                                'result' => false,
                                'message' => 'Imposible expresar ' . $entryAccount['entries']['currency']['symbol']
                                . ' (' . $entryAccount['entries']['currency']['name'] . ')'
                                . ' en ' . $currency['symbol'] . '(' . $currency['name'] . ')' .
                                ', verificar tipos de cambio configurados. Para la fecha de ' .
                                $entryAccount['entries']['from_date'],
                            ], 200);
                        }
                    }
                }
            }
        }

        /* Enlace para el reporte */
        $url = 'cashFlowStatement/' . $endDate . '/' . $level . '/' . $zero;

        $currentDate = new DateTime();
        $currentDate = $currentDate->format('Y-m-d');

        /* Almacena el registro del reporte del día si existe */
        $report = AccountingReportHistory::whereBetween('updated_at', [
            $currentDate . ' 00:00:00',
            $currentDate . ' 23:59:59',
        ])
            ->where('report', 'Balance General')
            ->where('institution_id', $institution_id)->first();

        /* se crea o actualiza el registro del reporte*/
        if (!$report) {
            $report = AccountingReportHistory::create(
                [
                    'report' => 'Estado de flujo de efectivo',
                    'url' => $url,
                    'currency_id' => $currency->id,
                    'institution_id' => $institution_id,
                ]
            );
        } else {
            $report->url = $url;
            $report->currency_id = $currency->id;
            $report->institution_id = $institution_id;
            $report->save();
        }

        /*
         * El siguiente segmento es necesario para evaluar cuando se va a mostrar
         * información y cuando no en el reporte.
         * [$records Registros disponibles para mostrarse]
         */

        $endDate = explode('/', $report->url)[1];
        $level = explode('/', $report->url)[2];
        $zero = explode('/', $report->url)[3] == 'true' ? true : false;
        $this->setCurrency($report->currency);

        if ($is_admin) {
            $institution = Institution::where('default', true)->first();
        } else {
            $user_profile = Profile::with('institution')
                ->where('user_id', auth()->user()->id)->first();
            $institution = $user_profile['institution'] ?? null;
        }

        $records = AccountingEntry::with(['accountingAccounts' => function ($query) {
            $query->with('account')
                ->whereHas('account', function ($query) {
                    $query->whereIn('group', [1, 2, 3, 4]);
                });
        }])
            ->where('institution_id', $institution->id)
            ->where('from_date', '<=', $endDate)
            ->where('approved', true)
            ->get();

        $dataInToEndDate = $records->toArray();
        if ($dataInToEndDate == []) {
            return response()->json(
                [
                    'result' => false,
                    'message' => 'No se ha encontrado ningún registro que cumpla con los parámetros establecidos.',
                ],
                200
            );
        }

        return response()->json(['result' => true, 'id' => $report->id], 200);
    }

    /**
     * Genera el reporte en hojade cálculo de balance general
     *
     * @param  integer $report id de reporte y su información
     *
     * return mixed
     */
    public function export($report)
    {
        return  $this->pdf($report, true);
    }

    /**
     * Método para buscar las cuentas padre de una formulación
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    array con las cuentas padre de la formulación
     */
    public function getAccountParents($childData, $parents = [])
    {
        $child = AccountingAccount::findOrFail($childData['id']);
        $child['balance'] = $childData['balance'];
        $child['lastMonthBalance'] = $childData['lastMonthBalance'];
        $child['level'] = $childData['level'] - 1;
        if (!isset($child)) {
            return $parents;
        }

        if (!array_key_exists($child->id, $parents)) {
            $parents[$child->id] = $child;
        }

        if ($child->parent_id == null) {
            return $parents;
        } else {
            $child->load('parent');
            $parent = $child->parent;
            $parent['balance'] += $child['balance'];
            $parent['lastMonthBalance'] += $child['lastMonthBalance'];
            $parent['level'] = $child['level'];

            return $this->getAccountParents($parent, $parents);
        }
    }

    public function calculateVariation($lastMonthBalance, $balance)
    {
        if ($balance >= $lastMonthBalance) {
            return abs($balance) - abs($lastMonthBalance);
        } elseif ($balance < $lastMonthBalance) {
            return abs($lastMonthBalance) - abs($balance);
        }
    }

    public function roundTotal($total, $decimal_places = 2)
    {
        return round($total, $decimal_places);
    }
}
