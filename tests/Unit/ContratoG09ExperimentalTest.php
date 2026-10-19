<?php

declare(strict_types=1);

namespace Tests\Unit;

use DomainException;
use MotorExperto\aplicacion\casos_uso\EjecutarPerfilExperimental;
use MotorExperto\investigacion\dcl\CasoSinteticoCincoDominios as Caso;
use MotorExperto\investigacion\g09\CatalogoReglasG09Experimental as Catalogo;
use MotorExperto\investigacion\g09\ControlesTareaG09Experimental as Controles;
use MotorExperto\investigacion\g09\EjecutorConocimientoG09Experimental;
use MotorExperto\investigacion\g09\EvaluadorG09Experimental;
use MotorExperto\investigacion\g09\PaqueteSemanticoG09Experimental;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Matriz VT01–VT15 de G09-EXP: pruebas de ingeniería para las quince reglas. */
final class ContratoG09ExperimentalTest extends TestCase
{
    public static function reglas(): iterable
    {
        foreach (Catalogo::todas() as $regla => $meta) {
            yield $regla => [$regla, $meta];
        }
    }

    public static function condiciones(): iterable
    {
        foreach (self::reglas() as $regla => [$id, $meta]) {
            foreach (['C1' => 'dificultad_especifica_test_only', 'C2' => 'atribucion_test_only', 'C3' => 'contexto_regla_suficiente'] as $codigo => $campo) {
                foreach (['false', 'null', 'ausente'] as $estado) {
                    yield $regla.'/'.$codigo.'/'.$estado => [$id, $meta, $codigo, $campo, $estado];
                }
            }
        }
    }

    #[DataProvider('reglas')]
    public function test_vt01_vt03_ausencia_null_texto_sin_mapeo_y_completa_sin_respuesta_no_emiten(string $regla, array $meta): void
    {
        $original = Caso::crear();
        $indice = array_search($regla, array_column($original['tareas'], 'regla'), true);
        foreach (['ausente', 'null', 'no_mapeado', 'sin_respuesta'] as $modo) {
            $caso = $original;
            if ($modo === 'ausente') {
                unset($caso['tareas'][$indice]);
                $caso['tareas'] = array_values($caso['tareas']);
            } elseif ($modo === 'sin_respuesta') {
                unset($caso['capturas']['respuestas_instrumento'][$indice]);
                $caso['capturas']['respuestas_instrumento'] = array_values($caso['capturas']['respuestas_instrumento']);
            } else {
                $caso['capturas']['respuestas_instrumento'][$indice]['valor_texto'] = $modo === 'null' ? null : 'TEXTO_SIN_MAPEO';
            }
            $r = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
            self::assertSame([], $r['subcomponentes'][$meta['componente']]['resultado'], $modo);
            self::assertSame('NO_EVALUADA', $r['reglas_examinadas']['g09'][$regla]['estado'], $modo);
            self::assertNotNull($r['subcomponentes'][$meta['componente']]['motivo_no_emision'], $modo);
        }
    }

    #[DataProvider('reglas')]
    public function test_vt01_false_sin_mapeo_textual_se_rechaza_por_tipo_y_no_se_convierte_en_normalidad(string $regla, array $meta): void
    {
        $caso = Caso::crear();
        $indice = array_search($regla, array_column($caso['tareas'], 'regla'), true);
        $caso['capturas']['respuestas_instrumento'][$indice]['valor_texto'] = false;
        $this->expectException(DomainException::class);
        (new EjecutarPerfilExperimental)->ejecutar(...$caso);
    }

    #[DataProvider('reglas')]
    public function test_vt04_edicion_original_incompatible_se_conserva_y_no_emite(string $regla, array $meta): void
    {
        $caso = Caso::crear();
        $indice = array_search($regla, array_column($caso['tareas'], 'regla'), true);
        $caso['capturas']['instrumentos'][$indice]['version'] = 'SIM-OTRA-EDICION';
        $r = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
        self::assertSame([], $r['subcomponentes'][$meta['componente']]['resultado']);
        $informe = $r['resumen']['g09'][$meta['criterio']];
        $ruta = array_values(array_filter($informe['rutas_explicacion'], static fn ($f) => $f['regla_fuente'] === $regla))[0];
        self::assertSame('SIM-OTRA-EDICION', $ruta['edicion_original']);
        self::assertNotEmpty($ruta['motivos_no_admision']);
        self::assertStringContainsString('SIM-OTRA-EDICION', $informe['como_se_llego']);
    }

