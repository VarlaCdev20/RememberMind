<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Instrumento;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\PreguntaInstrumento;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarConocimientoInstitucional;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarConocimientoPetersen;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\CapturasInstrumentalesResidente;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\PrepararEvaluacionInstitucional;
use App\Modulos\ApoyoDecision\Aplicacion\Servicios\PerfilesInstrumentalesIniciales;
use App\Modulos\Valoraciones\Aplicacion\Acciones\AplicarInstrumento;
use App\Modulos\Valoraciones\Aplicacion\Consultas\PrepararCapturaInstrumental;
use App\Modulos\Valoraciones\Presentacion\Livewire\Funcionalidades\EvaluacionGeriatricaModal;
use Carbon\CarbonImmutable;
use Database\Seeders\CircuitoInstrumentalInicialSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use MotorExperto\dominio\instrumentos\CircuitoInstrumentalInicial as C;
use MotorExperto\soporte\conservacion\InstantaneaEvaluacionInstitucional;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\EsquemaSinTransaccion;
use Tests\TestCase;
use Tests\Unit\CircuitoInstrumentalInicialTest as Casos;

final class CircuitoInstrumentalInicialTest extends TestCase
{
    use EsquemaSinTransaccion;

    private User $usuario;
    private Residente $residente;

    protected function setUp(): void
    {
        parent::setUp();
        config(['remembermind.superadmin_clinical_write' => false]);
        $this->travelTo(CarbonImmutable::parse('2026-11-04 12:00:00'));
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(CircuitoInstrumentalInicialSeeder::class);
        $this->usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $this->usuario->assignRole('PSICOLOGO/A');
        $personal = Personal::create(['cod_usuario' => $this->usuario->getKey(), 'nombres' => 'Profesional', 'apellido_paterno' => 'Sintético',
            'numero_documento' => 'INICIAL-TEST', 'profesion' => 'PSICOLOGIA', 'estado' => 'ACTIVO']);
        $area = Area::create(['nombre' => 'Área sintética', 'estado' => 'ACTIVA']);
        $turno = Turno::create(['nombre' => 'Turno sintético', 'hora_inicio' => '00:00', 'hora_cierre' => '23:59', 'orden' => 1, 'estado' => 'ACTIVO']);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        AsignacionPersonal::create(['cod_personal' => $personal->getKey(), 'cod_jornada' => $jornada->getKey(),
            'cod_area' => $area->getKey(), 'fecha_asignacion' => now(), 'estado' => 'ACTIVA']);
        $this->residente = Residente::factory()->create(['estado' => 'ADMITIDO', 'fecha_nacimiento' => '1945-01-01']);
        $this->actingAs($this->usuario);
    }

    private function instrumento(string $metodo): Instrumento
    {
        return Instrumento::query()->where('codigo', (new C)->definiciones()[$metodo]['codigo'])->sole();
    }

    private function entrada(string $metodo, array $cambios = []): array
    {
        $c = array_replace(Casos::captura($metodo, true), $cambios);
        $respuestas = [];
        foreach ($this->instrumento($metodo)->preguntas as $p) {
            if (($c[$p->codigo] ?? null) !== null) {
                $respuestas[] = ['cod_pregunta' => $p->getKey(), $p->tipo_respuesta === 'LOGICO' ? 'valor_logico' : 'valor_numero' => $c[$p->codigo]];
            }
        }

        return ['fecha_hora' => '2026-11-04 11:00:00', 'respuestas' => $respuestas];
    }

    public static function metodos(): array
    {
        return Casos::metodos();
    }

    #[DataProvider('metodos')]
    public function test_catalogo_aplicacion_extraccion_y_replay_sin_convertir_totales_en_dominios(string $metodo): void
    {
        $i = $this->instrumento($metodo);
        $a = app(AplicarInstrumento::class)->aplicar($this->usuario, $this->entrada($metodo), $i, $this->residente);
        self::assertSame('30.00', $a->puntaje_total);
        self::assertSame('30.00', $a->puntaje_maximo);
        self::assertSame($this->usuario->personal->getKey(), $a->cod_personal);
        self::assertSame($this->usuario->getKey(), $a->cod_usuario_registro);
        $capturas = app(CapturasInstrumentalesResidente::class)->consultar($this->usuario, $this->residente, CarbonImmutable::now());
        $e = $capturas['capturas'][0]['extraccion'];
        self::assertSame($metodo, $e['metodo']);
        self::assertSame(30, $e['normalizacion']['resumen_inicial']['total']);
        self::assertSame([], $e['normalizacion']['componentes']);
        app(RegistrarConocimientoInstitucional::class)->ejecutar($this->usuario);
        app(RegistrarConocimientoPetersen::class)->ejecutar($this->usuario);
        $dto = app(PrepararEvaluacionInstitucional::class)->preparar($this->usuario, $this->residente, CarbonImmutable::now());
        $salida = $dto->contenido['petersen']['contenido']['salida'];
        self::assertNull($salida['patron']);
        self::assertSame('U', $salida['requisitos']['C02']['estado']);
        self::assertSame('U', $salida['requisitos']['C03']['estado']);
        self::assertSame(['U'], array_values(array_unique(array_column($salida['dominios'], 'estado'))));
        // Cambiar configuración no reconstruye los vínculos adquiridos ni las respuestas originales.
        config(['sistema_experto.perfiles_instrumentales' => [['id' => 'OTRO-PERFIL']]]);
        self::assertSame($dto->contenido, InstantaneaEvaluacionInstitucional::verificar($dto->contenido, $dto->huella)->contenido);
        self::assertDatabaseCount('evaluaciones_expertas', 0);
    }

