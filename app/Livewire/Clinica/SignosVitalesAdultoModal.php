<?php

namespace App\Livewire\Clinica;

use Livewire\Component;
use App\Models\SignosVitalesAdulto;
use App\Services\Clinica\ValidacionSignosVitalesService;
use App\Services\Clinica\SignosVitalesService;
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
    public $motivoRectificacion = '';

    protected $listeners = ['abrirModalSignos'];



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
        $this->motivoRectificacion = '';
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
        $servicio = app(SignosVitalesService::class);
        $datos = [
            'fecha' => $this->fecha,
            'hora' => $this->hora,
            'presion_arterial' => $this->presion_arterial,
            'frecuencia_cardiaca' => $this->frecuencia_cardiaca,
            'temperatura' => $this->temperatura,
            'saturacion' => $this->saturacion,
            'glucosa' => $this->glucosa,
            'peso' => $this->peso,
            'talla' => $this->talla,
            'dolor' => $this->dolor,
            'observacion' => $this->observacion,
        ];

        if ($this->isEditing) {
            $original = SignosVitalesAdulto::where('cod_am', $this->cod_am)->findOrFail($this->cod_signo);
            $servicio->rectificar($original, $datos, $this->motivoRectificacion, Auth::user());
            $mensaje = 'Rectificación registrada sin sobrescribir el control original.';
        } else {
            $servicio->registrar($this->cod_am, $datos, Auth::user());
            $mensaje = 'Signos vitales registrados correctamente.';
        }

        $this->cerrarModal();
        $this->dispatch('signos-actualizados');
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Registro guardado', 'text' => $mensaje]);
    }

    public function render()
    {
        return view('livewire.clinica.signos-vitales-adulto-modal');
    }
}
