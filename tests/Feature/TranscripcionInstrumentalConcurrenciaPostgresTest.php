<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AplicacionInstrumento;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\DocumentoClinico;
use App\Models\Instrumento;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\PreguntaInstrumento;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Valoraciones\Aplicacion\Acciones\AplicarInstrumento;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Process\Process;
use Tests\Support\EntornoPruebas;
use Tests\TestCase;

#[Group('postgresql')]
final class TranscripcionInstrumentalConcurrenciaPostgresTest extends TestCase
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
        return [['original'], ['rectificacion']];
    }

    #[DataProvider('operaciones')]
    public function test_dos_transcriptores_no_duplican_fuente_ni_sucesora(string $operacion): void
    {
        $db = DB::connection();
        if ($db->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Requiere dos conexiones PostgreSQL del runner desechable.');
        }
        if (! app()->environment('testing') || ! EntornoPruebas::postgresDesechable($db->getDatabaseName(), $db->getConfig('username'), $db->getConfig('host'))) {
            throw new \RuntimeException('Esta prueba no se ejecuta contra una BDD operativa.');
        }
        $this->desechable = true;
        DB::statement('DROP SCHEMA public CASCADE');
        DB::statement('CREATE SCHEMA public');
        $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
        $this->seed(RolesAndPermissionsSeeder::class);
        $area = Area::create(['nombre' => 'Área sintética', 'estado' => 'ACTIVA']);
        $turno = Turno::create(['nombre' => 'Turno sintético', 'hora_inicio' => '00:00', 'hora_cierre' => '23:59', 'orden' => 1, 'estado' => 'ACTIVO']);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        $usuarios = [];
        foreach ([1, 2] as $i) {
            $u = User::factory()->create(['estado' => 'ACTIVO']);
            $u->assignRole('PSICOLOGO/A');
            $p = Personal::create(['cod_usuario' => $u->getKey(), 'nombres' => 'Profesional', 'apellido_paterno' => 'Concurrente '.$i,
                'numero_documento' => 'TRANS-RACE-'.$i, 'profesion' => 'PSICOLOGIA', 'estado' => 'ACTIVO']);
            AsignacionPersonal::create(['cod_personal' => $p->getKey(), 'cod_jornada' => $jornada->getKey(), 'cod_area' => $area->getKey(), 'fecha_asignacion' => now(), 'estado' => 'ACTIVA']);
            $usuarios[$i] = $u->getKey();
        }
        $residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $inst = Instrumento::create(['codigo' => 'RACE-SINTETICO', 'nombre' => 'Captura sintética', 'tipo' => 'COGNITIVO', 'version' => 'TEST-1', 'estado' => 'ACTIVO']);
        $q = PreguntaInstrumento::create(['cod_instrumento' => $inst->getKey(), 'codigo' => 'Q-RACE', 'enunciado' => 'Medida sintética', 'tipo_respuesta' => 'NUMERICO', 'orden' => 1, 'estado' => 'ACTIVA']);
        $dir = sys_get_temp_dir().'/remembermind-transcripcion-race-'.bin2hex(random_bytes(8));
        File::makeDirectory($dir, 0700, true);
        config(['filesystems.disks.fuente_race' => ['driver' => 'local', 'root' => $dir]]);
        // Ambos procesos leen el mismo original sintético, nunca el disco clínico.
        config(['filesystems.disks.local' => ['driver' => 'local', 'root' => $dir]]);
        Storage::forgetDisk('local');
        Storage::disk('local')->put('original.txt', 'Protocolo original sintético.');
        $autor = Personal::query()->where('cod_usuario', $usuarios[1])->sole();
        $doc = DocumentoClinico::create(['cod_residente' => $residente->getKey(), 'cod_personal' => $autor->getKey(), 'tipo_documento' => 'INFORME',
            'titulo' => 'Original sintético', 'ruta_archivo' => 'original.txt', 'formato' => 'text/plain', 'hash_archivo' => hash('sha256', 'Protocolo original sintético.'), 'fecha_hora' => now(), 'estado' => 'VIGENTE']);
        $entrada = ['cod_personal_original' => $autor->getKey(), 'cod_documento_clinico' => $doc->getKey(), 'localizador_fuente' => 'p1/f1',
            'original_cotejado' => true, 'fecha_hora' => now()->subDay()->format('Y-m-d H:i:s'), 'respuestas' => [['cod_pregunta' => $q->getKey(), 'valor_numero' => 1]]];
        if ($operacion === 'rectificacion') {
            $this->actingAs(User::query()->findOrFail($usuarios[1]));
            $anterior = app(AplicarInstrumento::class)->transcribir(auth()->user(), $entrada, $inst, $residente);
            $entrada['cod_aplicacion_anterior'] = $anterior->getKey();
            $entrada['motivo_rectificacion'] = 'Corrección concurrente cotejada con original sintético.';
        }
        $contexto = $dir.'/contexto.json';
        file_put_contents($contexto, json_encode(['conexion' => $db->getConfig(), 'usuarios' => $usuarios, 'directorio' => $dir,
            'instrumento' => $inst->getKey(), 'residente' => $residente->getKey(), 'entrada' => $entrada, 'operacion' => $operacion], JSON_THROW_ON_ERROR));
        $barrera = fopen($dir.'/barrera', 'c+');
        flock($barrera, LOCK_EX);
        $procesos = [];
        try {
            foreach ([1, 2] as $i) {
                $p = new Process([PHP_BINARY, base_path('tests/Support/transcripcion_instrumental_worker.php'), $contexto, (string) $i], base_path());
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
            if ($operacion === 'original') {
                self::assertSame($salidas[0]['codigo'], $salidas[1]['codigo']);
                self::assertDatabaseCount('aplicaciones_instrumento', 1);
                self::assertDatabaseCount('respuestas_instrumento', 1);
            } else {
                $estados = array_column($salidas, 'estado');
                sort($estados);
                self::assertSame(['conflicto', 'guardado'], $estados);
                self::assertDatabaseCount('aplicaciones_instrumento', 2);
                self::assertDatabaseCount('respuestas_instrumento', 2);
                self::assertSame('ANULADA', $anterior->fresh()->estado);
                self::assertEquals(1, $anterior->respuestas()->sole()->valor_numero);
                self::assertSame(1, AplicacionInstrumento::query()->where('cod_aplicacion_anterior', $anterior->getKey())->count());
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
                && preg_match('/^remembermind-transcripcion-race-[a-f0-9]{16}$/D', basename($dir))) {
                File::deleteDirectory($dir);
            }
        }
    }
}
