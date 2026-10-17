<?php

namespace Tests\Feature;

use App\Compartido\Aplicacion\Contratos\ArchivosAplicacion;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\DocumentoClinico;
use App\Models\Herida;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Clinica\Presentacion\Livewire\Paginas\FichaPaciente;
use App\Modulos\Cuidados\Aplicacion\Acciones\LesionesEnfermeriaService;
use App\Modulos\Cuidados\Aplicacion\Acciones\RegistrarCuidadosDeTurno;
use App\Modulos\Cuidados\Presentacion\Livewire\Paginas\MisPacientes;
use App\Modulos\Cuidados\Presentacion\Livewire\Paginas\RegistrosEnfermeria;
use Database\Seeders\RolesAndPermissionsSeeder;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\GenerateSignedUploadUrl;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class FotografiasHeridasTest extends TestCase
{
    use RefreshDatabase;

    private User $enfermera;

    private Personal $personal;

    private Residente $residente;

    private Herida $herida;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->startOfDay()->addHours(10));
        $this->seed(RolesAndPermissionsSeeder::class);
        config(['remembermind.acceso_clinico_temporal_superadmin' => false]);
        Storage::fake('local');
        Storage::fake('public');
        $this->enfermera = User::factory()->create(['estado' => 'ACTIVO']);
        $this->enfermera->assignRole('ENFERMEROS');
        $this->personal = Personal::create(['cod_personal' => 'PER_FOTO', 'cod_usuario' => $this->enfermera->getKey(), 'nombres' => 'Profesional', 'apellido_paterno' => 'Sintética', 'numero_documento' => 'FOTO-SYNTH', 'profesion' => 'ENFERMERIA', 'estado' => 'ACTIVO']);
        Area::create(['cod_area' => 'ARE_FOTO', 'nombre' => 'Cuidados sintéticos', 'estado' => 'ACTIVA']);
        Turno::create(['cod_turno' => 'TUR_FOTO', 'nombre' => 'Turno sintético', 'hora_inicio' => '07:00', 'hora_cierre' => '19:00', 'estado' => 'ACTIVO']);
        Jornada::create(['cod_jornada' => 'JOR_FOTO', 'cod_turno' => 'TUR_FOTO', 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        AsignacionPersonal::create(['cod_asignacion_personal' => 'ASP_FOTO', 'cod_personal' => 'PER_FOTO', 'cod_jornada' => 'JOR_FOTO', 'cod_area' => 'ARE_FOTO', 'funcion' => 'ENFERMERA', 'tipo_asignacion' => 'TURNO', 'fecha_asignacion' => today(), 'estado' => 'ACTIVO']);
        $this->residente = Residente::crearDesdeAdmision(['cod_residente' => 'RES_FOTO', 'nombres' => 'Residente', 'apellido_paterno' => 'Sintético', 'fecha_nacimiento' => '1940-01-01', 'estado' => 'ADMITIDO']);
        AsignacionResidenteJornada::create(['cod_asignacion' => 'ARJ_FOTO', 'cod_residente' => 'RES_FOTO', 'cod_personal' => 'PER_FOTO', 'cod_jornada' => 'JOR_FOTO', 'fecha_hora' => now(), 'estado' => 'ACTIVA']);
        $this->herida = Herida::create(['cod_herida' => 'HER_FOTO', 'cod_residente' => 'RES_FOTO', 'cod_personal' => 'PER_FOTO', 'tipo_herida' => 'Herida sintética', 'ubicacion' => 'Antebrazo', 'fecha_hora_identificacion' => now(), 'estado' => 'ACTIVA']);
        $this->actingAs($this->enfermera);
    }

    private function entrada(array $fotos = []): array
    {
        return ['codResidente' => 'RES_FOTO', 'lesionId' => 'HER_FOTO', 'largoLesion' => 2.5, 'anchoLesion' => 1.5, 'profundidadLesion' => 0.25, 'dolorLesion' => 0, 'aspectoLesion' => 'Descripción sintética', 'exudadoLesion' => '', 'accionLesion' => 'Procedimiento sintético completo', 'observacionLesion' => '', 'fotografias' => $fotos, 'descripcionesFotos' => ['Descripción de prueba'], 'cod_personal' => 'OTRO', 'hash_archivo' => 'FORJADO'];
    }

    public function test_reintento_de_foto_valida_limpia_solo_errores_de_carga_y_preserva_captura(): void
    {
        $componente = Livewire::test(RegistrosEnfermeria::class, ['codResidente' => 'RES_FOTO', 'emergente' => true])->instance();
        $componente->accionLesion = 'Procedimiento conservado';
        $componente->addError('loteFotografias', 'Carga anterior fallida');
        $componente->addError('fotografias.0', 'Foto anterior inválida');
        $componente->addError('respuestaLesion', 'Error de otro campo');
        $componente->loteFotografias = [UploadedFile::fake()->image('reintento.png')];

        $componente->updatedLoteFotografias();

        $this->assertFalse($componente->getErrorBag()->has('loteFotografias'));
        $this->assertFalse($componente->getErrorBag()->has('fotografias.0'));
        $this->assertTrue($componente->getErrorBag()->has('respuestaLesion'));
        $this->assertSame('Procedimiento conservado', $componente->accionLesion);
        $this->assertCount(1, $componente->fotografias);
        $this->assertDatabaseCount('curaciones_herida', 0);
        $this->assertDatabaseCount('documentos_clinicos', 0);
    }

    public function test_selector_heridas_abre_popup_en_el_directorio_y_regresa_sin_escribir(): void
    {
        $vista = Livewire::test(MisPacientes::class)
            ->call('seleccionarResidente', 'RES_FOTO')->call('mostrarSelectorRegistro')
            ->assertSeeHtml("abrirFormularioRegistro('heridas')")
            ->call('abrirFormularioRegistro', 'heridas')
            ->assertSet('registroTipo', 'heridas')->assertSet('drawerPaso', 'register-form')
            ->assertSeeHtml('data-wound-popup="true"')
            ->call('cerrarPopupHeridas')->assertSet('drawerPaso', 'register-selector')
            ->assertSet('registroTipo', null)->assertSet('mostrarSelectorModal', true);
        $this->assertDatabaseCount('heridas', 1);
        $this->assertDatabaseCount('curaciones_herida', 0);
        $this->assertDatabaseCount('documentos_clinicos', 0);
    }

    public function test_popup_elige_curacion_activa_y_sin_heridas_abre_identificacion(): void
    {
        Livewire::test(RegistrosEnfermeria::class, ['codResidente' => 'RES_FOTO', 'emergente' => true])
            ->assertSet('mostrarFormularioHerida', true)
            ->assertSet('lesionId', 'HER_FOTO')->assertSeeHtml('data-wound-operation="curacion"')
            ->assertDontSee('Sin heridas activas registradas')
            ->assertDontSeeHtml('id="residente-registro"');
        $this->herida->update(['estado' => 'CERRADA']);
        Livewire::test(RegistrosEnfermeria::class, ['codResidente' => 'RES_FOTO', 'emergente' => true])
            ->assertSet('mostrarFormularioHerida', true)
            ->assertSet('lesionId', '')->assertSeeHtml('data-wound-operation="nueva-herida"')
            ->assertSee('Sin heridas activas registradas')->assertSee('Registrar primera curación');
        $this->assertDatabaseCount('heridas', 1);
        $this->assertDatabaseCount('curaciones_herida', 0);
    }

    public function test_popup_no_elude_permiso_clinico_revocado(): void
    {
        $this->enfermera->roles->first()->revokePermissionTo('curaciones_herida.crear');
        Livewire::test(RegistrosEnfermeria::class, ['codResidente' => 'RES_FOTO', 'emergente' => true])->assertForbidden();
        $this->assertDatabaseCount('curaciones_herida', 0);
        $this->assertDatabaseCount('documentos_clinicos', 0);
    }

    public function test_ficha_abre_el_mismo_popup_sin_cambiar_tab_ni_persistir(): void
    {
        Livewire::test(FichaPaciente::class, ['adulto' => 'RES_FOTO'])
            ->call('abrirModalHeridas')->assertSet('modalHeridas', true)->assertSet('tabActivo', 'resumen')
            ->assertSeeHtml('data-wound-popup="true"')
            ->call('cerrarPopupHeridas')->assertSet('modalHeridas', false);
        $this->assertDatabaseCount('heridas', 1);
        $this->assertDatabaseCount('curaciones_herida', 0);
        $this->assertDatabaseCount('documentos_clinicos', 0);
    }

    public function test_preparar_otra_herida_limpia_captura_sin_modificar_la_herida_existente(): void
    {
        Livewire::test(RegistrosEnfermeria::class, ['codResidente' => 'RES_FOTO', 'emergente' => true])
            ->set('accionLesion', 'Captura sin guardar')->call('prepararNuevaHerida')
            ->assertSet('lesionId', '')->assertSet('accionLesion', '')->assertSet('registrandoNuevaHerida', true);
        $this->assertDatabaseHas('heridas', ['cod_herida' => 'HER_FOTO', 'estado' => 'ACTIVA']);
        $this->assertDatabaseCount('heridas', 1);
        $this->assertDatabaseCount('curaciones_herida', 0);
    }

    public function test_obligatorios_del_formulario_solo_participan_en_su_operacion(): void
    {
        $vista = Livewire::test(RegistrosEnfermeria::class, ['codResidente' => 'RES_FOTO', 'emergente' => true]);
        $documento = new \DOMDocument;
        $anterior = libxml_use_internal_errors(true);
        try {
            $documento->loadHTML('<?xml encoding="UTF-8">'.$vista->html());
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($anterior);
        }
        $xpath = new \DOMXPath($documento);
        $obligatorios = $xpath->query('//form[@id="clinical-wound-form"]//*[self::input or self::select or self::textarea][@required]');
        $this->assertSame(4, $obligatorios->length);
        // Contrato HTML: x-show no excluye controles de validación; disabled sí.
        // La ejecución real de Alpine y la validación del navegador requieren QA runtime.
        foreach ([true, false] as $nueva) {
            $activos = [];
            foreach ($obligatorios as $control) {
                $deshabilitado = false;
                for ($padre = $control; $padre instanceof \DOMElement; $padre = $padre->parentNode) {
                    $condicion = $padre->getAttribute(':disabled');
                    $deshabilitado = $deshabilitado || $padre->hasAttribute('disabled')
                        || ($condicion === 'nueva' && $nueva) || ($condicion === '!nueva' && ! $nueva);
                }
                if (! $deshabilitado) {
                    $activos[] = $control->getAttribute('id');
                }
            }
            $this->assertSame($nueva
                ? ['nueva-nuevaHeridaTipo', 'nueva-nuevaHeridaUbicacion']
                : ['curacion-aspectoLesion', 'curacion-accionLesion'], $activos);
        }
    }

    public function test_identificacion_ofrece_ilustraciones_locales_para_tipos_y_ubicaciones(): void
    {
        $vista = Livewire::test(RegistrosEnfermeria::class, ['codResidente' => 'RES_FOTO', 'emergente' => true]);
        $documento = new \DOMDocument;
        $anterior = libxml_use_internal_errors(true);
        try {
            $documento->loadHTML('<?xml encoding="UTF-8">'.$vista->html());
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($anterior);
        }
        $xpath = new \DOMXPath($documento);
        foreach (['Tipo' => 13, 'Ubicacion' => 38] as $campo => $cantidad) {
            $base = '//div[@id="nueva-nuevaHerida'.$campo.'-examples"]/button';
            $this->assertSame($cantidad, $xpath->query($base)->length);
            $this->assertSame($cantidad, $xpath->query($base.'[.//svg and @type="button"]')->length);
            $this->assertSame(0, $xpath->query($base.'//image | '.$base.'//img')->length);
        }
        $this->assertSame(2, $xpath->query('//details[contains(@class,"rm-free-text-select__examples")]')->length);
        $vista->assertSee('Ver opciones ilustradas')->assertSee('Esquemas de apoyo; no representan el aspecto clínico ni determinan la clasificación.');
        $this->assertDatabaseCount('heridas', 1);
        $this->assertDatabaseCount('curaciones_herida', 0);
    }

    public function test_nueva_herida_rechaza_obligatorios_vacios_y_permite_reintentar_sin_campos_de_curacion(): void
    {
        $vista = Livewire::test(RegistrosEnfermeria::class, ['codResidente' => 'RES_FOTO', 'emergente' => true])
            ->call('guardarNuevaHerida')->assertHasErrors(['nuevaHeridaTipo' => 'required', 'nuevaHeridaUbicacion' => 'required'])
            ->assertSet('resultadoHerida', [])->assertNotDispatched('herida-evidencia-registrada');
        $this->assertDatabaseCount('heridas', 1);
        $this->assertDatabaseCount('curaciones_herida', 0);
        $vista->set('nuevaHeridaTipo', 'Abrasión')->set('nuevaHeridaUbicacion', 'Talón derecho')
            ->call('guardarNuevaHerida')->assertHasNoErrors()->assertDispatched('herida-evidencia-registrada');
        $this->assertDatabaseHas('heridas', ['tipo_herida' => 'Abrasión', 'ubicacion' => 'Talón derecho', 'cod_residente' => 'RES_FOTO', 'cod_personal' => 'PER_FOTO']);
        $this->assertDatabaseCount('heridas', 2);
        $this->assertDatabaseCount('curaciones_herida', 0);
    }

    public function test_nueva_herida_confirma_datos_persistidos_y_prepara_primera_curacion_sin_escribirla(): void
    {
        $vista = Livewire::withQueryParams(['cuidado' => 'heridas', 'seccion' => 'HERIDAS'])->test(RegistrosEnfermeria::class, ['codResidente' => 'RES_FOTO'])
            ->set('nuevaHeridaTipo', 'Tipo de prueba')->set('nuevaHeridaUbicacion', 'Talón sintético')
            ->set('nuevaHeridaClasificacion', 'Clasificación de prueba')->set('nuevaHeridaObservacion', 'Observación inicial de prueba')
            ->call('guardarNuevaHerida')->assertHasNoErrors()
            ->assertSet('resultadoHerida.tipo_herida', 'Tipo de prueba')->assertSet('resultadoHerida.clasificacion', 'Clasificación de prueba')
            ->assertSee('Herida registrada')->assertSee('Registrar primera curación');
        $codigo = $vista->get('resultadoHerida.cod_herida');
        $vista->call('prepararPrimeraCuracion')->assertHasNoErrors()->assertSet('lesionId', $codigo)
            ->assertSet('registrandoNuevaHerida', false)->assertSet('resultadoHerida', []);
        $this->assertDatabaseHas('heridas', ['cod_herida' => $codigo, 'tipo_herida' => 'Tipo de prueba', 'cod_personal' => 'PER_FOTO']);
        $this->assertDatabaseCount('heridas', 2);
        $this->assertDatabaseCount('curaciones_herida', 0);
    }

    public function test_http_real_admite_cinco_fotos_de_ocho_mb_y_persiste_evidencia_privada(): void
    {
        $root = sys_get_temp_dir().'/remembermind-wound-http-'.bin2hex(random_bytes(8));
        File::makeDirectory($root);
        foreach (['views', 'sessions', 'temporary', 'private'] as $directorio) {
            File::makeDirectory($root.'/'.$directorio);
        }
        touch($root.'/qa.sqlite');
        // PNG válido: el chunk textual de QA permite verificar el límite exacto sin datos reales.
        $imagen = imagecreatetruecolor(16, 16);
        ob_start();
        imagepng($imagen);
        $png = ob_get_clean();
        imagedestroy($imagen);
        $texto = "QA\0".str_repeat('X', 8192 * 1024 - strlen($png) - 15);
        $chunk = pack('N', strlen($texto)).'tEXt'.$texto.pack('N', crc32('tEXt'.$texto));
        $archivo = $root.'/evidencia-sintetica.png';
        file_put_contents($archivo, substr($png, 0, -12).$chunk.substr($png, -12));
        $this->assertSame(8192 * 1024, filesize($archivo));
        $listener = stream_socket_server('tcp://127.0.0.1:0', $codigo, $mensaje);
        $this->assertNotFalse($listener);
        $host = stream_socket_get_name($listener, false);
        fclose($listener);
        $base = 'http://'.$host;
        $proceso = new Process([PHP_BINARY, '-d', 'upload_max_filesize=8M', '-d', 'post_max_size=45M', '-S', $host, '-t', public_path(), base_path('tests/Support/heridas_upload_router.php')], base_path(), [
            'RM_QA_HTTP_ROOT' => $root, 'APP_KEY' => config('app.key'), 'APP_URL' => $base,
        ]);
        $proceso->setTimeout(45);
        try {
            $proceso->start();
            $this->assertTrue($proceso->waitUntil(function () use ($host) {
                $socket = @stream_socket_client('tcp://'.$host, $codigo, $mensaje, 0.1);
                if (! $socket) {
                    return false;
                }
                fclose($socket);

                return true;
            }), 'El servidor HTTP de QA debe arrancar.');
            URL::forceRootUrl($base);
            // La sesión HTTP usa el reloj real del SO; la captura clínica del test sigue congelada.
            $url = Carbon::withTestNow(null, fn () => app(GenerateSignedUploadUrl::class)->forLocal());
            $cliente = Http::acceptJson()->timeout(30)->withOptions(['cookies' => new CookieJar]);
            $login = $cliente->get($base.'/login');
            $this->assertSame(200, $login->status());
            $this->assertSame(1, preg_match('/name="csrf-token"\s+content="([^"]+)"/', $login->body(), $token));
            $this->assertSame(419, $cliente->attach('files[]', fopen($archivo, 'rb'), 'sin-csrf.png')->post($url)->status());
            $cliente = $cliente->withHeaders(['X-CSRF-TOKEN' => $token[1]]);
            $peticion = clone $cliente;
            for ($i = 0; $i < 5; $i++) {
                $peticion = $peticion->attach('files[]', fopen($archivo, 'rb'), 'sintetica-'.$i.'.png');
            }
            $respuesta = $peticion->post($url);
            $this->assertSame(200, $respuesta->status(), $respuesta->body());
            $paths = $respuesta->json('paths');
            $this->assertCount(5, $paths);
            config(['filesystems.disks.tmp-for-tests' => ['driver' => 'local', 'root' => $root.'/private', 'throw' => true]]);
            Storage::forgetDisk('tmp-for-tests');
            $fotos = [];
            foreach ($paths as $signedPath) {
                $path = TemporaryUploadedFile::extractPathFromSignedPath($signedPath);
                $this->assertNotFalse($path, 'La referencia temporal debe conservar su firma Livewire válida.');
                $fotos[] = TemporaryUploadedFile::createFromLivewire($path);
            }
            foreach ($fotos as $foto) {
                $this->assertSame(8192 * 1024, $foto->getSize());
            }
            $curacion = app(RegistrarCuidadosDeTurno::class)->guardarSeguimientoLesion($this->enfermera, $this->entrada($fotos));
            $this->assertDatabaseCount('curaciones_herida', 1);
            $this->assertDatabaseCount('documentos_clinicos', 5);
            foreach (DocumentoClinico::all() as $documento) {
                $this->assertSame($curacion->getKey(), $documento->cod_curacion);
                $this->assertSame(8192 * 1024, $documento->tamano_bytes);
                $this->assertSame(hash_file('sha256', $archivo), $documento->hash_archivo);
                Storage::disk('local')->assertExists($documento->ruta_archivo);
            }
            $this->assertSame([], Storage::disk('public')->allFiles());
            $this->assertSame(401, $cliente->attach('files[]', fopen($archivo, 'rb'), 'sin-firma.png')->post(strtok($url, '?'))->status());
            $grande = $root.'/excede-limite.png';
            copy($archivo, $grande);
            file_put_contents($grande, 'X', FILE_APPEND);
            $rechazo = $cliente->attach('files[]', fopen($grande, 'rb'), 'excede-limite.png')->post($url);
            $this->assertSame(422, $rechazo->status(), $rechazo->body());
            $this->assertDatabaseCount('documentos_clinicos', 5);
        } finally {
            $proceso->stop();
            URL::forceRootUrl(null);
            Storage::forgetDisk('tmp-for-tests');
            if (realpath(dirname($root)) === realpath(sys_get_temp_dir()) && preg_match('/^remembermind-wound-http-[a-f0-9]{16}$/D', basename($root))) {
                File::deleteDirectory($root);
            }
        }
    }

    public function test_cierre_del_formulario_conserva_historia_y_rechaza_repeticion_sin_exito_falso(): void
    {
        $this->herida->update(['observacion' => 'Observación original conservada']);
        $curacion = app(RegistrarCuidadosDeTurno::class)->guardarSeguimientoLesion($this->enfermera, $this->entrada());
        $vista = Livewire::test(RegistrosEnfermeria::class, ['codResidente' => 'RES_FOTO'])
            ->set('resultadoCierreLesion', 'Cierre sintético verificado')->set('motivoCierreLesion', 'Motivo profesional sintético')
            ->call('cerrarLesion', 'HER_FOTO')->assertHasNoErrors()->assertDispatched('swal');
        $cerrada = $this->herida->fresh();
        $this->assertSame('CERRADA', $cerrada->estado);
        $this->assertStringContainsString('Observación original conservada', $cerrada->observacion);
        $this->assertStringContainsString('Motivo profesional sintético', $cerrada->observacion);
        $this->assertTrue($cerrada->fecha_hora_cierre->equalTo(now()));
        $this->assertDatabaseHas('curaciones_herida', ['cod_curacion' => $curacion->getKey(), 'procedimiento' => 'Procedimiento sintético completo']);
        $vista->set('resultadoCierreLesion', 'Segundo cierre improcedente')->set('motivoCierreLesion', 'No debe reemplazar el cierre previo')
            ->call('cerrarLesion', 'HER_FOTO')->assertHasErrors('resultadoCierreLesion')->assertNotDispatched('swal')
            ->assertSet('resultadoCierreLesion', 'Segundo cierre improcedente')->assertSet('motivoCierreLesion', 'No debe reemplazar el cierre previo');
        $this->assertSame($cerrada->observacion, $this->herida->fresh()->observacion);
        $this->assertTrue($cerrada->fecha_hora_cierre->equalTo($this->herida->fresh()->fecha_hora_cierre));
        $this->assertDatabaseCount('curaciones_herida', 1);
    }

    public function test_cierre_sin_permiso_no_modifica_herida(): void
    {
        $this->enfermera->roles->first()->revokePermissionTo('atenciones.crear');
        try {
            app(RegistrarCuidadosDeTurno::class)->cerrarLesion($this->enfermera, [
                'codResidente' => 'RES_FOTO', 'resultadoCierreLesion' => 'Resultado sintético', 'motivoCierreLesion' => 'Motivo sintético válido',
            ], 'HER_FOTO');
            $this->fail('El cierre requiere el permiso vigente.');
        } catch (HttpException $error) {
            $this->assertSame(403, $error->getStatusCode());
        }
        $this->assertSame('ACTIVA', $this->herida->fresh()->estado);
        $this->assertNull($this->herida->fresh()->fecha_hora_cierre);
        $this->assertDatabaseCount('curaciones_herida', 0);
    }

    public function test_preparar_primera_curacion_sin_resultado_o_sin_permiso_es_rechazado(): void
    {
        Livewire::test(RegistrosEnfermeria::class, ['codResidente' => 'RES_FOTO'])
            ->set('lesionId', 'HER_FOTO')->call('prepararPrimeraCuracion')->assertStatus(409);
        $vista = Livewire::test(RegistrosEnfermeria::class, ['codResidente' => 'RES_FOTO'])
            ->set('nuevaHeridaTipo', 'Tipo de prueba')->set('nuevaHeridaUbicacion', 'Talón sintético')
            ->call('guardarNuevaHerida')->assertHasNoErrors();
        $this->enfermera->roles->first()->revokePermissionTo('curaciones_herida.crear');
        $vista->call('prepararPrimeraCuracion')->assertForbidden();
        $this->assertDatabaseCount('curaciones_herida', 0);
    }

    public static function formatos(): array
    {
        return [['jpg', 'image/jpeg'], ['jpeg', 'image/jpeg'], ['png', 'image/png'], ['webp', 'image/webp']];
    }

    #[DataProvider('formatos')]
    public function test_foto_privada_con_metadatos_servidor_y_endpoint_autorizado(string $extension, string $mime): void
    {
        $foto = UploadedFile::fake()->image('nombre-no-confiable.'.$extension);
        $curacion = app(RegistrarCuidadosDeTurno::class)->guardarSeguimientoLesion($this->enfermera, $this->entrada([$foto]));
        $documento = DocumentoClinico::sole();
        $this->assertSame('RES_FOTO', $documento->cod_residente);
        $this->assertSame('PER_FOTO', $documento->cod_personal);
        $this->assertNull($documento->cod_herida);
        $this->assertSame($curacion->getKey(), $documento->cod_curacion);
        $this->assertSame('CURACION_HERIDA', $documento->origen);
        $this->assertSame($mime, $documento->formato);
        $this->assertSame($foto->getSize(), $documento->tamano_bytes);
        $this->assertSame(hash_file('sha256', $foto->getRealPath()), $documento->hash_archivo);
        $this->assertStringNotContainsString('nombre-no-confiable', $documento->ruta_archivo);
        Storage::disk('local')->assertExists($documento->ruta_archivo);
        $this->assertSame([], Storage::disk('public')->allFiles());
        $response = $this->get(route('admin.cuidados.fotografias.ver', ['RES_FOTO', $documento->getKey()]));
        $response->assertOk()->assertHeader('Content-Type', $mime)->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertSame(file_get_contents($foto->getRealPath()), $response->streamedContent());
        $this->get(route('admin.cuidados.fotografias.ver', ['RES_FOTO', $documento->getKey()]).'?descargar=1')->assertOk()->assertDownload();
        $this->assertDatabaseHas('activity_log', ['log_name' => 'evidencia_heridas', 'causer_id' => $this->enfermera->getKey()]);
        $this->get('/storage/'.$documento->ruta_archivo)->assertForbidden();
    }

    public static function cantidades(): array
    {
        return [[1], [2], [5]];
    }

    #[DataProvider('cantidades')]
    public function test_varias_fotos_conservan_historial_y_relaciones(int $cantidad): void
    {
        app(RegistrarCuidadosDeTurno::class)->guardarSeguimientoLesion($this->enfermera, $this->entrada([UploadedFile::fake()->image('anterior.png')]));
        $anterior = DocumentoClinico::sole()->getAttributes();
        $nueva = app(RegistrarCuidadosDeTurno::class)->guardarSeguimientoLesion($this->enfermera, $this->entrada(array_map(fn () => UploadedFile::fake()->image('nueva.png'), range(1, $cantidad))));
        $this->assertSame($anterior, DocumentoClinico::find($anterior['cod_documento_clinico'])->getAttributes());
        $this->assertCount($cantidad, $nueva->documentosClinicos);
        $this->assertDatabaseCount('documentos_clinicos', $cantidad + 1);
        $this->assertDatabaseCount('curaciones_herida', 2);
        $this->assertSame('ACTIVA', $this->herida->fresh()->estado);
    }

    public function test_nueva_herida_con_foto_directa(): void
    {
        $herida = app(LesionesEnfermeriaService::class)->registrar('RES_FOTO', ['tipo' => 'Tipo sintético', 'zona_corporal' => 'Mano', 'fotografias' => [UploadedFile::fake()->image('master.png')]], $this->enfermera);
        $doc = DocumentoClinico::sole();
        $this->assertSame($herida->getKey(), $doc->cod_herida);
        $this->assertNull($doc->cod_curacion);
        $this->assertSame('HERIDA', $doc->origen);
        $this->assertCount(1, $herida->documentosClinicos);
        $this->assertDatabaseCount('curaciones_herida', 0);
    }

    public static function invalidos(): array
    {
        return [['svg'], ['gif'], ['pdf'], ['extension_falsa'], ['grande'], ['seis']];
    }

    #[DataProvider('invalidos')]
    public function test_upload_invalido_no_deja_registros_ni_archivos(string $caso): void
    {
        $fotos = match ($caso) {
            'svg' => [UploadedFile::fake()->createWithContent('foto.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>')],
            'gif' => [UploadedFile::fake()->image('foto.gif')],
            'pdf' => [UploadedFile::fake()->createWithContent('foto.pdf', '%PDF-1.4 contenido')],
            'extension_falsa' => [UploadedFile::fake()->createWithContent('foto.jpg', '<?php echo "no imagen";')],
            'grande' => [UploadedFile::fake()->image('grande.jpg')->size(8193)],
            'seis' => array_map(fn () => UploadedFile::fake()->image('foto.png'), range(1, 6)),
        };
        try {
            app(RegistrarCuidadosDeTurno::class)->guardarSeguimientoLesion($this->enfermera, $this->entrada($fotos));
            $this->fail('Debe rechazar archivo inválido.');
        } catch (ValidationException $e) {
            $this->assertNotEmpty($e->errors());
        }
        $this->assertDatabaseCount('documentos_clinicos', 0);
        $this->assertDatabaseCount('curaciones_herida', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public static function fallos(): array
    {
        return [['storage'], ['metadata'], ['db']];
    }

    #[DataProvider('fallos')]
    public function test_fallo_parcial_compensa_todos_los_archivos_y_db(string $caso): void
    {
        if ($caso === 'db') {
            DocumentoClinico::creating(function ($doc): void {
                $doc->cod_personal = 'PER_INEXISTENTE';
            });
        } elseif ($caso === 'metadata') {
            DocumentoClinico::creating(function ($doc) {
                if (DocumentoClinico::count() === 1) {
                    throw new \RuntimeException('Fallo sintético metadata');
                }
            });
        } else {
            $real = app(ArchivosAplicacion::class);
            $calls = 0;
            $mock = \Mockery::mock(ArchivosAplicacion::class);
            $mock->shouldReceive('recibirComo')->andReturnUsing(function ($file, $folder, $name, $disk) use ($real, &$calls) {
                if (++$calls === 2) {
                    throw new \RuntimeException('Fallo sintético storage');
                }

                return $real->recibirComo($file, $folder, $name, $disk);
            });
            $mock->shouldReceive('existe')->andReturnUsing(fn ($disk, $path) => $real->existe($disk, $path));
            $mock->shouldReceive('hash')->andReturnUsing(fn ($disk, $path) => $real->hash($disk, $path));
            $mock->shouldReceive('eliminar')->andReturnUsing(fn ($disk, $path) => $real->eliminar($disk, $path));
            $this->app->instance(ArchivosAplicacion::class, $mock);
        }
        try {
            app(RegistrarCuidadosDeTurno::class)->guardarSeguimientoLesion($this->enfermera, $this->entrada([UploadedFile::fake()->image('uno.png'), UploadedFile::fake()->image('dos.png')]));
            $this->fail('Debe fallar.');
        } catch (\RuntimeException $e) {
            if ($caso === 'db') {
                $this->assertInstanceOf(QueryException::class, $e);
            } else {
                $this->assertStringStartsWith('Fallo sintético', $e->getMessage());
            }
        } finally {
            DocumentoClinico::flushEventListeners();
            DocumentoClinico::clearBootedModels();
        }
        $this->assertDatabaseCount('curaciones_herida', 0);
        $this->assertDatabaseCount('documentos_clinicos', 0);
        $this->assertSame(0, DB::table('activity_log')->where('log_name', 'evidencia_heridas')->count());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_relaciones_cruzadas_y_exclusion_rechazadas_y_general_admitido(): void
    {
        $curacion = app(RegistrarCuidadosDeTurno::class)->guardarSeguimientoLesion($this->enfermera, $this->entrada());
        $base = ['cod_documento_clinico' => 'DOC_REL', 'cod_residente' => 'RES_FOTO', 'tipo_documento' => 'GENERAL', 'titulo' => 'General', 'ruta_archivo' => 'general.pdf', 'formato' => 'application/pdf', 'hash_archivo' => str_repeat('a', 64), 'fecha_hora' => now(), 'estado' => 'VIGENTE'];
        $this->assertNull(DocumentoClinico::create($base)->cod_herida);
        Residente::crearDesdeAdmision(['cod_residente' => 'RES_AJENO', 'nombres' => 'Otro sintético', 'apellido_paterno' => 'Prueba', 'fecha_nacimiento' => '1940-01-01', 'estado' => 'ADMITIDO']);
        foreach ([['cod_herida' => 'HER_FOTO', 'cod_curacion' => $curacion->getKey()], ['cod_residente' => 'RES_AJENO', 'cod_herida' => 'HER_FOTO'], ['cod_residente' => 'RES_AJENO', 'cod_curacion' => $curacion->getKey()], ['cod_herida' => 'NO_EXISTE'], ['cod_curacion' => 'NO_EXISTE']] as $cambio) {
            try {
                DocumentoClinico::create(array_replace($base, ['cod_documento_clinico' => 'DOC_RECHAZADO'], $cambio));
                $this->fail('Debe rechazar relación.');
            } catch (ValidationException) {
                $this->assertDatabaseCount('documentos_clinicos', 1);
            }
        }
    }

    public static function denegaciones(): array
    {
        return [['inactiva'], ['sin_permiso'], ['ajeno'], ['anulado'], ['familiar'], ['superadmin']];
    }

    #[DataProvider('denegaciones')]
    public function test_acceso_privado_denegado_sin_exponer_contenido(string $caso): void
    {
        app(RegistrarCuidadosDeTurno::class)->guardarSeguimientoLesion($this->enfermera, $this->entrada([UploadedFile::fake()->image('uno.png')]));
        $doc = DocumentoClinico::sole();
        match ($caso) {
            'inactiva' => $this->enfermera->update(['estado' => 'INACTIVO']),
            'sin_permiso' => $this->enfermera->syncRoles([]),
            'ajeno' => AsignacionResidenteJornada::query()->update(['estado' => 'FINALIZADA']),
            'anulado' => $doc->update(['estado' => 'ANULADO']),
            'familiar' => $this->enfermera->syncRoles(['FAMILIAR']),
            'superadmin' => $this->enfermera->syncRoles(['ADMINISTRADOR']),
        };
        $this->get(route('admin.cuidados.fotografias.ver', ['RES_FOTO', $doc->getKey()]))->assertForbidden();
        $this->assertDatabaseCount('documentos_clinicos', 1);
    }

    public function test_residente_cruzado_en_url_y_reemplazo_historico_denegados(): void
    {
        app(RegistrarCuidadosDeTurno::class)->guardarSeguimientoLesion($this->enfermera, $this->entrada([UploadedFile::fake()->image('uno.png')]));
        $doc = DocumentoClinico::sole();
        Residente::crearDesdeAdmision(['cod_residente' => 'RES_OTRO', 'nombres' => 'Otro', 'apellido_paterno' => 'Prueba', 'fecha_nacimiento' => '1940-01-01', 'estado' => 'ADMITIDO']);
        $this->get(route('admin.cuidados.fotografias.ver', ['RES_OTRO', $doc->getKey()]))->assertForbidden();
        try {
            $doc->update(['ruta_archivo' => 'reemplazada.png']);
            $this->fail('No reemplazar historia.');
        } catch (ValidationException) {
            $this->assertNotSame('reemplazada.png', $doc->fresh()->ruta_archivo);
        }
    }

    public static function mutacionesDenegadas(): array
    {
        return [['inactiva'], ['personal_inactivo'], ['sin_permiso'], ['sin_asignacion'], ['cerrada'], ['superadmin'], ['herida_cruzada']];
    }

    #[DataProvider('mutacionesDenegadas')]
    public function test_mutacion_denegada_no_deja_curacion_documentos_ni_archivos(string $caso): void
    {
        $entrada = $this->entrada([UploadedFile::fake()->image('privada.png')]);
        match ($caso) {
            'inactiva' => $this->enfermera->update(['estado' => 'INACTIVO']),
            'personal_inactivo' => $this->personal->update(['estado' => 'INACTIVO']),
            'sin_permiso' => $this->enfermera->syncRoles([]),
            'sin_asignacion' => AsignacionResidenteJornada::query()->update(['estado' => 'FINALIZADA']),
            'cerrada' => $this->herida->update(['estado' => 'CERRADA']),
            'superadmin' => $this->enfermera->syncRoles(['SUPERADMINISTRADOR']),
            'herida_cruzada' => $entrada['codResidente'] = Residente::crearDesdeAdmision(['cod_residente' => 'RES_MUTACION_OTRO', 'nombres' => 'Otro', 'apellido_paterno' => 'Sintético', 'fecha_nacimiento' => '1940-01-01', 'estado' => 'ADMITIDO'])->getKey(),
        };
        try {
            app(RegistrarCuidadosDeTurno::class)->guardarSeguimientoLesion($this->enfermera, $entrada);
            $this->fail('La mutación no debe estar autorizada.');
        } catch (HttpException|AuthorizationException|ModelNotFoundException $error) {
            $this->assertDatabaseCount('curaciones_herida', 0);
            $this->assertDatabaseCount('documentos_clinicos', 0);
            $this->assertSame([], Storage::disk('local')->allFiles());
        }
    }

    public function test_superadmin_puede_leer_pero_no_adjuntar_solo_por_rol(): void
    {
        app(RegistrarCuidadosDeTurno::class)->guardarSeguimientoLesion($this->enfermera, $this->entrada([UploadedFile::fake()->image('uno.png')]));
        $this->enfermera->syncRoles(['SUPERADMINISTRADOR']);
        $this->get(route('admin.cuidados.fotografias.ver', ['RES_FOTO', DocumentoClinico::sole()->getKey()]))->assertOk();
    }

    public function test_lotes_reemplazo_y_quitar_no_persisten_evidencia(): void
    {
        $component = Livewire::withQueryParams(['cuidado' => 'heridas', 'seccion' => 'HERIDAS'])->test(RegistrosEnfermeria::class, ['codResidente' => 'RES_FOTO'])->set('lesionId', 'HER_FOTO');
        $component->set('loteFotografias', [UploadedFile::fake()->image('primera.png')])->assertHasNoErrors()->assertSet('revisionFotos', 1);
        $component->set('loteFotografias', [UploadedFile::fake()->image('segunda.png')])->assertHasNoErrors()->assertSet('revisionFotos', 2);
        $this->assertCount(2, $component->get('fotografias'));
        $component->set('reemplazarFotografiaIndice', 0)->set('loteFotografias', [UploadedFile::fake()->image('reemplazo.png')])->assertHasNoErrors()->assertSet('revisionFotos', 3);
        $this->assertCount(2, $component->get('fotografias'));
        $component->call('quitarFotografia', 0);
        $this->assertCount(1, $component->get('fotografias'));
        $component->call('descartarFotosHerida')->assertSet('fotografias', [])->assertSet('loteFotografias', []);
        $this->assertDatabaseCount('documentos_clinicos', 0);
    }

    public function test_livewire_error_conserva_captura_fotos_y_galeria_sin_ruta_fisica(): void
    {
        $component = Livewire::withQueryParams(['cuidado' => 'heridas', 'seccion' => 'HERIDAS'])->test(RegistrosEnfermeria::class, ['codResidente' => 'RES_FOTO']);
        $component->set('lesionId', 'HER_FOTO')->set('aspectoLesion', 'Aspecto sintético')->set('fotografias', [UploadedFile::fake()->image('foto.png')])->call('guardarSeguimientoLesion')->assertHasErrors('accionLesion')->assertSet('aspectoLesion', 'Aspecto sintético');
        $this->assertCount(1, $component->get('fotografias'));
        $component->set('accionLesion', 'Procedimiento sintético')->call('guardarSeguimientoLesion')->assertHasNoErrors()->assertSet('fotografias', [])->assertDispatched('herida-evidencia-registrada')->assertSee('Curación registrada correctamente');
        $doc = DocumentoClinico::sole();
        $component->set('lesionId', 'HER_FOTO')->assertSee('Fotografías clínicas')->assertDontSee($doc->ruta_archivo);
    }

    public function test_cambiar_herida_descarta_captura_confirmada_sin_escribir_historia(): void
    {
        $component = Livewire::withQueryParams(['cuidado' => 'heridas', 'seccion' => 'HERIDAS'])->test(RegistrosEnfermeria::class, ['codResidente' => 'RES_FOTO']);
        $component->set('aspectoLesion', 'Dato anterior')->set('accionLesion', 'Procedimiento anterior')
            ->call('seleccionarHerida', 'HER_FOTO')->assertSet('lesionId', 'HER_FOTO')
            ->assertSet('aspectoLesion', '')->assertSet('accionLesion', '')->assertSet('fotografias', []);
        $this->assertDatabaseCount('curaciones_herida', 0);
        $this->assertDatabaseCount('documentos_clinicos', 0);
    }
}
