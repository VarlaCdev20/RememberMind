<?php

declare(strict_types=1);

namespace MotorExperto\dominio\conocimiento\versiones;

use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\conocimiento\PerfilConceptualCognitivo;

/** Conocimiento A–D adoptado por la propietaria el 19/10/2026; no contiene datos ni fixtures. */
final class PaqueteCognitivoInstitucional
{
    public const CODIGO = 'ORION-COGNITIVO-1';

    public const FUNDAMENTO = 'Contrato institucional A–D adoptado el 19/10/2026. C1: interpretación específica versionada; C2: atribución de tarea; C3: contexto suficiente. Sin umbral universal ni equivalencia observación/instrumento. Revisión profesional posterior pendiente.';

    public static function reconoce(PaqueteConocimiento $p): bool
    {
        return ($p->contratosExtraccion['RESPUESTA_INSTITUCIONAL'] ?? null) === 'SELECTOR_COMPONENTE';
    }

    public static function contratos(): array
    {
        return ['relaciones' => [
            ['tipo_relacion' => 'SUBCOMPONENTE_DE', 'tipo_origen' => 'SUBCOMPONENTE', 'tipo_destino' => 'CRITERIO', 'modalidades' => [null]],
            ['tipo_relacion' => 'APORTA_EVIDENCIA', 'tipo_origen' => 'VARIABLE', 'tipo_destino' => 'CRITERIO', 'modalidades' => [null], 'habilita_participacion' => true],
            ['tipo_relacion' => 'CONTEXTUALIZA', 'tipo_origen' => 'VARIABLE', 'tipo_destino' => 'CRITERIO', 'modalidades' => [null], 'habilita_participacion' => false],
        ], 'extracciones' => ['RESPUESTA_INSTITUCIONAL' => 'SELECTOR_COMPONENTE'], 'tipo_regla_resultado' => 'RESULTADO',
            'vinculos_instrumentales' => [], 'admite_memoria_vacia' => true];
    }

    public static function reglasG09(): array
    {
        return ['ATE-S01' => 'ATE-R-SOSTENIMIENTO', 'ATE-S02' => 'ATE-R-SELECCION', 'ATE-S03' => 'ATE-R-DISTRIBUCION',
            'EJE-S01' => 'EJE-R-INHIBICION', 'EJE-S02' => 'EJE-R-FLEXIBILIDAD', 'EJE-S03' => 'EJE-R-ACTUALIZACION',
            'EJE-S04' => 'EJE-R-PLANIFICACION', 'EJE-S05' => 'EJE-R-RAZONAMIENTO',
            'LEN-S01' => 'LEN-R-DENOMINACION', 'LEN-S02' => 'LEN-R-FLUIDEZ', 'LEN-S03' => 'LEN-R-COMPRENSION',
            'LEN-S04' => 'LEN-R-REPETICION', 'LEN-S05' => 'LEN-R-DISCURSO',
            'VIS-S01' => 'VIS-R-PERCEPCION', 'VIS-S02' => 'VIS-R-CONSTRUCCION'];
    }

    public static function id(string $tipo, string $codigo): string
    {
        return $tipo.'_'.substr(hash('sha256', self::CODIGO.'|'.$codigo), 0, 16);
    }

