<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AplicacionInstrumento;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\DocumentoClinico;
use App\Models\Instrumento;
use App\Models\Jornada;
use App\Models\OpcionPregunta;
use App\Models\Personal;
use App\Models\PreguntaInstrumento;
use App\Models\Residente;
use App\Models\RespuestaInstrumento;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\CapturasInstrumentalesResidente;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarPerfilInstrumental;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResultadosExpertoResidente;
use App\Modulos\Valoraciones\Aplicacion\Acciones\AnularAplicacionInstrumento;
use App\Modulos\Valoraciones\Aplicacion\Acciones\AplicarInstrumento;
use App\Modulos\Valoraciones\Aplicacion\Consultas\PrepararCapturaInstrumental;
use App\Modulos\Valoraciones\Presentacion\Livewire\Funcionalidades\EvaluacionGeriatricaModal;
use Carbon\CarbonImmutable;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use MotorExperto\dominio\instrumentos\NormalizadorBateriaBolivia2015;
use MotorExperto\dominio\instrumentos\NormalizadorHvltBolivia2015 as Hvlt;
use MotorExperto\dominio\instrumentos\NormalizadorReferenciasEspana;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

final class CapturaInstrumentalInstitucionalTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    private Residente $residente;

    private Instrumento $instrumento;

    private array $perfil;

    private array $respuestas;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-10-18 12:00:00'));
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $this->usuario->assignRole('PSICOLOGO/A');
        $personal = Personal::create(['cod_usuario' => $this->usuario->getKey(), 'nombres' => 'Profesional', 'apellido_paterno' => 'Sintético',
            'numero_documento' => 'CAPTURA-TEST', 'profesion' => 'PSICOLOGIA', 'estado' => 'ACTIVO']);
        $area = Area::create(['nombre' => 'Área sintética', 'estado' => 'ACTIVA']);
        $turno = Turno::create(['nombre' => 'Turno sintético', 'hora_inicio' => '00:00', 'hora_cierre' => '23:59', 'orden' => 1, 'estado' => 'ACTIVO']);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        AsignacionPersonal::create(['cod_personal' => $personal->getKey(), 'cod_jornada' => $jornada->getKey(),
            'cod_area' => $area->getKey(), 'fecha_asignacion' => now(), 'estado' => 'ACTIVA']);
        $this->residente = Residente::factory()->create(['estado' => 'ADMITIDO', 'fecha_nacimiento' => '1950-06-15']);
        $this->actingAs($this->usuario);
        $this->instrumento = Instrumento::create(['codigo' => 'CAPTURA-SINTETICA', 'nombre' => 'Medidas sintéticas de integración',
            'tipo' => 'COGNITIVO', 'version' => 'TEST-1', 'estado' => 'ACTIVO']);
        $selectores = $this->respuestas = [];
        foreach (Hvlt::ENTRADAS as $campo => $tipo) {
            $pregunta = PreguntaInstrumento::create(['cod_instrumento' => $this->instrumento->getKey(), 'codigo' => 'MEDIDA-'.(count($selectores) + 1),
                'enunciado' => 'Medida sintética '.$campo, 'tipo_respuesta' => $tipo === 'LOGICO' ? 'LOGICO' : 'NUMERICO',
                'orden' => count($selectores) + 1, 'estado' => 'ACTIVA']);
            $columna = $tipo === 'LOGICO' ? 'valor_logico' : 'valor_numero';
            $selectores[$campo] = ['cod_pregunta' => $pregunta->getKey(), 'campo' => $columna];
            $valor = match ($campo) {
                'ensayo_1' => 6, 'ensayo_2' => 7, 'ensayo_3' => 8, 'recuerdo_diferido' => 4,
                'demora_minutos' => 22, 'escolaridad_anios' => 4, default => true,
            };
            $this->respuestas[] = ['cod_pregunta' => $pregunta->getKey(), $columna => $valor];
        }
        $this->perfil = ['id' => 'CAPTURA-HVLT-TEST', 'cod_instrumento' => $this->instrumento->getKey(),
            'codigo_instrumento' => $this->instrumento->codigo, 'version_instrumento' => 'TEST-1', 'metodo' => Hvlt::METODO,
            'identidad_instrumento' => ['instrumento' => 'HVLT-R', 'forma' => 5, 'idioma' => 'es'], 'selectores' => $selectores];
    }

    private function aplicar(array $cambios = []): AplicacionInstrumento
    {
        return app(AplicarInstrumento::class)->aplicar($this->usuario, array_replace(['fecha_hora' => '2020-10-18 09:10:00', 'respuestas' => $this->respuestas], $cambios), $this->instrumento, $this->residente);
    }

    private function captura(): array
    {
        return app(CapturasInstrumentalesResidente::class)->consultar($this->usuario, $this->residente, CarbonImmutable::now());
    }

    /** Recorre captura SQL autorizada y perfil real; los seis perfiles usan solo fixtures sintéticas. */
    public function test_referencias_espanolas_se_integran_desde_sql_sin_escribir_resultados_clinicos(): void
    {
        $valores = ['escolaridad_anios' => 12, 'sexo_norma' => 1, 'longitud_inversa' => 3,
            'longitud_letras_numeros' => 4, 'aciertos' => 18, 'elementos_puntuados' => 30,
            'libre_1' => 6, 'adicional_claves_1' => 7, 'libre_2' => 8, 'adicional_claves_2' => 6,
            'libre_3' => 10, 'adicional_claves_3' => 5];
        $perfiles = [];
        foreach ((new NormalizadorReferenciasEspana)->metodos() as $metodo => $meta) {
            $instrumento = Instrumento::create(['codigo' => 'NORMA-ES-TEST-'.count($perfiles), 'nombre' => 'Referencia sintética',
                'tipo' => 'COGNITIVO', 'version' => 'VERSION-TEST', 'estado' => 'ACTIVO']);
            $selectores = $respuestas = [];
            foreach ($meta['entradas'] as $campo => $tipo) {
                $q = PreguntaInstrumento::create(['cod_instrumento' => $instrumento->getKey(), 'codigo' => 'ENTRADA-'.count($selectores),
                    'enunciado' => 'Entrada sintética '.$campo, 'tipo_respuesta' => $tipo === 'LOGICO' ? 'LOGICO' : 'NUMERICO',
                    'orden' => count($selectores) + 1, 'estado' => 'ACTIVA']);
                $columna = $tipo === 'LOGICO' ? 'valor_logico' : 'valor_numero';
                $selectores[$campo] = ['cod_pregunta' => $q->getKey(), 'campo' => $columna];
                $respuestas[] = ['cod_pregunta' => $q->getKey(),
                    $columna => $valores[$campo] ?? ($tipo === 'LOGICO' ? true : throw new \LogicException('Falta valor sintético.'))];
            }
            $perfiles[] = ['id' => 'NORMA-ES-TEST-'.count($perfiles), 'cod_instrumento' => $instrumento->getKey(),
                'codigo_instrumento' => $instrumento->codigo, 'version_instrumento' => $instrumento->version,
                'metodo' => $metodo, 'identidad_instrumento' => $meta['identidad'], 'selectores' => $selectores];
            $a = app(AplicarInstrumento::class)->aplicar($this->usuario,
                ['fecha_hora' => '2020-10-18 09:10:00', 'respuestas' => $respuestas], $instrumento, $this->residente);
            self::assertNull($a->puntaje_total);
            self::assertNull($a->clasificacion);
        }
        config(['sistema_experto.perfiles_instrumentales' => $perfiles]);
        $capturas = $this->captura()['capturas'];
        self::assertCount(6, $capturas);
        foreach ($capturas as $captura) {
            $e = $captura['extraccion'];
            self::assertSame([], $e['motivos_captura']);
            self::assertSame(70, $e['captura']['edad_anios']);
            self::assertSame('NO_CONFIGURADA', $e['normalizacion']['adopcion_institucional']);
            foreach ($e['normalizacion']['componentes'] as $componente) {
                foreach ($componente['medidas'] ?? [$componente] as $m) {
                    self::assertSame('NORMALIZADO', $m['estado']);
                    self::assertTrue(is_finite($m['z']));
                    self::assertNull($m['clasificacion_clinica']);
                }
            }
            foreach ($e['fuentes_entradas'] as $fuente) {
                self::assertSame($captura['cod_aplicacion'], $fuente['aplicacion']);
                self::assertNotEmpty($fuente['respuesta']);
            }
        }
        $r = app(ConsultarPerfilInstrumental::class)->consultar($this->usuario, $this->residente, CarbonImmutable::now());
        $mediciones = $r['dominios']['COG-EJE']['componentes']['EJE-S03']['mediciones'];
        $conjuntas = array_values(array_filter($mediciones, fn ($m) => $m['metodo'] === NormalizadorReferenciasEspana::SPAN));
        self::assertCount(2, $conjuntas);
        self::assertSame($conjuntas[0]['aplicacion'], $conjuntas[1]['aplicacion']);
        self::assertNull($r['diagnostico']);
        self::assertFalse($r['persistido']);
        self::assertDatabaseCount('aplicaciones_instrumento', 6);
        self::assertDatabaseCount('evaluaciones_expertas', 0);
    }

    public function test_originales_sin_puntuacion_conservan_null_fecha_y_autoria(): void
    {
        $a = $this->aplicar(['cod_personal' => 'AUTOR-IMPUESTO', 'puntaje_total' => 999, 'observacion' => 'Observación sintética original.']);
        self::assertNull($a->puntaje_total);
        self::assertNull($a->puntaje_maximo);
        self::assertSame('2020-10-18 09:10:00', $a->fecha_hora->format('Y-m-d H:i:s'));
        self::assertSame($this->usuario->personal()->sole()->getKey(), $a->cod_personal);
        self::assertCount(11, $a->respuestas);
        self::assertSame($a->respuestas[0]->getRawOriginal('valor_numero'), $this->captura()['capturas'][0]['respuestas_originales'][0]['valor_numero']);
        self::assertNull($this->captura()['capturas'][0]['extraccion']);
        self::assertContains('SIN_PERFIL_EXPLICITO', $this->captura()['capturas'][0]['motivos']);
        self::assertSame('Observación sintética original.', $this->captura()['capturas'][0]['aplicacion_actual']['observacion']);
        self::assertSame($this->instrumento->version, $this->captura()['capturas'][0]['instrumento_actual']['version']);
        self::assertSame($this->respuestas[0]['cod_pregunta'], $this->captura()['capturas'][0]['respuestas_originales'][0]['pregunta_actual']['cod_pregunta']);
    }

    public function test_normaliza_desde_preguntas_exactas_y_edad_de_la_aplicacion_sin_escribir_resultados(): void
    {
        $a = $this->aplicar();
        config(['sistema_experto.perfiles_instrumentales' => [$this->perfil]]);
        $r = $this->captura()['capturas'][0];
        self::assertSame([], $r['motivos']);
        self::assertSame(70, $r['extraccion']['captura']['edad_anios']);
        self::assertSame([6, 7, 8], $r['extraccion']['captura']['ensayos']);
        self::assertSame('NORMALIZADO', $r['extraccion']['normalizacion']['componentes']['MEM-S01']['estado']);
        self::assertEqualsWithDelta(16.713, $r['extraccion']['normalizacion']['componentes']['MEM-S01']['esperado'], 0.000001);
        self::assertSame($a->getKey(), $r['extraccion']['fuentes_entradas']['ensayo_1']['aplicacion']);
        self::assertSame($a->respuestas[0]->getRawOriginal('valor_numero'), $r['extraccion']['fuentes_entradas']['ensayo_1']['bruto']);
        self::assertNull($r['extraccion']['normalizacion']['diagnostico']);
        self::assertDatabaseCount('evaluaciones_expertas', 0);
        self::assertNull($a->fresh()->puntaje_total);
        $perfil = app(ConsultarPerfilInstrumental::class)->consultar($this->usuario, $this->residente, CarbonImmutable::now());
        self::assertSame($r['extraccion']['normalizacion']['componentes']['MEM-S01'], $perfil['dominios']['COG-MEM']['componentes']['MEM-S01']['mediciones'][0]['resultado']);
        self::assertSame('MEDICIONES_DISPONIBLES', $perfil['dominios']['COG-MEM']['componentes']['MEM-S01']['estado_medicion']);
        self::assertNull($perfil['dominios']['COG-MEM']['resultado_clinico']);
    }

    public function test_faltantes_no_se_llenan_y_cero_original_se_conserva(): void
    {
        $respuestas = $this->respuestas;
        $respuestas[0]['valor_numero'] = 0;
        array_pop($respuestas);
        $this->aplicar(['respuestas' => $respuestas]);
        config(['sistema_experto.perfiles_instrumentales' => [$this->perfil]]);
        $r = $this->captura()['capturas'][0]['extraccion'];
        self::assertSame(0, $r['captura']['ensayos'][0]);
        self::assertNull($r['captura']['administracion_estandarizada']);
        self::assertSame('NO_NORMALIZABLE', $r['normalizacion']['componentes']['MEM-S01']['estado']);
        self::assertContains('RESPUESTA_AUSENTE_O_INCOMPATIBLE:administracion_estandarizada', $r['motivos_captura']);
    }

    public function test_sin_permiso_de_respuestas_no_consulta_ni_expone_las_fuentes(): void
    {
        $this->aplicar();
        $this->usuario->syncPermissions([]);
        $rol = Role::findByName('PSICOLOGO/A');
        $rol->revokePermissionTo('respuestas_instrumento.ver');
        $consultas = [];
        DB::listen(function ($q) use (&$consultas): void {
            if (preg_match('/(?:aplicaciones_instrumento|respuestas_instrumento)/', $q->sql)) {
                $consultas[] = $q->sql;
            }
        });
        $r = $this->captura();
        self::assertFalse($r['disponible']);
        self::assertSame([], $r['capturas']);
        self::assertSame([], $consultas);
    }

    public static function valoresInvalidos(): array
    {
        return [['fecha_hora', '2030-01-01 10:00:00'], ['fecha_hora', '2020-10-18'], ['numero', '1.00001'], ['numero', '100000000'],
            ['tipo', 'texto'], ['multiple', 'ambos'], ['puntaje', 5], ['clave', 'cod_aplicacion']];
    }

    #[DataProvider('valoresInvalidos')]
    public function test_rechaza_fecha_tipo_puntaje_o_identidad_incompatible_sin_escrituras(string $campo, mixed $valor): void
    {
        $entrada = ['respuestas' => $this->respuestas];
        match ($campo) {
            'fecha_hora' => $entrada['fecha_hora'] = $valor,
            'numero' => $entrada['respuestas'][0]['valor_numero'] = $valor,
            'tipo' => $entrada['respuestas'][0] = ['cod_pregunta' => $this->respuestas[0]['cod_pregunta'], 'valor_texto' => 'Etiqueta nominal'],
            'multiple' => $entrada['respuestas'][0]['valor_texto'] = 'Otro valor',
            'puntaje' => $entrada['respuestas'][0]['puntaje'] = $valor,
            'clave' => $entrada['respuestas'][0][$valor] = 'APLICACION-AJENA',
        };
        try {
            $this->aplicar($entrada);
            self::fail('Debe rechazar la entrada incompatible.');
        } catch (ValidationException|HttpException $e) {
            self::assertTrue($e instanceof ValidationException || $e->getStatusCode() === 422);
        }
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
        self::assertDatabaseCount('respuestas_instrumento', 0);
    }

    public function test_fallo_al_guardar_una_respuesta_revierte_la_aplicacion(): void
    {
        RespuestaInstrumento::creating(fn () => throw new \RuntimeException('Fallo sintético de escritura.'));
        try {
            $this->aplicar();
            self::fail('Debe propagar el fallo.');
        } catch (\RuntimeException $e) {
            self::assertSame('Fallo sintético de escritura.', $e->getMessage());
        }
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
        self::assertDatabaseCount('respuestas_instrumento', 0);
    }

    public function test_pantalla_consulta_capturas_sin_depender_de_una_version_investigada(): void
    {
        $this->aplicar();
        config(['sistema_experto.perfiles_instrumentales' => [$this->perfil]]);
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertSee('Mediciones instrumentales del expediente')->assertSee('Medidas sintéticas de integración')
            ->assertSee('2020-10-18')->assertSee('NORMALIZADO')->assertSee('Medición: 21');
        self::assertDatabaseCount('evaluaciones_expertas', 0);
    }

    public function test_error_del_informe_historico_se_informa_y_permite_consultar_fuentes_independientes(): void
    {
        $this->aplicar();
        DB::connection()->beforeExecuting(static function (string $sql, array $bindings): void {
            if (str_contains($sql, 'evaluaciones_expertas') && in_array('D137', $bindings, true)) {
                throw new \DomainException('Detalle interno de integridad.');
            }
        });
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertSee('No se pudo consultar la evaluación')->assertDontSee('Detalle interno de integridad.')
            ->assertSee('Mediciones instrumentales del expediente')->assertSee('Medidas sintéticas de integración')
            ->assertSee('Hallazgos observacionales del seguimiento');
        self::assertDatabaseCount('evaluaciones_expertas', 0);
    }

    public function test_corte_y_residente_aislan_fuentes_y_anulada_no_se_normaliza(): void
    {
        $a = $this->aplicar();
        config(['sistema_experto.perfiles_instrumentales' => [$this->perfil]]);
        $antes = app(CapturasInstrumentalesResidente::class)->consultar($this->usuario, $this->residente, CarbonImmutable::parse('2019-01-01'));
        self::assertSame([], $antes['capturas']);
        $otro = Residente::factory()->create(['estado' => 'ADMITIDO']);
        self::assertSame([], app(CapturasInstrumentalesResidente::class)->consultar($this->usuario, $otro, CarbonImmutable::now())['capturas']);
        DB::table('aplicaciones_instrumento')->where('cod_aplicacion', $a->getKey())->update(['estado' => 'ANULADA']);
        $r = $this->captura()['capturas'][0];
        self::assertCount(11, $r['respuestas_originales']);
        self::assertNull($r['extraccion']);
        self::assertContains('APLICACION_NO_COMPLETA', $r['motivos']);
    }

    public function test_offset_original_se_convierte_a_la_zona_institucional_sin_cambiar_el_instante(): void
    {
        $a = $this->aplicar(['fecha_hora' => '2020-10-18T09:10:00Z']);
        self::assertSame('2020-10-18 05:10:00', $a->fresh()->fecha_hora->format('Y-m-d H:i:s'));
        self::assertSame(CarbonImmutable::parse('2020-10-18T09:10:00Z')->getTimestamp(), $a->fresh()->fecha_hora->getTimestamp());
    }

    public function test_identidad_ambigua_de_perfiles_se_rechaza(): void
    {
        $this->aplicar();
        config(['sistema_experto.perfiles_instrumentales' => [$this->perfil, $this->perfil]]);
        $this->expectException(\DomainException::class);
        $this->captura();
    }

    public function test_etiqueta_nominal_no_reemplaza_medicion_original_y_perfil_no_se_adivina_por_nombre(): void
    {
        $this->instrumento->update(['nombre' => 'HVLT-R']);
        $this->aplicar();
        $r = $this->captura()['capturas'][0];
        self::assertNull($r['extraccion']);
        self::assertContains('SIN_PERFIL_EXPLICITO', $r['motivos']);
        config(['sistema_experto.perfiles_instrumentales' => [$this->perfil]]);
        $id = $this->respuestas[0]['cod_pregunta'];
        DB::table('respuestas_instrumento')->where('cod_pregunta', $id)->update(['valor_numero' => null, 'valor_texto' => 'CONSERVADA']);
        $r = $this->captura()['capturas'][0]['extraccion'];
        self::assertNull($r['captura']['ensayos'][0]);
        self::assertSame('NO_NORMALIZABLE', $r['normalizacion']['componentes']['MEM-S01']['estado']);
    }

    public function test_seis_perfiles_investigados_recuperan_respuestas_originales_y_se_presentan_sin_escribir_evaluaciones(): void
    {
        $valores = ['escolaridad_anios' => 4, 'forma_n_aciertos' => 6, 'forma_l_aciertos' => 7,
            'segundos_parte_b' => 200, 'errores_observados' => 1, 'aciertos_espontaneos' => 40, 'aciertos_clave_semantica' => 5,
            'puntaje_copia' => 22.5, 'elementos_revisados' => 18, 'f_validas' => 8, 'a_validas' => 9, 's_validas' => 10, 'animales_validas' => 12,
            'f_segundos' => 60, 'a_segundos' => 60, 's_segundos' => 60, 'animales_segundos' => 60,
            'palabras_correctas' => 60, 'colores_correctos' => 30, 'palabra_color_correctas' => 20,
            'segundos_palabras' => 45, 'segundos_colores' => 45, 'segundos_palabra_color' => 45];
        $perfiles = [];
        foreach ((new NormalizadorBateriaBolivia2015)->metodos() as $metodo => $meta) {
            $instrumento = Instrumento::create(['codigo' => 'BATERIA-TEST-'.count($perfiles), 'nombre' => 'Instrumento sintético '.count($perfiles),
                'tipo' => 'COGNITIVO', 'version' => 'VERSION-TEST', 'estado' => 'ACTIVO']);
            $selectores = $respuestas = [];
            foreach ($meta['entradas'] as $campo => $tipo) {
                $pregunta = PreguntaInstrumento::create(['cod_instrumento' => $instrumento->getKey(), 'codigo' => 'ENTRADA-'.count($selectores),
                    'enunciado' => 'Entrada sintética '.$campo, 'tipo_respuesta' => $tipo === 'LOGICO' ? 'LOGICO' : 'NUMERICO',
                    'orden' => count($selectores) + 1, 'estado' => 'ACTIVA']);
                $columna = $tipo === 'LOGICO' ? 'valor_logico' : 'valor_numero';
                $selectores[$campo] = ['cod_pregunta' => $pregunta->getKey(), 'campo' => $columna];
                $respuestas[] = ['cod_pregunta' => $pregunta->getKey(), $columna => $valores[$campo] ?? ($tipo === 'LOGICO' ? true : throw new \LogicException('Falta medición sintética explícita.'))];
            }
            $perfiles[] = ['id' => 'BATERIA-TEST-'.count($perfiles), 'cod_instrumento' => $instrumento->getKey(), 'codigo_instrumento' => $instrumento->codigo,
                'version_instrumento' => $instrumento->version, 'metodo' => $metodo, 'identidad_instrumento' => $meta['identidad'], 'selectores' => $selectores];
            $a = app(AplicarInstrumento::class)->aplicar($this->usuario, ['fecha_hora' => '2020-10-18 09:10:00', 'respuestas' => $respuestas], $instrumento, $this->residente);
            self::assertNull($a->puntaje_total);
            self::assertNull($a->clasificacion);
        }
        config(['sistema_experto.perfiles_instrumentales' => $perfiles]);
        $r = $this->captura();
        self::assertCount(6, $r['capturas']);
        foreach ($r['capturas'] as $captura) {
            self::assertSame([], $captura['motivos']);
            self::assertSame([], $captura['extraccion']['motivos_captura']);
            foreach ($captura['extraccion']['normalizacion']['componentes'] as $componente) {
                foreach ($componente['medidas'] ?? [$componente] as $medida) {
                    self::assertSame('NORMALIZADO', $medida['estado']);
                    self::assertNull($medida['clasificacion_clinica']);
                }
            }
            foreach ($captura['extraccion']['fuentes_entradas'] as $fuente) {
                self::assertSame($captura['cod_aplicacion'], $fuente['aplicacion']);
                self::assertNotEmpty($fuente['respuesta']);
                self::assertNotNull($fuente['bruto']);
            }
        }
        $perfil = app(ConsultarPerfilInstrumental::class)->consultar($this->usuario, $this->residente, CarbonImmutable::now());
        $fluidez = $perfil['dominios']['COG-LEN']['componentes']['LEN-S02'];
        self::assertCount(4, $fluidez['mediciones']);
        self::assertCount(1, array_unique(array_column($fluidez['mediciones'], 'aplicacion')));
        self::assertNull($perfil['dominios']['COG-LEN']['resultado_clinico']);
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertSee('Campos y método de evaluación')->assertSee('Intervalo percentilar aproximado')
            ->assertSee('TMT_B_SEGUNDOS')->assertSee('Menor tiempo indica mejor rendimiento')
            ->assertSee('FLUIDEZ_F')->assertSee('FLUIDEZ_ANIMALES');
        self::assertDatabaseCount('evaluaciones_expertas', 0);
    }

    public function test_fecha_original_es_obligatoria_tambien_en_post_directo(): void
    {
        $this->postJson(route('admin.instrumentos.aplicar', [$this->instrumento, $this->residente]), ['respuestas' => $this->respuestas])
            ->assertUnprocessable()->assertJsonValidationErrors('fecha_hora');
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
        self::assertDatabaseCount('respuestas_instrumento', 0);
    }

    public function test_metodo_no_aporta_identidad_aplicada_y_selector_sin_respuesta_conserva_definicion(): void
    {
        $respuestas = $this->respuestas;
        array_pop($respuestas);
        $this->aplicar(['respuestas' => $respuestas]);
        unset($this->perfil['identidad_instrumento']);
        config(['sistema_experto.perfiles_instrumentales' => [$this->perfil]]);
        $r = $this->captura()['capturas'][0];
        self::assertNull($r['extraccion']['captura']['forma']);
        self::assertSame('NO_NORMALIZABLE', $r['extraccion']['normalizacion']['componentes']['MEM-S01']['estado']);
        $v = $r['vinculos_preguntas_actuales']['administracion_estandarizada'];
        self::assertNotNull($v['pregunta_actual']);
        self::assertTrue($v['pertenece_al_instrumento']);
        self::assertFalse($v['tiene_respuesta']);
    }

    private function entradaHistorica(): array
    {
        Storage::fake('local');
        Storage::disk('local')->put('original-prueba.txt', 'Contenido original sintético para cotejo.');
        $autor = Personal::create(['cod_usuario' => User::factory()->create(['estado' => 'INACTIVO'])->getKey(), 'nombres' => 'Aplicador', 'apellido_paterno' => 'Histórico',
            'numero_documento' => 'AUTOR-TEST', 'profesion' => 'PSICOLOGIA', 'estado' => 'INACTIVO']);
        $doc = DocumentoClinico::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $autor->getKey(),
            'tipo_documento' => 'INFORME', 'titulo' => 'Protocolo sintético', 'ruta_archivo' => 'original-prueba.txt', 'formato' => 'text/plain',
            'hash_archivo' => hash('sha256', 'Contenido original sintético para cotejo.'), 'fecha_hora' => now(), 'estado' => 'VIGENTE']);
        $this->usuario->givePermissionTo('documentos_clinicos.ver');

        return ['cod_personal_original' => $autor->getKey(), 'cod_documento_clinico' => $doc->getKey(),
            'localizador_fuente' => 'página 1 / aplicación 1', 'original_cotejado' => true,
            'fecha_hora' => '2020-10-18 09:10:00', 'respuestas' => $this->respuestas];
    }

    public function test_transcripcion_concilia_sin_escritura_preserva_autor_y_reintento_es_identico(): void
    {
        $entrada = $this->entradaHistorica();
        $accion = app(AplicarInstrumento::class);
        self::assertSame('NUEVA', $accion->conciliar($this->usuario, $entrada, $this->instrumento, $this->residente)['estado']);
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
        $a = $accion->transcribir($this->usuario, $entrada, $this->instrumento, $this->residente);
        self::assertSame($entrada['cod_personal_original'], $a->cod_personal);
        self::assertSame($this->usuario->getKey(), $a->cod_usuario_registro);
        self::assertSame('2020-10-18 09:10:00', $a->fecha_hora->format('Y-m-d H:i:s'));
        self::assertTrue($a->registrado_en->equalTo(now()));
        $b = $accion->transcribir($this->usuario, $entrada, $this->instrumento, $this->residente);
        self::assertSame($a->getKey(), $b->getKey());
        self::assertDatabaseCount('aplicaciones_instrumento', 1);
        self::assertDatabaseCount('respuestas_instrumento', 11);
        self::assertSame('IDENTICA', $accion->conciliar($this->usuario, $entrada, $this->instrumento, $this->residente)['estado']);
    }

    public function test_conflicto_no_sobrescribe_y_rectificacion_conserva_original_y_auditoria(): void
    {
        $entrada = $this->entradaHistorica();
        $accion = app(AplicarInstrumento::class);
        $original = $accion->transcribir($this->usuario, $entrada, $this->instrumento, $this->residente);
        $entrada['respuestas'][0]['valor_numero'] = 0;
        try {
            $accion->transcribir($this->usuario, $entrada, $this->instrumento, $this->residente);
            self::fail('Exige corrección explícita.');
        } catch (HttpException $e) {
            self::assertSame(409, $e->getStatusCode());
        }
        self::assertDatabaseCount('aplicaciones_instrumento', 1);
        $entrada['cod_aplicacion_anterior'] = $original->getKey();
        $entrada['motivo_rectificacion'] = 'Corrección de transcripción cotejada con el original.';
        self::assertSame('RECTIFICACION', $accion->conciliar($this->usuario, $entrada, $this->instrumento, $this->residente)['estado']);
        $nueva = $accion->transcribir($this->usuario, $entrada, $this->instrumento, $this->residente);
        self::assertSame('ANULADA', $original->fresh()->estado);
        self::assertEquals(6, $original->respuestas()->where('cod_pregunta', $this->respuestas[0]['cod_pregunta'])->sole()->valor_numero);
        self::assertEquals(0, $nueva->respuestas()->where('cod_pregunta', $this->respuestas[0]['cod_pregunta'])->sole()->valor_numero);
        self::assertSame($original->getKey(), $nueva->cod_aplicacion_anterior);
        self::assertDatabaseCount('aplicaciones_instrumento', 2);
        self::assertSame(1, Activity::query()->where('event', 'aplicacion_rectificada')->count());
        self::assertSame($nueva->getKey(), $accion->transcribir($this->usuario, $entrada, $this->instrumento, $this->residente)->getKey());
    }

    public function test_transcripcion_rechaza_hash_ajeno_y_permiso_revocado_sin_escrituras(): void
    {
        $entrada = $this->entradaHistorica();
        Storage::disk('local')->put('original-prueba.txt', 'Contenido alterado.');
        try {
            app(AplicarInstrumento::class)->transcribir($this->usuario, $entrada, $this->instrumento, $this->residente);
            self::fail('Rechaza original alterado.');
        } catch (HttpException $e) {
            self::assertSame(422, $e->getStatusCode());
        }
        Role::findByName('PSICOLOGO/A')->revokePermissionTo('aplicaciones_instrumento.transcribir');
        $this->postJson(route('admin.instrumentos.transcripciones.guardar', [$this->instrumento, $this->residente]), $entrada)->assertForbidden();
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
        self::assertDatabaseCount('respuestas_instrumento', 0);
    }

    public function test_ruta_anterior_exige_respuestas_y_no_deduce_dcl_de_un_total(): void
    {
        $this->postJson(route('admin.adultos-mayores.evaluaciones.store', $this->residente),
            ['cod_tipo_eval' => $this->instrumento->getKey(), 'puntaje_total' => 5, 'fecha_eval' => '2020-10-18'])
            ->assertUnprocessable();
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
    }

    public function test_ruta_anterior_vincula_residente_y_anulacion_preserva_originales(): void
    {
        $this->post(route('admin.adultos-mayores.evaluaciones.store', $this->residente), ['cod_instrumento' => $this->instrumento->getKey(),
            'fecha_hora' => '2020-10-18 09:10:00', 'respuestas' => $this->respuestas, 'observacion' => 'Original conservado.'])
            ->assertRedirect(route('admin.salud-seguimiento.evaluaciones-geriatricas', ['adulto' => $this->residente->getKey()]));
        $a = AplicacionInstrumento::query()->sole();
        self::assertSame($this->residente->getKey(), $a->cod_residente);
        $this->delete(route('admin.adultos-mayores.evaluaciones.destroy', [$this->residente, $a]), ['motivo_anulacion' => 'Aplicación registrada en expediente equivocado.'])->assertRedirect();
        self::assertSame('ANULADA', $a->fresh()->estado);
        self::assertSame('Original conservado.', $a->fresh()->observacion);
        self::assertDatabaseCount('respuestas_instrumento', 11);
        $this->patchJson(route('admin.adultos-mayores.evaluaciones.restore', [$this->residente, $a]))->assertConflict();
        self::assertSame('ANULADA', $a->fresh()->estado);
    }

    public function test_fuente_anulada_no_se_rectifica_y_respuesta_persistida_no_se_edita(): void
    {
        $entrada = $this->entradaHistorica();
        $accion = app(AplicarInstrumento::class);
        $a = $accion->transcribir($this->usuario, $entrada, $this->instrumento, $this->residente);
        try {
            $a->respuestas->first()->update(['valor_numero' => 11]);
            self::fail('No permite editar una respuesta original.');
        } catch (ValidationException $e) {
            self::assertArrayHasKey('respuesta', $e->errors());
        }
        app(AnularAplicacionInstrumento::class)->anular($this->usuario, $this->residente, $a->getKey(), ['motivo_anulacion' => 'Original no válido para continuar.']);
        $entrada['cod_aplicacion_anterior'] = $a->getKey();
        $entrada['motivo_rectificacion'] = 'Intento de volver a completar la fuente anulada.';
        $entrada['respuestas'][0]['valor_numero'] = 0;
        try {
            $accion->transcribir($this->usuario, $entrada, $this->instrumento, $this->residente);
            self::fail('No permite reactivar una cabeza anulada.');
        } catch (HttpException $e) {
            self::assertSame(409, $e->getStatusCode());
        }
        self::assertDatabaseCount('aplicaciones_instrumento', 1);
        self::assertEquals(6, $a->respuestas()->orderBy('cod_respuesta')->first()->valor_numero);
    }

    public function test_opcion_historica_no_inventa_puntaje_y_rechaza_puntaje_incompatible(): void
    {
        $entrada = $this->entradaHistorica();
        $q = PreguntaInstrumento::create(['cod_instrumento' => $this->instrumento->getKey(), 'codigo' => 'OPC-ORIGINAL',
            'enunciado' => 'Respuesta sintética', 'tipo_respuesta' => 'OPCION', 'puntaje_maximo' => 2, 'orden' => 20, 'estado' => 'ACTIVA']);
        $o = OpcionPregunta::create(['cod_pregunta' => $q->getKey(), 'nombre' => 'Opción sintética', 'valor' => 'OBSERVADO', 'puntaje' => 2, 'orden' => 1, 'estado' => 'ACTIVO']);
        $entrada['respuestas'] = [['cod_pregunta' => $q->getKey(), 'cod_opcion' => $o->getKey(), 'puntaje' => 1]];
        try {
            app(AplicarInstrumento::class)->transcribir($this->usuario, $entrada, $this->instrumento, $this->residente);
            self::fail('No sustituye silenciosamente una puntuación.');
        } catch (ValidationException $e) {
            self::assertArrayHasKey('respuestas', $e->errors());
        }
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
        unset($entrada['respuestas'][0]['puntaje']);
        $a = app(AplicarInstrumento::class)->transcribir($this->usuario, $entrada, $this->instrumento, $this->residente);
        self::assertNull($a->respuestas->sole()->puntaje);
        self::assertNull($a->puntaje_total);
    }

    public function test_precision_tecnica_y_vinculacion_de_registro_existente_sin_duplicar(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-18 12:00:00.123456'));
        $a = $this->aplicar();
        self::assertSame('123456', $a->fresh()->registrado_en->format('u'));
        $a->update(['estado' => 'VIGENTE']);
        $entrada = $this->entradaHistorica();
        $entrada['cod_personal_original'] = $a->cod_personal;
        DocumentoClinico::query()->findOrFail($entrada['cod_documento_clinico'])->update(['cod_personal' => $a->cod_personal]);
        $entrada['cod_aplicacion_existente'] = $a->getKey();
        $accion = app(AplicarInstrumento::class);
        self::assertSame('VINCULACION', $accion->conciliar($this->usuario, $entrada, $this->instrumento, $this->residente)['estado']);
        self::assertNull($a->fresh()->cod_documento_clinico);
        $vinculada = $accion->transcribir($this->usuario, $entrada, $this->instrumento, $this->residente);
        self::assertSame($a->getKey(), $vinculada->getKey());
        self::assertSame($entrada['cod_documento_clinico'], $vinculada->cod_documento_clinico);
        self::assertSame('VIGENTE', $vinculada->estado);
        self::assertSame($a->getKey(), $accion->transcribir($this->usuario, $entrada, $this->instrumento, $this->residente)->getKey());
        self::assertDatabaseCount('aplicaciones_instrumento', 1);
        self::assertDatabaseCount('respuestas_instrumento', 11);
    }

    public function test_modal_concilia_transcripcion_y_muestra_procedencia_del_original(): void
    {
        $entrada = $this->entradaHistorica();
        $c = Livewire::test(EvaluacionGeriatricaModal::class, ['cod_residente' => $this->residente->getKey()])->call('abrir', $this->residente->getKey())
            ->set('cod_instrumento', $this->instrumento->getKey())->set('transcripcion', true)
            ->assertSet('fecha_eval', '')->assertSet('hora_eval', '')
            ->set('cod_documento_clinico', $entrada['cod_documento_clinico'])->set('cod_personal_original', $entrada['cod_personal_original'])
            ->set('localizador_fuente', $entrada['localizador_fuente'])->set('original_cotejado', true)
            ->set('fecha_eval', '2020-10-18')->set('hora_eval', '09:10:00');
        foreach ($this->respuestas as $respuesta) {
            $valor = $respuesta['valor_numero'] ?? ($respuesta['valor_logico'] ? '1' : '0');
            $c->set('valores.'.$respuesta['cod_pregunta'], $valor);
        }
        $c->call('revisar')->assertSet('estadoConciliacion', 'NUEVA')->assertSet('confirmando', true);
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
        $c->call('guardar')->assertHasNoErrors()->assertSet('mostrar', false);
        self::assertDatabaseCount('aplicaciones_instrumento', 1);
        $r = $this->captura()['capturas'][0];
        self::assertSame('VERIFICADO', $r['procedencia']['estado_documento']);
        self::assertSame($entrada['cod_personal_original'], $r['procedencia']['cod_personal_aplicador']);
        self::assertSame($this->usuario->getKey(), $r['procedencia']['cod_usuario_registro']);
    }

    public function test_fallo_de_auditoria_revierte_correccion_y_anulacion_del_original(): void
    {
        $entrada = $this->entradaHistorica();
        $accion = app(AplicarInstrumento::class);
        $a = $accion->transcribir($this->usuario, $entrada, $this->instrumento, $this->residente);
        $entrada['respuestas'][0]['valor_numero'] = 0;
        $entrada['cod_aplicacion_anterior'] = $a->getKey();
        $entrada['motivo_rectificacion'] = 'Corrección sintética para verificar atomicidad.';
        DB::connection()->beforeExecuting(static function (string $sql): void {
            if (str_starts_with(strtolower($sql), 'insert into "activity_log"')) {
                throw new \RuntimeException('Fallo sintético de auditoría.');
            }
        });
        try {
            $accion->transcribir($this->usuario, $entrada, $this->instrumento, $this->residente);
            self::fail('Fallo de auditoría debe revertir la corrección.');
        } catch (\RuntimeException $e) {
            self::assertSame('Fallo sintético de auditoría.', $e->getMessage());
        }
        self::assertSame('COMPLETA', $a->fresh()->estado);
        self::assertDatabaseCount('aplicaciones_instrumento', 1);
        self::assertDatabaseCount('respuestas_instrumento', 11);
    }

    public function test_migracion_revierte_solo_sin_metadatos_y_conserva_historia_y_referencias(): void
    {
        $a = $this->aplicar();
        $m = require database_path('migrations/2026_10_19_010000_add_procedencia_aplicaciones_instrumento.php');
        try {
            $m->down();
            self::fail('No retira metadatos ya conservados.');
        } catch (\LogicException $e) {
            self::assertStringContainsString('No se retira procedencia', $e->getMessage());
        }
        // Simula una fila anterior a la ampliación, exclusivamente en esta BDD desechable.
        DB::table('aplicaciones_instrumento')->where('cod_aplicacion', $a->getKey())->update(['cod_usuario_registro' => null, 'registrado_en' => null]);
        $respuestas = DB::table('respuestas_instrumento')->orderBy('cod_respuesta')->get()->map(fn ($fila) => (array) $fila)->all();
        $m->down();
        self::assertFalse(Schema::hasColumn('aplicaciones_instrumento', 'cod_documento_clinico'));
        self::assertSame($respuestas, DB::table('respuestas_instrumento')->orderBy('cod_respuesta')->get()->map(fn ($fila) => (array) $fila)->all());
        $m->up();
        self::assertNull(AplicacionInstrumento::query()->findOrFail($a->getKey())->cod_usuario_registro);
        self::assertDatabaseCount('aplicaciones_instrumento', 1);
        self::assertDatabaseCount('respuestas_instrumento', 11);
        if (DB::getDriverName() === 'sqlite') {
            self::assertSame([], DB::select('PRAGMA foreign_key_check'));
        }
    }

    public function test_permiso_de_transcripcion_no_concede_descarga_general_y_superadmin_no_transcribe(): void
    {
        $entrada = $this->entradaHistorica();
        $this->usuario->revokePermissionTo('documentos_clinicos.ver');
        $fuentes = app(PrepararCapturaInstrumental::class)
            ->fuentesTranscripcion($this->usuario, $this->residente->getKey());
        self::assertSame(['cod_documento_clinico', 'titulo', 'fecha_hora'], array_keys($fuentes->sole()->getAttributes()));
        $this->get(route('admin.documentos-clinicos.descargar', $entrada['cod_documento_clinico']))->assertForbidden();
        $this->postJson(route('admin.instrumentos.transcripciones.conciliar', [$this->instrumento, $this->residente]), $entrada)
            ->assertOk()->assertJsonPath('estado', 'NUEVA');
        $super = User::factory()->create(['estado' => 'ACTIVO']);
        $super->assignRole('SUPERADMINISTRADOR');
        self::assertFalse($super->checkPermissionTo('aplicaciones_instrumento.transcribir', 'web'));
        $this->actingAs($super)->postJson(route('admin.instrumentos.transcripciones.guardar', [$this->instrumento, $this->residente]), $entrada)->assertForbidden();
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
    }

    public function test_vinculacion_no_declara_identicos_puntajes_distintos_del_registro_previo(): void
    {
        $a = $this->aplicar();
        $entrada = $this->entradaHistorica();
        $entrada['cod_personal_original'] = $a->cod_personal;
        DocumentoClinico::query()->findOrFail($entrada['cod_documento_clinico'])->update(['cod_personal' => $a->cod_personal]);
        $entrada['cod_aplicacion_existente'] = $a->getKey();
        DB::table('aplicaciones_instrumento')->where('cod_aplicacion', $a->getKey())->update(['puntaje_total' => 0]);
        try {
            app(AplicarInstrumento::class)->conciliar($this->usuario, $entrada, $this->instrumento, $this->residente);
            self::fail('No declara identidad de puntajes NULL y cero.');
        } catch (HttpException $e) {
            self::assertSame(409, $e->getStatusCode());
        }
        self::assertNull($a->fresh()->cod_documento_clinico);
        self::assertDatabaseCount('aplicaciones_instrumento', 1);
    }

    public static function originalesIncompatibles(): array
    {
        return [['residente'], ['autor'], ['archivo'], ['cotejo']];
    }

    #[DataProvider('originalesIncompatibles')]
    public function test_original_incompatible_no_produce_cabecera_respuestas_ni_exito(string $incompatible): void
    {
        $entrada = $this->entradaHistorica();
        match ($incompatible) {
            'residente' => DocumentoClinico::query()->findOrFail($entrada['cod_documento_clinico'])->update(['cod_residente' => Residente::factory()->create()->getKey()]),
            'autor' => $entrada['cod_personal_original'] = $this->usuario->personal()->sole()->getKey(),
            'archivo' => Storage::disk('local')->delete('original-prueba.txt'),
            'cotejo' => $entrada['original_cotejado'] = false,
        };
        $this->postJson(route('admin.instrumentos.transcripciones.guardar', [$this->instrumento, $this->residente]), $entrada)->assertUnprocessable();
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
        self::assertDatabaseCount('respuestas_instrumento', 0);
        self::assertSame(0, Activity::query()->where('event', 'aplicacion_registrada')->count());
    }
}
