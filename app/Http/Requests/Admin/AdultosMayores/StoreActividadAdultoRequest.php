<?php

namespace App\Http\Requests\Admin\AdultosMayores;

use Illuminate\Foundation\Http\FormRequest;

class StoreActividadAdultoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha' => 'required|date',
            'hora' => 'required',
            'cod_tipo_act' => 'required|exists:tipo_actividades_adulto,cod_tipo_act',
            'obs' => 'nullable|string|max:2000',
            'estado' => 'required|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'fecha.required' => 'La fecha es obligatoria.',
            'hora.required' => 'La hora es obligatoria.',
            'cod_tipo_act.required' => 'El tipo de actividad es obligatorio.',
        ];
    }
}
