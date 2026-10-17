<?php

declare(strict_types=1);

namespace App\SistemaExperto\Domain\Services\Compuertas;

use App\SistemaExperto\Domain\Entities\Hechos\EvidenciaCognitiva;
use App\SistemaExperto\Domain\Entities\Hechos\HechoExperto;
use App\SistemaExperto\Domain\Entities\Hechos\MemoriaTrabajo;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\DominioCognitivoEnum;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\EvaluabilidadEnum;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\HallazgoInstrumentalEnum;
use App\SistemaExperto\Domain\ValueObjects\Dcl\EstadoCriterioDclEnum;
use DomainException;

/**
 * TÍTULO: EvaluadorEvaluabilidadService.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Compuertas de evaluabilidad.
 *
 * RESPONSABILIDAD:
 * Determinar información insuficiente, señal o integración técnica de un dominio.
 *
 * ENTRADAS:
 * Memoria, dominio, candidatas, procedencias y revisión contextual explícitas.
 *
 * SALIDAS:
 * Nivel tipado, código EV específico, soportes, exclusiones y motivos trazables.
 *
 * RESTRICCIONES:
 * No emite A/P ni patrones PET; evaluabilidad no es alteración.
 * No exige basal previa, mínimo de observaciones, baremos o puntuación global.
 * Fuera del piloto COG-MEM esta coordinación es propuesta técnica experimental.
 * El nivel 2 requiere referencia externa sintética completa, procedencia
 * verificada y contexto acreditado; no aprueba instrumentos ni uso clínico.
 *
 * TRAZABILIDAD:
 * Reglamento maestro R1/R2/R3/R4/R6/R8/R9; D-122 y CTX-MEM-01.
 */
