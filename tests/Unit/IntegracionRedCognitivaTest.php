<?php

declare(strict_types=1);

namespace Tests\Unit;

use DomainException;
use MotorExperto\adaptadores\entrada\AdaptadorHechosCOGMEM;
use MotorExperto\adaptadores\entrada\EjemploTecnicoCOGMEM;
use MotorExperto\aplicacion\casos_uso\EjecutarPerfilExperimental;
use MotorExperto\dominio\conocimiento\CompiladorPerfilCognitivo;
use MotorExperto\dominio\conocimiento\EditorPaqueteExperimental;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\explicacion\ConstructorResultadoExperimental;
use MotorExperto\dominio\inferencia\MemoriaTrabajo;
use MotorExperto\dominio\objetos_valor\EvidenciaExperta;
use MotorExperto\investigacion\dcl\CasoSinteticoCincoDominios;
use MotorExperto\investigacion\dcl\PaqueteCincoDominiosExperimental;
use MotorExperto\investigacion\g09\EjecutorConocimientoG09Experimental;
use MotorExperto\investigacion\g09\EvaluadorG09Experimental;
use MotorExperto\investigacion\g09\PaqueteSemanticoG09Experimental;
use PHPUnit\Framework\TestCase;

/** Verificación de software con fuentes sintéticas; no representa mediciones de residentes. */
final class IntegracionRedCognitivaTest extends TestCase
{
    public function test_unifica_cinco_dominios_diecinieve_componentes_y_diecisiete_reglas_con_origen(): void
    {
        $g09 = PaqueteSemanticoG09Experimental::crear();
        $antes = $g09->declaracion();
        $i = (new PaqueteCincoDominiosExperimental)->integrar($g09);
        self::assertCount(5, $i['red']['dominios']);
        self::assertCount(19, $i['red']['componentes']);
        self::assertCount(17, $i['red']['rutas_reglas']);
        self::assertSame('COG-EJE', $i['red']['componentes']['EJE-S03']['dominio']);
        self::assertSame('INACTIVO', $i['red']['componentes']['MEM-S03']['estado']);
        self::assertSame('G09', $i['origenes']['reglas_expertas']['R-ATE-S01-P01']['familia']);
        self::assertSame($g09->version, $i['fuentes']['G09']['version']);
        foreach ($i['paquete']->declaracion()['tablas'] as $filas) {
            foreach ($filas as $fila) {
                if (isset($fila['cod_version_modelo'])) {
                    self::assertSame($i['paquete']->version, $fila['cod_version_modelo']);
                }
            }
        }
        self::assertSame($antes, $g09->declaracion());
        self::assertSame($i['paquete']->declaracion(), (new PaqueteCincoDominiosExperimental)->integrar($g09)['paquete']->declaracion());
    }

    public function test_ejecucion_integrada_conserva_reglas_evidencias_y_versiones_originales(): void
    {
        $entrada = CasoSinteticoCincoDominios::crear();
        $r = (new EjecutarPerfilExperimental)->ejecutar(...$entrada);
        self::assertSame($r['manifiesto_versiones']['COG-MEM'], $r['manifiesto_versiones']['G09']);
        foreach ($r['perfil_cinco_dominios'] as $dominio) {
            self::assertSame($r['conocimiento_integrado']['version'], $dominio['version']);
        }
        self::assertNotEmpty($r['traza_tecnica']['memoria']['rutas']);
        self::assertNotEmpty($r['reglas_examinadas']['g09']['R-ATE-S01-P01']['soportes_originales']);
        self::assertSame('MISMA_UNIDAD', $r['reglas_examinadas']['g09']['R-ATE-S01-P01']['alcance']);
        self::assertFalse($r['persistido']);
        self::assertSame($r, (new EjecutarPerfilExperimental)->ejecutar(...$r['instantanea_entrada']));
    }

    public function test_archivar_reglas_memoria_y_g09_no_reescribe_resultado_ni_conocimiento_original(): void
    {
        $p = (new PaqueteCincoDominiosExperimental)->integrar(PaqueteSemanticoG09Experimental::crear())['paquete'];
        $entrada = CasoSinteticoCincoDominios::crear();
        $entrada['conocimientoG09'] = $p->declaracion();
        $antes = (new EjecutarPerfilExperimental)->ejecutar(...$entrada);
        $sucesora = (new EditorPaqueteExperimental)->revisar($p, 'SIM-5-REV-2', [], ['RULE_A', 'R-ATE-S01-P01']);
        $entrada['conocimientoG09'] = $sucesora->declaracion();
        $despues = (new EjecutarPerfilExperimental)->ejecutar(...$entrada);
        self::assertSame('DIFICULTAD_EVIDENCIADA', $antes['resumen']['memoria']['resultado']['codigo']);
        self::assertNull($despues['resumen']['memoria']['resultado']);
        self::assertSame('SIN_REGLA_CON_SOPORTE', $despues['perfil_cinco_dominios']['COG-MEM']['salida']['abstencion']);
        self::assertSame('SIN_REGLA_ELEGIBLE', $despues['subcomponentes']['ATE-S01']['motivo_no_emision']);
        self::assertArrayNotHasKey('R-ATE-S01-P01', $despues['reglas_examinadas']['g09']);
        self::assertSame('ACTIVO', $p->fila('reglas_expertas', 'RULE_A')['estado']);
        self::assertSame($antes, (new EjecutarPerfilExperimental)->ejecutar(...$antes['instantanea_entrada']));
    }

