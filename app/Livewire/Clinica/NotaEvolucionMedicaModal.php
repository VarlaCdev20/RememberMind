<?php

namespace App\Livewire\Clinica;

use Livewire\Component;
use App\Models\AdultoMayor;
use App\Models\Atencion;
use App\Models\NotaClinica;
use App\Models\SignoVital;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotaEvolucionMedicaModal extends Component
{
    public bool    $mostrar  = false;
    public ?string $cod_am   = null;
    public $adulto           = null;

    public string  $tipo_nota   = 'EVOLUCION';
    public string  $fecha        = '';
    public string  $hora         = '';
    public string  $subjetivo    = '';
    public string  $objetivo     = '';
    public string  $valoracion   = '';
    public string  $plan         = '';
    public string  $observaciones = '';

    // Signos vitales opcionales en la nota
    public bool    $incluirSignos  = false;
    public ?string $pa_sistolica  = null;
    public ?string $pa_diastolica = null;
    public ?string $fc            = null;
    public ?string $fr            = null;
    public ?string $temperatura   = null;
    public ?string $saturacion    = null;
    public ?string $glucosa       = null;
    public ?string $peso          = null;

    protected $listeners = ['abrir-nota-evolucion' => 'abrir'];

    public function mount(): void
    {
        $this->fecha = date('Y-m-d');
        $this->hora  = date('H:i');
    }

    public function abrir(string $cod_am): void
    {
        $this->resetForm();
        $this->cod_am  = $cod_am;
        $this->adulto  = AdultoMayor::find($cod_am);
        $this->fecha   = date('Y-m-d');
        $this->hora    = date('H:i');
        $this->mostrar = true;
    }

    public function cerrar(): void
    {
        $this->mostrar = false;
        $this->resetForm();
    }

    protected function rules(): array
    {
        $rules = [
            'cod_am'      => 'required|exists:residentes,cod_residente',
            'tipo_nota'   => 'required|in:EVOLUCION,INGRESO,EGRESO,INTERCONSULTA,URGENCIA,PROCEDIMIENTO',
            'fecha'       => 'required|date|before_or_equal:today',
            'hora'        => 'nullable|string',
            'subjetivo'   => 'nullable|string|max:2000',
            'objetivo'    => 'nullable|string|max:2000',
            'valoracion'  => 'required|string|min:10|max:3000',
            'plan'        => 'required|string|min:5|max:3000',
            'observaciones' => 'nullable|string|max:1000',
        ];

        if ($this->incluirSignos) {
            $rules['pa_sistolica']  = 'nullable|integer|min:50|max:300';
            $rules['pa_diastolica'] = 'nullable|integer|min:30|max:200';
            $rules['fc']            = 'nullable|integer|min:20|max:300';
            $rules['fr']            = 'nullable|integer|min:5|max:60';
            $rules['temperatura']   = 'nullable|numeric|min:30|max:44';
            $rules['saturacion']    = 'nullable|integer|min:50|max:100';
            $rules['glucosa']       = 'nullable|numeric|min:0|max:800';
            $rules['peso']          = 'nullable|numeric|min:10|max:300';
        }

        return $rules;
    }

    protected $messages = [
        'valoracion.required' => 'La valoración/diagnóstico es obligatoria.',
        'valoracion.min'      => 'La valoración debe tener al menos 10 caracteres.',
        'plan.required'       => 'El plan de tratamiento es obligatorio.',
        'plan.min'            => 'El plan debe tener al menos 5 caracteres.',
        'fecha.before_or_equal' => 'La fecha no puede ser futura.',
    ];

    public function guardar(): void
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $personal = auth()->user()?->personal;
                $codArea = $personal?->asignaciones()
                    ->whereIn('estado', ['ACTIVA', 'ACTIVO'])
                    ->latest('fecha_asignacion')
                    ->value('cod_area');
                abort_unless($personal && $codArea, 422, 'El usuario debe tener personal y área institucional activa.');

                $fechaHora = $this->fecha . ' ' . ($this->hora ?: now()->format('H:i'));
                $atencion = Atencion::query()->create([
                    'cod_atencion' => 'ATN_' . Str::upper(Str::random(10)),
                    'cod_residente' => $this->cod_am,
                    'cod_area' => $codArea,
                    'cod_personal' => $personal->cod_personal,
                    'tipo_atencion' => $this->tipo_nota,
                    'motivo' => $this->valoracion,
                    'fecha_hora' => $fechaHora,
                    'estado' => 'FINALIZADA',
                    'observacion' => $this->observaciones ?: null,
                ]);

                NotaClinica::query()->create([
                    'cod_nota' => 'NCL_' . Str::upper(Str::random(10)),
                    'cod_atencion' => $atencion->cod_atencion,
                    'cod_residente' => $this->cod_am,
                    'cod_personal' => $personal->cod_personal,
                    'tipo_nota' => $this->tipo_nota,
                    'contenido' => collect([
                        filled($this->subjetivo) ? "Subjetivo: {$this->subjetivo}" : null,
                        filled($this->objetivo) ? "Objetivo: {$this->objetivo}" : null,
                        "Valoración: {$this->valoracion}",
                        "Plan: {$this->plan}",
                        filled($this->observaciones) ? "Observaciones: {$this->observaciones}" : null,
                    ])->filter()->implode("\n"),
                    'fecha_hora' => $fechaHora,
                    'estado' => 'ACTIVA',
                ]);

                if ($this->incluirSignos) {
                    SignoVital::query()->create([
                        'cod_signo' => 'SGN_' . Str::upper(Str::random(10)),
                        'cod_residente' => $this->cod_am,
                        'cod_personal' => $personal->cod_personal,
                        'cod_atencion' => $atencion->cod_atencion,
                        'fecha_hora' => $fechaHora,
                        'presion_sistolica' => $this->pa_sistolica ?: null,
                        'presion_diastolica' => $this->pa_diastolica ?: null,
                        'frecuencia_cardiaca' => $this->fc ?: null,
                        'frecuencia_respiratoria' => $this->fr ?: null,
                        'temperatura' => $this->temperatura ?: null,
                        'saturacion_oxigeno' => $this->saturacion ?: null,
                        'glucemia' => $this->glucosa ?: null,
                        'estado' => 'VIGENTE',
                        'observacion' => $this->peso ? "Peso informado: {$this->peso} kg" : null,
                    ]);
                }
            });

            $this->cerrar();
            $this->dispatch('nota-evolucion-guardada');
            $this->dispatch('swal:alert', [
                'type'    => 'success',
                'title'   => 'Nota registrada',
                'message' => 'La nota de evolución ha sido guardada correctamente.',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('swal:alert', [
                'type'    => 'error',
                'title'   => 'Error',
                'message' => 'No se pudo guardar la nota: ' . $e->getMessage(),
            ]);
        }
    }

    private function resetForm(): void
    {
        $this->cod_am       = null;
        $this->adulto       = null;
        $this->tipo_nota    = 'EVOLUCION';
        $this->fecha        = date('Y-m-d');
        $this->hora         = date('H:i');
        $this->subjetivo    = '';
        $this->objetivo     = '';
        $this->valoracion   = '';
        $this->plan         = '';
        $this->observaciones = '';
        $this->incluirSignos = false;
        $this->pa_sistolica  = null;
        $this->pa_diastolica = null;
        $this->fc = $this->fr = $this->temperatura = null;
        $this->saturacion = $this->glucosa = $this->peso = null;
    }

    public function render()
    {
        return view('livewire.clinica.nota-evolucion-medica-modal');
    }
}
