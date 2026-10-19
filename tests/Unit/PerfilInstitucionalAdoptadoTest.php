<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\ArchivoConocimientoOrion;
use DateTimeImmutable;
use MotorExperto\aplicacion\casos_uso\EvaluarPerfilInstitucional;
use MotorExperto\dominio\conocimiento\AdmisibilidadCognitivaInstitucional;
use MotorExperto\dominio\conocimiento\AdmisibilidadFuenteCOGMEM;
use MotorExperto\dominio\conocimiento\MapeadorSemantico;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\conocimiento\PerfilConceptualCognitivo;
use MotorExperto\dominio\conocimiento\versiones\PaqueteCognitivoInstitucional as P;
use MotorExperto\dominio\inferencia\MemoriaTrabajo;
use MotorExperto\dominio\objetos_valor\ContextoEjecucion;
use MotorExperto\dominio\objetos_valor\FuenteBrutaCOGMEM;
use PHPUnit\Framework\TestCase;

/** Casos sintéticos de software; ningún predicado se importa como historia clínica. */
final class PerfilInstitucionalAdoptadoTest extends TestCase
{
    private function paquete(): PaqueteConocimiento
    {
        $red = (new PerfilConceptualCognitivo)->construir((new ArchivoConocimientoOrion)->leer());
        $p = (new P)->construir($red, 'VERSION-SINTETICA', 'AUTOR-SINTETICO', '2026-10-19 08:00:00');
        $t = $p->declaracion()['tablas'];
        $t['fuentes_datos_expertas'][] = ['cod_fuente_dato_experta' => 'FUENTE-SINTETICA', 'cod_version_modelo' => $p->version,
            'tabla_raiz' => 'aplicaciones_instrumento', 'estado' => 'ACTIVO'];
        foreach ($t['variables_expertas'] as $v) {
            $codigo = $p->red->nodo($v['cod_nodo_semantico'])['codigo_semantico'];
            if (str_starts_with($codigo, 'VAR-OBS-')) {
                continue;
            }
            $mapa = P::id('M', $codigo);
            $t['mapeos_variables_fuente'][] = ['cod_mapeo_variable_fuente' => $mapa, 'cod_version_modelo' => $p->version,
                'cod_variable_experta' => $v['cod_variable_experta'], 'cod_fuente_dato_experta' => 'FUENTE-SINTETICA',
                'tipo_extraccion' => 'RESPUESTA_INSTITUCIONAL', 'campo_valor' => null, 'clave_selector' => $codigo, 'estado' => 'ACTIVO'];
            $valores = $codigo === 'VAR-MEM-INST-01' ? ['DIFICULTAD_INSTRUMENTAL', 'SIN_DIFICULTAD_INSTRUMENTAL'] : ['ACREDITADA'];
            foreach ($valores as $valor) {
                $t['mapeos_valores_fuente'][] = ['cod_mapeo_valor_fuente' => P::id('L', $codigo.$valor), 'cod_mapeo_variable_fuente' => $mapa,
                    'cod_valor_semantico' => P::id('V', $valor), 'tipo_valor_fuente' => 'string', 'valor_fuente_exacto' => $valor,
                    'estado' => 'ACTIVO', 'estado_aprobacion' => 'APROBADO', 'fecha_hora_vigencia_desde' => null, 'fecha_hora_vigencia_hasta' => null];
            }
        }

        return new PaqueteConocimiento($p->version, $t, $p->contratosRelaciones, false, $p->tipoReglaResultado, $p->contratosExtraccion);
    }

    private function memoria(PaqueteConocimiento $p): MemoriaTrabajo
    {
        return new MemoriaTrabajo(new ContextoEjecucion('EJEC-SINTETICA', 'RES-SINTETICO', $p->version, new DateTimeImmutable('2026-10-19T12:00:00-04:00')));
    }

