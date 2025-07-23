<?php

namespace Modules\ProjectTracking\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Payroll\Models\PayrollStaff;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\ProjectTracking\Models\ProjectTrackingTags;
use Modules\ProjectTracking\Models\ProjectTrackingTask;
use Modules\ProjectTracking\Models\ProjectTrackingProduct;
use Modules\ProjectTracking\Models\ProjectTrackingProject;
use Modules\ProjectTracking\Models\ProjectTrackingSubTask;
use Modules\ProjectTracking\Models\ProjectTrackingWorkDay;
use Modules\ProjectTracking\Models\ProjectTrackingTaskTimer;
use Modules\ProjectTracking\Models\ProjectTrackingSubProject;
use Modules\ProjectTracking\Models\ProjectTrackingActivityStatus;

/**
 * @class ProjectTrackingTaskController
 * @brief Gestiona los procesos del controlador
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingTaskController extends Controller
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

    /**
     * Define la configuración de la clase
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return    void
     */
    public function __construct()
    {
        /** Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:project.tracking.task.index', ['only' => ['index']]);
        $this->middleware('permission:project.tracking.task.create', ['only' => ['store']]);
        $this->middleware('permission:project.tracking.task.edit', ['only' => ['update']]);
        $this->middleware('permission:project.tracking.task.delete', ['only' => 'destroy']);
    }

    /**
     * Muestra el listado de tareas
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @return    \Illuminate\View\View
     */
    public function index()
    {
        $classType = ProjectTrackingTask::class;
        return view('projecttracking::tasks.index', compact('classType'));
    }

    /**
     * Muestra el formulario para crear una nueva tarea
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        $projectDates = ProjectTrackingProject::select('id', 'start_date', 'end_date')->toBase()->get();
        foreach ($projectDates as $projectDate) {
            $projectDate->type = 'project';
        }
        $subProjectDates = ProjectTrackingSubProject::select('id', 'start_date', 'end_date')->toBase()->get();
        foreach ($subProjectDates as $subProjectDate) {
            $subProjectDate->type = 'sub_project';
        }
        $productDates = ProjectTrackingProduct::select('id', 'start_date', 'end_date')->toBase()->get();
        foreach ($productDates as $productDate) {
            $productDate->type = 'product';
        }

        $datesToValidate = array_merge($projectDates->toArray(), $subProjectDates->toArray(), $productDates->toArray());

        $datesToValidate = json_encode($datesToValidate);

        return view('projecttracking::tasks.create-edit', compact('datesToValidate'));
    }

    /**
     * Almacena la información de una nueva tarea
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $rules = [
            'tasks' => ['nullable', function ($attribute, $value, $fail) {
                if (count($value) == 0) {
                    $fail('Debe agregar al menos una tarea.');
                }
            }],
            'tasks.*.project_name' => [
                'bail',
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    $this->validateDateInRange($request, $attribute, $value, $fail, ProjectTrackingProject::query());
                }
            ],
            'tasks.*.subproject_name' => [
                'bail',
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    $this->validateDateInRange($request, $attribute, $value, $fail, ProjectTrackingSubProject::query());
                }
            ],
            'tasks.*.product_name' => [
                'bail',
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    $this->validateDateInRange($request, $attribute, $value, $fail, ProjectTrackingProduct::query());
                }
            ],
            'tasks.*.activity_plan_id' => ['required'],
            'tasks.*.name' => ['required'],
            'tasks.*.description' => ['nullable', 'Max:250'],
            'tasks.*.employers_id' => ['required'],
            'tasks.*.priority_id' => ['required'],
            'tasks.*.start_date' => ['required', 'before_or_equal:tasks.*.end_date'],
            'tasks.*.end_date' => ['required', 'after_or_equal:tasks.*.start_date'],
            'tasks.*.new_end_date' => ['nullable', 'after_or_equal:tasks.*.start_date', 'after:tasks.*.end_date'],
            'tasks.*.cut_off_time' => ['nullable', 'Max:8'],
            'tasks.*.activity_status_id' => ['required'],
            'tasks.*.weight' => ['nullable', 'integer', 'Min:1', 'Max:100'],
            'tasks.*.subTasks.*.name' => ['sometimes', 'required'],
            'tasks.*.subTasks.*.description' => ['sometimes', 'required'],
        ];
        $messages = [];
        foreach ($request->input('tasks', []) as $index => $task) {
            $messages["tasks.{$index}.project_name.required"] = "El campo Proyecto en la tarea "
                . ($index + 1) . " es obligatorio";
            $messages["tasks.{$index}.subproject_name.required"] = "El campo Subproyecto en la tarea "
                . ($index + 1) . " es obligatorio";
            $messages["tasks.{$index}.product_name.required"] = "El campo Producto en la tarea "
                . ($index + 1) . " es obligatorio";
            $messages["tasks.{$index}.activity_plan_id.required"] = "El campo Actividad en la tarea "
                . ($index + 1) . " es obligatorio";
            $messages["tasks.{$index}.name.required"] = "El campo Nombre en la tarea "
                . ($index + 1) . " es obligatorio";
            $messages["tasks.{$index}.description.max"] = "El campo Descripción en la tarea "
                . ($index + 1) . " no debe superar los 250 caracteres";
            $messages["tasks.{$index}.employers_id.required"] = "El campo responsable de la tarea en la tarea "
                . ($index + 1) . " es obligatorio";
            $messages["tasks.{$index}.priority_id.required"] = "El campo Prioridad en la tarea "
                . ($index + 1) . " es obligatorio";
            $messages["tasks.{$index}.start_date.required"] = "El campo Fecha de inicio en la tarea "
                . ($index + 1) . " es obligatorio";
            $messages["tasks.{$index}.end_date.required"] = "El campo Fecha de fin en la tarea "
                . ($index + 1) . " es obligatorio";
            $messages["tasks.{$index}.end_date.after_or_equal"] =
                "La fecha de inicio no puede ser posterior a la fecha de fin en la tarea "
                . ($index + 1);
            $messages["tasks.{$index}.new_end_date.after_or_equal"] =
                "La fecha de inicio no puede ser posterior a la nueva fecha de fin en la tarea "
                . ($index + 1);
            $messages["tasks.{$index}.new_end_date.after"] =
                "La Nueva fecha de culminación debe ser mayor a la primera fecha de culminación"
                . ($index + 1);
            $messages["tasks.{$index}.start_date.before_or_equal"] =
                "La fecha de fin no puede ser anterior a la fecha de inicio en la tarea "
                . ($index + 1);
            $messages["tasks.{$index}.cut_off_time.max"] = "El campo Hora límite en la tarea "
                . ($index + 1) . " no debe superar los 8";
            $messages["tasks.{$index}.weight.integer"] = "El campo Peso en la tarea "
                . ($index + 1) . " debe ser un valor numerico";
            $messages["tasks.{$index}.weight.between"] = "El campo Peso en la tarea "
                . ($index + 1) . " debe estar entre 1 y 100";
            $messages["tasks.{$index}.activity_status_id.required"] = "El campo estatus de la actividad  en la tarea "
                . ($index + 1) . " es obligatorio";
        }

        $this->validate($request, $rules, $messages);

        DB::transaction(function () use ($request) {
            foreach ($request->tasks as $task) {
                $projectTrackingTask = ProjectTrackingTask::create([
                    'project_name' => $task['project_name'],
                    'subproject_name' => $task['subproject_name'],
                    'product_name' => $task['product_name'],
                    'activity_plan_id' => $task['activity_plan_id'],
                    'name' => $task['name'],
                    'description' => $task['description'],
                    'employers_id' => $task['employers_id'],
                    'priority_id' => $task['priority_id'],
                    'start_date' => $task['start_date'],
                    'end_date' => $task['end_date'],
                    'new_end_date' => $task['new_end_date'],
                    'cut_off_time' => $task['cut_off_time'],
                    'activity_status_id' => $task['activity_status_id'],
                    'depending_task_id' => $task['depending_task_id'],
                    'dependency_type_id' => $task['dependency_type_id'],
                    'task_type_id' => $task['task_type_id'],
                    'weight' => $task['weight'],
                    'percentage' => $task['percentage'],
                    'reviewer_id' => $task['reviewer_id'],
                    'approver_id' => $task['approver_id'],
                    'is_private' => $task['is_private'],
                    'payroll_staffs' => json_encode($task['payroll_staffs']),
                ]);

                $tags_id = array_column($task['tags'], 'id');
                $TaskTime =  ProjectTrackingTaskTimer::create([
                    'project_tracking_task_id' => $projectTrackingTask->id,
                    'start_time' => $task['start_date'],
                    'initial_status_id' => $task['activity_status_id'],
                ]);
                $projectTrackingTask->tags()->attach($tags_id);

                if ($task['subTasks'] && count($task['subTasks']) > 0) {
                    foreach ($task['subTasks'] as $subTask) {
                        ProjectTrackingSubTask::create([
                            'task_id' => $projectTrackingTask->id,
                            'name' => $subTask['name'],
                            'description' => $subTask['description']
                        ]);
                    }
                }
            }
        });

        return response()->json(['result' => true, 'redirect' => route('projecttracking.tasks.index')], 200);
    }

    /**
     * Muestra información de una tarea
     *
     * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @param  integer $id Identificador de la tarea
     *
     * @return Renderable
     */
    public function show($id)
    {
        $user_id = auth()->user()->id;
        $projectTrackingTask = ProjectTrackingTask::where('id', $id)
            ->with([
                'Project' => function ($query): void {
                    $query->select('id', 'name');
                },
                'Subproject' => function ($query): void {
                    $query->select('id', 'name');
                },
                'Product' => function ($query): void {
                    $query->select('id', 'name');
                },
                'ActivityPlan' => function ($query): void {
                    $query->with([
                        'teams',
                    ]);
                },
                'Activity' => function ($query): void {
                    $query->select('id', 'name_activity');
                },
                'Responsable' => function ($query): void {
                    $query->with([
                        'projectTrackingPersonalRegister'
                    ]);
                },
                'Approver' => function ($query): void {
                    $query->with([
                        'projectTrackingPersonalRegister'
                    ]);
                },
                'Reviewer' => function ($query): void {
                    $query->with([
                        'projectTrackingPersonalRegister'
                    ]);
                },
                'Priority' => function ($query): void {
                    $query->select('id', 'name', 'color');
                },
                'ActivityStatus' => function ($query): void {
                    $query->select('id', 'name', 'color');
                },
                'subTasks',
                'dependingTask',
                'dependenciesType',
                'tags',
                'taskType' => function ($query): void {
                    $query->select('id', 'name', 'color');
                },
            ])->first();
        $projectTrackingTask->time_spent = 0;
        $lastTimer = ProjectTrackingTaskTimer::where('project_tracking_task_id', $id)
            ->orderBy('created_at', 'desc')
            ->first();
        if ($lastTimer) {
            $time_spent = $this->calculateWorkHours($lastTimer->start_time, now());
            $projectTrackingTask->time_spent = $time_spent;
        }

        $projectTrackingTask->payroll_staffs = json_decode($projectTrackingTask->payroll_staffs);

        return view('projecttracking::tasks.show', compact('projectTrackingTask', 'user_id'));
    }

    /**
     * Solicita una tarea seleccionada a editar
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function edit($id)
    {
        $projecttrackingTask = ProjectTrackingTask::find($id);
        $projectDates = ProjectTrackingProject::select('id', 'start_date', 'end_date')->toBase()->get();
        foreach ($projectDates as $projectDate) {
            $projectDate->type = 'project';
        }
        $subProjectDates = ProjectTrackingSubProject::select('id', 'start_date', 'end_date')->toBase()->get();
        foreach ($subProjectDates as $subProjectDate) {
            $subProjectDate->type = 'sub_project';
        }
        $productDates = ProjectTrackingProduct::select('id', 'start_date', 'end_date')->toBase()->get();
        foreach ($productDates as $productDate) {
            $productDate->type = 'product';
        }

        $datesToValidate = array_merge($projectDates->toArray(), $subProjectDates->toArray(), $productDates->toArray());

        $datesToValidate = json_encode($datesToValidate);

        return view('projecttracking::tasks.create-edit', compact('projecttrackingTask', 'datesToValidate'));
    }

    /**
     * Carga la tarea seleccionada a editar
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function vueInfo($id): JsonResponse
    {
        $task = ProjectTrackingTask::where('id', $id)
            ->with([
                'Project',
                'Subproject',
                'Product',
                'ActivityPlan' => function ($query): void {
                    $query->with([
                        'teams',
                    ]);
                },
                'Activity',
                'Responsable',
                'Priority',
                'ActivityStatus',
                'subTasks',
            ])->first();

        $task->payroll_staffs = json_decode($task->payroll_staffs);

        $tags = $task->tags()->get()->map(function ($tag) {
            return [
                'id' => $tag->id,
                'text' => $tag->name,
            ];
        });

        $task->tags = $tags;
        return response()->json(['records' => $task], 200);
    }

    /**
     * Actualiza la información de una tarea
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $pausedStatusId = ProjectTrackingActivityStatus::where('name', 'Pausada')->first()->id;
        $this->validate(
            $request,
            [
                'project_name' => isset($request->project_name) ? ['required'] : ['nullable'],
                'subproject_name' => isset($request->subproject_name) ? ['required'] : ['nullable'],
                'product_name' => isset($request->product_name) ? ['required'] : ['nullable'],
                'activity_plan_id' => ['required'],
                'name' => ['required'],
                'description' => ['nullable', 'Max:250'],
                'employers_id' => ['required'],
                'priority_id' => ['required'],
                'start_date' => ['required', 'before_or_equal:end_date'],
                'end_date' => ['required', 'after_or_equal:start_date'],
                'new_end_date' => $request->activity_status_id == $pausedStatusId ?
                    ['required', 'after_or_equal:start_date', 'after:end_date'] :
                    ['nullable'],
                'cut_off_time' => $request->activity_status_id == $pausedStatusId ?
                    ['required', 'Max:8', 'Min:8', 'regex:/^[^_]+$/'] :
                    ['nullable'],
                'activity_status_id' => ['required'],
                'weight' => ['nullable', 'integer', 'Min:1', 'Max:100'],
                'subTasks.*.name' => ['sometimes', 'required'],
                'subTasks.*.description' => ['sometimes', 'required'],
            ],
            [],
            [
                'project_name' => 'Proyecto',
                'subproject_name' => 'Subproyecto',
                'product_name' => 'Producto',
                'activity_plan_id' => 'Actividad',
                'employers_id' => 'Responsable',
                'priority_id' => 'Prioridad',
                'start_date' => 'Fecha de incio',
                'end_date' => 'Fecha de culminación',
                'new_end_date' => 'Nueva fecha de culminación',
                'cut_off_time' => 'Hora límite',
                'activity_status_id' => 'Estatus de la Actividad',
                'weight' => 'Peso',
                'subTasks.*.name' => 'Nombre de la subtarea',
                'subTasks.*.description' => 'Descripción de la subtarea',
            ]
        );

        $task = ProjectTrackingTask::find($request->input('id'));
        $TaskTimer = ProjectTrackingTaskTimer::latest()->first();
        if ($TaskTimer) {
            if (isset($request->activity_status_id) && ($request->activity_status_id != $task->activity_status_id)) {
                $this->updateTaskTimer($request->input('id'), $request->activity_status_id);
            }
        } else {
            ProjectTrackingTaskTimer::create([
                'project_tracking_task_id' => $request->input('id'),
                'start_time' => $request->input('start_date'),
                'initial_status_id' => $request->input('activity_status_id'),
            ]);
        }
        if (isset($request->project_name)) {
            $task->project_name = $request->input('project_name');
        } elseif (isset($request->subproject_name)) {
            $task->subproject_name = $request->input('subproject_name');
        } else {
            $task->product_name = $request->input('product_name');
        }

        $task->activity_plan_id = $request->input('activity_plan_id');
        $task->name = $request->input('name');
        $task->description = $request->input('description');
        $task->employers_id = $request->input('employers_id');
        $task->priority_id = $request->input('priority_id');
        $task->start_date = $request->input('start_date');
        $task->end_date = $request->input('end_date');
        $task->new_end_date = $request->input('new_end_date');
        $task->cut_off_time = $request->input('cut_off_time');
        $task->activity_status_id = $request->input('activity_status_id');
        $task->depending_task_id = $request->input('depending_task_id');
        $task->dependency_type_id = $request->input('dependency_type_id');
        $task->task_type_id = $request->input('task_type_id');
        $task->weight = $request->input('weight');
        $task->percentage = $request->input('percentage');
        $task->reviewer_id = $request->input('reviewer_id');
        $task->approver_id = $request->input('approver_id');
        $task->is_private = $request->input('is_private');
        $task->payroll_staffs = $request->input('payroll_staffs');

        $tags_id = array_column($request['tags'], 'id');
        $task->tags()->sync($tags_id);
        $task->save();

        $subTasks = $request->input('subTasks');

        foreach ($subTasks as $subTaskData) {
            if (isset($subTaskData['id'])) {
                $subTask = ProjectTrackingSubTask::find($subTaskData['id']);
                $subTask->task_id = $task->id;
                $subTask->name = $subTaskData['name'];
                $subTask->description = $subTaskData['description'];
                $subTask->save();
            } else {
                ProjectTrackingSubTask::create([
                    'task_id' => $task->id,
                    'name' => $subTaskData['name'],
                    'description' => $subTaskData['description']
                ]);
            }
        }

        return response()->json(['result' => true, 'redirect' => route('projecttracking.tasks.index')], 200);
    }

    /**
     * Muestra la tarea seleccionada
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function recordInfo($id): JsonResponse
    {
        $records = ProjectTrackingTask::where('id', $id)
            ?->with('dependingTask', 'dependenciesType', 'tags')
            ->get()
            ->map(function (ProjectTrackingTask $record): array {
                return array_merge($record->toArray(), [
                    'project' => $record->project,
                    'subproject' => $record->subproject,
                    'product' => $record->product,
                    'priority' => $record->priority,
                    'activity_name' => $record->activity->name_activity,
                    'activity_status_name' => $record->activityStatus->name,
                    'depending_task_name' => $record?->dependingTask?->name ?? null,
                    'dependency_type_name' => $record?->dependenciesType?->name ?? null,
                    'employers_name' => $record->responsable->projectTrackingPersonalRegister->fullName,
                    'approver_name' => $record->approver->projectTrackingPersonalRegister->fullName,
                    'reviewer_name' => $record->reviewer->projectTrackingPersonalRegister->fullName,
                    'subtasks' => $record?->subTasks,
                ]);
            });
        return response()->json(['records' => $records[0]], 200);
    }

    /**
     * Elimina la tarea seleccionada
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @param     ProjectTrackingTask $projectTrackingTask  Registro a eliminar.
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy(ProjectTrackingTask $projectTrackingTask): JsonResponse
    {
        $projectTrackingTask->subTasks()->delete();
        $projectTrackingTask->delete();
        return response()->json(['message' => 'destroy'], 200);
    }

    /**
     * Muestra las tareas registradas
     *
     * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @return    JsonResponse JSON con los registros
     */
    public function vueList(): JsonResponse
    {
        $user = auth()->user();
        $profile = $user->profile;
        $isAdmin = $user->hasRole('admin');
        $records = ProjectTrackingTask::all();
        $rec = [];

        foreach ($records as $record) {
            $added = false;

            // Si es administrador, añade todos los registros, sean privados o no
            if ($isAdmin) {
                array_push($rec, array_merge($record->toArray(), [
                    'activity_status' => $record->activityStatus,
                    'priority' => $record->priority,
                    'project' => $record->project,
                    'subproject' => $record->subproject,
                    'product' => $record->product,
                    'employers_name' => $record->responsable->projectTrackingPersonalRegister->fullName,
                    'subTasks' => $record->subTasks,
                ]));
                continue; // Salta al siguiente registro, ya que no hay necesidad de verificar la privacidad
            }

            if ($record->is_private) {
                $staff = json_decode($record->payroll_staffs);

                foreach ($staff as $st) {
                    $emp = PayrollStaff::with('payrollEmployment')
                        ->without([
                            'payrollNationality',
                            'payrollFinancial',
                            'payrollGender',
                            'payrollBloodType',
                            'payrollDisability',
                            'payrollLicenseDegree',
                            'payrollStaffUniformSize',
                            'payrollSocioeconomic',
                            'payrollProfessional',
                            'payrollResponsibility'
                        ])
                        ->find($st->id)
                        ?->payrollEmployment
                        ?->id;
                    if ($emp == $profile->employee_id) {
                        if (!$added) {
                            array_push($rec, array_merge($record->toArray(), [
                                'activity_status' => $record->activityStatus,
                                'priority' => $record->priority,
                                'project' => $record->project,
                                'subproject' => $record->subproject,
                                'product' => $record->product,
                                'employers_name' => $record->responsable->projectTrackingPersonalRegister->fullName,
                                'subTasks' => $record->subTasks,
                            ]));
                            $added = true;
                        }
                        break;
                    }
                }
            } else {
                array_push($rec, array_merge($record->toArray(), [
                    'activity_status' => $record->activityStatus,
                    'priority' => $record->priority,
                    'project' => $record->project,
                    'subproject' => $record->subproject,
                    'product' => $record->product,
                    'employers_name' => $record->responsable->projectTrackingPersonalRegister->fullName,
                    'subTasks' => $record->subTasks,
                ]));
            }
        }

        return response()->json(['records' => $rec], 200);
    }

    /**
     * Valida un rango de fechas
     *
     * @param \Illuminate\Http\Request $request Datos e la petición
     * @param string $attribute Nombre del atributo
     * @param integer $value Identificador del registro
     * @param mixed $fail Registros fallidos
     * @param mixed $querySet Objeto con los datos de la consulta
     *
     * @return void
     */
    private function validateDateInRange(Request $request, $attribute, $value, $fail, $querySet)
    {
        $currentIndex = explode('.', $attribute)[1];
        if (isset($value)) {
            $entity = $querySet->find($value);
            $startDateEntity = Carbon::parse($entity->start_date);
            $endDateEntity = Carbon::parse($entity->end_date);
            $startDateTask = Carbon::parse($request->tasks[$currentIndex]['start_date']);
            $endDateTask = Carbon::parse($request->tasks[$currentIndex]['end_date']);
            if (
                !$startDateTask->between($startDateEntity, $endDateEntity)
                && !$endDateTask->between($startDateEntity, $endDateEntity)
            ) {
                $fail(
                    "La fecha de inicio y fin de la tarea " . $currentIndex + 1 .
                        " debe estar entre {$startDateEntity->format('d/m/Y')} y {$endDateEntity->format('d/m/Y')}."
                );
            }
        }
    }

    public function updateTaskTimer($task_id, $new_status_id)
    {
        $lastTimer = ProjectTrackingTaskTimer::where('project_tracking_task_id', $task_id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lastTimer) {
            return;
        }

        $lastTimer->final_status_id = $new_status_id;
        $lastTimer->end_time = now();
        $lastTimer->time_spent = $this->calculateWorkHours($lastTimer->start_time, $lastTimer->end_time);

        $lastTimer->save();

        // Crea un nuevo temporizador para el nuevo estatus
        $newTimer = new ProjectTrackingTaskTimer();
        $newTimer->project_tracking_task_id = $task_id;
        $newTimer->start_time = now();
        $newTimer->initial_status_id = $new_status_id;
        $newTimer->save();

        return $lastTimer;
    }
    private function calculateWorkHours($start_time, $end_time)
    {
        $start_time = Carbon::parse($start_time);
        $end_time = Carbon::parse($end_time);
        $refStart = $start_time->copy();
        $refEnd = $end_time->copy();
        $work_day_config = ProjectTrackingWorkDay::latest()->first();
        $working_days = json_decode($work_day_config->working_days, true);
        $working_hours = floatval($work_day_config->working_hours);

        $total_hours = 0;
        $turn = 0;
        $current_date = $start_time->startOfDay();
        $end_date = $end_time->startOfDay();
        $from_time_start = Carbon::parse($work_day_config->from)->setDate($start_time->year, $start_time->month, $start_time->day);
        $from_time_end = Carbon::parse($work_day_config->from)->setDate($end_time->year, $end_time->month, $end_time->day);
        $to_time_start  = Carbon::parse($work_day_config->to)->setDate($start_time->year, $start_time->month, $start_time->day);
        $to_time_end  = Carbon::parse($work_day_config->to)->setDate($end_time->year, $end_time->month, $end_time->day);
        while ($current_date->lte($end_date)) {
            if ($this->isWorkingDay($current_date, $working_days)) {
                if ($current_date->eq($end_date)) {
                    $from_time = Carbon::parse($work_day_config->from)->setDate($end_time->year, $end_time->month, $end_time->day);
                    if ($turn == 0) {
                        if ($refStart->gt($from_time_start) && $refStart->lt($to_time_start)) {
                            $turn = 1;
                            // la fecha de la creacion de la tarea esta entre el inicio y finalizacion de la jornada laboral ;
                            if ($refEnd->gt($from_time_end) && $refEnd->lt($to_time_end)) {
                                // la fecha de la actual entre el inicio y finalizacion de la jornada laboral ;
                                $total_hours += $refEnd->diffInRealHours($refStart);
                                $current_date->addDay();
                            } else {
                                $total_hours += $refStart->diffInRealHours($to_time_start);
                                $current_date->addDay();
                            }

                            continue;
                        } else {
                            // "La fecha no está entre la fecha de inicio y la fecha de fin.";
                        }
                    } else {
                        if ($from_time->isAfter($refEnd)) {
                            // No sumar horas si from_time es mayor que end_time
                        } else {
                            $total_hours += $refEnd->diffInRealHours($from_time_end);
                        }
                    }
                } else {
                    //las fechas no son iguales
                    if ($turn == 0) {
                        $total_hours +=  $refStart->diffInRealHours($to_time_start);
                            $turn = 1;
                    } else {
                        $total_hours += $working_hours;
                    }
                }
            } else {
                $turn = 1;
                $current_date->addDay();
                continue;
            }
            $turn = 1;
            $current_date->addDay();
        }
        return $total_hours; // retorna el total de segundos
    }

    private function isWorkingDay($date, $working_days)
    {
        $day_of_week = $date->format('l'); // obtiene el día de la semana (lunes, martes, etc.)

        $day_map = [
            'Monday' => 'Lunes',
            'Tuesday' => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sábado',
            'Sunday' => 'Domingo',
        ];

        foreach ($working_days as $day) {
            if ($day_map[$day_of_week] === $day['day']) {
                return true;
            }
        }

        return false;
    }
    /**
     * Retorna un json con todas las tareas
     *
     * @method getTasks
     *
     * @author Pedro Contreras <pdrocont@gmail.com/pmcontreras@cenditel.gob.ve>
     *
     * @return Renderable    [descripción de los datos devueltos]
     */
    public function getTasks(Request $request): JsonResponse
    {
        $query = ProjectTrackingTask::query()
            ->where('activity_plan_id', $request->activity_plan_id)
            ->where('id', '!=', $request->id)
            ->where(function ($query) use ($request) {
                $query->where('depending_task_id', '!=', $request->id)
                    ->orWhereNull('depending_task_id');
            });

        $tasks = $query->get()->map(function ($task) {
            return [
                'id' => $task->id,
                'text' => $task->name,
            ];
        })->prepend([
            'id' => '',
            'text' => 'Seleccione...',
        ]);

        return response()->json($tasks, JsonResponse::HTTP_OK);
    }
}
