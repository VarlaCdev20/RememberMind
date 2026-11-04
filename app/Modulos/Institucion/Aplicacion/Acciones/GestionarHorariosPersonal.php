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

    public function guardar(array $entrada): void
    {
        $actor = $this->autorizar();
        if ($entrada['modoEdicion'] ?? false) {
            app(ConservarEventoAsignacion::class)->exigir();
        }
        Validator::make($entrada, ['usuarioId' => ['required', 'string', 'exists:usuarios,cod_usuario'],
            'f_cod_area' => ['required', 'string', 'exists:areas,cod_area'], 'f_cod_turno' => ['required', 'string', 'exists:turnos,cod_turno'],
            'f_dias_semana' => ['required', 'array', 'min:1', 'max:7'], 'f_dias_semana.*' => ['required', 'in:'.implode(',', self::DIAS), 'distinct'],
            'f_fecha_inicio' => ['required', 'date_format:Y-m-d'], 'f_observaciones' => ['nullable', 'string', 'max:1000'],
            'f_tipo_asignacion' => ['required', 'string', 'max:30'], 'modoEdicion' => ['required', 'boolean']])->validate();
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
        $turno = Turno::query()->whereKey($entrada['f_cod_turno'])->where('estado', 'ACTIVO')->first();
        if (! $turno) {
            throw ValidationException::withMessages(['f_cod_turno' => 'El turno seleccionado no está disponible.']);
        }
        $fechaInicio = Carbon::parse($entrada['f_fecha_inicio'])->startOfDay();
        $isEdit = $entrada['modoEdicion'];
        DB::beginTransaction();

        try {
            $this->autorizar();
            $usuario = User::whereKey($usuario->getKey())->lockForUpdate()->firstOrFail();
            $areaVigente = Area::whereKey($entrada['f_cod_area'])->lockForUpdate()->firstOrFail();
            if (! in_array($areaVigente->estado, ['ACTIVO', 'ACTIVA'], true)) {
                throw ValidationException::withMessages(['f_cod_area' => 'El área ya no está activa.']);
            }
            $personal = Personal::whereKey($usuario->personal->getKey())->lockForUpdate()->firstOrFail();
            $turno = Turno::whereKey($entrada['f_cod_turno'])->lockForUpdate()->firstOrFail();
            if ($personal->estado !== 'ACTIVO' || $turno->estado !== 'ACTIVO' || $usuario->fresh()->estado !== 'ACTIVO') {
                throw ValidationException::withMessages(['f_cod_area' => 'La cuenta, el trabajador o el turno cambiaron de estado. Actualiza antes de asignar.']);
            }
            if (! $personal) {
                throw ValidationException::withMessages(['f_cod_area' => 'El usuario no tiene un registro de personal V2 asociado.']);
            }
            $solapamiento = $this->verificarSolapamiento($entrada, $turno);
            if ($solapamiento) {
                throw ValidationException::withMessages(['f_dias_semana' => $solapamiento]);
            }

            if ($isEdit && $entrada['codAsignacionEditar']) {
                $this->actualizarEstadoAsignacionGrupo($entrada,
                    $entrada['codAsignacionEditar'],
                    'INACTIVO',
                    $fechaInicio->copy()->subSecond()
                );
            }

            foreach ($entrada['f_dias_semana'] as $dia) {
                $fechaJornada = $this->fechaParaDia($entrada, $fechaInicio, $dia);
                // El cast date se almacena con hora en SQLite y como DATE en PostgreSQL.
                // Buscar por fecha evita crear otra jornada y eludir el estado de la original.
                $jornada = Jornada::query()->where('cod_turno', $turno->cod_turno)
                    ->whereDate('fecha_jornada', $fechaJornada->toDateString())->first();
                if (! $jornada) {
                    $jornada = Jornada::create([
                        'cod_jornada' => app(CodigosEntidad::class)->siguiente('jornadas'),
                        'cod_turno' => $turno->cod_turno, 'fecha_jornada' => $fechaJornada->toDateString(),
                        'estado' => 'ABIERTA',
                    ]);
                }
                $jornada = Jornada::whereKey($jornada->getKey())->lockForUpdate()->firstOrFail();
                if (! in_array($jornada->estado, ['PLANIFICADA', 'ABIERTA', 'ACTIVA', 'EN_CURSO'], true)) {
                    throw ValidationException::withMessages(['f_fecha_inicio' => 'La jornada está cerrada o no admite asignaciones.']);
                }
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

    private function fechaParaDia(array $entrada, Carbon $base, string $dia): Carbon
    {
        $indice = array_search($this->normalizarDiaSemana($entrada, $dia), self::DIAS, true);
        $objetivoIso = ($indice === false ? 0 : $indice) + 1;
        $diferencia = ($objetivoIso - $base->dayOfWeekIso + 7) % 7;

        return $base->copy()->addDays($diferencia);
    }

    private function normalizarDiaSemana(array $entrada, string $dia): string
    {
        $dia = mb_strtoupper(trim($dia));
        $dia = str_replace(['Á', 'É', 'Í', 'Ó', 'Ú'], ['A', 'E', 'I', 'O', 'U'], $dia);

        return in_array($dia, self::DIAS, true) ? $dia : 'LUNES';
    }

    public function actualizarGrupo(array $entrada, string $codAsignacion, string $estado, Carbon $fecha): void
    {
        $this->autorizar();
        app(ConservarEventoAsignacion::class)->exigir();
        abort_unless(in_array($estado, ['ACTIVO', 'INACTIVO'], true), 422);
        DB::transaction(fn () => $this->actualizarEstadoAsignacionGrupo($entrada, $codAsignacion, $estado, $fecha));
    }

    private function verificarSolapamiento(array $entrada, Turno $turno): ?string
    {
        $idsExcluir = $entrada['modoEdicion'] && ($entrada['codAsignacionEditar'] ?? null)
            ? $this->idsGrupo($entrada, $entrada['codAsignacionEditar'])
            : [];
        if ($idsExcluir) {
            $corte = Carbon::parse($entrada['f_fecha_inicio'])->startOfDay();
            $idsExcluir = $this->queryHorarios($entrada)->whereKey($idsExcluir)->with('jornada.turno')->get()
                ->filter(fn ($a) => Carbon::parse($a->jornada->fecha_jornada)->setTimeFromTimeString($a->jornada->turno->hora_inicio)->gte($corte))->modelKeys();
        }

        $fechaBase = Carbon::parse($entrada['f_fecha_inicio'])->startOfDay();
        $fechas = collect($entrada['f_dias_semana'])
            ->map(fn (string $dia) => $this->fechaParaDia($entrada, $fechaBase, $dia)->toDateString())
            ->all();
        $existentes = $this->queryHorarios($entrada)
            ->with('jornada.turno')
            ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->whereHas('jornada', fn ($query) => $query->whereBetween('fecha_jornada', [Carbon::parse(min($fechas))->subDay()->toDateString(), Carbon::parse(max($fechas))->addDay()->toDateString()]))
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
            foreach ($fechas as $fecha) {
                [$inicio, $fin] = $this->intervalo($fecha, $turno);
                if ($inicio->lt($finExistente) && $inicioExistente->lt($fin)) {
                    $dia = $this->diaDesdeFecha($entrada, $existente->jornada->fecha_jornada);

                    return "Ya existe un horario activo que se cruza el día {$dia} con el turno {$turnoExistente?->nombre}.";
                }
            }
        }

        return null;
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

    private function diaDesdeFecha(array $entrada, mixed $fecha): string
    {
        return self::DIAS[Carbon::parse($fecha)->dayOfWeekIso - 1];
    }
}
