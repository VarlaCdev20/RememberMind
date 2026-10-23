<?php

declare(strict_types=1);

namespace OrionNominal;

use DomainException;

/** Contrato físico de ensayo cerrado; nunca se deriva el tipo de los datos de un residente. */
final class ContratoNominal
{
    public const VERSION = 'ENTRADAS-NOMINALES-1';

    public static function mapa(): array
    {
        $mapa = require dirname(__DIR__).'/orion-conservacion-0_2/mapa-cerrado.php';
        $soporte = &$mapa['modelos']['m022']['estructura']['campos']['datos_originales']['campos']['captura_original'];
        $mapa['modelos']['m028'] = ['tabla' => 'capturas_adquiridas', 'padre' => 'm022',
            'ruta' => '/petersen/valoraciones/soportes/datos_originales/captura_original', 'estructura' => $soporte];
        $soporte = ['clase' => 'registro', 'modelo' => 'm028', 'identidad' => 'CAPTURA_SOPORTE'];
        unset($soporte);
        foreach (['m023', 'm024', 'm025', 'm026', 'm027'] as $id) {
            $mapa['modelos'][$id]['padre'] = 'm028';
        }
        $tablas = ['m001' => 'entradas_institucionales', 'm002' => 'capturas_adquiridas',
            'm003' => 'motivos_captura', 'm004' => 'selectores_captura', 'm005' => 'preguntas_captura',
            'm006' => 'respuestas_captura', 'm007' => 'vinculos_pregunta_captura',
            'm008' => 'contratos_relacion_adquiridos', 'm009' => 'modalidades_contrato_adquirido',
            'm010' => 'vinculos_instrumentales_adquiridos', 'm011' => 'mediciones_requeridas_perfil',
            'm012' => 'predicados_perfil_adquirido', 'm013' => 'exclusiones_adquisicion',
            'm014' => 'constancias_documentales_adquiridas', 'm015' => 'contratos_relacion_adquiridos',
            'm016' => 'modalidades_contrato_adquirido', 'm017' => 'membresias_juicio_adquiridas',
            'm018' => 'miembros_soporte_adquiridos', 'm019' => 'procedencias_juicio_adquiridas',
            'm020' => 'juicios_petersen_adquiridos', 'm021' => 'dominios_revisados_adquiridos',
            'm022' => 'fuentes_soporte_adquiridas', 'm023' => 'motivos_captura', 'm024' => 'selectores_captura',
            'm025' => 'preguntas_captura', 'm026' => 'respuestas_captura', 'm027' => 'vinculos_pregunta_captura',
            'm028' => 'capturas_adquiridas'];
        foreach ($mapa['modelos'] as $id => &$modelo) {
            $modelo['tabla'] = $tablas[$id] ?? 'conocimiento_'.substr($id, 2);
            $modelo['fk_propietario'] = $id === 'm028' ? 'cod_fuente_soporte'
                : ($id === 'm002' ? 'cod_entrada_institucional' : 'cod_propietario');
        }
        unset($modelo);

        return $mapa;
    }

    /** Tipos provenientes de contratos PHP y columnas operativas, no de frecuencias del corpus. */
    public static function tipos(string $modelo, string $ruta): array
    {
        $nombre = basename($ruta);
        if ($ruta === '') {
            return ['STRING']; // Motivo, modalidad, predicado, dominio revisado o ID de soporte nominal.
        }
        if (str_starts_with($ruta, '/comprobaciones/') || in_array($nombre, ['disponible', 'persistido',
            'solo_pruebas_tecnicas', 'conservacion_completa', 'fuentes_originales_mutables', 'reproduce_adquisicion_sql',
            'revalida_bytes_documentales', 'revalida_permisos_historicos', 'habilita_participacion', 'coherente',
            'pertenece_al_instrumento', 'tiene_respuesta', 'verificado', 'vigente'], true)) {
            return ['BOOL'];
        }
        if (in_array($nombre, ['orden', 'edad_en_aplicacion', 'corte_offset', 'valoraciones_al_corte'], true)) {
            return ['INT'];
        }
        if ($nombre === 'forma') {
            // El contrato instrumental admite formas numeradas y nominales (por ejemplo 5 y A).
            return ['STRING', 'INT'];
        }
        if ($nombre === 'valor_logico') {
            // getRawOriginal: SQL SQLite 0/1, SQL PostgreSQL bool; la captura canónica usa bool.
            return ['BOOL', 'INT'];
        }
        if (in_array($nombre, ['valor_numero', 'puntaje', 'puntaje_total', 'puntaje_maximo'], true)) {
            // DECIMAL SQL se conserva como cadena; las fuentes Petersen también admiten números PHP originales.
            return ['STRING', 'INT', 'FLOAT'];
        }

        return ['STRING'];
    }

    public static function columna(string $ruta, string $modelo): string
    {
        if ($ruta === '') {
            return match ($modelo) {
                'm003', 'm023' => 'motivo',
                'm009', 'm016' => 'modalidad',
                'm012' => 'predicado',
                'm018' => 'cod_soporte_valoracion',
                'm021' => 'dominio_revisado',
                default => 'registro',
            };
        }
        $ruta = str_replace(['/instrumental/cabecera_conocimiento', '/petersen/gobierno/cabecera',
            '/instrumental/conocimiento', '/petersen/gobierno/conocimiento', '/perfil_captura_original',
            '/identidad_instrumento', '/pregunta_actual', '/opcion_actual', '/aplicacion_actual', '/instrumento_actual'],
            ['/i_cabecera', '/p_cabecera', '/i_k', '/p_k', '/perfil', '/identidad',
                '/pregunta', '/opcion', '/aplicacion', '/instrumento'], $ruta);
        $nombre = trim(str_replace('/', '_', $ruta), '_');
        if (! preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $nombre) || strlen($nombre) > 49) {
            throw new DomainException('Nombre nominal pendiente de definir: '.$ruta);
        }

        return strtolower($nombre);
    }

    public static function campos(array $estructura, string $modelo, string $ruta = ''): array
    {
        $campos = [$ruta => ['columna' => $estructura['columna'] ?? self::columna($ruta, $modelo), 'clase' => $estructura['clase']]];
        if ($estructura['clase'] === 'escalar') {
            $campos[$ruta]['tipos'] = $estructura['tipos'] ?? self::tipos($modelo, $ruta);
        } elseif ($estructura['clase'] === 'objeto') {
            foreach ($estructura['campos'] as $nombre => $hijo) {
                $campos += self::campos($hijo, $modelo, $ruta.'/'.$nombre);
            }
        }

        return $campos;
    }
}
