<?php

namespace App\Http\Requests\Alerta;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'idMovimientos'   => 'required|array|min:1',
            'idMovimientos.*' => 'integer|exists:movimientos,id',
            'descripcion'     => 'nullable|string',
            'fecha'           => 'required|date',
        ];
    }

    public function messages(): array
    {
        return [
            'idMovimientos.required'  => 'Debes enviar al menos un movimiento.',
            'idMovimientos.array'     => 'El campo de movimientos debe ser un array.',
            'idMovimientos.min'       => 'Debes incluir al menos un movimiento.',
            'idMovimientos.*.integer' => 'Cada movimiento debe ser un número válido.',
            'idMovimientos.*.exists'  => 'Alguno de los movimientos seleccionados no existe en la base de datos.',
            'descripcion.string'      => 'La descripción debe ser texto.',
            'fecha.required'          => 'La fecha es obligatoria.',
            'fecha.date'              => 'La fecha debe ser válida.',
        ];
    }
}
