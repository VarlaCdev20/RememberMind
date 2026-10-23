<?php

declare(strict_types=1);

namespace MotorExperto\soporte\conservacion;

use DomainException;

/** Identidad relacional adquirida. No interpreta fuentes ni completa datos históricos ausentes. */
final class MembresiasOriginalesPetersen
{
    public const FUENTES = [
        'cod_aplicacion' => 'aplicaciones_instrumento',
        'cod_control_cognitivo' => 'controles_cognitivos',
        'cod_control_iadl' => 'controles_funcionales_iadl',
        'cod_valoracion_psicologica' => 'valoraciones_psicologicas',
        'cod_antecedente' => 'antecedentes_clinicos',
        'cod_diagnostico' => 'diagnosticos',
        'cod_documento_clinico' => 'documentos_clinicos',
        'cod_valoracion_revisada' => 'valoraciones_petersen_originales',
    ];

    public const CAMPOS = ['cod_soporte_petersen', 'cod_valoracion_petersen', 'tipo_soporte', 'orden',
        ...self::CLAVES_FUENTES, 'metodo_original', 'huella_perfil_original'];

    public const CLAVES_FUENTES = ['cod_aplicacion', 'cod_control_cognitivo', 'cod_control_iadl',
        'cod_valoracion_psicologica', 'cod_antecedente', 'cod_diagnostico', 'cod_documento_clinico', 'cod_valoracion_revisada'];