    #[DataProvider('reglas')]
    public function test_vt02_senal_y_tarea_sin_contexto_se_mantienen_en_ev1(string $regla, array $meta): void
    {
        $entrada = $this->entrada($regla, $meta);
        $entrada['contexto_suficiente'] = false;
        foreach ([true, false] as $conTarea) {
            $entrada['senal_contextual'] = true;
            if (! $conTarea) {
                $entrada['evidencias'] = [];
            }
            $r = (new EvaluadorG09Experimental)->evaluar($entrada);
            self::assertStringEndsWith('-1', $r['evaluabilidad']);
            self::assertSame('SOLO_SENAL_CONTEXTO', $r['estado_tecnico']);
            self::assertSame([], $r['consecuencias_candidatas']);
            self::assertSame([], $r['traza']);
        }
    }

    #[DataProvider('reglas')]
    public function test_vt04_cada_control_a01_a14_negativo_o_desconocido_impide_solo_su_tarea(string $regla, array $meta): void
    {
        $original = Caso::crear();
        $app = 'SIM-'.$meta['componente'];
        foreach (Controles::CONTROLES as $control) {
            foreach ([false, null] as $valor) {
                $caso = $original;
                $caso['contexto']['controles_tareas'][$app]['atestaciones'][$control] = $valor;
                $r = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
                self::assertSame([], $r['subcomponentes'][$meta['componente']]['resultado']);
                self::assertSame('NO_EVALUADA', $r['reglas_examinadas']['g09'][$regla]['estado']);
                self::assertCount(14, $r['evidencias_admitidas']['g09']);
                $rechazo = array_values(array_filter($r['evidencias_excluidas']['g08'], static fn ($e) => $e['regla'] === $regla))[0];
                self::assertContains($control.($valor === null ? '_DESCONOCIDO' : '_NO_ACREDITADO'), $rechazo['motivos']);
            }
        }
    }

    #[DataProvider('condiciones')]
    public function test_vt06_vt08_cada_condicion_false_null_o_ausente_impide_la_regla(string $regla, array $meta, string $codigo, string $campo, string $estado): void
    {
        $entrada = $this->entrada($regla, $meta);
        if ($estado === 'ausente') {
            unset($entrada['evidencias'][0][$campo]);
        } else {
            $entrada['evidencias'][0][$campo] = $estado === 'false' ? false : null;
        }
        $r = (new EvaluadorG09Experimental)->evaluar($entrada);
        self::assertSame([], $r['consecuencias_candidatas']);
        self::assertSame('EVALUABLE_SIN_SOPORTE', $r['estado_tecnico']);
        self::assertSame($estado === 'false' ? false : null, $r['traza'][0]['condiciones'][$codigo]);
        self::assertSame($estado === 'false' ? 'NO_CUMPLE' : 'NO_EVALUABLE', $r['traza'][0]['estados_condiciones'][$codigo]);
        $d = (new EjecutorConocimientoG09Experimental)->evaluar(PaqueteSemanticoG09Experimental::crear(), $entrada, $r);
        self::assertSame([], $d['consecuencias']);
        self::assertNotSame('CUMPLE', $d['reglas'][$regla]['estado']);
    }

    #[DataProvider('reglas')]
    public function test_vt09_otro_residente_version_o_fecha_futura_se_excluyen(string $regla, array $meta): void
    {
        foreach (['residente_ficticio' => 'SINTETICO-OTRO', 'version' => 'SIM-OTRA-VERSION', 'fecha' => '2026-10-18 00:00:00'] as $campo => $valor) {
            $entrada = $this->entrada($regla, $meta);
            $entrada['evidencias'][0][$campo] = $valor;
            $r = (new EvaluadorG09Experimental)->evaluar($entrada);
            self::assertSame([], $r['evidencias_admitidas']);
            self::assertSame([], $r['consecuencias_candidatas']);
            self::assertCount(1, $r['excluidas']);
        }
    }

