<?php

namespace Tests\Feature;

use App\Frontend\Livewire\Compartido\Valoraciones\ValoracionInicialModal;
use App\Models\Personal;
use App\Models\Preadmision;
use App\Models\User;
use App\Models\ValoracionEnfermeriaPreadmision;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ValoracionEnfermeriaAutoriaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        }
        $this->seed(DatabaseSeeder::class);
    }

    private function crearPreadmision(string $estado = 'PENDIENTE'): Preadmision
    {
        $admin = User::where('correo', 'admincasaamandita@gmail.com')->firstOrFail();

        return Preadmision::create([
            'cod_preadmision' => 'PRE_'.strtoupper(Str::random(10)),
            'cod_usuario_registro' => $admin->cod_usuario,
            'nombres' => 'Juan',
            'apellido_paterno' => 'Perez',
            'fecha_nacimiento' => '1945-05-10',
            'motivo_ingreso' => 'Valoracion preadmision',
            'fecha_solicitud' => now(),
            'estado' => $estado,
        ]);
    }

    private function getValidFormData(): array
    {
        return [
            'estado_general' => 'ESTABLE',
            'nivel_conciencia' => 'ALERTA',
            'orientacion_persona' => 'ORIENTADO',
            'orientacion_tiempo' => 'ORIENTADO',
            'orientacion_espacio' => 'ORIENTADO',
            'movilidad' => 'AUTONOMO',
            'riesgo_caida' => 'BAJO',
            'pa_sistolica' => 120,
            'pa_diastolica' => 80,
            'frecuencia_cardiaca' => 72,
            'frecuencia_respiratoria' => 18,
            'temperatura' => 36.5,
            'saturacion_oxigeno' => 98,
            'dependencia_funcional' => 'INDEPENDIENTE',
            'riesgo_nutricional' => 'SIN RIESGO',
            'riesgo_cognitivo' => 'SIN DETERIORO',
            'prioridad_sugerida' => 'MEDIA',
            'recomendacion_enfermeria' => 'Paciente en condiciones optimas de ingreso asistido.',
            'confirmacion_documentacion' => true,
        ];
    }

    /**
     * 1. ENFERMEROS + permiso registrar + Personal activo: puede registrar.
     * 10. cod_usuario_registro: corresponde al usuario autenticado.
     * 11. cod_personal_valorador: corresponde exactamente al Personal del usuario.
     */
    public function test_1_10_11_enfermero_con_rol_permiso_y_personal_activo_registra_con_autoria_separada(): void
    {
        $enfermero = User::where('correo', 'enfermeria@remembermind.com')->firstOrFail();
        $personal = $enfermero->personal;
        $this->assertNotNull($personal);
        $this->assertSame('ACTIVO', $personal->estado);
        $this->assertTrue($enfermero->hasRole('ENFERMEROS'));
        $this->assertTrue($enfermero->can('valoracion_enfermeria.registrar'));

        $preadmision = $this->crearPreadmision('PENDIENTE');

        Livewire::actingAs($enfermero)
            ->test(ValoracionInicialModal::class)
            ->call('open', $preadmision->cod_preadmision)
            ->set($this->getValidFormData())
            ->call('guardar');

        $this->assertDatabaseHas('valoraciones_enfermeria_preadmision', [
            'cod_preadmision' => $preadmision->cod_preadmision,
            'cod_personal_valorador' => $personal->cod_personal,
            'cod_usuario_registro' => $enfermero->cod_usuario,
            'estado_general' => 'ESTABLE',
        ]);
    }

    /**
     * 2. Rol ENFERMEROS pero SIN permiso registrar: rechazado.
     */
    public function test_2_rol_enfermeros_sin_permiso_registrar_es_rechazado(): void
    {
        $enfermero = User::where('correo', 'enfermeria@remembermind.com')->firstOrFail();
        $role = Role::findByName('ENFERMEROS');
        $role->revokePermissionTo('valoracion_enfermeria.registrar');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->assertTrue($enfermero->hasRole('ENFERMEROS'));
        $this->assertFalse($enfermero->can('valoracion_enfermeria.registrar'));

        $preadmision = $this->crearPreadmision('PENDIENTE');

        Livewire::actingAs($enfermero)
            ->test(ValoracionInicialModal::class)
            ->call('open', $preadmision->cod_preadmision)
            ->set($this->getValidFormData())
            ->call('guardar')
            ->assertForbidden();

        $this->assertDatabaseMissing('valoraciones_enfermeria_preadmision', [
            'cod_preadmision' => $preadmision->cod_preadmision,
        ]);
    }

    /**
     * 3. Permiso registrar pero SIN rol ENFERMEROS: rechazado.
     */
    public function test_3_permiso_registrar_sin_rol_enfermeros_es_rechazado(): void
    {
        $usuario = User::create([
            'cod_usuario' => 'USU_SIN_ROL',
            'correo' => 'sin_rol@remembermind.com',
            'contrasena' => bcrypt('Secret123!'),
            'estado' => 'ACTIVO',
        ]);

        Personal::create([
            'cod_personal' => 'PER_SIN_ROL',
            'cod_usuario' => $usuario->cod_usuario,
            'nombres' => 'Sin',
            'apellido_paterno' => 'Rol',
            'numero_documento' => 'SR-001',
            'profesion' => 'OTRA',
            'fecha_ingreso' => now()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        $usuario->givePermissionTo('valoracion_enfermeria.registrar');

        $this->assertFalse($usuario->hasRole('ENFERMEROS'));
        $this->assertTrue($usuario->can('valoracion_enfermeria.registrar'));

        $preadmision = $this->crearPreadmision('PENDIENTE');

        Livewire::actingAs($usuario)
            ->test(ValoracionInicialModal::class)
            ->call('open', $preadmision->cod_preadmision)
            ->set($this->getValidFormData())
            ->call('guardar')
            ->assertForbidden();
    }

    /**
     * 4. Usuario sin Personal: rechazado.
     */
    public function test_4_usuario_sin_personal_es_rechazado(): void
    {
        $usuarioSinPersonal = User::create([
            'cod_usuario' => 'USU_NO_PER',
            'correo' => 'no_personal@remembermind.com',
            'contrasena' => bcrypt('Secret123!'),
            'estado' => 'ACTIVO',
        ]);
        $usuarioSinPersonal->assignRole('ENFERMEROS');

        $this->assertNull($usuarioSinPersonal->personal);

        $preadmision = $this->crearPreadmision('PENDIENTE');

        Livewire::actingAs($usuarioSinPersonal)
            ->test(ValoracionInicialModal::class)
            ->call('open', $preadmision->cod_preadmision)
            ->set($this->getValidFormData())
            ->call('guardar')
            ->assertForbidden();
    }

    /**
     * 5. Personal inactivo: rechazado.
     */
    public function test_5_personal_inactivo_es_rechazado(): void
    {
        $enfermero = User::where('correo', 'enfermeria@remembermind.com')->firstOrFail();
        $enfermero->personal->update(['estado' => 'INACTIVO']);

        $preadmision = $this->crearPreadmision('PENDIENTE');

        Livewire::actingAs($enfermero)
            ->test(ValoracionInicialModal::class)
            ->call('open', $preadmision->cod_preadmision)
            ->set($this->getValidFormData())
            ->call('guardar')
            ->assertForbidden();
    }

    /**
     * 6. ADMINISTRADOR: no registra.
     */
    public function test_6_administrador_no_registra(): void
    {
        $admin = User::create([
            'cod_usuario' => 'USU_ADMIN_ONLY',
            'correo' => 'admin_only@remembermind.com',
            'contrasena' => bcrypt('Secret123!'),
            'estado' => 'ACTIVO',
        ]);
        $admin->assignRole('ADMINISTRADOR');

        Personal::create([
            'cod_personal' => 'PER_ADMIN_ONLY',
            'cod_usuario' => $admin->cod_usuario,
            'nombres' => 'Admin',
            'apellido_paterno' => 'Puro',
            'numero_documento' => 'ADM-001',
            'profesion' => 'ADMINISTRACION',
            'fecha_ingreso' => now()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        $preadmision = $this->crearPreadmision('PENDIENTE');

        Livewire::actingAs($admin)
            ->test(ValoracionInicialModal::class)
            ->call('open', $preadmision->cod_preadmision)
            ->set($this->getValidFormData())
            ->call('guardar')
            ->assertForbidden();
    }

    /**
     * 7. Escritura temporal del superadministrador con autoría propia.
     */
    public function test_7_superadministrador_registra_con_su_personal_activo(): void
    {
        $superadmin = User::where('correo', 'carlaencinas78@gmail.com')->firstOrFail();

        $this->assertTrue($superadmin->can('valoracion_enfermeria.ver'), 'Superadmin conserva lectura.');
        $this->assertTrue($superadmin->can('valoracion_enfermeria.registrar'));

        $preadmision = $this->crearPreadmision('PENDIENTE');

        Livewire::actingAs($superadmin)
            ->test(ValoracionInicialModal::class)
            ->call('open', $preadmision->cod_preadmision)
            ->set($this->getValidFormData())
            ->call('guardar')
            ->assertOk();

        $this->assertDatabaseHas('valoraciones_enfermeria_preadmision', [
            'cod_preadmision' => $preadmision->cod_preadmision,
            'cod_personal_valorador' => $superadmin->personal->cod_personal,
            'cod_usuario_registro' => $superadmin->cod_usuario,
        ]);
    }

    /**
     * 8. La excepción temporal permite editar sin rol de Enfermería.
     */
    public function test_8_superadministrador_puede_editar_temporalmente(): void
    {
        $superadmin = User::where('correo', 'carlaencinas78@gmail.com')->firstOrFail();
        $this->assertTrue($superadmin->can('valoracion_enfermeria.editar'));

        $enfermero = User::where('correo', 'enfermeria@remembermind.com')->firstOrFail();
        $preadmision = $this->crearPreadmision('PENDIENTE');

        $valoracion = ValoracionEnfermeriaPreadmision::create([
            'cod_valoracion_enfermeria' => 'VEN_TEST_ED_01',
            'cod_preadmision' => $preadmision->cod_preadmision,
            'cod_personal_valorador' => $enfermero->personal->cod_personal,
            'cod_usuario_registro' => $enfermero->cod_usuario,
            'fecha_hora' => now(),
            'estado_general' => 'ESTABLE',
        ]);

        $this->assertTrue(Gate::forUser($superadmin)->allows('update', $valoracion));
    }

    /**
     * 9. delete: siempre rechazado.
     */
    public function test_9_delete_siempre_rechazado_por_politica(): void
    {
        $enfermero = User::where('correo', 'enfermeria@remembermind.com')->firstOrFail();
        $superadmin = User::where('correo', 'carlaencinas78@gmail.com')->firstOrFail();
        $preadmision = $this->crearPreadmision('PENDIENTE');

        $valoracion = ValoracionEnfermeriaPreadmision::create([
            'cod_valoracion_enfermeria' => 'VEN_TEST_DEL_01',
            'cod_preadmision' => $preadmision->cod_preadmision,
            'cod_personal_valorador' => $enfermero->personal->cod_personal,
            'cod_usuario_registro' => $enfermero->cod_usuario,
            'fecha_hora' => now(),
            'estado_general' => 'ESTABLE',
        ]);

        $this->assertFalse(Gate::forUser($enfermero)->allows('delete', $valoracion));
        $this->assertFalse(Gate::forUser($superadmin)->allows('delete', $valoracion));
        $this->assertFalse(Gate::forUser($enfermero)->allows('forceDelete', $valoracion));
        $this->assertFalse(Gate::forUser($superadmin)->allows('forceDelete', $valoracion));
    }

    /**
     * 12. No existe fallback automatico (no se crea Personal).
     */
    public function test_12_no_existe_fallback_automatico(): void
    {
        $usuarioSinPersonal = User::create([
            'cod_usuario' => 'USU_FALLBACK_CHK',
            'correo' => 'fallback_check@remembermind.com',
            'contrasena' => bcrypt('Secret123!'),
            'estado' => 'ACTIVO',
        ]);
        $usuarioSinPersonal->assignRole('ENFERMEROS');

        $preadmision = $this->crearPreadmision('PENDIENTE');
        $conteoAntes = Personal::count();

        try {
            Livewire::actingAs($usuarioSinPersonal)
                ->test(ValoracionInicialModal::class)
                ->call('open', $preadmision->cod_preadmision)
                ->set($this->getValidFormData())
                ->call('guardar');
        } catch (\Throwable $e) {
            // Rechazo esperado
        }

        $this->assertSame($conteoAntes, Personal::count(), 'No se debe haber creado ningun Personal automatico.');
    }

    /**
     * 13. Preadmision en estado no permitido: rechazada.
     */
    public function test_13_preadmision_en_estado_no_permitido_es_rechazada(): void
    {
        $enfermero = User::where('correo', 'enfermeria@remembermind.com')->firstOrFail();
        $preadmisionAprobada = $this->crearPreadmision('APROBADA');

        $this->assertFalse(Gate::forUser($enfermero)->allows('create', [ValoracionEnfermeriaPreadmision::class, $preadmisionAprobada]));

        Livewire::actingAs($enfermero)
            ->test(ValoracionInicialModal::class)
            ->call('open', $preadmisionAprobada->cod_preadmision)
            ->set($this->getValidFormData())
            ->call('guardar')
            ->assertStatus(422);

        $this->assertDatabaseMissing('valoraciones_enfermeria_preadmision', [
            'cod_preadmision' => $preadmisionAprobada->cod_preadmision,
        ]);
    }

    /**
     * 14. FK invalidas: rechazadas por motor.
     */
    public function test_14_fk_invalidas_rechazadas_por_motor(): void
    {
        $enfermero = User::where('correo', 'enfermeria@remembermind.com')->firstOrFail();
        $preadmision = $this->crearPreadmision('PENDIENTE');

        $this->expectException(QueryException::class);

        DB::table('valoraciones_enfermeria_preadmision')->insert([
            'cod_valoracion_enfermeria' => 'VEN_BAD_FK_CHK',
            'cod_preadmision' => $preadmision->cod_preadmision,
            'cod_personal_valorador' => 'PER_INVENTADO_9999',
            'cod_usuario_registro' => $enfermero->cod_usuario,
            'fecha_hora' => now(),
            'estado_general' => 'ESTABLE',
        ]);
    }
}
