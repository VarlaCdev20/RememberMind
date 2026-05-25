<?php

namespace App\Livewire\Admin\AdultosMayores\Salud;

use Livewire\Component;
use App\Models\FichaMedicaAdulto;
use Illuminate\Support\Facades\Auth;

class FichaMedicaAdultoModal extends Component
{
    public $showModal = false;
    public $isEditing = false;
    public $cod_ficha_medica = null;
    public $cod_am;

    // Campos del formulario
    public $hipertension = false;
    public $diabetes = false;
    public $problemas_cardiacos = false;
    public $acv = false;
    public $parkinson = false;
    public $epilepsia = false;
    public $alzheimer_diagnosticado = false;
    public $depresion = false;
    public $ansiedad = false;
    public $problemas_sueno = false;
    public $problemas_visuales = false;
    public $problemas_auditivos = false;
    public $dolor_cronico = false;
    public $alergias = '';
    public $restricciones_alimentarias = '';
    public $hospitalizaciones = '';
    public $cirugias = '';
    public $observacion_medica = '';

    protected $listeners = ['abrirModalFichaMedica'];

    public function rules()
    {
        return [
            'alergias' => 'nullable|string|max:255',
            'restricciones_alimentarias' => 'nullable|string|max:255',
            'hospitalizaciones' => 'nullable|string|max:255',
            'cirugias' => 'nullable|string|max:255',
            'observacion_medica' => 'nullable|string|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'alergias.max' => 'El texto no debe exceder los 255 caracteres.',
            'restricciones_alimentarias.max' => 'El texto no debe exceder los 255 caracteres.',
            'hospitalizaciones.max' => 'El texto no debe exceder los 255 caracteres.',
            'cirugias.max' => 'El texto no debe exceder los 255 caracteres.',
            'observacion_medica.max' => 'La observación no debe exceder los 1000 caracteres.',
        ];
    }

    public function abrirModalFichaMedica($cod_am, $id_ficha = null)
    {
        $this->resetValidation();
        $this->cod_am = $cod_am;
        
        if ($id_ficha) {
            $this->isEditing = true;
            $this->cod_ficha_medica = $id_ficha;
            $this->cargarDatos();
        } else {
            $this->isEditing = false;
            $this->resetCampos();
        }

        $this->showModal = true;
    }

    public function cerrarModal()
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->resetCampos();
    }

    public function cargarDatos()
    {
        $ficha = FichaMedicaAdulto::findOrFail($this->cod_ficha_medica);
        
        $this->hipertension = $ficha->hipertension;
        $this->diabetes = $ficha->diabetes;
        $this->problemas_cardiacos = $ficha->problemas_cardiacos;
        $this->acv = $ficha->acv;
        $this->parkinson = $ficha->parkinson;
        $this->epilepsia = $ficha->epilepsia;
        $this->alzheimer_diagnosticado = $ficha->alzheimer_diagnosticado;
        $this->depresion = $ficha->depresion;
        $this->ansiedad = $ficha->ansiedad;
        $this->problemas_sueno = $ficha->problemas_sueno;
        $this->problemas_visuales = $ficha->problemas_visuales;
        $this->problemas_auditivos = $ficha->problemas_auditivos;
        $this->dolor_cronico = $ficha->dolor_cronico;
        $this->alergias = $ficha->alergias;
        $this->restricciones_alimentarias = $ficha->restricciones_alimentarias;
        $this->hospitalizaciones = $ficha->hospitalizaciones;
        $this->cirugias = $ficha->cirugias;
        $this->observacion_medica = $ficha->observacion_medica;
    }

    public function resetCampos()
    {
        $this->cod_ficha_medica = null;
        $this->hipertension = false;
        $this->diabetes = false;
        $this->problemas_cardiacos = false;
        $this->acv = false;
        $this->parkinson = false;
        $this->epilepsia = false;
        $this->alzheimer_diagnosticado = false;
        $this->depresion = false;
        $this->ansiedad = false;
        $this->problemas_sueno = false;
        $this->problemas_visuales = false;
        $this->problemas_auditivos = false;
        $this->dolor_cronico = false;
        $this->alergias = '';
        $this->restricciones_alimentarias = '';
        $this->hospitalizaciones = '';
        $this->cirugias = '';
        $this->observacion_medica = '';
    }

    public function guardar()
    {
        $this->validate();

        $datos = [
            'cod_am' => $this->cod_am,
            'hipertension' => $this->hipertension,
            'diabetes' => $this->diabetes,
            'problemas_cardiacos' => $this->problemas_cardiacos,
            'acv' => $this->acv,
            'parkinson' => $this->parkinson,
            'epilepsia' => $this->epilepsia,
            'alzheimer_diagnosticado' => $this->alzheimer_diagnosticado,
            'depresion' => $this->depresion,
            'ansiedad' => $this->ansiedad,
            'problemas_sueno' => $this->problemas_sueno,
            'problemas_visuales' => $this->problemas_visuales,
            'problemas_auditivos' => $this->problemas_auditivos,
            'dolor_cronico' => $this->dolor_cronico,
            'alergias' => $this->alergias,
            'restricciones_alimentarias' => $this->restricciones_alimentarias,
            'hospitalizaciones' => $this->hospitalizaciones,
            'cirugias' => $this->cirugias,
            'observacion_medica' => $this->observacion_medica,
            'estado' => 'ACTIVO',
        ];

        if ($this->isEditing) {
            $ficha = FichaMedicaAdulto::findOrFail($this->cod_ficha_medica);
            $ficha->update($datos);
            $mensaje = 'Ficha médica actualizada correctamente.';
        } else {
            $datos['registrado_por'] = Auth::user()->cod_usu;
            FichaMedicaAdulto::create($datos);
            $mensaje = 'Ficha médica registrada correctamente.';
        }

        $this->cerrarModal();
        
        $this->dispatch('ficha-medica-actualizada');
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Éxito',
            'text' => $mensaje,
        ]);
    }

    public function render()
    {
        $fichaList = FichaMedicaAdulto::where('cod_am', $this->cod_am)
            ->latest()
            ->get();

        return view('livewire.admin.adultos-mayores.salud.ficha-medica-adulto-modal', [
            'fichaList' => $fichaList
        ]);
    }
}
