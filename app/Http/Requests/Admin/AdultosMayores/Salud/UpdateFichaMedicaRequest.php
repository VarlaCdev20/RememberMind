<?php

namespace App\Http\Requests\Admin\AdultosMayores\Salud;

class UpdateFichaMedicaRequest extends StoreFichaMedicaRequest
{
    // Hereda reglas y mensajes de StoreFichaMedicaRequest.
    // cod_am ya viene del route parameter, no del form body.

    public function rules(): array
    {
        $rules = parent::rules();
        // En update, cod_am viene de la URL, no del formulario
        unset($rules['cod_am']);
        return $rules;
    }
}
