<?php

declare(strict_types=1);

namespace Tests\Unit;

use DateTimeImmutable;
use DomainException;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\conocimiento\versiones\PaquetePetersenInstitucional as P;
use MotorExperto\dominio\instrumentos\NormalizadorHvltBolivia2015 as Hvlt;
use MotorExperto\dominio\instrumentos\RegistroMetodosInstrumentales;
use MotorExperto\dominio\objetos_valor\ContextoEjecucion;
use MotorExperto\dominio\petersen\EvaluarPetersenInstitucional as E;
use MotorExperto\dominio\petersen\FuentePetersenInstitucional as F;
use MotorExperto\dominio\petersen\ValoracionOriginalPetersen as V;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Casos sintéticos independientes de la BDD: entrada = juicios y brutos, nunca el patrón esperado. */
final class PetersenInstitucionalTest extends TestCase
{
    private function contexto(): ContextoEjecucion
    {
        return new ContextoEjecucion('EVAL-TEST', 'RES-TEST', 'VERSION-TEST', new DateTimeImmutable('2026-10-19T12:00:00.123456-04:00'));
    }

    private function fuente(string $id, string $tipo, array $datos = [], ?string $episodio = null,
        string $fecha = '2026-10-15T09:00:00.000000-04:00', bool $vigente = true, string $residente = 'RES-TEST'): F
    {
        $tabla = match ($tipo) {
            'MEDICION', 'VALORACION_ORIGINAL' => 'aplicaciones_instrumento', 'IADL' => 'controles_funcionales_iadl',
            'HISTORIA_PREVIA', 'CORROBORACION' => 'controles_cognitivos', default => 'valoraciones_psicologicas',
        };

        return new F($tabla, $id, $residente, 'AUTOR-TEST', new DateTimeImmutable($fecha), $episodio ?? 'EP-'.$id, $tipo, $vigente, $datos);
    }

    private function medicion(string $dominio): F
    {
        $metodo = match ($dominio) {
            'COG-MEM' => Hvlt::METODO, 'COG-ATE' => 'BTA-BO-2015-NL', 'COG-EJE' => 'TMT-B-BO-2015',
            'COG-LEN' => 'BNT-BO-2015-60', 'COG-VIS' => 'ROCF-BO-2015-COPIA-A',
        };
        $meta = (new RegistroMetodosInstrumentales)->metodos()[$metodo];
        $a = ['cod_aplicacion' => 'MED-'.$dominio, 'cod_residente' => 'RES-TEST', 'cod_personal' => 'AUTOR-TEST',
            'cod_instrumento' => 'INST-'.$dominio, 'codigo_instrumento' => 'TEST-'.$dominio, 'version_instrumento' => 'TEST-1',
            'fecha_hora' => '2026-10-15T09:00:00.000000-04:00', 'edad_en_aplicacion' => 70, 'estado_original' => 'COMPLETA',
            'instrumento_actual' => ['tipo' => 'COGNITIVO'], 'motivos' => [], 'respuestas_originales' => []];
        $valores = ['ensayo_1' => 6, 'ensayo_2' => 7, 'ensayo_3' => 8, 'recuerdo_diferido' => 4,
            'demora_minutos' => 22, 'escolaridad_anios' => 4, 'forma_n_aciertos' => 6, 'forma_l_aciertos' => 7,
            'segundos_parte_b' => 180.5, 'errores_observados' => 0, 'aciertos_espontaneos' => 40,
            'aciertos_clave_semantica' => 2, 'puntaje_copia' => 22.5, 'elementos_revisados' => 18];
        $selectores = [];
        foreach ($meta['entradas'] as $entrada => $tipo) {
            $campo = $tipo === 'LOGICO' ? 'valor_logico' : 'valor_numero';
            $id = 'P-'.count($selectores);
            $selectores[$entrada] = ['cod_pregunta' => $id, 'campo' => $campo];
            $a['respuestas_originales'][] = ['cod_respuesta' => 'R-'.$id, 'cod_aplicacion' => $a['cod_aplicacion'], 'cod_pregunta' => $id,
                'tipo_respuesta' => $tipo === 'LOGICO' ? 'LOGICO' : 'NUMERICO', 'coherente' => true,
                $campo => $tipo === 'LOGICO' ? true : ($valores[$entrada] ?? throw new \LogicException('Falta bruto del caso sintético.'))];
        }
        $a['perfil_captura_original'] = ['id' => 'PERFIL-'.$dominio, 'cod_instrumento' => $a['cod_instrumento'],
            'codigo_instrumento' => $a['codigo_instrumento'], 'version_instrumento' => 'TEST-1', 'metodo' => $metodo,
            'identidad_instrumento' => $meta['identidad'], 'selectores' => $selectores];

        $atencion = 'AT-'.$dominio;
        $a['aplicacion_actual'] = ['cod_atencion' => $atencion];
        $a['atencion_original'] = ['cod_atencion' => $atencion, 'cod_residente' => 'RES-TEST'];

        return $this->fuente($a['cod_aplicacion'], 'MEDICION', ['dominio' => $dominio, 'captura_original' => $a], 'ATENCION:'.$atencion);
    }

