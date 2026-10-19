<?php

declare(strict_types=1);

namespace Tests\Unit;

use DomainException;
use MotorExperto\dominio\instrumentos\ComprobacionAdquisicionInstrumental;
use MotorExperto\dominio\instrumentos\ExtractorCapturaInstrumental;
use MotorExperto\dominio\instrumentos\NormalizadorHvltBolivia2015 as Hvlt;
use PHPUnit\Framework\TestCase;

final class ComprobacionAdquisicionInstrumentalTest extends TestCase
{
    /** Fuentes sintéticas; no contiene reactivos ni mediciones de residentes. */
    private function aplicacion(?int $edad = 70, array $cambios = [], array $sinSelector = [], array $sinRespuesta = [], array $identidad = []): array
    {
        $a = ['cod_aplicacion' => 'AP-SINTETICA', 'cod_instrumento' => 'INST-SINTETICO',
            'codigo_instrumento' => 'COD-SINTETICO', 'version_instrumento' => 'VERSION-SINTETICA',
            'cod_personal' => 'AUTOR-SINTETICO', 'fecha_hora' => '2026-10-19T09:00:00.123456-04:00', 'edad_en_aplicacion' => $edad,
            'procedencia' => ['cod_usuario_registro' => 'TRANSCRIPTOR-SINT', 'registrado_en' => '2026-10-19T11:00:00.654321-04:00',
                'cod_documento_clinico' => 'DOC-SINTETICO', 'estado_documento' => 'VERIFICADO', 'localizador_fuente' => 'Página sintética 1'],
            'motivos' => []];
        $perfil = $a + ['id' => 'PERFIL-SINTETICO', 'metodo' => Hvlt::METODO,
            'identidad_instrumento' => array_replace(['instrumento' => 'HVLT-R', 'forma' => 5, 'idioma' => 'es'], $identidad), 'selectores' => []];
        $respuestas = [];
        foreach (Hvlt::ENTRADAS as $campo => $tipo) {
            $q = 'PREGUNTA-'.$campo;
            $columna = $tipo === 'LOGICO' ? 'valor_logico' : 'valor_numero';
            if (! in_array($campo, $sinSelector, true)) {
                $perfil['selectores'][$campo] = ['cod_pregunta' => $q, 'campo' => $columna];
            }
            if (in_array($campo, $sinRespuesta, true)) {
                continue;
            }
            $valor = match ($campo) {
                'ensayo_1' => 6, 'ensayo_2' => 7, 'ensayo_3' => 8, 'recuerdo_diferido' => 4,
                'demora_minutos' => 22, 'escolaridad_anios' => 4, default => true,
            };
            $respuestas[] = ['cod_aplicacion' => $a['cod_aplicacion'], 'cod_pregunta' => $q,
                'cod_respuesta' => 'RESPUESTA-'.$campo, 'coherente' => true,
                'tipo_respuesta' => $tipo === 'LOGICO' ? 'LOGICO' : 'NUMERICO',
                $columna => array_key_exists($campo, $cambios) ? $cambios[$campo] : $valor];
        }
        $a['vinculos_preguntas_actuales'] = array_map(static fn ($s) => ['selector' => $s], $perfil['selectores']);
        $a['extraccion'] = (new ExtractorCapturaInstrumental)->extraer($perfil, $a, $respuestas, $edad);

        return $a;
    }

    private function comprobar(array $apps): array
    {
        return (new ComprobacionAdquisicionInstrumental)->comprobar(['disponible' => true, 'capturas' => $apps]);
    }

