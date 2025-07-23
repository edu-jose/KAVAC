<?php

/**
 * Controlador de dependencias en el módulo de seguimientos
 */

namespace Modules\ProjectTracking\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\ProjectTracking\Models\ProjectTrackingDependenciesType;

/**
 * @class ProjectTrackingDependenciesTypeController
 * @brief Clase que gestiona los tipos de dependencia
 * @author Mauricio Araujo <maraujo@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingDependenciesTypeController extends Controller
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
     * Define la configuración de la clase
     *
     * @author Mauricio Araujo <maraujo@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        /**
 * Establece permisos de acceso para cada método del controlador
*/
        $this->middleware('permission:project.tracking.dependencies.type.create', ['only' => ['store']]);
        $this->middleware('permission:project.tracking.dependencies.type.edit', ['only' => ['update']]);
        $this->middleware('permission:project.tracking.dependencies.type.delete', ['only' => 'destroy']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'name'                                  => ['required'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'name.required'                                  => 'El campo nombre es obligatorio.',
        ];
    }

    /**
     * Muestra todos los registros de dependencias
     *
     * @author Mauricio Araujo <maraujo@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de dependencias
     */
    public function index()
    {
        return response()->json(['records' => ProjectTrackingDependenciesType::all()], 200);
    }

    /**
     * Retorna un json con todas las dependencias para ser usado en un componente <select2>
     *
     * @author Mauricio Araujo <maraujo@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDependenciesType()
    {
        $dependenciesTypeList = ProjectTrackingDependenciesType::all();
        $dependenciesType = [];
        array_push(
            $dependenciesType,
            [
            'id' => '',
            'text' => 'Seleccione...'
            ]
        );
        foreach ($dependenciesTypeList as $dependencyType) {
            array_push(
                $dependenciesType,
                [
                'id' => $dependencyType->id,
                'text' => $dependencyType->name
                ]
            );
        }
        return response()->json($dependenciesType);
    }

    /**
     * Mostrar el formulario para crear un nuevo tipo de dependencia.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('projecttracking::create');
    }

    /**
     * Valida y registra un nuevo tipo de dependencia
     *
     * @author Mauricio Araujo <maraujo@cenditel.gob.ve>
     *
     * @param \Illuminate\Http\Request $request Solicitud con los datos a guardar
     *
     * @return \Illuminate\Http\JsonResponse        Json: objeto guardado y mensaje de confirmación de la operación
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'name' => ['required', 'max:100', 'unique:project_tracking_dependencies_types,name']
            ]
        );

        $projecttrackingDependenciesType = ProjectTrackingDependenciesType::create([
            'name' => $request->name,
            'description' => $request->description
        ]);

        return response()->json(['record' => $projecttrackingDependenciesType, 'message' => 'Success'], 200);
    }

    /**
     * Mostrar el recurso específico.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('projecttracking::show');
    }

    /**
     * Mostrar el formulario para el recurso específico
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('projecttracking::edit');
    }

    /**
     * Actualiza la información del cargo
     *
     * @author Mauricio Araujo <maraujo@cenditel.gob.ve>
     *
     * @param \Illuminate\Http\Request $request Solicitud con los datos a actualizar
     * @param integer                  $id      Identificador de la dependencia a actualizar
     *
     * @return \Illuminate\Http\JsonResponse        Json con mensaje de confirmación de la operación
     */
    public function update(Request $request, $id)
    {
        $projecttrackingDependenciesType = ProjectTrackingDependenciesType::find($id);
        $this->validate(
            $request,
            [
                'name' => [
                    'required',
                    'max:100',
                    'unique:project_tracking_dependencies_types,name,' . $projecttrackingDependenciesType->id
                ],
            ]
        );
        $projecttrackingDependenciesType->name  = $request->name;
        $projecttrackingDependenciesType->description = $request->description;
        $projecttrackingDependenciesType->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina la dependencia
     *
     * @author Mauricio Araujo <maraujo@cenditel.gob.ve>
     *
     * @param integer $id Identificador de la dependencia a eliminar
     *
     * @return \Illuminate\Http\JsonResponse    Json: objeto eliminado y mensaje de confirmación de la operación
     */
    public function destroy($id)
    {
        $projecttrackingDependenciesType = ProjectTrackingDependenciesType::find($id);
        $projecttrackingDependenciesType->delete();
        return response()->json(['record' => $projecttrackingDependenciesType, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene las dependencias registradas
     *
     * @author Mauricio Araujo <maraujo@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de cargos
     */
    public function getProjectTrackingDependenciesType()
    {
        return response()->json(
            template_choices('Modules\ProjectTracking\Models\ProjectTrackingDependenciesType', 'name', '', true)
        );
    }
}
