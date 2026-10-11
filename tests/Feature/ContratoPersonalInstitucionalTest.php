<?php

namespace Tests\Feature;

use App\Mail\PersonalInstitucionalBienvenidaMail;
use App\Models\Contacto;
use App\Models\Personal;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\GuardarPersonalInstitucional;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ContratoPersonalInstitucionalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Mail::fake();
        Role::findOrCreate('ADMINISTRADOR', 'web');
    }

    private function actor(bool $cuentas = true, string $estado = 'ACTIVO'): User
    {
        $actor = User::factory()->create(['estado' => $estado]);
        foreach (['personal.gestionar', ...($cuentas ? ['usuarios.crear', 'usuarios.editar', 'documentos.subir'] : [])] as $permiso) {
            Permission::findOrCreate($permiso, 'web');
            $actor->givePermissionTo($permiso);
        }

        return $actor;
    }

    private function entrada(): array
    {
        return ['esEdicion' => false, 'usuarioId' => null, 'correo' => 'nuevo-personal@example.test', 'contrasena_temporal' => 'ClaveSintetica#123',
            'nombres' => 'Ana', 'ap_paterno' => 'Flores', 'ap_materno' => 'Rojas', 'numero_documento' => '12345678', 'expedido' => 'LP',
            'fecha_nacimiento' => '1990-01-01', 'genero' => 'F', 'telefono' => '77712345', 'direccion' => 'Dirección sintética',
            'estado' => 'ACTIVO', 'roles_seleccionados' => ['ADMINISTRADOR'], 'matricula_prof' => 'MAT_SINTETICA',
            'archivos_temporales' => ['CI' => UploadedFile::fake()->create('ci.pdf', 10, 'application/pdf'),
                'FOTO_DOC' => UploadedFile::fake()->image('foto.jpg'), 'CV' => UploadedFile::fake()->create('cv.pdf', 10, 'application/pdf')]];
    }

    public function test_alta_usa_usuario_y_personal_canonicos_sin_crear_contactos_o_asignaciones(): void
    {
        $actor = $this->actor();
        $contactosAntes = Contacto::count();
        $resultado = app(GuardarPersonalInstitucional::class)->guardar($actor, $this->entrada());
        $usuario = $resultado['usuario'];
        $this->assertSame('nuevo-personal@example.test', $usuario->correo);
        $this->assertTrue(Hash::check('ClaveSintetica#123', $usuario->contrasena));
        $this->assertSame('ANA', $usuario->personal->nombres);
        $this->assertSame('FLORES', $usuario->personal->apellido_paterno);
        $this->assertSame('12345678', $usuario->personal->numero_documento);
        $this->assertSame('MAT_SINTETICA', $usuario->personal->matricula_profesional);
        $this->assertNull($usuario->personal->fecha_ingreso);
        $this->assertTrue($usuario->hasRole('ADMINISTRADOR'));
        $this->assertSame($contactosAntes, Contacto::count());
        $this->assertDatabaseCount('asignaciones_personal', 0);
        $this->assertDatabaseCount('documentos', 3);
        $this->assertArrayNotHasKey('nombres', $usuario->getAttributes());
        $this->assertArrayNotHasKey('created_at', $usuario->getAttributes());
        Mail::assertSent(PersonalInstitucionalBienvenidaMail::class, fn ($mail) => $mail->usuario->personal->numero_documento === '12345678');
        $this->assertTrue($resultado['correo_enviado']);
    }

    public function test_actor_nulo_o_inactivo_no_produce_efectos(): void
    {
        $inactivo = $this->actor(estado: 'INACTIVO');
        $usuariosAntes = User::count();
        foreach ([null, $inactivo] as $actor) {
            try {
                app(GuardarPersonalInstitucional::class)->guardar($actor, $this->entrada());
                $this->fail('Debe rechazarse el actor no autorizado.');
            } catch (HttpException $error) {
                $this->assertSame(403, $error->getStatusCode());
            }
        }
        $this->assertSame($usuariosAntes, User::count());
        $this->assertDatabaseCount('documentos', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
        Mail::assertNothingSent();
    }

    public function test_gestion_de_personal_no_concede_alta_de_cuentas(): void
    {
        $actor = $this->actor(cuentas: false);
        $antes = User::count();
        try {
            app(GuardarPersonalInstitucional::class)->guardar($actor, $this->entrada());
            $this->fail('La gestión laboral no concede alta de cuentas.');
        } catch (HttpException $error) {
            $this->assertSame(403, $error->getStatusCode());
        }
        $this->assertSame($antes, User::count());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_editar_identidad_laboral_preserva_cuenta_bloqueada_password_y_profesion(): void
    {
        $usuario = app(GuardarPersonalInstitucional::class)->guardar($this->actor(), $this->entrada())['usuario'];
        $usuario->update(['estado' => 'INACTIVO']);
        $usuario->personal->update(['profesion' => 'Profesión institucional existente', 'fecha_ingreso' => '2020-03-01']);
        $hash = $usuario->fresh()->contrasena;
        $entrada = [...$this->entrada(), 'esEdicion' => true, 'usuarioId' => $usuario->getKey(), 'estado' => 'INACTIVO',
            'nombres' => 'Elena', 'archivos_temporales' => []];
        $resultado = app(GuardarPersonalInstitucional::class)->guardar($this->actor(cuentas: false), $entrada)['usuario'];
        $this->assertSame('INACTIVO', $resultado->estado);
        $this->assertSame($hash, $resultado->contrasena);
        $this->assertSame('ELENA', $resultado->personal->nombres);
        $this->assertSame('Profesión institucional existente', $resultado->personal->profesion);
        $this->assertSame('2020-03-01', $resultado->personal->fecha_ingreso->toDateString());
        $this->assertSame('ACTIVO', $resultado->personal->estado);
    }

    public function test_role_manipulado_no_se_asigna(): void
    {
        $actor = $this->actor();
        Role::findOrCreate('SUPERADMINISTRADOR', 'web');
        $antes = User::count();
        try {
            app(GuardarPersonalInstitucional::class)->guardar($actor, [...$this->entrada(), 'roles_seleccionados' => ['SUPERADMINISTRADOR']]);
            $this->fail('No debe concederse Superadministración.');
        } catch (HttpException $error) {
            $this->assertSame(403, $error->getStatusCode());
        }
        $this->assertSame($antes, User::count());
        $this->assertFalse($actor->fresh()->hasRole('SUPERADMINISTRADOR'));
    }

    public function test_no_se_retira_el_rol_del_ultimo_superadministrador_activo(): void
    {
        Role::findOrCreate('SUPERADMINISTRADOR', 'web');
        $actor = $this->actor();
        $usuario = app(GuardarPersonalInstitucional::class)->guardar($actor, $this->entrada())['usuario'];
        $usuario->syncRoles(['SUPERADMINISTRADOR']);
        $antes = $usuario->personal->nombres;
        try {
            app(GuardarPersonalInstitucional::class)->guardar($actor, [...$this->entrada(), 'esEdicion' => true,
                'usuarioId' => $usuario->getKey(), 'nombres' => 'Cambio rechazado', 'archivos_temporales' => []]);
            $this->fail('Debe conservarse el último superadministrador activo.');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('roles_seleccionados', $error->errors());
        }
        $this->assertTrue($usuario->fresh()->hasRole('SUPERADMINISTRADOR'));
        $this->assertSame($antes, $usuario->personal->fresh()->nombres);
        $this->assertSame('ACTIVO', $usuario->fresh()->estado);
    }

    public function test_datos_incompletos_o_documentos_obligatorios_ausentes_no_crean_cuenta(): void
    {
        $actor = $this->actor();
        $antes = User::count();
        foreach ([['ap_paterno' => null], ['archivos_temporales' => []], ['roles_seleccionados' => 'ADMINISTRADOR']] as $invalido) {
            try {
                app(GuardarPersonalInstitucional::class)->guardar($actor, [...$this->entrada(), ...$invalido]);
                $this->fail('Debe rechazarse el registro incompleto.');
            } catch (ValidationException $error) {
                $this->assertNotEmpty($error->errors());
            }
        }
        $this->assertSame($antes, User::count());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_documento_de_identidad_duplicado_no_crea_otra_cuenta(): void
    {
        $actor = $this->actor();
        app(GuardarPersonalInstitucional::class)->guardar($actor, $this->entrada());
        $antes = User::count();
        $archivosAntes = Storage::disk('public')->allFiles();
        try {
            app(GuardarPersonalInstitucional::class)->guardar($actor, [...$this->entrada(), 'correo' => 'otra-cuenta@example.test']);
            $this->fail('Debe rechazarse la identidad duplicada.');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('numero_documento', $error->errors());
        }
        $this->assertSame($antes, User::count());
        $this->assertSame($archivosAntes, Storage::disk('public')->allFiles());
    }

    public function test_fallo_de_correo_no_finge_rollback_de_registro_confirmado(): void
    {
        $actor = $this->actor();
        Mail::shouldReceive('to')->once()->andThrow(new RuntimeException('Fallo sintético de correo.'));
        $resultado = app(GuardarPersonalInstitucional::class)->guardar($actor, $this->entrada());
        $this->assertFalse($resultado['correo_enviado']);
        $this->assertDatabaseHas('usuarios', ['cod_usuario' => $resultado['usuario']->getKey(), 'correo' => 'nuevo-personal@example.test']);
        $this->assertDatabaseHas('personal', ['cod_usuario' => $resultado['usuario']->getKey(), 'numero_documento' => '12345678']);
        $this->assertDatabaseCount('documentos', 3);
    }

    public function test_fallo_final_revierte_cuenta_personal_roles_documentos_y_archivos(): void
    {
        $actor = $this->actor();
        $usuariosAntes = User::count();
        $personalAntes = Personal::count();
        $dispatcher = Activity::getEventDispatcher();
        Activity::setEventDispatcher(clone $dispatcher);
        try {
            Activity::creating(fn () => throw new RuntimeException('Fallo sintético de registro.'));
            try {
                app(GuardarPersonalInstitucional::class)->guardar($actor, $this->entrada());
                $this->fail('Debe informarse el fallo y revertir.');
            } catch (RuntimeException $error) {
                $this->assertSame('Fallo sintético de registro.', $error->getMessage());
            }
        } finally {
            Activity::setEventDispatcher($dispatcher);
        }
        $this->assertSame($usuariosAntes, User::count());
        $this->assertSame($personalAntes, Personal::count());
        $this->assertDatabaseCount('documentos', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
        Mail::assertNothingSent();
    }
}
