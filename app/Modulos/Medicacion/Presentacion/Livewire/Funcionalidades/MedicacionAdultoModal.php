<?php

namespace App\Modulos\Medicacion\Presentacion\Livewire\Funcionalidades;

use App\Models\Prescripcion;
use App\Models\Residente;
use App\Modulos\Clinica\Aplicacion\Acciones\AccesoClinicoTemporalService;
use App\Modulos\Medicacion\Aplicacion\Acciones\GestionarPrescripciones;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Component;

class MedicacionAdultoModal extends Component
{
    public bool $showModal = false;

    public bool $isEditing = false;

    public ?string $cod_prescripcion = null;

    public string $cod_residente = '';

    public string $nombre_medicamento = '';

    public string $dosis = '';

    public string $frecuencia = '';

    public bool $es_prn = false;

    public string $condicion_prn = '';

    public ?int $intervalo_horas = null;

    public string $via_administracion = '';

    public string $hora_programada = '';

    public string $fecha_inicio = '';

    public string $fecha_fin = '';

    public string $medico_indica = '';

    public string $observacion = '';

    public string $estado = 'ACTIVO';

    protected $listeners = ['abrirModalMedicacion'];

    public function rules(): array
    {
        return [
            'nombre_medicamento' => 'required|string|max:120',
            'dosis' => 'required|string|max:60',
            'frecuencia' => 'required|string|max:80',
            'via_administracion' => 'required|string|max:60',
            'hora_programada' => 'required_unless:es_prn,true|nullable|date_format:H:i',
            'es_prn' => 'boolean',
            'condicion_prn' => 'required_if:es_prn,true|nullable|string|min:5|max:500',
            'intervalo_horas' => 'nullable|integer|min:1|max:24',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'observacion' => 'nullable|string|max:1000',
            'estado' => ['required', Rule::in(['ACTIVO', 'ACTIVA', 'SUSPENDIDO', 'SUSPENDIDA', 'FINALIZADO', 'FINALIZADA'])],
            'cod_residente' => 'required|string|exists:residentes,cod_residente',
        ];
    }

    public function abrirModalMedicacion($cod_residente, $id_med = null): void
    {
        $this->autorizarGestionOrden($id_med ? 'prescripciones.editar' : 'prescripciones.crear');
        $this->resetValidation();
        $this->cod_residente = (string) $cod_residente;
        $this->isEditing = filled($id_med);
        $this->cod_prescripcion = $id_med ? (string) $id_med : null;
        if ($this->isEditing) {
            $this->cargarDatos();
        } else {
            $this->resetCampos();
            $this->cod_residente = (string) $cod_residente;
            $this->fecha_inicio = now()->format('Y-m-d');
        }
        $this->showModal = true;
    }

    public function cerrarModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->resetCampos();
    }

    public function cargarDatos(): void
    {
        $prescripcion = Prescripcion::query()->with(['medicamento', 'horarios', 'personal'])
            ->where('cod_residente', $this->cod_residente)->findOrFail($this->cod_prescripcion);
        $this->nombre_medicamento = $prescripcion->nombre_medicamento;
        $this->dosis = trim($prescripcion->dosis.' '.$prescripcion->unidad_dosis);
        $this->frecuencia = (string) $prescripcion->frecuencia;
        $this->es_prn = (bool) $prescripcion->segun_necesidad;
        $this->condicion_prn = (string) $prescripcion->indicacion;
        $this->via_administracion = (string) $prescripcion->via_administracion;
        $this->hora_programada = $prescripcion->hora_programada
            ? Carbon::parse($prescripcion->hora_programada)->format('H:i') : '';
        $this->fecha_inicio = $prescripcion->fecha_hora_prescripcion->format('Y-m-d');
        $this->fecha_fin = '';
        $this->medico_indica = (string) $prescripcion->medico_indica;
        $this->observacion = (string) $prescripcion->observacion;
        $this->estado = $prescripcion->estado;
        $this->cod_residente = $prescripcion->cod_residente;
        $this->cod_residente = $prescripcion->cod_residente;
    }

    public function resetCampos(): void
    {
        $this->cod_prescripcion = null;
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
    }

    public function guardar(): void
    {
        $this->autorizarGestionOrden($this->isEditing ? 'prescripciones.editar' : 'prescripciones.crear');
        $this->validate();
        app(GestionarPrescripciones::class)->guardarDesdeModal(auth()->user(), [
            'dosis' => $this->dosis,
            'nombre_medicamento' => $this->nombre_medicamento,
            'via_administracion' => $this->via_administracion,
            'observacion' => $this->observacion,
            'fecha_fin' => $this->fecha_fin,
            'es_prn' => $this->es_prn,
            'intervalo_horas' => $this->intervalo_horas,
            'isEditing' => $this->isEditing,
            'cod_residente' => $this->cod_residente,
            'cod_prescripcion' => $this->cod_prescripcion,
            'frecuencia' => $this->frecuencia,
            'condicion_prn' => $this->condicion_prn,
            'fecha_inicio' => $this->fecha_inicio,
            'estado' => $this->estado,
            'hora_programada' => $this->hora_programada,
        ]);

        $mensaje = $this->isEditing ? 'Medicación actualizada correctamente.' : 'Medicación registrada correctamente.';
        $this->cerrarModal();
        $this->dispatch('medicacion-actualizada');
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Éxito', 'text' => $mensaje]);
    }

    public function cambiarEstado($id, $estado): void
    {
        $this->autorizarGestionOrden('prescripciones.editar');
        app(GestionarPrescripciones::class)->cambiarEstadoModal(auth()->user(), $this->cod_residente, $id, $estado);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Éxito', 'text' => "Estado cambiado a {$estado}."]);
    }

    public function render()
    {
        return view('livewire.medicacion.medicacion-adulto-modal', [
            'adultosDisponibles' => Residente::query()->whereIn('estado', ['ADMITIDO', 'ACTIVO'])->orderBy('nombres')->get(),
            'adultoSeleccionado' => ($this->cod_residente) ? Residente::query()->find($this->cod_residente) : null,
        ]);
    }

    private function autorizarGestionOrden(string $permiso): void
    {
        abort_unless(app(AccesoClinicoTemporalService::class)
            ->tieneRol(auth()->user(), ['MEDICO GENERAL/GERIATRA']) && auth()->user()?->can($permiso), 403,
            'Las órdenes médicas solo pueden ser creadas o modificadas por personal médico autorizado.');
    }
}
