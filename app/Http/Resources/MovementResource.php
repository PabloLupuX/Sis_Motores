<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
            $empleado = $this->employees->first(); // puede haber varios, tomamos el primero

        return [
            'id' => $this->id,
            'idEspacio' => $this->idEspacio,
            'Espacio' => $this->espacio->name, // Relación con la tabla spaces
            'idTipo' => $this->idTipo,
            'tipoDescripcion' => $this->idTipo === 1 ? 'Cara' : 'Huella', // 🔹 Traducción del idTipo
            'reconocido' => $this->reconocido,
            'access' => $this->access,
            'error' => $this->error,
            'fechaEnvioESP32' => $this->fechaEnvioESP32 
                ? Carbon::parse($this->fechaEnvioESP32)->format('d-m-Y H:i:s A') 
                : null,
            'fechaRecepcion' => $this->fechaRecepcion 
                ? Carbon::parse($this->fechaRecepcion)->format('d-m-Y H:i:s A') 
                : null,
            'fechaReconocimiento' => $this->fechaReconocimiento 
                ? Carbon::parse($this->fechaReconocimiento)->format('d-m-Y H:i:s A') 
                : null,
            'persona' => $empleado ? $empleado->name . ' ' . $empleado->apellido : null,     
            'creacion' => Carbon::parse($this->created_at)->format('d-m-Y H:i:s A'),
            'actualizacion' => Carbon::parse($this->updated_at)->format('d-m-Y H:i:s A'),
        ];
    }
}
