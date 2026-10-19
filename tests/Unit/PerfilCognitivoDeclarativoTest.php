<?php

declare(strict_types=1);

namespace Tests\Unit;

use DateTimeImmutable;
use DomainException;
use MotorExperto\adaptadores\entrada\AdaptadorHechosCOGMEM;
use MotorExperto\adaptadores\entrada\EjemploTecnicoCOGMEM;
use MotorExperto\aplicacion\casos_uso\EvaluarConocimientoCognitivo;
use MotorExperto\dominio\conocimiento\MapeadorSemantico;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\inferencia\MemoriaTrabajo;
use MotorExperto\dominio\objetos_valor\ContextoEjecucion;
use MotorExperto\dominio\objetos_valor\EvidenciaExperta;
use MotorExperto\dominio\objetos_valor\FuenteBrutaCOGMEM;
use MotorExperto\investigacion\dcl\PaqueteCincoDominiosExperimental;
use MotorExperto\investigacion\g09\PaqueteSemanticoG09Experimental;
use PHPUnit\Framework\TestCase;

/** La entrada institucional pura se prueba con fuentes sintéticas y mapeos exactos, sin generadores en el ejecutor. */
final class PerfilCognitivoDeclarativoTest extends TestCase
{
    private function preparar(): array
    {
        $plantilla = (new PaqueteCincoDominiosExperimental)->integrar(PaqueteSemanticoG09Experimental::crear())['paquete'];
        $t = $plantilla->declaracion()['tablas'];
        $t['valores_semanticos'][] = ['cod_valor_semantico' => 'VAL-SIM-FALSE', 'cod_dominio_valores' => 'DOM-SIM-COND',
            'codigo_valor' => 'FALSE-DE-FIXTURE', 'estado' => 'ACTIVO', 'estado_aprobacion' => 'APROBADO'];
        $t['fuentes_datos_expertas'][] = ['cod_fuente_dato_experta' => 'FU-G09', 'cod_version_modelo' => $plantilla->version,
            'tabla_raiz' => 'aplicaciones_instrumento', 'estado' => 'ACTIVO'];
        foreach (range(1, 3) as $i) {
            $t['mapeos_variables_fuente'][] = ['cod_mapeo_variable_fuente' => 'MAP-G09-'.$i, 'cod_version_modelo' => $plantilla->version,
                'cod_variable_experta' => 'VAR-R-ATE-S01-P01-C'.$i, 'cod_fuente_dato_experta' => 'FU-G09',
                'tipo_extraccion' => 'CAMPO_DIRECTO', 'campo_valor' => 'C'.$i, 'clave_selector' => null, 'estado' => 'ACTIVO'];
            foreach (['true' => 'VAL-SIM-TRUE', 'false' => 'VAL-SIM-FALSE'] as $literal => $valor) {
                $t['mapeos_valores_fuente'][] = ['cod_mapeo_valor_fuente' => 'MV-G09-'.$i.'-'.$literal,
                    'cod_mapeo_variable_fuente' => 'MAP-G09-'.$i, 'cod_valor_semantico' => $valor,
                    'tipo_valor_fuente' => 'boolean', 'valor_fuente_exacto' => $literal,
                    'estado' => 'ACTIVO', 'estado_aprobacion' => 'APROBADO',
                    'fecha_hora_vigencia_desde' => null, 'fecha_hora_vigencia_hasta' => null];
            }
        }
        $p = new PaqueteConocimiento($plantilla->version, $t, $plantilla->contratosRelaciones, false, 'RESULTADO',
            $plantilla->contratosExtraccion + ['CAMPO_DIRECTO' => 'CAMPO_DIRECTO']);
        $m = new MemoriaTrabajo(new ContextoEjecucion('EVAL-SINTETICA', 'RES_TEST', $p->version, new DateTimeImmutable('2026-10-19T12:00:00-04:00')));

        return [$p, $m];
    }

