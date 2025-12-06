<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Si usas Policies, Gate::authorize ya lo controla
    }

    public function rules(): array
    {
        return [
            'codigo'   => 'required|string|max:255|unique:customers,codigo',
            'nombres'  => 'required|string|max:255',
            'alias'    => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:50',
            'state'    => 'required|boolean',
        ];
    }
}
