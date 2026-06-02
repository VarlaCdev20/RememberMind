<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActividadAdulto;
use App\Models\ActividadParticipante;
use App\Models\TipoActividadAdulto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActividadCalendarioController extends Controller
{
    // ── Colores institucionales por estado ────────────────────────────────────

    private function colorPorEstado(string $estado): array
    {
        return match (strtoupper(trim($estado))) {
            'BORRADOR'                              => ['bg' => '#9CA3AF', 'border' => '#6B7280'],
            'PROGRAMADA', 'PENDIENTE'               => ['bg' => '#4A90D9', 'border' => '#2A6096'],
            'EN_CURSO'                              => ['bg' => '#8DA280', 'border' => '#5A7A56'],
            'REPROGRAMADA'                          => ['bg' => '#D9A05B', 'border' => '#BC6C25'],
            'REALIZADA', 'COMPLETADA', 'FINALIZADA' => ['bg' => '#2A9D8F', 'border' => '#1F7A6F'],
            'EVALUADA'                              => ['bg' => '#5A8A70', 'border' => '#3D6B52'],
            'CANCELADA', 'ANULADA'                  => ['bg' => '#E27D60', 'border' => '#C85C3E'],
            default                                 => ['bg' => '#BC6C25', 'border' => '#9A5020'],
        };
    }

    // ── Vista principal del calendario ────────────────────────────────────────

    public function index()
    {
        return view('admin.actividades.calendario', [
            'tipos' => TipoActividadAdulto::orderBy('tipo')->get(),
        ]);
    }

    // ── Endpoint JSON: eventos para FullCalendar ──────────────────────────────

    public function eventos(Request $request): JsonResponse
    {
        $query = ActividadAdulto::with(['tipoActividad'])
            ->withCount(['participantesActivos as total_participantes'])
            ->whereNull('deleted_at');

        // Rango de fechas de FullCalendar
        if ($request->filled('start')) {
            $query->where('fecha', '>=', substr($request->start, 0, 10));
        }
        if ($request->filled('end')) {
            $query->where('fecha', '<=', substr($request->end, 0, 10));
        }

        // Filtros opcionales desde el panel
        if ($request->filled('tipo')) {
            $query->where('cod_tipo_act', (int) $request->tipo);
        }
        if ($request->filled('estado')) {
            $query->where('estado', strtoupper($request->estado));
        }
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(fn($q) =>
                $q->where('nombre', 'ilike', "%{$buscar}%")
                  ->orWhereHas('tipoActividad', fn($t) =>
                      $t->where('tipo', 'ilike', "%{$buscar}%")
                  )
                  ->orWhere('lugar', 'ilike', "%{$buscar}%")
            );
        }

        $actividades = $query->get();

        $eventos = $actividades->map(function (ActividadAdulto $act) {
            $colores = $this->colorPorEstado($act->estado ?? '');

            // Color: prioridad actividad > tipo > defecto
            if ($act->color) {
                $colores['bg']     = $act->color;
                $colores['border'] = $act->color;
            }

            // Título del evento
            $titulo = $act->nombre
                ?? optional($act->tipoActividad)->tipo
                ?? 'Actividad';

            // Hora inicio (combinar fecha + hora)
            $start = $act->fecha->format('Y-m-d');
            if ($act->hora) {
                $start .= 'T' . substr($act->hora, 0, 5);
            }

            // Hora fin
            $end = null;
            if ($act->hora_fin) {
                $end = $act->fecha->format('Y-m-d') . 'T' . substr($act->hora_fin, 0, 5);
            } elseif ($act->hora) {
                // Duración estimada del tipo, o 60 min por defecto
                $minutos = optional($act->tipoActividad)->duracion_estimada_minutos ?? 60;
                $horaInicio = \Carbon\Carbon::parse($act->fecha->format('Y-m-d') . ' ' . $act->hora);
                $end = $horaInicio->addMinutes($minutos)->format('Y-m-d\TH:i');
            }

            return [
                'id'              => $act->cod_act_adul,
                'title'           => $titulo,
                'start'           => $start,
                'end'             => $end,
                'backgroundColor' => $colores['bg'],
                'borderColor'     => $colores['border'],
                'textColor'       => '#ffffff',
                'extendedProps'   => [
                    'estado'            => $act->estado,
                    'tipo'              => optional($act->tipoActividad)->tipo,
                    'categoria'         => optional($act->tipoActividad)->categoria,
                    'lugar'             => $act->lugar,
                    'responsable'       => $act->responsable_id,
                    'participantes'     => $act->total_participantes ?? 0,
                    'nivel_cumplimiento'=> $act->nivel_cumplimiento,
                    'resultado_general' => $act->resultado_general,
                    'evaluacion_final'  => $act->evaluacion_final,
                ],
            ];
        });

        return response()->json($eventos);
    }

    // ── Endpoint JSON: detalle completo de una actividad ──────────────────────

    public function detalleJson(ActividadAdulto $actividad): JsonResponse
    {
        $actividad->load(['tipoActividad', 'adultoMayor']);

        // Resumen de asistencia desde actividad_participantes
        $asistencia = DB::table('actividad_participantes')
            ->where('cod_act_adul', $actividad->cod_act_adul)
            ->whereNull('deleted_at')
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN estado_asistencia = 'ASISTIO'     THEN 1 ELSE 0 END) as asistieron,
                SUM(CASE WHEN estado_asistencia = 'FALTO'       THEN 1 ELSE 0 END) as faltaron,
                SUM(CASE WHEN estado_asistencia = 'JUSTIFICADO' THEN 1 ELSE 0 END) as justificados,
                SUM(CASE WHEN estado_asistencia = 'INSCRITO'    THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN requiere_seguimiento = true        THEN 1 ELSE 0 END) as requiere_seguimiento
            ")
            ->first();

        return response()->json([
            'id'                => $actividad->cod_act_adul,
            'nombre'            => $actividad->nombre,
            'tipo'              => optional($actividad->tipoActividad)->tipo,
            'categoria'         => optional($actividad->tipoActividad)->categoria,
            'fecha'             => $actividad->fecha?->format('d/m/Y'),
            'fecha_iso'         => $actividad->fecha?->format('Y-m-d'),
            'hora'              => $actividad->hora ? substr($actividad->hora, 0, 5) : null,
            'hora_fin'          => $actividad->hora_fin ? substr($actividad->hora_fin, 0, 5) : null,
            'lugar'             => $actividad->lugar,
            'responsable'       => $actividad->responsable_id,
            'objetivo'          => $actividad->objetivo,
            'descripcion'       => $actividad->descripcion,
            'materiales'        => $actividad->materiales,
            'estado'            => $actividad->estado,
            'color'             => $actividad->color,
            'cupo_maximo'       => $actividad->cupo_maximo,
            'participantes'     => $asistencia->total ?? 0,
            'asistieron'        => $asistencia->asistieron ?? 0,
            'faltaron'          => $asistencia->faltaron ?? 0,
            'justificados'      => $asistencia->justificados ?? 0,
            'pendientes'        => $asistencia->pendientes ?? 0,
            'requiere_seguimiento' => $asistencia->requiere_seguimiento ?? 0,
            'resultado_general' => $actividad->resultado_general,
            'evaluacion_final'  => $actividad->evaluacion_final,
            'nivel_cumplimiento'=> $actividad->nivel_cumplimiento,
            'incidencias'       => $actividad->incidencias,
            'recomendaciones'   => $actividad->recomendaciones,
            'obs'               => $actividad->obs,
            // URLs de acciones rápidas
            'url_editar'        => route('admin.actividades.index'),
            'url_participantes' => route('admin.actividades.participacion'),
            'url_asistencia'    => route('admin.actividades.asistencia'),
        ]);
    }

    // ── Endpoint PATCH: reprogramar actividad (drag & drop / resize) ──────────

    public function reprogramar(Request $request, ActividadAdulto $actividad): JsonResponse
    {
        // Verificar permiso
        if (! auth()->user()->can('actividades.editar')) {
            return response()->json(['message' => 'Sin permiso para reprogramar actividades.'], 403);
        }

        // Validar
        $validated = $request->validate([
            'fecha'    => 'required|date',
            'hora'     => 'required|date_format:H:i',
            'hora_fin' => 'nullable|date_format:H:i|after:hora',
        ], [
            'fecha.required'    => 'La fecha es obligatoria.',
            'hora.required'     => 'La hora de inicio es obligatoria.',
            'hora_fin.after'    => 'La hora de fin debe ser posterior a la hora de inicio.',
        ]);

        // Verificar si puede reprogramarse
        $estadoUpper = strtoupper($actividad->estado ?? '');
        if (in_array($estadoUpper, ['EVALUADA', 'CANCELADA', 'ANULADA'])) {
            return response()->json([
                'message' => "No se puede reprogramar una actividad con estado {$actividad->estado}.",
            ], 422);
        }

        // Guardar datos anteriores para Activitylog
        $fechaAnterior = $actividad->fecha?->format('d/m/Y');
        $horaAnterior  = $actividad->hora ? substr($actividad->hora, 0, 5) : '—';

        // Determinar nuevo estado
        $nuevoEstado = in_array($estadoUpper, ['PROGRAMADA', 'EN_CURSO', 'PENDIENTE'])
            ? 'REPROGRAMADA'
            : $actividad->estado;

        $formatHora = fn(string $h) => strlen($h) === 5 ? $h . ':00' : $h;

        $actividad->update([
            'fecha'    => $validated['fecha'],
            'hora'     => $formatHora($validated['hora']),
            'hora_fin' => isset($validated['hora_fin']) && $validated['hora_fin']
                ? $formatHora($validated['hora_fin'])
                : null,
            'estado'   => $nuevoEstado,
        ]);

        activity('Actividades')
            ->performedOn($actividad)
            ->log(
                "Actividad reprogramada de {$fechaAnterior} {$horaAnterior} " .
                "a {$validated['fecha']} {$validated['hora']}."
            );

        return response()->json([
            'success' => true,
            'message' => 'Actividad reprogramada correctamente.',
            'estado'  => $nuevoEstado,
        ]);
    }
}
