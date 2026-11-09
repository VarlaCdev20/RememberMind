<?php

namespace App\Modulos\Reportes\Aplicacion\Consultas;

use App\Models\Cama;
use App\Models\User;
use App\Modulos\Admisiones\Aplicacion\Acciones\FormalizarAdmision;
use App\Modulos\Identidad\Aplicacion\Consultas\ConsultarDocumentacionPersonal;
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
        $avisos = app(ConsultarDocumentacionPersonal::class)->avisosGerencia($usuario);
        $grupos = collect($avisos)->countBy('tipo');
        $situacionAvisos = $grupos->map(fn (int $total, string $tipo) => [
            'label' => match ($tipo) {
                'vencido' => 'Plazo vencido',
                'proximo' => 'Plazo próximo',
                'recibido' => 'Recibidos para revisión',
                'correccion' => 'Corrección pendiente',
                'plazo_por_revisar' => 'Plazo por revisar',
            },
            'value' => $total,
        ])->values()->all();
        $totalAvisos = $avisos === null ? null : count($avisos);
        $vencidos = $avisos === null ? null : ($grupos['vencido'] ?? 0);
        $filas = [];
        foreach ([['personal', 'Personal activo', $personal, 'personas'], ['ocupacion', 'Camas ocupadas', $ocupadas, 'camas'],
            ['ocupacion', 'Camas disponibles', $disponibles, 'camas'], ['ocupacion', 'Camas no habilitadas', $noHabilitadas, 'camas'],
            ['alertas', 'Avisos documentales', $totalAvisos, 'avisos'],
            ['alertas', 'Avisos con plazo vencido', $vencidos, 'avisos']] as [$origen, $indicador, $valor, $unidad]) {
            if ($seccion !== 'resumen' && $seccion !== $origen) {
                continue;
            }
            $filas[] = compact('indicador', 'valor', 'unidad') + ['referencia' => 'Situación actual',
                'calidad' => $valor === null ? 'Consulta no disponible' : 'Consulta realizada'];
        }

        return ['seccion' => $seccion, 'consultado_en' => now()->toIso8601String(), 'referencia' => 'Situación actual',
            'ambito' => 'Institucional autorizado', 'personal' => $personal, 'camas' => $camas,
            'ocupadas' => $ocupadas, 'disponibles' => $disponibles, 'no_habilitadas' => $noHabilitadas,
            'avisos_documentales' => $totalAvisos, 'avisos_vencidos' => $vencidos,
            'camas_serie' => $situacionCamas, 'avisos_serie' => $situacionAvisos, 'filas' => $filas,
            'limites' => ['Los conteos actuales no representan un cierre histórico.',
                'La disponibilidad requiere cama y habitación habilitadas, sin ocupación vigente.',
                'Los avisos documentales se cuentan por solicitud y trabajador dentro del ámbito autorizado.',
                'Personal activo cuenta trabajadores, no cuentas ni roles.']];
    }
}