    public function test_red_no_deduce_participacion_por_pertenencia_ni_acepta_propietario_cruzado(): void
    {
        $p = (new PaqueteCincoDominiosExperimental)->integrar(PaqueteSemanticoG09Experimental::crear())['paquete'];
        $d = $p->declaracion();
        foreach ($d['tablas']['variables_expertas'] as &$v) {
            if ($v['cod_variable_experta'] === 'VAR-R-ATE-S01-P01-C1') {
                $v['cod_nodo_propietario_primario'] = 'SUB-LEN-S01';
            }
        }
        unset($v);
        $invalido = new PaqueteConocimiento($p->version, $d['tablas'], $p->contratosRelaciones, true, 'RESULTADO', $p->contratosExtraccion);
        $this->expectException(DomainException::class);
        (new CompiladorPerfilCognitivo)->compilar($invalido);
    }

    public function test_no_convierte_un_paquete_institucional_en_prueba_tecnica(): void
    {
        $p = PaqueteSemanticoG09Experimental::crear();
        $real = new PaqueteConocimiento($p->version, $p->declaracion()['tablas'], $p->contratosRelaciones, false, 'RESULTADO');
        $this->expectException(DomainException::class);
        (new PaqueteCincoDominiosExperimental)->integrar($real);
    }

    public function test_perfil_completo_sin_manifiesto_ancestral_no_reatribuye_referencias_antiguas(): void
    {
        $p = (new PaqueteCincoDominiosExperimental)->integrar(PaqueteSemanticoG09Experimental::crear())['paquete'];
        $entrada = CasoSinteticoCincoDominios::crear(['COG-MEM' => 'A', 'COG-ATE' => 'P', 'COG-EJE' => 'P', 'COG-LEN' => 'P', 'COG-VIS' => 'P']);
        $entrada['conocimientoG09'] = $p->declaracion();
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('no conserva el manifiesto');
        (new EjecutarPerfilExperimental)->ejecutar(...$entrada);
    }

    public function test_perfil_completo_y_sucesora_admiten_referencias_independientes_de_su_version(): void
    {
        $p = (new PaqueteCincoDominiosExperimental)->integrar(PaqueteSemanticoG09Experimental::crear())['paquete'];
        $sucesora = (new EditorPaqueteExperimental)->revisar($p, 'SIM-5-REV-3', [], ['R-ATE-S01-P01']);
        foreach ([$p, $sucesora] as $version) {
            $entrada = CasoSinteticoCincoDominios::crear(['COG-MEM' => 'A', 'COG-ATE' => 'P', 'COG-EJE' => 'P', 'COG-LEN' => 'P', 'COG-VIS' => 'P']);
            $entrada['conocimientoG09'] = $version->declaracion();
            foreach ($entrada['referencias']['dominios'] as &$lista) {
                foreach ($lista as &$referencia) {
                    $referencia['version_conocimiento'] = $version->version;
                }
                unset($referencia);
            }
            unset($lista);
            $r = (new EjecutarPerfilExperimental)->ejecutar(...$entrada);
            self::assertSame('PET-01', $r['petersen']['patron_candidato']);
            self::assertSame($version->version, $r['conocimiento_integrado']['fuentes']['PERFIL']['version']);
            self::assertNotEmpty($r['conocimiento_integrado']['origenes']['reglas_expertas']);
            self::assertSame($entrada['referencias'], $r['fuentes_examinadas']['referencias_dcl']);
            self::assertSame($r, (new EjecutarPerfilExperimental)->ejecutar(...$r['instantanea_entrada']));
        }
    }

