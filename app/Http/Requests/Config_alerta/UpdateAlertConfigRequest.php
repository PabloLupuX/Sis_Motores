<?php

namespace App\Http\Requests\Config_alerta;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAlertConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'time' => 'sometimes|numeric|min:0',
            'amount' => 'sometimes|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'time.numeric' => 'El tiempo debe ser un número válido.',
            'time.min' => 'El tiempo no puede ser negativo.',

            'amount.integer' => 'La cantidad debe ser un número entero.',
            'amount.min' => 'La cantidad no puede ser negativa.',
        ];
    }
}
