<?php

namespace App\Livewire\Admin\Actividades;

use App\Models\ActividadAdulto;
use App\Models\ActividadParticipante;
use App\Models\TipoActividadAdulto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

class AsistenciaPanel extends Component
{
    use WithPagination;

    // ── Modo de vista ─────────────────────────────────────────────────────────
    public string $viewMode    = 'lista';   // 'lista' | 'asistencia'
    public ?int   $actividadId = null;

    // ── Filtros lista actividades ─────────────────────────────────────────────
    public string $search           = '';
    public string $filtroTipo       = '';
    public string $filtroEstado     = '';
    public string $filtroFechaDesde = '';
    public string $filtroFechaHasta = '';

    // ── Asistencia inline (clave: id del participante) ────────────────────────
    public array $asistencias = [];
    // Estructura: [ participanteId => [estado_asistencia, nivel_participacion, estado_observado, observacion_individual, requiere_seguimiento] ]

    // ── Modal: cerrar actividad ───────────────────────────────────────────────
    public bool   $modalCerrar        = false;
    public string $resultadoGeneral   = '';
    public string $nivelCumplimiento  = 'NO_EVALUADO';
    public string $incidencias        = '';
    public string $recomendaciones    = '';

    // ── Modal: detalle actividad ──────────────────────────────────────────────
    public bool $modalDetalle      = false;
    public ?int $detalleActividadId = null;

    // ── Reset de paginación ───────────────────────────────────────────────────
    public function updatingSearch(): void           { $this->resetPage(); }
    public function updatingFiltroTipo(): void        { $this->resetPage(); }
    public function updatingFiltroEstado(): void      { $this->resetPage(); }
    public function updatingFiltroFechaDesde(): void  { $this->resetPage(); }
    public function updatingFiltroFechaHasta(): void  { $this->resetPage(); }

    // ── Navegación ────────────────────────────────────────────────────────────

    public function seleccionarActividad(int $id): void
    {
        $actividad = ActividadAdulto::find($id);
        if (! $actividad) {
            return;
        }

        if ($actividad->estaCancelada()) {
            $this->dispatch('swal', [
                'icon'  => 'error',
                'title' => 'Actividad cancelada',
                'text'  => 'No se puede registrar asistencia en una actividad cancelada.',
            ]);
            return;
        }

        $this->actividadId = $id;
        $this->viewMode    = 'asistencia';
        $this->cargarAsistencias();
        $this->resetPage();
    }

    public function volverALista(): void
    {
        $this->actividadId = null;
        $this->viewMode    = 'lista';
        $this->asistencias = [];
        $this->cerrarModales();
        $this->resetPage();
    }

    // ── Carga de datos de asistencia ──────────────────────────────────────────

    public function cargarAsistencias(): void
    {
        if (! $this->actividadId) {
            return;
        }

        $participantes = ActividadParticipante::where('cod_act_adul', $this->actividadId)
            ->whereNull('deleted_at')
            ->get();

        $this->asistencias = [];
        foreach ($participantes as $p) {
            $this->asistencias[$p->id] = [
                'estado_asistencia'     => $p->estado_asistencia ?? 'INSCRITO',
                'nivel_participacion'   => $p->nivel_participacion ?? 'NO_APLICA',
                'estado_observado'      => $p->estado_observado ?? '',
                'observacion_individual'=> $p->observacion_individual ?? '',
                'requiere_seguimiento'  => (bool) ($p->requiere_seguimiento ?? false),
            ];
        }
    }

    // ── Acciones masivas ──────────────────────────────────────────────────────

    public function marcarTodosAsistieron(): void
    {
        foreach ($this->asistencias as $id => &$datos) {
            $datos['estado_asistencia'] = 'ASISTIO';
            if ($datos['nivel_participacion'] === 'NO_APLICA') {
                $datos['nivel_participacion'] = 'MEDIA';
            }
        }
        unset($datos);
    }

    public function marcarTodosFaltaron(): void
    {
        foreach ($this->asistencias as $id => &$datos) {
            $datos['estado_asistencia']   = 'FALTO';
            $datos['nivel_participacion'] = 'NO_APLICA';
        }
        unset($datos);
    }

    public function limpiarAsistencia(): void
    {
        foreach ($this->asistencias as $id => &$datos) {
            $datos['estado_asistencia']     = 'INSCRITO';
            $datos['nivel_participacion']   = 'NO_APLICA';
            $datos['estado_observado']      = '';
            $datos['observacion_individual']= '';
            $datos['requiere_seguimiento']  = false;
        }
        unset($datos);
    }

    // ── Guardar asistencia ────────────────────────────────────────────────────

