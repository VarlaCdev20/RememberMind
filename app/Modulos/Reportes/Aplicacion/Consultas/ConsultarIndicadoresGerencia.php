<?php

namespace App\Modulos\Reportes\Aplicacion\Consultas;

use App\Models\Cama;
use App\Models\User;
use App\Modulos\Admisiones\Aplicacion\Acciones\FormalizarAdmision;
use App\Modulos\Alertas\Aplicacion\Consultas\BandejaAlertas;
use App\Modulos\Alertas\Aplicacion\Formato\LenguajeAlerta;
use Illuminate\Support\Facades\DB;

/** Proyección agregada compartida por Inicio y Reportes; nunca contiene expedientes. */
final class ConsultarIndicadoresGerencia
{
    public function autorizarReporte(User $usuario): void
    {
        abort_unless($usuario->fresh()?->estado === 'ACTIVO' && ! $usuario->hasRole('FAMILIAR')
            && $usuario->can('reportes.institucional')
            && $usuario->can('personal_institucional.ver')
            && $usuario->can('habitaciones.ver') && $usuario->can('alertas.ver'), 403);
    }

    public function consultar(User $usuario, string $seccion = 'resumen'): array
    {
        abort_unless($usuario->fresh()?->estado === 'ACTIVO' && ! $usuario->hasRole('FAMILIAR'), 403);
        abort_unless(in_array($seccion, ['resumen', 'personal', 'ocupacion', 'alertas'], true), 422);
        $personal = $usuario->can('personal_institucional.ver')
            ? DB::table('personal')->where('estado', 'ACTIVO')->count() : null;
        $camas = $ocupadas = $disponibles = $noHabilitadas = null;
        $situacionCamas = [];
        if ($usuario->can('habitaciones.ver')) {
            $camas = Cama::query()->count();
            $ocupadas = Cama::query()->whereHas('ocupaciones', fn ($q) => $q->whereIn('estado', FormalizarAdmision::ESTADOS_OCUPACION_ACTIVA))->count();
            $disponibles = FormalizarAdmision::filtrarCamasDisponibles(Cama::query())->count();
            $noHabilitadas = $camas - $ocupadas - $disponibles;
            $situacionCamas = [['label' => 'Ocupadas', 'value' => $ocupadas],
                ['label' => 'Disponibles', 'value' => $disponibles], ['label' => 'No habilitadas', 'value' => $noHabilitadas]];
        }
        $activas = $sinResponsable = $limiteSuperado = null;
        $situacionAlertas = [];
        if ($usuario->can('alertas.ver')) {
            $base = app(BandejaAlertas::class)->pendientes($usuario);
            $grupos = (clone $base)->select('estado')->selectRaw('COUNT(*) as total')->groupBy('estado')->orderBy('estado')->get();
            $activas = (int) $grupos->sum('total');
            $situacionAlertas = $grupos->map(fn ($grupo) => [
                'label' => match ($grupo->estado) {
                    'ABIERTA' => 'Abiertas', 'PENDIENTE' => 'Pendientes',
                    default => LenguajeAlerta::estado($grupo->estado),
                }, 'value' => (int) $grupo->total,
            ])->all();
            $sinResponsable = (clone $base)->whereNull('cod_personal_responsable')->count();
            $limiteSuperado = (clone $base)->whereNotNull('fecha_hora_limite')->where('fecha_hora_limite', '<', now())->count();
        }
        $filas = [];
        foreach ([['personal', 'Personal activo', $personal, 'personas'], ['ocupacion', 'Camas ocupadas', $ocupadas, 'camas'],
            ['ocupacion', 'Camas disponibles', $disponibles, 'camas'], ['ocupacion', 'Camas no habilitadas', $noHabilitadas, 'camas'],
            ['alertas', 'Alertas activas', $activas, 'alertas']] as [$origen, $indicador, $valor, $unidad]) {
            if ($seccion !== 'resumen' && $seccion !== $origen) {
                continue;
            }
            $filas[] = compact('indicador', 'valor', 'unidad') + ['referencia' => 'Situación actual',
                'calidad' => $valor === null ? 'Fuente no autorizada' : 'Consulta realizada'];
        }

        return ['seccion' => $seccion, 'consultado_en' => now()->toIso8601String(), 'referencia' => 'Situación actual',
            'ambito' => 'Institucional autorizado', 'personal' => $personal, 'camas' => $camas,
            'ocupadas' => $ocupadas, 'disponibles' => $disponibles, 'no_habilitadas' => $noHabilitadas,
            'alertas' => $activas, 'sin_responsable' => $sinResponsable, 'limite_superado' => $limiteSuperado,
            'camas_serie' => $situacionCamas, 'alertas_serie' => $situacionAlertas, 'filas' => $filas,
            'limites' => ['Los conteos actuales no representan un cierre histórico.',
                'La disponibilidad requiere cama y habitación habilitadas, sin ocupación vigente.',
                'Los estados de alertas son categorías excluyentes; no constituyen diagnósticos.',
                'Personal activo cuenta trabajadores, no cuentas ni roles.']];
    }
}
