<?php

namespace App\Http\Requests\Horario;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha'      => 'required|date',
            'fechaInicio'=> 'required|date|after_or_equal:fecha',
            'fechaFin'   => 'required|date|after:fechaInicio',

            'idEspacio'  => 'required|exists:spaces,id',
            'idEmpleado' => 'required|exists:employees,id',
            'state' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'fecha.required'       => 'La fecha es obligatoria.',
            'fecha.date'           => 'La fecha debe tener un formato válido.',

            'fechaInicio.required' => 'La fecha de inicio es obligatoria.',
            'fechaInicio.date'     => 'La fecha de inicio debe tener un formato válido.',
            'fechaInicio.after_or_equal' => 'La fecha de inicio debe ser igual o posterior a la fecha.',

            'fechaFin.required'    => 'La fecha de fin es obligatoria.',
            'fechaFin.date'        => 'La fecha de fin debe tener un formato válido.',
            'fechaFin.after'       => 'La fecha de fin debe ser posterior a la fecha de inicio.',

            'idEspacio.required'   => 'El espacio es obligatorio.',
            'idEspacio.exists'     => 'El espacio seleccionado no es válido.',

            'idEmpleado.required'  => 'El empleado es obligatorio.',
            'idEmpleado.exists'    => 'El empleado seleccionado no es válido.',
            
            'state.required' => 'El estado es obligatorio.',
            'state.boolean' => 'El estado debe ser verdadero o falso.',
        ];
    }
}
