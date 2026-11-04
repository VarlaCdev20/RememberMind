<?php

namespace Tests\Feature;

use App\Compartido\Infraestructura\Persistencia\GeneradorCodigo;
use App\Compartido\Infraestructura\Persistencia\RenumerarCodigos;
use App\Models\Personal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Group;
use Spatie\Permission\Models\Role;
use Tests\Support\EntornoPruebas;
use Tests\TestCase;

#[Group('postgresql')]
class RenumeracionCodigosPostgresTest extends TestCase
{
    private string $directorio;

    protected function setUp(): void
    {
        parent::setUp();
        $db = DB::connection();
        if ($db->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('La conversión exige PostgreSQL dedicado.');
        }
        if (! app()->environment('testing') || ! EntornoPruebas::postgresDesechable($db->getDatabaseName(), $db->getConfig('username'), $db->getConfig('host'))) {
            throw new \RuntimeException('No se usa una base ajena al runner desechable.');
        }
        $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
        $this->directorio = storage_path('framework/testing/codigos-'.bin2hex(random_bytes(8)));
        mkdir($this->directorio, 0700, true);
        $usuario = User::create(['cod_usuario' => 'USU_0001', 'correo' => 'renumeracion@example.test', 'contrasena' => bin2hex(random_bytes(16)), 'estado' => 'ACTIVO']);
        Personal::create(['cod_personal' => 'PER_0001', 'cod_usuario' => $usuario->getKey(), 'nombres' => 'Persona', 'apellido_paterno' => 'Sintética',
            'numero_documento' => 'IDENTIDAD-TEST', 'profesion' => 'PRUEBA_TECNICA', 'estado' => 'ACTIVO']);
        Role::findOrCreate('ROL_TECNICO_IDENTIDAD', 'web');
        $usuario->assignRole('ROL_TECNICO_IDENTIDAD');
        DB::table('activity_log')->insert(['log_name' => 'prueba_identidad', 'description' => 'Texto histórico USU_0001 conservado',
            'subject_type' => User::class, 'subject_id' => 'USU_0001', 'causer_type' => User::class, 'causer_id' => 'USU_0001',
            'properties' => json_encode(['attributes' => ['cod_usuario' => 'USU_0001', 'observacion' => 'USU_0001'], 'nota' => 'USU_0001'])]);
    }

    public function test_reidentifica_relaciones_y_auditoria_conservando_texto_y_roles(): void
    {
        DB::table('fotografias_institucionales')->insert([
            'cod_fotografia' => 'FGI_0001', 'cod_usuario' => 'USU_0001', 'titulo' => 'Fotografía sintética',
            'ruta_archivo' => 'galeria-institucional/prueba/imagen.webp', 'ruta_miniatura' => 'galeria-institucional/prueba/miniatura.webp',
            'tipo_archivo' => 'image/webp', 'tamanio_bytes' => 100, 'ancho' => 10, 'alto' => 10,
            'hash_archivo' => str_repeat('a', 64), 'estado' => 'ACTIVA', 'usar_en_bienvenida' => false,
            'fecha_registro' => now(), 'fecha_actualizacion' => now(),
        ]);
        $contratoAntes = DB::select("SELECT conname,pg_get_constraintdef(oid) AS definicion FROM pg_constraint WHERE contype='f' ORDER BY conname");
        $resultado = app(RenumerarCodigos::class)->ejecutar(DB::connection(), $this->directorio);
        $this->assertSame(95, $resultado['entidades']);
        $this->assertFalse($resultado['revertido']);
        $usuario = User::findOrFail('COD_USU_000001');
        $this->assertTrue($usuario->hasRole('ROL_TECNICO_IDENTIDAD'));
        $this->assertSame('COD_USU_000001', Personal::findOrFail('COD_PER_000001')->cod_usuario);
        $this->assertDatabaseHas('fotografias_institucionales', ['cod_fotografia' => 'COD_FGI_000001', 'cod_usuario' => 'COD_USU_000001']);
        $this->assertEquals($contratoAntes, DB::select("SELECT conname,pg_get_constraintdef(oid) AS definicion FROM pg_constraint WHERE contype='f' ORDER BY conname"));
        $log = DB::table('activity_log')->where('log_name', 'prueba_identidad')->sole();
        $props = json_decode($log->properties, true);
        $this->assertSame('COD_USU_000001', $props['attributes']['cod_usuario']);
        $this->assertSame('USU_0001', $props['attributes']['observacion']);
        $this->assertSame('USU_0001', $props['nota']);
        $this->assertSame('Texto histórico USU_0001 conservado', $log->description);
        $this->assertSame('COD_USU_000001', $log->subject_id);
        $this->assertSame('COD_USU_000002', app(GeneradorCodigo::class)->siguiente('usuarios'));
        $this->assertFileExists($resultado['manifest']);
        $this->assertFileExists($resultado['manifest'].'.resultado.json');
    }

    public function test_fallo_intermedio_revierte_contenido_y_definiciones_fk(): void
    {
        $db = DB::connection();
        $definiciones = $db->select("SELECT oid,pg_get_constraintdef(oid) AS definicion FROM pg_constraint WHERE contype='f' ORDER BY oid");
        foreach (['temporal', 'nuevo'] as $fase) {
            try {
                app(RenumerarCodigos::class)->ejecutar($db, $this->directorio, true, null, $fase);
                $this->fail('Debe propagarse el fallo inyectado.');
            } catch (\RuntimeException $e) {
                $this->assertSame('Fallo de ensayo en fase '.$fase, $e->getMessage());
            }
            $this->assertSame('USU_0001', Personal::findOrFail('PER_0001')->cod_usuario);
            $this->assertDatabaseHas('usuarios', ['cod_usuario' => 'USU_0001']);
            $this->assertDatabaseMissing('usuarios', ['cod_usuario' => 'COD_USU_000001']);
            $this->assertSame(serialize($definiciones), serialize($db->select("SELECT oid,pg_get_constraintdef(oid) AS definicion FROM pg_constraint WHERE contype='f' ORDER BY oid")));
        }
    }
}
