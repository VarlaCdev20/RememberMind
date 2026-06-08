<?php

namespace App\Livewire\Admin\PersonalInstitucional;

use App\Models\AreaInstitucional;
use App\Models\TurnoInstitucional;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class TurnosAsignacionesPanel extends Component
{
    private const DIAS = [
        'LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO', 'DOMINGO',
    ];

    private const BLOQUES = ['MAÑANA', 'TARDE', 'NOCHE', 'MADRUGADA', 'ADMINISTRATIVO'];

    public string $busqueda = '';
    public string $filtroTipo = '';
    public string $filtroRol = '';
    public string $filtroArea = '';
    public string $filtroTurno = '';
    public string $filtroEstado = '';
    public string $vistaCalendario = 'semana';
    public string $fechaSeleccionada = '';

    public bool $modalAbierto = false;
    public string $busquedaModal = '';
    public ?string $usuarioSeleccionado = null;

    protected $listeners = [
        'cerrarModalHorarios' => 'cerrarModal',
        'asignacionActualizada' => '$refresh',
        'actualizarTablaPersonal' => '$refresh',
    ];

    public function mount(): void
    {
        $this->fechaSeleccionada = now()->format('Y-m-d');
    }

    public function abrirNuevaAsignacion(): void
    {
        $this->modalAbierto = true;
        $this->usuarioSeleccionado = null;
        $this->busquedaModal = '';
    }

    public function seleccionarUsuario(string $codUsu): void
    {
        $this->usuarioSeleccionado = $codUsu;
        $this->modalAbierto = true;
    }

    public function cerrarModal(): void
    {
        $this->modalAbierto = false;
        $this->usuarioSeleccionado = null;
        $this->busquedaModal = '';
    }

    public function cambiarVista(string $vista): void
    {
        if (in_array($vista, ['dia', 'semana', 'mes', 'anio'], true)) {
            $this->vistaCalendario = $vista;
        }
    }

    public function seleccionarFecha(string $fecha): void
    {
        $this->fechaSeleccionada = Carbon::parse($fecha)->format('Y-m-d');
        $this->vistaCalendario = 'dia';
    }

    public function irHoy(): void
    {
        $this->fechaSeleccionada = now()->format('Y-m-d');
    }

    public function moverPeriodo(int $direccion): void
    {
        $fecha = Carbon::parse($this->fechaSeleccionada);

        $fecha = match ($this->vistaCalendario) {
            'dia' => $fecha->addDays($direccion),
            'mes' => $fecha->addMonths($direccion),
            'anio' => $fecha->addYears($direccion),
            default => $fecha->addWeeks($direccion),
        };

        $this->fechaSeleccionada = $fecha->format('Y-m-d');
    }

    public function limpiarFiltros(): void
    {
        $this->reset([
            'busqueda',
            'filtroTipo',
            'filtroRol',
            'filtroArea',
            'filtroTurno',
            'filtroEstado',
        ]);
    }

    public function exportarCalendario(): void
    {
        $this->dispatch('mostrarAlerta', [
            'type' => 'info',
            'title' => 'Exportación no disponible',
            'message' => 'El botón queda preparado visualmente para conectar el backend de exportación.',
        ]);
    }

    public function render(): \Illuminate\View\View
    {
        $fecha = Carbon::parse($this->fechaSeleccionada);
        $areas = collect(User::$areasEstaticas)->map(fn ($nombre, $codigo) => (object) [
            'cod_area' => $codigo,
            'nombre' => $nombre,
        ]);
        $turnos = TurnoInstitucional::activos()->orderBy('hora_inicio')->get();
        $roles = Role::orderBy('name')->pluck('name');
        $personal = $this->personalFiltrado()->get();
        $asignaciones = $this->asignacionesFiltradas();
        $asignacionesActivas = $asignaciones->where('estado', 'ACTIVA')->values();
        $conflictos = $this->detectarConflictos($asignacionesActivas);

        $datosCalendario = match ($this->vistaCalendario) {
            'dia' => $this->datosDia($fecha, $personal, $asignaciones, $conflictos),
            'mes' => $this->datosMes($fecha, $asignacionesActivas, $turnos, $conflictos),
            'anio' => $this->datosAnio($fecha, $asignacionesActivas, $turnos, $conflictos),
            default => $this->datosSemana($fecha, $asignacionesActivas, $conflictos),
        };

        $usuarioSeleccionado = $this->usuarioSeleccionado
            ? User::with(['roles'])
                ->where('cod_usu', $this->usuarioSeleccionado)
                ->first()
            : null;

        return view('livewire.admin.personal-institucional.turnos-asignaciones-panel', [
            'areas' => $areas,
            'turnos' => $turnos,
            'roles' => $roles,
            'stats' => $this->metricas($personal, $asignacionesActivas, $turnos),
            'datosCalendario' => $datosCalendario,
            'personalModal' => $this->personalModal(),
            'usuarioSeleccionadoData' => $usuarioSeleccionado,
        ]);
    }

    private function personalFiltrado(): Builder
    {
        $query = User::with(['roles'])
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', [
                'SUPERADMINISTRADOR', 'ADMINISTRADOR', 'ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA'
            ]));

        if ($this->busqueda !== '') {
            $busqueda = $this->busqueda;
            $query->where(function (Builder $query) use ($busqueda) {
                $query->where('nombres', 'ilike', "%{$busqueda}%")
                    ->orWhere('ap_paterno', 'ilike', "%{$busqueda}%")
                    ->orWhere('ap_materno', 'ilike', "%{$busqueda}%")
                    ->orWhere('correo', 'ilike', "%{$busqueda}%")
                    ->orWhere('cod_usu', 'ilike', "%{$busqueda}%");
            });
        }

        if ($this->filtroTipo === 'salud') {
            $query->whereHas('roles', fn (Builder $query) => $query->whereIn('name', [
                'ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA'
            ]));
        } elseif ($this->filtroTipo === 'admin') {
            $query->whereHas('roles', fn (Builder $query) => $query->whereIn('name', [
                'SUPERADMINISTRADOR', 'ADMINISTRADOR'
            ]));
        }

        if ($this->filtroRol !== '') {
            $rol = $this->filtroRol;
            $query->whereHas('roles', fn (Builder $query) => $query->where('name', $rol));
        }

        if ($this->filtroArea !== '') {
            $query->where('cod_area', $this->filtroArea);
        }

        return $query->orderBy('nombres')->orderBy('ap_paterno');
    }

    private function obtenerVirtualAsignaciones(): Collection
    {
        $salud = \App\Models\HorarioPersonalSalud::with(['user.roles'])->get();
        $admin = \App\Models\HorarioPersonalAdmin::with(['user.roles'])->get();

        $mapped = collect();

        foreach ($salud as $h) {
            $mapped->push((object) [
                'cod_asignacion' => $h->cod_hor_per_sal,
                'cod_usu' => $h->cod_usu,
                'usuario' => $h->user,
                'estado' => $h->estado === 'ACTIVO' ? 'ACTIVA' : 'FINALIZADA',
                'dias_semana' => [$h->dia_semana],
                'fecha_inicio' => Carbon::create(1900),
                'fecha_fin' => Carbon::create(2999),
                'cod_area' => $h->user?->cod_area,
                'cod_turno' => $h->cod_hor_per_sal,
                'turno' => (object) [
                    'nombre' => $h->turno,
                    'hora_inicio' => $h->hora_inicio,
                    'hora_fin' => $h->hora_fin,
                ],
                'area' => (object) [
                    'nombre' => $h->user?->areaInstitucional?->nombre ?? 'Sin asignar',
                ],
                'creador' => (object) ['name' => 'Sistema'],
                'editor' => (object) ['name' => 'Sistema'],
            ]);
        }

        foreach ($admin as $h) {
            $mapped->push((object) [
                'cod_asignacion' => $h->cod_hor_per_admin,
                'cod_usu' => $h->cod_usu,
                'usuario' => $h->user,
                'estado' => $h->estado === 'ACTIVO' ? 'ACTIVA' : 'FINALIZADA',
                'dias_semana' => [$h->dia_semana],
                'fecha_inicio' => Carbon::create(1900),
                'fecha_fin' => Carbon::create(2999),
                'cod_area' => $h->user?->cod_area,
                'cod_turno' => $h->cod_hor_per_admin,
                'turno' => (object) [
                    'nombre' => $h->turno,
                    'hora_inicio' => $h->hora_inicio,
                    'hora_fin' => $h->hora_fin,
                ],
                'area' => (object) [
                    'nombre' => $h->user?->areaInstitucional?->nombre ?? 'Sin asignar',
                ],
                'creador' => (object) ['name' => 'Sistema'],
                'editor' => (object) ['name' => 'Sistema'],
            ]);
        }

        return $mapped;
    }

    private function asignacionesFiltradas(): Collection
    {
        $all = $this->obtenerVirtualAsignaciones();

        if ($this->busqueda !== '') {
            $busqueda = strtolower($this->busqueda);
            $all = $all->filter(function ($asig) use ($busqueda) {
                return str_contains(strtolower($asig->usuario?->nombres ?? ''), $busqueda)
                    || str_contains(strtolower($asig->usuario?->ap_paterno ?? ''), $busqueda)
                    || str_contains(strtolower($asig->usuario?->ap_materno ?? ''), $busqueda)
                    || str_contains(strtolower($asig->usuario?->cod_usu ?? ''), $busqueda);
            });
        }

        if ($this->filtroTipo === 'salud') {
            $all = $all->filter(fn ($asig) => in_array($asig->usuario?->roles->first()?->name, ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA']));
        } elseif ($this->filtroTipo === 'admin') {
            $all = $all->filter(fn ($asig) => in_array($asig->usuario?->roles->first()?->name, ['SUPERADMINISTRADOR', 'ADMINISTRADOR']));
        }

        if ($this->filtroRol !== '') {
            $rol = $this->filtroRol;
            $all = $all->filter(fn ($asig) => $asig->usuario?->roles->first()?->name === $rol);
        }

        if ($this->filtroArea !== '') {
            $area = $this->filtroArea;
            $all = $all->filter(fn ($asig) => $asig->cod_area === $area);
        }

        if ($this->filtroTurno !== '') {
            $turno = $this->filtroTurno;
            $all = $all->filter(fn ($asig) => $asig->turno?->nombre === $turno);
        }

        if ($this->filtroEstado === 'finalizado') {
            $all = $all->filter(fn ($asig) => $asig->estado === 'FINALIZADA');
        } else {
            $all = $all->filter(fn ($asig) => $asig->estado === 'ACTIVA');
        }

        return $all;
    }

    private function personalModal(): Collection
    {
        if (!$this->modalAbierto || $this->usuarioSeleccionado) {
            return collect();
        }

        return User::with(['roles'])
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', [
                'SUPERADMINISTRADOR', 'ADMINISTRADOR', 'ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA'
            ]))
            ->when($this->busquedaModal !== '', function (Builder $query) {
                $busqueda = $this->busquedaModal;
                $query->where(fn (Builder $query) => $query
                    ->where('nombres', 'ilike', "%{$busqueda}%")
                    ->orWhere('ap_paterno', 'ilike', "%{$busqueda}%")
                    ->orWhere('cod_usu', 'ilike', "%{$busqueda}%"));
            })
            ->orderBy('nombres')
            ->limit(20)
            ->get();
    }

    private function metricas(Collection $personal, Collection $asignaciones, Collection $turnos): array
    {
        $hoy = now();
        $asignacionesHoy = $this->eventosFecha($asignaciones, $hoy);
        $personalActivo = $personal->filter(fn (User $usuario) => $this->usuarioActivo($usuario));
        $conHorario = $asignaciones->pluck('cod_usu')->unique();
        $enTurno = $asignacionesHoy->filter(fn (object $asignacion) => $this->estaEnTurnoAhora($asignacion));
        $turnosCubiertos = $asignacionesHoy->pluck('cod_turno')->unique()->count();

        return [
            'con_horario' => $personalActivo->whereIn('cod_usu', $conHorario)->count(),
            'sin_horario' => $personalActivo->whereNotIn('cod_usu', $conHorario)->count(),
            'en_turno_hoy' => $enTurno->pluck('cod_usu')->unique()->count(),
            'disponibles_hoy' => max(0, $personalActivo->count() - $asignacionesHoy->pluck('cod_usu')->unique()->count()),
            'turnos_por_cubrir' => max(0, $turnos->count() - $turnosCubiertos),
            'asignaciones_activas' => $asignaciones->count(),
        ];
    }

    private function datosDia(Carbon $fecha, Collection $personal, Collection $asignaciones, array $conflictos): array
    {
        $filas = $personal->map(function (User $usuario) use ($fecha, $asignaciones, $conflictos) {
            $asignacionesUsuario = $asignaciones->where('cod_usu', $usuario->cod_usu);
            $asignacion = $this->eventosFecha($asignacionesUsuario, $fecha)->first();
            $estado = $this->estadoVisual($usuario, $asignacion, $fecha, $asignacionesUsuario, $conflictos);
            $rol = $this->rolVisual($usuario);

            return [
                'usuario' => $usuario,
                'asignacion' => $asignacion,
                'estado' => $estado,
                'rol' => $rol,
            ];
        });

        if ($this->filtroEstado !== '') {
            $filas = $filas->where('estado', strtoupper(str_replace('_', ' ', $this->filtroEstado)));
        }

        return [
            'titulo' => ucfirst($fecha->locale('es')->translatedFormat('l d \d\e F \d\e Y')),
            'filas' => $filas->values(),
        ];
    }

    private function datosSemana(Carbon $fecha, Collection $asignaciones, array $conflictos): array
    {
        $inicio = $fecha->copy()->startOfWeek(Carbon::MONDAY);
        $dias = collect(range(0, 6))->map(function (int $indice) use ($inicio, $asignaciones, $conflictos) {
            $dia = $inicio->copy()->addDays($indice);
            $eventos = $this->eventosSegunFiltro($this->eventosFecha($asignaciones, $dia), $dia, $conflictos)
                ->map(fn (object $asignacion) => $this->mapEvento($asignacion, $dia, $conflictos));

            return [
                'fecha' => $dia->format('Y-m-d'),
                'numero' => $dia->format('d'),
                'nombre' => ucfirst($dia->locale('es')->translatedFormat('D')),
                'es_hoy' => $dia->isToday(),
                'bloques' => collect(self::BLOQUES)->mapWithKeys(fn (string $bloque) => [
                    $bloque => $eventos->where('bloque', $bloque)->values(),
                ])->all(),
                'eventos' => $eventos,
            ];
        });

        return [
            'titulo' => $inicio->locale('es')->translatedFormat('d M') . ' - ' . $inicio->copy()->addDays(6)->locale('es')->translatedFormat('d M Y'),
            'dias' => $dias,
            'bloques' => self::BLOQUES,
        ];
    }

    private function datosMes(Carbon $fecha, Collection $asignaciones, Collection $turnos, array $conflictos): array
    {
        $inicio = $fecha->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $fin = $fecha->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $dias = collect();

        for ($cursor = $inicio->copy(); $cursor <= $fin; $cursor->addDay()) {
            $eventos = $this->eventosSegunFiltro($this->eventosFecha($asignaciones, $cursor), $cursor, $conflictos);
            $ids = $eventos->pluck('cod_asignacion');
            $dias->push([
                'fecha' => $cursor->format('Y-m-d'),
                'numero' => $cursor->format('d'),
                'es_hoy' => $cursor->isToday(),
                'es_mes' => $cursor->month === $fecha->month,
                'asignados' => $eventos->pluck('cod_usu')->unique()->count(),
                'cubiertos' => $eventos->pluck('cod_turno')->unique()->count(),
                'faltantes' => max(0, $turnos->count() - $eventos->pluck('cod_turno')->unique()->count()),
                'conflictos' => $ids->intersect(array_keys($conflictos))->count(),
            ]);
        }

        return [
            'titulo' => ucfirst($fecha->locale('es')->translatedFormat('F Y')),
            'dias' => $dias,
        ];
    }

    private function datosAnio(Carbon $fecha, Collection $asignaciones, Collection $turnos, array $conflictos): array
    {
        $meses = collect(range(1, 12))->map(function (int $mes) use ($fecha, $asignaciones, $turnos, $conflictos) {
            $inicio = Carbon::create($fecha->year, $mes, 1);
            $fin = $inicio->copy()->endOfMonth();
            $asignados = collect();
            $conflictosMes = collect();
            $cobertura = 0;

            for ($dia = $inicio->copy(); $dia <= $fin; $dia->addDay()) {
                $eventos = $this->eventosSegunFiltro($this->eventosFecha($asignaciones, $dia), $dia, $conflictos);
                $asignados = $asignados->merge($eventos->pluck('cod_usu'));
                $conflictosMes = $conflictosMes->merge($eventos->pluck('cod_asignacion')->intersect(array_keys($conflictos)));
                $cobertura += $eventos->pluck('cod_turno')->unique()->count();
            }

            $capacidad = max(1, $turnos->count() * $inicio->daysInMonth);

            return [
                'mes' => ucfirst($inicio->locale('es')->translatedFormat('F')),
                'fecha' => $inicio->format('Y-m-d'),
                'asignados' => $asignados->unique()->count(),
                'cobertura' => min(100, (int) round(($cobertura / $capacidad) * 100)),
                'conflictos' => $conflictosMes->unique()->count(),
            ];
        });

        return [
            'titulo' => (string) $fecha->year,
            'meses' => $meses,
        ];
    }

    private function eventosFecha(Collection $asignaciones, Carbon $fecha): Collection
    {
        $dia = self::DIAS[$fecha->dayOfWeekIso - 1];

        return $asignaciones->filter(function (object $asignacion) use ($fecha, $dia) {
            return $asignacion->estado === 'ACTIVA'
                && in_array($dia, $asignacion->dias_semana ?: [], true)
                && (!$asignacion->fecha_inicio || $asignacion->fecha_inicio->lte($fecha))
                && (!$asignacion->fecha_fin || $asignacion->fecha_fin->gte($fecha));
        })->values();
    }

    private function mapEvento(object $asignacion, Carbon $fecha, array $conflictos): array
    {
        return [
            'asignacion' => $asignacion,
            'rol' => $this->rolVisual($asignacion->usuario),
            'estado' => !$this->usuarioActivo($asignacion->usuario)
                ? 'SUSPENDIDO'
                : (isset($conflictos[$asignacion->cod_asignacion])
                    ? 'CONFLICTO'
                    : ($this->estaEnTurnoAhora($asignacion) && $fecha->isToday() ? 'EN TURNO' : 'OCUPADO')),
            'bloque' => $this->bloqueTurno($asignacion),
        ];
    }

    private function eventosSegunFiltro(Collection $eventos, Carbon $fecha, array $conflictos): Collection
    {
        if ($this->filtroEstado === '') {
            return $eventos;
        }

        return $eventos->filter(function (object $asignacion) use ($fecha, $conflictos) {
            $enConflicto = isset($conflictos[$asignacion->cod_asignacion]);
            $enTurno = $fecha->isToday() && $this->estaEnTurnoAhora($asignacion);
            $suspendido = !$this->usuarioActivo($asignacion->usuario);

            return match ($this->filtroEstado) {
                'conflicto' => $enConflicto,
                'en_turno' => !$suspendido && $enTurno,
                'ocupado' => !$suspendido && !$enConflicto && !$enTurno,
                'suspendido' => $suspendido,
                'disponible', 'sin_horario' => false,
                default => true,
            };
        })->values();
    }

    private function estadoVisual(User $usuario, ?object $asignacion, Carbon $fecha, Collection $asignacionesUsuario, array $conflictos): string
    {
        if (!$this->usuarioActivo($usuario)) {
            return 'SUSPENDIDO';
        }

        if ($asignacion && isset($conflictos[$asignacion->cod_asignacion])) {
            return 'CONFLICTO';
        }

        if ($asignacion && $fecha->isToday() && $this->estaEnTurnoAhora($asignacion)) {
            return 'EN TURNO';
        }

        if ($asignacion) {
            return 'OCUPADO';
        }

        return $asignacionesUsuario->where('estado', 'ACTIVA')->isEmpty() ? 'SIN HORARIO' : 'DISPONIBLE';
    }

    private function detectarConflictos(Collection $asignaciones): array
    {
        $conflictos = [];

        foreach ($asignaciones->groupBy('cod_usu') as $items) {
            $items = $items->values();
            for ($i = 0; $i < $items->count(); $i++) {
                for ($j = $i + 1; $j < $items->count(); $j++) {
                    if ($this->asignacionesSeCruzan($items[$i], $items[$j])) {
                        $conflictos[$items[$i]->cod_asignacion] = true;
                        $conflictos[$items[$j]->cod_asignacion] = true;
                    }
                }
            }
        }

        return $conflictos;
    }

    private function asignacionesSeCruzan(object $a, object $b): bool
    {
        if (empty(array_intersect($a->dias_semana ?: [], $b->dias_semana ?: []))) {
            return false;
        }

        $inicioA = $a->fecha_inicio ?? Carbon::create(1900);
        $inicioB = $b->fecha_inicio ?? Carbon::create(1900);
        $finA = $a->fecha_fin ?? Carbon::create(2999);
        $finB = $b->fecha_fin ?? Carbon::create(2999);

        if ($inicioA->gt($finB) || $inicioB->gt($finA)) {
            return false;
        }

        return $this->rangosHoraSeCruzan(
            $a->turno?->hora_inicio,
            $a->turno?->hora_fin,
            $b->turno?->hora_inicio,
            $b->turno?->hora_fin
        );
    }

    private function rangosHoraSeCruzan(?string $inicioA, ?string $finA, ?string $inicioB, ?string $finB): bool
    {
        if (!$inicioA || !$finA || !$inicioB || !$finB) {
            return true;
        }

        [$aInicio, $aFin] = $this->rangoMinutos($inicioA, $finA);
        [$bInicio, $bFin] = $this->rangoMinutos($inicioB, $finB);

        foreach ([-1440, 0, 1440] as $desplazamiento) {
            if ($aInicio < $bFin + $desplazamiento && $bInicio + $desplazamiento < $aFin) {
                return true;
            }
        }

        return false;
    }

    private function rangoMinutos(string $inicio, string $fin): array
    {
        $inicioMinutos = Carbon::parse($inicio)->hour * 60 + Carbon::parse($inicio)->minute;
        $finMinutos = Carbon::parse($fin)->hour * 60 + Carbon::parse($fin)->minute;

        if ($finMinutos <= $inicioMinutos) {
            $finMinutos += 1440;
        }

        return [$inicioMinutos, $finMinutos];
    }

    private function estaEnTurnoAhora(object $asignacion): bool
    {
        if (!$asignacion->turno?->hora_inicio || !$asignacion->turno?->hora_fin) {
            return false;
        }

        [$inicio, $fin] = $this->rangoMinutos($asignacion->turno->hora_inicio, $asignacion->turno->hora_fin);
        $ahora = now()->hour * 60 + now()->minute;

        return ($ahora >= $inicio && $ahora < $fin)
            || ($fin > 1440 && $ahora + 1440 < $fin);
    }

    private function bloqueTurno(object $asignacion): string
    {
        $nombre = mb_strtoupper($asignacion->turno?->nombre ?? '');

        foreach (self::BLOQUES as $bloque) {
            if (str_contains($nombre, $bloque)) {
                return $bloque;
            }
        }

        $hora = (int) substr((string) $asignacion->turno?->hora_inicio, 0, 2);

        return match (true) {
            $hora < 6 => 'MADRUGADA',
            $hora < 12 => 'MAÑANA',
            $hora < 18 => 'TARDE',
            default => 'NOCHE',
        };
    }

    private function usuarioActivo(User $usuario): bool
    {
        return in_array((string) $usuario->estado, ['ACTIVO', '1'], true);
    }

    private function rolVisual(User $usuario): array
    {
        $texto = mb_strtoupper(($usuario->personalSalud?->tipo_personal_salud ?? '') . ' ' . $usuario->roles->pluck('name')->implode(' '));

        return match (true) {
            $usuario->personalAdmin !== null => ['label' => 'Administrativo', 'class' => 'border-slate-300 bg-slate-100 text-slate-700'],
            str_contains($texto, 'ENFERM') => ['label' => 'Enfermería', 'class' => 'border-emerald-200 bg-emerald-50 text-emerald-700'],
            str_contains($texto, 'MEDICO') || str_contains($texto, 'MÉDICO') || str_contains($texto, 'GERIATRA') => ['label' => 'Médico / Geriatra', 'class' => 'border-[#F2CFC4] bg-[#FDF5F2] text-[#B85C45]'],
            str_contains($texto, 'PSICO') => ['label' => 'Psicología', 'class' => 'border-violet-200 bg-violet-50 text-violet-700'],
            str_contains($texto, 'NUTRI') => ['label' => 'Nutrición', 'class' => 'border-amber-200 bg-amber-50 text-amber-700'],
            str_contains($texto, 'FISIO') => ['label' => 'Fisioterapia', 'class' => 'border-sky-200 bg-sky-50 text-sky-700'],
            str_contains($texto, 'PEDAGOG') => ['label' => 'Pedagogía', 'class' => 'border-orange-200 bg-orange-50 text-orange-700'],
            default => ['label' => $usuario->roles->first()?->name ?? 'Sin rol', 'class' => 'border-stone-200 bg-stone-50 text-stone-600'],
        };
    }
}
