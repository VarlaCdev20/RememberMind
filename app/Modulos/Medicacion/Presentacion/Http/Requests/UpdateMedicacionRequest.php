<?php

namespace App\Modulos\Medicacion\Presentacion\Http\Requests;

class UpdateMedicacionRequest extends StoreMedicacionRequest
{
    public function rules(): array
    {
        return parent::rules();
    }
}
