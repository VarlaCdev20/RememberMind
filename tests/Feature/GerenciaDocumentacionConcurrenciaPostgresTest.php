<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\GestionarSolicitudesDocumentales;
use App\Modulos\Social\Aplicacion\Acciones\GestionarRedApoyo;
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
class GerenciaDocumentacionConcurrenciaPostgresTest extends TestCase
{
    public static function operaciones(): array
    {
        return [['emitir'], ['prorrogar'], ['prorrogar_expediente'], ['laboral'], ['vinculo'], ['horario']];
    }

    #[DataProvider('operaciones')]
    public function test_confirmaciones_solapadas_no_duplican_solicitud_prorroga_o_historia(string $modo): void
    {
        $db = DB::connection();
        if ($db->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Requiere runner PostgreSQL desechable.');
        }
        if (! app()->environment('testing') || ! EntornoPruebas::postgresDesechable($db->getDatabaseName(), $db->getConfig('username'), $db->getConfig('host'))) {
            throw new \RuntimeException('Base ajena al runner.');
        }
        $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
        (require base_path('database/propuestas/gerencia/2026_10_26_000001_documentacion_y_estado_laboral.php'))->up();
        (require base_path('database/propuestas/gerencia/2026_10_26_000002_historia_vinculos_asignaciones.php'))->up();
        $this->seed(RolesAndPermissionsSeeder::class);
        $actor = User::factory()->create();
        $actor->assignRole('GERENTE');
        $trabajador = User::factory()->create();
        $trabajador->assignRole('ADMINISTRADOR');
        $trabajador->forceFill(['fecha_hora_alta' => now()])->save();
        $personal = Personal::create(['cod_usuario' => $trabajador->getKey(), 'nombres' => 'Persona', 'apellido_paterno' => 'Sintética', 'numero_documento' => '93456789', 'profesion' => 'ADMINISTRADOR', 'estado' => 'ACTIVO']);
        $entrada = ['rol' => 'ADMINISTRADOR', 'tipo_documento' => 'OTROS', 'nombre_documento' => 'Respaldo concurrente',
            'motivo' => 'Solicitud de prueba concurrente', 'personas' => [$personal->getKey()], 'origen_plazo' => 'ALTA', 'clave_solicitud' => (string) Str::uuid()];
        $datos = ['modo' => $modo, 'actor' => $actor->getKey(), 'personal' => $personal->getKey(), 'entrada' => $entrada];
        if ($modo === 'prorrogar') {
            $datos['destinatario'] = app(GestionarSolicitudesDocumentales::class)->emitir($actor, $entrada)->destinatarios->sole()->getKey();
        }
        if ($modo === 'prorrogar_expediente') {
            $gestion = app(GestionarSolicitudesDocumentales::class);
            $datos['destinatarios'] = [$gestion->emitir($actor, $entrada)->destinatarios->sole()->getKey(),
                $gestion->emitir($actor, [...$entrada, 'clave_solicitud' => (string) Str::uuid()])->destinatarios->sole()->getKey()];
        }
        if ($modo === 'laboral') {
            $datos['entrada'] = ['estado_esperado' => 'ACTIVO', 'estado_nuevo' => 'INACTIVO', 'fecha_hora_efecto' => now()->toDateTimeString(), 'motivo' => 'Cambio laboral concurrente de prueba'];
        }
        if ($modo === 'vinculo') {
            $datos['residente'] = Residente::factory()->create(['estado' => 'ADMITIDO'])->getKey();
            $datos['contacto'] = app(GestionarRedApoyo::class)->guardarContacto($actor, ['nombres' => 'Contacto', 'apellido_paterno' => 'Sintético'])->getKey();
            $datos['entrada'] = ['parentesco' => 'Hija', 'responsable_principal' => false, 'contacto_emergencia' => false];
        }
        if ($modo === 'horario') {
            $area = Area::create(['nombre' => 'Área sintética', 'estado' => 'ACTIVO']);
            $turno = Turno::create(['nombre' => 'Mañana', 'hora_inicio' => '08:00:00', 'hora_cierre' => '16:00:00', 'estado' => 'ACTIVO']);
            $datos['entrada'] = ['usuarioId' => $trabajador->getKey(), 'f_cod_area' => $area->getKey(), 'f_cod_turno' => $turno->getKey(),
                'f_fecha_inicio' => '2026-10-26', 'f_dias_semana' => ['LUNES'], 'f_tipo_asignacion' => 'REGULAR',
                'f_observaciones' => 'Dos solicitudes del mismo intervalo', 'modoEdicion' => false];
        }
        $root = sys_get_temp_dir().'/remembermind-gerencia-race-'.bin2hex(random_bytes(16));
        File::makeDirectory($root);
        file_put_contents($root.'/datos.json', json_encode($datos, JSON_THROW_ON_ERROR));
        $env = ['APP_ENV' => 'testing', 'APP_CONFIG_CACHE' => $root.'/config.php', 'APP_ROUTES_CACHE' => $root.'/routes.php',
            'DB_CONNECTION' => 'pgsql', 'DB_URL' => '', 'DB_HOST' => $db->getConfig('host'), 'DB_PORT' => (string) $db->getConfig('port'),
            'DB_DATABASE' => $db->getDatabaseName(), 'DB_USERNAME' => $db->getConfig('username'), 'DB_PASSWORD' => $db->getConfig('password'),
            'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array'];
        $procesos = [];
        try {
            foreach (['1', '2'] as $numero) {
                $p = new Process([PHP_BINARY, base_path('tests/Support/GerenciaConcurrenteWorker.php'), $root, $numero], base_path(), $env, timeout: 35);
                $p->start();
                $procesos[] = $p;
            }
            $salidas = [];
            foreach ($procesos as $p) {
                $p->wait();
                $this->assertTrue($p->isSuccessful(), $p->getErrorOutput());
                $salidas[] = json_decode($p->getOutput(), true, flags: JSON_THROW_ON_ERROR);
            }
            if ($modo === 'emitir') {
                $this->assertSame($salidas[0]['codigo'], $salidas[1]['codigo']);
                $this->assertDatabaseCount('solicitudes_documentales_personal', 1);
                $this->assertDatabaseCount('destinatarios_solicitud_documental', 1);
            } else {
                $this->assertEqualsCanonicalizing(['CONFIRMADO', 'RECHAZADO'], array_column($salidas, 'estado'));
                if ($modo === 'laboral') {
                    $this->assertDatabaseCount('movimientos_laborales_personal', 1);
                    $this->assertSame('INACTIVO', $personal->fresh()->estado);
                    $this->assertSame('ACTIVO', $trabajador->fresh()->estado);
                } elseif ($modo === 'vinculo') {
                    $this->assertDatabaseCount('residentes_contactos', 1);
                    $this->assertDatabaseCount('versiones_vinculo_contacto', 1);
                } elseif ($modo === 'horario') {
                    $this->assertDatabaseCount('asignaciones_personal', 1);
                    $this->assertDatabaseCount('jornadas', 1);
                    $this->assertDatabaseCount('eventos_asignacion_personal', 1);
                } else {
                    $this->assertSame(1, DB::table('destinatarios_solicitud_documental')->whereNotNull('fecha_limite_prorrogada')->count());
                }
            }
        } finally {
            RefreshDatabaseState::$migrated = false;
            foreach ($procesos as $p) {
                if ($p->isRunning()) {
                    $p->stop();
                }
            }
            $real = realpath($root);
            if ($real && dirname($real) === realpath(sys_get_temp_dir()) && preg_match('/^remembermind-gerencia-race-[a-f0-9]{32}$/D', basename($real)) && ! is_link($real)) {
                File::deleteDirectory($real);
            }
        }
    }
}
