<?php

namespace Tests\Feature;

use App\Models\FotografiaInstitucional;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Process\Process;
use Tests\Support\EntornoPruebas;
use Tests\TestCase;

#[Group('postgresql')]
class GaleriaInstitucionalConcurrenciaPostgresTest extends TestCase
{
    public function test_dos_subidas_solapadas_conservan_un_registro_y_solo_sus_dos_archivos(): void
    {
        $db = DB::connection();
        if ($db->getDriverName() !== 'pgsql') $this->markTestSkipped('Requiere el runner PostgreSQL desechable.');
        if (! app()->environment('testing') || ! EntornoPruebas::postgresDesechable($db->getDatabaseName(), $db->getConfig('username'), $db->getConfig('host'))) {
            throw new \RuntimeException('Base ajena al runner desechable.');
        }
        $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
        $this->seed(RolesAndPermissionsSeeder::class);
        $usuario = User::factory()->create(['nombres' => 'Gestora', 'ap_paterno' => 'Sintética']);
        $usuario->assignRole('GERENTE');
        $root = sys_get_temp_dir().'/remembermind-gallery-race-'.bin2hex(random_bytes(16));
        File::makeDirectory($root);
        $imagen = UploadedFile::fake()->image('entrada.png', 200, 140);
        File::copy($imagen->getRealPath(), $root.'/entrada.png');
        $env = ['APP_ENV' => 'testing', 'APP_CONFIG_CACHE' => $root.'/sin-config.php', 'APP_ROUTES_CACHE' => $root.'/sin-rutas.php',
            'DB_CONNECTION' => 'pgsql', 'DB_URL' => '', 'DB_HOST' => $db->getConfig('host'), 'DB_PORT' => (string) $db->getConfig('port'),
            'DB_DATABASE' => $db->getDatabaseName(), 'DB_USERNAME' => $db->getConfig('username'), 'DB_PASSWORD' => $db->getConfig('password'),
            'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array'];
        $procesos = [];
        try {
            foreach (['1', '2'] as $worker) {
                $p = new Process([PHP_BINARY, base_path('tests/Support/GaleriaConcurrenteWorker.php'), $root, $worker, $usuario->getKey()], base_path(), $env, timeout: 35);
                $p->start();
                $procesos[] = $p;
            }
            $resultados = [];
            foreach ($procesos as $p) {
                $p->wait();
                $this->assertTrue($p->isSuccessful(), $p->getErrorOutput());
                $resultados[] = json_decode($p->getOutput(), true, flags: JSON_THROW_ON_ERROR);
            }
            $this->assertEqualsCanonicalizing([true, false], array_column($resultados, 'nueva'));
            $this->assertSame($resultados[0]['codigo'], $resultados[1]['codigo']);
            $this->assertDatabaseCount('fotografias_institucionales', 1);
            $foto = FotografiaInstitucional::firstOrFail();
            $this->assertFileExists($root.'/archivos/'.$foto->ruta_archivo);
            $this->assertFileExists($root.'/archivos/'.$foto->ruta_miniatura);
            $this->assertCount(2, File::allFiles($root.'/archivos'));
        } finally {
            // Los workers confirman fuera de transacciones: el siguiente RefreshDatabase debe reconstruir.
            \Illuminate\Foundation\Testing\RefreshDatabaseState::$migrated = false;
            foreach ($procesos as $p) if ($p->isRunning()) $p->stop();
            $resuelto = realpath($root);
            if ($resuelto && dirname($resuelto) === realpath(sys_get_temp_dir()) && preg_match('/^remembermind-gallery-race-[a-f0-9]{32}$/D', basename($resuelto)) && ! is_link($resuelto)) {
                File::deleteDirectory($resuelto);
            }
        }
    }
}
