<?php

namespace App\Http\Requests\Engine;

use Illuminate\Foundation\Http\FormRequest;

class StoreEngineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Gate se encarga de permisos
    }

    public function rules(): array
    {
        return [
            'hp'     => 'required|string|max:50',
            'tipo'   => 'required|string|max:100',
            'marca'  => 'required|string|max:100',
            'modelo' => 'required|string|max:150',
            'combustible'    => 'required|string|max:150',
            'state'    => 'required|boolean',
        ];
    }
}
