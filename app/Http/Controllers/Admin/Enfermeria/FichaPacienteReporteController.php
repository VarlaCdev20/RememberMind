<?php

namespace App\Http\Controllers\Admin\Enfermeria;

use App\Http\Controllers\Controller;
use App\Models\AdultoMayor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FichaPacienteReporteController extends Controller
{
    public function pdf($adulto_id)
    {
        $adultoMayor = AdultoMayor::with([
            'habitacion', 'cama',
            'planCuidadoActivo',
            'valoracionesEnfermeria' => fn($q) => $q->orderBy('fecha_valoracion', 'desc')->orderBy('hora_valoracion', 'desc')->take(1),
            'valoracionesMedicas' => fn($q) => $q->orderBy('fecha', 'desc')->take(1),
            'signosVitales' => fn($q) => $q->orderBy('fecha', 'desc')->orderBy('hora', 'desc')->take(7),
            'administracionesMedicacion' => fn($q) => $q->where('estado', 'PENDIENTE')->orderBy('fecha')->orderBy('hora_programada')->take(5),
            'tareasActuales' => fn($q) => $q->orderBy('fecha_programada')->orderBy('hora_programada')->take(5),
            'alertasAbiertas' => fn($q) => $q->orderBy('nivel', 'desc')->orderBy('created_at', 'desc'),
            'seguimientosDiarios' => fn($q) => $q->orderBy('fecha', 'desc')->orderBy('hora', 'desc')->take(5),
            'pasesTurno' => fn($q) => $q->orderBy('fecha', 'desc')->orderBy('created_at', 'desc')->take(3)
        ])->findOrFail($adulto_id);

        if (!auth()->user()->hasAnyRole(['SUPERADMINISTRADOR', 'ADMINISTRADOR', 'ENFERMEROS', 'MEDICO GENERAL/GERIATRA'])) {
            abort(403, 'No tienes permiso para ver la ficha operativa de este residente.');
        }

        $timeline = [
            'PREADMISIÓN' => true,
            'VALORACIÓN ENFERMERÍA' => $adultoMayor->valoracionesEnfermeria->isNotEmpty(),
            'VALORACIÓN MÉDICA' => $adultoMayor->valoracionesMedicas->isNotEmpty(),
            'ADMITIDO' => in_array($adultoMayor->estadoTexto, ['ACTIVO', 'EN_OBSERVACION']),
            'ASIGNADO' => $adultoMayor->habitacion !== null,
            'PLAN ACTIVO' => $adultoMayor->planCuidadoActivo !== null,
            'SEGUIMIENTO ACTIVO' => $adultoMayor->seguimientosDiarios->isNotEmpty(),
        ];

        // Ensure variables exist for charts representation
        $signos = $adultoMayor->signosVitales->reverse();
        $labelsSignos = $signos->map(fn($s) => Carbon::parse($s->fecha)->format('d/m') . ' ' . Carbon::parse($s->hora)->format('H:i'))->values();
        $dataPresionSis = $signos->map(fn($s) => $s->presion_arterial_sistolica ?? $s->presion_sistolica ?? 0)->values();
        $dataPresionDia = $signos->map(fn($s) => $s->presion_arterial_diastolica ?? $s->presion_diastolica ?? 0)->values();
        $dataSaturacion = $signos->map(fn($s) => $s->saturacion_oxigeno ?? $s->saturacion ?? 0)->values();

        $tareas = $adultoMayor->tareasActuales()->get();
        $tareasRealizadas = $tareas->where('estado', 'REALIZADO')->count();
        $tareasPendientes = $tareas->where('estado', 'PENDIENTE')->count();
        $tareasOmitidas = $tareas->where('estado', 'OMITIDO')->count();

        $alertas = $adultoMayor->alertasAbiertas()->get();
        $alertasLabels = $alertas->pluck('tipo_alerta')->unique()->values();
        $alertasData = $alertasLabels->map(function($tipo) use ($alertas) {
            return $alertas->where('tipo_alerta', $tipo)->count();
        });

        $pdf = Pdf::loadView('admin.enfermeria.reportes.ficha-paciente-pdf', compact(
            'adultoMayor', 
            'timeline',
            'labelsSignos', 'dataPresionSis', 'dataPresionDia', 'dataSaturacion',
            'tareasRealizadas', 'tareasPendientes', 'tareasOmitidas',
            'alertasLabels', 'alertasData'
        ));

        return $pdf->stream('Ficha_Operativa_'.$adultoMayor->nombres.'_'.$adultoMayor->ap_paterno.'.pdf');
    }
}
