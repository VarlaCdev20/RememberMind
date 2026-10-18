<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Acciones;

use App\Models\EvaluacionExperta;
use App\Models\RevisionEvaluacionExperta;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\LecturaResultadosExperto;
use App\Modulos\ApoyoDecision\Dominio\Revision\CadenaRevisiones;
use App\Modulos\ApoyoDecision\Dominio\Revision\DisciplinaRevision;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/** Registra un dictamen de una disciplina sobre la valoración exacta, sin confirmar automáticamente otra. */
final readonly class RevisarValoracionExperta
{
    public function __construct(private LecturaResultadosExperto $lector) {}

    /**
     * @param  User  $usuario  Cuenta de la sesión; su personal se obtiene de nuevo dentro de la transacción.
     * @param  string  $evaluacionId  Evaluación original, autorizada para revisión profesional.
     * @param  DisciplinaRevision  $disciplina  Responsabilidad explícita de esta firma.
     * @param  string  $decision  CONFIRMADA o RECHAZADA, sin alterar la salida del motor.
     * @param  string  $justificacion  Fundamento profesional obligatorio.
     * @param  string|null  $anteriorId  Cabeza que el profesional estaba revisando; null solo para la primera firma.
     * @return RevisionEvaluacionExperta Dictamen nuevo o reintento idéntico.
     */
    public function ejecutar(User $usuario, string $evaluacionId, DisciplinaRevision $disciplina,
        string $decision, string $justificacion, ?string $anteriorId = null): RevisionEvaluacionExperta
    {
        $justificacion = trim($justificacion);
        if (! in_array($decision, ['CONFIRMADA', 'RECHAZADA'], true) || $justificacion === '' || mb_strlen($justificacion) > 10000) {
            throw new DomainException('Indique una decisión válida y su justificación, hasta 10000 caracteres.');
        }

        return DB::transaction(function () use ($usuario, $evaluacionId, $disciplina, $decision, $justificacion, $anteriorId): RevisionEvaluacionExperta {
            $cuenta = User::query()->whereKey($usuario->getKey())->lockForUpdate()->firstOrFail();
            $evaluacion = EvaluacionExperta::query()->with('residente')->whereKey($evaluacionId)->lockForUpdate()->firstOrFail();
            Gate::forUser($cuenta)->authorize('revisarValoracion', [$evaluacion, $disciplina]);
            $personal = $cuenta->personal()->where('estado', 'ACTIVO')->where('profesion', $disciplina->value)->lockForUpdate()->sole();
            // La lectura verifica el soporte original; jamás acepta firmas de fixtures o de resultados corruptos.
            $contrato = config('sistema_experto.lectura_clinica', [])[$evaluacion->cod_version_modelo] ?? [];
            if (($contrato['revision_profesional'] ?? false) !== true || ! in_array($evaluacion->estado_ejecucion, $contrato['estados_ejecucion'] ?? [], true)
                || preg_match('/SIM|SINTETIC|ARTIFICIAL|PRUEBA|TEST|EXPERIMENTAL|G09-EXP/i', $evaluacion->version->codigo_version.' '.$evaluacion->origen_activacion.' '.$evaluacion->estado_ejecucion)) {
                throw new DomainException('Esta evaluación no tiene un contrato operativo aprobado para revisión profesional.');
            }
            $areas = $contrato['criterios'] ?? [];
            if ($areas === []) {
                throw new DomainException('La valoración no declara áreas revisables.');
            }
            foreach ($areas as $area) {
                $l = $this->lector->consultar($cuenta, $evaluacion->residente, $evaluacionId, $area);
                if ($l['prueba_tecnica'] || isset($l['simulacion_local']) || ! $l['detalle']['autorizado'] || $l['detalle']['integridad']
                    || ! $l['detalle']['criterio_registrado']) {
                    throw new DomainException('La valoración original no está íntegra o no tiene autorización de revisión.');
                }
            }
            $base = RevisionEvaluacionExperta::query()->where('cod_evaluacion_experta', $evaluacionId)->where('disciplina', $disciplina->value);
            $filas = (clone $base)->lockForUpdate()->get();
            $cabezaId = CadenaRevisiones::cabeza($filas->pluck('cod_revision_anterior', 'cod_revision_experta')->all());
            $cabeza = $cabezaId === null ? null : $filas->firstWhere('cod_revision_experta', $cabezaId);
            if ($cabeza && $cabeza->cod_revision_anterior === $anteriorId && $cabeza->cod_usuario === $cuenta->getKey()
                && $cabeza->cod_personal === $personal->getKey() && $cabeza->decision === $decision && $cabeza->justificacion === $justificacion) {
                return $cabeza;
            }
            if ($cabeza?->getKey() !== $anteriorId || ($anteriorId !== null && ! $filas->contains('cod_revision_experta', $anteriorId))) {
                throw new DomainException('La revisión cambió. Recargue la valoración antes de registrar otra decisión.');
            }
            $revision = RevisionEvaluacionExperta::create(['cod_evaluacion_experta' => $evaluacionId, 'disciplina' => $disciplina->value,
                'cod_personal' => $personal->getKey(), 'cod_usuario' => $cuenta->getKey(), 'fecha_hora_revision' => now(),
                'decision' => $decision, 'justificacion' => $justificacion, 'cod_revision_anterior' => $anteriorId]);
            activity('SistemaExperto')->causedBy($cuenta)->performedOn($revision)
                ->withProperties(['evaluacion' => $evaluacionId, 'disciplina' => $disciplina->value, 'decision' => $decision])
                ->log('Revisión profesional de valoración experta registrada.');

            return $revision;
        }, 5);
    }
}