    public function test_captura_completa_usa_originales_y_separa_aplicador_transcriptor_y_fechas(): void
    {
        $r = $this->comprobar([$this->aplicacion()]);
        self::assertSame(11, $r['metricas_entradas_60_o_mas']['entradas_requeridas']);
        self::assertSame(100.0, $r['metricas_entradas_60_o_mas']['porcentaje_completitud']);
        $a = $r['aplicaciones'][0];
        self::assertSame('COINCIDE_CON_METODO', $a['identidad']['forma']['estado']);
        self::assertSame('PREGUNTA-ensayo_1', $a['entradas']['ensayo_1']['cod_pregunta']);
        self::assertSame('RESPUESTA-ensayo_1', $a['entradas']['ensayo_1']['cod_respuesta']);
        self::assertSame('AUTOR-SINTETICO', $a['procedencia']['cod_personal_aplicador']);
        self::assertSame('TRANSCRIPTOR-SINT', $a['procedencia']['cod_usuario_registro']);
        self::assertSame('2026-10-19T09:00:00.123456-04:00', $a['procedencia']['fecha_hora_clinica']);
        self::assertSame('2026-10-19T11:00:00.654321-04:00', $a['procedencia']['registrado_en']);
        self::assertSame('VERIFICADO', $a['procedencia']['estado_documento']);
        self::assertFalse($r['autoriza_interpretacion_clinica']);
        self::assertArrayNotHasKey('ruta_archivo', $a['procedencia']);
    }

    public function test_distingue_sin_selector_sin_respuesta_null_cero_y_no_sin_recalcular_el_metodo(): void
    {
        $a = $this->aplicacion(cambios: ['ensayo_1' => 0, 'lectoescritura' => false, 'recuerdo_diferido' => null],
            sinSelector: ['ensayo_3'], sinRespuesta: ['ensayo_2']);
        $r = $this->comprobar([$a]);
        $entradas = $r['aplicaciones'][0]['entradas'];
        self::assertSame('DATO_TIPADO_DISPONIBLE', $entradas['ensayo_1']['estado']);
        self::assertSame('DATO_TIPADO_DISPONIBLE', $entradas['lectoescritura']['estado']);
        self::assertSame('SIN_RESPUESTA_COMPATIBLE', $entradas['ensayo_2']['estado']);
        self::assertSame('PREGUNTA-ensayo_2', $entradas['ensayo_2']['cod_pregunta']);
        self::assertNull($entradas['ensayo_2']['cod_respuesta']);
        self::assertSame('SIN_SELECTOR', $entradas['ensayo_3']['estado']);
        self::assertSame('VALOR_AUSENTE_O_TIPO_INVALIDO', $entradas['recuerdo_diferido']['estado']);
        self::assertSame(8, $r['metricas_entradas_60_o_mas']['entradas_con_dato_tipado']);
        self::assertSame(72.73, $r['metricas_entradas_60_o_mas']['porcentaje_completitud']);
        self::assertSame($a['extraccion']['normalizacion']['componentes']['MEM-S01']['motivos'], $r['aplicaciones'][0]['calculos']['MEM-S01'][0]['motivos']);
        self::assertContains('APLICABILIDAD_NO_ACREDITADA:lectoescritura', $r['aplicaciones'][0]['calculos']['MEM-S01'][0]['motivos']);
    }

    public function test_completitud_total_no_oculta_identidad_incompatible_ni_rango_invalido(): void
    {
        $a = $this->aplicacion(cambios: ['ensayo_1' => 13], identidad: ['forma' => 4, 'idioma' => null]);
        $r = $this->comprobar([$a]);
        self::assertSame(100.0, $r['metricas_entradas_60_o_mas']['porcentaje_completitud']);
        self::assertSame('INCOMPATIBLE_CON_METODO', $r['aplicaciones'][0]['identidad']['forma']['estado']);
        self::assertSame('SIN_DECLARACION', $r['aplicaciones'][0]['identidad']['idioma']['estado']);
        self::assertSame('NO_NORMALIZABLE', $r['aplicaciones'][0]['calculos']['MEM-S01'][0]['estado']);
        self::assertContains('REQUIERE_TRES_ENSAYOS_CON_ACIERTOS_DE_0_A_12', $r['aplicaciones'][0]['calculos']['MEM-S01'][0]['motivos']);
    }

