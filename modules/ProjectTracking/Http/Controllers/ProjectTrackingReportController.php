<?php

namespace Modules\ProjectTracking\Http\Controllers;

use Carbon\Carbon;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Validator;
use Modules\ProjectTracking\Models\ProjectTrackingTask;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Modules\ProjectTracking\Models\ProjectTrackingProduct;
use Modules\ProjectTracking\Models\ProjectTrackingProject;
use Modules\ProjectTracking\Repositories\WorkerReportRepository;
use Modules\ProjectTracking\Models\ProjectTrackingSubProject;
use Modules\ProjectTracking\Models\ProjectTrackingActivityStatus;
use Modules\ProjectTracking\Models\ProjectTrackingPersonalRegister;

/**
 * @class ProjectTrackingReportController
 * @brief Controlador de los reportes generados en el módulo de seguimiento
 *
 * Clase que gestiona los reportes generados en el módulo de bienes
 *
 * @author Fabián Palmera <fapalmera@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingReportController extends Controller
{
    /**
     * Método constructor de la clase
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:project.tracking.reports.create', ['only' => 'create']);
    }
    /**
     * Muestra un listado de los reportes de seguimiento
     *
     * @author    Fabián Palmera <fapalmera@cenditel.gob.ve>
     *
     * @return    \Illuminate\View\View

     */
    public function index()
    {
        return view('projecttracking::index');
    }

    /**
     * Valida y registra una reporte de rendimiento de los trabajadores
     *
     * @param     \Illuminate\Http\Request $request
     *
     * @author    Fabián Palmera <fapalmera@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        $profileUser = $user->profile;
        if (($profileUser) && isset($profileUser->institution_id)) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }

        $pdf = new WorkerReportRepository();
        $filename = 'projecttracking-report-' . Carbon::now()->format('Y-m-d') . '.pdf';
        $workers = [];
        $report_type = '';

        $pdf->setConfig(
            [
                'institution' => $institution,
                'urlVerify' => url(''),
                'orientation' => 'P',
                'filename' => $filename,
            ]
        );

        if ($request->current == 'personal-register') {
            $body = 'projecttracking::pdf.projecttracking-personal-register';
        } else {
            $body = '';
        }

        // Validar y obtener datos para generar el reporte
        list($records, $tasks, $status, $count_statuses, $report_message) = $this->getRecords($request);

        if ($request->input('monthly_date')) {
            $pdf->setHeader('Reporte de trabajadores - ' . $report_message);
        } else {
            $pdf->setHeader('Reporte desde ' . $report_message);
        }

        $pdf->setFooter(true, strip_tags($institution->legal_address));

        // Obtener codigo base64 de la imagen del gráfico
        $image = $request->input('chart') ? $request->input('chart') : '';
        $image_code = '';
        if (isset($image)) {
            $image_code = explode(',', $image)[1];
            $image_code = $image_code ? '@' . base64_decode($image_code) : '';
        }

        $new_pdf = $pdf->setBody(
            $body,
            true,
            [
                'pdf' => $pdf,
                'field' => $records,
                'image' => $image_code,
                'tasks' => $tasks,
                'statuses' => $status,
                'count_statuses' => $count_statuses,
            ]
        );

        $url = route('project-tracking.reports.show', [$filename]);
        return response()->json(['result' => true, 'redirect' => $url], 200);
    }

    /**
     * Muestra información de un reporte
     *
     * @param string filename Nombre del archivo
     *
     * @author Fabián Palmera <fapalmera@cenditel.gob.ve>
     *
     * @return BinaryFileResponse
     */
    public function show($filename)
    {
        $file = storage_path() . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR . $filename
            ?? 'project-tracking-report-' . Carbon::now() . '.pdf';
        return response()->download($file, $filename, [], 'inline');
    }

    /**
     * Reporte de registros personales
     *
     * @author Fabián Palmera <fapalmera@cenditel.gob.ve>
     *
     * @return \Illuminate\View\View
     */
    public function personalRegisters()
    {
        return view('projecttracking::reports.project-tracking-report-personal-registers');
    }

    private function getRecords($request)
    {
        // Validar los datos ingresados
        $empty = ['records' => ''];
        if (!$request->input('project_id') && !$request->input('subproject_id') && !$request->input('product_id')) {
            $validator = Validator::make(
                $empty,
                ['records' => 'required'],
                ['records.required' => 'Se debe de escoger al menos un proyecto, subproyecto o producto para generar el reporte.']
            )->validate();
        }

        if (!$request->input('workers')) {
            $validator = Validator::make(
                $empty,
                ['records' => 'required'],
                ['records.required' => 'Se debe de escoger al menos un trabajador para generar el reporte.']
            )->validate();
        }

        if ($request->input('monthly_date')) {
            if (!$request->input('month_id') || !$request->input('year_id')) {
                $validator = Validator::make(
                    $empty,
                    ['records' => 'required'],
                    ['records.required' => 'Se debe de escoger el año y el mes para generar el reporte mensual.']
                )->validate();
            }
        }

        if ($request->input('customized_date')) {
            if (!$request->input('start_date') || !$request->input('end_date')) {
                $validator = Validator::make(
                    $empty,
                    ['records' => 'required'],
                    ['records.required' => 'Se debe de escoger la fecha de inicio y fin para generar el reporte.']
                )->validate();
            }
        }

        if ($request->workers[0]['id'] === "todos") {
            if (Module::has('Payroll') && Module::isEnabled('Payroll')) {
                $payrollStaff = \Modules\Payroll\Models\PayrollStaff::all()
                    ->toBase();
            } else {
                $payrollStaff = ProjectTrackingPersonalRegister::all()->with('Position')->toBase();
            }
            $workers = $payrollStaff->toArray();
        } else {
            foreach ($request->workers as $employer) {
                if (Module::has('Payroll') && Module::isEnabled('Payroll')) {
                    $payrollStaff = \Modules\Payroll\Models\PayrollStaff::find($employer['id']);
                } else {
                    $payrollStaff = ProjectTrackingPersonalRegister::with('Position')->find($employer['id']);
                }
                $workers[] = $payrollStaff->toArray();
            }
        }

        // Fechas de inicio y fin para reporte mensual
        $month_date_start = $month_date_end = '';
        if ($request->input('month_id') && $request->input('year_id')) {
            $month_date_start = Carbon::createFromDate($request->year_id, $request->month_id, 1)->format('Y-m-d');
            $month_date_end = Carbon::createFromDate($request->year_id, $request->month_id, 1)->endOfMonth()->format('Y-m-d');
        }

        if (count($workers) == 0) {
            $empty = ['records' => ''];
            $validator = Validator::make(
                $empty,
                ['records' => 'required'],
                ['records.required' => 'No hay trabajadores en los registros.']
            )->validate();
        }
        if ($request->project_data) {
            $project = ProjectTrackingProject::find($request->project_id)->toBase()->get()->toArray()[0];
            $report_type = 'proyecto';
        } elseif ($request->subproject_data) {
            $project = ProjectTrackingSubProject::find($request->subproject_id)->toBase()->get()->toArray()[0];
            $report_type = 'subproyecto';
        } elseif ($request->product_data) {
            $project = ProjectTrackingProduct::find($request->product_id)->toBase()->get()->toArray()[0];
            $report_type = 'producto';
        }

        $tasks = [];
        $status = [];

        if ($request->input('monthly_date')) {
            $report_date_start = $month_date_start;
            $report_date_end = $month_date_end;
            $month_text = $request->input('month_text')[0]['text'];
            $report_message = 'Mes: ' . $month_text . ' ' . $request->input('year_id');
        } else {
            $report_date_start = Carbon::parse($request->input('start_date'));
            $report_date_end = Carbon::parse($request->input('date_end'));
            $report_message = Carbon::parse($request->input('start_date'))->format("d/m/Y")
                . ' hasta '
                . Carbon::parse($request->input('date_end'))->format("d/m/Y");
        }

        // Establecer cantidad de estados de las tareas en total
        $activity_statuses = ProjectTrackingActivityStatus::all();
        $count_statuses = [];
        $all_statuses = $activity_statuses->all();
        foreach ($all_statuses as $stat_name) {
             $count_statuses[$stat_name->name] = 0;
        }

        foreach ($workers as $employer) {
            $statuses = [];
            if ($request->project_data) {
                $task = ProjectTrackingTask::with(['activityStatus'])
                ->where('project_name', $project->id)
                ->where('employers_id', $employer['id'])
                ->whereBetween('start_date', [$report_date_start, $report_date_end])
                ->orwhereBetween('end_date', [$report_date_start, $report_date_end])
                ->orWhere(function ($query) use ($report_date_start, $report_date_end) {
                    return $query->whereDate('start_date', '<=', $report_date_start)
                        ->whereDate('end_date', '>=', $report_date_end);
                });
                foreach ($task->get() as $activityTask) {
                    $statuses[] = $activityTask['activityStatus']->name;
                }
            } elseif ($request->subproject_data) {
                $task = ProjectTrackingTask::with(['activityStatus'])
                ->where('subproject_name', $project->id)
                ->where('employers_id', $employer['id'])
                ->whereBetween('start_date', [$report_date_start, $report_date_end])
                ->orwhereBetween('end_date', [$report_date_start, $report_date_end])
                ->orWhere(function ($query) use ($report_date_start, $report_date_end) {
                    return $query->whereDate('start_date', '<=', $report_date_start)
                        ->whereDate('end_date', '>=', $report_date_end);
                });
                foreach ($task->get() as $activityTask) {
                    $statuses[] = $activityTask['activityStatus']->name;
                }
            } elseif ($request->product_data) {
                $task = ProjectTrackingTask::with(['activityStatus'])
                ->where('product_name', $project->id)
                ->where('employers_id', $employer['id'])
                ->whereBetween('start_date', [$report_date_start, $report_date_end])
                ->orwhereBetween('end_date', [$report_date_start, $report_date_end])
                ->orWhere(function ($query) use ($report_date_start, $report_date_end) {
                    return $query->whereDate('start_date', '<=', $report_date_start)
                        ->whereDate('end_date', '>=', $report_date_end);
                });
                foreach ($task->get() as $activityTask) {
                    $statuses[] = $activityTask['activityStatus']->name;
                }
            }
            // Validar si hay tareas del trabajador
            if (count($task->get()) == 0) {
                $empty = ['records' => ''];
                $validator = Validator::make(
                    $empty,
                    ['records' => 'required'],
                    ['records.required' => 'No hay tareas asignadas para este trabajador en la fecha estipulada: ' . $employer['first_name'] . ' ' . $employer['last_name']]
                )->validate();
            }
            $tasks[] = $task->toBase()->get()->toArray();
            $status[] = $statuses;
            foreach ($statuses as $status_data) {
                foreach ($all_statuses as $stat) {
                    if ($status_data == $stat->name) {
                        $count_statuses[$stat->name]++;
                    }
                }
            }
        }

        $records = [];
        $records['workers'] = $workers;
        $records['start_date'] = $report_date_start;
        $records['end_date'] = $report_date_end;
        $records['project'] = $project;
        $records['report_type'] = $report_type;

        return array($records, $tasks, $status, $count_statuses, $report_message);
    }
    /**
     * Obtener datos para generar gráfico de reportes
     *
     * @param     \Illuminate\Http\Request $request
     *
     * @author Fabián Palmera <fapalmera@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function getGraphData(Request $request)
    {
        // Validar y obtener datos neceistar para obtener grafica
        $count_late_tasks = 0;
        $count_tasks = 0;
        list($records, $tasks, $status, $count_statuses, $report_message) = $this->getRecords($request);
        foreach ($tasks as $employer_task) {
            foreach ($employer_task as $task) {
                if (isset($task->new_end_date)) {
                    $count_late_tasks++;
                } else {
                    $count_tasks++;
                }
            }
        }
        return response()->json(['result' => true, 'records' => ['tasks' => [$count_tasks, $count_late_tasks],  'statuses' => $count_statuses]], 200);
    }
}
