<?php

namespace App\Http\Requests\Residentes;

use Illuminate\Foundation\Http\FormRequest;

class StoreAtencionAdultoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalizar datos antes de validar.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'estado' => strtoupper(trim($this->estado ?? '')),
            'obs'    => $this->obs ? trim($this->obs) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'fecha'         => 'required|date|before_or_equal:today',
            'hora'          => 'required|date_format:H:i',
            'cod_tipo_aten' => 'required|exists:tipo_atenciones_adulto,cod_tipo_aten',
            'estado'        => 'required|in:PENDIENTE,REALIZADA,FINALIZADA,CANCELADA',
            'obs'           => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'fecha.required'         => 'La fecha de atención es obligatoria.',
            'fecha.date'             => 'La fecha ingresada no tiene un formato válido.',
            'fecha.before_or_equal'  => 'La fecha de atención no puede ser posterior a hoy.',
            'hora.required'          => 'La hora de atención es obligatoria.',
            'hora.date_format'       => 'La hora debe tener un formato válido (HH:MM).',
            'cod_tipo_aten.required' => 'Debe seleccionar un tipo de atención.',
            'cod_tipo_aten.exists'   => 'El tipo de atención seleccionado no es válido.',
            'estado.required'        => 'Debe seleccionar un estado para la atención.',
            'estado.in'              => 'El estado seleccionado no es válido. Use: Pendiente, Realizada, Finalizada o Cancelada.',
            'obs.max'                => 'La observación no debe superar los 1000 caracteres.',
        ];
    }
}
