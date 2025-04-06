<?php

namespace Modules\Budget\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use Modules\Budget\Actions\Reports\ExportConsolidatedReportAction;
use Modules\Budget\Models\BudgetAccount;
use Modules\Budget\Models\BudgetProject;
use Modules\Budget\Models\BudgetCentralizedAction;
use Modules\Budget\Models\BudgetSubSpecificFormulation;
use Modules\Budget\Models\BudgetModificationAccount;
use Modules\Budget\Models\DocumentStatus;
use Modules\Budget\Models\BudgetCompromise;
use Modules\Budget\Models\BudgetModification;
use Modules\Budget\Mail\BudgetSendMail;
use Nwidart\Modules\Facades\Module;
use App\Exports\MultiSheetExport;
use App\Notifications\SystemNotification;
use App\Models\User;
use Carbon\Carbon;

/**
 * @class CreateAndSendBudgetConsolidatedReportJob
 * @brief Genera el reporte de conciliación y lo envía al usuario.
 *
 * Genera el reporte de conciliación y lo envía al usuario.
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve || juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateAndSendBudgetConsolidatedReportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Parametros del reporte
     * @var array
     */
    private $request;

    /**
     * Fecha inicial
     * @var string
     */
    private $monthFrom;

    /**
     * Fecha final
     * @var string
     */
    private $monthTo;

    /**
     * Nombre del archivo
     * @var string
     */
    private $filename;

    /**
     * Variable que contiene el tiempo de espera para la ejecución del trabajo,
     * si no se quiere limite de tiempo, se define en 0
     *
     * @var integer $timeout
     */
    public $timeout = 0;

    /**
     * ID del usuario
     * @var int
     */
    private $userId;

    public function __construct(int $userId, array $request, string $monthFrom, string $monthTo, string $filename)
    {
        $this->request = $request;
        $this->monthFrom = $monthFrom;
        $this->monthTo = $monthTo;
        $this->filename = $filename;
        $this->userId = $userId;

        if ('local' !== @env('APP_ENV')) {
            $this->onQueue('bulk');
        }
    }

    public function handle()
    {
        $export = app(ExportConsolidatedReportAction::class);

        $tempPath = $export->invoke(
            $this->budgetLoadConsolidated($this->request),
            $this->monthFrom,
            $this->monthTo,
            $this->filename
        );

        $user = User::find($this->userId);

        // Se envía el correo con el archivo temporal
        Mail::to($user->email)->send(
            new BudgetSendMail($tempPath, 'Reporte consolidado', 'xlsx')
        );


        if ($user) {
            $user->notify(
                new SystemNotification(
                    'Aviso',
                    'Se ha generado el reporte consolidado, '
                    . 'el archivo ha sido enviado a su correo electrónico.',
                )
            );
        }
    }

    private function budgetLoadConsolidated(array $request)
    {
        $data = [];
        $collectionAccounts = collect($request['accounts']);
        $accountsAll = $collectionAccounts->search(fn ($item) => 'Todas' === $item['id']);
        $projectsAll = collect($request['proyects'])->search(fn ($item) => 'Todos' === $item['id']);
        $centralizedActionsAll = collect($request['centralized_actions'])->search(fn ($item) => 'Todas' === $item['id']);

        $accountIds = $collectionAccounts == null || count($collectionAccounts) == 0 || $accountsAll !== false ?
            'Todas' :
            $collectionAccounts->pluck('id')->toArray();

        $dateFrom = Carbon::parse($request['from']);
        $dateTo = Carbon::parse($request['to']);
        $specificActions = collect($request['specific_actions']);
        $specificActionIds = $request['specific_actions'];

        if (!empty($request['proyects'])) {
            $projectIds = $projectsAll !== false ?
                'Todos' :
                collect($request['proyects'])->pluck('id')->toArray();

            if ($projectIds == 'Todos') {
                $projects = BudgetProject::query()
                    ->get();
            } else {
                $projects = BudgetProject::query()
                    ->whereIn('id', $projectIds)
                    ->get();
            }

            foreach ($projects as $project) {
                foreach ($project->specificActions as $specificA) {
                    if (empty($specificActions->where('id', $specificA['id'])->first())) {
                        $specificActionIds[]['id'] = $specificA['id'];
                    }
                }
            }
        }

        if (!empty($request['centralized_actions'])) {
            $centralizedActionIds = $centralizedActionsAll !== false ?
                'Todas' :
                collect($request['centralized_actions'])->pluck('id')->toArray();

            if ($centralizedActionIds == 'Todas') {
                $centralizedActions = BudgetCentralizedAction::query()
                    ->get();
            } else {
                $centralizedActions = BudgetCentralizedAction::query()
                    ->whereIn('id', $centralizedActionIds)
                    ->get();
            }

            foreach ($centralizedActions as $centralizedAction) {
                foreach ($centralizedAction->specificActions as $specificA) {
                    if (empty($specificActions->where('id', $specificA['id'])->first())) {
                        $specificActionIds[]['id'] = $specificA['id'];
                    }
                }
            }
        }

        if (!empty($specificActionIds)) {
            $documentStatus = DocumentStatus::getStatus('AP');

            foreach ($specificActionIds as $key => $specificAction) {
                // Se buscan las formulaciones para cada accion específica
                $formulation = BudgetSubSpecificFormulation::query()
                    ->with('accountOpens')
                    ->where('budget_specific_action_id', $specificAction['id'])
                    ->first();

                if ($formulation) {
                    $centralizedOrProyect = $formulation->specificAction->specificable_type::find($formulation->specificAction->specificable_id);
                    $code = $centralizedOrProyect::class == 'Modules\Budget\Models\BudgetCentralizedAction' ? 'CA' : 'PR';
                    $code = $formulation->specificAction->code . '-' . $code . '-' . $centralizedOrProyect->code;
                    $budgetAccountIds = [];

                    // Se obtienen las cuentas presupuestarias formuladas
                    if ($accountIds == 'Todas') {
                        $accounts = $formulation?->accountOpens ?? collect(null);

                        if ($accounts->count() > 0) {
                            foreach ($accounts as $account) {
                                $budgetAccountIds[] = $account->budget_account_id;
                            }

                            $modificationAccounts = BudgetModificationAccount::query()
                                ->whereHas('budgetModification', function ($query) use ($documentStatus) {
                                    $query
                                        ->where('document_status_id', $documentStatus->id)
                                        ->where('status', 'AP');
                                })
                                ->where('budget_sub_specific_formulation_id', $formulation->id)
                                ->whereNotIn('budget_account_id', $budgetAccountIds)
                                ->get();

                            foreach ($modificationAccounts as $modificationAccount) {
                                $accounts[] = $modificationAccount;
                            }
                        }
                    } else {
                        $accounts = $formulation?->accountOpens->whereIn('budget_account_id', $accountIds) ?? collect(null);

                        foreach ($accounts as $account) {
                            $budgetAccountIds[] = $account->budget_account_id;
                        }

                        $modificationAccounts = BudgetModificationAccount::query()
                            ->whereHas('budgetModification', function ($query) use ($documentStatus) {
                                $query
                                    ->where('document_status_id', $documentStatus->id)
                                    ->where('status', 'AP');
                            })
                            ->where('budget_sub_specific_formulation_id', $formulation->id)
                            ->whereIn('budget_account_id', $accountIds)
                            ->whereNotIn('budget_account_id', $budgetAccountIds)
                            ->get();

                        foreach ($modificationAccounts as $modificationAccount) {
                            $accounts[] = $modificationAccount;
                        }
                    }

                    if ($accounts->count() > 0) {
                        foreach ($accounts as $key => $account) {
                            $bAccount = BudgetAccount::query()
                                ->where('id', $account->budget_account_id)
                                ->first();

                            $data[$code][$bAccount['code']] = [
                                'specific_action' => $code,
                                'account_code' => $bAccount['code'],
                                'budget_account_id' => $bAccount['id'],
                                'account_denomination' => $bAccount['denomination'],
                                'approved_budget' => $account->total_year_amount ?? 0,
                                'months' => [
                                    'enero' => [
                                        'month_modifications' => 0,
                                        'budget_modifications' => 0,
                                        'month_programmed' => 0,
                                        'month_compromised' => 0,
                                        'month_caused' => 0,
                                        'month_paid' => 0,
                                        'absolute_variation_caused_vs_programmed' => 0,
                                        'accumulated_programmed' => 0,
                                        'accumulated_compromised' => 0,
                                        'accumulated_caused' => 0,
                                        'accumulated_paid' => 0,
                                        'accumulated_absolute_variation_caused_vs_programmed' => 0,
                                        'budgetary_availability_compromised' => 0,
                                        'budgetary_availability_caused' => 0,
                                    ],
                                    'febrero' => [
                                        'month_modifications' => 0,
                                        'budget_modifications' => 0,
                                        'month_programmed' => 0,
                                        'month_compromised' => 0,
                                        'month_caused' => 0,
                                        'month_paid' => 0,
                                        'absolute_variation_caused_vs_programmed' => 0,
                                        'accumulated_programmed' => 0,
                                        'accumulated_compromised' => 0,
                                        'accumulated_caused' => 0,
                                        'accumulated_paid' => 0,
                                        'accumulated_absolute_variation_caused_vs_programmed' => 0,
                                        'budgetary_availability_compromised' => 0,
                                        'budgetary_availability_caused' => 0,
                                    ],
                                    'marzo' => [
                                        'month_modifications' => 0,
                                        'budget_modifications' => 0,
                                        'month_programmed' => 0,
                                        'month_compromised' => 0,
                                        'month_caused' => 0,
                                        'month_paid' => 0,
                                        'absolute_variation_caused_vs_programmed' => 0,
                                        'accumulated_programmed' => 0,
                                        'accumulated_compromised' => 0,
                                        'accumulated_caused' => 0,
                                        'accumulated_paid' => 0,
                                        'accumulated_absolute_variation_caused_vs_programmed' => 0,
                                        'budgetary_availability_compromised' => 0,
                                        'budgetary_availability_caused' => 0,
                                    ],
                                    'abril' => [
                                        'month_modifications' => 0,
                                        'budget_modifications' => 0,
                                        'month_programmed' => 0,
                                        'month_compromised' => 0,
                                        'month_caused' => 0,
                                        'month_paid' => 0,
                                        'absolute_variation_caused_vs_programmed' => 0,
                                        'accumulated_programmed' => 0,
                                        'accumulated_compromised' => 0,
                                        'accumulated_caused' => 0,
                                        'accumulated_paid' => 0,
                                        'accumulated_absolute_variation_caused_vs_programmed' => 0,
                                        'budgetary_availability_compromised' => 0,
                                        'budgetary_availability_caused' => 0,
                                    ],
                                    'mayo' => [
                                        'month_modifications' => 0,
                                        'budget_modifications' => 0,
                                        'month_programmed' => 0,
                                        'month_compromised' => 0,
                                        'month_caused' => 0,
                                        'month_paid' => 0,
                                        'absolute_variation_caused_vs_programmed' => 0,
                                        'accumulated_programmed' => 0,
                                        'accumulated_compromised' => 0,
                                        'accumulated_caused' => 0,
                                        'accumulated_paid' => 0,
                                        'accumulated_absolute_variation_caused_vs_programmed' => 0,
                                        'budgetary_availability_compromised' => 0,
                                        'budgetary_availability_caused' => 0,
                                    ],
                                    'junio' => [
                                        'month_modifications' => 0,
                                        'budget_modifications' => 0,
                                        'month_programmed' => 0,
                                        'month_compromised' => 0,
                                        'month_caused' => 0,
                                        'month_paid' => 0,
                                        'absolute_variation_caused_vs_programmed' => 0,
                                        'accumulated_programmed' => 0,
                                        'accumulated_compromised' => 0,
                                        'accumulated_caused' => 0,
                                        'accumulated_paid' => 0,
                                        'accumulated_absolute_variation_caused_vs_programmed' => 0,
                                        'budgetary_availability_compromised' => 0,
                                        'budgetary_availability_caused' => 0,
                                    ],
                                    'julio' => [
                                        'month_modifications' => 0,
                                        'budget_modifications' => 0,
                                        'month_programmed' => 0,
                                        'month_compromised' => 0,
                                        'month_caused' => 0,
                                        'month_paid' => 0,
                                        'absolute_variation_caused_vs_programmed' => 0,
                                        'accumulated_programmed' => 0,
                                        'accumulated_compromised' => 0,
                                        'accumulated_caused' => 0,
                                        'accumulated_paid' => 0,
                                        'accumulated_absolute_variation_caused_vs_programmed' => 0,
                                        'budgetary_availability_compromised' => 0,
                                        'budgetary_availability_caused' => 0,
                                    ],
                                    'agosto' => [
                                        'month_modifications' => 0,
                                        'budget_modifications' => 0,
                                        'month_programmed' => 0,
                                        'month_compromised' => 0,
                                        'month_caused' => 0,
                                        'month_paid' => 0,
                                        'absolute_variation_caused_vs_programmed' => 0,
                                        'accumulated_programmed' => 0,
                                        'accumulated_compromised' => 0,
                                        'accumulated_caused' => 0,
                                        'accumulated_paid' => 0,
                                        'accumulated_absolute_variation_caused_vs_programmed' => 0,
                                        'budgetary_availability_compromised' => 0,
                                        'budgetary_availability_caused' => 0,
                                    ],
                                    'septiembre' => [
                                        'month_modifications' => 0,
                                        'budget_modifications' => 0,
                                        'month_programmed' => 0,
                                        'month_compromised' => 0,
                                        'month_caused' => 0,
                                        'month_paid' => 0,
                                        'absolute_variation_caused_vs_programmed' => 0,
                                        'accumulated_programmed' => 0,
                                        'accumulated_compromised' => 0,
                                        'accumulated_caused' => 0,
                                        'accumulated_paid' => 0,
                                        'accumulated_absolute_variation_caused_vs_programmed' => 0,
                                        'budgetary_availability_compromised' => 0,
                                        'budgetary_availability_caused' => 0,
                                    ],
                                    'octubre' => [
                                        'month_modifications' => 0,
                                        'budget_modifications' => 0,
                                        'month_programmed' => 0,
                                        'month_compromised' => 0,
                                        'month_caused' => 0,
                                        'month_paid' => 0,
                                        'absolute_variation_caused_vs_programmed' => 0,
                                        'accumulated_programmed' => 0,
                                        'accumulated_compromised' => 0,
                                        'accumulated_caused' => 0,
                                        'accumulated_paid' => 0,
                                        'accumulated_absolute_variation_caused_vs_programmed' => 0,
                                        'budgetary_availability_compromised' => 0,
                                        'budgetary_availability_caused' => 0,
                                    ],
                                    'noviembre' => [
                                        'month_modifications' => 0,
                                        'budget_modifications' => 0,
                                        'month_programmed' => 0,
                                        'month_compromised' => 0,
                                        'month_caused' => 0,
                                        'month_paid' => 0,
                                        'absolute_variation_caused_vs_programmed' => 0,
                                        'accumulated_programmed' => 0,
                                        'accumulated_compromised' => 0,
                                        'accumulated_caused' => 0,
                                        'accumulated_paid' => 0,
                                        'accumulated_absolute_variation_caused_vs_programmed' => 0,
                                        'budgetary_availability_compromised' => 0,
                                        'budgetary_availability_caused' => 0,
                                    ],
                                    'diciembre' => [
                                        'month_modifications' => 0,
                                        'budget_modifications' => 0,
                                        'month_programmed' => 0,
                                        'month_compromised' => 0,
                                        'month_caused' => 0,
                                        'month_paid' => 0,
                                        'absolute_variation_caused_vs_programmed' => 0,
                                        'accumulated_programmed' => 0,
                                        'accumulated_compromised' => 0,
                                        'accumulated_caused' => 0,
                                        'accumulated_paid' => 0,
                                        'accumulated_absolute_variation_caused_vs_programmed' => 0,
                                        'budgetary_availability_compromised' => 0,
                                        'budgetary_availability_caused' => 0,
                                    ],
                                ],
                            ];

                            $modifications = BudgetModification::query()
                                ->where('document_status_id', $documentStatus->id)
                                ->where('status', 'AP')
                                ->whereDate('approved_date', '>=', $dateFrom)
                                ->whereDate('approved_date', '<=', $dateTo)
                                ->whereHas('budgetModificationAccounts', function ($query) use ($account, $formulation) {
                                    $query
                                        ->where('budget_account_id', $account->budget_account_id)
                                        ->where('budget_sub_specific_formulation_id', $formulation->id);
                                })
                                ->orderBy('approved_at', 'asc')
                                ->get();

                            $compromises = BudgetCompromise::query()
                                ->with([
                                    'budgetStages' => function ($query) {
                                        $query->withTrashed();
                                    },
                                    'budgetCompromiseDetails'
                                ])
                                ->has('budgetStages')
                                ->whereDate('compromised_at', '>=', $dateFrom)
                                ->whereDate('compromised_at', '<=', $dateTo)
                                ->whereHas('budgetCompromiseDetails', function ($query) use ($account, $formulation) {
                                    $query
                                        ->where('budget_account_id', $account->budget_account_id)
                                        ->where('budget_sub_specific_formulation_id', $formulation->id);
                                })
                                ->orderBy('compromised_at', 'asc')
                                ->get();

                            foreach ($modifications as $modification) {
                                $totalModificationAmount = 0;
                                $dateModification = Carbon::parse($modification->approved_at)->monthName;
                                $postMonths = [];

                                if ($dateModification != '') {
                                    // Obtener el numero del mes
                                    $currentMonth = Carbon::parse($modification->approved_at)->month;

                                    // pushear todos los meses que esten por encima del numero del mes
                                    for ($i = $currentMonth; $i <= 12; $i++) {
                                        $postMonths[] = Carbon::parse($modification->approved_at)
                                            ->addMonths($i - $currentMonth)
                                            ->monthName;
                                    }
                                }

                                foreach ($modification->budgetModificationAccounts as $mKey => $modificationAccount) {
                                    if ($modificationAccount->operation == 'I' && $modificationAccount->budget_account_id == $account->budget_account_id) {
                                        $totalModificationAmount += $modificationAccount->amount;
                                    } elseif ($modificationAccount->operation == 'D' && $modificationAccount->budget_account_id == $account->budget_account_id) {
                                        $totalModificationAmount -= $modificationAccount->amount;
                                    }
                                }

                                $totalModificationAmount = (string)$totalModificationAmount;

                                $data[$code][$bAccount['code']]['months'][$dateModification]['month_modifications'] += $totalModificationAmount;
                            }

                            $anulatedStatus = DocumentStatus::where('action', 'AN')->first();

                            foreach ($compromises as $compromise) {
                                $totalAmount = 0;
                                $totalAmountCaused = 0;
                                $totalAmountPaid = 0;

                                $dateCompromise = Carbon::parse($compromise->compromised_at)->monthName;
                                $causedSum = false;

                                foreach ($compromise->budgetStages as $stage) {
                                    if ($stage->type == 'CAU' && $causedSum == false) {
                                        foreach ($compromise->budgetCompromiseDetails as $detail) {
                                            if (
                                                $detail->budget_account_id == $account->budget_account_id
                                                && $detail->document_status_id != $anulatedStatus->id
                                            ) {
                                                $totalAmountCaused += $detail->amount;
                                                $causedSum = true;
                                            }
                                        }
                                    }

                                    if ($stage->type == 'PAG') {
                                        foreach ($compromise->budgetCompromiseDetails as $detail) {
                                            if (
                                                $detail->budget_account_id == $account->budget_account_id
                                                && $detail->document_status_id != $anulatedStatus->id
                                            ) {
                                                $totalAmountPaid += $detail->amount;
                                            }
                                        }

                                        if (Module::has('Finance') && Module::isEnabled('Finance')) {
                                            if ($stage->stageable_type == \Modules\Finance\Models\FinancePaymentExecute::class) {
                                                $payment = \Modules\Finance\Models\FinancePaymentExecute::find($stage->stageable_id);

                                                foreach ($payment->financePaymentDeductions as $deduction) {
                                                    if (
                                                        !empty($deduction->deductionable->budget_account_id)
                                                        && $deduction->deductionable->budget_account_id == $account->budget_account_id
                                                    ) {
                                                        $totalAmountPaid -= $deduction->amount;
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    if ($stage->type == 'COM') {
                                        foreach ($compromise->budgetCompromiseDetails as $detail) {
                                            if (
                                                $detail->budget_account_id == $account->budget_account_id
                                                && $detail->document_status_id != $anulatedStatus->id
                                            ) {
                                                $totalAmount += $detail->amount;
                                            }
                                        }
                                    }
                                }

                                $totalAmountCaused = (string)$totalAmountCaused;
                                $totalAmountPaid = (string)$totalAmountPaid;
                                $totalAmount = (string)$totalAmount;

                                $data[$code][$bAccount['code']]['months'][$dateCompromise]['month_programmed'] += $totalAmount;
                                $data[$code][$bAccount['code']]['months'][$dateCompromise]['month_compromised'] += $totalAmount;
                                $data[$code][$bAccount['code']]['months'][$dateCompromise]['month_caused'] += $totalAmountCaused;
                                $data[$code][$bAccount['code']]['months'][$dateCompromise]['month_paid'] += $totalAmountPaid;
                                $data[$code][$bAccount['code']]['months'][$dateCompromise]['absolute_variation_caused_vs_programmed'] +=
                                    ($totalAmountCaused - $totalAmount);
                            }
                        }
                    }
                }
            }
        }

        $parentsArray = [];

        foreach ($data as $key => $value) {
            foreach ($value as $key2 => $value2) {
                $accountP = BudgetAccount::find($value2['budget_account_id']);
                if (count($accountP->accountChildrens) == 0) {
                    $parents = $this->getNewAccountParents($value2, []);
                    array_push($parentsArray, $parents);
                }
            }
        }

        $combined = [];

        foreach ($parentsArray as $array) {
            foreach ($array as $key => $account) {
                if (!array_key_exists($key, $combined)) {
                    $combined[$key] = $account;
                } else {
                    $combined[$key]['approved_budget'] += $account['approved_budget'];

                    foreach ($account['months'] as $month => $values) {
                        if (!array_key_exists($month, $combined[$key]['months'])) {
                            $combined[$key]['months'][$month] = $values;
                        } else {
                            foreach ($values as $month_key => $value) {
                                if (!array_key_exists($month_key, $combined[$key]['months'][$month])) {
                                    $combined[$key]['months'][$month][$month_key] = $value;
                                } else {
                                    $combined[$key]['months'][$month][$month_key] += $value;
                                }
                            }
                        }
                    }
                }
            }
        }

        foreach ($combined as $com) {
            if (!array_key_exists($com['account_code'], $data[$com['specific_action']])) {
                $data[$com['specific_action']][$com['account_code']] = $com;
            } else {
                foreach ($com['months'] as $month => $values) {
                    if (!array_key_exists($month, $data[$com['specific_action']][$com['account_code']]['months'])) {
                        $data[$com['specific_action']][$com['account_code']]['months'][$month] = $values;
                    } else {
                        foreach ($values as $value_key => $value) {
                            if (
                                !array_key_exists($value_key, $data[$com['specific_action']][$com['account_code']]['months'][$month])
                                || $data[$com['specific_action']][$com['account_code']]['months'][$month][$value_key] == 0
                            ) {
                                $data[$com['specific_action']][$com['account_code']]['months'][$month][$value_key] = $value;
                            }
                        }
                    }
                }
            }
        }

        $combined = [];

        foreach ($data as $key => $accounts) {
            // Extrae las últimas dos secciones del key
            preg_match('/(\w+-\w+)$/', $key, $matches);
            $identifier = $matches[1];

            foreach ($accounts as $account_code => $account) {
                if (!isset($combined[$identifier][$account_code])) {
                    $combined[$identifier][$account_code] = $account;
                } else {
                    $combined[$identifier][$account_code]['approved_budget'] += $account['approved_budget'];
                    foreach ($account['months'] as $month => $values) {
                        if (!isset($combined[$identifier][$account_code]['months'][$month])) {
                            $combined[$identifier][$account_code]['months'][$month] = $values;
                        } else {
                            foreach ($values as $value_key => $value) {
                                if (!isset($combined[$identifier][$account_code]['months'][$month][$value_key])) {
                                    $combined[$identifier][$account_code]['months'][$month][$value_key] = $value;
                                } else {
                                    $combined[$identifier][$account_code]['months'][$month][$value_key] += $value;
                                }
                            }
                        }
                    }
                }
            }
        }

        $months = [
            1 => 'enero',
            2 => 'febrero',
            3 => 'marzo',
            4 => 'abril',
            5 => 'mayo',
            6 => 'junio',
            7 => 'julio',
            8 => 'agosto',
            9 => 'septiembre',
            10 => 'octubre',
            11 => 'noviembre',
            12 => 'diciembre'
        ];

        foreach ($combined as $key => $value) {
            $data[$key] = $value;
        }

        foreach ($data as $key => $value) {
            ksort($data[$key]);

            foreach ($value as $key2 => $value2) {
                $monthKey = 1;
                foreach ($value2['months'] as $key3 => $value3) {
                    if ($key3 == 'enero') {
                        $data[$key][$key2]['months'][$key3]['budget_modifications'] += $data[$key][$key2]['months'][$key3]['month_modifications']
                        + $value2['approved_budget'];
                        $data[$key][$key2]['months'][$key3]['accumulated_programmed'] += $value3['month_programmed'];
                        $data[$key][$key2]['months'][$key3]['accumulated_compromised'] += $value3['month_compromised'];
                        $data[$key][$key2]['months'][$key3]['accumulated_caused'] += $value3['month_caused'];
                        $data[$key][$key2]['months'][$key3]['accumulated_paid'] += $value3['month_paid'];
                        $data[$key][$key2]['months'][$key3]['accumulated_absolute_variation_caused_vs_programmed'] += $value3['absolute_variation_caused_vs_programmed'];
                        $data[$key][$key2]['months'][$key3]['budgetary_availability_compromised'] = (
                            $data[$key][$key2]['months'][$key3]['budget_modifications']
                            - $value3['month_compromised']
                        );
                        $data[$key][$key2]['months'][$key3]['budgetary_availability_caused'] = (
                            $data[$key][$key2]['months'][$key3]['budget_modifications']
                            - $value3['month_caused']
                        );
                    } else {
                        $data[$key][$key2]['months'][$key3]['budget_modifications'] += $data[$key][$key2]['months'][$key3]['month_modifications']
                        + $data[$key][$key2]['months'][$months[$monthKey - 1]]['budgetary_availability_compromised'];
                        $data[$key][$key2]['months'][$key3]['accumulated_programmed'] += $value3['month_programmed']
                            + $data[$key][$key2]['months'][$months[$monthKey - 1]]['accumulated_programmed'];

                        $data[$key][$key2]['months'][$key3]['accumulated_compromised'] += $value3['month_compromised']
                            + $data[$key][$key2]['months'][$months[$monthKey - 1]]['accumulated_compromised'];

                        $data[$key][$key2]['months'][$key3]['accumulated_caused'] += $value3['month_caused']
                            + $data[$key][$key2]['months'][$months[$monthKey - 1]]['accumulated_caused'];

                        $data[$key][$key2]['months'][$key3]['accumulated_paid'] += $value3['month_paid']
                            + $data[$key][$key2]['months'][$months[$monthKey - 1]]['accumulated_paid'];

                        $data[$key][$key2]['months'][$key3]['accumulated_absolute_variation_caused_vs_programmed'] += $value3['absolute_variation_caused_vs_programmed']
                            + $data[$key][$key2]['months'][$months[$monthKey - 1]]['accumulated_absolute_variation_caused_vs_programmed'];

                        $data[$key][$key2]['months'][$key3]['budgetary_availability_compromised'] = (
                                $data[$key][$key2]['months'][$key3]['budget_modifications']
                                - $data[$key][$key2]['months'][$key3]['month_compromised']
                            );

                        $data[$key][$key2]['months'][$key3]['budgetary_availability_caused'] = (
                                $data[$key][$key2]['months'][$key3]['budget_modifications']
                                - $data[$key][$key2]['months'][$key3]['month_compromised']
                            );
                    }

                    $monthKey++;
                }
            }
        }

        foreach ($data as $key => $value) {
            ksort($data[$key]);

            foreach ($value as $key2 => $value2) {
                foreach ($value2['months'] as $key3 => $value3) {
                    foreach ($value3 as $key4 => $value4) {
                        $data[$key][$key2]['months'][$key3][$key4] = round($value4, 2);
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Método para buscar las cuentas padre al generar el reporte consolidado
     *
     * @method    getNewAccountParents
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    Array con las cuentas padre de la formulación
     */
    private function getNewAccountParents($child, $parents = [])
    {
        if (!isset($child)) {
            return $parents;
        }

        if (!array_key_exists($child['account_code'], $parents)) {
            $parents[$child['account_code']] = $child;
        }

        $accountChild = BudgetAccount::find($child['budget_account_id']);

        if ($accountChild->parent_id == null) {
            return $parents;
        } else {
            $accountChild->load('accountParent');
            $parent = [
                'specific_action' => $child['specific_action'],
                'account_code' => $accountChild->accountParent->code,
                'budget_account_id' => $accountChild->accountParent->id,
                'account_denomination' => $accountChild->accountParent->denomination,
                'months' => []
            ];

            if (!array_key_exists('approved_budget', $parent)) {
                $parent['approved_budget'] = $child['approved_budget'];
            } else {
                $parent['approved_budget'] += $child['approved_budget'];
            }

            foreach ($child['months'] as $month => $values) {
                if (!array_key_exists($month, $parent['months'])) {
                    $parent['months'][$month] = $values;
                } else {
                    foreach ($values as $key => $value) {
                        if (!array_key_exists($key, $parent['months'][$month])) {
                            $parent['months'][$month][$key] = $value;
                        } else {
                            $parent['months'][$month][$key] += $value;
                        }
                    }
                }
            }

            return $this->getNewAccountParents($parent, $parents);
        }
    }
}
