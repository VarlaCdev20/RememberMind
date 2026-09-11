<?php

namespace App\Http\Requests\Medicacion;

use App\Models\AdministracionMedicacion;
use App\Models\MedicacionAdulto;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreAdministracionMedicacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    protected function prepareForValidation(): void
    {
        if ($adulto = $this->route('adulto_mayor')) {
            $this->merge(['cod_am' => $adulto->cod_am]);
        }
        if (!$this->has('administrado')) {
            $this->merge(['administrado' => false]);
        }
    }

    public function rules(): array
    {
        $codAm = $this->input('cod_am') ?: $this->route('adulto_mayor')?->cod_am;

        return [
            'cod_med_adulto'   => [
                'required',
                'string',
                \Illuminate\Validation\Rule::exists('medicacion_adulto', 'cod_med_adulto')
                    ->where('cod_am', $codAm)
                    ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE'])
                    ->whereNull('deleted_at'),
            ],
            'cod_am'           => 'required|string|exists:adulto_mayor,cod_am',
            'fecha'            => 'required|date|before_or_equal:today',
            'hora_programada'  => 'required|date_format:H:i',
            'hora_real'        => 'required_if:administrado,1,true|nullable|date_format:H:i',
            'administrado'     => 'required|boolean',
            'motivo_omision'   => 'required_if:administrado,0,false|nullable|string|min:5|max:2000',
            'efecto_observado' => 'nullable|string|max:2000',
            'observacion'      => 'nullable|string|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'cod_med_adulto.required'    => 'La medicación es obligatoria.',
            'cod_med_adulto.exists'      => 'La medicación no existe, no pertenece al paciente o no está activa.',
            'cod_am.required'            => 'El adulto mayor es obligatorio.',
            'cod_am.exists'              => 'El adulto mayor seleccionado no existe.',
            'fecha.required'             => 'La fecha es obligatoria.',
            'fecha.before_or_equal'      => 'La fecha no puede ser futura.',
            'hora_programada.required'   => 'La hora programada es obligatoria.',
            'hora_programada.date_format'=> 'La hora programada debe tener formato HH:MM.',
            'hora_real.required_if'      => 'La hora real de administración es obligatoria cuando el medicamento es administrado.',
            'hora_real.date_format'      => 'La hora real debe tener formato HH:MM.',
            'administrado.required'      => 'Debe indicar si se administró el medicamento.',
            'motivo_omision.required_if' => 'El motivo de omisión es obligatorio y debe tener al menos 5 caracteres.',
            'motivo_omision.min'         => 'El motivo de omisión debe tener al menos 5 caracteres.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $codAm = $this->input('cod_am');
            $codMed = $this->input('cod_med_adulto');
            $fecha = $this->input('fecha');
            $horaProg = $this->input('hora_programada');

            // Verificar ámbito de enfermería
            if ($codAm && Auth::check()) {
                try {
                    app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($codAm, Auth::user());
                } catch (\Throwable $e) {
                    $validator->errors()->add('cod_am', $e->getMessage());
                }
            }

            // Prevenir doble administración de la misma dosis programada
            if ($codMed && $fecha && $horaProg) {
                $h = substr($horaProg, 0, 5);
                $duplicado = AdministracionMedicacion::where('cod_med_adulto', $codMed)
                    ->whereDate('fecha', $fecha)
                    ->where('hora_programada', 'like', '%' . $h . '%')
                    ->exists();

                if ($duplicado) {
                    $validator->errors()->add(
                        'cod_med_adulto',
                        'Ya existe un registro de administración u omisión para esta medicación en la fecha y hora programada.'
                    );
                }
            }
        });
    }
}
