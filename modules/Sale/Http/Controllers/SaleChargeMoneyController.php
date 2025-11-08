<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Sale\Models\SaleChargeMoney;

/**
 * @class SaleChargeMoneyController
 * @brief Controlador que gestiona los cargos de dinero
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleChargeMoneyController extends Controller
{
    use ValidatesRequests;

    /**
     * Lista de elementos a mostrar en select
     *
     * @var array $data
     */
    protected $data = [];

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
        $this->middleware('permission:sale.setting.charge.money', ['only' => 'index']);
        $this->middleware('permission:sale.setting.charge.store', ['only' => 'store']);
        $this->middleware('permission:sale.setting.charge.update', ['only' => 'update']);
        $this->middleware('permission:sale.setting.charge.destroy', ['only' => 'destroy']);
    }

    /**
     * Listado de registros de cargos de dinero
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $data = [];
        $records = SaleChargeMoney::all();
        foreach ($records as $record) {
            $data[] = [
                'id' => $record->id,
                'name_charge_money' => $record->name_charge_money,
                'description_charge_money' => $record->description_charge_money,
                'created_at' => $record->created_at,
                'updated_at' => $record->updated_at,
            ];
        }

        return response()->json(['records' => $data], 200);
    }

    /**
     * Muestra el formulario para crear un cargo de dinero
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('sale::create');
    }

    /**
     * Almacena un nuevo metodo de cobro
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'name_charge_money' => [
                    'required',
                    'max:200',
                    'regex:/^[a-zA-ZáéíóúÁÉÍÓÚüÜ\s]+$/u'
                ],
                'description_charge_money' => [
                    'max:200'
                ],
            ],
            [
                'name_charge_money.required' => 'El campo Nombre es obligatorio.',
                'name_charge_money.regex' => 'El campo nombre no debe contener caracteres especiales.',
            ]
        );

        $charge_money = SaleChargeMoney::create([
            'name_charge_money' => $request->name_charge_money,
            'description_charge_money' => $request->description_charge_money,
        ]);

        return response()->json(['record' => $charge_money, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de un cargo de dinero
     *
     * @param integer $id Identificador del registro
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        return view('sale::show');
    }

    /**
     * Muestra el formulario para editar un cargo de dinero
     *
     * @param integer $id Identificador del registro
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        return view('sale::edit');
    }

    /**
     * Actualiza la información de un cargo de dinero
     *
     * @param  Request $request Datos de la petición
     * @param  integer $id     Identificador del registro
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        /* Datos del metodo de cobro */
        $charge_money = SaleChargeMoney::find($id);

        $this->validate(
            $request,
            [
                'name_charge_money' => [
                    'required',
                    'max:200',
                    'regex:/^[a-zA-ZáéíóúÁÉÍÓÚüÜ\s]+$/u'
                ],
                'description_charge_money' => [
                    'max:200'
                ],
            ],
            [
                'name_charge_money.required' => 'El campo Nombre es obligatorio.',
                'name_charge_money.regex' => 'El campo Nombre no debe contener caracteres especiales.',
            ]
        );

        $charge_money->name_charge_money = $request->name_charge_money;
        $charge_money->description_charge_money = $request->description_charge_money;
        $charge_money->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina un cargo de dinero
     *
     * @param Request $request Datos de la petición
     * @param integer $id     Identificador del registro
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $charge_money = SaleChargeMoney::find($id);
        $charge_money->delete();

        return response()->json(['record' => $charge_money, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene las formas de cobro registrados
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSaleChargeMonies()
    {
        return response()->json(template_choices('Modules\Sale\Models\SaleChargeMoney', 'name_charge_money', '', true));
    }
}
