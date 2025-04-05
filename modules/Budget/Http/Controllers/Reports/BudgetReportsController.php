<?php

namespace Modules\Budget\Http\Controllers\Reports;

use App\Models\Profile;
use App\Models\FiscalYear;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\DocumentStatus;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Budget\Models\Currency;
use App\Repositories\ReportRepository;
use Carbon\Carbon;
use Modules\Budget\Models\Institution;
use Modules\Budget\Models\BudgetAccount;
use Modules\Budget\Models\BudgetProject;
use Modules\Budget\Exports\RecordsExport;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Modules\Budget\Actions\Reports\ExportConsolidatedReportAction;
use Modules\Budget\Models\BudgetAccountOpen;
use Modules\Budget\Models\BudgetCompromiseDetail;
use Modules\Budget\Models\BudgetCentralizedAction;
use Modules\Budget\Models\BudgetModificationAccount;
use Modules\Budget\Jobs\CreateBudgetAnalyticalMajorJob;
use Modules\Budget\Jobs\CreateAndSendBudgetConsolidatedReportJob;
use Modules\Budget\Models\BudgetCompromise;
use Modules\Budget\Models\BudgetSubSpecificFormulation;
use Modules\Budget\Exports\BudgetCompromiseExport;
use Illuminate\Support\Str;
use Modules\Budget\Models\BudgetModification;
use Modules\Budget\Models\BudgetSpecificAction;
use Nwidart\Modules\Facades\Module;
use Modules\Budget\Exports\BudgetFormulatedSheetExport;

