<?php

declare(strict_types=1);

namespace App\SistemaExperto\Application\DTOs;

use App\SistemaExperto\Domain\Entities\Cognicion\ResultadoDominio;
use App\SistemaExperto\Domain\Entities\Cognicion\VectorCognitivo;
use App\SistemaExperto\Domain\Entities\Conocimiento\ReglaExperta;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\DominioCognitivoEnum;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\EstadoDominioEnum;
use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Application (Transporte del perfil cognitivo).
 *
 * TÍTULO:
 * Resultado del análisis cognitivo.
 *
 * CAPA:
 * Application - Objetos de Transferencia de Datos (DTOs).
 *
 * ¿PARA QUÉ SIRVE?
 * Transporta los cinco resultados de Domain con evaluabilidad, evidencias,
 * reglas y trazabilidad, manteniendo separados los dominios.
 *
 * ¿QUÉ RECIBE?
 * Solicitud, vector cognitivo original, versión del motor, reglas por dominio
 * y textos de traza y advertencias identificados, suministrados por el productor.
 *
 * ¿QUÉ HACE?
 * Contrasta el contexto y el tipado de las reglas. Copia metadatos y expone
 * los resultados originales, incluyendo sus contradicciones declaradas.
 *
 * ¿QUÉ DEVUELVE?
 * Un perfil inmutable con sus cinco resultados y versiones trazables.
 *
 * ¿QUÉ NO DEBE HACER?
 * No ejecuta el motor, evalúa reglas, convierte G09 en A/P ni calcula un índice
 * global. Los textos recibidos no acreditan por sí mismos una ejecución.
 *
 * RELACIÓN CON O.R.I.O.N.:
 * Es el contrato de salida de CU-03. El futuro caso de uso deberá proporcionar
 * resultados y trazas reales; este DTO no simula los servicios pendientes.
 */
final readonly class ResultadoInferenciaDTO
{
    /** @var array<string, ResultadoDominio> Resultados originales con estados, evaluabilidad y evidencias. */
    public array $dominios;

    /** @var array<string, list<ReglaExperta>> Reglas originales declaradas para cada uno de los cinco dominios. */
    public array $reglasPorDominio;

    /** @var array<string, string> Textos originales de traza por identificador, en el orden recibido. */
    public array $trazas;

    /** @var array<string, string> Advertencias declaradas; no se crean advertencias clínicas. */
    public array $advertencias;

    /** @var array<string, string> Justificaciones originales de los dominios declarados como X. */
    public array $contradicciones;

    /**
     * Envuelve un perfil validado sin reconstruir ni modificar sus resultados.
     *
     * @param  SolicitudInferenciaDTO  $solicitud  Solicitud propietaria del resultado.
     * @param  VectorCognitivo  $perfilCognitivo  Perfil completo producido por Domain.
     * @param  string  $versionMotor  Versión del productor, explícita y sin defaults.
     * @param  array<string, list<ReglaExperta>>  $reglasPorDominio  Cinco listas explícitas, posiblemente vacías.
     * @param  array<string, string>  $trazas  Textos de ejecución recibidos; vacío conserva falta de trazas.
     * @param  array<string, string>  $advertencias  Limitaciones declaradas por el productor.
     *
     * @throws DomainException Por contexto, versión, reglas o metadatos inválidos.
     */
    public function __construct(
        /** Petición original. */ public SolicitudInferenciaDTO $solicitud,
        /** Vector original; sus entidades conservan todos los soportes. */ public VectorCognitivo $perfilCognitivo,
        /** Versión declarada del motor que produjo el perfil. */ public string $versionMotor,
        array $reglasPorDominio,
        array $trazas,
        array $advertencias = [],
    ) {
        $solicitud->exigirContexto($perfilCognitivo->memoria);
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
    }

    /**
     * Conserva textos identificados sin mantener referencias mutables del llamador.
     *
     * @param  array<string, string>  $textos  Metadatos textuales del productor.
     * @return array<string, string> Copia con el mismo contenido y orden.
     *
     * @throws DomainException Por identificador o texto inválido.
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
