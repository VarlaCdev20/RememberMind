<?php

declare(strict_types=1);

namespace Tests\Unit;

use MotorExperto\aplicacion\casos_uso\EjecutarPerfilExperimental;
use MotorExperto\dominio\conocimiento\EditorPaqueteExperimental;
use MotorExperto\investigacion\dcl\CasoSinteticoCincoDominios as Caso;
use MotorExperto\investigacion\g09\CatalogoReglasG09Experimental;
use MotorExperto\investigacion\g09\EjecutorConocimientoG09Experimental;
use MotorExperto\investigacion\g09\EvaluadorG09Experimental;
use MotorExperto\investigacion\g09\PaqueteSemanticoG09Experimental;
use MotorExperto\investigacion\g09\VerificadorNucleoG09Experimental;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ReglasDeclarativasPerfilExperimentalTest extends TestCase
{
    private function fuente(): array
    {
        $base = ['uso' => 'TEST_ONLY', 'aislado' => true, 'residente_ficticio' => 'SINTETICO-A',
            'evaluacion' => 'SIM-A', 'version' => CatalogoReglasG09Experimental::VERSION, 'criterio' => 'COG-ATE',
            'fecha_corte' => '2026-10-17', 'contexto_suficiente' => true];
        $evidencia = ['id' => 'SIM-E1', 'regla' => 'R-ATE-S01-P01', 'residente_ficticio' => 'SINTETICO-A',
            'evaluacion' => 'SIM-A', 'version' => CatalogoReglasG09Experimental::VERSION, 'fecha' => '2026-10-16 10:00:00',
            'fuente' => 'SIM-SRC', 'episodio' => 'SIM-EP', 'procedencia_verificada' => true, 'instrumento_test_only' => true,
            'tarea_atribuible' => true, 'confusor_invalidante' => false, 'dificultad_especifica_test_only' => true,
            'atribucion_test_only' => true, 'contexto_regla_suficiente' => true];

        return [$base, (new EvaluadorG09Experimental)->evaluar($base + ['evidencias' => [$evidencia]])];
    }

    public static function contextosAjenos(): array
    {
        return [['residente_ficticio', 'SINTETICO-B'], ['evaluacion', 'SIM-B'], ['version', 'SIM-OTRA'],
            ['fecha_corte', '2026-10-15'], ['fecha_corte', '2026-10-18'], ['criterio', 'COG-LEN']];
    }

    #[DataProvider('contextosAjenos')]
    public function test_no_reasigna_un_resultado_fuente_a_otro_contexto(string $campo, string $valor): void
    {
        [$base, $resultado] = $this->fuente();
        $base[$campo] = $valor;
        $this->expectException(\DomainException::class);
        (new EjecutorConocimientoG09Experimental)->evaluar(PaqueteSemanticoG09Experimental::crear(), $base, $resultado);
    }

    public static function trazasAlteradas(): array
    {
        return [['evidencia', 'SIM-OTRA'], ['regla', 'R-ATE-S02-P01'], ['fecha_fuente', '2026-10-15 10:00:00'],
            ['episodio_fuente', 'SIM-OTRO'], ['condiciones', ['C1' => false, 'C2' => true, 'C3' => true]]];
    }

    #[DataProvider('trazasAlteradas')]
    public function test_no_confia_en_trazas_editadas_sin_su_fuente_original(string $campo, mixed $valor): void
    {
        [$base, $resultado] = $this->fuente();
        $resultado['traza'][0][$campo] = $valor;
        $this->expectException(\DomainException::class);
        (new EjecutorConocimientoG09Experimental)->evaluar(PaqueteSemanticoG09Experimental::crear(), $base, $resultado);
    }

    #[DataProvider('contextosAjenos')]
    public function test_cotejo_no_afirma_contexto_original_de_otro_caso(string $campo, string $valor): void
    {
        [$base, $resultado] = $this->fuente();
        $base[$campo] = $valor;
        $this->expectException(\DomainException::class);
        (new VerificadorNucleoG09Experimental)->cotejar($resultado, $base);
    }

    #[DataProvider('trazasAlteradas')]
    public function test_cotejo_con_contexto_rechaza_la_traza_editada(string $campo, mixed $valor): void
    {
        [$base, $resultado] = $this->fuente();
        $resultado['traza'][0][$campo] = $valor;
        $this->expectException(\DomainException::class);
        (new VerificadorNucleoG09Experimental)->cotejar($resultado, $base);
    }

    public function test_fuente_sin_evidencias_conserva_contexto_y_rechaza_reasignacion(): void
    {
        [$base] = $this->fuente();
        $resultado = (new EvaluadorG09Experimental)->evaluar($base);
        $base['evaluacion'] = 'SIM-OTRA';
        $this->expectException(\DomainException::class);
        (new EjecutorConocimientoG09Experimental)->evaluar(PaqueteSemanticoG09Experimental::crear(), $base, $resultado);
    }

    public function test_contexto_horario_y_revision_declarativa_separada_conservan_sus_identidades(): void
    {
        [$base, $resultado] = $this->fuente();
        $base['fecha_corte'] = '2026-10-17 09:00:00';
        $resultado = (new EvaluadorG09Experimental)->evaluar($base + ['evidencias' => $resultado['fuentes_examinadas']]);
        $p = (new EditorPaqueteExperimental)->revisar(PaqueteSemanticoG09Experimental::crear(), 'SIM-REV-OTRA', [], []);
        $r = (new EjecutorConocimientoG09Experimental)->evaluar($p, $base, $resultado);
        self::assertSame('SIM-REV-OTRA', $r['version_conocimiento']);
        self::assertArrayHasKey('ATE-R-SOSTENIMIENTO', $r['consecuencias']);
    }

    public function test_paquete_incompleto_se_rechaza_sin_errores_de_indices(): void
    {
        $caso = Caso::crear();
        $caso['conocimientoG09'] = ['solo_pruebas_tecnicas' => true];
        $this->expectException(\DomainException::class);
        (new EjecutarPerfilExperimental)->ejecutar(...$caso);
    }

    public function test_codigos_duplicados_no_ocultan_reglas_o_soportes_en_la_explicacion(): void
    {
        $p = PaqueteSemanticoG09Experimental::crear();
        $d = $p->declaracion();
        $d['tablas']['reglas_expertas'][1]['codigo_regla'] = $d['tablas']['reglas_expertas'][0]['cod_regla_experta'];
        $caso = Caso::crear();
        $caso['conocimientoG09'] = $d;
        $this->expectException(\DomainException::class);
        (new EjecutarPerfilExperimental)->ejecutar(...$caso);
    }

    public function test_compuerta_cerrada_no_presenta_reglas_base_como_ejecutadas(): void
    {
        $caso = Caso::crear();
        $caso['conocimientoG09'] = PaqueteSemanticoG09Experimental::crear()->declaracion();
        unset($caso['contexto']['controles_tareas']);
        $r = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
        self::assertSame([], $r['reglas_examinadas']['g09']);
        self::assertSame('COMPUERTA_NO_HABILITA', $r['subcomponentes']['ATE-S01']['motivo_no_emision']);
        self::assertNotEmpty($r['reglas_examinadas']['condiciones_fuente_g09']);
    }

    public function test_regla_nueva_cambia_el_perfil_por_datos_y_sucesora_no_modifica_historia(): void
    {
        $p = PaqueteSemanticoG09Experimental::crear();
        $regla = ['id' => 'SIM-ATE-NUEVA', 'codigo' => 'SIM-DOS-TAREAS', 'nombre' => 'Conjunción ficticia entre dos tareas',
            'descripcion' => 'Regla de ingeniería, exclusivamente sintética.', 'fundamento' => 'Caso de software sin equivalencia clínica.',
            'condiciones' => [['variable' => 'VAR-R-ATE-S01-P01-C1', 'valor' => 'VAL-SIM-TRUE'],
                ['variable' => 'VAR-R-ATE-S03-P01-C1', 'valor' => 'VAL-SIM-TRUE']],
            'criterio_dominio' => 'CR-ATE', 'valor_consecuencia' => 'VAL-RES-R-ATE-S02-P01'];
        $revision = (new EditorPaqueteExperimental)->revisar($p, 'SIM-G09-REV-2', [$regla], ['R-ATE-S02-P01']);
        $caso = Caso::crear();
        $caso['capturas']['respuestas_instrumento'][1]['valor_texto'] = 'SIN_HALLAZGO_SIMULADO';
        $base = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
        self::assertSame([], $base['subcomponentes']['ATE-S02']['resultado']);
        $caso['conocimientoG09'] = $revision->declaracion();
        $nuevo = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
        self::assertNotEmpty($nuevo['subcomponentes']['ATE-S02']['resultado']);
        self::assertSame(['SIM-DOS-TAREAS'], $nuevo['subcomponentes']['ATE-S02']['reglas']);
        self::assertSame('CUMPLE', $nuevo['reglas_examinadas']['g09']['SIM-DOS-TAREAS']['estado']);
        self::assertArrayNotHasKey('R-ATE-S02-P01', $nuevo['reglas_examinadas']['g09']);
        self::assertSame('SIM-G09-REV-2', $nuevo['conocimiento_integrado']['fuentes']['G09']['version']);
        self::assertSame($nuevo['manifiesto_versiones']['COG-MEM'], $nuevo['manifiesto_versiones']['G09']);
        self::assertSame($nuevo, (new EjecutarPerfilExperimental)->ejecutar(...$nuevo['instantanea_entrada']));
        $caso['capturas']['respuestas_instrumento'][2]['valor_texto'] = 'SIN_HALLAZGO_SIMULADO';
        $negativo = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
        self::assertSame([], $negativo['subcomponentes']['ATE-S02']['resultado']);
        self::assertSame('NO_CUMPLE', $negativo['reglas_examinadas']['g09']['SIM-DOS-TAREAS']['estado']);
        self::assertSame('ACTIVO', $p->fila('reglas_expertas', 'R-ATE-S02-P01')['estado']);
        self::assertSame($base, (new EjecutarPerfilExperimental)->ejecutar(...$base['instantanea_entrada']));
    }

    public function test_condiciones_parciales_de_dos_aplicaciones_no_completan_una_conjuncion_de_tarea(): void
    {
        [$base, $original] = $this->fuente();
        $a = $original['fuentes_examinadas'][0];
        $b = array_replace($a, ['id' => 'SIM-E2', 'episodio' => 'SIM-EP2', 'dificultad_especifica_test_only' => false]);
        $a['atribucion_test_only'] = false;
        $resultado = (new EvaluadorG09Experimental)->evaluar($base + ['evidencias' => [$a, $b]]);
        self::assertSame([], $resultado['consecuencias_candidatas']);
        $r = (new EjecutorConocimientoG09Experimental)->evaluar(PaqueteSemanticoG09Experimental::crear(), $base, $resultado);
        self::assertSame('EVALUABLE_SIN_SOPORTE', $r['estado_tecnico']);
        self::assertSame([], $r['consecuencias']);
        self::assertSame('NO_CUMPLE', $r['reglas']['R-ATE-S01-P01']['estado']);
        self::assertCount(2, $r['reglas']['R-ATE-S01-P01']['evaluaciones_unidad']);
        self::assertSame('COINCIDE', (new VerificadorNucleoG09Experimental)->cotejar($resultado, $base)['coincidencia']);
    }

    public function test_regla_multicomponente_no_mezcla_aplicaciones_parciales_del_mismo_componente(): void
    {
        [$base, $original] = $this->fuente();
        $a = $original['fuentes_examinadas'][0];
        $b = array_replace($a, ['id' => 'SIM-E2', 'episodio' => 'SIM-EP2', 'dificultad_especifica_test_only' => false]);
        $c = array_replace($a, ['id' => 'SIM-E3', 'episodio' => 'SIM-EP3', 'regla' => 'R-ATE-S03-P01']);
        $a['atribucion_test_only'] = false;
        $p = (new EditorPaqueteExperimental)->revisar(PaqueteSemanticoG09Experimental::crear(), 'SIM-MULTI-2', [[
            'id' => 'SIM-MULTI', 'codigo' => 'SIM-MULTI', 'nombre' => 'Dos componentes explícitos', 'descripcion' => 'Regresión de alcance.',
            'fundamento' => 'No combinar condiciones de dos aplicaciones de una misma tarea.',
            'condiciones' => [['variable' => 'VAR-R-ATE-S01-P01-C1', 'valor' => 'VAL-SIM-TRUE'],
                ['variable' => 'VAR-R-ATE-S01-P01-C2', 'valor' => 'VAL-SIM-TRUE'],
                ['variable' => 'VAR-R-ATE-S03-P01-C1', 'valor' => 'VAL-SIM-TRUE']],
            'criterio_dominio' => 'CR-ATE', 'valor_consecuencia' => 'VAL-RES-R-ATE-S02-P01',
        ]]);
        $fuente = (new EvaluadorG09Experimental)->evaluar($base + ['evidencias' => [$a, $b, $c]]);
        $r = (new EjecutorConocimientoG09Experimental)->evaluar($p, $base, $fuente);
        self::assertSame('MULTICOMPONENTE_EXPLICITO', $r['reglas']['SIM-MULTI']['alcance']);
        self::assertSame('NO_CUMPLE', $r['reglas']['SIM-MULTI']['estado']);
        self::assertArrayNotHasKey('ATE-R-SELECCION', $r['consecuencias']);
        $a['atribucion_test_only'] = true;
        $fuente = (new EvaluadorG09Experimental)->evaluar($base + ['evidencias' => [$a, $b, $c]]);
        $r = (new EjecutorConocimientoG09Experimental)->evaluar($p, $base, $fuente);
        self::assertSame('CUMPLE', $r['reglas']['SIM-MULTI']['estado']);
        self::assertArrayHasKey('ATE-R-SELECCION', $r['consecuencias']);
    }
}
