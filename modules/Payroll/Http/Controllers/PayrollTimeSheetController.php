<?php

namespace Modules\Payroll\Http\Controllers;

use App\Models\DocumentStatus;
use App\Models\Institution;
use App\Models\Profile;
use App\Notifications\System;
use App\Notifications\SystemNotification;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Payroll\Exports\PayrollTimeSheetExport;
use Modules\Payroll\Http\Resources\TimeSheetResource;
use Modules\Payroll\Imports\PayrollTimeSheetImport;
use Modules\Payroll\Models\PayrollEmployment;
use Modules\Payroll\Models\PayrollHoliday;
use Modules\Payroll\Models\PayrollSupervisedGroup;
use Modules\Payroll\Models\PayrollTimeSheet;
use Modules\Payroll\Models\PayrollTimeSheetParameter;
use Modules\Payroll\Rules\PayrollTimeSheetDataRequired;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @class PayrollTimeSheetController
 * @brief Gestiona los procesos del controlador
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollTimeSheetController extends Controller
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
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:payroll.timesheet.index', ['only' => ['index', 'vueList']]);
        $this->middleware('permission:payroll.timesheet.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.timesheet.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.timesheet.delete', ['only' => 'destroy']);
        $this->middleware('permission:payroll.timesheet.approve', ['only' => 'approve']);
        $this->middleware('permission:payroll.timesheet.reject', ['only' => 'reject']);
        $this->middleware('permission:payroll.timesheet.confirm', ['only' => 'confirm']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'payroll_supervised_group_id' => ['required'],
            'payroll_time_sheet_parameter_id' => ['required'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'after_or_equal:from_date'],
            'time_sheet_data' => [new PayrollTimeSheetDataRequired()],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'from_date.required' => 'El campo desde es obligatorio',
            'to_date.required' => 'El campo hasta es obligatorio',
            'to_date.after_or_equal' => 'El campo hasta debe ser mayor o igual que el campo desde',
            'payroll_supervised_group_id.required' => 'El campo código es obligatorio',
            'payroll_time_sheet_parameter_id.required' => 'El campo parámetros de hoja de tiempo es obligatorio',
        ];
    }

    /**
     * Listado de las hojas de tiempo
     *
     * @return    \Illuminate\View\View
     */
    public function index()
    {
        return view('payroll::time_sheets.index');
    }

    /**
     * Muestra el formulario para registrar una hoja de tiempo
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::time_sheets.create-edit');
    }

    /**
     * Almacena una nueva hoja de tiempo
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $profileUser = auth()->user()->profile;
        if ($profileUser && $profileUser->institution_id !== null) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }

        $categoryGroups = [];
        $turnsArray = array_reduce($request->parameters, function ($carry, $group) use (&$categoryGroups) {
            foreach ($group as $item) {
                $carry[$item['text']] = $item['max_value_allowed_per_time_sheet'];
                $categoryGroups["subtotal - " . $item['group'] . "-"] = $item['max'];
            }
            return $carry;
        }, []);

        $fromDate = Carbon::parse($request->from_date);
        $toDate = Carbon::parse($request->to_date);
        $daysInPeriod =  $fromDate->diffInDays($toDate) + 1;

        foreach ($request->time_sheet_data as $requestKey => $requestValue) {
            foreach ($turnsArray as $resultKey => $resultValue) {
                if (strpos($requestKey, $resultKey) === 0) {
                    if (intval($requestValue) > intval($resultValue)) {
                        return response()->json(['errors' => ['error' => ['El valor de ' . $resultKey . ' no debe ser mayor a ' . $resultValue]]], 422);
                    }
                }
            }

            if (str_contains($requestKey, 'total-') && intval($requestValue) > intval($daysInPeriod)) {
                return response()->json(['errors' => ['error' => ['El total no debe ser mayor a ' . $daysInPeriod]]], 422);
            }
        }

        if (!$request->total_for_period) {
            foreach ($request->time_sheet_data as $requestKey => $requestValue) {
                foreach ($categoryGroups as $category => $categoryMaxValue) {
                    if (strpos($requestKey, $category) === 0) {
                        if (intval($requestValue) > intval($categoryMaxValue)) {
                            return response()->json(['errors' => ['error' => ['El valor de la categoria ' . $category . ' no debe ser mayor a ' . $categoryMaxValue]]], 422);
                        }
                    }
                }
            }
        }

        $payrollLastTimeSheet = PayrollTimeSheet::query()
            ->where([
                'institution_id' => $institution->id,
                'payroll_supervised_group_id' => $request->payroll_supervised_group_id,
                'payroll_time_sheet_parameter_id' => $request->payroll_time_sheet_parameter_id
            ])
            ->orderBy('to_date')
            ?->get()
            ?->last();

        $validateRules  = $this->validateRules;
        $messages  = $this->messages;

        if (isset($payrollLastTimeSheet)) {
            $validateRules  = array_replace(
                $validateRules,
                [
                    'from_date' => ['required', 'date', 'after:' . $payrollLastTimeSheet?->to_date],
                ]
            );

            $to_date_obj = date_create_from_format('Y-m-d', $payrollLastTimeSheet->to_date);
            $to_date_formatted = date_format($to_date_obj, 'd-m-Y');

            $messages = array_merge(
                $messages,
                [
                    'from_date.after' =>
                    'Ya existe un registro para este grupo de supervisados con el periodo indicado.
                            El campo Desde debe ser mayor al ' . $to_date_formatted . '.',
                ]
            );
        }

        $this->validate($request, $validateRules, $messages);

        DB::transaction(function () use ($request) {
            $status = DocumentStatus::where('action', 'EL')->first();

            $profileUser = auth()->user()->profile;
            if ($profileUser && $profileUser->institution_id !== null) {
                $institution = Institution::find($profileUser->institution_id);
            } else {
                $institution = Institution::where('active', true)->where('default', true)->first();
            }

            $payrollTimeSheet = PayrollTimeSheet::create([
                'from_date' => $request->from_date,
                'to_date' => $request->to_date,
                'payroll_supervised_group_id' => $request->payroll_supervised_group_id,
                'payroll_time_sheet_parameter_id' => $request->payroll_time_sheet_parameter_id,
                'document_status_id' => $status->id,
                'time_sheet_data' => $request->time_sheet_data,
                'time_sheet_original_data' => $request->time_sheet_original_data,
                'time_sheet_columns' => $request->time_sheet_columns,
                'institution_id' => $institution->id
            ]);

            $supervisedGroup = PayrollSupervisedGroup::find($request->payroll_supervised_group_id);

            if ($supervisedGroup) {
                $profile = Profile::query()
                    ->with('user')
                    ->has('user')
                    ->where('employee_id', $supervisedGroup->approver_id)
                    ->first();

                if ($profile) {
                    $profile->user->notify(
                        new SystemNotification(
                            'Exito',
                            'Se ha realizado un nuevo registro de hoja de tiempo para su aprobación'
                        )
                    );

                    $profile->user->notify(
                        new System(
                            'Registro de hoja de tiempo',
                            'Talento Humano',
                            'Se ha registrado una nueva hoja de tiempo para su aprobación',
                            true
                        )
                    );
                }
            }
        });

        $request->session()->flash('message', ['type' => 'store']);
        return response()->json(['redirect' => route('payroll.time-sheet.index')], 200);
    }

    /**
     * Muestra información de una hoja de tiempo
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function show($id)
    {
        return view('payroll::show');
    }

    /**
     * Muestra el formulario para editar una hoja de tiempo
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function edit($id)
    {
        $payrollTimeSheet = PayrollTimeSheet::find($id);
        return view('payroll::time_sheets.create-edit', compact('payrollTimeSheet'));
    }

    /**
     * Actualiza la hoja de tiempo
     *
     * @param     Request    $request  Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $categoryGroups = [];
        $turnsArray = array_reduce($request->parameters, function ($carry, $group) use (&$categoryGroups) {
            foreach ($group as $item) {
                $carry[$item['text']] = $item['max_value_allowed_per_time_sheet'];
                $categoryGroups["subtotal - " . $item['group'] . "-"] = $item['max'];
            }
            return $carry;
        }, []);

        $fromDate = Carbon::parse($request->from_date);
        $toDate = Carbon::parse($request->to_date);
        $daysInPeriod =  $fromDate->diffInDays($toDate) + 1;

        foreach ($request->time_sheet_data as $requestKey => $requestValue) {
            foreach ($turnsArray as $resultKey => $resultValue) {
                if (strpos($requestKey, $resultKey) === 0) {
                    if (intval($requestValue) > intval($resultValue)) {
                        return response()->json(['errors' => ['error' => ['El valor de ' . $resultKey . ' no debe ser mayor a ' . $resultValue]]], 422);
                    }
                }
            }

            if (str_contains($requestKey, 'total-') && intval($requestValue) > intval($daysInPeriod)) {
                return response()->json(['errors' => ['error' => ['El total no debe ser mayor a ' . $daysInPeriod]]], 422);
            }
        }

        foreach ($request->time_sheet_data as $requestKey => $requestValue) {
            foreach ($categoryGroups as $category => $categoryMaxValue) {
                if (strpos($requestKey, $category) === 0) {
                    if (intval($requestValue) > intval($categoryMaxValue)) {
                        return response()->json(['errors' => ['error' => ['El valor de la categoria ' . $category . ' no debe ser mayor a ' . $categoryMaxValue]]], 422);
                    }
                }
            }
        }

        $payrollTimeSheet = PayrollTimeSheet::find($id);

        $this->validateRules['from_date'] = [
            'required',
            Rule::unique('payroll_time_sheets', 'from_date')
                ->where('to_date', $request->to_date)
                ->where('payroll_supervised_group_id', $request->payroll_supervised_group_id)
                ->where('payroll_time_sheet_parameter_id', $request->payroll_time_sheet_parameter_id)
                ->ignore($payrollTimeSheet->id)
        ];

        $this->validate($request, $this->validateRules, $this->messages);

        DB::transaction(function () use ($request, $payrollTimeSheet) {
            $payrollTimeSheet->from_date = $request->from_date;
            $payrollTimeSheet->to_date = $request->to_date;
            $payrollTimeSheet->payroll_supervised_group_id = $request->payroll_supervised_group_id;
            $payrollTimeSheet->payroll_time_sheet_parameter_id = $request->payroll_time_sheet_parameter_id;
            $payrollTimeSheet->time_sheet_data = $request->time_sheet_data;
            $payrollTimeSheet->time_sheet_original_data = $request->time_sheet_original_data;
            $payrollTimeSheet->time_sheet_columns = $request->time_sheet_columns;
            $payrollTimeSheet->save();
        });

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['redirect' => route('payroll.time-sheet.index')], 200);
    }

    /**
     * Elimina una hoja de tiempo
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $payrollTimeSheet = PayrollTimeSheet::find($id);
        $payrollTimeSheet->delete();

        return response()->json(['record' => $payrollTimeSheet, 'message' => 'Success'], 200);
    }

    /**
     * Aprueba una hoja de tiempo
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function approve(Request $request, $id)
    {
        $status = DocumentStatus::where('action', 'AP')->first();
        $payrollTimeSheet = PayrollTimeSheet::find($id);
        $payrollTimeSheet->document_status_id = $status->id;
        $payrollTimeSheet->save();

        $supervisedGroup = PayrollSupervisedGroup::find($payrollTimeSheet->payroll_supervised_group_id);

        if ($supervisedGroup) {
            $profile = Profile::query()
                ->with('user')
                ->has('user')
                ->where('employee_id', $supervisedGroup->supervisor_id)
                ->first();

            if ($profile) {
                $profile->user->notify(
                    new SystemNotification(
                        'Exito',
                        'Se ha aprobado la hoja de tiempo registrada'
                    )
                );

                $profile->user->notify(
                    new System(
                        'Hoja de tiempo',
                        'Talento Humano',
                        'Se ha aprobado su registro de hoja de tiempo del periodo ' .
                            $payrollTimeSheet->from_date . ' - ' . $payrollTimeSheet->to_date,
                        true
                    )
                );
            }
        }

        $request->session()->flash('message', [
            'type' => 'other',
            'title' => '¡Éxito!',
            'text' => 'Hoja de tiempo aprobada correctamente',
            'icon' => 'screen-ok',
            'class' => 'growl-success'
        ]);

        return response()->json(['redirect' => route('payroll.time-sheet.index')], 200);
    }

    /**
     * Rechaza una hoja de tiempo
     *
     * @param     Request    $request  Datos de la petición
     * @param     integer    $id       Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function reject(Request $request, $id)
    {
        $status = DocumentStatus::where('action', 'RE')->first();
        $payrollTimeSheet = PayrollTimeSheet::find($id);
        $payrollTimeSheet->document_status_id = $status->id;
        $payrollTimeSheet->observations .= '<br>' . $request->observation;
        $payrollTimeSheet->save();

        $supervisedGroup = PayrollSupervisedGroup::find($payrollTimeSheet->payroll_supervised_group_id);

        if ($supervisedGroup) {
            $profile = Profile::query()
                ->with('user')
                ->has('user')
                ->where('employee_id', $supervisedGroup->supervisor_id)
                ->first();

            if ($profile) {
                $profile->user->notify(
                    new SystemNotification(
                        'Exito',
                        'Se ha rechazado la hoja de tiempo registrada debido a las siguientes observaciones: ' .
                            $request->observation
                    )
                );

                $profile->user->notify(
                    new System(
                        'Hoja de tiempo',
                        'Talento Humano',
                        'Se ha rechazado su registro de hoja de tiempo del periodo ' .
                            $payrollTimeSheet->from_date . ' - ' . $payrollTimeSheet->to_date .
                            ' debido a las siguientes observaciones: ' . $request->observation,
                        true
                    )
                );
            }
        }

        $request->session()->flash('message', [
            'type' => 'other',
            'title' => '¡Éxito!',
            'text' => 'Hoja de tiempo rechazada correctamente',
            'icon' => 'screen-ok',
            'class' => 'growl-success'
        ]);

        return response()->json(['redirect' => route('payroll.time-sheet.index')], 200);
    }

    /**
     * Confirma una hoja de tiempo
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function confirm(Request $request, $id)
    {
        $status = DocumentStatus::where('action', 'CE')->first();
        $payrollTimeSheet = PayrollTimeSheet::find($id);
        $payrollTimeSheet->document_status_id = $status->id;
        $payrollTimeSheet->save();

        $supervisedGroup = PayrollSupervisedGroup::find($payrollTimeSheet->payroll_supervised_group_id);

        if ($supervisedGroup) {
            $supervisor = Profile::query()
                ->with('user')
                ->has('user')
                ->where('employee_id', $supervisedGroup->supervisor_id)
                ->first();

            $approver = Profile::query()
                ->with('user')
                ->has('user')
                ->where('employee_id', $supervisedGroup->approver_id)
                ->first();

            if ($supervisor) {
                $supervisor->user->notify(
                    new SystemNotification(
                        'Exito',
                        'Se ha confirmado el periodo ' . $payrollTimeSheet->from_date . ' a ' .
                            $payrollTimeSheet->to_date . ' de hoja de tiempo activo'
                    )
                );

                $supervisor->user->notify(
                    new System(
                        'Hoja de tiempo',
                        'Talento Humano',
                        'Se ha confirmado el periodo ' . $payrollTimeSheet->from_date . ' a ' .
                            $payrollTimeSheet->to_date . ' de hoja de tiempo activo',
                        true
                    )
                );
            }

            if ($approver) {
                $approver->user->notify(
                    new SystemNotification(
                        'Exito',
                        'Se ha confirmado el periodo ' . $payrollTimeSheet->from_date . ' a ' .
                            $payrollTimeSheet->to_date . ' de hoja de tiempo activo'
                    )
                );

                $approver->user->notify(
                    new System(
                        'Hoja de tiempo',
                        'Talento Humano',
                        'Se ha confirmado el periodo ' . $payrollTimeSheet->from_date . ' a ' .
                            $payrollTimeSheet->to_date . ' de hoja de tiempo activo',
                        true
                    )
                );
            }
        }

        $request->session()->flash('message', [
            'type' => 'other',
            'title' => '¡Éxito!',
            'text' => 'Hoja de tiempo confirmada correctamente',
            'icon' => 'screen-ok',
            'class' => 'growl-success'
        ]);

        return response()->json(['redirect' => route('payroll.time-sheet.index')], 200);
    }

    /**
     * Listado de hojas de tiempo
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function vueList(Request $request)
    {
        $user = auth()->user();
        $profileUser = $user->profile;

        $query = PayrollTimeSheet::query()
            ->with([
                'payrollTimeSheetParameters',
                'documentStatus',
                'payrollSupervisedGroup.supervisor',
                'payrollSupervisedGroup.approver'
            ]);

        if (!$user->hasRole('admin, payroll')) {
            $employment = PayrollEmployment::find($profileUser->employee_id);
            $query->whereHas('payrollSupervisedGroup', function ($q) use ($employment) {
                $q->where('supervisor_id', $employment->payroll_staff_id)
                ->orWhere('approver_id', $employment->payroll_staff_id);
            });
        }

        // Apply sorting and filtering based on v-server-table parameters
        if ($request->has('orderBy')) {
            $query = $query->sortBy($request->orderBy, $request->ascending ? 'ASC' : 'DESC');
        }

        if ($request->has('query')) {
            $search = $request->query('query');
            $query = $query->search($search);
        }

        $records = $query->paginate($request->limit ?? 10);

        return response()->json([
            'data' => TimeSheetResource::collection($records->items()),
            'count' => $records->total(),
        ], 200);
    }

    /**
     * Muestra los datos de una hoja de tiempo
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function vueInfo($id)
    {
        return response()->json([
            'record' => TimeSheetResource::make(PayrollTimeSheet::query()
                ->with([
                    'payrollTimeSheetParameters.payrollParameterTimeSheetParameters.parameter',
                    'payrollTimeSheetParameters.payrollExceptionTypeTimeSheetParameters.payrollExceptionType',
                ])
                ->find($id))
        ], 200);
    }

    /**
     * Importa la hoja de tiempo
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function import()
    {
        $rows = Excel::toCollection(new PayrollTimeSheetImport(), request()->file('file'));
        $rows = $rows[0];
        return response()->json($rows);
    }

    /**
     * Exporta la hoja de tiempo
     *
     * @param     Request    $request         Datos de la petición
     *
     * @return    BinaryFileResponse
     */
    public function export(Request $request)
    {
        return Excel::download(new PayrollTimeSheetExport($request->all()), 'payroll-time-sheet.xlsx');
    }

    /**
     * Obtiene la cantidad de días feriados y domningos dentro de un periodo
     *
     * @param     Request    $request         Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function getTimeSheetHolidaysByPeriod(Request $request)
    {
        if (!$request->from_date || !$request->to_date) {
            return response()->json([
                'maxHolidays' => [],
            ], 200);
        }

        $holidays = PayrollHoliday::query()
            ->where('date', '>=', $request->from_date)
            ->where('date', '<=', $request->to_date)
            ->count();

        // Crear un rango de fechas usando CarbonPeriod
        $period = CarbonPeriod::create($request->from_date, $request->to_date);

        // Filtrar los días que son domingos
        $sundays = $period->filter(function (Carbon $date) {
            return $date->isSunday(); // Verifica si el día es domingo
        });

        $maxHolidays = [
            'feriados' => $holidays,
            'domingos' => $sundays->count(),
        ];

        return response()->json([
            'maxHolidays' => $maxHolidays,
        ], 200);
    }
}
