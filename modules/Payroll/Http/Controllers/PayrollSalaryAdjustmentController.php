<?php

namespace Modules\Payroll\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Contracts\Support\Renderable;
use Modules\Payroll\Imports\SalaryAdjustmentImport;
use Modules\Payroll\Models\PayrollSalaryAdjustment;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Rules\PayrollCheckSalaryAdjustments;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Modules\Payroll\Exports\PayrollSalaryAdjustmentExport;
use Modules\Payroll\Exports\PayrollSalaryAdjustmentTabulatorExport;

/**
 * @class PayrollSalaryAdjustmentController
 * @brief Controlador de ajustes en tablas salariales
 *
 * Clase que gestiona los ajustes en tablas salariales
 *
 * @author  Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSalaryAdjustmentController extends Controller
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
     * Arreglo con los nombres de los atributos para las reglas de validación
     *
     * @var array $attributes
     */
    protected $attributes;

    /**
     * Define la configuración de la clase
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:payroll.salary.adjustments.list', ['only' => 'index']);
        $this->middleware('permission:payroll.salary.adjustments.create', ['only' => 'store']);
        $this->middleware('permission:payroll.salary.adjustments.edit', ['only' => 'update']);
        $this->middleware('permission:payroll.salary.adjustments.delete', ['only' => 'destroy']);
        $this->middleware('permission:payroll.salary.adjustments.import', ['only' => 'import']);
        $this->middleware('permission:payroll.salary.adjustments.export', ['only' => 'export']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'created_at'                  => ['required', 'date'],
            'start_increase_date'            => ['required', 'date', new PayrollCheckSalaryAdjustments()],
            'end_increase_date'           =>
            [
                'nullable',
                'date',
                'after_or_equal:start_increase_date',
                new PayrollCheckSalaryAdjustments()
            ],
            'payroll_salary_tabulator_id' => ['required'],
            'increase_of_type'            => ['required'],
            'value'                       => ['nullable', 'numeric']
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'created_at.required'                  => 'El campo fecha de generación es obligatorio.',
            'start_increase_date.required'         => 'El campo fecha del aumento es obligatorio.',
            'end_increase_date.after_or_equal'     => 'El campo fecha de culminación debe ser igual o después de la fecha de aumento',
            'payroll_salary_tabulator_id.required' => 'El campo tabulador salarial es obligatorio.',
            'increase_of_type.required'            => 'El campo tipo de aumento es obligatorio.',
            'value.numeric'                        => 'El campo valor tiene que ser numérico',
        ];

        $this->attributes = [
            'start_increase_date' => 'Fecha de aumento',
            'end_increase_date' => 'Fecha de culminacion'
        ];
    }

    /**
     * Muestra todos los registros de ajustes en tablas salariales
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\View\View    Muestra los datos organizados en una tabla
     */
    public function index()
    {
        return view('payroll::salary_adjustments.index');
    }

    /**
     * Muestra el formulario para registrar un nuevo ajuste en las tablas salariales
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::salary_adjustments.create');
    }

    /**
     * Muestra el formulario de actualización de ajuste en las tablas salariales
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param integer $id Identificador del registro
     *
     * @return \Illuminate\View\View    Vista con el formulario y el objeto a actualizar
     */
    public function edit($id)
    {
        $payrollSalaryAdjustment = PayrollSalaryAdjustment::with(
            [
            'payrollSalaryTabulator'
            ]
        )->find($id);
        return view('payroll::salary_adjustments.create', compact('payrollSalaryAdjustment'));
    }

    /**
     * Valida y registra una nueva nómina de sueldos
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages, $this->attributes);

        $value = ($request->increase_of_type == 'different') ? 0.00 : $request->value;
        $scale_values = ($request->increase_of_type == 'different') ? json_encode($request->scale_values) : null;

        /* Objeto con información del ajuste salarial registrado */
        $payrollSalaryAdjustment = PayrollSalaryAdjustment::create(
            [
            'increase_of_type'                   => $request->input('increase_of_type'),
            'value'                              => $value,
            'payroll_salary_tabulator_id'        => $request->input('payroll_salary_tabulator_id'),
            'start_increase_date'               => $request->input('start_increase_date'),
            'end_increase_date'              => $request->input('end_increase_date'),
            'salary_values'                  => $scale_values,
            ]
        );

        $request->session()->flash('message', ['type' => 'store']);
        return response()->json(['redirect' => route('payroll.salary-adjustments.index')], 200);
    }

    /**
     * Valida y actualiza un ajuste en tablas salariales
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        if ($request->end_increase_date == 'Invalid date') {
            $request->merge(['end_increase_date' => null]);
        }
        $this->validate($request, $this->validateRules, $this->messages, $this->attributes);

        $value = ($request->increase_of_type == 'different') ? 0.00 : $request->value;
        $scale_values = ($request->increase_of_type == 'different') ? json_encode($request->scale_values) : null;

        /* Objeto asociado al modelo PayrollSalaryAdjustment */
        $payrollSalaryAdjustment = PayrollSalaryAdjustment::find($id);
        $payrollSalaryAdjustment->increase_of_type            = $request->increase_of_type;
        $payrollSalaryAdjustment->value                       = $value;
        $payrollSalaryAdjustment->payroll_salary_tabulator_id = $request->payroll_salary_tabulator_id;
        $payrollSalaryAdjustment->start_increase_date         = $request->start_increase_date;
        $payrollSalaryAdjustment->end_increase_date           = $request->end_increase_date;
        $payrollSalaryAdjustment->salary_values               = $scale_values;
        $payrollSalaryAdjustment->save();

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['redirect' => route('payroll.salary-adjustments.index')], 200);
    }

    /**
     * Elimina un registro
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param integer $id Identificador del registro
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $salaryAdjustment = PayrollSalaryAdjustment::find($id);
        $salaryAdjustment->delete();

        return response()->json(['message' => 'destroy'], 200);
    }

    /**
     * Muestra los ajustes en tabuladores salariales registrados
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueList()
    {
        return response()->json(
            ['records' => PayrollSalaryAdjustment::with(
                [
                'payrollSalaryTabulator'
                ]
            )->get()],
            200
        );
    }

    /**
     * Muestra los ajustes en tabuladores salariales registrados
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueInfo($id)
    {
        return response()->json(
            ['record' => PayrollSalaryAdjustment::with(
                [
                'payrollSalaryTabulator'
                ]
            )->find($id)],
            200
        );
    }

    /**
     * Método para realizar carga masiva de datos del ajuste en tablas salariales
     *
     * @author Fabián Palmera <fapalmera@cenditel.gob.ve>
     *
     * @param Request $request Datos de la petición
     *
     * @return void
     */
    public function import(Request $request)
    {
        request()->validate(
            [
            'file' => 'required|mimes:xlx,xls,xlsx'
            ]
        );

        $data['filePath'] = $request->file('file')->store('/tmp');

        $import = new SalaryAdjustmentImport();
        $import->import($data['filePath']);
        if ($import->failures()->isNotEmpty()) {
            return response()->json(['errors' => $import->failures()], 422);
        }
    }

    /**
     * Método para realizar exportación de planilla del ajuste en tablas salariales
     *
     * @author Fabián Palmera <fapalmera@cenditel.gob.ve>
     *
     * @return BinaryFileResponse    Objeto que permite descargar el archivo con la información a ser exportada
     */
    public function export()
    {
        return Excel::download(new PayrollSalaryAdjustmentExport(), 'registros_ajuste_salario.xlsx');
    }

    /**
     * Método para realizar exportación del ajuste para el tabulador salarial
     *
     * @author Fabián Palmera <fapalmera@cenditel.gob.ve>
     *
     * @param $salaryAdjustmentId   Identificador del ajuste en tabla salarial
     *
     * @return BinaryFileResponse    Objeto que permite descargar el archivo con la información a ser exportada
     */
    public function exportSalaryAdjustmentTabulator($salaryAdjustmentId)
    {
        $payrollSalaryAdjustment = PayrollSalaryAdjustment::find($salaryAdjustmentId);
        if ($payrollSalaryAdjustment) {
            $export = new PayrollSalaryAdjustmentTabulatorExport(PayrollSalaryAdjustment::class);
            $export->setSalaryAdjustmentId($salaryAdjustmentId);
            return Excel::download($export, 'ajuste_tabulador_salarial_' . Carbon::parse($payrollSalaryAdjustment->created_at)->format('d-m-Y') . '.xlsx');
        }
        return response()->json(['message' => 'error'], 400);
    }
}
