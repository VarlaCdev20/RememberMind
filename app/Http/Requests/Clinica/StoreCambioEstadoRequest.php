<?php

namespace App\Http\Requests\Clinica;

use Illuminate\Foundation\Http\FormRequest;

class StoreCambioEstadoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'cod_est_adul'        => 'required|string|exists:estado_adulto,cod_est_adul',
            'motivo'              => 'required|string|min:10|max:2000',
            'documento_respaldo'  => 'nullable|string|exists:documentos_adulto_mayor,cod_doc_am',
            'observacion'         => 'nullable|string|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'cod_est_adul.required' => 'El nuevo estado es obligatorio.',
            'cod_est_adul.exists'   => 'El estado seleccionado no existe en el catálogo.',
            'motivo.required'       => 'El motivo del cambio de estado es obligatorio.',
            'motivo.min'            => 'El motivo debe tener al menos 10 caracteres.',
            'motivo.max'            => 'El motivo no puede exceder 2000 caracteres.',
            'documento_respaldo.exists' => 'El documento de respaldo seleccionado no existe.',
        ];
    }
}
