<?php

namespace Modules\Payroll\Http\Controllers;

use DateTime;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Payroll\Models\PayrollStaff;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Modules\Payroll\Models\PayrollWageGarnishments;
use Modules\Payroll\Exports\PayrollWageGarnishmentsExport;
use Modules\Payroll\Jobs\PayrollWageGarnishmentsImportJob;

/**
 * @class PayrollWageGarnishmentsController
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollWageGarnishmentsController extends Controller
{
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
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:payroll.wagegarnishments.list', ['only' => ['index', 'getWageGarnishments']]);
        $this->middleware('permission:payroll.wagegarnishments.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.wagegarnishments.edit', ['only' => ['edit']]);
        $this->middleware('permission:payroll.wagegarnishments.delete', ['only' => ['destroy']]);
        $this->middleware('permission:payroll.wagegarnishments.import', ['only' => 'import']);
        $this->middleware('permission:payroll.wagegarnishments.export', ['only' => 'export']);

        $this->rules = [
            "id"                            => 'nullable',
            "percetage"                     => 'required',
            "startDate"                     => ['required'],
            "endDate"                       => 'nullable|after:startDate',
            "payroll_staff_id"              => "required",
        ];
        $this->messages = [
            "percetage.required"            => 'El porcentaje de pago es requerido',
            "startDate.required"            => 'La fecha de inicio es requerida',
            "startDate.before"              => 'La fecha de inicio debe ser anterior a la fecha final',
            "endDate.after"                 => 'La fecha final debe ser posterior a la fecha de inicio',
            "payroll_staff_id.required"     => 'El trabajador es requerido',
            "payroll_staff_id.unique"       => 'El registro ya existe para este trabajador'
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
        return view('payroll::wage_garnishments.index');
    }
    public function create()
    {
        return view('payroll::wage_garnishments.create-edit');
    }

    /**
     * Obtiene la lista de registros de la planilla ARI
     *
     * @return \Illuminate\Http\JsonResponse
     **/
    public function getWageGarnishments(): JsonResponse
    {
        return response()->json([
            'records' => PayrollStaff::query()
                ->whereHas('payrollWageGarnishmentRegisters')
                ->whereHas('payrollNationality')
                ->with([
                    'payrollWageGarnishmentRegisters',
                    'payrollNationality' => function ($query): void {
                        $query->select('id', 'name');
                    },
                    ])
                ->get()
        ], JsonResponse::HTTP_OK);
    }
    /**
     * Almacena un nuevo registro del fondo de ahorro
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     Request    $request    Objeto con información de la petición
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function store(Request $request)
    {
        if ($request->endDate) {
            array_push($this->rules["startDate"], "before:endDate");
        }

        $request->validate($this->rules, $this->messages);

        $allAriRegisterForPayrollStaff =
            PayrollWageGarnishments::query()
                ->where('payroll_staff_id', $request->payroll_staff_id)
                ->when(isset($request->id), function ($query) use ($request) {
                    $query->where('id', '!=', $request->id);
                })
                ->get();

        $from_date = null;
        $to_date = null;

        /* Fechas del formulario ARI*/
        $startDate = new DateTime($request->startDate);

        if (count($allAriRegisterForPayrollStaff) > 0) {
            $lastestAriRegisterForPayrollStaff = $allAriRegisterForPayrollStaff->last();
            /* Fechas del ultimo registro ARI */
            $from_date = new DateTime($lastestAriRegisterForPayrollStaff?->from_date);
            $to_date = $lastestAriRegisterForPayrollStaff->to_date ? new DateTime($lastestAriRegisterForPayrollStaff->to_date) : null;

            if (new DateTime($request->startDate) <= $from_date && !$request->id) {
                return response()->json(['errors' => ['error' => ['La fecha de inicio debe ser posterior a la fecha de inicio anterior. Periodo: ' . $from_date->format('d-m-Y')]]], 500);
            }

            if ($to_date) {
                if ($startDate <= $to_date && $startDate >= $from_date && (count($allAriRegisterForPayrollStaff) > 1 || $request->id && count($allAriRegisterForPayrollStaff) >= 1)) {
                    return response()->json(['errors' => ['error' => ['La fecha de inicio se encuentra dentro del periodo de un registro anterior. Periodo: ' . $from_date->format('d-m-Y') . ' - ' . $to_date->format('d-m-Y')]]], 500);
                } else {
                    $lastestAriRegisterForPayrollStaff->to_date = $startDate->modify('-1 day')->format('Y-m-d');
                    $lastestAriRegisterForPayrollStaff->save();
                }
            } else {
                if ($startDate <= $from_date && count($allAriRegisterForPayrollStaff) > 1) {
                    return response()->json(['errors' => ['error' => ['La fecha de inicio se encuentra dentro del periodo de un registro anterior. Periodo: ' . $from_date->format('d-m-Y')]]], 500);
                }

                $lastestAriRegisterForPayrollStaff->to_date = $startDate->modify('-1 day')->format('Y-m-d');
                $lastestAriRegisterForPayrollStaff->save();
            }
        }

        if (!$request->id) {
            PayrollWageGarnishments::query()->create([
                "percetage" => $request->percetage / 100,
                "from_date" => $request->startDate,
                "to_date" => $request->endDate ?? null,
                "payroll_staff_id" => $request->payroll_staff_id
            ]);
        } else {
            $register = PayrollWageGarnishments::query()->find($request->id);
            $register->percetage = $request->percetage / 100;
            $register->from_date = $request->startDate;
            $register->to_date = $request->endDate ?? null;
            $register->save();
        }

        if ($request->id) {
            $request->session()->flash('message', ['type' => 'update']);
        } else {
            $request->session()->flash('message', ['type' => 'store']);
        }

        return response()->json(['success' => true, 'redirect_back' => 'payroll/wage-garnishments'], 200);
    }

    /**
     * Muestra el formulario para editar el registro del fondo de ahorro
     *
     * @param     PayrollWageGarnishments    $payrollWageGarnishments    Registro del fondo de ahorro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function edit($id)
    {
        $payrollWageGarnishmentsLast = PayrollWageGarnishments::query()->where('payroll_staff_id', $id)->latest()->first();

        return view('payroll::wage_garnishments.create-edit', ['register' => json_encode($payrollWageGarnishmentsLast ?? '')]);
    }

    /**
     * [descripción del método]
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     Request    $request         Objeto con datos de la petición
     * @param     PayrollWageGarnishments    $payrollWageGarnishments    Registro del fondo de ahorro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function update(Request $request, PayrollWageGarnishments $payrollWageGarnishments)
    {
        //
    }

    /**
     * Elimina el registro del fondo de ahorro
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     PayrollWageGarnishments    $payrollWageGarnishments    Registro del fondo de ahorro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function destroy($id)
    {
        $payrollRegisters = PayrollWageGarnishments::where('payroll_staff_id', $id)->get();

        foreach ($payrollRegisters as $payrollRegister) {
            $payrollRegister->delete();
        }

        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Importa registros del fondo de ahorro
     *
     * @param Request    $request    Objeto con datos de la petición
     *
     * @return void
     */
    public function import(Request $request)
    {
        request()->validate([
            'file' => 'required|mimes:xlx,xls,xlsx'
        ]);

        $data['filePath'] = $request->file('file')->store('/tmp');

        dispatch(new PayrollWageGarnishmentsImportJob($data));
    }

    /**
     * Descarga los registros de la planilla ARI
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        return Excel::download(new PayrollWageGarnishmentsExport(), 'registros_wage_garnishments.xlsx');
    }
}
