<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource{

    public function toArray(Request $request): array{
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'apellido'        => $this->apellido,
            'codigo'          => $this->codigo,
            'employee_type_id'=> $this->employee_type_id,
            'empleadoType'   => $this->empleadoType->name,
            'idHuella'        => $this->idHuella,
            'foto'            => $this->foto, // aquí tendrás la ruta al archivo
            'state'           => $this->state,
            'creacion'        => Carbon::parse($this->created_at)->format('d-m-Y H:i:s A'),
            'actualizacion'   => Carbon::parse($this->updated_at)->format('d-m-Y H:i:s A'),
        ];
    }
}
