<?php

namespace Tests\Feature;

use App\Models\Documento;
use App\Models\Personal;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Process\Process;
use Tests\Support\EntornoPruebas;
use Tests\TestCase;

#[Group('postgresql')]
class GerenciaAltaSustitucionConcurrentePostgresTest extends TestCase
{
    public static function carreras(): array
    {
        return ['alta_identica' => ['alta_personal', 'ambos', false], 'alta_correo_duplicado' => ['alta_personal', 'correo', false],
            'alta_documento_duplicado' => ['alta_personal', 'numero_documento', false],
            'sustitucion_original' => ['sustituir_documento', 'file', true], 'primera_carga' => ['sustituir_documento', 'file', false],
            'alta_con_cuenta_suspendida_durante_espera' => ['alta_personal', 'ambos', false, 'cuenta'],
            'alta_con_rol_revocado_durante_espera' => ['alta_personal', 'ambos', false, 'rol'],
            'alta_con_permiso_crear_revocado' => ['alta_personal', 'ambos', false, 'crear'],
            'alta_con_permiso_documental_revocado' => ['alta_personal', 'ambos', false, 'subir']];
    }

    #[DataProvider('carreras')]
    public function test_solicitudes_simultaneas_conservan_una_alta_o_un_sucesor(string $modo, string $duplicado, bool $conOriginal, ?string $revocacion = null): void
    {
        $db = DB::connection();
        if ($db->getDriverName() !== 'pgsql') $this->markTestSkipped('Requiere PostgreSQL desechable.');
        if (! app()->environment('testing') || ! EntornoPruebas::postgresDesechable($db->getDatabaseName(), $db->getConfig('username'), $db->getConfig('host'))) {
            throw new \RuntimeException('Destino ajeno al runner de concurrencia.');
        }
        $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
        (require base_path('database/propuestas/gerencia/2026_10_26_000001_documentacion_y_estado_laboral.php'))->up();
        $this->seed(RolesAndPermissionsSeeder::class);
        $actor = User::factory()->create();
        $actor->assignRole('GERENTE');
        if ($modo === 'sustituir_documento') $actor->givePermissionTo('documentos.subir');
        if (in_array($revocacion, ['crear', 'subir'], true)) {
            $actor->syncRoles([]);
            $actor->givePermissionTo(['personal.gestionar', 'usuarios.crear', 'documentos.subir']);
        }
        $root = sys_get_temp_dir().'/remembermind-gerencia-race-'.bin2hex(random_bytes(16));
        File::makeDirectory($root);
        file_put_contents($root.'/entrada.pdf', "%PDF-1.4\n1 0 obj <</Type/Catalog>> endobj\n%%EOF");
        $fotoSintetica = UploadedFile::fake()->image('foto.jpg');
        file_put_contents($root.'/foto.jpg', file_get_contents($fotoSintetica->getRealPath()));
        $privado = $root.'/storage/app/private';
        config(['filesystems.disks.local.root' => $privado, 'filesystems.disks.public.root' => $root.'/storage/app/public']);
        Storage::forgetDisk('local');
        Storage::forgetDisk('public');
        $nombreAplicacion = 'rm_gerencia_12_'.bin2hex(random_bytes(6));
        $datos = ['modo' => $modo, 'actor' => $actor->getKey(), 'aplicacion' => $nombreAplicacion, 'identificar_workers' => true];
        $original = null;
        if ($modo === 'alta_personal') {
            $entrada = ['esEdicion' => false, 'correo' => 'alta@example.test', 'numero_documento' => '95678910',
                'nombres' => 'Persona', 'ap_paterno' => 'Sintética', 'expedido' => 'LP', 'fecha_nacimiento' => '1990-01-01',
                'genero' => 'F', 'telefono' => '77712345', 'direccion' => 'Dirección de prueba', 'roles_seleccionados' => ['ADMINISTRADOR']];
            $segunda = $entrada;
            if ($duplicado === 'correo') $segunda['numero_documento'] = '95678911';
            if ($duplicado === 'numero_documento') $segunda['correo'] = 'otra-alta@example.test';
            $datos['entradas'] = [$entrada, $segunda];
        } else {
            $titular = User::factory()->create();
            $titular->assignRole('ADMINISTRADOR');
            Personal::create(['cod_usuario' => $titular->getKey(), 'nombres' => 'Titular', 'apellido_paterno' => 'Sintético',
                'numero_documento' => '95678912', 'profesion' => 'ADMINISTRADOR', 'estado' => 'ACTIVO']);
            if ($conOriginal) {
                $ruta = 'documentos_personal_privados/'.$titular->getKey().'/original.pdf';
                Storage::disk('local')->put($ruta, file_get_contents($root.'/entrada.pdf'));
                $original = Documento::create(['cod_usuario' => $titular->getKey(), 'tipo_documento' => 'CI', 'nombre' => 'Original sintético',
                    'ruta_archivo' => $ruta, 'tipo_archivo' => 'application/pdf', 'hash_archivo' => hash_file('sha256', $root.'/entrada.pdf'),
                    'estado' => 'OBSERVADO', 'cod_usuario_carga' => $actor->getKey(), 'fecha_hora_carga' => now()]);
            }
            $datos += ['titular' => $titular->getKey(), 'referencia' => ['cod_usuario' => $titular->getKey(), 'tipo_documento' => 'CI', 'cod_documento' => $original?->getKey()]];
        }
        file_put_contents($root.'/datos.json', json_encode($datos, JSON_THROW_ON_ERROR));
        $env = ['APP_ENV' => 'testing', 'APP_CONFIG_CACHE' => $root.'/config.php', 'APP_ROUTES_CACHE' => $root.'/routes.php',
            'DB_CONNECTION' => 'pgsql', 'DB_URL' => '', 'DB_HOST' => $db->getConfig('host'), 'DB_PORT' => (string) $db->getConfig('port'),
            'DB_DATABASE' => $db->getDatabaseName(), 'DB_USERNAME' => $db->getConfig('username'), 'DB_PASSWORD' => $db->getConfig('password'),
            'LARAVEL_STORAGE_PATH' => $root.'/storage', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'MAIL_MAILER' => 'array'];
        $procesos = [];
        $cuentasAntes = User::count();
        $personasAntes = Personal::count();
        try {
            DB::beginTransaction();
            if ($modo === 'alta_personal') {
                DB::table('secuencias_codigos')->where('entidad', 'usuarios')->lockForUpdate()->first();
            } else {
                DB::table('usuarios')->where('cod_usuario', $datos['titular'])->lockForUpdate()->first();
            }
            foreach (['1', '2'] as $numero) {
                $p = new Process([PHP_BINARY, base_path('tests/Support/GerenciaConcurrenteWorker.php'), $root, $numero], base_path(), $env, timeout: 45);
                $p->start();
                $procesos[] = $p;
            }
            $limite = microtime(true) + 25;
            do {
                DB::select('select pg_stat_clear_snapshot()');
                $esperando = (int) DB::scalar("select count(*) from pg_stat_activity where datname=current_database() and application_name in (?,?) and wait_event_type='Lock'", [$nombreAplicacion.'_1', $nombreAplicacion.'_2']);
                if ($esperando === 2 || collect($procesos)->contains(fn ($p) => ! $p->isRunning())) break;
                usleep(20000);
            } while (microtime(true) < $limite);
            $this->assertSame(2, $esperando, 'Ambas solicitudes deben esperar el bloqueo real antes de liberarlo. '.implode(' ', array_map(fn ($p) => $p->getOutput().$p->getErrorOutput(), $procesos)));
            if ($revocacion === 'cuenta') DB::table('usuarios')->where('cod_usuario', $actor->getKey())->update(['estado' => 'INACTIVO']);
            if ($revocacion === 'rol') $actor->syncRoles(['FAMILIAR']);
            if ($revocacion === 'crear') $actor->revokePermissionTo('usuarios.crear');
            if ($revocacion === 'subir') $actor->revokePermissionTo('documentos.subir');
            DB::commit();
            $salidas = [];
            foreach ($procesos as $p) {
                $p->wait();
                $this->assertTrue($p->isSuccessful(), $p->getErrorOutput());
                $salidas[] = json_decode($p->getOutput(), true, flags: JSON_THROW_ON_ERROR);
            }
            if ($revocacion !== null) {
                $this->assertSame(['DENEGADO', 'DENEGADO'], array_column($salidas, 'estado'));
                $this->assertSame($cuentasAntes, User::count());
                $this->assertSame($personasAntes, Personal::count());
                $this->assertDatabaseCount('documentos', 0);
                $this->assertSame([], Storage::disk('local')->allFiles());
                $this->assertSame([], Storage::disk('public')->allFiles());
                $this->assertSame(0, array_sum(array_column($salidas, 'bienvenidas')));
                $this->assertSame(0, DB::table('activity_log')->where('event', 'personal')->count());

                return;
            }
            $this->assertEqualsCanonicalizing(['CONFIRMADO', 'RECHAZADO'], array_column($salidas, 'estado'));
            $rechazado = collect($salidas)->firstWhere('estado', 'RECHAZADO');
            foreach ($duplicado === 'ambos' ? ['correo', 'numero_documento'] : [$duplicado] as $campo) {
                $this->assertContains($campo, $rechazado['campos'], 'El conflicto debe identificar el campo que requiere revisión.');
            }
            if ($modo === 'alta_personal') {
                $this->assertSame($cuentasAntes + 1, User::count());
                $this->assertSame($personasAntes + 1, Personal::count());
                $this->assertDatabaseCount('documentos', 3);
                $this->assertSame(1, array_sum(array_column($salidas, 'bienvenidas')));
                $this->assertSame(1, DB::table('activity_log')->where('event', 'personal')->count());
                $ganador = User::findOrFail(collect($salidas)->firstWhere('estado', 'CONFIRMADO')['codigo']);
                $this->assertTrue($ganador->cambio_password_pendiente);
                $this->assertNotNull($ganador->fecha_hora_alta);
                $this->assertSame($actor->getKey(), Documento::first()->cod_usuario_carga);
            } else {
                $this->assertSame($cuentasAntes, User::count());
                $this->assertSame($personasAntes, Personal::count());
                $this->assertDatabaseCount('documentos', $conOriginal ? 2 : 1);
                $this->assertSame(1, Documento::where('cod_usuario', $datos['titular'])->where('tipo_documento', 'CI')->where('estado', 'CARGADO')->count());
                $sucesor = Documento::findOrFail(collect($salidas)->firstWhere('estado', 'CONFIRMADO')['codigo']);
                $this->assertSame($original?->getKey(), $sucesor->cod_documento_anterior);
                $this->assertSame($actor->getKey(), $sucesor->cod_usuario_carga);
                if ($original) $this->assertSame('REEMPLAZADO', $original->fresh()->estado);
                $this->assertSame(1, DB::table('activity_log')->where('event', 'documentacion')->count());
            }
            $guardados = Documento::pluck('ruta_archivo')->all();
            $this->assertEqualsCanonicalizing($guardados, Storage::disk('local')->allFiles());
            $this->assertSame([], Storage::disk('public')->allFiles());
            foreach (Documento::all() as $documento) {
                $this->assertSame($documento->hash_archivo, hash_file('sha256', Storage::disk('local')->path($documento->ruta_archivo)));
            }
        } finally {
            if (DB::transactionLevel() > 0) DB::rollBack();
            foreach ($procesos as $p) if ($p->isRunning()) $p->stop();
            RefreshDatabaseState::$migrated = false;
            $real = realpath($root);
            if ($real && dirname($real) === realpath(sys_get_temp_dir()) && preg_match('/^remembermind-gerencia-race-[a-f0-9]{32}$/D', basename($real)) && ! is_link($real)) File::deleteDirectory($real);
        }
    }
}
