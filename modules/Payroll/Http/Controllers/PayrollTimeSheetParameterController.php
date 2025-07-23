<?php

namespace Modules\Payroll\Http\Controllers;

use App\Models\Parameter;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\DB;
use Modules\Payroll\Models\PayrollClassificationParameter;
use Modules\Payroll\Models\PayrollExceptionType;
use Modules\Payroll\Models\PayrollParameterTimeSheetParameter;
use Modules\Payroll\Models\PayrollPaymentTypeTimeSheetParameter;
use Modules\Payroll\Models\PayrollExceptionTypeTimeSheetParameter;
use Modules\Payroll\Models\PayrollClassificationParameterTimeSheetOrder;
use Modules\Payroll\Models\PayrollTimeSheet;
use Modules\Payroll\Models\PayrollTimeSheetParameter;
use Modules\Payroll\Models\PayrollTimeSheetPending;

/**
 * @class PayrollTimeSheetParameterController
 * @brief Gestiona los procesos del controlador
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollTimeSheetParameterController extends Controller
{
    use ValidatesRequests;

    /**
     * Reglas de validación
     *
     * @var array $validateRules
     */
    protected $validateRules;

    /**
     * Mensajes de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:payroll.timesheetparameter.index', ['only' => 'index']);
        $this->middleware('permission:payroll.timesheetparameter.create', ['only' => ['store']]);
        $this->middleware('permission:payroll.timesheetparameter.edit', ['only' => ['update']]);
        $this->middleware('permission:payroll.timesheetparameter.delete', ['only' => 'destroy']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'code' => ['required', 'unique:payroll_time_sheet_parameters,code'],
            'name' => ['required'],
            'time_parameters' => ['required'],
            'time_parameters.*.id' => ['exists:parameters,id'],
            'payment_types' => ['required'],
            'payment_types.*.id' => ['exists:payroll_payment_types,id'],
            'breaks_allowed_per_week' => ['integer', 'min:0'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'code.required'        => 'El campo código es obligatorio.',
            'code.unique'          => 'El campo código ya ha sido registrado.',
            'name.required' => 'El campo nombre es obligatorio.',
            'time_parameters.required' => 'El campo parámetros es obligatorio.',
            'time_parameters.*.id.exists' => 'El campo parámetros no existe.',
            'payment_types.required' => 'El campo tipos de nómina es obligatorio.',
            'payment_types.*.id.exists' => 'El campo tipos de nómina no existe.',
            'breaks_allowed_per_week.integer' => 'El campo descansos permitidos por semana debe ser un número entero.',
        ];
    }

    /**
     * Muestra todos los registros de parámetros de tiempo
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $records = PayrollTimeSheetParameter::query()
            ->whereHas('payrollParameterTimeSheetParameters.parameter')
            ->whereHas('payrollPaymentTypeTimeSheetParameters.payrollPaymentType')
            ->with([
                'payrollParameterTimeSheetParameters.parameter',
                'payrollPaymentTypeTimeSheetParameters.payrollPaymentType',
                'payrollExceptionTypeTimeSheetParameters.payrollExceptionType',
                'classificationParameters.payrollExceptionType',
                'classificationParameterPivots' => function ($query) {
                    $query->with([
                        'parameterOrder' => function ($query) {
                            $query->orderBy('order')->with('parameter');
                        },
                    ]);
                }
            ])
            ->get();

        // Verificación de datos cargados
        foreach ($records as $parameter) {
            foreach ($parameter->classificationParameterPivots as $pivot) {
                // Carga manual si la relación eager loading falla
                if ($pivot->parameterOrder->isEmpty()) {
                    $manualOrders = PayrollClassificationParameterTimeSheetOrder::where(
                        'payroll_classification_parameter_payroll_time_sheet_parameter_id',
                        $pivot->id
                    )->orderBy('order')->with('parameter')->get();

                    $pivot->setRelation('parameterOrder', $manualOrders);
                }
            }
        }

        return response()->json(['records' => $records], 200);
    }
    /**
     * Muestra el formulario para crear un nuevo registro de parámetro de tiempo
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::create');
    }

    /**
     * Valida y registra un nuevo parámetro de tiempo
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        $payrollTimeSheetParameter = DB::transaction(function () use ($request) {
            $payrollTimeSheetParameter = PayrollTimeSheetParameter::create([
                'code' => $request->code,
                'name' => $request->name,
                'description' => $request->description,
                'validate_total_for_period' => $request->validate_total_for_period,
                'breaks_allowed_per_week' => $request->breaks_allowed_per_week,
            ]);

            foreach ($request->time_parameters as $parameter) {
                PayrollParameterTimeSheetParameter::create([
                    'payroll_time_sheet_parameter_id' => $payrollTimeSheetParameter->id,
                    'parameter_id' => $parameter['id']
                ]);
            }

            foreach ($request->payment_types as $type) {
                PayrollPaymentTypeTimeSheetParameter::create([
                    'payroll_time_sheet_parameter_id' => $payrollTimeSheetParameter->id,
                    'payroll_payment_type_id' => $type['id']
                ]);
            }

            foreach ($request->exception_types as $type) {
                PayrollExceptionTypeTimeSheetParameter::create([
                    'payroll_time_sheet_parameter_id' => $payrollTimeSheetParameter->id,
                    'payroll_exception_type_id' => $type['id']
                ]);
            }

            // 1. Primero sincronizamos los classificationParameters
            $syncData = [];
            foreach ($request->evaluation_orders as $index => $classificationId) {
                $syncData[$classificationId] = [
                    'created_at' => now(),
                    'updated_at' => now(),
                    'order' => $index + 1,
                ];
            }

            // Usamos syncWithoutDetaching para mantener los IDs existentes
            $payrollTimeSheetParameter->classificationParameters()->sync($syncData);

            // 2. Luego obtenemos los IDs de los registros pivot recién creados
            $pivotIds = DB::table('payroll_classification_parameter_payroll_time_sheet_parameter')
                ->where('payroll_time_sheet_parameter_id', $payrollTimeSheetParameter->id)
                ->pluck('id', 'payroll_classification_parameter_id')
                ->toArray();

            $indexSuperOrder = 1;
            // 3. Ahora procesamos los orders para cada classification parameter
            foreach ($request->evaluation_orders as $index => $classificationId) {
                if (!isset($pivotIds[$classificationId]) || !isset($request->evaluation_orders_parameters[$index])) {
                    continue;
                }

                $pivotId = $pivotIds[$classificationId];
                $parameterIds = $request->evaluation_orders_parameters[$index];

                // Preparamos los datos para insertar
                $orderData = [];
                foreach ($parameterIds as $parameterId) {
                    $orderData[] = [
                        'payroll_classification_parameter_payroll_time_sheet_parameter_id' => $pivotId,
                        'parameter_id' => $parameterId,
                        'order' => $indexSuperOrder++,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // Eliminamos primero los registros existentes para evitar duplicados
                PayrollClassificationParameterTimeSheetOrder::where(
                    'payroll_classification_parameter_payroll_time_sheet_parameter_id',
                    $pivotId
                )->delete();

                // Insertamos los nuevos registros
                if (!empty($orderData)) {
                    PayrollClassificationParameterTimeSheetOrder::insert($orderData);
                }
            }

            return $payrollTimeSheetParameter;
        });

        return response()->json(['record' => $payrollTimeSheetParameter, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de un parámetro de tiempo
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function show($id)
    {
        return view('payroll::show');
    }

    /**
     * Muestra el formulario para editar un parámetro de tiempo
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function edit($id)
    {
        return view('payroll::edit');
    }

    /**
     * Actualiza los datos de un parámetro de tiempo
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $payrollTimeSheetParameter = PayrollTimeSheetParameter::find($id);
        $this->validateRules['code'] = [
            'required',
            'unique:payroll_time_sheet_parameters,code,' . $payrollTimeSheetParameter->id
        ];
        $this->validate($request, $this->validateRules, $this->messages);

        DB::transaction(function () use ($request, $payrollTimeSheetParameter) {
            // Actualizar los datos básicos
            $payrollTimeSheetParameter->update([
                'code' => $request->code,
                'name' => $request->name,
                'description' => $request->description,
                'validate_total_for_period' => $request->validate_total_for_period,
                'breaks_allowed_per_week' => $request->breaks_allowed_per_week,
            ]);

            // Eliminar y recrear los parámetros de tiempo
            PayrollParameterTimeSheetParameter::where('payroll_time_sheet_parameter_id', $payrollTimeSheetParameter->id)->delete();
            foreach ($request->time_parameters as $parameter) {
                PayrollParameterTimeSheetParameter::create([
                    'payroll_time_sheet_parameter_id' => $payrollTimeSheetParameter->id,
                    'parameter_id' => $parameter['id']
                ]);
            }

            // Eliminar y recrear los tipos de pago
            PayrollPaymentTypeTimeSheetParameter::where('payroll_time_sheet_parameter_id', $payrollTimeSheetParameter->id)->delete();
            foreach ($request->payment_types as $type) {
                PayrollPaymentTypeTimeSheetParameter::create([
                    'payroll_time_sheet_parameter_id' => $payrollTimeSheetParameter->id,
                    'payroll_payment_type_id' => $type['id']
                ]);
            }

            // Eliminar y recrear los tipos de excepción
            PayrollExceptionTypeTimeSheetParameter::where('payroll_time_sheet_parameter_id', $payrollTimeSheetParameter->id)->delete();
            foreach ($request->exception_types as $type) {
                PayrollExceptionTypeTimeSheetParameter::create([
                    'payroll_time_sheet_parameter_id' => $payrollTimeSheetParameter->id,
                    'payroll_exception_type_id' => $type['id']
                ]);
            }

            // Sincronizar los parámetros de clasificación
            $syncData = [];
            foreach ($request->evaluation_orders as $index => $classificationId) {
                $syncData[$classificationId] = [
                    'created_at' => now(),
                    'updated_at' => now(),
                    'order' => $index + 1,
                ];
            }
            $payrollTimeSheetParameter->classificationParameters()->sync($syncData);

            // Obtener los IDs de los registros pivot
            $pivotIds = DB::table('payroll_classification_parameter_payroll_time_sheet_parameter')
                ->where('payroll_time_sheet_parameter_id', $payrollTimeSheetParameter->id)
                ->pluck('id', 'payroll_classification_parameter_id')
                ->toArray();

            // Procesar los orders para cada classification parameter
            $indexSuperOrder = 1;
            foreach ($request->evaluation_orders as $index => $classificationId) {
                if (!isset($pivotIds[$classificationId]) || !isset($request->evaluation_orders_parameters[$index])) {
                    continue;
                }

                $pivotId = $pivotIds[$classificationId];
                $parameterIds = $request->evaluation_orders_parameters[$index];

                // Preparar datos para insertar
                $orderData = [];
                foreach ($parameterIds as $parameterId) {
                    $orderData[] = [
                        'payroll_classification_parameter_payroll_time_sheet_parameter_id' => $pivotId,
                        'parameter_id' => $parameterId,
                        'order' => $indexSuperOrder++,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // Eliminar registros existentes y crear nuevos
                PayrollClassificationParameterTimeSheetOrder::where(
                    'payroll_classification_parameter_payroll_time_sheet_parameter_id',
                    $pivotId
                )->delete();

                if (!empty($orderData)) {
                    PayrollClassificationParameterTimeSheetOrder::insert($orderData);
                }
            }
        });

        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina un parámetro de tiempo
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $timeSheet = PayrollTimeSheet::where('payroll_time_sheet_parameter_id', $id)->first();
        $timeSheetPending = PayrollTimeSheetPending::where('payroll_time_sheet_parameter_id', $id)->first();

        if ($timeSheet || $timeSheetPending) {
            return response()->json([
                'error' => true,
                'message' => __('No se puede eliminar los parámetros de hoja de tiempo debido a que tiene una hoja de tiempo asociada')
            ], 200);
        }

        $payrollTimeSheetParameter = PayrollTimeSheetParameter::find($id);

        DB::transaction(function () use ($payrollTimeSheetParameter) {
            // Eliminar parámetros de tiempo asociados
            PayrollParameterTimeSheetParameter::where('payroll_time_sheet_parameter_id', $payrollTimeSheetParameter->id)->delete();

            // Eliminar tipos de pago asociados
            PayrollPaymentTypeTimeSheetParameter::where('payroll_time_sheet_parameter_id', $payrollTimeSheetParameter->id)->delete();

            // Eliminar tipos de excepción asociados
            PayrollExceptionTypeTimeSheetParameter::where('payroll_time_sheet_parameter_id', $payrollTimeSheetParameter->id)->delete();

            // Eliminar órdenes de parámetros de clasificación
            $pivotIds = DB::table('payroll_classification_parameter_payroll_time_sheet_parameter')
                ->where('payroll_time_sheet_parameter_id', $payrollTimeSheetParameter->id)
                ->pluck('id');

            PayrollClassificationParameterTimeSheetOrder::whereIn(
                'payroll_classification_parameter_payroll_time_sheet_parameter_id',
                $pivotIds
            )->delete();

            // Eliminar registros pivot
            DB::table('payroll_classification_parameter_payroll_time_sheet_parameter')
                ->where('payroll_time_sheet_parameter_id', $payrollTimeSheetParameter->id)
                ->delete();

            // Eliminar la relación many-to-many
            $payrollTimeSheetParameter->classificationParameters()->detach();

            // Finalmente eliminar el parámetro principal
            $payrollTimeSheetParameter->delete();
        });

        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Obtiene los parámetros de la hoja de tiempo
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPayrollTimeSheetParameters(Request $request)
    {
        $parameters = PayrollTimeSheetParameter::query()
            ->with([
                'payrollParameterTimeSheetParameters.parameter',
                'payrollExceptionTypeTimeSheetParameters.payrollExceptionType',
                'classificationParameterPivots' => function ($query) {
                    $query->with([
                        'parameterOrder' => function ($query) {
                            $query->orderBy('order')->with('parameter');
                        },
                    ]);
                }
            ])
            ->get();

        foreach ($parameters as $parameter) {
            foreach ($parameter->classificationParameterPivots as $pivot) {
                // Carga manual si la relación eager loading falla
                if ($pivot->parameterOrder->isEmpty()) {
                    $manualOrders = PayrollClassificationParameterTimeSheetOrder::where(
                        'payroll_classification_parameter_payroll_time_sheet_parameter_id',
                        $pivot->id
                    )->orderBy('order')->with('parameter')->get();

                    $pivot->setRelation('parameterOrder', $manualOrders);
                }
            }
        }

        $records = [];

        foreach ($parameters as $key => $parameter) {
            // Obtener los nombres de exception types solo para este parámetro
            $exceptionTypeNames = $parameter->payrollExceptionTypeTimeSheetParameters
                ->map(function ($item) {
                    return $item->payrollExceptionType?->name;
                })
                ->filter()->unique()->values()->all();

            $records[$key] = [
                'id' => $parameter->id,
                'text' => $parameter->code,
                'total_for_period' => $parameter->validate_total_for_period,
                'breaks_allowed_per_week' => $parameter->breaks_allowed_per_week,
                'total_groups' => $exceptionTypeNames,
                'parameters' => []
            ];

            $hasHolidays = [
                'domingos' => false,
                'feriados' => false,
                'descansos' => false
            ];

            foreach ($parameter->payrollParameterTimeSheetParameters as $param) {
                $pValue = json_decode($param->parameter->p_value, true);
                $paramName = array_key_exists('classification_type', $pValue)
                    ? PayrollClassificationParameter::query()
                        ->find((int)$pValue["classification_type"])
                        ?->name
                    : '';

                if ($paramName == 'Domingo') {
                    $hasHolidays['domingos'] = true;
                } elseif ($paramName == 'Descanso') {
                    $hasHolidays['descansos'] = true;
                } elseif ($paramName == 'Feriado') {
                    $hasHolidays['feriados'] = true;
                }
            }

            foreach ($parameter->payrollParameterTimeSheetParameters as $param) {
                $pValue = json_decode($param->parameter->p_value, true);

                $exceptionType = PayrollExceptionType::find($pValue["exception_type"]);
                $records[$key]['parameters'][$exceptionType->name][] = [
                    'id' => $param->parameter->id,
                    'group' => $exceptionType->name,
                    'max' => $exceptionType->value_max ?? null,
                    'max_value_allowed_per_time_sheet' => isset(
                        $pValue["max_value_allowed_per_time_sheet"]
                    ) ? $pValue["max_value_allowed_per_time_sheet"] : null,
                    'affectGroup' => $exceptionType->affect?->name,
                    'text' => $pValue["acronym"] . ' - ' . $pValue["name"],
                    'formula' => $this->translateFormula(
                        $pValue["formula"],
                        $parameter->validate_total_for_period,
                        $parameter->breaks_allowed_per_week,
                        $request->from_date ?? '',
                        $request->to_date ?? '',
                        $hasHolidays
                    ),
                    'order' => array_key_exists('classification_type', $pValue)
                        ? $parameter
                            ->classificationParameterPivots
                            ->where('payroll_classification_parameter_id', $pValue["classification_type"])
                            ->first()
                            ?->parameterOrder
                            ->where('parameter_id', $param->parameter->id)
                            ->first()
                            ?->order
                        : '',
                    'classification_type' => array_key_exists('classification_type', $pValue)
                        ? PayrollClassificationParameter::query()
                            ->find((int)$pValue["classification_type"])
                            ?->name
                        : '',
                ];
            }
        }

        $data = array_merge(
            [
                [
                    'id' => '',
                    'text' => 'Seleccione...',
                ]
            ],
            $records
        );

        return response()->json(
            $data,
            200
        );
    }

    public function translateFormula(string $formula, bool $validateForPeriod, int $breaksPerWeek, string $fromDate, string $toDate, array $hasHolidays): string
    {
        // Buscar todas las ocurrencias de parameter(x) en la fórmula
        preg_match_all('/parameter\((\d+)\)/', $formula, $matches);

        // $matches[1] contendrá todos los IDs dentro de los paréntesis
        $parameterIds = $matches[1];
        sort($parameterIds);

        if (!empty($parameterIds)) {
            // Reemplazar cada parameter(x) en la fórmula con su valor correspondiente
            foreach ($parameterIds as $id) {
                $parameter = Parameter::query()
                    ->where('p_key', 'global_parameter_' . $id)
                    ->first();

                $paramValue = json_decode($parameter->p_value);

                if (property_exists($paramValue, 'is_excedent') && $paramValue->is_excedent == true) {
                    $formulaExc = $paramValue->formula;

                    // Se establece el máximo de acuerdo a la clasificación del parámetro
                    if ($validateForPeriod && !empty($fromDate) && !empty($toDate)) {
                        $classification = PayrollClassificationParameter::find((int)$paramValue->classification_type);
                        $classificationName = $classification ? strtolower($classification->name) : '';

                        $request = new Request([
                            'from_date' => $fromDate,
                            'to_date' => $toDate,
                        ]);

                        $getMaxDays = new \Modules\Payroll\Http\Controllers\PayrollTimeSheetController();
                        $getMaxDays = $getMaxDays->getTimeSheetHolidaysByPeriod($request)->getData();

                        $formulaExc = explode('-', $paramValue->formula);

                        $getMaxSunday = $getMaxDays->maxHolidays->domingos;
                        $getMaxHolidays = $getMaxDays->maxHolidays->feriados;

                        if (preg_match('/\bdomingo(s)?\b/', $classificationName) && $getMaxSunday > 0) {
                            $formulaExc = $formulaExc[0] . '-' .  $formulaExc[0] . 'MAX(' . $getMaxSunday . ')';
                        } elseif (preg_match('/\bferiado(s)?\b/', $classificationName) && $getMaxHolidays > 0) {
                            $formulaExc = $formulaExc[0] . '-' . $formulaExc[0] . 'MAX(' . $getMaxHolidays . ')';
                        } elseif (preg_match('/\bdescanso(s)?\b/', $classificationName) && $breaksPerWeek > 0) {
                            // Definir cuantas semanas hay en el periodo y multiplicarlo por $breaksPerWeek
                            $startDate = Carbon::parse($fromDate);
                            $endDate = Carbon::parse($toDate);

                            // Calcular la diferencia en semanas (redondeando hacia arriba)
                            $weeksInPeriod = $startDate->diffInWeeks($endDate) + 1; // +1 para incluir la semana actual

                            // Multiplicar por los descansos permitidos por semana
                            $totalBreaks = $weeksInPeriod * $breaksPerWeek;

                            // Usar $totalBreaks en tu lógica
                            $formulaExc = $formulaExc[0] . '-' . $formulaExc[0] . 'MAX(' . $totalBreaks . ')';
                        } elseif (preg_match('/\bturno(s)? sencillo(s)?\b/', $classificationName)) {
                            // Definir cuantos dias hay en el periodo
                            $startDate = Carbon::parse($fromDate);
                            $endDate = Carbon::parse($toDate);

                            // Calcular la diferencia en semanas (redondeando hacia arriba)
                            $weeksInPeriod = $startDate->diffInWeeks($endDate) + 1; // +1 para incluir la semana actual
                            // Multiplicar por los descansos permitidos por semana
                            $totalBreaks = $weeksInPeriod * $breaksPerWeek;

                            // Calcular la diferencia en dias (redondeando hacia arriba)
                            $totalDays = $startDate->diffInDays($endDate);

                            if ($hasHolidays['domingos'] == true) {
                                $totalDays = $totalDays - ($getMaxSunday ?? 0);
                            }

                            if ($hasHolidays['feriados'] == true) {
                                $totalDays = $totalDays - ($getMaxHolidays ?? 0);
                            }

                            if ($hasHolidays['descansos'] == true) {
                                $totalDays = $totalDays - ($totalBreaks ?? 0);
                            }

                            $totalDays = $totalDays + 1;

                            $formulaExc = $formulaExc[0] . '-' . $formulaExc[0] . 'MAX(' . $totalDays . ')';
                        } else {
                            $formulaExc = $formulaExc[0] . '-' . $formulaExc[0] . 'MAX(' . $formulaExc[1] . ')';
                        }
                    }

                    $excedentFormula = $this->translateFormula($formulaExc, false, 0, '', '', $hasHolidays);
                    $excedentFormula = "($excedentFormula)";
                }

                $formula = str_replace("parameter($id)", $excedentFormula ?? $paramValue?->name, $formula);
            }
        }

        return $formula;
    }
}
