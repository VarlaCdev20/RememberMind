<?php

namespace App\Http\Requests\Medicacion;

use App\Models\AdministracionMedicacion;
use App\Models\Prescripcion;
use App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreAdministracionMedicacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check()
            && Auth::user()->hasRole('ENFERMEROS')
            && Auth::user()->can('administraciones_medicacion.crear');
    }

    protected function prepareForValidation(): void
    {
        if ($adulto = $this->route('adulto_mayor')) {
            $codRes = $adulto->cod_residente;
            $this->merge(['cod_residente' => $codRes]);
        }
        if (!$this->has('administrado')) {
            $this->merge(['administrado' => false]);
        }
        $this->merge([
            'fecha' => today()->toDateString(),
            'hora_real' => $this->boolean('administrado') ? now()->format('H:i') : null,
        ]);
    }

    public function rules(): array
    {
        $codRes = $this->input('cod_residente') ?: $this->route('adulto_mayor')?->cod_residente;

        return [
            'cod_med_adulto'   => [
                'required',
                'string',
                \Illuminate\Validation\Rule::exists('prescripciones', 'cod_prescripcion')
                    ->where('cod_residente', $codRes)
                    ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE'])
                    ->whereNull('deleted_at'),
            ],
            'cod_residente'    => 'required|string|exists:residentes,cod_residente',
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
            'cod_residente.required'     => 'El residente es obligatorio.',
            'cod_residente.exists'       => 'El residente seleccionado no existe.',
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
            $codRes = $this->input('cod_residente');
            $codMed = $this->input('cod_med_adulto');
            $fecha = $this->input('fecha');
            $horaProg = $this->input('hora_programada');

            // Verificar ámbito de enfermería
            if ($codRes && Auth::check()) {
                try {
                    app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente(
                        $codRes, 'administraciones_medicacion.crear', Auth::user()
                    );
                } catch (\Throwable $e) {
                    $validator->errors()->add('cod_residente', $e->getMessage());
                }
            }

            // Prevenir doble administración de la misma dosis programada
            if ($codMed && $fecha && $horaProg) {
                $h = substr($horaProg, 0, 5);
                $duplicado = AdministracionMedicacion::where('cod_prescripcion', $codMed)
                    ->whereDate('fecha_hora_programada', $fecha)
                    ->whereTime('fecha_hora_programada', $h)
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
