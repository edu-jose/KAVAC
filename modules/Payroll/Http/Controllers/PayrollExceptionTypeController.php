<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Payroll\Models\Parameter;
use Modules\Payroll\Models\PayrollClassificationParameter;
use Modules\Payroll\Models\PayrollExceptionType;

/**
 * @class PayrollExceptionTypeController
 * @brief Controlador de tipo de excepciones de jornada laboral
 *
 * Clase que gestiona los tipos de excepciones de jornada laboral
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
final class PayrollExceptionTypeController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param array $validateRules Reglas de validación
     * @param array $messages      Mensajes de validación
     *
     * @return void
     */
    public function __construct(
        protected array $validateRules = [],
        protected array $messages = [],
    ) {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:payroll.exception.types.create', ['only' => ['store']]);
        $this->middleware('permission:payroll.exception.types.edit', ['only' => ['update']]);
        $this->middleware('permission:payroll.exception.types.delete', ['only' => 'destroy']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'name'        => ['required', 'max:100', 'unique:payroll_exception_types,name'],
            'description' => ['nullable', 'max:200'],
            'affect_id'   => ['nullable', 'integer'],
            'sign'        => ['required_with:affect_id'],
            'value_max'   => ['required', 'integer'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'name.required' => 'El campo nombre es obligatorio.',
            'sign.required_with' => 'El campo signo es requerido si se indica el inside sobre.',
            'value_max.required' => 'El campo valor máximo por periodo es obligatorio.'
        ];
    }

    /**
     * Muestra todos los registros de tipos de excepciones
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\JsonResponse    Json con los datos de los tipos de excepciones
     */
    public function index()
    {
        return response()->json(['records' => PayrollExceptionType::with('payrollClassificationParameter')->get()->map(fn($type) => [
            'id'   => $type->id,
            'name' => $type->name,
            'sign' => $type->sign ?? '',
            'description' => $type->description ?? '',
            'affect_id' => $type->affect_id,
            'value_max' => $type->value_max,
            'classifications' => $type->payrollClassificationParameter,
            'created_at' => $type->created_at,
            'updated_at' => $type->updated_at,
            'deleted_at' => $type->deleted_at,
        ])], 200);
    }

    /**
     * Valida y registra un nuevo tipo de excepción
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse Json con objeto guardado y mensaje de confirmación de la operación
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        $exceptionType = PayrollExceptionType::create([
            'name'        => $request->name,
            'description' => $request->description,
            'sign'        => $request->sign,
            'affect_id'   => $request->affect_id,
            'value_max'   => $request->value_max,
        ]);

        if (count($request->classifications) > 0) {
            foreach ($request->classifications as $classification) {
                PayrollClassificationParameter::create([
                    'name' => $classification['name'],
                    'payroll_exception_type_id' => $exceptionType->id,
                ]);
            }
        }

        return response()->json(['record' => $exceptionType, 'message' => 'Success'], 200);
    }

    /**
     * Actualiza la información de un tipo de excepción de nómina
     *
     * Esta función valida los datos de la solicitud, actualiza la nómina especificada.
     * tipo de excepción y administra sus clasificaciones asociadas.
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @author Juan Rosas <jrosar@cenditel.gob.ve>
     *
     * @param Request $request Request data
     * @param PayrollExceptionType $exceptionType Payroll exception type record
     *
     * @return \Illuminate\Http\JsonResponse Json response with success message
     */
    public function update(Request $request, PayrollExceptionType $exceptionType)
    {
        // Customize validation rules to include unique check excluding current record
        $validationRules = array_replace(
            $this->validateRules,
            [
                'name' => [
                    'required',
                    'max:100',
                    'unique:payroll_exception_types,name,' . $exceptionType->id
                ]
            ]
        );

        // Validar los datos de la solicitud
        $this->validate($request, $validationRules, $this->messages);

        // Actualizar el registro del tipo de excepción de nómina
        $exceptionType->update([
            'name' => $request->name,
            'description' => $request->description,
            'sign' => $request->sign,
            'affect_id' => $request->affect_id,
            'value_max' => $request->value_max
        ]);

        // Obtener clasificaciones existentes vinculadas a este tipo de excepción
        $existingClassifications = PayrollClassificationParameter::where('payroll_exception_type_id', $exceptionType->id)->get();
        $incomingClassificationNames = collect($request->classifications)->pluck('name')->toArray();

        // Eliminar clasificaciones que ya no están asociadas
        foreach ($existingClassifications as $existingClassification) {
            if (!in_array($existingClassification->name, $incomingClassificationNames)) {
                $existingClassification->delete();
            }
        }

        // Agregar o restaurar clasificaciones desde la solicitud
        foreach ($request->classifications as $classification) {
            $classificationRecord = PayrollClassificationParameter::firstOrCreate(
                [
                    'name' => $classification['name'],
                    'payroll_exception_type_id' => $exceptionType->id
                ]
            );

            // Restaurar la clasificación si fue eliminada previamente
            if ($classificationRecord->trashed()) {
                $classificationRecord->restore();
            }
        }

        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina un registro de tipo de excepción
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param PayrollExceptionType $exceptionType Registro de tipo de excepción
     *
     * @return \Illuminate\Http\JsonResponse Registro de tipo de excepción eliminado
     */
    public function destroy(PayrollExceptionType $exceptionType)
    {
        $parameters = Parameter::query()
            ->where(
                [
                    'active' => true,
                    'required_by' => 'payroll',
                ]
            )
            ->where('p_key', 'like', 'global_parameter_%')
            ->where('p_value', 'like', '%time_parameter%')
            ->withTrashed()
            ->toBase()
            ->get()
            ->filter(function ($parameter) use ($exceptionType) {
                return $exceptionType->id == json_decode($parameter->p_value)?->exception_type;
            });

        if ($parameters->count() > 0) {
            return response()->json(
                [
                    'error' => true,
                    'message' => 'El registro no puede ser eliminado ' .
                    ' porque está relacionado con registros de parámetros globales.'
                ],
                200
            );
        }

        PayrollClassificationParameter::query()
            ->where('payroll_exception_type_id', $exceptionType->id)
            ->delete();

        $exceptionType->delete();

        return response()->json(['record' => $exceptionType, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los tipos de excepciones de jornada laboral registradas
     *
     * @return \Illuminate\Http\JsonResponse Listado con los datos de los tipos de excepciones de jornada laboral
     */
    public function getPayrollExceptionTypes()
    {
        $payrollExceptionTypes = PayrollExceptionType::query()
            ->select('id', 'name as text', 'value_max')
            ->get()
            ->toArray();

        return response()->json(
            array_merge(
                array(['id' => '', 'text' => 'Seleccione...']),
                $payrollExceptionTypes
            ),
            200
        );
    }
}