    /** Las interpretaciones y sus selectores se vinculan después desde originales identificados. */
    public function construir(array $red, string $version, string $autor, string $fecha, array $fuentesObservacionales = []): PaqueteConocimiento
    {
        $t = array_fill_keys(array_keys(PaqueteConocimiento::CLAVES), []);
        $sello = ['estado' => 'ACTIVO', 'fecha_hora_creacion' => $fecha, 'cod_usuario_creacion' => $autor, 'observacion' => self::FUNDAMENTO];
        foreach ($red['nodos'] as $n) {
            $t['nodos_semanticos'][] = $sello + ['cod_nodo_semantico' => self::id('N', $n['codigo_semantico']),
                'cod_version_modelo' => $version, 'codigo_semantico' => $n['codigo_semantico'], 'nombre' => $n['nombre'],
                'tipo_nodo' => $n['tipo_nodo'], 'definicion' => 'Concepto de la red documental O.R.I.O.N.; su pertenencia no acredita un hallazgo.'];
        }
        foreach ($red['relaciones'] as $r) {
            $t['relaciones_semanticas'][] = $sello + ['cod_relacion_semantica' => self::id('R', $r['cod_nodo_origen']),
                'cod_version_modelo' => $version, 'cod_nodo_origen' => self::id('N', $r['cod_nodo_origen']),
                'cod_nodo_destino' => self::id('N', $r['cod_nodo_destino']), 'tipo_relacion' => 'SUBCOMPONENTE_DE',
                'modalidad_relacion' => null, 'significado' => 'Pertenencia primaria documentada.'];
        }
        $dominios = ['CONDICION' => ['ACREDITADA'], 'OBSERVACION' => ['DIFICULTAD_DOCUMENTADA', 'SIN_DIFICULTAD_DOCUMENTADA'],
            'MEM-INSTRUMENTAL' => ['DIFICULTAD_INSTRUMENTAL', 'SIN_DIFICULTAD_INSTRUMENTAL']];
        foreach (PerfilConceptualCognitivo::CANTIDADES as $codigo => $cantidad) {
            $dominios[$codigo] = $codigo === 'COG-MEM' ? ['DIFICULTAD_EVIDENCIADA', 'SIN_DIFICULTAD_EVIDENCIADA', 'HALLAZGOS_MIXTOS']
                : array_values(array_filter(self::reglasG09(), static fn ($valor, $componente) => str_starts_with($componente, substr($codigo, 4).'-'), ARRAY_FILTER_USE_BOTH));
            $t['criterios_dominios_resultado'][] = $sello + ['cod_criterio_dominio_resultado' => self::id('C', $codigo),
                'cod_nodo_criterio' => self::id('N', $codigo), 'cod_dominio_valores' => self::id('D', $codigo)];
        }
        foreach ($dominios as $codigo => $valores) {
            $t['dominios_valores_expertos'][] = $sello + ['cod_dominio_valores' => self::id('D', $codigo), 'cod_version_modelo' => $version,
                'codigo_dominio' => 'DOM-'.$codigo, 'nombre' => $codigo, 'descripcion' => self::FUNDAMENTO,
                'tipo_dominio' => str_starts_with($codigo, 'COG-') ? 'RESULTADO' : 'INSTRUMENTAL'];
            foreach ($valores as $valor) {
                $t['valores_semanticos'][] = $sello + ['cod_valor_semantico' => self::id('V', $valor),
                    'cod_dominio_valores' => self::id('D', $codigo), 'codigo_valor' => $valor, 'nombre' => $valor,
                    'definicion_semantica' => self::FUNDAMENTO, 'estado_aprobacion' => 'APROBADO'];
            }
        }
        $variables = ['VAR-MEM-INST-01' => ['COG-MEM', 'COG-MEM', 'MEM-INSTRUMENTAL']];
        foreach (['COMPONENTE', 'CONDICIONES', 'CONFUSORES'] as $codigo) {
            $variables['VAR-CTX-MEM-'.$codigo] = ['COG-MEM', 'COG-MEM', 'CONDICION'];
        }
        foreach ($red['dominios'] as $dominio => $meta) {
            foreach ($meta['componentes'] as $componente => $_) {
                $variables['VAR-OBS-'.$componente] = [$componente, $dominio, 'OBSERVACION'];
            }
        }
        foreach (self::reglasG09() as $componente => $salida) {
            $dominio = 'COG-'.substr($componente, 0, 3);
            $codigo = 'R-'.$componente.'-P01';
            $condiciones = [];
            foreach (['C1', 'C2', 'C3'] as $c) {
                $variable = 'VAR-'.$componente.'-'.$c;
                $variables[$variable] = [$componente, $dominio, 'CONDICION'];
                $condiciones[$variable] = 'ACREDITADA';
            }
            $this->regla($t, $sello, $version, $codigo, $dominio, $salida, $condiciones);
        }
        foreach (['DIFICULTAD_INSTRUMENTAL' => 'DIFICULTAD_EVIDENCIADA', 'SIN_DIFICULTAD_INSTRUMENTAL' => 'SIN_DIFICULTAD_EVIDENCIADA'] as $entrada => $salida) {
            $this->regla($t, $sello, $version, 'MEM-'.($entrada === 'DIFICULTAD_INSTRUMENTAL' ? 'DIFICULTAD' : 'SIN-DIFICULTAD'),
                'COG-MEM', $salida, ['VAR-MEM-INST-01' => $entrada]);
        }
        foreach ($variables as $codigo => [$propietario, $criterio, $dominio]) {
            $contextual = str_starts_with($codigo, 'VAR-OBS-') || str_starts_with($codigo, 'VAR-CTX-');
            $t['nodos_semanticos'][] = $sello + ['cod_nodo_semantico' => self::id('N', $codigo), 'cod_version_modelo' => $version,
                'codigo_semantico' => $codigo, 'nombre' => $codigo, 'tipo_nodo' => 'VARIABLE', 'definicion' => self::FUNDAMENTO];
            $t['variables_expertas'][] = $sello + ['cod_variable_experta' => self::id('E', $codigo), 'cod_version_modelo' => $version,
                'cod_nodo_semantico' => self::id('N', $codigo), 'cod_nodo_propietario_primario' => self::id('N', $propietario),
                'cod_dominio_valores' => self::id('D', $dominio), 'tipo_semantico' => $dominio === 'OBSERVACION' ? 'OBSERVACIONAL' : 'INSTRUMENTAL',
                'papel_inferencial' => $contextual ? 'CONTEXTUAL' : 'PRIMARIO'];
            $t['relaciones_semanticas'][] = $sello + ['cod_relacion_semantica' => self::id('R', $codigo), 'cod_version_modelo' => $version,
                'cod_nodo_origen' => self::id('N', $codigo), 'cod_nodo_destino' => self::id('N', $criterio),
                'tipo_relacion' => $contextual ? 'CONTEXTUALIZA' : 'APORTA_EVIDENCIA', 'modalidad_relacion' => null,
                'significado' => $contextual ? 'Contexto conservado; no satisface antecedentes instrumentales.' : 'Participación explícita condicionada al hecho acreditado.'];
        }
        if ($fuentesObservacionales !== []) {
            $t['fuentes_datos_expertas'][] = $sello + ['cod_fuente_dato_experta' => self::id('F', 'OBSERVACIONES'), 'cod_version_modelo' => $version,
                'codigo_fuente' => 'OBS-COMPONENTES-ORIGINALES', 'nombre' => 'Observaciones clínicas por componente', 'tipo_fuente' => 'OPERATIVA',
                'tabla_raiz' => 'aplicaciones_instrumento', 'campo_pk_raiz' => 'cod_aplicacion', 'campo_residente' => 'cod_residente',
                'campo_temporal' => 'fecha_hora', 'campo_personal' => 'cod_personal', 'campo_estado' => 'estado', 'clave_adaptador' => 'OBS_COMPONENTES'];
            foreach ($fuentesObservacionales as $componente => $vinculo) {
                if (! isset($variables['VAR-OBS-'.$componente]) || ! is_string($vinculo['cod_pregunta'] ?? null)) {
                    throw new \DomainException('Vínculo observacional fuera del perfil de diecinueve componentes.');
                }
                $mapa = self::id('M', $componente);
                $t['mapeos_variables_fuente'][] = $sello + ['cod_mapeo_variable_fuente' => $mapa, 'cod_version_modelo' => $version,
                    'cod_variable_experta' => self::id('E', 'VAR-OBS-'.$componente), 'cod_fuente_dato_experta' => self::id('F', 'OBSERVACIONES'),
                    'tipo_extraccion' => 'RESPUESTA_INSTITUCIONAL', 'campo_valor' => null, 'clave_selector' => $vinculo['cod_pregunta']];
                foreach (['SIM_DIFICULTAD' => 'DIFICULTAD_DOCUMENTADA', 'SIM_SIN_DIFICULTAD' => 'SIN_DIFICULTAD_DOCUMENTADA'] as $literal => $valor) {
                    $t['mapeos_valores_fuente'][] = $sello + ['cod_mapeo_valor_fuente' => self::id('L', $componente.'|'.$literal),
                        'cod_mapeo_variable_fuente' => $mapa, 'cod_valor_semantico' => self::id('V', $valor), 'tipo_valor_fuente' => 'string',
                        'valor_fuente_exacto' => $literal, 'estado_aprobacion' => 'APROBADO',
                        'fecha_hora_vigencia_desde' => null, 'fecha_hora_vigencia_hasta' => null];
                }
            }
        }
        $c = self::contratos();

        return new PaqueteConocimiento($version, $t, $c['relaciones'], false, $c['tipo_regla_resultado'], $c['extracciones']);
    }

