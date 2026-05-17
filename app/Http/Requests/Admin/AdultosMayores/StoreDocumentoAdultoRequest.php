<?php

namespace App\Http\Requests\Admin\AdultosMayores;

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
            'nom_doc' => 'required|string|max:150',
            'tipo_doc' => 'required|string|max:100',
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'fecha_doc' => 'required|date',
            'observaciones' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'nom_doc.required' => 'el nombre del documento es obligatorio.',
            'archivo.required' => 'El archivo es obligatorio.',
            'archivo.max' => 'El archivo no debe pesar más de 5MB.',
        ];
    }
}
