<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\Services\Explicacion;

use MotorExperto\dominio\orion\Entities\Cognicion\VectorCognitivo;
use MotorExperto\dominio\orion\Entities\Conocimiento\ReglaExperta;
use MotorExperto\dominio\orion\Entities\Conocimiento\RelacionSemantica;
use MotorExperto\dominio\orion\Entities\Dcl\ResultadoClasificacionDcl;
use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Explicación (Reconstrucción de antecedentes).
 * TÍTULO: Explicación reproducible en tres niveles.
 * CAPA: Domain.
 * SUBSISTEMA: Explicación.
 * RESPONSABILIDAD: Exponer perfil, compuertas, reglas, fuentes y motivos originales.
 * ENTRADAS: Vector original, reglas aplicadas, trazas, advertencias y DCL opcional.
 * SALIDAS: Resumen, interpretación y fundamento como texto sin formato de presentación.
 * RESTRICCIONES: No genera HTML/PDF, inventa revisión profesional ni prescribe acciones.
 * TRAZABILIDAD: R1/R3/R4/R7/R8/R9; Documento 04 y contrato DCL-PET experimental.
 */
final readonly class GeneradorExplicacionService
{
    /**
     * Construye la explicación desde resultados conservados, sin volver a ejecutar reglas.
     *
     * @param  VectorCognitivo  $perfil  Perfil original de una única instantánea.
     * @param  array<string, list<ReglaExperta>>  $reglasPorDominio  Reglas efectivamente cumplidas.
     * @param  array<string, string>  $trazas  Antecedentes de compuertas y condiciones.
     * @param  array<string, string>  $advertencias  Limitaciones del productor.
     * @param  ResultadoClasificacionDcl|null  $dcl  Salida del mismo perfil, si se solicitó.
     * @param  list<RelacionSemantica>  $relaciones  Aristas utilizadas de la versión original.
     * @param  array<string, string>  $exclusiones  Evidencias descartadas y razones.
     * @return array{resumen: string, interpretacion: string, fundamento: string} Tres niveles reproducibles.
     *
     * @throws DomainException Por mezcla de perfiles, referencias o metadatos incompatibles.
     */
    public function generar(VectorCognitivo $perfil, array $reglasPorDominio, array $trazas, array $advertencias = [],
        ?ResultadoClasificacionDcl $dcl = null, array $relaciones = [], array $exclusiones = []): array
    {
        $memoria = $perfil->memoria;
        if (! str_starts_with($memoria->evaluacionId, 'SIM-') || strlen($memoria->evaluacionId) <= 4
            || ! str_starts_with($memoria->residenteId, 'SINTETICO-') || strlen($memoria->residenteId) <= 10) {
            throw new DomainException('El generador solo explica casos explícitamente sintéticos.');
        }
        if ($dcl !== null && $dcl->perfil->cognicion !== $perfil) {
            throw new DomainException('La explicación DCL pertenece a otro perfil cognitivo.');
        }
        if (array_diff(array_keys($reglasPorDominio), array_keys($perfil->resultados))
            || count($reglasPorDominio) !== 5 || ! array_is_list($relaciones)) {
            throw new DomainException('La explicación requiere los cinco dominios y relaciones explícitas.');
        }
        $m = $perfil->memoria;
        $resumen = 'Simulación académica de '.$m->evaluacionId.' para '.$m->residenteId
            .'; corte '.$m->fechaCorte->format('Y-m-d\TH:i:s.uP').'; conocimiento '.$m->versionConocimiento.'.';
        $interpretacion = $fundamento = [];
        foreach ($perfil->resultados as $codigo => $resultado) {
            $resumen .= ' '.$codigo.'='.$resultado->estado->value.' ('.$resultado->evaluabilidad->codigo($resultado->dominio).').';
            $interpretacion[] = $codigo.': '.$resultado->justificacion;
            $reglas = $reglasPorDominio[$codigo];
            if (! is_array($reglas) || ! array_is_list($reglas)) {
                throw new DomainException('Las reglas explicadas deben ser listas originales por dominio.');
            }
            $ids = [];
            foreach ($reglas as $regla) {
                if (! $regla instanceof ReglaExperta || ! $regla->habilitada || $regla->versionConocimiento !== $m->versionConocimiento
                    || isset($ids[$regla->id])) {
                    throw new DomainException('Regla explicada inválida, duplicada o de otra versión.');
                }
                $ids[$regla->id] = true;
                $fundamento[] = 'Regla '.$regla->id.'; versión '.$regla->versionConocimiento.'; fuente '.$regla->fuente
                    .'; consecuencia declarativa '.$this->literal($regla->consecuencia).'.';
            }
            foreach ($resultado->subcomponentes as $subresultado) {
                $fundamento[] = $subresultado->subcomponente->value.'='.$subresultado->hallazgo->value
                    .'; '.$subresultado->justificacion;
            }
        }
        if ($dcl === null) {
            $interpretacion[] = 'Clasificación DCL no solicitada; no se atribuye un patrón.';
        } else {
            $interpretacion[] = $dcl->patronCandidato === null
                ? 'Abstención: '.implode(', ', array_map(static fn ($motivo): string => $motivo->value, $dcl->motivos))
                : 'Patrón candidato experimental '.$dcl->patronCandidato->value.'.';
            $fundamento[] = 'Contrato Petersen '.$dcl->perfil->versionContrato.'; '.$dcl->justificacion;
            foreach ($dcl->perfil->criterios as $codigo => $estado) {
                $fundamento[] = $codigo.'='.$estado->value.'; '.$dcl->perfil->justificaciones[$codigo]
                    .'; soportes='.implode(',', array_map(static fn ($h): string => $h->id, $dcl->perfil->soportes[$codigo]));
            }
            $fundamento[] = 'Diferencial='.$dcl->perfil->diferencial->value
                .'; independencia relativa='.$dcl->perfil->funcionalidad->independenciaRelativa->value
                .'; '.$dcl->perfil->funcionalidad->justificacion;
            foreach ($dcl->perfil->funcionalidad->evidencias as $e) {
                $fundamento[] = 'IADL '.$e->hecho->id.'; actividad='.$e->actividadId.'; desempeño='.$e->estado->value
                    .'; oportunidad='.$e->oportunidad->value.'; previo='.($e->nivelPrevio?->value ?? 'DESCONOCIDO')
                    .'; causa='.$this->literal($e->causaAyuda).'; atribución ficticia='.$this->literal($e->atribucionCognitivaSimulada)
                    .'; revisor ficticio='.$this->literal($e->revisorSinteticoId);
            }
        }
        // Incluye las fuentes excluidas y las declaraciones de compuerta, no solo las favorables.
        $hechos = $m->hechos;
        ksort($hechos, SORT_STRING);
        foreach ($hechos as $h) {
            $fundamento[] = 'Hecho '.$h->id.'; concepto='.$h->concepto.'; valor='.$this->literal($h->valor)
                .'; fuente='.$h->fuenteId.'; registro='.$h->origenId.'; episodio='.$h->episodioId
                .'; fecha='.$h->fecha->format('Y-m-d\TH:i:s.uP').'; versión='.$h->versionConocimiento;
            $e = $m->evidencias[$h->id] ?? null;
            if ($e !== null) {
                $fundamento[] = 'Evidencia '.$h->id.'; alcance='.($e->subcomponente?->value ?? $e->dominio->value)
                    .'; hallazgo='.$e->hallazgo->value.'; '.$e->justificacion.'; referencia='.$this->literal($e->referenciaSintetica);
            }
        }
        $ids = [];
        foreach ($relaciones as $r) {
            if (! $r instanceof RelacionSemantica || $r->versionConocimiento !== $m->versionConocimiento || isset($ids[$r->id])) {
                throw new DomainException('Relación explicada inválida, duplicada o de otra versión.');
            }
            $ids[$r->id] = true;
            $fundamento[] = 'Relación '.$r->id.': '.$r->origen->codigo.' '.$r->tipo->value.' '.$r->destino->codigo
                .'; versión='.$r->versionConocimiento;
        }
        foreach (['Traza' => $trazas, 'Advertencia' => $advertencias, 'Exclusión' => $exclusiones] as $tipo => $textos) {
            ksort($textos, SORT_STRING);
            foreach ($textos as $id => $texto) {
                if (! is_string($id) || $id === '' || ! is_string($texto) || trim($texto) === '') {
                    throw new DomainException('La explicación requiere antecedentes identificados y no vacíos.');
                }
                $fundamento[] = $tipo.' '.$id.': '.$texto;
            }
        }
        $interpretacion[] = 'Los hallazgos descriptivos no acreditan A/P. No es diagnóstico ni habilita decisiones asistenciales.';

        return ['resumen' => $resumen, 'interpretacion' => implode("\n", $interpretacion),
            'fundamento' => 'Evaluación '.$m->evaluacionId.'; conocimiento '.$m->versionConocimiento."\n".implode("\n", $fundamento)];
    }

    /** @param mixed $valor Dato original. @return string Representación tipada sin reemplazar null, false o cero; puede lanzar JsonException por texto inválido. */
    private function literal(mixed $valor): string
    {
        return json_encode($valor, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    }
}
