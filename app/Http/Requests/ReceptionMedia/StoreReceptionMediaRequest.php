<?php

namespace App\Http\Requests\ReceptionMedia;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReceptionMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Gate en controller
    }

    public function rules(): array
    {
        return [
            'reception_id' => ['required', 'exists:receptions,id'],
            'type' => ['required', Rule::in(['foto', 'video', 'audio'])],
            'url'  => ['required', 'string'],
        ];
    }
}
