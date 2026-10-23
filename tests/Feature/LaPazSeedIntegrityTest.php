<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\LaPaz\ConservacionDatos;
use Database\Seeders\LaPaz\HistoriaResidencialSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class LaPazSeedIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['seeders.lapaz.base_autorizada' => DB::connection()->getDatabaseName()]);
    }

    public function test_detecta_cambios_en_filas_preexistentes(): void
    {
        $usuario = User::factory()->create(['correo' => 'conservar@example.invalid']);
        $control = new ConservacionDatos;
        $control->capturar();
        $usuario->update(['estado' => 'INACTIVO']);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Una fila preexistente cambió en usuarios.');
        $control->comprobar();
    }

    public function test_carga_relacionada_conserva_datos_ajenos_y_no_precarga_experto(): void
    {
        Storage::fake('local');
        $this->seed(RolesAndPermissionsSeeder::class);
        $previa = User::factory()->create(['correo' => 'cuenta.conservada@example.invalid']);
        $original = $previa->getRawOriginal();
        // Una instalación poblada ya puede tener estos catálogos con otras PK.
        DB::table('turnos')->insert([
            ['cod_turno' => 'TUR_PRE_0', 'nombre' => 'Guardia de madrugada y mañana', 'hora_inicio' => '00:00:00', 'hora_cierre' => '12:00:00', 'orden' => 1, 'estado' => 'ACTIVO', 'observacion' => 'Texto previo que se conserva.'],
            ['cod_turno' => 'TUR_PRE_1', 'nombre' => 'Guardia de tarde y noche', 'hora_inicio' => '12:00:00', 'hora_cierre' => '00:00:00', 'orden' => 2, 'estado' => 'ACTIVO', 'observacion' => null],
        ]);
        DB::table('tipos_estudio_clinico')->insert(['cod_tipo_estudio' => 'TEC_PRE', 'nombre' => 'Glucemia de laboratorio',
            'categoria' => 'LABORATORIO', 'descripcion' => 'Descripción anterior conservada.', 'requiere_componentes' => true, 'requiere_informe' => true, 'estado' => 'ACTIVO']);
        DB::table('secuencias_codigos')->insert(['entidad' => 'residentes', 'ultimo_valor' => 25]);
        $conservacion = new ConservacionDatos;
        $conservacion->capturar();
        $this->seed(HistoriaResidencialSeeder::class);
        $this->assertGreaterThan(0, $conservacion->comprobar());
        $this->assertDatabaseCount('turnos', 2);
        $this->assertDatabaseCount('tipos_estudio_clinico', 1);
        $this->assertFalse(DB::table('jornadas')->whereNotIn('cod_turno', ['TUR_PRE_0', 'TUR_PRE_1'])->exists());
        $this->assertFalse(DB::table('programaciones_cuidado')->whereNotIn('cod_turno', ['TUR_PRE_0', 'TUR_PRE_1'])->exists());
        $this->assertFalse(DB::table('estudios_clinicos')->where('cod_tipo_estudio', '<>', 'TEC_PRE')->exists());
        $this->assertDatabaseHas('secuencias_codigos', ['entidad' => 'residentes', 'ultimo_valor' => 73]);
        $this->assertDatabaseCount('residentes', 48);
        $this->assertDatabaseCount('camas', 48);
        $this->assertSame(48, DB::table('residentes')->distinct()->count('fecha_nacimiento'));
        $this->assertGreaterThan(10000, DB::table('signos_vitales')->count());
        $this->assertSame('2025-01-06 08:00:00', DB::table('signos_vitales')->min('fecha_hora'));
        $this->assertFalse(DB::table('registros_hidratacion')->where('fecha_hora', '>', HistoriaResidencialSeeder::CUTOFF)->exists());
        $this->assertSame($original, $previa->fresh()->getRawOriginal());
        $this->assertDatabaseCount('evaluaciones_expertas', 0);
        $this->assertDatabaseCount('reglas_expertas', 0);
        self::assertSame(0, \App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\BloquesExpertos::consulta('resultados_criterio')->count());
        $this->assertFalse(DB::table('aplicaciones_instrumento')->whereNotNull('clasificacion')->exists());
        $this->assertFalse(DB::table('aplicaciones_instrumento')->whereNotNull('interpretacion')->exists());
        $this->assertFalse(User::findOrFail('LP_USU_N01')->can('prescripciones.crear'));
        $this->assertTrue(User::findOrFail('LP_USU_MED')->can('prescripciones.crear'));
        $this->assertSame(48, DB::table('residentes_contactos')->distinct()->count('cod_residente'));
        $password = User::findOrFail('LP_USU_MED')->contrasena;
        $conteos = [DB::table('usuarios')->count(), DB::table('registros_hidratacion')->count(), DB::table('activity_log')->count()];
        $this->seed(HistoriaResidencialSeeder::class);
        $this->assertSame($conteos, [DB::table('usuarios')->count(), DB::table('registros_hidratacion')->count(), DB::table('activity_log')->count()]);
        $this->assertSame($password, User::findOrFail('LP_USU_MED')->contrasena);
        // Una sola fila ausente debe detectarse aunque la fecha siga cubierta por otras cuatro.
        DB::table('registros_hidratacion')->where('cod_hidratacion', 'LP_HI_01_2501060')->delete();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Número de registros incompleto en registros_hidratacion.');
        $this->seed(HistoriaResidencialSeeder::class);
    }

    public function test_rechaza_produccion_antes_de_escribir(): void
    {
        $this->app->instance('env', 'production');
        try {
            app(HistoriaResidencialSeeder::class)->run();
            $this->fail('Debe rechazar el entorno.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('prohibido', $e->getMessage());
            $this->assertDatabaseCount('usuarios', 0);
            $this->assertDatabaseCount('residentes', 0);
        }
    }

    public function test_rechaza_avance_de_contador_sin_codigos_nuevos(): void
    {
        DB::table('secuencias_codigos')->insert(['entidad' => 'residentes', 'ultimo_valor' => 25]);
        $control = new ConservacionDatos;
        $control->capturar();
        DB::table('secuencias_codigos')->where('entidad', 'residentes')->update(['ultimo_valor' => 26]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Avance de secuencia sin registros nuevos');
        $control->comprobar();
    }

    public function test_rechaza_retroceso_de_contador(): void
    {
        DB::table('secuencias_codigos')->insert(['entidad' => 'residentes', 'ultimo_valor' => 25]);
        $control = new ConservacionDatos;
        $control->capturar();
        DB::table('secuencias_codigos')->where('entidad', 'residentes')->update(['ultimo_valor' => 24]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cambio no permitido en una secuencia');
        $control->comprobar();
    }

    public function test_turno_de_mismo_nombre_con_horario_distinto_rechaza_y_revierte(): void
    {
        Storage::fake('local');
        $this->seed(RolesAndPermissionsSeeder::class);
        DB::table('turnos')->insert(['cod_turno' => 'TUR_INCOMPATIBLE', 'nombre' => 'Guardia de madrugada y mañana',
            'hora_inicio' => '07:00:00', 'hora_cierre' => '15:00:00', 'orden' => 1, 'estado' => 'ACTIVO']);
        try {
            $this->seed(HistoriaResidencialSeeder::class);
            $this->fail('No debe elegir un turno por parecido ni modificarlo.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('turno existente no coincide', $e->getMessage());
            $this->assertDatabaseHas('turnos', ['cod_turno' => 'TUR_INCOMPATIBLE', 'hora_inicio' => '07:00:00']);
            $this->assertDatabaseCount('residentes', 0);
            $this->assertDatabaseCount('usuarios', 0);
        }
    }

    public function test_rechaza_nombre_de_base_no_autorizado(): void
    {
        $this->app->instance('env', 'local');
        config(['seeders.lapaz.base_autorizada' => 'otra_base']);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('nombre exacto');
        app(HistoriaResidencialSeeder::class)->run();
    }

    public function test_fallo_de_archivo_revierte_datos_y_no_finge_exito(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::shouldReceive('disk')->with('local')->andReturnSelf();
        Storage::shouldReceive('exists')->once()->andReturn(false);
        Storage::shouldReceive('put')->once()->andReturn(false);
        Storage::shouldReceive('delete')->once()->andReturn(true);
        try {
            $this->seed(HistoriaResidencialSeeder::class);
            $this->fail('Debe revertir la carga.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('archivo privado', $e->getMessage());
            $this->assertDatabaseCount('residentes', 0);
            $this->assertDatabaseCount('preadmisiones', 0);
            $this->assertDatabaseCount('usuarios', 0);
            $this->assertDatabaseCount('medicamentos', 0);
        }
    }

    public function test_seeder_normal_no_invoca_la_carga_masiva(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->assertDatabaseCount('residentes', 0);
        $this->assertDatabaseCount('signos_vitales', 0);
        $this->assertFalse(DB::table('usuarios')->where('correo', 'like', '%@lapaz.example.invalid')->exists());
    }
}
