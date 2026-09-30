<?php

namespace App\Backend\Modulos\Enfermeria\Servicios;

use App\Models\EjecucionCuidado;
use App\Models\OcupacionCama;
use App\Models\RegistroConductual;
use App\Models\User;
use Carbon\Carbon;

/** Lecturas compactas del contexto ya autorizado por MiTurnoService. */
final class DashboardComplementosService
{
    public function resumir(array $dashboard, User $usuario): array
    {
        $activo = ($dashboard['estado'] ?? null) !== 'SIN_JORNADA_ACTIVA'
            && ! empty($dashboard['jornada']['cod_jornada']);
        $residentes = collect($dashboard['residentes'] ?? [])->keyBy('cod_residente');
        $resultado = [
            'activo' => $activo,
            'tareas' => ['disponible' => $usuario->can('ejecuciones_cuidado.ver'), 'total' => 0, 'completadas' => 0, 'progreso' => null, 'items' => []],
            'ubicacion' => ['disponible' => $usuario->can('ocupaciones_cama.ver'), 'total' => $residentes->count(), 'sin_ubicacion' => 0, 'items' => []],
            'notas' => ['disponible' => $usuario->can('registros_conductuales.ver'), 'items' => []],
        ];
        if (! $activo || $residentes->isEmpty()) {
            return $resultado;
        }

        $ahora = Carbon::now();
        $codResidentes = $residentes->keys()->all();
        if ($resultado['tareas']['disponible']) {
            $jornadas = ($dashboard['modo'] ?? '') === 'EN_TURNO'
                ? [$dashboard['jornada']['cod_jornada']]
                : app(MiTurnoService::class)->resolverJornadasActivasSistema($ahora)->pluck('cod_jornada')->all();
            $tareas = EjecucionCuidado::query()
                ->whereIn('cod_residente', $codResidentes)
                ->whereIn('cod_jornada', $jornadas)
                ->when(($dashboard['modo'] ?? '') === 'EN_TURNO', fn ($q) => $q->where('cod_personal', $dashboard['usuario']['cod_personal'] ?? ''))
                ->when(($dashboard['modo'] ?? '') !== 'EN_TURNO', fn ($q) => $q->whereHas('personal.usuario.roles', fn ($roles) => $roles->where('name', 'ENFERMEROS')))
                ->with(['intervencion', 'personal'])
                ->get();
            $completadas = $tareas->filter(fn ($tarea) => in_array($tarea->estado, ['REALIZADA', 'COMPLETADA', 'FINALIZADA'], true)
                && $tarea->fecha_hora_ejecucion !== null)->count();
            $resultado['tareas']['total'] = $tareas->count();
            $resultado['tareas']['completadas'] = $completadas;
            $resultado['tareas']['progreso'] = $tareas->isNotEmpty() ? (int) round(100 * $completadas / $tareas->count()) : null;
            $orden = ['PENDIENTE' => 0, 'EN_PROCESO' => 1, 'REPROGRAMADA' => 2];
            $resultado['tareas']['items'] = $tareas->sort(fn ($a, $b) =>
                (($orden[$a->estado] ?? 3) <=> ($orden[$b->estado] ?? 3))
                ?: (($a->fecha_hora_programada?->timestamp ?? PHP_INT_MAX) <=> ($b->fecha_hora_programada?->timestamp ?? PHP_INT_MAX))
                ?: strcmp($a->cod_ejecucion, $b->cod_ejecucion)
            )->take(5)->map(fn ($tarea) => [
                'id' => $tarea->cod_ejecucion,
                'cod_residente' => $tarea->cod_residente,
                'title' => $tarea->intervencion?->nombre ?? 'Intervención sin título',
                'status' => $tarea->estado,
                'completed_verified' => in_array($tarea->estado, ['REALIZADA', 'COMPLETADA', 'FINALIZADA'], true) && $tarea->fecha_hora_ejecucion !== null,
                'patient' => $residentes[$tarea->cod_residente]['nombre_completo'] ?? $tarea->cod_residente,
                'time' => $tarea->fecha_hora_programada?->format('H:i'),
                'priority' => $tarea->intervencion?->prioridad,
                'responsible' => $tarea->personal ? trim($tarea->personal->nombres.' '.$tarea->personal->apellido_paterno) : null,
            ])->values()->all();
        }

        if ($resultado['ubicacion']['disponible']) {
            // Mismas condiciones de ocupación/cama activa que el KPI, sin plano inventado.
            $ocupaciones = OcupacionCama::query()->whereIn('cod_residente', $codResidentes)
                ->where('estado', 'ACTIVA')->whereNull('fecha_hora_liberacion')
                ->where('fecha_hora_asignacion', '<=', $ahora)
                ->whereHas('cama', fn ($q) => $q->where('estado', 'ACTIVA'))
                ->with('cama.habitacion')->get()->groupBy('cod_residente');
            foreach ($residentes as $codigo => $residente) {
                $asignaciones = $ocupaciones->get($codigo, collect());
                if ($asignaciones->count() !== 1) {
                    $resultado['ubicacion']['sin_ubicacion']++;
                    continue;
                }
                $cama = $asignaciones->sole()->cama;
                $habitacion = $cama->habitacion;
                $alertas = (int) ($residente['alertas_count'] ?? 0);
                $resultado['ubicacion']['items'][] = [
                    'cod_residente' => $codigo,
                    'label' => $habitacion?->codigo ?: ($habitacion?->nombre ?: 'Sin habitación disponible'),
                    'bed' => $cama->codigo ?: $cama->cod_cama,
                    'patient' => $residente['nombre_completo'] ?? $codigo,
                    'status' => $alertas > 0 ? 'alert' : 'assigned',
                    'alerts' => $alertas,
                ];
            }
            usort($resultado['ubicacion']['items'], fn ($a, $b) => strnatcmp($a['label'], $b['label']) ?: strnatcmp($a['bed'], $b['bed']));
            $resultado['ubicacion']['ubicados'] = count($resultado['ubicacion']['items']);
            $resultado['ubicacion']['items'] = array_slice($resultado['ubicacion']['items'], 0, 6);
        }

        if ($resultado['notas']['disponible']) {
            $resultado['notas']['items'] = RegistroConductual::query()
                ->whereIn('cod_residente', $codResidentes)
                ->whereBetween('fecha_hora', [$ahora->copy()->subHours(24), $ahora])
                ->whereIn('estado', ['VIGENTE', 'ACTIVO', 'ACTIVA'])
                ->where(fn ($q) => $q->whereRaw("TRIM(COALESCE(estado_animo, '')) <> ''")->orWhereRaw("TRIM(COALESCE(descripcion, '')) <> ''")->orWhere('cambio_conducta', true))
                ->orderByDesc('fecha_hora')->orderBy('cod_registro_conductual')->limit(3)->get()
                ->map(fn ($nota) => [
                    'id' => $nota->cod_registro_conductual,
                    'cod_residente' => $nota->cod_residente,
                    'patient' => $residentes[$nota->cod_residente]['nombre_completo'] ?? $nota->cod_residente,
                    'avatar' => $residentes[$nota->cod_residente]['foto'] ?? null,
                    'initials' => $this->iniciales($residentes[$nota->cod_residente]['nombre_completo'] ?? ''),
                    'time' => $nota->fecha_hora->format('d/m H:i'),
                    'datetime' => $nota->fecha_hora->toIso8601String(),
                    'text' => trim((string) $nota->descripcion),
                    'mood' => trim((string) $nota->estado_animo) !== '' ? str_replace('_', ' ', trim($nota->estado_animo)) : null,
                    'change' => (bool) $nota->cambio_conducta,
                ])->all();
        }

        return $resultado;
    }

    private function iniciales(string $nombre): string
    {
        return collect(preg_split('/\s+/u', trim($nombre), -1, PREG_SPLIT_NO_EMPTY))
            ->take(2)->map(fn ($parte) => mb_strtoupper(mb_substr($parte, 0, 1)))->implode('');
    }
}
