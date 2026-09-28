<?php

namespace App\Http\Requests\Clinica;

class UpdateFichaMedicaRequest extends StoreFichaMedicaRequest
{
    // Hereda reglas y mensajes de StoreFichaMedicaRequest.
    // cod_residente ya viene del route parameter, no del form body.

    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['cod_residente']);
        return $rules;
    }
}