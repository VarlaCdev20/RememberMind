<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\Jornada;
use App\Models\PaseTurno;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Cuidados\Aplicacion\Acciones\PaseTurnoService;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Group;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\Process\Process;
use Tests\Support\EntornoPruebas;
use Tests\TestCase;

#[Group('postgresql')]
class PaseTurnoConcurrenciaPostgresTest extends TestCase
{
    private User $saliente;

    private User $entrante;

    private Residente $residente;

    private array $entrada;

    protected function setUp(): void
    {
        parent::setUp();
        $db = DB::connection();
        if ($db->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('La carrera requiere PostgreSQL desechable.');
        }
        if (! app()->environment('testing') || ! EntornoPruebas::postgresDesechable($db->getDatabaseName(), $db->getConfig('username'), $db->getConfig('host'))) {
            throw new \RuntimeException('No es la base aislada del runner.');
        }
        $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
        Carbon::setTestNow('2026-10-16 10:00:00');
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->saliente = User::factory()->create(['nombres' => 'Emisor sintético', 'estado' => 'ACTIVO']);
        $this->entrante = User::factory()->create(['nombres' => 'Receptor sintético', 'estado' => 'ACTIVO']);
        $this->saliente->assignRole('ENFERMEROS');
        $this->entrante->assignRole('ENFERMEROS');
        $this->residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $area = Area::create(['cod_area' => 'ARE_PASE_PG', 'nombre' => 'Enfermería sintética', 'estado' => 'ACTIVA']);
        foreach ([['1', '07:00', '15:00', $this->saliente, 'ABIERTA'], ['2', '15:00', '23:00', $this->entrante, 'PLANIFICADA']] as [$numero, $inicio, $fin, $usuario, $estado]) {
            $turno = Turno::create(['cod_turno' => 'TUR_PASE_PG_'.$numero, 'nombre' => 'Turno sintético '.$numero, 'hora_inicio' => $inicio, 'hora_cierre' => $fin, 'orden' => (int) $numero, 'estado' => 'ACTIVO']);
            $jornada = Jornada::create(['cod_jornada' => 'JOR_PASE_PG_'.$numero, 'cod_turno' => $turno->getKey(), 'fecha_jornada' => today(), 'estado' => $estado]);
            AsignacionPersonal::create(['cod_personal' => $usuario->personal->getKey(), 'cod_jornada' => $jornada->getKey(), 'cod_area' => $area->getKey(), 'tipo_asignacion' => 'RESPONSABLE', 'fecha_asignacion' => now(), 'estado' => 'ACTIVO']);
            AsignacionResidenteJornada::create(['cod_personal' => $usuario->personal->getKey(), 'cod_jornada' => $jornada->getKey(), 'cod_residente' => $this->residente->getKey(), 'estado' => 'ACTIVA']);
        }
        $this->entrada = ['cod_jornada_saliente' => 'JOR_PASE_PG_1', 'cod_jornada_entrante' => 'JOR_PASE_PG_2', 'cod_personal_entrante' => $this->entrante->personal->getKey(), 'resumen' => 'Entrega concurrente sintética'];
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        // Este fixture usa commits reales; la siguiente suite debe reconstruir su propia base.
        RefreshDatabaseState::$migrated = false;
        parent::tearDown();
    }

    public function test_dos_entradas_distintas_no_crean_dos_primeros_pases(): void
    {
        $this->competir('entregar', $this->saliente);
        $this->assertDatabaseCount('pases_turno', 1);
        $pase = PaseTurno::sole();
        $this->assertSame($this->saliente->personal->getKey(), $pase->cod_personal_saliente);
        $this->assertSame(1, Activity::where('subject_type', PaseTurno::class)->count());
    }

    public function test_doble_recepcion_concurrente_conserva_una_confirmacion(): void
    {
        $pase = app(PaseTurnoService::class)->confirmarEntrega($this->saliente, ['cod_residente' => $this->residente->getKey(), 'resumen' => 'Resumen que se conserva']);
        $this->competir('recibir', $this->entrante, $pase);
        $this->assertSame('RECIBIDO', $pase->fresh()->estado);
        $this->assertSame('Resumen que se conserva', $pase->fresh()->resumen);
        $this->assertSame($this->entrante->personal->getKey(), $pase->fresh()->cod_personal_entrante);
        $this->assertSame(1, Activity::where('description', 'Confirmación de recepción de pase de turno')->count());
    }

    private function competir(string $operacion, User $usuario, ?PaseTurno $pase = null): void
    {
        $directorio = sys_get_temp_dir().'/remembermind-pase-race-'.bin2hex(random_bytes(8));
        File::makeDirectory($directorio, 0700, true);
        $config = $directorio.'/contexto.json';
        file_put_contents($config, json_encode(['conexion' => DB::connection()->getConfig(), 'usuario' => $usuario->getKey(), 'residente' => $this->residente->getKey(), 'pase' => $pase?->getKey(), 'entrada' => $this->entrada, 'operacion' => $operacion, 'momento' => now()->toDateTimeString(), 'directorio' => $directorio], JSON_THROW_ON_ERROR));
        $barrera = fopen($directorio.'/barrera', 'c+');
        flock($barrera, LOCK_EX);
        $procesos = [];
        try {
            foreach ([1, 2] as $numero) {
                $proceso = new Process([PHP_BINARY, base_path('tests/Support/pase_turno_concurrencia_worker.php'), $config, (string) $numero], base_path());
                $proceso->setTimeout(30)->start();
                $procesos[] = $proceso;
            }
            $limite = microtime(true) + 20;
            while ((! is_file($directorio.'/listo-1') || ! is_file($directorio.'/listo-2')) && microtime(true) < $limite) {
                clearstatcache();
                usleep(10000);
            }
            $this->assertFileExists($directorio.'/listo-1');
            $this->assertFileExists($directorio.'/listo-2');
            flock($barrera, LOCK_UN);
            $resultados = [];
            foreach ($procesos as $proceso) {
                $this->assertSame(0, $proceso->wait(), $proceso->getErrorOutput());
                $resultados[] = json_decode($proceso->getOutput(), true, flags: JSON_THROW_ON_ERROR)['resultado'];
            }
            sort($resultados);
            $this->assertSame(['guardado', 'rechazado'], $resultados);
        } finally {
            flock($barrera, LOCK_UN);
            fclose($barrera);
            foreach ($procesos as $proceso) {
                if ($proceso->isRunning()) {
                    $proceso->stop(1);
                }
            }
            if (dirname(realpath($directorio)) === realpath(sys_get_temp_dir()) && ! is_link($directorio)
                && preg_match('/^remembermind-pase-race-[a-f0-9]{16}$/D', basename($directorio))) {
                File::deleteDirectory($directorio);
            }
        }
    }
}
