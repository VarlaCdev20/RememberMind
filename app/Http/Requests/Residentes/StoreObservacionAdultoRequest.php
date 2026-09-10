<?php

namespace App\Http\Requests\Residentes;

use Illuminate\Foundation\Http\FormRequest;

class StoreObservacionAdultoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha' => 'required|date|before_or_equal:today',
            'tipo_obs' => 'required|string|max:80',
            'descripcion' => 'required|string|min:5|max:1000',
            'cod_est_adul' => 'required|exists:estado_adulto,cod_est_adul',
        ];
    }

    public function messages(): array
    {
        return [
            'fecha.required' => 'La fecha de observación es obligatoria.',
            'fecha.date' => 'La fecha de observación no tiene un formato válido.',
            'fecha.before_or_equal' => 'La fecha no puede ser posterior a hoy.',
            'tipo_obs.required' => 'El tipo de observación es obligatorio.',
            'tipo_obs.string' => 'El tipo de observación no es válido.',
            'tipo_obs.max' => 'El tipo de observación no debe superar los 80 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.string' => 'La descripción no es válida.',
            'descripcion.min' => 'La descripción debe tener al menos 5 caracteres.',
            'descripcion.max' => 'La descripción no debe superar los 1000 caracteres.',
            'cod_est_adul.required' => 'Debe seleccionar un estado institucional válido.',
            'cod_est_adul.exists' => 'Debe seleccionar un estado institucional válido.',
        ];
    }
}
