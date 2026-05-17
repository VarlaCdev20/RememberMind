<?php

namespace App\Http\Requests\Admin\AdultosMayores;

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
            'cod_fam' => 'nullable|exists:familiares,cod_fam',
            'parentesco_vinculo' => 'required|string|max:100',
            'es_responsable' => 'required|boolean',
            'estado' => 'required|string|max:50',
            'observaciones' => 'nullable|string|max:1000',
            // Si es familiar nuevo (opcional, dependiendo de la implementación UI)
            'nombre_nuevo' => 'nullable|required_without:cod_fam|string|max:100',
            'email_nuevo' => 'nullable|required_without:cod_fam|email|unique:users,correo',
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
