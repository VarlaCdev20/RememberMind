<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AplicacionInstrumento;
use App\Models\DocumentoClinico;
use App\Models\Instrumento;
use App\Models\OpcionPregunta;
use App\Models\Personal;
use App\Models\PreguntaInstrumento;
use App\Models\Residente;
use App\Models\RespuestaInstrumento;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\GestionarReglasExpertas;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarConocimientoCognitivo;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarPerfilInstrumental;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\ConocimientoVersionado;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\IdentidadesConocimiento;
use App\Modulos\ApoyoDecision\Aplicacion\Servicios\ContratosConocimiento;
use App\Modulos\ApoyoDecision\Dominio\Puertos\FuenteConocimientoOrion;
use Carbon\CarbonImmutable;
use Database\Seeders\RolesAndPermissionsSeeder;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use MotorExperto\adaptadores\entrada\ConstructorMemoriaInstrumental;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\objetos_valor\ContextoEjecucion;
use MotorExperto\investigacion\dcl\PaqueteCincoDominiosExperimental;
use MotorExperto\investigacion\g09\PaqueteSemanticoG09Experimental;
use MotorExperto\soporte\conservacion\InstantaneaInstrumental;
use Tests\Support\InformeCognitivoFixture;
use Tests\TestCase;

/** Solo datos sintéticos en la BDD desechable; no identifica instrumentos de residentes por similitud. */
final class ConocimientoCognitivoInstitucionalTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    private Residente $residente;

    private string $version = 'COG-FIXTURE';

    private string $aplicacion;

    private string $primeraPregunta;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-10-19 12:00:00'));
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $this->usuario->assignRole('PSICOLOGO/A');
        $personal = Personal::create(['cod_usuario' => $this->usuario->getKey(), 'nombres' => 'Profesional',
            'apellido_paterno' => 'Sintético', 'numero_documento' => 'COG-TEST-1', 'profesion' => 'PSICOLOGIA', 'estado' => 'ACTIVO']);
        $this->actingAs($this->usuario);
        $this->residente = Residente::factory()->create(['estado' => 'ADMITIDO', 'fecha_nacimiento' => '1950-10-18']);
        $instrumento = Instrumento::create(['codigo' => 'COG-CAPTURA-TEST', 'nombre' => 'Protocolo de ingeniería',
            'tipo' => 'COGNITIVO', 'version' => 'FIXTURE-1', 'estado' => 'ACTIVO']);
        $a = AplicacionInstrumento::create(['cod_instrumento' => $instrumento->getKey(), 'cod_residente' => $this->residente->getKey(),
            'cod_personal' => $personal->getKey(), 'fecha_hora' => '2026-10-18 09:00:00', 'estado' => 'COMPLETA']);
        $this->aplicacion = $a->getKey();
        $p = (new PaqueteCincoDominiosExperimental)->integrar(PaqueteSemanticoG09Experimental::paraConservacion())['paquete'];
        $t = $p->declaracion()['tablas'];
        foreach ($t['reglas_expertas'] as &$regla) {
            $regla['codigo_regla'] ??= $regla['cod_regla_experta'];
        }
        unset($regla);
        $t['fuentes_datos_expertas'][] = ['cod_fuente_dato_experta' => 'FU-ORIGINAL', 'cod_version_modelo' => $p->version,
            'tabla_raiz' => 'aplicaciones_instrumento', 'estado' => 'ACTIVO', 'campo_pk_raiz' => 'cod_aplicacion',
            'campo_residente' => 'cod_residente', 'campo_temporal' => 'fecha_hora', 'campo_personal' => 'cod_personal'];
        $vinculos = [];
        foreach (range(1, 3) as $i) {
            $q = PreguntaInstrumento::create(['cod_instrumento' => $instrumento->getKey(), 'codigo' => 'C'.$i,
                'enunciado' => 'Literal sintético '.$i, 'tipo_respuesta' => 'LOGICO', 'orden' => $i, 'estado' => 'ACTIVA']);
            if ($i === 1) {
                $this->primeraPregunta = $q->getKey();
            }
            RespuestaInstrumento::create(['cod_aplicacion' => $a->getKey(), 'cod_pregunta' => $q->getKey(), 'valor_logico' => true]);
            $mapa = 'MAP-ORIG-'.$i;
            $t['mapeos_variables_fuente'][] = ['cod_mapeo_variable_fuente' => $mapa, 'cod_version_modelo' => $p->version,
                'cod_variable_experta' => 'VAR-R-ATE-S01-P01-C'.$i, 'cod_fuente_dato_experta' => 'FU-ORIGINAL',
                'tipo_extraccion' => 'RESPUESTA_ORIGINAL', 'campo_valor' => null, 'clave_selector' => $q->getKey(), 'estado' => 'ACTIVO'];
            $t['mapeos_valores_fuente'][] = ['cod_mapeo_valor_fuente' => 'MV-ORIG-'.$i, 'cod_mapeo_variable_fuente' => $mapa,
                'cod_valor_semantico' => 'VAL-SIM-TRUE', 'tipo_valor_fuente' => 'boolean', 'valor_fuente_exacto' => 'true',
                'estado' => 'ACTIVO', 'estado_aprobacion' => 'APROBADO', 'fecha_hora_vigencia_desde' => null, 'fecha_hora_vigencia_hasta' => null];
            $vinculos[$mapa] = ['cod_instrumento' => $instrumento->getKey(), 'codigo_instrumento' => $instrumento->codigo,
                'version_instrumento' => $instrumento->version, 'cod_pregunta' => $q->getKey(), 'campo' => 'valor_logico'];
        }
        $header = ['cod_version_modelo' => $p->version, 'codigo_version' => 'ORION-COG-ORIGINALES', 'estado' => 'ACTIVO',
            'fecha_hora_creacion' => '2026-10-17 08:00:00', 'fecha_hora_vigencia' => '2026-10-17 08:00:00',
            'cod_usuario_creacion' => $this->usuario->getKey()];
        $traducidas = app(IdentidadesConocimiento::class)->traducir(['versiones_modelo_experto' => [$header]] + $t, $p->version, $this->version, false);
        foreach ($traducidas as $tabla => $filas) {
            foreach ($filas as $fila) {
                if (array_key_exists('cod_usuario_creacion', $fila)) {
                    $fila['cod_usuario_creacion'] = $this->usuario->getKey();
                }
                InformeCognitivoFixture::insertar($tabla, $fila);
            }
        }
        $mapeos = array_column($traducidas['mapeos_variables_fuente'], 'cod_mapeo_variable_fuente', 'clave_selector');
        $vinculosReales = [];
        foreach ($vinculos as $vinculo) {
            $vinculosReales[$mapeos[$vinculo['cod_pregunta']]] = $vinculo;
        }
        config(['sistema_experto.version_cognitiva' => $this->version,
            'sistema_experto.contratos_conocimiento.'.$this->version => ['relaciones' => $p->contratosRelaciones,
                'tipo_regla_resultado' => 'RESULTADO', 'extracciones' => $p->contratosExtraccion + ['RESPUESTA_ORIGINAL' => 'SELECTOR_COMPONENTE'],
                'vinculos_instrumentales' => $vinculosReales]]);
    }

    private function consultar(): array
    {
        return app(ConsultarConocimientoCognitivo::class)->consultar($this->usuario, $this->residente, CarbonImmutable::now());
    }

    public function test_recorrido_sql_fuente_original_regla_conclusion_y_perfil_sin_guardado_implicitamente(): void
    {
        $r = $this->consultar();
        self::assertTrue($r['disponible']);
        self::assertCount(5, $r['resultado']['perfil']);
        $conclusiones = $r['resultado']['perfil']['COG-ATE']['conclusiones_candidatas'];
        self::assertCount(1, $conclusiones);
        self::assertCount(1, array_values($conclusiones)[0]['soportes_efectivos']);
        $rutas = $r['resultado']['perfil']['COG-ATE']['traza']['rutas'];
        self::assertCount(3, $rutas);
        self::assertSame($this->aplicacion, $rutas[0]['registro']);
        self::assertSame($this->usuario->personal->getKey(), $rutas[0]['personal']);
        self::assertSame(0, DB::table('evaluaciones_expertas')->count());
        $perfil = app(ConsultarPerfilInstrumental::class)->consultar($this->usuario, $this->residente, CarbonImmutable::now());
        self::assertTrue($perfil['inferencia_declarativa']['disponible']);
        self::assertSame(1, $perfil['metricas_60_o_mas']['aplicaciones_60_o_mas']);
    }

    public function test_null_no_activa_regla_y_no_se_traduce_en_false(): void
    {
        DB::table('respuestas_instrumento')->where('cod_aplicacion', $this->aplicacion)->where('cod_pregunta', $this->primeraPregunta)->update(['valor_logico' => null]);
        $r = $this->consultar()['resultado']['perfil']['COG-ATE'];
        self::assertSame([], $r['conclusiones_candidatas']);
        self::assertCount(1, $r['traza']['sin_emision']);
    }

    public function test_observaciones_nominales_recorran_fuente_regla_componente_dominio_y_traza_sin_normalizar(): void
    {
        $this->prepararObservacionesNominales();
        $r = $this->consultar();
        $dominio = $r['resultado']['perfil']['COG-ATE'];
        self::assertCount(1, $dominio['conclusiones_candidatas']);
        $conclusion = array_values($dominio['conclusiones_candidatas'])[0];
        self::assertCount(1, $conclusion['reglas']);
        self::assertCount(3, $conclusion['evidencias']);
        self::assertCount(1, $conclusion['soportes_efectivos']);
        self::assertSame('COG-ATE', $r['resultado']['red']['componentes']['ATE-S01']['dominio']);
        self::assertCount(3, $r['resultado']['red']['rutas_reglas'][$conclusion['reglas'][0]]);
        foreach ($dominio['traza']['rutas'] as $ruta) {
            $original = $ruta['procedencia_instrumental'];
            self::assertSame('OBSERVACION_CLINICA', $original['naturaleza_fuente']);
            self::assertSame('valor_texto', $original['campo_original']);
            self::assertSame('OBSERVACIONAL', $original['instrumento']['tipo']);
            self::assertSame($original['cod_pregunta'], $original['opcion_original']['cod_pregunta']);
            self::assertSame($ruta['valor_original'], $original['opcion_original']['valor']);
            self::assertSame($this->aplicacion, $original['cod_aplicacion']);
            self::assertSame($this->usuario->personal->getKey(), $original['cod_personal_original']);
        }
        self::assertNull($r['capturas']['capturas'][0]['extraccion']);
        self::assertNull($r['resultado']['clasificacion_dcl']);
        self::assertFalse($r['persistido']);
        self::assertDatabaseCount('evaluaciones_expertas', 0);
    }

    public function test_observacion_nominal_sin_texto_no_reconstruye_literal_desde_opcion(): void
    {
        $this->prepararObservacionesNominales();
        DB::table('respuestas_instrumento')->where('cod_aplicacion', $this->aplicacion)
            ->where('cod_pregunta', $this->primeraPregunta)->update(['valor_texto' => null]);
        $r = $this->consultar()['resultado']['perfil']['COG-ATE'];
        self::assertSame([], $r['conclusiones_candidatas']);
        $e = array_values(array_filter($r['traza']['evidencias'], fn ($e) => $e['procedencia_instrumental']['cod_pregunta'] === $this->primeraPregunta))[0];
        self::assertSame('NO_DISPONIBLE', $e['representacion']);
        self::assertNull($e['valor_original']);
        self::assertNotNull($e['procedencia_instrumental']['opcion_original']);
    }

    public function test_ausencia_de_respuesta_nominal_conserva_el_formato_historico_de_traza(): void
    {
        $instrumento = DB::table('aplicaciones_instrumento')->where('cod_aplicacion', $this->aplicacion)->value('cod_instrumento');
        DB::table('instrumentos')->where('cod_instrumento', $instrumento)->update(['tipo' => 'OBSERVACIONAL']);
        DB::table('preguntas_instrumento')->where('cod_pregunta', $this->primeraPregunta)->update(['tipo_respuesta' => 'OPCION']);
        DB::table('respuestas_instrumento')->where('cod_aplicacion', $this->aplicacion)->where('cod_pregunta', $this->primeraPregunta)->delete();
        $mapa = DB::table('mapeos_variables_fuente')->where('cod_version_modelo', $this->version)->where('clave_selector', $this->primeraPregunta)
            ->value('cod_mapeo_variable_fuente');
        config(['sistema_experto.contratos_conocimiento.'.$this->version.'.vinculos_instrumentales.'.$mapa.'.campo' => 'valor_texto']);
        $r = $this->consultar()['resultado']['perfil']['COG-ATE'];
        self::assertSame([], $r['conclusiones_candidatas']);
        $e = array_values(array_filter($r['traza']['evidencias'], fn ($e) => $e['procedencia_instrumental']['cod_pregunta'] === $this->primeraPregunta))[0];
        self::assertSame('NO_DISPONIBLE', $e['representacion']);
        self::assertArrayNotHasKey('naturaleza_fuente', $e['procedencia_instrumental']);
        self::assertArrayNotHasKey('opcion_original', $e['procedencia_instrumental']);
        self::assertNull($e['procedencia_instrumental']['cod_respuesta']);
    }

    public function test_observacion_nominal_desconocida_conserva_literal_sin_inferir_por_etiqueta_o_puntaje(): void
    {
        $this->prepararObservacionesNominales();
        $opcion = DB::table('respuestas_instrumento')->where('cod_aplicacion', $this->aplicacion)
            ->where('cod_pregunta', $this->primeraPregunta)->value('cod_opcion');
        DB::table('opciones_pregunta')->where('cod_opcion', $opcion)->update(['valor' => 'otro_literal', 'puntaje' => 0]);
        DB::table('respuestas_instrumento')->where('cod_aplicacion', $this->aplicacion)
            ->where('cod_pregunta', $this->primeraPregunta)->update(['valor_texto' => 'otro_literal']);
        $r = $this->consultar()['resultado']['perfil']['COG-ATE'];
        self::assertSame([], $r['conclusiones_candidatas']);
        $e = array_values(array_filter($r['traza']['evidencias'], fn ($e) => $e['procedencia_instrumental']['cod_pregunta'] === $this->primeraPregunta))[0];
        self::assertSame('VALOR_NO_RECONOCIDO', $e['representacion']);
        self::assertSame('otro_literal', $e['valor_original']);
    }

    public function test_opcion_y_texto_discordantes_no_se_eligen_arbitrariamente(): void
    {
        $this->prepararObservacionesNominales();
        DB::table('respuestas_instrumento')->where('cod_aplicacion', $this->aplicacion)
            ->where('cod_pregunta', $this->primeraPregunta)->update(['valor_texto' => ' Literal sintético 1 ']);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('La opción original y el literal observacional son incompatibles.');
        $this->consultar();
    }

    public function test_pregunta_opcion_no_observacional_no_adopta_el_contrato_nominal(): void
    {
        $this->prepararObservacionesNominales();
        $instrumento = DB::table('aplicaciones_instrumento')->where('cod_aplicacion', $this->aplicacion)->value('cod_instrumento');
        DB::table('instrumentos')->where('cod_instrumento', $instrumento)->update(['tipo' => 'COGNITIVO']);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Respuesta incompatible');
        $this->consultar();
    }

    public function test_observacion_nominal_con_numero_simultaneo_se_rechaza(): void
    {
        $this->prepararObservacionesNominales();
        DB::table('respuestas_instrumento')->where('cod_aplicacion', $this->aplicacion)
            ->where('cod_pregunta', $this->primeraPregunta)->update(['valor_numero' => 0]);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Respuesta incompatible');
        $this->consultar();
    }

    public function test_observacion_nominal_sin_opcion_no_infiere_por_texto(): void
    {
        $this->prepararObservacionesNominales();
        DB::table('respuestas_instrumento')->where('cod_aplicacion', $this->aplicacion)
            ->where('cod_pregunta', $this->primeraPregunta)->update(['cod_opcion' => null]);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('La opción original y el literal observacional son incompatibles.');
        $this->consultar();
    }

    public function test_opcion_cruzada_excluye_fuente_y_no_genera_resultado(): void
    {
        $this->prepararObservacionesNominales();
        $opcionAjena = DB::table('respuestas_instrumento')->where('cod_aplicacion', $this->aplicacion)
            ->where('cod_pregunta', '!=', $this->primeraPregunta)->value('cod_opcion');
        DB::table('respuestas_instrumento')->where('cod_aplicacion', $this->aplicacion)
            ->where('cod_pregunta', $this->primeraPregunta)->update(['cod_opcion' => $opcionAjena]);
        $r = $this->consultar();
        self::assertSame([], $r['resultado']['perfil']['COG-ATE']['conclusiones_candidatas']);
        self::assertContains('RESPUESTA_CON_REFERENCIAS_INCOMPATIBLES', $r['fuentes_excluidas'][0]['motivos']);
        self::assertDatabaseCount('evaluaciones_expertas', 0);
    }

    public function test_motor_verifica_las_pk_nominales_aunque_el_adaptador_marque_coherente(): void
    {
        $this->prepararObservacionesNominales();
        $r = $this->consultar();
        $contrato = config('sistema_experto.contratos_conocimiento.'.$this->version);
        $tablas = app(ConocimientoVersionado::class)->instantanea($this->version);
        $p = new PaqueteConocimiento($this->version, $tablas, $contrato['relaciones'], false, $contrato['tipo_regla_resultado'], $contrato['extracciones']);
        $c = new ContextoEjecucion($r['resultado']['evaluacion'], $this->residente->getKey(), $this->version, CarbonImmutable::now()->toDateTimeImmutable());
        foreach ([['opcion_actual', 'cod_opcion'], ['opcion_actual', 'cod_pregunta'], ['pregunta_actual', 'cod_pregunta']] as [$fila, $campo]) {
            $capturas = $r['capturas'];
            $capturas['capturas'][0]['respuestas_originales'][0][$fila][$campo] = 'IDENTIDAD-AJENA';
            self::assertTrue($capturas['capturas'][0]['respuestas_originales'][0]['coherente']);
            try {
                (new ConstructorMemoriaInstrumental)->construir($p, $c, $capturas, $contrato['vinculos_instrumentales']);
                self::fail('El motor confió en la etiqueta coherente sin cotejar las PK.');
            } catch (DomainException $e) {
                self::assertSame('La opción original y el literal observacional son incompatibles.', $e->getMessage());
            }
        }
    }

    public function test_observacion_nominal_tampoco_acredita_medicion_instrumental_de_memoria(): void
    {
        $this->prepararMemoriaTextual();
        $this->prepararObservacionesNominales();
        $r = $this->consultar();
        $memoria = $r['resultado']['perfil']['COG-MEM'];
        self::assertSame([], $memoria['conclusiones_candidatas']);
        self::assertSame('NO_ADMISIBLE', $memoria['traza']['evidencias'][0]['admisibilidad']);
        self::assertContains('OBSERVACION_NO_ACREDITA_COMPONENTE_INSTRUMENTAL', $memoria['traza']['evidencias'][0]['motivos']);
        self::assertSame('OBSERVACION_CLINICA', $memoria['traza']['evidencias'][0]['procedencia_instrumental']['naturaleza_fuente']);
        self::assertNull($r['resultado']['clasificacion_dcl']);
    }

    public function test_opcion_unica_de_catalogo_retirado_conserva_la_observacion_historica(): void
    {
        $this->prepararObservacionesNominales();
        $instrumento = DB::table('aplicaciones_instrumento')->where('cod_aplicacion', $this->aplicacion)->value('cod_instrumento');
        DB::table('instrumentos')->where('cod_instrumento', $instrumento)->update(['estado' => 'INACTIVO']);
        DB::table('preguntas_instrumento')->where('cod_instrumento', $instrumento)->update(['tipo_respuesta' => 'OPCION_UNICA', 'estado' => 'INACTIVA']);
        DB::table('opciones_pregunta')->update(['estado' => 'INACTIVA']);
        self::assertCount(1, $this->consultar()['resultado']['perfil']['COG-ATE']['conclusiones_candidatas']);
    }

    public function test_sucesoras_heredan_vinculos_traducidos_y_archivar_conserva_la_reproduccion_original(): void
    {
        $this->prepararObservacionesNominales();
        $antes = $this->consultar();
        $dto = $this->instantanea($antes);
        $original = app(ConocimientoVersionado::class)->instantanea($this->version);
        $vinculosOriginales = config('sistema_experto.contratos_conocimiento.'.$this->version.'.vinculos_instrumentales');
        $sucesora = app(GestionarReglasExpertas::class)->crearRevision($this->usuario, $this->version,
            'ORION-NOMINAL-1', 'Revisar el conocimiento conservando las fuentes nominales originales.');
        config(['sistema_experto.version_cognitiva' => $sucesora]);
        $r = $this->consultar();
        self::assertCount(1, $r['resultado']['perfil']['COG-ATE']['conclusiones_candidatas']);
        $heredados = app(ContratosConocimiento::class)->porVersion($sucesora)['vinculos_instrumentales'];
        self::assertCount(3, $heredados);
        self::assertSame([], array_intersect(array_keys($heredados), array_keys($vinculosOriginales)));
        self::assertSame(array_values($vinculosOriginales), array_values($heredados));
        self::assertSame($original, app(ConocimientoVersionado::class)->instantanea($this->version));
        $regla = array_values($r['resultado']['perfil']['COG-ATE']['conclusiones_candidatas'])[0]['reglas'][0];
        $archivo = app(GestionarReglasExpertas::class)->crearRevision($this->usuario, $sucesora,
            'ORION-NOMINAL-2', 'Archivar la regla sin modificar sus evidencias ni resultados previos.', retiradas: [$regla]);
        config(['sistema_experto.version_cognitiva' => $archivo]);
        self::assertSame([], $this->consultar()['resultado']['perfil']['COG-ATE']['conclusiones_candidatas']);
        self::assertSame($dto->contenido, InstantaneaInstrumental::verificar($dto->contenido, $dto->huella)->contenido);
        self::assertSame($vinculosOriginales, config('sistema_experto.contratos_conocimiento.'.$this->version.'.vinculos_instrumentales'));
        self::assertDatabaseCount('evaluaciones_expertas', 0);
    }

    public function test_sucesora_no_hereda_un_vinculo_cuya_identidad_semantica_cambio(): void
    {
        $sucesora = app(GestionarReglasExpertas::class)->crearRevision($this->usuario, $this->version,
            'ORION-VINCULO-INVALIDO', 'Negativo de extracción alterada entre revisiones de conocimiento.');
        DB::table('mapeos_variables_fuente')->where('cod_version_modelo', $sucesora)
            ->where('clave_selector', $this->primeraPregunta)->update(['clave_selector' => 'OTRA-PREGUNTA']);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('El vínculo heredado no tiene un mapeo semántico único en la sucesora.');
        app(ContratosConocimiento::class)->porVersion($sucesora);
    }

    public function test_sucesora_con_dos_mapeos_equivalentes_no_elige_uno_por_su_estado(): void
    {
        $sucesora = app(GestionarReglasExpertas::class)->crearRevision($this->usuario, $this->version,
            'ORION-VINCULO-AMBIGUO', 'Negativo de identidad semántica duplicada en una sucesora.');
        $duplicado = (array) DB::table('mapeos_variables_fuente')->where('cod_version_modelo', $sucesora)
            ->where('clave_selector', $this->primeraPregunta)->sole();
        $duplicado['cod_mapeo_variable_fuente'] = 'MAPEO-AMBIGUO';
        $duplicado['estado'] = 'INACTIVO';
        DB::table('mapeos_variables_fuente')->insert($duplicado);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('El vínculo heredado no tiene un mapeo semántico único en la sucesora.');
        app(ContratosConocimiento::class)->porVersion($sucesora);
    }

    private function prepararObservacionesNominales(): void
    {
        $instrumento = DB::table('aplicaciones_instrumento')->where('cod_aplicacion', $this->aplicacion)->value('cod_instrumento');
        DB::table('instrumentos')->where('cod_instrumento', $instrumento)->update(['tipo' => 'OBSERVACIONAL']);
        foreach (DB::table('preguntas_instrumento')->where('cod_instrumento', $instrumento)->get() as $q) {
            $literal = 'literal_observado_'.$q->codigo;
            DB::table('preguntas_instrumento')->where('cod_pregunta', $q->cod_pregunta)->update(['tipo_respuesta' => 'OPCION']);
            $opcion = OpcionPregunta::create(['cod_pregunta' => $q->cod_pregunta, 'nombre' => 'Etiqueta sin interpretación',
                'valor' => $literal, 'puntaje' => null, 'orden' => 1, 'estado' => 'ACTIVA']);
            DB::table('respuestas_instrumento')->where('cod_aplicacion', $this->aplicacion)->where('cod_pregunta', $q->cod_pregunta)
                ->update(['valor_logico' => null, 'valor_texto' => $literal, 'cod_opcion' => $opcion->getKey()]);
            $mapa = DB::table('mapeos_variables_fuente')->where('cod_version_modelo', $this->version)->where('clave_selector', $q->cod_pregunta)
                ->value('cod_mapeo_variable_fuente');
            DB::table('mapeos_valores_fuente')->where('cod_mapeo_variable_fuente', $mapa)
                ->update(['tipo_valor_fuente' => 'string', 'valor_fuente_exacto' => $literal]);
            config(['sistema_experto.contratos_conocimiento.'.$this->version.'.vinculos_instrumentales.'.$mapa.'.campo' => 'valor_texto']);
        }
    }

    public function test_perfil_e_inferencia_usan_una_sola_captura_aunque_el_original_se_rectifique_despues(): void
    {
        $original = app(FuenteConocimientoOrion::class);
        app()->instance(FuenteConocimientoOrion::class, new class($original, $this->aplicacion, $this->primeraPregunta) implements FuenteConocimientoOrion
        {
            public function __construct(private FuenteConocimientoOrion $original, private string $aplicacion, private string $pregunta) {}

            public function leer(): array
            {
                // Simula una rectificación después de extraer, antes de construir el perfil.
                DB::table('respuestas_instrumento')->where('cod_aplicacion', $this->aplicacion)
                    ->where('cod_pregunta', $this->pregunta)->update(['valor_logico' => null]);

                return $this->original->leer();
            }
        });
        $lecturas = 0;
        DB::listen(static function ($q) use (&$lecturas): void {
            if (str_starts_with(strtolower($q->sql), 'select * from "aplicaciones_instrumento"')) {
                $lecturas++;
            }
        });
        $perfil = app(ConsultarPerfilInstrumental::class)->consultar($this->usuario, $this->residente, CarbonImmutable::now());
        self::assertSame(1, $lecturas);
        self::assertNotNull($perfil['capturas_originales']['capturas'][0]['respuestas_originales'][0]['valor_logico']);
        self::assertCount(1, $perfil['inferencia_declarativa']['resultado']['perfil']['COG-ATE']['conclusiones_candidatas']);
        self::assertNull(DB::table('respuestas_instrumento')->where('cod_aplicacion', $this->aplicacion)
            ->where('cod_pregunta', $this->primeraPregunta)->value('valor_logico'));
        self::assertArrayNotHasKey('capturas', $perfil['inferencia_declarativa']);
    }

    public function test_traza_conserva_respuesta_documento_edad_y_precision_del_corte(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('original-sintetico.txt', 'Protocolo original sintético.');
        $documento = DocumentoClinico::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->usuario->personal->getKey(),
            'tipo_documento' => 'INFORME', 'titulo' => 'Original de prueba', 'ruta_archivo' => 'original-sintetico.txt', 'formato' => 'text/plain',
            'hash_archivo' => hash('sha256', 'Protocolo original sintético.'), 'fecha_hora' => now(), 'estado' => 'VIGENTE']);
        DB::table('aplicaciones_instrumento')->where('cod_aplicacion', $this->aplicacion)
            ->update(['cod_documento_clinico' => $documento->getKey(), 'localizador_fuente' => 'Página 1 / C1']);
        $corte = CarbonImmutable::parse('2026-10-19T11:59:59.123456-04:00');
        $r = app(ConsultarConocimientoCognitivo::class)->consultar($this->usuario, $this->residente, $corte);
        $traza = $r['resultado']['perfil']['COG-ATE']['traza'];
        self::assertSame($corte->format('Y-m-d\TH:i:s.uP'), $r['resultado']['fecha_corte']);
        self::assertSame($r['resultado']['fecha_corte'], $traza['fecha_corte']);
        $ruta = array_values(array_filter($traza['rutas'], fn ($r) => $r['procedencia_instrumental']['cod_pregunta'] === $this->primeraPregunta))[0];
        $p = $ruta['procedencia_instrumental'];
        self::assertSame($this->primeraPregunta, $p['cod_pregunta']);
        self::assertSame('valor_logico', $p['campo_original']);
        self::assertSame(DB::table('respuestas_instrumento')->where('cod_aplicacion', $this->aplicacion)
            ->where('cod_pregunta', $this->primeraPregunta)->value('cod_respuesta'), $p['cod_respuesta']);
        self::assertSame('FIXTURE-1', $p['instrumento']['version']);
        self::assertSame('C1', $p['pregunta_original']['codigo']);
        self::assertSame($documento->getKey(), $p['registro_original']['cod_documento_clinico']);
        self::assertSame('VERIFICADO', $p['registro_original']['estado_documento']);
        self::assertSame('1950-10-18', $p['fuente_edad']['fecha_nacimiento']);
        $evidencia = array_values(array_filter($traza['evidencias'], fn ($e) => $e['id'] === $ruta['evidencia']))[0];
        self::assertSame($p, $evidencia['procedencia_instrumental']);
        self::assertStringNotContainsString('ruta_archivo', serialize($p));
    }

    public function test_pregunta_declarativa_sin_respuesta_conserva_definicion_y_ausencia(): void
    {
        DB::table('respuestas_instrumento')->where('cod_aplicacion', $this->aplicacion)->where('cod_pregunta', $this->primeraPregunta)->delete();
        $r = $this->consultar();
        $e = array_values(array_filter($r['resultado']['perfil']['COG-ATE']['traza']['evidencias'],
            fn ($e) => $e['procedencia_instrumental']['cod_pregunta'] === $this->primeraPregunta))[0];
        self::assertSame('NO_DISPONIBLE', $e['representacion']);
        self::assertNull($e['procedencia_instrumental']['cod_respuesta']);
        self::assertSame('C1', $e['procedencia_instrumental']['pregunta_original']['codigo']);
        self::assertSame('Literal sintético 1', $e['procedencia_instrumental']['pregunta_original']['enunciado']);
        self::assertSame([], $r['resultado']['perfil']['COG-ATE']['conclusiones_candidatas']);
    }

    public function test_instantanea_reproduce_sin_sql_configuracion_o_reglas_actuales(): void
    {
        $r = $this->consultar();
        $dto = $this->instantanea($r);
        self::assertSame($dto->huella, $r['preparacion_conservacion']['huella']);
        self::assertFalse($r['preparacion_conservacion']['persistido']);
        DB::table('respuestas_instrumento')->where('cod_aplicacion', $this->aplicacion)->update(['valor_logico' => null]);
        DB::table('reglas_expertas')->where('cod_version_modelo', $this->version)->update(['estado' => 'INACTIVO']);
        DB::table('versiones_modelo_experto')->where('cod_version_modelo', $this->version)->update(['estado' => 'INACTIVO']);
        config(['sistema_experto.contratos_conocimiento' => [], 'sistema_experto.perfiles_instrumentales' => []]);
        $consultas = 0;
        DB::listen(static function () use (&$consultas): void {
            $consultas++;
        });
        $restaurada = InstantaneaInstrumental::verificar($dto->contenido, $dto->huella);
        self::assertSame(0, $consultas);
        self::assertSame($dto->contenido, $restaurada->contenido);
        self::assertSame('ACTIVO', $restaurada->contenido['cabecera_conocimiento']['estado']);
        self::assertCount(1, $restaurada->contenido['salida']['resultado']['perfil']['COG-ATE']['conclusiones_candidatas']);
        $corrupta = $dto->contenido;
        $corrupta['capturas']['capturas'][0]['respuestas_originales'][0]['valor_logico'] = null;
        $this->expectException(DomainException::class);
        InstantaneaInstrumental::verificar($corrupta, $dto->huella);
    }

    private function instantanea(array $r): InstantaneaInstrumental
    {
        $contrato = config('sistema_experto.contratos_conocimiento.'.$this->version);
        $tablas = app(ConocimientoVersionado::class)->instantanea($this->version);
        $p = new PaqueteConocimiento($this->version, $tablas, $contrato['relaciones'], false, $contrato['tipo_regla_resultado'], $contrato['extracciones']);
        $c = new ContextoEjecucion($r['resultado']['evaluacion'], $this->residente->getKey(), $this->version, CarbonImmutable::now()->toDateTimeImmutable());
        $cabecera = (array) DB::table('versiones_modelo_experto')->where('cod_version_modelo', $this->version)->sole();

        return InstantaneaInstrumental::crear($p, $c, $r['capturas'], $contrato['vinculos_instrumentales'], $this->usuario->getKey(), $cabecera);
    }

    public function test_memoria_usa_literal_textual_original_y_su_admisibilidad_especifica(): void
    {
        $this->prepararMemoriaTextual();
        $memoria = $this->consultar()['resultado']['perfil']['COG-MEM'];
        self::assertSame(['DIFICULTAD_EVIDENCIADA'], array_column($memoria['conclusiones_candidatas'], 'codigo'));
        self::assertCount(1, $memoria['traza']['rutas']);
        self::assertSame('literal_A', $memoria['traza']['rutas'][0]['valor_original']);
        self::assertSame('ADMISIBLE', $memoria['traza']['evidencias'][0]['admisibilidad']);
    }

    public function test_observacion_literal_no_acredita_instrumento_de_memoria_por_un_vinculo_configurado(): void
    {
        $this->prepararMemoriaTextual();
        $instrumento = DB::table('aplicaciones_instrumento')->where('cod_aplicacion', $this->aplicacion)->value('cod_instrumento');
        DB::table('instrumentos')->where('cod_instrumento', $instrumento)->update(['tipo' => 'OBSERVACIONAL']);
        $r = $this->consultar();
        $memoria = $r['resultado']['perfil']['COG-MEM'];
        self::assertSame([], $memoria['conclusiones_candidatas']);
        self::assertSame('NO_ADMISIBLE', $memoria['traza']['evidencias'][0]['admisibilidad']);
        self::assertContains('OBSERVACION_NO_ACREDITA_COMPONENTE_INSTRUMENTAL', $memoria['traza']['evidencias'][0]['motivos']);
        self::assertSame('literal_A', $r['capturas']['capturas'][0]['respuestas_originales'][0]['valor_texto']);
        self::assertDatabaseCount('evaluaciones_expertas', 0);
    }

    private function prepararMemoriaTextual(): void
    {
        $mapa = DB::table('mapeos_variables_fuente')->where('cod_version_modelo', $this->version)
            ->where('clave_selector', $this->primeraPregunta)->sole();
        $variable = DB::table('variables_expertas as v')->join('nodos_semanticos as n', 'n.cod_nodo_semantico', '=', 'v.cod_nodo_semantico')
            ->where('v.cod_version_modelo', $this->version)->where('n.codigo_semantico', 'VAR-MEM-INST-01')->sole();
        $valor = DB::table('valores_semanticos as v')->join('dominios_valores_expertos as d', 'd.cod_dominio_valores', '=', 'v.cod_dominio_valores')
            ->where('d.cod_version_modelo', $this->version)->where('v.codigo_valor', 'TEST_A')->sole();
        DB::table('preguntas_instrumento')->where('cod_pregunta', $this->primeraPregunta)->update(['tipo_respuesta' => 'TEXTO']);
        DB::table('respuestas_instrumento')->where('cod_aplicacion', $this->aplicacion)->where('cod_pregunta', $this->primeraPregunta)
            ->update(['valor_logico' => null, 'valor_texto' => 'literal_A']);
        DB::table('mapeos_variables_fuente')->where('cod_mapeo_variable_fuente', $mapa->cod_mapeo_variable_fuente)
            ->update(['cod_variable_experta' => $variable->cod_variable_experta]);
        DB::table('mapeos_valores_fuente')->where('cod_mapeo_variable_fuente', $mapa->cod_mapeo_variable_fuente)
            ->update(['cod_valor_semantico' => $valor->cod_valor_semantico, 'tipo_valor_fuente' => 'string', 'valor_fuente_exacto' => 'literal_A']);
        config(['sistema_experto.contratos_conocimiento.'.$this->version.'.vinculos_instrumentales.'.$mapa->cod_mapeo_variable_fuente.'.campo' => 'valor_texto']);

    }

    public function test_fuente_sin_columna_original_de_autor_se_rechaza(): void
    {
        DB::table('fuentes_datos_expertas')->where('cod_version_modelo', $this->version)
            ->where('tabla_raiz', 'aplicaciones_instrumento')->update(['campo_personal' => null]);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('La fuente no declara las columnas originales');
        $this->consultar();
    }

    public function test_respuesta_con_dos_valores_originales_no_se_interpreta(): void
    {
        DB::table('respuestas_instrumento')->where('cod_aplicacion', $this->aplicacion)
            ->where('cod_pregunta', $this->primeraPregunta)->update(['valor_texto' => 'Literal sintético incompatible']);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Respuesta incompatible');
        $this->consultar();
    }

    public function test_edad_historica_se_calcula_en_aplicacion_y_menor_60_se_excluye(): void
    {
        DB::table('residentes')->where('cod_residente', $this->residente->getKey())->update(['fecha_nacimiento' => '1966-10-19']);
        $r = $this->consultar();
        self::assertSame([], $r['resultado']['perfil']['COG-ATE']['conclusiones_candidatas']);
        self::assertCount(3, $r['fuentes_excluidas']);
        DB::table('residentes')->where('cod_residente', $this->residente->getKey())->update(['fecha_nacimiento' => '1966-10-18']);
        self::assertNotEmpty($this->consultar()['resultado']['perfil']['COG-ATE']['conclusiones_candidatas']);
    }

    public function test_cuenta_inactiva_no_accede(): void
    {
        DB::table('usuarios')->where('cod_usuario', $this->usuario->getKey())->update(['estado' => 'INACTIVO']);
        $this->expectException(AuthorizationException::class);
        $this->consultar();
    }

    public function test_familiar_no_consulta_inferencia_con_permisos_aislados(): void
    {
        $this->usuario->syncRoles(['FAMILIAR']);
        $this->usuario->givePermissionTo(['residentes.ver', 'controles_cognitivos.ver', 'valoracion_medica.ver', 'respuestas_instrumento.ver']);
        $this->expectException(AuthorizationException::class);
        $this->consultar();
    }

    public function test_no_lee_aplicaciones_de_otro_residente(): void
    {
        $otro = Residente::factory()->create(['estado' => 'ADMITIDO', 'fecha_nacimiento' => '1950-10-18']);
        $r = app(ConsultarConocimientoCognitivo::class)->consultar($this->usuario, $otro, CarbonImmutable::now());
        self::assertSame([], $r['resultado']['perfil']['COG-ATE']['traza']['evidencias']);
        self::assertSame([], $r['resultado']['perfil']['COG-ATE']['conclusiones_candidatas']);
        self::assertSame($otro->getKey(), $r['resultado']['residente']);
    }

    public function test_sin_configuracion_y_sin_vinculos_se_declara_ausencia(): void
    {
        config(['sistema_experto.version_cognitiva' => null]);
        self::assertSame('SIN_VERSION_COGNITIVA_VINCULADA', $this->consultar()['motivo']);
        config(['sistema_experto.version_cognitiva' => $this->version,
            'sistema_experto.contratos_conocimiento.'.$this->version.'.vinculos_instrumentales' => []]);
        self::assertSame('SIN_VINCULOS_ORIGINALES_DEL_CONOCIMIENTO', $this->consultar()['motivo']);
    }

    public function test_corte_anterior_no_incluye_la_aplicacion_y_futuro_es_invalido(): void
    {
        $r = app(ConsultarConocimientoCognitivo::class)->consultar($this->usuario, $this->residente, CarbonImmutable::parse('2026-10-17 12:00:00'));
        self::assertSame([], $r['resultado']['perfil']['COG-ATE']['traza']['evidencias']);
        $this->expectException(DomainException::class);
        app(ConsultarConocimientoCognitivo::class)->consultar($this->usuario, $this->residente, CarbonImmutable::now()->addDay());
    }

    public function test_fuente_anulada_no_se_reutiliza(): void
    {
        DB::table('aplicaciones_instrumento')->where('cod_aplicacion', $this->aplicacion)->update(['estado' => 'ANULADA']);
        $r = $this->consultar();
        self::assertSame([], $r['resultado']['perfil']['COG-ATE']['conclusiones_candidatas']);
        self::assertSame(['APLICACION_NO_COMPLETA'], $r['fuentes_excluidas'][0]['motivos']);
    }

    public function test_permiso_fuente_retirado_no_devuelve_datos(): void
    {
        $this->usuario->roles()->sole()->revokePermissionTo('respuestas_instrumento.ver');
        $r = $this->consultar();
        self::assertFalse($r['disponible']);
        self::assertSame('ACCESO_A_FUENTES_NO_DISPONIBLE', $r['motivo']);
        self::assertArrayNotHasKey('resultado', $r);
    }

    public function test_version_sin_vigencia_no_usa_fallback_al_laboratorio(): void
    {
        DB::table('versiones_modelo_experto')->where('cod_version_modelo', $this->version)->update(['estado' => 'INACTIVO']);
        $this->expectException(DomainException::class);
        $this->consultar();
    }
}
