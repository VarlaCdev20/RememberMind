<?php

declare(strict_types=1);

namespace Tests\Unit;

use DateTimeImmutable;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\objetos_valor\ContextoEjecucion;
use MotorExperto\investigacion\dcl\PaqueteCincoDominiosExperimental;
use MotorExperto\investigacion\g09\PaqueteSemanticoG09Experimental;
use MotorExperto\soporte\conservacion\InstantaneaInstrumental;
use PHPUnit\Framework\TestCase;

final class ReproduccionFuenteNominalTest extends TestCase
{
    public function test_ausencia_nominal_reproduce_la_huella_del_motor_anterior(): void
    {
        // Huella calculada con los bytes de f9537e60, antes del soporte de respuestas nominales.
        self::assertSame('d96661ed285c7b257fcc7a87df0768f8555be6e5e49d59e66d62794aa903d631', self::ausenciaHistorica()->huella);
    }

    /** Fixture sintética determinista para comparar revisiones, sin SQL ni historia de residentes. */
    public static function ausenciaHistorica(): InstantaneaInstrumental
    {
        $base = (new PaqueteCincoDominiosExperimental)->integrar(PaqueteSemanticoG09Experimental::paraConservacion())['paquete'];
        $t = $base->declaracion()['tablas'];
        $t['fuentes_datos_expertas'][] = ['cod_fuente_dato_experta' => 'FU-AUSENCIA', 'cod_version_modelo' => $base->version,
            'tabla_raiz' => 'aplicaciones_instrumento', 'estado' => 'ACTIVO', 'campo_pk_raiz' => 'cod_aplicacion',
            'campo_residente' => 'cod_residente', 'campo_temporal' => 'fecha_hora', 'campo_personal' => 'cod_personal'];
        $t['mapeos_variables_fuente'][] = ['cod_mapeo_variable_fuente' => 'MAP-AUSENCIA', 'cod_version_modelo' => $base->version,
            'cod_variable_experta' => 'VAR-R-ATE-S01-P01-C1', 'cod_fuente_dato_experta' => 'FU-AUSENCIA',
            'tipo_extraccion' => 'RESPUESTA_ORIGINAL', 'campo_valor' => null, 'clave_selector' => 'PREG-AUSENTE', 'estado' => 'ACTIVO'];
        $p = new PaqueteConocimiento($base->version, $t, $base->contratosRelaciones, false, 'RESULTADO',
            $base->contratosExtraccion + ['RESPUESTA_ORIGINAL' => 'SELECTOR_COMPONENTE']);
        $c = new ContextoEjecucion('EVAL-HISTORICA', 'RES-SINTETICO', $p->version, new DateTimeImmutable('2026-10-19T12:00:00.123456-04:00'));
        $capturas = ['disponible' => true, 'fecha_corte' => $c->fechaCorte->format('Y-m-d\TH:i:s.uP'), 'capturas' => [[
            'cod_aplicacion' => 'APP-AUSENCIA', 'cod_residente' => $c->residente, 'cod_instrumento' => 'INSTR-OBS',
            'codigo_instrumento' => 'OBS-SINTETICA', 'version_instrumento' => '1', 'cod_personal' => 'AUTOR-SINTETICO',
            'fecha_hora' => '2026-10-18T09:00:00.000000-04:00', 'estado_original' => 'COMPLETA', 'edad_en_aplicacion' => 75,
            'motivos' => [], 'extraccion' => null, 'procedencia' => [], 'fuente_edad' => [], 'aplicacion_actual' => [],
            'instrumento_actual' => ['cod_instrumento' => 'INSTR-OBS', 'tipo' => 'OBSERVACIONAL', 'version' => '1'],
            'respuestas_originales' => [], 'vinculos_preguntas_actuales' => [],
            'preguntas_declarativas_originales' => ['PREG-AUSENTE' => ['cod_pregunta' => 'PREG-AUSENTE',
                'cod_instrumento' => 'INSTR-OBS', 'tipo_respuesta' => 'OPCION']],
        ]]];
        $vinculos = ['MAP-AUSENCIA' => ['cod_instrumento' => 'INSTR-OBS', 'codigo_instrumento' => 'OBS-SINTETICA',
            'version_instrumento' => '1', 'cod_pregunta' => 'PREG-AUSENTE', 'campo' => 'valor_texto']];

        return InstantaneaInstrumental::crear($p, $c, $capturas, $vinculos, 'USUARIO-SINTETICO', ['cod_version_modelo' => $p->version], 'INSTRUMENTAL-DECLARATIVO-1');
    }
}
