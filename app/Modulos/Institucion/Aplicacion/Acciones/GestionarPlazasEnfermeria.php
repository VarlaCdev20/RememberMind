<?php

namespace App\Modulos\Institucion\Aplicacion\Acciones;

use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class GestionarPlazasEnfermeria
{
    public function guardarAsignacionPlaza(User $actor, array $entrada): void
    {
        abort_unless($actor->estado === 'ACTIVO' && $actor->can('turnos.asignar'), 403);
        $plaza = $entrada['plazaSeleccionada'];
        $codUsuario = $entrada['enfermeroSeleccionado'] ?: null;
        $tipo = $entrada['tipoAsignacion'];
        $fecha = $entrada['fechaSeleccionadaPlaza'];
        $motivo = $entrada['motivoAsignacion'] ?: null;
        if ($codUsuario) {
            $user = User::find($codUsuario);
            abort_unless($user && $user->hasRole('ENFERMEROS') && $user->estado === 'ACTIVO', 422, 'El usuario seleccionado debe tener el rol de ENFERMEROS y estar ACTIVO.');
            $otraPlaza = AsignacionPersonal::where('cod_personal', $user->personal?->cod_personal)->where('estado', 'ACTIVA')->where('funcion', 'like', 'PLAZA:%')->where('funcion', '!=', 'PLAZA:'.$plaza)->whereHas('jornada', fn ($q) => $q->whereDate('fecha_jornada', $fecha))->first();
            abort_if($otraPlaza, 422, 'El enfermero ya tiene otra asignación para esa fecha.');
        }
        $personal = null;
        $areaId = null;
        if ($tipo !== 'DESCANSO' && $codUsuario) {
            $personal = User::findOrFail($codUsuario)->personal;
            abort_unless($personal, 422, 'El usuario no tiene ficha de personal V2.');
            $areaId = $this->resolverAreaAsignacionEnfermeria($actor, $entrada, $personal);
        }

        DB::transaction(function () use ($plaza, $fecha, $tipo, $personal, $areaId, $motivo, $entrada): void {
            AsignacionPersonal::where('funcion', 'PLAZA:'.$plaza)->where('estado', 'ACTIVA')
                ->whereHas('jornada', fn ($q) => $q->whereDate('fecha_jornada', $fecha))
                ->update(['estado' => 'ANULADA']);

            if ($tipo === 'DESCANSO' || ! $personal || ! $areaId) {
                return;
            }

            $turnoCodigo = $entrada['turnoCodigo'];
            $turnoId = match ($turnoCodigo) {
                'MANANA' => 'TUR_0001', 'TARDE' => 'TUR_0002', 'NOCHE' => 'TUR_0003', default => 'TUR_0004',
            };
            $jornada = Jornada::firstOrCreate(
                ['cod_turno' => $turnoId, 'fecha_jornada' => $fecha],
                ['cod_jornada' => 'JOR_'.Str::upper(Str::random(10)), 'estado' => 'ACTIVA']
            );

            AsignacionPersonal::create([
                'cod_jornada' => $jornada->cod_jornada, 'cod_personal' => $personal->cod_personal, 'cod_area' => $areaId,
                'funcion' => 'PLAZA:'.$plaza, 'tipo_asignacion' => $tipo, 'fecha_asignacion' => now(),
                'estado' => 'ACTIVA', 'observacion' => $motivo,
            ]);
        });

    }

    public function desvincularPlaza(User $actor, string $plaza, string $fecha): void
    {
        abort_unless($actor->estado === 'ACTIVO' && $actor->can('turnos.asignar'), 403);
        $asignaciones = AsignacionPersonal::where('funcion', 'PLAZA:'.$plaza)->where('estado', 'ACTIVA')
            ->whereHas('jornada', fn ($q) => $q->whereDate('fecha_jornada', $fecha))->get();
        foreach ($asignaciones as $asignacion) {
            $asignacion->update(['estado' => 'ANULADA', 'observacion' => trim(($asignacion->observacion ?? '').' | Desvinculada manualmente')]);
        }

    }

    private function resolverAreaAsignacionEnfermeria(User $actor, array $entrada, Personal $personal): string
    {
        $codigosAsignados = $personal->asignaciones()
            ->where('estado', 'ACTIVA')
            ->distinct()
            ->pluck('cod_area')
            ->filter()
            ->values();

        if ($codigosAsignados->count() === 1) {
            return (string) $codigosAsignados->sole();
        }

        $areasEnfermeria = Area::query()
            ->whereIn('estado', ['ACTIVA', 'ACTIVO'])
            ->get(['cod_area', 'nombre'])
            ->filter(fn (Area $area): bool => str_contains(
                Str::upper(Str::ascii((string) $area->nombre)),
                'ENFERMER'
            ))
            ->values();

        abort_unless(
            $areasEnfermeria->count() === 1,
            422,
            'La asignación requiere un área de Enfermería única y explícita; revise la configuración institucional.'
        );

        return (string) $areasEnfermeria->sole()->cod_area;
    }
}
