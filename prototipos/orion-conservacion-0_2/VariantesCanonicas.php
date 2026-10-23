<?php

declare(strict_types=1);

namespace OrionFase02;

use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\instrumentos\ExtractorCapturaInstrumental;
use MotorExperto\dominio\petersen\FuentePetersenInstitucional as F;
use MotorExperto\dominio\petersen\ValoracionOriginalPetersen as V;

/** Corpus de ingeniería; no define nuevos reactivos ni reglas clínicas. */
final class VariantesCanonicas
{
    private static function crear(array $alterados=['COG-MEM'], bool $sinC03=false, bool $sinGobierno=false, ?callable $variar=null): \MotorExperto\soporte\conservacion\InstantaneaEvaluacionInstitucional
    {
        static $secuencia=0;
        return (new CasoCanonico)->crear($alterados,$sinC03,$sinGobierno,$variar,'VARIANTE-'.++$secuencia);
    }

    public static function casos(): array
    {
        return [
            'PET01' => self::crear(),
            'PET02' => self::crear(['COG-MEM', 'COG-ATE']),
            'PET03' => self::crear(['COG-ATE']),
            'PET04' => self::crear(['COG-ATE', 'COG-EJE']),
            'SIN_C03' => self::crear(sinC03: true),
            'SIN_GOBIERNO' => self::crear(sinGobierno: true),
            'SIN_DATOS' => self::crear(variar: static function (&$a, &$v, &$p, &$c, &$procedencia, &$miembros, &$adquisicion): void {
                $a = $v = $procedencia = $miembros = [];
                $adquisicion['valoraciones_al_corte'] = 0;
            }),
            'AUSENCIAS_NULL_ORDEN' => self::crear(variar: static function (&$a, &$v, &$p, &$c, &$procedencia, &$miembros, &$adquisicion, &$docs): void {
                // Captura excluida con definición y respuesta parciales, sin convertirla en dato válido.
                $extra = $a[0];
                $extra['cod_aplicacion'] = 'APP-DESCARTADA';
                $extra['motivos'] = ['SIN_PERFIL_EXPLICITO', 'DOCUMENTO_NO_VERIFICABLE', 'SIN_PERFIL_EXPLICITO'];
                $extra['extraccion'] = null;
                $extra['perfil_captura_original']['identidad_instrumento'] = [];
                $extra['perfil_captura_original']['selectores']['ensayo_1'] = null;
                unset($extra['perfil_captura_original']['selectores']['ensayo_2']);
                $extra['respuestas_originales'] = [];
                $extra['documento_original'] = ['cod_documento_clinico' => 'DOC-OBSERVADO', 'cod_residente' => 'RES-TEST',
                    'cod_personal' => 'AUTOR-TEST', 'hash_archivo' => null, 'estado' => 'ANULADO', 'fecha_hora' => null];
                $a[] = $extra;
                // Procedencia de juicio excluido con colección vacía y exclusiones repetidas ordenadas.
                $old = $procedencia[0];
                $old['cod_valoracion_petersen'] = 'JUICIO-EXCLUIDO';
                $old['tipo_registro'] = 'ANULACION';
                $procedencia[] = $old;
                $miembros[] = ['valoracion' => 'JUICIO-EXCLUIDO', 'soportes' => []];
                $adquisicion['valoraciones_al_corte']++;
                $adquisicion['exclusiones'] = [['valoracion' => 'JUICIO-EXCLUIDO', 'elemento' => $old['elemento'], 'motivo' => 'ANULADA'],
                    ['valoracion' => 'JUICIO-EXCLUIDO', 'elemento' => $old['elemento'], 'motivo' => 'ANULADA']];
                $docs[] = ['valoracion' => 'JUICIO-EXCLUIDO', 'papel' => 'SOPORTE', 'cod_documento_clinico' => 'DOC-FUTURO',
                    'cod_residente' => 'RES-TEST', 'cod_personal' => 'AUTOR-TEST', 'hash_archivo' => str_repeat('a', 64),
                    'fecha_hora' => '2026-10-20T09:00:00.000000-04:00', 'estado' => 'VIGENTE', 'localizador_fuente' => null, 'verificado' => true];
                $j = $v[7];
                $v[7] = new V($j->elemento, $j->estado, $j->justificacion, $j->origen, $j->soportes,
                    ['revision_explicita' => null, 'impedimento_no_resuelto' => false]);
            }),
            'SEIS_FUENTES_Y_DOCUMENTOS' => self::crear(variar: static function (&$a, &$v, &$p, &$c, &$procedencia, &$miembros, &$adquisicion, &$docs): void {
                $j = $v[7];
                $soportes = [];
                foreach (['antecedentes_clinicos', 'diagnosticos', 'documentos_clinicos'] as $n => $tabla) {
                    $soportes[] = new F($tabla, 'FUE-'.$n, 'RES-TEST', 'AUTOR-TEST', new \DateTimeImmutable('2026-10-15T09:00:00.000000-04:00'),
                        'EPISODIO-'.$n, 'DIFERENCIAL', true, ['descripcion' => $n === 0 ? null : ($n === 1 ? '' : 'Descripción sintética')]);
                }
                $v[7] = new V($j->elemento, $j->estado, $j->justificacion, $j->origen, $soportes, $j->comprobaciones);
                $miembros[7]['soportes'] = ['DOC-SOP-0', 'DOC-SOP-1', 'DOC-SOP-2'];
                $docs[] = ['valoracion' => $j->origen->registro, 'papel' => 'SOPORTE', 'cod_documento_clinico' => 'FUE-2',
                    'cod_residente' => 'RES-TEST', 'cod_personal' => 'AUTOR-TEST', 'hash_archivo' => str_repeat('b', 64),
                    'fecha_hora' => '2026-10-15T09:00:00.000000-04:00', 'estado' => 'VIGENTE', 'localizador_fuente' => null, 'verificado' => true];
                $procedencia[7]['cod_documento_clinico'] = 'DOC-ORIGINAL';
                $procedencia[7]['localizador_fuente'] = 'página 1';
                $docs[] = ['valoracion' => $j->origen->registro, 'papel' => 'ORIGINAL', 'cod_documento_clinico' => 'DOC-ORIGINAL',
                    'cod_residente' => 'RES-TEST', 'cod_personal' => 'AUTOR-TEST', 'hash_archivo' => str_repeat('c', 64),
                    'fecha_hora' => '2026-10-15T09:00:00.000000-04:00', 'estado' => 'VIGENTE', 'localizador_fuente' => 'página 1', 'verificado' => true];
            }),
            'VINCULO_INTERPRETATIVO' => self::crear(variar: self::interpretativo(...)),
        ];
    }

