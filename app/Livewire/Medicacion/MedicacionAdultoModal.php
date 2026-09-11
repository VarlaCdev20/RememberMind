<?php

namespace App\Livewire\Medicacion;

use Livewire\Component;
use App\Models\MedicacionAdulto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MedicacionAdultoModal extends Component
{
    public $showModal = false;
    public $isEditing = false;
    public $cod_med_adulto = null;
    public $cod_am;

    public $nombre_medicamento = '';
    public $dosis = '';
    public $frecuencia = '';
    public bool $es_prn = false;
    public $condicion_prn = '';
    public $intervalo_horas = null;
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
            'hora_programada' => 'required_unless:es_prn,true|nullable|date_format:H:i',
            'es_prn' => 'boolean',
            'condicion_prn' => 'required_if:es_prn,true|nullable|string|min:5|max:500',
            'intervalo_horas' => 'nullable|integer|min:1|max:24',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'medico_indica' => 'nullable|string|max:100',
            'observacion' => 'nullable|string|max:255',
            'estado' => ['required', Rule::in(['ACTIVO', 'PAUSADO', 'EN REVISION', 'SUSPENDIDO', 'FINALIZADO'])],
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
            'hora_programada.date_format' => 'La hora programada debe tener el formato HH:MM.',
            'condicion_prn.required_if' => 'La medicación PRN requiere una condición clínica explícita.',
            'intervalo_horas.min' => 'El intervalo mínimo es de una hora.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la de inicio.',
            'cod_am.required' => 'Debe seleccionar un adulto mayor.',
        ];
    }

    public function abrirModalMedicacion($cod_am, $id_med = null)
    {
        $this->autorizarGestionOrden();
        $permiso = $id_med ? ['medicacion.editar', 'salud.medicacion.editar'] : ['medicacion.crear', 'salud.medicacion.crear'];
        abort_unless(auth()->user()?->canAny($permiso), 403);
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
        $med = MedicacionAdulto::where('cod_am', $this->cod_am)->findOrFail($this->cod_med_adulto);
        
        $this->nombre_medicamento = $med->nombre_medicamento;
        $this->dosis = $med->dosis;
        $this->frecuencia = $med->frecuencia;
        $this->es_prn = (bool) $med->es_prn;
        $this->condicion_prn = $med->condicion_prn;
        $this->intervalo_horas = $med->intervalo_horas;
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
        $this->es_prn = false;
        $this->condicion_prn = '';
        $this->intervalo_horas = null;
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
        $this->autorizarGestionOrden();
        $permiso = $this->isEditing ? ['medicacion.editar', 'salud.medicacion.editar'] : ['medicacion.crear', 'salud.medicacion.crear'];
        abort_unless(auth()->user()?->canAny($permiso), 403);
        $this->validate();

        $datos = [
            'cod_am' => $this->cod_am,
            'nombre_medicamento' => trim($this->nombre_medicamento),
            'dosis' => trim($this->dosis),
            'frecuencia' => trim($this->frecuencia),
            'es_prn' => $this->es_prn,
            'condicion_prn' => $this->es_prn ? trim($this->condicion_prn) : null,
            'intervalo_horas' => $this->es_prn ? null : $this->intervalo_horas,
            'via_administracion' => trim($this->via_administracion),
            'hora_programada' => $this->es_prn ? null : $this->hora_programada,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin ?: null,
            'medico_indica' => filled($this->medico_indica) ? trim($this->medico_indica) : null,
            'observacion' => filled($this->observacion) ? trim($this->observacion) : null,
            'estado' => $this->estado,
        ];

        if ($this->isEditing) {
            $med = MedicacionAdulto::where('cod_am', $this->cod_am)->findOrFail($this->cod_med_adulto);
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
        $this->autorizarGestionOrden();
        abort_unless(auth()->user()?->canAny(['medicacion.editar', 'salud.medicacion.editar']), 403);
        validator(['estado' => $estado], ['estado' => ['required', Rule::in(['ACTIVO', 'PAUSADO', 'EN REVISION', 'SUSPENDIDO', 'FINALIZADO'])]])->validate();
        $med = MedicacionAdulto::where('cod_am', $this->cod_am)->findOrFail($id);
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

        return view('livewire.medicacion.medicacion-adulto-modal', [
            'adultosDisponibles' => $adultosDisponibles,
            'adultoSeleccionado' => $adultoSeleccionado,
        ]);
    }

    private function autorizarGestionOrden(): void
    {
        abort_unless(
            auth()->user()?->hasAnyRole(['SUPERADMINISTRADOR', 'MEDICO GENERAL/GERIATRA']),
            403,
            'Las órdenes médicas solo pueden ser creadas o modificadas por personal médico autorizado.'
        );
    }
}
