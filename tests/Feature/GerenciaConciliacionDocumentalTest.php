<?php

namespace Tests\Feature;

use App\Models\Contacto;
use App\Models\Documento;
use App\Models\Preadmision;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\DocumentacionUsuarioService;
use App\Modulos\Identidad\Presentacion\Livewire\Paginas\UsuarioFichaPanel;
use App\Modulos\Identidad\Presentacion\Livewire\Paginas\UsuariosPanel;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class GerenciaConciliacionDocumentalTest extends TestCase
{
    use RefreshDatabase;

    private User $titular;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->titular = User::factory()->create(['nombres' => 'Persona', 'ap_paterno' => 'Sintética']);
        $this->titular->assignRole('ADMINISTRADOR');
        $this->actingAs($this->titular);
        Storage::fake('local');
        Storage::fake('public');
    }

    private function documento(string $tipo, string $estado = 'CARGADO', ?Documento $anterior = null): Documento
    {
        $ruta = 'documentos_personal_privados/'.bin2hex(random_bytes(8)).'.pdf';
        Storage::disk('local')->put($ruta, "%PDF-1.4\n Fotografía sintética\n%%EOF");

        return Documento::create(['cod_usuario' => $this->titular->getKey(), 'tipo_documento' => $tipo,
            'nombre' => 'Fotografía original '.$tipo, 'estado' => $estado, 'ruta_archivo' => $ruta,
            'tipo_archivo' => 'application/pdf', 'hash_archivo' => hash('sha256', $ruta),
            'cod_documento_anterior' => $anterior?->getKey()]);
    }

    private function foto(): array
    {
        return collect(app(DocumentacionUsuarioService::class)->obtenerChecklistUsuario($this->titular))->firstWhere('cod_tipo_doc', 'FOTO');
    }

    public function test_fotografia_historica_y_sucesora_no_se_renombran_y_excluyen_archivos_no_vigentes(): void
    {
        $historica = $this->documento('FOTO', 'REEMPLAZADO');
        $vigente = $this->documento('FOTO_DOC', 'CARGADO', $historica);
        $anulada = $this->documento('FOTO', 'ANULADO');
        $antes = Documento::orderBy('cod_documento')->get()->map->getAttributes()->all();
        $foto = $this->foto();
        $this->assertSame($vigente->getKey(), $foto['documento']->getKey());
        $this->assertFalse($foto['conflicto']);
        $this->assertCount(1, $foto['documentos_candidatos']);
        $this->assertSame($antes, Documento::orderBy('cod_documento')->get()->map->getAttributes()->all());
        foreach ([$historica, $vigente, $anulada] as $archivo) {
            Storage::disk('local')->assertExists($archivo->ruta_archivo);
        }
    }

    public function test_dos_fotografias_vigentes_se_muestran_sin_elegir_por_validacion_o_codigo(): void
    {
        $primera = $this->documento('FOTO', 'VALIDADO');
        $segunda = $this->documento('FOTO_DOC', 'VALIDADO');
        $antes = Documento::orderBy('cod_documento')->get()->map->getAttributes()->all();
        $foto = $this->foto();
        $this->assertTrue($foto['cargado']);
        $this->assertTrue($foto['conflicto']);
        $this->assertNull($foto['documento']);
        $this->assertCount(2, $foto['documentos_candidatos']);
        $avance = app(DocumentacionUsuarioService::class)->calcularAvanceDocumental($this->titular);
        $this->assertFalse($avance['completo']);
        $this->assertSame(1, $avance['conflictos']);
        Livewire::test(UsuarioFichaPanel::class, ['usuario' => $this->titular])->set('activeTab', 'documentacion')
            ->assertSee('Hay varios archivos vigentes')->assertSee($primera->nombre)->assertSee($segunda->nombre)
            ->assertSee(route('admin.personal.documento.archivo', $primera), false)
            ->assertDontSee('/storage/documentos_personal_privados/');
        $this->assertSame($antes, Documento::orderBy('cod_documento')->get()->map->getAttributes()->all());
    }

    public function test_archivo_foto_anterior_sigue_cubriendo_un_unico_requisito(): void
    {
        $anterior = $this->documento('FOTO', 'VALIDADO');
        $lista = collect(app(DocumentacionUsuarioService::class)->obtenerChecklistUsuario($this->titular));
        $this->assertSame($anterior->getKey(), $this->foto()['documento']->getKey());
        $this->assertCount(1, $lista->filter(fn ($item) => $item['nombre'] === 'Fotografía actual'));
        $this->assertSame(1, app(DocumentacionUsuarioService::class)->calcularAvanceDocumental($this->titular)['validados']);
        $this->assertSame('FOTO', $anterior->fresh()->tipo_documento);
    }

    public function test_exportacion_documental_no_inventa_fecha_de_carga_ni_falla_ante_duplicidad(): void
    {
        $primera = $this->documento('FOTO', 'VALIDADO');
        $primera->update(['fecha_validacion' => '2020-03-04 12:00:00']);
        $this->documento('FOTO_DOC');
        $datos = ['usuario' => $this->titular, 'nombre_rol' => 'Administración', 'area' => 'Sin asignación',
            'avance_documental' => app(DocumentacionUsuarioService::class)->calcularAvanceDocumental($this->titular),
            'checklist' => app(DocumentacionUsuarioService::class)->obtenerChecklistUsuario($this->titular),
            'fecha' => now()->format('d/m/Y'), 'usuario_solicitante' => 'Persona solicitante'];
        $html = view('pdf.exports.usuarios.documentacion_pdf', $datos)->render();
        $this->assertStringContainsString('Revisión necesaria', $html);
        $this->assertStringContainsString('No registrada', $html);
        $this->assertStringNotContainsString('04/03/2020', $html);
        $this->assertStringContainsString($primera->nombre, $html);
    }

    public function test_ficha_resuelve_archivo_privado_mediante_ruta_que_reautoriza(): void
    {
        $documento = $this->documento('FOTO_DOC');
        Livewire::test(UsuarioFichaPanel::class, ['usuario' => $this->titular])
            ->call('descargarDoc', $documento->getKey())->assertRedirect(route('admin.personal.documento.archivo', $documento));
        $this->get(route('admin.personal.documento.archivo', $documento))->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $otro = User::factory()->create(['nombres' => 'Otra persona', 'ap_paterno' => 'Prueba']);
        $otro->assignRole('ADMINISTRADOR');
        $this->actingAs($otro)->get(route('admin.personal.documento.archivo', $documento))->assertForbidden();
        Storage::disk('local')->assertExists($documento->ruta_archivo);
    }

    public function test_reemplazo_desde_ficha_conserva_tipo_original_y_cadena_foto_doc(): void
    {
        $anterior = $this->documento('FOTO_DOC', 'OBSERVADO');
        $foto = $this->foto();
        Livewire::test(UsuarioFichaPanel::class, ['usuario' => $this->titular])->set('activeTab', 'documentacion')
            ->assertSee("abrirSubida('FOTO_DOC')", false);
        $nuevo = app(DocumentacionUsuarioService::class)->subirDocumento($this->titular, $foto['documento']->tipo_documento,
            UploadedFile::fake()->image('nueva.jpg'), subidoPor: $this->titular);
        $this->assertSame('FOTO_DOC', $nuevo->tipo_documento);
        $this->assertSame($anterior->getKey(), $nuevo->cod_documento_anterior);
        $this->assertSame('REEMPLAZADO', $anterior->fresh()->estado);
        $this->assertFalse($this->foto()['conflicto']);
        $this->assertSame($nuevo->getKey(), $this->foto()['documento']->getKey());
        Storage::disk('local')->assertExists($anterior->ruta_archivo);
        $this->assertStringStartsWith('documentos_personal_privados/', $nuevo->ruta_archivo);
        Storage::disk('local')->assertExists($nuevo->ruta_archivo);
        Storage::disk('public')->assertMissing($nuevo->ruta_archivo);
    }

    public function test_documento_de_contexto_contacto_o_preadmision_no_cubre_requisito_laboral(): void
    {
        $contacto = Contacto::create(['nombres' => 'Contacto', 'apellido_paterno' => 'Prueba', 'estado' => 'ACTIVO']);
        $preadmision = Preadmision::create(['cod_contacto' => $contacto->getKey(), 'cod_usuario_registro' => $this->titular->getKey(),
            'nombres' => 'Postulante', 'apellido_paterno' => 'Sintética', 'fecha_nacimiento' => '1945-05-05',
            'motivo_ingreso' => 'Solicitud de acompañamiento', 'fecha_solicitud' => now(), 'estado' => 'PENDIENTE']);
        $mixtoContacto = $this->documento('FOTO_DOC', 'VALIDADO');
        $mixtoContacto->update(['cod_contacto' => $contacto->getKey()]);
        $mixtoPreadmision = $this->documento('FOTO', 'VALIDADO');
        $mixtoPreadmision->update(['cod_preadmision' => $preadmision->getKey()]);
        $foto = $this->foto();
        $this->assertFalse($foto['cargado']);
        $this->assertNull($foto['documento']);
        $this->assertCount(0, $foto['documentos_candidatos']);
        $this->assertSame(0, app(DocumentacionUsuarioService::class)->calcularAvanceDocumental($this->titular)['validados']);
        $this->get(route('admin.personal.documento.archivo', $mixtoContacto))->assertForbidden();
        $this->get(route('admin.personal.documento.archivo', $mixtoPreadmision))->assertForbidden();
    }

    public function test_sustituir_no_enlaza_ni_modifica_un_antecesor_de_contexto_ajeno(): void
    {
        $contacto = Contacto::create(['nombres' => 'Contacto', 'apellido_paterno' => 'Prueba', 'estado' => 'ACTIVO']);
        $mixto = $this->documento('FOTO_DOC', 'OBSERVADO');
        $mixto->update(['cod_contacto' => $contacto->getKey()]);
        $antes = $mixto->fresh()->getAttributes();
        $laboral = $this->documento('FOTO_DOC', 'OBSERVADO');
        $nuevo = app(DocumentacionUsuarioService::class)->subirDocumento($this->titular, 'FOTO_DOC',
            UploadedFile::fake()->image('sucesora.jpg'), subidoPor: $this->titular);
        $this->assertSame($laboral->getKey(), $nuevo->cod_documento_anterior);
        $this->assertSame('REEMPLAZADO', $laboral->fresh()->estado);
        $this->assertSame($antes, $mixto->fresh()->getAttributes());
    }

    public function test_permiso_generico_de_subida_no_permite_cambiar_el_expediente_de_otro_trabajador(): void
    {
        $otro = User::factory()->create(['nombres' => 'Otro trabajador', 'ap_paterno' => 'Prueba']);
        $otro->assignRole('ADMINISTRADOR');
        $otro->givePermissionTo('documentos.subir');
        try {
            app(DocumentacionUsuarioService::class)->subirDocumento($this->titular, 'FOTO_DOC',
                UploadedFile::fake()->image('ajena.jpg'), subidoPor: $otro);
            $this->fail('Un permiso genérico no sustituye el alcance de la ficha laboral.');
        } catch (HttpException $error) {
            $this->assertSame(403, $error->getStatusCode());
        }
        $this->assertDatabaseCount('documentos', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_correccion_con_capacidad_nominal_conserva_fecha_cargador_y_emision_por_separado(): void
    {
        (require base_path('database/propuestas/gerencia/2026_10_26_000001_documentacion_y_estado_laboral.php'))->up();
        $this->travelTo(Carbon::parse('2026-10-26 10:00:00'));
        $anterior = $this->documento('FOTO_DOC', 'OBSERVADO');
        $nuevo = app(DocumentacionUsuarioService::class)->subirDocumento($this->titular, 'FOTO_DOC',
            UploadedFile::fake()->image('corregida.jpg'), fechaEmision: '2026-10-25', subidoPor: $this->titular);
        $this->assertSame($this->titular->getKey(), $nuevo->cod_usuario_carga);
        $this->assertSame('2026-10-26 10:00:00', $nuevo->fecha_hora_carga->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-25', $nuevo->fecha_emision->toDateString());
        $this->assertSame($anterior->getKey(), $nuevo->cod_documento_anterior);
        $this->assertNull($nuevo->cod_usuario_validacion);
        $this->assertNull($nuevo->fecha_validacion);
        Storage::disk('local')->assertExists($nuevo->ruta_archivo);
        Storage::disk('public')->assertMissing($nuevo->ruta_archivo);
    }

    public static function formulariosDeCorreccion(): array
    {
        return ['ficha' => [UsuarioFichaPanel::class, 'abrirSubida', 'archivoSubida', 'subirArchivo', 'mostrarSubidaModal'],
            'directorio' => [UsuariosPanel::class, 'abrirModalSubirDoc', 'archivoTemporal', 'guardarDocumento', 'mostrarModalSubirDoc']];
    }

    #[DataProvider('formulariosDeCorreccion')]
    public function test_formularios_sobre_mismo_original_rechazan_el_obsoleto_y_permiten_reabrir(string $clase, string $abrir, string $campo, string $guardar, string $modal): void
    {
        $gestor = User::factory()->create();
        $gestor->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($gestor);
        $original = $this->documento('FOTO_DOC', 'OBSERVADO');
        $componentes = [];
        foreach ([1, 2] as $numero) {
            $panel = Livewire::test($clase, $clase === UsuarioFichaPanel::class ? ['usuario' => $this->titular] : []);
            if ($clase === UsuariosPanel::class) $panel->call('verUsuario', $this->titular->getKey());
            $componentes[] = $panel->call($abrir, 'FOTO_DOC')->assertSet('referenciaDocumento.cod_documento', $original->getKey());
        }
        $componentes[0]->set($campo, UploadedFile::fake()->image('ganadora.jpg'))->call($guardar)
            ->assertHasNoErrors()->assertSet($modal, false);
        $sucesor = Documento::where('cod_documento_anterior', $original->getKey())->sole();
        $archivosAntes = Storage::disk('local')->allFiles();
        $componentes[1]->set($campo, UploadedFile::fake()->image('obsoleta.jpg'))->call($guardar)
            ->assertHasErrors($campo)->assertSet($modal, true)
            ->assertSee('El documento cambió mientras estaba abierto este formulario');
        $this->assertNotNull($componentes[1]->get($campo));
        $this->assertDatabaseCount('documentos', 2);
        $this->assertSame($archivosAntes, Storage::disk('local')->allFiles());
        $this->assertSame('REEMPLAZADO', $original->fresh()->estado);
        $this->assertSame('CARGADO', $sucesor->fresh()->estado);
        // Abrir expresamente la versión vigente permite una corrección posterior legítima.
        $componentes[1]->call($abrir, 'FOTO_DOC')->assertSet('referenciaDocumento.cod_documento', $sucesor->getKey())
            ->set($campo, UploadedFile::fake()->image('posterior.jpg'))->call($guardar)->assertHasNoErrors()->assertSet($modal, false);
        $this->assertDatabaseCount('documentos', 3);
        $this->assertSame(1, Documento::where('estado', 'CARGADO')->count());
        $this->assertSame($sucesor->getKey(), Documento::where('estado', 'CARGADO')->sole()->cod_documento_anterior);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_referencia_de_otro_titular_o_tipo_no_permite_sustitucion(): void
    {
        $this->titular->givePermissionTo('documentos.subir');
        $original = $this->documento('FOTO_DOC', 'OBSERVADO');
        $servicio = app(DocumentacionUsuarioService::class);
        $referencia = $servicio->referenciaVigente($this->titular, 'FOTO_DOC', $this->titular);
        $archivos = Storage::disk('local')->allFiles();
        foreach ([['cod_usuario' => 'TITULAR_AJENO'], ['tipo_documento' => 'CI'], ['cod_documento' => []]] as $alteracion) {
            try {
                $servicio->subirDocumento($this->titular, 'FOTO_DOC', UploadedFile::fake()->image('alterada.jpg'),
                    subidoPor: $this->titular, referenciaEsperada: [...$referencia, ...$alteracion]);
                $this->fail('No se puede cambiar el contexto del original.');
            } catch (ValidationException $error) {
                $this->assertArrayHasKey('file', $error->errors());
            }
        }
        $this->assertDatabaseCount('documentos', 1);
        $this->assertSame('OBSERVADO', $original->fresh()->estado);
        $this->assertSame($archivos, Storage::disk('local')->allFiles());
    }

    public function test_foto_con_clave_distinta_no_crea_otro_documento_vigente(): void
    {
        $this->titular->givePermissionTo('documentos.subir');
        $original = $this->documento('FOTO_DOC');
        try {
            app(DocumentacionUsuarioService::class)->subirDocumento($this->titular, 'FOTO', UploadedFile::fake()->image('otra.jpg'), subidoPor: $this->titular);
            $this->fail('La clave alternativa no puede eludir el original.');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('file', $error->errors());
        }
        $this->assertDatabaseCount('documentos', 1);
        $this->assertSame('FOTO_DOC', $original->fresh()->tipo_documento);
        $this->assertSame('CARGADO', $original->fresh()->estado);
        $this->assertFalse(Documento::where('tipo_documento', 'FOTO')->exists());
    }

    public function test_referencia_livewire_no_se_puede_manipular_desde_el_cliente(): void
    {
        $this->titular->givePermissionTo('documentos.subir');
        $original = $this->documento('FOTO_DOC');
        $panel = Livewire::test(UsuarioFichaPanel::class, ['usuario' => $this->titular])->call('abrirSubida', 'FOTO_DOC');
        try {
            $panel->set('referenciaDocumento.cod_documento', null);
            $this->fail('La referencia se captura en el servidor.');
        } catch (\Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException) {
            $this->addToAssertionCount(1);
        }
        $this->assertDatabaseCount('documentos', 1);
        $this->assertSame('CARGADO', $original->fresh()->estado);
    }
}
