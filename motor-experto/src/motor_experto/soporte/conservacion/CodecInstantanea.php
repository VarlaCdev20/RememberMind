<?php

declare(strict_types=1);

namespace MotorExperto\soporte\conservacion;

use BackedEnum;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;

/**
 * TÍTULO:
 * Conservación exacta de valores y fechas.
 *
 * CAPA:
 * Infrastructure — Persistence.
 *
 * ¿PARA QUÉ SIRVE?
 * Evita coerciones al conservar instantáneas.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * Escalares, fechas y objetos públicos de Domain.
 *
 * ¿QUÉ HACE?
 * Codifica columnas fijas y calcula una huella tipada ordenada.
 *
 * ¿QUÉ DEVUELVE?
 * Valores originales, fechas originales y hashes SHA-256.
 *
 * ¿QUÉ NO DEBE HACER?
 * No serializa objetos, usa JSON ni interpreta datos clínicos.
 */
final class CodecInstantanea
{
    /**
     * @param  string  $p  Prefijo fijo de columnas definido por el diccionario.
     * @param  string|int|float|bool|null  $v  Escalar original sin coerción.
     * @return array<string, mixed> Una sola columna de valor activa o ninguna para NULL.
     *
     * @throws DomainException Por texto no representable o real no finito.
     */
    public static function escalar(string $p, string|int|float|bool|null $v): array
    {
        $r = array_fill_keys(array_map(fn ($c) => $p.'_'.$c, ['texto', 'entero', 'real_ieee754', 'logico']), null);
        $tipo = match (true) {
            $v === null => 'NULL', is_string($v) => 'STRING', is_int($v) => 'INT', is_float($v) => 'FLOAT', default => 'BOOL',
        };
        if (is_string($v)) {
            self::texto($v);
        }
        if (is_float($v) && ! is_finite($v)) {
            throw new DomainException('No se conservan números no finitos.');
        }
        $r[$p.'_tipo'] = $tipo;
        if ($tipo !== 'NULL') {
            $c = ['STRING' => 'texto', 'INT' => 'entero', 'FLOAT' => 'real_ieee754', 'BOOL' => 'logico'][$tipo];
            $r[$p.'_'.$c] = is_float($v) ? bin2hex(pack('E', $v)) : $v;
        }

        return $r;
    }

    /**
     * @param  string  $p  Prefijo fijo del diccionario.
     * @param  object  $r  Fila SQL con todas las columnas escalares.
     * @return string|int|float|bool|null Original tipado con exclusión de columnas ajenas.
     *
     * @throws DomainException Por discriminador, tipo, valor o forma corruptos.
     */
    public static function leerEscalar(string $p, object $r): string|int|float|bool|null
    {
        $tipo = $r->{$p.'_tipo'};
        $v = match ($tipo) {
            'NULL' => null,
            'STRING' => $r->{$p.'_texto'},
            'INT' => self::entero($r->{$p.'_entero'}),
            'BOOL' => self::booleano($r->{$p.'_logico'}),
            'FLOAT' => self::real($r->{$p.'_real_ieee754'}),
            default => throw new DomainException('Tipo escalar conservado desconocido.'),
        };
        $esperado = self::escalar($p, $v);
        foreach ($esperado as $campo => $dato) {
            if (($dato === null) !== ($r->{$campo} === null)) {
                throw new DomainException('Columnas escalares incompatibles.');
            }
        }

        return $v;
    }

    /**
     * Acepta solo la materialización booleana de los motores soportados.
     *
     * @param  mixed  $v  Valor SQL original.
     * @return bool Valor conocido exacto, sin interpretar ausencia como false.
     *
     * @throws DomainException Por valor fuera de bool/0/1.
     */
    public static function booleano(mixed $v): bool
    {
        return match ($v) {
            true, 1, '1' => true, false, 0, '0' => false,
            default => throw new DomainException('Booleano conservado inválido.'),
        };
    }

    private static function entero(mixed $v): int
    {
        if (is_int($v)) {
            return $v;
        }
        if (! is_string($v) || ! preg_match('/^-?(0|[1-9][0-9]*)$/D', $v)
            || filter_var($v, FILTER_VALIDATE_INT) === false) {
            throw new DomainException('Entero conservado inválido o fuera de rango.');
        }

        return (int) $v;
    }

