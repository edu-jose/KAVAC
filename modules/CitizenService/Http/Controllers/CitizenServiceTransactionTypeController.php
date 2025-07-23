<?php

namespace Modules\CitizenService\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Nwidart\Modules\Facades\Module;
use Modules\CitizenService\Models\CitizenServiceTransactionType;

/**
 * @class CitizenServiceTransactionTypeController
 * @brief Controlador de la configuración de tipo de transacción
 *
 * Clase que gestiona el controlador de tipo de transacción de la OAC
 *
 * @author Tsu. Miguel Narvaez
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceTransactionTypeController extends Controller
{
    use ValidatesRequests;

    /**
     * Reglas de validación
     *
     * @var array $validateRules
     */
    protected $validateRules;

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
        $this->middleware('permission:citizenservice.transaction.types.create', ['only' => ['index', 'create', 'store']]);
        $this->middleware('permission:citizenservice.transaction.types.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:citizenservice.transaction.types.delete', ['only' => 'destroy']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'name'                                  => ['required', 'regex:/^[\D][a-zA-ZÁ-ÿ0-9\s]*/u', 'max:100'],
            'description'                           => ['nullable', 'regex:/^[\D][a-zA-ZÁ-ÿ0-9\s]*/u', 'max:200'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'name.required'         => 'El campo nombre es obligatorio.',
            'name.max'              => 'El campo nombre no debe contener más de 100 caracteres.',
            'name.regex'            => 'El campo nombre no debe permitir números ni símbolos.',
            'description.max'       => 'El campo descripción no debe contener más de 200 caracteres.',
            'description.regex'     => 'El campo descripción no debe permitir números ni símbolos.',
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
        #return view('citizenservice::index');
        return response()->json(['records' => CitizenServiceTransactionType::all()], 200);
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
        return view('citizenservice::create');
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
        $this->validate($request, [
            'name' => [
                'required',
                'max:100',
                'unique:citizen_service_transaction_types,name'
            ]
        ]);

        $this->validate($request, $this->validateRules, $this->messages);

        //Guardar los registros del formulario en  CitizenServiceTransactionType
        $citizenServiceTransactionType = CitizenServiceTransactionType::create([

            'name'          => $request->input('name'),
            'description'   => $request->input('description'),
        ]);

        return response()->json(['record' => $citizenServiceTransactionType, 'message' => 'Success'], 200);
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
        return view('citizenservice::show');
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
        return view('citizenservice::edit');
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
    public function update(Request $request, $id)
    {
        $citizenServiceTransactionType = CitizenServiceTransactionType::find($id);

        $this->validate($request, [
            'name' => [
                'required',
                'max:100',
                'unique:citizen_service_transaction_types,name,' . $citizenServiceTransactionType->id,
            ],
            'description' => [
                'nullable',
                'max:200'
            ]
        ]);

        $citizenServiceTransactionType->name          = $request->name;
        $citizenServiceTransactionType->description   = $request->description;

        $citizenServiceTransactionType->save();

        return response()->json(['message' => 'Success'], 200);
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
    public function destroy($id)
    {
        $citizenServiceTransactionType = CitizenServiceTransactionType::find($id);
        $citizenServiceTransactionType->delete();
        return response()->json(['record' => $citizenServiceTransactionType, 'message' => 'Success'], 200);
    }

    /**
     * Retorna un json con todos los tipos de transacción para ser usado en un componente <select2>
     *
     * @author    Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function getTransactionTypes()
    {
        $transactiontypeList = CitizenServiceTransactionType::all();
        $transactiontypes = [];
        array_push($transactiontypes, [
            'id' => '',
            'text' => 'Seleccione...'
        ]);
        foreach ($transactiontypeList->all() as $transactiontype) {
            array_push($transactiontypes, [
                'id' => $transactiontype->id,
                'text' => $transactiontype->name
            ]);
        }
        return response()->json($transactiontypes);
    }
}
