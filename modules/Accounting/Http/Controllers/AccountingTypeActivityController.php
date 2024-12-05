<?php

namespace Modules\Accounting\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Accounting\Models\AccountingTypeActivity;
use Illuminate\Support\Facades\DB;

/**
 * @class AccountingTypeActivityController
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AccountingTypeActivityController extends Controller
{
    /**
     * [descripción del método]
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function index()
    {
        $records = [
            [
            'id' => '',
            'text' => 'Seleccione...'
            ]
        ];

        $query = DB::table('accounting_type_activities')
            ->select('accounting_type_activities.id', 'accounting_type_activities.name')
            ->get();

        foreach ($query as $key => $value) {
            array_push($records, ['id' => $value->id, 'text' => $value->name]);
        }

        return response()->json(
            [
                'records' => $records
            ],
            200
        );
    }
}
