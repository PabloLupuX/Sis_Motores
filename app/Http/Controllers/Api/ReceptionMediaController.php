<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReceptionMedia\StoreReceptionMediaRequest;
use App\Http\Requests\ReceptionMedia\UpdateReceptionMediaRequest;
use App\Http\Resources\ReceptionMediaResource;
use App\Models\ReceptionMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReceptionMediaController extends Controller
{
    /**
     * LISTADO DE ARCHIVOS MULTIMEDIA
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', ReceptionMedia::class);

        $perPage = $request->input('per_page', 15);
        $receptionId = $request->input('reception_id');
        $type = $request->input('type'); // foto / video / audio

        $query = ReceptionMedia::query();

        // Filtro por recepción
        if ($receptionId) {
            $query->where('reception_id', $receptionId);
        }

        // Filtro por tipo
        if ($type) {
            $query->where('type', $type);
        }

        // Ordenar por ID ascendente
        $query->orderBy('id', 'asc');

        return ReceptionMediaResource::collection($query->paginate($perPage));
    }


    /**
     * AGREGAR ARCHIVO MULTIMEDIA
     */
    public function store(StoreReceptionMediaRequest $request)
    {
        Gate::authorize('create', ReceptionMedia::class);

        $media = ReceptionMedia::create($request->validated());

        return response()->json([
            'state'  => true,
            'message' => 'Archivo multimedia registrado correctamente.',
            'media' => new ReceptionMediaResource($media),
        ]);
    }


    /**
     * MOSTRAR UN ARCHIVO MULTIMEDIA
     */
    public function show(ReceptionMedia $media)
    {
        Gate::authorize('view', $media);

        return response()->json([
            'state' => true,
            'message' => 'Archivo multimedia encontrado.',
            'media' => new ReceptionMediaResource($media),
        ]);
    }


    /**
     * ACTUALIZAR MULTIMEDIA
     */
    public function update(UpdateReceptionMediaRequest $request, ReceptionMedia $media)
    {
        Gate::authorize('update', $media);

        $media->update($request->validated());

        return response()->json([
            'state' => true,
            'message' => 'Archivo multimedia actualizado correctamente.',
            'media' => new ReceptionMediaResource($media->refresh()),
        ]);
    }


    /**
     * ELIMINAR MULTIMEDIA
     */
    public function destroy(ReceptionMedia $media)
    {
        Gate::authorize('delete', $media);

        $media->delete();

        return response()->json([
            'state' => true,
            'message' => 'Archivo multimedia eliminado correctamente.',
        ]);
    }
}
