<?php

namespace App\Livewire\Admin\Voluntariado;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class DisponibilidadPanel extends Component
{
    use WithPagination;

    public string $search = '';
    public string $fechaFiltro = '';
    public string $diaFiltro = '';
    public string $turnoFiltro = '';
    public string $estadoFiltro = '';
    public string $semanaInicio = '';

    public bool $mostrarFormulario = false;
    public bool $isEdit = false;
    public ?int $disponibilidadId = null;
    public string $cod_vol = '';
    public string $dia_semana = '';
    public string $hora_inicio = '';
    public string $hora_fin = '';
    public string $observaciones = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'fechaFiltro' => ['except' => ''],
        'diaFiltro' => ['except' => ''],
        'turnoFiltro' => ['except' => ''],
        'estadoFiltro' => ['except' => ''],
        'semanaInicio' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->semanaInicio = $this->semanaInicio ?: now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingDiaFiltro(): void
    {
        $this->resetPage();
    }

    public function updatingTurnoFiltro(): void
    {
        $this->resetPage();
    }

    public function updatingEstadoFiltro(): void
    {
        $this->resetPage();
    }

    public function updatedFechaFiltro(): void
    {
        if ($this->fechaFiltro) {
            $fecha = Carbon::parse($this->fechaFiltro);
            $this->semanaInicio = $fecha->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
            $this->diaFiltro = $this->diaDesdeFecha($fecha);
        }

        $this->resetPage();
    }

    public function semanaAnterior(): void
    {
        $this->semanaInicio = Carbon::parse($this->semanaInicio)->subWeek()->format('Y-m-d');
        $this->fechaFiltro = '';
        $this->resetPage();
    }

    public function semanaSiguiente(): void
    {
        $this->semanaInicio = Carbon::parse($this->semanaInicio)->addWeek()->format('Y-m-d');
        $this->fechaFiltro = '';
        $this->resetPage();
    }

    public function irHoy(): void
    {
        $this->semanaInicio = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $this->fechaFiltro = '';
        $this->diaFiltro = '';
        $this->resetPage();
    }

    public function abrirCrear(): void
    {
        abort_unless(auth()->user()->can('voluntarios.crear') || auth()->user()->can('voluntarios.editar'), 403);

        $this->resetForm();
        $this->isEdit = false;
        $this->mostrarFormulario = true;
    }

    public function editar(int $id): void
    {
        abort_unless(auth()->user()->can('voluntarios.editar'), 403);

        $disponibilidad = DB::table('disponibilidad_voluntarios')->where('cod_hor_vol', $id)->first();
        if (! $disponibilidad) {
            return;
        }

        $this->resetValidation();
        $this->disponibilidadId = (int) $disponibilidad->cod_hor_vol;
        $this->cod_vol = (string) $disponibilidad->cod_vol;
        $this->dia_semana = (string) $disponibilidad->dia_semana;
        $this->hora_inicio = $disponibilidad->hora_inicio ? substr((string) $disponibilidad->hora_inicio, 0, 5) : '';
        $this->hora_fin = $disponibilidad->hora_fin ? substr((string) $disponibilidad->hora_fin, 0, 5) : '';
        $this->observaciones = (string) $disponibilidad->observaciones;
        $this->isEdit = true;
        $this->mostrarFormulario = true;
    }

    public function cerrarFormulario(): void
    {
        $this->mostrarFormulario = false;
        $this->resetForm();
    }

