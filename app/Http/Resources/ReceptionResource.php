<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,

            // MOTOR
            'engine_id'          => $this->engine_id,
            'engine'             => $this->whenLoaded('engine'),

            // CLIENTES
            'customer_owner_id'  => $this->customer_owner_id,
            'owner'              => $this->whenLoaded('owner'),

            'customer_contact_id'=> $this->customer_contact_id,
            'contact'            => $this->whenLoaded('contact'),

            // PROBLEMA
            'problema'           => $this->problema,

            //NUMERO DE SERIE
            'numero_serie'           => $this->numero_serie,

            // FECHAS
            'fecha_ingreso'      => $this->fecha_ingreso,
            'fecha_resuelto'     => $this->fecha_resuelto,
            'fecha_entrega'      => $this->fecha_entrega,


            'tipo_mantenimiento'      => $this->tipo_mantenimiento,


            // ESTADO
            'state'              => $this->state,

            // ACCESORIOS (Many-to-Many)
            'accessories'        => AccessoryResource::collection(
                $this->whenLoaded('accessories')
            ),

            // ARCHIVOS MULTIMEDIA
            'media'              => ReceptionMediaResource::collection(
                $this->whenLoaded('media')
            ),

            // FECHAS DEL REGISTRO
            'created_at'         => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'         => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