    public function test_proyeccion_distingue_condicion_null_falsa_y_fuente_ausente(): void
    {
        $caso = CasoSinteticoCincoDominios::crear();
        $base = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
        $p = (new PaqueteCincoDominiosExperimental)->integrar(PaqueteSemanticoG09Experimental::crear())['paquete'];
        foreach ([[null, 'NO_EVALUADA'], [false, 'NO_CUMPLE'], ['AUSENTE', 'NO_EVALUADA']] as [$valor, $esperado]) {
            $perfil = $base['perfil_experimental'];
            $e = $perfil['perfil']['COG-ATE']['salida']['resultado']['fuentes_examinadas'][0];
            $e['dificultad_especifica_test_only'] = $valor === 'AUSENTE' ? null : $valor;
            $contexto = $caso['contexto'] + ['criterio' => 'COG-ATE', 'evidencias' => $valor === 'AUSENTE' ? [] : [$e]];
            $fuente = (new EvaluadorG09Experimental)->evaluar($contexto);
            $fuente['inferencia_declarativa'] = (new EjecutorConocimientoG09Experimental)->evaluar($p, $contexto, $fuente);
            $perfil['perfil']['COG-ATE']['salida']['resultado'] = $fuente;
            $r = (new ConstructorResultadoExperimental)->construir($perfil, $caso['contexto'], null);
            if ($valor === 'AUSENTE') {
                self::assertArrayNotHasKey('R-ATE-S01-P01', $r['reglas_examinadas']['g09']);
            } else {
                self::assertSame($esperado, $r['reglas_examinadas']['g09']['R-ATE-S01-P01']['estado']);
                self::assertSame('NO_CUMPLE', $r['reglas_examinadas']['g09']['R-ATE-S01-P01']['estado_nucleo']);
            }
            self::assertSame([], $r['subcomponentes']['ATE-S01']['resultado']);
        }
    }

    public function test_dos_identificadores_del_mismo_soporte_declarado_no_son_independientes(): void
    {
        $p = EjemploTecnicoCOGMEM::paquete();
        $m = new MemoriaTrabajo(EjemploTecnicoCOGMEM::contexto());
        $a = (new AdaptadorHechosCOGMEM)->adaptar($p, $m,
            EjemploTecnicoCOGMEM::fuente(registro: 'SIM-REG-A', contexto: ['unidad_soporte' => 'SIM-EPISODIO']), 'MAP_INS');
        $b = (new AdaptadorHechosCOGMEM)->adaptar($p, $m,
            EjemploTecnicoCOGMEM::fuente(registro: 'SIM-REG-B', contexto: ['unidad_soporte' => 'SIM-EPISODIO']), 'MAP_INS');
        self::assertNotSame($a->id, $b->id);
        self::assertCount(1, $m->soportesEfectivos([$a->id, $b->id]));
        self::assertSame($a, $m->incorporar($a));
        self::assertCount(2, $m->todas());
    }

    public function test_referencias_mal_formadas_se_rechazan_sin_warnings(): void
    {
        $p = (new PaqueteCincoDominiosExperimental)->integrar(PaqueteSemanticoG09Experimental::crear())['paquete'];
        foreach (['texto', ['COG-MEM' => 'texto'], ['COG-MEM' => ['texto']]] as $dominios) {
            $caso = CasoSinteticoCincoDominios::crear();
            $caso['conocimientoG09'] = $p->declaracion();
            $caso['referencias'] = ['dominios' => $dominios];
            try {
                (new EjecutarPerfilExperimental)->ejecutar(...$caso);
                self::fail('Se admitió una estructura de referencias inválida.');
            } catch (DomainException $e) {
                self::assertStringContainsString('referencia', mb_strtolower($e->getMessage()));
            }
        }
    }

    public function test_una_unidad_ausente_o_distinta_no_separa_mapeos_del_mismo_original(): void
    {
        foreach ([[], ['unidad_soporte' => 'SIM-OTRA-UNIDAD']] as $contexto) {
            $p = EjemploTecnicoCOGMEM::paquete();
            $m = new MemoriaTrabajo(EjemploTecnicoCOGMEM::contexto());
            $a = (new AdaptadorHechosCOGMEM)->adaptar($p, $m,
                EjemploTecnicoCOGMEM::fuente(registro: 'SIM-REG-A', contexto: ['unidad_soporte' => 'SIM-UNIDAD']), 'MAP_INS');
            $b = new EvidenciaExperta('SIM-OTRO-MAPEO', $m->contexto,
                EjemploTecnicoCOGMEM::fuente(registro: 'SIM-REG-A', contexto: $contexto),
                'SIM-MAP-OTRO', $a->mapeoValor, $a->variable, $a->valor, $a->representacion, $a->admisibilidad, $a->motivos);
            $m->incorporar($b);
            self::assertCount(2, $m->todas());
            self::assertCount(1, $m->soportesEfectivos([$a->id, $b->id]));
        }
    }
}
