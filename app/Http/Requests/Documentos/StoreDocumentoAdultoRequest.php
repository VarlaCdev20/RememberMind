<?php

namespace App\Http\Requests\Documentos;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentoAdultoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:150',
            'tipo_documento' => 'required|string|max:100',
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png,webp,doc,docx|max:5120',
            'fecha_subida' => 'required|date',
            'observaciones' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'el nombre del documento es obligatorio.',
            'archivo.required' => 'El archivo es obligatorio.',
            'archivo.max' => 'El archivo no debe pesar más de 5MB.',
        ];
    }
}
