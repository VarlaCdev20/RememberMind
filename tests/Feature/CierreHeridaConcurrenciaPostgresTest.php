<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\Herida;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use PDO;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class CierreHeridaConcurrenciaPostgresTest extends TestCase
{
    use DatabaseMigrations;

    protected function beforeRefreshingDatabase(): void
    {
        if (config('database.default') !== 'pgsql') {
            $this->markTestSkipped('La concurrencia requiere PostgreSQL desechable.');
        }
    }

    public function test_dos_cierres_solapados_conservan_un_solo_cierre_y_la_historia(): void
    {
        $this->travelTo(now()->startOfDay()->addHours(10));
        $this->seed(RolesAndPermissionsSeeder::class);
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('ENFERMEROS');
        $personal = Personal::create(['cod_personal' => 'PER_CIERRE', 'cod_usuario' => $usuario->getKey(), 'nombres' => 'Enfermería', 'apellido_paterno' => 'Sintética', 'numero_documento' => 'CIERRE-QA', 'profesion' => 'ENFERMERIA', 'estado' => 'ACTIVO']);
        Area::create(['cod_area' => 'ARE_CIERRE', 'nombre' => 'Área sintética', 'estado' => 'ACTIVA']);
        Turno::create(['cod_turno' => 'TUR_CIERRE', 'nombre' => 'Turno sintético', 'hora_inicio' => '07:00', 'hora_cierre' => '19:00', 'estado' => 'ACTIVO']);
        Jornada::create(['cod_jornada' => 'JOR_CIERRE', 'cod_turno' => 'TUR_CIERRE', 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        AsignacionPersonal::create(['cod_asignacion_personal' => 'ASP_CIERRE', 'cod_personal' => $personal->getKey(), 'cod_jornada' => 'JOR_CIERRE', 'cod_area' => 'ARE_CIERRE', 'funcion' => 'ENFERMERA', 'tipo_asignacion' => 'TURNO', 'fecha_asignacion' => today(), 'estado' => 'ACTIVO']);
        $residente = Residente::crearDesdeAdmision(['cod_residente' => 'RES_CIERRE', 'nombres' => 'Residente', 'apellido_paterno' => 'Sintético', 'fecha_nacimiento' => '1940-01-01', 'estado' => 'ADMITIDO']);
        AsignacionResidenteJornada::create(['cod_asignacion' => 'ARJ_CIERRE', 'cod_residente' => $residente->getKey(), 'cod_personal' => $personal->getKey(), 'cod_jornada' => 'JOR_CIERRE', 'fecha_hora' => now(), 'estado' => 'ACTIVA']);
        $herida = Herida::create(['cod_herida' => 'HER_CIERRE', 'cod_residente' => $residente->getKey(), 'cod_personal' => $personal->getKey(), 'tipo_herida' => 'Herida sintética', 'ubicacion' => 'Ubicación sintética', 'fecha_hora_identificacion' => now(), 'estado' => 'ACTIVA', 'observacion' => 'Historia inicial conservada']);
        $config = DB::connection()->getConfig();
        $observador = new PDO('pgsql:host='.$config['host'].';port='.$config['port'].';dbname='.$config['database'], $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $servidor = stream_socket_server('tcp://127.0.0.1:0', $codigo, $mensaje);
        $this->assertNotFalse($servidor);
        $procesos = $sockets = [];
        try {
            $observador->beginTransaction();
            $lock = $observador->prepare('select cod_herida from heridas where cod_herida = ? for update');
            $lock->execute([$herida->getKey()]);
            $pids = [];
            foreach (['UNO', 'DOS'] as $marca) {
                $proceso = new Process([PHP_BINARY, base_path('tests/Support/cierre_herida_worker.php'), stream_socket_get_name($servidor, false), $usuario->getKey(), $residente->getKey(), $herida->getKey(), $marca, now()->toDateTimeString()], base_path());
                $proceso->setTimeout(30);
                $proceso->start();
                $procesos[] = $proceso;
                $socket = stream_socket_accept($servidor, 15);
                $this->assertNotFalse($socket, $proceso->getErrorOutput());
                stream_set_timeout($socket, 20);
                $sockets[] = $socket;
                $ready = json_decode((string) fgets($socket), true, flags: JSON_THROW_ON_ERROR);
                $this->assertTrue($ready['ready']);
                $pids[] = (int) $ready['pid'];
            }
            $this->assertNotSame($pids[0], $pids[1]);
            foreach ($sockets as $socket) {
                fwrite($socket, "GO\n");
            }
            $consulta = $observador->prepare("select count(*) from pg_stat_activity where pid in (?, ?) and wait_event_type = 'Lock' and cardinality(pg_blocking_pids(pid)) > 0");
            $limite = hrtime(true) + 10_000_000_000;
            do {
                $observador->query('select pg_stat_clear_snapshot()');
                $consulta->execute($pids);
                $esperando = (int) $consulta->fetchColumn();
            } while ($esperando !== 2 && hrtime(true) < $limite);
            $this->assertSame(2, $esperando, 'Ambos procesos deben competir realmente por la misma herida.');
            $observador->commit();
            $resultados = [];
            foreach ($sockets as $i => $socket) {
                $resultados[] = json_decode((string) fgets($socket), true, flags: JSON_THROW_ON_ERROR);
                $this->assertSame(0, $procesos[$i]->wait(), $procesos[$i]->getErrorOutput());
            }
            $this->assertEqualsCanonicalizing(['CERRADA', 'RECHAZADA'], array_column($resultados, 'result'));
            $this->assertSame(['resultadoCierreLesion'], collect($resultados)->firstWhere('result', 'RECHAZADA')['errors']);
            $this->assertLessThan(min(array_column($resultados, 'end')), max(array_column($resultados, 'start')));
            $guardada = $herida->fresh();
            $this->assertSame('CERRADA', $guardada->estado);
            $this->assertTrue($guardada->fecha_hora_cierre->equalTo(now()));
            $this->assertStringStartsWith('Historia inicial conservada', $guardada->observacion);
            $this->assertSame(1, substr_count($guardada->observacion, '[Cierre:'));
            $ganador = collect($resultados)->firstWhere('result', 'CERRADA')['marca'];
            $this->assertStringContainsString('Motivo concurrente '.$ganador, $guardada->observacion);
            $this->assertDatabaseCount('heridas', 1);
            $this->assertDatabaseCount('curaciones_herida', 0);
            $this->assertDatabaseCount('documentos_clinicos', 0);
            fwrite(STDOUT, "\nCIERRE_HERIDA_RACE ".json_encode(['waiting_locks' => $esperando, 'results' => array_column($resultados, 'result')])."\n");
        } finally {
            if ($observador->inTransaction()) {
                $observador->rollBack();
            }
            foreach ($sockets as $socket) {
                fclose($socket);
            }
            fclose($servidor);
            foreach ($procesos as $proceso) {
                if ($proceso->isRunning()) {
                    $proceso->stop();
                }
            }
        }
    }
}
