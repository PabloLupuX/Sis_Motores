<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlertResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Obtener los movimientos completos usando la relación
        $movimientosData = $this->movimientos()->map(function ($mov) {
            return [
                'id' => $mov->id,
                'idTipo' => $mov->idTipo,
                'tipoDescripcion' => $mov->idTipo === 1 ? 'Cara' : 'Huella',
            ];
        });

        return [
            'id' => $this->id,
            'idMovimientos' => $this->idMovimientos ?? [],
            'movimientos' => $movimientosData, // ✅ Solo los datos de la API de movimientos
            'descripcion' => $this->descripcion,
            'fecha' => $this->fecha ? $this->fecha->format('d-m-Y') : null,
            'creacion' => $this->created_at->format('d-m-Y H:i:s A'),
            'actualizacion' => $this->updated_at->format('d-m-Y H:i:s A'),
        ];
    }
}
