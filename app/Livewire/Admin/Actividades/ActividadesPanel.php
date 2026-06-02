<?php

namespace App\Livewire\Admin\Actividades;

use App\Models\ActividadAdulto;
use App\Models\AdultoMayor;
use App\Models\TipoActividadAdulto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
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
    public bool $modalCerrar    = false;

    // ── Campos formulario — datos principales ─────────────────────────────────
    public string $codAm      = '';
    public string $codTipoAct = '';
    public string $estado     = 'PROGRAMADA';
    public string $nombre     = '';
    public string $descripcion = '';
    public string $objetivo   = '';
    public string $obs        = '';

    // ── Campos formulario — programación ──────────────────────────────────────
    public string $fecha      = '';
    public string $hora       = '';
    public string $horaFin    = '';
    public string $lugar      = '';
    public string $cupoMaximo = '';

    // ── Campos formulario — logística ─────────────────────────────────────────
    public string $materiales = '';
    public string $color      = '';

    // ── Modal cerrar actividad ────────────────────────────────────────────────
    public ?int   $cerrandoId        = null;
    public string $resultadoGeneral  = '';   // EXITOSA|REGULAR|CON_INCIDENCIAS|NO_REALIZADA
    public string $nivelCumplimiento = 'NO_EVALUADO';
    public string $evaluacionFinal   = '';   // texto libre → se guarda en obs
    public string $incidencias       = '';
    public string $recomendaciones   = '';

    // ── Tracking ──────────────────────────────────────────────────────────────
    public ?int $editandoId = null;
    public ?int $detalleId  = null;

    // ── Validaciones ──────────────────────────────────────────────────────────

    protected function rules(): array
    {
        return [
            // Obligatorios
            'codTipoAct' => 'required|exists:tipo_actividades_adulto,cod_tipo_act',
            'fecha'      => 'required|date',
            'hora'       => 'required',
            'estado'     => 'required|string|max:50',
            // Opcionales — adulto (solo en actividad directa individual)
            'codAm'      => 'nullable|exists:adulto_mayor,cod_am',
            // Opcionales — descriptivos
            'nombre'     => 'nullable|string|max:200',
            'descripcion'=> 'nullable|string|max:1000',
            'objetivo'   => 'nullable|string|max:1000',
            'obs'        => 'nullable|string|max:2000',
            // Opcionales — programación
            'horaFin'    => 'nullable|date_format:H:i|after:hora',
            'lugar'      => 'nullable|string|max:200',
            'cupoMaximo' => 'nullable|integer|min:1|max:500',
            // Opcionales — logística
            'materiales' => 'nullable|string|max:500',
            'color'      => 'nullable|string|max:10',
        ];
    }

    protected function messages(): array
    {
        return [
            'codTipoAct.required'   => 'Seleccione el tipo de actividad.',
            'codTipoAct.exists'     => 'El tipo de actividad no es válido.',
            'codAm.exists'          => 'El adulto mayor seleccionado no es válido.',
            'fecha.required'        => 'La fecha es obligatoria.',
            'hora.required'         => 'La hora de inicio es obligatoria.',
            'estado.required'       => 'El estado es obligatorio.',
            'horaFin.after'         => 'La hora de finalización debe ser posterior a la hora de inicio.',
            'horaFin.date_format'   => 'Formato de hora inválido (use HH:MM).',
            'nombre.max'            => 'El nombre no puede superar 200 caracteres.',
            'cupoMaximo.integer'    => 'El cupo máximo debe ser un número entero.',
            'cupoMaximo.min'        => 'El cupo máximo debe ser al menos 1.',
            'cupoMaximo.max'        => 'El cupo máximo no puede superar 500.',
        ];
    }

    // ── Reset de paginación ───────────────────────────────────────────────────
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

    public function abrirEditar(int $id): void
    {
        $a = ActividadAdulto::findOrFail($id);
        $this->editandoId  = $id;
        // Datos principales
        $this->codAm       = $a->cod_am ?? '';
        $this->codTipoAct  = (string) $a->cod_tipo_act;
        $this->estado      = $a->estado;
        $this->nombre      = $a->nombre ?? '';
        $this->descripcion = $a->descripcion ?? '';
        $this->objetivo    = $a->objetivo ?? '';
        $this->obs         = $a->obs ?? '';
        // Programación
        $this->fecha       = $a->fecha->format('Y-m-d');
        $this->hora        = substr($a->hora ?? '', 0, 5);
        $this->horaFin     = $a->hora_fin ? substr($a->hora_fin, 0, 5) : '';
        $this->lugar       = $a->lugar ?? '';
        $this->cupoMaximo  = $a->cupo_maximo ? (string) $a->cupo_maximo : '';
        // Logística
        $this->materiales  = $a->materiales ?? '';
        $this->color       = $a->color ?? '';

        $this->resetValidation();
        $this->modalEditar = true;
    }

    public function abrirDetalle(int $id): void
    {
        $this->detalleId    = $id;
        $this->modalDetalle = true;
    }

    public function cerrarModales(): void
    {
        $this->modalRegistrar = false;
        $this->modalEditar    = false;
        $this->modalDetalle   = false;
        $this->modalCerrar    = false;
        $this->cerrandoId     = null;
        $this->resetForm();
        $this->resetValidation();
    }

    // ── Cierre de actividad ───────────────────────────────────────────────────

    public function abrirCerrar(int $id): void
    {
        $actividad = ActividadAdulto::with('participantes')->find($id);
        if (! $actividad) {
            return;
        }

        if ($actividad->estaCancelada()) {
            $this->dispatch('swal', [
                'icon'  => 'error',
                'title' => 'No se puede cerrar una actividad cancelada.',
            ]);
            return;
        }

        if ($actividad->estaEvaluada()) {
            $this->dispatch('swal', [
                'icon'  => 'info',
                'title' => 'Esta actividad ya fue evaluada y cerrada.',
            ]);
            return;
        }

        if ($actividad->participantes()->count() === 0) {
            $this->dispatch('swal', [
                'icon'  => 'warning',
                'title' => 'Sin participantes registrados.',
                'text'  => 'Agregue participantes en el módulo de Participación antes de cerrar.',
            ]);
            return;
        }

        $conAsistencia = $actividad->participantes()
            ->where('estado_asistencia', '!=', 'INSCRITO')
            ->count();

        if ($conAsistencia === 0) {
            $this->dispatch('swal', [
                'icon'  => 'warning',
                'title' => 'Sin asistencia registrada.',
                'text'  => 'Registre la asistencia de los participantes antes de cerrar la actividad.',
            ]);
            return;
        }

        $this->cerrandoId        = $id;
        $this->resultadoGeneral  = $actividad->resultado_general ?? '';
        $this->nivelCumplimiento = $actividad->nivel_cumplimiento ?? 'NO_EVALUADO';
        $this->evaluacionFinal   = $actividad->obs ?? '';
        $this->incidencias       = $actividad->incidencias ?? '';
        $this->recomendaciones   = $actividad->recomendaciones ?? '';
        $this->modalCerrar       = true;
    }

    public function confirmarCierre(): void
    {
        $this->validate([
            'resultadoGeneral'  => 'required|in:EXITOSA,REGULAR,CON_INCIDENCIAS,NO_REALIZADA',
            'nivelCumplimiento' => 'required|in:ALTO,MEDIO,BAJO,NO_EVALUADO',
            'evaluacionFinal'   => 'required|string|min:10|max:2000',
            'incidencias'       => 'nullable|string|max:1000',
            'recomendaciones'   => 'nullable|string|max:1000',
        ], [
            'resultadoGeneral.required' => 'Seleccione el resultado general de la actividad.',
            'resultadoGeneral.in'       => 'Resultado general no válido.',
            'nivelCumplimiento.required'=> 'Seleccione el nivel de cumplimiento.',
            'nivelCumplimiento.in'      => 'Nivel de cumplimiento no válido.',
            'evaluacionFinal.required'  => 'La evaluación final es obligatoria.',
            'evaluacionFinal.min'       => 'La evaluación debe tener al menos 10 caracteres.',
        ]);

        $actividad = ActividadAdulto::findOrFail($this->cerrandoId);
        $actividad->update([
            'estado'            => 'EVALUADA',
            'resultado_general' => $this->resultadoGeneral,
            'evaluacion_final'  => $this->evaluacionFinal ?: null,
            'nivel_cumplimiento'=> $this->nivelCumplimiento,
            'incidencias'       => $this->incidencias ?: null,
            'recomendaciones'   => $this->recomendaciones ?: null,
            'updated_by'        => Auth::id(),
            // obs conserva su valor anterior sin sobreescribirlo
        ]);

        activity('Actividades')
            ->performedOn($actividad)
            ->log("Actividad #{$actividad->cod_act_adul} cerrada y evaluada. Resultado: {$this->resultadoGeneral}. Nivel: {$this->nivelCumplimiento}.");

        $this->modalCerrar = false;
        $this->cerrandoId  = null;
        $this->dispatch('swal', [
            'icon'  => 'success',
            'title' => 'Actividad cerrada y evaluada correctamente.',
        ]);
    }

    // ── CRUD principal ────────────────────────────────────────────────────────

    public function guardarActividad(): void
    {
        $this->validate();
        ActividadAdulto::create($this->buildPayload());
        $this->modalRegistrar = false;
        $this->resetForm();
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Actividad registrada correctamente.']);
    }

    public function actualizarActividad(): void
    {
        $this->validate();
        ActividadAdulto::findOrFail($this->editandoId)->update($this->buildPayload());
        $this->modalEditar = false;
        $this->resetForm();
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Actividad actualizada correctamente.']);
    }

    public function cancelarActividad(int $id): void
    {
        $a = ActividadAdulto::findOrFail($id);
        $a->update(['estado' => 'CANCELADA']);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Actividad cancelada. El historial se conserva.']);
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

    private function buildPayload(): array
    {
        $formatHora = fn(string $h) => strlen(trim($h)) === 5 ? $h . ':00' : $h;

        return [
            'cod_am'       => $this->codAm ?: null,
            'cod_tipo_act' => (int) $this->codTipoAct,
            'estado'       => strtoupper(trim($this->estado)),
            'nombre'       => $this->nombre ?: null,
            'descripcion'  => $this->descripcion ?: null,
            'objetivo'     => $this->objetivo ?: null,
            'obs'          => $this->obs ?: null,
            'fecha'        => $this->fecha,
            'hora'         => $formatHora($this->hora),
            'hora_fin'     => $this->horaFin ? $formatHora($this->horaFin) : null,
            'lugar'        => $this->lugar ?: null,
            'cupo_maximo'  => $this->cupoMaximo ? (int) $this->cupoMaximo : null,
            'materiales'   => $this->materiales ?: null,
            'color'        => $this->color ?: null,
            'created_by'   => Auth::id(),
            'updated_by'   => Auth::id(),
        ];
    }

    private function resetForm(): void
    {
        $this->codAm      = '';
        $this->codTipoAct = '';
        $this->estado     = 'PROGRAMADA';
        $this->nombre     = '';
        $this->descripcion = '';
        $this->objetivo   = '';
        $this->obs        = '';
        $this->fecha      = '';
        $this->hora       = '';
        $this->horaFin    = '';
        $this->lugar      = '';
        $this->cupoMaximo = '';
        $this->materiales = '';
        $this->color      = '';
        $this->editandoId = null;
        $this->detalleId  = null;
    }

    private function tablaExiste(): bool
    {
        return Schema::hasTable('actividades_adulto');
    }

    private function getStats(): array
    {
        if (! $this->tablaExiste()) {
            return array_fill_keys(
                ['total','programadas','realizadas','evaluadas','canceladas','reprogramadas','hoy','proximas7','adultos_distintos'],
                0
            );
        }
        return [
            'total'            => ActividadAdulto::count(),
            'programadas'      => ActividadAdulto::whereIn('estado', ['PROGRAMADA', 'EN_CURSO', 'PENDIENTE'])->count(),
            'realizadas'       => ActividadAdulto::whereIn('estado', ['COMPLETADA', 'REALIZADA'])->count(),
            'evaluadas'        => ActividadAdulto::where('estado', 'EVALUADA')->count(),
            'canceladas'       => ActividadAdulto::whereIn('estado', ['CANCELADA', 'ANULADA'])->count(),
            'reprogramadas'    => ActividadAdulto::where('estado', 'REPROGRAMADA')->count(),
            'hoy'              => ActividadAdulto::whereDate('fecha', today())->count(),
            'proximas7'        => ActividadAdulto::whereIn('estado', ['PROGRAMADA', 'EN_CURSO'])
                                    ->whereBetween('fecha', [today(), today()->addDays(7)])->count(),
            'adultos_distintos'=> ActividadAdulto::distinct('cod_am')->count('cod_am'),
        ];
    }

    private function getActividades()
    {
        if (! $this->tablaExiste()) {
            return ActividadAdulto::paginate(12);
        }
        return ActividadAdulto::with(['tipoActividad', 'adultoMayor'])
            ->withCount(['participantesActivos as total_participantes'])
            ->when($this->search, fn($q) =>
                $q->where(fn($sq) =>
                    $sq->where('nombre', 'ilike', '%' . $this->search . '%')
                      ->orWhereHas('adultoMayor', fn($am) =>
                          $am->where('nombres', 'ilike', '%' . $this->search . '%')
                             ->orWhere('ap_paterno', 'ilike', '%' . $this->search . '%')
                             ->orWhere('ap_materno', 'ilike', '%' . $this->search . '%')
                      )
                      ->orWhereHas('tipoActividad', fn($ta) =>
                          $ta->where('tipo', 'ilike', '%' . $this->search . '%')
                      )
                )
            )
            ->when($this->filtroTipo,       fn($q) => $q->where('cod_tipo_act', (int) $this->filtroTipo))
            ->when($this->filtroEstado,     fn($q) => $q->where('estado', $this->filtroEstado))
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

    private function getAdultos()
    {
        return AdultoMayor::select('cod_am', 'nombres', 'ap_paterno', 'ap_materno')
            ->whereNull('archivado_en')
            ->orderBy('ap_paterno')
            ->orderBy('nombres')
            ->get();
    }

    private function getDetalle(): ?ActividadAdulto
    {
        return $this->detalleId
            ? ActividadAdulto::with(['tipoActividad', 'adultoMayor', 'participantesActivos'])
                ->withCount(['participantesActivos as total_participantes'])
                ->find($this->detalleId)
            : null;
    }

    public function render()
    {
        return view('livewire.admin.actividades.actividades-panel', [
            'stats'            => $this->getStats(),
            'actividades'      => $this->getActividades(),
            'tipos'            => $this->getTipos(),
            'adultos'          => $this->getAdultos(),
            'detalle'          => $this->getDetalle(),
            'actividadCerrando'=> $this->cerrandoId
                ? ActividadAdulto::with('tipoActividad')
                    ->withCount('participantesActivos as total_participantes')
                    ->find($this->cerrandoId)
                : null,
        ])->layout('layouts.sistema');
    }
}
