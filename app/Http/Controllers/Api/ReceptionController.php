<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reception\StoreReceptionRequest;
use App\Http\Requests\Reception\UpdateReceptionRequest;
use App\Http\Resources\ReceptionResource;
use App\Models\Reception;
use App\Models\ReceptionMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReceptionController extends Controller
{
    /**
     * LISTA DE RECEPCIONES
     */
public function index(Request $request)
{
    Gate::authorize('viewAny', Reception::class);

    $perPage = $request->input('per_page', 15);
    $search  = $request->input('search');
    $state   = $request->input('state');

    $query = Reception::with(['engine', 'owner', 'contact', 'accessories', 'media']);

    // BUSCADOR
    if (!empty($search)) {
        $search = strtolower(trim($search));

        $query->where(function ($q) use ($search) {
            // Por dueño
            $q->whereHas('owner', function ($q2) use ($search) {
                $q2->whereRaw('LOWER(nombres) LIKE ?', ["%{$search}%"])
                ->orWhereRaw('LOWER(alias) LIKE ?', ["%{$search}%"]);
            })
            // Por contacto
            ->orWhereHas('contact', function ($q3) use ($search) {
                $q3->whereRaw('LOWER(nombres) LIKE ?', ["%{$search}%"])
                ->orWhereRaw('LOWER(alias) LIKE ?', ["%{$search}%"]);
            });
        });
    }

    // FILTRO ESTADO
    if ($state !== null && $state !== '') {
        $query->where('state', $state);
    }

    // === FILTRO POR RANGO DE FECHAS ===
    $fechaInicio = $request->input('fecha_inicio');
    $fechaFin    = $request->input('fecha_fin');

   if ($fechaInicio && $fechaFin) {
    $query->whereBetween('fecha_ingreso', [
        date('Y-m-d 00:00:00', strtotime($fechaInicio)),
        date('Y-m-d 23:59:59', strtotime($fechaFin))
    ]);
}


    $query->orderBy('id', 'asc');

    return ReceptionResource::collection($query->paginate($perPage));
}





    /**
     * CREAR RECEPCIÓN
     */
    public function store(StoreReceptionRequest $request)
    {
        Gate::authorize('create', Reception::class);

        // Crear recepción
        $reception = Reception::create($request->validated());

        // ============================================
        // GUARDAR ACCESORIOS (Many-to-Many)
        // ============================================
        if ($request->has('accessories')) {
            $reception->accessories()->sync($request->accessories);
        }

        // ============================================
        // SUBIR ARCHIVOS (fotos, videos, audios)
        // ============================================
        if ($request->media && is_array($request->media)) {
            foreach ($request->media as $media) {
                ReceptionMedia::create([
                    'reception_id' => $reception->id,
                    'type'         => $media['type'],
                    'url'          => $media['url'],
                ]);
            }
        }

        return response()->json([
            'state'     => true,
            'message'   => 'Recepción registrada correctamente.',
            'reception' => new ReceptionResource(
                $reception->load('media', 'engine', 'owner', 'contact', 'accessories')
            ),
        ]);
    }



    /**
     * MOSTRAR RECEPCIÓN
     */
    public function show(Reception $reception)
    {
        Gate::authorize('view', $reception);

        return response()->json([
            'state'     => true,
            'message'   => 'Recepción encontrada.',
            'reception' => new ReceptionResource(
                $reception->load('media', 'engine', 'owner', 'contact', 'accessories')
            ),
        ]);
    }



    /**
     * ACTUALIZAR RECEPCIÓN
     */
public function update(UpdateReceptionRequest $request, Reception $reception)
{
    Gate::authorize('update', $reception);

    // ============================================
    // 1. ACTUALIZAR DATOS PRINCIPALES
    // ============================================
    $reception->update($request->validated());

    // ============================================
    // 2. ACTUALIZAR ACCESORIOS
    // ============================================
    if ($request->has('accessories')) {
        $reception->accessories()->sync($request->accessories);
    }

    // ============================================
    // 3. ELIMINAR ARCHIVOS EXISTENTES
    // ============================================
    if ($request->media_delete && is_array($request->media_delete)) {
        ReceptionMedia::whereIn('id', $request->media_delete)->delete();
    }

    // ============================================
    // 4. AGREGAR NUEVOS ARCHIVOS
    // ============================================
    if ($request->media_new && is_array($request->media_new)) {
        foreach ($request->media_new as $media) {
            ReceptionMedia::create([
                'reception_id' => $reception->id,
                'type'         => $media['type'],
                'url'          => $media['url'],
            ]);
        }
    }

    // ============================================
    // 5. RETORNAR RECEPCIÓN ACTUALIZADA
    // ============================================
    return response()->json([
        'state'     => true,
        'message'   => 'Recepción actualizada correctamente.',
        'reception' => new ReceptionResource(
            $reception->load('media', 'engine', 'owner', 'contact', 'accessories')
        ),
    ]);
}




    /**
     * ELIMINAR RECEPCIÓN
     */
    public function destroy(Reception $reception)
    {
        Gate::authorize('delete', $reception);

        $reception->delete();

        return response()->json([
            'state'   => true,
            'message' => 'Recepción eliminada correctamente.',
        ]);
    }
}
