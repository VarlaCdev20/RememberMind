<?php

namespace App\Frontend\Livewire\Administracion\Actividades;

use App\Models\Actividad;
use App\Models\AdultoMayor;
use App\Models\ParticipanteActividad;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class ActividadesPanel extends Component
{
    use WithPagination;

    // ── Filtros ───────────────────────────────────────────────────────────────
    public string $search           = '';
    public string $filtroTipo       = '';
    public string $filtroEstado     = '';
    public string $filtroFechaDesde = '';
    public string $filtroFechaHasta = '';

    // ── Modales ───────────────────────────────────────────────────────────────
    public bool $modalRegistrar = false;
    public bool $modalEditar    = false;
    public bool $modalDetalle   = false;

    // ── Campos del formulario ─────────────────────────────────────────────────
    public string $codResidente      = '';
    public string $codTipoAct = '';
    public string $fecha      = '';
    public string $hora       = '';
    public string $obs        = '';
    public string $estado     = 'PROGRAMADA';

    // ── Tracking ──────────────────────────────────────────────────────────────
    public ?string $editandoId = null;
    public ?string $detalleId  = null;

    protected function rules(): array
    {
        return [
            'codResidente'      => 'required|exists:residentes,cod_residente',
            'codTipoAct' => 'required|string|max:60',
            'fecha'      => 'required|date',
            'hora'       => 'required',
            'obs'        => 'nullable|string|max:2000',
            'estado'     => 'required|string|max:50',
        ];
    }

    protected function messages(): array
    {
        return [
            'codResidente.required'      => 'Seleccione un adulto mayor.',
            'codResidente.exists'        => 'El adulto mayor seleccionado no es válido.',
            'codTipoAct.required' => 'Seleccione el tipo de actividad.',
            'codTipoAct.exists'   => 'El tipo de actividad no es válido.',
            'fecha.required'      => 'La fecha es obligatoria.',
            'hora.required'       => 'La hora es obligatoria.',
            'estado.required'     => 'El estado es obligatorio.',
        ];
    }

    public function updatingSearch(): void           { $this->resetPage(); }
    public function updatingFiltroTipo(): void        { $this->resetPage(); }
    public function updatingFiltroEstado(): void      { $this->resetPage(); }
    public function updatingFiltroFechaDesde(): void  { $this->resetPage(); }
    public function updatingFiltroFechaHasta(): void  { $this->resetPage(); }

    // ── Apertura de modales ───────────────────────────────────────────────────
    public function abrirRegistrar(): void
    {
        $this->resetForm();
        $this->fecha  = today()->format('Y-m-d');
        $this->estado = 'PROGRAMADA';
        $this->modalRegistrar = true;
    }

    public function abrirEditar(string $id): void
    {
        $a = Actividad::with('participantes')->findOrFail($id);
        $this->editandoId  = $id;
        $this->codResidente       = $a->cod_residente;
        $this->codTipoAct  = (string) $a->cod_tipo_act;
        $this->fecha       = $a->fecha->format('Y-m-d');
        $this->hora        = substr($a->hora ?? '', 0, 5);
        $this->obs         = $a->obs ?? '';
        $this->estado      = $a->estado;
        $this->resetValidation();
        $this->modalEditar = true;
    }

    public function abrirDetalle(string $id): void
    {
        $this->detalleId    = $id;
        $this->modalDetalle = true;
    }

    public function cerrarModales(): void
    {
        $this->modalRegistrar = false;
        $this->modalEditar    = false;
        $this->modalDetalle   = false;
        $this->resetForm();
    }

    // ── CRUD ──────────────────────────────────────────────────────────────────
    public function guardarActividad(): void
    {
        $this->validate();
        [$personal, $area] = $this->contextoInstitucional();
        $actividad = Actividad::create([
            'cod_actividad' => $this->codigo('ACT'),
            'cod_area' => $area,
            'cod_personal' => $personal,
            'tipo' => $this->codTipoAct,
            'nombre' => Str::headline($this->codTipoAct),
            'descripcion' => $this->obs ?: null,
            'fecha_hora' => $this->fecha.' '.$this->hora,
            'estado' => strtoupper(trim($this->estado)),
            'observacion' => $this->obs ?: null,
        ]);
        ParticipanteActividad::create([
            'cod_participante' => $this->codigo('PAR'),
            'cod_actividad' => $actividad->cod_actividad,
            'cod_residente' => $this->codResidente,
        ]);
        $this->modalRegistrar = false;
        $this->resetForm();
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Actividad registrada correctamente.']);
    }

    public function actualizarActividad(): void
    {
        $this->validate();
        $a = Actividad::with('participantes')->findOrFail($this->editandoId);
        $a->update([
            'tipo' => $this->codTipoAct,
            'nombre' => Str::headline($this->codTipoAct),
            'fecha_hora' => $this->fecha.' '.$this->hora,
            'descripcion' => $this->obs ?: null,
            'observacion' => $this->obs ?: null,
            'estado' => strtoupper(trim($this->estado)),
        ]);
        $participante = $a->participantes->first();
        if ($participante) {
            $participante->update(['cod_residente' => $this->codResidente]);
        } else {
            ParticipanteActividad::create(['cod_participante'=>$this->codigo('PAR'),'cod_actividad'=>$a->cod_actividad,'cod_residente'=>$this->codResidente]);
        }
        $this->modalEditar = false;
        $this->resetForm();
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Actividad actualizada correctamente.']);
    }

    public function cancelarActividad(string $id): void
    {
        $a = Actividad::findOrFail($id);
        $a->update(['estado' => 'CANCELADA']);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Actividad cancelada.']);
    }

    public function limpiarFiltros(): void
    {
        $this->search           = '';
        $this->filtroTipo       = '';
        $this->filtroEstado     = '';
        $this->filtroFechaDesde = '';
        $this->filtroFechaHasta = '';
        $this->resetPage();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    private function resetForm(): void
    {
        $this->codResidente      = '';
        $this->codTipoAct = '';
        $this->fecha      = '';
        $this->hora       = '';
        $this->obs        = '';
        $this->estado     = 'PROGRAMADA';
        $this->editandoId = null;
        $this->detalleId  = null;
        $this->resetValidation();
    }

    private function getStats(): array
    {
        return [
            'total'            => Actividad::count(),
            'programadas'      => Actividad::where('estado', 'PROGRAMADA')->count(),
            'realizadas'       => Actividad::whereIn('estado', ['COMPLETADA', 'REALIZADA'])->count(),
            'canceladas'       => Actividad::where('estado', 'CANCELADA')->count(),
            'reprogramadas'    => Actividad::where('estado', 'REPROGRAMADA')->count(),
            'hoy'              => Actividad::whereDate('fecha_hora', today())->count(),
            'proximas7'        => Actividad::where('estado', 'PROGRAMADA')
                                    ->whereBetween('fecha_hora', [today(), today()->addDays(7)->endOfDay()])
                                    ->count(),
            'adultos_distintos'=> ParticipanteActividad::distinct('cod_residente')->count('cod_residente'),
        ];
    }

    private function getActividades()
    {
        return Actividad::with(['participantes', 'adultoMayor'])
            ->when($this->search, fn($q) =>
                $q->whereHas('adultoMayor', fn($sq) =>
                    $sq->whereLike('nombres', '%' . $this->search . '%')
                      ->orWhereLike('apellido_paterno', '%' . $this->search . '%')
                      ->orWhereLike('apellido_materno', '%' . $this->search . '%')
                )
            )
            ->when($this->filtroTipo,       fn($q) => $q->where('tipo', $this->filtroTipo))
            ->when($this->filtroEstado,     fn($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->filtroFechaDesde, fn($q) => $q->whereDate('fecha_hora', '>=', $this->filtroFechaDesde))
            ->when($this->filtroFechaHasta, fn($q) => $q->whereDate('fecha_hora', '<=', $this->filtroFechaHasta))
            ->orderByDesc('fecha_hora')
            ->paginate(12);
    }

    private function getTipos()
    {
        $tipos = Actividad::query()->select('tipo')->distinct()->orderBy('tipo')->pluck('tipo');
        if ($tipos->isEmpty()) {
            $tipos = collect(['RECREATIVA','COGNITIVA','FISICA','SOCIAL','EDUCATIVA','TERAPEUTICA']);
        }
        return $tipos->map(fn (string $tipo) => (object) ['cod_tipo_act'=>$tipo,'tipo'=>Str::headline($tipo),'nombre'=>Str::headline($tipo)]);
    }

    private function getAdultos()
    {
        return AdultoMayor::select('cod_residente', 'nombres', 'apellido_paterno', 'apellido_materno', 'fecha_nacimiento')
            ->orderBy('apellido_paterno')
            ->orderBy('nombres')
            ->get();
    }

    private function getDetalle(): ?Actividad
    {
        return $this->detalleId
            ? Actividad::with(['participantes', 'adultoMayor'])->find($this->detalleId)
            : null;
    }

    private function contextoInstitucional(): array
    {
        $personal = auth()->user()?->personal;
        $asignacion = $personal?->asignaciones()->where('estado', 'ACTIVO')->first();
        if (! $personal || ! $asignacion) {
            throw ValidationException::withMessages(['codTipoAct' => 'El usuario debe tener personal y área activa para registrar actividades.']);
        }
        return [$personal->cod_personal, $asignacion->cod_area];
    }

    private function codigo(string $prefijo): string
    {
        return $prefijo.'_'.Str::upper(Str::random(12));
    }

    public function render()
    {
        return view('livewire.actividades.actividades-panel', [
            'stats'       => $this->getStats(),
            'actividades' => $this->getActividades(),
            'tipos'       => $this->getTipos(),
            'adultos'     => $this->getAdultos(),
            'detalle'     => $this->getDetalle(),
        ])->layout('layouts.sistema');
    }
}
