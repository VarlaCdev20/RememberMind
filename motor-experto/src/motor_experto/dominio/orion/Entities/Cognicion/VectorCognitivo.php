<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\Entities\Cognicion;

use MotorExperto\dominio\orion\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\DominioCognitivoEnum;
use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Motor de inferencia (Perfil cognitivo multicriterio).
 *
 * Mantiene separados memoria, atención, función ejecutiva, lenguaje y capacidad visuoespacial.
 * Conserva un único contexto de evaluación y evita reutilizar soportes como contribuciones independientes.
 *
 * TÍTULO: Perfil explícito de cinco dominios.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Motor de inferencia.
 *
 * RESPONSABILIDAD:
 * Conservar los cinco ejes sin puntuación global y comprobar soportes no reutilizados.
 *
 * ENTRADAS:
 * Lista completa de cinco resultados de la misma instantánea.
 *
 * SALIDAS:
 * Mapa canónico de dominios con resultados y trazabilidad originales.
 *
 * RESTRICCIONES:
 * No rellena dominios faltantes ni calcula medias, severidad o índices.
 * Las referencias A/P no se reutilizan entre dominios como corroboraciones
 * independientes; los hallazgos descriptivos siguen separados.
 *
 * TRAZABILIDAD:
 * Reglamento maestro R1/R3/R4/R6/R7/R8.
 */
final readonly class VectorCognitivo
{
    /** Contexto común, incluidos evaluación, sujeto, versión y fecha de corte. */
    public MemoriaTrabajo $memoria;

    /** @var array<string, ResultadoDominio> Cinco resultados en orden documental. */
    public array $resultados;

    /**
     * Construye un perfil completo sin inventar resultados faltantes.
     *
     * @param list<ResultadoDominio> $resultados Cinco dominios explícitos.
     * @throws DomainException Por cobertura incompleta, duplicación o evidencia reutilizada.
     */
    public function __construct(array $resultados)
    {
        if (! array_is_list($resultados) || count($resultados) !== count(DominioCognitivoEnum::cases())) {
            throw new DomainException('El vector requiere cinco resultados explícitos.');
        }
        $mapa = $positivos = [];
        $memoria = null;
        foreach ($resultados as $resultado) {
            if (! $resultado instanceof ResultadoDominio || isset($mapa[$resultado->dominio->value])) {
                throw new DomainException('Dominio duplicado o resultado mal tipado.');
            }
            $memoria ??= $resultado->memoria;
            if ($resultado->memoria !== $memoria) {
                throw new DomainException('El vector no puede mezclar instantáneas de memoria.');
            }
            foreach ($resultado->soportesPositivos() as $soporte) {
                foreach ($positivos as [$dominio, $anterior]) {
                    if ($dominio !== $resultado->dominio && ! $soporte->esIndependienteDe($anterior)) {
                        throw new DomainException('Soporte o episodio reutilizado entre dominios A/P.');
                    }
                }
                $positivos[] = [$resultado->dominio, $soporte];
            }
            $mapa[$resultado->dominio->value] = $resultado;
        }
        $ordenados = [];
        foreach (DominioCognitivoEnum::cases() as $dominio) {
            $ordenados[$dominio->value] = $mapa[$dominio->value]
                ?? throw new DomainException('Dominio requerido ausente del perfil.');
        }
        $this->memoria = $memoria;
        $this->resultados = $ordenados;
    }

    /**
     * Recupera un eje existente sin sintetizar un estado global.
     *
     * @param DominioCognitivoEnum $dominio Eje solicitado.
     * @return ResultadoDominio Resultado original.
     *
     * No lanza excepciones: la cobertura completa fue validada al construir.
     */
    public function resultado(DominioCognitivoEnum $dominio): ResultadoDominio
    {
        return $this->resultados[$dominio->value];
    }

}
