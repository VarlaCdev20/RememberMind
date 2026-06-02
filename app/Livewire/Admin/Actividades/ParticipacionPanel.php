<?php

namespace App\Livewire\Admin\Actividades;

use App\Models\ActividadAdulto;
use App\Models\ActividadParticipante;
use App\Models\AdultoMayor;
use App\Models\TipoActividadAdulto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ParticipacionPanel extends Component
{
    use WithPagination;

    // ── Modo de vista ─────────────────────────────────────────────────────────
    public string $viewMode    = 'lista';   // 'lista' | 'participantes'
    public ?int   $actividadId = null;

    // ── Filtros lista de actividades ──────────────────────────────────────────
    public string $searchActividad  = '';
    public string $filtroTipoAct    = '';
    public string $filtroEstadoAct  = '';
    public string $filtroFechaDesde = '';
    public string $filtroFechaHasta = '';

    // ── Búsqueda dentro de participantes de una actividad ─────────────────────
    public string $searchParticipante = '';

    // ── Modal: agregar participante ───────────────────────────────────────────
    public bool   $modalAgregar                  = false;
    public string $searchAdultoAgregar           = '';
    public string $codAmAgregar                  = '';
    public string $estadoAsistenciaAgregar       = 'INSCRITO';
    public string $nivelParticipacionAgregar     = 'NO_APLICA';
    public string $estadoObservadoAgregar        = '';
    public string $observacionIndividualAgregar  = '';
    public bool   $requiereSeguimientoAgregar    = false;

    // ── Autocomplete selección ────────────────────────────────────────────────
    // Se almacena el nombre del adulto seleccionado para mostrar en UI
    public string $nombreAdultoSeleccionado = '';

    // ── Modal: detalle / editar participante ──────────────────────────────────
    public bool   $modalParticipante            = false;
    public bool   $editandoParticipante         = false;
    public ?int   $participanteId               = null;
    public string $estadoAsistenciaEdit         = 'INSCRITO';
    public string $nivelParticipacionEdit       = 'NO_APLICA';
    public string $estadoObservadoEdit          = '';
    public string $observacionIndividualEdit    = '';
    public bool   $requiereSeguimientoEdit      = false;

    // ── Reseteo de paginación al cambiar filtros ───────────────────────────────
    public function updatingSearchActividad(): void  { $this->resetPage(); }
    public function updatingFiltroTipoAct(): void    { $this->resetPage(); }
    public function updatingFiltroEstadoAct(): void  { $this->resetPage(); }
    public function updatingFiltroFechaDesde(): void { $this->resetPage(); }
    public function updatingFiltroFechaHasta(): void { $this->resetPage(); }

    // ── Navegación entre modos ────────────────────────────────────────────────

    public function seleccionarActividad(int $id): void
    {
        $this->actividadId        = $id;
        $this->viewMode           = 'participantes';
        $this->searchParticipante = '';
        $this->resetPage();
    }

    public function volverALista(): void
    {
        $this->actividadId = null;
        $this->viewMode    = 'lista';
        $this->cerrarModales();
        $this->resetPage();
    }

    // ── Modales ───────────────────────────────────────────────────────────────

    public function abrirAgregarParticipante(): void
    {
        $actividad = $this->getActividad();
        if (! $actividad) {
            return;
        }

        if ($actividad->estaCancelada()) {
            $this->dispatch('swal', [
                'icon'  => 'error',
                'title' => 'Actividad cancelada',
                'text'  => 'No se pueden agregar participantes a una actividad cancelada.',
            ]);
            return;
        }

        $this->resetFormAgregar();
        $this->modalAgregar = true;
    }

    public function abrirDetalleParticipante(int $id): void
    {
        $p = ActividadParticipante::find($id);
        if (! $p) {
            return;
        }

        $this->participanteId             = $id;
        $this->editandoParticipante       = false;
        $this->estadoAsistenciaEdit       = $p->estado_asistencia;
        $this->nivelParticipacionEdit     = $p->nivel_participacion ?? 'NO_APLICA';
        $this->estadoObservadoEdit        = $p->estado_observado ?? '';
        $this->observacionIndividualEdit  = $p->observacion_individual ?? '';
        $this->requiereSeguimientoEdit    = (bool) $p->requiere_seguimiento;
        $this->modalParticipante          = true;
    }

    public function abrirEditarParticipante(int $id): void
    {
        $this->abrirDetalleParticipante($id);
        $this->editandoParticipante = true;
    }

    public function cerrarModales(): void
    {
        $this->modalAgregar      = false;
        $this->modalParticipante = false;
        $this->resetFormAgregar();
        $this->resetFormEditar();
    }

    // ── CRUD participantes ────────────────────────────────────────────────────

    public function agregarParticipante(): void
    {
        $this->validate([
            'codAmAgregar'                 => 'required|exists:adulto_mayor,cod_am',
            'estadoAsistenciaAgregar'      => 'required|in:INSCRITO,ASISTIO,FALTO,JUSTIFICADO',
            'nivelParticipacionAgregar'    => 'required|in:ALTA,MEDIA,BAJA,NO_APLICA',
            'estadoObservadoAgregar'       => 'nullable|in:ACTIVO,TRANQUILO,AISLADO,IRRITABLE,CANSADO,COLABORADOR,DESORIENTADO',
            'observacionIndividualAgregar' => 'nullable|string|max:1000',
        ], [
            'codAmAgregar.required'            => 'Seleccione un adulto mayor.',
            'codAmAgregar.exists'              => 'El adulto mayor seleccionado no es válido.',
            'estadoAsistenciaAgregar.required' => 'El estado de asistencia es obligatorio.',
            'estadoAsistenciaAgregar.in'       => 'Estado de asistencia no válido.',
            'nivelParticipacionAgregar.in'     => 'Nivel de participación no válido.',
            'estadoObservadoAgregar.in'        => 'Estado observado no válido.',
            'observacionIndividualAgregar.max' => 'La observación no puede superar 1000 caracteres.',
        ]);

        $nivelFinal = $this->nivelParticipacionAgregar;
        if (in_array($this->estadoAsistenciaAgregar, ['FALTO', 'JUSTIFICADO'])) {
            $nivelFinal = 'NO_APLICA';
        }

        try {
            ActividadParticipante::create([
                'cod_act_adul'          => $this->actividadId,
                'cod_am'                => $this->codAmAgregar,
                'estado_asistencia'     => $this->estadoAsistenciaAgregar,
                'nivel_participacion'   => $nivelFinal,
                'estado_observado'      => $this->estadoObservadoAgregar ?: null,
                'observacion_individual'=> $this->observacionIndividualAgregar ?: null,
                'requiere_seguimiento'  => $this->requiereSeguimientoAgregar,
                'registrado_por'        => Auth::id(),
            ]);

            $this->modalAgregar = false;
            $this->resetFormAgregar();
            $this->dispatch('swal', ['icon' => 'success', 'title' => 'Participante agregado correctamente.']);
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'uq_participante_actividad')) {
                $this->dispatch('swal', [
                    'icon'  => 'warning',
                    'title' => 'Participante duplicado',
                    'text'  => 'Este adulto mayor ya está inscrito en esta actividad.',
                ]);
            } else {
                $this->dispatch('swal', ['icon' => 'error', 'title' => 'Error al agregar participante.']);
            }
        }
    }

    public function guardarParticipante(): void
    {
        $this->validate([
            'estadoAsistenciaEdit'      => 'required|in:INSCRITO,ASISTIO,FALTO,JUSTIFICADO',
            'nivelParticipacionEdit'    => 'required|in:ALTA,MEDIA,BAJA,NO_APLICA',
            'estadoObservadoEdit'       => 'nullable|in:ACTIVO,TRANQUILO,AISLADO,IRRITABLE,CANSADO,COLABORADOR,DESORIENTADO',
            'observacionIndividualEdit' => 'nullable|string|max:1000',
        ], [
            'estadoAsistenciaEdit.required' => 'El estado de asistencia es obligatorio.',
            'estadoAsistenciaEdit.in'       => 'Estado de asistencia no válido.',
            'nivelParticipacionEdit.in'     => 'Nivel de participación no válido.',
            'estadoObservadoEdit.in'        => 'Estado observado no válido.',
            'observacionIndividualEdit.max' => 'La observación no puede superar 1000 caracteres.',
        ]);

        $nivelFinal = $this->nivelParticipacionEdit;
        if (in_array($this->estadoAsistenciaEdit, ['FALTO', 'JUSTIFICADO'])) {
            $nivelFinal = 'NO_APLICA';
        }

        $p = ActividadParticipante::findOrFail($this->participanteId);
        $p->update([
            'estado_asistencia'     => $this->estadoAsistenciaEdit,
            'nivel_participacion'   => $nivelFinal,
            'estado_observado'      => $this->estadoObservadoEdit ?: null,
            'observacion_individual'=> $this->observacionIndividualEdit ?: null,
            'requiere_seguimiento'  => $this->requiereSeguimientoEdit,
        ]);

        $this->editandoParticipante = false;
        $this->modalParticipante    = false;
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Participación actualizada.']);
    }

    public function quitarParticipante(int $id): void
    {
        $p = ActividadParticipante::find($id);
        if (! $p) {
            return;
        }

        $p->delete(); // SoftDelete
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Participante removido. El registro se conserva en el historial.']);
    }

    public function limpiarFiltros(): void
    {
        $this->searchActividad  = '';
        $this->filtroTipoAct    = '';
        $this->filtroEstadoAct  = '';
        $this->filtroFechaDesde = '';
        $this->filtroFechaHasta = '';
        $this->resetPage();
    }

    // ── Helpers privados ──────────────────────────────────────────────────────

    // ── Autocomplete adulto mayor ─────────────────────────────────────────────

    public function seleccionarAdultoAgregar(string $codAm, string $nombre): void
    {
        $this->codAmAgregar             = $codAm;
        $this->nombreAdultoSeleccionado = $nombre;
        $this->searchAdultoAgregar      = '';
    }

    public function limpiarAdultoAgregar(): void
    {
        $this->codAmAgregar             = '';
        $this->nombreAdultoSeleccionado = '';
        $this->searchAdultoAgregar      = '';
    }

    private function resetFormAgregar(): void
    {
        $this->searchAdultoAgregar          = '';
        $this->codAmAgregar                 = '';
        $this->nombreAdultoSeleccionado     = '';
        $this->estadoAsistenciaAgregar      = 'INSCRITO';
        $this->nivelParticipacionAgregar    = 'NO_APLICA';
        $this->estadoObservadoAgregar       = '';
        $this->observacionIndividualAgregar = '';
        $this->requiereSeguimientoAgregar   = false;
        $this->resetValidation();
    }

    private function resetFormEditar(): void
    {
        $this->participanteId            = null;
        $this->editandoParticipante      = false;
        $this->estadoAsistenciaEdit      = 'INSCRITO';
        $this->nivelParticipacionEdit    = 'NO_APLICA';
        $this->estadoObservadoEdit       = '';
        $this->observacionIndividualEdit = '';
        $this->requiereSeguimientoEdit   = false;
        $this->resetValidation();
    }

    private function getActividad(): ?ActividadAdulto
    {
        return $this->actividadId
            ? ActividadAdulto::with(['tipoActividad'])->find($this->actividadId)
            : null;
    }

    private function getParticipantes()
    {
        if (! $this->actividadId) {
            return collect();
        }
        return ActividadParticipante::with(['adultoMayor'])
            ->where('cod_act_adul', $this->actividadId)
            ->when($this->searchParticipante, fn($q) =>
                $q->whereHas('adultoMayor', fn($sq) =>
                    $sq->where('nombres', 'ilike', '%' . $this->searchParticipante . '%')
                      ->orWhere('ap_paterno', 'ilike', '%' . $this->searchParticipante . '%')
                      ->orWhere('ci', 'ilike', '%' . $this->searchParticipante . '%')
                )
            )
            ->orderBy('created_at')
            ->get();
    }

    private function getActividades()
    {
        return ActividadAdulto::with(['tipoActividad'])
            ->withCount(['participantesActivos as total_participantes'])
            ->when($this->searchActividad, fn($q) =>
                $q->where(fn($s) =>
                    $s->where('nombre', 'ilike', '%' . $this->searchActividad . '%')
                      ->orWhereHas('tipoActividad', fn($t) =>
                            $t->where('tipo', 'ilike', '%' . $this->searchActividad . '%'))
                )
            )
            ->when($this->filtroTipoAct, fn($q) => $q->where('cod_tipo_act', (int) $this->filtroTipoAct))
            ->when($this->filtroEstadoAct, fn($q) => $q->where('estado', $this->filtroEstadoAct))
            ->when($this->filtroFechaDesde, fn($q) => $q->whereDate('fecha', '>=', $this->filtroFechaDesde))
            ->when($this->filtroFechaHasta, fn($q) => $q->whereDate('fecha', '<=', $this->filtroFechaHasta))
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->paginate(12);
    }

    private function getAdultosDisponibles()
    {
        // Sin búsqueda activa no cargamos la lista (autocomplete bajo demanda)
        if (strlen(trim($this->searchAdultoAgregar)) < 2) {
            return collect();
        }

        $yaInscritos = ActividadParticipante::where('cod_act_adul', $this->actividadId)
            ->whereNull('deleted_at')
            ->pluck('cod_am');

        return AdultoMayor::select('cod_am', 'nombres', 'ap_paterno', 'ap_materno', 'ci')
            ->whereNull('archivado_en')
            ->whereNotIn('cod_am', $yaInscritos)
            ->where(fn($q) =>
                $q->where('nombres', 'ilike', '%' . $this->searchAdultoAgregar . '%')
                  ->orWhere('ap_paterno', 'ilike', '%' . $this->searchAdultoAgregar . '%')
                  ->orWhere('ap_materno', 'ilike', '%' . $this->searchAdultoAgregar . '%')
                  ->orWhere('ci', 'ilike', '%' . $this->searchAdultoAgregar . '%')
            )
            ->orderBy('ap_paterno')
            ->limit(8)
            ->get();
    }

    private function getAlertasPorParticipante(): array
    {
        if (! $this->actividadId) {
            return [];
        }

        $alertas = [];
        $participantes = ActividadParticipante::where('cod_act_adul', $this->actividadId)
            ->whereNull('deleted_at')
            ->get();

        foreach ($participantes as $p) {
            $flags = [];

            if ($p->requiere_seguimiento) {
                $flags[] = 'seguimiento';
            }

            if (in_array($p->estado_observado, ['AISLADO', 'IRRITABLE', 'DESORIENTADO'])) {
                $flags[] = 'observacion';
            }

            // 3 faltas consecutivas (últimos 3 registros del adulto)
            $ultimas3 = ActividadParticipante::where('cod_am', $p->cod_am)
                ->whereNull('deleted_at')
                ->latest('created_at')
                ->limit(3)
                ->pluck('estado_asistencia')
                ->toArray();

            if (count($ultimas3) === 3 && count(array_filter($ultimas3, fn($e) => in_array($e, ['FALTO', 'JUSTIFICADO']))) === 3) {
                $flags[] = 'faltas_consecutivas';
            }

            // 3 participaciones bajas consecutivas
            $ultimas3niv = ActividadParticipante::where('cod_am', $p->cod_am)
                ->whereNull('deleted_at')
                ->where('estado_asistencia', 'ASISTIO')
                ->latest('created_at')
                ->limit(3)
                ->pluck('nivel_participacion')
                ->toArray();

            if (count($ultimas3niv) === 3 && count(array_filter($ultimas3niv, fn($n) => $n === 'BAJA')) === 3) {
                $flags[] = 'participacion_baja';
            }

            if (! empty($flags)) {
                $alertas[$p->id] = $flags;
            }
        }

        return $alertas;
    }

    private function getStats(): array
    {
        return [
            'total'          => ActividadAdulto::count(),
            'con_participantes' => ActividadAdulto::has('participantesActivos')->count(),
            'total_inscritos'   => ActividadParticipante::whereNull('deleted_at')->count(),
            'requieren_seguimiento' => ActividadParticipante::whereNull('deleted_at')
                ->where('requiere_seguimiento', true)->count(),
        ];
    }

    private function getTipos()
    {
        return TipoActividadAdulto::orderBy('tipo')->get();
    }

    public function render()
    {
        $actividad    = $this->getActividad();
        $participantes = $this->getParticipantes();
        $alertas      = $this->getAlertasPorParticipante();

        return view('livewire.admin.actividades.participacion-panel', [
            'actividad'          => $actividad,
            'participantes'      => $participantes,
            'alertas'            => $alertas,
            'actividades'        => $this->viewMode === 'lista' ? $this->getActividades() : collect(),
            'adultos'            => $this->modalAgregar ? $this->getAdultosDisponibles() : collect(),
            'tipos'              => $this->getTipos(),
            'stats'              => $this->getStats(),
            'detalleParticipante'=> $this->participanteId
                ? ActividadParticipante::with('adultoMayor')->find($this->participanteId)
                : null,
        ])->layout('layouts.sistema');
    }
}