    public function guardarAsistencia(): void
    {
        if (empty($this->asistencias)) {
            $this->dispatch('swal', ['icon' => 'warning', 'title' => 'No hay participantes registrados para esta actividad.']);
            return;
        }

        // Validación por participante
        $rules    = [];
        $messages = [];
        foreach (array_keys($this->asistencias) as $id) {
            $rules["asistencias.{$id}.estado_asistencia"]   = 'required|in:INSCRITO,ASISTIO,FALTO,JUSTIFICADO';
            $rules["asistencias.{$id}.nivel_participacion"] = 'required|in:ALTA,MEDIA,BAJA,NO_APLICA';
            $rules["asistencias.{$id}.estado_observado"]    = 'nullable|in:ACTIVO,TRANQUILO,AISLADO,IRRITABLE,CANSADO,COLABORADOR,DESORIENTADO';
            $rules["asistencias.{$id}.observacion_individual"] = 'nullable|string|max:1000';

            $messages["asistencias.{$id}.estado_asistencia.required"] = 'El estado de asistencia es obligatorio para cada participante.';
            $messages["asistencias.{$id}.estado_asistencia.in"]       = 'Estado de asistencia no válido.';
            $messages["asistencias.{$id}.nivel_participacion.in"]     = 'Nivel de participación no válido.';
        }
        $this->validate($rules, $messages);

        foreach ($this->asistencias as $participanteId => $datos) {
            $nivelFinal = $datos['nivel_participacion'];
            if (in_array($datos['estado_asistencia'], ['FALTO', 'JUSTIFICADO'])) {
                $nivelFinal = 'NO_APLICA';
            }
            if ($datos['estado_asistencia'] === 'ASISTIO' && $nivelFinal === 'NO_APLICA') {
                $nivelFinal = 'MEDIA';
            }

            ActividadParticipante::find($participanteId)?->update([
                'estado_asistencia'     => $datos['estado_asistencia'],
                'nivel_participacion'   => $nivelFinal,
                'estado_observado'      => $datos['estado_observado'] ?: null,
                'observacion_individual'=> $datos['observacion_individual'] ?: null,
                'requiere_seguimiento'  => (bool) ($datos['requiere_seguimiento'] ?? false),
                'registrado_por'        => Auth::id(),
            ]);
        }

        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Asistencia registrada correctamente.']);
    }

    // ── Cierre de actividad ───────────────────────────────────────────────────

    public function abrirCerrar(): void
    {
        $actividad = $this->getActividad();
        if (! $actividad) {
            return;
        }

        if ($actividad->estaCancelada()) {
            $this->dispatch('swal', [
                'icon'  => 'error',
                'title' => 'Actividad cancelada',
                'text'  => 'No se puede cerrar una actividad cancelada.',
            ]);
            return;
        }

        if ($actividad->participantes()->count() === 0) {
            $this->dispatch('swal', [
                'icon'  => 'warning',
                'title' => 'Sin participantes',
                'text'  => 'Registre al menos un participante antes de cerrar la actividad.',
            ]);
            return;
        }

        $conAsistencia = $actividad->participantes()
            ->where('estado_asistencia', '!=', 'INSCRITO')
            ->count();

        if ($conAsistencia === 0) {
            $this->dispatch('swal', [
                'icon'  => 'warning',
                'title' => 'Sin asistencia registrada',
                'text'  => 'Registre la asistencia de al menos un participante antes de cerrar.',
            ]);
            return;
        }

        $this->resultadoGeneral  = '';
        $this->nivelCumplimiento = 'NO_EVALUADO';
        $this->incidencias       = '';
        $this->recomendaciones   = '';
        $this->modalCerrar       = true;
    }

    public function cerrarActividad(): void
    {
        $this->validate([
            'resultadoGeneral'  => 'required|string|min:5|max:2000',
            'nivelCumplimiento' => 'required|in:ALTO,MEDIO,BAJO,NO_EVALUADO',
            'incidencias'       => 'nullable|string|max:1000',
            'recomendaciones'   => 'nullable|string|max:1000',
        ], [
            'resultadoGeneral.required' => 'El resultado general es obligatorio para cerrar la actividad.',
            'resultadoGeneral.min'      => 'El resultado debe tener al menos 5 caracteres.',
            'nivelCumplimiento.required'=> 'Seleccione el nivel de cumplimiento.',
            'nivelCumplimiento.in'      => 'Nivel de cumplimiento no válido.',
        ]);

        $actividad = $this->getActividad();
        if (! $actividad) {
            return;
        }

        $actividad->update([
            'estado'            => 'EVALUADA',
            'resultado_general' => $this->resultadoGeneral,
            'evaluacion_final'  => $this->resultadoGeneral ?: null,
            'nivel_cumplimiento'=> $this->nivelCumplimiento,
            'incidencias'       => $this->incidencias ?: null,
            'recomendaciones'   => $this->recomendaciones ?: null,
            'updated_by'        => Auth::id(),
            // obs conserva su valor previo sin sobrescribirse
        ]);

        activity('Actividades')
            ->performedOn($actividad)
            ->log("Actividad \"{$actividad->nombre}\" cerrada y evaluada. Nivel: {$this->nivelCumplimiento}.");

        $this->modalCerrar = false;
        $this->viewMode    = 'lista';
        $this->actividadId = null;
        $this->asistencias = [];

        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Actividad cerrada y evaluada correctamente.']);
    }

    // ── Acciones rápidas (compatibilidad legacy) ──────────────────────────────

