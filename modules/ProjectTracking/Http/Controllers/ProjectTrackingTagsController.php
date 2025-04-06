<?php

/** [descripción del namespace] */

namespace Modules\ProjectTracking\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\ProjectTracking\Models\ProjectTrackingTags;

/**
 * @class ProjectTrackingTagsController
 * @brief Gestiona los procesos de
 *
 *
 *
 * @author Mauricio Araujo araujoperezme20@gmail.com
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingTagsController extends Controller
{
    use ValidatesRequests;

    /**
     * Arreglo con las reglas de validación sobre los datos de un formulario
     * @var Array $validateRules
     */
    protected $validateRules;

    /**
     * Arreglo con los mensajes para las reglas de validación
     * @var Array $messages
     */
    protected $messages;
    /**
     * Define la configuración de la clase
     *
     * @author    Mauricio Araujo <araujoperezme20@gmail.com>
     */
    public function __construct()
    {
        /**Establecer los permisos de acceso para cada método del controlador  */
        $this->middleware('permission:project.tracking.tags.create', ['only' => 'store']);
        $this->middleware('permission:project.tracking.tags.update', ['only' => 'update']);
        $this->middleware('permission:project.tracking.tags.delete', ['only' => 'destroy']);

        /** Define las reglas de validación para el formulario */
        $this->validateRules = [
            'color'               => ['required'],
            'name'                => ['required'],
        ];

        /** Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'color.required'      => 'El campo color es obligatorio.',
            'name.required'       => 'El campo nombre es obligatorio.',
        ];
    }

    /**
     * [descripción del método]
     *
     * @method    index
     *
     * @author    Mauricio Araujo <araujoperezme20@gmail.com>
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function index()
    {
        return response()->json(['records' => ProjectTrackingTags::all()], 200);
    }

    /**
     * [descripción del método]
     *
     * @method    create
     *
     * @author    Mauricio Araujo <araujoperezme20@gmail.com>
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function create()
    {
        return view('projecttracking::create');
    }
    /**
     * [descripción del método]
     *
     * @method    store
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
        $projectTrackingTags = ProjectTrackingTags::create([
            'name' => $request->input('name'),
            'color' => $request->input('color'),
        ]);
        return response()->json(['record' => $projectTrackingTags, 'message' => 'Success'], 200);
    }

    /**
     * [descripción del método]
     *
     * @method    show
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function show($id)
    {
        return view('projecttracking::show');
    }

    /**
     * [descripción del método]
     *
     * @method    edit
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function edit($id)
    {
        return view('projecttracking::edit');
    }

    /**
     * [descripción del método]
     *
     * @method    update
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
        $this->validate($request, $this->validateRules, $this->messages);
        $projectTrackingTags = ProjectTrackingTags::find($id);
        $projectTrackingTags->name = $request->input('name');
        $projectTrackingTags->color = $request->input('color');
        $projectTrackingTags->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * [descripción del método]
     *
     * @method    destroy
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function destroy($id)
    {
        $projectTrackingTags = ProjectTrackingTags::find($id);
        $projectTrackingTags->delete();
        return response()->json(['message' => 'Success'], 200);
    }
}
