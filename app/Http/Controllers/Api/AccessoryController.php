<?php

namespace App\Http\Controllers\Api;

use App\Exports\AccessoriesExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accessory\StoreAccessoryRequest;
use App\Http\Requests\Accessory\UpdateAccessoryRequest;
use App\Http\Resources\AccessoryResource;
use App\Models\Accessory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;

class AccessoryController extends Controller
{

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Accessory::class);

        $perPage = $request->input('per_page', 15);
        $search  = $request->input('search');
        $state   = $request->input('state');

        $query = Accessory::query();

        // Filtro de búsqueda general (case-insensitive)
        if ($search) {
            $search = strtolower($search);

            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
            });
        }

        // Filtro por estado (true / false)
        if ($state !== null && $state !== '') {
            $query->where('state', $state);
        }
    // 👇 Ordenar SIEMPRE de menor a mayor por ID
    $query->orderBy('id', 'asc');
        return AccessoryResource::collection($query->paginate($perPage));
    }

    public function store(StoreAccessoryRequest $request)
    {
        Gate::authorize('create', Accessory::class);

        $accessory = Accessory::create($request->validated());

        return response()->json([
            'state'    => true,
            'message'  => 'Accesorio registrado correctamente.',
            'accessory' => new AccessoryResource($accessory),
        ]);
    }

    public function show(Accessory $accessory)
    {
        Gate::authorize('view', $accessory);

        return response()->json([
            'state'    => true,
            'message'  => 'Accesorio encontrado.',
            'accessory' => new AccessoryResource($accessory),
        ]);
    }

    public function update(UpdateAccessoryRequest $request, Accessory $accessory)
    {
        Gate::authorize('update', $accessory);

        $accessory->update($request->validated());

        return response()->json([
            'state'    => true,
            'message'  => 'Accesorio actualizado correctamente.',
            'accessory' => new AccessoryResource($accessory->refresh()),
        ]);
    }

    public function destroy(Accessory $accessory)
    {
        Gate::authorize('delete', $accessory);

        $accessory->delete();

        return response()->json([
            'state' => true,
            'message' => 'Accesorio eliminado correctamente.',
        ]);
    }

        # EXPORTACION
    public function exportExcel()
    {
        $fecha = now()->format('d-m-Y');
        $fileName = "Lista de Accesorios {$fecha}.xlsx";

        return Excel::download(new AccessoriesExport, $fileName);
    }

}
