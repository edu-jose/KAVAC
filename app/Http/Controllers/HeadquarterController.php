<?php

namespace App\Http\Controllers;

use App\Rules\Rif;
use App\Models\Headquarter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @class HeadquarterController
 * @brief Gestiona información de las sedes
 *
 * Controlador para gestionar las sedes
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class HeadquarterController extends Controller
{
    protected $rules;
    protected $ruleMessages;

    /**
     * Define la configuración de la clase
     *
     * @author  Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:headquarter.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:headquarter.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:headquarter.delete', ['only' => 'destroy']);
        $this->middleware('permission:headquarter.list', ['only' => 'index']);

        $this->rules = [
            'name' => ['required'],
            'rif'  => ['nullable', 'string', 'size:10', 'regex:/^[E, G, J, P, V, 0-9 ]+$/', new Rif()],
            'address' => ['nullable', 'string'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'municipality_id' => ['nullable', 'exists:municipalities,id'],
            'region_id' => ['nullable', 'exists:regions,id'],
        ];

        $this->ruleMessages = [
            'name.required' => 'El campo nombre es obligatorio.',
            'rif.required' => 'El campo R.I.F. es obligatorio.',
            'rif.size' => 'El campo R.I.F. debe tener 10 carácteres.',
            'rif.regex' => 'El campo R.I.F. es inválido.',
        ];
    }

    /**
     * Muesta todos los registros de los sectores de organizaciones
     *
     * @author  Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
     *
     * @return JsonResponse     JSON con el listado de sectores de organismos
     */
    public function index()
    {
        $headquarters = Headquarter::with(['city', 'municipality', 'region'])->get();

        return response()->json(['records' => $headquarters], 200);
    }

    /**
     * Registra un nuevo sector de organización
     *
     * @author  Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
     *
     * @param  Request  $request    Objeto con información de la petición
     *
     * @return JsonResponse         JSON con el resultado del registro para sectores de organismos
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->rules, $this->ruleMessages);

        // Objeto con información de la sede
        $headquarter = Headquarter::create($request->all());

        return response()->json(['record' => $headquarter, 'message' => 'success'], 200);
    }

    /**
     * Actualiza la información del sector de organización
     *
     * @author  Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
     *
     * @param  Request  $request                        Objeto con información de la perición
     * @param  Headquarter  $headquarter    Objeto con información del sector de la organización a actualizar
     *
     * @return JsonResponse     JSON con el resultado de la actualización
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, $this->rules, $this->ruleMessages);

        $headquarter = Headquarter::find($id);

        if (isset($headquarter)) {
            $headquarter->update($request->all());
        }

        return response()->json(['message' => 'update'], 200);
    }

    /**
     * Elimina un sector de organización
     *
     * @author  Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
     *
     * @param  Headquarter  $headquarter    Objeto con información de la sede a eliminar
     *
     * @return JsonResponse     JSON con información del resultado de la eliminación
     */
    public function destroy($id)
    {
        $headquarter = Headquarter::find($id);
        $headquarter->delete();
        return response()->json(['record' => $headquarter, 'message' => 'destroy'], 200);
    }

    /**
     * Muesta todos los registros de los sectores de organizaciones
     *
     * @author  Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return array     JSON con el listado de sectores de organismos
     */
    public function getHeadquarters()
    {
        return template_choices('App\Models\Headquarter', 'name', '', true, null);
    }
}
