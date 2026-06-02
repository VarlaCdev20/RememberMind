<?php

namespace App\Livewire\Admin\AdultosMayores\Salud;

use Livewire\Component;
use App\Models\AdultoMayor;
use App\Models\SignosVitalesAdulto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

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
            'fecha' => 'required|date',
            'hora' => 'required',
            'presion_arterial' => 'nullable|string|max:20',
            'frecuencia_cardiaca' => 'nullable|integer|min:0|max:300',
            'temperatura' => 'nullable|numeric|min:30|max:45',
            'saturacion' => 'nullable|integer|min:0|max:100',
            'glucosa' => 'nullable|numeric|min:0|max:1000',
            'peso' => 'nullable|numeric|min:0|max:300',
            'talla' => 'nullable|numeric|min:0|max:300',
            'imc' => 'nullable|numeric|min:0|max:100',
            'dolor' => 'nullable|integer|min:0|max:10',
            'observacion' => 'nullable|string|max:500',
        ];
    }

    public function messages()
    {
        return [
            'fecha.required' => 'La fecha es obligatoria.',
            'hora.required' => 'La hora es obligatoria.',
            'dolor.max' => 'El dolor debe ser entre 0 y 10.',
            'temperatura.max' => 'La temperatura debe ser válida.',
        ];
    }

    public function abrirModalSignos($cod_am, $id_signo = null)
    {
        $adulto = AdultoMayor::findOrFail($cod_am);
        Gate::authorize('viewClinicalData', $adulto);

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
        abort_if($signo->cod_am !== $this->cod_am, 403);
        
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
        if ($this->peso > 0 && $this->talla > 0) {
            $tallaMetros = $this->talla > 3 ? $this->talla / 100 : $this->talla;
            $this->imc = round($this->peso / ($tallaMetros * $tallaMetros), 2);
        } else {
            $this->imc = '';
        }
    }

    public function guardar()
    {
        $this->validate();
        $adulto = AdultoMayor::findOrFail($this->cod_am);
        Gate::authorize('viewClinicalData', $adulto);

        // Validar que al menos haya un signo vital
        if (empty($this->presion_arterial) && empty($this->frecuencia_cardiaca) && empty($this->temperatura) && 
            empty($this->saturacion) && empty($this->glucosa) && empty($this->peso) && empty($this->dolor)) {
            $this->addError('general', 'Debe registrar al menos un signo vital.');
            return;
        }

        $datos = [
            'cod_am' => $this->cod_am,
            'fecha' => $this->fecha,
            'hora' => $this->hora,
            'presion_arterial' => $this->presion_arterial ?: null,
            'frecuencia_cardiaca' => $this->frecuencia_cardiaca ?: null,
            'temperatura' => $this->temperatura ?: null,
            'saturacion' => $this->saturacion ?: null,
            'glucosa' => $this->glucosa ?: null,
            'peso' => $this->peso ?: null,
            'talla' => $this->talla ?: null,
            'imc' => $this->imc ?: null,
            'dolor' => $this->dolor !== '' ? $this->dolor : null,
            'observacion' => $this->observacion,
        ];

        if ($this->isEditing) {
            $signo = SignosVitalesAdulto::findOrFail($this->cod_signo);
            abort_if($signo->cod_am !== $this->cod_am, 403);
            $signo->update($datos);
            $mensaje = 'Signos vitales actualizados correctamente.';
        } else {
            $datos['registrado_por'] = Auth::id() ?? \App\Models\User::first()->cod_usu;
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
        $signosList = SignosVitalesAdulto::where('cod_am', $this->cod_am)
            ->latest('fecha')
            ->latest('hora')
            ->take(5)
            ->get();

        return view('livewire.admin.adultos-mayores.salud.signos-vitales-adulto-modal', [
            'signosList' => $signosList
        ]);
    }
}