    /** Valida el grafo original completo, incluidas las membresías de juicios excluidos. */
    public static function comprobar(array $contenido): void
    {
        $filas = $contenido['soportes_originales'];
        if (! is_array($filas) || ! array_is_list($filas)) {
            throw new DomainException('Los soportes originales requieren una colección ordenada.');
        }
        $miembros = [];
        foreach ($contenido['membresias_soportes'] as $m) {
            if (! is_array($m) || array_diff(array_keys($m), ['valoracion', 'soportes'])
                || count($m) !== 2 || ! is_string($m['valoracion'] ?? null) || ! is_array($m['soportes'] ?? null)
                || ! array_is_list($m['soportes']) || isset($miembros[$m['valoracion']])) {
                throw new DomainException('La membresía original tiene un propietario incompatible.');
            }
            $miembros[$m['valoracion']] = $m['soportes'];
        }
        $procedencias = [];
        foreach ($contenido['procedencia_valoraciones'] as $p) {
            if (! is_array($p) || ! is_string($p['cod_valoracion_petersen'] ?? null)
                || ($p['cod_residente'] ?? null) !== $contenido['contexto']['residente']
                || isset($procedencias[$p['cod_valoracion_petersen']])) {
                throw new DomainException('La procedencia original tiene otro residente o identidad repetida.');
            }
            $procedencias[$p['cod_valoracion_petersen']] = $p;
        }
        if (array_diff_key($miembros, $procedencias) || array_diff_key($procedencias, $miembros)) {
            throw new DomainException('Falta la membresía original de una valoración adquirida.');
        }
        $porJuicio = array_fill_keys(array_keys($miembros), []);
        $ids = $ordenes = [];
        foreach ($filas as $fila) {
            if (! is_array($fila) || array_diff(array_keys($fila), self::CAMPOS) || array_diff(self::CAMPOS, array_keys($fila))) {
                throw new DomainException('El soporte original no corresponde al diccionario nominal.');
            }
            foreach (['cod_soporte_petersen', 'cod_valoracion_petersen'] as $campo) {
                self::clave($fila[$campo]);
            }
            $id = $fila['cod_soporte_petersen'];
            $propietario = $fila['cod_valoracion_petersen'];
            if (isset($ids[$id]) || ! isset($porJuicio[$propietario]) || ! is_int($fila['orden']) || $fila['orden'] < 0
                || isset($ordenes[$propietario][$fila['orden']])
                || ! in_array($fila['tipo_soporte'], ['HISTORIA_PREVIA', 'CORROBORACION', 'MEDICION', 'IADL',
                    'DIFERENCIAL', 'CALIDAD', 'DOMINIO_REVISADO'], true)) {
                throw new DomainException('Identidad, propietario, tipo u orden original del soporte incompatible.');
            }
            $fuentes = [];
            foreach (self::FUENTES as $fk => $tabla) {
                if ($fila[$fk] !== null) {
                    self::clave($fila[$fk]);
                    $fuentes[$fk] = $fila[$fk];
                }
            }
            if (count($fuentes) !== 1) {
                throw new DomainException('El soporte original requiere una única FK de fuente.');
            }
            $medicion = $fila['tipo_soporte'] === 'MEDICION';
            if (($medicion && ($fila['cod_aplicacion'] === null || ! is_string($fila['metodo_original'])
                    || trim($fila['metodo_original']) === '' || strlen($fila['metodo_original']) > 80
                    || ! is_string($fila['huella_perfil_original']) || ! preg_match('/^[a-f0-9]{64}$/D', $fila['huella_perfil_original'])))
                || (! $medicion && ($fila['metodo_original'] !== null || $fila['huella_perfil_original'] !== null))
                || ($fila['tipo_soporte'] === 'IADL' && $fila['cod_control_iadl'] === null)
                || (($fila['tipo_soporte'] === 'DOMINIO_REVISADO') !== ($fila['cod_valoracion_revisada'] !== null))) {
                throw new DomainException('El soporte original no conserva su vínculo tipado.');
            }
            $ids[$id] = true;
            $ordenes[$propietario][$fila['orden']] = true;
            $porJuicio[$propietario][] = $fila;
        }
        foreach ($miembros as $juicio => $esperados) {
            // El orden de la colección adquirida es significativo; no se reordena para aceptar una discrepancia.
            $actuales = array_column($porJuicio[$juicio], 'cod_soporte_petersen');
            $orden = array_column($porJuicio[$juicio], 'orden');
            $ascendente = $orden;
            sort($ascendente, SORT_NUMERIC);
            if ($actuales !== $esperados || $orden !== $ascendente) {
                throw new DomainException('Los soportes originales discrepan de la membresía adquirida.');
            }
        }
        $secuencia = array_merge([], ...array_values($miembros));
        if (array_column($filas, 'cod_soporte_petersen') !== $secuencia) {
            throw new DomainException('La colección original no respeta el orden global de las membresías.');
        }
        foreach ($contenido['valoraciones'] as $juicio) {
            $codigo = $juicio['origen']['registro'] ?? null;
            if (! is_string($codigo)) {
                throw new DomainException('El juicio interpretado requiere una identidad original.');
            }
            if (! isset($porJuicio[$codigo])) {
                throw new DomainException('El juicio interpretado carece de su membresía original.');
            }
            $fuentes = array_values(array_filter($porJuicio[$codigo], static fn ($s) => $s['tipo_soporte'] !== 'DOMINIO_REVISADO'));
            if (count($fuentes) !== count($juicio['soportes'])) {
                throw new DomainException('La lista de fuentes no coincide con sus soportes originales.');
            }
            foreach ($juicio['soportes'] as $i => $fuente) {
                $fila = $fuentes[$i];
                $fk = array_key_first(array_filter(array_intersect_key($fila, self::FUENTES), static fn ($v) => $v !== null));
                if (($fuente['registro'] ?? null) !== $fila[$fk] || ($fuente['tabla'] ?? null) !== self::FUENTES[$fk]
                    || ($fuente['tipo'] ?? null) !== $fila['tipo_soporte']) {
                    throw new DomainException('La fuente interpretada apunta a otro soporte original.');
                }
                if ($fila['tipo_soporte'] === 'MEDICION') {
                    $perfil = $fuente['datos_originales']['captura_original']['perfil_captura_original'] ?? null;
                    if (! is_array($perfil) || ($perfil['metodo'] ?? null) !== $fila['metodo_original']
                        || ! hash_equals($fila['huella_perfil_original'], CodecInstantanea::huella(CapturaInstrumentalCanonica::ordenar($perfil)))) {
                        throw new DomainException('El método o huella original no corresponde al perfil instrumental adquirido.');
                    }
                }
            }
            $revisados = array_values(array_filter($porJuicio[$codigo], static fn ($s) => $s['tipo_soporte'] === 'DOMINIO_REVISADO'));
            $originales = [];
            foreach ($revisados as $fila) {
                $referencia = $procedencias[$fila['cod_valoracion_revisada']] ?? null;
                if ($referencia === null || isset($originales[$referencia['elemento']])) {
                    throw new DomainException('El dominio revisado no identifica un juicio adquirido único.');
                }
                $originales[$referencia['elemento']] = 'valoraciones_petersen_originales:'.$fila['cod_valoracion_revisada'];
            }
            if (CapturaInstrumentalCanonica::ordenar($originales) !== CapturaInstrumentalCanonica::ordenar($juicio['dominios_revisados'])) {
                throw new DomainException('Los dominios revisados carecen del vínculo original exacto.');
            }
        }
    }

    private static function clave(mixed $valor): void
    {
        if (! is_string($valor) || trim($valor) === '' || strlen($valor) > 20) {
            throw new DomainException('La referencia original requiere su código completo.');
        }
    }
}
