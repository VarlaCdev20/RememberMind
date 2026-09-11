<?php

namespace App\Livewire\Admin\Voluntariado;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class AsignacionesPanel extends Component
{
    use WithPagination;

    public string $search = '';
    public string $fechaFiltro = '';
    public string $fechaDesde = '';
    public string $fechaHasta = '';
    public string $estadoFiltro = '';
    public string $turnoFiltro = '';
    public string $asistenciaFiltro = '';
    public string $voluntarioFiltro = '';

    public bool $mostrarFormulario = false;
    public bool $isEdit = false;
    public ?string $asignacionId = null;
    public string $cod_vol = '';
    public string $cod_am = '';
    public string $fecha_asig = '';
    public string $fecha_fin = '';
    public string $estado = 'Programada';
    public string $obser = '';
    public bool $permitirSinDisponibilidad = false;
    public string $advertenciaDisponibilidad = '';

    public ?string $detalleId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'fechaFiltro' => ['except' => ''],
        'fechaDesde' => ['except' => ''],
        'fechaHasta' => ['except' => ''],
        'estadoFiltro' => ['except' => ''],
        'turnoFiltro' => ['except' => ''],
        'asistenciaFiltro' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->voluntarioFiltro = request('voluntario') ? (string) request('voluntario') : '';
        $this->fechaFiltro = request('fecha') ? (string) request('fecha') : '';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFechaFiltro(): void
    {
        $this->resetPage();
    }

    public function updatingFechaDesde(): void
    {
        $this->resetPage();
    }

    public function updatingFechaHasta(): void
    {
        $this->resetPage();
    }

    public function updatingEstadoFiltro(): void
    {
        $this->resetPage();
    }

    public function updatingTurnoFiltro(): void
    {
        $this->resetPage();
    }

    public function updatingAsistenciaFiltro(): void
    {
        $this->resetPage();
    }

    public function updatedCodVol(): void
    {
        $this->permitirSinDisponibilidad = false;
        $this->actualizarAdvertenciaDisponibilidad();
    }

    public function updatedFechaAsig(): void
    {
        $this->permitirSinDisponibilidad = false;
        $this->actualizarAdvertenciaDisponibilidad();
    }

    public function abrirCrear(): void
    {
        abort_unless(auth()->user()->can('asignaciones.crear'), 403);

        $this->resetForm();
        $this->isEdit = false;
        $this->mostrarFormulario = true;
    }

    public function editar(string $id): void
    {
        abort_unless(auth()->user()->can('asignaciones.editar'), 403);

        $asignacion = DB::table('asignacion_voluntarios')->where('cod_asig_vol', $id)->first();
        if (! $asignacion) {
            return;
        }

        $this->resetValidation();
        $this->asignacionId = (string) $asignacion->cod_asig_vol;
        $this->cod_vol = (string) $asignacion->cod_vol;
        $this->cod_am = (string) $asignacion->cod_am;
        $this->fecha_asig = $asignacion->fecha_asig ? Carbon::parse($asignacion->fecha_asig)->format('Y-m-d') : '';
        $this->fecha_fin = $asignacion->fecha_fin ? Carbon::parse($asignacion->fecha_fin)->format('Y-m-d') : '';
        $this->estado = $this->normalizarEstado($asignacion->estado);
        $this->obser = (string) $asignacion->obser;
        $this->permitirSinDisponibilidad = true;
        $this->actualizarAdvertenciaDisponibilidad();
        $this->isEdit = true;
        $this->mostrarFormulario = true;
    }

    public function reprogramar(string $id): void
    {
        abort_unless(auth()->user()->can('asignaciones.editar'), 403);

        $this->editar($id);
        $this->estado = 'Reprogramada';
    }

    public function cerrarFormulario(): void
    {
        $this->mostrarFormulario = false;
        $this->resetForm();
    }

    public function guardar(): void
    {
        $this->guardarAsignacion(false);
    }

