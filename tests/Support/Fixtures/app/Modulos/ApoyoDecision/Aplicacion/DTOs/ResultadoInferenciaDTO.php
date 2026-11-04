<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\DTOs;

use MotorExperto\dominio\Entities\Cognicion\ResultadoDominio;
use MotorExperto\dominio\Entities\Cognicion\VectorCognitivo;
use MotorExperto\dominio\Entities\Conocimiento\ReglaExperta;
use MotorExperto\dominio\Entities\Conocimiento\RelacionSemantica;
use MotorExperto\dominio\ValueObjects\Cognicion\DominioCognitivoEnum;
use MotorExperto\dominio\ValueObjects\Cognicion\EstadoDominioEnum;
use DomainException;

/** Transporta los cinco resultados de Domain con evaluabilidad, evidencias, reglas y trazabilidad, manteniendo separados los dominios. */
final readonly class ResultadoInferenciaDTO
{
    /** @var array<string, ResultadoDominio> Resultados originales con estados, evaluabilidad y evidencias. */
    public array $dominios;

    /** @var array<string, list<ReglaExperta>> Reglas originales declaradas para cada uno de los cinco dominios. */
    public array $reglasPorDominio;

    /** @var array<string, string> Textos originales de traza por identificador, en el orden recibido. */
    public array $trazas;

    /** @var array<string, array> Evaluaciones originales estructuradas; no se reconstruyen leyendo textos. */
    public array $evaluacionesReglas;

    /** @var array<string, string> Advertencias declaradas; no se crean advertencias clínicas. */
    public array $advertencias;

    /** @var array<string, string> Justificaciones originales de los dominios declarados como X. */
    public array $contradicciones;

    /** @var list<RelacionSemantica> Aristas originales realmente consultadas por el motor. */
    public array $relacionesUtilizadas;

    /** @var array<string, string> Exclusiones originales; no se eliminan por producir U/I/X. */
    public array $exclusiones;

    /**
     * Envuelve un perfil validado sin reconstruir ni modificar sus resultados.
     * @param SolicitudInferenciaDTO $solicitud
     * @param VectorCognitivo $perfilCognitivo
     * @param string $versionMotor
     * @param array<string, list<ReglaExperta>> $reglasPorDominio
     * @param array<string, string> $trazas
     * @param array<string, string> $advertencias
     * @param list<RelacionSemantica> $relacionesUtilizadas
     * @param array<string, string> $exclusiones
     * @param array<string, array> $evaluacionesReglas
     * @throws DomainException
     */
    public function __construct(
        /** Petición original. */ public SolicitudInferenciaDTO $solicitud,
        /** Vector original; sus entidades conservan todos los soportes. */ public VectorCognitivo $perfilCognitivo,
        /** Versión declarada del motor que produjo el perfil. */ public string $versionMotor,
        array $reglasPorDominio,
        array $trazas,
        array $advertencias = [],
        array $relacionesUtilizadas = [],
        array $exclusiones = [],
        array $evaluacionesReglas = [],
    ) {
        $solicitud->exigirContexto($perfilCognitivo->memoria);
        foreach ($evaluacionesReglas as $id => $evaluacion) {
            if (! is_array($evaluacion) || ! ($evaluacion['regla'] ?? null) instanceof ReglaExperta
                || $evaluacion['regla']->id !== $id || $evaluacion['regla']->versionConocimiento !== $solicitud->versionConocimiento
                || ($evaluacion['memoria'] ?? null) !== $perfilCognitivo->memoria
                || ! ($evaluacion['estado'] ?? null) instanceof \MotorExperto\dominio\ValueObjects\Conocimiento\EstadoReglaEnum) {
                throw new DomainException('Evaluación estructurada ajena al resultado original.');
            }
        }
        $this->evaluacionesReglas = $evaluacionesReglas;
        $codigos = array_column(DominioCognitivoEnum::cases(), 'value');
        if ($versionMotor === '' || preg_match('/[\x00-\x20\x7F]/', $versionMotor) === 1
            || count($reglasPorDominio) !== count($codigos) || array_diff(array_keys($reglasPorDominio), $codigos) !== []) {
            throw new DomainException('El resultado requiere versión de motor y reglas explícitas para los cinco dominios.');
        }
        $reglas = [];
        foreach ($codigos as $codigo) {
            $lista = $reglasPorDominio[$codigo];
            if (! is_array($lista) || ! array_is_list($lista)) {
                throw new DomainException('Las reglas de cada dominio deben entregarse como lista.');
            }
            $copia = $ids = [];
            foreach ($lista as $regla) {
                if (! $regla instanceof ReglaExperta || ! $regla->habilitada
                    || $regla->versionConocimiento !== $solicitud->versionConocimiento || isset($ids[$regla->id])) {
                    throw new DomainException('Regla declarada mal tipada, duplicada, deshabilitada o de otra versión.');
                }
                $ids[$regla->id] = true;
                $copia[] = $regla;
            }
            $reglas[$codigo] = $copia;
        }
        $contradicciones = [];
        foreach ($perfilCognitivo->resultados as $codigo => $resultado) {
            // Proyección literal de X: no se detecta ni resuelve aquí el conflicto.
            if ($resultado->estado === EstadoDominioEnum::CONTRADICTORIO) {
                $contradicciones[$codigo] = $resultado->justificacion;
            }
        }
        $this->dominios = $perfilCognitivo->resultados;
        $this->reglasPorDominio = $reglas;
        $this->trazas = self::copiarTextos($trazas);
        $this->advertencias = self::copiarTextos($advertencias);
        $this->contradicciones = $contradicciones;
        if (! array_is_list($relacionesUtilizadas)) {
            throw new DomainException('Las relaciones utilizadas deben ser una lista.');
        }
        $relaciones = [];
        foreach ($relacionesUtilizadas as $r) {
            if (! $r instanceof RelacionSemantica || $r->versionConocimiento !== $solicitud->versionConocimiento
                || isset($relaciones[$r->id])) {
                throw new DomainException('Relación utilizada inválida, duplicada o de otra versión.');
            }
            $relaciones[$r->id] = $r;
        }
        $this->relacionesUtilizadas = array_values($relaciones);
        $this->exclusiones = self::copiarTextos($exclusiones);
    }

    /**
     * Conserva textos identificados sin mantener referencias mutables del llamador.
     * @param array<string, string> $textos
     * @return array<string, string>
     * @throws DomainException
     */
    private static function copiarTextos(array $textos): array
    {
        $copia = [];
        foreach ($textos as $id => $texto) {
            if (! is_string($id) || $id === '' || preg_match('/[\x00-\x20\x7F]/', $id) === 1
                || ! is_string($texto) || trim($texto) === '') {
                throw new DomainException('Las trazas y advertencias requieren identificador y texto explícitos.');
            }
            $copia[$id] = $texto;
        }

        return $copia;
    }
}
