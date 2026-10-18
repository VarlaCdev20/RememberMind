<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\EvaluacionExperta;
use App\Models\RevisionEvaluacionExperta;
use App\Models\User;
use App\Modulos\ApoyoDecision\Dominio\Revision\CadenaRevisiones;
use App\Modulos\ApoyoDecision\Dominio\Revision\DisciplinaRevision;
use Illuminate\Support\Facades\Gate;

/** Presenta las dos cadenas de dictámenes conservados, sin fabricar una aprobación conjunta. */
final class ConsultarRevisionesValoracionExperta
{
    public function consultar(User $usuario, string $evaluacionId): ?array
    {
        $usuario = User::query()->findOrFail($usuario->getKey());
        $e = EvaluacionExperta::with('residente', 'version')->findOrFail($evaluacionId);
        Gate::forUser($usuario)->authorize('consultarResultados', [EvaluacionExperta::class, $e->residente]);
        $c = config('sistema_experto.lectura_clinica', [])[$e->cod_version_modelo] ?? [];
        if (($c['revision_profesional'] ?? false) !== true
            || ! in_array($e->estado_ejecucion, $c['estados_ejecucion'] ?? [], true)
            || preg_match('/SIM|SINTETIC|ARTIFICIAL|PRUEBA|TEST|EXPERIMENTAL|G09-EXP/i', $e->version->codigo_version.' '.$e->origen_activacion.' '.$e->estado_ejecucion)) {
            return null;
        }
        $revisiones = RevisionEvaluacionExperta::with('personal')->where('cod_evaluacion_experta', $evaluacionId)
            ->orderBy('fecha_hora_revision')->orderBy('cod_revision_experta')->get()->groupBy('disciplina');
        $salida = [];
        foreach (DisciplinaRevision::cases() as $d) {
            $filas = $revisiones->get($d->value, collect());
            $cabezaId = CadenaRevisiones::cabeza($filas->pluck('cod_revision_anterior', 'cod_revision_experta')->all());
            $cabeza = $cabezaId === null ? null : $filas->firstWhere('cod_revision_experta', $cabezaId);
            $salida[$d->value] = ['nombre' => $d === DisciplinaRevision::MEDICINA ? 'Medicina' : 'Psicología',
                'estado' => $cabeza?->decision ?? 'PENDIENTE', 'cabeza' => $cabeza?->getKey(),
                'puede_firmar' => Gate::forUser($usuario)->allows('revisarValoracion', [$e, $d]),
                'historial' => $filas->map(fn ($r) => ['id' => $r->getKey(), 'decision' => $r->decision,
                    'profesional' => trim(implode(' ', [$r->personal->nombres, $r->personal->apellido_paterno, $r->personal->apellido_materno])),
                    'fecha' => $r->fecha_hora_revision->format('d/m/Y H:i'), 'justificacion' => $r->justificacion,
                    'anterior' => $r->cod_revision_anterior])->all()];
        }

        return $salida;
    }
}
