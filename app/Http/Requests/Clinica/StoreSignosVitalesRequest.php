<?php

namespace App\Http\Requests\Clinica;

use Illuminate\Foundation\Http\FormRequest;

class StoreSignosVitalesRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'cod_am'              => 'required|string|exists:adulto_mayor,cod_am',
            'fecha'               => 'required|date',
            'hora'                => 'required|date_format:H:i',
            'presion_arterial'    => 'nullable|string|max:20',
            'frecuencia_cardiaca' => 'nullable|integer|min:20|max:220',
            'temperatura'         => 'nullable|numeric|min:30|max:45',
            'saturacion'          => 'nullable|integer|min:50|max:100',
            'glucosa'             => 'nullable|numeric|min:20|max:600',
            'peso'                => 'nullable|numeric|min:20|max:250',
            'talla'               => 'nullable|numeric|min:0.8|max:2.2',
            'imc'                 => 'nullable|numeric|min:5|max:80',
            'dolor'               => 'nullable|string|max:50',
            'observacion'         => 'nullable|string|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'cod_am.required'          => 'El adulto mayor es obligatorio.',
            'cod_am.exists'            => 'El adulto mayor seleccionado no existe.',
            'fecha.required'           => 'La fecha es obligatoria.',
            'hora.required'            => 'La hora es obligatoria.',
            'hora.date_format'         => 'La hora debe tener formato HH:MM.',
            'frecuencia_cardiaca.min'  => 'La frecuencia cardíaca debe ser al menos 20 bpm.',
            'frecuencia_cardiaca.max'  => 'La frecuencia cardíaca no puede exceder 220 bpm.',
            'temperatura.min'          => 'La temperatura debe ser al menos 30°C.',
            'temperatura.max'          => 'La temperatura no puede exceder 45°C.',
            'saturacion.min'           => 'La saturación debe ser al menos 50%.',
            'saturacion.max'           => 'La saturación no puede exceder 100%.',
            'glucosa.min'              => 'La glucosa debe ser al menos 20 mg/dL.',
            'glucosa.max'              => 'La glucosa no puede exceder 600 mg/dL.',
            'peso.min'                 => 'El peso debe ser al menos 20 kg.',
            'peso.max'                 => 'El peso no puede exceder 250 kg.',
            'talla.min'                => 'La talla debe ser al menos 0.80 m.',
            'talla.max'                => 'La talla no puede exceder 2.20 m.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $campos = ['presion_arterial','frecuencia_cardiaca','temperatura','saturacion','glucosa','peso','talla','dolor'];
            $alMenosUno = collect($campos)->contains(fn($c) => !is_null($this->input($c)) && $this->input($c) !== '');

            if (!$alMenosUno) {
                $validator->errors()->add('signos', 'Debe registrar al menos un signo vital o medición.');
            }
        });
    }
}
