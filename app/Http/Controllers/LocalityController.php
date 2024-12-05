<?php

namespace App\Http\Controllers;

use App\Models\Parish;
use App\Models\Locality;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class LocalityController extends Controller
{
    protected $rules;
    protected $ruleMessages;

    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:locality.create', ['only' => ['store']]);
        $this->middleware('permission:locality.update', ['only' => ['update']]);
        $this->middleware('permission:locality.delete', ['only' => 'destroy']);
        $this->middleware('permission:locality.list', ['only' => 'index']);

        $this->rules = [
            'code' => ['required', 'max:20', 'unique:localities'],
            'name' => ['required'],
            'country_id' => ['required', 'exists:countries,id'],
            'estate_id' => ['required', 'exists:estates,id'],
            'municipality_id' => ['required', 'exists:municipalities,id'],
            'parish_id' => ['required', 'exists:parishes,id'],
        ];

        $this->ruleMessages = [
            'code.required' => 'El campo código es obligatorio.',
            'code.max' => 'El campo código no debe ser mayor a 20 carácteres.',
            'code.unique' => 'El código ya ha sido registrado.',
            'name.required' => 'El campo nombre es obligatorio.',
            'country_id.required' => 'El campo país es obligatorio.',
            'country_id.exists' => 'El país no existe.',
            'estate_id.required' => 'El campo estado es obligatorio.',
            'estate_id.exists' => 'El estado no existe.',
            'municipality_id.required' => 'El campo municipio es obligatorio.',
            'municipality_id.exists' => 'El municipio no existe.',
            'parish_id.required' => 'El campo parroquia es obligatorio.',
            'parish_id.exists' => 'La parroquia no existe.',
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): JsonResponse
    {
        $data = Locality::with('parish');

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
            'tableRef' => 'tableResults',
            'records' => $data->items()
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

        $locality = DB::transaction(function () use ($request): Locality {
            return Locality::create($request->all());
        });

        return response()->json([
            'record' => $locality,
            'message' => 'Success',
            'tableRef' => 'tableResults'
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Locality  $locality
     * @return \Illuminate\Http\Response
     */
    public function show(Locality $locality): JsonResponse
    {
        return response()->json(['record' => $locality], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Locality  $locality
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Locality $locality): JsonResponse
    {
        $this->rules['code'] = ['required', 'max:20', 'unique:localities,code,' . $locality->id];
        $this->validate($request, $this->rules, $this->ruleMessages);

        DB::transaction(function () use ($request, $locality): void {
            $locality->update($request->all());
        });

        return response()->json([
            'message' => 'Registro actualizado correctamente',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Locality  $locality
     * @return \Illuminate\Http\Response
     */
    public function destroy(Locality $locality)
    {
        $locality->delete();

        return response()->json(['record' => $locality], 200);
    }

    public function getLocalities(Request $request, Parish $parish): JsonResponse
    {
        $parish = Parish::with('localities')->find($parish->id);

        $localities = $parish->localities->map(function ($locality) {
            return [
                'id' => $locality->id,
                'text' => $locality->name
            ];
        })->toArray();
        array_unshift($localities, ['id' => '0', 'text' => 'Seleccione...']);
        return response()->json(['records' => $localities], 200);
    }

    public function getAll()
    {
        $localities = Locality::get()->map(function ($locality) {
            return [
                'id' => $locality->id,
                'text' => $locality->name
            ];
        })->toArray();
        array_unshift($localities, ['id' => '0', 'text' => 'Seleccione...']);
        return response()->json(['records' => $localities], 200);
    }
}
