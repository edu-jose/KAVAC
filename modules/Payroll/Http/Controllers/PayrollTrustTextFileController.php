<?php

namespace Modules\Payroll\Http\Controllers;

use App\Models\Parameter;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Payroll\Exports\PayrollTextFileExport;
use Modules\Payroll\Models\Payroll;
use Modules\Payroll\Models\PayrollProcessCode;

/**
 * @class PayrollTrustTextFileController
 * @brief Controlador para los archivos de texto de fideicomiso

 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollTrustTextFileController extends Controller
{
    use ValidatesRequests;

    /**
     * Reglas de validación
     *
     * @var array $rules
     */
    protected $rules;

    /**
     * Mensajes de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Método constructor de la clase
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:payroll.txt.trust.create', ['only' => ['create', 'store']]);

        $this->rules = [
            'file_name' => ['required'],
            'process_code' => ['required'],
            'payroll_id' => ['required'],
            'payroll_payment_type_id' => ['required'],
            'date' => ['required', 'date'],
        ];

        $this->messages = [
            'file_name.required' => 'El nombre del archivo es obligatorio',
            'process_code.required' => 'El código de proceso es obligatorio',
            'date.required' => 'La fecha de pago es obligatoria',
            'date.date' => 'La fecha de pago tiene un formato inválido',
            'payroll_payment_type_id.required' => 'El campo tipo de nómina es obligatorio',
            'payroll_id.required' => 'El campo periodo es obligatorio',
        ];
    }

    /**
     * Muestra el formulario de creación de archivos de texto de fideicomiso
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::trust_text_file.create-edit');
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
        $payrollId = Payroll::query()
            ->where('payroll_payment_period_id', $request['payroll_id'])
            ->first()
            ?->id;

        $trustCode = Parameter::query()
            ->where('p_key', 'trust_code')
            ->where('required_by', 'payroll')
            ->first()
            ?->p_value;

        $processCode = PayrollProcessCode::query()
            ->find($request['process_code'])
            ?->code;

        $export = new PayrollTextFileExport();
        $export->setPayrollId([$payrollId], null, $processCode, $request['date'], $trustCode);
        return Excel::download($export, $request['fileNumber'] . $request["fileName"] . now('utc') . '.txt', \Maatwebsite\Excel\Excel::CSV);
    }

    /**
     * Valida los datos del archivo de texto de nómina
     *
     * @param \Illuminate\Http\JsonResponse
     */
    public function validateTxtData(Request $request)
    {
        $validator = Validator::make($request->toArray(), $this->rules, $this->messages);

        $trustCode = Parameter::query()
            ->where('p_key', 'trust_code')
            ->where('required_by', 'payroll')
            ->first()
            ?->p_value;

        if (is_null($trustCode)) {
            $request->session()->flash('message', [
                'type' => 'other',
                'title' => __('Alerta'),
                'icon' => 'screen-error',
                'class' => 'growl-danger',
                'text' => __('Debe configurar previamente el código de fideicomitente'),
            ]);

            return response()->json([
                'result' => false,
                'redirect' => route('payroll.settings.index')
            ], 200);
        }

        $payrollId = Payroll::query()
            ->where('payroll_payment_period_id', $request['payroll_id'])
            ->first()
            ?->id;

        $trustCode = Parameter::query()
            ->where('p_key', 'trust_code')
            ->where('required_by', 'payroll')
            ->first()
            ?->p_value;

        $processCode = PayrollProcessCode::query()
            ->find($request['process_code'])
            ?->code;

        try {
            $export = new PayrollTextFileExport();
            $export->setPayrollId([$payrollId], null, $processCode, $request['date'], $trustCode);
        } catch (\Exception $e) {
            $message = [];

            foreach (json_decode($e->getMessage()) as $m) {
                if (!empty($m->employments) && count($m->employments) > 0) {
                    $message[0] = 'Los siguientes empleados no poseen datos laborales, por favor verifique e intente nuevamente:';

                    foreach ($m->employments as $employment) {
                        $message[] = $employment;
                    }
                }
            }

            return response()->json(['message' => 'The given data was invalid.', 'errors' => ['error' => $message]], 422);
        }

        if (!$validator->fails()) {
            return response()->json(['message' => 'Success'], 200);
        }

        return response()->json(['errors' => $validator->errors()], 422);
    }

    /**
     * Obtener los períodos de pago por estatus
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentTypes()
    {
        return response()->json(
            template_choices(
                'Modules\Payroll\Models\PayrollPaymentType',
                ['code', '-', 'name'],
                ['is_trust' => true],
                true
            )
        );
    }

    /**
     * Obtener los períodos de pago por estatus
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentPeriodsByStatus(Request $request)
    {
        return response()->json(
            template_choices(
                'Modules\Payroll\Models\PayrollPaymentPeriod',
                ['start_date', '-', 'end_date'],
                ['payroll_payment_type_id' => $request->payment_type, 'payment_status' => 'generated'],
                true
            )
        );
    }
}
