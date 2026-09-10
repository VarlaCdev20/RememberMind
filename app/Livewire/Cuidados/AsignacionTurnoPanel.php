<?php

namespace App\Livewire\Cuidados;

use App\Models\AdultoMayor;
use App\Models\AsignacionTurnoAdulto;
use App\Models\Cama;
use App\Models\Habitacion;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class AsignacionTurnoPanel extends Component
{
    use WithPagination;

    public string $search        = '';
    public string $filtroEstado  = 'ACTIVA';
    public string $filtroTurno   = '';

    public bool   $modalForm  = false;
    public bool   $modalVer   = false;
    public ?string $editandoId = null;
    public ?string $viendoId   = null;

    public string $codAm              = '';
    public string $codTurno           = '';
    public string $codEnfermero       = '';
    public string $codHabitacion      = '';
    public string $codCama            = '';
    public string $fechaInicio        = '';
    public string $fechaFin           = '';
    public string $nivelSupervision   = 'ESTANDAR';
    public string $estadoAsig         = 'ACTIVA';
    public string $motivoAsignacion   = '';

    public function abrirCrear(): void
    {
        $this->reset('editandoId','codAm','codTurno','codEnfermero','codHabitacion',
                     'codCama','fechaFin','nivelSupervision','motivoAsignacion');
        $this->fechaInicio = today()->format('Y-m-d');
        $this->estadoAsig  = 'ACTIVA';
        $this->modalForm   = true;
    }

    public function guardar(): void
    {
        $this->validate([
            'codAm'            => 'required|exists:adulto_mayor,cod_am',
            'codTurno'         => 'required|exists:turnos_enfermeria,cod_turno',
            'codEnfermero'     => 'required|exists:users,cod_usu',
            'fechaInicio'      => 'required|date',
            'nivelSupervision' => 'required|in:MINIMO,ESTANDAR,INTENSIVO,CRITICO',
            'motivoAsignacion' => 'required|string|min:5',
            'estadoAsig'       => 'required|in:ACTIVA,FINALIZADA,REEMPLAZADA,ANULADA',
        ], [
            'codAm.required'            => 'Seleccione un adulto mayor.',
            'codTurno.required'         => 'Seleccione un turno.',
            'codEnfermero.required'     => 'Seleccione un enfermero.',
            'fechaInicio.required'      => 'La fecha de inicio es obligatoria.',
            'motivoAsignacion.required' => 'El motivo de asignación es obligatorio.',
            'motivoAsignacion.min'      => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        // Validar que no haya dos asignaciones activas del mismo adulto en el mismo turno
        if (! $this->editandoId) {
            $existente = AsignacionTurnoAdulto::where('cod_am', $this->codAm)
                ->where('cod_turno', $this->codTurno)
                ->where('estado', 'ACTIVA')
                ->exists();
            if ($existente) {
                $this->addError('codTurno', 'Este adulto mayor ya tiene una asignación activa en este turno.');
                return;
            }
        }

        $datos = [
            'cod_am'           => $this->codAm,
            'cod_turno'        => $this->codTurno,
            'cod_usu_enfermero'=> $this->codEnfermero,
            'cod_habitacion'   => $this->codHabitacion ?: null,
            'cod_cama'         => $this->codCama ?: null,
            'fecha_inicio'     => $this->fechaInicio,
            'fecha_fin'        => $this->fechaFin ?: null,
            'nivel_supervision'=> $this->nivelSupervision,
            'estado'           => $this->estadoAsig,
            'motivo_asignacion'=> $this->motivoAsignacion,
            'asignado_por'     => Auth::id(),
        ];

        if ($this->editandoId) {
            AsignacionTurnoAdulto::findOrFail($this->editandoId)->update($datos);
            $msg = 'Asignación actualizada.';
        } else {
            AsignacionTurnoAdulto::create($datos);
            $msg = 'Asignación de turno registrada.';
        }

        $this->modalForm = false;
        $this->dispatch('swal', ['icon' => 'success', 'title' => $msg]);
    }

    public function finalizarAsignacion(string $id): void
    {
        AsignacionTurnoAdulto::findOrFail($id)->update([
            'estado'   => 'FINALIZADA',
            'fecha_fin'=> today()->toDateString(),
        ]);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Asignación finalizada.']);
    }

    public function cerrarModales(): void
    {
        $this->modalForm = false;
        $this->modalVer  = false;
        $this->viendoId  = null;
        $this->resetValidation();
    }

    public function render()
    {
        $asignaciones = AsignacionTurnoAdulto::with(['adultoMayor','turno','enfermero','habitacion','cama'])
            ->when($this->search, fn($q) =>
                $q->whereHas('adultoMayor', fn($sq) =>
                    $sq->where('nombres', 'ilike', '%' . $this->search . '%')
                      ->orWhere('ap_paterno', 'ilike', '%' . $this->search . '%')
                )
            )
            ->when($this->filtroEstado, fn($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->filtroTurno,  fn($q) => $q->where('cod_turno', $this->filtroTurno))
            ->orderByDesc('fecha_inicio')
            ->paginate(12);

        return view('livewire.admin.enfermeria.asignacion-turno-panel', [
            'asignaciones' => $asignaciones,
            'adultos'      => AdultoMayor::select('cod_am','nombres','ap_paterno','ap_materno')
                ->whereNull('archivado_en')->orderBy('ap_paterno')->get(),
            'turnos'       => TurnoEnfermeria::activos()->get(),
            'enfermeros'   => User::orderBy('ap_paterno')->get(['cod_usu','nombres','ap_paterno']),
            'habitaciones' => Habitacion::where('estado', 'DISPONIBLE')->orderBy('codigo')->get(),
            'camas'        => $this->codHabitacion
                ? Cama::where('cod_habitacion', $this->codHabitacion)->where('estado','DISPONIBLE')->get()
                : collect(),
        ])->layout('layouts.sistema');
    }
}
