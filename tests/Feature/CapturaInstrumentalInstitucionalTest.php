<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AplicacionInstrumento;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Instrumento;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\PreguntaInstrumento;
use App\Models\Residente;
use App\Models\RespuestaInstrumento;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\CapturasInstrumentalesResidente;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarPerfilInstrumental;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResultadosExpertoResidente;
use App\Modulos\Valoraciones\Aplicacion\Acciones\AplicarInstrumento;
use Carbon\CarbonImmutable;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use MotorExperto\dominio\instrumentos\NormalizadorBateriaBolivia2015;
use MotorExperto\dominio\instrumentos\NormalizadorHvltBolivia2015 as Hvlt;
use PHPUnit\Framework\Attributes\DataProvider;
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
            'codigo_instrumento' => $this->instrumento->codigo, 'version_instrumento' => 'TEST-1', 'metodo' => Hvlt::METODO, 'selectores' => $selectores];
    }

    private function aplicar(array $cambios = []): AplicacionInstrumento
    {
        return app(AplicarInstrumento::class)->aplicar($this->usuario, array_replace(['fecha_hora' => '2020-10-18 09:10:00', 'respuestas' => $this->respuestas], $cambios), $this->instrumento, $this->residente);
    }

    private function captura(): array
    {
        return app(CapturasInstrumentalesResidente::class)->consultar($this->usuario, $this->residente, CarbonImmutable::now());
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
                'version_instrumento' => $instrumento->version, 'metodo' => $metodo, 'selectores' => $selectores];
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
}
