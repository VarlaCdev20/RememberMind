<?php

namespace App\Livewire\Clinica;

use Livewire\Component;
use App\Models\SignosVitalesAdulto;
use App\Services\Clinica\ValidacionSignosVitalesService;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use Illuminate\Support\Facades\Auth;

class SignosVitalesAdultoModal extends Component
{
    public $showModal = false;
    public $isEditing = false;
    public $cod_signo = null;
    public $cod_am;

    public $fecha = '';
    public $hora = '';
    public $presion_arterial = '';
    public $frecuencia_cardiaca = '';
    public $temperatura = '';
    public $saturacion = '';
    public $glucosa = '';
    public $peso = '';
    public $talla = '';
    public $imc = '';
    public $dolor = '';
    public $observacion = '';

    protected $listeners = ['abrirModalSignos'];

    public function rules()
    {
        return [
            'fecha'               => 'required|date|before_or_equal:today',
            'hora'                => 'required',
            'presion_arterial'    => 'nullable|string|max:20',
            'frecuencia_cardiaca' => 'nullable|integer|min:' . ValidacionSignosVitalesService::FC_MIN . '|max:' . ValidacionSignosVitalesService::FC_MAX,
            'temperatura'         => 'nullable|numeric|min:' . ValidacionSignosVitalesService::TEMP_MIN . '|max:' . ValidacionSignosVitalesService::TEMP_MAX,
            'saturacion'          => 'nullable|integer|min:' . ValidacionSignosVitalesService::SPO2_MIN . '|max:' . ValidacionSignosVitalesService::SPO2_MAX,
            'glucosa'             => 'nullable|numeric|min:' . ValidacionSignosVitalesService::GLUCOSA_MIN . '|max:' . ValidacionSignosVitalesService::GLUCOSA_MAX,
            'peso'                => 'nullable|numeric|min:' . ValidacionSignosVitalesService::PESO_MIN . '|max:' . ValidacionSignosVitalesService::PESO_MAX,
            'talla'               => 'nullable|numeric|min:0.5|max:' . ValidacionSignosVitalesService::TALLA_CM_MAX,
            'dolor'               => 'nullable|integer|min:' . ValidacionSignosVitalesService::DOLOR_MIN . '|max:' . ValidacionSignosVitalesService::DOLOR_MAX,
            'observacion'         => 'nullable|string|max:5000',
        ];
    }

    public function messages()
    {
        return array_merge(ValidacionSignosVitalesService::mensajes(), [
            'fecha.required'        => 'La fecha es obligatoria.',
            'fecha.before_or_equal' => 'La fecha no puede ser futura.',
            'hora.required'         => 'La hora es obligatoria.',
        ]);
    }

    public function abrirModalSignos($cod_am, $id_signo = null)
    {
        $this->resetValidation();
        $this->cod_am = $cod_am;
        
        if ($id_signo) {
            $this->isEditing = true;
            $this->cod_signo = $id_signo;
            $this->cargarDatos();
        } else {
            $this->isEditing = false;
            $this->resetCampos();
            $this->fecha = now()->format('Y-m-d');
            $this->hora = now()->format('H:i');
        }

        $this->showModal = true;
    }

    public function cerrarModal()
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function cargarDatos()
    {
        $signo = SignosVitalesAdulto::findOrFail($this->cod_signo);
        
        $this->fecha = $signo->fecha ? $signo->fecha->format('Y-m-d') : null;
        $this->hora = $signo->hora ? \Carbon\Carbon::parse($signo->hora)->format('H:i') : null;
        $this->presion_arterial = $signo->presion_arterial;
        $this->frecuencia_cardiaca = $signo->frecuencia_cardiaca;
        $this->temperatura = $signo->temperatura;
        $this->saturacion = $signo->saturacion;
        $this->glucosa = $signo->glucosa;
        $this->peso = $signo->peso;
        $this->talla = $signo->talla;
        $this->imc = $signo->imc;
        $this->dolor = $signo->dolor;
        $this->observacion = $signo->observacion;
    }

    public function resetCampos()
    {
        $this->cod_signo = null;
        $this->fecha = '';
        $this->hora = '';
        $this->presion_arterial = '';
        $this->frecuencia_cardiaca = '';
        $this->temperatura = '';
        $this->saturacion = '';
        $this->glucosa = '';
        $this->peso = '';
        $this->talla = '';
        $this->imc = '';
        $this->dolor = '';
        $this->observacion = '';
    }

