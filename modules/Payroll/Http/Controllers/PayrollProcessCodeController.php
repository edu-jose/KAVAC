<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Payroll\Models\PayrollProcessCode;

/**
 * @class PayrollProcessCodeController
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollProcessCodeController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct(
        protected array $validateRules = [],
        protected array $messages = [],
    ) {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:payroll.process.code.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.process.code.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.process.code.delete', ['only' => 'destroy']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'name' => ['required', 'unique:payroll_process_codes,name'],
            'code' => ['required', 'unique:payroll_process_codes,code'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'name.required' => 'El campo nombre es obligatorio.',
            'name.unique' => 'El nombre ya ha sido registrado.',
            'code.required' => 'El campo código es obligatorio.',
            'code.unique' => 'El código ya ha sido registrado.',
        ];
    }

    /**
     * [descripción del método]
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function index()
    {
        return response()->json(['records' => PayrollProcessCode::all()], 200);
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
     * [descripción del método]
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        $payrollProcessCode = PayrollProcessCode::create([
            'name' => $request->name,
            'code' => $request->code
        ]);

        return response()->json(['record' => $payrollProcessCode, 'message' => 'Success'], 200);
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
     * [descripción del método]
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     PayrollProcessCode $payrollProcessCode Registro a actualizar.
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function update(Request $request, PayrollProcessCode $payrollProcessCode)
    {
        $validateRules  = array_replace(
            $this->validateRules,
            ['name' => ['required', 'max:100', 'unique:payroll_seniorities,name,' . $payrollProcessCode->id],
             'code' => ['required', 'max:100', 'unique:payroll_seniorities,code,' . $payrollProcessCode->id]]
        );

        $this->validate($request, $validateRules, $this->messages);

        $payrollProcessCode->update([
            'name' => $request->name,
            'code' => $request->code,
        ]);

        return response()->json(['record' => $payrollProcessCode, 'message' => 'Success'], 200);
    }

    /**
     * [descripción del método]
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     PayrollProcessCode $payrollProcessCode Registro a eliminar.
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function destroy(PayrollProcessCode $payrollProcessCode)
    {
        $payrollProcessCode->delete();

        return response()->json([
            'record' => $payrollProcessCode,
            'message' => 'Success'
        ], 200);
    }


    /**
     * Obtiene los grupos etarios registrados
     *
     * @author    Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @return    array    Listado de los registros a mostrar
     */
    public function getPayrollAgeGroups()
    {
        $payrollAgeGroups = PayrollAgeGroup::query()
            ->get()
            ->map(fn($model) => [
                'id' => $model->id,
                'text' => $model->code . ' - ' . $model->name,
                'minimum' => $model->minimum_age,
                'maximum' => $model->maximum_age
            ])->toArray();

            return array_merge(
                [
                    [
                        'id' => '',
                        'text' => 'Seleccione...',
                        'payroll_ids' => [],
                    ]
                ],
                $payrollAgeGroups
            );
    }

    /**
     * [descripción del método]
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return JsonResponse     JSON con información de respuesta a la petición
     */
    public function getPayrollProcessCode()
    {
        return response()->json(
            template_choices(PayrollProcessCode::class, ['code', '-', 'name'], [], true)
        );
    }
}
