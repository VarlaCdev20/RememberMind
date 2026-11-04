<?php

namespace Tests\Feature;

use App\Mail\PersonalInstitucionalBienvenidaMail;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\AutorizarGestionPersonal;
use App\Modulos\Identidad\Aplicacion\Acciones\CambiarEstadoPersonal;
use App\Modulos\Identidad\Aplicacion\Acciones\EnviarRecuperacionAccesoPersonal;
use App\Modulos\Identidad\Aplicacion\Acciones\GuardarPersonalInstitucional;
use App\Modulos\Identidad\Presentacion\Livewire\Funcionalidades\PersonalInstitucionalForm;
use App\Modulos\Identidad\Presentacion\Livewire\Paginas\PersonalInstitucionalPanel;
use App\Notifications\ResetPasswordNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class GerenciaPersonalRenovacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake('public');
        Mail::fake();
    }

    private function gerente(): User
    {
        $actor = User::factory()->create();
        $actor->assignRole('GERENTE');
        $this->actingAs($actor);

        return $actor;
    }

    public function test_directorio_gerencial_tiene_recorrido_propio_sin_paneles_de_demostracion(): void
    {
        $actor = $this->gerente();
        $trabajador = User::factory()->create(['correo' => 'trabajador@remembermind.test']);
        $trabajador->assignRole('ENFERMEROS');
        $panel = Livewire::test(PersonalInstitucionalPanel::class)
            ->assertSee('Buscar trabajador')->assertSee('Estado laboral')->assertSee('Estado de acceso')
            ->assertSee($trabajador->correo)->assertDontSee('Desempeño y carga')
            ->assertDontSee('Estadísticas de pacientes atendidos')
            ->set('busquedaResumen', 'Sin coincidencia')->assertSee('Sin coincidencias')
            ->call('limpiarFiltrosResumen')->assertSee($trabajador->correo)
            ->call('abrirModalNuevo')->assertSee('Paso 1 de 4')->assertSee('Identidad y contacto')
            ->call('cerrarModal')->assertSet('modalGestionAbierto', false);
        $this->assertFalse($actor->can('usuarios.crear'));
        $this->assertDatabaseCount('asignaciones_personal', 0);
    }

    public function test_gerencia_crea_trabajador_sin_permisos_globales_y_sin_mora_para_opcionales(): void
    {
        $actor = $this->gerente();
        $resultado = app(GuardarPersonalInstitucional::class)->guardar($actor, [
            'esEdicion' => false, 'correo' => 'alta@remembermind.test', 'contrasena_temporal' => 'Sintetica#12345',
            'nombres' => 'Persona', 'ap_paterno' => 'Sintética', 'numero_documento' => '45678910', 'expedido' => 'LP',
            'fecha_nacimiento' => '1990-01-01', 'genero' => 'F', 'telefono' => '77712345', 'direccion' => 'Dirección de prueba',
            'roles_seleccionados' => ['ADMINISTRADOR'],
            'archivos_temporales' => ['CI' => UploadedFile::fake()->create('ci.pdf', 10, 'application/pdf'),
                'FOTO_DOC' => UploadedFile::fake()->image('foto.jpg'), 'CV' => UploadedFile::fake()->create('cv.pdf', 10, 'application/pdf')],
        ]);
        $usuario = $resultado['usuario'];
        $this->assertSame('ACTIVO', $usuario->estado);
        $this->assertSame('ADMINISTRADOR', $usuario->personal->profesion);
        $this->assertTrue($usuario->hasRole('ADMINISTRADOR'));
        $this->assertDatabaseCount('documentos', 3);
        $antes = \App\Models\Documento::where('cod_usuario', $usuario->getKey())->orderBy('cod_documento')->get()->map->getAttributes()->all();
        $foto = collect(app(\App\Modulos\Identidad\Aplicacion\Acciones\DocumentacionUsuarioService::class)->obtenerChecklistUsuario($usuario))->firstWhere('cod_tipo_doc', 'FOTO');
        $this->assertTrue($foto['cargado']);
        $this->assertFalse($foto['conflicto']);
        $this->assertSame('FOTO_DOC', $foto['documento']->tipo_documento);
        $this->assertSame($antes, \App\Models\Documento::where('cod_usuario', $usuario->getKey())->orderBy('cod_documento')->get()->map->getAttributes()->all());
        Mail::assertSent(PersonalInstitucionalBienvenidaMail::class, fn ($mail) => $mail->documentos_pendientes === [] && $mail->fecha_limite === '');
    }

    public function test_scope_rechaza_roles_excluidos_multirrol_y_cambios_de_rol_sin_efectos(): void
    {
        $actor = $this->gerente();
        $antes = User::count();
        $alcance = app(AutorizarGestionPersonal::class);
        $mixto = User::factory()->create();
        $mixto->assignRole(['ENFERMEROS', 'GERENTE']);
        $enfermero = User::factory()->create();
        $enfermero->assignRole('ENFERMEROS');
        foreach ([[null, ['GERENTE']], [null, ['SUPERADMINISTRADOR']], [null, ['FAMILIAR']], [$mixto, []], [$enfermero, ['PSICOLOGO/A']]] as [$destino, $roles]) {
            try {
                $alcance->autorizar($actor, $destino, $roles);
                $this->fail('Se permitió una operación fuera del alcance aprobado.');
            } catch (HttpException $error) {
                $this->assertSame(403, $error->getStatusCode());
            }
        }
        $this->assertSame($antes + 2, User::count());
        $this->assertSame(['ENFERMEROS'], $enfermero->fresh()->getRoleNames()->all());
        $this->assertDatabaseCount('documentos', 0);
    }

    public function test_wizard_no_salta_validaciones_y_revalida_permiso_al_guardar(): void
    {
        $actor = $this->gerente();
        $form = Livewire::test(PersonalInstitucionalForm::class)
            ->assertSet('totalPasos', 4)->call('avanzarPaso')->assertHasErrors(['nombres', 'numero_documento'])
            ->assertSet('pasoActual', 1)->call('gotoStep', 4)->assertSet('pasoActual', 1);
        $actor->removeRole('GERENTE');
        $form->call('guardar')->assertForbidden();
        $this->assertDatabaseCount('documentos', 0);
    }

    public function test_cambio_de_acceso_preserva_trabajo_y_rechaza_reintento_obsoleto(): void
    {
        $actor = $this->gerente();
        $destino = User::factory()->create(['nombres' => 'Persona', 'ap_paterno' => 'Sintética']);
        $destino->assignRole('ENFERMEROS');
        $accion = app(CambiarEstadoPersonal::class);
        $laboral = $destino->personal->getAttributes();
        $accion->ejecutar($actor, $destino, 'ACTIVO');
        $this->assertSame('SUSPENDIDO', $destino->fresh()->estado);
        $this->assertSame($laboral, $destino->personal->fresh()->getAttributes());
        try {
            $accion->ejecutar($actor, $destino, 'ACTIVO');
            $this->fail('Un reintento obsoleto no puede reactivar el acceso.');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('acceso', $error->errors());
        }
        $this->assertSame('SUSPENDIDO', $destino->fresh()->estado);
        $accion->ejecutar($actor, $destino, 'SUSPENDIDO');
        $this->assertSame('ACTIVO', $destino->fresh()->estado);
        $this->assertDatabaseCount('asignaciones_personal', 0);
        $this->assertDatabaseHas('activity_log', ['subject_id' => $destino->getKey(), 'event' => 'acceso_personal']);
    }

    public function test_formulario_confirma_alta_completa_y_refresca_el_directorio(): void
    {
        $this->gerente();
        Livewire::test(PersonalInstitucionalForm::class)->set([
            'nombres' => 'Alta sintética', 'ap_paterno' => 'Prueba', 'ap_materno' => 'Verificación',
            'numero_documento' => '56789123', 'expedido' => 'LP', 'fecha_nacimiento' => '1990-01-01', 'genero' => 'F',
            'telefono' => '77712345', 'direccion' => 'Dirección de prueba', 'correo' => 'formulario@remembermind.test',
            'contrasena_temporal' => 'Sintetica#12345', 'rol_seleccionado' => 'ADMINISTRADOR',
        ])->set('archivos_temporales.CI', UploadedFile::fake()->create('ci.pdf', 10, 'application/pdf'))
            ->set('archivos_temporales.FOTO_DOC', UploadedFile::fake()->image('foto.jpg'))
            ->set('archivos_temporales.CV', UploadedFile::fake()->create('cv.pdf', 10, 'application/pdf'))
            ->call('guardar')->assertHasNoErrors()->assertDispatched('actualizarTablaPersonal')
            ->assertDispatched('cerrar-modal-gestion-personal')->assertSet('contrasena_temporal', '');
        $this->assertDatabaseHas('usuarios', ['correo' => 'formulario@remembermind.test', 'estado' => 'ACTIVO']);
        $this->assertDatabaseHas('personal', ['nombres' => 'ALTA SINTÉTICA', 'numero_documento' => '56789123']);
        $this->assertDatabaseCount('documentos', 3);
    }

    public function test_recuperacion_gerencial_reutiliza_broker_sin_cambiar_password_y_limita_reintentos(): void
    {
        $actor = $this->gerente();
        Notification::fake();
        config(['mail.from.address' => 'remembermind.health@gmail.com']);
        $destino = User::factory()->create(['nombres' => 'Persona', 'ap_paterno' => 'Sintética']);
        $destino->assignRole('ENFERMEROS');
        $anterior = $destino->password;
        Livewire::test(PersonalInstitucionalPanel::class)->call('enviarRecuperacion', $destino->getKey())
            ->assertHasNoErrors()->assertDispatched('swal');
        Notification::assertSentTo($destino, ResetPasswordNotification::class, fn ($notificacion) => Password::broker(config('fortify.passwords'))->tokenExists($destino, $notificacion->token));
        $this->assertSame($anterior, $destino->fresh()->password);
        $this->assertSame('ACTIVO', $destino->fresh()->estado);
        Livewire::test(PersonalInstitucionalPanel::class)->call('enviarRecuperacion', $destino->getKey())
            ->assertHasErrors('recuperacion')->assertNotDispatched('swal');
        Notification::assertSentToTimes($destino, ResetPasswordNotification::class, 1);
        $this->assertDatabaseHas('activity_log', ['causer_id' => $actor->getKey(), 'subject_id' => $destino->getKey(), 'event' => 'recuperacion_acceso_personal']);
    }

    public function test_recuperacion_rechaza_destinos_excluidos_cuenta_suspendida_y_permiso_revocado(): void
    {
        $actor = $this->gerente();
        Notification::fake();
        $excluido = User::factory()->create(['nombres' => 'Dirección', 'ap_paterno' => 'Sintética']);
        $excluido->assignRole(['GERENTE', 'ENFERMEROS']);
        Livewire::test(PersonalInstitucionalPanel::class)->call('enviarRecuperacion', $excluido->getKey())->assertForbidden();
        $destino = User::factory()->create(['nombres' => 'Persona', 'ap_paterno' => 'Sintética', 'estado' => 'SUSPENDIDO']);
        $destino->assignRole('ENFERMEROS');
        Livewire::test(PersonalInstitucionalPanel::class)->call('enviarRecuperacion', $destino->getKey())->assertHasErrors('recuperacion');
        $actor->removeRole('GERENTE');
        try {
            app(EnviarRecuperacionAccesoPersonal::class)->ejecutar($actor, $destino->getKey());
            $this->fail('El permiso debe comprobarse también en la acción.');
        } catch (HttpException $error) {
            $this->assertSame(403, $error->getStatusCode());
        }
        Notification::assertNothingSent();
        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    public function test_correo_no_configurado_no_anuncia_recuperacion_exitosa(): void
    {
        $this->gerente();
        Notification::fake();
        config(['mail.default' => 'log']);
        $destino = User::factory()->create(['nombres' => 'Persona', 'ap_paterno' => 'Sintética']);
        $destino->assignRole('ADMINISTRADOR');
        Livewire::test(PersonalInstitucionalPanel::class)->call('enviarRecuperacion', $destino->getKey())
            ->assertHasErrors('recuperacion')->assertNotDispatched('swal');
        Notification::assertNothingSent();
        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    public function test_rol_familiar_no_consulta_directorio_institucional_por_acumulacion_de_roles(): void
    {
        $actor = $this->gerente();
        $actor->assignRole('FAMILIAR');
        $this->get(route('admin.personal-institucional'))->assertForbidden();
    }
}
