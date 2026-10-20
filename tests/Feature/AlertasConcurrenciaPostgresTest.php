<?php

namespace Tests\Feature;

use App\Models\Alerta;
use App\Models\Residente;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class AlertasConcurrenciaPostgresTest extends TestCase
{
    use DatabaseMigrations;

    public function test_dos_cierres_simultaneos_conservan_un_evento_y_un_conflicto(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Los bloqueos se verifican en PostgreSQL desechable.');
        }
        $this->seed(RolesAndPermissionsSeeder::class);
        $actor = User::factory()->create(['nombres' => 'Coordinación sintética']);
        $actor->assignRole('ADMINISTRADOR');
        $residente = Residente::factory()->create();
        $alerta = Alerta::create(['cod_residente' => $residente->getKey(), 'tipo' => 'COORDINACION', 'modulo' => 'MANUAL', 'prioridad' => 'MEDIO', 'estado' => 'ASIGNADA']);
        $barrera = sys_get_temp_dir().'/remembermind-alertas-carrera-'.bin2hex(random_bytes(8));
        mkdir($barrera, 0700);
        $workers = [];
        try {
            foreach ([1, 2] as $indice) {
                $worker = new Process([PHP_BINARY, base_path('tests/Support/alertas_concurrencia_worker.php'), $actor->getKey(), $alerta->getKey(), $barrera, (string) $indice], base_path());
                $worker->setTimeout(45);
                $worker->start();
                $workers[] = $worker;
            }
            $limite = microtime(true) + 35;
            while (count(glob($barrera.'/*.ready')) !== 2) {
                foreach ($workers as $worker) {
                    if (! $worker->isRunning()) {
                        $this->fail('El worker terminó antes de la barrera: '.$worker->getErrorOutput());
                    }
                }
                if (microtime(true) > $limite) {
                    $this->fail('Los dos workers no alcanzaron la barrera.');
                }
                usleep(2000);
            }
            file_put_contents($barrera.'/go', 'go');
            $salidas = [];
            foreach ($workers as $worker) {
                $worker->wait();
                $this->assertTrue($worker->isSuccessful(), $worker->getErrorOutput());
                $salidas[] = trim($worker->getOutput());
            }
            $this->assertEqualsCanonicalizing(['CERRADA', 'CONFLICTO'], $salidas);
            $this->assertSame('CERRADA', $alerta->fresh()->estado);
            $this->assertSame(1, $alerta->eventos()->count());
            $this->assertSame('ASIGNADA', $alerta->eventos()->sole()->estado_anterior);
            $this->assertSame($actor->getKey(), $alerta->eventos()->sole()->cod_usuario);
        } finally {
            foreach ($workers as $worker) {
                if ($worker->isRunning()) {
                    $worker->stop();
                }
            }
            foreach (glob($barrera.'/*') as $archivo) {
                unlink($archivo);
            }
            rmdir($barrera);
        }
    }
}
