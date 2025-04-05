<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

/**
 * @class DepartmentController
 * @brief Gestiona información de Departamentos
 *
 * Controlador para gestionar Departamentos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DepartmentController extends Controller
{
    /**
     * Lista de elementos a mostrar
     *
     * @var array $data
     */
    protected $data = [];
    protected $acronymRule;

    /**
     * Método constructor de la clase
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     */
    public function __construct()
    {
        $this->data[0] = [
            'id' => '',
            'text' => 'Seleccione...',
        ];
        $this->acronymRule = ['nullable', 'max:4'];
    }

    /**
     * Listado con todos los departamentos registrados
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    JsonResponse     JSON con información de respuesta a la petición
     */
    public function index()
    {
        return response()->json(['records' => Department::with(['parent', 'childrens'])->get()], 200);
    }

    /**
     * Registra un nuevo departamento
     *
     * @author     Ing. Francisco Escala <fjescala@cenditel.gob.ve> | <fjescala@gmail.com>
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request    $request    Objeto con información de la petición
     *
     * @return    JsonResponse     JSON con información de respuesta a la petición
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => ['required', 'unique:departments,name'],
            'institution_id' => ['required'],
            'acronym' => $this->acronymRule,
        ];
        $mesg = [
            'name.required' => 'El campo nombre es obligatorio.',
            'name.unique' => 'El campo nombre ya ha sido registrado en la institución.',
            'acronym.max' => 'El campo acrónimo no debe ser mayor a 4 caracteres.',
            'acronym.unique' => 'El campo acrónimo ya ha sido registrado.',
            'institution_id.required' => 'El campo institución es obligatorio.',

        ];
        $acronym = Department::where([
            'institution_id' => $request->institution_id,
            'acronym' => $request->acronym
        ])->first();

        if ($request->acronym && $acronym) {
            $rules = array_merge($rules, [
                'acronym' => ['max:4', 'unique:departments,acronym'],
            ]);
            $mesg = array_merge($mesg, [
                'acronym.unique' => 'El campo acrónimo ya ha sido registrado en la institucion.',
            ]);
        }

        $this->validate($request, $rules, $mesg);

        // Establece la jerarquía del departamento
        $hierarchy = 0;

        if (!is_null($request->parent_id) || !empty($request->parent_id)) {
            // Departamento asociado
            $dto = Department::where('parent_id', $request->parent_id)->first();
            if ($dto) {
                $hierarchy = (int) $dto->hierarchy + 1;
            }
        }

        // Objeto con información del departamento registrado
        $request->merge(['hierarchy' => $hierarchy]);
        $department = Department::create($request->all());

        return response()->json(['record' => $department, 'message' => 'Success'], 200);
    }

    /**
     * Actualiza los datos de un departamento
     *
     * @author     Ing. Francisco Escala <fjescala@cenditel.gob.ve> | <fjescala@gmail.com>
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request       $request       Objeto con datos de la petición
     * @param     Department    $department    Objeto con información del departamento a modificar
     *
     * @return    JsonResponse     JSON con información de respuesta a la petición
     */
    public function update(Request $request, Department $department)
    {
        $rules = [
            'name' => ['required', Rule::unique('departments', 'name')->ignore($department->id)],
            'institution_id' => ['required'],
            'acronym' => $this->acronymRule,
        ];
        $mesg = [
            'name.required' => 'El campo nombre es obligatorio.',
            'name.unique' => 'El campo nombre ya ha sido registrado en la institución.',
            'acronym.max' => 'El campo acrónimo no debe ser mayor a 4 caracteres.',
            'acronym.unique' => 'El campo acrónimo ya ha sido registrado.',
            'institution_id.required' => 'El campo institución es obligatorio.',

        ];

        $acronym = Department::where([
            'institution_id' => $request->institution_id,
            'acronym' => $request->acronym
        ])->first();

        if ($request->acronym && $acronym && $acronym->id != $department->id) {
            $rules = array_merge($rules, [
                'acronym' => [Rule::unique('departments', 'acronym')->ignore($department->id)],
            ]);
            $mesg = array_merge($mesg, [
                'acronym.unique' => 'El campo acrónimo ya ha sido registrado en la institucion.',
            ]);
        }

        $this->validate($request, $rules, $mesg);

        $hierarchy = 0;

        if (!is_null($request->parent_id) || !empty($request->parent_id)) {
            $dto = Department::where('parent_id', $request->parent_id)->first();
            if ($dto) {
                $hierarchy = (int) $dto->hierarchy + 1;
            }
        }

        $request->merge(['hierarchy' => $hierarchy]);
        $department->update($request->all());

        return response()->json(['message' => __('Registro actualizado correctamente')], 200);
    }

    /**
     * Elimina un departamento
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Department    $department    Objeto con información del departamento a eliminar
     *
     * @return    JsonResponse     JSON con información de respuesta a la petición
     */
    public function destroy(Department $department)
    {
        $department->delete();
        return response()->json(['record' => $department, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene un listado de departamentos
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  Request  $request        Objeto con los datos de la petición
     * @param  integer  $institution_id Identificador de la organización
     *
     * @return JsonResponse     JSON con información de respuesta a la petición
     */
    public function getDepartments(Request $request, $institution_id)
    {
        return response()->json(
            template_choices(Department::class, 'name', ['institution_id' => $institution_id], true)
        );
    }
}
