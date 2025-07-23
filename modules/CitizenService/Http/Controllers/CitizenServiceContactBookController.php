<?php

namespace Modules\CitizenService\Http\Controllers;

use App\Models\Phone;
use App\Rules\IdCard;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;
use Modules\CitizenService\Models\CitizenServiceContactBook;
use Nette\Utils\Json;

/**
 * @class CitizenServiceContactBookController
 * @brief Controlador para la gestión de la agenda de contactos de la OAC
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceContactBookController extends Controller
{
    protected $rules;
    protected $ruleMessages;

    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware(
            'permission:citizenservice.contact.book.list',
            ['only' => ['index', 'vueList']]
        );
        $this->middleware(
            'permission:citizenservice.contact.book.create',
            ['only' => ['create', 'store']]
        );
        $this->middleware(
            'permission:citizenservice.contact.book.edit',
            ['only' => ['edit', 'update']]
        );
        $this->middleware(
            'permission:citizenservice.contact.book.delete',
            ['only' => 'destroy']
        );
        $this->rules = [
            'identity_card' => [
                'required',
                'string',
                'max:9',
                'unique:citizen_service_contact_books',
                'regex:/^[V|E]{1}[0-9]{6,8}$/',
            ],
            'name' => ['required', 'string'],
            'surname' => ['required', 'string'],
            'position' => ['nullable', 'string'],
            'description' => ['nullable'],
            'citizen_service_served_institution_id' => [
                'required',
                'integer',
                'exists:citizen_service_served_institutions,id'
            ],
            'phones' => ['required', 'array'],
            'phones.*.type' => ['required', 'string'],
            'phones.*.area_code' => ['required', 'string'],
            'phones.*.number' => ['required', 'string'],
        ];

        $this->ruleMessages = [
            'identity_card.required' => __('El campo cédula de identidad es obligatorio'),
            'identity_card.string' => __('El campo cédula de identidad es inválido'),
            'identity_card.max' => __('El campo cédula de identidad no puede tener más de 9 caracteres'),
            'identity_card.unique' => __('El campo cédula de identidad ya existe'),
            'identity_card.regex' => __('El campo cédula de identidad es inválido'),
            'name.required' => __('El campo nombre es obligatorio'),
            'name.string' => __('El campo nombre es inválido'),
            'surname.required' => __('El campo apellido es obligatorio'),
            'surname.string' => __('El campo apellido es inválido'),
            'position.string' => __('El campo cargo es inválido'),
            'citizen_service_served_institution_id.required' => __('El campo institución es obligatorio'),
            'citizen_service_served_institution_id.integer' => __('El campo institución es inválido'),
            'citizen_service_served_institution_id.exists' => __('El campo institución no existe'),
            'phones.required' => __('Debe indicar al menos un número telefónico del contacto'),
            'phones.array' => __(
                'Los campo de teléfonos son inválidos, ' .
                'debe indicar información en todos los campos requeridos'
            ),
            'phones.*.type.required' => __('El campo tipo de teléfono es obligatorio'),
            'phones.*.type.string' => __('El campo tipo de teléfono es inválido'),
            'phones.*.area_code.required' => __('El campo código de área es obligatorio'),
            'phones.*.area_code.string' => __('El campo código de área es inválido'),
            'phones.*.number.required' => __('El campo número de teléfono es obligatorio'),
            'phones.*.number.string' => __('El campo número de teléfono es inválido'),
        ];
    }

    /**
     * Muestra una lista de la agenda de contactos de la OAC
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    View
     */
    public function index(): View
    {
        return view('citizenservice::contact-books.list');
    }

    /**
     * Muestra el formulario para crear un nuevo registro de agenda de contactos de la OAC
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    View
     */
    public function create(): View
    {
        return view('citizenservice::contact-books.create');
    }

    /**
     * Almacena un nuevo registro de agenda de contactos de la OAC
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate($this->rules, $this->ruleMessages);
        DB::transaction(function () use ($request) {
            $contactBook = CitizenServiceContactBook::create($request->all());

            if ($request->phones && !empty($request->phones)) {
                foreach ($request->phones as $phone) {
                    $contactBook->phones()->save(new Phone([
                        'type' => $phone['type'],
                        'area_code' => $phone['area_code'],
                        'number' => $phone['number'],
                        'extension' => $phone['extension']
                    ]));
                }
            }
        });
        $request->session()->flash('message', ['type' => 'store']);
        return response()->json([
            'result' => true,
            'redirect' => route('citizenservice.contact-books.index')
        ], 200);
    }

    /**
     * Muestra información de un registro de la agenda de contactos de la OAC
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     CitizenServiceContactBook $contactBook    Identificador del registro
     *
     * @return    View
     */
    public function show(CitizenServiceContactBook $contactBook): View
    {
        return view('citizenservice::show');
    }

    /**
     * Muestra el formulario para editar un registro de la agenda de contactos de la OAC
     *
     * @param     CitizenServiceContactBook $contactBook    Identificador del registro
     *
     * @return    View
     */
    public function edit(CitizenServiceContactBook $contactBook): View
    {
        $contactBook->load(['servedInstitution', 'phones']);
        return view(
            'citizenservice::contact-books.create',
            compact('contactBook')
        );
    }

    /**
     * Actualiza un registro de la agenda de contactos de la OAC
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     CitizenServiceContactBook $contactBook        Identificador del registro
     *
     * @return    JsonResponse
     */
    public function update(Request $request, CitizenServiceContactBook $contactBook): JsonResponse
    {
        $this->rules['identity_card'] = [
            'required',
            'string',
            'max:9',
            'unique:citizen_service_contact_books,identity_card,' . $contactBook->id,
            'regex:/^[V|E]{1}[0-9]{6,8}$/'
        ];
        $request->validate($this->rules, $this->ruleMessages);
        DB::transaction(function () use ($request, $contactBook) {
            $contactBook->update($request->all());

            foreach ($contactBook->phones as $phone) {
                $phone->delete();
            }

            if ($request->phones && !empty($request->phones)) {
                foreach ($request->phones as $phone) {
                    $contactBook->phones()->save(new Phone([
                        'type' => $phone['type'],
                        'area_code' => $phone['area_code'],
                        'number' => $phone['number'],
                        'extension' => $phone['extension']
                    ]));
                }
            }
        });

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json([
            'result' => true,
            'redirect' => route('citizenservice.contact-books.index')
        ], 200);
    }

    /**
     * Elimina un registro de la agenda de contactos de la OAC
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     CitizenServiceContactBook $contactBook    Identificador del registro
     *
     * @return    JsonResponse
     */
    public function destroy(CitizenServiceContactBook $contactBook): JsonResponse
    {
        $contactBook->delete();
        return response()->json(['result' => true], 200);
    }

    /**
     * Muestra una lista de la agenda de contactos de la OAC
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param Request $request Objeto con información de la petición
     *
     * @return JsonResponse
     */
    public function vueList(Request $request): JsonResponse
    {
        $contactBooks = CitizenServiceContactBook::with(['servedInstitution', 'phones'])
            ->orderBy('name')
            ->orderBy('surname');
        return response()->json([
            'records' => $contactBooks->get()
        ], 200);
    }
}
