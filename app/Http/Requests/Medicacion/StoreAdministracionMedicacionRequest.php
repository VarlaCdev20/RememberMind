<?php

namespace App\Http\Requests\Medicacion;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdministracionMedicacionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'cod_med_adulto'  => 'required|integer|exists:medicacion_adulto,cod_med_adulto',
            'cod_am'          => 'required|string|exists:adulto_mayor,cod_am',
            'fecha'           => 'required|date',
            'hora_programada' => 'required|date_format:H:i',
            'hora_real'       => 'nullable|date_format:H:i',
            'administrado'    => 'required|boolean',
            'motivo_omision'  => 'required_if:administrado,0,false|nullable|string|max:2000',
            'efecto_observado'=> 'nullable|string|max:2000',
            'observacion'     => 'nullable|string|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'cod_med_adulto.required' => 'La medicación es obligatoria.',
            'cod_med_adulto.exists'   => 'La medicación seleccionada no existe.',
            'cod_am.required'         => 'El adulto mayor es obligatorio.',
            'cod_am.exists'           => 'El adulto mayor seleccionado no existe.',
            'fecha.required'          => 'La fecha es obligatoria.',
            'hora_programada.required'=> 'La hora programada es obligatoria.',
            'hora_programada.date_format' => 'La hora programada debe tener formato HH:MM.',
            'hora_real.date_format'   => 'La hora real debe tener formato HH:MM.',
            'administrado.required'   => 'Debe indicar si se administró el medicamento.',
            'motivo_omision.required_if' => 'El motivo de omisión es obligatorio cuando el medicamento NO fue administrado.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('administrado')) {
            $this->merge(['administrado' => false]);
        }
    }
}
