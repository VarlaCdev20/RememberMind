<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Jornada;
use App\Models\Turno;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Process\Process;
use Tests\Support\EntornoPruebas;
use Tests\TestCase;

#[Group('postgresql')]
class GerenciaConcurrenciaPlanificacionPostgresTest extends TestCase
{
    public static function cambios(): array
    {
        return [['areas', 'cod_area', 'INACTIVO'], ['personal', 'cod_personal', 'INACTIVO'], ['turnos', 'cod_turno', 'INACTIVO'], ['jornadas', 'cod_jornada', 'CERRADA']];
    }

    #[DataProvider('cambios')]
    public function test_estado_revalidado_despues_de_esperar_bloqueo_no_guarda_asignacion(string $tabla, string $pk, string $estado): void
    {
        $db = DB::connection();
        if ($db->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Requiere PostgreSQL desechable.');
        }
        if (! app()->environment('testing') || ! EntornoPruebas::postgresDesechable($db->getDatabaseName(), $db->getConfig('username'), $db->getConfig('host'))) {
            throw new \RuntimeException('Destino ajeno al ensayo.');
        }
        $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
        (require base_path('database/propuestas/gerencia/2026_10_26_000002_historia_vinculos_asignaciones.php'))->up();
        $this->seed(RolesAndPermissionsSeeder::class);
        $actor = User::factory()->create();
        $actor->assignRole('GERENTE');
        $cuenta = User::factory()->create(['nombres' => 'Persona', 'ap_paterno' => 'Prueba']);
        $cuenta->assignRole('ENFERMEROS');
        $area = Area::create(['nombre' => 'Área de prueba', 'estado' => 'ACTIVO']);
        $turno = Turno::create(['nombre' => 'Mañana', 'hora_inicio' => '08:00:00', 'hora_cierre' => '16:00:00', 'estado' => 'ACTIVO', 'orden' => 1]);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => '2026-10-26', 'estado' => 'ABIERTA']);
        $codigo = match ($tabla) {
            'areas' => $area->getKey(),'personal' => $cuenta->personal->getKey(),'turnos' => $turno->getKey(),'jornadas' => $jornada->getKey()
        };
        $root = sys_get_temp_dir().'/remembermind-gerencia-race-'.bin2hex(random_bytes(16));
        File::makeDirectory($root);
        $aplicacion = 'rm_gerencia_worker_'.bin2hex(random_bytes(8));
        $datos = ['sin_barrera' => true, 'aplicacion' => $aplicacion, 'modo' => 'horario', 'actor' => $actor->getKey(), 'entrada' => [
            'usuarioId' => $cuenta->getKey(), 'f_cod_area' => $area->getKey(), 'f_cod_turno' => $turno->getKey(),
            'f_fecha_inicio' => '2026-10-26', 'f_dias_semana' => ['LUNES'], 'f_tipo_asignacion' => 'TEMPORAL', 'f_observaciones' => 'Ensayo de espera concurrente', 'modoEdicion' => false,
        ]];
        file_put_contents($root.'/datos.json', json_encode($datos, JSON_THROW_ON_ERROR));
        $env = ['APP_ENV' => 'testing', 'APP_CONFIG_CACHE' => $root.'/config.php', 'APP_ROUTES_CACHE' => $root.'/routes.php', 'DB_CONNECTION' => 'pgsql', 'DB_URL' => '',
            'DB_HOST' => $db->getConfig('host'), 'DB_PORT' => (string) $db->getConfig('port'), 'DB_DATABASE' => $db->getDatabaseName(),
            'DB_USERNAME' => $db->getConfig('username'), 'DB_PASSWORD' => $db->getConfig('password'), 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array'];
        $proceso = new Process([PHP_BINARY, base_path('tests/Support/GerenciaConcurrenteWorker.php'), $root, '1'], base_path(), $env, timeout: 35);
        try {
            DB::beginTransaction();
            DB::table($tabla)->where($pk, $codigo)->lockForUpdate()->first();
            $proceso->start();
            $limite = microtime(true) + 20;
            $bloqueado = false;
            do {
                // PostgreSQL conserva la instantánea estadística dentro de la transacción.
                // Renovarla permite observar al worker que acaba de conectar sin liberar el lock.
                DB::select('select pg_stat_clear_snapshot()');
                $bloqueado = (bool) DB::scalar("select exists(select 1 from pg_stat_activity where datname=current_database() and application_name=? and wait_event_type='Lock')", [$aplicacion]);
                if ($bloqueado || ! $proceso->isRunning()) {
                    break;
                }
                usleep(20000);
            } while (microtime(true) < $limite);
            $this->assertTrue($bloqueado, 'El escritor debe alcanzar el bloqueo real antes del cambio. '.$proceso->getErrorOutput());
            DB::table($tabla)->where($pk, $codigo)->update(['estado' => $estado]);
            DB::commit();
            $proceso->wait();
            $this->assertTrue($proceso->isSuccessful(), $proceso->getErrorOutput());
            $this->assertSame('RECHAZADO', json_decode($proceso->getOutput(), true, flags: JSON_THROW_ON_ERROR)['estado']);
            $this->assertDatabaseCount('asignaciones_personal', 0);
            $this->assertDatabaseCount('eventos_asignacion_personal', 0);
            $this->assertSame($estado, DB::table($tabla)->where($pk, $codigo)->value('estado'));
        } finally {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }if ($proceso->isRunning()) {
                $proceso->stop();
            }
            RefreshDatabaseState::$migrated = false;
            $real = realpath($root);
            if ($real && dirname($real) === realpath(sys_get_temp_dir()) && preg_match('/^remembermind-gerencia-race-[a-f0-9]{32}$/D', basename($real)) && ! is_link($real)) {
                File::deleteDirectory($real);
            }
        }
    }
}
