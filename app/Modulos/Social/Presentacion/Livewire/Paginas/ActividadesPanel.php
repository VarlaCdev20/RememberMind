<?php

namespace App\Modulos\Social\Presentacion\Livewire\Paginas;

use App\Models\Actividad;
use App\Models\ParticipanteActividad;
use App\Models\Residente;
use App\Modulos\Social\Aplicacion\Acciones\GestionarActividades;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class ActividadesPanel extends Component
{
    use WithPagination;

    // ── Filtros ───────────────────────────────────────────────────────────────
    public string $search = '';

    public string $filtroTipo = '';

    public string $filtroEstado = '';

    public string $filtroFechaDesde = '';

    public string $filtroFechaHasta = '';

    // ── Modales ───────────────────────────────────────────────────────────────
    public bool $modalRegistrar = false;

    public bool $modalEditar = false;

    public bool $modalDetalle = false;

    // ── Campos del formulario ─────────────────────────────────────────────────
    public string $codResidente = '';

    public string $codTipoAct = '';

    public string $fecha = '';

    public string $hora = '';

    public string $obs = '';

    public string $estado = 'PROGRAMADA';

    // ── Tracking ──────────────────────────────────────────────────────────────
    public ?string $editandoId = null;

    public ?string $detalleId = null;

    protected function rules(): array
    {
        return [
            'codResidente' => 'required|exists:residentes,cod_residente',
            'codTipoAct' => 'required|string|max:60',
            'fecha' => 'required|date',
            'hora' => 'required',
            'obs' => 'nullable|string|max:2000',
            'estado' => 'required|string|max:50',
        ];
    }

    protected function messages(): array
    {
        return [
            'codResidente.required' => 'Seleccione un adulto mayor.',
            'codResidente.exists' => 'El adulto mayor seleccionado no es válido.',
            'codTipoAct.required' => 'Seleccione el tipo de actividad.',
            'codTipoAct.exists' => 'El tipo de actividad no es válido.',
            'fecha.required' => 'La fecha es obligatoria.',
            'hora.required' => 'La hora es obligatoria.',
            'estado.required' => 'El estado es obligatorio.',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroTipo(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroFechaDesde(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroFechaHasta(): void
    {
        $this->resetPage();
    }

    // ── Apertura de modales ───────────────────────────────────────────────────
    public function abrirRegistrar(): void
    {
        $this->resetForm();
        $this->fecha = today()->format('Y-m-d');
        $this->estado = 'PROGRAMADA';
        $this->modalRegistrar = true;
    }

    public function abrirEditar(string $id): void
    {
        $a = Actividad::with('participantes')->findOrFail($id);
        $this->editandoId = $id;
        $this->codResidente = (string) ($a->participantes->first()?->cod_residente ?? '');
        $this->codTipoAct = (string) $a->cod_tipo_act;
        $this->fecha = $a->fecha->format('Y-m-d');
        $this->hora = substr($a->hora ?? '', 0, 5);
        $this->obs = $a->obs ?? '';
        $this->estado = $a->estado;
        $this->resetValidation();
        $this->modalEditar = true;
    }

    public function abrirDetalle(string $id): void
    {
        $this->detalleId = $id;
        $this->modalDetalle = true;
    }

    public function cerrarModales(): void
    {
        $this->modalRegistrar = false;
        $this->modalEditar = false;
        $this->modalDetalle = false;
        $this->resetForm();
    }

    // ── CRUD ──────────────────────────────────────────────────────────────────
    public function guardarActividad(): void
    {
        abort_unless(auth()->user()?->can('actividades.gestionar'), 403);
        $this->validate();
        app(GestionarActividades::class)->guardarActividad(auth()->user(), ['codTipoAct' => $this->codTipoAct, 'obs' => $this->obs, 'fecha' => $this->fecha, 'hora' => $this->hora, 'estado' => $this->estado, 'codResidente' => $this->codResidente]);

        $this->modalRegistrar = false;
        $this->resetForm();
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Actividad registrada correctamente.']);
    }

    public function actualizarActividad(): void
    {
        abort_unless(auth()->user()?->can('actividades.gestionar'), 403);
        $this->validate();
        app(GestionarActividades::class)->actualizarActividad(auth()->user(), ['editandoId' => $this->editandoId, 'codTipoAct' => $this->codTipoAct, 'fecha' => $this->fecha, 'hora' => $this->hora, 'obs' => $this->obs, 'estado' => $this->estado, 'codResidente' => $this->codResidente]);

        $this->modalEditar = false;
        $this->resetForm();
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Actividad actualizada correctamente.']);
    }

    public function cancelarActividad(string $id): void
    {
        abort_unless(auth()->user()?->can('actividades.gestionar'), 403);
        app(GestionarActividades::class)->cancelarActividad(auth()->user(), ['codResidente' => $this->codResidente, 'codTipoAct' => $this->codTipoAct, 'fecha' => $this->fecha, 'hora' => $this->hora, 'obs' => $this->obs, 'estado' => $this->estado], $id);

        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Actividad cancelada.']);
    }

    public function limpiarFiltros(): void
    {
        $this->search = '';
        $this->filtroTipo = '';
        $this->filtroEstado = '';
        $this->filtroFechaDesde = '';
        $this->filtroFechaHasta = '';
        $this->resetPage();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    private function resetForm(): void
    {
        $this->codResidente = '';
        $this->codTipoAct = '';
        $this->fecha = '';
        $this->hora = '';
        $this->obs = '';
        $this->estado = 'PROGRAMADA';
        $this->editandoId = null;
        $this->detalleId = null;
        $this->resetValidation();
    }

    private function getStats(): array
    {
        return [
            'total' => Actividad::count(),
            'programadas' => Actividad::where('estado', 'PROGRAMADA')->count(),
            'realizadas' => Actividad::whereIn('estado', ['COMPLETADA', 'REALIZADA'])->count(),
            'canceladas' => Actividad::where('estado', 'CANCELADA')->count(),
            'reprogramadas' => Actividad::where('estado', 'REPROGRAMADA')->count(),
            'hoy' => Actividad::whereDate('fecha_hora', today())->count(),
            'proximas7' => Actividad::where('estado', 'PROGRAMADA')
                ->whereBetween('fecha_hora', [today(), today()->addDays(7)->endOfDay()])
                ->count(),
            'adultos_distintos' => ParticipanteActividad::distinct('cod_residente')->count('cod_residente'),
        ];
    }

    private function getActividades()
    {
        return Actividad::with(['participantes', 'adultoMayor'])
            ->when($this->search, fn ($q) => $q->whereHas('adultoMayor', fn ($sq) => $sq->whereLike('nombres', '%'.$this->search.'%')
                ->orWhereLike('apellido_paterno', '%'.$this->search.'%')
                ->orWhereLike('apellido_materno', '%'.$this->search.'%')
            )
            )
            ->when($this->filtroTipo, fn ($q) => $q->where('tipo', $this->filtroTipo))
            ->when($this->filtroEstado, fn ($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->filtroFechaDesde, fn ($q) => $q->whereDate('fecha_hora', '>=', $this->filtroFechaDesde))
            ->when($this->filtroFechaHasta, fn ($q) => $q->whereDate('fecha_hora', '<=', $this->filtroFechaHasta))
            ->orderByDesc('fecha_hora')
            ->paginate(12);
    }

    private function getTipos()
    {
        $tipos = Actividad::query()->select('tipo')->distinct()->orderBy('tipo')->pluck('tipo');
        if ($tipos->isEmpty()) {
            $tipos = collect(['RECREATIVA', 'COGNITIVA', 'FISICA', 'SOCIAL', 'EDUCATIVA', 'TERAPEUTICA']);
        }

        return $tipos->map(fn (string $tipo) => (object) ['cod_tipo_act' => $tipo, 'tipo' => Str::headline($tipo), 'nombre' => Str::headline($tipo)]);
    }

    private function getAdultos()
    {
        return Residente::select('cod_residente', 'nombres', 'apellido_paterno', 'apellido_materno', 'fecha_nacimiento')
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

    public function render()
    {
        return view('livewire.actividades.actividades-panel', [
            'stats' => $this->getStats(),
            'actividades' => $this->getActividades(),
            'tipos' => $this->getTipos(),
            'adultos' => $this->getAdultos(),
            'detalle' => $this->getDetalle(),
        ])->layout('layouts.sistema');
    }
}
