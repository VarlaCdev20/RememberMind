<?php

declare(strict_types=1);

namespace MotorExperto\dominio\instrumentos;

use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Comprobación de fuentes y completitud instrumental.
 *
 * Expone los faltantes del extractor y las restricciones del método ya ejecutado.
 * Cuenta datos tipados, incluidos cero y «No»; no acredita su veracidad, adopta
 * instrumentos ni convierte completitud de captura en interpretación clínica.
 */
final class ComprobacionAdquisicionInstrumental
{
    public function comprobar(array $capturas): array
    {
        if (($capturas['disponible'] ?? false) !== true) {
            throw new DomainException('La comprobación requiere acceso autorizado a las fuentes.');
        }

        $metodos = (new RegistroMetodosInstrumentales)->metodos();
        $aplicaciones = $contadas = [];
        $requeridas = $disponibles = $sinExtraccion60 = $observacionales60 = 0;
        foreach ($capturas['capturas'] as $a) {
            $id = $a['cod_aplicacion'];
            if (isset($contadas[$id])) {
                throw new DomainException('Una aplicación no puede contarse dos veces en la comprobación.');
            }
            $contadas[$id] = true;
            $edad = $a['edad_en_aplicacion'] ?? $a['extraccion']['captura']['edad_anios'] ?? null;
            $es60 = is_int($edad) && $edad >= 60;
            $e = $a['extraccion'] ?? null;
            $esObservacion = ($a['instrumento_actual']['tipo'] ?? null) === 'OBSERVACIONAL';
            if ($esObservacion && $e !== null) {
                throw new DomainException('Una observación clínica no acredita una aplicación de prueba estandarizada.');
            }
            $meta = $e === null ? null : ($metodos[$e['metodo']] ?? null);
            if ($e !== null && $meta === null) {
                throw new DomainException('La extracción refiere un método instrumental no registrado.');
            }
            $identidad = $entradas = $calculos = [];
            if ($e !== null) {
                foreach ($meta['identidad'] as $campo => $requerido) {
                    $declarado = $e['identidad_declarada'][$campo] ?? null;
                    $identidad[$campo] = ['requerido_por_metodo' => $requerido, 'declarado' => $declarado,
                        'estado' => $declarado === null ? 'SIN_DECLARACION'
                            : ($declarado === $requerido ? 'COINCIDE_CON_METODO' : 'INCOMPATIBLE_CON_METODO')];
                }
                foreach ($meta['entradas'] as $entrada => $tipo) {
                    $fuente = $e['fuentes_entradas'][$entrada] ?? null;
                    $selector = $a['vinculos_preguntas_actuales'][$entrada]['selector'] ?? null;
                    $estado = match (true) {
                        in_array('SIN_SELECTOR:'.$entrada, $e['motivos_captura'] ?? [], true) => 'SIN_SELECTOR',
                        $fuente === null => 'SIN_RESPUESTA_COMPATIBLE',
                        ($fuente['valor'] ?? null) === null => 'VALOR_AUSENTE_O_TIPO_INVALIDO',
                        default => 'DATO_TIPADO_DISPONIBLE',
                    };
                    $entradas[$entrada] = ['tipo' => $tipo, 'campo' => $tipo === 'LOGICO' ? 'valor_logico' : 'valor_numero',
                        'cod_pregunta' => $fuente['pregunta'] ?? $selector['cod_pregunta'] ?? null,
                        'cod_respuesta' => $fuente['respuesta'] ?? null, 'estado' => $estado];
                    if ($es60) {
                        $requeridas++;
                        $disponibles += (int) ($estado === 'DATO_TIPADO_DISPONIBLE');
                    }
                }
                foreach ($e['normalizacion']['componentes'] as $componente => $resultado) {
                    foreach ($resultado['medidas'] ?? [$resultado] as $medida) {
                        $calculos[$componente][] = ['estado' => $medida['estado'], 'motivos' => $medida['motivos'] ?? []];
                    }
                }
            } elseif ($es60) {
                if ($esObservacion) {
                    $observacionales60++;
                } else {
                    $sinExtraccion60++;
                }
            }
            $procedencia = $a['procedencia'] ?? [];
            $aplicaciones[] = ['cod_aplicacion' => $id, 'cod_instrumento' => $a['cod_instrumento'] ?? null,
                'version_instrumento' => $a['version_instrumento'] ?? null, 'edad_en_aplicacion' => $edad,
                'pertenece_a_poblacion_60_o_mas' => is_int($edad) && $edad >= 0 ? $es60 : null,
                'naturaleza_fuente' => $esObservacion ? 'OBSERVACION_CLINICA' : 'REGISTRO_INSTRUMENTAL',
                'estado_normalizacion' => $esObservacion ? 'NO_APLICA_A_OBSERVACION' : ($e === null ? 'SIN_EXTRACCION' : 'CONSULTAR_CALCULOS'),
                'perfil' => $e['perfil'] ?? null, 'metodo' => $e['metodo'] ?? null, 'fuente_metodo' => $meta['fuente'] ?? null,
                'motivos_lectura' => $a['motivos'] ?? [], 'identidad' => $identidad, 'entradas' => $entradas,
                'motivos_captura' => $e['motivos_captura'] ?? [], 'aplicabilidad' => $e['aplicabilidad_geriatrica'] ?? null,
                'calculos' => $calculos,
                'procedencia' => ['cod_personal_aplicador' => $a['cod_personal'] ?? null,
                    'fecha_hora_clinica' => $a['fecha_hora'] ?? null,
                    'cod_usuario_registro' => $procedencia['cod_usuario_registro'] ?? null,
                    'registrado_en' => $procedencia['registrado_en'] ?? null,
                    'estado_documento' => $procedencia['estado_documento'] ?? 'SIN_VINCULO_DOCUMENTAL',
                    'cod_documento_clinico' => $procedencia['cod_documento_clinico'] ?? null,
                    'tiene_localizador' => is_string($procedencia['localizador_fuente'] ?? null)
                        && trim($procedencia['localizador_fuente']) !== ''],
            ];
        }

        return ['contrato' => 'COMPROBACION-ADQUISICION-2', 'aplicaciones' => $aplicaciones,
            'metricas_entradas_60_o_mas' => ['entradas_requeridas' => $requeridas, 'entradas_con_dato_tipado' => $disponibles,
                'entradas_faltantes_o_invalidas' => $requeridas - $disponibles,
                'porcentaje_completitud' => $requeridas === 0 ? null : round(100 * $disponibles / $requeridas, 2),
                'aplicaciones_sin_extraccion' => $sinExtraccion60,
                'aplicaciones_observacionales_sin_normalizacion' => $observacionales60,
                'denominador' => 'ENTRADAS_DE_EXTRACCIONES_DISPONIBLES_EN_APLICACIONES_60_O_MAS',
                'limite' => 'No estima entradas de instrumentos sin perfil; un dato tipado puede ser inaplicable o estar fuera de rango.'],
            'autoriza_interpretacion_clinica' => false];
    }
}
