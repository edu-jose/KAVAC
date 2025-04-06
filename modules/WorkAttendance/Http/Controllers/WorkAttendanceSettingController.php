<?php

namespace Modules\WorkAttendance\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * @class WorkAttendanceSettingController
 * @brief Configuración del módulo de control de acceso
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WorkAttendanceSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:workattendance.setting.index');
    }

    /**
     * Muestra la plantilla de configuración general del módulo
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    \Illuminate\View\View
     */
    public function __invoke()
    {
        return view('workattendance::settings');
    }
}
