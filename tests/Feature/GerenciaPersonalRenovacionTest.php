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
        (require base_path('database/propuestas/gerencia/2026_11_04_000004_pais_documento_telefono_personal.php'))->up();
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

    public function test_todos_los_pasos_validan_cada_campo_sin_borrar_errores_ajenos(): void
    {
        $this->gerente();
        Livewire::test(PersonalInstitucionalForm::class)->set('telefono', '123')->assertHasErrors(['telefono'])
            ->set('rol_seleccionado', 'ADMINISTRADOR')->assertSet('camposValidados.roles_seleccionados', true)
            ->set('fecha_ingreso', 'fecha inválida')->assertHasErrors(['fecha_ingreso', 'telefono'])
            ->set('fecha_ingreso', '2026-11-01')->assertHasNoErrors(['fecha_ingreso'])->assertHasErrors(['telefono'])
            ->set('matricula_prof', str_repeat('A', 51))->assertHasErrors(['matricula_prof'])
            ->set('matricula_prof', 'abc-123')->assertHasNoErrors(['matricula_prof'])->assertSet('matricula_prof', 'ABC-123')
            ->set('observaciones', '')->assertSet('camposValidados.observaciones', false)
            ->set('fecha_ingreso', '')->assertSet('camposValidados.fecha_ingreso', false)
            ->set('observaciones', 'observación sintética')->assertSet('camposValidados.observaciones', true)
            ->assertHasErrors(['telefono'])->set('pasoActual', 2)->assertSeeHtml('id="trabajador-rol-source"');
    }

    public function test_guardado_revisa_todos_los_pasos_y_vuelve_al_primero_invalido(): void
    {
        $this->gerente();
        $form = Livewire::test(PersonalInstitucionalForm::class)->set([
            'nombres'=>'Persona', 'ap_paterno'=>'Sintética', 'numero_documento'=>'87654321', 'expedido'=>'LP',
            'fecha_nacimiento'=>'1990-01-01', 'genero'=>'F', 'telefono'=>'123', 'direccion'=>'Dirección sintética',
            'correo'=>'validacion-integral@remembermind.test', 'contrasena_temporal'=>'corta', 'rol_seleccionado'=>'ADMINISTRADOR',
        ])->set('pasoActual', 4)->call('guardar')->assertHasErrors(['telefono'])->assertSet('pasoActual', 1)
            ->assertDispatched('personal-validacion-fallida');
        $form->set('telefono', '77712345')->set('pasoActual', 4)->call('guardar')
            ->assertHasErrors(['contrasena_temporal'])->assertSet('pasoActual', 3)
            ->set('contrasena_temporal', 'Sintetica#12345')->set('pasoActual', 4)->call('guardar')
            ->assertHasErrors(['archivos_temporales.CI', 'archivos_temporales.FOTO_DOC', 'archivos_temporales.CV'])->assertSet('pasoActual', 4);
        $this->assertDatabaseMissing('usuarios', ['correo'=>'validacion-integral@remembermind.test']);
        $this->assertDatabaseCount('documentos', 0);
    }

    public function test_recorrido_completo_llega_a_revision_sin_guardar_antes_de_confirmar(): void
    {
        $this->gerente();
        Livewire::test(PersonalInstitucionalForm::class)->set([
            'nombres'=>'Persona', 'ap_paterno'=>'Sintética', 'numero_documento'=>'87654321', 'expedido'=>'LP',
            'fecha_nacimiento'=>'1990-01-01', 'genero'=>'F', 'telefono'=>'77712345', 'direccion'=>'Dirección sintética',
        ])->call('avanzarPaso')->assertSet('pasoActual', 2)->set('rol_seleccionado', 'ADMINISTRADOR')
            ->call('avanzarPaso')->assertSet('pasoActual', 3)
            ->set('correo', 'recorrido@remembermind.test')->set('contrasena_temporal', 'Sintetica#12345')
            ->call('avanzarPaso')->assertSet('pasoActual', 4)->assertHasNoErrors()->assertSee('Resumen del registro')
            ->assertSee('Documentación')->assertSee('recorrido@remembermind.test');
        $this->assertDatabaseMissing('usuarios', ['correo'=>'recorrido@remembermind.test']);
        $this->assertDatabaseCount('documentos', 0);
    }

    public function test_documentos_tienen_estados_por_campo_y_quitar_limpia_el_error(): void
    {
        $this->gerente();
        Livewire::test(PersonalInstitucionalForm::class)->set('rol_seleccionado', 'ADMINISTRADOR')
            ->set('telefono', '123')->set('archivos_temporales.CI', UploadedFile::fake()->create('invalido.txt', 10, 'text/plain'))
            ->assertHasErrors(['archivos_temporales.CI', 'telefono'])
            ->set('archivos_temporales.CI', UploadedFile::fake()->create('identidad.pdf', 10, 'application/pdf'))
            ->assertHasNoErrors(['archivos_temporales.CI'])->assertSet('camposValidados', fn ($campos)=>($campos['archivos_temporales.CI'] ?? false) === true)
            ->assertHasErrors(['telefono'])->call('removerDocumento', 'CI')->assertHasNoErrors(['archivos_temporales.CI'])
            ->assertSet('camposValidados', fn ($campos)=>!isset($campos['archivos_temporales.CI']))
            ->set('archivos_temporales.NO_CONFIGURADO', UploadedFile::fake()->create('extra.pdf', 10, 'application/pdf'))
            ->assertHasErrors(['archivos_temporales']);
    }

    public function test_cambiar_calle_conserva_el_verde_del_departamento_y_municipio(): void
    {
        $this->gerente();
        Livewire::test(PersonalInstitucionalForm::class)->set('ciudad_id', 'LPZ')->set('municipio_id', 'MUN_LPZ')
            ->set('zona_id', 'ZON_SOP')->set('calle_id', 'CAL_ARC')->assertSet('camposValidados.ciudad_id', true)
            ->assertSet('camposValidados.municipio_id', true)->assertSet('camposValidados.zona_id', true)
            ->set('calle_id', 'CAL_6AG')->assertSet('camposValidados.ciudad_id', true)->assertSet('camposValidados.municipio_id', true);
    }

    public function test_fotografia_no_acepta_rutas_inventadas_y_vacia_no_es_verde(): void
    {
        $this->gerente();
        Livewire::test(PersonalInstitucionalForm::class)->set('foto_perfil', 'ruta-inventada.jpg')->assertHasErrors(['foto_perfil'])
            ->set('foto_perfil', UploadedFile::fake()->image('perfil.jpg'))->assertHasNoErrors(['foto_perfil'])
            ->assertSet('camposValidados.foto_perfil', true)->set('foto_perfil', null)->assertHasNoErrors(['foto_perfil'])
            ->assertSet('camposValidados.foto_perfil', false);
        $usuario = User::factory()->create(['foto'=>'personal/perfil-sintetico.jpg']);
        $usuario->assignRole('ADMINISTRADOR');
        Livewire::test(PersonalInstitucionalForm::class, ['usuarioId'=>$usuario->getKey()])->set('foto_perfil', $usuario->foto)
            ->assertHasNoErrors(['foto_perfil'])->set('foto_perfil', 'otra-ruta.jpg')->assertHasErrors(['foto_perfil']);
    }

    public function test_direccion_filtra_por_padres_y_aplica_solo_con_confirmacion(): void
    {
        $this->gerente();
        $form = Livewire::test(PersonalInstitucionalForm::class)->set('direccion', 'DIRECCIÓN ANTERIOR')
            ->assertDontSeeHtml('<textarea id="trabajador-direccion"')->assertSee('DIRECCIÓN ANTERIOR')
            ->set('ciudad_id', 'LPZ')->set('municipio_id', 'MUN_LPZ')->set('zona_id', 'ZON_SOP')
            ->set('calle_id', 'CAL_ARC')->set('nro_casa', '120')->assertSet('direccion', 'DIRECCIÓN ANTERIOR')
            ->call('completarDireccion')->assertHasNoErrors()->assertDispatched('direccion-personal-completada')
            ->assertSet('direccion', 'AV. ARCE, N.º 120, ZONA SOPOCACHI, LA PAZ, LA PAZ, BOLIVIA');
        $form->set('pais_direccion', 'PE')->assertSet('ciudad_id', null)->assertSet('municipio_id', null)
            ->assertSet('zona_id', null)->assertSet('calle_id', null)->assertSet('pais_documento', 'BO')
            ->assertSet('departamentos_list', [])->assertSet('direccion', 'AV. ARCE, N.º 120, ZONA SOPOCACHI, LA PAZ, LA PAZ, BOLIVIA')
            ->set('ciudad_id', 'LPZ')->assertHasErrors(['ciudad_id'])
            ->call('completarDireccion')->assertHasErrors(['ciudad_id'])->assertNotDispatched('cerrar-modal-gestion-personal');
    }

    public function test_direccion_otra_permite_pais_sin_sugerencias_y_rechaza_cruce_de_zonas(): void
    {
        $this->gerente();
        $form = Livewire::test(PersonalInstitucionalForm::class)->set('ciudad_id', 'LPZ')->set('municipio_id', 'MUN_EAL')
            ->set('zona_id', 'ZON_SOP')->set('calle_id', 'CAL_ARC')
            ->call('completarDireccion')->assertHasErrors(['zona_id', 'calle_id']);
        $form->set('pais_direccion', 'PE')->set('ciudad_id', 'OTRA')->set('otro_departamento', 'región sintética')
            ->set('municipio_id', 'OTRA')->set('otro_municipio', 'ciudad sintética')->set('zona_id', 'OTRA')
            ->set('otra_zona', 'barrio sintético')->set('calle_id', 'OTRA')->set('otra_calle', 'calle sintética')
            ->call('completarDireccion')->assertHasNoErrors()->assertSet('direccion', 'CALLE SINTÉTICA, ZONA BARRIO SINTÉTICO, CIUDAD SINTÉTICA, REGIÓN SINTÉTICA, PERÚ');
    }

    public function test_nacimiento_del_formulario_valida_18_a_50_y_publica_limites_html(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-11-05 14:00:00'));
        $this->gerente();
        Livewire::test(PersonalInstitucionalForm::class)->assertSeeHtml('min="1975-11-06"')->assertSeeHtml('max="2008-11-05"')
            ->set('fecha_nacimiento', '2008-11-06')->assertHasErrors(['fecha_nacimiento'])->assertSee('al menos 18 años')
            ->set('fecha_nacimiento', '2008-11-05')->assertHasNoErrors(['fecha_nacimiento'])
            ->set('fecha_nacimiento', '1975-11-05')->assertHasErrors(['fecha_nacimiento'])->assertSee('más de 50 años')
            ->set('fecha_nacimiento', '1975-11-06')->assertHasNoErrors(['fecha_nacimiento']);
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
            ->call('abrirModalNuevo')->assertSee('Pasos del registro')->assertSee('Identidad y contacto')
            ->call('cerrarModal')->assertSet('modalGestionAbierto', false);
        $this->assertFalse($actor->can('usuarios.crear'));
        $this->assertDatabaseCount('asignaciones_personal', 0);
    }

    public function test_gerencia_crea_trabajador_sin_permisos_globales_y_sin_mora_para_opcionales(): void
    {
        $actor = $this->gerente();
        $resultado = app(GuardarPersonalInstitucional::class)->guardar($actor, [
            'esEdicion' => false, 'pais_documento' => 'BO', 'tipo_documento' => 'CI', 'pais_telefono' => 'BO', 'correo' => 'alta@remembermind.test', 'contrasena_temporal' => 'Sintetica#12345',
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

    public function test_alta_de_trabajador_reutiliza_modal_centrado_para_cuentas_autorizadas(): void
    {
        foreach (['GERENTE', 'SUPERADMINISTRADOR'] as $rol) {
            $actor = User::factory()->create();
            $actor->assignRole($rol);
            $this->actingAs($actor);
            Livewire::test(PersonalInstitucionalPanel::class)->call('abrirModalNuevo')
                ->assertSet('modalGestionAbierto', true)
                ->assertSeeHtml('rm-staff-registration-dialog')
                ->assertDontSeeHtml('rm-staff-overlay');
        }
    }

    public function test_validacion_por_campo_corrige_error_y_no_marca_opcionales_vacios(): void
    {
        $this->gerente();
        Livewire::test(PersonalInstitucionalForm::class)
            ->assertDontSee('Dato válido')
            ->set('telefono', '123')->assertHasErrors(['telefono' => 'regex'])
            ->assertSee('Escribe 8 dígitos')->assertSee('aria-invalid="true"', false)
            ->set('telefono', '77712345')->assertHasNoErrors(['telefono'])
            ->assertSet('camposValidados.telefono', true)->assertSee('Dato válido')
            ->set('telefono', 'mal')->assertHasErrors(['telefono'])
            ->assertSet('camposValidados.telefono', null)
            ->set('ap_paterno', 'Prueba')->set('ap_materno', '')->assertHasNoErrors(['ap_materno'])
            ->assertSet('camposValidados.ap_materno', false)
            ->set('fecha_nacimiento', now()->subYears(101)->toDateString())->assertHasErrors(['fecha_nacimiento' => 'after_or_equal'])
            ->call('avanzarPaso')->assertHasErrors(['nombres', 'direccion', 'genero'])
            ->assertSet('pasoActual', 1)->assertDispatched('personal-validacion-fallida');
    }

    public function test_apellidos_alternativos_y_formato_de_identidad_en_servidor(): void
    {
        $this->gerente();
        Livewire::test(PersonalInstitucionalForm::class)
            ->set('ap_paterno', '')->assertHasErrors(['ap_paterno'])
            ->set('telefono', '123')->assertHasErrors(['telefono'])
            ->set('ap_materno', 'muñoz')->assertHasNoErrors(['ap_paterno', 'ap_materno'])->assertHasErrors(['telefono'])
            ->assertSet('ap_materno', 'MUÑOZ')
            ->set('nombres', 'maría josé')->assertHasNoErrors(['nombres'])->assertSet('nombres', 'MARÍA JOSÉ')
            ->set('nombres', 'María123')->assertHasErrors(['nombres' => 'regex'])
            ->set('numero_documento', 'AB12345')->assertHasErrors(['numero_documento' => 'regex'])
            ->set('expedido', 'LP3')->assertHasErrors(['expedido' => 'in'])
            ->set('direccion', 'calle 12')->assertSet('direccion', 'CALLE 12');
    }

    public function test_accion_rechaza_identidad_manipulada_sin_crear_cuenta(): void
    {
        $actor = $this->gerente();
        $cantidad = User::count();
        $base = ['esEdicion' => false, 'pais_documento' => 'BO', 'tipo_documento' => 'CI', 'pais_telefono' => 'BO', 'correo' => 'invalido@remembermind.test', 'contrasena_temporal' => 'Sintetica#12345',
            'nombres' => 'María', 'ap_paterno' => 'Prueba', 'numero_documento' => '99887766', 'expedido' => 'LP',
            'fecha_nacimiento' => '1990-01-01', 'genero' => 'F', 'telefono' => '77712345', 'direccion' => 'Calle 12',
            'roles_seleccionados' => ['ADMINISTRADOR']];
        foreach ([['nombres' => 'María123'], ['ap_paterno' => '', 'ap_materno' => ''],
            ['numero_documento' => 'AB12345'], ['telefono' => 'ABC12345']] as $invalido) {
            try {
                app(GuardarPersonalInstitucional::class)->guardar($actor, [...$base, ...$invalido]);
                $this->fail('La identidad inválida no puede guardarse.');
            } catch (ValidationException $e) {
                foreach (array_keys($invalido) as $campo) {
                    $this->assertArrayHasKey($campo, $e->errors());
                }
            }
        }
        $this->assertSame($cantidad, User::count());
        $this->assertDatabaseCount('documentos', 0);
    }

    public function test_alta_con_solo_apellido_materno_normaliza_y_persiste_sin_cambiar_credenciales(): void
    {
        $actor = $this->gerente();
        $resultado = app(GuardarPersonalInstitucional::class)->guardar($actor, [
            'esEdicion' => false, 'pais_documento' => 'BO', 'tipo_documento' => 'CI', 'pais_telefono' => 'BO', 'correo' => 'MATERNO@remembermind.test', 'contrasena_temporal' => 'Sintetica#12345',
            'nombres' => 'maría josé', 'ap_materno' => 'muñoz', 'numero_documento' => '99123456', 'expedido' => 'lp',
            'fecha_nacimiento' => '1990-01-01', 'genero' => 'F', 'telefono' => '77712345', 'direccion' => 'calle 12',
            'roles_seleccionados' => ['ADMINISTRADOR'],
            'archivos_temporales' => ['CI' => UploadedFile::fake()->create('ci.pdf', 10, 'application/pdf'),
                'FOTO_DOC' => UploadedFile::fake()->image('foto.jpg'), 'CV' => UploadedFile::fake()->create('cv.pdf', 10, 'application/pdf')],
        ]);
        $usuario = $resultado['usuario'];
        $this->assertSame('materno@remembermind.test', $usuario->correo);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('Sintetica#12345', $usuario->password));
        $this->assertDatabaseHas('personal', ['cod_usuario' => $usuario->getKey(), 'nombres' => 'MARÍA JOSÉ',
            'apellido_paterno' => '', 'apellido_materno' => 'MUÑOZ', 'direccion' => 'CALLE 12', 'expedicion_documento' => 'LP']);
    }

    public function test_validacion_por_campo_rechaza_correo_duplicado_y_clave_corta(): void
    {
        $this->gerente();
        User::factory()->create(['correo' => 'duplicado@remembermind.test']);
        Livewire::test(PersonalInstitucionalForm::class)
            ->set('correo', 'DUPLICADO@remembermind.test')->assertHasErrors(['correo' => 'unique'])
            ->set('correo', 'disponible@remembermind.test')->assertHasNoErrors(['correo'])
            ->assertSet('camposValidados.correo', true)
            ->set('contrasena_temporal', 'corta')->assertHasErrors(['contrasena_temporal' => 'min'])
            ->set('contrasena_temporal', 'Sintetica#12345')->assertHasNoErrors(['contrasena_temporal']);
    }

    public function test_guardado_muestra_juntos_todos_los_documentos_obligatorios_faltantes(): void
    {
        $this->gerente();
        Livewire::test(PersonalInstitucionalForm::class)->set([
            'nombres' => 'Sintético', 'ap_paterno' => 'Validación',
            'numero_documento' => '87654321', 'expedido' => 'LP', 'fecha_nacimiento' => '1990-01-01', 'genero' => 'F',
            'telefono' => '77712345', 'direccion' => 'Dirección sintética', 'correo' => 'faltantes@remembermind.test',
            'contrasena_temporal' => 'Sintetica#12345', 'rol_seleccionado' => 'ADMINISTRADOR',
        ])->call('guardar')->assertHasErrors(['archivos_temporales.CI', 'archivos_temporales.FOTO_DOC', 'archivos_temporales.CV'])
            ->assertSet('pasoActual', 4)->assertSee('Adjunte')->assertNotDispatched('cerrar-modal-gestion-personal');
        $this->assertDatabaseMissing('usuarios', ['correo' => 'faltantes@remembermind.test']);
        $this->assertDatabaseCount('documentos', 0);
    }

    public function test_fotografia_del_formulario_rechaza_archivos_no_validos_sin_avanzar(): void
    {
        $this->gerente();
        Livewire::test(PersonalInstitucionalForm::class)
            ->set('foto_perfil', UploadedFile::fake()->create('documento.pdf', 10, 'application/pdf'))
            ->assertHasErrors(['foto_perfil'])
            ->assertSet('pasoActual', 1);
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
        ])->set('foto_perfil', UploadedFile::fake()->image('perfil.jpg'))
            ->set('archivos_temporales.CI', UploadedFile::fake()->create('ci.pdf', 10, 'application/pdf'))
            ->set('archivos_temporales.FOTO_DOC', UploadedFile::fake()->image('foto.jpg'))
            ->set('archivos_temporales.CV', UploadedFile::fake()->create('cv.pdf', 10, 'application/pdf'))
            ->call('guardar')->assertHasNoErrors()->assertDispatched('actualizarTablaPersonal')
            ->assertDispatched('cerrar-modal-gestion-personal')->assertSet('contrasena_temporal', '');
        $this->assertDatabaseHas('usuarios', ['correo' => 'formulario@remembermind.test', 'estado' => 'ACTIVO']);
        $foto = User::where('correo', 'formulario@remembermind.test')->sole()->foto;
        $this->assertNotEmpty($foto);
        Storage::disk('public')->assertExists($foto);
        $this->assertDatabaseHas('personal', ['nombres' => 'ALTA SINTÉTICA', 'numero_documento' => '56789123']);
        $this->assertDatabaseCount('documentos', 3);
    }

    public function test_pais_documento_y_prefijo_telefonico_se_conservan_al_editar(): void
    {
        $actor = $this->gerente();
        $usuario = app(GuardarPersonalInstitucional::class)->guardar($actor, [
            'esEdicion'=>false,'pais_documento'=>'PE','tipo_documento'=>'DNI','pais_telefono'=>'CL',
            'correo'=>'extranjero@remembermind.test','contrasena_temporal'=>'Sintetica#12345',
            'nombres'=>'Persona','ap_paterno'=>'Prueba','numero_documento'=>'88994455','expedido'=>null,
            'fecha_nacimiento'=>'1990-01-01','genero'=>'F','telefono'=>'912345678','direccion'=>'Calle de prueba',
            'roles_seleccionados'=>['ADMINISTRADOR'],
            'archivos_temporales'=>['CI'=>UploadedFile::fake()->create('identidad.pdf',10,'application/pdf'),
                'FOTO_DOC'=>UploadedFile::fake()->image('foto.jpg'),'CV'=>UploadedFile::fake()->create('cv.pdf',10,'application/pdf')],
        ])['usuario'];
        $this->assertDatabaseHas('personal',['cod_usuario'=>$usuario->getKey(),'pais_documento'=>'PE','tipo_documento'=>'DNI',
            'pais_telefono'=>'CL','telefono'=>'+56912345678','expedicion_documento'=>null]);
        Livewire::test(PersonalInstitucionalForm::class,['usuarioId'=>$usuario->getKey()])
            ->assertSet('pais_documento','PE')->assertSet('tipo_documento','DNI')->assertSet('pais_telefono','CL')
            ->assertSet('telefono','912345678')->assertDontSee('id="trabajador-expedido"',false)
            ->set('pais_documento','BO')->assertSet('tipo_documento','CI')->assertSee('Departamento de expedición')
            ->set('expedido','LP')->set('pais_documento','PE')->assertSet('expedido',null)
            ->set('numero_documento','1234567')->assertHasErrors(['numero_documento']);
    }

    public function test_otro_pais_resuelve_iso_y_se_recupera_al_editar_sin_columnas_adicionales(): void
    {
        $actor = $this->gerente();
        $columnas = \Illuminate\Support\Facades\Schema::getColumnListing('personal');
        $form = Livewire::test(PersonalInstitucionalForm::class)->call('activarOtroPais','documento')
            ->assertSet('otro_pais_documento',true)->assertSet('pais_documento',null)->assertSee('Especifica el país del documento')
            ->set('pais_documento','FR')->assertSet('tipo_documento','DOCUMENTO_NACIONAL')
            ->call('activarOtroPais','telefono')->assertSet('pais_telefono',null)->set('pais_telefono','FR')
            ->set(['nombres'=>'Prueba','ap_paterno'=>'Internacional','numero_documento'=>'AB123456','fecha_nacimiento'=>'1990-01-01',
                'genero'=>'F','telefono'=>'612345678','direccion'=>'Calle de prueba','correo'=>'francia@remembermind.test',
                'contrasena_temporal'=>'Sintetica#12345','rol_seleccionado'=>'ADMINISTRADOR'])
            ->set('archivos_temporales.CI',UploadedFile::fake()->create('identidad.pdf',10,'application/pdf'))
            ->set('archivos_temporales.FOTO_DOC',UploadedFile::fake()->image('foto.jpg'))
            ->set('archivos_temporales.CV',UploadedFile::fake()->create('cv.pdf',10,'application/pdf'))
            ->call('guardar')->assertHasNoErrors()->assertDispatched('cerrar-modal-gestion-personal');
        $usuario = User::where('correo','francia@remembermind.test')->sole();
        $this->assertDatabaseHas('personal',['cod_usuario'=>$usuario->getKey(),'pais_documento'=>'FR','pais_telefono'=>'FR','telefono'=>'+33612345678','tipo_documento'=>'DOCUMENTO_NACIONAL']);
        $this->assertSame($columnas,\Illuminate\Support\Facades\Schema::getColumnListing('personal'));
        Livewire::test(PersonalInstitucionalForm::class,['usuarioId'=>$usuario->getKey()])->assertSet('otro_pais_documento',true)
            ->assertSet('otro_pais_telefono',true)->assertSet('pais_documento','FR')->assertSet('telefono','612345678');
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
