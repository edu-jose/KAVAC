<?php

namespace Modules\Payroll\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Support\Renderable;
use Modules\Payroll\Models\PayrollFinancial;
use Illuminate\Validation\ValidationException;
use Modules\Payroll\Jobs\PayrollExportNotification;
use Modules\Payroll\Imports\Staff\RegisterStaffImport;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * @class PayrollFinancialController
 * @brief Gestiona los procesos del controlador
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollFinancialController extends Controller
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
     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:payroll.financials.create', ['only' => ['store', 'create']]);
        $this->middleware('permission:payroll.financials.list', ['only' => ['index', 'vueList']]);
        $this->middleware('permission:payroll.financials.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.financials.delete', ['only' => 'destroy']);
        $this->middleware('permission:payroll.financials.restore', ['only' => 'restore']);
        $this->middleware('permission:payroll.financials.import', ['only' => 'import']);
        $this->middleware('permission:payroll.financials.export', ['only' => 'export']);
    }

    /**
     * Muestra el listados de datos financieros
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @return    \Illuminate\View\View
     */
    public function index()
    {
        return view('payroll::financials.index');
    }

    /**
     * Muestra el formulario de registro de datos financieros
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::financials.create-edit');
    }

    /**
     * Metodo que almacena un nuevo registro de datos financieros
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $accountNumber = ($request->bank_code ?? '') . ($request->payroll_account_number ?? '');
        $request->merge(['payroll_account_number' => $accountNumber]);

        // Revisión de existencia en registros borrados
        $deletedFinancial = PayrollFinancial::withTrashed()
        ->where('payroll_staff_id', $request->payroll_staff_id)
        ->where('payroll_account_number', $request->payroll_account_number)
        ->whereNotNull('deleted_at')
        ->first();

        if ($deletedFinancial) {
            $hasRestorePermission = auth()->user()->hasPermission('payroll.financials.restore');
            // Devolver respuesta JSON con el código de error y el ID del registro borrado para modal de restauracion
            return response()->json([
                'result'     => false,
                'message'    => $hasRestorePermission
                ? 'El trabajador ya cuenta con un número de cuenta asignado pero el registro se encuentra inactivo (borrado).'
                : 'El trabajador ya cuenta con un número de cuenta asignado pero el registro se encuentra inactivo (borrado). Contacte al administrador del sistema.',
                // Datos específicos solicitados
                'error_code' => $hasRestorePermission ? 'ACC_DEL_EXISTS_001' : 'ACC_DEL_EXISTS_002',
                'deleted_id' => $deletedFinancial->id, // ID del registro borrado
            ], 422); // 422 Unprocessable Entity
        }

        // 1. Definición de las reglas de validación (SIN la regla 'unique')
        $rules = [
            'payroll_staff_id'        => ['required','unique:payroll_financials,payroll_staff_id'],
            'finance_bank_id'         => ['required'],
            'finance_account_type_id' => ['required'],
            // Ya no se usa 'unique' de Laravel, confiamos en la DB
            'payroll_account_number'  => ['required', 'numeric', 'digits_between:20,20']
        ];

        // 2. Definición de mensajes personalizados (SIN el mensaje para 'unique')
        $messages = [
            'payroll_staff_id.required' => 'El campo trabajador es obligatorio.',
            'payroll_staff_id.unique'   => 'El trabajador ya tiene registrado datos financieros.',
            'finance_bank_id.required'  => 'El campo banco es obligatorio.',
            'finance_account_type_id.required' => 'El campo tipo de cuenta es obligatorio.',
            'payroll_account_number.required'  => 'El campo número de cuenta es obligatorio.',
            'payroll_account_number.numeric'   => 'El campo número de cuenta debe se númerico',
            'payroll_account_number.digits_between' => 'El campo número de cuenta debe tener 20 dígitos',
        ];

        // Ejecutar la validación de Laravel (solo valida formatos y existencia de staff_id)
        try {
            $this->validate($request, $rules, $messages);
        } catch (ValidationException $e) {
            // Captura y devuelve errores de validación estándar (422)
            return response()->json([
                'result' => false,
                'errors' => $e->errors(),
                'message' => 'Ocurrieron errores de validación.'
            ], 422);
        }

        // 3. Bloque Try-Catch para la inserción en la base de datos
        try {
            $payrollFinancial = PayrollFinancial::create([
                'payroll_staff_id'        => $request->payroll_staff_id,
                'finance_bank_id'         => $request->finance_bank_id,
                'finance_account_type_id' => $request->finance_account_type_id,
                'payroll_account_number'  => $request->payroll_account_number,
            ]);
        } catch (QueryException $e) {
            // El código SQLSTATE 23000 es el más común para Integrity Constraint Violation (violación de unicidad)
            if (in_array($e->getCode(), ['23000', '23505'])) {
                // 4. Analizar el error específico de unicidad
                $errorMessage = $e->getMessage();

                // Comprobamos si el error incluye el nombre de tu nuevo índice único
                // Esto es crucial para asegurarte de que es el error que quieres manejar.
                if (str_contains($errorMessage, 'unique_active_payroll_data')) {
                    $errorMsg = 'El número de cuenta ya está asignado a este trabajador pero el registro se encuentra inactivo (borrado).';

                    // 5. Devolver una respuesta JSON con el formato de error 422 de Laravel
                    return response()->json([
                        'result'  => false,
                        'message' => $errorMsg,
                        'errors'  => [
                            'payroll_account_number' => [$errorMsg]
                        ],
                    ], 422); // Código 422
                }
            }

            // Si no es el error de unicidad que esperas, o no se puede manejar, relanza la excepción
            throw $e;
        }

        $request->session()->flash('message', ['type' => 'store']);

        return response()->json(['result' => true, 'redirect' => route('payroll.financials.index')], 200);
    }

    /**
     * Muestra información de datos financieros
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
     * Muestra el formulario de edición de datos financieros
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function edit($id)
    {
        /* Objeto asociado al modelo PayrollFinancial */
        $payrollfinancial_edit = PayrollFinancial::find($id);
        return view('payroll::financials.create-edit', ['payrollfinancial_edit' => $payrollfinancial_edit ]);
    }

    /**
     * Realiza la acción necesaria para importar los datos Financieros
     *
     * @author    Francisco Escala
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    object    Objeto que permite descargar el archivo con la información a ser exportada
     */
    public function import(Request $request)
    {
        $filePath = $request->file('file')->store('', 'temporary');
        $fileErrorsPath = 'import' . uniqid() . '.errors';
        Storage::disk('temporary')->put($fileErrorsPath, '');
        $import = new RegisterStaffImport($filePath, 'temporary', auth()->user()->id, $fileErrorsPath);

        $import->import();

        return response()->json(['result' => true], 200);
    }

    /**
     * Exportar registros
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function export()
    {
        $userId = auth()->user()->id;
        PayrollExportNotification::dispatch(
            $userId,
            'Datos Financieros',
        );

        request()->session()->flash('message', ['type' => 'other', 'title' => '¡Éxito!',
            'text' => 'Su solicitud esta en proceso, esto puede tardar unos ' .
            'minutos. Se le notificara al terminar la operación',
            'icon' => 'screen-ok',
            'class' => 'growl-primary'
        ]);

        return redirect()->route('payroll.financials.index');
    }

    /**
     * Actualiza los datos financieros
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $accountNumber = ($request->bank_code ?? '') . ($request->payroll_account_number ?? '');
        $request->merge(['payroll_account_number' => $accountNumber]);

        $rules = [
            'payroll_staff_id'        => ['required'],
            'finance_bank_id'         => ['required'],
            'finance_account_type_id' => ['required'],
            'payroll_account_number'  => ['required', 'numeric', 'digits_between:20,20'],
        ];

        $messages = [
            'payroll_staff_id.required' => 'El campo trabajador es obligatorio.',
            'finance_bank_id.required'         => 'El campo banco es obligatorio.',
            'finance_account_type_id.required' => 'El campo tipo de cuenta es obligatorio.',
            'payroll_account_number.required'  => 'El campo número de cuenta es obligatorio.',
            'payroll_account_number.numeric'   => 'El campo número de cuenta debe se númerico',
            'payroll_account_number.digits_between'       => 'El campo número de cuenta debe tener 20 dígitos',
        ];

        // 2. Ejecutar la validación de Laravel
        try {
            $this->validate($request, $rules, $messages);
        } catch (ValidationException $e) {
            // Captura errores de validación estándar (ej. formato, campos obligatorios)
            return response()->json(['result' => false, 'errors' => $e->errors()], 422);
        }


        $payrollFinancial = PayrollFinancial::find($id);

        // 3. Bloque Try-Catch para la actualización en la base de datos
        try {
            $payrollFinancial->payroll_staff_id = $request->payroll_staff_id;
            $payrollFinancial->finance_bank_id = $request->finance_bank_id;
            $payrollFinancial->finance_account_type_id = $request->finance_account_type_id;
            $payrollFinancial->payroll_account_number = $request->payroll_account_number;
            $payrollFinancial->save();
        } catch (QueryException $e) {
            // Códigos SQLSTATE comunes para violación de unicidad: 23000 (MySQL), 23505 (PostgreSQL)
            if (in_array($e->getCode(), ['23000', '23505'])) {
                // Comprueba que el error provenga de tu índice específico
                if (str_contains($e->getMessage(), 'unique_active_payroll_data')) {
                    $errorMsg = 'El número de cuenta ya está asignado y activo a este trabajador. Revise los datos e intente de nuevo.';

                    // Devuelve una respuesta JSON 422 con un mensaje amigable
                    return response()->json([
                        'result'  => false,
                        'message' => $errorMsg,
                        'errors'  => [
                            'payroll_account_number' => [$errorMsg]
                        ],
                    ], 422); // Código 422 Unprocessable Entity
                }
            }

            // Si es otra QueryException (ej. foreign key) o no es tu índice, relanza la excepción
            throw $e;
        }

        $request->session()->flash('message', ['type' => 'update']);

        return response()->json(['result' => true, 'redirect' => route('payroll.financials.index')], 200);
    }

    /**
     * Elimina los datos financieros
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $payrollFinancial = PayrollFinancial::find($id);
        $payrollFinancial->delete();

        session()->flash('message', ['type' => 'destroy']);

        return response()->json(['record' => $payrollFinancial, 'message' => 'Success'], 200);
    }

    /**
     * Restaurar un registro de datos financieros eliminado
     *
     * @param int $id
     * @return void
     */
    public function restore($id)
    {

        restore_record(PayrollFinancial::class, ['id' => $id]);

        return response()->json(['message' => 'Success', 'redirect' => route('payroll.financials.edit', ['financial' => $id])], 200);
    }

    /**
     * Muestra los datos laborales registrados
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\JsonResponse    Json con los datos financieros del trabajador
     */
    public function vueList(Request $request)
    {
        $records = PayrollFinancial::with([
            'payrollStaff' => function ($query) {
                $query->without(
                    'payrollNationality',
                    'payrollFinancial',
                    'payrollGender',
                    'payrollBloodType',
                    'payrollDisability',
                    'payrollLicenseDegree',
                    'payrollEmployment',
                    'payrollStaffUniformSize',
                    'payrollSocioeconomic',
                    'payrollProfessional'
                );
            },
            'financeBank',
            'financeAccountType'
        ])
        ->search($request->get('query'))
        ->paginate($request->get('limit'));

        return response()->json(
            [
                'data' => $records->items(),
                'count' => $records->total(),
            ],
            200
        );
    }
}
