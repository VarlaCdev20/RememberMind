<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarValoracionPetersenOriginal;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Process\Process;
use Tests\Support\EntornoPruebas;
use Tests\TestCase;

#[Group('postgresql')]
final class PetersenOriginalConcurrenciaPostgresTest extends TestCase
{
    private bool $desechable = false;

    protected function tearDown(): void
    {
        if ($this->desechable) {
            DB::statement('DROP SCHEMA public CASCADE');
            DB::statement('CREATE SCHEMA public');
        }
        RefreshDatabaseState::$migrated = false;
        parent::tearDown();
    }

    public static function operaciones(): array
    {
        return [['registro'], ['rectificacion']];
    }

    #[DataProvider('operaciones')]
    public function test_reenvios_y_rectificaciones_concurrentes_no_duplican_ni_ramifican(string $operacion): void
    {
        $db = DB::connection();
        if ($db->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Requiere dos procesos y PostgreSQL desechable.');
        }
        if (! app()->environment('testing') || ! EntornoPruebas::postgresDesechable($db->getDatabaseName(), $db->getConfig('username'), $db->getConfig('host'))) {
            throw new \RuntimeException('No se ejecuta contra una base operativa.');
        }
        $this->desechable = true;
        DB::statement('DROP SCHEMA public CASCADE');
        DB::statement('CREATE SCHEMA public');
        $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
        $this->seed(RolesAndPermissionsSeeder::class);
        $u = User::factory()->create(['estado' => 'ACTIVO']);
        $u->assignRole('PSICOLOGO/A');
        Personal::create(['cod_usuario' => $u->getKey(), 'nombres' => 'Profesional', 'apellido_paterno' => 'Concurrente',
            'numero_documento' => 'PET-RACE', 'profesion' => 'PSICOLOGIA', 'estado' => 'ACTIVO']);
        $r = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $this->actingAs($u);
        $entrada = ['solicitud_uuid' => (string) Str::uuid(), 'elemento' => 'C04', 'estado_original' => 'N', 'tipo_registro' => 'REGISTRO',
            'fecha_hora' => now()->subDay()->format('Y-m-d H:i:s'), 'justificacion' => 'Valoración sintética concurrente explícita.', 'soportes' => []];
        if ($operacion === 'rectificacion') {
            $anterior = app(RegistrarValoracionPetersenOriginal::class)->ejecutar($u, $r, $entrada);
            $entrada['tipo_registro'] = 'RECTIFICACION';
            $entrada['cod_valoracion_anterior'] = $anterior->getKey();
        }
        $dir = sys_get_temp_dir().'/remembermind-petersen-race-'.bin2hex(random_bytes(8));
        File::makeDirectory($dir, 0700, true);
        $contexto = $dir.'/contexto.json';
        file_put_contents($contexto, json_encode(['conexion' => $db->getConfig(), 'usuario' => $u->getKey(), 'directorio' => $dir,
            'residente' => $r->getKey(), 'entrada' => $entrada, 'operacion' => $operacion,
            'solicitudes' => [1 => (string) Str::uuid(), 2 => (string) Str::uuid()]], JSON_THROW_ON_ERROR));
        $barrera = fopen($dir.'/barrera', 'c+');
        flock($barrera, LOCK_EX);
        $procesos = [];
        try {
            foreach ([1, 2] as $i) {
                $p = new Process([PHP_BINARY, base_path('tests/Support/petersen_original_worker.php'), $contexto, (string) $i], base_path());
                $p->setTimeout(35)->start();
                $procesos[] = $p;
            }
            $limite = microtime(true) + 20;
            while ((! is_file($dir.'/listo-1') || ! is_file($dir.'/listo-2')) && microtime(true) < $limite) {
                clearstatcache();
                usleep(10000);
            }
            self::assertFileExists($dir.'/listo-1');
            self::assertFileExists($dir.'/listo-2');
            flock($barrera, LOCK_UN);
            $salidas = [];
            foreach ($procesos as $p) {
                self::assertSame(0, $p->wait(), $p->getErrorOutput());
                $salidas[] = json_decode($p->getOutput(), true, flags: JSON_THROW_ON_ERROR);
            }
            if ($operacion === 'registro') {
                self::assertSame($salidas[0]['codigo'], $salidas[1]['codigo']);
                self::assertDatabaseCount('valoraciones_petersen_originales', 1);
            } else {
                $estados = array_column($salidas, 'estado');
                sort($estados);
                self::assertSame(['conflicto', 'guardado'], $estados);
                self::assertDatabaseCount('valoraciones_petersen_originales', 2);
                self::assertSame('N', $anterior->fresh()->estado_original);
            }
        } finally {
            flock($barrera, LOCK_UN);
            fclose($barrera);
            foreach ($procesos as $p) {
                if ($p->isRunning()) {
                    $p->stop(1);
                }
            }
            if (dirname(realpath($dir)) === realpath(sys_get_temp_dir()) && ! is_link($dir)
                && preg_match('/^remembermind-petersen-race-[a-f0-9]{16}$/D', basename($dir))) {
                File::deleteDirectory($dir);
            }
        }
    }
}
