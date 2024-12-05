<?php

namespace Modules\Accounting\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Facade\Ignition\QueryRecorder\Query;
use Illuminate\Contracts\Support\Renderable;
use Modules\Accounting\Models\AccountingAccount;
use PhpParser\Node\Expr\Cast\Array_;
use Carbon\Carbon;
use App\Models\Parameter;
use App\Repositories\ReportRepository;
use Modules\Accounting\Models\AccountingEntry;
use Modules\Accounting\Models\AccountingReportHistory;
use Modules\Accounting\Models\Profile;
use Modules\Accounting\Models\Setting;
use Modules\Accounting\Models\Currency;
use Modules\Accounting\Models\Institution;

/**
 * @class AccountingBaseController
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AccountingBaseController extends Controller
{
    /**
     * [metodo que permite el calculo de operaciones de cuentas 1,2,3 para una determinada fecha]
     *
     * @author    [Francisco Escala ] [fescala@cenditel.gov.ve]
     *
     * @return    [Producto del resultado de las operaciones correspondientes a situacion finaciera]
     */
    public function balanceSheetCalculation($formDate, $endDate, $institution_id, $is_admin, $date)
    {
        if (count(explode('-', $endDate)) > 1) {
            $query = AccountingAccount::with(["accountingTypeActivity",
                'entryAccount.entries' => function ($query) use ($formDate, $endDate, $institution_id, $is_admin) {
                    if ($institution_id) {
                        if (
                            $query->whereBetween('from_date', [$formDate, $endDate])->where('approved', true)
                            ->where('institution_id', $institution_id)
                        ) {
                            $query->whereBetween('from_date', [$formDate, $endDate])->where('approved', true)
                                ->where('institution_id', $institution_id);
                        }
                    } else {
                        if ($is_admin) {
                            if ($query->whereBetween('from_date', [$formDate, $endDate])->where('approved', true)) {
                                $query->whereBetween('from_date', [$formDate, $endDate])->where('approved', true);
                            }
                        }
                    }
                },
            ]);
            $lastMonthBalances = AccountingAccount::select(
                'accounting_accounts.denomination',
                'accounting_accounts.id',
                'accounting_entries.id as entry_id',
                DB::raw(
                    "CONCAT(
                        accounting_accounts.group, '.',
                        accounting_accounts.subgroup, '.',
                        accounting_accounts.item, '.',
                        accounting_accounts.generic, '.',
                        accounting_accounts.specific, '.',
                        accounting_accounts.subspecific, '.',
                        accounting_accounts.institutional
                    ) AS code_account"
                ),
                'accounting_entries.reference',
                'accounting_entries.concept',
                'accounting_entries.from_date',
                DB::raw(
                    "CASE
                        WHEN accounting_accounts.group IN ('1', '4', '6') THEN
                            (
                                SELECT SUM(debit - assets)
                                FROM accounting_entry_accounts
                                WHERE accounting_entry_accounts.accounting_account_id = accounting_accounts.id
                                AND accounting_entry_accounts.accounting_entry_id = accounting_entries.id
                                AND accounting_entry_accounts.deleted_at IS NULL
                            )
                        WHEN accounting_accounts.group IN ('2', '3', '5') THEN
                            (
                                SELECT SUM(assets - debit)
                                FROM accounting_entry_accounts
                                WHERE accounting_entry_accounts.accounting_account_id = accounting_accounts.id
                                AND accounting_entry_accounts.accounting_entry_id = accounting_entries.id
                                AND accounting_entry_accounts.deleted_at IS NULL
                            )
                        ELSE 0
                    END AS total"
                )
            )
                ->leftJoin('accounting_entry_accounts', 'accounting_accounts.id', '=', 'accounting_entry_accounts.accounting_account_id')
                ->leftJoin('accounting_entries', 'accounting_entry_accounts.accounting_entry_id', '=', 'accounting_entries.id')
                ->whereIn('accounting_accounts.group', [1, 2, 3])
                ->where(function ($query) use ($formDate, $institution_id, $is_admin) {
                    if ($institution_id) {
                        if (
                            $query->where('accounting_entries.from_date', '<', $formDate)
                            ->where('accounting_entries.approved', true)
                            ->where('accounting_entries.institution_id', $institution_id)
                        ) {
                            $query->where('accounting_entries.from_date', '<', $formDate)
                                ->where('accounting_entries.approved', true)
                                ->where('accounting_entries.institution_id', $institution_id)
                                ->where('accounting_entries.deleted_at', null);
                        }
                    } else {
                        if ($is_admin) {
                            if (
                                $query->where('accounting_entries.from_date', '<', $formDate)
                                ->where('accounting_entries.approved', true)
                            ) {
                                $query->where('accounting_entries.from_date', '<', $formDate)
                                    ->where('accounting_entries.approved', true)
                                    ->where('accounting_entries.deleted_at', null);
                            }
                        }
                    }
                })
                ->whereRaw('accounting_entry_accounts.deleted_at IS NULL')
                ->groupBy('accounting_accounts.denomination', 'accounting_entries.id', 'accounting_accounts.id', 'accounting_accounts.group', 'accounting_accounts.subgroup', 'accounting_accounts.item', 'accounting_accounts.generic', 'accounting_accounts.specific', 'accounting_accounts.subspecific', 'accounting_accounts.institutional', 'accounting_entries.reference', 'accounting_entries.concept', 'accounting_entries.from_date')
                ->orderBy('accounting_entries.from_date', 'ASC')
                ->get();
        } else {
            $query = AccountingAccount::with(["accountingTypeActivity",
                'entryAccount.entries' => function ($query) use ($date, $institution_id, $is_admin) {
                    if ($institution_id) {
                        if (
                            $query->whereYear('from_date', $date)->where('approved', true)
                            ->where('institution_id', $institution_id)
                        ) {
                            $query->whereYear('from_date', $date)->where('approved', true)
                                ->where('institution_id', $institution_id);
                        }
                    } else {
                        if ($is_admin) {
                            if ($query->whereYear('from_date', $date)->where('approved', true)) {
                                $query->whereYear('from_date', $date)->where('approved', true);
                            }
                        }
                    }
                },
            ]);
            $lastMonthBalances = [];
        }
        $query = $query->whereIn('group', [1, 2, 3])
            ->orderBy('group', 'ASC')
            ->orderBy('subgroup', 'ASC')
            ->orderBy('item', 'ASC')
            ->orderBy('generic', 'ASC')
            ->orderBy('specific', 'ASC')
            ->orderBy('subspecific', 'ASC')
            ->orderBy('denomination', 'ASC')->get();
        return ["query" => $query , "lastMonthBalances" => $lastMonthBalances ] ;
    }
    /**
     * [metodo que permite el calculo de operaciones de cuentas 5,6 para una determinada fecha]
     *
     * @author    [Francisco Escala ] [fescala@cenditel.gov.ve]
     *
     * @return    [Producto del resultado de las operaciones correspondientes a estado de resultados]
     */
    public function stateofResoultCalculation($formDate, $endDate, $institution_id, $is_admin, $date)
    {
        if (count(explode('-', $endDate)) > 1) {
            $query = AccountingAccount::with(["accountingTypeActivity",
            'entryAccount.entries' => function ($query) use ($formDate, $endDate, $institution_id, $is_admin) {
                if ($institution_id) {
                    if (
                        $query->whereBetween('from_date', [$formDate, $endDate])->where('approved', true)
                        ->where('institution_id', $institution_id)->where('concept', '<>', 'Cierre de ejercicio')
                    ) {
                        $query->whereBetween('from_date', [$formDate, $endDate])->where('approved', true)
                            ->where('institution_id', $institution_id)->where('currency_id', $this->currency->id)->where('concept', '<>', 'Cierre de ejercicio');
                    }
                } else {
                    if ($is_admin) {
                        if ($query->whereBetween('from_date', [$formDate, $endDate])->where('approved', true)) {
                            $query->whereBetween('from_date', [$formDate, $endDate])->where('approved', true)->where('currency_id', $this->currency->id)->where('concept', '<>', 'Cierre de ejercicio');
                        }
                    }
                }
            }
            ]);

            $beginnBalances = AccountingAccount::with(["accountingTypeActivity",
            'entryAccount.entries' => function ($query) use ($formDate, $institution_id, $is_admin) {
                if ($institution_id) {
                    if (
                        $query->where('from_date', '<', $formDate)->where('approved', true)
                        ->where('institution_id', $institution_id)->where('concept', '<>', 'Cierre de ejercicio')
                    ) {
                        $query->where('from_date', '<', $formDate)->where('approved', true)
                            ->where('institution_id', $institution_id)->where('currency_id', $this->currency->id)->where('concept', '<>', 'Cierre de ejercicio');
                    }
                } else {
                    if ($is_admin) {
                        if ($query->where('from_date', '<', $formDate)->where('approved', true)) {
                            $query->where('from_date', '<', $formDate)->where('approved', true)->where('currency_id', $this->currency->id)->where('concept', '<>', 'Cierre de ejercicio');
                        }
                    }
                }
            }
            ])->whereIn('group', [5,6])
            ->orderBy('group', 'ASC')
            ->orderBy('subgroup', 'ASC')
            ->orderBy('item', 'ASC')
            ->orderBy('generic', 'ASC')
            ->orderBy('specific', 'ASC')
            ->orderBy('subspecific', 'ASC')
            ->orderBy('denomination', 'ASC')->get();
        } else {
            $query = AccountingAccount::with(["accountingTypeActivity",
            'entryAccount.entries' => function ($query) use ($date, $institution_id, $is_admin) {
                if ($institution_id) {
                    if (
                        $query->whereYear('from_date', $date)->where('approved', true)
                        ->where('institution_id', $institution_id)->where('concept', '<>', 'Cierre de ejercicio')
                    ) {
                        $query->whereYear('from_date', $date)->where('approved', true)
                            ->where('institution_id', $institution_id)->where('currency_id', $this->currency->id)->where('concept', '<>', 'Cierre de ejercicio');
                    }
                } else {
                    if ($is_admin) {
                        if ($query->whereYear('from_date', $date)->where('approved', true)) {
                            $query->whereYear('from_date', $date)->where('approved', true)->where('currency_id', $this->currency->id)->where('concept', '<>', 'Cierre de ejercicio');
                        }
                    }
                }
            }
            ]);

            $beginnBalances = [];
        }
        $query = $query->whereIn('group', [5,6])
        ->orderBy('group', 'ASC')
        ->orderBy('subgroup', 'ASC')
        ->orderBy('item', 'ASC')
        ->orderBy('generic', 'ASC')
        ->orderBy('specific', 'ASC')
        ->orderBy('subspecific', 'ASC')
        ->orderBy('denomination', 'ASC')->get();
        return ["query" => $query , "beginnBalances" => $beginnBalances ] ;
    }


    /**
     * Genera el reporte en pdf de balance general
     *
     * @author Juan Rosas <juan.rosasr01@gmail.com>
     *
     * @param  integer $report id de reporte y su información
     * @param  boolean $xml si se requiere el reporte en xml
     *
     * @return mixed
     */
    public function balanceSheetResult($report)
    {
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
        $arr = [];

        $formDate = $date . '-01';
        $fecha = Carbon::createFromFormat('Y-m-d', $formDate);
        if ($fecha->month == 1) {
            $lastAvalible = false;
        } else {
            $lastAvalible = true;
        }
        $mesAnterior = $fecha->subMonth();
        $initDate2 = $mesAnterior->copy()->startOfMonth();
        $endDate2 = $mesAnterior->copy()->endOfMonth();
        $rest = $this->balanceSheetCalculation($formDate, $endDate, $institution_id, $is_admin, $date);
        $lastMonthBalances = $rest['lastMonthBalances'] ?? null;
        $query = $rest['query'] ?? null;
        foreach ($query as $account) {
            if (!$account->entryAccount->isEmpty()) {
                foreach ($account->entryAccount as $entrie) {
                    if (!is_null($entrie->entries)) {
                        $balance = $this->getRealBalaceCalculator(
                            $account->code[0],
                            $entrie->debit,
                            $entrie->assets
                        );
                        if (!array_key_exists($account->code, $arr)) {
                            $data = [
                                "id" => $account->id,
                                "code" => $account->code,
                                "denomination" => $account->denomination,
                                "balance" => $balance,
                                "lastMonthBalance" => 0,
                                "level" => 6,
                                "parent" => [],
                                "accountingTypeActivity" => $account->accountingTypeActivity ? $account->accountingTypeActivity->slug : null,
                            ];
                            $arr[$account->code] = $data;
                        } else {
                            $arr[$account->code]['balance'] += $balance;
                        }
                    }
                }
            } else {
                $data = [
                    "id" => $account->id,
                    "code" => $account->code,
                    "denomination" => $account->denomination,
                    "balance" => 0,
                    "lastMonthBalance" => 0,
                    "level" => 6,
                    "parent" => [],
                ];
                $arr[$account->code] = $data;
            }
        }
        foreach ($lastMonthBalances as $account) {
            $balance = 0;
            if (!array_key_exists($account->code_account, $arr)) {
                $data = [
                    "id" => $account->id,
                    "code" => $account->code_account,
                    "denomination" => $account->denomination,
                    "balance" => 0,
                    "lastMonthBalance" => $account->total,
                    "level" => 6,
                    "parent" => [],
                ];
                $arr[$account->code_account] = $data;
            } else {
                $arr[$account->code_account]['lastMonthBalance'] += $account->total;
            }
        }
        $parentsArray = [];
        foreach ($arr as $key => $a) {
            $b = explode('.', $a["code"]);
            if ($b[6] == 000) {
                unset($arr[$key]);
            }
        }
        $accountingAccount = AccountingAccount::where('group', "3")
            ->where('subgroup', "2")
            ->where('item', "5")
            ->where('generic', "02")
            ->where('specific', "01")
            ->where('subspecific', "01")
            ->where('institutional', "001")
            ->where('active', true)->first();

        $accountValue = Parameter::where('p_key', 'close_fiscal_year_account')->get('p_value')->first()?->p_value;
        if ($accountValue) {
            $nameEDR = AccountingAccount::find($accountValue)->code;
        } else {
            $nameEDR = $accountingAccount->code;
        }

        if ($accountingAccount) {
            $data = [
                "id" => $accountingAccount->id,
                "code" => $nameEDR,
                "denomination" => "RESULTADOS DEL EJERCICIO",
                "balance" => $this->getTotalResults($endDate, $formDate),
                "level" => 2,
                "lastMonthBalance" => $this->getLastTotalResults($initDate2, $endDate2, $lastAvalible),
                "parent" => $accountingAccount->parent_id,
            ];
            $arr["3.2.5.02.01.01.001"] = $data;
        } else {
            $data = [
                "id" => 348,
                "code" => $nameEDR,
                "denomination" => "RESULTADOS DEL EJERCICIO",
                "balance" => $this->getTotalResults($endDate, $formDate),
                "level" => 2,
                "lastMonthBalance" => $this->getLastTotalResults($initDate2, $endDate2, $lastAvalible),
                "parent" => 346,
            ];
            $arr["3.2.5.02.01.01.001"] = $data;
        }

        foreach ($arr as $finalAccount) {
            $parents = $this->getAccountParents($finalAccount, []);
            array_push($parentsArray, $parents);
        }

        foreach ($parentsArray as $pArray) {
            foreach ($pArray as $pA) {
                if (!array_key_exists($pA->code, $arr)) {
                    $data = [
                        "id" => $pA['id'],
                        "code" => $pA['code'],
                        "denomination" => $pA['denomination'],
                        "balance" => $pA['balance'],
                        "lastMonthBalance" => $pA['lastMonthBalance'],
                        "level" => $pA['level'] < 0 ? $pA['level'] * -1 : $pA['level'],
                        "parent" => $pA['parent_id'],
                    ];
                    $arr[$pA->code] = $data;
                } else {
                    $childrenCode = explode('.', $pA['code']);
                    if ($childrenCode[6] == '000' && $arr[$pA->code]['code'] != '3.2.5.02.01.01.001') {
                        $arr[$pA->code]['balance'] += $pA['balance'];
                        $arr[$pA->code]['lastMonthBalance'] += $pA['lastMonthBalance'];
                    }
                }
            }
        }
        ksort($arr);

        $replacements = array(
            "0" => array("value" => "0", "length" => 1),
            "00.000" => array("value" => "00.000", "length" => 6),
            "00.00.000" => array("value" => "00.00.000", "length" => 9),
            "00.00.00.000" => array("value" => "00.00.00.000", "length" => 12),
            "0.00.00.00.000" => array("value" => "0.00.00.00.000", "length" => 14),
            "0.0.00.00.00.000" => array("value" => "0.0.00.00.00.000", "length" => 16),
        );

        foreach ($arr as $key => &$value) {
            foreach ($replacements as $replacement) {
                $length = $replacement['length'];
                if (substr($key, -$length) != $replacement['value']) {
                    $sum = $value['balance'];
                    $key_zero = substr_replace($key, $replacement['value'], -$length);
                }
            }
        }

        if ($level == 1) {
            foreach ($arr as $key => $a) {
                $b = explode('.', $key);

                if ($b[1] != 0) {
                    unset($arr[$key]);
                }
            }
        } elseif ($level == 2) {
            foreach ($arr as $key => $a) {
                $b = explode('.', $key);

                if ($b[2] != 0) {
                    unset($arr[$key]);
                }
            }
        } elseif ($level == 3) {
            foreach ($arr as $key => $a) {
                $b = explode('.', $key);

                if ($b[3] != 0) {
                    unset($arr[$key]);
                }
            }
        } elseif ($level == 4) {
            foreach ($arr as $key => $a) {
                $b = explode('.', $key);

                if ($b[4] != 0) {
                    unset($arr[$key]);
                }
            }
        } elseif ($level == 5) {
            foreach ($arr as $key => $a) {
                $b = explode('.', $key);

                if ($b[5] != 0) {
                    unset($arr[$key]);
                }
            }
        }

        $totArr = [];

        foreach ($arr as $key => $a) {
            if ($a['code'][0] == 1) {
                $totArr[1][$key] = $a;
            } elseif ($a['code'][0] == 2) {
                $totArr[2][$key] = $a;
            } elseif ($a['code'][0] == 3) {
                $totArr[3][$key] = $a;
            } elseif ($a['code'][0] == 4) {
                $totArr[4][$key] = $a;
            }
        }

        /* Configuración general de la apliación */
        $setting = Setting::all()->first();

        /* Base para generar el pdf */
        $pdf = new ReportRepository();

        /* Definición de las características generales de la página pdf */

        if (count(explode('-', $endDate)) > 1) {
            $lastOfThePreviousMonth = date(
                'd',
                (mktime(0, 0, 0, explode('-', $date)[1], 1, explode('-', $date)[0]) - 1)
            );
            $last = $lastOfThePreviousMonth . '/' . (explode('-', $date)[1] - 1) . '/' . explode('-', $date)[0];
        } else {
            $last = '';
        }
        $institution = Institution::find(1);

        return [
            'records' => $totArr,
            'monthBefore' => $last,
        ];
    }

    /**
     * Realiza el cálculo de las cuentas de acuerdo a como suman
     *
     * @author Ing. Francisco Escala <fjescala@gmail.com>
     * @author Juan Rosas <juan.rosasr01@gmail.com>
     *
     * @param  string $date     Fecha de la consulta
     * @param  string $formDate Fecha de la consulta
     *
     * @return float           Retorna el valor del total de las cuentas en el asiento
     */

    public function getTotalResults($date, $formDate)
    {
        $balance = 0;

        $arr = [];
        $result_of_the_excersice = 0;
        $parentsArray = [];
        $is_admin = auth()->user()->isAdmin();

        if ($is_admin) {
            $institution = Institution::where('default', true)->first();
        } else {
            $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();
            $institution = $user_profile['institution'] ?? null;
        }

        $records = AccountingEntry::with(['accountingAccounts' => function ($query) {
            $query->with('account')
                ->whereHas('account', function ($query) {
                    $query->whereIn('group', [5, 6]);
                });
        }])
            ->where('institution_id', $institution->id)
            ->where('approved', true)
            ->whereBetween('from_date', [$formDate, $date])
            ->get();

        foreach ($records as $record) {
            if ($record['accountingAccounts']) {
                foreach ($record['accountingAccounts'] as $account) {
                    $balance = $this->getRealBalaceCalculator(
                        $account['account']['code'][0],
                        $account['debit'],
                        $account['assets']
                    );
                    if (!array_key_exists($account['account']['code'], $arr)) {
                        $data = [
                            "id" => $account['account']['id'],
                            "code" => $account['account']['code'],
                            "denomination" => $account['account']['denomination'],
                            "balance" => $balance,
                            "lastMonthBalance" => 0,
                            "level" => 6,
                            "parent" => [],
                        ];
                        $arr[$account['account']['code']] = $data;
                    } else {
                        $arr[$account['account']['code']]['balance'] += $balance;
                    }
                }
            }
        }
        foreach ($arr as $key => $a) {
            if ($a['code'][0] == 6) {
                $result_of_the_excersice -= $a['balance'];
            } elseif ($a['code'][0] == 5) {
                $result_of_the_excersice += $a['balance'];
            }
        }

        return $result_of_the_excersice;
    }

    /**
     * Realiza el cálculo de las cuentas de acuerdo a como suman
     *
     * @author Ing. Francisco Escala <fjescala@gmail.com>
     * @author Juan Rosas <juan.rosasr01@gmail.com>
     *
     * @param  object  $formDate     Fecha de la consulta
     * @param  object  $endDate      Fecha de la consulta
     * @param  boolean $lastAvalible Última disponibilidad
     *
     * @return float           Retorna el valor del total de las cuentas en el asiento
     */

    public function getLastTotalResults($formDate, $endDate, $lastAvalible)
    {
        if (!$lastAvalible) {
            return 0;
        }
        // determinamos el primero de enero del año a buscar
        // Obtén el año de la fecha
        $ano = $formDate->year;

        // Crea una nueva instancia de Carbon para el 1 de enero del mismo año
        $primerDeEnero = Carbon::create($ano, 1, 1);
        $balance = 0;

        $arr = [];
        $result_of_the_excersice = 0;
        $parentsArray = [];
        $is_admin = auth()->user()->isAdmin();

        if ($is_admin) {
            $institution = Institution::where('default', true)->first();
        } else {
            $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();
            $institution = $user_profile['institution'] ?? null;
        }

        $beginnBalances = AccountingEntry::query()
            ->with(['accountingAccounts' => function ($query) {
                $query
                    ->with('account')
                    ->whereHas('account', function ($query) {
                        $query->whereIn('group', [5, 6]);
                    });
            }])
            ->where('institution_id', $institution->id)
            ->where('approved', true)
            ->whereBetween('from_date', [$primerDeEnero, $endDate])
            ->get();

        foreach ($beginnBalances as $beginnbalance) {
            if ($beginnbalance['accountingAccounts']) {
                foreach ($beginnbalance['accountingAccounts'] as $account) {
                    $balance = $this->getRealBalaceCalculator(
                        $account['account']['code'][0],
                        $account['debit'],
                        $account['assets']
                    );
                    if (array_key_exists($account['account']['code'], $arr)) {
                        $arr[$account['account']['code']]['lastMonthBalance'] += $balance;
                    } else {
                        $acc = [
                        'denomination' => $account['account']['denomination'],
                        'code' => $account['account']["code"],
                        'lastMonthBalance' => $balance,
                        ];
                        $arr[$account['account']['code']] = $acc;
                    }
                }
            }
        }

        foreach ($arr as $key => $a) {
            if ($a['code'][0] == 6) {
                $result_of_the_excersice -= $a['lastMonthBalance'];
            } elseif ($a['code'][0] == 5) {
                $result_of_the_excersice += $a['lastMonthBalance'];
            }
        }

        return $result_of_the_excersice;
    }

    /**
     * Realiza el cálculo de las cuentas de acuerdo a como suman
     *
     * @author Ing. Francisco Escala <fjescala@gmail.com>
     * @author Juan Rosas <juan.rosasr01@gmail.com>
     *
     * @param  integer $ini     Identificador del tipo de cuenta
     * @param  integer $debit   Valor de la cuenta por la columna del debe
     * @param  integer $assets  Valor de la cuenta por la columna del haber
     *
     * @return float           Retorna el valor del total de las cuentas en el asiento
     */
    public function getRealBalaceCalculator($ini, $debit, $assets)
    {
        $balance = 0;
        switch ($ini) {
            case 1:
                $balance = $debit - $assets;
                break;
            case 2:
                $balance = $assets - $debit;
                break;
            case 3:
                $balance = $assets - $debit;
                break;
            case 4:
                $balance = $debit - $assets;
                break;
            case 5:
                $balance = $assets - $debit;
                break;
            case 6:
                $balance = $debit - $assets;
                break;
        }

        return $balance;
    }

    /**
     * Genera el reporte en pdf de estado de resultados
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  integer $report id de reporte y su información
     *
     * @return mixed
     */
    public function calculateStateOfResult($report, $xml = false)
    {
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
        $is_admin = auth()->user()->isAdmin();

        if ($is_admin) {
            $institution = Institution::where('default', true)->first();
        } else {
            $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();
            $institution = $user_profile['institution'] ?? null;
        }
        $institution_id = $institution->id;
        $arr = [];
        $formDate = $date . '-01';
        $rest = $this->stateofResoultCalculation($formDate, $endDate, $institution_id, $is_admin, $date);
        $beginnBalances = $rest['beginnBalances'] ?? null;
        $query = $rest['query'] ?? null;

        foreach ($query as $account) {
            if (!$account->entryAccount->isEmpty()) {
                foreach ($account->entryAccount as $entrie) {
                    if (!is_null($entrie->entries)) {
                        $balance = $this->getRealBalaceCalculator(
                            $account->code[0],
                            $entrie->debit,
                            $entrie->assets
                        );
                        if (!array_key_exists($account->code, $arr)) {
                            // dd($account->code);
                            $data = [
                                "id" => $account->id,
                                "code" => $account->code,
                                "denomination" => $account->denomination,
                                "balance" => $balance,
                                "beginningBalance" => 0,
                                "level" => 6,
                                "parent" => [],
                                "accountingTypeActivity" => $account->accountingTypeActivity ? $account->accountingTypeActivity->slug : null,
                            ];
                            $arr[$account->code] = $data;
                        } else {
                            $arr[$account->code]['balance'] += $balance;
                        }
                    }
                }
            } else {
                $data = [
                    "id" => $account->id,
                    "code" => $account->code,
                    "denomination" => $account->denomination,
                    "balance" => 0,
                    "beginningBalance" => 0,
                    "level" => 6,
                    "parent" => [],
                    "accountingTypeActivity" => $account->accountingTypeActivity ? $account->accountingTypeActivity->slug : null,
                ];
                $arr[$account->code] = $data;
            }
        }

        foreach ($beginnBalances as $account) {
            $balance = 0;
            if (!$account->entryAccount->isEmpty()) {
                foreach ($account->entryAccount as $entrie) {
                    if (!is_null($entrie->entries)) {
                        $balance = $this->getRealBalaceCalculator(
                            $account->code[0],
                            $entrie->debit,
                            $entrie->assets
                        );
                        if (!array_key_exists($account->code, $arr)) {
                            $data = [
                                "id" => $account->id,
                                "code" => $account->code,
                                "denomination" => $account->denomination,
                                "balance" => 0,
                                "beginningBalance" => $balance,
                                "level" => 6,
                                "parent" => [],
                                "accountingTypeActivity" => $account->accountingTypeActivity ? $account->accountingTypeActivity->slug : null,
                            ];
                            $arr[$account->code] = $data;
                        } else {
                            $arr[$account->code]['beginningBalance'] += $balance;
                        }
                    }
                }
            }
        }

        $parentsArray = [];
        foreach ($arr as $key => $a) {
            $b = explode('.', $a["code"]);
            if ($b[6] == 000) {
                unset($arr[$key]);
            }
        }
        foreach ($arr as $finalAccount) {
            $parents = $this->getAccountParentsStateOfResult($finalAccount, []);
            array_push($parentsArray, $parents);
        }
        $arr = [];
        // este es un array de cuentas con sus padres mas proximos
        // este foreach agrega la cuenta 6.0.00.0 a $arr
        foreach ($parentsArray as $pArray) {
            foreach ($pArray as $pA) {
                if (!array_key_exists($pA->code, $arr)) {
                    $data = [
                        "id" => $pA['id'],
                        "code" => $pA['code'],
                        "denomination" => $pA['denomination'],
                        "balance" => $pA['balance'],
                        "beginningBalance" => $pA['beginningBalance'],
                        "level" => $pA['level'],
                        "parent" => [],
                        "accountingTypeActivity" => $pA->accountingTypeActivity ? $pA->accountingTypeActivity->slug : null,
                    ];
                    $arr[$pA->code] = $data;
                } else {
                    $childrenCode = explode('.', $pA['code']);
                    if ($childrenCode[6] == '000' && $arr[$pA->code]['code'] != '3.1.5.02.00.00.000') {
                        $arr[$pA->code]['balance'] += $pA['balance'];
                        $arr[$pA->code]['beginningBalance'] += $pA['beginningBalance'];
                    }
                }
            }
        }
        if ($zero) {
            $zeroAccount = AccountingAccount::whereBetween('group', [5, 6])
                ->orderBy('group', 'ASC')
                ->orderBy('subgroup', 'ASC')
                ->orderBy('item', 'ASC')
                ->orderBy('generic', 'ASC')
                ->orderBy('specific', 'ASC')
                ->orderBy('subspecific', 'ASC')
                ->orderBy('denomination', 'ASC')->get();
            foreach ($zeroAccount as $pA) {
                if (!array_key_exists($pA->code, $arr)) {
                    $data = [
                        "id" => $pA['id'],
                        "code" => $pA['code'],
                        "denomination" => $pA['denomination'],
                        "balance" => $pA['balance'],
                        "beginningBalance" => $pA['beginningBalance'],
                        "level" => $pA['level'],
                        "parent" => [],
                        "accountingTypeActivity" => $pA->accountingTypeActivity ? $pA->accountingTypeActivity->slug : null,
                    ];
                    $arr[$pA->code] = $data;
                }
            }
        }

        ksort($arr);

        if ($level == 1) {
            foreach ($arr as $key => $a) {
                $b = explode('.', $key);

                if ($b[1] != 0) {
                    unset($arr[$key]);
                }
            }
        } elseif ($level == 2) {
            foreach ($arr as $key => $a) {
                $b = explode('.', $key);

                if ($b[2] != 0) {
                    unset($arr[$key]);
                }
            }
        } elseif ($level == 3) {
            foreach ($arr as $key => $a) {
                $b = explode('.', $key);

                if ($b[3] != 0) {
                    unset($arr[$key]);
                }
            }
        } elseif ($level == 4) {
            foreach ($arr as $key => $a) {
                $b = explode('.', $key);

                if ($b[4] != 0) {
                    unset($arr[$key]);
                }
            }
        } elseif ($level == 5) {
            foreach ($arr as $key => $a) {
                $b = explode('.', $key);

                if ($b[5] != 0) {
                    unset($arr[$key]);
                }
            }
        }

        /* configuración general de la apliación */
        $setting = Setting::all()->first();

        /* base para generar el pdf */
        $pdf = new ReportRepository();

        /* Definicion de las caracteristicas generales de la página pdf */
        if (count(explode('-', $endDate)) > 1) {
            $lastOfThePreviousMonth = date(
                'd',
                (mktime(0, 0, 0, explode('-', $date)[1], 1, explode('-', $date)[0]) - 1)
            );
            $last = $lastOfThePreviousMonth . '/' . (explode('-', $date)[1] - 1) . '/' . explode('-', $date)[0];
        } else {
            $last = '';
        }

        if ($xml) {
            // return Excel::download(new AccountingStateOfResultsExport([
            // 'pdf' => $pdf,
            // 'records' => $arr,
            // 'currency' => $this->getCurrency(),
            // 'level' => $level,
            // 'zero' => $zero,
            // 'endDate' => $endDate,
            // 'monthBefore' => $last,
            // 'institution' => $institution,
            // ]), now()->format('d-m-Y') . '_ESTADO_DE_RENDIMIENTO_FINANCIERA.xlsx');
        } else {
            return [
                'records' => $arr,
                'monthBefore' => $last,
            ];
        }
    }

        /**
     * Método para buscar las cuentas padre de una formulación
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    array con las cuentas padre de la formulación
     */
    public function getAccountParentsStateOfResult($childData, $parents = [])
    {
        $child = AccountingAccount::find($childData['id']);
        $child['balance'] = $childData['balance'];
        $child['beginningBalance'] = $childData['beginningBalance'];
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
            $parent['beginningBalance'] += $child['beginningBalance'];
            $parent['level'] = $child['level'];
            return $this->getAccountParents($parent, $parents);
        }
    }
}