/**
 * @class BudgetAccountOpenController
 * @brief Clase para generar reporte de disponibilad presupuestaria
 *
 * Clase para generar reporte de disponibilad presupuestaria
 *
 * @author Jonathan Alvarado <wizardx1407@gmail.com> | <jonathanalvarado1407@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetReportsController extends Controller
{
    /**
     * Método constructor de la clase
     *
     * @return void
     */
    public function __construct()
    {
        /* Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:budget.analyticalmajor.index', ['only' => 'budgetAnalyticalMajor']);
        $this->middleware('permission:budget.budgetavailability.index', ['only' => 'budgetAvailability']);
        $this->middleware('permission:budget.formulated.index', ['only' => 'getFormulatedView']);
    }

    /**
     * Genera los datos necesarios para el formulario de generacion de reporte de disponibilidad presupuestaria
     *
     * @author    Jonathan Alvarado <wizardx1407@gmail.com> | <jonathanalvarado1407@gmail.com>
     *
     * @return    Renderable|array
     */
    public function budgetAvailability($flag = null)
    {
        $budgetItems = $this->getBudgetAccounts();
        $budgetProjects = $this->getBudgetProjects(true);
        $budgetCentralizedActions = $this->getBudgetCentralizedActions(true);

        $data = array();
        $temp = array('text' => '', 'children' => []);
        $isFirst = true;

        foreach ($budgetItems as $budgetItem) {
            $code = str_replace('.', '', $budgetItem->getCodeAttribute());

            if (substr_count($code, '0') == 8) {
                if (!$isFirst) {
                    array_push($data, $temp);
                    $temp = array('text' => '', 'children' => []);
                }

                $temp['text'] = $budgetItem->denomination;
                $isFirst = false;
            }

            array_push($temp['children'], array(
                'ID' => (int) $budgetItem->id,
                'id' => (int) $code,
                'text' => $budgetItem->denomination . ' ' . "($code)",
            ));
        }

        array_push($data, $temp);

        if ($flag) {
            return [
                'budgetItems' => json_encode($data),
                'budgetProjects' => json_encode($budgetProjects),
                'budgetCentralizedActions' => json_encode($budgetCentralizedActions),
            ];
        }

        return view('budget::reports.budgetAvailability', [
            'budgetItems' => json_encode($data),
            'budgetProjects' => json_encode($budgetProjects),
            'budgetCentralizedActions' => json_encode($budgetCentralizedActions),
        ]);
    }

    /**
     * Metodo para retornar un array con las cuentas presupuestarias
     *
     * @author    Jonathan Alvarado <wizardx1407@gmail.com> | <jonathanalvarado1407@gmail.com>
     *
     * @return    array Arreglo ordenado de cuentas presupuestarias
     */
    public function getBudgetAccounts()
    {

        $budgetItems = BudgetAccount::all()->all();

        usort($budgetItems, function ($budgetItemOne, $budgetItemTwo) {

            $codeOne = str_replace('.', '', $budgetItemOne->getCodeAttribute());
            $codeTwo = str_replace('.', '', $budgetItemTwo->getCodeAttribute());

            if ($codeOne > $codeTwo) {
                return 1;
            } elseif ($codeOne == $codeTwo) {
                return 0;
            } else {
                return -1;
            }
        });

        return $budgetItems;
    }

    /**
     * Metodo para retornar un array con las cuentas presupuestarias para el reporte
     *
     * @author  Jonathan Alvarado <wizardx1407@gmail.com> | <jonathanalvarado1407@gmail.com>
     * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param   bool    $accountsWithMovements Indica si se consultan solo las cuentas con movimientos
     * @param   object  $project Datos del proyecto
     * @param   string  $initialDate Fecha inicial de consulta
     * @param   string  $finalDate Fecha final de consulta
     * @param   Array   $budgetItemsIds IDs de las Cuentas presupuestarias
     *
     * @return  array Arreglo ordenado de cuentas presupuestarias para el reporte
     */
    public function getBudgetAccountsOpen(bool $accountsWithMovements, object $project, string $initialDate, string $finalDate, array $budgetItemsIds = [])
    {
        ini_set('max_execution_time', 600);
        $project_accounts_open = array();
        $compromised = 0;
        $documentStatus = DocumentStatus::getStatus('AP');

        foreach ($project->specificActions as $specificAction) {
            $finalAccounts = [];
            $subSpecificFormulation = $specificAction
                ->subSpecificFormulations
                ->where('year', Carbon::parse($initialDate)->format('Y'))
                ->first();

            $accounts = BudgetAccount::query()
                ->when(count($budgetItemsIds) > 0, function ($query) use ($budgetItemsIds) {
                    $query->whereIn('id', $budgetItemsIds);
                })
                ->whereHas('accountOpens', function ($query) use ($initialDate, $finalDate) {
                    $query
                        ->with('subSpecificFormulation')
                        ->whereHas('subSpecificFormulation', function ($query) use ($initialDate, $finalDate) {
                            $query
                                ->where('date', '>=', $initialDate)
                                ->where('date', '<=', $finalDate);
                        });
                })
                ->with(['accountOpens' => function ($query) use ($subSpecificFormulation) {
                    $query->where('budget_sub_specific_formulation_id', $subSpecificFormulation->id);
                }, 'accountParent'])
                ->get();

            $modificationAccounts = BudgetAccount::query()
                ->when(count($budgetItemsIds) > 0, function ($query) use ($budgetItemsIds) {
                    $query->whereIn('id', $budgetItemsIds);
                })
                ->whereHas('modificationAccounts.budgetModification', function ($query) use ($initialDate, $finalDate, $documentStatus) {
                    $query
                        ->where('document_status_id', $documentStatus->id)
                        ->where('status', 'AP')
                        ->whereDate('approved_date', '>=', $initialDate)
                        ->whereDate('approved_date', '<=', $finalDate);
                })
                ->with(['modificationAccounts' => function ($query) use ($subSpecificFormulation) {
                    $query->where('budget_sub_specific_formulation_id', $subSpecificFormulation->id);
                }, 'accountParent', 'modificationAccounts.budgetSubSpecificFormulation.accountOpens',
                'modificationAccounts.budgetModification'])
                ->get();

            $formFormId = [];
            $formComKey = [];
            $formCauKey = [];
            $formPaidKey = [];

            foreach ($accounts as $account) {
                $arrayMod = [];

                if (isset($account['accountOpens']) && isset($account['accountOpens'][0])) {
                    if (!array_key_exists($account->code, $finalAccounts)) {
                        $account['self_amount'] = $account['accountOpens'][0]['total_year_amount'];
                        $account['compromised'] = 0;
                        $account['caused'] = 0;
                        $account['paid'] = 0;
                        $account['increment'] = 0;
                        $account['date'] = $account['accountOpens'][0]['subSpecificFormulation']['date'] ??
                            $account['accountOpens'][0]['created_at'];

                        if (!in_array($account['accountOpens'][0]['id'], $formFormId)) {
                            array_push($formFormId, $account['accountOpens'][0]['id']);
                            $compromisedAmount = (array)$this->getAccountCompromisedAmout($account['accountOpens'][0], $initialDate, $finalDate);
                            $causedAmount = (array)$this->getAccountCompromisedCausedAmount($account['accountOpens'][0], $initialDate, $finalDate);
                            $paidAmount = (array)$this->getAccountCompromisedPaidAmount($account['accountOpens'][0], $initialDate, $finalDate);
                        } else {
                            $compromisedAmount = [];
                            $causedAmount = [];
                            $paidAmount = [];
                        }

                        if (count($compromisedAmount) > 0) {
                            foreach ($compromisedAmount as $key => $value) {
                                $data = [
                                    'current' => $value['amount'],
                                    'self_available' => $value['amount'],
                                    'increment' => 0,
                                    'decrement' => 0,
                                    'compromised' => $value['amount'],
                                    'caused' => 0,
                                    'paid' => 0,
                                    'date' => $value['date']->setTime(1, 0)->format('Y-m-d H:i:s'),
                                    'decrement_descriptions' => $value['description']
                                ];

                                if (!in_array($key . $account['accountOpens'][0]['budget_account_id'], $formComKey)) {
                                    array_push($formComKey, $key . $account['accountOpens'][0]['budget_account_id']);
                                    array_push($arrayMod, $data);
                                }
                            }
                        }

                        if (count($causedAmount) > 0) {
                            foreach ($causedAmount as $key => $value) {
                                $data = [
                                    'current' => $value['amount'],
                                    'self_available' => $value['amount'],
                                    'increment' => 0,
                                    'decrement' => 0,
                                    'compromised' => 0,
                                    'caused' => $value['amount'],
                                    'paid' => 0,
                                    'date' => $value['date']->setTime(2, 0)->format('Y-m-d H:i:s'),
                                    'decrement_descriptions' => $compromisedAmount[$key]['description']
                                ];

                                if (!in_array($key . $account['accountOpens'][0]['budget_account_id'], $formCauKey)) {
                                    array_push($formCauKey, $key . $account['accountOpens'][0]['budget_account_id']);
                                    array_push($arrayMod, $data);
                                }
                            }
                        }

                        if (count($paidAmount) > 0) {
                            foreach ($paidAmount as $key => $value) {
                                $data = [
                                    'current' => $value['amount'],
                                    'self_available' => $value['amount'],
                                    'increment' => 0,
                                    'decrement' => 0,
                                    'compromised' => 0,
                                    'caused' => 0,
                                    'paid' => $value['amount'],
                                    'date' => $value['date']->setTime(3, 0)->format('Y-m-d H:i:s'),
                                    'decrement_descriptions' => $compromisedAmount[$key]['description']
                                ];

                                if (!in_array($key . $account['accountOpens'][0]['budget_account_id'], $formPaidKey)) {
                                    array_push($formPaidKey, $key . $account['accountOpens'][0]['budget_account_id']);
                                    array_push($arrayMod, $data);
                                }
                            }
                        }

                        $account['modifications'] = collect($arrayMod)->sortBy('date')->toArray();
                        $finalAccounts[$account->code] = $account;
                    }
                }
            }

            $arrayId = [];
            $arrayComKey = [];
            $arrayCauKey = [];
            $arrayPaidKey = [];

            foreach ($modificationAccounts as $modificationAccount) {
                if (isset($modificationAccount['modificationAccounts']) && isset($modificationAccount['modificationAccounts'][0])) {
                    $arrayMod = [];
                    foreach ($modificationAccount['modificationAccounts'] as $modAccount) {
                        if ($modAccount['operation'] == 'I') {
                            $data = [
                                'current' => $modAccount['amount'],
                                'self_available' => $modAccount['amount'],
                                'increment' => $modAccount['amount'],
                                'decrement' => 0,
                                'compromised' => 0,
                                'caused' => 0,
                                'paid' => 0,
                                'date' => $modAccount['budgetModification']['approved_at'],
                                'increment_descriptions' => $modAccount['budgetModification']['description']
                            ];
                            array_push($arrayMod, $data);
                        } else {
                            $data = [
                                'current' => $modAccount['amount'],
                                'self_available' => $modAccount['amount'],
                                'increment' => 0,
                                'decrement' => $modAccount['amount'],
                                'compromised' => 0,
                                'caused' => 0,
                                'paid' => 0,
                                'date' => $modAccount['budgetModification']['approved_at'],
                                'decrement_descriptions' => $modAccount['budgetModification']['description']
                            ];
                            array_push($arrayMod, $data);
                        }

                        if (!in_array($modAccount->id, $arrayId)) {
                            array_push($arrayId, $modAccount->id);
                            $compromisedAmount = (array)$this->getAccountCompromisedAmout($modAccount, $initialDate, $finalDate);
                            $causedAmount = (array)$this->getAccountCompromisedCausedAmount($modAccount, $initialDate, $finalDate);
                            $paidAmount =  (array)$this->getAccountCompromisedPaidAmount($modAccount, $initialDate, $finalDate);
                        } else {
                            $compromisedAmount = [];
                            $causedAmount = [];
                            $paidAmount = [];
                        }

                        if (count($compromisedAmount) > 0) {
                            foreach ($compromisedAmount as $key => $value) {
                                $data = [
                                    'current' => $value['amount'],
                                    'self_available' => $value['amount'],
                                    'increment' => 0,
                                    'decrement' => 0,
                                    'compromised' => $value['amount'],
                                    'caused' => 0,
                                    'paid' => 0,
                                    'date' => $value['date']->setTime(1, 0)->format('Y-m-d H:i:s'),
                                    'decrement_descriptions' => $value['description']
                                ];

                                if (!in_array($key . $modAccount->budget_account_id, $arrayComKey)) {
                                    array_push($arrayComKey, $key . $modAccount->budget_account_id);
                                    array_push($arrayMod, $data);
                                }
                            }
                        }

                        if (count($causedAmount) > 0) {
                            foreach ($causedAmount as $key => $value) {
                                $data = [
                                    'current' => $value['amount'],
                                    'self_available' => $value['amount'],
                                    'increment' => 0,
                                    'decrement' => 0,
                                    'compromised' => 0,
                                    'caused' => $value['amount'],
                                    'paid' => 0,
                                    'date' => $value['date']->setTime(2, 0)->format('Y-m-d H:i:s'),
                                    'decrement_descriptions' => $compromisedAmount[$key]['description']
                                ];

                                if (!in_array($key . $modAccount->budget_account_id, $arrayCauKey)) {
                                    array_push($arrayCauKey, $key . $modAccount->budget_account_id);
                                    array_push($arrayMod, $data);
                                }
                            }
                        }

                        if (count($paidAmount) > 0) {
                            foreach ($paidAmount as $key => $value) {
                                $data = [
                                    'current' => $value['amount'],
                                    'self_available' => $value['amount'],
                                    'increment' => 0,
                                    'decrement' => 0,
                                    'compromised' => 0,
                                    'caused' => 0,
                                    'paid' => $value['amount'],
                                    'date' => $value['date']->setTime(3, 0)->format('Y-m-d H:i:s'),
                                    'decrement_descriptions' => $compromisedAmount[$key]['description']
                                ];

                                if (!in_array($key . $modAccount->budget_account_id, $arrayPaidKey)) {
                                    array_push($arrayPaidKey, $key . $modAccount->budget_account_id);
                                    array_push($arrayMod, $data);
                                }
                            }
                        }

                        if (array_key_exists($modificationAccount->code, $finalAccounts)) {
                            foreach ($modAccount['budgetSubSpecificFormulation']['accountOpens'] as $accsOpen) {
                                if ($accsOpen['budget_account_id'] == $modificationAccount['id']) {
                                    $modificationAccount['date'] = $accsOpen->subSpecificFormulation->date ??
                                        $accsOpen['created_at'];
                                    $modificationAccount['self_amount'] = $accsOpen['total_year_amount'];
                                }
                            };
                        }

                        $modificationAccount['modifications'] = collect($arrayMod)->sortBy('date')->toArray();
                        $finalAccounts[$modificationAccount->code] = $modificationAccount;
                    }
                }
            }

            $parentsArray = [];

            foreach ($finalAccounts as $finalAccount) {
                $parents = $this->getAccountParents($finalAccount, []);
                array_push($parentsArray, $parents);
            }

            foreach ($parentsArray as $pArray) {
                foreach ($pArray as $pA) {
                    if (!array_key_exists($pA->code, $finalAccounts)) {
                        $finalAccounts[$pA->code] = $pA;
                    }
                }
            }

            krsort($finalAccounts);

            foreach ($finalAccounts as $finalAccount) {
                if ($finalAccount->parent_id != null) {
                    $finalAccountParent = $finalAccount->getParent(
                        $finalAccount->group,
                        $finalAccount->item,
                        $finalAccount->generic,
                        $finalAccount->specific,
                        $finalAccount->subspecific
                    );

                    if ($finalAccount->generic == 00) {
                        if (isset($finalAccounts[$finalAccountParent->code])) {
                            $finalAccounts[$finalAccountParent->code]['increment_total'] += $finalAccount['increment_total'] ??
                                $finalAccount['increment'];
                            $finalAccounts[$finalAccountParent->code]['decrement_total'] += $finalAccount['decrement_total'] ??
                                $finalAccount['decrement'];
                            $finalAccounts[$finalAccountParent->code]['compromised_total'] += $finalAccount['compromised_total'] ??
                                $finalAccount['compromised'];
                            $finalAccounts[$finalAccountParent->code]['compromised_caused'] += $finalAccount['compromised_caused'] ??
                                $finalAccount['caused'];
                            $finalAccounts[$finalAccountParent->code]['compromised_paid'] += $finalAccount['compromised_paid'] ??
                                $finalAccount['paid'];
                            $finalAccounts[$finalAccountParent->code]['date'] = $finalAccount['date'] ??
                                $finalAccount['accountOpens'][0]['created_at'] ??
                                $finalAccount['modificationAccounts'][0]['budgetSubSpecificFormulation']['created_at'];

                            if (isset($finalAccount['modifications']) && count($finalAccount['modifications']) > 0) {
                                foreach ($finalAccount['modifications'] as $mod) {
                                    $finalAccounts[$finalAccountParent->code]['increment_total'] += $mod['increment_total'] ??
                                        $mod['increment'];
                                    $finalAccounts[$finalAccountParent->code]['decrement_total'] += $mod['decrement_total'] ??
                                        $mod['decrement'];
                                    $finalAccounts[$finalAccountParent->code]['compromised_total'] += $mod['compromised_total'] ??
                                        $mod['compromised'];
                                    $finalAccounts[$finalAccountParent->code]['compromised_caused'] += $mod['compromised_caused'] ??
                                        $mod['caused'];
                                    $finalAccounts[$finalAccountParent->code]['compromised_paid'] += $mod['compromised_paid'] ??
                                        $mod['paid'];
                                    $finalAccounts[$finalAccountParent->code]['date'] = $mod['date'];
                                }
                            }
                        }
                    } elseif ($finalAccount->specific == 00) {
                        if (isset($finalAccounts[$finalAccountParent->code])) {
                            $finalAccounts[$finalAccountParent->code]['increment_total'] += $finalAccount['increment_total'] ??
                                $finalAccount['increment'];
                            $finalAccounts[$finalAccountParent->code]['decrement_total'] += $finalAccount['decrement_total'] ??
                                $finalAccount['decrement'];
                            $finalAccounts[$finalAccountParent->code]['compromised_total'] += $finalAccount['compromised_total'] ??
                                $finalAccount['compromised'];
                            $finalAccounts[$finalAccountParent->code]['compromised_caused'] += $finalAccount['compromised_caused'] ??
                                $finalAccount['caused'];
                            $finalAccounts[$finalAccountParent->code]['compromised_paid'] += $finalAccount['compromised_paid'] ??
                                $finalAccount['paid'];
                            $finalAccounts[$finalAccountParent->code]['date'] = $finalAccount['date'] ??
                                $finalAccount['accountOpens'][0]['created_at'] ??
                                $finalAccount['modificationAccounts'][0]['budgetSubSpecificFormulation']['created_at'];

                            if (isset($finalAccount['modifications']) && count($finalAccount['modifications']) > 0) {
                                foreach ($finalAccount['modifications'] as $mod) {
                                    $finalAccounts[$finalAccountParent->code]['increment_total'] += $mod['increment_total'] ??
                                        $mod['increment'];
                                    $finalAccounts[$finalAccountParent->code]['decrement_total'] += $mod['decrement_total'] ??
                                        $mod['decrement'];
                                    $finalAccounts[$finalAccountParent->code]['compromised_total'] += $mod['compromised_total'] ??
                                        $mod['compromised'];
                                    $finalAccounts[$finalAccountParent->code]['compromised_caused'] += $mod['compromised_caused'] ??
                                        $mod['caused'];
                                    $finalAccounts[$finalAccountParent->code]['compromised_paid'] += $mod['compromised_paid'] ??
                                        $mod['paid'];
                                    $finalAccounts[$finalAccountParent->code]['date'] = $mod['date'];
                                }
                            }
                        }
                    } elseif ($finalAccount->subspecific == 00) {
                        if (isset($finalAccounts[$finalAccountParent->code])) {
                            $finalAccounts[$finalAccountParent->code]['increment_total'] += $finalAccount['increment_total'] ??
                                $finalAccount['increment'];
                            $finalAccounts[$finalAccountParent->code]['decrement_total'] += $finalAccount['decrement_total'] ??
                                $finalAccount['decrement'];
                            $finalAccounts[$finalAccountParent->code]['compromised_total'] += $finalAccount['compromised_total'] ??
                                $finalAccount['compromised'];
                            $finalAccounts[$finalAccountParent->code]['compromised_caused'] += $finalAccount['compromised_caused'] ??
                                $finalAccount['caused'];
                            $finalAccounts[$finalAccountParent->code]['compromised_paid'] += $finalAccount['compromised_paid'] ??
                                $finalAccount['paid'];
                            $finalAccounts[$finalAccountParent->code]['date'] = $finalAccount['date'];

                            if (isset($finalAccount['modifications']) && count($finalAccount['modifications']) > 0) {
                                foreach ($finalAccount['modifications'] as $mod) {
                                    $finalAccounts[$finalAccountParent->code]['increment_total'] += $mod['increment_total'] ??
                                        $mod['increment'];
                                    $finalAccounts[$finalAccountParent->code]['decrement_total'] += $mod['decrement_total'] ??
                                        $mod['decrement'];
                                    $finalAccounts[$finalAccountParent->code]['compromised_total'] += $mod['compromised_total'] ??
                                        $mod['compromised'];
                                    $finalAccounts[$finalAccountParent->code]['compromised_caused'] += $mod['compromised_caused'] ??
                                        $mod['caused'];
                                    $finalAccounts[$finalAccountParent->code]['compromised_paid'] += $mod['compromised_paid'] ??
                                        $mod['paid'];
                                    $finalAccounts[$finalAccountParent->code]['date'] = $mod['date'];
                                }
                            }
                        }
                    } elseif ($finalAccount->subspecific != 00) {
                        if (isset($finalAccounts[$finalAccountParent->code])) {
                            $finalAccounts[$finalAccountParent->code]['increment_total'] += $finalAccount['increment'];
                            $finalAccounts[$finalAccountParent->code]['decrement_total'] += $finalAccount['decrement'];
                            $finalAccounts[$finalAccountParent->code]['compromised_total'] += $finalAccount['compromised'];
                            $finalAccounts[$finalAccountParent->code]['compromised_caused'] += $finalAccount['caused'];
                            $finalAccounts[$finalAccountParent->code]['compromised_paid'] += $finalAccount['paid'];
                            $finalAccounts[$finalAccountParent->code]['date'] = $finalAccount['date'] ??
                                $finalAccount['modificationAccounts'][0]['budgetSubSpecificFormulation']['created_at'];

                            if (isset($finalAccount['modifications']) && count($finalAccount['modifications']) > 0) {
                                foreach ($finalAccount['modifications'] as $mod) {
                                    $finalAccounts[$finalAccountParent->code]['increment_total'] += $mod['increment'];
                                    $finalAccounts[$finalAccountParent->code]['decrement_total'] += $mod['decrement'];
                                    $finalAccounts[$finalAccountParent->code]['compromised_total'] += $mod['compromised'];
                                    $finalAccounts[$finalAccountParent->code]['compromised_caused'] += $mod['caused'];
                                    $finalAccounts[$finalAccountParent->code]['compromised_paid'] += $mod['paid'];
                                    $finalAccounts[$finalAccountParent->code]['date'] = $mod['date'];
                                }
                            }
                        }
                    }
                }

                if (!isset($finalAccount['increment_total'])) {
                    $finalAccount['increment_total'] = $finalAccount['increment'];
                }

                if (!isset($finalAccount['decrement_total'])) {
                    $finalAccount['decrement_total'] = $finalAccount['decrement'];
                }

                if (!isset($finalAccount['compromised_total'])) {
                    $finalAccount['compromised_total'] = $finalAccount['compromised'];
                }

                if (!isset($finalAccount['compromised_caused'])) {
                    $finalAccount['compromised_caused'] = $finalAccount['caused'];
                }

                if (!isset($finalAccount['compromised_paid'])) {
                    $finalAccount['compromised_paid'] = $finalAccount['paid'];
                }

                if (!isset($finalAccount['date'])) {
                    $finalAccount['date'] = $finalAccount['accountOpens'][0]['created_at'] ??
                        $finalAccount['modificationAccounts'][0]['budgetSubSpecificFormulation']['created_at'] ??
                        $finalAccount['modificationAccounts'][0]['created_at'] ?? null;
                }

                $finalAccount['current'] = $finalAccount['self_amount'] + $finalAccount['increment_total'] -
                    $finalAccount['decrement_total'];

                $finalAccount['self_available'] = $finalAccount['current'] - $finalAccount['compromised_total'];

                if (isset($finalAccount['modifications']) && count($finalAccount['modifications']) > 0) {
                    $accountCurrent = $finalAccount['current'];
                    $available = $finalAccount['current'];

                    $finalAccount['modifications'] = collect($finalAccount['modifications'])->map(function ($mod) use (
                        &$accountCurrent,
                        &$available
                    ) {
                        if ($mod['increment'] > 0) {
                            $accountCurrent = $accountCurrent + $mod['current'];
                            $available = $available + $mod['current'];
                        } elseif ($mod['decrement'] > 0) {
                            $accountCurrent = $accountCurrent - $mod['current'];
                            $available = $available - $mod['current'];
                        } elseif ($mod['compromised'] > 0) {
                            $available = $available - $mod['current'];
                        } elseif ($mod['compromised'] < 0) {
                            $available = $available - $mod['current'];
                        }

                        $mod['current'] = $accountCurrent;
                        $mod['self_available'] = $available;

                        $previousCurrent = $available;

                        return $mod;
                    })->all();
                }
            }

            ksort($finalAccounts);

            array_push(
                $project_accounts_open,
                [
                    $finalAccounts,
                    $subSpecificFormulation,
                    "project_code" => $project->code,
                    "specific_action_code" => $specificAction->code,
                    "specific_action_name" => $specificAction->name,
                ]
            );
        }

        foreach ($project_accounts_open as $accounts) {
            array_filter($accounts[0], function ($account) use ($accountsWithMovements) {
                if ($accountsWithMovements && ($account['amount_available'] === $account['total_year_amount'])) {
                    return false;
                }

                return true;
            });
        }

        return $project_accounts_open;
    }

    /**
     * Metodo que retorna el monto comprometido para la cuenta $account_id
     *
     * @author José Briceño <josejorgebriceno9@gmail.com>
     *
     * @param integer   $account_id     Datos de la cuenta presupuestaria
     * @param string    $initialDate    Fecha inicial de consulta
     * @param string    $finalDate      Fecha final de consulta
     *
     * @return array Monto del compromiso para la cuenta presupuestaria con 'id' $account_id     *
     */
    public function getAccountCompromisedAmout(object $accout_id, $initialDate, $finalDate)
    {
        $compromised = BudgetCompromiseDetail::query()
            ->with(['budgetCompromise' => function ($query) use ($initialDate, $finalDate) {
                $query->whereBetween('compromised_at', [$initialDate, $finalDate]);
            }])
            ->where('budget_sub_specific_formulation_id', $accout_id->budget_sub_specific_formulation_id)
            ->where('budget_account_id', $accout_id->budget_account_id)
            ->get();

        $compromises = [];

        $anulatedStatus = DocumentStatus::where('action', 'AN')->first();

        if (!$compromised->isEmpty()) {
            foreach ($compromised as $com) {
                if ($com->budgetCompromise) {
                    $budgetCompromiseId = $com->budgetCompromise->id;

                    // Iteración con monto en positivo
                    $positiveKey = $budgetCompromiseId;
                    if (!array_key_exists($positiveKey, $compromises)) {
                        $compromises[$positiveKey] = [
                            'amount' => 0,
                            'description' => '',
                            'date' => '',
                        ];
                    }

                    $compromises[$positiveKey]['amount'] += $com->amount;
                    $compromises[$positiveKey]['description'] = $com->budgetCompromise->description;

                    if (gettype($com->budgetCompromise->compromised_at) === 'string') {
                        $compromises[$positiveKey]['date'] = \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $com->budgetCompromise->compromised_at);
                    } else {
                        $compromises[$positiveKey]['date'] = $com->budgetCompromise->compromised_at;
                    }

                    // Iteración con monto en negativo
                    if ($anulatedStatus->id == $com->document_status_id) {
                        $negativeKey = $budgetCompromiseId . '_negative';
                        if (!array_key_exists($negativeKey, $compromises)) {
                            $compromises[$negativeKey] = [
                                'amount' => 0,
                                'description' => '',
                                'date' => '',
                            ];
                        }

                        $compromises[$negativeKey]['amount'] += $com->amount * -1;
                        $compromises[$negativeKey]['description'] = $com->budgetCompromise->description;

                        if (gettype($com->budgetCompromise->compromised_at) === 'string') {
                            $compromises[$negativeKey]['date'] = \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $com->budgetCompromise->compromised_at);
                        } else {
                            $compromises[$negativeKey]['date'] = $com->budgetCompromise->compromised_at;
                        }
                    }
                }
            }

            return $compromises;
        }
        return $compromises;
    }

    /**
     * Metodo que retorna las modificaciones de una cuenta
     *
     * @author José Briceño <josejorgebriceno9@gmail.com>
     *
     * @param int $account_budget_sub_specific_formulation_id Identificador de la cuenta presupuestaria
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|null Objecto que contiene aumentos y
     *                                                                                               disminuciones
     */
    public function getAccountModifications(int $account_budget_sub_specific_formulation_id)
    {
        $documentStatus = DocumentStatus::getStatus('AP');
        $modifications = BudgetModificationAccount::query()
            ->whereHas('budgetModification', function ($query) use ($documentStatus) {
                $query
                    ->where('document_status_id', $documentStatus->id)
                    ->where('status', 'AP');
            })
            ->with('budgetModification')
            ->where('budget_sub_specific_formulation_id', $account_budget_sub_specific_formulation_id)
            ->get();
        return !$modifications->isEmpty() ? $modifications : null;
    }

    /**
     * Metodo para filtrar y retornar un array con las cuentas presupuestarias formuladas
     *
     * @author    Jonathan Alvarado <wizardx1407@gmail.com> | <jonathanalvarado1407@gmail.com>     *
     * @author José Briceño <josejorgebriceno9@gmail.com>
     *
     * @param array     $budgetAccountsOpen Arreglo de cuentas presupuestarias abiertas
     * @param integer   $initialCode Código inicial de la Cuenta presupuestaria
     * @param integer   $finalCode Código final de la Cuenta presupuestaria
     * @param string    $initialDate Fecha inicial de la Cuenta presupuestaria
     * @param string    $finalDate Fecha final de la Cuenta presupuestaria
     *
     * @return    array Arreglo ordenado de cuentas presupuestarias formuladas
     */
    public function filterBudgetAccounts(
        array $budgetAccountsOpen,
        int $initialCode,
        int $finalCode,
        string $initialDate,
        string $finalDate
    ) {
        $filteredArray = array();

        foreach ($budgetAccountsOpen as $budgetItem) {
            if ($budgetItem->code > $finalCode) {
                break;
            }

            if (isset($budgetItem->budgetAccount)) {
                $code = str_replace('.', '', $budgetItem->budgetAccount->getCodeAttribute());
            } else {
                $code = str_replace('.', '', $budgetItem->getCodeAttribute());
            }

            if ($code >= $initialCode && $code <= $finalCode) {
                array_push($filteredArray, $budgetItem);
            }
        }

        return $filteredArray;
    }

    /**
     * Metodo para generar el reporte en PDF de disponibilad presupuestaria
     *
     * @author    Jonathan Alvarado <wizardx1407@gmail.com> | <jonathanalvarado1407@gmail.com>     *
     * @author  José Briceño <josejorgebriceno9@gmail.com>
     *
     * @param Request $request Datos de la petición
     *
     * @return void
     */
    public function getPdf(Request $request)
    {
        $data = $request->validate([
            'initialDate' => ['required', 'before_or_equal:finalDate'],
            'finalDate' => ['required', 'after_or_equal:initialDate'],
            'accountsWithMovements' => 'required',
            'project_id' => 'required',
            'project_type' => 'required',
            'specific_actions_ids' => 'required',
            'budget_items_ids' => 'requiredif:all_budget_items,1',
        ]);

        $ids = $data["specific_actions_ids"];

        if ($request->project_type === 'project') {
            $project = BudgetProject::with(['specificActions' => function ($query) use ($ids) {
                $query->with(['subSpecificFormulations' => function ($query) {
                    $query->with(['accountOpens' => function ($query) {
                        $query->with('budgetAccount');
                        $query->orderBy('id', 'desc');
                    }])->whereHas('accountOpens');
                }])->whereIn('id', $ids)->get();
            }])->find($data["project_id"]);
        } else {
            $project = BudgetCentralizedAction::with(['specificActions' => function ($query) use ($ids) {
                $query->with(['subSpecificFormulations' => function ($query) {
                    $query->with(['accountOpens' => function ($query) {
                        $query->with('budgetAccount');
                        $query->orderBy('id', 'desc');
                    }])->whereHas('accountOpens');
                }])->whereIn('id', $ids)->get();
            }])->find($data["project_id"]);
        }

        $records = $this->getBudgetAccountsOpen($data['accountsWithMovements'], $project, $data['initialDate'], $data['finalDate'], $data['budget_items_ids']);
        $records = $this->getLastAccountsOpen($records);

        if ($request['initialCode'] && $request['finalCode']) {
            for ($i = 0; $i < count($records); $i++) {
                $records[$i][0] = $this->filterBudgetAccounts($records[$i][0], $data['initialCode'], $data['finalCode'], $data['initialDate'], $data['finalDate']);
            }
        }

        $is_admin = auth()->user()->level == 1 ? true : false;
        if ($is_admin) {
            $institution = Institution::where('default', true)->first();
        } else {
            $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();
            $institution = $user_profile['institution'];
        }
        $fiscal_year = FiscalYear::where('active', true)->first();
        $currency = Currency::where('default', true)->first();
        $project = $records[0][1]?->specificAction?->specificable;
        $profile = Profile::where('user_id', auth()->user()->id)->first();
        $date = 'Información Presupuestaria desde ' . \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $data['initialDate'])->format('d/m/Y') . ' hasta ' . \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $data['finalDate'])->format('d/m/Y');

        if ($request->exportReport) {
            return Excel::download(new RecordsExport([
                'records' => $records, 'institution' => $institution,
                'currencySymbol' => $currency['symbol'],
                'fiscal_year' => $fiscal_year['year'],
                'report_date' => \Carbon\Carbon::today()->format('d-m-Y'),
                'initialDate' => '',
                'finalDate' => '',
                'report_type_id' => '3',
                'profile' => $profile,
                'project' => $project,
                'date' => $date,
                'report_view' => 'budget::pdf.budgetAvailability',
            ]), now()->format('d-m-Y') . '_reporte_disponibilidad_presupuestaria.csv');
        } else {
            $pdf = new ReportRepository();
            $pdf->setConfig(['institution' => $institution, 'orientation' => 'P', 'reportDateIssues #2010: Presupuesto ->Reportes->Disponiblidad presupuestaria>Generar reporte.' => '', 'urlVerify'   => url('')]);
            $pdf->setHeader('Reporte de Disponibilidad Presupuestaria', $date);
            $pdf->setFooter();
            $pdf->setBody('budget::pdf.budgetAvailability', true, [
                'pdf' => $pdf,
                'records' => $records,
                'institution' => $institution,
                'currencySymbol' => $currency['symbol'],
                'fiscal_year' => $fiscal_year['year'],
                "report_date" => \Carbon\Carbon::today()->format('d-m-Y'),
                'profile' => $profile,
                'project' => $project,
            ]);
        }
    }

    /**
     * Actualiza el arreglo de registros con el monto total disponible para las cuentas madres.
     *
     * Itera a través de los registros, identificando cuentas padre (con código '00') y cuentas hijas.
     * Para cada cuenta padre, calcula el monto total disponible a partir de la ultima modificacion de sus cuentas hijas y actualiza el valor de 'self_available' de la cuenta padre.
     *
     * @param array &$records Arreglo de registros a actualizar
     * @return array El arreglo de registros actualizado
     */
    public function getLastAccountsOpen(&$records)
    {
        if ($records) {
            $total = 0;
            $total_parent = 0;
            $array_main_accounts_keys = array();
            $array_accounts_keys = array();
            $is_not_parent = false;

            foreach ($records as $key => $budgetAccounts) {
                foreach ($budgetAccounts[0] as $key2 => $budgetAccount) {
                    $specific = $budgetAccount->specific ?? $budgetAccount->budgetAccount->specific;
                    $item = $budgetAccount->item ?? $budgetAccount->budgetAccount->item;
                    $is_main_parent = $item === '00';
                    $is_parent = $specific === '00' && !$is_main_parent;

                    if ($is_main_parent) {
                        if (!empty($array_accounts_keys) && $is_not_parent) {
                            foreach ($array_accounts_keys as $keys) {
                                $records[$keys[0]][0][$keys[1]]['self_available'] = $total_parent;
                            }
                            $array_accounts_keys = array();
                            $total_parent = 0;
                        }
                        if (!empty($array_main_accounts_keys) && $is_not_parent) {
                            foreach ($array_main_accounts_keys as $keys) {
                                $records[$keys[0]][0][$keys[1]]['self_available'] = $total;
                            }
                            $array_main_accounts_keys = array();
                            $total = 0;
                        }
                        $is_not_parent = false;
                        $array_main_accounts_keys[] = [$key, $key2];
                    } elseif ($is_parent) {
                        if (!empty($array_accounts_keys) && $is_not_parent) {
                            foreach ($array_accounts_keys as $keys) {
                                $records[$keys[0]][0][$keys[1]]['self_available'] = $total_parent;
                            }
                            $array_accounts_keys = array();
                            $total_parent = 0;
                            $is_not_parent = false;
                        }
                        $array_accounts_keys[] = [$key, $key2];
                    } else {
                        if (isset($budgetAccount['modifications']) && !empty($budgetAccount['modifications'])) {
                            $last_modification = $budgetAccount['modifications'];
                            $last_modification = end($last_modification);
                            $account_self_available = $last_modification['self_available'] ?? 0;
                            $total_parent += $account_self_available;
                            $total += $account_self_available;
                        } elseif (empty($budgetAccount['modifications'])) {
                            $total_parent += $budgetAccount['self_available'] ?? 0;
                            $total += $budgetAccount['self_available'] ?? 0;
                        }
                        $is_not_parent = true;
                    }
                }
            }
            if (!empty($array_accounts_keys) && $is_not_parent) {
                foreach ($array_accounts_keys as $keys) {
                    $records[$keys[0]][0][$keys[1]]['self_available'] = $total_parent;
                }
            }
            if (!empty($array_main_accounts_keys) && $is_not_parent) {
                foreach ($array_main_accounts_keys as $keys) {
                    $records[$keys[0]][0][$keys[1]]['self_available'] = $total;
                }
            }
        }
        return $records;
    }
    /**
     * Metodo que retorna el monto comprometido para la cuenta $account_id
     *
     * @author José Briceño <josejorgebriceno9@gmail.com>
     *
     * @param Request $request Datos de la petición
     *
     * @return void
     */

    public function consolidatedReportPdf(Request $request)
    {
        $request->validate([
            'initialDate' => ['required', 'before_or_equal:finalDate'],
            'finalDate' => ['required', 'after_or_equal:initialDate'],
            'initialCode' => 'required',
            'finalCode' => 'required',
            'accountsWithMovements' => 'required',
        ]);

        $data = $request->toArray();
        $data["projects_ids"] = json_decode('[' . $data["projects_ids"] . ']', true);
        $data["centralized_actions_ids"] = json_decode('[' . $data["centralized_actions_ids"] . ']', true);

        $projects = BudgetProject::with(['specificActions' => function ($query) {
            $query->with(['subSpecificFormulations' => function ($query) {
                $query->with(['accountOpens' => function ($query) {
                    $query->with('budgetAccount');
                    $query->orderBy('id', 'desc');
                }])->whereHas('accountOpens');
            }])->whereHas('subSpecificFormulations');
        }])->whereIn('id', $data["projects_ids"])->get();

        $centrilized_actions = BudgetCentralizedAction::with(['specificActions' => function ($query) {
            $query->with(['subSpecificFormulations' => function ($query) {
                $query->with(['accountOpens' => function ($query) {
                    $query->with('budgetAccount');
                    $query->orderBy('id', 'desc');
                }])->whereHas('accountOpens');
            }])->whereHas('subSpecificFormulations');
        }])->whereIn('id', $data["centralized_actions_ids"])->get();

        $projects_accounts = array();
        foreach ($projects as $project) {
            array_push($projects_accounts, $this->getBudgetAccountsOpen($data['accountsWithMovements'], $project, $data['initialDate'], $data['finalDate']));
        }

        $centrilized_actions_accounts = array();
        foreach ($centrilized_actions as $centrilized_action) {
            array_push($centrilized_actions_accounts, $this->getBudgetAccountsOpen($data['accountsWithMovements'], $centrilized_action, $data['initialDate'], $data['finalDate']));
        }

        $records = array();

        foreach ($projects_accounts as $projects_account) {
            $projects_account[0][0] = $this->filterBudgetAccounts($projects_account[0][0], $data['initialCode'], $data['finalCode'], $data['initialDate'], $data['finalDate']);
            array_push($records, ...$projects_account);
        }

        foreach ($centrilized_actions_accounts as $centrilized_actions_account) {
            $centrilized_actions_account[0][0] = $this->filterBudgetAccounts($centrilized_actions_account[0][0], $data['initialCode'], $data['finalCode'], $data['initialDate'], $data['finalDate']);
            array_push($records, ...$centrilized_actions_account);
        }

        $institution = Institution::find(1);
        $fiscal_year = FiscalYear::where('active', true)->first();
        $currency = Currency::where('default', true)->first();
        $profile = Profile::where('user_id', auth()->user()->id)->first();
        $date = 'Proyectos y Acciones Centralizadas desde ' . \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $data['initialDate'])->format('d-m-Y') . ' hasta ' . \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $data['finalDate'])->format('d-m-Y');

        if ($request->exportReport === 'true') {
            return Excel::download(new RecordsExport([
                'records' => $records, 'institution' => $institution,
                'currencySymbol' => $currency['symbol'],
                'fiscal_year' => $fiscal_year['year'],
                'report_date' => \Carbon\Carbon::today()->format('d-m-Y'),
                'initialDate' => '',
                'finalDate' => '',
                'report_type_id' => '3',
                'profile' => $profile,
                'date' => $date,
                'report_view' => 'budget::pdf.budgetAvailability',
            ]), now()->format('d-m-Y') . '_reporte_disponibilidad_presupuestaria.csv');
        } else {
            $pdf = new ReportRepository();
            $pdf->setConfig(['institution' => $institution, 'orientation' => 'P', 'reportDate' => '', 'urlVerify'   => url('')]);
            $pdf->setHeader('Reporte de Presupuesto', $date);
            $pdf->setFooter();
            $pdf->setFooter();
            $pdf->setBody('budget::pdf.budgetAvailability', true, [
                'pdf' => $pdf,
                'records' => $records,
                'institution' => $institution,
                'currencySymbol' => $currency['symbol'],
                'fiscal_year' => $fiscal_year['year'],
                "report_date" => \Carbon\Carbon::today()->format('d-m-Y'),
                'profile' => $profile,
            ]);
        }
    }

    /**
     * Muestra el formulario para generar el reporte de proyectos
     *
     * @return \Illuminate\View\View
     */
    public function getProjectsView()
    {
        return view('budget::reports.projects');
    }

    /**
     * Obtiene los datos para generar el reporte de proyectos
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getProjectsReportData(Request $request)
    {
        try {
            $project_code = $request->input('project_code');
            $search = $request->input('search');

            $query = BudgetProject::query();

            if ($project_code) {
                $query->where("code", "LIKE", "%" . $project_code . "%");
            }

            if ($search) {
                $query->where("name", "LIKE", "%" . $search . "%");
            }

            $query = $query->get();

            $response = [
                'data' => $query,
                "message" => "Data para reporte de proyectos",
            ];
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $code = $e->getCode() ? (is_numeric($e->getCode()) ? $e->getCode() : 500) : 500;
            $msg = $e->getMessage() ?? "Error al obtener la data para el reporte de proyectos";
            $response = [
                "message" => $msg,
                "errors" => [],
            ];
        }

        return response()->json($response, $code ?? 200);
    }

    /**
     * Genera el reporte de proyectos
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function getProjectsReportPdf(Request $request)
    {
        try {
            $project_code = $request->input('project_code');
            $search = $request->input('search');

            $query = BudgetProject::query();

            if ($project_code) {
                $query->where("code", "LIKE", "%" . $project_code . "%");
            }

            if ($search) {
                $query->where("name", "LIKE", "%" . $search . "%");
            }

            $query = $query->get();

            $pdf = new ReportRepository();
            $institution = Institution::find(1);

            $pdf->setConfig(['institution' => $institution]);
            $pdf->setHeader('Reporte de proyectos', 'Reporte de proyectos de la institucion');
            $pdf->setFooter();
            $pdf->setBody('budget::pdf.projects', true, [
                'pdf' => $pdf,
                'records' => $query,
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $code = $e->getCode() ? (is_numeric($e->getCode()) ? $e->getCode() : 500) : 500;
            $msg = $e->getMessage() ?? "Error al obtener la data para el reporte de proyectos";
            $response = [
                "message" => $msg,
                "errors" => [],
            ];

            return response()->json($response, $code ?? 200);
        }
    }

    /**
     * Método que recopila todos los años que poseen formulaciones
     *
     * @return \Illuminate\View\View
     */
    public function getFormulatedView()
    {
        $formulations_years = BudgetSubSpecificFormulation::select(['year'])->distinct()->get()->all();
        $years = json_encode(array_column($formulations_years, 'year'));
        $budgetProjects = $this->getBudgetProjects(true);
        $budgetCentralizedActions = $this->getBudgetCentralizedActions(true);

        return view('budget::reports.budgetFormulated', [
            'years' => $years,
            'budgetProjects' => json_encode($budgetProjects),
            'budgetCentralizedActions' => json_encode($budgetCentralizedActions)
        ]);
    }

    /**
     * Genera los datos necesarios para el formulario de generacion de reporte de disponibilidad presupuestaria
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return    Renderable|array
     */
    public function getCompromiseView(): View
    {
        $formulations_years = BudgetSubSpecificFormulation::select(['year'])->distinct()->get()->all();
        $years = json_encode(array_column($formulations_years, 'year'));
        $budgetProjects = $this->getBudgetProjects(true);
        $budgetCentralizedActions = $this->getBudgetCentralizedActions(true);

        return view('budget::reports.budgetCompromise', [
            'years' => $years,
            'budgetProjects' => json_encode($budgetProjects),
            'budgetCentralizedActions' => json_encode($budgetCentralizedActions),
        ]);
    }


    /**
     * Obtiene los datos de las formulaciones
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getFormulations(Request $request)
    {
        $entity = $request->input('is_project')
        ? BudgetProject::class
        : BudgetCentralizedAction::class;

        $id = $request->input('id');

        $query = BudgetSubSpecificFormulation::query();

        $query = $query->whereHas('specificAction', function ($query) use ($entity, $id) {
            $query->whereHasMorph('specificable', [BudgetProject::class, BudgetCentralizedAction::class], function ($query) use ($entity, $id) {
                return $query->where('specificable_id', $id)
                    ->where('specificable_type', $entity);
            });
        });

        $query = $query->get();

        $formulations = $query->count() ? [['id' => '', 'text' => 'Seleccione']] : [];

        foreach ($query as $formulation) {
            $formulations[] = [
                'id' => $formulation->id,
                'text' => $formulation->specificAction->code . ' - ' . $formulation->specificAction->name,
            ];
        }
        return response()->json($formulations);
    }

    /**
     * Obtiene los datos de las acciones especificas
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getSpecificActionsList(Request $request)
    {
        $entity = $request->input('is_project')
        ? BudgetProject::class
        : BudgetCentralizedAction::class;

        $id = $request->input('id');

        $query = BudgetSubSpecificFormulation::query();

        $query = $query->whereHas('specificAction', function ($query) use ($entity, $id) {
            $query->whereHasMorph('specificable', [BudgetProject::class, BudgetCentralizedAction::class], function ($query) use ($entity, $id) {
                return $query->where('specificable_id', $id)
                    ->where('specificable_type', $entity);
            });
        });

        $query = $query->get();

        $formulations = $query->count() ? [['id' => '', 'text' => 'Seleccione']] : [];

        foreach ($query as $formulation) {
            $formulations[] = [
                'id' => $formulation->specificAction->id,
                'text' => $formulation->specificAction->code . ' - ' . $formulation->specificAction->name,
            ];
        }
        return response()->json($formulations);
    }

    /**
     * Obtiene los datos para el reporte de presupuesto formulado
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getFormulatedReportData(Request $request)
    {
        $formulation_id = $request->input('formulation_id');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        $query = BudgetAccountOpen::where('budget_sub_specific_formulation_id', $formulation_id);

        if ($start_date) {
            $query->where('created_at', '>=', $start_date);
        }

        if ($end_date) {
            $query->where('created_at', '<=', $end_date);
        }

        $query = $query->get();

        $total = $query->sum('total_real_amount');

        foreach ($query as $account) {
            $account->code = $account->budgetAccount->getCodeAttribute();
            $account->percentage = round(($account->total_real_amount * 100) / $total);
            $account->total = $total;
        }

        return response()->json(['data' => $query]);
    }

    /**
     * Obtiene los datos para el reporte de compromisos en formato xlsx
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return BinaryFileResponse    Objeto que permite descargar el archivo con la información a ser exportada
     */
    public function exportCompromisesReport(Request $request)
    {
        $request->validate(
            [
            'formulation_id' => $request->input('all_specific_actions') === 'true' || $request->query('all')  === 'true' ?
                'nullable' :
                ['required', 'array', 'not_in:0|null, ""'],
            'start_date' => ['required', 'before_or_equal:end_date'],
            'end_date' => ['required', 'after_or_equal:start_date'],
            'status_id' => $request->input('all_statuses') === 'true'
            || $request->query('all') === 'true' ?
                'nullable' :
                ['required', 'array', 'not_in:0|null, ""'],
            ],
            [],
            [
            'formulation_id' => 'El campo Acción Especifica',
            'start_date' => 'Desde',
            'end_date' => 'Hasta',
            'status_id' => 'El campo Estatus',
            ]
        );

        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');
        $currency = $request->query('currency');
        $statusIds = count($request->query('status_id', []) ?? []) < 1 ?
        [] : explode(',', $request->query('status_id')[0]);

        $records = BudgetCompromise::query()
        ->filterCompromises($request->query())
        ->get();

        if (
            $request->query('is_status') == 'true'
            && $request->input('all_statuses') === 'false'
        ) {
            $records = $records->reject(function ($record) use ($request, $statusIds) {
                return !in_array($record->status, $statusIds);
            });
        }
        $currency = Currency::where('id', $currency)->first();
        return Excel::download(
            new BudgetCompromiseExport($records, $currency),
            'reporte_compromisos.xlsx'
        );
    }

    /**
     * Obtiene los datos para el reporte de compromisos
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getCompromiseReportPdf(Request $request)
    {
        $request->validate(
            [
            'formulation_id' => $request->input('all_specific_actions') === 'true'
            || $request->query('all')  === 'true' ?
                'nullable' :
                ['required', 'array', 'not_in:0|null'],
            'start_date' => ['required', 'before_or_equal:end_date'],
            'end_date' => ['required', 'after_or_equal:start_date'],
            'status_id' => $request->input('all_statuses') === 'true'
            || $request->query('all') === 'true' ?
                'nullable' :
                ['required', 'array', 'not_in:0|null, ""'],
            ],
            [],
            [
            'formulation_id' => 'El campo Acción Especifica',
            'start_date' => 'Desde',
            'end_date' => 'Hasta',
            'status_id' => 'El campo Estatus',
            ]
        );

        $start_date = $request->query('start_date');
        $currency = $request->query('currency');
        $end_date = $request->query('end_date');
        $statusIds = count($request->query('status_id', []) ?? []) < 1 ?
        [] : explode(',', $request->query('status_id')[0]);

        $records = BudgetCompromise::query()
        ->filterCompromises($request->query())
        ->get();

        if (
            $request->query('is_status') === 'true'
            && $request->input('all_statuses') === 'false'
        ) {
            $records = $records->reject(function ($record) use ($request, $statusIds) {
                return !in_array($record->status, $statusIds);
            });
        }
        try {
            $pdf = new ReportRepository();

            $institution = Institution::query()
            ->where('default', true)
            ->first();
            $fiscal_year = FiscalYear::where('active', true)->first();
            $currency = Currency::where('id', $currency)->first();
            $profile = auth()->user();
            $date = 'Informacion de compromisos desde ' . \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $start_date)->format('d-m-Y') . ' hasta ' . \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $end_date)->format('d-m-Y');

            $pdf->setConfig([
                'institution' => $institution,
                'orientation' => 'L',
                'format' => 'A2 LANDSCAPE',
                'urlVerify'   => url(''),
            ]);
            $pdf->setHeader(
                "Reporte de Compromisos",
                $date,
            );
            $pdf->setFooter();
            $pdf->setBody(
                'budget::pdf.compromises',
                true,
                [
                'pdf' => $pdf,
                'records' => $records,
                'institution' => $institution,
                'currencySymbol' => $currency['symbol'],
                'fiscal_year' => $fiscal_year['year'],
                'profile' => $profile,
                'currency' => $currency ,
                ]
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $code = $e->getCode() ? (is_numeric($e->getCode()) ? $e->getCode() : 500) : 500;
            $msg = $e->getMessage() ?? "Error al obtener los datos para el reporte de presupuestos formulados";
            $response = [
                "message" => $msg,
                "errors" => [],
            ];

            return response()->json($response, $code ?? 200);
        }
    }

    /**
     * Genera el reporte de presupuesto formulado
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function getFormulatedReportPdf(Request $request)
    {
        $request->validate([
            'formulation_id' => $request->input('all_specific_actions') === 'true' ?
                'nullable' :
                ['required', 'array', 'not_in:0|null, ""'],
            'start_date' => ['required', 'before_or_equal:end_date'],
            'end_date' => ['required', 'after_or_equal:start_date'],
            'currency_id' => ['required', 'exists:currencies,id'],
        ], [], [
            'formulation_id' => 'El campo Acción Especifica',
            'start_date' => 'El campo Desde',
            'end_date' => 'El campo Hasta',
            'currency_id' => 'Moneda',
        ]);

        try {
            $formulation_id = explode(',', $request->input('formulation_id')[0]);
            $start_date = $request->input('start_date');
            $end_date = $request->input('end_date');
            $isProject = BudgetProject::class;
            $isCentralizedAction = BudgetCentralizedAction::class;

            if ($request->all_specific_actions == 'true') {
                if ($request->is_project) {
                    $formulation = BudgetSubSpecificFormulation::query()->whereHas('specificAction', function ($query) use ($isProject, $request) {
                        $query->whereHasMorph('specificable', [BudgetProject::class], function ($query) use ($isProject, $request) {
                            return $query
                                ->where('specificable_type', $isProject)
                                ->where('specificable_id', $request->project_id);
                        });
                    })->get();
                } else {
                    $formulation = BudgetSubSpecificFormulation::query()->whereHas('specificAction', function ($query) use ($isCentralizedAction, $request) {
                        $query->whereHasMorph('specificable', [BudgetCentralizedAction::class], function ($query) use ($isCentralizedAction, $request) {
                            return $query
                                ->where('specificable_type', $isCentralizedAction)
                                ->where('specificable_id', $request->centralized_action_id);
                        });
                    })->get();
                }
            } else {
                $formulation = BudgetSubSpecificFormulation::query()->whereIn('id', $formulation_id)->get();
            }

            $pdf = new ReportRepository();

            $institution = Institution::find(1);

            $fiscal_year = FiscalYear::where('active', true)->first();

            // Establece la moneda en la que se va a mostrar el reporte
            $currency = Currency::find($request->input('currency_id'));

            $profile = Profile::where('user_id', auth()->user()->id)->first();

            $date = 'Presupuesto Formulado desde ' . \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $start_date)->format('d-m-Y') . ' hasta ' . \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $end_date)->format('d-m-Y');

            $totalFormulations = 0;

            foreach ($formulation as $form) {
                $totalFormulations += $form->total_formulated;
            }

            if ($request->xml == 'true') {
                return Excel::download(new BudgetFormulatedSheetExport([
                    'pdf' => $pdf,
                    'formulations' => $formulation,
                    'totalFormulations' => $totalFormulations,
                    'institution' => $institution,
                    'currencySymbol' => $currency['symbol'],
                    'fiscal_year' => $fiscal_year['year'],
                    'profile' => $profile,
                    'currency' => $currency,
                ]), now()->format('d-m-Y') . '_Reporte_de_Presupuesto_formulado.xlsx');
            } else {
                $pdf->setConfig([
                    'institution' => $institution,
                    'orientation' => 'L',
                    'format' => 'A2 LANDSCAPE',
                    'urlVerify'   => url(''),
                ]);

                $pdf->setHeader(
                    "Reporte de Presupuesto Formulado",
                    $date,
                );

                $pdf->setFooter();

                $pdf->setBody('budget::pdf.formulations', true, [
                    'pdf' => $pdf,
                    'formulations' => $formulation,
                    'totalFormulations' => $totalFormulations,
                    'institution' => $institution,
                    'currencySymbol' => $currency['symbol'],
                    'fiscal_year' => $fiscal_year['year'],
                    'profile' => $profile,
                    'currency' => $currency,
                ]);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $code = $e->getCode() ? (is_numeric($e->getCode()) ? $e->getCode() : 500) : 500;
            $msg = $e->getMessage() ?? "Error al obtener los datos para el reporte de presupuestos formulados";
            $response = [
                "message" => $msg,
                "errors" => [],
            ];

            return response()->json($response, $code ?? 200);
        }
    }

    /**
     * Obtiene información delos proyectos
     *
     * @param bool $list Indica si debe retornar una lista
     *
     * @return array
     */
    public function getBudgetProjects(bool $list = null)
    {
        $budgetProjects = BudgetProject::with(['specificActions'])->whereHas('specificActions', function ($query) {
            $query->whereHas('subSpecificFormulations', function ($query) {
                $query->where('confirmed', true);
            });
        })->get()->all();

        if ($list) {
            $budgetProjects = array_map(function ($budgeProject) {
                return array(
                    'id' => $budgeProject->id,
                    'text' => $budgeProject->code . ' - ' . $budgeProject->name,
                );
            }, $budgetProjects);

            array_unshift($budgetProjects, ['id' => "", 'text' => "Seleccione..."]);
            return $budgetProjects;
        }

        return $budgetProjects;
    }

    /**
     * Obtiene información de las acciones centralizadas
     *
     * @param bool $list Indica si debe retornar una lista
     *
     * @return array
     */
    public function getBudgetCentralizedActions(bool $list = null)
    {
        $budgetCentralizedActions = BudgetCentralizedAction::with(['specificActions'])->whereHas('specificActions', function ($query) {
            $query->whereHas('subSpecificFormulations', function ($query) {
                $query->where('confirmed', true);
            });
        })->get()->all();

        if ($list) {
            $budgetCentralizedActions = array_map(function ($budgetCentralizedAction) {
                return array(
                    'id' => $budgetCentralizedAction->id,
                    'text' => $budgetCentralizedAction->code . ' - ' . $budgetCentralizedAction->name,
                );
            }, $budgetCentralizedActions);

            array_unshift($budgetCentralizedActions, ['id' => "", 'text' => "Seleccione..."]);

            return $budgetCentralizedActions;
        }

        return $budgetCentralizedActions;
    }

    /**
     * Muestra el formulario para la generación del reporte del mayor analítico
     *
     * @return \Illuminate\View\View
     */
    public function budgetAnalyticalMajor()
    {
        $budgetAvailability = $this->budgetAvailability(true);
        return view('budget::reports.budgetAnalyticalMajor', $budgetAvailability);
    }

    /**
     * Genera el reporte de mayor analítico
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|void
     */
    public function getbudgetAnalyticalMajorPdf(Request $request)
    {
        $data = $request->validate([
            'initialDate' => ['required', 'before:finalDate'],
            'finalDate' => ['required', 'after:initialDate'],
            'initialCode' => 'required',
            'finalCode' => 'required',
            'accountsWithMovements' => 'required',
        ]);

        $data = $request->toArray();
        $data["specific_actions_ids"] = json_decode('[' . $data["specific_actions_ids"] . ']', true);
        $ids = $data["specific_actions_ids"];

        if ($request->project_type === 'project') {
            $project = BudgetProject::with(['specificActions' => function ($query) use ($ids) {
                $query->with(['subSpecificFormulations' => function ($query) {
                    $query->with(['accountOpens' => function ($query) {
                        $query->with('budgetAccount');
                        $query->orderBy('id', 'desc');
                    }])->whereHas('accountOpens');
                }])->whereIn('id', $ids)->get();
            }])->find($data["project_id"]);
        } else {
            $project = BudgetCentralizedAction::with(['specificActions' => function ($query) use ($ids) {
                $query->with(['subSpecificFormulations' => function ($query) {
                    $query->with(['accountOpens' => function ($query) {
                        $query->with('budgetAccount');
                        $query->orderBy('id', 'desc');
                    }])->whereHas('accountOpens');
                }])->whereIn('id', $ids)->get();
            }])->find($data["project_id"]);
        }

        $records = $this->getBudgetAccountsOpen($data['accountsWithMovements'], $project, $data['initialDate'], $data['finalDate']);

        for ($i = 0; $i < count($records); $i++) {
            $records[$i][0] = $this->filterBudgetAccounts($records[$i][0], $data['initialCode'], $data['finalCode'], $data['initialDate'], $data['finalDate']);
        }

        $institution = Institution::find(1);
        $fiscal_year = FiscalYear::where('active', true)->first();
        $currency = Currency::where('default', true)->first();
        $profile = Profile::where('user_id', auth()->user()->id)->first();
        $date = 'Mayor Analítico desde ' . \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $data['initialDate'])->format('d-m-Y') . ' hasta ' . \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $data['finalDate'])->format('d-m-Y');

        if ($request->exportReport === 'true') {
            return Excel::download(new RecordsExport([
                'records' => $records, 'institution' => $institution,
                'currencySymbol' => $currency['symbol'],
                'fiscal_year' => $fiscal_year['year'],
                'report_date' => \Carbon\Carbon::today()->format('d-m-Y'),
                'initialDate' => array_key_exists('initialDate', $data) ? Carbon::parse($data['initialDate'])->format('d-m-Y') : '',
                'finalDate' => array_key_exists('finalDate', $data) ? Carbon::parse($data['finalDate'])->format('d-m-Y') : '',
                'report_type_id' => $data['report_type_id'],
                'profile' => $profile,
            ]), now()->format('d-m-Y') . '_Reporte_Mayor_Analitico.csv');
        } else {
            $pdf = new ReportRepository();
            $pdf->setConfig(['institution' => $institution, 'orientation' => 'L', 'format' => 'A2 LANDSCAPE', 'urlVerify' => url('')]);
            $pdf->setHeader('Reporte Mayor Analítico por Proyecto o Acción Centralizada', $date);
            $pdf->setFooter();
            $pdf->setBody('budget::pdf.budgetAnalyticMajor', true, [
                'pdf' => $pdf,
                'records' => $records,
                'institution' => $institution,
                'currencySymbol' => $currency['symbol'],
                'fiscal_year' => $fiscal_year['year'],
                'report_date' => \Carbon\Carbon::today()->format('d-m-Y'),
                'initialDate' => \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $request['initialDate'])->format('d-m-Y'),
                'finalDate' => \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $request['finalDate'])->format('d-m-Y'),
                'profile' => $profile,
            ]);
        }
    }

    /**
     * Crea el reporte de mayor analítico
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createBudgetAnalyticalMajorPdf(Request $request)
    {
        $userId = auth()->user()->id;
        $data = $request->validate([
            'initialDate' => ['required', 'before:finalDate'],
            'finalDate' => ['required', 'after:initialDate'],
            'initialCode' => 'required',
            'finalCode' => 'required',
            'accountsWithMovements' => 'required',
        ]);

        $data = $request->toArray();
        $created_at = \Carbon\Carbon::now();
        CreateBudgetAnalyticalMajorJob::dispatch(
            $data,
            'budget::pdf.budgetAnalyticMajor',
            'report-budget-analytic-major',
            $userId,
            $created_at
        );
        return response()->json(['result' => true], 200);
    }

    /**
     * Método para buscar las cuentas padre de una formulación
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    array con las cuentas padre de la formulación
     */
    public function getAccountParents($child, $parents = [])
    {
        if (!isset($child)) {
            return $parents;
        }

        if (!array_key_exists($child->id, $parents)) {
            $parents[$child->id] = $child;
        }

        if ($child->parent_id == null) {
            return $parents;
        } else {
            $child->load('accountParent');
            $parent = $child->accountParent;
            $parent['increment'] += $child['increment'];
            $parent['self_amount'] += $child['self_amount'];

            return $this->getAccountParents($parent, $parents);
        }
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
    public function getNewAccountParents($child, $parents = [])
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

    /**
     * Metodo que retorna el monto causado para la cuenta $account
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param BudgetAccount $account Cuenta presupuestaria
     * @param string $initialDate Fecha inicial
     * @param string $finalDate   Fecha final
     *
     * @return float|array Monto del causado para la cuenta presupuestaria $account     *
     */

    public function getAccountCompromisedCausedAmount($account, $initialDate, $finalDate)
    {
        $compromised = BudgetCompromiseDetail::query()
            ->with(['budgetCompromise' => function ($query) use ($initialDate, $finalDate) {
                $query
                    ->whereBetween('compromised_at', [$initialDate, $finalDate])
                    ->with(['budgetStages' => function ($q) {
                        $q->withTrashed()->with('stageable')->where('type', 'CAU');
                    }]);
            }])
            ->where('budget_sub_specific_formulation_id', $account->budget_sub_specific_formulation_id)
            ->where('budget_account_id', $account->budget_account_id)
            ->get();

        $compromises = [];
        $amount = [
            'amount' => 0,
            'date' => '',
        ];

        $anulatedStatus = DocumentStatus::where('action', 'AN')->first();

        if (count($compromised) > 0) {
            if (isset($compromised[0]) && isset($compromised[0]['budgetCompromise'])) {
                foreach ($compromised as $com) {
                    if ($com->budgetCompromise) {
                        $budgetCompromiseId = $com->budgetCompromise->id;

                        // Iteración con monto en positivo
                        $positiveKey = $budgetCompromiseId;

                        // Verificamos si el identificador existe en $compromises y lo inicializamos si no
                        if (!array_key_exists($positiveKey, $compromises)) {
                            $amount = [
                                'amount' => 0,
                                'date' => ''
                            ];
                        } else {
                            // Si ya existe, obtenemos su valor actual
                            $amount = $compromises[$positiveKey];
                        }

                        if ($com->budgetCompromise->budgetStages) {
                            $stageCau = false;
                            foreach ($com->budgetCompromise->budgetStages as $stage) {
                                if ($stage->type == 'CAU') {
                                    $stageCau = true;
                                }
                                $date = $stage->stageable->ordered_at ?? $stage->stageable->payment_date;
                                // Seteamos la fecha desde el último elemento de budgetStages
                                if (gettype($date) === 'string') {
                                    $amount['date'] = \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $date);
                                } else {
                                    $amount['date'] = $date;
                                }
                            }

                            $newAmount = $com->amount;

                            if ($stageCau) {
                                $amount['amount'] += $newAmount;
                                $compromises[$positiveKey] = $amount; // Actualizamos el valor en el arreglo $compromises
                            }
                        }

                        if ($anulatedStatus->id == $com->document_status_id) {
                            // Iteración con monto en negativo
                            $negativeKey = $budgetCompromiseId . '_negative';

                            // Verificamos si el identificador existe en $compromises y lo inicializamos si no
                            if (!array_key_exists($negativeKey, $compromises)) {
                                $amount = [
                                    'amount' => 0,
                                    'date' => ''
                                ];
                            } else {
                                // Si ya existe, obtenemos su valor actual
                                $amount = $compromises[$negativeKey];
                            }

                            if ($com->budgetCompromise->budgetStages) {
                                $stageCau = false;
                                foreach ($com->budgetCompromise->budgetStages as $stage) {
                                    if ($stage->type == 'CAU') {
                                        $stageCau = true;
                                    }
                                    $date = $stage->stageable->ordered_at ?? $stage->stageable->payment_date;
                                    // Seteamos la fecha desde el último elemento de budgetStages
                                    if (gettype($date) === 'string') {
                                        $amount['date'] = \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $date);
                                    } else {
                                        $amount['date'] = $date;
                                    }
                                }

                                $newAmount = $com->amount * -1;

                                if ($stageCau) {
                                    $amount['amount'] += $newAmount;
                                    $compromises[$negativeKey] = $amount; // Actualizamos el valor en el arreglo $compromises
                                }
                            }
                        }
                    }
                }

                return $compromises;
            }
        }

        return $compromises;
    }

    /**
     * Metodo que retorna el monto pagado para la cuenta $account
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param BudgetAccount $account Cuenta presupuestaria
     * @param string $initialDate Fecha inicial
     * @param string $finalDate   Fecha final
     *
     * @return array Monto del pagado para la cuenta presupuestaria $account     *
     */

    public function getAccountCompromisedPaidAmount($account, $initialDate, $finalDate)
    {
        $compromised = BudgetCompromiseDetail::query()
            ->with(['budgetCompromise' => function ($query) use ($initialDate, $finalDate) {
                $query
                    ->whereBetween('compromised_at', [$initialDate, $finalDate])
                    ->with(['budgetStages' => function ($q) {
                        $q->withTrashed()->with('stageable')->where('type', 'PAG');
                    }]);
            }])
            ->where('budget_sub_specific_formulation_id', $account->budget_sub_specific_formulation_id)
            ->where('budget_account_id', $account->budget_account_id)
            ->get();

        $anulatedStatus = DocumentStatus::where('action', 'AN')->first();

        $compromises = [];
        $amount = [
            'amount' => 0,
            'date' => '',
        ];

        if (count($compromised) > 0) {
            if (isset($compromised[0]) && isset($compromised[0]['budgetCompromise'])) {
                foreach ($compromised as $com) {
                    if ($com->budgetCompromise) {
                        $budgetCompromiseId = $com->budgetCompromise->id;

                        // Iteración con monto en positivo
                        $positiveKey = $budgetCompromiseId;

                        // Verificamos si el identificador existe en $compromises y lo inicializamos si no
                        if (!array_key_exists($positiveKey, $compromises)) {
                            $amount = [
                                'amount' => 0,
                                'date' => ''
                            ];
                        } else {
                            // Si ya existe, obtenemos su valor actual
                            $amount = $compromises[$positiveKey];
                        }

                        if ($com->budgetCompromise->budgetStages) {
                            $stagePag = false;
                            foreach ($com->budgetCompromise->budgetStages as $stage) {
                                if ($stage->type == 'PAG') {
                                    $stagePag = true;
                                }
                                $date = $stage->stageable->paid_at ?? $stage->stageable->payment_date;
                                // Seteamos la fecha desde el último elemento de budgetStages
                                if (gettype($date) === 'string') {
                                    $amount['date'] = \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $date);
                                } else {
                                    $amount['date'] = $date;
                                }
                            }

                            $newAmount = $com->amount;

                            if ($stagePag) {
                                $amount['amount'] += $newAmount;
                                $compromises[$positiveKey] = $amount; // Actualizamos el valor en el arreglo $compromises
                            }
                        }

                        if ($anulatedStatus->id == $com->document_status_id) {
                            // Iteración con monto en negativo
                            $negativeKey = $budgetCompromiseId . '_negative';

                            // Verificamos si el identificador existe en $compromises y lo inicializamos si no
                            if (!array_key_exists($negativeKey, $compromises)) {
                                $amount = [
                                    'amount' => 0,
                                    'date' => ''
                                ];
                            } else {
                                // Si ya existe, obtenemos su valor actual
                                $amount = $compromises[$negativeKey];
                            }

                            if ($com->budgetCompromise->budgetStages) {
                                $stagePag = false;
                                foreach ($com->budgetCompromise->budgetStages as $stage) {
                                    if ($stage->type == 'PAG') {
                                        $stagePag = true;
                                    }
                                    $date = $stage->stageable->paid_at ?? $stage->stageable->payment_date;
                                    // Seteamos la fecha desde el último elemento de budgetStages
                                    if (gettype($date) === 'string') {
                                        $amount['date'] = \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $date);
                                    } else {
                                        $amount['date'] = $date;
                                    }
                                }

                                $newAmount = $com->amount * -1;

                                if ($stagePag) {
                                    $amount['amount'] += $newAmount;
                                    $compromises[$negativeKey] = $amount; // Actualizamos el valor en el arreglo $compromises
                                }
                            }
                        }
                    }
                }

                return $compromises;
            }
        }

        return $compromises;
    }

    /**
     * Metodo que retorna la vista para crear el reporte consolidado
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @method createBudgetConsolidated
     *
     */
    public function createBudgetConsolidated(Request $request)
    {
        $errorMessage = $request->message;
        return view('budget::reports.consolidated', compact('errorMessage'));
    }

    /**
     * Metodo que obtiene una listado de los proyectos para usar en el reporte consolidado
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @method getProyects
     *
     */
    public function getProyects()
    {
        $data = BudgetProject::query()
            ->toBase()
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'text' => $project->name,
                ];
            })
            ->toArray();

        array_unshift($data, ['id' => 'Todos', 'text' => 'Todos', 'denomination' => 'Todos']);

        return response()->json($data);
    }

    /**
     * Metodo que obtiene una listado de las acciones centralizdas para usar en el reporte consolidado
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @method getCentralizedActions
     *
     */
    public function getCentralizedActions()
    {
        $data = BudgetCentralizedAction::query()
            ->toBase()
            ->get()
            ->map(function ($action) {
                return [
                    'id' => $action->id,
                    'name' => $action->name,
                    'text' => $action->name,
                ];
            })
            ->toArray();

        array_unshift($data, ['id' => 'Todas', 'text' => 'Todas', 'denomination' => 'Todas']);

        return response()->json($data);
    }

    /**
     * Metodo que obtiene una listado de las acciones especificas para usar en el reporte consolidado
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @method getSpecificActions
     *
     */
    public function getSpecificActions()
    {
        $data = BudgetSpecificAction::query()
            ->toBase()
            ->get()
            ->map(function ($action) {
                return [
                    'id' => $action->id,
                    'name' => $action->name,
                    'text' => $action->name,
                ];
            })
            ->toArray();

        array_unshift($data, ['id' => 'Todas', 'text' => 'Todas', 'denomination' => 'Todas']);

        return response()->json($data);
    }

    /**
     * Metodo que obtiene una listado de las cuentas presupuestarias para usar en el reporte consolidado
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @method getSpecificActions
     *
     */
    public function getAccounts()
    {
        $data = [['id' => 'Todas', 'text' => 'Todas', 'denomination' => 'Todas']];
        $accounts = BudgetAccount::query()
            ->where('specific', '!=', '00')
            ->toBase()
            ->get();

        foreach ($accounts as $account) {
            if ($account->group > 3) {
                $data[] = [
                    'id' => $account->id,
                    'code' => "{$account->group}.{$account->item}.{$account->generic}.{$account->specific}.{$account->subspecific}",
                    'text' => "{$account->group}.{$account->item}.{$account->generic}.{$account->specific}.{$account->subspecific}",
                    'denomination' => $account->denomination,
                ];
            }
        }

        return response()->json($data);
    }

    private function budgetLoadConsolidated(Request $request)
    {
        $data = [];
        $collectionAccounts = collect(json_decode($request->accounts, true));
        $accountsAll = $collectionAccounts->search(fn ($item) => 'Todas' === $item['id']);
        $projectsAll = collect(json_decode($request->proyects, true))->search(fn ($item) => 'Todos' === $item['id']);
        $centralizedActionsAll = collect(json_decode($request->centralized_actions, true))->search(fn ($item) => 'Todas' === $item['id']);

        $accountIds = $collectionAccounts == null || count($collectionAccounts) == 0 || $accountsAll !== false ?
            'Todas' :
            $collectionAccounts->pluck('id')->toArray();

        $dateFrom = Carbon::parse($request->from);
        $dateTo = Carbon::parse($request->to);
        $specificActions = collect(json_decode($request->specific_actions, true));
        $specificActionIds = json_decode($request->specific_actions, true);

        if (!empty($request->proyects)) {
            $projectIds = $projectsAll !== false ?
                'Todos' :
                collect(json_decode($request->proyects, true))->pluck('id')->toArray();

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

        if (!empty($request->centralized_actions)) {
            $centralizedActionIds = $centralizedActionsAll !== false ?
                'Todas' :
                collect(json_decode($request->centralized_actions, true))->pluck('id')->toArray();

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
     * Genera el reporte de conciliado y lo envía al usuario.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Modules\Budget\Actions\Reports\ExportConsolidatedReportAction  $export
     * @return \Illuminate\Http\RedirectResponse
     */
    public function budgetConsolidatedExport(Request $request, ExportConsolidatedReportAction $export)
    {
        try {
            $monthFrom = Str::ucfirst(Carbon::parse($request->from)->translatedFormat('F'));
            $monthTo = Str::ucfirst(Carbon::parse($request->to)->translatedFormat('F'));
            $date = Carbon::createFromFormat('Y-m-d', $request->to)->format('d/m/Y');

            CreateAndSendBudgetConsolidatedReportJob::dispatch(
                auth()->user()->id,
                $request->all(),
                $monthFrom,
                $monthTo,
                now()->format('d-m-Y') . '_Reporte_Consolidado'
            );

            return response()->json(['result' => true], 200);
        } catch (\Exception $e) {
            Log::error($e);

            return redirect(
                route('budget.report.consolidated', [
                    'message' => 'error'
                ])
            );
        }
    }

    /**
     * [Genera el reporte en hoja de calculo de Mayor Analítico]
     * @param  integer $report [id de reporte y su informacion]
     */
    public function export($report)
    {
        return  $this->pdf($report, true);
    }
}
