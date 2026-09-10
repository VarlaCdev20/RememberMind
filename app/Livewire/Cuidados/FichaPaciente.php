<?php

namespace App\Livewire\Cuidados;

use App\Models\AdultoMayor;
use Carbon\Carbon;
use Livewire\Component;

class FichaPaciente extends Component
{
    public AdultoMayor $adultoMayor;
    public string $tabActivo = 'resumen';

    public function mount(string $adulto)
    {
        $this->adultoMayor = AdultoMayor::with([
            'habitacion', 'cama',
            'asignacionTurnoActiva.turno',
            'asignacionTurnoActiva.enfermero',
            'planCuidadoActivo',
            'valoracionesEnfermeria' => fn($q) => $q->orderBy('fecha_valoracion', 'desc')->orderBy('hora_valoracion', 'desc')->take(5),
            'valoracionesMedicas' => fn($q) => $q->orderBy('fecha', 'desc')->take(5),
            'signosVitales' => fn($q) => $q->orderBy('fecha', 'desc')->orderBy('hora', 'desc')->take(15),
            'administracionesMedicacion' => fn($q) => $q->where('estado', 'PENDIENTE')->orderBy('fecha')->orderBy('hora_programada')->take(15),
            'tareasActuales' => fn($q) => $q->orderBy('fecha_programada')->orderBy('hora_programada')->take(15),
            'alertasAbiertas' => fn($q) => $q->orderBy('nivel', 'desc')->orderBy('created_at', 'desc')->take(10),
            'seguimientosDiarios' => fn($q) => $q->orderBy('fecha', 'desc')->orderBy('hora', 'desc')->take(10),
            'pasesTurno' => fn($q) => $q->orderBy('fecha', 'desc')->orderBy('created_at', 'desc')->take(10)
        ])->findOrFail($adulto);

        // Security check
        if (!auth()->user()->hasRole('SUPERADMINISTRADOR')) {
            $asignacion = $this->adultoMayor->asignacionTurnoActiva;
            if (!$asignacion || $asignacion->cod_usu_enfermero !== auth()->id()) {
                abort(403, 'No tienes permiso para ver la ficha de un paciente no asignado a tu turno actual.');
            }
        }
    }

    public function cambiarTab(string $tab)
    {
        $this->tabActivo = $tab;
    }

    public function render()
    {
        // Datos para gráficas (extraer de las relaciones cargadas)
        $signos = $this->adultoMayor->signosVitales->reverse();
        $labelsSignos = $signos->map(fn($s) => Carbon::parse($s->fecha)->format('d/m') . ' ' . Carbon::parse($s->hora)->format('H:i'));
        $dataPresionSis = $signos->map(fn($s) => $s->presion_sistolica);
        $dataPresionDia = $signos->map(fn($s) => $s->presion_diastolica);
        $dataSaturacion = $signos->map(fn($s) => $s->saturacion);

        // Datos para gráficas Tareas
        $tareas = $this->adultoMayor->tareasActuales()->get(); // fetch all to count
        $tareasTotal = $tareas->count();
        $tareasRealizadas = $tareas->where('estado', 'REALIZADO')->count();
        $tareasPendientes = $tareas->where('estado', 'PENDIENTE')->count();
        $tareasOmitidas = $tareas->where('estado', 'OMITIDO')->count();

        // Datos para gráficas Alertas
        $alertas = $this->adultoMayor->alertasAbiertas()->get();
        $alertasLabels = $alertas->pluck('tipo_alerta')->unique()->values();
        $alertasData = $alertasLabels->map(function($tipo) use ($alertas) {
            return $alertas->where('tipo_alerta', $tipo)->count();
        });

        // Datos para gráficas Seguimientos
        $seguimientos = $this->adultoMayor->seguimientosDiarios()->with('turno')->get();
        $seguimientosLabels = $seguimientos->pluck('turno.nombre')->filter()->unique()->values();
        $seguimientosData = $seguimientosLabels->map(function($turno) use ($seguimientos) {
            return $seguimientos->where('turno.nombre', $turno)->count();
        });

        $timeline = [
            'PREADMISIÓN' => true,
            'VALORACIÓN ENFERMERÍA' => $this->adultoMayor->valoracionesEnfermeria->isNotEmpty(),
            'VALORACIÓN MÉDICA' => $this->adultoMayor->valoracionesMedicas->isNotEmpty(),
            'ADMITIDO' => in_array($this->adultoMayor->estadoTexto, ['ACTIVO', 'EN_OBSERVACION']),
            'ASIGNADO' => $this->adultoMayor->asignacionTurnoActiva !== null,
            'PLAN ACTIVO' => $this->adultoMayor->planCuidadoActivo !== null,
            'SEGUIMIENTO ACTIVO' => $this->adultoMayor->seguimientosDiarios->isNotEmpty(),
        ];

        return view('livewire.cuidados.ficha-paciente', [
            'labelsSignos' => $labelsSignos->values(),
            'dataPresionSis' => $dataPresionSis->values(),
            'dataPresionDia' => $dataPresionDia->values(),
            'dataSaturacion' => $dataSaturacion->values(),
            
            'dataTareas' => [$tareasRealizadas, $tareasPendientes, $tareasOmitidas],
            
            'labelsAlertas' => $alertasLabels,
            'dataAlertas' => $alertasData,

            'labelsSeguimientos' => $seguimientosLabels,
            'dataSeguimientos' => $seguimientosData,

            'timeline' => $timeline,
        ])->layout('layouts.sistema');
    }
}
