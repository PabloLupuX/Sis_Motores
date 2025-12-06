<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Engine\StoreEngineRequest;
use App\Http\Requests\Engine\UpdateEngineRequest;
use App\Http\Resources\EngineResource;
use App\Models\Engine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Exports\EnginesExport;
use Maatwebsite\Excel\Facades\Excel;
class EngineController extends Controller
{
public function index(Request $request)
{
    Gate::authorize('viewAny', Engine::class);

    $perPage = $request->input('per_page', 15);
    $search  = $request->input('search');
    $state  = $request->input('state');

    $query = Engine::query();

    if ($search) {
        $search = strtolower($search);

        $query->where(function ($q) use ($search) {
            $q->whereRaw('LOWER(hp) LIKE ?', ["%{$search}%"])
              ->orWhereRaw('LOWER(tipo) LIKE ?', ["%{$search}%"])
              ->orWhereRaw('LOWER(marca) LIKE ?', ["%{$search}%"])
              ->orWhereRaw('LOWER(modelo) LIKE ?', ["%{$search}%"])
              ->orWhere('year', 'LIKE', "%{$search}%");
        });
    }
    // Filtro por estado
    if ($state !== null && $state !== '') {
        $query->where('state', $state);
    }
    // 👇 Ordenar SIEMPRE de menor a mayor por ID
    $query->orderBy('id', 'asc');

    return EngineResource::collection($query->paginate($perPage));
}

    public function store(StoreEngineRequest $request)
    {
        Gate::authorize('create', Engine::class);

        $engine = Engine::create($request->validated());

        return response()->json([
            'state'   => true,
            'message' => 'Motor registrado correctamente.',
            'engine'  => new EngineResource($engine),
        ]);
    }

    public function show(Engine $engine)
    {
        Gate::authorize('view', $engine);

        return response()->json([
            'state'   => true,
            'message' => 'Motor encontrado',
            'engine'  => new EngineResource($engine),
        ]);
    }

    public function update(UpdateEngineRequest $request, Engine $engine)
    {
        Gate::authorize('update', $engine);

        $engine->update($request->validated());

        return response()->json([
            'state'   => true,
            'message' => 'Motor actualizado correctamente.',
            'engine'  => new EngineResource($engine->refresh()),
        ]);
    }

    public function destroy(Engine $engine)
    {
        Gate::authorize('delete', $engine);

        $engine->delete();

        return response()->json([
            'state'   => true,
            'message' => 'Motor eliminado correctamente.',
        ]);
    }

     # EXPORTACION
    public function exportExcel()
    {
        $fecha = now()->format('d-m-Y');
        $fileName = "Lista de Motores {$fecha}.xlsx";

        return Excel::download(new EnginesExport, $fileName);
    }
}
