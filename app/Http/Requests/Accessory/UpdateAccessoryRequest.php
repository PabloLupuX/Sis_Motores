<?php

namespace App\Http\Requests\Accessory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccessoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Si usas Policies, Gate::authorize ya lo controla
    }

    public function rules()
    {
        return [
            'name'  => 'sometimes|string|max:255',
            'state' => 'sometimes|boolean',
        ];
    }
}
