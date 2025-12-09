<?php

namespace App\Http\Requests\ReceptionMedia;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReceptionMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Gate en controller
    }

    public function rules(): array
    {
        return [
            'type' => ['sometimes', Rule::in(['foto', 'video', 'audio'])],
            'url'  => ['sometimes', 'string'],
        ];
    }
}
