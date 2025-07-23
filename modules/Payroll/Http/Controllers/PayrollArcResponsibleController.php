<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollArcResponsible;

/**
 * @class PayrollArcResponsibleController
 *
 * @brief Gestión de los datos registrados de los Responsables de ARC.
 *
 * Clase que gestiona los Responsables de ARC.
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollArcResponsibleController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase.
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct(
        protected array $validateRules = [],
        protected array $messages = [],
    ) {
        /* Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:payroll.arc.responsibles.index', ['only' => 'index']);
        $this->middleware('permission:payroll.arc.responsibles.store', ['only' => 'store']);
        $this->middleware('permission:payroll.arc.responsibles.update', ['only' => 'update']);
        $this->middleware('permission:payroll.arc.responsibles.destroy', ['only' => 'destroy']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'payroll_staff_id' => ['required'],
            'fiscal_year' => ['required', 'integer'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'fiscal_year.required' => 'El campo Período fiscal es requerido',
            'payroll_staff_id.required' => 'El campo Trabajador es requerido',
        ];
    }

    /**
     * Devuelve un listado de los registros almacenados.
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse Json con los datos de las responsabilidades.
     */
    public function index()
    {
        $data = PayrollArcResponsible::query()
            ->with(['payrollStaff' => function ($query) {
                $query->without([
                        'payrollNationality',
                        'payrollFinancial',
                        'payrollGender',
                        'payrollBloodType',
                        'payrollDisability',
                        'payrollLicenseDegree',
                        'payrollEmployment',
                        'payrollStaffUniformSize',
                        'payrollSocioeconomic',
                        'payrollProfessional',
                        'payrollResponsibility'
                    ]);
            }])
            ->orderBy('fiscal_year')->get();

        return response()->json(['records' => $data], 200);
    }

    /**
     * Valida y registra una nueva responsabilidad.
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request Solicitud con los datos a guardar.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $payrollLastArcResponsible = PayrollArcResponsible::query()
            ->orderBy('fiscal_year')
            ?->get()
            ?->last();

        $validateRules  = $this->validateRules;
        $messages  = $this->messages;

        if (isset($payrollLastArcResponsible)) {
            $validateRules  = array_replace(
                $validateRules,
                [
                    'fiscal_year' => [
                        'required',
                        'integer',
                        function ($attribute, $value, $fail) use ($payrollLastArcResponsible) {
                            if ($value <= $payrollLastArcResponsible?->fiscal_year) {
                                $fail('Ya existe un responsable de ARC para el período seleccionado (' . $payrollLastArcResponsible->fiscal_year . ')');
                            }
                        }
                    ],
                ]
            );
        }

        $this->validate($request, $validateRules, $messages);

        if (isset($payrollLastArcResponsible) && is_null($payrollLastArcResponsible->end_date)) {
            $payrollLastArcResponsible->end_date = $request->start_date;
            $payrollLastArcResponsible->save();
        }

        $payrollArcResponsible = PayrollArcResponsible::create([
            'payroll_staff_id' => $request->payroll_staff_id,
            'fiscal_year' => $request->fiscal_year,
        ]);

        return response()->json([
            'record' => $payrollArcResponsible,
            'message' => 'Success'
        ], 200);
    }

    /**
     * Actualiza la información del registro.
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request Solicitud con los datos a actualizar.
     * @param  PayrollArcResponsible $payrollArcResponsible Registro a actualizar.
     *
     * @return \Illuminate\Http\JsonResponse Json con mensaje de confirmación de la operación.
     */
    public function update(Request $request, PayrollArcResponsible $payrollArcResponsible)
    {
        $payrollLastArcResponsible = PayrollArcResponsible::query()
            ->where('id', '<>', $payrollArcResponsible->id)
            ->orderBy('fiscal_year')
            ?->get()
            ?->last();

        $validateRules  = $this->validateRules;
        $messages  = $this->messages;

        $validateRules  = array_replace(
            $validateRules,
            [
                'id' => [
                    'integer',
                    function ($attribute, $value, $fail) use ($payrollArcResponsible) {
                        if ($payrollArcResponsible->blocked_at != null) {
                            $fail('El responsable de ARC no puede ser modificado si ya han sido generadas para el período (' . $payrollArcResponsible->fiscal_year . ')');
                        }
                    }
                ]
            ]
        );

        if (isset($payrollLastArcResponsible)) {
            $validateRules  = array_replace(
                $validateRules,
                [
                    'id' => [
                        'integer',
                        function ($attribute, $value, $fail) use ($payrollArcResponsible) {
                            if ($payrollArcResponsible->blocked_at != null) {
                                $fail('El responsable de ARC no puede ser modificado si ya han sido generadas para el período (' . $payrollArcResponsible->fiscal_year . ')');
                            }
                        }
                    ],
                    'fiscal_year' => [
                        'required',
                        'integer',
                        function ($attribute, $value, $fail) use ($payrollLastArcResponsible) {
                            if ($value <= $payrollLastArcResponsible?->fiscal_year) {
                                $fail('Ya existe un responsable de ARC para el período seleccionado (' . $payrollLastArcResponsible->fiscal_year . ')');
                            }
                        }
                    ],
                ]
            );
        }

        $this->validate($request, $validateRules, $messages);

        $payrollArcResponsible->update([
            'payroll_staff_id' => $request->payroll_staff_id,
            'fiscal_year' => $request->fiscal_year,
        ]);

        return response()->json([
            'message' => 'Success'
        ], 200);
    }

    /**
     * Elimina una responsabilidad registrada.
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  PayrollArcResponsible $payrollArcResponsible Registro a eliminar.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(PayrollArcResponsible $payrollArcResponsible)
    {
        if ($payrollArcResponsible->blocked_at != null) {
            return response()->json(['errors' => ['id' => ['El responsable de ARC no puede ser eliminado si ya han sido generadas para el período (' . $payrollArcResponsible->fiscal_year . ')']]], 422);
        };

        $payrollArcResponsible->forceDelete();

        return response()->json([
            'record' => $payrollArcResponsible,
            'message' => 'Success'
        ], 200);
    }
}
