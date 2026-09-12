<?php

namespace App\Livewire\Clinica;

use Livewire\Component;
use App\Models\AdultoMayor;
use App\Models\SignosVitalesAdulto;
use App\Services\Clinica\ValidacionSignosVitalesService;
use App\Services\Clinica\SignosVitalesService;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegistroSignosVitalesModal extends Component
{
    public bool    $mostrar = false;
    public ?string $cod_am  = null;
    public $adulto          = null;

    public string  $fecha    = '';
    public string  $hora     = '';
    public ?string $pa_sistolica  = null;
    public ?string $pa_diastolica = null;
    public ?string $fc            = null;
    public ?string $fr            = null;
    public ?string $temperatura   = null;
    public ?string $saturacion    = null;
    public ?string $glucosa       = null;
    public ?string $peso          = null;
    public ?string $talla         = null;
    public ?string $imc           = null;
    public ?string $dolor         = null;
    public string  $observacion   = '';
    public bool $confirmar_presion_atipica = false;

    protected $listeners = ['abrir-signos-vitales-medico' => 'abrir'];

    public function mount(): void
    {
        $this->fecha = today()->toDateString();
        $this->hora  = now()->format('H:i');
    }

    public function abrir(string $cod_am): void
    {
        abort_unless(Auth::check(), 401);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($cod_am, Auth::user());

        $this->resetForm();
        $this->cod_am = $cod_am;
        $this->adulto = AdultoMayor::find($cod_am);

        // Pre-cargar último peso/talla conocido
        $ultimo = SignosVitalesAdulto::where('cod_am', $cod_am)
            ->where('estado', 'VIGENTE')
            ->whereNotNull('peso')
            ->orderByDesc('fecha')->first();
        if ($ultimo) {
            $this->peso  = $ultimo->peso;
            $this->talla = $ultimo->talla;
            $this->imc   = $ultimo->imc;
        }

        $this->mostrar = true;
    }

    public function cerrar(): void
    {
        $this->mostrar = false;
        $this->resetForm();
    }

    public function updatedPeso(): void  { $this->calcularImc(); }
    public function updatedTalla(): void { $this->calcularImc(); }

    private function calcularImc(): void
    {
        $p = $this->peso !== null && $this->peso !== '' ? (float) $this->peso : null;
        $t = $this->talla !== null && $this->talla !== '' ? (float) $this->talla : null;
        $this->imc = ValidacionSignosVitalesService::calcularImc($p, $t);
    }





    public function guardar(): void
    {
        try {
            app(SignosVitalesService::class)->registrar($this->cod_am, [
                'fecha' => $this->fecha, 'hora' => $this->hora,
                'presion_sistolica' => $this->pa_sistolica, 'presion_diastolica' => $this->pa_diastolica,
                'frecuencia_cardiaca' => $this->fc, 'frecuencia_respiratoria' => $this->fr,
                'temperatura' => $this->temperatura, 'saturacion' => $this->saturacion, 'glucosa' => $this->glucosa,
                'peso' => $this->peso, 'talla' => $this->talla, 'dolor' => $this->dolor,
                'valor_atipico_confirmado' => $this->confirmar_presion_atipica, 'observacion' => $this->observacion,
            ], Auth::user());

            $this->cerrar();
            $this->dispatch('signos-guardados');
            $this->dispatch('swal', [
                'icon'  => 'success',
                'title' => 'Signos vitales registrados',
                'text'  => 'Control guardado correctamente.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $campos = [
                'presion_arterial' => 'pa_sistolica',
                'presion_sistolica' => 'pa_sistolica',
                'presion_diastolica' => 'pa_sistolica',
                'frecuencia_cardiaca' => 'fc',
                'frecuencia_respiratoria' => 'fr',
            ];
            foreach ($e->errors() as $campo => $mensajes) {
                foreach ($mensajes as $mensaje) {
                    $this->addError($campos[$campo] ?? $campo, $mensaje);
                }
            }
            return;
        } catch (\Exception $e) {
            $this->addError('general', $e->getMessage());
        }
    }

    private function resetForm(): void
    {
        $this->cod_am        = null;
        $this->adulto        = null;
        $this->fecha         = today()->toDateString();
        $this->hora          = now()->format('H:i');
        $this->pa_sistolica  = null;
        $this->pa_diastolica = null;
        $this->fc            = null;
        $this->fr            = null;
        $this->temperatura   = null;
        $this->saturacion    = null;
        $this->glucosa       = null;
        $this->peso          = null;
        $this->talla         = null;
        $this->imc           = null;
        $this->dolor         = null;
        $this->observacion   = '';
        $this->confirmar_presion_atipica = false;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.clinica.registro-signos-vitales-modal');
    }
}
