<?php

declare(strict_types=1);

namespace Modules\Payroll\Http\Controllers;

use App\Models\Institution;
use App\Models\Parameter;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Payroll\Exports\PayrollTrustFileStaffExport;
use Modules\Payroll\Jobs\PayrollDeleteTrustFileJob;
use Modules\Payroll\Jobs\PayrollTrustFileJob;
use Nwidart\Modules\Facades\Module;

/**
 * @class PayrollTrustFileStaffController
 * @brief Controlador para agregar nuevos trabajadores a txt de fideicomiso
 *
 * Clase que gestiona agregar nuevos trabajadores a txt de fideicomiso
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollTrustFileStaffController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
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
        $this->middleware('permission:payroll.txt.trust.staff.create', ['only' => ['create', 'store']]);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'payroll_staffs' => ['required'],
            'file' => ['required'],
            'finance_bank_account_id' => ['required'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'payroll_staffs.required' => 'El campo trabajadores es requerido',
            'file.required' => 'El campo documento es requerido',
            'finance_bank_account_id.required' => 'El campo cuenta bancaria es requerido',
        ];
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
        $bankId = null;
        if (Module::has('Finance') && Module::isEnabled('Finance')) {
            $bankId = \Modules\Finance\Models\FinanceBank::where('short_name', 'BNC')->first()?->id;
        }

        return view('payroll::trust_file_staff.create-edit', compact('bankId'));
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
        $institutionAccountCode = '';
        $financeBankAccountCode = '';
        $profileUser = auth()->user()->profile;
        if ($profileUser && $profileUser->institution_id !== null) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }

        $parameter = Parameter::where([
            'active' => true, 'required_by' => 'accounting', 'p_key' => 'institution_account'
        ])->first();

        if (Module::has('Accounting') && Module::isEnabled('Accounting') && !empty($parameter)) {
            $institutionAccount = \Modules\Accounting\Models\AccountingAccount::find($parameter->p_value);
            $institutionAccountCode = $institutionAccount->code;
        }

        if (Module::has('Accounting') && Module::isEnabled('Accounting') && !empty($parameter)) {
            $financeBankAccountId = $request->input('finance_bank_account_id');
            $financeBankAccount = \Modules\Finance\Models\FinanceBankAccount::find($financeBankAccountId);
            $financeBankAccountCode = $financeBankAccount?->formated_ccc_number ?? '';
            $financeBankAccountCode = str_replace('-', '', $financeBankAccountCode);
        }

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $originalName = $file->getClientOriginalName();
        $fileNameWithoutExtension = pathinfo($originalName, PATHINFO_FILENAME);
        $pathName = 'documents/' . $fileName;
        Storage::disk('documents')->put($fileName, File::get($file));

        PayrollTrustFileJob::dispatch(
            $institution,
            auth()->user()->id,
            $fileName,
            $financeBankAccountCode,
            $fileNameWithoutExtension,
            $pathName,
            json_decode($request->input('payroll_staffs'), true),
        );

        $delay = now()->addWeeks(2);
        PayrollDeleteTrustFileJob::dispatch(
            $file->getClientOriginalName(),
            $fileNameWithoutExtension . '.txt',
        )
            ->delay($delay);

        return response()->json(['message' => 'success'], 200);
    }
}
