<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\EvaluacionExperta;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\Gate;

/** Resuelve el contrato de revisión y comprueba la instantánea exacta antes de exponer o firmar decisiones. */
final readonly class ValoracionRevisable
{
    public function __construct(private LecturaResultadosExperto $lector, private ConsultarObservacionesConservadas $observaciones) {}

    public function verificar(User $usuario, EvaluacionExperta $evaluacion): ?array
    {
        $usuario = User::query()->findOrFail($usuario->getKey());
        $evaluacion = EvaluacionExperta::with('residente', 'version')->findOrFail($evaluacion->getKey());
        Gate::forUser($usuario)->authorize('consultarResultados', [EvaluacionExperta::class, $evaluacion->residente]);
        if ($evaluacion->origen_activacion === 'OBSERVACIONAL' && in_array($evaluacion->version_conservacion, ['OBS-SNAP-1', 'OBS-SNAP-2'], true)
            && $evaluacion->estado_ejecucion === 'FINALIZADA') {
            $conservado = $this->observaciones->consultar($usuario, $evaluacion->residente, $evaluacion->getKey());
            if (! $conservado || ! $conservado['persistido'] || $conservado['id'] !== $evaluacion->getKey()) {
                throw new DomainException('Los hallazgos observacionales no tienen una instantánea íntegra para revisar.');
            }

            return ['contrato' => $evaluacion->version_conservacion, 'alcance' => 'HALLAZGOS_OBSERVACIONALES'];
        }
        $contrato = config('sistema_experto.lectura_clinica', [])[$evaluacion->cod_version_modelo] ?? [];
        if (($contrato['revision_profesional'] ?? false) !== true || ! in_array($evaluacion->estado_ejecucion, $contrato['estados_ejecucion'] ?? [], true)
            || preg_match('/SIM|SINTETIC|ARTIFICIAL|PRUEBA|TEST|EXPERIMENTAL|G09-EXP/i', $evaluacion->version->codigo_version.' '.$evaluacion->origen_activacion.' '.$evaluacion->estado_ejecucion)) {
            return null;
        }
        $areas = $contrato['criterios'] ?? [];
        if ($areas === []) {
            throw new DomainException('La valoración no declara áreas revisables.');
        }
        foreach ($areas as $area) {
            $l = $this->lector->consultar($usuario, $evaluacion->residente, $evaluacion->getKey(), $area);
            if ($l['prueba_tecnica'] || isset($l['simulacion_local']) || ! $l['detalle']['autorizado'] || $l['detalle']['integridad'] || ! $l['detalle']['criterio_registrado']) {
                throw new DomainException('La valoración original no está íntegra o no tiene autorización de revisión.');
            }
        }

        return ['contrato' => $evaluacion->version_conservacion, 'alcance' => 'VALORACION_COGNITIVA'];
    }
}
