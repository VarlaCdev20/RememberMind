<?php

namespace App\Http\Requests\Valoraciones;

use Illuminate\Foundation\Http\FormRequest;

class StoreValoracionFuncionalRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'cod_am'                => 'required|string|exists:adulto_mayor,cod_am',
            'fecha_valoracion'      => 'required|date',
            'come_solo'             => 'nullable|boolean',
            'se_bana_solo'          => 'nullable|boolean',
            'se_viste_solo'         => 'nullable|boolean',
            'va_bano_solo'          => 'nullable|boolean',
            'camina_solo'           => 'nullable|boolean',
            'usa_baston'            => 'nullable|boolean',
            'usa_andador'           => 'nullable|boolean',
            'usa_silla_ruedas'      => 'nullable|boolean',
            'baja_vision'           => 'nullable|boolean',
            'baja_audicion'         => 'nullable|boolean',
            'dificultad_hablar'     => 'nullable|boolean',
            'molestia_luz'          => 'nullable|boolean',
            'molestia_ruido'        => 'nullable|boolean',
            'se_asusta_facil'       => 'nullable|boolean',
            'necesita_supervision'  => 'nullable|boolean',
            'nivel_dependencia'     => 'required|in:INDEPENDIENTE,DEPENDENCIA_PARCIAL,ALTA_DEPENDENCIA,SUPERVISION_PERMANENTE',
            'observacion'           => 'nullable|string|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'cod_am.required'            => 'El adulto mayor es obligatorio.',
            'cod_am.exists'              => 'El adulto mayor seleccionado no existe.',
            'fecha_valoracion.required'  => 'La fecha de valoración es obligatoria.',
            'nivel_dependencia.required' => 'El nivel de dependencia es obligatorio.',
            'nivel_dependencia.in'       => 'El nivel de dependencia debe ser: INDEPENDIENTE, DEPENDENCIA_PARCIAL, ALTA_DEPENDENCIA o SUPERVISION_PERMANENTE.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $booleans = [
            'come_solo','se_bana_solo','se_viste_solo','va_bano_solo','camina_solo',
            'usa_baston','usa_andador','usa_silla_ruedas','baja_vision','baja_audicion',
            'dificultad_hablar','molestia_luz','molestia_ruido','se_asusta_facil','necesita_supervision',
        ];
        foreach ($booleans as $field) {
            if (!$this->has($field)) {
                $this->merge([$field => false]);
            }
        }
    }
}
