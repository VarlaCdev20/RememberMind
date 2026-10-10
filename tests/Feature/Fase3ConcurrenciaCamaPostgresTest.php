<?php

namespace Tests\Feature;

use App\Models\Cama;
use App\Models\Habitacion;
use App\Models\Preadmision;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use PDO;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class Fase3ConcurrenciaCamaPostgresTest extends TestCase
{
    use DatabaseMigrations;

    protected function beforeRefreshingDatabase(): void
    {
        if (config('database.default') !== 'pgsql') {
            $this->markTestSkipped('Prueba de locks PostgreSQL; debe ejecutarse en base desechable dedicada.');
        }
        $this->assertTrue(app()->environment('testing'));
        $this->assertMatchesRegularExpression('/^remembermind_f3_test_[a-f0-9]{8}$/', config('database.connections.pgsql.database'));
        $contexto = DB::selectOne('select current_database() as db, current_user as usuario');
        $this->assertSame(config('database.connections.pgsql.database'), $contexto->db);
        $this->assertSame($contexto->db.'_runner', $contexto->usuario);
    }

    public function test_dos_procesos_solapados_solo_formalizan_una_admision_para_la_cama(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $usuario = User::factory()->create();
        $usuario->assignRole('ADMINISTRADOR');
        $habitacion = Habitacion::create(['cod_habitacion' => 'HAB_F3_RACE', 'codigo' => 'Sintética', 'capacidad' => 1, 'estado' => 'ACTIVA']);
        $cama = Cama::create(['cod_cama' => 'CAM_F3_RACE', 'cod_habitacion' => $habitacion->cod_habitacion, 'codigo' => 'Sintética', 'estado' => 'ACTIVA']);
        $solicitudes = [];
        foreach ([1, 2] as $numero) {
            $solicitudes[] = Preadmision::create(['cod_preadmision' => 'PRE_F3_RACE_'.$numero,
                'cod_usuario_registro' => $usuario->cod_usuario, 'nombres' => 'Postulante sintético '.$numero,
                'apellido_paterno' => 'Prueba', 'fecha_nacimiento' => '1940-01-01',
                'motivo_ingreso' => 'Prueba de concurrencia', 'fecha_solicitud' => now(), 'estado' => 'APROBADA']);
        }
        $configuracion = config('database.connections.pgsql');
        $auditoriaAntes = DB::table('activity_log')->count();
        $observador = new PDO('pgsql:host='.$configuracion['host'].';port='.$configuracion['port'].';dbname='.$configuracion['database'],
            $configuracion['username'], $configuracion['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $servidor = stream_socket_server('tcp://127.0.0.1:0', $codigo, $mensaje);
        $this->assertNotFalse($servidor);
        $direccion = stream_socket_get_name($servidor, false);
        $procesos = [];
        $sockets = [];
        try {
            $observador->beginTransaction();
            $lock = $observador->prepare('select cod_cama from camas where cod_cama = ? for update');
            $lock->execute([$cama->cod_cama]);
            $pids = [];
            foreach ($solicitudes as $solicitud) {
                $proceso = new Process([PHP_BINARY, base_path('tests/Support/fase3_admision_worker.php'), $direccion,
                    $usuario->cod_usuario, $solicitud->cod_preadmision, $cama->cod_cama], base_path());
                $proceso->setTimeout(30);
                $proceso->start();
                $procesos[] = $proceso;
                $socket = stream_socket_accept($servidor, 15);
                $this->assertNotFalse($socket, 'El worker debe llegar a la barrera: '.$proceso->getErrorOutput());
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
            // La fila queda retenida hasta observar ambas sesiones realmente esperando un lock.
            // El timeout limita una prueba fallida; no simula concurrencia mediante sleep.
            $esperas = $observador->prepare("select count(*) from pg_stat_activity where pid in (?, ?) and wait_event_type = 'Lock' and cardinality(pg_blocking_pids(pid)) > 0");
            $limite = hrtime(true) + 10_000_000_000;
            do {
                $observador->query('select pg_stat_clear_snapshot()');
                $esperas->execute($pids);
                $esperando = (int) $esperas->fetchColumn();
            } while ($esperando !== 2 && hrtime(true) < $limite);
            $this->assertSame(2, $esperando, 'Ambas conexiones deben competir simultáneamente por el lock de cama.');
            $observador->commit();
            $resultados = [];
            foreach ($sockets as $indice => $socket) {
                $resultados[] = json_decode((string) fgets($socket), true, flags: JSON_THROW_ON_ERROR);
                $this->assertSame(0, $procesos[$indice]->wait(), $procesos[$indice]->getErrorOutput());
            }
            $this->assertEqualsCanonicalizing(['ADMITIDA', 'RECHAZADA'], array_column($resultados, 'result'));
            $rechazo = collect($resultados)->firstWhere('result', 'RECHAZADA');
            $this->assertSame(['cod_cama'], $rechazo['errors']);
            $this->assertLessThan(min(array_column($resultados, 'end')), max(array_column($resultados, 'start')));
            foreach (['residentes', 'admisiones', 'ocupaciones_cama', 'contactos', 'residentes_contactos',
                'historial_estados_residente', 'consentimientos'] as $tabla) {
                $this->assertDatabaseCount($tabla, 1);
            }
            $this->assertDatabaseCount('activity_log', $auditoriaAntes + 1);
            $this->assertSame(1, DB::table('activity_log')->where('log_name', 'Admisiones')
                ->where('causer_id', $usuario->cod_usuario)->count());
            $this->assertDatabaseCount('seguros_residente', 0);
            $this->assertSame(1, Preadmision::where('estado', 'ADMITIDA')->count());
            $this->assertSame(1, Preadmision::where('estado', 'APROBADA')->count());
            $this->assertDatabaseHas('ocupaciones_cama', ['cod_cama' => $cama->cod_cama, 'estado' => 'ACTIVA']);
            fwrite(STDOUT, "\nF3_RACE ".json_encode(['database' => $configuracion['database'], 'pids' => $pids,
                'waiting_locks' => $esperando, 'results' => array_column($resultados, 'result'),
                'residentes' => DB::table('residentes')->count(), 'ocupaciones' => DB::table('ocupaciones_cama')->count()])."\n");
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
