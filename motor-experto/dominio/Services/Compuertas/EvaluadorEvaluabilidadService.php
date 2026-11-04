<?php

declare(strict_types=1);

namespace MotorExperto\dominio\Services\Compuertas;

use MotorExperto\dominio\Entities\Hechos\EvidenciaCognitiva;
use MotorExperto\dominio\Entities\Hechos\HechoExperto;
use MotorExperto\dominio\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\ValueObjects\Cognicion\DominioCognitivoEnum;
use MotorExperto\dominio\ValueObjects\Cognicion\EvaluabilidadEnum;
use MotorExperto\dominio\ValueObjects\Cognicion\HallazgoInstrumentalEnum;
use MotorExperto\dominio\ValueObjects\Dcl\EstadoCriterioDclEnum;
use DomainException;

/** Determina si hay información suficiente para evaluar; no establece alteración cognitiva. */
final readonly class EvaluadorEvaluabilidadService
{
    /**
     * Conecta colaboradores puros sin iniciar procesos externos.
     * @param VerificadorEvidenciaService $verificador
     * @param EvaluadorConfusoresService $confusores
     */
    public function __construct(
        /** Verificación pura de candidatas. */
        private VerificadorEvidenciaService $verificador = new VerificadorEvidenciaService(),
        /** Revisión contextual sin diagnóstico automático. */
        private EvaluadorConfusoresService $confusores = new EvaluadorConfusoresService(),
    ) {}

    /**
     * Evalúa compuertas y mantiene separados nivel, resultado e incertidumbre.
     * @param MemoriaTrabajo $memoria
     * @param DominioCognitivoEnum $dominio
     * @param list<EvidenciaCognitiva> $candidatas
     * @param list<HechoExperto> $revisionesProcedencia
     * @param list<HechoExperto> $revisionesContexto
     * @return array{ uso: string, memoria: MemoriaTrabajo, dominio: DominioCognitivoEnum, estado: EvaluabilidadEnum, codigo: string, habilitaResultado: bool, soportesIntegracion: list<EvidenciaCognitiva>, faltantes: list<string>, motivos: list<string>, modificador: ?string, verificacion: array, contexto: array }
     * @throws DomainException
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
