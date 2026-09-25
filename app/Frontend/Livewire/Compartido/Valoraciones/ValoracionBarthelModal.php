<?php

namespace App\Frontend\Livewire\Compartido\Valoraciones;

use App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService;
use App\Models\AdultoMayor;
use App\Models\Atencion;
use App\Models\ValoracionFuncional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class ValoracionBarthelModal extends Component
{
    public bool $mostrar = false;

    public ?string $cod_residente = null;

    public $adulto = null;

    public string $fecha_valoracion = '';

    // Barthel Index — 10 ítems con puntuación oficial estándar
    public int $alimentacion = 0;   // 0,5,10

    public int $bano = 0;   // 0,5

    public int $aseo_personal = 0;   // 0,5

    public int $vestido = 0;   // 0,5,10

    public int $control_intestinal = 0; // 0,5,10

    public int $control_vesical = 0;   // 0,5,10

    public int $uso_retrete = 0;   // 0,5,10

    public int $traslados = 0;   // 0,5,10,15

    public int $deambulacion = 0;   // 0,5,10,15

    public int $escaleras = 0;   // 0,5,10

    // Auxiliares de movilidad (observación clínica independiente)
    public bool $usa_baston = false;

    public bool $usa_andador = false;

    public bool $usa_silla_ruedas = false;

    // Sensoriales/conductuales
    public bool $baja_vision = false;

    public bool $baja_audicion = false;

    public bool $dificultad_hablar = false;

    public bool $necesita_supervision = false;

    public string $nivel_dependencia = '';

    public string $riesgo_caida = 'MODERADO';

    public string $observacion = '';

    protected $listeners = ['abrir-valoracion-barthel' => 'abrir'];

    public function mount(): void
    {
        $this->fecha_valoracion = today()->toDateString();
    }

    public function abrir(string $cod_residente): void
    {
        $this->resetForm();
        $this->cod_residente = $cod_residente;

        abort_unless(Auth::check(), 401);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($cod_residente, Auth::user());

        $this->adulto = AdultoMayor::find($cod_residente);
        $this->recalcular();
        $this->mostrar = true;
    }

    public function cerrar(): void
    {
        $this->mostrar = false;
        $this->resetForm();
    }

    public function getTotalBarthelProperty(): int
    {
        return $this->alimentacion + $this->bano + $this->aseo_personal
            + $this->vestido + $this->control_intestinal + $this->control_vesical
            + $this->uso_retrete + $this->traslados + $this->deambulacion + $this->escaleras;
    }

    public function getClasificacionBarthelProperty(): string
    {
        return match (true) {
            $this->totalBarthel >= 100 => 'Independiente',
            $this->totalBarthel >= 91 => 'Dependencia escasa',
            $this->totalBarthel >= 61 => 'Dependencia leve',
            $this->totalBarthel >= 41 => 'Dependencia moderada',
            $this->totalBarthel >= 21 => 'Dependencia severa',
            default => 'Dependencia total',
        };
    }

    public function updatedAlimentacion(): void
    {
        $this->recalcular();
    }

    public function updatedBano(): void
    {
        $this->recalcular();
    }

    public function updatedAseoPersonal(): void
    {
        $this->recalcular();
    }

    public function updatedVestido(): void
    {
        $this->recalcular();
    }

    public function updatedControlIntestinal(): void
    {
        $this->recalcular();
    }

    public function updatedControlVesical(): void
    {
        $this->recalcular();
    }

    public function updatedUsoRetrete(): void
    {
        $this->recalcular();
    }

    public function updatedTraslados(): void
    {
        $this->recalcular();
    }

    public function updatedDeambulacion(): void
    {
        $this->recalcular();
    }

    public function updatedEscaleras(): void
    {
        $this->recalcular();
    }

    private function recalcular(): void
    {
        // El total y la dependencia corresponden exclusivamente al instrumento Barthel
        $this->nivel_dependencia = $this->clasificacionBarthel;
        // El riesgo de caída NO se deduce artificialmente del índice Barthel
    }

    protected function rules(): array
    {
        return [
            'cod_residente' => 'required|exists:residentes,cod_residente',
            'fecha_valoracion' => 'required|date|before_or_equal:today',
            'alimentacion' => 'required|in:0,5,10',
            'bano' => 'required|in:0,5',
            'aseo_personal' => 'required|in:0,5',
            'vestido' => 'required|in:0,5,10',
            'control_intestinal' => 'required|in:0,5,10',
            'control_vesical' => 'required|in:0,5,10',
            'uso_retrete' => 'required|in:0,5,10',
            'traslados' => 'required|in:0,5,10,15',
            'deambulacion' => 'required|in:0,5,10,15',
            'escaleras' => 'required|in:0,5,10',
            'riesgo_caida' => 'required|in:BAJO,MODERADO,ALTO',
            'observacion' => 'nullable|string|max:1000',
        ];
    }

    public function guardar(): void
    {
        abort_unless(Auth::check(), 401);
        app(TurnoEnfermeriaService::class)->autorizarMutacionEnfermeria($this->cod_residente, 'valoracion_enfermeria.crear', Auth::user());

        $this->recalcular();
        $this->validate();

        try {
            DB::transaction(function () {
                // Marcar anterior como histórica
                ValoracionFuncional::where('cod_residente', $this->cod_residente)
                    ->vigente()
                    ->update(['estado' => 'HISTORICA']);

                $personal = Auth::user()?->personal;
                $codArea = $personal?->asignaciones()
                    ->whereIn('estado', ['ACTIVA', 'ACTIVO'])
                    ->latest('fecha_asignacion')
                    ->value('cod_area');
                abort_unless($personal && $codArea, 422, 'El usuario debe tener personal y área institucional activa.');

                $atencion = Atencion::query()->create([
                    'cod_atencion' => 'ATN_'.Str::upper(Str::random(10)),
                    'cod_residente' => $this->cod_residente,
                    'cod_area' => $codArea,
                    'cod_personal' => $personal->cod_personal,
                    'tipo_atencion' => 'VALORACION_BARTHEL',
                    'motivo' => "Índice de Barthel {$this->totalBarthel}/100",
                    'fecha_hora' => $this->fecha_valoracion.' '.now()->format('H:i:s'),
                    'estado' => 'FINALIZADA',
                    'observacion' => $this->observacion ?: null,
                ]);

                ValoracionFuncional::create([
                    'cod_valoracion_funcional' => 'VAF_'.Str::upper(Str::random(10)),
                    'cod_residente' => $this->cod_residente,
                    'cod_personal' => $personal->cod_personal,
                    'cod_atencion' => $atencion->cod_atencion,
                    'fecha_hora' => $this->fecha_valoracion.' '.now()->format('H:i:s'),
                    'marcha' => $this->deambulacion >= 15 ? 'INDEPENDIENTE' : 'ASISTIDA',
                    'equilibrio' => ($this->usa_baston || $this->usa_andador) ? 'CON_APOYO' : 'SIN_APOYO',
                    'traslado' => $this->usa_silla_ruedas ? 'SILLA_RUEDAS' : ($this->traslados >= 15 ? 'INDEPENDIENTE' : 'ASISTIDO'),
                    'alimentacion_autonoma' => $this->alimentacion >= 10 ? 'INDEPENDIENTE' : 'REQUIERE_APOYO',
                    'bano_autonomo' => $this->bano >= 5 ? 'INDEPENDIENTE' : 'REQUIERE_APOYO',
                    'vestido_autonomo' => $this->vestido >= 10 ? 'INDEPENDIENTE' : 'REQUIERE_APOYO',
                    'higiene_autonoma' => $this->aseo_personal >= 5 ? 'INDEPENDIENTE' : 'REQUIERE_APOYO',
                    'continencia' => ($this->control_intestinal >= 10 && $this->control_vesical >= 10) ? 'CONTINENTE' : 'REQUIERE_APOYO',
                    'movilidad_autonoma' => $this->deambulacion >= 15 ? 'INDEPENDIENTE' : 'REQUIERE_APOYO',
                    'necesita_supervision' => $this->necesita_supervision,
                    'nivel_dependencia' => $this->nivel_dependencia,
                    'conclusion' => "Barthel {$this->totalBarthel}/100 ({$this->clasificacionBarthel}). Riesgo de caída: {$this->riesgo_caida}."
                        .($this->observacion ? " {$this->observacion}" : ''),
                    'estado' => 'ACTIVA',
                ]);
            });

            $this->cerrar();
            $this->dispatch('valoracion-barthel-guardada');
            $this->dispatch('swal:alert', [
                'type' => 'success',
                'title' => 'Barthel registrado',
                'message' => "Índice de Barthel: {$this->totalBarthel}/100 — {$this->clasificacionBarthel}.",
            ]);
        } catch (\Exception $e) {
            $this->dispatch('swal:alert', [
                'type' => 'error',
                'title' => 'Error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function resetForm(): void
    {
        $this->cod_residente = null;

        $this->adulto = null;
        $this->fecha_valoracion = today()->toDateString();
        $this->alimentacion = 0;
        $this->bano = 0;
        $this->aseo_personal = 0;
        $this->vestido = 0;
        $this->control_intestinal = 0;
        $this->control_vesical = 0;
        $this->uso_retrete = 0;
        $this->traslados = 0;
        $this->deambulacion = 0;
        $this->escaleras = 0;
        $this->usa_baston = false;
        $this->usa_andador = false;
        $this->usa_silla_ruedas = false;
        $this->baja_vision = false;
        $this->baja_audicion = false;
        $this->dificultad_hablar = false;
        $this->necesita_supervision = false;
        $this->nivel_dependencia = '';
        $this->riesgo_caida = 'MODERADO';
        $this->observacion = '';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.valoraciones.valoracion-barthel-modal', [
            'totalBarthel' => $this->totalBarthel,
            'clasificacionBarthel' => $this->clasificacionBarthel,
        ]);
    }
}
