<?php

namespace Tests\Feature;

use App\Modulos\Cuidados\Infraestructura\Persistencia\RegistrosConsumo;
use App\Modulos\Cuidados\Aplicacion\Consultas\MiTurnoService;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\Historical2026Seeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class Historical2026SeedIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // La carga prolongada no comparte archivos falsos con otras suites de QA.
        $this->app->useStoragePath(storage_path('framework/testing/historical-'.bin2hex(random_bytes(6))));
    }

    public function test_complete_histories_daily_coverage_roles_and_idempotency(): void
    {
        Storage::fake('local');
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(Historical2026Seeder::class);
        $this->assertDatabaseCount('residentes', 30);
        $this->assertDatabaseCount('camas', 30);
        $this->assertDatabaseCount('medicamentos', 20);
        $this->assertDatabaseCount('objetivos_signos_vitales', 2);
        $this->assertSame(30, DB::table('residentes')->distinct()->count('fecha_nacimiento'));
        $this->assertSame(30, DB::table('residentes')->where('estado', 'ADMITIDO')->count());
        $cases = require base_path('database/seeders/data/historical2026.php');
        $expectedDays = array_sum(array_map(fn ($c) => (int) Carbon::parse($c[5])->diffInDays(Carbon::parse('2026-10-08'), true) + 1, $cases));
        $this->assertSame($expectedDays + 1, \App\Models\SignoVital::count());
        $this->assertSame($expectedDays - 30, \App\Models\RegistroSueno::count());
        $this->assertSame($expectedDays, DB::table('atenciones')->where('tipo_atencion', 'SEGUIMIENTO_DIARIO')->count());
        $this->assertFalse(\App\Modulos\Cuidados\Infraestructura\Persistencia\RegistrosConsumo::consulta('signos_vitales')->where('fecha_hora', '>', Historical2026Seeder::CUTOFF)->exists());
        $this->assertSame(10, DB::table('preguntas_instrumento')->where('estado', 'ACTIVA')->count());
        $this->assertFalse(RegistrosConsumo::consulta('valoraciones_dolor')->where('intensidad', '>', 0)->where('respuesta', 'No refiere dolor.')->exists());
        $this->assertSame([8, 5, 3], RegistrosConsumo::consulta('valoraciones_dolor')->where('cod_residente', DB::table('residentes')->where('numero_documento', '99000002')->value('cod_residente'))->whereIn('fecha_hora', [Historical2026Seeder::REFERENCE_DATE.' 07:20:00', Historical2026Seeder::REFERENCE_DATE.' 08:20:00', Historical2026Seeder::REFERENCE_DATE.' 09:20:00'])->orderBy('fecha_hora')->pluck('intensidad')->map(fn ($value) => (int) $value)->all());
        $this->assertSame(2, RegistrosConsumo::consulta('valoraciones_dolor')->where('cod_valoracion_origen', RegistrosConsumo::consulta('valoraciones_dolor')->where('cod_residente', DB::table('residentes')->where('numero_documento', '99000002')->value('cod_residente'))->whereIn('fecha_hora', [Historical2026Seeder::REFERENCE_DATE.' 07:20:00', Historical2026Seeder::REFERENCE_DATE.' 08:20:00', Historical2026Seeder::REFERENCE_DATE.' 09:20:00'])->whereNull('cod_valoracion_origen')->sole(['cod_valoracion_dolor'])->cod_valoracion_dolor)->count());
        $this->assertSame([4, 4], RegistrosConsumo::consulta('valoraciones_dolor')->where('cod_residente', DB::table('residentes')->where('numero_documento', '99000003')->value('cod_residente'))->whereIn('fecha_hora', [Historical2026Seeder::REFERENCE_DATE.' 07:20:00', Historical2026Seeder::REFERENCE_DATE.' 08:20:00', Historical2026Seeder::REFERENCE_DATE.' 09:20:00'])->orderBy('fecha_hora')->pluck('intensidad')->map(fn ($value) => (int) $value)->all());
        $this->assertNull(RegistrosConsumo::consulta('valoraciones_dolor')->where('cod_residente', DB::table('residentes')->where('numero_documento', '99000001')->value('cod_residente'))->whereIn('fecha_hora', [Historical2026Seeder::REFERENCE_DATE.' 07:20:00', Historical2026Seeder::REFERENCE_DATE.' 08:20:00', Historical2026Seeder::REFERENCE_DATE.' 09:20:00'])->whereNull('cod_valoracion_origen')->value('respuesta'));
        $this->assertFalse(DB::table('registros_hidratacion')->whereNotIn('tolerancia', ['ADECUADA', 'PARCIAL', 'INSUFICIENTE', 'RECHAZADA'])->exists());
        $this->assertFalse(DB::table('registros_observaciones_cuidado')->where('tipo_evento', 'MOVILIDAD')->whereNotIn('traslado', ['INDEPENDIENTE', 'SUPERVISION', 'AYUDA_UNA_PERSONA', 'AYUDA_DOS_PERSONAS', 'GRUA'])->exists());
        $this->assertFalse(User::where('correo', 'enfermeria.01@remembermind.test')->sole()->can('prescripciones.crear'));
        $this->assertTrue(User::where('correo', 'med@remembermind.test')->sole()->can('prescripciones.crear'));
        $nurse = RegistrosConsumo::consulta('asignaciones_residente_jornada')->where('cod_jornada', DB::table('jornadas as j')->join('turnos as t', 't.cod_turno', '=', 'j.cod_turno')->where('j.fecha_jornada', '2026-10-08')->where('t.hora_inicio', '00:00:00')->where('t.hora_cierre', '12:00:00')->sole(['j.cod_jornada'])->cod_jornada)->first()->cod_personal;
        $this->travelTo(Carbon::parse(Historical2026Seeder::CUTOFF));
        $dashboard = app(MiTurnoService::class)->obtenerDatosDashboard(\App\Models\Personal::findOrFail($nurse)->usuario);
        $this->assertSame('EN_TURNO', $dashboard['modo']);
        $this->assertNotEmpty($dashboard['residentes']);
        $this->travelBack();
        $before = \App\Modulos\Cuidados\Infraestructura\Persistencia\RegistrosConsumo::consulta('signos_vitales')->orderBy('cod_registro_medicion')->get()->toJson();
        $password = User::where('correo', 'med@remembermind.test')->sole()->contrasena;
        $this->seed(Historical2026Seeder::class);
        $this->assertSame($before, \App\Modulos\Cuidados\Infraestructura\Persistencia\RegistrosConsumo::consulta('signos_vitales')->orderBy('cod_registro_medicion')->get()->toJson());
        $this->assertSame($password, User::where('correo', 'med@remembermind.test')->sole()->contrasena);
        // Detecta corrupción en una familia antes omitida; jamás declara verificada una carga incompleta.
        \App\Modulos\Cuidados\Infraestructura\Persistencia\RegistrosConsumo::consulta('registros_sueno')->where('cod_observacion_cuidado', \App\Modulos\Cuidados\Infraestructura\Persistencia\RegistrosConsumo::consulta('registros_sueno')->value('cod_observacion_cuidado'))->delete();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Sueño, cognición o seguimiento diario incompletos.');
        $this->seed(Historical2026Seeder::class);
    }

    public function test_rejects_production_before_any_mutation(): void
    {
        $this->app->instance('env', 'production');
        try {
            app(Historical2026Seeder::class)->run();
            $this->fail('No se debe admitir una carga sintética en producción.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('prohibido', $e->getMessage());
            $this->assertDatabaseCount('residentes', 0);
            $this->assertDatabaseCount('medicamentos', 0);
        }
    }

    public function test_file_failure_rolls_back_admissions_accounts_and_catalog(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::shouldReceive('disk')->with('local')->andReturnSelf();
        Storage::shouldReceive('put')->once()->andReturn(false);
        try {
            $this->seed(Historical2026Seeder::class);
            $this->fail('Un documento no guardado no debe confirmar la carga.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('archivo privado', $e->getMessage());
            $this->assertDatabaseCount('residentes', 0);
            $this->assertDatabaseCount('preadmisiones', 0);
            $this->assertDatabaseCount('usuarios', 0);
            $this->assertDatabaseCount('medicamentos', 0);
        }
    }
}
