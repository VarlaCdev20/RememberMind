<?php

declare(strict_types=1);

namespace MotorExperto\investigacion\g09;

use DomainException;
use PDO;

/** Proyección descartable de capturas ficticias; nunca acepta un PDO institucional ni crea tablas de negocio. */
final class BaseInstrumentalExperimental
{
    public function abrir(array $capturas): PDO
    {
        $db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $columnas = [
            'instrumentos' => ['cod_instrumento', 'version'],
            'aplicaciones_instrumento' => ['cod_aplicacion', 'cod_instrumento', 'cod_residente', 'fecha_hora', 'estado', 'cod_personal'],
            'preguntas_instrumento' => ['cod_pregunta', 'cod_instrumento', 'tipo_respuesta'],
            'opciones_pregunta' => ['cod_opcion', 'cod_pregunta'],
            'respuestas_instrumento' => ['cod_respuesta', 'cod_aplicacion', 'cod_pregunta', 'valor_texto'],
        ];
        foreach ($columnas as $tabla => $campos) {
            $db->exec('CREATE TABLE '.$tabla.' ('.implode(', ', array_map(static fn ($c) => $c.' TEXT', $campos)).', PRIMARY KEY ('.$campos[0].'))');
        }
        if (array_diff(array_keys($capturas), array_keys($columnas))) {
            throw new DomainException('Fuente experimental no permitida.');
        }
        foreach ($capturas as $filas) {
            if (! is_array($filas) || ! array_is_list($filas)) {
                throw new DomainException('Colección de capturas experimental inválida.');
            }
        }
        $db->beginTransaction();
        foreach ($columnas as $tabla => $campos) {
            foreach ($capturas[$tabla] ?? [] as $fila) {
                $obligatorios = $tabla === 'aplicaciones_instrumento' ? array_diff($campos, ['cod_personal']) : $campos;
                if (! is_array($fila) || array_diff($obligatorios, array_keys($fila)) || array_diff(array_keys($fila), $campos)) {
                    throw new DomainException('Captura experimental incompleta o incompatible: '.$tabla);
                }
                foreach ($campos as $campo) {
                    if ($campo === 'cod_personal' && ($fila[$campo] ?? null) === null) {
                        continue; // Autor desconocido permanece NULL; no se completa con la cuenta ejecutora.
                    }
                    if ($fila[$campo] !== null && ! is_string($fila[$campo])) {
                        throw new DomainException('Tipo bruto experimental incompatible: '.$campo);
                    }
                    $prefijo = $campo === 'cod_residente' ? 'SINTETICO-' : ($campo === 'cod_pregunta' ? 'Q-' : 'SIM-');
                    if (str_starts_with($campo, 'cod_') && (! is_string($fila[$campo]) || ! str_starts_with($fila[$campo], $prefijo))) {
                        throw new DomainException('Identidad ajena al laboratorio: '.$campo);
                    }
                }
                $stmt = $db->prepare('INSERT INTO '.$tabla.' ('.implode(',', $campos).') VALUES ('.implode(',', array_fill(0, count($campos), '?')).')');
                $stmt->execute(array_map(static fn ($c) => $fila[$c] ?? null, $campos));
            }
        }
        $db->commit();

        return $db;
    }
}
