<?php

namespace App\Modulos\Clinica\Aplicacion\Consultas;

use App\Compartido\Aplicacion\Contratos\ContextoEjecucion;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;

/** Preparación inicial y tras hidratación; conserva límites y orden de la ficha. */
final class PrepararFichaClinica
{
    public function __construct(private readonly ContextoEjecucion $contexto, private readonly TurnoEnfermeriaService $turnos) {}

    public function cargar(string $codResidente): Residente
    {
        $this->autorizar($codResidente);

        return Residente::with($this->relaciones())->findOrFail($codResidente);
    }

    public function preparar(Residente $residente): void
    {
        $this->autorizar($residente->getKey());
        $residente->loadMissing($this->relaciones());
    }

    private function relaciones(): array
    {
        return [
            'ocupacionActiva.cama.habitacion',
            'cama.habitacion',
            'admisiones',
            'familiares',
            'alergias' => fn ($q) => $q->whereIn('estado', ['ACTIVA', 'ACTIVO'])->orderByDesc('fecha_hora'),
            'diagnosticos' => fn ($q) => $q->whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE'])->orderByDesc('fecha_hora'),
            'antecedentesClinicos' => fn ($q) => $q->whereNotIn('estado', ['ANULADO', 'INACTIVO'])->orderByDesc('fecha_referencia'),
            'seguros' => fn ($q) => $q->whereIn('estado', ['ACTIVO', 'ACTIVA']),
            'asignacionesJornada.jornada.turno',
            'asignacionesJornada.personal.usuario',
            'atenciones',
            'pasesTurno.personalSaliente.usuario',
            'notasClinicas' => fn ($q) => $q->whereNotIn('estado', ['ANULADA', 'ANULADO', 'INACTIVA', 'INACTIVO'])->orderByDesc('fecha_hora')->take(10),
            'actividades' => fn ($q) => $q->orderBy('fecha_hora')->take(20),
            'signosVitales' => fn ($q) => $q->with('registradoPor')->orderByDesc('fecha_hora')->take(60),
            'ejecucionesCuidado' => fn ($q) => $q->with('intervencion')->orderBy('fecha_hora_programada')->take(30),
            'registrosIngesta' => fn ($q) => $q->orderByDesc('fecha_hora')->take(10),
            'registrosHidratacion' => fn ($q) => $q->orderByDesc('fecha_hora')->take(30),
            'registrosMovilidad' => fn ($q) => $q->orderByDesc('fecha_hora')->take(10),
            'controlesCognitivos' => fn ($q) => $q->orderByDesc('fecha_hora')->take(10),
            'valoracionesDolor' => fn ($q) => $q->orderByDesc('fecha_hora')->take(10),
            'medicaciones' => fn ($q) => $q->with(['medicamento', 'horarios'])->where('estado', 'ACTIVA'),
            'administracionesMedicacion' => fn ($q) => $q->with(['medicacion.medicamento', 'personal'])->orderByDesc('fecha_hora_programada')->take(30),
            'alertas' => fn ($q) => $q->with('eventos.usuario')->orderByDesc('fecha_hora')->take(25),
            'evaluacionesGeriatricas' => fn ($q) => $q->orderByDesc('fecha_hora')->take(10),
            'valoracionesFuncionales' => fn ($q) => $q->with('registradoPor')->orderByDesc('fecha_hora')->take(10),
        ];
    }

    private function autorizar(string $codigo): void
    {
        $actor = $this->contexto->usuario();
        $actor = $actor ? User::query()->find($actor->getKey()) : null;
        abort_unless($actor?->estado === 'ACTIVO', 403);
        if ($actor->hasAnyRole(['ADMINISTRADOR', 'GERENTE']) && $actor->can('residentes.ver')) {
            return;
        }
        if ($actor->hasRole('ENFERMEROS')) {
            $this->turnos->autorizarConsultaResidente($codigo, $actor);

            return;
        }
        $this->turnos->autorizarAccionPaciente($codigo, $actor);
    }
}