    public function test_denominador_excluye_menores_y_edad_desconocida_sin_ocultar_sus_capturas(): void
    {
        $menor = array_replace($this->aplicacion(59), ['cod_aplicacion' => 'AP-MENOR']);
        $desconocida = array_replace($this->aplicacion(null), ['cod_aplicacion' => 'AP-EDAD-DESCONOCIDA']);
        $r = $this->comprobar([$menor, $desconocida, $this->aplicacion(60)]);
        self::assertCount(3, $r['aplicaciones']);
        self::assertFalse($r['aplicaciones'][0]['pertenece_a_poblacion_60_o_mas']);
        self::assertNull($r['aplicaciones'][1]['pertenece_a_poblacion_60_o_mas']);
        self::assertTrue($r['aplicaciones'][2]['pertenece_a_poblacion_60_o_mas']);
        self::assertSame(11, $r['metricas_entradas_60_o_mas']['entradas_requeridas']);
    }

    public function test_no_estima_entradas_de_instrumento_sin_perfil_y_no_divide_entre_cero(): void
    {
        $a = ['cod_aplicacion' => 'AP-SIN-PERFIL', 'edad_en_aplicacion' => 70, 'extraccion' => null,
            'nombre_instrumento' => 'HVLT-R', 'motivos' => ['SIN_PERFIL_EXPLICITO']];
        $r = $this->comprobar([$a]);
        self::assertSame([], $r['aplicaciones'][0]['entradas']);
        self::assertNull($r['aplicaciones'][0]['metodo']);
        self::assertSame(['SIN_PERFIL_EXPLICITO'], $r['aplicaciones'][0]['motivos_lectura']);
        self::assertSame(1, $r['metricas_entradas_60_o_mas']['aplicaciones_sin_extraccion']);
        self::assertSame(0, $r['metricas_entradas_60_o_mas']['entradas_requeridas']);
        self::assertNull($r['metricas_entradas_60_o_mas']['porcentaje_completitud']);
        self::assertNull($this->comprobar([])['metricas_entradas_60_o_mas']['porcentaje_completitud']);
    }

    public function test_rechaza_repetir_la_misma_aplicacion_para_inflar_la_metrica(): void
    {
        $a = $this->aplicacion();
        $this->expectException(DomainException::class);
        $this->comprobar([$a, $a]);
    }

    public function test_observacion_declarada_no_exige_aciertos_tiempos_o_perfil_de_otra_prueba(): void
    {
        $a = ['cod_aplicacion' => 'OBS-SINTETICA', 'edad_en_aplicacion' => 70, 'extraccion' => null,
            'instrumento_actual' => ['tipo' => 'OBSERVACIONAL'], 'motivos' => ['SIN_PERFIL_EXPLICITO']];
        $r = $this->comprobar([$a]);
        self::assertSame('OBSERVACION_CLINICA', $r['aplicaciones'][0]['naturaleza_fuente']);
        self::assertSame('NO_APLICA_A_OBSERVACION', $r['aplicaciones'][0]['estado_normalizacion']);
        self::assertSame([], $r['aplicaciones'][0]['entradas']);
        self::assertSame(0, $r['metricas_entradas_60_o_mas']['aplicaciones_sin_extraccion']);
        self::assertSame(1, $r['metricas_entradas_60_o_mas']['aplicaciones_observacionales_sin_normalizacion']);
        self::assertNull($r['metricas_entradas_60_o_mas']['porcentaje_completitud']);
    }

    public function test_una_extraccion_no_puede_reclasificar_observacion_como_prueba_medida(): void
    {
        $a = $this->aplicacion();
        $a['instrumento_actual']['tipo'] = 'OBSERVACIONAL';
        $this->expectException(DomainException::class);
        $this->comprobar([$a]);
    }

    public function test_no_produce_comprobacion_si_las_fuentes_no_estan_autorizadas(): void
    {
        $this->expectException(DomainException::class);
        (new ComprobacionAdquisicionInstrumental)->comprobar(['disponible' => false, 'capturas' => []]);
    }
}
