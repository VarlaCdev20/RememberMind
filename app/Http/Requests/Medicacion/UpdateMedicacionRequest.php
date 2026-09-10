<?php

namespace App\Http\Requests\Medicacion;

class UpdateMedicacionRequest extends StoreMedicacionRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['cod_am']);
        return $rules;
    }
}
