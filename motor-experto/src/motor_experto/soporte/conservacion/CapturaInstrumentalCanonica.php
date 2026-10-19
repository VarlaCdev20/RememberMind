<?php

declare(strict_types=1);

namespace MotorExperto\soporte\conservacion;

use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Contrato escalar de fuentes instrumentales.
 * Proyecta columnas explícitas y sus tipos SQL sin almacenar datos ni aceptar objetos Eloquent.
 * Mantiene NULL, cero, false, decimales exactos y preguntas seleccionadas sin respuesta.
 */
final class CapturaInstrumentalCanonica
{
    private const INSTRUMENTO = ['cod_instrumento', 'codigo', 'nombre', 'tipo', 'version', 'descripcion', 'puntaje_maximo', 'estado'];

    private const PREGUNTA = ['cod_pregunta', 'cod_instrumento', 'codigo', 'enunciado', 'dominio', 'tipo_respuesta', 'puntaje_maximo', 'orden', 'estado'];

    private const OPCION = ['cod_opcion', 'cod_pregunta', 'nombre', 'valor', 'puntaje', 'orden', 'estado'];

    public function construir(array $fuentes, bool $conservarEpisodios = false, bool $conservarPerfil = false): array
    {
        if (($fuentes['disponible'] ?? null) !== true) {
            throw new DomainException('La captura canónica requiere fuentes autorizadas.');
        }
        $capturas = [];
        foreach ($fuentes['capturas'] as $a) {
            $fila = $this->fila($a, ['cod_aplicacion', 'cod_residente', 'cod_instrumento', 'codigo_instrumento', 'nombre_instrumento',
                'version_instrumento', 'cod_personal', 'autor', 'fecha_hora', 'estado_original', 'edad_en_aplicacion', 'fuente_edad', 'procedencia', 'motivos', 'extraccion']);
            $fila['instrumento_actual'] = $this->fila($a['instrumento_actual'], self::INSTRUMENTO);
            $fila['aplicacion_actual'] = $this->fila($a['aplicacion_actual'], ['cod_aplicacion', 'cod_residente', 'cod_instrumento', 'cod_personal',
                'cod_atencion', 'fecha_hora', 'puntaje_total', 'puntaje_maximo', 'clasificacion', 'interpretacion', 'estado', 'observacion',
                'cod_usuario_registro', 'registrado_en', 'cod_documento_clinico', 'localizador_fuente', 'cod_aplicacion_anterior']);
            $fila['documento_original'] = $this->fila($a['documento_original'] ?? null,
                ['cod_documento_clinico', 'cod_residente', 'cod_personal', 'hash_archivo', 'estado', 'fecha_hora']);
            if ($conservarEpisodios) {
                $fila['atencion_original'] = $this->fila($a['atencion_original'] ?? null, ['cod_atencion', 'cod_residente']);
            }
            if ($conservarPerfil) {
                $perfil = $a['perfil_captura_original'] ?? null;
                if ($perfil !== null && (! is_array($perfil) || array_diff(array_keys($perfil),
                    ['id', 'metodo', 'cod_instrumento', 'codigo_instrumento', 'version_instrumento', 'identidad_instrumento', 'selectores']))) {
                    throw new DomainException('El perfil conservado solo admite su contrato de captura explícito.');
                }
                $fila['perfil_captura_original'] = $perfil;
            }
            foreach ($fila['extraccion']['fuentes_entradas'] ?? [] as $entrada => $fuente) {
                if (($fuente['bruto'] ?? null) !== null && $fuente['campo'] === 'valor_numero') {
                    $fila['extraccion']['fuentes_entradas'][$entrada]['bruto'] = self::decimal($fuente['bruto'], 4, 8);
                } elseif (($fuente['bruto'] ?? null) !== null && $fuente['campo'] === 'valor_logico') {
                    $fila['extraccion']['fuentes_entradas'][$entrada]['bruto'] = CodecInstantanea::booleano($fuente['bruto']);
                }
            }
            $fila['respuestas_originales'] = [];
            foreach ($a['respuestas_originales'] as $r) {
                $respuesta = $this->fila($r, ['cod_respuesta', 'cod_aplicacion', 'cod_pregunta', 'cod_opcion', 'valor_numero', 'valor_texto',
                    'valor_logico', 'puntaje', 'observacion', 'codigo_pregunta', 'pregunta', 'dominio_original', 'tipo_respuesta', 'estado_pregunta_actual', 'coherente']);
                $respuesta['pregunta_actual'] = $this->fila($r['pregunta_actual'], self::PREGUNTA);
                $respuesta['opcion_actual'] = $this->fila($r['opcion_actual'], self::OPCION);
                $fila['respuestas_originales'][] = $respuesta;
            }
            usort($fila['respuestas_originales'], static fn ($a, $b) => $a['cod_respuesta'] <=> $b['cod_respuesta']);
            $fila['preguntas_declarativas_originales'] = [];
            foreach ($a['preguntas_declarativas_originales'] ?? [] as $id => $q) {
                $fila['preguntas_declarativas_originales'][$id] = $this->fila($q, self::PREGUNTA);
            }
            $fila['vinculos_preguntas_actuales'] = [];
            foreach ($a['vinculos_preguntas_actuales'] as $id => $v) {
                $fila['vinculos_preguntas_actuales'][$id] = $this->fila($v, ['selector', 'pertenece_al_instrumento', 'tiene_respuesta'])
                    + ['pregunta_actual' => $this->fila($v['pregunta_actual'], self::PREGUNTA)];
            }
            $capturas[] = $fila;
        }
        usort($capturas, static fn ($a, $b) => [$a['fecha_hora'], $a['cod_aplicacion']] <=> [$b['fecha_hora'], $b['cod_aplicacion']]);

        return self::ordenar(['disponible' => true, 'fecha_corte' => $fuentes['fecha_corte'], 'capturas' => $capturas, 'persistido' => false]);
    }

