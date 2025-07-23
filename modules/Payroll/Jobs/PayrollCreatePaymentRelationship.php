<?php

namespace Modules\Payroll\Jobs;

use App\Events\SystemNotification as EventSystemNotification;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Modules\Payroll\Models\Payroll;
use Modules\Payroll\Models\Parameter;
use Illuminate\Queue\SerializesModels;
use Modules\Payroll\Models\Institution;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Payroll\Models\PayrollStaff;
use App\Notifications\SystemNotification;
use Doctrine\DBAL\Types\ObjectType;
use Modules\Payroll\Models\PayrollConcept;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Payroll\Models\PayrollAriRegister;
use Modules\Payroll\Models\PayrollConceptType;
use Modules\Payroll\Models\PayrollStaffPayroll;
use Modules\Payroll\Models\PayrollPaymentPeriod;
use Illuminate\Queue\MaxAttemptsExceededException;
use Modules\Payroll\Models\PayrollSalaryTabulator;
use Modules\Payroll\Actions\PayrollPaymentRelationshipAction;
use Modules\Payroll\Exceptions\FailedPayrollConceptException;
use Modules\Payroll\Models\DocumentStatus;
use Modules\Payroll\Repositories\PayrollAssociatedParametersRepository;

/**
 * @class PayrollCreatePaymentRelationship
 * @brief Trabajo que se encarga de registrar la relación de pago de la nómina
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollCreatePaymentRelationship implements ShouldQueue
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
            $user = User::without(['roles', 'permissions'])->where('id', $this->data['user_id'])->first();
            $user->notify(new SystemNotification('Alerta', 'Se está ejecutando la nómina, por favor espere...'));
            //$user->notify(new System('', 'Talento Humano', 'Ejecutando', 'Se está ejecutando la nómina, por favor espere'));
            $payrollParameters = new PayrollAssociatedParametersRepository();
            /* Objeto asociado al modelo Payroll */
            $payroll = Payroll::query()->findOrFail($this->data['id']);

            $period = $payroll->payrollPaymentPeriod;
            $period_start = $period?->start_date;
            $period_end = $period?->end_date;

            $this->data['payroll_parameters'] = $this->payrollPaymentAction->getPayrollParameters($payroll->id);

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
                $formula = null;
                $formula = $this->translateFormConcept($payrollConcept->formula);
                $exploded = multiexplode(
                    [
                        'if', '(', ')', '{', '}',
                        '==', '<=', '>=', '<', '>', '!=',
                        '+', '-', '*', '/', 'select', 'case',
                        'when', 'else', 'end', ';', 'then',
                        ',', '.',
                    ],
                    $formula
                );
                while (count($exploded) > 0) {
                    $complete = false;
                    $current = max_length($exploded);
                    $key = array_search($current, $exploded);
                    /** Se descartan los elementos vacios y las constantes númericas */
                    if ($current == '' || is_numeric($current)) {
                        unset($exploded[$key]);
                        $complete = true;
                    } else {
                        /* Se recorre el listado de parámetros para sustituirlos por su valor real en la formula del concepto */
                        foreach ($this->data['payroll_parameters'] as $parameter) {
                            if (gettype($parameter) == 'object') {
                                $parameter = (array)$parameter;
                            }
                            if (
                                isset($parameter['time_sheet']) &&
                                $parameter['time_sheet'] != $concept['time_sheet']
                            ) {
                                continue;
                            }

                            if ($parameter['name'] == $current) {
                                if (! isset($parameter['staff_id'])) {
                                    unset($exploded[$key]);
                                    $complete = true;
                                    $formula = str_replace($parameter['name'], $parameter['value'], $formula);
                                }
                            }
                        }
                        if ($complete == false) {
                            /* Se descartan los parametro de vacaciones y los del expediente del trabajador para ser analizados mas adelante */
                            unset($exploded[$key]);
                            $complete = true;
                        }
                    }
                }
                array_push(
                    $concepts,
                    [
                        'field' => $payrollConcept,
                        'formula' => $formula,
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

            $startTime = microtime(true);
            $assignToRules = $payrollParameters->loadData('assignTo');
            $allRelevantStaffIdsForConcept = [];

            foreach ($concepts as $payrollConcept) {
                $conceptId = $payrollConcept['field']->id;
                $conceptFilters = json_decode($payrollConcept['field']->assign_to) ?? [];
                $isStrict = $payrollConcept['field']->is_strict ?? false;
                $conceptOptions = $payrollConcept['field']->payrollConceptAssignOptions;

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

                $assignableIds = $assignableStaffs->pluck('id')->filter()->all(); // Filtrar IDs no nulos;

                // Acumular IDs de trabajadores para cada concepto
                if (!empty($assignableIds)) {
                    $allRelevantStaffIdsForConcept[$conceptId] = $assignableIds;
                }
            }

            /**
             * TODO: Usar uniqueRelevantStaffIds para filtrar los trabajadores
             * que se van a procesar en la nómina.
             */
            $uniqueRelevantStaffIds = array_unique(array_merge(...array_values($allRelevantStaffIdsForConcept)));

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            $payrollStaffsTotal = count($uniqueRelevantStaffIds);
            $concepsTotal = count($concepts);

            Log::info("Tiempo de ejecución para conseguir {$payrollStaffsTotal} trabajadores, en {$concepsTotal} conceptos: {$executionTime} segundos");


            /* Se obtienen todos los trabajadores asociados a la institución y se evalua si aplica cada uno de los conceptos */
            $payrollStaffs = PayrollStaff::query()
                ->without(
                    'payrollNationality',
                    'payrollFinancial',
                    'payrollGender',
                    'payrollBloodType',
                    'payrollDisability',
                    'payrollLicenseDegree',
                    'payrollStaffUniformSize',
                    'payrollSocioeconomic',
                    'payrollProfessional',
                    'payrollResponsibility'
                )->with('payrollEmployment')
                ->whereHas('payrollEmployment', function ($q) use ($institution, $period_end) {
                    $q->whereHas('department', function ($qq) use ($institution) {
                        $qq->where('institution_id', $institution->id);
                    })
                    ->where('start_date', '<=', $period_end);
                })
                ->whereIn('id', $uniqueRelevantStaffIds);

            /* Se definen los arreglos de asignaciones y deducciones para clasificar los conceptos */
            $conceptTypes = PayrollConceptType::query()
                ->get('name');
            $types = [];

            $totalStaff = $payrollStaffs->count();
            $progreso = [25, 50, 75];
            $notificados = [];

            $payrollStaffs = $payrollStaffs->orderBy('first_name', 'asc')->get();
            foreach ($payrollStaffs as $keyStaff => $payrollStaff) {
                $this->totalAsignationsByPayrollStaff = 0;
                $this->totalDeductionsByPayrollStaff = 0;
                $this->totalPaidByPayrollStaff = 0;

                foreach ($conceptTypes as $conceptType) {
                    $types[$conceptType->name] = [];
                }

                foreach ($concepts as $concept) {
                    $conceptId = $concept['field']->id;

                    $currentConceptIndex = $this->getIndex(
                        $this->payrollConceptsArray,
                        'id',
                        $conceptId
                    );
                    $conceptWithMark = $this->payrollConceptsArray[$currentConceptIndex];
                    $originalConcept = $concept;

                    // Verificar si el trabajador aplica para el concepto
                    $verify = isset($allRelevantStaffIdsForConcept[$conceptId])
                        ? (in_array($payrollStaff->id, $allRelevantStaffIdsForConcept[$conceptId]))
                        : false;

                    if ($verify) {
                        if (($concept['time_sheet'] == 'pending') && !in_array($payrollStaff->id, $concept['staffs'])) {
                            $concept['field']->load('payrollConceptType');
                            array_push($types[$concept['field']->payrollConceptType->name], [
                                'id' => $conceptId ?? '',
                                'name' => $concept['field']->name,
                                'value' => 0,
                                'time_sheet' => $concept['time_sheet'] ?? '',
                                'sign' => $concept['field']->payrollConceptType['sign'],
                                'accouting_account_id' => $concept['field']->accounting_account_id ?? '',
                                'budget_account_id' => $concept['field']->budget_account_id ?? '',
                                'budget_account_code' => $concept['field']->budgetAccount->code ?? '',
                                'budget_account_denomination' => $concept['field']->budgetAccount->denomination ?? '',
                                'accounting_account_code' => $concept['field']->accountingAccount->code ?? '',
                                'accounting_account_denomination' => $concept['field']->accountingAccount->denomination ?? '',
                                'formula' => $concept['field']->translate_formula ?? '',
                            ]);
                        } else {
                            $types = $this->setFormula(
                                $payrollStaff,
                                $concept,
                                $payrollParameters,
                                $institution,
                                $types,
                                $period_start,
                                $period_end
                            );
                        }
                    } else {
                        /* Se carga la propiedad payrollConceptType para determinar como clasificar el concepto */
                        $concept['field']->load('payrollConceptType');
                        array_push($types[$concept['field']->payrollConceptType->name], [
                            'id' => $conceptId ?? '',
                            'name' => $concept['field']->name,
                            'value' => 0,
                            'time_sheet' => $concept['time_sheet'] ?? '',
                            'sign' => $concept['field']->payrollConceptType['sign'],
                            'accouting_account_id' => $concept['field']->accounting_account_id ?? '',
                            'budget_account_id' => $concept['field']->budget_account_id ?? '',
                            'budget_account_code' => $concept['field']->budgetAccount->code ?? '',
                            'budget_account_denomination' => $concept['field']->budgetAccount->denomination ?? '',
                            'accounting_account_code' => $concept['field']->accountingAccount->code ?? '',
                            'accounting_account_denomination' => $concept['field']->accountingAccount->denomination ?? '',
                            'formula' => $concept['field']->translate_formula ?? '',
                        ]);
                    }
                }

                $this->totalAsignationsByPayrollStaff = 0;
                $this->totalDeductionsByPayrollStaff = 0;
                $this->totalPaidByPayrollStaff = 0;
                $add = false;

                foreach ($types as $type) {
                    foreach ($type as $t) {
                        if ($t['value'] > 0) {
                            $add = true;
                        }
                    }
                }

                if ($add == true) {
                    $basic_payroll_staff_data = loadBasicPayrollStaffData($payrollStaff, $period_end);
                    PayrollStaffPayroll::create(
                        [
                            'payroll_id' => $payroll->id,
                            'payroll_staff_id' => $payrollStaff->id,
                            'concept_type' => $types,
                            'basic_payroll_staff_data' => $basic_payroll_staff_data ?? [],
                        ]
                    );
                }

                $porcentaje = (int) ((($keyStaff + 1) / $totalStaff) * 100);
                if (100 != $porcentaje) {
                    event(new EventSystemNotification([
                        'user_id' => $user->id,
                        'payroll_id' => $payroll->id,
                        'payroll_code' => $payroll->code,
                        'porcentaje' => $porcentaje,
                        'procesados' => $keyStaff + 1,
                        'total' => $totalStaff
                    ]));
                    foreach ($progreso as $p) {
                        if ($porcentaje >= $p && !in_array($p, $notificados)) {
                            $notificados[] = $p;
                            $timeOut = (microtime(true) - $startTime);
                            Log::info('Se ha alcanzado el ' . $p . '% de la nómina ' . $payroll->code . ', en: ' . $timeOut . ' segundos');
                            $estimatedTime = (100 * $timeOut) / $p;
                            LOg::info("Tiempo estimado para alcanzar el 100% de la nómina: {$estimatedTime} segundos");
                            $user->notify(new SystemNotification('Información', $p . '% de la nómina ' . $payroll->code . ' completado.'));
                        }
                    }
                }
            }

            /* Se capturan los conceptos de la nómina */
            $payroll->concept_types = $types;
            $payroll->document_status_id = DocumentStatus::query()->where('action', 'EL')->value('id');
            $payroll->save();

            Log::info('Se ha alcanzado el 100% de la nómina ' . $payroll->code . ', en ' . (microtime(true) - $startTime) . ' segundos');
            $user->notify(new SystemNotification('Éxito', 'Nomina ejecutada con éxito'));
            event(new EventSystemNotification([
                'user_id' => $user->id,
                'payroll_id' => $payroll->id,
                'payroll_code' => $payroll->code,
                'porcentaje' => 100,
                'procesados' => $totalStaff,
                'total' => $totalStaff
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
     * Establece la fórmula del concepto
     *
     * @param PayrollStaff $payrollStaff
     * @param PayrollConcept $concept
     * @param object $payrollParameters
     * @param Institution $institution
     * @param mixed $types
     * @param string $period_start
     * @param string $period_end
     *
     * @throws \Modules\Payroll\Exceptions\FailedPayrollConceptException
     *
     * @return mixed
     */
    private function setFormula(
        $payrollStaff,
        $concept,
        $payrollParameters,
        $institution,
        $types,
        $period_start,
        $period_end
    ) {
        foreach ($this->data['payroll_parameters'] as $parameter) {
            if (gettype($parameter) == 'object') {
                $parameter = (array)$parameter;
            }
            if (
                isset($parameter['time_sheet']) &&
                $parameter['time_sheet'] != $concept['time_sheet']
            ) {
                continue;
            }
            if (isset($parameter['staff_id']) && $parameter['staff_id'] == $payrollStaff->id) {
                $concept['formula'] = str_replace($parameter['name'], $parameter['value'], $concept['formula']);
            }
        }

        $formula = $concept['formula'];
        /* Se hace la busqueda de los tabuladores */
        $matchs = [];
        preg_match_all("/tabulator\([0-9]+\)/", $formula, $matchs);
        foreach ($matchs[0] as $match) {
            $id = substr($match, (strpos($match, '(') + 1), strpos($match, ')') - (strpos($match, '(') + 1));
            $payrollSalaryTabulator = PayrollSalaryTabulator::find($id);
            $salaryAdjustments = null;

            if ($payrollSalaryTabulator) {
                /* Revisar si el tabulador salarial tiene ajustes por la fecha del periodo final */
                $salaryAdjustmentWithEndDate = $payrollSalaryTabulator->payrollSalaryAdjustments()
                    ->whereNotNull('end_increase_date')
                    ->whereDate('start_increase_date', '<=', $period_end)
                    ->whereDate('end_increase_date', '>=', $period_end);

                if ($salaryAdjustmentWithEndDate->get()->isNotEmpty()) {
                    $salaryAdjustments = $salaryAdjustmentWithEndDate->first();
                }

                $salaryAdjustmentWithoutEndDate = $payrollSalaryTabulator->payrollSalaryAdjustments()
                    ->whereNull('end_increase_date')
                    ->whereDate('start_increase_date', '<=', $period_end);

                if ($salaryAdjustmentWithoutEndDate->get()->isNotEmpty()) {
                    $salaryAdjustments = $salaryAdjustmentWithoutEndDate->first();
                }
            }

            if ($payrollSalaryTabulator->payroll_salary_tabulator_type == 'horizontal') {
                /* Se carga el escalafón horizontal asociado al tabulador */
                $payrollSalaryTabulator->load(['payrollHorizontalSalaryScale' => function ($q) {
                    $q->with('payrollScales');
                }]);
                foreach ($payrollParameters->loadData('associatedWorkerFile') as $parameter) {
                    if (! empty($parameter['children'])) {
                        foreach ($parameter['children'] as $children) {
                            if ($children['id'] == $payrollSalaryTabulator->payrollHorizontalSalaryScale['group_by']) {
                                $record = ($parameter['model'] != PayrollStaff::class)
                                    ? $parameter['model']::query()
                                        ->where('payroll_staff_id', $payrollStaff->id)
                                        ->first()
                                    : $payrollStaff;
                                if (isset($record)) {
                                    foreach ($payrollSalaryTabulator->payrollHorizontalSalaryScale->payrollScales as $scale) {
                                        if ($children['type'] == 'number') {
                                            /* Se calcula el número de registros existentes según sea el caso
                                            y se sustituye por su valor en el tabulador */
                                            $scl = json_decode($scale['value']);
                                            $record->loadCount($children['required'][0]);

                                            if (isset($scl->from) && isset($scl->to)) {
                                                if (
                                                    ($record[Str::snake($children['required'][0]) . '_count'] >= $scl->from) &&
                                                    ($record[Str::snake($children['required'][0]) . '_count'] <= $scl->to)
                                                ) {
                                                    $formula = addTabulatorValuetoFormula(
                                                        $payrollSalaryTabulator,
                                                        $salaryAdjustments,
                                                        $scale,
                                                        null,
                                                        $concept,
                                                        $match,
                                                        $formula
                                                    );
                                                }
                                            } else {
                                                if ($scl == $record[Str::snake($children['required'][0]) . '_count']) {
                                                    $formula = addTabulatorValuetoFormula(
                                                        $payrollSalaryTabulator,
                                                        $salaryAdjustments,
                                                        $scale,
                                                        null,
                                                        $concept,
                                                        $match,
                                                        $formula
                                                    );
                                                }
                                            }
                                        } elseif ($children['type'] == 'date') {
                                            /* Se calcula el número de años según la fecha de ingreso
                                            y se sustituye por su valor en el tabulador */
                                            $scl = json_decode($scale['value']);
                                            if (isset($scl->from) && isset($scl->to)) {
                                                if (
                                                    (age($record[$children['required'][0]], $period_end, true) >= $scl->from) &&
                                                    (age($record[$children['required'][0]], $period_end, true) <= $scl->to)
                                                ) {
                                                    $formula = addTabulatorValuetoFormula(
                                                        $payrollSalaryTabulator,
                                                        $salaryAdjustments,
                                                        $scale,
                                                        null,
                                                        $concept,
                                                        $match,
                                                        $formula
                                                    );
                                                }
                                            } else {
                                                if ($scl == age($record[$children['required'][0]], $period_end)) {
                                                    $formula = addTabulatorValuetoFormula(
                                                        $payrollSalaryTabulator,
                                                        $salaryAdjustments,
                                                        $scale,
                                                        null,
                                                        $concept,
                                                        $match,
                                                        $formula
                                                    );
                                                }
                                            }
                                        } else {
                                            /* Se identifica el valor según el expediente del trabajador
                                            y se sustituye por su valor en el tabulador */
                                            if (json_decode($scale['value']) == $record[$children['required'][0]]) {
                                                $formula = addTabulatorValuetoFormula(
                                                    $payrollSalaryTabulator,
                                                    $salaryAdjustments,
                                                    $scale,
                                                    null,
                                                    $concept,
                                                    $match,
                                                    $formula
                                                );
                                            }
                                        }
                                    }
                                } else {
                                    $formula = str_replace(
                                        $match,
                                        0,
                                        $formula ?? $concept['formula']
                                    );
                                }
                            }
                        }
                    }
                }
            } elseif ($payrollSalaryTabulator->payroll_salary_tabulator_type == 'vertical') {
                /* Se carga el escalafón vertical asociado al tabulador */
                $payrollSalaryTabulator->load(['payrollVerticalSalaryScale' => function ($q) {
                    $q->with('payrollScales');
                }]);

                foreach ($payrollParameters->loadData('associatedWorkerFile') as $parameter) {
                    if (! empty($parameter['children'])) {
                        foreach ($parameter['children'] as $children) {
                            if ($children['id'] == $payrollSalaryTabulator->payrollVerticalSalaryScale['group_by']) {
                                $record = ($parameter['model'] != PayrollStaff::class)
                                    ? $parameter['model']::query()
                                        ->where('payroll_staff_id', $payrollStaff->id)
                                        ->first()
                                    : $payrollStaff;
                                if (isset($record)) {
                                    foreach ($payrollSalaryTabulator->payrollVerticalSalaryScale->payrollScales as $scale) {
                                        if ($children['type'] == 'number') {
                                            /* Se calcula el número de registros existentes según sea el caso
                                            y se sustituye por su valor en el tabulador */
                                            $scl = json_decode($scale['value']);
                                            $record->loadCount($children['required'][0]);

                                            if (isset($scl->from) && isset($scl->to)) {
                                                if (
                                                    ($record[Str::snake($children['required'][0]) . '_count'] >= $scl->from) &&
                                                    ($record[Str::snake($children['required'][0]) . '_count'] <= $scl->to)
                                                ) {
                                                    $formula = addTabulatorValuetoFormula(
                                                        $payrollSalaryTabulator,
                                                        $salaryAdjustments,
                                                        null,
                                                        $scale,
                                                        $concept,
                                                        $match,
                                                        $formula
                                                    );
                                                }
                                            } else {
                                                if ($scl == $record[Str::snake($children['required'][0]) . '_count']) {
                                                    $formula = addTabulatorValuetoFormula(
                                                        $payrollSalaryTabulator,
                                                        $salaryAdjustments,
                                                        null,
                                                        $scale,
                                                        $concept,
                                                        $match,
                                                        $formula
                                                    );
                                                }
                                            }
                                        } elseif ($children['type'] == 'date') {
                                            /* Se calcula el número de años según la fecha de ingreso
                                            y se sustituye por su valor en el tabulador */
                                            $scl = json_decode($scale['value']);
                                            if (isset($scl->from) && isset($scl->to)) {
                                                if (
                                                    (age($record[$children['required'][0]], $period_end, true) >= $scl->from) &&
                                                    (age($record[$children['required'][0]], $period_end, true) <= $scl->to)
                                                ) {
                                                    $formula = addTabulatorValuetoFormula(
                                                        $payrollSalaryTabulator,
                                                        $salaryAdjustments,
                                                        null,
                                                        $scale,
                                                        $concept,
                                                        $match,
                                                        $formula
                                                    );
                                                }
                                            } else {
                                                if ($scl == age($record[$children['required'][0]], $period_end)) {
                                                    $formula = addTabulatorValuetoFormula(
                                                        $payrollSalaryTabulator,
                                                        $salaryAdjustments,
                                                        null,
                                                        $scale,
                                                        $concept,
                                                        $match,
                                                        $formula
                                                    );
                                                }
                                            }
                                        } else {
                                            /* Se identifica el valor según el expediente del trabajador
                                            y se sustituye por su valor en el tabulador */
                                            if (json_decode($scale['value']) == $record[$children['required'][0]]) {
                                                $formula = addTabulatorValuetoFormula(
                                                    $payrollSalaryTabulator,
                                                    $salaryAdjustments,
                                                    null,
                                                    $scale,
                                                    $concept,
                                                    $match,
                                                    $formula
                                                );
                                            }
                                        }
                                    }
                                } else {
                                    $formula = str_replace(
                                        $match,
                                        0,
                                        $formula ?? $concept['formula']
                                    );
                                }
                            }
                        }
                    }
                }
            } else {
                /* Se cargan los escalafones horizontal y vertical asociados al tabulador */
                $payrollSalaryTabulator->load([
                    'payrollHorizontalSalaryScale' => function ($q) {
                        $q->with('payrollScales');
                    }, 'payrollVerticalSalaryScale' => function ($q) {
                        $q->with('payrollScales');
                    },
                ]);

                foreach ($payrollParameters->loadData('associatedWorkerFile') as $parameter) {
                    if (! empty($parameter['children'])) {
                        foreach ($parameter['children'] as $children) {
                            if ($children['id'] == $payrollSalaryTabulator->payrollHorizontalSalaryScale['group_by']) {
                                $record = ($parameter['model'] != PayrollStaff::class)
                                    ? $parameter['model']::query()
                                        ->where('payroll_staff_id', $payrollStaff->id)
                                        ->first()
                                    : $payrollStaff;
                                if (isset($record)) {
                                    foreach ($payrollSalaryTabulator->payrollHorizontalSalaryScale->payrollScales as $scale) {
                                        if ($children['type'] == 'number') {
                                            /* Se calcula el número de registros existentes según sea el caso
                                            y se sustituye por su valor en el tabulador */
                                            $scl = json_decode($scale['value']);
                                            $record->loadCount($children['required'][0]);

                                            if (isset($scl->from) && isset($scl->to)) {
                                                if (
                                                    ($record[Str::snake($children['required'][0]) . '_count'] >= $scl->from) &&
                                                    ($record[Str::snake($children['required'][0]) . '_count'] <= $scl->to)
                                                ) {
                                                    foreach ($payrollParameters->loadData('associatedWorkerFile') as $parameterV) {
                                                        if (! empty($parameterV['children'])) {
                                                            foreach ($parameterV['children'] as $childrenV) {
                                                                if ($childrenV['id'] == $payrollSalaryTabulator->payrollVerticalSalaryScale['group_by']) {
                                                                    $recordV = ($parameterV['model'] != PayrollStaff::class)
                                                                        ? $parameterV['model']::query()
                                                                            ->where('payroll_staff_id', $payrollStaff->id)
                                                                            ->first()
                                                                        : $payrollStaff;
                                                                    if (isset($recordV)) {
                                                                        foreach ($payrollSalaryTabulator->payrollVerticalSalaryScale->payrollScales as $scaleV) {
                                                                            if ($childrenV['type'] == 'number') {
                                                                                /* Se calcula el número de registros existentes según sea el caso
                                                                                y se sustituye por su valor en el tabulador */
                                                                                $sclV = json_decode($scaleV['value']);
                                                                                $recordV->loadCount($childrenV['required'][0]);

                                                                                if (isset($sclV->from) && isset($sclV->to)) {
                                                                                    if (
                                                                                        ($recordV[Str::snake($childrenV['required'][0]) . '_count'] >= $sclV->from) &&
                                                                                        ($recordV[Str::snake($childrenV['required'][0]) . '_count'] <= $sclV->to)
                                                                                    ) {
                                                                                        $formula = addTabulatorValuetoFormula(
                                                                                            $payrollSalaryTabulator,
                                                                                            $salaryAdjustments,
                                                                                            $scale,
                                                                                            $scaleV,
                                                                                            $concept,
                                                                                            $match,
                                                                                            $formula
                                                                                        );
                                                                                    }
                                                                                } else {
                                                                                    if ($sclV == $recordV[Str::snake($childrenV['required'][0]) . '_count']) {
                                                                                        $formula = addTabulatorValuetoFormula(
                                                                                            $payrollSalaryTabulator,
                                                                                            $salaryAdjustments,
                                                                                            $scale,
                                                                                            $scaleV,
                                                                                            $concept,
                                                                                            $match,
                                                                                            $formula
                                                                                        );
                                                                                    }
                                                                                }
                                                                            } elseif ($childrenV['type'] == 'date') {
                                                                                /* Se calcula el número de años según la fecha de ingreso
                                                                                y se sustituye por su valor en el tabulador */
                                                                                $sclV = json_decode($scaleV['value']);
                                                                                if (isset($sclV->from) && isset($sclV->to)) {
                                                                                    if (
                                                                                        (age($recordV[$childrenV['required'][0]], $period_end, true) >= $sclV->from) &&
                                                                                        (age($recordV[$childrenV['required'][0]], $period_end, true) <= $sclV->to)
                                                                                    ) {
                                                                                        $formula = addTabulatorValuetoFormula(
                                                                                            $payrollSalaryTabulator,
                                                                                            $salaryAdjustments,
                                                                                            $scale,
                                                                                            $scaleV,
                                                                                            $concept,
                                                                                            $match,
                                                                                            $formula
                                                                                        );
                                                                                    }
                                                                                } else {
                                                                                    if ($sclV == age($recordV[$childrenV['required'][0]], $period_end)) {
                                                                                        $formula = addTabulatorValuetoFormula(
                                                                                            $payrollSalaryTabulator,
                                                                                            $salaryAdjustments,
                                                                                            $scale,
                                                                                            $scaleV,
                                                                                            $concept,
                                                                                            $match,
                                                                                            $formula
                                                                                        );
                                                                                    }
                                                                                }
                                                                            } else {
                                                                                /* Se identifica el valor según el expediente del trabajador
                                                                                y se sustituye por su valor en el tabulador */
                                                                                if (json_decode($scaleV['value']) == $recordV[$childrenV['required'][0]]) {
                                                                                    $formula = addTabulatorValuetoFormula(
                                                                                        $payrollSalaryTabulator,
                                                                                        $salaryAdjustments,
                                                                                        $scale,
                                                                                        $scaleV,
                                                                                        $concept,
                                                                                        $match,
                                                                                        $formula
                                                                                    );
                                                                                }
                                                                            }
                                                                        }
                                                                    } else {
                                                                        $formula = str_replace(
                                                                            $match,
                                                                            0,
                                                                            $formula ?? $concept['formula']
                                                                        );
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            } else {
                                                if ($scl == $record[Str::snake($children['required'][0]) . '_count']) {
                                                    foreach ($payrollParameters->loadData('associatedWorkerFile') as $parameterV) {
                                                        if (! empty($parameterV['children'])) {
                                                            foreach ($parameterV['children'] as $childrenV) {
                                                                if ($childrenV['id'] == $payrollSalaryTabulator->payrollVerticalSalaryScale['group_by']) {
                                                                    $recordV = ($parameterV['model'] != PayrollStaff::class)
                                                                        ? $parameterV['model']::query()
                                                                            ->where('payroll_staff_id', $payrollStaff->id)
                                                                            ->first()
                                                                        : $payrollStaff;
                                                                    if (isset($recordV)) {
                                                                        foreach ($payrollSalaryTabulator->payrollVerticalSalaryScale->payrollScales as $scaleV) {
                                                                            if ($childrenV['type'] == 'number') {
                                                                                /* Se calcula el número de registros existentes según sea el caso
                                                                                y se sustituye por su valor en el tabulador */
                                                                                $sclV = json_decode($scaleV['value']);
                                                                                $recordV->loadCount($childrenV['required'][0]);

                                                                                if (isset($sclV->from) && isset($sclV->to)) {
                                                                                    if (
                                                                                        ($recordV[Str::snake($childrenV['required'][0]) . '_count'] >= $sclV->from) &&
                                                                                        ($recordV[Str::snake($childrenV['required'][0]) . '_count'] <= $sclV->to)
                                                                                    ) {
                                                                                        $formula = addTabulatorValuetoFormula(
                                                                                            $payrollSalaryTabulator,
                                                                                            $salaryAdjustments,
                                                                                            $scale,
                                                                                            $scaleV,
                                                                                            $concept,
                                                                                            $match,
                                                                                            $formula
                                                                                        );
                                                                                    }
                                                                                } else {
                                                                                    if ($sclV == $recordV[Str::snake($childrenV['required'][0]) . '_count']) {
                                                                                        $formula = addTabulatorValuetoFormula(
                                                                                            $payrollSalaryTabulator,
                                                                                            $salaryAdjustments,
                                                                                            $scale,
                                                                                            $scaleV,
                                                                                            $concept,
                                                                                            $match,
                                                                                            $formula
                                                                                        );
                                                                                    }
                                                                                }
                                                                            } elseif ($childrenV['type'] == 'date') {
                                                                                /* Se calcula el número de años según la fecha de ingreso
                                                                                y se sustituye por su valor en el tabulador */
                                                                                $sclV = json_decode($scaleV['value']);
                                                                                if (isset($sclV->from) && isset($sclV->to)) {
                                                                                    if (
                                                                                        (age($recordV[$childrenV['required'][0]], $period_end, true) >= $sclV->from) &&
                                                                                        (age($recordV[$childrenV['required'][0]], $period_end, true) <= $sclV->to)
                                                                                    ) {
                                                                                        $formula = addTabulatorValuetoFormula(
                                                                                            $payrollSalaryTabulator,
                                                                                            $salaryAdjustments,
                                                                                            $scale,
                                                                                            $scaleV,
                                                                                            $concept,
                                                                                            $match,
                                                                                            $formula
                                                                                        );
                                                                                    }
                                                                                } else {
                                                                                    if ($sclV == age($recordV[$childrenV['required'][0]], $period_end)) {
                                                                                        $formula = addTabulatorValuetoFormula(
                                                                                            $payrollSalaryTabulator,
                                                                                            $salaryAdjustments,
                                                                                            $scale,
                                                                                            $scaleV,
                                                                                            $concept,
                                                                                            $match,
                                                                                            $formula
                                                                                        );
                                                                                    }
                                                                                }
                                                                            } else {
                                                                                /* Se identifica el valor según el expediente del trabajador
                                                                                y se sustituye por su valor en el tabulador */
                                                                                if (json_decode($scaleV['value']) == $recordV[$childrenV['required'][0]]) {
                                                                                    $formula = addTabulatorValuetoFormula(
                                                                                        $payrollSalaryTabulator,
                                                                                        $salaryAdjustments,
                                                                                        $scale,
                                                                                        $scaleV,
                                                                                        $concept,
                                                                                        $match,
                                                                                        $formula
                                                                                    );
                                                                                }
                                                                            }
                                                                        }
                                                                    } else {
                                                                        $formula = str_replace(
                                                                            $match,
                                                                            0,
                                                                            $formula ?? $concept['formula']
                                                                        );
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        } elseif ($children['type'] == 'date') {
                                            /* Se calcula el número de años según la fecha de ingreso
                                            y se sustituye por su valor en el tabulador */
                                            $scl = json_decode($scale['value']);
                                            if (isset($scl->from) && isset($scl->to)) {
                                                if (
                                                    (age($record[$children['required'][0]], $period_end, true) >= $scl->from) &&
                                                    (age($record[$children['required'][0]], $period_end, true) <= $scl->to)
                                                ) {
                                                    foreach ($payrollParameters->loadData('associatedWorkerFile') as $parameterV) {
                                                        if (! empty($parameterV['children'])) {
                                                            foreach ($parameterV['children'] as $childrenV) {
                                                                if ($childrenV['id'] == $payrollSalaryTabulator->payrollVerticalSalaryScale['group_by']) {
                                                                    $recordV = ($parameterV['model'] != PayrollStaff::class)
                                                                        ? $parameterV['model']::query()
                                                                            ->where('payroll_staff_id', $payrollStaff->id)
                                                                            ->first()
                                                                        : $payrollStaff;
                                                                    if (isset($recordV)) {
                                                                        foreach ($payrollSalaryTabulator->payrollVerticalSalaryScale->payrollScales as $scaleV) {
                                                                            if ($childrenV['type'] == 'number') {
                                                                                /* Se calcula el número de registros existentes según sea el caso
                                                                                y se sustituye por su valor en el tabulador */
                                                                                $sclV = json_decode($scaleV['value']);
                                                                                $recordV->loadCount($childrenV['required'][0]);

                                                                                if (isset($sclV->from) && isset($sclV->to)) {
                                                                                    if (
                                                                                        ($recordV[Str::snake($childrenV['required'][0]) . '_count'] >= $sclV->from) &&
                                                                                        ($recordV[Str::snake($childrenV['required'][0]) . '_count'] <= $sclV->to)
                                                                                    ) {
                                                                                        $formula = addTabulatorValuetoFormula(
                                                                                            $payrollSalaryTabulator,
                                                                                            $salaryAdjustments,
                                                                                            $scale,
                                                                                            $scaleV,
                                                                                            $concept,
                                                                                            $match,
                                                                                            $formula
                                                                                        );
                                                                                    }
                                                                                } else {
                                                                                    if ($sclV == $recordV[Str::snake($childrenV['required'][0]) . '_count']) {
                                                                                        $formula = addTabulatorValuetoFormula(
                                                                                            $payrollSalaryTabulator,
                                                                                            $salaryAdjustments,
                                                                                            $scale,
                                                                                            $scaleV,
                                                                                            $concept,
                                                                                            $match,
                                                                                            $formula
                                                                                        );
                                                                                    }
                                                                                }
                                                                            } elseif ($childrenV['type'] == 'date') {
                                                                                /* Se calcula el número de años según la fecha de ingreso
                                                                                y se sustituye por su valor en el tabulador */
                                                                                $sclV = json_decode($scaleV['value']);
                                                                                if (isset($sclV->from) && isset($sclV->to)) {
                                                                                    if (
                                                                                        (age($recordV[$childrenV['required'][0]], $period_end, true) >= $sclV->from) &&
                                                                                        (age($recordV[$childrenV['required'][0]], $period_end, true) <= $sclV->to)
                                                                                    ) {
                                                                                        $formula = addTabulatorValuetoFormula(
                                                                                            $payrollSalaryTabulator,
                                                                                            $salaryAdjustments,
                                                                                            $scale,
                                                                                            $scaleV,
                                                                                            $concept,
                                                                                            $match,
                                                                                            $formula
                                                                                        );
                                                                                    }
                                                                                } else {
                                                                                    if ($sclV == age($recordV[$childrenV['required'][0]], $period_end)) {
                                                                                        $formula = addTabulatorValuetoFormula(
                                                                                            $payrollSalaryTabulator,
                                                                                            $salaryAdjustments,
                                                                                            $scale,
                                                                                            $scaleV,
                                                                                            $concept,
                                                                                            $match,
                                                                                            $formula
                                                                                        );
                                                                                    }
                                                                                }
                                                                            } else {
                                                                                /* Se identifica el valor según el expediente del trabajador
                                                                                y se sustituye por su valor en el tabulador */
                                                                                if (json_decode($scaleV['value']) == $recordV[$childrenV['required'][0]]) {
                                                                                    $formula = addTabulatorValuetoFormula(
                                                                                        $payrollSalaryTabulator,
                                                                                        $salaryAdjustments,
                                                                                        $scale,
                                                                                        $scaleV,
                                                                                        $concept,
                                                                                        $match,
                                                                                        $formula
                                                                                    );
                                                                                }
                                                                            }
                                                                        }
                                                                    } else {
                                                                        $formula = str_replace(
                                                                            $match,
                                                                            0,
                                                                            $formula ?? $concept['formula']
                                                                        );
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            } else {
                                                if ($scl == age($record[$children['required'][0]], $period_end)) {
                                                    foreach ($payrollParameters->loadData('associatedWorkerFile') as $parameterV) {
                                                        if (! empty($parameterV['children'])) {
                                                            foreach ($parameterV['children'] as $childrenV) {
                                                                if ($childrenV['id'] == $payrollSalaryTabulator->payrollVerticalSalaryScale['group_by']) {
                                                                    $recordV = ($parameterV['model'] != PayrollStaff::class)
                                                                        ? $parameterV['model']::query()
                                                                            ->where('payroll_staff_id', $payrollStaff->id)
                                                                            ->first()
                                                                        : $payrollStaff;
                                                                    if (isset($recordV)) {
                                                                        foreach ($payrollSalaryTabulator->payrollVerticalSalaryScale->payrollScales as $scaleV) {
                                                                            if ($childrenV['type'] == 'number') {
                                                                                /* Se calcula el número de registros existentes según sea el caso
                                                                                y se sustituye por su valor en el tabulador */
                                                                                $sclV = json_decode($scaleV['value']);
                                                                                $recordV->loadCount($childrenV['required'][0]);

                                                                                if (isset($sclV->from) && isset($sclV->to)) {
                                                                                    if (
                                                                                        ($recordV[Str::snake($childrenV['required'][0]) . '_count'] >= $sclV->from) &&
                                                                                        ($recordV[Str::snake($childrenV['required'][0]) . '_count'] <= $sclV->to)
                                                                                    ) {
                                                                                        $formula = addTabulatorValuetoFormula(
                                                                                            $payrollSalaryTabulator,
                                                                                            $salaryAdjustments,
                                                                                            $scale,
                                                                                            $scaleV,
                                                                                            $concept,
                                                                                            $match,
                                                                                            $formula
                                                                                        );
                                                                                    }
                                                                                } else {
                                                                                    if ($sclV == $recordV[Str::snake($childrenV['required'][0]) . '_count']) {
                                                                                        $formula = addTabulatorValuetoFormula(
                                                                                            $payrollSalaryTabulator,
                                                                                            $salaryAdjustments,
                                                                                            $scale,
                                                                                            $scaleV,
                                                                                            $concept,
                                                                                            $match,
                                                                                            $formula
                                                                                        );
                                                                                    }
                                                                                }
                                                                            } elseif ($childrenV['type'] == 'date') {
                                                                                /* Se calcula el número de años según la fecha de ingreso
                                                                                y se sustituye por su valor en el tabulador */
                                                                                $sclV = json_decode($scaleV['value']);
                                                                                if (isset($sclV->from) && isset($sclV->to)) {
                                                                                    if (
                                                                                        (age($recordV[$childrenV['required'][0]], $period_end, true) >= $sclV->from) &&
                                                                                        (age($recordV[$childrenV['required'][0]], $period_end, true) <= $sclV->to)
                                                                                    ) {
                                                                                        $formula = addTabulatorValuetoFormula(
                                                                                            $payrollSalaryTabulator,
                                                                                            $salaryAdjustments,
                                                                                            $scale,
                                                                                            $scaleV,
                                                                                            $concept,
                                                                                            $match,
                                                                                            $formula
                                                                                        );
                                                                                    }
                                                                                } else {
                                                                                    if ($sclV == age($recordV[$childrenV['required'][0]], $period_end)) {
                                                                                        $formula = addTabulatorValuetoFormula(
                                                                                            $payrollSalaryTabulator,
                                                                                            $salaryAdjustments,
                                                                                            $scale,
                                                                                            $scaleV,
                                                                                            $concept,
                                                                                            $match,
                                                                                            $formula
                                                                                        );
                                                                                    }
                                                                                }
                                                                            } else {
                                                                                /* Se identifica el valor según el expediente del trabajador
                                                                                y se sustituye por su valor en el tabulador */
                                                                                if (json_decode($scaleV['value']) == $recordV[$childrenV['required'][0]]) {
                                                                                    $formula = addTabulatorValuetoFormula(
                                                                                        $payrollSalaryTabulator,
                                                                                        $salaryAdjustments,
                                                                                        $scale,
                                                                                        $scaleV,
                                                                                        $concept,
                                                                                        $match,
                                                                                        $formula
                                                                                    );
                                                                                }
                                                                            }
                                                                        }
                                                                    } else {
                                                                        $formula = str_replace(
                                                                            $match,
                                                                            0,
                                                                            $formula ?? $concept['formula']
                                                                        );
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        } else {
                                            /* Se identifica el valor según el expediente del trabajador
                                            y se sustituye por su valor en el tabulador */
                                            if (json_decode($scale['value']) == $record[$children['required'][0]]) {
                                                foreach ($payrollParameters->loadData('associatedWorkerFile') as $parameterV) {
                                                    if (! empty($parameterV['children'])) {
                                                        foreach ($parameterV['children'] as $childrenV) {
                                                            if ($childrenV['id'] == $payrollSalaryTabulator->payrollVerticalSalaryScale['group_by']) {
                                                                $recordV = ($parameterV['model'] != PayrollStaff::class)
                                                                    ? $parameterV['model']::query()
                                                                        ->where('payroll_staff_id', $payrollStaff->id)
                                                                        ->first()
                                                                    : $payrollStaff;
                                                                if (isset($recordV)) {
                                                                    foreach ($payrollSalaryTabulator->payrollVerticalSalaryScale->payrollScales as $scaleV) {
                                                                        if ($childrenV['type'] == 'number') {
                                                                            /* Se calcula el número de registros existentes según sea el caso
                                                                            y se sustituye por su valor en el tabulador */
                                                                            $sclV = json_decode($scaleV['value']);
                                                                            $recordV->loadCount($childrenV['required'][0]);

                                                                            if (isset($sclV->from) && isset($sclV->to)) {
                                                                                if (
                                                                                    ($recordV[Str::snake($childrenV['required'][0]) . '_count'] >= $sclV->from) &&
                                                                                    ($recordV[Str::snake($childrenV['required'][0]) . '_count'] <= $sclV->to)
                                                                                ) {
                                                                                    $formula = addTabulatorValuetoFormula(
                                                                                        $payrollSalaryTabulator,
                                                                                        $salaryAdjustments,
                                                                                        $scale,
                                                                                        $scaleV,
                                                                                        $concept,
                                                                                        $match,
                                                                                        $formula
                                                                                    );
                                                                                }
                                                                            } else {
                                                                                if ($sclV == $recordV[Str::snake($childrenV['required'][0]) . '_count']) {
                                                                                    $formula = addTabulatorValuetoFormula(
                                                                                        $payrollSalaryTabulator,
                                                                                        $salaryAdjustments,
                                                                                        $scale,
                                                                                        $scaleV,
                                                                                        $concept,
                                                                                        $match,
                                                                                        $formula
                                                                                    );
                                                                                }
                                                                            }
                                                                        } elseif ($childrenV['type'] == 'date') {
                                                                            /* Se calcula el número de años según la fecha de ingreso
                                                                            y se sustituye por su valor en el tabulador */
                                                                            $sclV = json_decode($scaleV['value']);
                                                                            if (isset($sclV->from) && isset($sclV->to)) {
                                                                                if (
                                                                                    (age($recordV[$childrenV['required'][0]], $period_end, true) >= $sclV->from) &&
                                                                                    (age($recordV[$childrenV['required'][0]], $period_end, true) <= $sclV->to)
                                                                                ) {
                                                                                    $formula = addTabulatorValuetoFormula(
                                                                                        $payrollSalaryTabulator,
                                                                                        $salaryAdjustments,
                                                                                        $scale,
                                                                                        $scaleV,
                                                                                        $concept,
                                                                                        $match,
                                                                                        $formula
                                                                                    );
                                                                                }
                                                                            } else {
                                                                                if ($sclV == age($recordV[$childrenV['required'][0]], $period_end)) {
                                                                                    $formula = addTabulatorValuetoFormula(
                                                                                        $payrollSalaryTabulator,
                                                                                        $salaryAdjustments,
                                                                                        $scale,
                                                                                        $scaleV,
                                                                                        $concept,
                                                                                        $match,
                                                                                        $formula
                                                                                    );
                                                                                }
                                                                            }
                                                                        } else {
                                                                            /* Se identifica el valor según el expediente del trabajador
                                                                            y se sustituye por su valor en el tabulador */
                                                                            if (json_decode($scaleV['value']) == $recordV[$childrenV['required'][0]]) {
                                                                                $formula = addTabulatorValuetoFormula(
                                                                                    $payrollSalaryTabulator,
                                                                                    $salaryAdjustments,
                                                                                    $scale,
                                                                                    $scaleV,
                                                                                    $concept,
                                                                                    $match,
                                                                                    $formula
                                                                                );
                                                                            }
                                                                        }
                                                                    }
                                                                } else {
                                                                    $formula = str_replace(
                                                                        $match,
                                                                        0,
                                                                        $formula ?? $concept['formula']
                                                                    );
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    $formula = str_replace(
                                        $match,
                                        0,
                                        $formula ?? $concept['formula']
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
        /* Si no se encuentra resultado se envian a cero los tabuladores */
        $matchs = [];
        preg_match_all("/tabulator\([0-9]+\)/", $formula, $matchs);
        foreach ($matchs[0] as $match) {
            $formula = str_replace(
                $match,
                0,
                $formula ?? $concept['formula']
            );
        }
        /* Fin de la busqueda */
        $exploded = multiexplode(
            [
                'if', '(', ')', '{', '}',
                '==', '<=', '>=', '<', '>', '!=',
                '+', '-', '*', '/', 'select', 'case',
                'when', 'else', 'end', ';', 'then',
                ',', '.',
            ],
            $formula
        );
        while (count($exploded) > 0) {
            $complete = false;
            $current = max_length($exploded);
            $key = array_search($current, $exploded);
            /* Se descartan los elementos vacios y las constantes númericas */
            if ($current == '' || is_numeric($current)) {
                unset($exploded[$key]);
                $complete = true;
            } else {
                /* Se recorre el listado de parámetros asociados a la configuración de prestaciones sociales
                para sustituirlos por su valor real en la formula del concepto */
                if ($complete == false) {
                    foreach ($payrollParameters->loadData('associatedBenefit') as $parameter) {
                        if ($parameter['id'] == $current) {
                            $record = (is_object($parameter['model']))
                                ? $parameter['model']
                                : $parameter['model']::query()
                                    ->where('institution_id', $institution->id)
                                    ->first();
                            unset($exploded[$key]);
                            $complete = true;
                            if (isset($record)) {
                                if ($parameter['id'] == 'BENEFIT_ADDITIONAL_DAYS_PER_YEAR') {
                                    $employment = $payrollStaff->payrollEmployment;
                                    $year = (age(($employment['start_date'] ?? $period_start), $period_end));
                                    $increment = $record[$parameter['required'][0]] *
                                        ($year - (($record[$parameter['minimum'][0]] ?? 0) - 1) ?? 0);
                                    $formula = str_replace(
                                        $parameter['id'],
                                        ($year < $record['minimum_number_years'])
                                            ? 0
                                            : ((($increment ?? 0) > $record['additional_maximum_days_per_year'])
                                                ? $record['additional_maximum_days_per_year']
                                                : ($increment ?? 0)),
                                        $formula ?? $concept['formula']
                                    );
                                } else {
                                    $formula = str_replace(
                                        $parameter['id'],
                                        $record[$parameter['required'][0]],
                                        $formula ?? $concept['formula']
                                    );
                                }
                            } else {
                                $formula = str_replace(
                                    $parameter['id'],
                                    0,
                                    $formula ?? $concept['formula']
                                );
                            }
                        }
                    }
                }
                /* Se recorre el listado de parámetros asociados a la configuración de vacaciones
                para sustituirlos por su valor real en la formula del concepto */
                if ($complete == false) {
                    foreach ($payrollParameters->loadData('associatedVacation') as $parameter) {
                        if ($parameter['id'] == $current) {
                            $record = (is_object($parameter['model']))
                                ? $parameter['model']
                                : $parameter['model']::query()
                                    ->where('institution_id', $institution->id)
                                    ->first();
                            unset($exploded[$key]);
                            $complete = true;
                            if (isset($record)) {
                                $formula = str_replace(
                                    $parameter['id'],
                                    $record[$parameter['required'][0]],
                                    $formula ?? $concept['formula']
                                );
                            } else {
                                $formula = str_replace(
                                    $parameter['id'],
                                    0,
                                    $formula ?? $concept['formula']
                                );
                            }
                        }
                    }
                }
                /* Se recorre el listado de parámetros asociados al expediente del trabajador
                para sustituirlos por su valor real en la formula del concepto */
                if ($complete == false) {
                    foreach ($payrollParameters->loadData('associatedWorkerFile') as $parameter) {
                        if (! empty($parameter['children'])) {
                            foreach ($parameter['children'] as $children) {
                                if ($children['id'] == $current) {
                                    $record = ($parameter['model'] != PayrollStaff::class)
                                        ? $parameter['model']::query()
                                            ->where('payroll_staff_id', $payrollStaff->id)
                                            ->first()
                                        : $payrollStaff;
                                    unset($exploded[$key]);
                                    $complete = true;
                                    if ($children['type'] == 'number') {
                                        /* Se calcula el número de registros existentes según sea el caso
                                        y se sustituye por su valor real en la fórmula del concepto */
                                        if (isset($record)) {
                                            $number_children = 0;
                                            if ($children['id'] == 'NUMBER_CHILDREN') {
                                                foreach (
                                                    $concept['field']
                                                        ->payrollConceptAssignOptions
                                                        ->where('key', 'all_staff_with_sons') as $assign_option
                                                ) {
                                                    $range = multiexplode(
                                                        ['"', "'", 'minimum', ',', 'maximum', '{', '}', ':'],
                                                        $assign_option['value']
                                                    );
                                                    $values = array_reduce($range, function ($val, $i) {
                                                        if (isset($i) && $i != '') {
                                                            $val[] = $i;
                                                        }

                                                        return $val;
                                                    }, []);
                                                }
                                                $relationshipSon = \Modules\Payroll\Models\PayrollRelationship::where('name', 'Hijo(a)')->first();
                                                if (!isset($relationshipSon)) {
                                                    $relationshipSon["id"] = 3;
                                                }
                                                foreach ($record[$children['required'][0]] as $child) {
                                                    if ($child->payroll_relationships_id === $relationshipSon["id"]) {
                                                        $age = age($child->birthdate, $period_end, true);
                                                        if (isset($values) && count($values) > 0) {
                                                            if ($age >= $values[0] && $age <= $values[1]) {
                                                                $number_children++;
                                                            }
                                                        } else {
                                                            $number_children++;
                                                        }
                                                    }
                                                }
                                                $formula = str_replace(
                                                    $children['id'],
                                                    $number_children,
                                                    $formula ?? $concept['formula']
                                                );
                                            } else {
                                                $record->loadCount($children['required'][0]);
                                                $formula = str_replace(
                                                    $children['id'],
                                                    $number_children > 0
                                                    ? $number_children
                                                    : $record[Str::snake($children['required'][0]) . '_count'],
                                                    $formula ?? $concept['formula']
                                                );
                                            }
                                        } else {
                                            $formula = str_replace(
                                                $children['id'],
                                                0,
                                                $formula ?? $concept['formula']
                                            );
                                        }
                                    } elseif ($children['type'] == 'date') {
                                        /* Se calcula el número de años según la fecha de ingreso
                                        y se sustituye por su valor real en la fórmula del concepto */
                                        if (isset($record)) {
                                            $formula = str_replace(
                                                $children['id'],
                                                age($record[$children['required'][0]], $period_end, true),
                                                $formula ?? $concept['formula']
                                            );
                                        } else {
                                            $formula = str_replace(
                                                $children['id'],
                                                0,
                                                $formula ?? $concept['formula']
                                            );
                                        }
                                    } else {
                                        /* Se identifica el valor según el expediente del trabajador
                                        y se sustituye por su valor real en la fórmula del concepto */
                                        if (isset($record)) {
                                            $formula = str_replace(
                                                $children['id'],
                                                $record[$children['required'][0]],
                                                $formula ?? $concept['formula']
                                            );
                                        } else {
                                            $formula = str_replace(
                                                $children['id'],
                                                0,
                                                $formula ?? $concept['formula']
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                /* Se descartan todos los demas parámetros */
                if ($complete == false) {
                    $coincidences = [];
                    $patterns = [
                        "/concept\([0-9]+\)/",
                        "/parameter\([0-9]+\)/",
                        "/tabulator\([0-9]+\)/",
                    ];

                    foreach ($patterns as $pattern) {
                        preg_match_all($pattern, $formula ?? $concept['formula'], $matches);

                        foreach ($matches[0] as $match) {
                            $coincidences[] = $match;
                        }
                    }
                    if (count($coincidences) > 0) {
                        throw new FailedPayrollConceptException("Error en la fórmula del concepto: " . $concept['field']->name);
                    }
                    $ari = ("ari_register" === $exploded[$key])
                        ? PayrollAriRegister::query()
                            ->where(function ($query) use ($period_end, $payrollStaff) {
                                $query->where('payroll_staff_id', $payrollStaff->id)
                                    ->whereNull('to_date')
                                    ->where('from_date', '<=', $period_end);
                            })
                            ->orWhere(function ($query) use ($period_end, $payrollStaff) {
                                $query->where('payroll_staff_id', $payrollStaff->id)
                                    ->whereNotNull('to_date')
                                    ->where('from_date', '<=', $period_end)
                                    ->where('to_date', '>=', $period_end);
                            })->first()
                        : null;
                    $formula = str_replace($exploded[$key], $ari?->percetage ?? 0, $formula ?? $concept['formula']);
                    unset($exploded[$key]);
                    $complete = true;
                }
            }
        }
        /* Se carga la propiedad payrollConceptType para determinar como clasificar el concepto */
        $concept['field']->load('payrollConceptType');
        // Aqui empiezo a hacer mi cambio
        $currentConceptIndex = $this->getIndex($this->payrollConceptsArray, 'id', $concept['field']->id);
        $conceptWithMark = $this->payrollConceptsArray [$currentConceptIndex];

        if ($concept['field']->payrollConceptType['sign'] == '+' && !$conceptWithMark['marked']) {
            $this->totalAsignations += str_eval($formula);
            $this->totalAsignationsByPayrollStaff += str_eval($formula);
        } elseif ($concept['field']->payrollConceptType['sign'] == '-' && !$conceptWithMark['marked']) {
            $this->totalDeductions += str_eval($formula);
            $this->totalDeductionsByPayrollStaff += str_eval($formula);
        } elseif ($concept['field']->payrollConceptType['sign'] == '+' && $conceptWithMark['marked']) {
            $formula = $concept['field']->translateFormula;
            $formula = str_replace(
                'TOTAL_ASSIGNMENTS',
                $this->totalAsignationsByPayrollStaff,
                $formula ?? $concept['formula']
            );
            $formula = str_replace(
                'TOTAL_PAID',
                $this->totalAsignationsByPayrollStaff - $this->totalDeductionsByPayrollStaff,
                $formula ?? $concept['formula']
            );
            $this->totalAsignations += str_eval($formula ?? $concept['formula']);
            $this->totalAsignationsByPayrollStaff += str_eval($formula ?? $concept['formula']);
        } elseif ($concept['field']->payrollConceptType['sign'] == '-' && $conceptWithMark['marked']) {
            $totalPaidValueByPayrollStaff = $this->totalAsignationsByPayrollStaff - $this->totalDeductionsByPayrollStaff;
            $formula = $concept['field']->translateFormula;
            $formula = str_replace(
                'TOTAL_ASSIGNMENTS',
                $this->totalAsignationsByPayrollStaff,
                $formula ?? $concept['formula']
            );
            $formula = str_replace(
                'TOTAL_PAID',
                $totalPaidValueByPayrollStaff,
                $formula ?? $concept['formula']
            );
            $this->totalDeductions += str_eval($formula ?? $concept['formula']);
            $this->totalDeductionsByPayrollStaff += str_eval($formula ?? $concept['formula']);
        }
        $formula = expression_format($formula ?? $concept['formula']);
        array_push($types[$concept['field']->payrollConceptType->name], [
            'id' => $concept['field']->id ?? '',
            'name' => $concept['field']->name,
            'value' => str_eval($formula),
            'time_sheet' => $concept['time_sheet'] ?? '',
            'sign' => $concept['field']->payrollConceptType['sign'],
            'accouting_account_id' => $concept['field']->accounting_account_id ?? '',
            'budget_account_id' => $concept['field']->budget_account_id ?? '',
            'budget_account_code' => $concept['field']->budgetAccount->code ?? '',
            'budget_account_denomination' => $concept['field']->budgetAccount->denomination ?? '',
            'accounting_account_code' => $concept['field']->accountingAccount->code ?? '',
            'accounting_account_denomination' => $concept['field']->accountingAccount->denomination ?? '',
            'formula' => $concept['field']->translate_formula ?? '',
        ]);

        return $types;
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