    private static function real(string $hex): float
    {
        if (strlen($hex) !== 16 || ! ctype_xdigit($hex)) {
            throw new DomainException('Representación IEEE-754 inválida.');
        }

        return unpack('E', hex2bin($hex))[1];
    }

    /**
     * @param  string  $p  Prefijo temporal fijo del diccionario.
     * @param  DateTimeImmutable  $v  Fecha original que no se modifica.
     * @return array<string, string|int> Instante UTC y zona/offset originales.
     *                                   No produce efectos ni excepciones para la fecha tipada recibida.
     */
    public static function fecha(string $p, DateTimeImmutable $v): array
    {
        return [$p.'_utc' => $v->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s.u'),
            $p.'_zona' => $v->getTimezone()->getName(), $p.'_offset' => $v->getOffset()];
    }

    /**
     * Reconstruye y contrasta la zona explícita; nunca usa la zona global.
     *
     * @param  string  $p  Prefijo temporal fijo del diccionario.
     * @param  object  $r  Fila con instante, zona y offset originales.
     * @return DateTimeImmutable Fecha exacta de la instantánea.
     *
     * @throws DomainException Por fecha, calendario u offset incompatibles.
     * @throws \DateInvalidTimeZoneException Por zona no representable.
     */
    public static function leerFecha(string $p, object $r): DateTimeImmutable
    {
        $utc = $r->{$p.'_utc'};
        $zona = $r->{$p.'_zona'};
        if (! is_string($utc) || ! is_string($zona) || $zona === ''
            || ! preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})(?:\.(\d{1,6}))?$/D', $utc, $partes)) {
            throw new DomainException('Fecha conservada incompleta o inválida.');
        }
        $normal = $partes[1].'.'.str_pad($partes[2] ?? '', 6, '0');
        $original = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s.u', $normal, new DateTimeZone('UTC'));
        if ($original === false || $original->format('Y-m-d H:i:s.u') !== $normal) {
            throw new DomainException('Calendario conservado inválido.');
        }
        $v = $original->setTimezone(new DateTimeZone($zona));
        if ($v->getOffset() !== self::entero($r->{$p.'_offset'})) {
            throw new DomainException('Zona y offset conservados incompatibles.');
        }

        return $v;
    }

    /**
     * Hash de contenido, tipos y orden; no es un almacén alternativo.
     *
     * @param  mixed  $v  Escalares, arrays y objetos públicos de Domain sin ciclos.
     * @return string SHA-256 de la representación canónica tipada.
     *
     * @throws DomainException Por objeto ajeno, valor no finito o texto no representable.
     */
    public static function huella(mixed $v): string
    {
        return hash('sha256', self::canonico($v));
    }

    private static function canonico(mixed $v): string
    {
        if ($v instanceof DateTimeImmutable) {
            return 'D'.self::canonico(self::fecha('f', $v));
        }
        if ($v instanceof BackedEnum) {
            return 'E'.self::identidadContractual($v::class).self::canonico($v->value);
        }
        if (is_object($v)) {
            if (! str_starts_with($v::class, 'MotorExperto\\dominio\\orion\\')) {
                throw new DomainException('Solo se firman objetos de Domain.');
            }

            return 'O'.self::identidadContractual($v::class).self::canonico(get_object_vars($v));
        }
        if (is_array($v)) {
            $s = 'A'.count($v).':';
            foreach ($v as $k => $dato) {
                $s .= self::canonico($k).self::canonico($dato);
            }

            return $s;
        }
        if (is_string($v)) {
            self::texto($v);

            return 'S'.strlen($v).':'.$v;
        }

        return match (true) {
            $v === null => 'N', is_bool($v) => $v ? 'B1' : 'B0', is_int($v) => 'I'.$v.';',
            is_float($v) && is_finite($v) => 'F'.bin2hex(pack('E', $v)),
            default => throw new DomainException('Tipo no conservable.'),
        };
    }

    /** Identidad del formato histórico, independiente de la ubicación PHP; no carga ni declara clases antiguas. */
    private static function identidadContractual(string $clase): string
    {
        $prefijo = 'MotorExperto\\dominio\\orion\\';

        return str_starts_with($clase, $prefijo) ? 'App\\SistemaExperto\\Domain\\'.substr($clase, strlen($prefijo)) : $clase;
    }

    private static function texto(string $v): void
    {
        if (preg_match('//u', $v) !== 1 || str_contains($v, "\0")) {
            throw new DomainException('La conservación requiere texto UTF-8 sin bytes NUL.');
        }
    }
}
