<?php

namespace App\Http\Controllers\Api;

use App\Exports\MovementExport;
use App\Http\Controllers\Controller;
use App\Exports\MovementsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\Movimiento\StoreMovementRequest;
use App\Http\Requests\Movimiento\UpdateMovementRequest;
use App\Http\Resources\MovementResource;
use App\Models\Movement;
use App\Pipelines\FilterByReconocido; // Ajusta si hay filtros específicos de movimientos
use App\Pipelines\FilterByAccess; // Ajusta si hay filtros específicos de movimientos
use App\Pipelines\FilterByMovement;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Gate;

class MovementController extends Controller
{
    public function index(Request $request)
{
    Gate::authorize('viewAny', Movement::class);
    $perPage = $request->input('per_page', 15);
    $search = $request->input('search');
    $idTipo = $request->input('idTipo');

    $query = app(Pipeline::class)
        ->send(Movement::with('employees')) // 👈 aquí en vez de query()
        ->through([
            new FilterByMovement($search, $idTipo),
            new FilterByReconocido($request->input('reconocido')),
            new FilterByAccess($request->input('access')),
        ])
        ->thenReturn();

    return MovementResource::collection($query->paginate($perPage));
}


    public function store(StoreMovementRequest $request)
    {
        Gate::authorize('create', Movement::class);
        $validated = $request->validated();
        $movement = Movement::create($validated);
        return response()->json([
            'message' => 'Movimiento registrado correctamente.',
            'movement' => $movement
        ]);
    }
    public function show(Movement $movement){
        Gate::authorize('view', $movement);
        return response()->json([
            'message' => 'Movimiento encontrado',
            'movement' => new MovementResource($movement)
        ]);
    }
    public function update(UpdateMovementRequest $request, Movement $movement)
    {
        Gate::authorize('update', $movement);
        $validated = $request->validated();
        $movement->update($validated);
        return response()->json([
            'message' => 'Movimiento actualizado correctamente.',
            'movement' => $movement->refresh()
        ]);
    }

    public function destroy(Movement $movement)
    {
        Gate::authorize('delete', $movement);
        if($movement->tieneRelaciones())
        {
            return response()->json([
                'state'=>false,
                'message'=> 'No se puede eliminar este movimiento porque tiene relaciones con otros registros.'
            ],400);
        }
        $movement->delete();
        return response()->json([
            'message' => 'Movimiento eliminado correctamente'
        ]);
    }

    #EXPORTACION
    public function exportExcel()
    {
        set_time_limit(0);
        return Excel::download(new MovementExport, 'Movimientos.xlsx');
    }
}
