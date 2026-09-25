<?php

namespace App\Backend\Modulos\Enfermeria\Servicios;

use App\Models\Residente;
use App\Models\Alerta;
use App\Models\EjecucionCuidado;
use App\Models\User;
use App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService;
use App\Backend\Modulos\Medicacion\Servicios\AgendaMedicacionService;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class AgendaTurnoService
{
    public function __construct(
        private readonly TurnoEnfermeriaService $turnos,
        private readonly AgendaMedicacionService $medicacion,
    ) {}

    public function generar(User $usuario, ?CarbonInterface $momento = null): Collection
    {
        $momento ??= now();
        $turno = $this->turnos->obtenerTurnoActivo($usuario, $momento->toDateString());
        $codigos = $this->turnos->esSuperAdmin($usuario)
            ? $this->turnos->obtenerPacientesAsignadosIds($usuario)
            : $this->turnos->obtenerPacientesAsignadosIds($usuario, $turno?->cod_turno);

        if ($codigos === []) {
            return collect();
        }

        $residentes = Residente::with(['cama.habitacion'])->whereIn('cod_residente', $codigos)->get()->keyBy('cod_residente');
        $enCentro = $residentes->filter(fn (Residente $adulto) => ($adulto->estado_operativo ?: 'EN_CENTRO') === 'EN_CENTRO')->keys()->all();

        $alertas = Alerta::whereIn('cod_residente', $codigos)->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])
            ->get()->map(fn ($alerta) => [
                'id' => 'alerta-'.$alerta->cod_alerta,
                'origen_id' => $alerta->cod_alerta,
                'tipo' => 'ALERTA',
                'estado' => $alerta->estado,
                'prioridad' => in_array($alerta->prioridad, ['CRITICO', 'CRITICA']) ? 1 : 2,
                'fecha_hora' => $alerta->fecha_hora,
                'titulo' => $alerta->tipo,
                'detalle' => $alerta->descripcion,
                'paciente' => $residentes->get($alerta->cod_residente),
            ]);

        $dosis = $this->medicacion->paraAdultos($enCentro, $momento)->map(fn (array $dosis) => [
            'id' => 'medicacion-'.$dosis['id'],
            'origen_id' => $dosis['medicacion']->cod_med_adulto,
            'tipo' => 'MEDICACION',
            'estado' => $dosis['estado'],
            'prioridad' => match ($dosis['estado']) { 'VENCIDA' => 2, 'PROXIMA' => 5, 'PENDIENTE' => 7, default => 8 },
            'fecha_hora' => $dosis['programada'],
            'titulo' => $dosis['medicacion']->nombre_medicamento,
            'detalle' => trim($dosis['medicacion']->dosis.' · '.$dosis['medicacion']->via_administracion),
            'paciente' => $residentes->get($dosis['medicacion']->cod_residente),
        ]);

        $tareas = EjecucionCuidado::whereIn('cod_residente', $enCentro)
            ->whereIn('estado', ['PENDIENTE', 'EN_PROCESO'])
            ->whereDate('fecha_hora_programada', '<=', $momento->toDateString())
            ->get()->map(function ($tarea) use ($residentes, $momento) {
                $fechaHora = $tarea->fecha_hora_programada ? $tarea->fecha_hora_programada->copy() : now();
                $vencida = $fechaHora->isPast();
                return [
                    'id' => 'tarea-'.$tarea->cod_ejecucion,
                    'origen_id' => $tarea->cod_ejecucion,
                    'tipo' => 'TAREA',
                    'estado' => $vencida ? 'VENCIDA' : 'PROXIMA',
                    'prioridad' => $vencida ? 4 : 6,
                    'fecha_hora' => $fechaHora,
                    'titulo' => $tarea->titulo,
                    'detalle' => $tarea->descripcion,
                    'paciente' => $residentes->get($tarea->cod_residente),
                ];
            });

        return $alertas->concat($dosis)->concat($tareas)
            ->sortBy(fn (array $item) => sprintf('%02d-%s', $item['prioridad'], $item['fecha_hora']?->format('YmdHis') ?? ''))
            ->values();
    }
}