    private function hecho(PaqueteConocimiento $p, MemoriaTrabajo $m, string $codigo, string $valor = 'ACREDITADA', string $aplicacion = 'APP-1', array $contexto = []): void
    {
        $componente = $codigo === 'VAR-MEM-INST-01' ? 'MEM-S01' : substr($codigo, 4, 7);
        $perfil = ['codigo' => 'PERFIL-SINTETICO', 'version' => '1', 'componente' => $componente,
            'instrumento' => ['codigo' => 'PROTOCOLO-SINTETICO', 'version' => '1'], 'metodo' => 'Interpretación sintética de ingeniería',
            'fuente' => 'PROTOCOLO-PRUEBA-1', 'predicados' => ['C1', 'C2', 'C3', 'MEMORIA'],
            'mediciones_requeridas' => ['aciertos' => ['cod_pregunta' => 'PREG-MEDIDA', 'campo' => 'valor_numero']]];
        $f = new FuenteBrutaCOGMEM('FUENTE-SINTETICA', 'aplicaciones_instrumento', $codigo, $aplicacion, $m->contexto->residente,
            new DateTimeImmutable('2026-10-18T09:00:00-04:00'), $valor, $valor, 'PROFESIONAL-TEST', true, true,
            contexto: $contexto + ['unidad_conjuncion' => $aplicacion, 'unidad_soporte' => $aplicacion,
                'instrumento' => ['tipo' => 'COGNITIVO', 'codigo' => 'PROTOCOLO-SINTETICO', 'version' => '1'],
                'componente_instrumental_verificado' => true, 'componente_declarado' => $componente,
                'perfil_interpretativo' => $perfil, 'mediciones_interpretacion' => ['aciertos' => ['cod_aplicacion' => $aplicacion,
                    'cod_pregunta' => 'PREG-MEDIDA', 'cod_respuesta' => 'RESP-MEDIDA', 'campo' => 'valor_numero', 'valor_original' => '2.0000']]]);
        $e = (new MapeadorSemantico)->mapear($p, $m->contexto, $f, P::id('M', $codigo));
        $e = (new AdmisibilidadCognitivaInstitucional)->validar($p, $e);
        if ($codigo === 'VAR-MEM-INST-01') {
            $e = (new AdmisibilidadFuenteCOGMEM)->validar($p, $e);
        }
        $m->incorporar($e);
        foreach ($p->tabla('criterios_dominios_resultado') as $c) {
            if ($p->variableParticipa($e->variable, $c['cod_nodo_criterio'])) {
                $m->vincular($c['cod_nodo_criterio'], $e->id, 'FUENTE_SINTETICA');
            }
        }
    }

    public function test_cada_regla_g09_exige_sus_tres_antecedentes_y_conserva_la_ruta_de_soporte(): void
    {
        $p = $this->paquete();
        // Oráculo independiente del constructor, transcrito de la tabla B del contrato adoptado.
        $esperadas = ['ATE-S01' => 'ATE-R-SOSTENIMIENTO', 'ATE-S02' => 'ATE-R-SELECCION', 'ATE-S03' => 'ATE-R-DISTRIBUCION',
            'EJE-S01' => 'EJE-R-INHIBICION', 'EJE-S02' => 'EJE-R-FLEXIBILIDAD', 'EJE-S03' => 'EJE-R-ACTUALIZACION',
            'EJE-S04' => 'EJE-R-PLANIFICACION', 'EJE-S05' => 'EJE-R-RAZONAMIENTO',
            'LEN-S01' => 'LEN-R-DENOMINACION', 'LEN-S02' => 'LEN-R-FLUIDEZ', 'LEN-S03' => 'LEN-R-COMPRENSION',
            'LEN-S04' => 'LEN-R-REPETICION', 'LEN-S05' => 'LEN-R-DISCURSO', 'VIS-S01' => 'VIS-R-PERCEPCION', 'VIS-S02' => 'VIS-R-CONSTRUCCION'];
        foreach ($esperadas as $componente => $consecuencia) {
            foreach ([null, 'C1', 'C2', 'C3'] as $ausente) {
                $m = $this->memoria($p);
                foreach (['C1', 'C2', 'C3'] as $c) {
                    if ($c !== $ausente) {
                        $this->hecho($p, $m, 'VAR-'.$componente.'-'.$c);
                    }
                }
                $d = (new EvaluarPerfilInstitucional)->ejecutar($p, $m)['perfil']['COG-'.substr($componente, 0, 3)];
                if ($ausente !== null) {
                    self::assertSame([], $d['conclusiones_candidatas'], $componente.' sin '.$ausente);
                } else {
                    self::assertCount(1, $d['conclusiones_candidatas']);
                    $r = array_values($d['conclusiones_candidatas'])[0];
                    self::assertSame($consecuencia, $r['codigo']);
                    self::assertCount(3, $r['evidencias']);
                    self::assertCount(1, $r['soportes_efectivos']);
                    self::assertCount(3, $d['traza']['rutas']);
                }
            }
        }
    }