    public function guardar(): void
    {
        $permiso = $this->isEdit ? 'voluntarios.editar' : 'voluntarios.crear';
        abort_unless(auth()->user()->can($permiso), 403);

        $validated = $this->validate($this->rules(), $this->messages());

        if (! $this->voluntarioEstaActivo((int) $validated['cod_vol'])) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Voluntario no activo',
                'text' => 'Solo se puede registrar disponibilidad para voluntarios activos.',
            ]);
            return;
        }

        if ($this->horarioDuplicado($validated)) {
            $this->addError('hora_inicio', 'Este voluntario ya tiene registrada esa misma disponibilidad.');
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Horario duplicado',
                'text' => 'Ya existe una disponibilidad igual para el voluntario seleccionado.',
            ]);
            return;
        }

        $payload = [
            'cod_vol' => (int) $validated['cod_vol'],
            'dia_semana' => $validated['dia_semana'],
            'hora_inicio' => $validated['hora_inicio'],
            'hora_fin' => $validated['hora_fin'],
            'observaciones' => $validated['observaciones'] ?: null,
        ];

        if ($this->isEdit) {
            DB::table('disponibilidad_voluntarios')
                ->where('cod_hor_vol', $this->disponibilidadId)
                ->update($payload);
        } else {
            DB::table('disponibilidad_voluntarios')->insert($payload);
        }

        $this->mostrarFormulario = false;
        $this->resetForm();
        $this->resetPage();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => $this->isEdit ? 'Disponibilidad actualizada' : 'Disponibilidad registrada',
            'text' => 'El horario fue asociado al voluntario seleccionado.',
        ]);
    }

    public function marcarNoDisponible(int $id): void
    {
        abort_unless(auth()->user()->can('voluntarios.editar'), 403);

        $registro = DB::table('disponibilidad_voluntarios')->where('cod_hor_vol', $id)->first();
        if (! $registro) {
            return;
        }

        $observaciones = trim((string) $registro->observaciones);
        if (! str_contains(strtoupper($observaciones), '[NO DISPONIBLE]')) {
            $observaciones = trim('[NO DISPONIBLE] ' . $observaciones);
        }

        DB::table('disponibilidad_voluntarios')
            ->where('cod_hor_vol', $id)
            ->update(['observaciones' => $observaciones]);

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Disponibilidad desactivada',
            'text' => 'El registro se conserva como historial y queda marcado como no disponible.',
        ]);
    }

    public function reactivarDisponibilidad(int $id): void
    {
        abort_unless(auth()->user()->can('voluntarios.editar'), 403);

        $registro = DB::table('disponibilidad_voluntarios')->where('cod_hor_vol', $id)->first();
        if (! $registro) {
            return;
        }

        $observaciones = trim(str_replace('[NO DISPONIBLE]', '', (string) $registro->observaciones));

        DB::table('disponibilidad_voluntarios')
            ->where('cod_hor_vol', $id)
            ->update(['observaciones' => $observaciones ?: null]);

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Disponibilidad reactivada',
            'text' => 'El horario vuelve a figurar como disponible.',
        ]);
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['search', 'fechaFiltro', 'diaFiltro', 'turnoFiltro', 'estadoFiltro']);
        $this->resetPage();
    }

    public function render()
    {
        $registrosCalendario = $this->disponibilidadQuery(false)->get()->map(fn (object $item) => $this->decorarDisponibilidad($item));

        return view('livewire.admin.voluntariado.disponibilidad-panel', [
            'metricas' => $this->metricas(),
            'diasSemana' => $this->diasSemana(),
            'turnos' => $this->turnos(),
            'estados' => $this->estados(),
            'calendario' => $this->calendario($registrosCalendario),
            'registros' => $this->disponibilidadQuery()->paginate(8)->through(fn (object $item) => $this->decorarDisponibilidad($item)),
            'voluntariosActivos' => $this->voluntariosActivos(),
            'linksCabecera' => [
                'resumen' => route('admin.voluntariado.index'),
                'voluntarios' => route('admin.voluntariado.voluntarios.index'),
            ],
            'rangoSemana' => $this->rangoSemanaTexto(),
        ]);
    }

    private function rules(): array
    {
        return [
            'cod_vol' => ['required', 'integer', Rule::exists('voluntarios', 'cod_vol')],
            'dia_semana' => ['required', Rule::in($this->diasSemana())],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ];
    }

    private function messages(): array
    {
        return [
            'cod_vol.required' => 'Selecciona un voluntario.',
            'cod_vol.exists' => 'El voluntario seleccionado no existe.',
            'dia_semana.required' => 'Selecciona el día de la semana.',
            'dia_semana.in' => 'Selecciona un día válido.',
            'hora_inicio.required' => 'Registra la hora de inicio.',
            'hora_inicio.date_format' => 'Usa el formato de hora correcto.',
            'hora_fin.required' => 'Registra la hora de fin.',
            'hora_fin.date_format' => 'Usa el formato de hora correcto.',
            'hora_fin.after' => 'La hora de fin debe ser mayor que la hora de inicio.',
            'observaciones.max' => 'La observación no debe superar 500 caracteres.',
        ];
    }

    private function disponibilidadQuery(bool $ordenar = true): Builder
    {
        $query = DB::table('disponibilidad_voluntarios as d')
            ->join('voluntarios as v', 'd.cod_vol', '=', 'v.cod_vol')
            ->join('users as u', 'v.cod_usu', '=', 'u.cod_usu')
            ->select([
                'd.cod_hor_vol',
                'd.dia_semana',
                'd.hora_inicio',
                'd.hora_fin',
                'd.observaciones',
                'd.cod_vol',
                'v.estado as voluntario_estado',
                'v.archivado_en',
                'v.area_apoyo',
                'u.nombres',
                'u.ap_paterno',
                'u.ap_materno',
                'u.numero_documento',
                DB::raw("(select count(*) from asignacion_voluntarios av where av.cod_vol = d.cod_vol and av.fecha_asig <= CURRENT_DATE and (av.fecha_fin is null or av.fecha_fin >= CURRENT_DATE) and upper(coalesce(av.estado, '')) not in ('INACTIVO','INACTIVA','FINALIZADO','FINALIZADA','CANCELADO','CANCELADA','ANULADO','ANULADA')) as asignaciones_activas_count"),
            ]);

        if ($this->search !== '') {
            $term = '%' . trim($this->search) . '%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('u.nombres', 'ilike', $term)
                    ->orWhere('u.ap_paterno', 'ilike', $term)
                    ->orWhere('u.ap_materno', 'ilike', $term)
                    ->orWhere('u.numero_documento', 'ilike', $term);
            });
        }

        if ($this->diaFiltro !== '') {
            $query->where('d.dia_semana', $this->diaFiltro);
        }

        if ($this->turnoFiltro !== '') {
            $query = $this->aplicarFiltroTurno($query, $this->turnoFiltro);
        }

        if ($this->estadoFiltro !== '') {
            $query = $this->aplicarFiltroEstado($query, $this->estadoFiltro);
        }

        if ($ordenar) {
            $query
                ->orderByRaw($this->ordenDiaSql('d.dia_semana'))
                ->orderBy('d.hora_inicio')
                ->orderBy('u.ap_paterno')
                ->orderBy('u.nombres');
        }

        return $query;
    }

    private function aplicarFiltroTurno(Builder $query, string $turno): Builder
    {
        return match ($turno) {
            'Mañana' => $query->where('d.hora_inicio', '<', '12:00'),
            'Tarde' => $query->where('d.hora_inicio', '>=', '12:00')->where('d.hora_inicio', '<', '18:00'),
            'Noche' => $query->where('d.hora_inicio', '>=', '18:00'),
            'Flexible' => $query->where(function (Builder $q) {
                $q->whereNull('d.hora_inicio')->orWhereNull('d.hora_fin');
            }),
            default => $query,
        };
    }

    private function aplicarFiltroEstado(Builder $query, string $estado): Builder
    {
        return match ($estado) {
            'Disponible' => $query
                ->where('v.estado', 'ACTIVO')
                ->whereNull('v.archivado_en')
                ->whereRaw("upper(coalesce(d.observaciones, '')) not like '%[NO DISPONIBLE]%'")
                ->whereNotNull('d.hora_inicio')
                ->whereNotNull('d.hora_fin'),
            'No disponible' => $query->whereRaw("upper(coalesce(d.observaciones, '')) like '%[NO DISPONIBLE]%'"),
            'Pendiente' => $query->where(function (Builder $q) {
                $q->whereNull('d.hora_inicio')->orWhereNull('d.hora_fin');
            }),
            'Suspendido' => $query->where(function (Builder $q) {
                $q->where('v.estado', '<>', 'ACTIVO')->orWhereNotNull('v.archivado_en');
            }),
            default => $query,
        };
    }

    private function decorarDisponibilidad(object $item): object
    {
        $item->nombre_voluntario = trim(($item->nombres ?? '') . ' ' . ($item->ap_paterno ?? '') . ' ' . ($item->ap_materno ?? ''));
        $item->turno = $this->turnoDesdeHora($item->hora_inicio);
        $item->estado_operativo = $this->estadoOperativo($item);
        $item->fecha_referencia = $this->fechaParaDia($item->dia_semana);

        return $item;
    }

    private function calendario(Collection $registros): array
    {
        $inicio = Carbon::parse($this->semanaInicio)->startOfWeek(Carbon::MONDAY);

        return collect($this->diasSemana())
            ->map(function (string $dia, int $index) use ($inicio, $registros) {
                $items = $registros
                    ->where('dia_semana', $dia)
                    ->sortBy('hora_inicio')
                    ->values();

                return [
                    'dia' => $dia,
                    'fecha' => $inicio->copy()->addDays($index),
                    'items' => $items,
                ];
            })
            ->all();
    }

    private function metricas(): array
    {
        $hoy = $this->diaDesdeFecha(today());
        $activos = DB::table('voluntarios')->where('estado', 'ACTIVO')->whereNull('archivado_en')->count();
        $disponiblesHoy = DB::table('disponibilidad_voluntarios as d')
            ->join('voluntarios as v', 'd.cod_vol', '=', 'v.cod_vol')
            ->where('d.dia_semana', $hoy)
            ->where('v.estado', 'ACTIVO')
            ->whereNull('v.archivado_en')
            ->whereRaw("upper(coalesce(d.observaciones, '')) not like '%[NO DISPONIBLE]%'")
            ->distinct()
            ->count('d.cod_vol');
        $disponiblesSemana = DB::table('disponibilidad_voluntarios as d')
            ->join('voluntarios as v', 'd.cod_vol', '=', 'v.cod_vol')
            ->where('v.estado', 'ACTIVO')
            ->whereNull('v.archivado_en')
            ->whereRaw("upper(coalesce(d.observaciones, '')) not like '%[NO DISPONIBLE]%'")
            ->distinct()
            ->count('d.cod_vol');
        $sinDisponibilidad = DB::table('voluntarios as v')
            ->where('v.estado', 'ACTIVO')
            ->whereNull('v.archivado_en')
            ->whereNotExists(function (Builder $q) {
                $q->select('d.cod_vol')
                    ->from('disponibilidad_voluntarios as d')
                    ->whereColumn('d.cod_vol', 'v.cod_vol');
            })
            ->count();
        $turnosCubiertos = DB::table('disponibilidad_voluntarios as d')
            ->join('voluntarios as v', 'd.cod_vol', '=', 'v.cod_vol')
            ->where('v.estado', 'ACTIVO')
            ->whereNull('v.archivado_en')
            ->whereNotNull('d.hora_inicio')
            ->whereNotNull('d.hora_fin')
            ->whereRaw("upper(coalesce(d.observaciones, '')) not like '%[NO DISPONIBLE]%'")
            ->count();
        $pendientes = DB::table('disponibilidad_voluntarios as d')
            ->join('voluntarios as v', 'd.cod_vol', '=', 'v.cod_vol')
            ->where(function (Builder $q) {
                $q->whereNull('d.hora_inicio')
                    ->orWhereNull('d.hora_fin')
                    ->orWhere('v.estado', '<>', 'ACTIVO')
                    ->orWhereNotNull('v.archivado_en');
            })
            ->count();

        return [
            ['label' => 'Disponibles hoy', 'valor' => $disponiblesHoy, 'subtitulo' => $hoy, 'icono' => 'ph-calendar-check', 'tono' => 'verde'],
            ['label' => 'Disponibles esta semana', 'valor' => $disponiblesSemana, 'subtitulo' => 'Voluntarios con horario', 'icono' => 'ph-users-three', 'tono' => 'azul'],
            ['label' => 'Sin disponibilidad', 'valor' => $sinDisponibilidad, 'subtitulo' => 'Activos pendientes', 'icono' => 'ph-calendar-x', 'tono' => 'terracota'],
            ['label' => 'Turnos cubiertos', 'valor' => $turnosCubiertos, 'subtitulo' => 'Bloques activos', 'icono' => 'ph-clock-countdown', 'tono' => 'dorado'],
            ['label' => 'Disponibilidad pendiente', 'valor' => $pendientes, 'subtitulo' => 'Requiere revisión', 'icono' => 'ph-warning-circle', 'tono' => 'terracota'],
            ['label' => 'Voluntarios activos', 'valor' => $activos, 'subtitulo' => 'Base operativa', 'icono' => 'ph-hand-heart', 'tono' => 'verde'],
        ];
    }

    private function voluntariosActivos()
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

    private function horarioDuplicado(array $data): bool
    {
        $query = DB::table('disponibilidad_voluntarios')
            ->where('cod_vol', $data['cod_vol'])
            ->where('dia_semana', $data['dia_semana'])
            ->where('hora_inicio', $data['hora_inicio'])
            ->where('hora_fin', $data['hora_fin']);

        if ($this->isEdit && $this->disponibilidadId) {
            $query->where('cod_hor_vol', '<>', $this->disponibilidadId);
        }

        return $query->exists();
    }

    private function voluntarioEstaActivo(int $codVol): bool
    {
        return DB::table('voluntarios')
            ->where('cod_vol', $codVol)
            ->where('estado', 'ACTIVO')
            ->whereNull('archivado_en')
            ->exists();
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->disponibilidadId = null;
        $this->cod_vol = '';
        $this->dia_semana = $this->diaFiltro ?: $this->diaDesdeFecha(today());
        $this->hora_inicio = '';
        $this->hora_fin = '';
        $this->observaciones = '';
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

    private function estadoOperativo(object $item): string
    {
        if (str_contains(strtoupper((string) $item->observaciones), '[NO DISPONIBLE]')) {
            return 'No disponible';
        }

        if ($item->archivado_en || strtoupper((string) $item->voluntario_estado) !== 'ACTIVO') {
            return 'Suspendido';
        }

        if (! $item->hora_inicio || ! $item->hora_fin) {
            return 'Pendiente';
        }

        return 'Disponible';
    }

    private function fechaParaDia(string $dia): string
    {
        $index = array_search($dia, $this->diasSemana(), true);

        return Carbon::parse($this->semanaInicio)
            ->startOfWeek(Carbon::MONDAY)
            ->addDays($index === false ? 0 : $index)
            ->format('d/m/Y');
    }

    private function diaDesdeFecha(Carbon $fecha): string
    {
        return $this->diasSemana()[$fecha->dayOfWeekIso - 1] ?? 'Lunes';
    }

    private function rangoSemanaTexto(): string
    {
        $inicio = Carbon::parse($this->semanaInicio)->startOfWeek(Carbon::MONDAY);
        $fin = $inicio->copy()->addDays(6);

        return $inicio->format('d/m/Y') . ' - ' . $fin->format('d/m/Y');
    }

    private function ordenDiaSql(string $columna): string
    {
        return "case {$columna} when 'Lunes' then 1 when 'Martes' then 2 when 'Miércoles' then 3 when 'Miercoles' then 3 when 'Jueves' then 4 when 'Viernes' then 5 when 'Sábado' then 6 when 'Sabado' then 6 when 'Domingo' then 7 else 8 end";
    }

    private function diasSemana(): array
    {
        return ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
    }

    private function turnos(): array
    {
        return ['Mañana', 'Tarde', 'Noche', 'Flexible'];
    }

    private function estados(): array
    {
        return ['Disponible', 'No disponible', 'Pendiente', 'Suspendido'];
    }
}
