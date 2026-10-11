<?php

namespace App\Modulos\Institucion\Presentacion\Livewire\Paginas;

use App\Models\AsignacionResidenteJornada;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Institucion\Aplicacion\Acciones\GestionarAsignacionesJornada;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class AsignacionTurnoPanel extends Component
{
    use WithPagination;

    #[Url(as: 'adulto')]
    public string $filtroAdulto = '';

    public string $search = '';

    public string $filtroEstado = 'ACTIVA';

    public string $filtroTurno = '';

    public bool $modalForm = false;

    public bool $modalVer = false;

    public ?string $editandoId = null;

    public ?string $viendoId = null;

    public string $codResidente = '';

    public string $codTurno = '';

    public string $codEnfermero = '';

    public string $codHabitacion = '';

    public string $codCama = '';

    public string $fechaInicio = '';

    public string $fechaFin = '';

    public string $nivelSupervision = 'ESTANDAR';

    public string $estadoAsig = 'ACTIVA';

    public string $motivoAsignacion = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('turnos.ver'), 403);
    }

    public function abrirCrear(): void
    {
        abort_unless(auth()->user()?->can('turnos.asignar'), 403);
        $this->reset('editandoId', 'codResidente', 'codTurno', 'codEnfermero', 'codHabitacion',
            'codCama', 'fechaFin', 'nivelSupervision', 'motivoAsignacion');
        $this->fechaInicio = today()->format('Y-m-d');
        $this->codResidente = $this->filtroAdulto;
        $this->estadoAsig = 'ACTIVA';
        $this->modalForm = true;
    }

    public function guardar(): void
    {
        app(GestionarAsignacionesJornada::class)->guardar(auth()->user(), [
            'codEnfermero' => $this->codEnfermero,
            'codTurno' => $this->codTurno,
            'fechaInicio' => $this->fechaInicio,
            'codResidente' => $this->codResidente,
            'nivelSupervision' => $this->nivelSupervision,
            'motivoAsignacion' => $this->motivoAsignacion,
        ]);

        $this->modalForm = false;
        session()->flash('mensaje', 'Asignación de jornada registrada. La cama de admisión se mantiene sin cambios.');
    }

    public function finalizarAsignacion(string $id): void
    {
        app(GestionarAsignacionesJornada::class)->finalizarAsignacion(auth()->user(), [

        ], $id);

        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Asignación finalizada.']);
    }

    public function cerrarModales(): void
    {
        $this->modalForm = false;
        $this->modalVer = false;
        $this->viendoId = null;
        $this->resetValidation();
    }

    public function render()
    {
        $asignaciones = AsignacionResidenteJornada::query()
            ->with(['residente.ocupacionActiva.cama.habitacion', 'jornada.turno', 'personal.usuario'])
            ->when($this->filtroAdulto, fn ($q) => $q->where('cod_residente', $this->filtroAdulto))
            ->when($this->search, fn ($q) => $q->whereHas('residente', fn ($residente) => $residente
                ->whereLike('nombres', '%'.$this->search.'%')
                ->orWhereLike('apellido_paterno', '%'.$this->search.'%')))
            ->when($this->filtroEstado, fn ($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->filtroTurno, fn ($q) => $q->whereHas('jornada', fn ($jornada) => $jornada
                ->where('cod_turno', $this->filtroTurno)))
            ->orderByDesc('fecha_hora')
            ->paginate(12);

        return view('livewire.cuidados.asignacion-turno-panel', [
            'asignaciones' => $asignaciones,
            'adultos' => Residente::query()->where('estado', 'ADMITIDO')->orderBy('apellido_paterno')->get(),
            'turnos' => Turno::activos()->orderBy('orden')->get(),
            'enfermeros' => User::role('ENFERMEROS')->with('personal')->where('estado', 'ACTIVO')->get(),
        ])->layout('layouts.sistema');
    }
}
