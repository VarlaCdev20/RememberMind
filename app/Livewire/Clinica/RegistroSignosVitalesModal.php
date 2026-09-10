<?php

namespace App\Livewire\Clinica;

use Livewire\Component;
use App\Models\AdultoMayor;
use App\Models\SignosVitalesAdulto;
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

    protected $listeners = ['abrir-signos-vitales-medico' => 'abrir'];

    public function mount(): void
    {
        $this->fecha = date('Y-m-d');
        $this->hora  = date('H:i');
    }

    public function abrir(string $cod_am): void
    {
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
        if ($this->peso > 0 && $this->talla > 0) {
            $tallaM = $this->talla > 3 ? $this->talla / 100 : (float) $this->talla;
            $this->imc = round((float) $this->peso / ($tallaM * $tallaM), 1);
        } else {
            $this->imc = null;
        }
    }

    protected function rules(): array
    {
        return [
            'cod_am'       => 'required|exists:adulto_mayor,cod_am',
            'fecha'        => 'required|date|before_or_equal:today',
            'hora'         => 'nullable|string',
            'pa_sistolica' => 'nullable|integer|min:50|max:300',
            'pa_diastolica'=> 'nullable|integer|min:30|max:200',
            'fc'           => 'nullable|integer|min:20|max:300',
            'fr'           => 'nullable|integer|min:5|max:60',
            'temperatura'  => 'nullable|numeric|min:30|max:44',
            'saturacion'   => 'nullable|integer|min:50|max:100',
            'glucosa'      => 'nullable|numeric|min:0|max:800',
            'peso'         => 'nullable|numeric|min:10|max:300',
            'talla'        => 'nullable|numeric|min:50|max:250',
            'dolor'        => 'nullable|integer|min:0|max:10',
            'observacion'  => 'nullable|string|max:500',
        ];
    }

    protected $messages = [
        'fecha.before_or_equal' => 'La fecha no puede ser futura.',
        'saturacion.min' => 'La saturación debe ser ≥ 50%.',
        'dolor.max'      => 'El dolor es una escala de 0 a 10.',
    ];

    public function guardar(): void
    {
        $this->validate();

        $tieneDatos = collect([
            $this->pa_sistolica, $this->fc, $this->temperatura,
            $this->saturacion, $this->glucosa, $this->peso, $this->dolor,
        ])->filter(fn($v) => $v !== null && $v !== '')->isNotEmpty();

        if (!$tieneDatos) {
            $this->addError('general', 'Registra al menos un signo vital.');
            return;
        }

        try {
            DB::transaction(function () {
                $pa = null;
                if ($this->pa_sistolica && $this->pa_diastolica) {
                    $pa = "{$this->pa_sistolica}/{$this->pa_diastolica}";
                }

                SignosVitalesAdulto::create([
                    'cod_am'               => $this->cod_am,
                    'fecha'                => $this->fecha,
                    'hora'                 => $this->hora ?: null,
                    'presion_arterial'     => $pa,
                    'presion_sistolica'    => $this->pa_sistolica ?: null,
                    'presion_diastolica'   => $this->pa_diastolica ?: null,
                    'frecuencia_cardiaca'  => $this->fc ?: null,
                    'frecuencia_respiratoria' => $this->fr ?: null,
                    'temperatura'          => $this->temperatura ?: null,
                    'saturacion'           => $this->saturacion ?: null,
                    'glucosa'              => $this->glucosa ?: null,
                    'peso'                 => $this->peso ?: null,
                    'talla'                => $this->talla ?: null,
                    'imc'                  => $this->imc ?: null,
                    'dolor'                => $this->dolor !== '' ? $this->dolor : null,
                    'observacion'          => $this->observacion ?: null,
                    'registrado_por'       => auth()->user()->cod_usu,
                    'estado'               => 'VIGENTE',
                ]);
            });

            $this->cerrar();
            $this->dispatch('signos-actualizados');
            $this->dispatch('swal:alert', [
                'type'    => 'success',
                'title'   => 'Signos registrados',
                'message' => 'Los signos vitales han sido guardados.',
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
        $this->fecha  = date('Y-m-d');
        $this->hora   = date('H:i');
        $this->pa_sistolica = $this->pa_diastolica = null;
        $this->fc = $this->fr = $this->temperatura = null;
        $this->saturacion = $this->glucosa = null;
        $this->peso = $this->talla = $this->imc = null;
        $this->dolor = null;
        $this->observacion = '';
    }

    public function render()
    {
        return view('livewire.clinica.registro-signos-vitales-modal');
    }
}
