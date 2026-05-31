<?php

namespace App\Livewire\Admin\Voluntariado;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class AsistenciaPanel extends Component
{
    use WithPagination;

    public string $search = '';
    public string $fechaFiltro = '';
    public string $fechaDesde = '';
    public string $fechaHasta = '';
    public string $estadoFiltro = '';
    public string $turnoFiltro = '';
    public string $voluntarioFiltro = '';

    public bool $mostrarFormulario = false;
    public bool $isEdit = false;
    public ?int $asistenciaId = null;
    public string $asignacionContexto = '';
    public string $cod_vol = '';
    public string $fecha = '';
    public string $hora_entrada = '';
    public string $hora_salida = '';
    public string $estado = 'Asistió';
    public string $actividad_realizada = '';
    public string $observaciones = '';
    public string $advertenciaAsignacion = '';

    public ?int $detalleId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'fechaFiltro' => ['except' => ''],
        'fechaDesde' => ['except' => ''],
        'fechaHasta' => ['except' => ''],
        'estadoFiltro' => ['except' => ''],
        'turnoFiltro' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->voluntarioFiltro = request('voluntario') ? (string) request('voluntario') : '';
        $this->fechaFiltro = request('fecha') ? (string) request('fecha') : '';

        if (request('asignacion')) {
            $this->abrirCrearDesdeAsignacion((int) request('asignacion'));
            return;
        }

        if ($this->voluntarioFiltro || $this->fechaFiltro) {
            $this->cod_vol = $this->voluntarioFiltro;
            $this->fecha = $this->fechaFiltro ?: today()->format('Y-m-d');
        }
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

    public function abrirCrear(): void
    {
        abort_unless($this->puedeGestionarAsistencia(), 403);

        $this->resetForm();
        $this->isEdit = false;
        $this->mostrarFormulario = true;
    }

    public function abrirCrearDesdeAsignacion(int $id): void
    {
        abort_unless($this->puedeGestionarAsistencia(), 403);

        $asignacion = $this->buscarAsignacion($id);
        if (! $asignacion) {
            return;
        }

        $this->resetForm();
        $this->isEdit = false;
        $this->asignacionContexto = (string) $asignacion->cod_asig_vol;
        $this->cod_vol = (string) $asignacion->cod_vol;
        $this->fecha = Carbon::parse($asignacion->fecha_asig)->format('Y-m-d');
        $this->actividad_realizada = 'Apoyo institucional: ' . ($asignacion->adulto_nombre ?: 'adulto mayor asignado');
        $this->actualizarAdvertenciaAsignacion();
        $this->mostrarFormulario = true;
    }

    public function editar(int $id): void
    {
        abort_unless($this->puedeGestionarAsistencia(), 403);

        $asistencia = DB::table('asistencia_voluntarios')->where('cod_asis_vol', $id)->first();
        if (! $asistencia) {
            return;
        }

        $this->resetValidation();
        $this->asistenciaId = (int) $asistencia->cod_asis_vol;
        $this->cod_vol = (string) $asistencia->cod_vol;
        $this->fecha = $asistencia->fecha ? Carbon::parse($asistencia->fecha)->format('Y-m-d') : '';
        $this->hora_entrada = $asistencia->hora_entrada ? substr((string) $asistencia->hora_entrada, 0, 5) : '';
        $this->hora_salida = $asistencia->hora_salida ? substr((string) $asistencia->hora_salida, 0, 5) : '';
        $this->estado = $this->normalizarEstado($asistencia->estado);
        $this->actividad_realizada = (string) $asistencia->actividad_realizada;
        $this->observaciones = (string) $asistencia->observaciones;
        $this->asignacionContexto = (string) ($this->asignacionRelacionada((int) $asistencia->cod_vol, (string) $asistencia->fecha)?->cod_asig_vol ?? '');
        $this->actualizarAdvertenciaAsignacion();
        $this->isEdit = true;
        $this->mostrarFormulario = true;
    }

    public function cerrarFormulario(): void
    {
        $this->mostrarFormulario = false;
        $this->resetForm();
    }

