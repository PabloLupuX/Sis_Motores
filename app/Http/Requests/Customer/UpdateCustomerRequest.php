<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo'   => 'required|string|max:255|unique:customers,codigo,' . $this->customer->id,
            'nombres'  => 'required|string|max:255',
            'alias'    => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:50',
            'state'    => 'required|boolean',
        ];
    }
}
