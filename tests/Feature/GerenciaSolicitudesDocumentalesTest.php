<?php

namespace Tests\Feature;

use App\Models\DestinatarioSolicitudDocumental;
use App\Models\Documento;
use App\Models\EntregaSolicitudDocumental;
use App\Models\Personal;
use App\Models\User;
use App\Modulos\Alertas\Presentacion\Livewire\Funcionalidades\CampanaNotificaciones;
use App\Modulos\Identidad\Aplicacion\Acciones\DocumentacionUsuarioService;
use App\Modulos\Identidad\Aplicacion\Acciones\GestionarSolicitudesDocumentales;
use App\Modulos\Identidad\Aplicacion\Acciones\GuardarPersonalInstitucional;
use App\Modulos\Identidad\Aplicacion\Acciones\RegistrarArchivoSolicitudDocumental;
use App\Modulos\Identidad\Aplicacion\Acciones\RegistrarMovimientoLaboral;
use App\Modulos\Identidad\Aplicacion\Consultas\ConsultarDocumentacionPersonal;
use App\Modulos\Identidad\Presentacion\Livewire\Paginas\DocumentacionPersonalPanel;
use App\Modulos\Identidad\Presentacion\Policies\DocumentoLaboralPolicy;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class GerenciaSolicitudesDocumentalesTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    private User $trabajador;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-10-26 10:00:00'));
        (require base_path('database/propuestas/gerencia/2026_10_26_000001_documentacion_y_estado_laboral.php'))->up();
        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake('local');
        Storage::fake('public');
        $this->actor = User::factory()->create();
        $this->actor->assignRole('GERENTE');
        $this->trabajador = User::factory()->create();
        $this->trabajador->assignRole('ADMINISTRADOR');
        $this->trabajador->forceFill(['fecha_hora_alta' => now()->subHour()])->save();
        Personal::create(['cod_usuario' => $this->trabajador->getKey(), 'nombres' => 'Trabajador', 'apellido_paterno' => 'Sintético', 'numero_documento' => '91234567', 'profesion' => 'ADMINISTRADOR', 'estado' => 'ACTIVO']);
        $this->actingAs($this->actor);
    }

    private function entrada(array $cambios = []): array
    {
        return $cambios + ['rol' => 'ADMINISTRADOR', 'tipo_documento' => 'OTROS', 'nombre_documento' => 'Constancia de prueba',
            'indicaciones' => 'Adjunta el respaldo original.', 'motivo' => 'Seguimiento documental de prueba', 'personas' => [$this->trabajador->personal->getKey()],
            'origen_plazo' => 'ALTA', 'fecha_limite' => null, 'clave_solicitud' => (string) Str::uuid()];
    }

    private function emitir(array $cambios = []): DestinatarioSolicitudDocumental
    {
        return app(GestionarSolicitudesDocumentales::class)->emitir($this->actor, $this->entrada($cambios))->destinatarios->sole();
    }

    public function test_emision_idempotente_conserva_membresia_y_limite_sin_recalcular_rol_actual(): void
    {
        $datos = $this->entrada();
        $servicio = app(GestionarSolicitudesDocumentales::class);
        $primera = $servicio->emitir($this->actor, $datos);
        $segunda = $servicio->emitir($this->actor, $datos);
        $this->assertSame($primera->getKey(), $segunda->getKey());
        $destino = $primera->destinatarios->sole();
        $this->assertSame('2026-11-02', $destino->fecha_limite_original->toDateString());
        $this->trabajador->syncRoles(['ENFERMEROS']);
        $this->assertSame($destino->getKey(), $primera->fresh()->destinatarios->sole()->getKey());
        $this->assertDatabaseCount('solicitudes_documentales_personal', 1);
        try {
            $servicio->emitir($this->actor, [...$datos, 'nombre_documento' => 'Otro contenido']);
            $this->fail('El mismo reintento no cambia contenido.');
        } catch (HttpException $e) {
            $this->assertSame(409, $e->getStatusCode());
        }
    }

    public function test_no_emite_para_rol_ajeno_o_minimo_inmediato_postergado(): void
    {
        try {
            $this->emitir(['rol' => 'PSICOLOGO/A']);
            $this->fail('No corresponde al rol.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
        try {
            $this->emitir(['tipo_documento' => 'CI']);
            $this->fail('El CI inmediato no se posterga.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('tipo_documento', $e->errors());
        }
        $this->assertDatabaseCount('solicitudes_documentales_personal', 0);
        $this->assertDatabaseCount('destinatarios_solicitud_documental', 0);
    }

    public function test_entrega_privada_idempotente_revision_y_anulacion_conservan_archivo_y_autores(): void
    {
        $destino = $this->emitir();
        $archivo = UploadedFile::fake()->create('respaldo.pdf', 10, 'application/pdf');
        $accion = app(RegistrarArchivoSolicitudDocumental::class);
        $entrega = $accion->ejecutar($this->trabajador, $destino->getKey(), $archivo, '2026-10-20');
        $this->assertSame($entrega->getKey(), $accion->ejecutar($this->trabajador, $destino->getKey(), $archivo, '2026-10-20')->getKey());
        $documento = $entrega->documento;
        $this->assertSame($this->trabajador->getKey(), $documento->cod_usuario_carga);
        $this->assertSame('2026-10-20', $documento->fecha_emision->toDateString());
        Storage::disk('local')->assertExists($documento->ruta_archivo);
        Storage::disk('public')->assertMissing($documento->ruta_archivo);
        $this->actingAs($this->trabajador)->get(route('admin.personal.documento.archivo', $documento))->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $otro = User::factory()->create(['nombres' => 'Otro', 'ap_paterno' => 'Trabajador']);
        $otro->assignRole('ENFERMEROS');
        $this->actingAs($otro)->get(route('admin.personal.documento.archivo', $documento))->assertForbidden();
        $this->actingAs($this->actor);
        $revision = app(DocumentacionUsuarioService::class);
        $revision->validarDocumento($documento, $this->actor, $this->trabajador->getKey());
        $validacion = $documento->fresh()->fecha_validacion->toDateTimeString();
        $revision->anularDocumento($documento, 'Documento equivocado conservado para historia', $this->actor, $this->trabajador->getKey());
        $documento->refresh();
        $this->assertSame('ANULADO', $documento->estado);
        $this->assertSame($this->actor->getKey(), $documento->cod_usuario_anulacion);
        $this->assertSame($validacion, $documento->fecha_validacion->toDateTimeString());
        $this->assertSame($this->trabajador->getKey(), $documento->cod_usuario_carga);
        Storage::disk('local')->assertExists($documento->ruta_archivo);
        $this->assertDatabaseCount('entregas_solicitud_documental', 1);
    }

    public function test_prorroga_unica_avisos_derivados_y_sin_suspension_automatica(): void
    {
        $destino = $this->emitir();
        $gestion = app(GestionarSolicitudesDocumentales::class);
        $gestion->prorrogar($this->actor, $destino->getKey(), 'Respaldo en preparación');
        try {
            $gestion->prorrogar($this->actor, $destino->getKey(), 'Intento de otra prórroga');
            $this->fail('No se permite sobrescribir la prórroga.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('prorroga', $e->errors());
        }
        $this->travelTo(Carbon::parse('2026-11-10 10:00:00'));
        $avisos = app(ConsultarDocumentacionPersonal::class)->avisos($this->trabajador);
        $this->assertCount(1, $avisos);
        $this->assertSame('Plazo registrado vencido', $avisos[0]['mensaje']);
        $this->assertSame('ACTIVO', $this->trabajador->fresh()->estado);
        $this->assertSame('2026-11-02', $destino->fresh()->fecha_limite_original->toDateString());
        $this->assertSame('2026-11-09', $destino->fresh()->fecha_limite_prorrogada->toDateString());
    }

    public function test_prorroga_del_expediente_se_consulta_en_otras_solicitudes_sin_copiar_el_acto(): void
    {
        $origen = $this->emitir(['nombre_documento' => 'Respaldo de origen']);
        $otra = $this->emitir(['nombre_documento' => 'Respaldo complementario']);
        app(GestionarSolicitudesDocumentales::class)->prorrogar($this->actor, $origen->getKey(), 'Ampliación única del expediente');
        $posterior = $this->emitir(['nombre_documento' => 'Respaldo solicitado tras la concesión']);
        $lectura = app(ConsultarDocumentacionPersonal::class);
        foreach ([$otra, $posterior] as $destino) {
            $estado = $lectura->situacion($destino->fresh()->load('entregas.documento'));
            $this->assertSame('2026-11-09', $estado['limite']->toDateString());
            $this->assertSame($origen->getKey(), $estado['concesion']->getKey());
            $this->assertSame($this->actor->getKey(), $estado['concesion']->cod_usuario_prorroga);
            $this->assertFalse($estado['conflicto_plazo']);
            $this->assertSame('2026-11-02', $destino->fresh()->fecha_limite_original->toDateString());
            foreach (['fecha_limite_prorrogada', 'cod_usuario_prorroga', 'fecha_hora_prorroga', 'motivo_prorroga'] as $columna) {
                $this->assertNull($destino->fresh()->getAttribute($columna));
            }
        }
        Livewire::test(DocumentacionPersonalPanel::class)->call('verDetalle', $otra->getKey())
            ->assertSee('Plazo del expediente prorrogado')->assertSee('Ampliación única del expediente')
            ->assertSee('La concesión se registró en otra solicitud de esta misma alta')
            ->assertDontSee('Conceder la única prórroga del alta');
        $this->travelTo(Carbon::parse('2026-11-03 10:00:00'));
        $this->assertSame([], $lectura->avisos($this->trabajador));
        $this->travelTo(Carbon::parse('2026-11-08 10:00:00'));
        $this->assertSame(['09/11/2026'], collect($lectura->avisos($this->trabajador))->pluck('fecha')->unique()->values()->all());
        $this->assertSame(1, DestinatarioSolicitudDocumental::whereNotNull('fecha_limite_prorrogada')->count());
    }

    public function test_otra_solicitud_de_la_misma_alta_no_puede_conceder_una_segunda_prorroga(): void
    {
        $primera = $this->emitir();
        $otra = $this->emitir();
        $gestion = app(GestionarSolicitudesDocumentales::class);
        $gestion->prorrogar($this->actor, $primera->getKey(), 'Concesión original del expediente');
        $auditorias = DB::table('activity_log')->where('event', 'prorroga_documental')->count();
        try {
            $gestion->prorrogar($this->actor, $otra->getKey(), 'Segunda concesión por otra petición');
            $this->fail('La única concesión pertenece al expediente, no a cada archivo.');
        } catch (ValidationException $error) {
            $this->assertStringContainsString('expediente de esta alta ya tiene', $error->errors()['prorroga'][0]);
        }
        $this->assertNull($otra->fresh()->fecha_limite_prorrogada);
        $this->assertSame(1, DestinatarioSolicitudDocumental::whereNotNull('fecha_limite_prorrogada')->count());
        $this->assertSame($auditorias, DB::table('activity_log')->where('event', 'prorroga_documental')->count());
    }

    public function test_prorroga_no_se_propaga_a_peticiones_posteriores_otras_altas_o_personas(): void
    {
        $origen = $this->emitir();
        // Fixture de otro límite conservado, sin alterar una membresía ya emitida.
        $peticionHistorica = $origen->solicitud->replicate()->fill(['clave_solicitud' => (string) Str::uuid()]);
        $peticionHistorica->save();
        $otraAlta = DestinatarioSolicitudDocumental::create(['cod_solicitud_documental' => $peticionHistorica->getKey(),
            'cod_personal' => $this->trabajador->personal->getKey(), 'origen_plazo' => 'ALTA', 'fecha_limite_original' => '2026-10-30']);
        $posterior = $this->emitir(['origen_plazo' => 'SOLICITUD_POSTERIOR', 'fecha_limite' => '2026-11-02']);
        $otroUsuario = User::factory()->create(['fecha_hora_alta' => now()]);
        $otroUsuario->assignRole('ADMINISTRADOR');
        $otroPersonal = Personal::create(['cod_usuario' => $otroUsuario->getKey(), 'nombres' => 'Otra persona',
            'apellido_paterno' => 'Sintética', 'numero_documento' => '91234568', 'profesion' => 'ADMINISTRADOR', 'estado' => 'ACTIVO']);
        $otraPersona = $this->emitir(['personas' => [$otroPersonal->getKey()]]);
        app(GestionarSolicitudesDocumentales::class)->prorrogar($this->actor, $origen->getKey(), 'Ampliación solo de esta alta');
        foreach ([$otraAlta, $posterior, $otraPersona] as $destino) {
            $estado = app(ConsultarDocumentacionPersonal::class)->situacion($destino->fresh()->load('entregas.documento'));
            $this->assertNull($estado['concesion']);
            $this->assertFalse($estado['conflicto_plazo']);
            $this->assertSame($destino->fecha_limite_original->toDateString(), $estado['limite']->toDateString());
        }
    }

    public function test_dos_concesiones_inconsistentes_no_se_ocultan_ni_se_elige_un_limite_arbitrario(): void
    {
        $primera = $this->emitir();
        $otra = $this->emitir();
        foreach ([$primera, $otra] as $destino) {
            // Bypass SQL deliberado: la Action no permite generar esta inconsistencia.
            $destino->update(['fecha_limite_prorrogada' => '2026-11-09', 'cod_usuario_prorroga' => $this->actor->getKey(),
                'fecha_hora_prorroga' => now(), 'motivo_prorroga' => 'Historia inconsistente de prueba']);
        }
        $estado = app(ConsultarDocumentacionPersonal::class)->situacion($otra->fresh()->load('entregas.documento'));
        $this->assertTrue($estado['conflicto_plazo']);
        $this->assertNull($estado['limite']);
        $this->assertNull($estado['concesion']);
        $this->assertSame('Plazo con concesiones contradictorias', $estado['situacion']);
        Livewire::test(DocumentacionPersonalPanel::class)->call('verDetalle', $otra->getKey())
            ->assertSee('Hay varias concesiones para la misma alta')->assertSee('Por revisar')
            ->assertDontSee('Conceder la única prórroga del alta');
        $this->assertSame(['Por revisar'], collect(app(ConsultarDocumentacionPersonal::class)->avisos($this->trabajador))->pluck('fecha')->unique()->values()->all());
        $this->assertSame(2, DestinatarioSolicitudDocumental::whereNotNull('fecha_limite_prorrogada')->count());
    }

    public function test_fallo_de_auditoria_revierte_la_concesion_y_permite_reintento(): void
    {
        $destino = $this->emitir();
        $fallar = true;
        Event::listen('eloquent.creating: '.\Spatie\Activitylog\Models\Activity::class, function ($actividad) use (&$fallar) {
            if ($fallar && $actividad->event === 'prorroga_documental') throw new \RuntimeException('Fallo de auditoría sintético');
        });
        try {
            app(GestionarSolicitudesDocumentales::class)->prorrogar($this->actor, $destino->getKey(), 'Prórroga con rollback de prueba');
            $this->fail('El fallo debe revertir la concesión completa.');
        } catch (\RuntimeException $error) {
            $this->assertSame('Fallo de auditoría sintético', $error->getMessage());
        } finally {
            $fallar = false;
        }
        $this->assertNull($destino->fresh()->fecha_limite_prorrogada);
        $this->assertSame(0, DB::table('activity_log')->where('event', 'prorroga_documental')->count());
        app(GestionarSolicitudesDocumentales::class)->prorrogar($this->actor, $destino->getKey(), 'Reintento confirmado de prueba');
        $this->assertSame('2026-11-09', $destino->fresh()->fecha_limite_prorrogada->toDateString());
        $this->assertSame(1, DB::table('activity_log')->where('event', 'prorroga_documental')->count());
    }

    public function test_entrega_dentro_de_prorroga_heredada_no_se_declara_tardia_al_observarla(): void
    {
        $origen = $this->emitir();
        $otra = $this->emitir();
        app(GestionarSolicitudesDocumentales::class)->prorrogar($this->actor, $origen->getKey(), 'Ampliación única del expediente');
        $this->travelTo(Carbon::parse('2026-11-05 10:00:00'));
        $entrega = app(RegistrarArchivoSolicitudDocumental::class)->ejecutar($this->trabajador, $otra->getKey(), UploadedFile::fake()->create('en-plazo.pdf', 10, 'application/pdf'));
        $this->travelTo(Carbon::parse('2026-11-10 10:00:00'));
        app(DocumentacionUsuarioService::class)->observarDocumento($entrega->documento, 'Falta una página legible', $this->actor, $this->trabajador->getKey());
        $estado = app(ConsultarDocumentacionPersonal::class)->situacion($otra->fresh()->load('entregas.documento'));
        $this->assertSame('Corrección pendiente; la entrega original se recibió en plazo', $estado['aviso']);
        $this->assertNull($otra->fresh()->fecha_limite_prorrogada);
        $this->assertSame('2026-11-09', $estado['limite']->toDateString());
    }

    public function test_inactivar_trabajo_no_suspende_cuenta_y_reintento_no_duplica_historia(): void
    {
        $personal = $this->trabajador->personal;
        $datos = ['estado_esperado' => 'ACTIVO', 'estado_nuevo' => 'INACTIVO', 'fecha_hora_efecto' => now()->toDateTimeString(), 'motivo' => 'Baja laboral de prueba'];
        app(RegistrarMovimientoLaboral::class)->ejecutar($this->actor, $personal->getKey(), $datos);
        $this->assertSame('INACTIVO', $personal->fresh()->estado);
        $this->assertSame('ACTIVO', $this->trabajador->fresh()->estado);
        try {
            app(RegistrarMovimientoLaboral::class)->ejecutar($this->actor, $personal->getKey(), $datos);
            $this->fail('Debe rechazar estado obsoleto.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('estado_esperado', $e->errors());
        }
        $this->assertDatabaseCount('movimientos_laborales_personal', 1);
    }

    public function test_interfaz_worker_no_consulta_solicitudes_ajenas_y_manager_emite_desde_formulario(): void
    {
        $destino = $this->emitir();
        $this->get(route('admin.personal.documentacion'))->assertOk()->assertSeeText('Documentación del personal');
        Livewire::test(DocumentacionPersonalPanel::class)->call('nuevaSolicitud')
            ->set('nombreDocumento', 'Petición desde formulario')->set('motivo', 'Seguimiento de prueba desde interfaz')
            ->set('origenPlazo', 'ALTA')->set('personas', [$this->trabajador->personal->getKey()])
            ->call('emitirSolicitud')->assertHasNoErrors()->assertSet('formulario', false)
            ->assertSee('Solicitud emitida y destinatarios conservados.');
        $this->assertDatabaseCount('solicitudes_documentales_personal', 2);
        $otro = User::factory()->create(['nombres' => 'Otra persona', 'ap_paterno' => 'Sintética']);
        $otro->assignRole('ENFERMEROS');
        $this->actingAs($otro);
        Livewire::test(DocumentacionPersonalPanel::class)->assertSee('Sin solicitudes en esta consulta')->call('verDetalle', $destino->getKey())->assertNotFound();
        $this->actingAs($this->trabajador);
        Livewire::test(DocumentacionPersonalPanel::class)->assertSee('Constancia de prueba')->call('verDetalle', $destino->getKey())->assertSee('Adjuntar respuesta');
    }

    public function test_fallo_despues_de_carga_revierte_sql_y_compensa_solo_archivo_nuevo(): void
    {
        $destino = $this->emitir();
        $fallar = true;
        Event::listen('eloquent.creating: '.EntregaSolicitudDocumental::class, function () use (&$fallar) {
            if ($fallar) {
                throw new \RuntimeException('Fallo sintético de entrega');
            }
        });
        try {
            app(RegistrarArchivoSolicitudDocumental::class)->ejecutar($this->trabajador, $destino->getKey(), UploadedFile::fake()->create('respaldo.pdf', 10, 'application/pdf'));
            $this->fail('Debe fallar.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Fallo sintético de entrega', $e->getMessage());
        } finally {
            $fallar = false;
        }
        $this->assertDatabaseCount('documentos', 0);
        $this->assertDatabaseCount('entregas_solicitud_documental', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
        $this->assertDatabaseCount('solicitudes_documentales_personal', 1);
    }

    public function test_alta_nueva_con_extension_conserva_carga_privada_y_exige_cambio_inicial(): void
    {
        Mail::fake();
        $resultado = app(GuardarPersonalInstitucional::class)->guardar($this->actor, [
            'esEdicion' => false, 'correo' => 'alta-privada@example.test', 'contrasena_temporal' => 'TemporalAlta#123',
            'nombres' => 'Nueva persona', 'ap_paterno' => 'Sintética', 'numero_documento' => '89123456', 'expedido' => 'LP',
            'fecha_nacimiento' => '1990-01-01', 'genero' => 'F', 'telefono' => '77712345', 'direccion' => 'Dirección de prueba',
            'roles_seleccionados' => ['ADMINISTRADOR'],
            'archivos_temporales' => ['CI' => UploadedFile::fake()->create('ci.pdf', 10, 'application/pdf'),
                'FOTO_DOC' => UploadedFile::fake()->image('foto.jpg'), 'CV' => UploadedFile::fake()->create('cv.pdf', 10, 'application/pdf')],
        ]);
        $usuario = $resultado['usuario']->fresh();
        $this->assertTrue($usuario->cambio_password_pendiente);
        $this->assertSame(now()->toDateTimeString(), $usuario->fecha_hora_alta->toDateTimeString());
        $archivos = Documento::where('cod_usuario', $usuario->getKey())->get();
        $this->assertCount(3, $archivos);
        foreach ($archivos as $documento) {
            $this->assertSame($this->actor->getKey(), $documento->cod_usuario_carga);
            $this->assertNotNull($documento->fecha_hora_carga);
            $this->assertStringStartsWith('documentos_personal_privados/', $documento->ruta_archivo);
            Storage::disk('local')->assertExists($documento->ruta_archivo);
            Storage::disk('public')->assertMissing($documento->ruta_archivo);
        }
        $this->actingAs($usuario)->get('/dashboard')->assertRedirect(route('password.inicial'));
    }

    public function test_entrega_en_plazo_observada_despues_no_genera_mora_retroactiva_y_conserva_aviso(): void
    {
        $destino = $this->emitir();
        $entrega = app(RegistrarArchivoSolicitudDocumental::class)->ejecutar($this->trabajador, $destino->getKey(), UploadedFile::fake()->create('respaldo.pdf', 10, 'application/pdf'));
        $this->travelTo(Carbon::parse('2026-11-03 10:00:00'));
        app(DocumentacionUsuarioService::class)->observarDocumento($entrega->documento, 'Falta una página legible', $this->actor, $this->trabajador->getKey());
        Role::findByName('ADMINISTRADOR', 'web')->revokePermissionTo(['alertas.ver', 'alertas.gestionar', 'prescripciones.ver', 'administraciones_medicacion.ver']);
        $avisos = app(ConsultarDocumentacionPersonal::class)->avisos($this->trabajador);
        $this->assertCount(1, $avisos);
        $this->assertSame('Corrección pendiente; la entrega original se recibió en plazo', $avisos[0]['mensaje']);
        $this->actingAs($this->trabajador);
        Livewire::test(CampanaNotificaciones::class)
            ->assertSee('Constancia de prueba')->assertSee('Corrección pendiente; la entrega original se recibió en plazo')
            ->assertSet('conteoDocumentales', 1)->call('verificarAlertas')->assertSet('conteoDocumentales', 1);
        $this->assertDatabaseCount('destinatarios_solicitud_documental', 1);
        $this->assertSame('ACTIVO', $this->trabajador->fresh()->estado);
        $this->assertNull($destino->fresh()->fecha_limite_prorrogada);
        $this->assertSame('2026-11-02', $destino->fresh()->fecha_limite_original->toDateString());
        $this->assertSame('2026-10-26', $entrega->fresh()->fecha_hora_registro->toDateString());
    }

    public function test_primera_entrega_tardia_observada_no_se_presenta_como_recibida_en_plazo(): void
    {
        $destino = $this->emitir();
        $this->travelTo(Carbon::parse('2026-11-03 10:00:00'));
        $entrega = app(RegistrarArchivoSolicitudDocumental::class)->ejecutar($this->trabajador, $destino->getKey(), UploadedFile::fake()->create('tardio.pdf', 10, 'application/pdf'));
        app(DocumentacionUsuarioService::class)->observarDocumento($entrega->documento, 'Falta una página legible', $this->actor, $this->trabajador->getKey());
        $situacion = app(ConsultarDocumentacionPersonal::class)->situacion($destino->fresh()->load('entregas.documento'));
        $this->assertSame('Plazo registrado vencido; falta corregir el documento', $situacion['aviso']);
        $this->assertNull($destino->fresh()->fecha_limite_prorrogada);
        $this->assertDatabaseCount('entregas_solicitud_documental', 1);
        $this->assertSame('ACTIVO', $this->trabajador->fresh()->estado);
    }

    public function test_historia_documental_autoriza_cada_archivo_sin_consultas_por_entrega(): void
    {
        $destino = $this->emitir();
        foreach (range(1, 12) as $numero) {
            app(RegistrarArchivoSolicitudDocumental::class)->ejecutar($this->trabajador, $destino->getKey(),
                UploadedFile::fake()->createWithContent('respaldo.pdf', "%PDF-1.4\n Respaldo sintético {$numero}\n%%EOF"));
        }
        $documentos = $destino->fresh()->entregas()->with('documento')->get()->pluck('documento');
        $this->assertCount(12, $documentos);
        DB::flushQueryLog();
        DB::enableQueryLog();
        try {
            $permisos = app(DocumentoLaboralPolicy::class)->permisosDocumentos($this->actor, $documentos);
            $consultas = count(DB::getQueryLog());
        } finally {
            DB::disableQueryLog();
        }
        $this->assertCount(12, $permisos);
        $this->assertTrue(collect($permisos)->every(fn ($p) => $p['ver']));
        $this->assertLessThanOrEqual(10, $consultas, 'Los permisos del historial no deben consultar por cada archivo.');
        $documentos[0]->cod_residente = 'CONTEXTO_AJENO';
        $limitados = app(DocumentoLaboralPolicy::class)->permisosDocumentos($this->actor, $documentos);
        $this->assertFalse($limitados[$documentos[0]->getKey()]['ver']);
        $this->assertTrue($limitados[$documentos[1]->getKey()]['ver']);
    }

    public function test_titular_ajeno_y_revision_propia_se_rechazan_sin_efectos(): void
    {
        $destino = $this->emitir();
        $otro = User::factory()->create(['nombres' => 'Otra persona', 'ap_paterno' => 'Prueba']);
        $otro->assignRole('ADMINISTRADOR');
        try {
            app(RegistrarArchivoSolicitudDocumental::class)->ejecutar($otro, $destino->getKey(), UploadedFile::fake()->create('ajeno.pdf', 10, 'application/pdf'));
            $this->fail('No se puede entregar por otro titular sin competencia gerencial.');
        } catch (HttpException $error) {
            $this->assertSame(403, $error->getStatusCode());
        }
        $this->assertDatabaseCount('entregas_solicitud_documental', 0);
        $this->assertDatabaseCount('documentos', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
        $entrega = app(RegistrarArchivoSolicitudDocumental::class)->ejecutar($this->trabajador, $destino->getKey(), UploadedFile::fake()->create('propio.pdf', 10, 'application/pdf'));
        $this->trabajador->givePermissionTo('documentos.gestionar');
        try {
            app(DocumentacionUsuarioService::class)->validarDocumento($entrega->documento, $this->trabajador, $this->trabajador->getKey());
            $this->fail('El permiso técnico no concede revisión laboral propia.');
        } catch (HttpException $error) {
            $this->assertSame(403, $error->getStatusCode());
        }
        $this->assertSame('CARGADO', $entrega->documento->fresh()->estado);
        $this->assertNull($entrega->documento->fresh()->cod_usuario_validacion);
    }

    public function test_archivos_invalidos_no_crean_entrega_documento_o_bytes_huerfanos(): void
    {
        $destino = $this->emitir();
        $contenidoFalso = UploadedFile::fake()->createWithContent('aparenta.pdf', '<?php echo "archivo falso";');
        // UploadedFile::fake infiere MIME por extensión; un upload real comprueba los bytes.
        $falso = new UploadedFile($contenidoFalso->getRealPath(), 'aparenta.pdf', 'application/pdf', null, true);
        foreach ([$falso,
            UploadedFile::fake()->create('grande.pdf', 5121, 'application/pdf')] as $archivo) {
            try {
                app(RegistrarArchivoSolicitudDocumental::class)->ejecutar($this->trabajador, $destino->getKey(), $archivo);
                $this->fail('El archivo inválido debe rechazarse antes de persistir.');
            } catch (ValidationException $error) {
                $this->assertArrayHasKey('archivo', $error->errors());
            }
            $this->assertDatabaseCount('entregas_solicitud_documental', 0);
            $this->assertDatabaseCount('documentos', 0);
            $this->assertSame([], Storage::disk('local')->allFiles());
        }
    }
}
