<?php

declare(strict_types=1);

namespace MotorExperto\soporte\conservacion;

use DomainException;

/** Forma y huella de datos ya adquiridos. No interpreta pruebas ni ejecuta reglas. */
final class IntegridadPreparacion
{
    public static function comprobar(array $contenido, string $huella): array
    {
        self::tipos($contenido);
        $contenido = CapturaInstrumentalCanonica::ordenar($contenido);
        if (! preg_match('/^[a-f0-9]{64}$/D', $huella) || ! hash_equals(CodecInstantanea::huella($contenido), $huella)) {
            throw new DomainException('La preparación no coincide con su huella esperada.');
        }

        return $contenido;
    }

    public static function campos(array $datos, array $campos): void
    {
        if (array_diff(array_keys($datos), $campos) || array_diff($campos, array_keys($datos))) {
            throw new DomainException('La preparación no conserva los campos de su contrato.');
        }
    }

    public static function contexto(mixed $datos): void
    {
        if (! is_array($datos)) {
            throw new DomainException('La preparación requiere su contexto original.');
        }
        self::campos($datos, ['evaluacion', 'residente', 'version', 'usuario_ejecucion', 'fecha_corte']);
        foreach (['evaluacion', 'residente', 'version', 'usuario_ejecucion'] as $campo) {
            if (! is_string($datos[$campo]) || $datos[$campo] === '' || strlen($datos[$campo]) > 20) {
                throw new DomainException('La preparación requiere identidades originales identificables.');
            }
        }
        if (strlen($datos['usuario_ejecucion']) > 20 || ! is_array($datos['fecha_corte'])) {
            throw new DomainException('La cuenta o fecha originales no corresponden al contrato.');
        }
        self::campos($datos['fecha_corte'], ['corte_utc', 'corte_zona', 'corte_offset']);
        if (! is_int($datos['fecha_corte']['corte_offset'])) {
            throw new DomainException('El desplazamiento temporal original debe conservar su tipo entero.');
        }
        CodecInstantanea::leerFecha('corte', (object) $datos['fecha_corte']);
    }

    private static function tipos(mixed $valor): void
    {
        if (is_array($valor)) {
            foreach ($valor as $hijo) {
                self::tipos($hijo);
            }
        } elseif (! is_null($valor) && ! is_string($valor) && ! is_int($valor) && ! is_bool($valor)
            && (! is_float($valor) || ! is_finite($valor))) {
            throw new DomainException('La preparación sólo admite datos planos tipados y números finitos.');
        }
    }
}
