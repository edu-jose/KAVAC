<?php

declare(strict_types=1);

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Payroll\Models\PayrollPosition;
use Modules\Payroll\Models\PayrollWorkload;
use Modules\Payroll\Models\PayrollWorkloadPosition;

/**
 * @class PayrollWorkloadController
 * @brief Controlador de cargas horarias
 *
 * Clase que gestiona las cargas horarias
 *
 * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollWorkloadController extends Controller
{
    use ValidatesRequests;

    /**
     * Arreglo con las reglas de validación sobre los datos de un formulario
     *
     * @var array $validateRules
     */
    protected $validateRules;

    /**
     * Arreglo con los mensajes para las reglas de validación
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
        /* Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:payroll.workload.index', ['only' => ['index']]);
        $this->middleware('permission:payroll.workload.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.workload.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.workload.delete', ['only' => 'destroy']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'hours'       => ['required', 'integer', 'unique:payroll_workloads,hours'],
            'payroll_workload_positions' => ['required'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'hours.required' => 'El campo carga horaria diaria (horas/turno) es obligatorio.',
            'hours.integer' => 'El campo carga horaria diaria (horas/turno) debe ser un número entero.',
            'hours.unique' => 'La carga horaria ya se encuentra registrada en el sistema.',
            'payroll_workload_positions.required' => 'El campo cargos es obligatorio.',
        ];
    }

    /**
     * Muestra un listado de las cargas horarias registradas
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\JsonResponse    Json con los datos de las cargas horarias
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'records' => PayrollWorkload::with(['payrollWorkloadPositions' => function ($query) {
                $query->join('payroll_positions', 'payroll_workload_positions.payroll_position_id', '=', 'payroll_positions.id')
                    ->orderBy('payroll_positions.name', 'asc')
                    ->select('payroll_workload_positions.*'); // Selecciona solo las columnas de payroll_workload_positions para evitar conflictos
            }])->get()
        ], 200);
    }

    /**
     * [descripción del método]
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function create()
    {
        return view('payroll::create');
    }

    /**
     * Guarda una nueva carga horaria
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->validate($request, $this->validateRules, $this->messages);

        $payrollWorkload = PayrollWorkload::create([
            'hours' => $request->hours,
            'description' => $request->description,
        ]);

        foreach ($request->payroll_workload_positions as $payrollPosition) {
            PayrollWorkloadPosition::create([
                'payroll_workload_id' => $payrollWorkload->id,
                'payroll_position_id' => $payrollPosition['id'],
            ]);
        }

        return response()->json(['record' => $payrollWorkload, 'message' => 'Success'], 200);
    }

    /**
     * [descripción del método]
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function show($id)
    {
        return view('payroll::show');
    }

    /**
     * [descripción del método]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function edit($id)
    {
        return view('payroll::edit');
    }

    /**
     * Actualiza una carga horaria previamente registrada
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $payrollWorkload = PayrollWorkload::find($id);
        $validateRules  = array_replace(
            $this->validateRules,
            [
                'hours' => ['required', 'integer', 'unique:payroll_workloads,hours,' . $payrollWorkload->id],
                'payroll_workload_positions' => ['required'],
            ]
        );

        $this->validate($request, $validateRules, $this->messages);

        $payrollWorkload->hours = $request->hours;
        $payrollWorkload->description = $request->description;
        $payrollWorkload->save();

        PayrollWorkloadPosition::where('payroll_workload_id', $id)->delete();

        foreach ($request->payroll_workload_positions as $payrollPosition) {
            $workloadPosition = PayrollWorkloadPosition::where([
                'payroll_workload_id' => $payrollWorkload->id,
                'payroll_position_id' => $payrollPosition['id'],
            ])->withTrashed()->first();

            if ($workloadPosition) {
                // Si ya existe, restaurarlo en lugar de crear uno nuevo
                $workloadPosition->restore();
                continue;
            }

            PayrollWorkloadPosition::create([
                'payroll_workload_id' => $payrollWorkload->id,
                'payroll_position_id' => $payrollPosition['id'],
            ]);
        }

        return response()->json(['record' => $payrollWorkload, 'message' => 'Success'], 200);
    }

    /**
     * Elimina una carga horaria previamente registrada
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        $payrollWorkload = PayrollWorkload::find($id);

        if ($payrollWorkload) {
            PayrollWorkloadPosition::where('payroll_workload_id', $id)->delete();
            $payrollWorkload->delete();
        }

        return response()->json(['record' => $payrollWorkload, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene las cargas horarias registradas
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return JsonResponse     JSON con información de respuesta a la petición
     */
    public function getPayrollWorkloads(): \Illuminate\Http\JsonResponse
    {
        return response()->json(
            template_choices(PayrollWorkload::class, ['hours'], [], true)
        );
    }

    /**
     * Obtiene las cargas horarias registradas de acuerdo al cargo seleccionado
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return JsonResponse     JSON con información de respuesta a la petición
     */
    public function getPayrollWorkloadByPosition(Request $request): \Illuminate\Http\JsonResponse
    {
        $payrollWorkload = PayrollWorkload::query()
            ->whereHas('payrollWorkloadPositions', function ($query) use ($request) {
                $query->where('payroll_position_id', $request->id);
            })
            ->first()
            ?->hours;

        return response()->json(['result' => $payrollWorkload, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los cargos que no han sido registrados en una carga horaria
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return array     Array con información de respuesta a la petición
     */
    public function getPayrollPositionsById(Request $request): array
    {
        $list = [['id' => '', 'text' => 'Seleccione...']];
        $payrollWorkloadPositions = PayrollWorkloadPosition::all()->pluck('payroll_position_id')->toArray();
        $payrollPositions = PayrollPosition::whereNotIn('id', $payrollWorkloadPositions)->get();

        if ($request->ids) {
            $savePositions = PayrollPosition::whereIn('id', $request->ids)->get();

            foreach ($savePositions as $position) {
                $payrollPositions->push($position);
            }
        }

        foreach ($payrollPositions as $payrollPosition) {
            $list[] = ['id' => $payrollPosition->id, 'text' => $payrollPosition->name];
        }

        return $list;
    }
}
