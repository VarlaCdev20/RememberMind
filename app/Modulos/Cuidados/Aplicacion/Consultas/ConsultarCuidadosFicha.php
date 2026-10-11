<?php

namespace App\Modulos\Cuidados\Aplicacion\Consultas;

use App\Compartido\Aplicacion\Contratos\ContextoEjecucion;
use App\Models\Residente;
use App\Models\User;

/** Conserva el alcance, límites y estados de las lecturas anteriores de la ficha. */
final class ConsultarCuidadosFicha
{
    public function __construct(private readonly ContextoEjecucion $contexto) {}

    public function consultar(Residente $residente, string $tab = 'resumen', ?User $actor = null): array
    {
        $actor ??= $this->contexto->usuario();
        $actor = $actor ? User::find($actor->getKey()) : null;
        abort_unless($actor?->estado === 'ACTIVO', 403);
        if (! $actor->hasAnyRole(['ADMINISTRADOR', 'GERENTE']) || ! $actor->can('residentes.ver')) {
            $turno = app(TurnoEnfermeriaService::class);
            if ($actor->hasRole('ENFERMEROS')) {
                $turno->autorizarConsultaResidente($residente, $actor);
            } else {
                $turno->autorizarAccionPaciente($residente, $actor);
            }
        }
        if (in_array($tab, ['signos', 'medicacion', 'seguimiento', 'eventos', 'alertas', 'estudios', 'resultados', 'historial', 'documentos'], true)) {
            return [];
        }
        if (! in_array($tab, ['cuidado', 'cuidados'], true)) {
            $residente->loadMissing('planCuidadoActivo');
            $ultSeg = $residente->pasesTurno()->with('personalSaliente.usuario')->first();
            $totalTareasHoy = $residente->ejecucionesCuidado()->whereDate('fecha_hora_programada', today())->count();
            $tareasCompletadasHoy = $residente->ejecucionesCuidado()->whereDate('fecha_hora_programada', today())
                ->whereIn('estado', ['REALIZADA', 'EJECUTADA', 'COMPLETADA'])->count();

            return compact('ultSeg', 'totalTareasHoy', 'tareasCompletadasHoy');
        }
        $residente->loadMissing('planCuidadoActivo');
        $planActivo = $residente->planCuidadoActivo;
        $intervenciones = $planActivo
            ? $planActivo->intervenciones()->whereIn('estado', ['ACTIVA', 'ACTIVO'])
                ->with(['ejecuciones' => fn ($q) => $q->latest('fecha_hora_programada')])->get()
            : collect();
        $ejecuciones = $residente->registrosCuidados()->with(['intervencion', 'personal'])
            ->latest('fecha_hora_programada')->take(30)->get();
        $ejecucionesHoy = $ejecuciones->filter(fn ($e) => $e->fecha_hora_programada?->isToday());
        $realizadosHoy = $ejecucionesHoy->where('estado', 'REALIZADA')->count();
        $incidenciasHoy = $ejecucionesHoy->whereIn('estado', ['OMITIDA', 'INCIDENCIA'])->count();
        $pendientesHoy = $ejecucionesHoy->whereIn('estado', ['PENDIENTE', 'EN_PROCESO'])->count();
        $totalHoy = $ejecucionesHoy->count();
        $cumplimientoPct = $totalHoy > 0 ? (int) round(($realizadosHoy / $totalHoy) * 100) : 0;

        return compact('planActivo', 'intervenciones', 'ejecuciones', 'ejecucionesHoy', 'realizadosHoy',
            'incidenciasHoy', 'pendientesHoy', 'totalHoy', 'cumplimientoPct');
    }
}
