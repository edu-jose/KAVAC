<?php

namespace Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Payroll\Models\PayrollClassificationParameter;

/**
 * @class PayrollClassificationParameterController
 * @brief Gestiona los procesos del controlador
 *
 * @author Juan Rosas <cenditel.gob.ve> | <juan.rosasr01@gmail.com
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollClassificationParameterController extends Controller
{
    /**
     * Muesta todos los registros de las clasificaciones de parametros globales
     *
     * @author  Juan Rosas <jrosasr@cenditel.gob.ve>
     *
     * @return array     JSON con el listado
     */
    public function getPayrollClassificationParameters(Request $request)
    {
        $exception_type_ids = is_string($request->exception_type_id) ? [$request->exception_type_id] : $request->exception_type_id;

        if (!$exception_type_ids) {
            $exception_type_ids = [];
        }

        return template_choices(PayrollClassificationParameter::class, 'name', [
            'whereIn' => [
                "key" => "payroll_exception_type_id",
                "list" => $exception_type_ids
            ]
        ], true, null);
    }
}