    #[DataProvider('reglas')]
    public function test_vt09_lector_rechaza_aplicacion_de_otro_residente(string $regla, array $meta): void
    {
        $caso = Caso::crear();
        $indice = array_search($regla, array_column($caso['tareas'], 'regla'), true);
        $caso['capturas']['aplicaciones_instrumento'][$indice]['cod_residente'] = 'SINTETICO-OTRO';
        $this->expectException(DomainException::class);
        (new EjecutarPerfilExperimental)->ejecutar(...$caso);
    }

    #[DataProvider('reglas')]
    public function test_vt10_confusor_de_una_tarea_no_invalida_la_vecina_admisible(string $regla, array $meta): void
    {
        $caso = Caso::crear();
        $app = 'SIM-'.$meta['componente'];
        $confusor = 'CONF-'.substr($meta['componente'], 0, 3).'-01';
        $caso['contexto']['controles_tareas'][$app]['confusores'] = [['codigo' => $confusor,
            'presente' => true, 'efecto' => 'INVALIDANTE', 'fuente' => 'SIM-REVISION-TAREA', 'justificacion' => 'Invalidación ficticia de esta tarea solamente.']];
        $r = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
        self::assertSame([], $r['subcomponentes'][$meta['componente']]['resultado']);
        self::assertCount(14, $r['evidencias_admitidas']['g09']);
        foreach (Catalogo::todas() as $otra => $vecina) {
            if ($otra !== $regla && $vecina['criterio'] === $meta['criterio']) {
                self::assertSame('CUMPLE', $r['reglas_examinadas']['g09'][$otra]['estado']);
                self::assertNotEmpty($r['subcomponentes'][$vecina['componente']]['resultado']);
            }
        }
    }

    #[DataProvider('reglas')]
    public function test_vt11_corroboraciones_conservan_originales_pero_no_duplican_soporte_efectivo(string $regla, array $meta): void
    {
        $entrada = $this->entrada($regla, $meta);
        $segunda = $entrada['evidencias'][0];
        $segunda['id'] = 'SIM-E2';
        $segunda['fuente'] = 'SIM-F2';
        $entrada['evidencias'][] = $segunda;
        foreach ([false, true] as $episodioIndependiente) {
            if ($episodioIndependiente) {
                $entrada['evidencias'][1]['episodio'] = 'SIM-EPISODIO-2';
            }
            $fuente = (new EvaluadorG09Experimental)->evaluar($entrada);
            $d = (new EjecutorConocimientoG09Experimental)->evaluar(PaqueteSemanticoG09Experimental::crear(), $entrada, $fuente);
            self::assertCount($episodioIndependiente ? 2 : 1, $d['consecuencias'][$meta['consecuencia']]);
            self::assertSame($fuente['consecuencias_candidatas'], $d['consecuencias']);
            self::assertSame(['SIM-E1', 'SIM-E2'], $d['reglas'][$regla]['soportes_originales']);
            self::assertCount($episodioIndependiente ? 2 : 1, $d['reglas'][$regla]['soportes_efectivos']);
            self::assertCount(6, $d['rutas_fuente']);
            self::assertCount(2, $fuente['traza']);
        }
    }

    #[DataProvider('reglas')]
    public function test_vt12_dos_procesos_no_se_resuelven_por_prioridad_arbitraria(string $regla, array $meta): void
    {
        $entrada = $this->entrada($regla, $meta);
        $otra = array_key_first(array_filter(Catalogo::todas(), static fn ($m, $id) => $id !== $regla && $m['criterio'] === $meta['criterio'], ARRAY_FILTER_USE_BOTH));
        $segunda = $this->entrada($otra, Catalogo::obtener($otra))['evidencias'][0];
        $segunda['id'] = 'SIM-E2';
        $segunda['episodio'] = 'SIM-EPISODIO-2';
        $entrada['evidencias'][] = $segunda;
        $r = (new EvaluadorG09Experimental)->evaluar($entrada);
        self::assertSame('MULTIPLES_HALLAZGOS_PENDIENTES_DE_RESOLUCION', $r['estado_tecnico']);
        self::assertCount(2, $r['consecuencias_candidatas']);
    }

