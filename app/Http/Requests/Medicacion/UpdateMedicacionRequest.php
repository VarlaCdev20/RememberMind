<?php

namespace App\Http\Requests\Medicacion;

class UpdateMedicacionRequest extends StoreMedicacionRequest
{
    public function rules(): array
    {
        return parent::rules();
    }
}