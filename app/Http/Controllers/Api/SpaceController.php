<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exports\SpacesExport;
//use App\Imports\SpaceImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\Espacio\StoreSpaceRequest;
use App\Http\Requests\Espacio\UpdateSpaceRequest;
use App\Http\Resources\SpaceResource;
use App\Models\Space;
use App\Pipelines\FilterByName;
use App\Pipelines\FilterByState;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class SpaceController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Space::class);
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');

        $query = app(Pipeline::class)
            ->send(Space::query())
            ->through([
                new FilterByName($search),
                new FilterByState($request->input('state')),
            ])
            ->thenReturn();

        return SpaceResource::collection($query->paginate($perPage));
    }

    public function store(StoreSpaceRequest $request)
    {
        Gate::authorize('create', Space::class);
        $validated = $request->validated();
        $space = Space::create($validated);

        return response()->json([
            'state' => true,
            'message' => 'Espacio registrado correctamente.',
            'space' => $space,
        ]);
    }

    public function show(Space $space)
    {
        Gate::authorize('view', $space);
        return response()->json([
            'state' => true,
            'message' => 'Espacio encontrado',
            'space' => new SpaceResource($space),
        ]);
    }

    public function update(UpdateSpaceRequest $request, Space $space)
    {
        Gate::authorize('update', $space);
        $validated = $request->validated();
        $space->update($validated);

        return response()->json([
            'state' => true,
            'message' => 'Espacio actualizado correctamente.',
            'space' => $space->refresh(),
        ]);
    }

    public function destroy(Space $space)
    {
        Gate::authorize('delete', $space);
        /*if($space->tieneRelaciones())
        {
            return response()->json([
                'state'=>false,
                'message'=> 'No se puede eliminar este espacio porque tiene relaciones con otros registros.'
            ],400);
        }*///PONER AQUI LAS RELACIONES QUE EXISTEN CON OTROS MODELOS
        $space->delete();
        return response()->json([
            'state' => true,
            'message' => 'Espacio eliminado correctamente.',
        ]);
    }

    # EXPORTACION
    public function exportExcel()
    {
        set_time_limit(0);
        return Excel::download(new SpacesExport, 'Espacios.xlsx');
    }
}
