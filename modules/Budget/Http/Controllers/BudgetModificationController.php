<?php

namespace Modules\Budget\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Models\CodeSetting;
use App\Models\Document;
use App\Models\DocumentStatus;
use App\Models\FiscalYear;
use Intervention\Image\Format;
use Modules\Budget\Models\BudgetModification;
use Modules\Budget\Models\BudgetModificationAccount;
use Modules\Budget\Models\BudgetSubSpecificFormulation;
use Modules\Budget\Models\BudgetAccountOpen;

/**
 * @class BudgetModificationController
 * @brief Controlador para las modificaciones presupuestarias del módulo de Presupuesto
 *
 * Clase que gestiona información de las modificaciones presupuestarias del módulo de Presupuesto
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetModificationController extends Controller
{
    use ValidatesRequests;

    /**
     * Arreglo con los datos a implementar en los atributos del formulario
     *
     * @var array $header
     */
    public $header;

    /**
     * Define la configuración de la clase
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:budget.modifications.list', ['only' => 'index', 'vueList']);
        $this->middleware('permission:budget.modifications.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:budget.modifications.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:budget.modifications.delete', ['only' => 'destroy']);
        $this->middleware('permission:budget.modifications.approve', ['only' => 'changeStatus']);

        /* Arreglo de opciones a implementar en el formulario */
        $this->header = [
            'route' => 'budget.modifications.store',
            'method' => 'POST',
            'role' => 'form',
            'class' => 'form-horizontal',
        ];
    }

    /**
     * Muestra el listado de modificaciones presupuestarias
     *
     * @return Renderable
     */
    public function index()
    {
        return view('budget::modifications.list');
    }

    /**
     * Muestra el formulario para crear un crédito adicional
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param string $type Indica el tipo de modificación presupuestaria
     *
     * @return Renderable
     */
    public function create($type)
    {
        $viewTemplate = ($type === "AC")
            ? 'aditional_credits'
            : (($type === 'RE')
                ? 'reductions'
                : (($type === "TR")
                    ? 'transfers' : ''));

        return view("budget::$viewTemplate.create-edit-form", compact('type'));
    }

    /**
     * Registra información de la modificación presupuestaria
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        /* Arreglo con las reglas de validación para el registro */
        $rules = [
            'approved_at' => ['required', 'date'],
            'description' => ['required'],
            'document' => ['required', 'max:20'],
            'institution_id' => ['required'],
            'currency_id' => $request->type_modification === 'AC' ? ['required'] : ['nullable'],
            'budget_account_id' => ['required', 'array', 'min:1']
        ];

        /* Arreglo con los mensajes para las reglas de validación */
        $messages = [
            'approved_at.required' => 'La fecha de aprobación es obligatoria.',
            'budget_account_id.required' => 'Las cuentas presupuestarias son obligatorias.',
            'document.max' => 'El campo Documento sólo debe tener 20 carácteres o menos',
            'budget_account_id.min' => 'Las cuentas presupuestarias son obligatorias.',
        ];

        $attributes = [
            'approved_at' => 'Fecha de creación',
            'document' => 'Documento',
            'institution_id' => 'Institución',
            'currency_id' => 'Moneda',
        ];

        /* Contiene la configuración del código establecido para el registro */
        if (!is_null($request->type)) {
            switch ($request->type) {
                case 'AC':
                    $codeFilter = 'budget.aditional-credits';
                    $codeSetting = CodeSetting::where(['table' => 'budget_modifications', 'type' => $codeFilter])->first();
                    break;
                case 'RE':
                    $codeFilter = 'budget.reductions';
                    $codeSetting = CodeSetting::where(['table' => 'budget_modifications', 'type' => $codeFilter])->first();
                    break;
                case 'TR':
                    $codeFilter = 'budget.transfers';
                    $codeSetting = CodeSetting::where(['table' => 'budget_modifications', 'type' => $codeFilter])->first();
                    break;
                default:
                    $codeFilter = '';
                    $codeSetting = '';
                    break;
            }
        }

        if (!isset($codeSetting) || !$codeSetting) {
            $rules['code'] = 'required';
            $message['code.required'] = 'Debe configurar previamente el formato para el código a generar';
        }

        $this->validate($request, $rules, $messages, $attributes);

        /* Obtiene el registro del documento con estatus aprobado */
        $documentStatus = DocumentStatus::getStatus('AP');
        /* Obtiene el registro del documento con estatus pendiente de aprobación */
        $documentStatusPR = DocumentStatus::getStatus('PR');

        $currentFiscalYear = FiscalYear::select('year')
            ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

        /* Contiene el código generado para el registro a crear */
        $code  = generate_registration_code(
            $codeSetting->format_prefix,
            strlen($codeSetting->format_digits),
            (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
                $currentFiscalYear->year : date('Y')),
            BudgetModification::class,
            $codeSetting->field
        );

        DB::transaction(function () use ($request, $code, $documentStatus, $documentStatusPR) {
            $type = ($request->type === "AC") ? 'C' : (($request->type === "RE") ? 'R' : 'T');

            /* Objeto que contiene los datos de la modificación presupuestaria creada */
            $budgetModification = BudgetModification::create([
                'type' => $type,
                'code' => $code,
                'approved_at' => $request->approved_at,
                'description' => $request->description,
                'document' => $request->document,
                'institution_id' => $request->institution_id,
                'currency_id' => $request->currency_id,
                'document_status_id' => $documentStatusPR->id
            ]);

            foreach ($request->budget_account_id as $account) {
                /* Obtiene la formulación correspondiente a la acción específica seleccionada */
                $formulation = BudgetSubSpecificFormulation::where('budget_specific_action_id', $account['from_specific_action_id'])
                    ->where('document_status_id', $documentStatus->id)
                    ->where('confirmed', true)
                    ->orderBy('year', 'desc')->first();

                if ($formulation) {
                    BudgetModificationAccount::create([
                        'amount' => $account['from_amount'],
                        'operation' => ($type === "C") ? 'I' : 'D',
                        'budget_sub_specific_formulation_id' => $formulation->id,
                        'budget_account_id' => $account['from_account_id'],
                        'budget_modification_id' => $budgetModification->id
                    ]);
                }

                if (isset($account['to_account_id'])) {
                    /* Obtiene la formulación correspondiente a la acción específica a donde transferir
                    los recursos */
                    $formulation_transfer = BudgetSubSpecificFormulation::currentFormulation(
                        $account['to_specific_action_id']
                    );

                    if ($formulation_transfer) {
                        BudgetModificationAccount::create([
                            'amount' => $account['to_amount'],
                            'operation' => 'I',
                            'budget_sub_specific_formulation_id' => $formulation_transfer->id,
                            'budget_account_id' => $account['to_account_id'],
                            'budget_modification_id' => $budgetModification->id
                        ]);
                    }
                }
            }

            if ($request->documentFiles) {
                //Verifica si tiene documentos para establecer la relación
                foreach ($request->documentFiles as $file) {
                    $doc = Document::find($file);
                    $doc->documentable_id = $budgetModification->id;
                    $doc->documentable_type = BudgetModification::class;
                    $doc->save();
                }
            }
        });

        $request->session()->flash('message', ['type' => 'store']);
        return response()->json([
            'result' => true, 'redirect' => route('budget.modifications.index')
        ], 200);
    }

    /**
     * Muestra el formulario de actualización de datos según el tipo de modificación presupuestaria
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param      string                $type            Define el tipo de modificación presupuestaria a mostrar
     * @param      BudgetModification    $modification    Objeto con información de la modificación presupuestaria a
     *                                                    actualizar
     *
     * @return     \Illuminate\View\View
     */
    public function edit($type, BudgetModification $modification)
    {
        $viewTemplate = ($type === "AC")
            ? 'aditional_credits'
            : (($type === 'RE')
                ? 'reductions'
                : (($type === "TR")
                    ? 'transfers' : ''));
        $model = $modification;

        return view("budget::$viewTemplate.create-edit-form", compact('type', 'model'));
    }

    /**
     * Actualiza los datos de la modificación presupuestaria
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        /* Arreglo con las reglas de validación para el registro */
        $rules = [
            'approved_at' => ['required', 'date'],
            'description' => ['required'],
            'document' => ['required', 'max:20'],
            'institution_id' => ['required'],
            'currency_id' => ['required'],
            'budget_account_id' => ['required', 'array', 'min:1']
        ];

        /* Arreglo con los mensajes para las reglas de validación */
        $messages = [
            'budget_account_id.required' => 'Las cuentas presupuestarias son obligatorias.',
            'document.max' => 'El campo Documento sólo debe tener 20 caracteres o menos',
        ];

        $attributes = [
            'approved_at' => 'Fecha de creación',
            'document' => 'Documento',
            'institution_id' => 'Institución',
            'currency_id' => 'Moneda',
        ];

        $this->validate($request, $rules, $messages, $attributes);

        $documentStatus = DocumentStatus::getStatus('AP');
        $documentStatusPR = DocumentStatus::getStatus('PR');

        DB::transaction(function () use ($request, $documentStatus, $documentStatusPR) {
            $budgetModification = BudgetModification::find($request->id);
            $type = ($request->type === "AC") ? 'C' : (($request->type === "RE") ? 'R' : 'T');

            /* Objeto que contiene los datos de la modificación presupuestaria creada */
            $budgetModification->type = $type;
            $budgetModification->approved_at = $request->approved_at;
            $budgetModification->description = $request->description;
            $budgetModification->document = $request->document;
            $budgetModification->institution_id = $request->institution_id;
            $budgetModification->currency_id = $request->currency_id;
            $budgetModification->document_status_id = $documentStatusPR->id;
            $budgetModification->save();

            $deleted = BudgetModificationAccount::where('budget_modification_id', $budgetModification->id)->delete();

            foreach ($request->budget_account_id as $account) {
                /* Obtiene la formulación correspondiente a la acción específica seleccionada */
                $formulation = BudgetSubSpecificFormulation::where('budget_specific_action_id', $account['from_specific_action_id'])
                    ->where('document_status_id', $documentStatus->id)
                    ->where('confirmed', true)
                    ->orderBy('year', 'desc')->first();

                if ($formulation) {
                    BudgetModificationAccount::create([
                        'amount' => $account['from_amount'],
                        'operation' => ($type === "C") ? 'I' : 'D',
                        'budget_sub_specific_formulation_id' => $formulation->id,
                        'budget_account_id' => $account['from_account_id'],
                        'budget_modification_id' => $budgetModification->id
                    ]);
                }

                if (isset($account['to_account_id'])) {
                    /* Obtiene la formulación correspondiente a la acción específica a donde transferir
                    los recursos */
                    $formulation_transfer = BudgetSubSpecificFormulation::currentFormulation(
                        $account['to_specific_action_id']
                    );

                    if ($formulation_transfer) {
                        BudgetModificationAccount::create([
                            'amount' => $account['to_amount'],
                            'operation' => 'I',
                            'budget_sub_specific_formulation_id' => $formulation_transfer->id,
                            'budget_account_id' => $account['to_account_id'],
                            'budget_modification_id' => $budgetModification->id
                        ]);
                    }
                }
            }

            if ($request->documentFiles) {
                // Elimina cualquier documento previamente cargado a la modificación presupuestaria
                Document::where(
                    [
                        'documentable_type' => BudgetModification::class,
                        'documentable_id' => $budgetModification->id
                    ]
                )->whereNotIn('id', $request->documentFiles)->delete();

                //Verifica si tiene documentos para establecer la relación
                foreach ($request->documentFiles as $file) {
                    $doc = Document::find($file);
                    $doc->documentable_id = $budgetModification->id;
                    $doc->documentable_type = BudgetModification::class;
                    $doc->save();
                }
            }
        });

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json([
            'result' => true, 'redirect' => route('budget.modifications.index')
        ], 200);
    }

    /**
     * Elimina una modificación presupuestaria
     *
     * @param integer $id Identificador de la modificación presupuestaria
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            /* Objeto con información de la modificación presupuestaria a eliminar */
            $budgetModification = BudgetModification::findOrFail($id);

            DB::beginTransaction();
            $budgetModification->delete();
            BudgetModificationAccount::where('budget_modification_id', $budgetModification->id)->delete();

            DB::commit();

            return response()->json(['record' => $budgetModification, 'message' => 'Success'], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => __($th->getMessage())], 500);
        }
    }

    /**
     * Actualiza el estatus del registro.
     *
     * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
     * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request
     * @param  integer $id Identificador de la modificación presupuestaria
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeStatus(Request $request, $id)
    {
        /* Arreglo con las reglas de validación para el registro */
        $rules = [
            'approved_date' => ['required', 'date', 'after_or_equal:approved_at']
        ];

        /* Arreglo con los mensajes para las reglas de validación */
        $messages = [
            'approved_date.required' => 'La :attribute es obligatoria.',
            'approved_date.date' => 'La :attribute debe ser una fecha.',
            'approved_date.after_or_equal' => 'La :attribute debe ser igual o posterior a la fecha de creación.',
        ];

        $attributes = [
            'approved_date' => 'Fecha de Aprobación',
        ];

        /* Valida la información del formulario */
        $request->validate($rules, $messages, $attributes);

        try {
            try {
                /* Objeto con información de la modificación presupuestaria a actualizar */
                $budgetModification = BudgetModification::query()->findOrFail($id);
            } catch (\Throwable $tr) {
                return response()->json([
                    'result' => false,
                    'message' => 'Registro no encontrado'
                ], 404);
            }

            /* Obtiene el registro del documento con estatus aprobado */
            $documentStatus = DocumentStatus::getStatus('AP');

            /* Objeto que contiene los datos de la modificación presupuestaria creada */
            $budgetModification->updateOrFail([
                'document_status_id' => $documentStatus->id,
                'approved_date' => $request->approved_date,
                'status' => $request->status
            ]);

            DB::transaction(function () use ($request, $budgetModification, $documentStatus) {
                $type = $budgetModification->type;

                /* Obtiene los registros de la tabla budget_modification_accounts */
                /**
                 *  'amount' => $account['from_amount' o 'to_amount'],
                 *  'operation' => 'I' or 'D',
                 *  'budget_sub_specific_formulation_id' => ID de la formulación,
                 *  'budget_account_id' => $account['from_account_id' OR 'to_account_id'],
                 *  'budget_modification_id' => ID de la modificación presupuestaria
                */
                $budgetModificationAccounts = BudgetModificationAccount::where([
                    'budget_modification_id' => $budgetModification->id
                ])->get();


                foreach ($budgetModificationAccounts as $account) {
                    /* Obtiene la formulación correspondiente a la acción específica seleccionada */
                    $formulation = BudgetSubSpecificFormulation::find($account['budget_sub_specific_formulation_id']);

                    if ($formulation) {
                        $budgetAccountOpen = BudgetAccountOpen::where('budget_sub_specific_formulation_id', $formulation->id)
                            ->where('budget_account_id', $account['budget_account_id'])
                            ->first();
                        if ($budgetAccountOpen) {
                            $modificationType = $account['operation'];

                            if ($modificationType == 'D') {
                                $budgetAccountOpen->total_year_amount_m = $budgetAccountOpen->total_year_amount_m - $account['amount'];
                                $budgetAccountOpen->save();
                            } elseif ($modificationType == 'I') {
                                $budgetAccountOpen->total_year_amount_m = $budgetAccountOpen->total_year_amount_m + $account['amount'];
                                $budgetAccountOpen->save();
                            }
                        }
                    }
                }
            });

            return response()->json([
                'result' => true,
                'message' => 'Registro aprobado correctamente',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'result' => false,
                'message' => 'Error al aprobar registro'
            ], 500);
        }
    }

    /**
     * Obtiene los registros a mostrar en listados de componente Vue
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param string $type Tipo de modificación presupuestaria
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueList($type)
    {
        switch ($type) {
            case 'AC':
                $tp = 'C';
                break;
            case 'RE':
                $tp = 'R';
                break;
            case 'TR':
                $tp = 'T';
                break;
            default:
                $tp = '';
                break;
        }
        $records = ($tp) ? BudgetModification::where('type', $tp)->get() : [];

        return response()->json([
            'records' => $records
        ], 200);
    }
}
