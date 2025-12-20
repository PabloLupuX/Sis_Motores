<?php

namespace App\Http\Requests\Reception;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReceptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

public function rules(): array
{
    return [
        'engine_id'           => ['required', 'exists:engines,id'],
        'customer_owner_id'   => ['required', 'exists:customers,id'],
        'customer_contact_id' => ['required', 'exists:customers,id'],
        'numero_serie'        => ['required', 'string', 'max:255',Rule::unique('receptions', 'numero_serie')
        ->where(fn ($query) =>
            $query->where('customer_owner_id', $this->customer_owner_id)
        ),],

        'tipo_mantenimiento'  => ['required', Rule::in([
            'preventivo',
            'correctivo',
            'predictivo',
            'proactivo',
            'detectivo_inspeccion',
        ])],

        'problema'            => ['required', 'string', 'max:5000'],
        'fecha_ingreso'       => ['required', 'date'],
        'state'               => ['boolean'],

        // ACCESORIOS
        'accessories'   => ['nullable', 'array'],
        'accessories.*' => ['exists:accessories,id'],

        // MEDIA (opcional)
        'media'         => ['nullable', 'array'],
        'media.*.type'  => ['required_with:media', Rule::in(['foto', 'video', 'audio'])],
        'media.*.url'   => ['required_with:media', 'string'],
    ];
}

public function messages(): array
{
    return [
        'tipo_mantenimiento.required' => 'El tipo de mantenimiento es obligatorio.',
        'tipo_mantenimiento.in'       => 'El tipo de mantenimiento debe ser: Preventivo, Correctivo, Predictivo, Proactivo o Detectivo / Inspección.',

        'numero_serie.required' => 'El número de serie es obligatorio.',
        'numero_serie.string'   => 'El número de serie debe ser un texto válido.',
        'numero_serie.max'      => 'El número de serie no puede superar los 255 caracteres.',
        'numero_serie.unique' => 'Este número de serie ya está registrado para otro cliente.',

        'engine_id.required' => 'Debe seleccionar un motor.',
        'customer_owner_id.required' => 'Debe seleccionar al cliente dueño del motor.',
        'customer_contact_id.required' => 'Debe seleccionar al cliente que entrega o recoge.',
        'problema.required' => 'Debe ingresar la descripción del problema.',
        'media.*.type.in' => 'El tipo de archivo debe ser foto, video o audio.',
    ];
}

}

