<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\Entities;

use MotorExperto\dominio\orion\Entities\Cognicion\ResultadoDominio;
use MotorExperto\dominio\orion\Entities\Cognicion\VectorCognitivo;
use MotorExperto\dominio\orion\Entities\Dcl\PerfilRequisitosDcl;
use MotorExperto\dominio\orion\Entities\Funcionalidad\EvidenciaIadl;
use MotorExperto\dominio\orion\Entities\Funcionalidad\VectorFuncionalidad;
use MotorExperto\dominio\orion\Entities\Hechos\EvidenciaCognitiva;
use MotorExperto\dominio\orion\Entities\Hechos\HechoExperto;
use MotorExperto\dominio\orion\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\DominioCognitivoEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\EstadoDominioEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\EvaluabilidadEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\HallazgoInstrumentalEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\SubcomponenteCognitivoEnum;
use MotorExperto\dominio\orion\ValueObjects\Dcl\CriterioDclEnum;
use MotorExperto\dominio\orion\ValueObjects\Dcl\EstadoCriterioDclEnum;
use MotorExperto\dominio\orion\ValueObjects\Funcionalidad\EstadoDiferencialEnum;
use MotorExperto\dominio\orion\ValueObjects\Funcionalidad\EstadoIadlEnum;
use MotorExperto\dominio\orion\ValueObjects\Funcionalidad\OportunidadIadlEnum;
use DateTimeImmutable;

/** Construir referencias manuales para verificar invariantes, sin interpretar datos. */
final class EscenarioEntidades
{
    /**
     * Entrega una fecha fija, independiente del reloj o la zona del proceso.
     * @return DateTimeImmutable
     */
    public static function fecha(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-10-17T12:00:00-04:00');
    }

    /**
     * Construye un hecho original con overrides explícitos para casos negativos.
     * @param array<string, mixed> $cambios
     * @return HechoExperto
     * @throws \DomainException
     */
    public static function hecho(array $cambios = []): HechoExperto
    {
        return new HechoExperto(...array_replace([
            'id' => 'SIM-H-1', 'evaluacionId' => 'SIM-EVAL', 'residenteId' => 'SINTETICO-RES',
            'episodioId' => 'SIM-EP-1', 'fuenteId' => 'OBS_SINTETICA', 'origenId' => 'SIM-OR-1',
            'versionConocimiento' => 'SIM-V1', 'concepto' => 'OBS', 'fecha' => self::fecha(), 'valor' => 'REGISTRO_FICTICIO',
        ], $cambios));
    }

    /**
     * Declara evidencia ficticia, con referencias externas solo para los dos SIM.
     * @param string $id
     * @param DominioCognitivoEnum $dominio
     * @param HallazgoInstrumentalEnum $hallazgo
     * @param SubcomponenteCognitivoEnum|null $subcomponente
     * @param array<string, mixed> $cambiosHecho
     * @param string $justificacion
     * @return EvidenciaCognitiva
     * @throws \DomainException
     */
    public static function evidencia(
        string $id = 'SIM-EV-MEM',
        DominioCognitivoEnum $dominio = DominioCognitivoEnum::MEMORIA,
        HallazgoInstrumentalEnum $hallazgo = HallazgoInstrumentalEnum::HALLAZGO_DESCRIPTIVO,
        ?SubcomponenteCognitivoEnum $subcomponente = null,
        array $cambiosHecho = [],
        string $justificacion = 'Referencia completamente ficticia.',
    ): EvidenciaCognitiva {
        $sim = in_array($hallazgo, [HallazgoInstrumentalEnum::ALTERACION_OBJETIVA_SIMULADA, HallazgoInstrumentalEnum::RENDIMIENTO_ESPERADO_SIMULADO], true);
        $hecho = self::hecho(array_replace([
            'id' => $id, 'episodioId' => 'SIM-EP-'.$id, 'origenId' => 'SIM-OR-'.$id,
            'fuenteId' => $sim ? 'REFERENCIA_EXTERNA_SINTETICA' : 'OBS_SINTETICA',
            'concepto' => $subcomponente?->value ?? $dominio->value, 'valor' => $hallazgo->value,
        ], $cambiosHecho));
        $instrumento = 'SIM-INSTR-'.$id;
        $referencia = $sim ? [
            'instrumento' => $instrumento, 'edicion' => 'SIM-EDICION', 'subprueba' => 'SIM-TAREA',
            'norma' => 'SIM-NORMA', 'idioma' => 'SIM-IDIOMA', 'poblacion' => 'SIM-POBLACION', 'revisor' => 'SIM-REVISOR',
        ] : [];

        return new EvidenciaCognitiva($hecho, $dominio, $subcomponente, $hallazgo, $instrumento, $justificacion, $referencia);
    }

    /**
     * Construye una memoria del caso fijo con hechos adicionales explícitos.
     * @param list<EvidenciaCognitiva> $evidencias
     * @param list<HechoExperto> $hechos
     * @return MemoriaTrabajo
     * @throws \DomainException
     */
    public static function memoria(array $evidencias = [], array $hechos = []): MemoriaTrabajo
    {
        return new MemoriaTrabajo('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::fecha(),
            [...$hechos, ...array_map(static fn (EvidenciaCognitiva $e): HechoExperto => $e->hecho, $evidencias)], $evidencias);
    }

