<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'fecha'       => Carbon::parse($this->fecha)->format('d-m-Y'),
            'fechaInicio' => Carbon::parse($this->fechaInicio)->format('d-m-Y H:i:s A'),
            'fechaFin'    => Carbon::parse($this->fechaFin)->format('d-m-Y H:i:s A'),
            'state'          => $this->state,
            
            'idEspacio'   => $this->idEspacio,
            'espacio'     => $this->espacio->name, // suponiendo relación espacio()

            'idEmpleado'  => $this->idEmpleado,
            'empleado'    => $this->empleado->name . ' ' . $this->empleado->apellido, // suponiendo relación empleado()

            'creacion'    => Carbon::parse($this->created_at)->format('d-m-Y H:i:s A'),
            'actualizacion'=> Carbon::parse($this->updated_at)->format('d-m-Y H:i:s A'),
        ];
    }
}
