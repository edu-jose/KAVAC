<?php

namespace App\Http\Controllers;

use App\Models\Estate;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RegionController extends Controller
{
    protected $rules;
    protected $ruleMessages;

    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:region.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:region.update', ['only' => ['edit', 'update']]);
        $this->middleware('permission:region.delete', ['only' => 'destroy']);
        $this->middleware('permission:region.list', ['only' => 'index']);

        $this->rules = [
            'code' => ['required', 'max:20', 'unique:regions'],
            'name' => ['required'],
            'country_id' => ['required', 'exists:countries,id'],
            'estates' => ['required', 'array', 'min:1'],
        ];
        $this->ruleMessages = [
            'code.required' => 'El campo código es obligatorio.',
            'code.max' => 'El campo código no debe ser mayor a 20 carácteres.',
            'code.unique' => 'El código ya ha sido registrado.',
            'name.required' => 'El campo nombre es obligatorio.',
            'country_id.required' => 'El campo país es obligatorio.',
            'country_id.exists' => 'El país no existe.',
            'estates.required' => 'El campo Estados es obligatorio.',
            'estates.min' => 'Debe indicar al menos un Estado para esta región',
            'estates.array' => 'El campo Estados es inválido o no a seleccionado ningún Estado',
        ];
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): JsonResponse
    {
        $data = Region::with('country', 'estates');

        if (!empty($request->query('query')) && $request->query('query') !== "{}") {
            $data = $data->search($request->query('query'));
        }
        if ($request->orderBy) {
            $data = $data->orderByColumn($request->orderBy, $request->ascending);
        }

        $data = $data->paginate($request->limit);

        return response()->json([
            'data' => $data->items(),
            'count' => $data->total(),
            'records' => $data->items(),
            'tableRef' => 'tableResults'
        ], 200, [], env('APP_DEBUG') == true ? JSON_PRETTY_PRINT : 0);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): JsonResponse
    {
        $this->validate($request, $this->rules, $this->ruleMessages);

        $region = DB::transaction(function () use ($request): Region {
            $region = Region::create($request->all());
            $region->estates()->sync(array_column($request->estates, 'id'));
            return $region;
        });

        return response()->json(['record' => $region], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Region  $region
     * @return \Illuminate\Http\Response
     */
    public function show(Region $region): JsonResponse
    {
        return response()->json(['record' => $region], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Region  $region
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Region $region): JsonResponse
    {
        $this->rules['code'] = ['required', 'max:20', 'unique:regions,code,' . $region->id];
        $this->validate($request, $this->rules, $this->ruleMessages);

        DB::transaction(function () use ($request, $region): void {
            $region->update($request->all());
            $region->estates()->sync(array_column($request->estates, 'id'));
        });

        return response()->json([
            'record' => $region,
            'message' => 'Región actualizada exitosamente.'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Region  $region
     * @return \Illuminate\Http\Response
     */
    public function destroy(Region $region): JsonResponse
    {
        $region->delete();

        return response()->json(['record' => $region], 200);
    }

    public function getRegions(Request $request, Estate $estate): JsonResponse
    {
        $estate = Estate::with('regions')->find($estate->id);

        $regions = $estate->regions->map(function ($estate) {
            return [
                'id' => $estate->id,
                'text' => $estate->name
            ];
        })->toArray();
        array_unshift($regions, ['id' => '0', 'text' => 'Seleccione...']);
        return response()->json(['records' => $regions], 200);
    }

    public function getAll()
    {
        $regions = Region::get()->map(function ($region) {
            return [
                'id' => $region->id,
                'text' => $region->name
            ];
        })->toArray();
        array_unshift($regions, ['id' => '0', 'text' => 'Seleccione...']);
        return response()->json(['records' => $regions], 200);
    }
}
