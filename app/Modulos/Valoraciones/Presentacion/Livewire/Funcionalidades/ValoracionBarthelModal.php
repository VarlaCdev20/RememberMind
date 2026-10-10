<?php

namespace App\Modulos\Valoraciones\Presentacion\Livewire\Funcionalidades;

use App\Models\Residente;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use App\Modulos\Valoraciones\Aplicacion\Acciones\RegistrarValoracionBarthel;
use App\Modulos\Valoraciones\Dominio\Reglas\IndiceBarthel;
use Illuminate\Support\Facades\Auth;
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

        $this->adulto = Residente::find($cod_residente);
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
        return IndiceBarthel::total([
            'alimentacion' => $this->alimentacion,
            'bano' => $this->bano,
            'aseo_personal' => $this->aseo_personal,
            'vestido' => $this->vestido,
            'control_intestinal' => $this->control_intestinal,
            'control_vesical' => $this->control_vesical,
            'uso_retrete' => $this->uso_retrete,
            'escaleras' => $this->escaleras,
            'uso_retrete' => $this->uso_retrete,
            'traslados' => $this->traslados,
            'deambulacion' => $this->deambulacion,
            'escaleras' => $this->escaleras,            ]);
    }

    public function getClasificacionBarthelProperty(): string
    {
        return IndiceBarthel::clasificar($this->totalBarthel);
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
        app(TurnoEnfermeriaService::class)->autorizarMutacionEnfermeria($this->cod_residente, 'valoracion_enfermeria.registrar', Auth::user());

        $this->recalcular();
        $this->validate();

        try {
            app(RegistrarValoracionBarthel::class)->ejecutar(auth()->user(), [
                'cod_residente' => $this->cod_residente,
                'totalBarthel' => $this->totalBarthel,
                'fecha_valoracion' => $this->fecha_valoracion,
                'observacion' => $this->observacion,
                'deambulacion' => $this->deambulacion,
                'usa_baston' => $this->usa_baston,
                'usa_andador' => $this->usa_andador,
                'usa_silla_ruedas' => $this->usa_silla_ruedas,
                'traslados' => $this->traslados,
                'alimentacion' => $this->alimentacion,
                'bano' => $this->bano,
                'vestido' => $this->vestido,
                'aseo_personal' => $this->aseo_personal,
                'control_intestinal' => $this->control_intestinal,
                'control_vesical' => $this->control_vesical,
                'necesita_supervision' => $this->necesita_supervision,
                'uso_retrete' => $this->uso_retrete,
                'escaleras' => $this->escaleras,
                'nivel_dependencia' => $this->nivel_dependencia,
                'clasificacionBarthel' => $this->clasificacionBarthel,
                'riesgo_caida' => $this->riesgo_caida,            ]);

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