    private function caso(array $alterados = ['COG-MEM']): array
    {
        $v = [];
        foreach (E::DOMINIOS as $dominio) {
            $v[] = new V($dominio, in_array($dominio, $alterados, true) ? 'A' : 'P', 'Interpretación original del dominio, caso sintético.',
                $this->fuente('REV-'.$dominio, 'VALORACION_ORIGINAL'), [$this->medicion($dominio)],
                ['alcance_dominio' => true, 'contexto_interpretado' => true]);
        }
        $v[] = new V('C01', 'S', 'Cambio previo y corroboración de episodios originales independientes.', $this->fuente('REV-C01', 'VALORACION_ORIGINAL'),
            [$this->fuente('PREVIO', 'HISTORIA_PREVIA', ['descripcion' => 'Funcionamiento previo documentado.'], fecha: '2026-10-10T09:00:00-04:00'),
                $this->fuente('CORROB', 'CORROBORACION', ['descripcion' => 'Cambio corroborado en otro episodio.'])],
            ['cambio_respecto_previo' => true, 'corroboracion_independiente' => true]);
        $v[] = new V('C03', 'S', 'Valoración original integrada de independencia relativa.', $this->fuente('REV-C03', 'VALORACION_ORIGINAL'),
            [$this->fuente('IADL-1', 'IADL', ['actividad' => 'MEDICACION', 'oportunidad' => 'OBSERVADA', 'desempeno' => 'INDEPENDIENTE',
                'tipo_ayuda' => 'NINGUNA', 'causa_ayuda' => 'NINGUNA', 'nivel_previo' => 'INDEPENDIENTE', 'fuente' => 'OBSERVACION_DIRECTA'])],
            ['revision_integrada' => true, 'oportunidad_adecuada' => true, 'atribucion_revisada' => true]);
        $v[] = new V('C04', 'S', 'Revisión diferencial original sin impedimento actual.', $this->fuente('REV-C04', 'VALORACION_ORIGINAL'),
            [$this->fuente('DIF-1', 'DIFERENCIAL', ['descripcion' => 'Factores pertinentes considerados explícitamente.'])],
            ['revision_explicita' => true, 'factores_pertinentes_revisados' => true, 'impedimento_no_resuelto' => false]);
        $v[] = new V('C05', 'S', 'Condiciones y exclusiones revisadas; perfil suficiente.', $this->fuente('REV-C05', 'VALORACION_ORIGINAL'),
            [$this->fuente('CAL-1', 'CALIDAD', ['descripcion' => 'Suficiencia y condiciones del conjunto revisadas.'])],
            ['suficiencia_conjunto' => true, 'condiciones_revisadas' => true, 'exclusiones_revisadas' => true],
            array_combine(E::DOMINIOS, array_map(fn ($d) => $this->fuente('REV-'.$d, 'VALORACION_ORIGINAL')->identidad(), E::DOMINIOS)));

        return $v;
    }

    public static function patrones(): array
    {
        return ['amnesico_unico' => [['COG-MEM'], 'PET-01'], 'amnesico_multiple' => [['COG-MEM', 'COG-LEN'], 'PET-02'],
            'no_amnesico_unico' => [['COG-ATE'], 'PET-03'], 'no_amnesico_multiple' => [['COG-EJE', 'COG-VIS'], 'PET-04']];
    }

    #[DataProvider('fechasJuicioNoRevisado')]
    public function test_c05_no_acredita_un_conjunto_ampliado_de_juicios_concordantes(string $fecha): void
    {
        $v = $this->caso();
        $memoria = $v[0];
        $v[] = new V('COG-MEM', 'A', 'Otro juicio original concordante que no pertenece al conjunto revisado.',
            $this->fuente('REV-MEM-ADICIONAL', 'VALORACION_ORIGINAL', fecha: $fecha),
            $memoria->soportes, $memoria->comprobaciones);

        $r = (new E)->evaluar($this->contexto(), $v);

        self::assertNull($r['patron']);
        self::assertSame('A', $r['dominios']['COG-MEM']['estado']);
        self::assertCount(2, $r['dominios']['COG-MEM']['valoraciones']);
        self::assertSame('U', $r['requisitos']['C05']['estado']);
        self::assertContains('C05_NO_IDENTIFICA_VALORACION_REVISADA:COG-MEM', $r['requisitos']['C05']['motivos']);
    }