    private function regla(array &$t, array $sello, string $version, string $codigo, string $criterio, string $salida, array $condiciones): void
    {
        $id = self::id('G', $codigo);
        $t['reglas_expertas'][] = $sello + ['cod_regla_experta' => $id, 'cod_version_modelo' => $version, 'codigo_regla' => $codigo,
            'nombre' => $codigo, 'tipo_regla' => 'RESULTADO', 'descripcion' => self::FUNDAMENTO, 'cod_regla_anterior' => null,
            'estado_revision' => 'PENDIENTE', 'cod_usuario_revision' => null, 'fecha_hora_revision' => null, 'justificacion_revision' => null];
        foreach ($condiciones as $variable => $valor) {
            $t['condiciones_regla_experta'][] = ['cod_condicion_regla' => self::id('K', $codigo.'|'.$variable),
                'cod_regla_experta' => $id, 'cod_variable_experta' => self::id('E', $variable), 'cod_valor_semantico' => self::id('V', $valor)];
        }
        $t['consecuencias_regla_experta'][] = ['cod_consecuencia_regla' => self::id('S', $codigo), 'cod_regla_experta' => $id,
            'cod_criterio_dominio_resultado' => self::id('C', $criterio), 'cod_valor_semantico' => self::id('V', $salida)];
    }
}
