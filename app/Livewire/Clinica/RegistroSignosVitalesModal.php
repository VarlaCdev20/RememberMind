<?php

namespace App\Livewire\Clinica;

use Livewire\Component;
use App\Models\AdultoMayor;
use App\Models\SignosVitalesAdulto;
use App\Services\Clinica\ValidacionSignosVitalesService;
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

    protected $listeners = ['abrir-signos-vitales-medico' => 'abrir'];

    public function mount(): void
    {
        $this->fecha = date('Y-m-d');
        $this->hora  = date('H:i');
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

    protected function rules(): array
    {
        return [
            'cod_am'       => 'required|exists:adulto_mayor,cod_am',
            'fecha'        => 'required|date|before_or_equal:today',
            'hora'         => 'nullable|string',
            'pa_sistolica' => 'nullable|integer|min:' . ValidacionSignosVitalesService::PAS_MIN . '|max:' . ValidacionSignosVitalesService::PAS_MAX,
            'pa_diastolica'=> 'nullable|integer|min:' . ValidacionSignosVitalesService::PAD_MIN . '|max:' . ValidacionSignosVitalesService::PAD_MAX,
            'fc'           => 'nullable|integer|min:' . ValidacionSignosVitalesService::FC_MIN . '|max:' . ValidacionSignosVitalesService::FC_MAX,
            'fr'           => 'nullable|integer|min:' . ValidacionSignosVitalesService::FR_MIN . '|max:' . ValidacionSignosVitalesService::FR_MAX,
            'temperatura'  => 'nullable|numeric|min:' . ValidacionSignosVitalesService::TEMP_MIN . '|max:' . ValidacionSignosVitalesService::TEMP_MAX,
            'saturacion'   => 'nullable|integer|min:' . ValidacionSignosVitalesService::SPO2_MIN . '|max:' . ValidacionSignosVitalesService::SPO2_MAX,
            'glucosa'      => 'nullable|numeric|min:' . ValidacionSignosVitalesService::GLUCOSA_MIN . '|max:' . ValidacionSignosVitalesService::GLUCOSA_MAX,
            'peso'         => 'nullable|numeric|min:' . ValidacionSignosVitalesService::PESO_MIN . '|max:' . ValidacionSignosVitalesService::PESO_MAX,
            'talla'        => 'nullable|numeric|min:0.5|max:' . ValidacionSignosVitalesService::TALLA_CM_MAX,
            'dolor'        => 'nullable|integer|min:' . ValidacionSignosVitalesService::DOLOR_MIN . '|max:' . ValidacionSignosVitalesService::DOLOR_MAX,
            'observacion'  => 'nullable|string|max:5000',
        ];
    }

    protected function messages(): array
    {
        return array_merge(ValidacionSignosVitalesService::mensajes(), [
            'fecha.before_or_equal' => 'La fecha no puede ser futura.',
            'dolor.max'      => 'El dolor es una escala de 0 a 10.',
        ]);
    }

    public function guardar(): void
    {
        abort_unless(Auth::check(), 401);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($this->cod_am, Auth::user());

        $this->validate();

        $sis = $this->pa_sistolica !== null && $this->pa_sistolica !== '' ? (int) $this->pa_sistolica : null;
        $dia = $this->pa_diastolica !== null && $this->pa_diastolica !== '' ? (int) $this->pa_diastolica : null;

        // Validar PA en conjunto y sistolica > diastolica
        if (($sis !== null && $dia === null) || ($sis === null && $dia !== null)) {
            $this->addError('pa_sistolica', 'Debe registrar tanto la presión sistólica como la diastólica.');
            return;
        }
        if ($sis !== null && $dia !== null && $sis <= $dia) {
            $this->addError('pa_sistolica', "La presión sistólica ({$sis}) debe ser mayor a la diastólica ({$dia}).");
            return;
        }

        $tieneDatos = collect([
            $this->pa_sistolica, $this->fc, $this->fr, $this->temperatura,
            $this->saturacion, $this->glucosa, $this->peso, $this->dolor,
        ])->filter(fn($v) => $v !== null && $v !== '')->isNotEmpty();

        if (!$tieneDatos) {
            $this->addError('general', 'Debe registrar al menos un signo vital.');
            return;
        }

        // Recalcular IMC y normalizar talla en backend
        $p = $this->peso !== null && $this->peso !== '' ? (float) $this->peso : null;
        $t = $this->talla !== null && $this->talla !== '' ? (float) $this->talla : null;
        $tallaNorm = ValidacionSignosVitalesService::normalizarTalla($t);
        $imcCalc = ValidacionSignosVitalesService::calcularImc($p, $t);

        try {
            DB::transaction(function () use ($sis, $dia, $tallaNorm, $imcCalc) {
                $pa = null;
                if ($sis !== null && $dia !== null) {
                    $pa = "{$sis}/{$dia}";
                }

                SignosVitalesAdulto::create([
                    'cod_am'                  => $this->cod_am,
                    'fecha'                   => $this->fecha,
                    'hora'                    => $this->hora ?: now()->format('H:i:s'),
                    'presion_arterial'        => $pa,
                    'presion_sistolica'       => $sis,
                    'presion_diastolica'      => $dia,
                    'frecuencia_cardiaca'     => $this->fc ?: null,
                    'frecuencia_respiratoria' => $this->fr ?: null,
                    'temperatura'             => $this->temperatura ?: null,
                    'saturacion'              => $this->saturacion ?: null,
                    'glucosa'                 => $this->glucosa ?: null,
                    'peso'                    => $this->peso ?: null,
                    'talla'                   => $tallaNorm,
                    'imc'                     => $imcCalc,
                    'dolor'                   => $this->dolor !== '' ? $this->dolor : null,
                    'observacion'             => $this->observacion ?: null,
                    'registrado_por'          => Auth::id(),
                    'estado'                  => 'VIGENTE',
                ]);
            });

            $this->cerrar();
            $this->dispatch('signos-guardados');
            $this->dispatch('swal', [
                'icon'  => 'success',
                'title' => 'Signos vitales registrados',
                'text'  => 'Control guardado correctamente.',
            ]);
        } catch (\Exception $e) {
            $this->addError('general', $e->getMessage());
        }
    }

    private function resetForm(): void
    {
        $this->cod_am        = null;
        $this->adulto        = null;
        $this->fecha         = date('Y-m-d');
        $this->hora          = date('H:i');
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
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.clinica.registro-signos-vitales-modal');
    }
}
