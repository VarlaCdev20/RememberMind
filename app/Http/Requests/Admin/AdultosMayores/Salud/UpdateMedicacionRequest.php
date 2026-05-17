<?php

namespace App\Http\Requests\Admin\AdultosMayores\Salud;

class UpdateMedicacionRequest extends StoreMedicacionRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['cod_am']);
        return $rules;
    }
}
