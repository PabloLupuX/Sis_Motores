<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EngineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'hp'        => $this->hp,
            'tipo'      => $this->tipo,
            'marca'     => $this->marca,
            'modelo'    => $this->modelo,
            'combustible'       => $this->combustible,
            'state'     =>  $this->state,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