    /**
     * Construye una IADL ficticiamente revisada, nunca una medición real.
     * @param array<string, mixed> $cambios
     * @return EvidenciaIadl
     * @throws \DomainException
     */
    public static function iadl(array $cambios = []): EvidenciaIadl
    {
        return new EvidenciaIadl(...array_replace([
            'hecho' => self::hecho(['id' => 'SIM-IADL', 'origenId' => 'SIM-OR-IADL',
                'episodioId' => 'SIM-EP-IADL', 'fuenteId' => 'REFERENCIA_IADL_SINTETICA', 'concepto' => 'SIM-ACTIVIDAD']),
            'actividadId' => 'SIM-ACTIVIDAD', 'estado' => EstadoIadlEnum::INDEPENDIENTE,
            'oportunidad' => OportunidadIadlEnum::REAL_Y_ADECUADA, 'nivelPrevio' => EstadoIadlEnum::INDEPENDIENTE,
            'causaAyuda' => null, 'atribucionCognitivaSimulada' => false, 'revisorSinteticoId' => 'SIM-REVISOR',
        ], $cambios));
    }

    /**
     * Prepara cinco dominios y C01–C05 desde declaraciones sintéticas manuales.
     * @param list<string> $estados
     * @param array<string, EstadoCriterioDclEnum> $cambiosCriterios
     * @param EstadoDiferencialEnum $diferencial
     * @param EstadoCriterioDclEnum $independencia
     * @param array<string, string> $razonesEvidencia
     * @return PerfilRequisitosDcl
     * @throws \DomainException
     */
    public static function perfil(
        array $estados = ['A', 'P', 'P', 'P', 'P'],
        array $cambiosCriterios = [],
        EstadoDiferencialEnum $diferencial = EstadoDiferencialEnum::COMPLETADO_SIN_IMPEDIMENTO,
        EstadoCriterioDclEnum $independencia = EstadoCriterioDclEnum::ACREDITADO,
        array $razonesEvidencia = [],
    ): PerfilRequisitosDcl {
        $evidencias = $idsPorDominio = [];
        foreach (DominioCognitivoEnum::cases() as $i => $dominio) {
            $estado = EstadoDominioEnum::from($estados[$i]);
            $hallazgos = match ($estado) {
                EstadoDominioEnum::ALTERACION => [HallazgoInstrumentalEnum::ALTERACION_OBJETIVA_SIMULADA],
                EstadoDominioEnum::RENDIMIENTO_ESPERADO => [HallazgoInstrumentalEnum::RENDIMIENTO_ESPERADO_SIMULADO],
                EstadoDominioEnum::CONTRADICTORIO => [HallazgoInstrumentalEnum::ALTERACION_OBJETIVA_SIMULADA, HallazgoInstrumentalEnum::RENDIMIENTO_ESPERADO_SIMULADO],
                EstadoDominioEnum::NO_INTERPRETABLE => [HallazgoInstrumentalEnum::NO_INTERPRETABLE],
                EstadoDominioEnum::DESCONOCIDO => [],
            };
            $idsPorDominio[$dominio->value] = [];
            foreach ($hallazgos as $n => $hallazgo) {
                $e = self::evidencia('SIM-EV-'.$dominio->value.'-'.$n, $dominio, $hallazgo,
                    justificacion: $razonesEvidencia[$dominio->value] ?? 'Referencia completamente ficticia.');
                $evidencias[] = $e;
                $idsPorDominio[$dominio->value][] = $e->hecho->id;
            }
        }
        $iadl = $independencia === EstadoCriterioDclEnum::NO_ACREDITADO
            ? self::iadl(['estado' => EstadoIadlEnum::CON_AYUDA, 'causaAyuda' => 'Atribución exclusivamente ficticia.', 'atribucionCognitivaSimulada' => true])
            : self::iadl();
        $criterios = $soportes = $razones = [];
        $hechos = [$iadl->hecho];
        foreach (CriterioDclEnum::cases() as $criterio) {
            $estado = $cambiosCriterios[$criterio->value] ?? EstadoCriterioDclEnum::ACREDITADO;
            $criterios[$criterio->value] = $estado;
            $razones[$criterio->value] = 'Declaración artificial independiente.';
            $hecho = self::hecho(['id' => 'SIM-'.$criterio->value, 'origenId' => 'SIM-OR-'.$criterio->value,
                'episodioId' => 'SIM-EP-REVISION', 'fuenteId' => 'REFERENCIA_CRITERIO_SINTETICA',
                'concepto' => $criterio->value, 'valor' => $estado->value]);
            $hechos[] = $hecho;
            $soportes[$criterio->value] = in_array($estado, [EstadoCriterioDclEnum::ACREDITADO, EstadoCriterioDclEnum::NO_ACREDITADO], true) ? [$hecho] : [];
        }
        $memoria = self::memoria($evidencias, $hechos);
        $resultados = [];
        foreach (DominioCognitivoEnum::cases() as $i => $dominio) {
            $estado = EstadoDominioEnum::from($estados[$i]);
            $resultados[] = new ResultadoDominio($dominio, $estado,
                $estado === EstadoDominioEnum::DESCONOCIDO ? EvaluabilidadEnum::NO_EVALUABLE : EvaluabilidadEnum::EVALUACION_INTEGRADA,
                $memoria, [], $idsPorDominio[$dominio->value], 'Estado ficticio declarado, sin cálculo.');
        }
        $vector = new VectorCognitivo($resultados);
        $funcional = new VectorFuncionalidad($memoria, $independencia, [$iadl], 'SIM-REVISOR', 'Revisión artificial, no clínica.');

        return new PerfilRequisitosDcl($vector, $funcional, $diferencial, $criterios, $soportes, $razones);
    }
}
