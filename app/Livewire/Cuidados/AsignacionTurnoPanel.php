<?php

namespace App\Livewire\Cuidados;

use App\Models\Residente;
use App\Models\AsignacionResidenteJornada;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class AsignacionTurnoPanel extends Component
{
    use WithPagination;

    #[\Livewire\Attributes\Url(as: 'adulto')]
    public string $filtroAdulto = '';
    public string $search = '';
    public string $filtroEstado = 'ACTIVA';
    public string $filtroTurno = '';
    public bool $modalForm = false;
    public bool $modalVer = false;
    public ?string $editandoId = null;
    public ?string $viendoId = null;
    public string $codAm = '';
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
        $this->reset('editandoId', 'codAm', 'codTurno', 'codEnfermero', 'codHabitacion',
            'codCama', 'fechaFin', 'nivelSupervision', 'motivoAsignacion');
        $this->fechaInicio = today()->format('Y-m-d');
        $this->codAm = $this->filtroAdulto;
        $this->estadoAsig = 'ACTIVA';
        $this->modalForm = true;
    }

    public function guardar(): void
    {
        abort_unless(auth()->user()?->can('turnos.asignar'), 403);
        $this->validate([
            'codAm' => 'required|exists:residentes,cod_residente',
            'codTurno' => ['required', Rule::exists('turnos', 'cod_turno')->where('estado', 'ACTIVO')],
            'codEnfermero' => ['required', Rule::exists('usuarios', 'cod_usuario')->where('estado', 'ACTIVO')],
            'fechaInicio' => 'required|date|before_or_equal:today',
            'nivelSupervision' => 'required|in:MINIMO,ESTANDAR,INTENSIVO,CRITICO',
            'motivoAsignacion' => 'required|string|min:5|max:1000',
        ]);

        DB::transaction(function (): void {
            $personal = Personal::query()->where('cod_usuario', $this->codEnfermero)->firstOrFail();
            $jornada = Jornada::query()->firstOrCreate(
                ['cod_turno' => $this->codTurno, 'fecha_jornada' => $this->fechaInicio],
                ['cod_jornada' => 'JOR_'.Str::upper(Str::random(12)), 'estado' => 'ABIERTA']
            );

            if (AsignacionResidenteJornada::query()
                ->where('cod_residente', $this->codAm)
                ->where('cod_jornada', $jornada->cod_jornada)
                ->where('estado', 'ACTIVA')->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'codTurno' => 'Este residente ya tiene una asignación activa en esta jornada.',
                ]);
            }

            AsignacionResidenteJornada::query()->create([
                'cod_asignacion' => 'ARJ_'.Str::upper(Str::random(12)),
                'cod_residente' => $this->codAm,
                'cod_jornada' => $jornada->cod_jornada,
                'cod_personal' => $personal->cod_personal,
                'nivel_supervision' => $this->nivelSupervision,
                'fecha_hora' => now(),
                'estado' => 'ACTIVA',
                'observacion' => trim($this->motivoAsignacion),
            ]);
        });

        $this->modalForm = false;
        session()->flash('mensaje', 'Asignación de jornada registrada. La cama de admisión se mantiene sin cambios.');
    }

    public function finalizarAsignacion(string $id): void
    {
        abort_unless(auth()->user()?->can('turnos.finalizar'), 403);
        AsignacionResidenteJornada::query()->findOrFail($id)->update(['estado' => 'FINALIZADA']);
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
            'turnos' => TurnoEnfermeria::activos()->get(),
            'enfermeros' => User::role('ENFERMEROS')->with('personal')->where('estado', 'ACTIVO')->get(),
        ])->layout('layouts.sistema');
    }
}
