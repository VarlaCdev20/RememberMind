<?php

namespace App\Modulos\Institucion\Aplicacion\Acciones;

use App\Models\AsignacionPersonal;
use App\Models\Jornada;
use App\Models\Turno;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class GestionarHorariosPersonal
{
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
        $usuario = User::with(['roles', 'personal'])->whereKey($entrada['usuarioId'])->firstOrFail();
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
        $solapamiento = $this->verificarSolapamiento($entrada, $turno);
        if ($solapamiento) {
            throw ValidationException::withMessages(['f_dias_semana' => $solapamiento]);
        }
        $fechaInicio = Carbon::parse($entrada['f_fecha_inicio'])->startOfDay();
        $isEdit = $entrada['modoEdicion'];
        DB::beginTransaction();

        try {
            $personal = $usuario->personal;
            if (! $personal) {
                throw ValidationException::withMessages(['f_cod_area' => 'El usuario no tiene un registro de personal V2 asociado.']);
            }

            if ($isEdit && $entrada['codAsignacionEditar']) {
                $this->actualizarEstadoAsignacionGrupo($entrada,
                    $entrada['codAsignacionEditar'],
                    'INACTIVO',
                    $fechaInicio->copy()->subSecond()
                );
            }

            $grupo = 'GRP_'.Str::upper(Str::random(10));
            foreach ($entrada['f_dias_semana'] as $dia) {
                $fechaJornada = $this->fechaParaDia($entrada, $fechaInicio, $dia);
                $jornada = Jornada::query()->firstOrCreate(
                    ['cod_turno' => $turno->cod_turno, 'fecha_jornada' => $fechaJornada->toDateString()],
                    ['cod_jornada' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('jornadas'), 'estado' => 'ABIERTA']
                );
                AsignacionPersonal::query()->create([
                    'cod_asignacion_personal' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('asignaciones_personal'),
                    'cod_jornada' => $jornada->cod_jornada,
                    'cod_personal' => $personal->cod_personal,
                    'cod_area' => $entrada['f_cod_area'],
                    'funcion' => $personal->profesion,
                    'tipo_asignacion' => $entrada['f_tipo_asignacion'],
                    'fecha_asignacion' => now(),
                    'estado' => 'ACTIVO',
                    'observacion' => $this->observacionesSerializadas($entrada, $grupo),
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function confirmarFinalizar(array $entrada): void
    {
        $fechaFin = Carbon::parse($entrada['f_fecha_fin_finalizar'])->endOfDay();
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
        $ids = collect(collect($entrada['asignaciones'])->firstWhere('cod_asignacion', $codAsignacion)['codigos_registro'] ?? [])->all();

        if ($ids === []) {
            return;
        }

        $this->queryHorarios($entrada)
            ->whereIn($this->primaryKeyHorario($entrada), $ids)
            ->update(['estado' => $estado]);
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

    private function observacionesSerializadas(array $entrada, string $grupo): string
    {
        $payload = [
            'grupo' => $grupo,
            'tipo_asignacion' => $entrada['f_tipo_asignacion'],
            'observaciones' => trim($entrada['f_observaciones']),
        ];

        return json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    public function actualizarGrupo(array $entrada, string $codAsignacion, string $estado, Carbon $fecha): void
    {
        $this->actualizarEstadoAsignacionGrupo($entrada, $codAsignacion, $estado, $fecha);
    }

    private function verificarSolapamiento(array $entrada, Turno $turno): ?string
    {
        $idsExcluir = $entrada['modoEdicion'] && $entrada['codAsignacionEditar']
            ? collect(collect($entrada['asignaciones'])->firstWhere('cod_asignacion', $entrada['codAsignacionEditar'])['codigos_registro'] ?? [])->all()
            : [];

        $fechaBase = Carbon::parse($entrada['f_fecha_inicio'])->startOfDay();
        $fechas = collect($entrada['f_dias_semana'])
            ->map(fn (string $dia) => $this->fechaParaDia($entrada, $fechaBase, $dia)->toDateString())
            ->all();
        $existentes = $this->queryHorarios($entrada)
            ->with('jornada.turno')
            ->where('estado', 'ACTIVO')
            ->whereHas('jornada', fn ($query) => $query->whereIn('fecha_jornada', $fechas))
            ->when($idsExcluir !== [], function ($query) use ($idsExcluir, $entrada) {
                $query->whereNotIn($this->primaryKeyHorario($entrada), $idsExcluir);
            })
            ->get();

        foreach ($existentes as $existente) {
            $turnoExistente = $existente->jornada?->turno;
            if ($this->rangosHoraSeCruzan($entrada,
                (string) $turno->hora_inicio,
                (string) $turno->hora_fin,
                (string) $turnoExistente?->hora_inicio,
                (string) $turnoExistente?->hora_cierre
            )) {
                $dia = $this->diaDesdeFecha($entrada, $existente->jornada->fecha_jornada);

                return "Ya existe un horario activo que se cruza el día {$dia} con el turno {$turnoExistente?->nombre}.";
            }
        }

        return null;
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