    public function updated($propertyName)
    {
        if ($propertyName === 'peso' || $propertyName === 'talla') {
            $this->calcularIMC();
        }
    }

    public function calcularIMC()
    {
        $p = $this->peso !== null && $this->peso !== '' ? (float) $this->peso : null;
        $t = $this->talla !== null && $this->talla !== '' ? (float) $this->talla : null;
        $calc = ValidacionSignosVitalesService::calcularImc($p, $t);
        $this->imc = $calc !== null ? (string) $calc : '';
    }

    public function guardar()
    {
        abort_unless(Auth::check(), 401);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($this->cod_am, Auth::user());

        $this->validate();

        // Validar PA en formato sis/dia
        $sis = null; $dia = null;
        if (!empty($this->presion_arterial)) {
            if (!str_contains($this->presion_arterial, '/')) {
                $this->addError('presion_arterial', 'La presión arterial debe tener formato Sistólica/Diastólica (ej. 120/80).');
                return;
            }
            $partes = explode('/', $this->presion_arterial);
            $sis = is_numeric(trim($partes[0])) ? (int) trim($partes[0]) : null;
            $dia = isset($partes[1]) && is_numeric(trim($partes[1])) ? (int) trim($partes[1]) : null;

            if ($sis === null || $dia === null) {
                $this->addError('presion_arterial', 'Valores de presión incompletos.');
                return;
            }
            if ($sis < ValidacionSignosVitalesService::PAS_MIN || $sis > ValidacionSignosVitalesService::PAS_MAX) {
                $this->addError('presion_arterial', 'La presión sistólica debe estar entre ' . ValidacionSignosVitalesService::PAS_MIN . ' y ' . ValidacionSignosVitalesService::PAS_MAX . ' mmHg.');
                return;
            }
            if ($dia < ValidacionSignosVitalesService::PAD_MIN || $dia > ValidacionSignosVitalesService::PAD_MAX) {
                $this->addError('presion_arterial', 'La presión diastólica debe estar entre ' . ValidacionSignosVitalesService::PAD_MIN . ' y ' . ValidacionSignosVitalesService::PAD_MAX . ' mmHg.');
                return;
            }
            if ($sis <= $dia) {
                $this->addError('presion_arterial', "La presión sistólica ({$sis}) debe ser estrictamente mayor a la diastólica ({$dia}).");
                return;
            }
        }

        // Validar que al menos haya un signo vital
        if (empty($this->presion_arterial) && empty($this->frecuencia_cardiaca) && empty($this->temperatura) && 
            empty($this->saturacion) && empty($this->glucosa) && empty($this->peso) && empty($this->dolor)) {
            $this->addError('general', 'Debe registrar al menos un signo vital o medición real.');
            return;
        }

        $p = $this->peso !== null && $this->peso !== '' ? (float) $this->peso : null;
        $t = $this->talla !== null && $this->talla !== '' ? (float) $this->talla : null;
        $tallaNorm = ValidacionSignosVitalesService::normalizarTalla($t);
        $imcCalc = ValidacionSignosVitalesService::calcularImc($p, $t);

        $datos = [
            'cod_am'              => $this->cod_am,
            'fecha'               => $this->fecha,
            'hora'                => $this->hora,
            'presion_arterial'    => $this->presion_arterial ?: null,
            'presion_sistolica'   => $sis,
            'presion_diastolica'  => $dia,
            'frecuencia_cardiaca' => $this->frecuencia_cardiaca ?: null,
            'temperatura'         => $this->temperatura ?: null,
            'saturacion'          => $this->saturacion ?: null,
            'glucosa'             => $this->glucosa ?: null,
            'peso'                => $this->peso ?: null,
            'talla'               => $tallaNorm,
            'imc'                 => $imcCalc,
            'dolor'               => $this->dolor !== '' ? $this->dolor : null,
            'observacion'         => $this->observacion,
        ];

        if ($this->isEditing) {
            $signo = SignosVitalesAdulto::findOrFail($this->cod_signo);
            $signo->update($datos);
            $mensaje = 'Signos vitales actualizados correctamente.';
        } else {
            $datos['registrado_por'] = Auth::id();
            SignosVitalesAdulto::create($datos);
            $mensaje = 'Signos vitales registrados correctamente.';
        }

        $this->cerrarModal();
        $this->dispatch('signos-actualizados');
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Éxito',
            'text' => $mensaje,
        ]);
    }

    public function render()
    {
        return view('livewire.clinica.signos-vitales-adulto-modal');
    }
}
