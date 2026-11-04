<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\{Area, AsignacionPersonal, ControlFuncionalIadl as Iadl, Jornada, Personal, Residente, Turno, User};
use App\Modulos\Valoraciones\Aplicacion\Acciones\RegistrarControlFuncionalIadl as Registrar;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\{DB, File};
use PHPUnit\Framework\Attributes\{DataProvider, Group};
use Symfony\Component\Process\Process;
use Tests\Support\EntornoPruebas;
use Tests\TestCase;

#[Group('postgresql')]
final class FuncionamientoCotidianoConcurrenciaPostgresTest extends TestCase
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

    public static function operaciones(): array { return [['reintento'], ['rectificacion']]; }

    #[DataProvider('operaciones')]
    public function test_dos_procesos_no_duplican_ni_bifurcan_observaciones(string $operacion): void
    {
        $db = DB::connection();
        if ($db->getDriverName() !== 'pgsql') $this->markTestSkipped('Requiere PostgreSQL desechable y dos procesos.');
        if (! app()->environment('testing') || ! EntornoPruebas::postgresDesechable($db->getDatabaseName(), $db->getConfig('username'), $db->getConfig('host'))) {
            throw new \RuntimeException('No se ejecuta contra una base operativa.');
        }
        $this->desechable = true;
        DB::statement('DROP SCHEMA public CASCADE'); DB::statement('CREATE SCHEMA public');
        $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
        $this->seed(RolesAndPermissionsSeeder::class);
        $u = User::factory()->create(['estado' => 'ACTIVO']); $u->assignRole('PSICOLOGO/A');
        $p = Personal::create(['cod_usuario' => $u->getKey(), 'nombres' => 'Profesional', 'apellido_paterno' => 'Sintético concurrente', 'numero_documento' => 'IADL-CONCURRENT', 'profesion' => 'PSICOLOGIA', 'estado' => 'ACTIVO']);
        Area::create(['cod_area' => 'ARE_FUN', 'nombre' => 'Área sintética', 'estado' => 'ACTIVA']);
        $t = Turno::create(['nombre' => 'Turno sintético', 'hora_inicio' => '00:00', 'hora_cierre' => '23:59', 'orden' => 1, 'estado' => 'ACTIVO']);
        $j = Jornada::create(['cod_turno' => $t->getKey(), 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        AsignacionPersonal::create(['cod_personal' => $p->getKey(), 'cod_jornada' => $j->getKey(), 'cod_area' => 'ARE_FUN', 'fecha_asignacion' => now(), 'estado' => 'ACTIVA']);
        $r = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $this->actingAs($u);
        $entrada = ['fecha_hora' => now()->subHour()->format('Y-m-d\TH:i'), 'actividad' => 'RUTINAS', 'oportunidad' => 'SIN_OPORTUNIDAD', 'desempeno' => 'NO_EVALUABLE',
            'tipo_ayuda' => 'NO_APLICA', 'causa_ayuda' => 'INSTITUCIONAL', 'nivel_previo' => 'DESCONOCIDO', 'fuente' => 'EQUIPO_CUIDADOS', 'observacion' => 'Observación sintética para verificar concurrencia.'];
        $anterior = null;
        if ($operacion === 'rectificacion') {
            $anterior = app(Registrar::class)->registrar($u, $r, $entrada, 'IAD_RACE_ORIGINAL')->getKey();
        }
        $dir = sys_get_temp_dir().'/remembermind-iadl-race-'.bin2hex(random_bytes(8));
        File::makeDirectory($dir, 0700, true);
        $contexto = $dir.'/contexto.json';
        file_put_contents($contexto, json_encode(['conexion' => $db->getConfig(), 'usuario' => $u->getKey(), 'residente' => $r->getKey(), 'entrada' => $entrada,
            'anterior' => $anterior, 'directorio' => $dir, 'codigos' => [1 => 'IAD_RACE_UNO', 2 => $operacion === 'reintento' ? 'IAD_RACE_UNO' : 'IAD_RACE_DOS']], JSON_THROW_ON_ERROR));
        $barrera = fopen($dir.'/barrera', 'c+'); flock($barrera, LOCK_EX);
        $procesos = [];
        try {
            foreach ([1, 2] as $i) {
                $proceso = new Process([PHP_BINARY, base_path('tests/Support/funcionamiento_cotidiano_worker.php'), $contexto, (string) $i], base_path());
                $proceso->setTimeout(40)->start(); $procesos[] = $proceso;
            }
            $limite = microtime(true) + 20;
            while ((! is_file($dir.'/listo-1') || ! is_file($dir.'/listo-2')) && microtime(true) < $limite) { clearstatcache(); usleep(10000); }
            self::assertFileExists($dir.'/listo-1'); self::assertFileExists($dir.'/listo-2');
            flock($barrera, LOCK_UN);
            $salidas = [];
            foreach ($procesos as $proceso) {
                self::assertSame(0, $proceso->wait(), $proceso->getErrorOutput());
                $salidas[] = json_decode($proceso->getOutput(), true, flags: JSON_THROW_ON_ERROR)['estado'];
            }
            sort($salidas);
            self::assertSame($operacion === 'reintento' ? ['OK', 'OK'] : ['OK', 'RECHAZADO'], $salidas);
            self::assertDatabaseCount('controles_funcionales_iadl', $operacion === 'reintento' ? 1 : 2);
            if ($anterior !== null) self::assertSame(1, Iadl::query()->where('cod_control_anterior', $anterior)->count());
        } finally {
            flock($barrera, LOCK_UN); fclose($barrera);
            foreach ($procesos as $proceso) { if ($proceso->isRunning()) $proceso->stop(); }
            // Borrar solo los archivos exactos propios; no hay borrado recursivo ni ruta calculada ajena.
            foreach (['contexto.json', 'barrera', 'listo-1', 'listo-2'] as $nombre) { if (is_file($dir.'/'.$nombre)) unlink($dir.'/'.$nombre); }
            rmdir($dir);
        }
    }
}
