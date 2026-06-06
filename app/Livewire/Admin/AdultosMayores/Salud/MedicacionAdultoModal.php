<?php

namespace App\Livewire\Admin\AdultosMayores\Salud;

use Livewire\Component;
use App\Models\MedicacionAdulto;
use Illuminate\Support\Facades\Auth;

class MedicacionAdultoModal extends Component
{
    public $showModal = false;
    public $isEditing = false;
    public $cod_med_adulto = null;
    public $cod_am;

    public $nombre_medicamento = '';
    public $dosis = '';
    public $frecuencia = '';
    public $via_administracion = '';
    public $hora_programada = '';
    public $fecha_inicio = '';
    public $fecha_fin = '';
    public $medico_indica = '';
    public $observacion = '';
    public $estado = 'ACTIVO';

    protected $listeners = ['abrirModalMedicacion'];

    public function rules()
    {
        return [
            'nombre_medicamento' => 'required|string|max:100',
            'dosis' => 'required|string|max:50',
            'frecuencia' => 'required|string|max:100',
            'via_administracion' => 'required|string|max:50',
            'hora_programada' => 'required',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'medico_indica' => 'nullable|string|max:100',
            'observacion' => 'nullable|string|max:255',
            'estado' => 'required|string|in:ACTIVO,PAUSADO,EN REVISION,SUSPENDIDO,FINALIZADO',
            'cod_am' => 'required|string|exists:adulto_mayor,cod_am',
        ];
    }

    public function messages()
    {
        return [
            'nombre_medicamento.required' => 'El medicamento es obligatorio.',
            'dosis.required' => 'La dosis es obligatoria.',
            'frecuencia.required' => 'La frecuencia es obligatoria.',
            'via_administracion.required' => 'La vía de administración es obligatoria.',
            'hora_programada.required' => 'La hora es obligatoria.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la de inicio.',
            'cod_am.required' => 'Debe seleccionar un adulto mayor.',
        ];
    }

    public function abrirModalMedicacion($cod_am, $id_med = null)
    {
        $this->resetValidation();
        $this->cod_am = $cod_am;
        
        if ($id_med) {
            $this->isEditing = true;
            $this->cod_med_adulto = $id_med;
            $this->cargarDatos();
        } else {
            $this->isEditing = false;
            $this->resetCampos();
            if ($cod_am) {
                $this->cod_am = $cod_am;
            }
            $this->fecha_inicio = now()->format('Y-m-d');
            $this->estado = 'ACTIVO';
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
        $med = MedicacionAdulto::findOrFail($this->cod_med_adulto);
        
        $this->nombre_medicamento = $med->nombre_medicamento;
        $this->dosis = $med->dosis;
        $this->frecuencia = $med->frecuencia;
        $this->via_administracion = $med->via_administracion;
        $this->hora_programada = $med->hora_programada ? \Carbon\Carbon::parse($med->hora_programada)->format('H:i') : null;
        $this->fecha_inicio = $med->fecha_inicio ? $med->fecha_inicio->format('Y-m-d') : null;
        $this->fecha_fin = $med->fecha_fin ? $med->fecha_fin->format('Y-m-d') : null;
        $this->medico_indica = $med->medico_indica;
        $this->observacion = $med->observacion;
        $this->estado = $med->estado;
        $this->cod_am = $med->cod_am;
    }

    public function resetCampos()
    {
        $this->cod_med_adulto = null;
        $this->nombre_medicamento = '';
        $this->dosis = '';
        $this->frecuencia = '';
        $this->via_administracion = '';
        $this->hora_programada = '';
        $this->fecha_inicio = '';
        $this->fecha_fin = '';
        $this->medico_indica = '';
        $this->observacion = '';
        $this->estado = 'ACTIVO';
        // No reseteamos cod_am aquí para no perder la selección en caso de fallar validación
    }

    public function guardar()
    {
        $this->validate();

        $datos = [
            'cod_am' => $this->cod_am,
            'nombre_medicamento' => $this->nombre_medicamento,
            'dosis' => $this->dosis,
            'frecuencia' => $this->frecuencia,
            'via_administracion' => $this->via_administracion,
            'hora_programada' => $this->hora_programada,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin ?: null,
            'medico_indica' => $this->medico_indica,
            'observacion' => $this->observacion,
            'estado' => $this->estado,
        ];

        if ($this->isEditing) {
            $med = MedicacionAdulto::findOrFail($this->cod_med_adulto);
            $med->update($datos);
            $mensaje = 'Medicación actualizada correctamente.';
        } else {
            $datos['registrado_por'] = Auth::user()->cod_usu;
            MedicacionAdulto::create($datos);
            $mensaje = 'Medicación registrada correctamente.';
        }

        $this->cerrarModal();
        
        $this->dispatch('medicacion-actualizada');
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Éxito',
            'text' => $mensaje,
        ]);
    }

    public function cambiarEstado($id, $estado)
    {
        $med = MedicacionAdulto::findOrFail($id);
        $med->estado = $estado;
        $med->save();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Éxito',
            'text' => "Estado cambiado a {$estado}.",
        ]);
    }

    public function render()
    {
        $adultosDisponibles = \App\Models\AdultoMayor::whereHas('estado', function ($q) {
            $q->whereIn('estado', ['ACTIVO', 'ACTIVA']);
        })->orderBy('nombres')->get();
        
        $adultoSeleccionado = null;
        if ($this->cod_am) {
            $adultoSeleccionado = \App\Models\AdultoMayor::where('cod_am', $this->cod_am)->first();
        }

        return view('livewire.admin.adultos-mayores.salud.medicacion-adulto-modal', [
            'adultosDisponibles' => $adultosDisponibles,
            'adultoSeleccionado' => $adultoSeleccionado,
        ]);
    }
}