    public function updatedAsignacionContexto(): void
    {
        if (! $this->asignacionContexto) {
            $this->actualizarAdvertenciaAsignacion();
            return;
        }

        $asignacion = $this->buscarAsignacion((int) $this->asignacionContexto);
        if (! $asignacion) {
            return;
        }

        $this->cod_vol = (string) $asignacion->cod_vol;
        $this->fecha = Carbon::parse($asignacion->fecha_asig)->format('Y-m-d');
        $this->actividad_realizada = $this->actividad_realizada ?: 'Apoyo institucional: ' . ($asignacion->adulto_nombre ?: 'adulto mayor asignado');
        $this->actualizarAdvertenciaAsignacion();
    }

    public function updatedCodVol(): void
    {
        $this->actualizarAdvertenciaAsignacion();
    }

    public function updatedFecha(): void
    {
        $this->actualizarAdvertenciaAsignacion();
    }

    public function guardar(): void
    {
        abort_unless($this->puedeGestionarAsistencia(), 403);

        $validated = $this->validate($this->rules(), $this->messages());

        if ($this->asistenciaDuplicada((int) $validated['cod_vol'], $validated['fecha'])) {
            $this->addError('fecha', 'Este voluntario ya tiene asistencia registrada para esa fecha.');
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Asistencia duplicada',
                'text' => 'Ya existe una asistencia para el voluntario y fecha seleccionados.',
            ]);
            return;
        }

        if ($this->asignacionContexto) {
            $asignacion = $this->buscarAsignacion((int) $this->asignacionContexto);
            if ($asignacion && $this->normalizarEstadoAsignacion($asignacion->estado) === 'Cancelada' && ! in_array($validated['estado'], ['Cancelado', 'Justificado'], true)) {
                $this->dispatch('swal', [
                    'icon' => 'warning',
                    'title' => 'Asignación cancelada',
                    'text' => 'La asignación está cancelada. Registra asistencia solo como cancelado o justificado.',
                ]);
                return;
            }
        }

        $estado = $validated['estado'];
        $payload = [
            'cod_vol' => (int) $validated['cod_vol'],
            'fecha' => $validated['fecha'],
            'hora_entrada' => in_array($estado, ['No asistió', 'Justificado', 'Cancelado', 'Reprogramado'], true) ? null : ($validated['hora_entrada'] ?: null),
            'hora_salida' => in_array($estado, ['No asistió', 'Justificado', 'Cancelado', 'Reprogramado'], true) ? null : ($validated['hora_salida'] ?: null),
            'estado' => $estado,
            'actividad_realizada' => $validated['actividad_realizada'] ?: null,
            'observaciones' => $validated['observaciones'] ?: null,
        ];

        $eraEdicion = $this->isEdit;

        if ($this->isEdit) {
            DB::table('asistencia_voluntarios')
                ->where('cod_asis_vol', $this->asistenciaId)
                ->update($payload);
        } else {
            DB::table('asistencia_voluntarios')->insert($payload);
        }

        $this->mostrarFormulario = false;
        $this->resetForm();
        $this->resetPage();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => $eraEdicion ? 'Asistencia actualizada' : 'Asistencia registrada',
            'text' => 'El control de asistencia quedó registrado correctamente.',
        ]);
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['search', 'fechaFiltro', 'fechaDesde', 'fechaHasta', 'estadoFiltro', 'turnoFiltro', 'voluntarioFiltro']);
        $this->resetPage();
    }

    public function verDetalle(int $id): void
    {
        $this->detalleId = $id;
    }

    public function cerrarDetalle(): void
    {
        $this->detalleId = null;
    }

    public function marcarAsistio(int $id): void
    {
        $this->cambiarEstado($id, 'Asistió', 'Asistencia confirmada', 'La asistencia quedó marcada como asistió.');
    }

    public function marcarNoAsistio(int $id): void
    {
        $this->cambiarEstado($id, 'No asistió', 'Ausencia registrada', 'La asistencia quedó registrada como ausencia y se conserva en historial.');
    }

    public function justificarAusencia(int $id): void
    {
        $this->cambiarEstado($id, 'Justificado', 'Ausencia justificada', 'La asistencia quedó registrada como justificada.');
    }

    public function marcarReprogramado(int $id): void
    {
        $this->cambiarEstado($id, 'Reprogramado', 'Asistencia reprogramada', 'El registro queda marcado como reprogramado.');
    }

    public function render()
    {
        $registros = $this->asistenciasQuery()
            ->paginate(8)
            ->through(fn (object $item) => $this->decorarAsistencia($item));

        return view('livewire.admin.voluntariado.asistencia-panel', [
            'metricas' => $this->metricas(),
            'estados' => $this->estados(),
            'turnos' => $this->turnos(),
            'registros' => $registros,
            'pendientes' => $this->asignacionesPendientes(),
            'detalleAsistencia' => $this->detalleAsistencia(),
            'voluntariosActivos' => $this->voluntariosActivos(),
            'asignacionesFormulario' => $this->asignacionesFormulario(),
            'programacionFormulario' => $this->programacionFormulario(),
            'linksCabecera' => [
                'resumen' => route('admin.voluntariado.index'),
                'asignaciones' => route('admin.voluntariado.asignaciones.index'),
            ],
        ]);
    }

    private function rules(): array
    {
        return [
            'cod_vol' => ['required', 'integer', Rule::exists('voluntarios', 'cod_vol')],
            'fecha' => ['required', 'date'],
            'hora_entrada' => [
                Rule::requiredIf(fn () => in_array($this->estado, ['Asistió', 'Tarde'], true)),
                'nullable',
                'date_format:H:i',
            ],
            'hora_salida' => ['nullable', 'date_format:H:i', 'after:hora_entrada'],
            'estado' => ['required', Rule::in($this->estados())],
            'actividad_realizada' => ['nullable', 'string', 'max:700'],
            'observaciones' => [
                Rule::requiredIf(fn () => in_array($this->estado, ['No asistió', 'Justificado'], true)),
                'nullable',
                'string',
                'max:700',
            ],
        ];
    }

    private function messages(): array
    {
        return [
            'cod_vol.required' => 'Selecciona un voluntario.',
            'cod_vol.exists' => 'El voluntario seleccionado no existe.',
            'fecha.required' => 'Registra la fecha de asistencia.',
            'fecha.date' => 'Registra una fecha válida.',
            'hora_entrada.required' => 'Registra la hora de llegada para este estado.',
            'hora_entrada.date_format' => 'Usa un formato de hora válido.',
            'hora_salida.date_format' => 'Usa un formato de hora válido.',
            'hora_salida.after' => 'La hora de salida debe ser mayor que la hora de llegada.',
            'estado.required' => 'Selecciona el estado de asistencia.',
            'estado.in' => 'Selecciona un estado válido.',
            'actividad_realizada.max' => 'La actividad no debe superar 700 caracteres.',
            'observaciones.required' => 'Registra el motivo u observación para este estado.',
            'observaciones.max' => 'La observación no debe superar 700 caracteres.',
        ];
    }

    private function asistenciasQuery(bool $ordenar = true): Builder
    {
        $query = DB::table('asistencia_voluntarios as asi')
            ->join('voluntarios as v', 'asi.cod_vol', '=', 'v.cod_vol')
            ->join('users as u', 'v.cod_usu', '=', 'u.cod_usu')
            ->select([
                'asi.cod_asis_vol',
                'asi.fecha',
                'asi.hora_entrada',
                'asi.hora_salida',
                'asi.estado',
                'asi.actividad_realizada',
                'asi.observaciones',
                'asi.cod_vol',
                'v.area_apoyo',
                'u.nombres',
                'u.ap_paterno',
                'u.ap_materno',
                'u.numero_documento',
                DB::raw("(select av.cod_asig_vol from asignacion_voluntarios av where av.cod_vol = asi.cod_vol and av.fecha_asig = asi.fecha order by av.cod_asig_vol limit 1) as cod_asig_vol"),
                DB::raw("(select av.estado from asignacion_voluntarios av where av.cod_vol = asi.cod_vol and av.fecha_asig = asi.fecha order by av.cod_asig_vol limit 1) as asignacion_estado"),
                DB::raw("(select concat_ws(' ', am.nombres, am.ap_paterno, am.ap_materno) from asignacion_voluntarios av left join adulto_mayor am on am.cod_am = av.cod_am where av.cod_vol = asi.cod_vol and av.fecha_asig = asi.fecha order by av.cod_asig_vol limit 1) as adulto_nombre"),
            ]);

        if ($this->voluntarioFiltro !== '') {
            $query->where('asi.cod_vol', (int) $this->voluntarioFiltro);
        }

        if ($this->search !== '') {
            $term = '%' . trim($this->search) . '%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('u.nombres', 'ilike', $term)
                    ->orWhere('u.ap_paterno', 'ilike', $term)
                    ->orWhere('u.ap_materno', 'ilike', $term)
                    ->orWhere('u.numero_documento', 'ilike', $term)
                    ->orWhere('asi.actividad_realizada', 'ilike', $term)
                    ->orWhereExists(function (Builder $subquery) use ($term) {
                        $subquery->select('av.cod_asig_vol')
                            ->from('asignacion_voluntarios as av')
                            ->leftJoin('adulto_mayor as am', 'am.cod_am', '=', 'av.cod_am')
                            ->whereColumn('av.cod_vol', 'asi.cod_vol')
                            ->whereColumn('av.fecha_asig', 'asi.fecha')
                            ->where(function (Builder $adulto) use ($term) {
                                $adulto->where('am.nombres', 'ilike', $term)
                                    ->orWhere('am.ap_paterno', 'ilike', $term)
                                    ->orWhere('am.ap_materno', 'ilike', $term)
                                    ->orWhere('am.ci', 'ilike', $term);
                            });
                    });
            });
        }

        if ($this->fechaFiltro !== '') {
            $query->whereDate('asi.fecha', $this->fechaFiltro);
        }

        if ($this->fechaDesde !== '') {
            $query->whereDate('asi.fecha', '>=', $this->fechaDesde);
        }

        if ($this->fechaHasta !== '') {
            $query->whereDate('asi.fecha', '<=', $this->fechaHasta);
        }

        if ($this->estadoFiltro !== '') {
            $query->where('asi.estado', $this->estadoFiltro);
        }

        if ($this->turnoFiltro !== '') {
            $this->aplicarFiltroTurno($query, $this->turnoFiltro);
        }

        if ($ordenar) {
            $query->orderByDesc('asi.fecha')
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
                ->whereColumn('d.cod_vol', 'asi.cod_vol')
                ->whereRaw('d.dia_semana = ' . $this->diaSqlDesdeFecha('asi.fecha'));

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

    private function metricas(): array
    {
        $hoy = today();
        $inicioMes = now()->startOfMonth()->toDateString();
        $finMes = now()->endOfMonth()->toDateString();

        $asistenciasHoy = DB::table('asistencia_voluntarios')->whereDate('fecha', $hoy)->count();
        $pendientes = $this->asignacionesPendientesQuery()->count();
        $voluntariosAsistieron = DB::table('asistencia_voluntarios')
            ->whereDate('fecha', $hoy)
            ->whereIn('estado', ['Asistió', 'ASISTIÓ', 'ASISTIO', 'Tarde', 'TARDE'])
            ->distinct()
            ->count('cod_vol');
        $ausencias = DB::table('asistencia_voluntarios')->whereIn('estado', ['No asistió', 'NO ASISTIÓ', 'NO ASISTIO'])->count();
        $tardanzas = DB::table('asistencia_voluntarios')->whereIn('estado', ['Tarde', 'TARDE'])->count();
        $justificadas = DB::table('asistencia_voluntarios')->whereIn('estado', ['Justificado', 'JUSTIFICADO', 'Justificada', 'JUSTIFICADA'])->count();
        $asistenciasMes = DB::table('asistencia_voluntarios')->whereBetween('fecha', [$inicioMes, $finMes])->count();

        return [
            ['label' => 'Asistencias de hoy', 'valor' => $asistenciasHoy, 'subtitulo' => now()->format('d/m/Y'), 'icono' => 'ph-calendar-check', 'tono' => 'verde'],
            ['label' => 'Pendientes', 'valor' => $pendientes, 'subtitulo' => 'Por registrar', 'icono' => 'ph-warning-circle', 'tono' => 'dorado'],
            ['label' => 'Asistieron', 'valor' => $voluntariosAsistieron, 'subtitulo' => 'Voluntarios hoy', 'icono' => 'ph-users-three', 'tono' => 'verde'],
            ['label' => 'Ausencias', 'valor' => $ausencias, 'subtitulo' => 'Historial total', 'icono' => 'ph-user-minus', 'tono' => 'terracota'],
            ['label' => 'Tardanzas', 'valor' => $tardanzas, 'subtitulo' => 'Llegadas tarde', 'icono' => 'ph-clock-countdown', 'tono' => 'dorado'],
            ['label' => 'Justificadas', 'valor' => $justificadas, 'subtitulo' => 'Con motivo', 'icono' => 'ph-note-pencil', 'tono' => 'azul'],
            ['label' => 'Horas colaboradas', 'valor' => $this->horasColaboradasTexto($inicioMes, $finMes), 'subtitulo' => 'Este mes', 'icono' => 'ph-hourglass-medium', 'tono' => 'azul'],
            ['label' => 'Asistencias del mes', 'valor' => $asistenciasMes, 'subtitulo' => now()->translatedFormat('F'), 'icono' => 'ph-chart-bar', 'tono' => 'verde'],
        ];
    }

    private function asignacionesPendientes(): Collection
    {
        return $this->asignacionesPendientesQuery()
            ->orderBy('a.fecha_asig')
            ->limit(6)
            ->get()
            ->map(fn (object $item) => $this->decorarAsignacionPendiente($item));
    }

    private function asignacionesPendientesQuery(): Builder
    {
        return DB::table('asignacion_voluntarios as a')
            ->join('voluntarios as v', 'a.cod_vol', '=', 'v.cod_vol')
            ->join('users as u', 'v.cod_usu', '=', 'u.cod_usu')
            ->leftJoin('adulto_mayor as am', 'a.cod_am', '=', 'am.cod_am')
            ->select([
                'a.cod_asig_vol',
                'a.fecha_asig',
                'a.fecha_fin',
                'a.estado',
                'a.obser',
                'a.cod_vol',
                'a.cod_am',
                'u.nombres',
                'u.ap_paterno',
                'u.ap_materno',
                'u.numero_documento',
                'am.nombres as adulto_nombres',
                'am.ap_paterno as adulto_ap_paterno',
                'am.ap_materno as adulto_ap_materno',
            ])
            ->whereDate('a.fecha_asig', '<=', today())
            ->whereNotIn('a.estado', ['Cancelada', 'CANCELADA', 'cancelada', 'Reprogramada', 'REPROGRAMADA', 'reprogramada'])
            ->whereNotExists(function (Builder $query) {
                $query->select('asi.cod_asis_vol')
                    ->from('asistencia_voluntarios as asi')
                    ->whereColumn('asi.cod_vol', 'a.cod_vol')
                    ->whereColumn('asi.fecha', 'a.fecha_asig');
            });
    }

    private function asignacionesFormulario(): Collection
    {
        return DB::table('asignacion_voluntarios as a')
            ->join('voluntarios as v', 'a.cod_vol', '=', 'v.cod_vol')
            ->join('users as u', 'v.cod_usu', '=', 'u.cod_usu')
            ->leftJoin('adulto_mayor as am', 'a.cod_am', '=', 'am.cod_am')
            ->select([
                'a.cod_asig_vol',
                'a.fecha_asig',
                'a.estado',
                'a.cod_vol',
                'u.nombres',
                'u.ap_paterno',
                'u.ap_materno',
                'am.nombres as adulto_nombres',
                'am.ap_paterno as adulto_ap_paterno',
                'am.ap_materno as adulto_ap_materno',
            ])
            ->whereDate('a.fecha_asig', '<=', today()->addMonth())
            ->whereNotIn('a.estado', ['Cancelada', 'CANCELADA', 'cancelada'])
            ->orderByDesc('a.fecha_asig')
            ->limit(120)
            ->get()
            ->map(fn (object $item) => $this->decorarAsignacionPendiente($item));
    }

    private function detalleAsistencia(): ?object
    {
        if (! $this->detalleId) {
            return null;
        }

        $item = $this->asistenciasQuery(false)
            ->where('asi.cod_asis_vol', $this->detalleId)
            ->first();

        return $item ? $this->decorarAsistencia($item) : null;
    }

    private function decorarAsistencia(object $item): object
    {
        $item->voluntario_nombre = trim(($item->nombres ?? '') . ' ' . ($item->ap_paterno ?? '') . ' ' . ($item->ap_materno ?? ''));
        $item->estado_normalizado = $this->normalizarEstado($item->estado);
        $item->fecha_texto = $item->fecha ? Carbon::parse($item->fecha)->format('d/m/Y') : 'Sin fecha';
        $item->dia_semana = $item->fecha ? $this->diaDesdeFecha(Carbon::parse($item->fecha)) : 'Sin día';
        $item->programacion = $this->programacionPorVoluntarioFecha((int) $item->cod_vol, (string) $item->fecha);
        $item->turno = $item->programacion['turno'];
        $item->horario_programado = $item->programacion['horario'];
        $item->tiempo_colaborado = $this->tiempoColaboradoTexto($item->hora_entrada, $item->hora_salida);
        $item->registrado_por = 'Equipo institucional';
        $item->adulto_nombre = trim((string) $item->adulto_nombre);

        return $item;
    }

    private function decorarAsignacionPendiente(object $item): object
    {
        $item->voluntario_nombre = trim(($item->nombres ?? '') . ' ' . ($item->ap_paterno ?? '') . ' ' . ($item->ap_materno ?? ''));
        $item->adulto_nombre = trim(($item->adulto_nombres ?? '') . ' ' . ($item->adulto_ap_paterno ?? '') . ' ' . ($item->adulto_ap_materno ?? ''));
        $item->fecha_texto = $item->fecha_asig ? Carbon::parse($item->fecha_asig)->format('d/m/Y') : 'Sin fecha';
        $item->estado_normalizado = $this->normalizarEstadoAsignacion($item->estado);
        $programacion = $this->programacionPorVoluntarioFecha((int) $item->cod_vol, (string) $item->fecha_asig);
        $item->horario_programado = $programacion['horario'];
        $item->turno = $programacion['turno'];

        return $item;
    }

    private function programacionFormulario(): array
    {
        if (! $this->cod_vol || ! $this->fecha) {
            return ['horario' => 'Seleccione voluntario y fecha', 'turno' => 'Sin dato', 'asignacion' => 'Sin asignación seleccionada'];
        }

        $programacion = $this->programacionPorVoluntarioFecha((int) $this->cod_vol, $this->fecha);
        $asignacion = $this->asignacionRelacionada((int) $this->cod_vol, $this->fecha);
        $programacion['asignacion'] = $asignacion ? ('Asignación #' . $asignacion->cod_asig_vol) : 'Sin asignación relacionada';

        return $programacion;
    }

    private function programacionPorVoluntarioFecha(int $codVol, string $fecha): array
    {
        if (! $fecha) {
            return ['horario' => 'Sin fecha', 'turno' => 'Sin dato'];
        }

        $dia = $this->diaDesdeFecha(Carbon::parse($fecha));
        $items = DB::table('disponibilidad_voluntarios')
            ->where('cod_vol', $codVol)
            ->whereIn('dia_semana', $this->variantesDia($dia))
            ->whereRaw("upper(coalesce(observaciones, '')) not like '%[NO DISPONIBLE]%'")
            ->orderBy('hora_inicio')
            ->get();

        if ($items->isEmpty()) {
            return ['horario' => 'Sin horario programado', 'turno' => 'Sin turno'];
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
        ];
    }

    private function buscarAsignacion(int $id): ?object
    {
        $asignacion = DB::table('asignacion_voluntarios as a')
            ->join('voluntarios as v', 'a.cod_vol', '=', 'v.cod_vol')
            ->join('users as u', 'v.cod_usu', '=', 'u.cod_usu')
            ->leftJoin('adulto_mayor as am', 'a.cod_am', '=', 'am.cod_am')
            ->select([
                'a.cod_asig_vol',
                'a.fecha_asig',
                'a.estado',
                'a.cod_vol',
                DB::raw("concat_ws(' ', am.nombres, am.ap_paterno, am.ap_materno) as adulto_nombre"),
            ])
            ->where('a.cod_asig_vol', $id)
            ->first();

        return $asignacion ?: null;
    }

    private function asignacionRelacionada(int $codVol, string $fecha): ?object
    {
        $asignacion = DB::table('asignacion_voluntarios')
            ->where('cod_vol', $codVol)
            ->whereDate('fecha_asig', $fecha)
            ->orderBy('cod_asig_vol')
            ->first();

        return $asignacion ?: null;
    }

    private function actualizarAdvertenciaAsignacion(): void
    {
        $this->advertenciaAsignacion = '';

        if (! $this->cod_vol || ! $this->fecha) {
            return;
        }

        $asignacion = $this->asignacionRelacionada((int) $this->cod_vol, $this->fecha);
        if (! $asignacion) {
            $this->advertenciaAsignacion = 'No existe una asignación registrada para este voluntario en la fecha seleccionada.';
            return;
        }

        if ($this->normalizarEstadoAsignacion($asignacion->estado) === 'Cancelada') {
            $this->advertenciaAsignacion = 'La asignación relacionada está cancelada. Registra solo cancelación o justificación si corresponde.';
        }
    }

    private function asistenciaDuplicada(int $codVol, string $fecha): bool
    {
        $query = DB::table('asistencia_voluntarios')
            ->where('cod_vol', $codVol)
            ->whereDate('fecha', $fecha);

        if ($this->isEdit && $this->asistenciaId) {
            $query->where('cod_asis_vol', '<>', $this->asistenciaId);
        }

        return $query->exists();
    }

    private function cambiarEstado(int $id, string $estado, string $titulo, string $texto): void
    {
        abort_unless($this->puedeGestionarAsistencia(), 403);

        $payload = ['estado' => $estado];
        if (in_array($estado, ['No asistió', 'Justificado', 'Cancelado', 'Reprogramado'], true)) {
            $payload['hora_entrada'] = null;
            $payload['hora_salida'] = null;
        }

        DB::table('asistencia_voluntarios')
            ->where('cod_asis_vol', $id)
            ->update($payload);

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => $titulo,
            'text' => $texto,
        ]);
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

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->asistenciaId = null;
        $this->asignacionContexto = '';
        $this->cod_vol = $this->voluntarioFiltro;
        $this->fecha = $this->fechaFiltro ?: today()->format('Y-m-d');
        $this->hora_entrada = '';
        $this->hora_salida = '';
        $this->estado = 'Asistió';
        $this->actividad_realizada = '';
        $this->observaciones = '';
        $this->advertenciaAsignacion = '';
    }

    private function tiempoColaboradoTexto(?string $entrada, ?string $salida): string
    {
        if (! $entrada || ! $salida) {
            return 'Sin cierre';
        }

        $inicio = Carbon::createFromFormat('H:i:s', strlen($entrada) > 5 ? $entrada : $entrada . ':00');
        $fin = Carbon::createFromFormat('H:i:s', strlen($salida) > 5 ? $salida : $salida . ':00');

        if ($fin->lessThanOrEqualTo($inicio)) {
            return 'Horario inválido';
        }

        $minutos = $inicio->diffInMinutes($fin);
        $horas = intdiv($minutos, 60);
        $resto = $minutos % 60;

        if ($horas === 0) {
            return $resto . ' min';
        }

        return $horas . ' h' . ($resto > 0 ? ' ' . $resto . ' min' : '');
    }

    private function horasColaboradasTexto(string $inicio, string $fin): string
    {
        $minutos = DB::table('asistencia_voluntarios')
            ->whereBetween('fecha', [$inicio, $fin])
            ->whereNotNull('hora_entrada')
            ->whereNotNull('hora_salida')
            ->get(['hora_entrada', 'hora_salida'])
            ->sum(function (object $item) {
                $entrada = Carbon::createFromFormat('H:i:s', strlen($item->hora_entrada) > 5 ? $item->hora_entrada : $item->hora_entrada . ':00');
                $salida = Carbon::createFromFormat('H:i:s', strlen($item->hora_salida) > 5 ? $item->hora_salida : $item->hora_salida . ':00');

                return $salida->greaterThan($entrada) ? $entrada->diffInMinutes($salida) : 0;
            });

        $horas = round($minutos / 60, 1);

        return rtrim(rtrim((string) $horas, '0'), '.') . ' h';
    }

    private function normalizarEstado(?string $estado): string
    {
        return match (strtoupper(trim((string) $estado))) {
            'ASISTIÓ', 'ASISTIO', 'ASISTIO ', 'PRESENTE' => 'Asistió',
            'NO ASISTIÓ', 'NO ASISTIO', 'AUSENTE', 'AUSENCIA' => 'No asistió',
            'TARDE', 'TARDANZA' => 'Tarde',
            'JUSTIFICADO', 'JUSTIFICADA' => 'Justificado',
            'CANCELADO', 'CANCELADA' => 'Cancelado',
            'REPROGRAMADO', 'REPROGRAMADA' => 'Reprogramado',
            'PENDIENTE' => 'Pendiente',
            default => 'Asistió',
        };
    }

    private function normalizarEstadoAsignacion(?string $estado): string
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

    private function puedeGestionarAsistencia(): bool
    {
        $user = auth()->user();

        return $user && (
            $user->can('asistencia.crear')
            || $user->can('asistencia.editar')
            || $user->can('asistencia.ver')
        );
    }

    private function diasSemana(): array
    {
        return ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
    }

    private function estados(): array
    {
        return ['Pendiente', 'Asistió', 'No asistió', 'Tarde', 'Justificado', 'Cancelado', 'Reprogramado'];
    }

    private function turnos(): array
    {
        return ['Mañana', 'Tarde', 'Noche', 'Flexible'];
    }
}