    public static function fechasJuicioNoRevisado(): array
    {
        return [
            'simultaneo' => ['2026-10-15T09:00:00.000000-04:00'],
            'posterior_a_calidad' => ['2026-10-16T09:00:00.000000-04:00'],
        ];
    }

    #[DataProvider('patrones')]
    public function test_obtiene_cuatro_patrones_desde_brutos_e_interpretaciones_originales(array $alterados, string $esperado): void
    {
        $r = (new E)->evaluar($this->contexto(), $this->caso($alterados));
        self::assertSame($esperado, $r['patron']);
        self::assertSame([], $r['impedimentos']);
        self::assertSame('S', $r['requisitos']['C02']['estado']);
        self::assertCount(5, $r['dominios']);
        self::assertSame('PENDIENTE', $r['revision_profesional']);
        self::assertFalse($r['persistido']);
    }

    public function test_todos_esperados_no_equivalen_a_dcl(): void
    {
        $r = (new E)->evaluar($this->contexto(), $this->caso([]));
        self::assertNull($r['patron']);
        self::assertSame('N', $r['requisitos']['C02']['estado']);
        self::assertSame('SIN_PATRON_CLASIFICABLE_POR_REQUISITO', $r['estado']);
    }

    public function test_ausencia_conserva_los_diez_elementos_desconocidos(): void
    {
        $r = (new E)->evaluar($this->contexto(), []);
        self::assertNull($r['patron']);
        self::assertSame(['U'], array_values(array_unique(array_column($r['requisitos'], 'estado'))));
        self::assertSame(['U'], array_values(array_unique(array_column($r['dominios'], 'estado'))));
    }

    public static function faltantes(): array
    {
        return array_map(static fn ($i) => [$i], range(0, 8));
    }

    #[DataProvider('faltantes')]
    public function test_cada_original_ausente_impide_clasificar(int $indice): void
    {
        $v = $this->caso();
        unset($v[$indice]);
        $r = (new E)->evaluar($this->contexto(), array_values($v));
        self::assertNull($r['patron']);
        self::assertContains('ABSTENCION_INFORMACION_INSUFICIENTE', $r['impedimentos']);
    }

    public function test_contradiccion_y_diferencial_no_desaparecen_por_dependencia_de_soportes(): void
    {
        $v = $this->caso();
        $original = $v[0];
        $v[] = new V('COG-MEM', 'P', 'Interpretación competidora original.', $this->fuente('OTRA-REV', 'VALORACION_ORIGINAL'),
            $original->soportes, $original->comprobaciones);
        $v[7] = new V('C04', 'N', 'Impedimento diferencial explícito.', $v[7]->origen, $original->soportes, []);
        $r = (new E)->evaluar($this->contexto(), $v);
        self::assertNull($r['patron']);
        self::assertSame('X', $r['dominios']['COG-MEM']['estado']);
        self::assertSame('N', $r['requisitos']['C04']['estado']);
        self::assertSame('ABSTENCION_CONTRADICCION', $r['estado']);
        self::assertContains('ABSTENCION_EVALUACION_DIFERENCIAL', $r['impedimentos']);
    }

    public function test_iadl_sin_oportunidad_ni_ayuda_documentada_no_acredita_c03(): void
    {
        $v = $this->caso();
        $v[6] = new V('C03', 'S', 'Caso sintético incompleto.', $v[6]->origen,
            [$this->fuente('IADL-INCOMP', 'IADL', ['oportunidad' => 'SIN_OPORTUNIDAD', 'nivel_previo' => 'INDEPENDIENTE', 'desempeno' => 'CON_APOYO'])], $v[6]->comprobaciones);
        $r = (new E)->evaluar($this->contexto(), $v);
        self::assertNull($r['patron']);
        self::assertSame('U', $r['requisitos']['C03']['estado']);
        self::assertContains('IADL_SIN_CONTEXTO_INTERPRETABLE', $r['valoraciones_excluidas'][0]['motivos']);
        self::assertCount(1, $r['valoraciones_excluidas'][0]['soportes']);
        self::assertSame('S', $r['valoraciones_excluidas'][0]['estado_original']);
    }

