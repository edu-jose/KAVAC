<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Sale\Models\SaleCustomerManagement;
use Modules\Sale\Models\SaleCustomerPhone;

/**
 * @class SaleCustomerManagementController
 * @brief Controlador que gestiona Gestión de Clientes
 *
 * @author Tsu. Miguel Narvaez mnarvaez@cenditel.gob.ve miguelnarvaez31@gmail.com
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleCustomerManagementController extends Controller
{
    public function __construct()
    {

        // Establece permisos de acceso para cada método del controlador
/*        $this->middleware('permission:sale.customermanagement.list', ['only' => ['index','vueList','show']]);
        $this->middleware('permission:sale.customermanagement.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:sale.customermanagement.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:sale.customermanagement.delete', ['only' => 'destroy']);*/
    }
    /**
     * Muestra el listado de registros
     *
     * @author Tsu. Miguel Narvaez mnarvaez@cenditel.gob.ve miguelnarvaez31@gmail.com
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('sale::customer-management.list');
    }

    /**
     * Muestra el formulario para crear un registro
     *
     * @author Tsu. Miguel Narvaez mnarvaez@cenditel.gob.ve miguelnarvaez31@gmail.com
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('sale::customer-management.create');
    }

    /**
     *  Almacena un nuevo registro
     *
     * @author Tsu. Miguel Narvaez mnarvaez@cenditel.gob.ve miguelnarvaez31@gmail.com
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'identifier_type' => 'required|in:V,E,J,G',
            'identification_number' => 'required|numeric|digits_between:1,10|unique:sale_customer_management',
            'name' => 'required|string|max:150',
            'fiscal_address' => 'required|string|max:150',
            'phones' => 'required|array|min:1',
            'phones.*.type' => 'required|in:mobile,phone,fax',
            'phones.*.area_code' => 'nullable|string|max:5',
            'phones.*.number' => 'required|string|max:15',
            'phones.*.extension' => 'nullable|string|max:10' // Validación para extensión
        ]);

        // Crear el cliente
        $customer = SaleCustomerManagement::create([
            'identifier_type' => $validated['identifier_type'],
            'identification_number' => $validated['identification_number'],
            'name' => $validated['name'],
            'fiscal_address' => $validated['fiscal_address']
        ]);

        // Crear los teléfonos
        foreach ($validated['phones'] as $phoneData) {
            SaleCustomerPhone::create([
                'sale_customer_id' => $customer->id,
                'type' => $phoneData['type'],
                'area_code' => $phoneData['area_code'] ?? '',
                'number' => $phoneData['number'],
                'extension' => $phoneData['extension'] ?? '' // Guardar la extensión
            ]);
        }
        return response()->json(['message' => 'Cliente creado exitosamente', 'redirect' => route('sale.customer-management.list')], 201);
    }


    /**
     *  Muestra el formulario para editar un descuento
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $customer = SaleCustomerManagement::with('phones')->findOrFail($id);
        return view('sale::customer-management.create', compact('customer'));
    }

    /**
     * Obtiene un cliente específico para API
     */
    public function getCustomer($id)
    {
        $customer = SaleCustomerManagement::with('phones')->findOrFail($id);
        return response()->json($customer);
    }

    /**
     * Actualiza los datoss de un cliente
     *
     * @author Tsu. Miguel Narvaez mnarvaez@cenditel.gob.ve miguelnarvaez31@gmail.com
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $customer = SaleCustomerManagement::findOrFail($id);

        $validated = $request->validate([
            'identifier_type' => 'required|in:V,E,J,G',
            'identification_number' => 'required|numeric|digits_between:1,10|unique:sale_customer_management,identification_number,' . $id,
            'name' => 'required|string|max:150',
            'fiscal_address' => 'required|string|max:150',
            'phones' => 'required|array|min:1',
            'phones.*.type' => 'required|in:mobile,phone,fax',
            'phones.*.area_code' => 'nullable|string|max:5',
            'phones.*.number' => 'required|string|max:15',
            'phones.*.extension' => 'nullable|string|max:10'
        ]);

        // Actualizar el cliente
        $customer->update([
            'identifier_type' => $validated['identifier_type'],
            'identification_number' => $validated['identification_number'],
            'name' => $validated['name'],
            'fiscal_address' => $validated['fiscal_address']
        ]);

        // Eliminar teléfonos existentes
        $customer->phones()->delete();

        // Crear los nuevos teléfonos
        foreach ($validated['phones'] as $phoneData) {
            SaleCustomerPhone::create([
                'sale_customer_id' => $customer->id,
                'type' => $phoneData['type'],
                'area_code' => $phoneData['area_code'] ?? '',
                'number' => $phoneData['number'],
                'extension' => $phoneData['extension'] ?? ''
            ]);
        }

        return response()->json(['message' => 'Success', 'redirect' => route('sale.customer-management.list')], 200);
    }

    /**
     * Elimina los datos de un cliente
     *
     * @author Tsu. Miguel Narvaez mnarvaez@cenditel.gob.ve miguelnarvaez31@gmail.com
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $customer = SaleCustomerManagement::findOrFail($id);

        // Eliminar teléfonos asociados
        $customer->phones()->delete();

        // Eliminar el cliente
        $customer->delete();

        return response()->json(['message' => 'Cliente eliminado exitosamente'], 200);
    }

    /**
     * Obtiene un listado de las Gestiónes de Clientes registradas
     *
     * @author Tsu. Miguel Narvaez mnarvaez@cenditel.gob.ve miguelnarvaez31@gmail.com
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function vueList(Request $request)
    {
        $query = SaleCustomerManagement::with('phones');

        // Si hay búsqueda
        if ($request->has('query') && !empty($request->get('query'))) {
            $search = $request->get('query');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('identification_number', 'ilike', "%{$search}%")
                  ->orWhere('fiscal_address', 'ilike', "%{$search}%");
            });
        }

        // Paginación
        $limit = $request->get('limit', 10);
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $limit;

        // Conteo total
        $count = $query->count();

        // Datos paginados
        $data = $query->offset($offset)
                     ->limit($limit)
                     ->get();

        return response()->json([
            'data' => $data,
            'count' => $count
        ], 200);
    }

    /**
     * Muestra la información detallada de un cliente
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $customer = SaleCustomerManagement::with('phones')->find($id);

        if (!$customer) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        return response()->json($customer, 200);
    }
}
