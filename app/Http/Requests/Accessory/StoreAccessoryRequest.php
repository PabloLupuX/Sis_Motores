<?php

namespace App\Http\Requests\Accessory;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccessoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Si usas Policies, Gate::authorize ya lo controla
    }

    public function rules()
    {
        return [
            'name'  => 'required|string|max:255',
            'state' => 'boolean',
        ];
    }
}
