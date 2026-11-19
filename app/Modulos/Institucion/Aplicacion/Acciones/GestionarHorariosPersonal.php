<?php

namespace App\Modulos\Institucion\Aplicacion\Acciones;

use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Compartido\Aplicacion\Contratos\ContextoEjecucion;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class GestionarHorariosPersonal
{
    public function __construct(private readonly ContextoEjecucion $contexto) {}

    public function autorizar(bool $escritura = true): User
    {
        $usuario = $this->contexto->usuario();
        $usuario = $usuario ? User::find($usuario->getKey()) : null;
        abort_unless($usuario?->estado === 'ACTIVO' && ! $usuario->hasRole('FAMILIAR'), 403);
        abort_unless($escritura ? $usuario->can('turnos.asignar') : $usuario->canAny(['asignaciones_personal.ver', 'personal_institucional.ver', 'turnos.asignar']), 403);
        abort_if($escritura && ($this->contexto->esLectura() || app(RolePreviewService::class)->isActive($usuario)), 403);

        return $usuario;
    }

    private const DIAS = [
        'LUNES',
        'MARTES',
        'MIERCOLES',
        'JUEVES',
        'VIERNES',
        'SABADO',
        'DOMINGO',
    ];

    public const TURNOS_ROTACION = [
        'MANANA' => ['nombre' => 'Mañana', 'catalogo' => ['TURNO MAÑANA', 'MANANA'], 'inicio' => '07:00', 'fin' => '15:00'],
        'TARDE' => ['nombre' => 'Tarde', 'catalogo' => ['TURNO TARDE', 'TARDE'], 'inicio' => '15:00', 'fin' => '23:00'],
        'NOCHE' => ['nombre' => 'Noche', 'catalogo' => ['TURNO NOCHE', 'NOCHE'], 'inicio' => '23:00', 'fin' => '07:00'],
    ];

    public function guardar(array $entrada): void
    {
        $actor = $this->autorizar();
        Validator::make($entrada, ['f_rotacion_automatica' => ['sometimes', 'boolean']])->validate();
        $esRotacion = (bool) ($entrada['f_rotacion_automatica'] ?? false);
        if ($entrada['modoEdicion'] ?? false) {
            app(ConservarEventoAsignacion::class)->exigir();
        }
        Validator::make($entrada, ['usuarioId' => ['required', 'string', 'exists:usuarios,cod_usuario'],
            'f_cod_area' => ['required', 'string', 'exists:areas,cod_area'],
            'f_cod_turno' => $esRotacion ? ['exclude'] : ['required', 'string', 'exists:turnos,cod_turno'],
            'f_observaciones' => ['nullable', 'string', 'max:1000'],
            'f_tipo_asignacion' => ['required', 'string', 'max:30'], 'modoEdicion' => ['required', 'boolean']])->validate();
        $calendario = $esRotacion
            ? $this->calendarioRotacion($entrada)
            : array_fill_keys($this->fechasParaAsignar($entrada), $entrada['f_cod_turno']);
        $usuario = User::with(['roles', 'personal'])->whereKey($entrada['usuarioId'])->firstOrFail();
        if ($usuario->personal?->estado !== 'ACTIVO' || ! Area::whereKey($entrada['f_cod_area'])->whereIn('estado', ['ACTIVO', 'ACTIVA'])->exists()) {
            throw ValidationException::withMessages(['f_cod_area' => 'El trabajador y el área deben estar activos.']);
        }
        if ($usuario->roles->isEmpty()) {
            throw ValidationException::withMessages(['f_cod_area' => 'El usuario no tiene un rol institucional asignado.']);
        }
        if (in_array(strtoupper((string) $usuario->estado), ['INACTIVO', 'SUSPENDIDO', 'RETIRADO', '0'], true)) {
            throw ValidationException::withMessages(['f_cod_area' => 'No se puede asignar horario a personal INACTIVO, SUSPENDIDO o RETIRADO.']);
        }
        $fechaInicio = Carbon::parse($entrada['f_fecha_inicio'])->startOfDay();
        $isEdit = $entrada['modoEdicion'];
        DB::beginTransaction();

        try {
            $this->autorizar();
            $usuario = User::whereKey($usuario->getKey())->lockForUpdate()->firstOrFail();
            if ($esRotacion && ($isEdit || $entrada['f_tipo_asignacion'] !== 'ROTATIVO' || ! $usuario->hasRole('ENFERMEROS'))) {
                throw ValidationException::withMessages(['f_rotacion_automatica' => 'La rotación automática se aplica al crear un horario rotativo de Enfermería.']);
            }
            if (! $esRotacion && ! empty($entrada['f_fecha_fin']) && ($isEdit || $entrada['f_tipo_asignacion'] !== 'FIJO' || ! $usuario->hasRole('ENFERMEROS'))) {
                throw ValidationException::withMessages(['f_fecha_fin' => 'La repetición semanal se aplica al crear un horario fijo de Enfermería.']);
            }
            $areaVigente = Area::whereKey($entrada['f_cod_area'])->lockForUpdate()->firstOrFail();
            if (! in_array($areaVigente->estado, ['ACTIVO', 'ACTIVA'], true)) {
                throw ValidationException::withMessages(['f_cod_area' => 'El área ya no está activa.']);
            }
            $personal = Personal::whereKey($usuario->personal->getKey())->lockForUpdate()->firstOrFail();
            if ($personal->estado !== 'ACTIVO' || $usuario->estado !== 'ACTIVO') {
                throw ValidationException::withMessages(['f_cod_area' => 'La cuenta o el trabajador cambiaron de estado. Actualiza antes de asignar.']);
            }
            $turnos = $esRotacion ? $this->turnosRotacion(bloquear: true) : $this->turnoIndividual($entrada['f_cod_turno']);
            $solapamiento = $this->verificarSolapamiento($entrada, $turnos, $calendario);
            if ($solapamiento) {
                throw ValidationException::withMessages([($esRotacion ? 'f_rotacion_automatica' : 'f_dias_semana') => $solapamiento]);
            }

            if ($isEdit && $entrada['codAsignacionEditar']) {
                $this->actualizarEstadoAsignacionGrupo($entrada,
                    $entrada['codAsignacionEditar'],
                    'INACTIVO',
                    $fechaInicio->copy()->subSecond()
                );
            }

            foreach ($calendario as $fecha => $turnoProgramado) {
                if ($turnoProgramado === null) {
                    continue;
                }
                $turno = $turnos[$turnoProgramado];
                $fechaJornada = Carbon::parse($fecha);
                // El cast date se almacena con hora en SQLite y como DATE en PostgreSQL.
                // Buscar por fecha evita crear otra jornada y eludir el estado de la original.
                $jornada = Jornada::query()->where('cod_turno', $turno->cod_turno)
                    ->whereDate('fecha_jornada', $fechaJornada->toDateString())->first();
                if (! $jornada) {
                    $jornada = Jornada::create([
                        'cod_jornada' => app(CodigosEntidad::class)->siguiente('jornadas'),
                        'cod_turno' => $turno->cod_turno, 'fecha_jornada' => $fechaJornada->toDateString(),
                        'estado' => 'ABIERTA',
                        'cod_usuario_apertura' => $actor->getKey(),
                    ]);
                }
                $jornada = Jornada::whereKey($jornada->getKey())->lockForUpdate()->firstOrFail();
                if (! in_array($jornada->estado, ['PLANIFICADA', 'ABIERTA', 'ACTIVA', 'EN_CURSO'], true)) {
                    throw ValidationException::withMessages(['f_fecha_inicio' => 'La jornada del '.$fechaJornada->format('d/m/Y').' está cerrada o no admite asignaciones.']);
                }
                $jornada->abrirAlConfirmarHorario($actor);
                $asignacion = AsignacionPersonal::query()->create([
                    'cod_asignacion_personal' => app(CodigosEntidad::class)->siguiente('asignaciones_personal'),
                    'cod_jornada' => $jornada->cod_jornada,
                    'cod_personal' => $personal->cod_personal,
                    'cod_area' => $entrada['f_cod_area'],
                    'funcion' => $personal->profesion,
                    'tipo_asignacion' => $entrada['f_tipo_asignacion'],
                    'fecha_asignacion' => now(),
                    'estado' => 'ACTIVO',
                    'observacion' => trim($entrada['f_observaciones'] ?? '') ?: null,
                ]);
                app(ConservarEventoAsignacion::class)->conservar($asignacion, $actor, 'ALTA', null, $fechaJornada->copy()->setTimeFromTimeString($turno->hora_inicio), 'Alta de ocurrencia explícita de planificación.');
            }

            activity('Planificación')->causedBy($actor)->performedOn($personal)->event('horario_registrado')
                ->log('Registró asignaciones de jornada; no acredita asistencia.');

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function confirmarFinalizar(array $entrada): void
    {
        $actor = $this->autorizar();
        app(ConservarEventoAsignacion::class)->exigir();
        Validator::make($entrada, ['f_fecha_fin_finalizar' => ['required', 'date_format:Y-m-d'], 'codAsignacionFinalizar' => ['required', 'string'], 'usuarioId' => ['required', 'string', 'exists:usuarios,cod_usuario']])->validate();
        $fechaFin = Carbon::parse($entrada['f_fecha_fin_finalizar'])->startOfDay()->subSecond();
        DB::beginTransaction();

        try {
            $this->actualizarEstadoAsignacionGrupo($entrada, $entrada['codAsignacionFinalizar'], 'INACTIVO', $fechaFin);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function actualizarEstadoAsignacionGrupo(array $entrada, string $codAsignacion, string $estado, Carbon $fecha): void
    {
        $actor = $this->autorizar();
        $origen = $this->queryHorarios($entrada)->whereKey($codAsignacion)->lockForUpdate()->firstOrFail();
        if (! in_array($origen->estado, ['ACTIVO', 'ACTIVA'], true)) {
            throw ValidationException::withMessages(['f_fecha_fin_finalizar' => 'La asignación ya no está activa. Actualiza la consulta.']);
        }
        $ids = $this->idsGrupo($entrada, $codAsignacion);
        $cambiadas = 0;
        foreach ($this->queryHorarios($entrada)->whereKey($ids)->whereIn('estado', ['ACTIVO', 'ACTIVA'])->with('jornada.turno')->lockForUpdate()->get() as $asignacion) {
            $inicio = Carbon::parse($asignacion->jornada->fecha_jornada)->setTimeFromTimeString($asignacion->jornada->turno->hora_inicio);
            if ($inicio->lte($fecha)) {
                continue;
            }
            if ($asignacion->estado === $estado) {
                continue;
            }
            $antes = $asignacion->estado;
            $asignacion->estado = $estado;
            $asignacion->save();
            $cambiadas++;
            app(ConservarEventoAsignacion::class)->conservar($asignacion, $actor, 'FINALIZACION', $antes, $fecha, 'Finalización de las ocurrencias posteriores a la fecha revisada.');
        }
        if (! $cambiadas && isset($entrada['codAsignacionFinalizar'])) {
            throw ValidationException::withMessages(['f_fecha_fin_finalizar' => 'No hay ocurrencias activas posteriores a esa fecha. No se modificó la historia previa.']);
        }
    }

    private function idsGrupo(array $entrada, string $codAsignacion): array
    {
        // Lectura del grupo histórico existente; no se confían sus IDs desde Livewire.
        $origen = $this->queryHorarios($entrada)->whereKey($codAsignacion)->lockForUpdate()->firstOrFail();
        $meta = json_decode((string) $origen->observacion, true);
        $grupo = is_array($meta) ? ($meta['grupo'] ?? null) : null;

        return $this->queryHorarios($entrada)->lockForUpdate()->get()->filter(function ($fila) use ($origen, $grupo) {
            if (! $grupo) {
                return $fila->getKey() === $origen->getKey();
            }
            $datos = json_decode((string) $fila->observacion, true);

            return is_array($datos) && ($datos['grupo'] ?? null) === $grupo;
        })->modelKeys();

    }

    private function queryHorarios(array $entrada)
    {
        return AsignacionPersonal::query()
            ->whereHas('personal', fn ($query) => $query->where('cod_usuario', $entrada['usuarioId']));
    }

    private function primaryKeyHorario(array $entrada): string
    {
        return 'cod_asignacion_personal';
    }

    /** @return list<string> */
    public function fechasParaAsignar(array $entrada): array
    {
        Validator::make($entrada, [
            'f_fecha_inicio' => ['required', 'date_format:Y-m-d'],
            'f_fecha_fin' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:f_fecha_inicio'],
            'f_dias_semana' => ['required', 'array', 'min:1', 'max:7'],
            'f_dias_semana.*' => ['required', 'in:'.implode(',', self::DIAS), 'distinct'],
        ], [
            'f_fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'f_fecha_fin.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha de inicio.',
            'f_dias_semana.required' => 'Selecciona al menos un día laboral.',
        ])->validate();

        $inicio = Carbon::parse($entrada['f_fecha_inicio']);
        $fin = empty($entrada['f_fecha_fin']) ? $inicio->copy()->addDays(6) : Carbon::parse($entrada['f_fecha_fin']);
        $this->validarHorizonte($inicio, $fin);

        $fechas = [];
        for ($fecha = $inicio; $fecha->lte($fin); $fecha->addDay()) {
            if (in_array(self::DIAS[$fecha->dayOfWeekIso - 1], $entrada['f_dias_semana'], true)) {
                $fechas[] = $fecha->toDateString();
            }
        }
        if ($fechas === []) {
            throw ValidationException::withMessages(['f_dias_semana' => 'Ninguno de los días seleccionados cae dentro de esas fechas.']);
        }

        return $fechas;
    }

    /** @return array<string, string|null> */
    public function previsualizarRotacion(array $entrada): array
    {
        $this->autorizar();
        $calendario = $this->calendarioRotacion($entrada);
        $this->turnosRotacion();

        return $calendario;
    }

    public function prepararTurnosRotacion(): void
    {
        $actor = $this->autorizar();
        abort_unless($actor->can('turnos.gestionar'), 403);

        DB::transaction(function () use ($actor) {
            $catalogo = Turno::query()->orderBy('cod_turno')->lockForUpdate()->get();
            foreach (self::TURNOS_ROTACION as $definicion) {
                $existentes = $catalogo->whereIn('nombre', $definicion['catalogo']);
                if ($existentes->isNotEmpty()) {
                    continue;
                }

                $orden = collect(range(1, 10))->diff($catalogo->pluck('orden'))->first();
                if ($orden === null) {
                    throw ValidationException::withMessages(['f_rotacion_automatica' => 'El catálogo de turnos no tiene un orden disponible. Revisa los turnos configurados.']);
                }
                app(GestionarTurnosInstitucionales::class)->guardar($actor, [
                    'editandoId' => null,
                    'nombre' => $definicion['catalogo'][0],
                    'horaInicio' => $definicion['inicio'],
                    'horaFin' => $definicion['fin'],
                    'orden' => $orden,
                    'estado' => 'ACTIVO',
                    'observacion' => 'Turno de ocho horas para rotación de Enfermería.',
                ]);
                $catalogo = Turno::query()->get();
            }
            $this->turnosRotacion();
        });
    }

    /** @return array<string, string|null> */
    private function calendarioRotacion(array $entrada): array
    {
        Validator::make($entrada, [
            'f_fecha_inicio' => ['required', 'date_format:Y-m-d'],
            'f_fecha_fin' => ['required', 'date_format:Y-m-d', 'after_or_equal:f_fecha_inicio'],
            'f_turno_inicial' => ['required', 'in:'.implode(',', array_keys(self::TURNOS_ROTACION))],
        ], [
            'f_fecha_inicio.required' => 'Indica cuándo comienza la rotación.',
            'f_fecha_fin.required' => 'Indica hasta qué fecha se programará la rotación.',
            'f_fecha_fin.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha de inicio.',
            'f_turno_inicial.in' => 'Selecciona un turno inicial válido.',
        ])->validate();

        $inicio = Carbon::parse($entrada['f_fecha_inicio']);
        $fin = Carbon::parse($entrada['f_fecha_fin']);
        $this->validarHorizonte($inicio, $fin);
        $ciclo = ['MANANA', 'TARDE', 'NOCHE', null];
        $indiceInicial = array_search($entrada['f_turno_inicial'], $ciclo, true);

        $calendario = [];
        for ($fecha = $inicio, $dia = 0; $fecha->lte($fin); $fecha->addDay(), $dia++) {
            $calendario[$fecha->toDateString()] = $ciclo[($dia + $indiceInicial) % count($ciclo)];
        }

        return $calendario;
    }

    private function validarHorizonte(Carbon $inicio, Carbon $fin): void
    {
        if ($fin->gt($inicio->copy()->addYearNoOverflow())) {
            throw ValidationException::withMessages(['f_fecha_fin' => 'Puedes planificar hasta un año por asignación.']);
        }
    }

    /** @return Collection<string, Turno> */
    private function turnoIndividual(string $codigo): Collection
    {
        $turno = Turno::whereKey($codigo)->where('estado', 'ACTIVO')->lockForUpdate()->first();
        if (! $turno) {
            throw ValidationException::withMessages(['f_cod_turno' => 'El turno seleccionado no está disponible.']);
        }

        return collect([$codigo => $turno]);
    }

    /** @return Collection<string, Turno> */
    private function turnosRotacion(bool $bloquear = false): Collection
    {
        $nombres = array_merge(...array_column(self::TURNOS_ROTACION, 'catalogo'));
        $catalogo = Turno::query()->whereIn('nombre', $nombres)->where('estado', 'ACTIVO')->orderBy('cod_turno')
            ->when($bloquear, fn ($query) => $query->lockForUpdate())->get();

        $turnos = collect();
        $faltantes = [];
        foreach (self::TURNOS_ROTACION as $fase => $definicion) {
            $coincidencias = $catalogo->filter(fn (Turno $turno) => in_array($turno->nombre, $definicion['catalogo'], true)
                && in_array($turno->hora_inicio, [$definicion['inicio'], $definicion['inicio'].':00'], true)
                && in_array($turno->hora_cierre, [$definicion['fin'], $definicion['fin'].':00'], true));
            if ($coincidencias->count() !== 1) {
                $faltantes[] = $definicion['nombre'].' '.$definicion['inicio'].'–'.$definicion['fin'];

                continue;
            }
            $turnos[$fase] = $coincidencias->sole();
        }
        if ($faltantes !== []) {
            throw ValidationException::withMessages(['f_rotacion_automatica' => 'El catálogo debe tener un turno activo por cada horario. Revisa: '.implode(', ', $faltantes).'.']);
        }

        return $turnos;
    }

    public function actualizarGrupo(array $entrada, string $codAsignacion, string $estado, Carbon $fecha): void
    {
        $this->autorizar();
        app(ConservarEventoAsignacion::class)->exigir();
        abort_unless(in_array($estado, ['ACTIVO', 'INACTIVO'], true), 422);
        DB::transaction(fn () => $this->actualizarEstadoAsignacionGrupo($entrada, $codAsignacion, $estado, $fecha));
    }

    private function verificarSolapamiento(array $entrada, Collection $turnos, array $calendario): ?string
    {
        $idsExcluir = $entrada['modoEdicion'] && ($entrada['codAsignacionEditar'] ?? null)
            ? $this->idsGrupo($entrada, $entrada['codAsignacionEditar'])
            : [];
        if ($idsExcluir) {
            $corte = Carbon::parse($entrada['f_fecha_inicio'])->startOfDay();
            $idsExcluir = $this->queryHorarios($entrada)->whereKey($idsExcluir)->with('jornada.turno')->get()
                ->filter(fn ($a) => Carbon::parse($a->jornada->fecha_jornada)->setTimeFromTimeString($a->jornada->turno->hora_inicio)->gte($corte))->modelKeys();
        }

        $desde = Carbon::parse(array_key_first($calendario))->subDays(2)->toDateString();
        $hasta = Carbon::parse(array_key_last($calendario))->addDays(2)->toDateString();
        $existentes = $this->queryHorarios($entrada)
            ->with('jornada.turno')
            ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->whereHas('jornada', fn ($query) => $query->whereDate('fecha_jornada', '>=', $desde)->whereDate('fecha_jornada', '<=', $hasta))
            ->when($idsExcluir !== [], function ($query) use ($idsExcluir, $entrada) {
                $query->whereNotIn($this->primaryKeyHorario($entrada), $idsExcluir);
            })
            ->get();

        foreach ($existentes as $existente) {
            $turnoExistente = $existente->jornada?->turno;
            if (! $turnoExistente) {
                throw ValidationException::withMessages(['f_cod_turno' => 'Una asignación existente no tiene un turno verificable. Revise la planificación.']);
            }
            [$inicioExistente, $finExistente] = $this->intervalo($existente->jornada->fecha_jornada, $turnoExistente);
            $finDescansoExistente = $this->finConDescansoRotativo($turnoExistente, (string) $existente->tipo_asignacion, $finExistente);
            $fechaExistente = Carbon::parse($existente->jornada->fecha_jornada);
            $diaExistente = $fechaExistente->toDateString();
            if (array_key_exists($diaExistente, $calendario) && $calendario[$diaExistente] === null) {
                return 'El descanso del '.$fechaExistente->format('d/m/Y').' tiene un turno activo ('.$turnoExistente->nombre.').';
            }
            // La noche rotativa conserva además las 24 horas de descanso posteriores.
            for ($desplazamiento = -2; $desplazamiento <= 2; $desplazamiento++) {
                $fecha = $fechaExistente->copy()->addDays($desplazamiento)->toDateString();
                if (! isset($calendario[$fecha])) {
                    continue;
                }
                $turno = $turnos[$calendario[$fecha]];
                [$inicio, $fin] = $this->intervalo($fecha, $turno);
                if ($inicio->lt($finExistente) && $inicioExistente->lt($fin)) {
                    return 'El horario del '.$inicio->format('d/m/Y').' se cruza con el turno '.$turnoExistente->nombre.' del '.$fechaExistente->format('d/m/Y').'.';
                }
                $finDescanso = $this->finConDescansoRotativo($turno, $entrada['f_tipo_asignacion'], $fin);
                if ($inicio->lt($finDescansoExistente) && $inicioExistente->lt($finDescanso)) {
                    return 'El horario del '.$inicio->format('d/m/Y').' invade las 24 horas de descanso posteriores a una noche rotativa.';
                }
            }
        }

        return null;
    }

    public function finConDescansoRotativo(Turno $turno, string $tipo, Carbon $fin): Carbon
    {
        $esNocheRotativa = $tipo === 'ROTATIVO' && in_array($turno->hora_inicio, ['23:00', '23:00:00'], true)
            && in_array($turno->hora_cierre, ['07:00', '07:00:00'], true);

        return $esNocheRotativa ? $fin->copy()->addDay() : $fin;
    }

    private function intervalo(mixed $fecha, Turno $turno): array
    {
        $dia = Carbon::parse($fecha)->toDateString();
        $inicio = Carbon::parse($dia.' '.$turno->hora_inicio);
        $fin = Carbon::parse($dia.' '.$turno->hora_cierre);
        if ($fin->lte($inicio)) {
            $fin->addDay();
        }

        return [$inicio, $fin];
    }

    private function rangosHoraSeCruzan(array $entrada, string $inicioA, string $finA, string $inicioB, string $finB): bool
    {
        [$aInicio, $aFin] = $this->rangoMinutos($entrada, $inicioA, $finA);
        [$bInicio, $bFin] = $this->rangoMinutos($entrada, $inicioB, $finB);

        foreach ([-1440, 0, 1440] as $desplazamiento) {
            if ($aInicio < $bFin + $desplazamiento && $bInicio + $desplazamiento < $aFin) {
                return true;
            }
        }

        return false;
    }

    private function rangoMinutos(array $entrada, string $inicio, string $fin): array
    {
        $inicioCarbon = Carbon::parse($inicio);
        $finCarbon = Carbon::parse($fin);

        $inicioMinutos = $inicioCarbon->hour * 60 + $inicioCarbon->minute;
        $finMinutos = $finCarbon->hour * 60 + $finCarbon->minute;

        if ($finMinutos <= $inicioMinutos) {
            $finMinutos += 1440;
        }

        return [$inicioMinutos, $finMinutos];
    }
}
