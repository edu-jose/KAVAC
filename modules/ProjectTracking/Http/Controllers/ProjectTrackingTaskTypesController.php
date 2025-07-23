<?php

/** [descripción del namespace] */

namespace Modules\ProjectTracking\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\ProjectTracking\Models\ProjectTrackingTaskTypes;

/**
 * @class ProjectTrackingTaskTypesController
 * @brief Gestiona los procesos de tipo de actividades
 *
 *
 *
 * @author Mauricio Araujo <maraujo@cenditel.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingTaskTypesController extends Controller
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
        $this->middleware('permission:project.tracking.task.types.create', ['only' => 'store']);
        $this->middleware('permission:project.tracking.task.types.update', ['only' => 'update']);
        $this->middleware('permission:project.tracking.task.types.delete', ['only' => 'destroy']);

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
        return response()->json(['records' => ProjectTrackingTaskTypes::all()], 200);
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
        $projectTrackingTaskTypes = ProjectTrackingTaskTypes::create([
            'name' => $request->input('name'),
            'color' => $request->input('color'),
            'description' => $request->input('description'),

        ]);
        return response()->json(['record' => $projectTrackingTaskTypes, 'message' => 'Success'], 200);
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

        $projectTrackingTaskTypes = ProjectTrackingTaskTypes::find($id);
        $projectTrackingTaskTypes->name = $request->input('name');
        $projectTrackingTaskTypes->color = $request->input('color');
        $projectTrackingTaskTypes->description = $request->input('description');
        $projectTrackingTaskTypes->save();

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
        $projectTrackingTaskTypes = ProjectTrackingTaskTypes::find($id);
        $projectTrackingTaskTypes->delete();

        return response()->json(['message' => 'Success'], 200);
    }

        /**
     * Retorna un json con todos los tipos de actividades registrados para ser usado en un componente <select2>
     *
     * @author    Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function getTaskTypes()
    {
        $taskTypesList = ProjectTrackingTaskTypes::all();
        $taskTypes = [];
        array_push($taskTypes, [
            'id' => '',
            'text' => 'Seleccione...'
        ]);

        foreach ($taskTypesList as $taskType) {
            array_push($taskTypes, [
                'id' => $taskType->id,
                'text' => $taskType->name
            ]);
        }

        return response()->json($taskTypes);
    }
}