    private function hecho(PaqueteConocimiento $p, MemoriaTrabajo $m, int $condicion, ?bool $valor, string $registro = 'APP-SINTETICA', array $contexto = []): EvidenciaExperta
    {
        $f = new FuenteBrutaCOGMEM('FU-G09', 'aplicaciones_instrumento', 'C'.$condicion, $registro, $m->contexto->residente,
            new DateTimeImmutable('2026-10-18T12:00:00-04:00'), $valor, $valor, 'PERSONAL-SINTETICO', true, true, contexto: $contexto);
        $e = (new MapeadorSemantico)->mapear($p, $m->contexto, $f, 'MAP-G09-'.$condicion);
        $m->incorporar($e);
        $m->vincular('CRIT-ATE', $e->id, 'INSTRUMENTAL');

        return $e;
    }

    public function test_ejecuta_paquete_no_test_only_desde_fuentes_mapeadas_y_explica_cinco_dominios(): void
    {
        [$p, $m] = $this->preparar();
        foreach (range(1, 3) as $i) {
            $this->hecho($p, $m, $i, true, contexto: ['unidad_soporte' => 'APP-SINTETICA']);
        }
        $mem = (new AdaptadorHechosCOGMEM)->adaptar($p, $m, EjemploTecnicoCOGMEM::fuente(), 'MAP_INS');
        $m->vincular('MEM', $mem->id, 'INSTRUMENTAL');
        $r = (new EvaluarConocimientoCognitivo)->ejecutar($p, $m);
        self::assertCount(5, $r['perfil']);
        $c = $r['perfil']['COG-ATE']['conclusiones_candidatas']['VAL-RES-R-ATE-S01-P01'];
        self::assertSame(['R-ATE-S01-P01'], $c['reglas']);
        self::assertCount(3, $c['evidencias']);
        self::assertCount(1, $c['soportes_efectivos']);
        self::assertCount(3, $r['perfil']['COG-ATE']['traza']['rutas']);
        self::assertSame(['RULE_A'], $r['perfil']['COG-MEM']['conclusiones_candidatas']['RES_D']['reglas']);
        self::assertNull($r['clasificacion_dcl']);
        self::assertFalse($r['persistido']);
    }

    public function test_null_y_condiciones_repartidas_no_producen_conclusion(): void
    {
        [$p, $m] = $this->preparar();
        $this->hecho($p, $m, 1, true, 'APP-A');
        $this->hecho($p, $m, 2, true, 'APP-B');
        $this->hecho($p, $m, 3, null, 'APP-A');
        $r = (new EvaluarConocimientoCognitivo)->ejecutar($p, $m);
        self::assertSame([], $r['perfil']['COG-ATE']['conclusiones_candidatas']);
        self::assertCount(1, $r['perfil']['COG-ATE']['traza']['sin_emision']);
    }

    public function test_contradiccion_de_un_mismo_soporte_conserva_traza_y_suspende_conclusiones(): void
    {
        [$p, $m] = $this->preparar();
        foreach (range(1, 3) as $i) {
            $this->hecho($p, $m, $i, true, 'APP-A', ['unidad_soporte' => 'EP-SINTETICO']);
        }
        $this->hecho($p, $m, 1, false, 'APP-B', ['unidad_soporte' => 'EP-SINTETICO']);
        $r = (new EvaluarConocimientoCognitivo)->ejecutar($p, $m);
        self::assertSame([], $r['perfil']['COG-ATE']['conclusiones_candidatas']);
        self::assertCount(1, $r['perfil']['COG-ATE']['contradicciones']);
        self::assertNotEmpty($r['perfil']['COG-ATE']['traza']['rutas']);
    }

    public function test_no_confia_en_un_valor_semantico_que_no_sale_de_su_fuente(): void
    {
        [$p, $m] = $this->preparar();
        $e = $this->hecho($p, $m, 1, false);
        $otra = new MemoriaTrabajo($m->contexto);
        $otra->incorporar(new EvidenciaExperta($e->id, $e->ejecucion, $e->fuente, $e->mapeoVariable, $e->mapeoValor,
            $e->variable, 'VAL-SIM-TRUE', $e->representacion, $e->admisibilidad, $e->motivos));
        $otra->vincular('CRIT-ATE', $e->id, 'INSTRUMENTAL');
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('no coincide');
        (new EvaluarConocimientoCognitivo)->ejecutar($p, $otra);
    }
}
