<?php

namespace Modules\CitizenService\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Payroll\Models\PayrollStaff;
use Illuminate\Contracts\Support\Renderable;
use Modules\Payroll\Models\Payroll;
use Modules\Payroll\Models\PayrollEmployment;

/**
 * @class CitizenServiceController
 * @brief Controlador de la oficina de atención al ciudadano
 *
 * Clase que gestiona el controlador de la OAC
 *
 * @author Ing. Yenifer Ramirez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceController extends Controller
{
    /**
     * Muestra el listado de las oficinas de la OAC
     *
     * @return Renderable
     */
    public function index()
    {
        return view('citizenservice::index');
    }

    /**
     * Muestra el formulario para crear una nueva oficina de la OAC
     *
     * @return Renderable
     */
    public function create()
    {
        return view('citizenservice::create');
    }

    /**
     * Almacena una nueva oficina de la OAC
     *
     * @param  Request $request Datos de la petición
     *
     * @return void
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Muestra detalles de una oficina de la OAC
     *
     * @return Renderable
     */
    public function show()
    {
        return view('citizenservice::show');
    }

    /**
     * Muestra el formulario para editar una oficina de la OAC
     *
     * @return Renderable
     */
    public function edit()
    {
        return view('citizenservice::edit');
    }

    /**
     * Actualiza información de una oficina de la OAC
     *
     * @param  Request $request datos de la petición
     *
     * @return void
     */
    public function update(Request $request)
    {
        //
    }

    /**
     * Elimina una oficina de la OAC
     *
     * @return void
     */
    public function destroy()
    {
        //
    }

    public function getPayrollStaffs()
    {
        return response()->json(
            PayrollStaff::query()
                ->select('id', 'id_number', 'first_name', 'last_name')
                ->toBase()
                ->get()
                ->map(function ($staff) {
                    $employee = PayrollEmployment::where('payroll_staff_id', $staff->id)->first();
                    $position = $employee && $employee->payrollPosition ? $employee->payrollPosition->name : '';
                    return [
                        'id' => $staff->id,
                        'text' => $staff->id_number . ' - ' . $staff->first_name . ' ' . $staff->last_name,
                        'payroll_staff' => [
                            'first_name' => $staff->first_name,
                            'last_name' => $staff->last_name
                        ],
                        'payrollPosition' => ['name' => $position],
                        'payroll_employee_id' => $employee ? $employee->id : null
                    ];
                })->prepend([
                    'id' => '',
                    'text' => 'Seleccione...'
                ])
        );
    }
}
