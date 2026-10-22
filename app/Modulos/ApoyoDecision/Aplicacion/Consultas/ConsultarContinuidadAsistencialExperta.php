<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\Diagnostico;
use App\Models\EjecucionCuidado;
use App\Models\EvaluacionExperta;
use App\Models\PlanCuidado;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\Cuidados\Aplicacion\Consultas\OcurrenciasCuidado;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;

/** Lectura del panel lateral: cada recurso exige su permiso antes de consultar relaciones. */
final class ConsultarContinuidadAsistencialExperta
{
    public function consultar(User $usuario, Residente $residente, Carbon $fecha): array
    {
        $usuario = User::query()->findOrFail($usuario->getKey());
        $residente = Residente::query()->findOrFail($residente->getKey());
        Gate::forUser($usuario)->authorize('consultarResultados', [EvaluacionExperta::class, $residente]);
        $puedeVerDiagnosticos = $usuario->checkPermissionTo('diagnosticos.ver', 'web');
        $puedeVerCuidados = collect(['planes_cuidado.ver', 'intervenciones_cuidado.ver', 'programaciones_cuidado.ver', 'ejecuciones_cuidado.ver'])
            ->every(fn ($permiso) => $usuario->checkPermissionTo($permiso, 'web'));
        $diagnosticosMedicos = $puedeVerDiagnosticos ? Diagnostico::with('personal')->where('cod_residente', $residente->getKey())
            ->whereIn('estado', ['ACTIVO', 'VIGENTE', 'CONFIRMADO'])->latest('fecha_hora')->get() : collect();
        $cuidadosPendientesHoy = collect();
        $otrosCuidadosVigentes = collect();
        if ($puedeVerCuidados) {
            $planes = PlanCuidado::where('cod_residente', $residente->getKey())->whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE'])
                ->with(['intervenciones' => fn ($q) => $q->whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE'])->with('programaciones')])->get();
            $ejecuciones = EjecucionCuidado::where('cod_residente', $residente->getKey())->whereDate('fecha_hora_programada', $fecha->toDateString())->get();
            $turno = app(TurnoEnfermeriaService::class)->obtenerTurnoActivo($usuario);
            $ocurrencias = app(OcurrenciasCuidado::class);
            foreach ($planes as $plan) {
                if (($plan->fecha_hora_apertura && Carbon::parse($plan->fecha_hora_apertura)->gt($fecha->copy()->endOfDay()))
                    || ($plan->fecha_hora_cierre && Carbon::parse($plan->fecha_hora_cierre)->lt($fecha->copy()->startOfDay()))) {
                    continue;
                }
                foreach ($plan->intervenciones as $intervencion) {
                    $programadaHoy = false;
                    foreach ($intervencion->programaciones as $programacion) {
                        $momento = $ocurrencias->momento($programacion, $fecha, $turno);
                        if ($momento === null || ($plan->fecha_hora_apertura && $momento->lt($plan->fecha_hora_apertura))
                            || ($plan->fecha_hora_cierre && $momento->gt($plan->fecha_hora_cierre))) {
                            continue;
                        }
                        $programadaHoy = true;
                        $ejecucion = $ejecuciones->first(fn ($e) => $ocurrencias->corresponde($e, $residente->getKey(), $intervencion->getKey(), $momento));
                        $estado = $ocurrencias->estado($ejecucion);
                        $item = ['intervencion' => $intervencion, 'plan' => $plan, 'hora' => $momento->format('H:i'),
                            'clave' => hash('sha256', $intervencion->getKey().'|'.$momento->toIso8601String()),
                            'programacion' => $programacion, 'ejecucion' => $ejecucion,
                            'fecha_hora_programada' => $momento->toIso8601String(), 'estado' => $estado];
                        if ($estado === 'PENDIENTE') {
                            $cuidadosPendientesHoy->push($item);
                        } else {
                            $otrosCuidadosVigentes->push($item);
                        }
                    }
                    if (! $programadaHoy) {
                        $otrosCuidadosVigentes->push(['intervencion' => $intervencion, 'plan' => $plan,
                            'clave' => hash('sha256', $intervencion->getKey().'|sin-ocurrencia'), 'programacion' => null, 'ejecucion' => null,
                            'hora' => null, 'estado' => 'SIN_OCURRENCIA_HOY']);
                    }
                }
            }
        }

        return compact('puedeVerDiagnosticos', 'puedeVerCuidados', 'diagnosticosMedicos', 'cuidadosPendientesHoy', 'otrosCuidadosVigentes');
    }
}
