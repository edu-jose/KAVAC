<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Sale\Models\SaleSettingProductAttribute;
use Modules\Sale\Models\SaleSettingProduct;

/**
 * @class SaleSettingProductController
 * @brief Gestiona los datos de configuración de productos
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleSettingProductController extends Controller
{
    use ValidatesRequests;

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
        $this->middleware('permission:sale.setting.product', ['only' => 'index']);
    }

    /**
     * Muestra todos los registros de los productos
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => SaleSettingProduct::with(['SaleSettingProductAttribute', 'saleSettingProductType'])->get()], 200);
    }

    /**
     * Valida y registra un nuevo producto
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @param  \Illuminate\Http\Request $request    Solicitud con los datos a guardar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->saleSettingProductValidate($request);
        foreach ($this->getSaleSaleSettingProductFields() as $id) {
            if ($id != 'attributes') {
                $inputs[$id] = $request->input($id);
            } else {
                $inputs[$id] = !empty($request->{$id}) ? $request->input($id) : false;
            }
        }
        $SaleSettingProduct = SaleSettingProduct::create($inputs);
        $attributes = $inputs['attributes'] ? $request->sale_setting_product_attribute : [];
        $this->createAttributes($attributes, $SaleSettingProduct->id);
        return response()->json(['record' => $SaleSettingProduct, 'message' => 'Success'], 200);
    }

    /**
     * Actualiza la información del producto
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @param     Request    $request         Datos de la petición
     * @param  integer $id                          Identificador del datos a actualizar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $SaleSettingProduct = SaleSettingProduct::with('SaleSettingProductAttribute')->find($id);

        $this->validate(
            $request,
            [
                'name' => ['required', 'unique:sale_setting_products,name,' . $SaleSettingProduct->id, 'max:60'],
                'description' => ['required'],
                'sale_setting_product_attribute.*' => ['max:100'],
                'sale_setting_product_type_id' => ['required'],
            ],
            [
                'sale_setting_product_type_id.required' => 'El campo tipo de producto es obligatorio.'
            ]
        );

        $SaleSettingProduct->name = $request->name;
        $SaleSettingProduct->description = $request->description;
        $SaleSettingProduct->sale_setting_product_type_id = $request->sale_setting_product_type_id;
        $SaleSettingProduct->attributes = $request->attributes == true ? true : false;
        $SaleSettingProduct->save();

        $attributes = SaleSettingProductAttribute::where('sale_setting_product_id', $SaleSettingProduct->id)->get();


        foreach ($attributes as $attr) {
            $attr->delete();
        }

        if ($request->sale_setting_product_attribute && !empty($request->sale_setting_product_attribute)) {
            foreach ($request->sale_setting_product_attribute as $att) {
                $attribute = SaleSettingProductAttribute::where('name', $att['name'])
                             ->where('sale_setting_product_id', $SaleSettingProduct->id)->first();
                if (is_null($attribute)) {
                    $attribute = SaleSettingProductAttribute::create([
                        'name' => $att['name'],
                        'sale_setting_product_id' => $SaleSettingProduct->id
                    ]);
                }
            }
        }

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true], 200);
    }

    /**
     * Elimina el producto
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @param  integer $id                          Identificador del dato a eliminar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $SaleSettingProduct = SaleSettingProduct::with(['SaleSettingProductAttribute', 'saleSettingProductType'])->find($id);
        $this->createAttributes([], $SaleSettingProduct->id);
        $SaleSettingProduct->delete();
        return response()->json(['record' => $SaleSettingProduct, 'message' => 'Success'], 200);
    }

    /**
     * Realiza la validación de un costo fijo
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @param     Request    $request         Datos de la petición
     *
     * @return void
     */
    public function saleSettingProductValidate(Request $request)
    {
        $validation = [];
        $validation['name'] = ['required', 'max:60', 'unique:sale_setting_products,name'];
        $validation['description'] = ['required'];
        $validation['sale_setting_product_type_id'] = ['required'];
        if (!empty($request->sale_setting_product_attribute)) {
            $validation['sale_setting_product_attribute.*'] = ['required', 'max:100'];
        }
        $this->validate($request, $validation, [
            'sale_setting_product_type_id.required' => 'El campo tipo de producto es obligatorio.'
        ]);
    }

    /**
     * Agrega atributos a un producto
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @param     array    $attributes         Arreglo con los atributos a agregar
     * @param     integer   $id        Identificador del Producto
     *
     * @return void
     */
    public function createAttributes($attributes = [], $id = 0)
    {
        if ($id) {
            SaleSettingProductAttribute::where('sale_setting_product_id', $id)->delete();
            foreach ($attributes as $att) {
                $attribute = SaleSettingProductAttribute::create([
                    'name' => $att['name'],
                    'sale_setting_product_id' => $id
                ]);
            }
        }
    }

    /**
     * Obtiene los campos de un producto
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @return array
     */
    public function getSaleSaleSettingProductFields()
    {
        return ['name', 'description', 'attributes', 'sale_setting_product_type_id'];
    }

    /**
     * Obtiene los productos registrados
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de los productos
     */
    public function getSaleSettingProduct()
    {
        return response()->json(template_choices(SaleSettingProduct::class, 'name', '', true));
    }

    /**
     * Muestra una lista de los atributos de un producto
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @param     integer   $sale_setting_product_id        Identificador del producto
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSaleSettingProductAttributes($sale_setting_product_id)
    {
        return response()->json([
            'records' => SaleSettingProductAttribute::with('SaleSettingProduct')->where(
                'sale_setting_product_id',
                $sale_setting_product_id
            )->get()
        ]);
    }
}
