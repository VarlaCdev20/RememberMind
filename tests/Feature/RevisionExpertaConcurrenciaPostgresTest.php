<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Personal;
use App\Models\Residente;
use App\Models\RevisionEvaluacionExperta;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Process\Process;
use Tests\Support\EntornoPruebas;
use Tests\Support\InformeCognitivoFixture as F;
use Tests\TestCase;

#[Group('postgresql')]
final class RevisionExpertaConcurrenciaPostgresTest extends TestCase
{
    protected function tearDown(): void
    {
        RefreshDatabaseState::$migrated = false;
        parent::tearDown();
    }

    public function test_dos_profesionales_concurrentes_no_crean_dos_firmas_iniciales_de_la_misma_disciplina(): void
    {
        $db = DB::connection();
        if ($db->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('La carrera de firmas requiere PostgreSQL desechable.');
        }
        if (! app()->environment('testing') || ! EntornoPruebas::postgresDesechable($db->getDatabaseName(), $db->getConfig('username'), $db->getConfig('host'))) {
            throw new \RuntimeException('No se ejecuta la carrera fuera de la base aislada del runner.');
        }
        $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
        $this->seed(RolesAndPermissionsSeeder::class);
        $usuarios = [];
        foreach ([1, 2] as $i) {
            $u = User::factory()->create(['estado' => 'ACTIVO']);
            $u->assignRole('MEDICO GENERAL/GERIATRA');
            Personal::create(['cod_usuario' => $u->getKey(), 'nombres' => 'Profesional', 'apellido_paterno' => 'Concurrente '.$i,
                'numero_documento' => 'REV-CONC-'.$i, 'profesion' => 'MEDICINA', 'estado' => 'ACTIVO']);
            $usuarios[$i] = $u;
        }
        $this->actingAs($usuarios[1]);
        $residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        F::conocimiento($usuarios[1]);
        F::evaluacion($residente, $usuarios[1]->personal, 'REV');
        DB::table('versiones_modelo_experto')->where('cod_version_modelo', 'VER_TEST')->update(['codigo_version' => 'VAL-REVISION-01', 'estado' => 'ACTIVO']);
        DB::table('evaluaciones_expertas')->where('cod_evaluacion_experta', 'E_REV')->update(['origen_activacion' => 'MANUAL', 'estado_ejecucion' => 'FINALIZADA']);
        DB::table('trazas_inferencia')->where('cod_traza_inferencia', 'TR_REV')->update(['estado_inferencia' => 'FINALIZADA']);
        DB::table('evidencias_criterio_evaluacion')->update(['estado_participacion' => 'UTILIZADA']);
        $c = config('sistema_experto.lectura_clinica.VER_TEST');
        $c['estados_ejecucion'] = $c['estados_inferencia'] = ['FINALIZADA'];
        $c['estados_participacion_soporte'] = ['UTILIZADA'];
        $c['revision_profesional'] = true;
        $directorio = sys_get_temp_dir().'/remembermind-revision-race-'.bin2hex(random_bytes(8));
        File::makeDirectory($directorio, 0700, true);
        $archivo = $directorio.'/contexto.json';
        file_put_contents($archivo, json_encode(['conexion' => $db->getConfig(), 'usuarios' => array_map(fn ($u) => $u->getKey(), $usuarios),
            'contrato' => $c, 'directorio' => $directorio], JSON_THROW_ON_ERROR));
        $barrera = fopen($directorio.'/barrera', 'c+');
        flock($barrera, LOCK_EX);
        $procesos = [];
        try {
            foreach ([1, 2] as $i) {
                $p = new Process([PHP_BINARY, base_path('tests/Support/revision_experta_concurrencia_worker.php'), $archivo, (string) $i], base_path());
                $p->setTimeout(30)->start();
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
            self::assertSame(['guardado', 'rechazado'], $salidas);
            self::assertSame(1, RevisionEvaluacionExperta::count());
            self::assertSame(1, DB::table('activity_log')->where('log_name', 'SistemaExperto')->count());
        } finally {
            flock($barrera, LOCK_UN);
            fclose($barrera);
            foreach ($procesos as $p) {
                if ($p->isRunning()) {
                    $p->stop(1);
                }
            }
            if (dirname(realpath($directorio)) === realpath(sys_get_temp_dir()) && ! is_link($directorio)
                && preg_match('/^remembermind-revision-race-[a-f0-9]{16}$/D', basename($directorio))) {
                File::deleteDirectory($directorio);
            }
        }
    }
}