final readonly class EvaluadorEvaluabilidadService
{
    /**
     * Conecta colaboradores puros sin iniciar procesos externos.
     *
     * @param VerificadorEvidenciaService $verificador Verificador de procedencia y calidad.
     * @param EvaluadorConfusoresService $confusores Evaluador de revisión contextual.
     *
     * No lanza excepciones.
     */
    public function __construct(
        /** Verificación pura de candidatas. */
        private VerificadorEvidenciaService $verificador = new VerificadorEvidenciaService(),
        /** Revisión contextual sin diagnóstico automático. */
        private EvaluadorConfusoresService $confusores = new EvaluadorConfusoresService(),
    ) {}

    /**
     * Evalúa compuertas y mantiene separados nivel, resultado e incertidumbre.
     *
     * La integración no se obtiene por acumular señales ni por falta de hallazgos.
     * La invalidación explícita conserva señales observacionales admisibles,
     * pero impide cerrar una evaluación apoyada solo en referencias instrumentales.
     *
     * @param MemoriaTrabajo $memoria Instantánea original.
     * @param DominioCognitivoEnum $dominio Dominio evaluado.
     * @param list<EvidenciaCognitiva> $candidatas Evidencias seleccionadas explícitamente.
     * @param list<HechoExperto> $revisionesProcedencia Declaraciones de procedencia.
     * @param list<HechoExperto> $revisionesContexto Declaraciones contextuales COG-*:campo.
     * @return array{
     *   uso: string, memoria: MemoriaTrabajo, dominio: DominioCognitivoEnum,
     *   estado: EvaluabilidadEnum, codigo: string, habilitaResultado: bool,
     *   soportesIntegracion: list<EvidenciaCognitiva>, faltantes: list<string>,
     *   motivos: list<string>, modificador: ?string, verificacion: array, contexto: array
     * } Informe de compuertas; habilitaResultado solo expresa nivel técnico.
     * @throws DomainException Por entradas incoherentes detectadas por los colaboradores.
     */
    public function evaluar(MemoriaTrabajo $memoria, DominioCognitivoEnum $dominio, array $candidatas,
        array $revisionesProcedencia = [], array $revisionesContexto = []): array
    {
        $verificacion = $this->verificador->verificar($memoria, $dominio, $candidatas, $revisionesProcedencia);
        $contexto = $this->confusores->evaluar($memoria, $dominio, $revisionesContexto);
        $faltantes = $contexto['faltantes'];
        foreach (['componente_validado', 'condiciones_aplicacion_conocidas'] as $campo) {
            if ($contexto['declaraciones'][$campo]['valor'] !== true) {
                $faltantes[] = $campo;
            }
        }
        $completas = [];
        $observacional = false;
        $hallazgosCompletos = [];
        foreach ($verificacion['admitidas'] as $e) {
            if ($e->hallazgo === HallazgoInstrumentalEnum::HALLAZGO_DESCRIPTIVO) {
                $observacional = true;
            }
            if ($e->subcomponente === null && $e->referenciaSintetica !== []
                && in_array($e->hallazgo, [HallazgoInstrumentalEnum::ALTERACION_OBJETIVA_SIMULADA, HallazgoInstrumentalEnum::RENDIMIENTO_ESPERADO_SIMULADO], true)) {
                $completas[] = $e;
                $hallazgosCompletos[$e->hallazgo->value] = true;
            }
        }
        $motivos = [];
        foreach ($verificacion['excluidas'] as $excluida) {
            foreach ($excluida['motivos'] as $motivo) {
                $motivos[$motivo] = $motivo;
            }
        }
        $impedimento = isset($motivos['EVIDENCIA_NO_INTERPRETABLE']) || isset($motivos['CONTRADICCION_PENDIENTE'])
            || isset($motivos['PROCEDENCIA_CONTRADICTORIA']);
        if (count($hallazgosCompletos) > 1) {
            $motivos['REFERENCIAS_COMPLETAS_CONTRADICTORIAS'] = 'REFERENCIAS_COMPLETAS_CONTRADICTORIAS';
            $impedimento = true;
        }
        $nivel = $verificacion['admitidas'] === [] ? EvaluabilidadEnum::NO_EVALUABLE : EvaluabilidadEnum::PRE_EVALUACION_SENAL;
        if ($contexto['invalidacion'] === true) {
            $motivos['CONTEXTO_INVALIDANTE'] = 'CONTEXTO_INVALIDANTE';
            if (! $observacional) {
                $nivel = EvaluabilidadEnum::NO_EVALUABLE;
            }
        } elseif ($completas !== [] && $contexto['estado'] === EstadoCriterioDclEnum::ACREDITADO
            && $faltantes === [] && ! $impedimento) {
            $nivel = EvaluabilidadEnum::EVALUACION_INTEGRADA;
        }
        if ($verificacion['admitidas'] === []) {
            $motivos['SIN_EVIDENCIA_ADMISIBLE'] = 'SIN_EVIDENCIA_ADMISIBLE';
        }
        if ($completas === []) {
            $motivos['SIN_REFERENCIA_COMPLETA_VERIFICADA'] = 'SIN_REFERENCIA_COMPLETA_VERIFICADA';
        }
        if ($contexto['estado'] === EstadoCriterioDclEnum::CONTRADICTORIO) {
            $motivos['CONTEXTO_CONTRADICTORIO'] = 'CONTEXTO_CONTRADICTORIO';
        }
        if ($faltantes !== []) {
            $motivos['CONTEXTO_NO_ACREDITADO'] = 'CONTEXTO_NO_ACREDITADO';
        }
        ksort($motivos, SORT_STRING);
        $faltantes = array_values(array_unique($faltantes));
        sort($faltantes, SORT_STRING);

        return [
            'uso' => 'PRUEBA_TECNICA_NO_CLINICA', 'memoria' => $memoria, 'dominio' => $dominio,
            'estado' => $nivel, 'codigo' => $nivel->codigo($dominio),
            'habilitaResultado' => $nivel === EvaluabilidadEnum::EVALUACION_INTEGRADA,
            'soportesIntegracion' => $nivel === EvaluabilidadEnum::EVALUACION_INTEGRADA ? $completas : [],
            'faltantes' => $faltantes, 'motivos' => array_values($motivos),
            'modificador' => $nivel === EvaluabilidadEnum::NO_EVALUABLE ? null : $contexto['modificador'],
            'verificacion' => $verificacion, 'contexto' => $contexto,
        ];
    }
}
