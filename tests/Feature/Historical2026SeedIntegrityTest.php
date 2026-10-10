<?php

namespace Tests\Feature;

use App\Backend\Modulos\Enfermeria\Servicios\MiTurnoService;
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
        $this->assertDatabaseCount('signos_vitales', $expectedDays + 1);
        $this->assertDatabaseCount('registros_sueno', $expectedDays - 30);
        $this->assertSame($expectedDays, DB::table('atenciones')->where('tipo_atencion', 'SEGUIMIENTO_DIARIO')->count());
        $this->assertFalse(DB::table('signos_vitales')->where('fecha_hora', '>', Historical2026Seeder::CUTOFF)->exists());
        $this->assertSame(10, DB::table('preguntas_instrumento')->where('estado', 'ACTIVA')->count());
        $this->assertFalse(DB::table('valoraciones_dolor')->where('intensidad', '>', 0)->where('respuesta', 'No refiere dolor.')->exists());
        $this->assertSame([8, 5, 3], DB::table('valoraciones_dolor')->where('cod_valoracion_dolor', 'like', 'H26_DV2_2_%')->orderBy('fecha_hora')->pluck('intensidad')->map(fn ($value) => (int) $value)->all());
        $this->assertSame(2, DB::table('valoraciones_dolor')->where('cod_valoracion_origen', 'H26_DV2_2_0')->count());
        $this->assertSame([4, 4], DB::table('valoraciones_dolor')->where('cod_valoracion_dolor', 'like', 'H26_DV2_3_%')->orderBy('fecha_hora')->pluck('intensidad')->map(fn ($value) => (int) $value)->all());
        $this->assertNull(DB::table('valoraciones_dolor')->where('cod_valoracion_dolor', 'H26_DV2_1_0')->value('respuesta'));
        $this->assertFalse(DB::table('registros_hidratacion')->whereNotIn('tolerancia', ['ADECUADA', 'PARCIAL', 'INSUFICIENTE', 'RECHAZADA'])->exists());
        $this->assertFalse(DB::table('registros_movilidad')->whereNotIn('traslado', ['INDEPENDIENTE', 'SUPERVISION', 'AYUDA_UNA_PERSONA', 'AYUDA_DOS_PERSONAS', 'GRUA'])->exists());
        $this->assertFalse(User::findOrFail('H26_USU_N01')->can('prescripciones.crear'));
        $this->assertTrue(User::findOrFail('H26_USU_MED')->can('prescripciones.crear'));
        $nurse = DB::table('asignaciones_residente_jornada')->where('cod_jornada', 'H26_J_261008_0')->first()->cod_personal;
        $this->travelTo(Carbon::parse(Historical2026Seeder::CUTOFF));
        $dashboard = app(MiTurnoService::class)->obtenerDatosDashboard(User::findOrFail(str_replace('PER', 'USU', $nurse)));
        $this->assertSame('EN_TURNO', $dashboard['modo']);
        $this->assertNotEmpty($dashboard['residentes']);
        $this->travelBack();
        $before = DB::table('signos_vitales')->orderBy('cod_signo')->get()->toJson();
        $password = User::findOrFail('H26_USU_MED')->contrasena;
        $this->seed(Historical2026Seeder::class);
        $this->assertSame($before, DB::table('signos_vitales')->orderBy('cod_signo')->get()->toJson());
        $this->assertSame($password, User::findOrFail('H26_USU_MED')->contrasena);
        // Detecta corrupción en una familia antes omitida; jamás declara verificada una carga incompleta.
        DB::table('registros_sueno')->where('cod_registro_sueno', DB::table('registros_sueno')->value('cod_registro_sueno'))->delete();
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
