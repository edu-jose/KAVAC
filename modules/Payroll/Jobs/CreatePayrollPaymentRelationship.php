<?php

namespace Modules\Payroll\Jobs;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Payroll\Models\Payroll;
use Modules\Payroll\Models\Parameter;
use Illuminate\Queue\SerializesModels;
use Modules\Payroll\Models\Institution;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\SystemNotification;
use Modules\Payroll\Models\DocumentStatus;
use Modules\Payroll\Models\PayrollConcept;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Payroll\Models\PayrollStaffPayroll;
use Illuminate\Queue\MaxAttemptsExceededException;
use Modules\Payroll\Models\PayrollFortnightlyAdvanceDebtor;
use App\Events\SystemNotification as EventSystemNotification;
use Modules\Payroll\Actions\PayrollPaymentRelationshipAction;
use Modules\Payroll\Exceptions\FailedPayrollConceptException;
use Modules\Payroll\Repositories\PayrollAssociatedParametersRepository;

/**
 * @class CreatePayrollPaymentRelationship
 * @brief Trabajo que se encarga de registrar la relación de pago de la nómina
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollPaymentRelationship implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Variable que contiene el tiempo de espera para la ejecución del trabajo,
     * si no se quiere limite de tiempo, se define en 0
     *
     * @var integer $timeout
     */
    public $timeout = 0; //300; /** 5min */

    private $payrollConceptsArray = [];

    /**
     * Variable que contendra el total de las asignaciones
     *
     * @var float
     */
    private float $totalAsignations = 0;

    private float $totalAsignationsByPayrollStaff = 0;
    private float $totalDeductionsByPayrollStaff = 0;
    private float $totalPaidByPayrollStaff = 0;

    /**
     * Variable que contendra el total de las deducciones
     *
     * @var float
     */
    private float $totalDeductions = 0;

    /**
     * Crea una nueva instancia del trabajo
     *
     * @return void
     */
    public function __construct(
        protected array $data,
        protected PayrollPaymentRelationshipAction $payrollPaymentAction = new PayrollPaymentRelationshipAction(),
    ) {
        if ('local' !== @env('APP_ENV')) {
            $this->onQueue('bulk');
        }
    }

    /**
     * Ejecuta el trabajo de registrar la nómina de sueldos
     *
     * @return void
     */
    public function handle()
    {
        try {
            $startTime = microtime(true);
            $user = User::without(['roles', 'permissions'])->where('id', $this->data['user_id'])->first();
            $user->notify(new SystemNotification('Alerta', 'Se está ejecutando la nómina, por favor espere...'));

            $payrollParameters = new PayrollAssociatedParametersRepository();
            /* Objeto asociado al modelo Payroll */
            $payroll = Payroll::query()->findOrFail($this->data['id']);

            $period = $payroll->payrollPaymentPeriod;
            $period_start = $period?->start_date;
            $period_end = $period?->end_date;

            PayrollStaffPayroll::query()
                ->where('payroll_id', $payroll->id)
                ->forceDelete();

            /* Se recorren los conceptos establecidos para la generación de la nómina */
            $concepts = [];
            $fullConcepts = array_merge(
                array_map(function ($item) {
                    $item['time_sheet'] = 'active';
                    return $item;
                }, $this->data['payroll_concepts'] ?? []),
                array_map(function ($item) {
                    $item['time_sheet'] = 'pending';
                    return $item;
                }, $this->data['pending_concepts'] ?? []),
            );

            $fullConceptsIds = array_column($fullConcepts, 'id');
            $prueba = PayrollConcept::query()
                ->with(['payrollConceptAssignOptions', 'payrollConceptType', 'budgetAccount', 'accountingAccount'])
                ->whereIn('id', $fullConceptsIds)
                ->get();
            $this->payrollConceptsArray = $prueba->toArray();

            [$withTotalsConcepts, $withoutTotalsConcepts] = $prueba->partition(
                function (PayrollConcept $concept): bool {
                    $currentIndex = $this->getIndex(
                        $this->payrollConceptsArray,
                        'id',
                        $concept->id
                    );

                    if ($this->verifyIfConceptContainsTotalParameters($concept)) {
                        $this->payrollConceptsArray[$currentIndex]['marked'] = true;
                        return true;
                    } else {
                        $this->payrollConceptsArray[$currentIndex]['marked'] = false;
                        return false;
                    }
                }
            );

            $prueba = $withoutTotalsConcepts->concat($withTotalsConcepts);

            foreach ($prueba as $payrollConcept) {
                $currentIndex = $this->getIndex($fullConcepts, 'id', $payrollConcept->id);
                $concept = $fullConcepts[$currentIndex];

                array_push(
                    $concepts,
                    [
                        'field' => $payrollConcept,
                        'time_sheet' => $concept['time_sheet'],
                        'staffs' => $concept['staffs'] ?? [],
                    ]
                );
            }

            /* Se guardan los tabuladores salariales usados en la nómina */
            $payroll->salary_tabulators = getPayrollSalaryTabulators($concepts);

            $extraOptions = [];

            foreach ($concepts as $concept) {
                foreach ($concept['field']->payrollConceptAssignOptions->where('key', 'staff') as $assign_option) {
                    $extraOptions[$concept['field']->id][] = $assign_option['assignable_id'];
                }
            }
            $staffsPending = array_reduce($this->data['pending_concepts'], function ($carry, $concept) {
                return array_merge($carry, $concept['staffs']);
            }, []);
            $exceptionStaffs = array_unique(array_merge($staffsPending, ...$extraOptions));
            /* Se evaluan los parámetros del expediente del trabajador y de la configuración de vacaciones */
            /* Se identifica la institución en la que se está operando */
            $institution = Institution::query()
                ->when(! empty($this->data['institution_id']), function ($query) {
                    $query->where('id', $this->data['institution_id']);
                }, function ($query) {
                    $query->where('active', true)->where('default', true);
                })
                ->first();

            /**
             * Se recorren los conceptos establecidos para la generación de la nómina
             * Se obtienen los trabajadores que aplican para cada concepto.
             */

            $assignToRules = $payrollParameters->loadData('assignTo');
            $finalResults = [];
            $totalConcept = count($concepts);
            $progreso = [25, 50, 75];
            $notificados = [];
            $usedStaffIds = [];

            foreach ($concepts as $indexConcept => $payrollConcept) {
                Log::info("Procesando concepto ID: {$payrollConcept['field']->id}, Nombre: {$payrollConcept['field']->name} (" . ($indexConcept + 1) . "/{$totalConcept}) de la nómina {$payroll->code}.");
                $results = collect();
                $conceptId = $payrollConcept['field']->id;
                $conceptFilters = json_decode($payrollConcept['field']->assign_to) ?? [];
                $isStrict = $payrollConcept['field']->is_strict ?? false;
                $conceptOptions = $payrollConcept['field']->payrollConceptAssignOptions;
                $isConceptAdvancement = $payrollConcept['field']->is_concept_advancement ?? false;
                $isAdvanceDeduction = $payrollConcept['field']->is_advance_deduction ?? false;

                // Llamar a findAssignableStaff para obtener IDs para ESTE concepto
                $assignableStaffs = findAssignableStaff(
                    $conceptFilters,
                    $assignToRules,
                    $conceptOptions,
                    $period_start,
                    $period_end,
                    $exceptionStaffs,
                    $isStrict
                );

                $assignableIds = $assignableStaffs->pluck('id')->filter()->toArray();

                if ($isConceptAdvancement) {
                    $insertData = [];
                    foreach ($assignableIds as $staffId) {
                        $insertData[] = [
                            'payroll_concept_id' => $conceptId,
                            'payroll_payment_type_id' => $period->payroll_payment_type_id,
                            'payroll_payment_period_id' => $payroll->payroll_payment_period_id,
                            'payroll_staff_id' => $staffId,
                            'filter_rule' => 'all_staff_not_in_vacation',
                            'in_debt' => true,
                        ];
                    }

                    if (!empty($insertData)) {
                        PayrollFortnightlyAdvanceDebtor::upsert(
                            $insertData,
                            ['payroll_concept_id', 'payroll_payment_type_id', 'payroll_payment_period_id', 'payroll_staff_id'],
                            ['filter_rule', 'in_debt']
                        );
                    }
                }

                if ($isAdvanceDeduction) {
                    if (!empty($assignableIds)) {
                        PayrollFortnightlyAdvanceDebtor::whereIn('payroll_staff_id', $assignableIds)->forceDelete();
                    }
                }

                $assignableIdsSqlArray = '{' . implode(',', $assignableIds) . '}';

                $results = DB::select(
                    "SELECT * FROM process_formula(?::INT[], ?, ?, ?, ?, ?)",
                    [
                        $assignableIdsSqlArray,
                        $conceptId,
                        $period_start,
                        $period_end,
                        $institution->id,
                        $payroll->id
                    ]
                );

                foreach ($results as $row) {
                    $staffId = $row->staff_id;
                    $usedStaffIds[$staffId] = $staffId;
                    $conceptType = $row->concept_type ?? 'Sin tipo';

                    // Inicializar si no existe aún
                    if (!isset($finalResults[$staffId])) {
                        $finalResults[$staffId] = [
                            'load_basic_data' => [],
                            'concept_type' => [],
                        ];
                    }

                    // Agregar el concepto formateado
                    $finalResults[$staffId]['load_basic_data'] = [
                        'full_name' => $row->staff_full_name ?? '',
                        'id_number' => $row->staff_id_number ?? '',
                        'instruction_degree' => $row->instruction_degree ?? '',
                        'position' => $row->staff_position ?? '',
                        'start_date' => $row->staff_start_date ?? '',
                        'institution_years' => $row->institution_years ?? 0,
                    ];

                    $finalResults[$staffId]['concept_type'][$conceptType][] = [
                        'id' => $conceptId,
                        'name' => $row->concept_name,
                        'value' => $row->calculated_value,
                        'time_sheet' => $payrollConcept['time_sheet'],
                        'sign' => $payrollConcept['field']->payrollConceptType->sign,
                        'accouting_account_id' => $payrollConcept['field']->accounting_account_id ?? '',
                        'budget_account_id' => $payrollConcept['field']->budget_account_id ?? '',
                        'budget_account_code' => $payrollConcept['field']->budgetAccount->code ?? '',
                        'budget_account_denomination' => $payrollConcept['field']->budgetAccount->denomination ?? '',
                        'accounting_account_code' => $payrollConcept['field']->accountingAccount->code ?? '',
                        'accounting_account_denomination' => $payrollConcept['field']->accountingAccount->denomination ?? '',
                        'formula' => Arr::last($payrollConcept['field']->formula_show_history),
                    ];
                }
                $porcentaje = (int) ((($indexConcept + 1) / $totalConcept) * 100);
                if (100 != $porcentaje) {
                    event(new EventSystemNotification([
                        'user_id' => $user->id,
                        'payroll_id' => $payroll->id,
                        'payroll_code' => $payroll->code,
                        'porcentaje' => $porcentaje,
                        'procesados' => $indexConcept + 1,
                        'total' => $totalConcept
                    ]));
                    foreach ($progreso as $p) {
                        if ($porcentaje >= $p && !in_array($p, $notificados)) {
                            $notificados[] = $p;
                            Log::info('Se ha alcanzado el ' . $p . '% de la nómina ' . $payroll->code);
                            $user->notify(new SystemNotification('Información', $p . '% de la nómina ' . $payroll->code . ' completado.'));
                        }
                    }
                }
                Log::info("Concepto ID: {$payrollConcept['field']->id}, Nombre: {$payrollConcept['field']->name} procesado correctamente.");
            }

            foreach ($concepts as $indexConcept => $payrollConcept) {
                foreach ($usedStaffIds as $usedStaffId) {
                    $conceptTypeName = $payrollConcept['field']->payrollConceptType->name;
                    $conceptId = $payrollConcept['field']->id;

                    // Verificar si el concepto ya existe para el staffId
                    $exists = isset($finalResults[$usedStaffId]['concept_type'][$conceptTypeName]) &&
                        array_filter($finalResults[$usedStaffId]['concept_type'][$conceptTypeName], function ($concept) use ($conceptId) {
                            return $concept['id'] === $conceptId;
                        });

                    // Solo agregar si no existe
                    if (!$exists) {
                        $finalResults[$usedStaffId]['concept_type'][$conceptTypeName][] = [
                            'id' => $conceptId,
                            'name' => $payrollConcept['field']->name,
                            'value' => 0,
                            'time_sheet' => $payrollConcept['time_sheet'],
                            'sign' => $payrollConcept['field']->payrollConceptType->sign,
                            'accouting_account_id' => $payrollConcept['field']->accounting_account_id ?? '',
                            'budget_account_id' => $payrollConcept['field']->budget_account_id ?? '',
                            'budget_account_code' => $payrollConcept['field']->budgetAccount->code ?? '',
                            'budget_account_denomination' => $payrollConcept['field']->budgetAccount->denomination ?? '',
                            'accounting_account_code' => $payrollConcept['field']->accountingAccount->code ?? '',
                            'accounting_account_denomination' => $payrollConcept['field']->accountingAccount->denomination ?? '',
                            'formula' => Arr::last($payrollConcept['field']->formula_show_history),
                        ];
                    }
                }
            }

            Log::info("Guardando los resultados de la nómina {$payroll->code}.");
            foreach ($finalResults as $staffId => &$row) {
                // Ordenar las claves de concept_type alfabéticamente
                ksort($row['concept_type']);

                // Ordenar los conceptos dentro de cada tipo por el campo 'id'
                foreach ($row['concept_type'] as $typeName => &$concepts) {
                    usort($concepts, function ($a, $b) {
                        return $a['id'] <=> $b['id'];
                    });
                }
            }
            unset($row);

            foreach ($finalResults as $staffId => $row) {
                $add = false;
                foreach ($row['concept_type'] as $type) {
                    foreach ($type as $t) {
                        if ($t['value'] > 0) {
                            $add = true;
                        }
                    }
                }

                if ($add == true) {
                    PayrollStaffPayroll::create(
                        [
                            'payroll_id' => $payroll->id,
                            'payroll_staff_id' => $staffId,
                            'concept_type' => $row['concept_type'],
                            'basic_payroll_staff_data' => $row['load_basic_data'],
                        ]
                    );
                }
            }
            Log::info("Se han guardado los resultados de la nómina {$payroll->code}.");

            /* Se capturan los conceptos de la nómina */
            $payroll->concept_types = collect($finalResults)->first()['concept_type'] ?? [];
            $payroll->document_status_id = DocumentStatus::query()->where('action', 'EL')->value('id');
            $payroll->save();

            $endTime = microtime(true);
            $duration = $endTime - $startTime;

            $hours = floor($duration / 3600);
            $minutes = floor(($duration % 3600) / 60);
            $seconds = $duration % 60;

            $executionTime = sprintf('%02d horas %02d minutos %02d segundos', $hours, $minutes, $seconds);
            Log::info("Se ha alcanzado el 100% de la nómina {$payroll->code} en {$executionTime}.");
            $user->notify(new SystemNotification('Éxito', "Nómina ejecutada con éxito en {$executionTime}."));

            event(new EventSystemNotification([
                'user_id' => $user->id,
                'payroll_id' => $payroll->id,
                'payroll_code' => $payroll->code,
                'porcentaje' => 100,
                'procesados' => $totalConcept,
                'total' => $totalConcept
            ]));
        } catch (\Exception $e) {
            $payroll = Payroll::where('id', $this->data['id'])->update(['document_status_id' => null]);

            $user = User::without(['roles', 'permissions'])->where('id', $this->data['user_id'])->first();
            Log::critical("Se generó un error en el procesamiento de la nómina en el archivo [{$e->getFile()}] en la línea [{$e->getLine()}]. Código del error: {$e->getCode()}, Detalles: {$e->getMessage()}.\n Se muestra a continuación una traza de los archivos que generaron el error: {$e->getTraceAsString()}");
            if ($e instanceof FailedPayrollConceptException) {
                $user->notify(
                    new SystemNotification('Fallido', 'Ah ocurrido un error en la ejecución de la nómina ' .
                        $e->getMessage())
                );
            } else {
                $user->notify(new SystemNotification('Alerta', 'Se generó un error al generar la nómina, Contacte al administrador del sistema.'));
            }
        }
    }

    /**
     * Traduce la fórmula de conceptos
     *
     * @param string $form Fórmula del concepto
     *
     * @return string
     */
    public function translateFormConcept($form)
    {
        $formula = $form;
        /* Se hace la busqueda de los parámetros globales */
        $parameters = Parameter::query()
            ->where(
                [
                    'required_by' => 'payroll',
                    'active' => true,
                ]
            )
            ->where('p_key', 'like', 'global_parameter_%')
            ->get();
        foreach ($parameters as $parameter) {
            $jsonValue = json_decode($parameter->p_value);
            if (
                $jsonValue->parameter_type == 'resettable_variable' ||
                $jsonValue->parameter_type == 'time_parameter' ||
                $jsonValue->parameter_type == 'processed_variable'
            ) {
                $formula = str_replace('parameter(' . $jsonValue->id . ')', $jsonValue->name, $formula);
            } else {
                if ($jsonValue->percentage) {
                    $jsonValue->value = $jsonValue->value / 100;
                }
                $formula = str_replace('parameter(' . $jsonValue->id . ')', $jsonValue->value, $formula);
            }
        }
        /* Se hace la busqueda de los conceptos */
        $matchs = [];
        preg_match_all("/concept\([0-9]+\)/", $formula, $matchs);

        foreach ($matchs[0] as $match) {
            $id = substr($match, (strpos($match, '(') + 1), strpos($match, ')') - (strpos($match, '(') + 1));
            $concept = PayrollConcept::find($id);
            $formula = str_replace('concept(' . $id . ')', $this->translateFormConcept($concept['formula']), $formula);
        }

        return '(' . $formula . ')';
    }

    /**
     * Maneja el fallo del trabajo.
     *
     * @param \Throwable $exception Excepción generada al ocurrir un error en el trabajo
     *
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        $user = User::without(['roles', 'permissions'])->where('id', $this->data['user_id'])->first();
        if ($exception instanceof MaxAttemptsExceededException) {
            $user->notify(
                new SystemNotification(
                    'Fallido',
                    'Ah ocurrido un error en la ejecución de la nómina, ' .
                        'para mas información comuniquese con el administrador.'
                )
            );
        } else {
            $user->notify(
                new SystemNotification('Fallido', 'Ah ocurrido un error en la ejecución de la nómina ' .
                    $exception->getMessage())
            );
        }
        Log::error($exception->getMessage());
    }

    /**
     * Obtiene el indice de un arreglo en base a un parámetro y un valor
     * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @return int|null
     */
    private function getIndex(array $array, string $key, $value): int|null
    {
        $index = 0;
        $valueFound = false;

        foreach ($array as $item) {
            if ($item[$key] == $value) {
                $valueFound = true;
                break;
            }
            $index++;
        }

        return $valueFound ? $index : null;
    }

    /**
     * Verifica si el concepto contiene los parámetros TOTAL_ASSIGNMENTS y TOTAL_PAID
     *
     * @param \Modules\Payroll\Models\PayrollConcept $concept
     * @return bool
     */
    private function verifyIfConceptContainsTotalParameters(PayrollConcept $concept): bool
    {
        $currentConceptFormula = $concept->translate_formula;
        return Str::contains($currentConceptFormula, 'TOTAL_ASSIGNMENTS') ||
            Str::contains($currentConceptFormula, 'TOTAL_PAID');
    }
}
