<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Residente;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FichaPacienteReporteController extends Controller
{
    public function pdf($adulto_id)
    {
        $adultoId = is_object($adulto_id) ? $adulto_id->cod_residente : $adulto_id;
        $adultoMayor = Residente::with([
            'cama.habitacion',
            'asignacionTurnoActiva.turno',
            'planCuidadoActivo',
            'signosVitales' => fn($q) => $q->orderByDesc('fecha_hora')->take(7),
            'administracionesMedicacion' => fn($q) => $q->orderByDesc('fecha_hora_programada')->take(5),
            'alertasAbiertas' => fn($q) => $q->orderByDesc('fecha_hora'),
            'pasesTurno' => fn($q) => $q->orderByDesc('fecha_hora')->take(3)
        ])->findOrFail($adultoId);

        if (!auth()->user()->hasAnyRole(['SUPERADMINISTRADOR', 'ADMINISTRADOR', 'ENFERMEROS', 'MEDICO GENERAL/GERIATRA'])) {
            abort(403, 'No tienes permiso para ver la ficha operativa de este residente.');
        }

        $tieneAtenciones = $adultoMayor->atenciones()->exists();
        $timeline = [
            'PREADMISIÓN' => true,
            'VALORACIÓN ENFERMERÍA' => $tieneAtenciones,
            'VALORACIÓN MÉDICA' => $tieneAtenciones,
            'ADMITIDO' => in_array($adultoMayor->estado, ['ACTIVO', 'EN_OBSERVACION']),
            'ASIGNADO' => $adultoMayor->cod_habitacion !== null || $adultoMayor->habitacion !== null,
            'PLAN ACTIVO' => $adultoMayor->planCuidadoActivo !== null,
            'SEGUIMIENTO ACTIVO' => $adultoMayor->signosVitales->isNotEmpty(),
        ];

        $habitacionActual = $adultoMayor->cod_habitacion
            ? \App\Models\Habitacion::find($adultoMayor->cod_habitacion)
            : $adultoMayor->habitacion;
        $camaActual = $adultoMayor->cod_cama
            ? \App\Models\Cama::find($adultoMayor->cod_cama)
            : $adultoMayor->cama;

        // Signos vitales para gráficos
        $signos = $adultoMayor->signosVitales->reverse();
        $labelsSignos = $signos->map(fn($s) => Carbon::parse($s->fecha_hora)->format('d/m H:i'))->values();
        $dataPresionSis = $signos->map(fn($s) => $s->presion_sistolica ?? 0)->values();
        $dataPresionDia = $signos->map(fn($s) => $s->presion_diastolica ?? 0)->values();
        $dataSaturacion = $signos->map(fn($s) => $s->saturacion_oxigeno ?? 0)->values();

        $ejecuciones = DB::table('ejecuciones_cuidado')->where('cod_residente', $adultoMayor->cod_residente)->get();
        $tareasRealizadas = $ejecuciones->where('estado', 'REALIZADA')->count();
        $tareasPendientes = $ejecuciones->where('estado', 'PENDIENTE')->count();
        $tareasOmitidas = $ejecuciones->where('estado', 'OMITIDA')->count();

        $alertas = $adultoMayor->alertasAbiertas;
        $alertasLabels = $alertas->pluck('tipo')->unique()->values();
        $alertasData = $alertasLabels->map(function($tipo) use ($alertas) {
            return $alertas->where('tipo', $tipo)->count();
        });

        $pdf = Pdf::loadView('pages.enfermeria.reportes.ficha-paciente-pdf', compact(
            'adultoMayor', 'habitacionActual', 'camaActual',
            'timeline',
            'labelsSignos', 'dataPresionSis', 'dataPresionDia', 'dataSaturacion',
            'tareasRealizadas', 'tareasPendientes', 'tareasOmitidas',
            'alertasLabels', 'alertasData'
        ));

        return $pdf->stream('Ficha_Operativa_'.$adultoMayor->nombres.'_'.$adultoMayor->apellido_paterno.'.pdf');
    }
}