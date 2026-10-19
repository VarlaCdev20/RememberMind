<?php

declare(strict_types=1);

namespace Tests\Unit;

use MotorExperto\aplicacion\casos_uso\EjecutarPerfilExperimental;
use MotorExperto\investigacion\dcl\CasoSinteticoCincoDominios as Caso;
use PHPUnit\Framework\TestCase;

final class ResultadoPerfilExperimentalTest extends TestCase
{
    public function test_contrato_consolida_identidad_fuentes_reglas_y_subcomponentes_sin_inventar_memoria(): void
    {
        $caso = Caso::crear();
        $r = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
        self::assertSame('SINTETICO-01', $r['caso']);
        self::assertSame('SIM-INTEGRAL-01', $r['evaluacion']);
        self::assertSame($r['perfil_experimental']['versiones'], $r['manifiesto_versiones']);
        self::assertCount(19, $r['subcomponentes']);
        self::assertFalse($r['subcomponentes']['MEM-S01']['evaluado']);
        self::assertNull($r['subcomponentes']['MEM-S01']['resultado']);
        self::assertSame('CONTRATO_DE_TAREA_PENDIENTE', $r['subcomponentes']['MEM-S01']['motivo_no_emision']);
        self::assertCount(15, $r['fuentes_examinadas']['g09']);
        self::assertCount(15, $r['reglas_examinadas']['g09']);
        self::assertSame('CUMPLE', $r['reglas_examinadas']['g09']['R-ATE-S01-P01']['estado']);
        self::assertSame('FALLO_SIMULADO', $r['hechos_instrumentales'][0]['valores_originales'][0]['valor']);
        self::assertSame('string', $r['hechos_instrumentales'][0]['valores_originales'][0]['tipo']);
        self::assertSame('INSTRUMENTAL_TEST_ONLY', $r['hechos_instrumentales'][0]['rol']);
        self::assertSame('SIM-AUTOR-01', $r['hechos_instrumentales'][0]['autor']);
        self::assertSame('CAPTURA_SINTETICA_DECLARADA', $r['hechos_instrumentales'][0]['procedencia']);
        self::assertSame($caso['capturas']['respuestas_instrumento'][0], $r['fuentes_examinadas']['g09'][0]['captura_original']['respuestas'][0]);
        self::assertSame($r, (new EjecutarPerfilExperimental)->ejecutar(...$r['instantanea_entrada']));
    }

    public function test_autor_ausente_permanece_null_y_contexto_instrumental_es_explicitamente_artificial(): void
    {
        $caso = Caso::crear();
        unset($caso['capturas']['aplicaciones_instrumento'][0]['cod_personal']);
        $r = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
        self::assertNull($r['hechos_instrumentales'][0]['autor']);
        $perfil = $r['fuentes_examinadas']['g09'][0]['perfil_instrumental'];
        self::assertSame('SIM-ES', $perfil['idioma']);
        self::assertSame('SIM-POBLACION-DE-INGENIERIA', $perfil['poblacion']);
        self::assertSame('SIM-PATRON-EXACTO-SIN-BAREMO', $perfil['norma']);
        self::assertSame($r, (new EjecutarPerfilExperimental)->ejecutar(...$r['instantanea_entrada']));
    }

    public function test_null_y_fuentes_excluidas_conservan_bruto_y_motivo_sin_crear_hechos_admitidos(): void
    {
        $caso = Caso::crear();
        $caso['capturas']['respuestas_instrumento'][0]['valor_texto'] = null;
        $r = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
        $h = $r['hechos_instrumentales'][0];
        self::assertNull($h['valores_originales'][0]['valor']);
        self::assertSame('null', $h['valores_originales'][0]['tipo']);
        self::assertFalse($h['admitida']);
        self::assertNull($h['valor_semantico']);
        self::assertContains('RESPUESTA_NO_INTERPRETABLE', $h['motivos']);
        self::assertSame('NO_EVALUADA', $r['reglas_examinadas']['g09']['R-ATE-S01-P01']['estado']);
        self::assertSame('FUENTE_NO_ADMISIBLE', $r['subcomponentes']['ATE-S01']['motivo_no_emision']);
        self::assertCount(1, $r['evidencias_excluidas']['g08']);
        self::assertCount(14, $r['evidencias_admitidas']['g09']);
    }

    public function test_tarea_no_solicitada_y_compuerta_sin_contexto_tienen_motivos_distintos(): void
    {
        $caso = Caso::crear();
        array_shift($caso['tareas']);
        $caso['contexto']['contextos_dominios']['COG-LEN']['contexto_suficiente'] = null;
        $r = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
        self::assertSame('TAREA_NO_SOLICITADA', $r['subcomponentes']['ATE-S01']['motivo_no_emision']);
        self::assertSame('COMPUERTA_NO_HABILITA', $r['subcomponentes']['LEN-S01']['motivo_no_emision']);
        self::assertCount(15, $r['reglas_examinadas']['g09']);
        self::assertContains('R-ATE-S01-P01', $r['evidencias_faltantes']['tareas_no_solicitadas']);
        self::assertSame('ABSTENCION_INFORMACION_INSUFICIENTE', $r['petersen']['estado']);
        self::assertSame('U', $r['requisitos_dcl']['DCL-C03']);
        self::assertContains('COG-LEN', $r['evidencias_faltantes']['contextos_no_habilitados']);
        self::assertContains('DCL-C03', $r['evidencias_faltantes']['requisitos_dcl_desconocidos']);
        self::assertCount(5, $r['evidencias_faltantes']['dominios_dcl_no_interpretables']);
    }

    public function test_confusor_omitido_permanece_desconocido_y_no_autoriza_la_captura(): void
    {
        $caso = Caso::crear();
        unset($caso['contexto']['confusor_invalidante']);
        $r = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
        self::assertNull($r['confusores']['COG-ATE']['confusor_invalidante']);
        self::assertCount(0, $r['evidencias_admitidas']['g09']);
        self::assertSame('SIN_EVIDENCIA_ADMISIBLE', $r['perfil_cinco_dominios']['COG-ATE']['salida']['resultado']['estado_tecnico']);
        self::assertSame($r, (new EjecutarPerfilExperimental)->ejecutar(...$r['instantanea_entrada']));
    }

    public function test_contradiccion_no_oculta_reglas_cumplidas_y_dcl_conserva_fuentes_independientes(): void
    {
        $caso = Caso::crear(['COG-MEM' => 'A', 'COG-ATE' => 'A', 'COG-EJE' => 'P', 'COG-LEN' => 'P', 'COG-VIS' => 'P']);
        $caso['contexto']['contextos_dominios']['COG-ATE']['contradiccion_documentada'] = true;
        $r = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
        self::assertSame(['COG-ATE'], $r['contradicciones']);
        self::assertSame('CUMPLE', $r['reglas_examinadas']['g09']['R-ATE-S01-P01']['estado']);
        self::assertSame([], $r['subcomponentes']['ATE-S01']['resultado']);
        self::assertSame('CONTRADICCION_DOCUMENTADA', $r['subcomponentes']['ATE-S01']['motivo_no_emision']);
        self::assertSame($caso['referencias'], $r['fuentes_examinadas']['referencias_dcl']);
        self::assertSame('S', $r['requisitos_dcl']['DCL-C03']);
        self::assertSame('PET-02', $r['petersen']['patron_candidato']);
        self::assertSame($r['perfil_experimental']['informe_dcl'], $r['resumen']['petersen']);
    }
}
