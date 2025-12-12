<?php

namespace App\Http\Requests\Reception;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReceptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
public function rules(): array
{
    return [
        'engine_id'           => ['sometimes', 'exists:engines,id'],
        'customer_owner_id'   => ['sometimes', 'exists:customers,id'],
        'customer_contact_id' => ['sometimes', 'exists:customers,id'],
        'numero_serie'        => ['required', 'string', 'max:255'],

        'tipo_mantenimiento'  => ['sometimes', Rule::in([
            'preventivo',
            'correctivo',
            'predictivo',
            'proactivo',
            'detectivo_inspeccion',
        ])],

        'problema' => ['sometimes', 'string', 'max:5000'],

        'fecha_ingreso'  => ['sometimes', 'date'],
        'fecha_resuelto' => ['nullable', 'date'],
        'fecha_entrega'  => ['nullable', 'date'],

        'state' => ['sometimes', 'boolean'],

        // ACCESORIOS
        'accessories'   => ['sometimes', 'array'],
        'accessories.*' => ['exists:accessories,id'],

        // NUEVOS ARCHIVOS
        'media_new'         => ['nullable', 'array'],
        'media_new.*.type'  => ['required_with:media_new', Rule::in(['foto', 'video', 'audio'])],
        'media_new.*.url'   => ['required_with:media_new', 'string'],

        // IDS DE MEDIA A BORRAR
        'media_delete'   => ['nullable', 'array'],
        'media_delete.*' => ['exists:reception_media,id'],
    ];
}

public function messages(): array
{
    return [
        'tipo_mantenimiento.in' => 'El tipo de mantenimiento debe ser: Preventivo, Correctivo, Predictivo, Proactivo o Detectivo / Inspección.',

        'media_new.*.type.in' => 'El tipo de archivo debe ser foto, video o audio.',
        'media_delete.*.exists' => 'Uno de los archivos a eliminar no existe.',

        'numero_serie.required' => 'El número de serie es obligatorio.',
        'numero_serie.string'   => 'Debe ingresar un número de serie válido.',
        'numero_serie.max'      => 'El número de serie no puede superar los 255 caracteres.',
    ];
}

}