    public function test_no_acepta_c02_o_patron_esperado_como_entrada(): void
    {
        $this->expectException(DomainException::class);
        new V('C02', 'S', 'No debe declararse.', $this->fuente('REV-C02', 'VALORACION_ORIGINAL'), [], []);
    }

    public function test_no_acepta_otro_residente_aunque_la_valoracion_parezca_completa(): void
    {
        $v = $this->caso();
        $v[7] = new V('C04', 'S', $v[7]->justificacion, $this->fuente('AJENA', 'VALORACION_ORIGINAL', residente: 'RES-AJENO'), $v[7]->soportes, $v[7]->comprobaciones);
        $this->expectException(DomainException::class);
        (new E)->evaluar($this->contexto(), $v);
    }

    public function test_original_futuro_se_conserva_excluido_y_no_acredita(): void
    {
        $v = $this->caso();
        $v[7] = new V('C04', 'S', $v[7]->justificacion, $this->fuente('FUTURA', 'VALORACION_ORIGINAL', fecha: '2026-10-20T09:00:00-04:00'), $v[7]->soportes, $v[7]->comprobaciones);
        $r = (new E)->evaluar($this->contexto(), $v);
        self::assertNull($r['patron']);
        self::assertSame('U', $r['requisitos']['C04']['estado']);
        self::assertCount(1, $r['valoraciones_excluidas']);
    }

    public function test_un_booleano_de_interpretabilidad_no_sustituye_los_brutos_del_metodo(): void
    {
        $v = $this->caso();
        $v[0] = new V('COG-MEM', 'A', $v[0]->justificacion, $v[0]->origen,
            [$this->fuente('SIN-CAPTURA', 'MEDICION', ['dominio' => 'COG-MEM'])], $v[0]->comprobaciones);
        $r = (new E)->evaluar($this->contexto(), $v);
        self::assertSame('U', $r['dominios']['COG-MEM']['estado']);
        self::assertSame('U', $r['requisitos']['C05']['estado']);
        self::assertNull($r['patron']);
    }

    public static function capturasIncompatibles(): array
    {
        return ['menor_60' => ['edad_en_aplicacion', 50], 'episodio_ajeno' => ['atencion_original', ['cod_atencion' => 'AT-OTRA', 'cod_residente' => 'RES-TEST']],
            'observacion_nominal' => ['instrumento_actual', ['tipo' => 'OBSERVACIONAL']], 'sin_protocolo' => ['perfil_captura_original', null]];
    }

    #[DataProvider('capturasIncompatibles')]
    public function test_una_captura_incompatible_no_acredita_el_dominio(string $campo, mixed $valor): void
    {
        $v = $this->caso();
        $s = $v[0]->soportes[0];
        $datos = $s->datos;
        $datos['captura_original'][$campo] = $valor;
        $v[0] = new V('COG-MEM', 'A', $v[0]->justificacion, $v[0]->origen,
            [$this->fuente($s->registro, 'MEDICION', $datos, $s->episodio)], $v[0]->comprobaciones);
        $r = (new E)->evaluar($this->contexto(), $v);
        self::assertNull($r['patron']);
        self::assertSame('U', $r['dominios']['COG-MEM']['estado']);
    }

    public function test_con_apoyo_y_ninguna_ayuda_no_se_resuelve_eligiendo_un_campo(): void
    {
        $v = $this->caso();
        $s = $v[6]->soportes[0];
        $v[6] = new V('C03', 'S', $v[6]->justificacion, $v[6]->origen,
            [$this->fuente($s->registro, 'IADL', array_replace($s->datos, ['desempeno' => 'CON_APOYO']))], $v[6]->comprobaciones);
        $r = (new E)->evaluar($this->contexto(), $v);
        self::assertNull($r['patron']);
        self::assertContains('IADL_CON_DATOS_DOCUMENTALES_INCOMPATIBLES', $r['valoraciones_excluidas'][0]['motivos']);
    }

    public function test_dos_aplicaciones_de_un_mismo_episodio_no_se_cuentan_como_soportes_independientes(): void
    {
        $v = $this->caso();
        foreach ([0, 1] as $i) {
            $s = $v[$i]->soportes[0];
            $datos = $s->datos;
            $datos['captura_original']['aplicacion_actual']['cod_atencion'] = 'AT-COMPARTIDA';
            $datos['captura_original']['atencion_original']['cod_atencion'] = 'AT-COMPARTIDA';
            $v[$i] = new V($v[$i]->elemento, $v[$i]->estado, $v[$i]->justificacion, $v[$i]->origen,
                [$this->fuente($s->registro, 'MEDICION', $datos, 'ATENCION:AT-COMPARTIDA')], $v[$i]->comprobaciones);
        }
        $r = (new E)->evaluar($this->contexto(), $v);
        self::assertNull($r['patron']);
        self::assertSame('U', $r['dominios']['COG-MEM']['estado']);
        self::assertSame('U', $r['dominios']['COG-ATE']['estado']);
        self::assertNotEmpty($r['dominios']['COG-MEM']['motivos']);
    }

