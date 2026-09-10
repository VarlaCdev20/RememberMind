<?php

namespace App\Http\Requests\Clinica;

use Illuminate\Foundation\Http\FormRequest;

class StoreFichaMedicaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'cod_am'                    => 'required|string|exists:adulto_mayor,cod_am',
            'hipertension'              => 'nullable|boolean',
            'diabetes'                  => 'nullable|boolean',
            'problemas_cardiacos'       => 'nullable|boolean',
            'acv'                       => 'nullable|boolean',
            'parkinson'                 => 'nullable|boolean',
            'epilepsia'                 => 'nullable|boolean',
            'alzheimer_diagnosticado'   => 'nullable|boolean',
            'depresion'                 => 'nullable|boolean',
            'ansiedad'                  => 'nullable|boolean',
            'problemas_sueno'           => 'nullable|boolean',
            'problemas_visuales'        => 'nullable|boolean',
            'problemas_auditivos'       => 'nullable|boolean',
            'dolor_cronico'             => 'nullable|boolean',
            'alergias'                  => 'nullable|string|max:2000',
            'restricciones_alimentarias'=> 'nullable|string|max:2000',
            'hospitalizaciones'         => 'nullable|string|max:2000',
            'cirugias'                  => 'nullable|string|max:2000',
            'observacion_medica'        => 'nullable|string|max:5000',
            'estado'                    => 'required|in:ACTIVO,ARCHIVADO,ANULADO',
        ];
    }

    public function messages(): array
    {
        return [
            'cod_am.required' => 'El adulto mayor es obligatorio.',
            'cod_am.exists'   => 'El adulto mayor seleccionado no existe.',
            'estado.required' => 'El estado de la ficha médica es obligatorio.',
            'estado.in'       => 'El estado debe ser ACTIVO, ARCHIVADO o ANULADO.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Checkbox fields: convert null to false
        $booleans = [
            'hipertension','diabetes','problemas_cardiacos','acv','parkinson',
            'epilepsia','alzheimer_diagnosticado','depresion','ansiedad',
            'problemas_sueno','problemas_visuales','problemas_auditivos','dolor_cronico',
        ];
        foreach ($booleans as $field) {
            if (!$this->has($field)) {
                $this->merge([$field => false]);
            }
        }
    }
}
