<?php

namespace App\Services\Enfermeria;

use App\Models\AlertaAdulto;
use App\Models\AsignacionTurnoAdulto;
use App\Models\IncidenteResidente;
use App\Models\LesionResidente;
use App\Models\PaseTurno;
use App\Models\RegistroCuidado;
use App\Models\TareaPlanCuidado;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use App\Services\Medicacion\AgendaMedicacionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PaseTurnoService
{
    public function __construct(private readonly TurnoEnfermeriaService $turnos, private readonly AgendaMedicacionService $medicacion) {}

    public function pendientes(string $codAm, TurnoEnfermeria $turno): array
    {
        $tareas = TareaPlanCuidado::where('cod_am', $codAm)->where('cod_turno', $turno->cod_turno)
            ->whereDate('fecha_programada', today())->whereIn('estado', ['PENDIENTE','EN_PROCESO','OMITIDA'])
            ->get()->map(fn ($r) => ['tipo' => 'TAREA', 'id' => $r->getKey(), 'detalle' => $r->titulo, 'estado' => $r->estado])->all();
        $dosis = $this->medicacion->paraAdultos([$codAm], now())->filter(fn ($r) => in_array($r['estado'], ['PENDIENTE','VENCIDA','PROXIMA']))
            ->map(fn ($r) => ['tipo' => 'MEDICACION', 'id' => $r['id'], 'detalle' => trim($r['medicacion']->nombre_medicamento.' '.$r['medicacion']->dosis), 'estado' => $r['estado'], 'hora' => $r['programada']->format('H:i')])->values()->all();
        $alertas = AlertaAdulto::where('cod_am', $codAm)->whereIn('estado', ['ABIERTA','EN_ATENCION'])
            ->get()->map(fn ($r) => ['tipo' => 'ALERTA', 'id' => $r->getKey(), 'detalle' => $r->tipo_alerta, 'estado' => $r->estado, 'nivel' => $r->nivel])->all();
        $cuidados = RegistroCuidado::where('cod_am', $codAm)->where('cod_turno', $turno->cod_turno)->whereDate('fecha_hora_evento', today())
            ->where(fn ($q) => $q->where('cambio_respecto_basal', 'PEOR')->orWhereIn('resultado', ['PARCIAL','NO_REALIZADO','CANCELADO']))
            ->get()->map(fn ($r) => ['tipo' => 'CUIDADO', 'id' => $r->getKey(), 'detalle' => $r->tipo.' '.$r->subtipo, 'estado' => $r->resultado ?: $r->cambio_respecto_basal])->all();
        $incidentes = IncidenteResidente::where('cod_am', $codAm)->whereIn('estado', ['ABIERTO','EN_SEGUIMIENTO'])
            ->get()->map(fn ($r) => ['tipo' => 'INCIDENTE', 'id' => $r->getKey(), 'detalle' => $r->tipo.': '.$r->descripcion, 'estado' => $r->estado])->all();
        $lesiones = LesionResidente::where('cod_am', $codAm)->where('estado', 'ACTIVA')
            ->get()->map(fn ($r) => ['tipo' => 'LESION', 'id' => $r->getKey(), 'detalle' => $r->tipo.' en '.$r->zona_corporal, 'estado' => 'ACTIVA'])->all();
        return array_values(array_merge($dosis, $alertas, $cuidados, $incidentes, $lesiones, $tareas));
    }

    public function generar(string $codAm, string $turnoEntranteId, string $enfermeroEntranteId, array $datos, User $usuario): PaseTurno
    {
        $saliente = $this->turnos->autorizarMutacionPaciente($codAm, 'pase_turno.generar', $usuario);
        $datos = Validator::make($datos, [
            'observaciones' => 'nullable|string|max:5000', 'estado_general' => 'nullable|string|max:100',
            'recomendacion' => 'nullable|string|max:5000', 'vigilancia' => 'required|boolean',
            'motivo_vigilancia' => 'required_if:vigilancia,true|nullable|string|min:10|max:2000',
        ])->validate();
        $entrante = TurnoEnfermeria::activos()->findOrFail($turnoEntranteId);
        if ($entrante->getKey() === $saliente->getKey()) {
            throw ValidationException::withMessages(['turno_entrante_id' => 'El turno entrante debe ser diferente al saliente.']);
        }
        $receptor = User::whereKey($enfermeroEntranteId)->where('estado', 'ACTIVO')->firstOrFail();
        if (! $receptor->hasRole('ENFERMEROS') || $receptor->getKey() === $usuario->getKey()) {
            throw ValidationException::withMessages(['enfermero_entrante_id' => 'El receptor debe ser otro enfermero activo.']);
        }
        $asignado = AsignacionTurnoAdulto::where('cod_am', $codAm)->where('cod_turno', $entrante->getKey())
            ->where('cod_usu_enfermero', $receptor->getKey())->whereIn('estado', ['ACTIVO','ACTIVA'])
            ->whereDate('fecha_inicio', '<=', today())->where(fn ($q) => $q->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', today()))->exists();
        if (! $asignado) {
            throw ValidationException::withMessages(['enfermero_entrante_id' => 'El receptor no está asignado al residente durante el turno entrante.']);
        }

        return DB::transaction(function () use ($codAm, $saliente, $entrante, $receptor, $datos, $usuario) {
            if (PaseTurno::where('cod_am', $codAm)->where('turno_saliente_id', $saliente->getKey())->whereDate('fecha', today())->lockForUpdate()->exists()) {
                throw ValidationException::withMessages(['cod_am' => 'Ya existe el pase de este residente para el turno actual.']);
            }
            $pendientes = $this->pendientes($codAm, $saliente);
            $realizadas = TareaPlanCuidado::where('cod_am', $codAm)->where('cod_turno', $saliente->getKey())->whereDate('fecha_programada', today())->where('estado', 'REALIZADA')->get(['cod_tarea','titulo','area','resultado'])->toArray();
            $alertas = array_values(array_filter($pendientes, fn ($r) => $r['tipo'] === 'ALERTA'));
            $automatico = count($pendientes).' pendiente(s) generados desde medicación, alertas, cuidados, incidentes, lesiones y tareas.';
            $resumen = trim($automatico.($datos['observaciones'] ? " Observaciones: {$datos['observaciones']}" : ''));
            return PaseTurno::create([
                'cod_am' => $codAm, 'turno_saliente_id' => $saliente->getKey(), 'turno_entrante_id' => $entrante->getKey(),
                'enfermero_saliente_id' => $usuario->getKey(), 'enfermero_entrante_id' => $receptor->getKey(), 'fecha' => today(),
                'estado_general_cierre' => $datos['estado_general'] ?? null, 'resumen_turno' => $resumen,
                'tareas_realizadas_json' => $realizadas, 'tareas_pendientes_json' => $pendientes, 'alertas_activas_json' => $alertas,
                'recomendacion_siguiente_turno' => $datos['recomendacion'] ?? null,
                'requiere_vigilancia_especial' => $datos['vigilancia'], 'motivo_vigilancia' => $datos['motivo_vigilancia'] ?? null, 'estado' => 'GENERADO',
            ]);
        });
    }

    public function recibir(PaseTurno $pase, User $usuario): PaseTurno
    {
        abort_unless($pase->enfermero_entrante_id === $usuario->getKey(), 403);
        $turno = $this->turnos->autorizarMutacionPaciente($pase->cod_am, 'pase_turno.recibir', $usuario);
        abort_unless($turno->getKey() === $pase->turno_entrante_id, 403, 'El pase corresponde a otro turno entrante.');
        return DB::transaction(function () use ($pase) {
            $bloqueado = PaseTurno::lockForUpdate()->findOrFail($pase->getKey());
            abort_unless($bloqueado->puedeRecibirse(), 409, 'El pase ya fue recibido.');
            $bloqueado->update(['estado' => 'RECIBIDO', 'fecha_recibido' => now()]);
            return $bloqueado->refresh();
        });
    }
}
