<?php

namespace App\Http\Requests\Movimiento;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'idEspacio' => 'required|exists:spaces,id',
            'idTipo' => 'required|in:1,2', // 🔹 Solo acepta 1 (Cara) o 2 (Huella)
            'reconocido' => 'required|boolean',
            'access' => 'required|boolean',
            'error' => 'nullable|string|max:3',
            'fechaEnvioESP32' => 'nullable|date',
            'fechaRecepcion' => 'nullable|date',
            'fechaReconocimiento' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'idEspacio.required' => 'El espacio es obligatorio.',
            'idEspacio.exists' => 'El espacio seleccionado no es válido.',

            'idTipo.required' => 'El tipo de movimiento es obligatorio.',
            'idTipo.in' => 'El tipo de movimiento debe ser Cara (1) o Huella (2).',

            'reconocido.required' => 'El campo reconocido es obligatorio.',
            'reconocido.boolean' => 'El campo reconocido debe ser verdadero o falso.',

            'access.required' => 'El campo acceso es obligatorio.',
            'access.boolean' => 'El campo acceso debe ser verdadero o falso.',

            'error.string' => 'El campo error debe ser texto.',
            'error.max' => 'El campo error no debe exceder los 3 caracteres.',

            'fechaEnvioESP32.date' => 'La fecha de envío debe ser válida.',
            'fechaRecepcion.date' => 'La fecha de recepción debe ser válida.',
            'fechaReconocimiento.date' => 'La fecha de reconocimiento debe ser válida.',
        ];
    }
}
