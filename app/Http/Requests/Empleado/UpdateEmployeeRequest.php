<?php

namespace App\Http\Requests\Empleado;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee')->id;

        return [
            'name'             => 'required|string|max:150',
            'apellido'         => 'required|string|max:150',
            'codigo'           => 'required|string|size:8|regex:/^[0-9]+$/|unique:employees,codigo,' . $employeeId,
            'employee_type_id' => 'required|exists:employee_types,id',
            'idHuella'         => 'required|integer|unique:employees,idHuella,' . $employeeId,
            'foto'             => 'nullable|image|mimes:jpg,jpeg,png|max:5120', // foto opcional
            'state'            => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'El nombre es obligatorio.',
            'name.string'       => 'El nombre debe ser una cadena de texto.',
            'name.max'          => 'El nombre no debe exceder los 150 caracteres.',

            'apellido.required' => 'El apellido es obligatorio.',
            'apellido.string'   => 'El apellido debe ser una cadena de texto.',
            'apellido.max'      => 'El apellido no debe exceder los 150 caracteres.',

            'codigo.required'   => 'El código es obligatorio.',
            'codigo.string'     => 'El código debe ser una cadena de texto.',
            'codigo.size'       => 'El código debe tener exactamente 8 caracteres.',
            'codigo.regex'      => 'El código debe contener solo números.',
            'codigo.unique'     => 'El código ya está en uso.',

            'employee_type_id.required' => 'El tipo de empleado es obligatorio.',
            'employee_type_id.exists'   => 'El tipo de empleado seleccionado no es válido.',

            'idHuella.required' => 'El ID de huella es obligatorio.',
            'idHuella.integer'  => 'El ID de huella debe ser un número entero.',
            'idHuella.unique'   => 'El ID de huella ya está en uso.',

            'foto.image'        => 'La foto debe ser una imagen.',
            'foto.mimes'        => 'La foto debe estar en formato jpg, jpeg o png.',
            'foto.max'          => 'La foto no debe exceder los 5 MB.',

            'state.required'    => 'El estado es obligatorio.',
            'state.boolean'     => 'El estado debe ser verdadero o falso.',
        ];
    }
}
