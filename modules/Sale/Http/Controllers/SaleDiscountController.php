<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Sale\Models\SaleDiscount;

/**
 * @class SaleClientsEmailController
 * @brief Controlador que gestiona los descuentos
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleDiscountController extends Controller
{
    use ValidatesRequests;

    public function __construct()
    {
        $this->middleware('permission:sale.setting.discount', ['only' => 'index']);
    }

    public function index()
    {
        return response()->json(['records' => SaleDiscount::all()], 200);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $this->saleDiscountValidate($request);

        $saleDiscount = SaleDiscount::create([
            'name' => $request->name,
            'description' => $request->description,
            'percent' => $request->percent
        ]);

        return response()->json(['record' => $saleDiscount, 'message' => 'Success'], 200);
    }

    public function saleDiscountValidate(Request $request)
    {
        $attributes = [
            'name' => 'Nombre del descuento',
            'description' => 'Descripción del descuento',
            'percent' => 'Porcentaje del descuento'
        ];

        $validation = [];
        $validation['name'] = ['required', 'max:100', 'regex:/([A-Za-z\s])\w+/u'];
        $validation['description'] = ['required', 'max:200'];
        $validation['percent'] = ['required', 'numeric', 'min:0', 'max:100'];

        // En actualización, excluir el registro actual para la validación unique
        if ($request->has('id') && $request->id) {
            $validation['name'] = ['required', 'max:100', 'regex:/([A-Za-z\s])\w+/u',
                                 'unique:sale_discounts,name,' . $request->id];
        } else {
            $validation['name'] = ['required', 'max:100', 'regex:/([A-Za-z\s])\w+/u',
                                 'unique:sale_discounts,name'];
        }

        $this->validate($request, $validation, [], $attributes);
    }

    public function show()
    {
        //
    }

    public function edit()
    {
        //
    }

    public function update(Request $request, $id)
    {
        $saleDiscount = SaleDiscount::findOrFail($id);

        $this->saleDiscountValidate($request);

        $saleDiscount->update([
            'name' => $request->name,
            'description' => $request->description,
            'percent' => $request->percent,
        ]);

        return response()->json(['message' => 'Success'], 200);
    }

    public function destroy($id)
    {
        $saleDiscount = SaleDiscount::find($id);
        $saleDiscount->delete();
        return response()->json(['record' => $saleDiscount, 'message' => 'Success'], 200);
    }

    public function getSaleDiscount()
    {
        return response()->json(template_choices('Modules\Sale\Models\SaleDiscount', 'percent', '', true));
    }
}