    private function fila(?array $fila, array $campos): ?array
    {
        if ($fila === null) {
            return null;
        }
        $r = array_intersect_key($fila, array_fill_keys($campos, true));
        foreach ($r as $campo => &$valor) {
            if ($valor === null) {
                continue;
            }
            if (in_array($campo, ['puntaje', 'puntaje_total', 'puntaje_maximo', 'valor_numero'], true)) {
                $valor = self::decimal($valor, $campo === 'valor_numero' ? 4 : 2, $campo === 'valor_numero' ? 8 : 6);
            } elseif ($campo === 'valor_logico') {
                $valor = CodecInstantanea::booleano($valor);
            } elseif ($campo === 'orden' && is_string($valor) && preg_match('/^-?\d+$/D', $valor)) {
                $valor = (int) $valor;
            }
        }
        unset($valor);

        return $r;
    }

    public static function decimal(mixed $valor, int $escala, int $enteros): string
    {
        // SQLite materializa DECIMAL como real; admitirlo solo si no requiere redondear su valor.
        if (is_float($valor)) {
            $decimal = is_finite($valor) ? number_format($valor, $escala, '.', '') : '';
            if ($decimal === '' || (float) $decimal !== $valor) {
                throw new DomainException('El decimal SQL no es representable sin pérdida en la escala contractual.');
            }
            $valor = $decimal;
        }
        if ((! is_int($valor) && ! is_string($valor))
            || ! preg_match('/^(-?)(\d+)(?:\.(\d+))?$/D', (string) $valor, $m)) {
            throw new DomainException('El decimal SQL original requiere una representación exacta.');
        }
        $entera = ltrim($m[2], '0') ?: '0';
        $fraccion = $m[3] ?? '';
        if (strlen($entera) > $enteros || trim(substr($fraccion, $escala), '0') !== '') {
            throw new DomainException('Decimal fuera de la precisión del contrato.');
        }
        $fraccion = str_pad(substr($fraccion, 0, $escala), $escala, '0');
        $signo = $entera === '0' && trim($fraccion, '0') === '' ? '' : $m[1];

        return $signo.$entera.'.'.$fraccion;
    }

    /** Ordena mapas; el orden de listas, motivos y reglas evaluadas conserva su significado. */
    public static function ordenar(mixed $v): mixed
    {
        if (is_array($v)) {
            if (! array_is_list($v)) {
                ksort($v, SORT_STRING);
            }
            foreach ($v as &$elemento) {
                $elemento = self::ordenar($elemento);
            }
            unset($elemento);
        } elseif (is_object($v) || is_resource($v) || (is_float($v) && ! is_finite($v))) {
            throw new DomainException('El DTO requiere escalares finitos y arreglos explícitos.');
        }

        return $v;
    }
}
