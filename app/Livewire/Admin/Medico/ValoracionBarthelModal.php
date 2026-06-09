<?php

namespace App\Livewire\Admin\Medico;

use Livewire\Component;
use App\Models\AdultoMayor;
use App\Models\ValoracionFuncionalAdulto;
use Illuminate\Support\Facades\DB;

class ValoracionBarthelModal extends Component
{
    public bool    $mostrar = false;
    public ?string $cod_am  = null;
    public $adulto          = null;

    public string  $fecha_valoracion = '';

    // Barthel Index — 10 ítems
    public int $alimentacion     = 0;   // 0,5,10
    public int $bano             = 0;   // 0,5
    public int $aseo_personal    = 0;   // 0,5
    public int $vestido          = 0;   // 0,5,10
    public int $control_intestinal = 0; // 0,5,10
    public int $control_vesical  = 0;   // 0,5,10
    public int $uso_retrete      = 0;   // 0,5,10
    public int $traslados        = 0;   // 0,5,10,15
    public int $deambulacion     = 0;   // 0,5,10,15
    public int $escaleras        = 0;   // 0,5,10

    // Auxiliares de movilidad
    public bool $usa_baston      = false;
    public bool $usa_andador     = false;
    public bool $usa_silla_ruedas = false;

    // Sensoriales/conductuales
    public bool $baja_vision     = false;
    public bool $baja_audicion   = false;
    public bool $dificultad_hablar = false;
    public bool $necesita_supervision = false;

    public string $nivel_dependencia = '';
    public string $riesgo_caida      = 'MODERADO';
    public string $observacion       = '';

    protected $listeners = ['abrir-valoracion-barthel' => 'abrir'];

    public function mount(): void
    {
        $this->fecha_valoracion = date('Y-m-d');
    }

    public function abrir(string $cod_am): void
    {
        $this->resetForm();
        $this->cod_am = $cod_am;
        $this->adulto = AdultoMayor::find($cod_am);
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
            $this->totalBarthel >= 91 => 'Independiente',
            $this->totalBarthel >= 61 => 'Dependencia leve',
            $this->totalBarthel >= 41 => 'Dependencia moderada',
            $this->totalBarthel >= 21 => 'Dependencia severa',
            default                   => 'Dependencia total',
        };
    }

    public function updatedAlimentacion(): void    { $this->recalcular(); }
    public function updatedBano(): void            { $this->recalcular(); }
    public function updatedAseoPersonal(): void    { $this->recalcular(); }
    public function updatedVestido(): void         { $this->recalcular(); }
    public function updatedControlIntestinal(): void { $this->recalcular(); }
    public function updatedControlVesical(): void  { $this->recalcular(); }
    public function updatedUsoRetrete(): void      { $this->recalcular(); }
    public function updatedTraslados(): void       { $this->recalcular(); }
    public function updatedDeambulacion(): void    { $this->recalcular(); }
    public function updatedEscaleras(): void       { $this->recalcular(); }

    private function recalcular(): void
    {
        $total = $this->totalBarthel;
        $this->nivel_dependencia = $this->clasificacionBarthel;

        $this->riesgo_caida = match (true) {
            $this->usa_silla_ruedas || $total < 20 => 'ALTO',
            $total < 60 || $this->usa_andador      => 'MODERADO',
            default                                => 'BAJO',
        };
    }

    protected function rules(): array
    {
        return [
            'cod_am'              => 'required|exists:adulto_mayor,cod_am',
            'fecha_valoracion'    => 'required|date|before_or_equal:today',
            'alimentacion'        => 'required|in:0,5,10',
            'bano'                => 'required|in:0,5',
            'aseo_personal'       => 'required|in:0,5',
            'vestido'             => 'required|in:0,5,10',
            'control_intestinal'  => 'required|in:0,5,10',
            'control_vesical'     => 'required|in:0,5,10',
            'uso_retrete'         => 'required|in:0,5,10',
            'traslados'           => 'required|in:0,5,10,15',
            'deambulacion'        => 'required|in:0,5,10,15',
            'escaleras'           => 'required|in:0,5,10',
            'observacion'         => 'nullable|string|max:1000',
        ];
    }

    public function guardar(): void
    {
        $this->recalcular();
        $this->validate();

        try {
            DB::transaction(function () {
                // Marcar anterior como histórica
                ValoracionFuncionalAdulto::where('cod_am', $this->cod_am)
                    ->vigente()
                    ->update(['estado' => 'HISTORICA']);

                ValoracionFuncionalAdulto::create([
                    'cod_am'            => $this->cod_am,
                    'fecha_valoracion'  => $this->fecha_valoracion,
                    'come_solo'         => $this->alimentacion >= 10,
                    'se_bana_solo'      => $this->bano >= 5,
                    'se_viste_solo'     => $this->vestido >= 10,
                    'va_bano_solo'      => $this->uso_retrete >= 10,
                    'camina_solo'       => $this->deambulacion >= 15,
                    'usa_baston'        => $this->usa_baston,
                    'usa_andador'       => $this->usa_andador,
                    'usa_silla_ruedas'  => $this->usa_silla_ruedas,
                    'baja_vision'       => $this->baja_vision,
                    'baja_audicion'     => $this->baja_audicion,
                    'dificultad_hablar' => $this->dificultad_hablar,
                    'necesita_supervision' => $this->necesita_supervision,
                    'nivel_dependencia' => $this->nivel_dependencia,
                    'riesgo_caida'      => $this->riesgo_caida,
                    'indice_barthel'    => $this->totalBarthel,
                    'estado'            => 'VIGENTE',
                    'observacion'       => $this->observacion ?: null,
                    'registrado_por'    => auth()->user()->cod_usu,
                ]);
            });

            $this->cerrar();
            $this->dispatch('valoracion-barthel-guardada');
            $this->dispatch('swal:alert', [
                'type'    => 'success',
                'title'   => 'Barthel registrado',
                'message' => "Índice de Barthel: {$this->totalBarthel}/100 — {$this->clasificacionBarthel}.",
            ]);
        } catch (\Exception $e) {
            $this->dispatch('swal:alert', [
                'type'    => 'error',
                'title'   => 'Error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function resetForm(): void
    {
        $this->cod_am = null;
        $this->adulto = null;
        $this->fecha_valoracion = date('Y-m-d');
        $this->alimentacion = $this->bano = $this->aseo_personal = 0;
        $this->vestido = $this->control_intestinal = $this->control_vesical = 0;
        $this->uso_retrete = $this->traslados = $this->deambulacion = $this->escaleras = 0;
        $this->usa_baston = $this->usa_andador = $this->usa_silla_ruedas = false;
        $this->baja_vision = $this->baja_audicion = $this->dificultad_hablar = false;
        $this->necesita_supervision = false;
        $this->nivel_dependencia = '';
        $this->riesgo_caida = 'MODERADO';
        $this->observacion = '';
    }

    public function render()
    {
        return view('livewire.admin.medico.valoracion-barthel-modal');
    }
}
