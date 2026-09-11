<?php

namespace App\Livewire\Cuidados;

use App\Models\AdultoMayor;
use App\Models\AsignacionTurnoAdulto;
use App\Models\Cama;
use App\Models\Habitacion;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class AsignacionTurnoPanel extends Component
{
    use WithPagination;
    #[\Livewire\Attributes\Url(as: 'adulto')]
    public string $filtroAdulto = '';

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

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('turnos.ver'), 403);
    }

    public function abrirCrear(): void
    {
        abort_unless(auth()->user()?->can('turnos.asignar'), 403);
        $this->reset('editandoId','codAm','codTurno','codEnfermero','codHabitacion',
                     'codCama','fechaFin','nivelSupervision','motivoAsignacion');
        $this->fechaInicio = today()->format('Y-m-d');
        $this->codAm = $this->filtroAdulto;
        $this->estadoAsig  = 'ACTIVA';
        $this->modalForm   = true;
    }

    public function guardar(): void
    {
        abort_unless(auth()->user()?->can('turnos.asignar'), 403);
        $this->validate([
            'codAm'            => 'required|exists:adulto_mayor,cod_am',
            'codTurno'         => ['required', Rule::exists('turnos_enfermeria', 'cod_turno')->where('estado', 'ACTIVO')],
            'codEnfermero'     => ['required', Rule::exists('users', 'cod_usu')->where('estado', 'ACTIVO')],
            'fechaInicio'      => 'required|date|before_or_equal:today',
            'fechaFin'         => 'nullable|date|after_or_equal:fechaInicio',
            'codHabitacion'    => ['required', Rule::exists('habitaciones', 'cod_habitacion')->where('estado', 'DISPONIBLE')],
            'codCama'          => ['required', Rule::exists('camas','cod_cama')->where('cod_habitacion', $this->codHabitacion)->where('estado', 'DISPONIBLE')],
            'nivelSupervision' => 'required|in:MINIMO,ESTANDAR,INTENSIVO,CRITICO',
            'motivoAsignacion' => 'required|string|min:5|max:1000',
            'estadoAsig'       => 'required|in:ACTIVA',
        ], [
            'codAm.required'            => 'Seleccione un adulto mayor.',
            'codTurno.required'         => 'Seleccione un turno.',
            'codEnfermero.required'     => 'Seleccione un enfermero.',
            'codEnfermero.exists'       => 'El enfermero seleccionado no existe o no se encuentra activo.',
            'codTurno.exists'           => 'Seleccione un turno activo.',
            'codHabitacion.exists'      => 'Seleccione una habitación disponible.',
            'codCama.exists'            => 'Seleccione una cama disponible de la habitación.',
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
            'motivo_asignacion'=> trim($this->motivoAsignacion),
            'asignado_por'     => Auth::id(),
        ];

        \Illuminate\Support\Facades\DB::transaction(function () use ($datos) {
            $adulto = AdultoMayor::lockForUpdate()->findOrFail($this->codAm);
            $cama = Cama::lockForUpdate()->findOrFail($this->codCama);
            $ocupada = AdultoMayor::where('cod_cama', $cama->cod_cama)->where('cod_am', '!=', $adulto->cod_am)->exists()
                || AsignacionTurnoAdulto::where('cod_cama', $cama->cod_cama)->where('estado', 'ACTIVA')->where('cod_am', '!=', $adulto->cod_am)->exists();
            if ($ocupada || in_array($cama->estado, ['MANTENIMIENTO','BLOQUEADA'])) throw \Illuminate\Validation\ValidationException::withMessages(['codCama' => 'La cama no está disponible.']);
            if ($adulto->cod_cama && $adulto->cod_cama !== $cama->cod_cama) throw \Illuminate\Validation\ValidationException::withMessages(['codCama' => 'Finalice la ocupación anterior antes de trasladar al residente.']);
            if (AsignacionTurnoAdulto::where('cod_am',$adulto->cod_am)->where('cod_turno',$this->codTurno)->where('estado','ACTIVA')->exists()) throw \Illuminate\Validation\ValidationException::withMessages(['codTurno'=>'Ya existe una asignación activa.']);
            AsignacionTurnoAdulto::create($datos);
            $adulto->update(['cod_habitacion'=>$this->codHabitacion, 'cod_cama'=>$this->codCama]);
            $cama->update(['estado'=>'OCUPADA']);
        });
        $msg = 'Asignación registrada y ocupación actualizada.';
        $this->modalForm = false;
        session()->flash('mensaje', $msg);
    }

    public function finalizarAsignacion(string $id): void
    {
        abort_unless(auth()->user()?->can('turnos.finalizar'), 403);
        \Illuminate\Support\Facades\DB::transaction(function () use ($id) {
            $asignacion = AsignacionTurnoAdulto::findOrFail($id);
            $adulto = AdultoMayor::lockForUpdate()->findOrFail($asignacion->cod_am);
            $asignacion->update(['estado'=>'FINALIZADA', 'fecha_fin'=>today()->toDateString()]);
            if (!AsignacionTurnoAdulto::where('cod_am',$adulto->cod_am)->where('estado','ACTIVA')->exists()) {
                if ($adulto->cod_cama) Cama::whereKey($adulto->cod_cama)->update(['estado'=>'DISPONIBLE']);
                $adulto->update(['cod_habitacion'=>null, 'cod_cama'=>null]);
            }
        });
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
        $asignaciones = AsignacionTurnoAdulto::query()->when($this->filtroAdulto, fn ($q) => $q->where('cod_am', $this->filtroAdulto))->with(['adultoMayor','turno','enfermero','habitacion','cama'])
            ->when($this->search, fn($q) =>
                $q->whereHas('adultoMayor', fn($sq) =>
                    $sq->whereLike('nombres', '%' . $this->search . '%')
                      ->orWhereLike('ap_paterno', '%' . $this->search . '%')
                )
            )
            ->when($this->filtroEstado, fn($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->filtroTurno,  fn($q) => $q->where('cod_turno', $this->filtroTurno))
            ->orderByDesc('fecha_inicio')
            ->paginate(12);

        return view('livewire.cuidados.asignacion-turno-panel', [
            'asignaciones' => $asignaciones,
            'adultos'      => AdultoMayor::select('cod_am','nombres','ap_paterno','ap_materno')
                ->whereNull('archivado_en')->orderBy('ap_paterno')->get(),
            'turnos'       => TurnoEnfermeria::activos()->get(),
            'enfermeros'   => User::role('ENFERMEROS')->where('estado', 'ACTIVO')->orderBy('ap_paterno')->get(['cod_usu','nombres','ap_paterno']),
            'habitaciones' => Habitacion::where('estado', 'DISPONIBLE')->orderBy('codigo')->get(),
            'camas'        => $this->codHabitacion
                ? Cama::where('cod_habitacion', $this->codHabitacion)->where('estado', 'DISPONIBLE')->get()
                : collect(),
        ])->layout('layouts.sistema');
    }
}
