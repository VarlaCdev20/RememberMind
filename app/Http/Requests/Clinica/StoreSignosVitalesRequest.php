<?php

namespace App\Http\Requests\Clinica;

use App\Services\Clinica\ValidacionSignosVitalesService;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use Illuminate\Foundation\Http\FormRequest;

class StoreSignosVitalesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAny(['signos_vitales.crear', 'salud.signos.crear']) === true;
    }

    protected function prepareForValidation(): void
    {
        if ($adulto = $this->route('adulto_mayor')) {
            $this->merge(['cod_am' => $adulto->cod_am]);
        }
        if ($this->user()?->hasRole('ENFERMEROS')) {
            $this->merge(['fecha' => today()->toDateString(), 'hora' => now()->format('H:i')]);
        }
        $sis = $this->input('presion_sistolica');
        $dia = $this->input('presion_diastolica');

        if (($sis === null || $sis === '') && !empty($this->input('presion_arterial')) && str_contains($this->input('presion_arterial'), '/')) {
            $partes = explode('/', $this->input('presion_arterial'));
            if (isset($partes[0]) && is_numeric(trim($partes[0]))) {
                $sis = (int) trim($partes[0]);
            }
            if (isset($partes[1]) && is_numeric(trim($partes[1]))) {
                $dia = (int) trim($partes[1]);
            }
            $this->merge([
                'presion_sistolica' => $sis,
                'presion_diastolica' => $dia,
            ]);
        }

        // Normalizar talla a cm e IMC server-side
        $talla = $this->input('talla') !== null && $this->input('talla') !== '' ? (float) $this->input('talla') : null;
        $peso = $this->input('peso') !== null && $this->input('peso') !== '' ? (float) $this->input('peso') : null;

        if ($peso && $talla) {
            $imc = ValidacionSignosVitalesService::calcularImc($peso, $talla);
            $this->merge(['imc' => $imc]);
        }
    }

    public function rules(): array
    {
        return [
            'cod_am'                  => 'required|string|exists:adulto_mayor,cod_am',
            'fecha'                   => 'required|date|before_or_equal:today',
            'hora'                    => 'required|date_format:H:i',
            'presion_arterial'        => 'nullable|string|max:20',
            'presion_sistolica'       => 'nullable|integer|min:' . ValidacionSignosVitalesService::PAS_MIN . '|max:' . ValidacionSignosVitalesService::PAS_MAX,
            'presion_diastolica'      => 'nullable|integer|min:' . ValidacionSignosVitalesService::PAD_MIN . '|max:' . ValidacionSignosVitalesService::PAD_MAX,
            'frecuencia_cardiaca'     => 'nullable|integer|min:' . ValidacionSignosVitalesService::FC_MIN . '|max:' . ValidacionSignosVitalesService::FC_MAX,
            'frecuencia_respiratoria' => 'nullable|integer|min:' . ValidacionSignosVitalesService::FR_MIN . '|max:' . ValidacionSignosVitalesService::FR_MAX,
            'temperatura'             => 'nullable|numeric|min:' . ValidacionSignosVitalesService::TEMP_MIN . '|max:' . ValidacionSignosVitalesService::TEMP_MAX,
            'saturacion'              => 'nullable|integer|min:' . ValidacionSignosVitalesService::SPO2_MIN . '|max:' . ValidacionSignosVitalesService::SPO2_MAX,
            'glucosa'                 => 'nullable|numeric|min:' . ValidacionSignosVitalesService::GLUCOSA_MIN,
            'peso'                    => 'nullable|numeric|min:' . ValidacionSignosVitalesService::PESO_MIN . '|max:' . ValidacionSignosVitalesService::PESO_MAX,
            'talla'                   => 'nullable|numeric|min:0.5|max:' . ValidacionSignosVitalesService::TALLA_CM_MAX,
            'imc'                     => 'nullable|numeric|min:5|max:80',
            'dolor'                   => 'nullable|integer|min:' . ValidacionSignosVitalesService::DOLOR_MIN . '|max:' . ValidacionSignosVitalesService::DOLOR_MAX,
            'observacion'             => 'nullable|string|max:5000',
            'confirmar_presion_atipica' => 'nullable|boolean',
            'motivo_rectificacion' => 'nullable|string|min:10|max:2000',
        ];
    }

    public function messages(): array
    {
        return array_merge(ValidacionSignosVitalesService::mensajes(), [
            'cod_am.required'        => 'El adulto mayor es obligatorio.',
            'cod_am.exists'          => 'El adulto mayor seleccionado no existe.',
            'fecha.required'         => 'La fecha es obligatoria.',
            'fecha.before_or_equal'  => 'La fecha no puede ser futura.',
            'hora.required'          => 'La hora es obligatoria.',
            'hora.date_format'       => 'La hora debe tener formato HH:MM.',
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $sis = $this->input('presion_sistolica');
            $dia = $this->input('presion_diastolica');

            $campos = [
                $sis, $dia,
                $this->input('frecuencia_cardiaca'),
                $this->input('frecuencia_respiratoria'),
                $this->input('temperatura'),
                $this->input('saturacion'),
                $this->input('glucosa'),
                $this->input('peso'),
                $this->input('dolor'),
            ];

            ValidacionSignosVitalesService::validarIntegridadCruzada(
                $validator,
                $sis !== null && $sis !== '' ? (int) $sis : null,
                $dia !== null && $dia !== '' ? (int) $dia : null,
                $campos,
                'presion_arterial',
                'signos',
                $this->boolean('confirmar_presion_atipica')
            );

            // Verificar ámbito del enfermero
            if ($this->user() && $this->input('cod_am')) {
                try {
                    if ($this->user()->hasRole('ENFERMEROS')) {
                        app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente(
                            $this->input('cod_am'), 'signos_vitales.crear', $this->user()
                        );
                    }
                } catch (\Throwable $e) {
                    $validator->errors()->add('cod_am', $e->getMessage());
                }
            }
        });
    }
}
