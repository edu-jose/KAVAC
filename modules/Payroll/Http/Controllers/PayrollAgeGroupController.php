<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Payroll\Models\PayrollAgeGroup;

/**
 * @class PayrollAgeGroupController
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollAgeGroupController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase
     *
     * @author Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     *
     * @return void
     */
    public function __construct(
        protected array $validateRules = [],
        protected array $messages = [],
    ) {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:payroll.age.groups.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.age.groups.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.age.groups.delete', ['only' => 'destroy']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'name' => ['required', 'max:100', 'unique:payroll_age_groups,name'],
            'description' => ['nullable', 'max:200'],
            'code' => ['required', 'max:100', 'unique:payroll_age_groups,code'],
            'minimum_age' => ['required', 'numeric'],
            'maximum_age' => ['required', 'numeric', 'gte:minimum_age'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'name.required' => 'El campo nombre es obligatorio.',
            'name.unique' => 'Ya ha sido registrado un grupo etario con ese nombre.',
            'code.required' => 'El campo código es obligatorio.',
            'code.unique' => 'Ya ha sido registrado un grupo etario con ese código.',
            'maximum_age.gte' => 'El campo máximo debe ser mayor al campo mínimo.',
            'maximum_age.required' => 'El campo máximo es obligatorio.',
            'minimum_age.required' => 'El campo mínimo es obligatorio.',
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
        return response()->json(['records' => PayrollAgeGroup::all()], 200);
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

        $payrollAgeGroup = PayrollAgeGroup::create([
            'name' => $request->name,
            'description' => $request->description,
            'code' => $request->code,
            'minimum_age' => $request->minimum_age,
            'maximum_age' => $request->maximum_age
        ]);

        return response()->json(['record' => $payrollAgeGroup, 'message' => 'Success'], 200);
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
     * @param     integer   $id        Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function update(Request $request, PayrollAgeGroup $payrollAgeGroup)
    {
        $validateRules  = array_replace(
            $this->validateRules,
            ['name' => ['required', 'max:100', 'unique:payroll_age_groups,name,' . $payrollAgeGroup->id],
             'code' => ['required', 'max:100', 'unique:payroll_age_groups,code,' . $payrollAgeGroup->id]]
        );

        $this->validate($request, $validateRules, $this->messages);

        $payrollAgeGroup->update([
            'name' => $request->name,
            'description' => $request->description,
            'code' => $request->code,
            'minimum_age' => $request->minimum_age,
            'maximum_age' => $request->maximum_age
            ]);

        return response()->json(['record' => $payrollAgeGroup, 'message' => 'Success'], 200);
    }

    /**
     * [descripción del método]
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     PayrollAgeGroup    $payrollAgeGroup Registro a eliminar.
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function destroy(PayrollAgeGroup $payrollAgeGroup)
    {
        $payrollAgeGroup->delete();

        return response()->json([
            'record' => $payrollAgeGroup,
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
}
