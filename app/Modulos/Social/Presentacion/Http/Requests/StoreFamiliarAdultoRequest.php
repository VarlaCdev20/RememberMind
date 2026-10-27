<?php

namespace App\Modulos\Social\Presentacion\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFamiliarAdultoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cod_contacto' => 'nullable|exists:contactos,cod_contacto',
            'parentesco_vinculo' => 'required|string|max:40',
            'es_responsable' => 'required|boolean',
            'estado' => 'required|in:ACTIVO,INACTIVO',
            'observaciones' => 'nullable|string|max:1000',
            // Si es familiar nuevo (opcional, dependiendo de la implementación UI)
            'nombres' => 'required_without:cod_contacto|nullable|string|max:100',
            'apellido_paterno' => 'required_without:cod_contacto|nullable|string|max:80',
            'apellido_materno' => 'nullable|string|max:80',
            'correo' => 'nullable|email|max:120',
            'contacto_emergencia' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'parentesco_vinculo.required' => 'El parentesco es obligatorio.',
            'es_responsable.required' => 'Debe indicar si es responsable.',
        ];
    }
}