    public function test_c05_no_se_aplica_a_otro_conjunto_solo_porque_los_estados_coincidan(): void
    {
        $v = $this->caso();
        $revisados = $v[8]->dominiosRevisados;
        $revisados['COG-MEM'] = 'aplicaciones_instrumento:OTRA-REVISION';
        $v[8] = new V('C05', 'S', $v[8]->justificacion, $v[8]->origen, $v[8]->soportes, $v[8]->comprobaciones, $revisados);
        $r = (new E)->evaluar($this->contexto(), $v);
        self::assertNull($r['patron']);
        self::assertSame('U', $r['requisitos']['C05']['estado']);
        self::assertContains('C05_NO_IDENTIFICA_VALORACION_REVISADA:COG-MEM', $r['requisitos']['C05']['motivos']);
    }

    #[DataProvider('patrones')]
    public function test_gobierno_ejecuta_las_cuatro_conjunciones_sin_precargar_el_patron(array $alterados, string $esperado): void
    {
        $p = (new P)->construir('GOBIERNO-TEST', 'AUTOR-TEST', '2026-10-19 10:00:00');
        $r = (new E)->evaluar($this->contexto(), $this->caso($alterados), $p, exigirGobierno: true);
        self::assertSame($esperado, $r['patron']);
        self::assertSame('GOBIERNO-TEST', $r['gobierno']['version']);
        self::assertCount(4, $r['gobierno']['reglas']);
        self::assertCount(1, array_filter($r['gobierno']['reglas'], fn ($r) => $r['cumple']));
        self::assertCount(8, $r['gobierno']['reglas'][0]['condiciones']);
        $sinOriginal = $this->caso($alterados);
        unset($sinOriginal[6]); // C03 no se sustituye con el patrón ni con los otros antecedentes.
        self::assertNull((new E)->evaluar($this->contexto(), array_values($sinOriginal), $p, exigirGobierno: true)['patron']);
    }

    public function test_consulta_que_exige_gobierno_no_cae_al_clasificador_fijo(): void
    {
        $r = (new E)->evaluar($this->contexto(), $this->caso(), exigirGobierno: true);
        self::assertNull($r['patron']);
        self::assertContains('ABSTENCION_SIN_GOBIERNO_PETERSEN_VIGENTE', $r['impedimentos']);
    }

    public static function dependenciasGobiernoAlteradas(): array
    {
        return [['nodo'], ['criterio'], ['dominio'], ['valor'], ['consecuencia'], ['condicion'], ['regla_duplicada']];
    }

    #[DataProvider('dependenciasGobiernoAlteradas')]
    public function test_rechaza_gobierno_con_dependencia_inactiva_o_semantica_alterada(string $cambio): void
    {
        $p = (new P)->construir('GOBIERNO-TEST', 'AUTOR-TEST', '2026-10-19 10:00:00');
        $t = $p->declaracion()['tablas'];
        match ($cambio) {
            'nodo' => $t['nodos_semanticos'][0]['estado'] = 'INACTIVO',
            'criterio' => $t['criterios_dominios_resultado'][0]['estado'] = 'INACTIVO',
            'dominio' => $t['dominios_valores_expertos'][0]['estado'] = 'INACTIVO',
            'valor' => $t['valores_semanticos'][0]['estado_aprobacion'] = 'PENDIENTE',
            'consecuencia' => $t['consecuencias_regla_experta'][0]['cod_valor_semantico'] = $t['consecuencias_regla_experta'][1]['cod_valor_semantico'],
            'condicion' => array_pop($t['condiciones_regla_experta']),
            'regla_duplicada' => $t['reglas_expertas'][0]['codigo_regla'] = $t['reglas_expertas'][1]['codigo_regla'],
        };
        $this->expectException(DomainException::class);
        $alterado = new PaqueteConocimiento($p->version, $t, $p->contratosRelaciones, false, $p->tipoReglaResultado, []);
        (new E)->evaluar($this->contexto(), $this->caso(), $alterado, exigirGobierno: true);
    }
}
