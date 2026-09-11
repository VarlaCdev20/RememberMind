<?php

namespace App\Services\Enfermeria;

use App\Models\AdultoMayor;
use App\Models\AlertaAdulto;
use App\Models\TareaPlanCuidado;
use App\Models\User;
use App\Services\Medicacion\AgendaMedicacionService;
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

        $residentes = AdultoMayor::with(['habitacion', 'cama'])->whereIn('cod_am', $codigos)->get()->keyBy('cod_am');
        $enCentro = $residentes->filter(fn (AdultoMayor $adulto) => ($adulto->estado_operativo ?: 'EN_CENTRO') === 'EN_CENTRO')->keys()->all();

        $alertas = AlertaAdulto::whereIn('cod_am', $codigos)->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])
            ->get()->map(fn ($alerta) => [
                'id' => 'alerta-'.$alerta->cod_alerta,
                'origen_id' => $alerta->cod_alerta,
                'tipo' => 'ALERTA',
                'estado' => $alerta->estado,
                'prioridad' => $alerta->nivel === 'CRITICO' ? 1 : 2,
                'fecha_hora' => $alerta->created_at,
                'titulo' => $alerta->tipo_alerta,
                'detalle' => $alerta->motivo,
                'paciente' => $residentes->get($alerta->cod_am),
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
            'paciente' => $residentes->get($dosis['medicacion']->cod_am),
        ]);

        $tareas = TareaPlanCuidado::whereIn('cod_am', $enCentro)
            ->whereIn('estado', ['PENDIENTE', 'EN_PROCESO'])
            ->whereDate('fecha_programada', '<=', $momento->toDateString())
            ->get()->map(function ($tarea) use ($residentes, $momento) {
                $fechaHora = $tarea->fecha_programada->copy()->setTimeFromTimeString($tarea->hora_programada ?: '23:59');
                $vencida = $fechaHora->isPast();
                return [
                    'id' => 'tarea-'.$tarea->cod_tarea,
                    'origen_id' => $tarea->cod_tarea,
                    'tipo' => 'TAREA',
                    'estado' => $vencida ? 'VENCIDA' : 'PROXIMA',
                    'prioridad' => $vencida ? 4 : 6,
                    'fecha_hora' => $fechaHora,
                    'titulo' => $tarea->titulo,
                    'detalle' => $tarea->descripcion,
                    'paciente' => $residentes->get($tarea->cod_am),
                ];
            });

        return $alertas->concat($dosis)->concat($tareas)
            ->sortBy(fn (array $item) => sprintf('%02d-%s', $item['prioridad'], $item['fecha_hora']?->format('YmdHis') ?? ''))
            ->values();
    }
}