    public static function interpretativo(&$a, &$v, &$p, &$c, &$procedencia, &$miembros, &$adquisicion, &$docs, &$vinculos): void
    {
        $t = $p->declaracion()['tablas'];
        $t['fuentes_datos_expertas'][] = ['cod_fuente_dato_experta' => 'FU-TEST', 'cod_version_modelo' => $p->version,
            'codigo_fuente' => 'FU-TEST', 'tabla_raiz' => 'aplicaciones_instrumento', 'campo_pk_raiz' => 'cod_aplicacion',
            'campo_residente' => 'cod_residente', 'campo_temporal' => 'fecha_hora', 'campo_personal' => 'cod_personal', 'estado' => 'ACTIVO'];
        $a0 = &$a[1];
        $a0['respuestas_originales'][] = ['cod_respuesta' => 'RESP-LOGICA', 'cod_aplicacion' => $a0['cod_aplicacion'], 'cod_pregunta' => 'P-LOGICA',
            'cod_opcion' => null, 'valor_numero' => null, 'valor_logico' => true, 'valor_texto' => null, 'tipo_respuesta' => 'LOGICO', 'coherente' => true,
            'pregunta_actual' => ['cod_pregunta' => 'P-LOGICA', 'cod_instrumento' => $a0['cod_instrumento'], 'tipo_respuesta' => 'LOGICO'], 'opcion_actual' => null];
        $map = 'MAP-TEST';
        $nodos = array_values(array_filter($t['nodos_semanticos'], fn ($r) => $r['codigo_semantico'] === 'VAR-ATE-S01-C1'));
        if (count($nodos) !== 1) throw new \DomainException('El caso necesita el nodo exacto de ATE-S01-C1.');
        $variables = array_values(array_filter($t['variables_expertas'], fn ($r) => $r['cod_nodo_semantico'] === $nodos[0]['cod_nodo_semantico']));
        if (count($variables) !== 1) throw new \DomainException('El caso necesita la variable exacta de ATE-S01-C1.');
        $variable = $variables[0];
        $valores = array_values(array_filter($t['valores_semanticos'], fn ($r) => $r['cod_dominio_valores'] === $variable['cod_dominio_valores'] && $r['codigo_valor'] === 'ACREDITADA'));
        if (count($valores) !== 1) throw new \DomainException('El caso necesita el valor ACREDITADA de su dominio original.');
        $valor = $valores[0];
        $t['mapeos_variables_fuente'][] = ['cod_mapeo_variable_fuente' => $map, 'cod_version_modelo' => $p->version,
            'cod_variable_experta' => $variable['cod_variable_experta'], 'cod_fuente_dato_experta' => 'FU-TEST', 'tipo_extraccion' => 'RESPUESTA_INSTITUCIONAL',
            'clave_selector' => 'P-LOGICA', 'campo_valor' => null, 'estado' => 'ACTIVO'];
        $t['mapeos_valores_fuente'][] = ['cod_mapeo_valor_fuente' => 'MV-TEST', 'cod_mapeo_variable_fuente' => $map,
            'cod_valor_semantico' => $valor['cod_valor_semantico'], 'tipo_valor_fuente' => 'boolean', 'valor_fuente_exacto' => 'true', 'estado' => 'ACTIVO', 'estado_aprobacion' => 'APROBADO',
            'fecha_hora_vigencia_desde' => null, 'fecha_hora_vigencia_hasta' => null];
        $perfil = ['codigo' => 'PERFIL-TEST', 'version' => '1', 'componente' => 'ATE-S01',
            'instrumento' => ['codigo' => $a0['codigo_instrumento'], 'version' => $a0['version_instrumento']], 'metodo' => 'Ingeniería', 'fuente' => 'TEST',
            'predicados' => ['C1', 'C1', 'C2'], 'mediciones_requeridas' => ['aciertos' => ['cod_pregunta' => $a0['respuestas_originales'][0]['cod_pregunta'], 'campo' => 'valor_numero']]];
        $vinculos[$map] = ['cod_instrumento' => $a0['cod_instrumento'], 'codigo_instrumento' => $a0['codigo_instrumento'],
            'version_instrumento' => $a0['version_instrumento'], 'cod_pregunta' => 'P-LOGICA', 'campo' => 'valor_logico', 'componente' => 'ATE-S01', 'perfil_interpretativo' => $perfil];
        $p = new PaqueteConocimiento($p->version, $t, $p->contratosRelaciones, $p->soloPruebasTecnicas, $p->tipoReglaResultado, $p->contratosExtraccion);
        $j = $v[1];
        $s = $j->soportes[0];
        $a0['extraccion'] = (new ExtractorCapturaInstrumental)->extraer($a0['perfil_captura_original'], $a0, $a0['respuestas_originales'], $a0['edad_en_aplicacion']);
        $v[1] = new V($j->elemento, $j->estado, $j->justificacion, $j->origen,
            [new F($s->tabla, $s->registro, $s->residente, $s->autor, $s->fecha, $s->episodio, $s->tipo, $s->vigente, ['dominio' => $j->elemento, 'captura_original' => $a0])], $j->comprobaciones);
    }
}

