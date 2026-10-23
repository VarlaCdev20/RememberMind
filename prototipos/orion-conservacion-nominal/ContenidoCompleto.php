<?php

declare(strict_types=1);

namespace OrionNominal;

use DomainException;
use MotorExperto\soporte\conservacion\CapturaInstrumentalCanonica as C;

/** Proyección relacional reversible de repeticiones exactas. No decide estados ni ejecuta reglas. */
final class ContenidoCompleto
{
    public static function proyectar(array $d): array
    {
        $i = &$d['instrumental']['contenido'];
        $p = &$d['petersen']['contenido'];
        $perfil = &$i['salida']['perfil_instrumental'];
        self::iguales($perfil['capturas_originales'], $i['capturas']);
        unset($perfil['capturas_originales']);
        foreach ($perfil['dominios'] as $codigo => &$dominio) {
            self::iguales($dominio['integracion'], $i['salida']['resultado']['perfil'][$codigo]);
            unset($dominio['integracion']);
            foreach ($dominio['componentes'] as $componente => &$detalle) {
                self::iguales($detalle['observaciones'], $i['salida']['resultado']['observaciones_por_componente'][$componente] ?? []);
                unset($detalle['observaciones']);
            }
            unset($detalle);
        }
        unset($dominio);
        self::iguales($p['salida']['adquisicion'], $p['adquisicion']);
        unset($p['salida']['adquisicion']);
        foreach (['dominios', 'requisitos'] as $grupo) {
            foreach ($p['salida'][$grupo] as &$estado) {
                $indices = [];
                foreach ($estado['valoraciones'] as $juicio) {
                    $encontrados = [];
                    foreach ($p['valoraciones'] as $indice => $original) {
                        unset($original['elemento']);
                        if ($juicio === $original) {
                            $encontrados[] = $indice;
                        }
                    }
                    if (count($encontrados) !== 1) {
                        throw new DomainException('La salida no identifica un único juicio original conservado.');
                    }
                    $indices[] = $encontrados[0];
                }
                unset($estado['valoraciones']);
                $estado['indices_valoraciones'] = $indices;
            }
            unset($estado);
        }

        return C::ordenar($d);
    }

    public static function reconstruir(array $d): array
    {
        $i = &$d['instrumental']['contenido'];
        $p = &$d['petersen']['contenido'];
        $perfil = &$i['salida']['perfil_instrumental'];
        if (array_key_exists('capturas_originales', $perfil) || array_key_exists('adquisicion', $p['salida'])) {
            throw new DomainException('La retención contiene una copia no prevista por su contrato.');
        }
        $perfil['capturas_originales'] = $i['capturas'];
        foreach ($perfil['dominios'] as $codigo => &$dominio) {
            if (array_key_exists('integracion', $dominio)) {
                throw new DomainException('La integración tiene dos propietarios históricos.');
            }
            $dominio['integracion'] = $i['salida']['resultado']['perfil'][$codigo]
                ?? throw new DomainException('Falta la integración histórica de un dominio.');
            foreach ($dominio['componentes'] as $componente => &$detalle) {
                if (array_key_exists('observaciones', $detalle)) {
                    throw new DomainException('Las observaciones tienen dos propietarios históricos.');
                }
                $detalle['observaciones'] = $i['salida']['resultado']['observaciones_por_componente'][$componente] ?? [];
            }
            unset($detalle);
        }
        unset($dominio);
        $p['salida']['adquisicion'] = $p['adquisicion'];
        foreach (['dominios', 'requisitos'] as $grupo) {
            foreach ($p['salida'][$grupo] as &$estado) {
                if (array_key_exists('valoraciones', $estado) || ! is_array($estado['indices_valoraciones'] ?? null)
                    || ! array_is_list($estado['indices_valoraciones'])) {
                    throw new DomainException('La membresía de juicios resueltos no es íntegra.');
                }
                $estado['valoraciones'] = [];
                foreach ($estado['indices_valoraciones'] as $indice) {
                    if (! is_int($indice) || ! array_key_exists($indice, $p['valoraciones'])) {
                        throw new DomainException('La membresía referencia un juicio no conservado.');
                    }
                    $juicio = $p['valoraciones'][$indice];
                    unset($juicio['elemento']);
                    $estado['valoraciones'][] = $juicio;
                }
                unset($estado['indices_valoraciones']);
            }
            unset($estado);
        }

        return C::ordenar($d);
    }

    /** Sólo prepara las entradas del replay retenido. La recuperación SQL no llama a este método. */
    public static function entradasReplay(array $d): array
    {
        $i = $d['instrumental']['contenido'];
        $p = $d['petersen']['contenido'];
        unset($i['salida'], $i['fuente_conceptual'], $p['salida']);
        $retirar = static function (array &$captura): void {
            $captura['extraccion_adquirida'] = ! array_key_exists('extraccion', $captura) ? 'AUSENTE'
                : ($captura['extraccion'] === null ? 'NULL' : 'CALCULADA');
            unset($captura['extraccion']);
        };
        foreach ($i['capturas']['capturas'] as &$captura) {
            $retirar($captura);
        }
        unset($captura);
        foreach ($p['valoraciones'] as &$juicio) {
            foreach ($juicio['soportes'] as &$soporte) {
                if (isset($soporte['datos_originales']['captura_original'])) {
                    $retirar($soporte['datos_originales']['captura_original']);
                }
            }
            unset($soporte);
        }
        unset($juicio);

        return ['instrumental' => $i, 'petersen' => $p];
    }

    private static function iguales(mixed $a, mixed $b): void
    {
        if ($a !== $b) {
            throw new DomainException('La repetición del DTO discrepa de su propietario original.');
        }
    }
}