    public function guardarBajoResponsabilidad(): void
    {
        $this->guardarAsignacion(true);
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['search', 'fechaFiltro', 'fechaDesde', 'fechaHasta', 'estadoFiltro', 'turnoFiltro', 'asistenciaFiltro', 'voluntarioFiltro']);
        $this->resetPage();
    }

    public function verDetalle(string $id): void
    {
        $this->detalleId = $id;
    }

    public function cerrarDetalle(): void
    {
        $this->detalleId = null;
    }

    public function confirmarAsignacion(string $id): void
    {
        $this->cambiarEstado($id, 'Confirmada', 'Asignación confirmada', 'El apoyo quedó listo para seguimiento institucional.');
    }

    public function marcarCumplida(string $id): void
    {
        $this->cambiarEstado($id, 'Cumplida', 'Asignación marcada como cumplida', 'La asignación queda registrada como apoyo cumplido.');
    }

    public function cancelarAsignacion(string $id): void
    {
        $this->cambiarEstado($id, 'Cancelada', 'Asignación cancelada', 'La asignación no fue eliminada; queda registrada como cancelada.');
    }

    public function render()
    {
        $registros = $this->asignacionesQuery()
            ->paginate(8)
            ->through(fn (object $item) => $this->decorarAsignacion($item));

        return view('livewire.voluntariado.asignaciones-panel', [
            'metricas' => $this->metricas(),
            'estados' => $this->estados(),
            'turnos' => $this->turnos(),
            'asistencias' => $this->estadosAsistencia(),
            'registros' => $registros,
            'proximasAsignaciones' => $this->proximasAsignaciones(),
            'detalleAsignacion' => $this->detalleAsignacion(),
            'voluntariosActivos' => $this->voluntariosActivos(),
            'adultosMayores' => $this->adultosMayores(),
            'disponibilidadFormulario' => $this->resumenDisponibilidadFormulario(),
            'linksCabecera' => [
                'resumen' => route('admin.voluntariado.index'),
                'disponibilidad' => route('admin.voluntariado.disponibilidad.index'),
                'asistencia' => route('admin.voluntariado.asistencia.index'),
            ],
        ]);
    }

    private function guardarAsignacion(bool $forzar): void
    {
        $permiso = $this->isEdit ? 'asignaciones.editar' : 'asignaciones.crear';
        abort_unless(auth()->user()->can($permiso), 403);

        $validated = $this->validate($this->rules(), $this->messages());

        if (! $this->voluntarioEstaActivo((string) $validated['cod_vol'])) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Voluntario no activo',
                'text' => 'Solo se puede programar asignaciones para voluntarios activos.',
            ]);
            return;
        }

        if ($this->asignacionDuplicada($validated)) {
            $this->addError('fecha_asig', 'Este voluntario ya tiene una asignación igual para esa fecha.');
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Asignación duplicada',
                'text' => 'Ya existe una asignación igual para el voluntario seleccionado.',
            ]);
            return;
        }

        $advertencia = $this->advertenciaDisponibilidad($validated);
        if ($advertencia && ! $forzar && ! $this->permitirSinDisponibilidad) {
            $this->advertenciaDisponibilidad = $advertencia;
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Disponibilidad por revisar',
                'text' => $advertencia,
            ]);
            return;
        }

        $payload = [
            'cod_vol' => $validated['cod_vol'],
            'cod_am' => $validated['cod_am'],
            'fecha_asig' => $validated['fecha_asig'],
            'fecha_fin' => $validated['fecha_fin'] ?: null,
            'estado' => $validated['estado'],
            'obser' => $validated['obser'] ?: null,
            'updated_at' => now(),
        ];

        $eraEdicion = $this->isEdit;

        if ($this->isEdit) {
            DB::table('asignacion_voluntarios')
                ->where('cod_asig_vol', $this->asignacionId)
                ->update($payload);
        } else {
            DB::table('asignacion_voluntarios')->insert($payload + [
                'cod_asig_vol' => $this->siguienteCodigoAsignacion(),
                'created_at' => now(),
            ]);
        }

        $this->mostrarFormulario = false;
        $this->resetForm();
        $this->resetPage();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => $eraEdicion ? 'Asignación actualizada' : 'Asignación registrada',
            'text' => 'La programación del voluntario quedó registrada correctamente.',
        ]);
    }

    private function rules(): array
    {
        return [
            'cod_vol' => ['required', Rule::exists('voluntarios', 'cod_vol')],
            'cod_am' => ['required', Rule::exists('adulto_mayor', 'cod_am')],
            'fecha_asig' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_asig'],
            'estado' => ['required', Rule::in($this->estados())],
            'obser' => ['nullable', 'string', 'max:700'],
        ];
    }

    private function messages(): array
    {
        return [
            'cod_vol.required' => 'Selecciona un voluntario.',
            'cod_vol.exists' => 'El voluntario seleccionado no existe.',
            'cod_am.required' => 'Selecciona el adulto mayor relacionado.',
            'cod_am.exists' => 'El adulto mayor seleccionado no existe.',
            'fecha_asig.required' => 'Registra la fecha de asignación.',
            'fecha_asig.date' => 'Registra una fecha válida.',
            'fecha_fin.date' => 'Registra una fecha de fin válida.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha inicial.',
            'estado.required' => 'Selecciona el estado de la asignación.',
            'estado.in' => 'Selecciona un estado válido.',
            'obser.max' => 'La observación no debe superar 700 caracteres.',
        ];
    }

    private function asignacionesQuery(bool $ordenar = true): Builder
    {
        $query = DB::table('asignacion_voluntarios as a')
            ->join('voluntarios as v', 'a.cod_vol', '=', 'v.cod_vol')
            ->join('users as u', 'v.cod_usu', '=', 'u.cod_usu')
            ->leftJoin('adulto_mayor as am', 'a.cod_am', '=', 'am.cod_am')
            ->select([
                'a.cod_asig_vol',
                'a.fecha_asig',
                'a.fecha_fin',
                'a.estado',
                'a.obser',
                'a.cod_am',
                'a.cod_vol',
                'v.estado as voluntario_estado',
                Schema::hasColumn('voluntarios', 'archivado_en') ? 'v.archivado_en as voluntario_archivado_en' : DB::raw('NULL AS voluntario_archivado_en'),
                Schema::hasColumn('voluntarios', 'area_apoyo') ? 'v.area_apoyo' : DB::raw('NULL AS area_apoyo'),
                'u.nombres as voluntario_nombres',
                'u.ap_paterno as voluntario_ap_paterno',
                'u.ap_materno as voluntario_ap_materno',
                'u.numero_documento',
                'am.nombres as adulto_nombres',
                'am.ap_paterno as adulto_ap_paterno',
                'am.ap_materno as adulto_ap_materno',
                'am.ci as adulto_ci',
                DB::raw('(select count(*) from asistencia_voluntarios asi where asi.cod_vol = a.cod_vol and asi.fecha = a.fecha_asig) as asistencia_count'),
            ]);

        if ($this->voluntarioFiltro !== '') {
            $query->where('a.cod_vol', $this->voluntarioFiltro);
        }

        if ($this->search !== '') {
            $term = '%' . trim($this->search) . '%';
            $query->where(function (Builder $q) use ($term) {
                $q->whereLike('u.nombres', $term)
                    ->orWhereLike('u.ap_paterno', $term)
                    ->orWhereLike('u.ap_materno', $term)
                    ->orWhereLike('u.numero_documento', $term)
                    ->orWhereLike('am.nombres', $term)
                    ->orWhereLike('am.ap_paterno', $term)
                    ->orWhereLike('am.ap_materno', $term)
                    ->orWhereLike('am.ci', $term);
            });
        }

        if ($this->fechaFiltro !== '') {
            $query->whereDate('a.fecha_asig', $this->fechaFiltro);
        }

        if ($this->fechaDesde !== '') {
            $query->whereDate('a.fecha_asig', '>=', $this->fechaDesde);
        }

        if ($this->fechaHasta !== '') {
            $query->whereDate('a.fecha_asig', '<=', $this->fechaHasta);
        }

        if ($this->estadoFiltro !== '') {
            $query->where('a.estado', $this->estadoFiltro);
        }

        if ($this->turnoFiltro !== '') {
            $this->aplicarFiltroTurno($query, $this->turnoFiltro);
        }

        if ($this->asistenciaFiltro !== '') {
            $this->aplicarFiltroAsistencia($query, $this->asistenciaFiltro);
        }

        if ($ordenar) {
            $query->orderBy('a.fecha_asig')
                ->orderBy('u.ap_paterno')
                ->orderBy('u.nombres');
        }

        return $query;
    }

    private function aplicarFiltroTurno(Builder $query, string $turno): void
    {
        $query->whereExists(function (Builder $subquery) use ($turno) {
            $subquery
                ->select('d.cod_hor_vol')
                ->from('disponibilidad_voluntarios as d')
                ->whereColumn('d.cod_vol', 'a.cod_vol')
                ->whereRaw('d.dia_semana = ' . $this->diaSqlDesdeFecha('a.fecha_asig'));

            match ($turno) {
                'Mañana' => $subquery->where('d.hora_inicio', '<', '12:00'),
                'Tarde' => $subquery->where('d.hora_inicio', '>=', '12:00')->where('d.hora_inicio', '<', '18:00'),
                'Noche' => $subquery->where('d.hora_inicio', '>=', '18:00'),
                'Flexible' => $subquery->where(function (Builder $q) {
                    $q->whereNull('d.hora_inicio')->orWhereNull('d.hora_fin');
                }),
                default => null,
            };
        });
    }

    private function aplicarFiltroAsistencia(Builder $query, string $asistencia): void
    {
        if ($asistencia === 'Registrada') {
            $query->whereExists(function (Builder $subquery) {
                $subquery->select('asi.cod_asis_vol')
                    ->from('asistencia_voluntarios as asi')
                    ->whereColumn('asi.cod_vol', 'a.cod_vol')
                    ->whereColumn('asi.fecha', 'a.fecha_asig');
            });
        }

        if ($asistencia === 'Pendiente') {
            $query->whereDate('a.fecha_asig', '<=', today())
                ->whereNotExists(function (Builder $subquery) {
                    $subquery->select('asi.cod_asis_vol')
                        ->from('asistencia_voluntarios as asi')
                        ->whereColumn('asi.cod_vol', 'a.cod_vol')
                        ->whereColumn('asi.fecha', 'a.fecha_asig');
                })
                ->whereNotIn('a.estado', ['Cancelada']);
        }
    }

    private function metricas(): array
    {
        $hoy = today()->toDateString();

        $activas = DB::table('asignacion_voluntarios')
            ->where(fn (Builder $query) => $this->whereEstadoActivoAsignacion($query))
            ->where(function (Builder $query) use ($hoy) {
                $query->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', $hoy);
            })
            ->count();

        $hoyCount = DB::table('asignacion_voluntarios')
            ->whereDate('fecha_asig', '<=', $hoy)
            ->where(function (Builder $query) use ($hoy) {
                $query->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', $hoy);
            })
            ->where(fn (Builder $query) => $this->whereEstadoActivoAsignacion($query))
            ->count();

        $proximas = DB::table('asignacion_voluntarios')
            ->whereDate('fecha_asig', '>', $hoy)
            ->where(fn (Builder $query) => $this->whereEstadoActivoAsignacion($query))
            ->count();

        $cumplidas = DB::table('asignacion_voluntarios')->whereIn('estado', ['Cumplida', 'CUMPLIDA', 'cumplida'])->count();
        $canceladas = DB::table('asignacion_voluntarios')->whereIn('estado', ['Cancelada', 'CANCELADA', 'cancelada'])->count();
        $reprogramadas = DB::table('asignacion_voluntarios')->whereIn('estado', ['Reprogramada', 'REPROGRAMADA', 'reprogramada'])->count();

        $sinAsistencia = DB::table('asignacion_voluntarios as a')
            ->whereDate('a.fecha_asig', '<=', $hoy)
            ->where(fn (Builder $query) => $this->whereEstadoNoCancelado($query, 'a.estado'))
            ->whereNotExists(function (Builder $query) {
                $query->select('asi.cod_asis_vol')
                    ->from('asistencia_voluntarios as asi')
                    ->whereColumn('asi.cod_vol', 'a.cod_vol')
                    ->whereColumn('asi.fecha', 'a.fecha_asig');
            })
            ->count();

        $voluntariosAsignados = DB::table('asignacion_voluntarios')
            ->where(fn (Builder $query) => $this->whereEstadoActivoAsignacion($query))
            ->distinct()
            ->count('cod_vol');

        return [
            ['label' => 'Asignaciones activas', 'valor' => $activas, 'subtitulo' => 'Programación vigente', 'icono' => 'ph-handshake', 'tono' => 'azul'],
            ['label' => 'Asignaciones de hoy', 'valor' => $hoyCount, 'subtitulo' => now()->format('d/m/Y'), 'icono' => 'ph-calendar-check', 'tono' => 'dorado'],
            ['label' => 'Próximas', 'valor' => $proximas, 'subtitulo' => 'Desde mañana', 'icono' => 'ph-calendar-plus', 'tono' => 'azul'],
            ['label' => 'Cumplidas', 'valor' => $cumplidas, 'subtitulo' => 'Cierre operativo', 'icono' => 'ph-check-circle', 'tono' => 'verde'],
            ['label' => 'Canceladas', 'valor' => $canceladas, 'subtitulo' => 'Sin eliminación', 'icono' => 'ph-x-circle', 'tono' => 'terracota'],
            ['label' => 'Sin asistencia', 'valor' => $sinAsistencia, 'subtitulo' => 'Requieren registro', 'icono' => 'ph-warning-circle', 'tono' => 'dorado'],
            ['label' => 'Reprogramadas', 'valor' => $reprogramadas, 'subtitulo' => 'Fecha por revisar', 'icono' => 'ph-arrows-clockwise', 'tono' => 'azul'],
            ['label' => 'Voluntarios asignados', 'valor' => $voluntariosAsignados, 'subtitulo' => 'Equipo vinculado', 'icono' => 'ph-users-three', 'tono' => 'verde'],
        ];
    }

    private function proximasAsignaciones(): Collection
    {
        return $this->asignacionesQuery(false)
            ->whereDate('a.fecha_asig', '>=', today())
            ->where(fn (Builder $query) => $this->whereEstadoActivoAsignacion($query, 'a.estado'))
            ->orderBy('a.fecha_asig')
            ->limit(5)
            ->get()
            ->map(fn (object $item) => $this->decorarAsignacion($item));
    }

    private function detalleAsignacion(): ?object
    {
        if (! $this->detalleId) {
            return null;
        }

        $item = $this->asignacionesQuery(false)
            ->where('a.cod_asig_vol', $this->detalleId)
            ->first();

        return $item ? $this->decorarAsignacion($item) : null;
    }

    private function decorarAsignacion(object $item): object
    {
        $item->voluntario_nombre = trim(($item->voluntario_nombres ?? '') . ' ' . ($item->voluntario_ap_paterno ?? '') . ' ' . ($item->voluntario_ap_materno ?? ''));
        $item->adulto_nombre = trim(($item->adulto_nombres ?? '') . ' ' . ($item->adulto_ap_paterno ?? '') . ' ' . ($item->adulto_ap_materno ?? ''));
        $item->estado_normalizado = $this->normalizarEstado($item->estado);
        $item->tipo_operativo = 'Acompañamiento institucional';
        $item->responsable_operativo = 'Equipo institucional';
        $item->fecha_texto = $item->fecha_asig ? Carbon::parse($item->fecha_asig)->format('d/m/Y') : 'Sin fecha';
        $item->fecha_fin_texto = $item->fecha_fin ? Carbon::parse($item->fecha_fin)->format('d/m/Y') : null;
        $item->dia_semana = $item->fecha_asig ? $this->diaDesdeFecha(Carbon::parse($item->fecha_asig)) : 'Sin día';
        $item->disponibilidad = $this->resumenDisponibilidad((string) $item->cod_vol, (string) $item->fecha_asig);
        $item->turno = $item->disponibilidad['turno'];
        $item->horario = $item->disponibilidad['horario'];
        $item->asistencia_estado = $this->estadoAsistenciaAsignacion($item);

        return $item;
    }

    private function resumenDisponibilidadFormulario(): array
    {
        if (! $this->cod_vol || ! $this->fecha_asig) {
            return ['horario' => 'Seleccione voluntario y fecha', 'turno' => 'Sin dato', 'estado' => 'Pendiente'];
        }

        return $this->resumenDisponibilidad((string) $this->cod_vol, $this->fecha_asig);
    }

    private function resumenDisponibilidad(string $codVol, string $fecha): array
    {
        if (! $fecha) {
            return ['horario' => 'Sin fecha asignada', 'turno' => 'Sin dato', 'estado' => 'Pendiente'];
        }

        $dia = $this->diaDesdeFecha(Carbon::parse($fecha));
        $items = DB::table('disponibilidad_voluntarios')
            ->where('cod_vol', $codVol)
            ->whereIn('dia_semana', $this->variantesDia($dia))
            ->whereRaw("upper(coalesce(observaciones, '')) not like '%[NO DISPONIBLE]%'")
            ->orderBy('hora_inicio')
            ->get();

        if ($items->isEmpty()) {
            return ['horario' => 'Sin disponibilidad registrada', 'turno' => 'Sin disponibilidad', 'estado' => 'Sin disponibilidad'];
        }

        $horarios = $items->map(function (object $item) {
            if (! $item->hora_inicio || ! $item->hora_fin) {
                return 'Horario flexible';
            }

            return substr((string) $item->hora_inicio, 0, 5) . ' - ' . substr((string) $item->hora_fin, 0, 5);
        })->unique()->values();

        $turnos = $items->map(fn (object $item) => $this->turnoDesdeHora($item->hora_inicio))->unique()->values();

        return [
            'horario' => $horarios->take(2)->implode(' / ') . ($horarios->count() > 2 ? ' +' . ($horarios->count() - 2) : ''),
            'turno' => $turnos->take(2)->implode(' / '),
            'estado' => 'Disponible',
        ];
    }

    private function advertenciaDisponibilidad(array $data): string
    {
        $fecha = Carbon::parse($data['fecha_asig']);
        $dia = $this->diaDesdeFecha($fecha);

        $tieneDisponibilidad = DB::table('disponibilidad_voluntarios')
            ->where('cod_vol', $data['cod_vol'])
            ->whereIn('dia_semana', $this->variantesDia($dia))
            ->whereRaw("upper(coalesce(observaciones, '')) not like '%[NO DISPONIBLE]%'")
            ->exists();

        if (! $tieneDisponibilidad) {
            return 'El voluntario no tiene disponibilidad registrada para ' . $dia . '.';
        }

        $otraAsignacionMismoDia = DB::table('asignacion_voluntarios')
            ->where('cod_vol', $data['cod_vol'])
            ->whereDate('fecha_asig', $data['fecha_asig'])
            ->when($this->isEdit && $this->asignacionId, fn (Builder $query) => $query->where('cod_asig_vol', '<>', $this->asignacionId))
            ->whereNotIn('estado', ['Cancelada', 'CANCELADA', 'cancelada'])
            ->exists();

        if ($otraAsignacionMismoDia) {
            return 'El voluntario ya tiene otra asignación registrada para esa fecha.';
        }

        return '';
    }

    private function actualizarAdvertenciaDisponibilidad(): void
    {
        $this->advertenciaDisponibilidad = '';

        if (! $this->cod_vol || ! $this->fecha_asig) {
            return;
        }

        $this->advertenciaDisponibilidad = $this->advertenciaDisponibilidad([
            'cod_vol' => (string) $this->cod_vol,
            'fecha_asig' => $this->fecha_asig,
        ]);
    }

    private function asignacionDuplicada(array $data): bool
    {
        $query = DB::table('asignacion_voluntarios')
            ->where('cod_vol', $data['cod_vol'])
            ->where('cod_am', $data['cod_am'])
            ->whereDate('fecha_asig', $data['fecha_asig'])
            ->whereNotIn('estado', ['Cancelada', 'CANCELADA', 'cancelada']);

        if ($this->isEdit && $this->asignacionId) {
            $query->where('cod_asig_vol', '<>', $this->asignacionId);
        }

        return $query->exists();
    }

    private function cambiarEstado(string $id, string $estado, string $titulo, string $texto): void
    {
        abort_unless(auth()->user()->can('asignaciones.editar'), 403);

        DB::table('asignacion_voluntarios')
            ->where('cod_asig_vol', $id)
            ->update(['estado' => $estado, 'updated_at' => now()]);

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => $titulo,
            'text' => $texto,
        ]);
    }

    private function voluntarioEstaActivo(string $codVol): bool
    {
        return DB::table('voluntarios')
            ->where('cod_vol', $codVol)
            ->where('estado', 'ACTIVO')
            ->whereNull('archivado_en')
            ->exists();
    }

    private function siguienteCodigoAsignacion(): string
    {
        $ultimo = DB::table('asignacion_voluntarios')
            ->where('cod_asig_vol', 'like', 'ASV_%')
            ->orderByDesc('cod_asig_vol')
            ->value('cod_asig_vol');

        $numero = $ultimo ? ((int) substr((string) $ultimo, 4)) + 1 : 1;

        do {
            $codigo = 'ASV_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            $numero++;
        } while (DB::table('asignacion_voluntarios')->where('cod_asig_vol', $codigo)->exists());

        return $codigo;
    }

    private function voluntariosActivos(): Collection
    {
        return DB::table('voluntarios as v')
            ->join('users as u', 'v.cod_usu', '=', 'u.cod_usu')
            ->select('v.cod_vol', 'u.nombres', 'u.ap_paterno', 'u.ap_materno', 'u.numero_documento')
            ->where('v.estado', 'ACTIVO')
            ->whereNull('v.archivado_en')
            ->orderBy('u.ap_paterno')
            ->orderBy('u.nombres')
            ->get()
            ->map(function (object $voluntario) {
                $voluntario->nombre = trim(($voluntario->nombres ?? '') . ' ' . ($voluntario->ap_paterno ?? '') . ' ' . ($voluntario->ap_materno ?? ''));

                return $voluntario;
            });
    }

    private function adultosMayores(): Collection
    {
        return DB::table('adulto_mayor')
            ->select('cod_am', 'nombres', 'ap_paterno', 'ap_materno', 'ci')
            ->whereNull('archivado_en')
            ->orderBy('ap_paterno')
            ->orderBy('nombres')
            ->limit(200)
            ->get()
            ->map(function (object $adulto) {
                $adulto->nombre = trim(($adulto->nombres ?? '') . ' ' . ($adulto->ap_paterno ?? '') . ' ' . ($adulto->ap_materno ?? ''));

                return $adulto;
            });
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->asignacionId = null;
        $this->cod_vol = $this->voluntarioFiltro;
        $this->cod_am = '';
        $this->fecha_asig = $this->fechaFiltro ?: today()->format('Y-m-d');
        $this->fecha_fin = '';
        $this->estado = 'Programada';
        $this->obser = '';
        $this->permitirSinDisponibilidad = false;
        $this->advertenciaDisponibilidad = '';
    }

    private function estadoAsistenciaAsignacion(object $item): string
    {
        if ((int) $item->asistencia_count > 0) {
            return 'Registrada';
        }

        if ($this->normalizarEstado($item->estado) === 'Cancelada') {
            return 'No aplica';
        }

        if ($item->fecha_asig && Carbon::parse($item->fecha_asig)->lte(today())) {
            return 'Pendiente';
        }

        return 'Programada';
    }

    private function normalizarEstado(?string $estado): string
    {
        return match (strtoupper(trim((string) $estado))) {
            'CONFIRMADA', 'CONFIRMADO' => 'Confirmada',
            'EN CURSO', 'EN_CURSO' => 'En curso',
            'CUMPLIDA', 'CUMPLIDO', 'FINALIZADA', 'FINALIZADO' => 'Cumplida',
            'CANCELADA', 'CANCELADO', 'ANULADA', 'ANULADO' => 'Cancelada',
            'REPROGRAMADA', 'REPROGRAMADO' => 'Reprogramada',
            default => 'Programada',
        };
    }

    private function whereEstadoActivoAsignacion(Builder $query, string $column = 'estado'): void
    {
        $query->whereNotIn($column, [
            'Cancelada',
            'CANCELADA',
            'cancelada',
            'Cumplida',
            'CUMPLIDA',
            'cumplida',
            'Finalizada',
            'FINALIZADA',
            'finalizada',
        ]);
    }

    private function whereEstadoNoCancelado(Builder $query, string $column = 'estado'): void
    {
        $query->whereNotIn($column, ['Cancelada', 'CANCELADA', 'cancelada']);
    }

    private function diaDesdeFecha(Carbon $fecha): string
    {
        return $this->diasSemana()[$fecha->dayOfWeekIso - 1] ?? 'Lunes';
    }

    private function diaSqlDesdeFecha(string $columna): string
    {
        return "case extract(isodow from {$columna})::int when 1 then 'Lunes' when 2 then 'Martes' when 3 then 'Miércoles' when 4 then 'Jueves' when 5 then 'Viernes' when 6 then 'Sábado' when 7 then 'Domingo' end";
    }

    private function variantesDia(string $dia): array
    {
        return match ($dia) {
            'Miércoles' => ['Miércoles', 'Miercoles'],
            'Sábado' => ['Sábado', 'Sabado'],
            default => [$dia],
        };
    }

    private function turnoDesdeHora(?string $hora): string
    {
        if (! $hora) {
            return 'Flexible';
        }

        $horaCorta = substr($hora, 0, 5);

        return match (true) {
            $horaCorta < '12:00' => 'Mañana',
            $horaCorta < '18:00' => 'Tarde',
            default => 'Noche',
        };
    }

    private function diasSemana(): array
    {
        return ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
    }

    private function estados(): array
    {
        return ['Programada', 'Confirmada', 'En curso', 'Cumplida', 'Cancelada', 'Reprogramada'];
    }

    private function turnos(): array
    {
        return ['Mañana', 'Tarde', 'Noche', 'Flexible'];
    }

    private function estadosAsistencia(): array
    {
        return ['Registrada', 'Pendiente', 'Programada'];
    }
}