    public function test_instalacion_idempotente_y_catalogo_duplicado_se_rechaza_sin_sobrescribir(): void
    {
        $antes = DB::table('preguntas_instrumento')->count();
        $this->seed(CircuitoInstrumentalInicialSeeder::class);
        self::assertSame($antes, DB::table('preguntas_instrumento')->count());
        self::assertCount(3, app(PerfilesInstrumentalesIniciales::class)->estado());
        $estado = (new \App\Modulos\ApoyoDecision\Aplicacion\Consultas\PreparacionCircuitoInstrumental)->resumir([], (new \MotorExperto\dominio\instrumentos\RegistroMetodosInstrumentales)->metodos(), app(PerfilesInstrumentalesIniciales::class)->estado());
        self::assertSame(['CAPTURA_Y_PUNTUACION_ACTIVAS'], array_values(array_unique(array_column($estado['familias'], 'estado_preparacion'))));
        $i = $this->instrumento(C::MOCA);
        $p = $i->preguntas->first()->getAttributes();
        unset($p['cod_pregunta']);
        PreguntaInstrumento::create($p);
        try {
            $this->seed(CircuitoInstrumentalInicialSeeder::class);
            self::fail('No se puede elegir una pregunta duplicada como selector.');
        } catch (DomainException $ex) {
            self::assertStringContainsString('catálogo', $ex->getMessage());
        }
        self::assertSame($antes + 1, DB::table('preguntas_instrumento')->count());
    }

    public static function accesosRevocados(): array
    {
        return [['cuenta'], ['permiso'], ['superadmin']];
    }

    #[DataProvider('accesosRevocados')]
    public function test_no_hay_escritura_al_revocar_cuenta_permiso_o_usar_superadmin(string $tipo): void
    {
        if ($tipo === 'cuenta') {
            $this->usuario->update(['estado' => 'INACTIVO']);
        } elseif ($tipo === 'permiso') {
            $this->usuario->roles->first()->revokePermissionTo('aplicaciones_instrumento.crear');
        } else {
            $this->usuario->syncRoles(['SUPERADMINISTRADOR']);
        }
        try {
            app(AplicarInstrumento::class)->aplicar($this->usuario, $this->entrada(C::MOCA), $this->instrumento(C::MOCA), $this->residente);
            self::fail('El catálogo no concede escritura clínica.');
        } catch (\Illuminate\Auth\Access\AuthorizationException|\Symfony\Component\HttpKernel\Exception\HttpException $ex) {
            self::assertTrue($ex instanceof \Illuminate\Auth\Access\AuthorizationException || $ex->getStatusCode() === 403);
        }
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
        self::assertDatabaseCount('respuestas_instrumento', 0);
    }

    public function test_ausencia_na_y_cero_se_conservan_sin_total_falso(): void
    {
        foreach ([['actividad_1' => 8], ['actividad_1' => null], ['informante_participo' => false]] as $c) {
            $a = app(AplicarInstrumento::class)->aplicar($this->usuario, $this->entrada(C::FAQ, $c), $this->instrumento(C::FAQ), $this->residente);
            self::assertNull($a->puntaje_total);
        }
        $a = app(AplicarInstrumento::class)->aplicar($this->usuario, $this->entrada(C::FAQ,
            array_fill_keys(array_map(static fn ($n) => 'actividad_'.$n, range(1, 10)), 0)), $this->instrumento(C::FAQ), $this->residente);
        self::assertSame('0.00', $a->puntaje_total);
    }

    public static function adulteraciones(): array
    {
        return [['valor_numero', 1.5], ['valor_numero', 7], ['puntaje', 1]];
    }

    #[DataProvider('adulteraciones')]
    public function test_manipulacion_de_puntos_no_guarda_aplicacion_ni_respuestas(string $campo, mixed $valor): void
    {
        $e = $this->entrada(C::MOCA);
        $e['respuestas'][0][$campo] = $valor;
        try {
            app(AplicarInstrumento::class)->aplicar($this->usuario, $e, $this->instrumento(C::MOCA), $this->residente);
            self::fail('Una puntuación incoherente no se guarda.');
        } catch (ValidationException $ex) {
            self::assertNotEmpty($ex->errors());
        }
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
        self::assertDatabaseCount('respuestas_instrumento', 0);
    }

    public function test_formulario_compartido_muestra_edicion_y_rangos_revisa_y_guarda(): void
    {
        $i = $this->instrumento(C::FAQ);
        $catalogo = app(PrepararCapturaInstrumental::class)->catalogo($this->usuario, $this->residente->getKey(), $i->getKey());
        self::assertSame('VINCULADO', $catalogo['vinculo_metodo']['estado']);
        $panel = Livewire::test(EvaluacionGeriatricaModal::class, ['cod_residente' => $this->residente->getKey()])
            ->call('abrir', $this->residente->getKey())->set('cod_instrumento', $i->getKey())
            ->assertSee('FAQ/Pfeffer B7S')->assertSee('No aplica')->assertSee('Puntuación incompleta');
        foreach ($this->entrada(C::FAQ)['respuestas'] as $r) {
            $panel->set('valores.'.$r['cod_pregunta'], $r['valor_logico'] ?? $r['valor_numero']);
        }
        $panel->assertSee('30 de 30 puntos')->call('revisar')->assertHasNoErrors()->assertSet('confirmando', true)
            ->call('guardar')->assertHasNoErrors();
        self::assertDatabaseCount('aplicaciones_instrumento', 1);
        $panel->call('guardar');
        self::assertDatabaseCount('aplicaciones_instrumento', 1);
    }
}