    #[DataProvider('reglas')]
    public function test_vt13_contradiccion_conserva_condiciones_cumplidas_y_suspende_consecuencias(string $regla, array $meta): void
    {
        $entrada = $this->entrada($regla, $meta);
        $entrada['contradiccion_documentada'] = true;
        $r = (new EvaluadorG09Experimental)->evaluar($entrada);
        self::assertSame('EVIDENCIA_CONTRADICTORIA', $r['estado_tecnico']);
        self::assertTrue($r['traza'][0]['cumple']);
        self::assertSame([], $r['consecuencias_candidatas']);
        $d = (new EjecutorConocimientoG09Experimental)->evaluar(PaqueteSemanticoG09Experimental::crear(), $entrada, $r);
        self::assertSame('CUMPLE', $d['reglas'][$regla]['estado']);
        self::assertSame([], $d['consecuencias']);
    }

    #[DataProvider('reglas')]
    public function test_vt05_vt14_vt15_captura_condiciones_explicacion_y_replay_son_exactos(string $regla, array $meta): void
    {
        foreach ([false, true] as $declarativa) {
            $caso = Caso::crear();
            if ($declarativa) {
                $caso['conocimientoG09'] = PaqueteSemanticoG09Experimental::crear()->declaracion();
            }
            $r = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
            self::assertSame('CUMPLE', $r['reglas_examinadas']['g09'][$regla]['estado']);
            self::assertNotEmpty($r['subcomponentes'][$meta['componente']]['resultado']);
            $informe = $r['resumen']['g09'][$meta['criterio']];
            $ruta = array_values(array_filter($informe['rutas_explicacion'], static fn ($f) => $f['regla_fuente'] === $regla))[0];
            self::assertSame('SIM-'.$meta['componente'], $ruta['registro_original']);
            self::assertSame('2026-10-16 11:00:00', $ruta['fecha_original']);
            self::assertSame('SIM-R-'.$meta['componente'], $ruta['respuestas_originales'][0]['cod_respuesta']);
            self::assertSame('FALLO_SIMULADO', $ruta['respuestas_originales'][0]['valor_texto']);
            self::assertSame(['C1' => true, 'C2' => true, 'C3' => true], $ruta['condiciones_calculadas'][0]['condiciones']);
            self::assertContains($ruta['condiciones_calculadas'][0]['evidencia'], $ruta['evidencias']);
            foreach (['instrumento', 'tarea', 'edicion', 'perfil', 'norma', 'idioma', 'poblacion', 'registro_original', 'fecha_original'] as $campo) {
                self::assertStringContainsString($ruta[$campo], $informe['como_se_llego']);
            }
            if ($declarativa) {
                foreach ($r['reglas_examinadas']['g09'][$regla]['condiciones_y_soportes'] as $condicion) {
                    foreach ($condicion['soportes'] as $id) {
                        $origen = $informe['rutas_condiciones_declarativas'][$id];
                        self::assertSame($regla, $origen['regla_fuente']);
                        self::assertContains($origen['evidencia_original'], $ruta['evidencias']);
                        self::assertTrue($ruta['condiciones_calculadas'][0]['condiciones'][$origen['condicion_fuente']]);
                    }
                }
            }
            self::assertSame($r, (new EjecutarPerfilExperimental)->ejecutar(...$r['instantanea_entrada']));
        }
    }

    private function entrada(string $regla, array $meta): array
    {
        return ['uso' => 'TEST_ONLY', 'aislado' => true, 'residente_ficticio' => 'SINTETICO-01',
            'evaluacion' => 'SIM-VT', 'version' => Catalogo::VERSION, 'criterio' => $meta['criterio'],
            'fecha_corte' => '2026-10-17', 'contexto_suficiente' => true,
            'evidencias' => [['id' => 'SIM-E1', 'fuente' => 'SIM-F1', 'episodio' => 'SIM-EPISODIO-1',
                'regla' => $regla, 'residente_ficticio' => 'SINTETICO-01', 'evaluacion' => 'SIM-VT',
                'version' => Catalogo::VERSION, 'fecha' => '2026-10-16 10:00:00',
                'procedencia_verificada' => true, 'instrumento_test_only' => true, 'tarea_atribuible' => true,
                'confusor_invalidante' => false, 'dificultad_especifica_test_only' => true,
                'atribucion_test_only' => true, 'contexto_regla_suficiente' => true]]];
    }
}
