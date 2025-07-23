<?php

namespace Modules\CitizenService\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\CitizenService\Models\CitizenServiceRequest;
use Modules\CitizenService\Models\CitizenServiceAddIndicator;
use App\Models\CodeSetting;
use App\Models\FiscalYear;
use App\Models\Phone;
use App\Rules\Rif as RifRule;
use Illuminate\Validation\Rule;
use Nwidart\Modules\Facades\Module;
use App\Models\Document;
use Modules\CitizenService\Models\CitizenServiceTransactionType;

/**
 * @class CitizenServiceRequestController
 * @brief Controlador para las solicitudes de la oficina de atención al ciudadano
 *
 * Clase que gestiona el controlador para las solicitudes de la OAC
 *
 * @author Ing. Yenifer Ramirez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceRequestController extends Controller
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

    protected $maxLenthOne = 'max:100';
    protected $maxLenthTwo = 'max:200';

    protected $createTemplate = 'citizenservice::requests.create';

    protected $defaultSize = 'size:10';

    protected $requiredCommunity = 'required_if:community,community';

    protected $requiredInstitution = 'required_if:type_institution,true';

    /**
     * Define la configuración de la clase
     *
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador

        $this->middleware('permission:citizenservice.requests.list', ['only' => ['index', 'vueList']]);
        $this->middleware('permission:citizenservice.requests.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:citizenservice.requests.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:citizenservice.requests.delete', ['only' => 'destroy']);
        $this->middleware('permission:citizenservice.requests.approved', ['only' => 'approved']);
        $this->middleware('permission:citizenservice.requests.rejected', ['only' => 'rejected']);
        $this->middleware('permission:citizenservice.requests.addindicator', ['only' => 'addIndicator']);
        $this->middleware('permission:citizenservice.requests.info', ['only' => 'vueInfo']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'first_name' => ['regex:/^[\D][a-zA-ZÁ-ÿ0-9\s]*/u', $this->maxLenthOne],
            'last_name' => ['regex:/^[\D][a-zA-ZÁ-ÿ0-9\s]*/u', $this->maxLenthOne],
            'id_number' => ['max:12', 'regex:/^([\d]{7,12})$/u'],
            'birth_date' => ['nullable', 'after_or_equal:01/01/1900'],
            'email' => ['email'],
            'address' => [$this->maxLenthOne],
            'motive_request' => [$this->maxLenthTwo],
            'attribute' => [$this->maxLenthTwo],
            'rif' => ['nullable', 'unique:citizen_service_requests,rif', $this->defaultSize, new RifRule()],
            'document' => ['max:20'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'first_name.max' => 'El campo nombres no debe contener más de 100 caracteres.',
            'first_name.regex' => 'El campo nombres no debe permitir números ni símbolos.',
            'last_name.max' => 'El campo apellidos no debe contener más de 100 caracteres.',
            'last_name.regex' => 'El campo apellidos no debe permitir números ni símbolos.',
            'id_number.max' => 'El campo cédula de identidad no debe de contener más de 12 caracteres.',
            'id_number.regex' => 'El campo cédula de identidad debe tener entre 7 y 12 digitos.',
            'birth_date.after_or_equal' => (
                'El campo fecha de nacimiento debe ser después o igual de la fecha 01/01/1900'
            ),
            'birth_date.before_or_equal' => (
                'El campo fecha de nacimiento debe ser antes o igual de la fecha 31/12/2100'
            ),
            'email.email' => 'El campo correo electrónico debe de ingresarse en formato de correo.',
            'address.max' => 'El campo dirección no debe contener más de 100 caracteres.',
            'motive_request.max' => 'El campo motivo de la solicitud no debe de contener más de 200 caracteres.',
            'attribute.max' => 'El campo atributos no debe de contener más de 200 caracteres.',
            'rif.unique' => 'El campo rif debe de ser único.',
            'rif.size' => 'El campo rif no debe de contener más de 10 caracteres. ',
            'document.max' => 'El campo Documento sólo debe tener 20 carácteres o menos',
        ];
    }

    /**
     * Muestra un listado de las solicitudes de atención al ciudadano
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('citizenservice::requests.list');
    }

    /**
     * Muestra el formulario para registrar una nueva solicitud
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view($this->createTemplate);
    }

    /**
     * Valida y registra una nueva solicitud de atención al ciudadano
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        $codeSetting = CodeSetting::where('table', 'citizen_service_requests')->first();
        if (!$codeSetting) {
            $request->session()->flash('message', [
                'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                'text' => 'Debe configurar previamente el formato para el código a generar'
            ]);
            return response()->json(['result' => false, 'redirect' => route('citizenservice.settings.index')], 200);
        }

        $currentFiscalYear = FiscalYear::select('year')
            ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

        $formatYear = strlen($codeSetting->format_year);
        $currentYear = isset($currentFiscalYear) ? $currentFiscalYear->year : date('Y');
        $year = $currentYear;

        if ($formatYear == 2) {
            $year = $currentYear ? substr($currentYear, 2, 2) : date('y');
        }

        $code  = generate_registration_code(
            $codeSetting->format_prefix,
            strlen($codeSetting->format_digits),
            $year,
            CitizenServiceRequest::class,
            $codeSetting->field
        );

        $request->merge([
            'file_counter' => 0,
            'code' => $code,
            'state' => 'Pendiente',
            'document_id' => $request->document,
            'has_venapp_report' => $request->has_venapp_report ? true : false,
            'is_household_head' => $request->is_household_head ? true : false,
            'has_work' => $request->has_work ? true : false
        ]);

        //Guardar los registros del formulario en  CitizenServiceRequest
        $citizenServiceRequest = CitizenServiceRequest::create($request->all());

        if ($request->phones && !empty($request->phones)) {
            foreach ($request->phones as $phone) {
                $citizenServiceRequest->phones()->save(new Phone([
                    'type' => $phone['type'],
                    'area_code' => $phone['area_code'],
                    'number' => $phone['number'],
                    'extension' => $phone['extension']
                ]));
            }
        }

        if ($request->documentFiles) {
            $this->setDocuments($request->documentFiles, $citizenServiceRequest->id);
        }

        $request->session()->flash('message', ['type' => 'store']);
        return response()->json([
            'result' => true,
            'redirect' => route('citizenservice.request.index')
        ], 200);
    }

    /**
     * Muestra el formulario para ver la información de las solicitudes de atención al ciudadano
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('citizenservice::show');
    }

    /**
     * Muestra el formulario para actualizar la información de las solicitudes de atención al ciudadano
     *
     * @param  integer $id ID de la solicitud
     *
     * @author Tsu. Miguel Narvaez <mnarvaez@cenditel.gob.ve>*
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $request = CitizenServiceRequest::find($id);
        if ($request) {
            //trae el archivo guardado
            $attributes = $request->getAttributes();
            $id = $attributes['id']; // id del documento
            $documentable_type = CitizenServiceRequest::class; //modulo del documento
            // Consulta documentos guardados segun id y modulos morfologicamente.
            $doc = Document::where('documentable_id', $attributes['id'])
            ->where('documentable_type', $documentable_type)->first();

            if ($doc) {
                $docattributes = $doc->getAttributes();
                return view($this->createTemplate, compact('request', 'docattributes'));
            } else {
                return view($this->createTemplate, compact('request'));
            }
        } else {
            return view($this->createTemplate, compact('request'));
        }
    }

    /**
     * Actualiza la información de las solicitudes de atención al ciudadano
     *
     * @param  Request $request Datos de la petición
     * @param  integer $id ID de la solicitud
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $citizenServiceRequest = CitizenServiceRequest::find($id);

        if ($request->type_institution) {
            $this->validateRules['rif'] = [
                'unique:citizen_service_requests,rif,' . $citizenServiceRequest->id,
                $this->defaultSize,
                new RifRule()
            ];
        }
        $this->validate($request, $this->validateRules, $this->messages);

        $request->merge([
            'type_institution' => $request->type_institution ? true : false,
            'state' => 'Pendiente',
            'has_venapp_report' => $request->has_venapp_report ? true : false,
            'is_household_head' => $request->is_household_head ? true : false,
            'has_work' => $request->has_work ? true : false
        ]);

        $citizenServiceRequest->update($request->all());

        foreach ($citizenServiceRequest->phones as $phone) {
            $phone->delete();
        }
        if ($request->phones && !empty($request->phones)) {
            foreach ($request->phones as $phone) {
                $citizenServiceRequest->phones()->create(
                    [
                        'type' => $phone['type'], 'area_code' => $phone['area_code'],
                        'number' => $phone['number'], 'extension' => $phone['extension']
                    ]
                );
            }
        }

        if ($request->documentFiles && is_array($request->documentFiles)) {
            if (array_filter($request->documentFiles, 'is_numeric')) {
                // Elimina cualquier documento previamente cargado
                Document::where(
                    [
                        'documentable_type' => CitizenServiceRequest::class,
                        'documentable_id' => $citizenServiceRequest->id
                    ]
                )->whereNotIn('id', $request->documentFiles)->delete();
            }

            //Verifica si tiene documentos para establecer la relación
            $this->setDocuments($request->documentFiles, $citizenServiceRequest->id);
        }

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('citizenservice.request.index')], 200);
    }

    /**
     * Elimina una solicitud de atención al ciudadano
     *
     * @param CitizenServiceRequest $request Datos de la solicitud
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(CitizenServiceRequest $request)
    {
        $attributes = $request->getAttributes();
        $documentable_type = CitizenServiceRequest::class;
        $doc = Document::where('documentable_id', $attributes['id'])
        ->where('documentable_type', $documentable_type)->first();
        if ($doc) {
            $docattributes = $doc->getAttributes();
            $id_delet = $docattributes['id'];
            Document::where('id', $id_delet)->delete();
        }
        $request->delete();
        return response()->json(['message' => 'destroy'], 200);
    }

    public function setDocuments($documentFiles, $id)
    {
        //Verifica si tiene documentos para establecer la relación
        foreach ($documentFiles as $file) {
            $doc = Document::find($file);
            $doc->documentable_id = $id;
            $doc->documentable_type = CitizenServiceRequest::class;
            $doc->save();
        }
    }

    /**
     * Obtiene un listado de las solicitudes registradas
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueList()
    {
        return response()->json([
            'records' => CitizenServiceRequest::with([
                'city',
                'parish',
                'requestGender',
                'requestNationality',
                'requestDirector',
                'citizenServiceRequestType',
                'citizenServiceDepartment',
                'procedure',
                'transactionType',
                'phones',
                'documents',
                'teams' => function ($query) {
                    $query->with(['payrollEmployee' => function ($query) {
                        $query->select('id', 'payroll_staff_id')->with(['payrollStaff' => function ($query) {
                            $query->select('id', 'first_name', 'last_name');
                        }]);
                    }]);
                }
            ])->get()
        ], 200);
    }

    /**
     * Obtiene información sobre una solicitud
     *
     * @param  integer $id ID de la solicitud
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueInfo($id)
    {
        $citizenServiceRequest = CitizenServiceRequest::where('id', $id)->with([
            'phones',
            'citizenServiceDepartment',
            'citizenServiceRequestType',
            'citizenServiceIndicator.indicator',
            'requestGender',
            'requestNationality',
            'requestDirector',
            'sector',
            'profession',
            'payrollInstructionDegree',
            'procedure',
            'transactionType'
        ])->first();

        if ($citizenServiceRequest) {
            //trae el archivo guardado
            $attributes = $citizenServiceRequest->getAttributes();
            $id = $attributes['id']; // id del documento
            $documentable_type = CitizenServiceRequest::class; //modulo del documento
            // Consulta documentos guardados segun id y modulos morfologicamente.
            $doc = Document::where('documentable_id', $attributes['id'])->where(
                'documentable_type',
                $documentable_type
            )->first();
            if ($doc) {
                $docattributes = $doc->getAttributes();
                return response()->json(['record' => $citizenServiceRequest, 'doc' => $docattributes], 200);
            } else {
                return response()->json(['record' => $citizenServiceRequest], 200);
            }
        } else {
            return response()->json(['record' => $citizenServiceRequest], 200);
        }
    }

    /**
     * Obtiene las solicitudes en estatus pendiente
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueListPending()
    {
        return response()->json([
            'records' => CitizenServiceRequest::where('state', 'Pendiente')->get()
        ], 200);
    }

    /**
     * Obtiene las solicitudes aceptadas
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueListClosing()
    {
        $citizenServiceRequest = CitizenServiceRequest::where('state', 'Aceptado')->get();
        return response()->json(['records' => $citizenServiceRequest], 200);
    }

    /**
     * Aprueba las solicitudes
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     * @param integer $id identificador de la solicitud
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function approved(Request $request, $id)
    {
        $citizenServiceRequest = CitizenServiceRequest::find($id);
        $citizenServiceRequest->state = 'Aceptado';
        $citizenServiceRequest->observation  = $request->observation;

        $citizenServiceRequest->save();

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('citizenservice.request.index')], 200);
    }


    /**
     * Rechaza las solicitudes
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     * @param integer $id identificador de la solicitud
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function rejected(Request $request, $id)
    {
        $citizenServiceRequest = CitizenServiceRequest::find($id);
        $citizenServiceRequest->state = 'Rechazado';
        $citizenServiceRequest->observation  = $request->observation;


        $citizenServiceRequest->save();

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('citizenservice.request.index')], 200);
    }

    /**
     * Agrega indicadores a las solicitudes
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     * @param integer $id identificador de la solicitud
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addIndicator(Request $request, $id)
    {
        $citizenServiceRequest = CitizenServiceRequest::find($id);

        foreach ($request->indicators as $indicator) {
            CitizenServiceAddIndicator::create([
                'name'         => $indicator['name'],
                'indicator_id' => $indicator['indicator_id'],
                'request_id'   => $citizenServiceRequest->id
            ]);
        }

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('citizenservice.request.index')], 200);
    }

    /**
     * Obtiene la lista de códigos de las solicitudes
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRequestCodes()
    {
        $codeList = CitizenServiceRequest::all();
        $codes = [];
        array_push($codes, [
            'id' => '',
            'text' => 'Seleccione...'
        ]);
        foreach ($codeList->all() as $code) {
            array_push($codes, [
                'id' => $code->id,
                'text' => $code->code
            ]);
        }
        return response()->json($codes);
    }

    /**
     * Obtiene la lista de tipo de transacción
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTransactionCodes()
    {
        $codeList = CitizenServiceTransactionType::all();
        $codes = [
            [
                'id' => '',
                'text' => 'Seleccione...'
            ]
        ];
        foreach ($codeList->all() as $code) {
            $codes = [
                ...$codes,
                [
                    'id' => $code->id,
                    'text' => $code->name
                ]
            ];
        }
        return response()->json($codes);
    }
}
