<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ControlCognitivo;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarObservacionesConservadas;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\ConocimientoObservacional;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Process\Process;
use Tests\Support\EntornoPruebas;
use Tests\Support\InformeCognitivoFixture;
use Tests\TestCase;

#[Group('postgresql')]
final class GobiernoReglasConcurrenciaPostgresTest extends TestCase
{
    private bool $baseDesechableVerificada = false;

    protected function tearDown(): void
    {
        if ($this->baseDesechableVerificada) {
            // migrate:fresh elimina tablas pero deja funciones PostgreSQL; este esquema es exclusivo del runner.
            DB::statement('DROP SCHEMA public CASCADE');
            DB::statement('CREATE SCHEMA public');
        }
        RefreshDatabaseState::$migrated = false;
        parent::tearDown();
    }

    public static function carreras(): array
    {
        return [['revision'], ['sucesion'], ['mixta'], ['ejecucion_sucesion'], ['ejecucion_rechazo']];
    }

    #[DataProvider('carreras')]
    public function test_gobierno_serializa_decisiones_y_sucesoras_sin_perder_historia(string $operacion): void
    {
        $db = DB::connection();
        if ($db->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Las carreras de gobierno requieren PostgreSQL desechable.');
        }
        if (! app()->environment('testing') || ! EntornoPruebas::postgresDesechable($db->getDatabaseName(), $db->getConfig('username'), $db->getConfig('host'))) {
            throw new \RuntimeException('La carrera no se ejecuta fuera del runner aislado.');
        }
        $this->baseDesechableVerificada = true;
        DB::statement('DROP SCHEMA public CASCADE');
        DB::statement('CREATE SCHEMA public');
        $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
        $this->seed(RolesAndPermissionsSeeder::class);
        $usuarios = [];
        foreach ([1, 2] as $i) {
            $u = User::factory()->create(['estado' => 'ACTIVO']);
            $u->assignRole('MEDICO GENERAL/GERIATRA');
            Personal::create(['cod_usuario' => $u->getKey(), 'nombres' => 'Profesional', 'apellido_paterno' => 'Concurrente '.$i,
                'numero_documento' => 'GOB-CONC-'.$i, 'profesion' => 'MEDICINA', 'estado' => 'ACTIVO']);
            $usuarios[$i] = $u->getKey();
        }
        $this->actingAs(User::query()->findOrFail($usuarios[1]));
        $contexto = [];
        $observacional = str_starts_with($operacion, 'ejecucion_');
        if ($observacional) {
            $residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
            $personal = Personal::query()->where('cod_usuario', $usuarios[1])->sole();
            ControlCognitivo::create(['cod_residente' => $residente->getKey(), 'cod_personal' => $personal->getKey(),
                'fecha_hora' => now()->subHour(), 'estado' => 'VIGENTE', 'repite_preguntas' => true,
                'olvida_indicaciones' => false, 'cambio_cognitivo' => null]);
            $paquete = app(ConocimientoObservacional::class)->preparar(auth()->user());
            $contexto = ['residente' => $residente->getKey(), 'version' => $paquete['version'], 'regla' => $paquete['reglas']['OBS-MEM-01']['regla']];
        } else {
            InformeCognitivoFixture::conocimiento(auth()->user());
        }
        $directorio = sys_get_temp_dir().'/remembermind-gobierno-race-'.bin2hex(random_bytes(8));
        File::makeDirectory($directorio, 0700, true);
        $archivo = $directorio.'/contexto.json';
        file_put_contents($archivo, json_encode(['conexion' => $db->getConfig(), 'usuarios' => $usuarios,
            'directorio' => $directorio, 'operacion' => $operacion] + $contexto, JSON_THROW_ON_ERROR));
        $barrera = fopen($directorio.'/barrera', 'c+');
        flock($barrera, LOCK_EX);
        $procesos = [];
        try {
            foreach ([1, 2] as $i) {
                $p = new Process([PHP_BINARY, base_path('tests/Support/gobierno_reglas_concurrencia_worker.php'), $archivo, (string) $i], base_path());
                $p->setTimeout(35)->start();
                $procesos[] = $p;
            }
            $limite = microtime(true) + 20;
            while ((! is_file($directorio.'/listo-1') || ! is_file($directorio.'/listo-2')) && microtime(true) < $limite) {
                clearstatcache();
                usleep(10000);
            }
            self::assertFileExists($directorio.'/listo-1');
            self::assertFileExists($directorio.'/listo-2');
            flock($barrera, LOCK_UN);
            $salidas = [];
            foreach ($procesos as $p) {
                self::assertSame(0, $p->wait(), $p->getErrorOutput());
                $salidas[] = json_decode($p->getOutput(), true, flags: JSON_THROW_ON_ERROR)['estado'];
            }
            sort($salidas);
            if (! $observacional && $operacion !== 'mixta') {
                self::assertSame(['conflicto', 'guardado'], $salidas);
            }
            $firmas = DB::table('activity_log')->where('event', 'regla_revisada')->count();
            $sucesoras = DB::table('versiones_modelo_experto')->where('cod_version_anterior', $contexto['version'] ?? 'VER_TEST')->count();
            self::assertSame(in_array($operacion, ['revision', 'ejecucion_rechazo'], true) ? 0 : 1, $sucesoras);
            self::assertLessThanOrEqual(1, $firmas);
            if ($observacional) {
                self::assertSame(['guardado', 'guardado'], $salidas);
                self::assertDatabaseCount('evaluaciones_expertas', 1);
                $evaluacion = DB::table('evaluaciones_expertas')->sole();
                $resultado = app(ConsultarObservacionesConservadas::class)->consultar(auth()->user(), $residente, $evaluacion->cod_evaluacion_experta);
                self::assertSame('OBS-SNAP-2', $evaluacion->version_conservacion);
                if ($evaluacion->cod_version_modelo !== $contexto['version'] || $operacion === 'ejecucion_rechazo') {
                    self::assertContains(count($resultado['resumen']), [2, 3]);
                } else {
                    self::assertCount(3, $resultado['resumen']);
                }
                self::assertDatabaseCount('hechos_instantanea_experta', 4);
                self::assertDatabaseCount('evidencias_evaluacion', 3);
            } else {
                self::assertSame(count(array_filter($salidas, fn ($s) => $s === 'guardado')), $firmas + $sucesoras);
                self::assertSame(1, DB::table('reglas_expertas')->where('cod_regla_experta', 'RULE_A')->where('estado', 'ACTIVO')->count());
            }
        } finally {
            flock($barrera, LOCK_UN);
            fclose($barrera);
            foreach ($procesos as $p) {
                if ($p->isRunning()) {
                    $p->stop(1);
                }
            }
            if (dirname(realpath($directorio)) === realpath(sys_get_temp_dir()) && ! is_link($directorio)
                && preg_match('/^remembermind-gobierno-race-[a-f0-9]{16}$/D', basename($directorio))) {
                File::deleteDirectory($directorio);
            }
        }
    }
}