    public function test_observacion_perfil_ausente_atribucion_erronea_o_conjuncion_repartida_no_acreditan_g09(): void
    {
        $p = $this->paquete();
        foreach ([['instrumento' => ['tipo' => 'OBSERVACIONAL']], ['perfil_interpretativo' => 'NO-EXISTE'], ['componente_declarado' => 'LEN-S01'], ['unidad_soporte' => 'atencion:COMUN'], ['mediciones_interpretacion' => []], ['instrumento' => ['tipo' => 'COGNITIVO', 'codigo' => 'PROTOCOLO-SINTETICO', 'version' => 'OTRA']]] as $i => $contexto) {
            $m = $this->memoria($p);
            foreach (['C1', 'C2', 'C3'] as $c) {
                $this->hecho($p, $m, 'VAR-ATE-S01-'.$c, aplicacion: $i === 3 ? 'APP-'.$c : 'APP-1', contexto: $contexto);
            }
            $d = (new EvaluarPerfilInstitucional)->ejecutar($p, $m)['perfil']['COG-ATE'];
            self::assertSame([], $d['conclusiones_candidatas']);
        }
    }

    public function test_memoria_no_toma_contexto_de_otra_aplicacion_y_no_depende_del_orden(): void
    {
        $p = $this->paquete();
        foreach ([false, true] as $inverso) {
            foreach (['APP-1', 'APP-OTRA'] as $appContexto) {
                $m = $this->memoria($p);
                $codigos = ['VAR-MEM-INST-01', 'VAR-CTX-MEM-COMPONENTE', 'VAR-CTX-MEM-CONDICIONES', 'VAR-CTX-MEM-CONFUSORES'];
                foreach ($inverso ? array_reverse($codigos) : $codigos as $codigo) {
                    $instrumental = $codigo === 'VAR-MEM-INST-01';
                    $this->hecho($p, $m, $codigo, $instrumental ? 'DIFICULTAD_INSTRUMENTAL' : 'ACREDITADA', $instrumental ? 'APP-1' : $appContexto);
                }
                $d = (new EvaluarPerfilInstitucional)->ejecutar($p, $m)['perfil']['COG-MEM'];
                self::assertSame($appContexto === 'APP-1' ? 'EV-CM-2' : 'EV-CM-1', $d['compuerta_institucional']['estado']);
                self::assertSame($appContexto !== 'APP-1', $d['resultado_institucional'] === null);
            }
        }
    }

    public function test_tarea_de_memoria_sin_contexto_no_invalida_otra_tarea_completa(): void
    {
        $p = $this->paquete();
        $m = $this->memoria($p);
        $this->hecho($p, $m, 'VAR-MEM-INST-01', 'DIFICULTAD_INSTRUMENTAL');
        foreach (['COMPONENTE', 'CONDICIONES', 'CONFUSORES'] as $c) {
            $this->hecho($p, $m, 'VAR-CTX-MEM-'.$c);
        }
        $this->hecho($p, $m, 'VAR-MEM-INST-01', 'SIN_DIFICULTAD_INSTRUMENTAL', 'APP-INCOMPLETA');
        $d = (new EvaluarPerfilInstitucional)->ejecutar($p, $m)['perfil']['COG-MEM'];
        self::assertSame('EV-CM-2', $d['compuerta_institucional']['estado']);
        self::assertSame('DIFICULTAD_EVIDENCIADA', $d['resultado_institucional']['codigo']);
        self::assertCount(1, $d['antecedentes_descartados_por_contexto']);
        self::assertSame('APP-INCOMPLETA', $d['antecedentes_descartados_por_contexto'][0]['aplicacion']);
    }

    public function test_hallazgos_opuestos_en_un_mismo_episodio_suspenden_memoria(): void
    {
        $p = $this->paquete();
        $m = $this->memoria($p);
        foreach (['APP-1' => 'DIFICULTAD_INSTRUMENTAL', 'APP-2' => 'SIN_DIFICULTAD_INSTRUMENTAL'] as $app => $valor) {
            foreach (['VAR-MEM-INST-01', 'VAR-CTX-MEM-COMPONENTE', 'VAR-CTX-MEM-CONDICIONES', 'VAR-CTX-MEM-CONFUSORES'] as $codigo) {
                $this->hecho($p, $m, $codigo, $codigo === 'VAR-MEM-INST-01' ? $valor : 'ACREDITADA', $app, ['unidad_soporte' => 'atencion:COMPARTIDA']);
            }
        }
        $d = (new EvaluarPerfilInstitucional)->ejecutar($p, $m)['perfil']['COG-MEM'];
        self::assertNotEmpty($d['contradicciones']);
        self::assertSame('EVIDENCIA_CONTRADICTORIA', $d['estado_institucional']);
        self::assertNull($d['resultado_institucional']);
        self::assertSame([], $d['conclusiones_candidatas']);
    }
}
