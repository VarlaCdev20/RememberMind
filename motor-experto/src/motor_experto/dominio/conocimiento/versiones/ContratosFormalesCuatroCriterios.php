<?php

namespace MotorExperto\dominio\conocimiento\versiones;

use DomainException;
use JsonException;

/**
 * Catálogo de contratos de los cuatro criterios candidatos.
 * SOLO CONSULTA: no es PaqueteConocimiento, no configura reglas ni evalúa pacientes.
 */
final class ContratosFormalesCuatroCriterios
{
    public const VERSION = 'FC4-20261016-v1.0-TECNICA';

    /** @return array<string, mixed> */
    public function consultar(): array
    {
        $path = dirname(__DIR__, 6).'/docs/sistema-experto/FORMALIZACION_COG4_V1.json';
        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new DomainException('No se puede recuperar el catálogo documental.');
        }
        try {
            $datos = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new DomainException('Catálogo documental JSON inválido.', 0, $e);
        }
        if (! is_array($datos)
            || ($datos['version'] ?? '') !== self::VERSION
            || ($datos['conocimiento_clinico_activo'] ?? true) !== false
            || ($datos['autorizacion_inferencial'] ?? true) !== false) {
            throw new DomainException('Catálogo de cuatro criterios fuera de contrato.');
        }
        $criterios = $datos['criterios'] ?? null;
        if (! is_array($criterios) || array_column($criterios, 'codigo') !==
            ['COG-ATE', 'COG-EJE', 'COG-LEN', 'COG-VIS']) {
            throw new DomainException('Inventario de criterios incorrecto.');
        }
        foreach ($criterios as $criterio) {
            if (($criterio['estado_tecnico'] ?? '') !== 'INACTIVO'
                || ($criterio['dictamen_firmado'] ?? true) !== false
                || ($criterio['resultados_clinicos'] ?? null) !== []) {
                throw new DomainException('Criterio clínico no autorizado.');
            }
            foreach ($criterio['subcomponentes'] as $subcomponente) {
                if (($subcomponente['regla']['ejecutable'] ?? true) !== false) {
                    throw new DomainException('Regla clínica candidata habilitada indebidamente.');
                }
            }
        }
        return $datos;
    }
}