    public function marcarRealizada(int $id): void
    {
        ActividadAdulto::findOrFail($id)->update(['estado' => 'REALIZADA']);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Actividad marcada como realizada.']);
    }

    public function marcarCancelada(int $id): void
    {
        ActividadAdulto::findOrFail($id)->update(['estado' => 'CANCELADA']);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Actividad cancelada. Historial conservado.']);
    }

    // ── Modales ───────────────────────────────────────────────────────────────

    public function abrirDetalle(int $id): void
    {
        $this->detalleActividadId = $id;
        $this->modalDetalle       = true;
    }

    public function cerrarModales(): void
    {
        $this->modalCerrar      = false;
        $this->modalDetalle     = false;
        $this->detalleActividadId = null;
        $this->resetValidation();
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

    // ── Helpers privados ──────────────────────────────────────────────────────

    private function getActividad(): ?ActividadAdulto
    {
        return $this->actividadId
            ? ActividadAdulto::with(['tipoActividad', 'participantesActivos.adultoMayor'])->find($this->actividadId)
            : null;
    }

    private function getRegistros()
    {
        if (! Schema::hasTable('actividades_adulto')) {
            return ActividadAdulto::paginate(12);
        }
        return ActividadAdulto::with(['tipoActividad'])
            ->withCount(['participantesActivos as total_participantes'])
            ->when($this->search, fn($q) =>
                $q->where(fn($s) =>
                    $s->where('nombre', 'ilike', '%' . $this->search . '%')
                      ->orWhereHas('tipoActividad', fn($t) =>
                            $t->where('tipo', 'ilike', '%' . $this->search . '%'))
                )
            )
            ->when($this->filtroTipo, fn($q) => $q->where('cod_tipo_act', (int) $this->filtroTipo))
            ->when($this->filtroEstado, function ($q) {
                return match ($this->filtroEstado) {
                    'PROGRAMADA'   => $q->whereIn('estado', ['PROGRAMADA', 'PENDIENTE']),
                    'EN_CURSO'     => $q->where('estado', 'EN_CURSO'),
                    'REALIZADA'    => $q->whereIn('estado', ['REALIZADA', 'COMPLETADA', 'FINALIZADA']),
                    'EVALUADA'     => $q->where('estado', 'EVALUADA'),
                    'CANCELADA'    => $q->whereIn('estado', ['CANCELADA', 'ANULADA']),
                    'REPROGRAMADA' => $q->where('estado', 'REPROGRAMADA'),
                    default        => $q,
                };
            })
            ->when($this->filtroFechaDesde, fn($q) => $q->whereDate('fecha', '>=', $this->filtroFechaDesde))
            ->when($this->filtroFechaHasta, fn($q) => $q->whereDate('fecha', '<=', $this->filtroFechaHasta))
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->paginate(12);
    }

    private function getTipos()
    {
        return TipoActividadAdulto::orderBy('tipo')->get();
    }

    private function getStats(): array
    {
        return [
            'total'        => ActividadAdulto::count(),
            'pendientes'   => ActividadAdulto::whereIn('estado', ['PROGRAMADA', 'EN_CURSO', 'PENDIENTE'])->count(),
            'evaluadas'    => ActividadAdulto::where('estado', 'EVALUADA')->count(),
            'realizadas'   => ActividadAdulto::whereIn('estado', ['REALIZADA', 'COMPLETADA', 'FINALIZADA'])->count(),
            'canceladas'   => ActividadAdulto::whereIn('estado', ['CANCELADA', 'ANULADA'])->count(),
            'hoy'          => ActividadAdulto::whereDate('fecha', today())->count(),
            'requieren_seguimiento' => ActividadParticipante::whereNull('deleted_at')
                ->where('requiere_seguimiento', true)->count(),
        ];
    }

    private function getAlertasAsistencia(): array
    {
        if (! $this->actividadId) {
            return [];
        }

        $alertas = [];
        foreach ($this->asistencias as $participanteId => $datos) {
            $flags = [];

            if (! empty($datos['requiere_seguimiento'])) {
                $flags[] = 'seguimiento';
            }
            if (in_array($datos['estado_observado'] ?? '', ['AISLADO', 'IRRITABLE', 'DESORIENTADO'])) {
                $flags[] = 'observacion';
            }

            if (! empty($flags)) {
                $alertas[$participanteId] = $flags;
            }
        }

        return $alertas;
    }

    public function render()
    {
        $actividad   = $this->viewMode === 'asistencia' ? $this->getActividad() : null;
        $alertas     = $this->getAlertasAsistencia();

        return view('livewire.admin.actividades.asistencia-panel', [
            'stats'     => $this->getStats(),
            'registros' => $this->viewMode === 'lista' ? $this->getRegistros() : collect(),
            'tipos'     => $this->getTipos(),
            'actividad' => $actividad,
            'alertas'   => $alertas,
            'detalleActividad' => $this->detalleActividadId
                ? ActividadAdulto::with(['tipoActividad', 'participantesActivos.adultoMayor'])->find($this->detalleActividadId)
                : null,
        ])->layout('layouts.sistema');
    }
}
