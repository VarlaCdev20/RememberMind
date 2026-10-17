<?php

namespace Tests\Feature;

use App\Models\FotografiaInstitucional;
use App\Models\User;
use App\Modulos\Institucion\Aplicacion\Acciones\GestionarFotografiasInstitucionales;
use App\Modulos\Institucion\Presentacion\Livewire\Paginas\GaleriaInstitucionalPanel;
use App\Modulos\Reportes\Presentacion\DashboardPhotoRotation;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class GaleriaInstitucionalTest extends TestCase
{
    use RefreshDatabase;

    private User $gerente;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->gerente = $this->usuario('GERENTE');
        $this->actingAs($this->gerente);
    }

    private function usuario(string $rol): User
    {
        $usuario = User::factory()->create(['nombres' => 'Gestora', 'ap_paterno' => 'Sintética', 'estado' => 'ACTIVO']);
        $usuario->assignRole($rol);
        return $usuario;
    }

    private function datos(array $cambios = []): array
    {
        return [...['titulo' => 'Jardín del centro', 'descripcion' => 'Imagen sintética de prueba.',
            'fecha_fotografia' => null, 'texto_alternativo' => 'Jardín de prueba', 'usar_en_bienvenida' => true], ...$cambios];
    }

    private function subir(?User $actor = null, array $datos = []): array
    {
        return app(GestionarFotografiasInstitucionales::class)->subir($actor ?? $this->gerente,
            UploadedFile::fake()->image('jardin.png', 320, 180), $this->datos($datos));
    }

    public function test_ambos_roles_guardan_fotos_privadas_y_el_contenido_permanece_al_recargar(): void
    {
        foreach (['GERENTE', 'ADMINISTRADOR'] as $rol) {
            $actor = $rol === 'GERENTE' ? $this->gerente : $this->usuario($rol);
            $resultado = app(GestionarFotografiasInstitucionales::class)->subir($actor,
                UploadedFile::fake()->image('centro.png', $rol === 'GERENTE' ? 320 : 321, 180), $this->datos(['cod_usuario' => 'manipulado']));
            $foto = $resultado['foto']->fresh();
            $this->assertTrue($resultado['nueva']);
            $this->assertSame($actor->getKey(), $foto->cod_usuario);
            $this->assertStringStartsWith('COD_FGI_', $foto->getKey());
            Storage::disk('local')->assertExists([$foto->ruta_archivo, $foto->ruta_miniatura]);
            $this->assertSame(hash('sha256', Storage::disk('local')->get($foto->ruta_archivo)), $foto->hash_archivo);
            $this->assertSame(strlen(Storage::disk('local')->get($foto->ruta_archivo)), $foto->tamanio_bytes);
            $this->assertSame('image/webp', getimagesize(Storage::disk('local')->path($foto->ruta_archivo))['mime']);
            $this->actingAs($actor)->get(route('admin.galeria.index'))->assertOk()->assertSee('Jardín del centro');
            $this->get(route('admin.galeria.archivo', [$foto->getKey(), 'imagen']))->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        }
        $this->assertDatabaseCount('fotografias_institucionales', 2);
        $this->assertSame(2, Activity::where('event', 'fotografia_subida')->count());
    }

    public function test_duplicado_no_sobrescribe_autor_textos_ni_deja_archivos_extra(): void
    {
        $primera = $this->subir()['foto'];
        $otra = $this->subir($this->usuario('ADMINISTRADOR'), ['titulo' => 'Texto que no debe sobrescribir']);
        $this->assertFalse($otra['nueva']);
        $this->assertSame($primera->getKey(), $otra['foto']->getKey());
        $this->assertSame('Jardín del centro', $primera->fresh()->titulo);
        $this->assertCount(2, Storage::disk('local')->allFiles());
        $this->assertDatabaseCount('fotografias_institucionales', 1);
    }

    public function test_archivar_retira_la_imagen_de_bienvenida_y_bloquea_su_url_sin_borrar(): void
    {
        $foto = $this->subir()['foto'];
        $rotador = app(DashboardPhotoRotation::class);
        $this->assertSame(route('admin.galeria.archivo', [$foto->getKey(), 'imagen']), $rotador->heroImage('gerencia', 'fallback'));
        $this->assertSame('Jardín de prueba', $rotador->altFor(route('admin.galeria.archivo', [$foto->getKey(), 'imagen']), 'fallback'));
        $accion = app(GestionarFotografiasInstitucionales::class);
        $accion->estado($this->gerente, $foto->getKey(), 'ARCHIVADA');
        $this->get(route('admin.galeria.archivo', [$foto->getKey(), 'imagen']))->assertForbidden();
        $this->assertNotSame(route('admin.galeria.archivo', [$foto->getKey(), 'imagen']), $rotador->heroImage('gerencia', 'fallback'));
        Storage::disk('local')->assertExists([$foto->ruta_archivo, $foto->ruta_miniatura]);
        $accion->estado($this->gerente, $foto->getKey(), 'ACTIVA');
        $this->get(route('admin.galeria.archivo', [$foto->getKey(), 'miniatura']))->assertOk();
    }

    public function test_familiar_profesional_inactivo_y_sin_permiso_no_pueden_subir_ni_leer_archivos(): void
    {
        $foto = $this->subir()['foto'];
        $sinPermiso = $this->usuario('GERENTE');
        $sinPermiso->syncRoles([]);
        $inactivo = $this->usuario('ADMINISTRADOR');
        $inactivo->update(['estado' => 'INACTIVO']);
        foreach ([$this->usuario('FAMILIAR'), $this->usuario('ENFERMEROS'), $sinPermiso, $inactivo] as $actor) {
            $this->actingAs($actor)->get(route('admin.galeria.archivo', [$foto->getKey(), 'imagen']))->assertForbidden();
            try { $this->subir($actor); $this->fail('Debe rechazar al actor.'); }
            catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) { $this->assertSame(403, $error->getStatusCode()); }
        }
        $this->assertDatabaseCount('fotografias_institucionales', 1);
        $this->assertCount(2, Storage::disk('local')->allFiles());
        $this->actingAs($this->usuario('FAMILIAR'));
        $this->assertStringNotContainsString('/admin/galeria/', app(DashboardPhotoRotation::class)->heroImage('familiar', 'fallback'));
    }

    public function test_el_rol_sin_permiso_explicito_no_autoriza_gestion_ni_descarga(): void
    {
        $foto = $this->subir()['foto'];
        $rol = \Spatie\Permission\Models\Role::findByName('GERENTE', 'web');
        $rol->revokePermissionTo('fotografias_institucionales.gestionar');
        $this->gerente->unsetRelation('roles')->unsetRelation('permissions');
        $this->get(route('admin.galeria.index'))->assertOk()->assertDontSee('wire:click="nuevo"', false);
        try { $this->subir(); $this->fail('El rol por sí solo no autoriza una carga.'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) { $this->assertSame(403, $error->getStatusCode()); }
        $rol->revokePermissionTo('fotografias_institucionales.ver');
        $this->gerente->unsetRelation('roles')->unsetRelation('permissions');
        $this->get(route('admin.galeria.index'))->assertForbidden();
        $this->get(route('admin.galeria.archivo', [$foto->getKey(), 'imagen']))->assertForbidden();
        $this->assertDatabaseCount('fotografias_institucionales', 1);
        $this->assertCount(2, Storage::disk('local')->allFiles());
        $this->assertSame(1, Activity::where('event', 'fotografia_subida')->count());
    }

    public function test_fallo_de_auditoria_revierte_registro_y_compensa_solo_archivos_nuevos(): void
    {
        $foto = $this->subir()['foto'];
        Activity::creating(function ($actividad) {
            if ($actividad->log_name === 'Galería institucional') throw new \RuntimeException('Fallo sintético de auditoría');
        });
        try {
            app(GestionarFotografiasInstitucionales::class)->subir($this->gerente, UploadedFile::fake()->image('otra.png', 77, 55), $this->datos());
            $this->fail('Debe propagar el fallo.');
        } catch (\RuntimeException $error) { $this->assertSame('Fallo sintético de auditoría', $error->getMessage()); }
        $this->assertDatabaseCount('fotografias_institucionales', 1);
        $this->assertCount(2, Storage::disk('local')->allFiles());
        Storage::disk('local')->assertExists($foto->ruta_archivo);
    }

    public function test_archivo_falso_y_limite_excedido_no_generan_persistencia(): void
    {
        foreach ([UploadedFile::fake()->create('falso.jpg', 10, 'image/jpeg'), UploadedFile::fake()->image('grande.jpg')->size(2049)] as $archivo) {
            try { app(GestionarFotografiasInstitucionales::class)->subir($this->gerente, $archivo, $this->datos()); $this->fail('Debe rechazar el archivo.'); }
            catch (\Illuminate\Validation\ValidationException $error) { $this->assertArrayHasKey('archivo', $error->errors()); }
        }
        $this->assertDatabaseCount('fotografias_institucionales', 0);
        $this->assertCount(0, Storage::disk('local')->allFiles());
    }

    public function test_carga_multiple_edicion_busqueda_y_archivo_conservan_identidad(): void
    {
        $panel = Livewire::test(GaleriaInstitucionalPanel::class)->call('nuevo')
            ->set('titulo', 'Espacios de prueba')->set('usar_en_bienvenida', true)
            ->set('archivos', [UploadedFile::fake()->image('uno.png', 44, 33), UploadedFile::fake()->image('dos.png', 45, 33)])
            ->call('guardar')->assertHasNoErrors()->assertSee('2 guardadas')->assertSet('formularioAbierto', false);
        $foto = FotografiaInstitucional::firstOrFail();
        $ruta = $foto->ruta_archivo;
        $panel->call('editar', $foto->getKey())->set('titulo', 'Salón accesible')->call('guardar')->assertHasNoErrors();
        $this->assertSame($ruta, $foto->fresh()->ruta_archivo);
        $panel->set('busqueda', 'Salón')->assertSee('Salón accesible')->call('cambiarEstado', $foto->getKey(), 'ARCHIVADA')
            ->set('estado', 'ARCHIVADA')->assertSee('Salón accesible');
        $this->assertFalse($this->gerente->can('usuarios.editar'));
    }

    public function test_constraints_sql_rechazan_datos_incoherentes(): void
    {
        $foto = $this->subir()['foto'];
        foreach ([['ancho' => 0], ['alto' => -1], ['tamanio_bytes' => 0], ['estado' => 'PUBLICA'], ['hash_archivo' => 'corto'], ['titulo' => ' '], ['cod_usuario' => 'no-existe']] as $cambio) {
            try {
                DB::transaction(fn () => DB::table('fotografias_institucionales')->where('cod_fotografia', $foto->getKey())->update($cambio));
                $this->fail('SQL debe rechazar '.array_key_first($cambio));
            } catch (\Illuminate\Database\QueryException $error) { $this->assertNotEmpty($error->getMessage()); }
        }
        $this->assertSame('ACTIVA', $foto->fresh()->estado);
    }

    public function test_lote_parcial_conserva_fallidas_y_no_finge_guardado_total(): void
    {
        Livewire::test(GaleriaInstitucionalPanel::class)->call('nuevo')->set('titulo', 'Actividad sintética')
            ->set('archivos', [UploadedFile::fake()->image('valida.png'), UploadedFile::fake()->create('invalida.jpg', 5, 'image/jpeg')])
            ->call('guardar')->assertSee('1 guardadas')->assertSee('1 pendientes de guardar')
            ->assertSet('formularioAbierto', true)->assertCount('archivos', 1);
        $this->assertDatabaseCount('fotografias_institucionales', 1);
        $this->assertCount(2, Storage::disk('local')->allFiles());
    }

    public function test_limite_total_del_lote_se_valida_antes_de_persistir(): void
    {
        Livewire::test(GaleriaInstitucionalPanel::class)->call('nuevo')->set('titulo', 'Lote de prueba')
            ->set('archivos', array_map(fn ($i) => UploadedFile::fake()->image('foto'.$i.'.png')->size(2048), range(1, 4)))
            ->call('guardar')->assertHasErrors('archivos');
        $this->assertDatabaseCount('fotografias_institucionales', 0);
    }

    public function test_fallo_de_segunda_escritura_limpia_la_primera_y_no_crea_registro(): void
    {
        $real = Storage::disk('local');
        $proxy = \Mockery::mock($real);
        $escrituras = 0;
        $proxy->shouldReceive('put')->andReturnUsing(function ($ruta, $bytes) use (&$escrituras, $real) {
            return ++$escrituras === 1 ? $real->put($ruta, $bytes) : false;
        });
        Storage::shouldReceive('disk')->with('local')->andReturn($proxy);
        try { $this->subir(); $this->fail('Debe fallar la segunda escritura.'); }
        catch (\RuntimeException $error) { $this->assertSame('No se pudo almacenar la fotografía.', $error->getMessage()); }
        $this->assertCount(0, $real->allFiles());
        $this->assertDatabaseCount('fotografias_institucionales', 0);
    }

    public function test_preview_y_superadministrador_no_adquieren_gestion_automatica(): void
    {
        $super = $this->usuario('SUPERADMINISTRADOR');
        $this->actingAs($super);
        $this->get(route('admin.galeria.index'))->assertOk()->assertDontSee('wire:click="nuevo"', false);
        $this->assertFalse($super->hasPermissionTo('fotografias_institucionales.gestionar'));
        $super->assignRole('GERENTE');
        app(\App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService::class)->activate($super, 'GERENTE');
        try { $this->subir($super); $this->fail('Preview no puede escribir.'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) { $this->assertSame(403, $error->getStatusCode()); }
        $this->assertDatabaseCount('fotografias_institucionales', 0);
    }

    public function test_orienta_jpeg_y_elimina_exif_en_el_archivo_normalizado(): void
    {
        $archivo = UploadedFile::fake()->image('orientada.jpg', 120, 80);
        $jpeg = file_get_contents($archivo->getRealPath());
        $tiff = "II\x2a\x00".pack('V', 8).pack('v', 1).pack('vvVv', 0x0112, 3, 1, 6)."\x00\x00".pack('V', 0);
        $exif = "Exif\x00\x00".$tiff;
        file_put_contents($archivo->getRealPath(), substr($jpeg, 0, 2)."\xff\xe1".pack('n', strlen($exif) + 2).$exif.substr($jpeg, 2));
        $foto = app(GestionarFotografiasInstitucionales::class)->subir($this->gerente, $archivo, $this->datos())['foto'];
        $this->assertSame(80, $foto->ancho);
        $this->assertSame(120, $foto->alto);
        $this->assertStringNotContainsString('Exif', Storage::disk('local')->get($foto->ruta_archivo));
    }

    public function test_migracion_con_roles_existentes_concede_solo_los_permisos_aprobados(): void
    {
        \Illuminate\Support\Facades\Schema::drop('fotografias_institucionales');
        \Spatie\Permission\Models\Permission::where('name', 'like', 'fotografias_institucionales.%')->get()->each->delete();
        $migracion = require database_path('migrations/2026_10_17_150000_create_fotografias_institucionales_table.php');
        $migracion->up();
        $asignaciones = DB::table('role_has_permissions as rp')->join('permissions as p', 'p.id', '=', 'rp.permission_id')
            ->join('roles as r', 'r.id', '=', 'rp.role_id')->where('p.name', 'like', 'fotografias_institucionales.%')
            ->orderBy('r.name')->orderBy('p.name')->get(['r.name as rol', 'p.name as permiso']);
        $this->assertSame(['ADMINISTRADOR', 'ADMINISTRADOR', 'GERENTE', 'GERENTE'], $asignaciones->pluck('rol')->all());
        $this->assertCount(2, $asignaciones->pluck('permiso')->unique());
        $this->assertDatabaseCount('fotografias_institucionales', 0);
    }

    public function test_forma_relacional_tiene_solo_los_17_atributos_aprobados(): void
    {
        $this->assertEqualsCanonicalizing(['cod_fotografia', 'cod_usuario', 'titulo', 'descripcion', 'fecha_fotografia',
            'texto_alternativo', 'ruta_archivo', 'ruta_miniatura', 'tipo_archivo', 'tamanio_bytes', 'ancho', 'alto',
            'hash_archivo', 'estado', 'usar_en_bienvenida', 'fecha_registro', 'fecha_actualizacion'],
            \Illuminate\Support\Facades\Schema::getColumnListing('fotografias_institucionales'));
    }

    public function test_estados_visuales_de_galeria_con_datos_sinteticos(): void
    {
        $panel = Livewire::test(GaleriaInstitucionalPanel::class)->assertSee('No hay fotografías');
        $this->exportar('vacio', $panel->html());
        $panel->call('nuevo')->assertSee('Título descriptivo');
        $this->exportar('formulario', $panel->html());
        $archivo = UploadedFile::fake()->image('centro-sintetico.png', 480, 320);
        $im = imagecreatetruecolor(480, 320);
        imagefill($im, 0, 0, imagecolorallocate($im, 232, 227, 222));
        imagefilledrectangle($im, 40, 65, 440, 250, imagecolorallocate($im, 130, 157, 138));
        imagefilledrectangle($im, 75, 100, 195, 220, imagecolorallocate($im, 211, 220, 204));
        imagefilledrectangle($im, 270, 100, 395, 220, imagecolorallocate($im, 211, 220, 204));
        imagestring($im, 5, 60, 275, 'QA - imagen sintetica', imagecolorallocate($im, 79, 71, 66));
        imagepng($im, $archivo->getRealPath());
        imagedestroy($im);
        $panel->set('titulo', 'Espacios del centro: fotografía sintética de prueba con título extenso')
            ->set('descripcion', 'Imagen sintética para verificar proporciones, campos y adaptación. No representa una fotografía real.')
            ->set('archivos', [$archivo]);
        $this->exportar('previsualizacion', $panel->html(), file_get_contents($archivo->getRealPath()));
        $panel->call('guardar')->assertHasNoErrors()->assertSee('1 guardadas');
        $this->exportar('poblada', $panel->html());
        $foto = FotografiaInstitucional::firstOrFail();
        $panel->call('editar', $foto->getKey())->assertSet('editando', $foto->getKey());
        $this->exportar('edicion', $panel->html());
        $panel->set('titulo', '')->call('guardar')->assertHasErrors('titulo');
        $this->exportar('errores', $panel->html());
        $panel->call('cerrar')->call('cambiarEstado', $foto->getKey(), 'ARCHIVADA')->set('estado', 'ARCHIVADA')->assertSee('Fotografía archivada');
        $this->exportar('archivada', $panel->html());
    }

    private function exportar(string $estado, string $html, ?string $preview = null): void
    {
        $dir = getenv('RM_GALLERY_QA_DIR');
        if (! $dir) return;
        if (! is_dir($dir)) mkdir($dir, 0700, true);
        $foto = FotografiaInstitucional::first();
        if ($foto) {
            $data = 'data:image/webp;base64,'.base64_encode(Storage::disk('local')->get($foto->ruta_archivo));
            $html = preg_replace('/(<img\b[^>]*\bsrc=")[^"]*(")/', '$1'.$data.'$2', $html);
        } elseif ($preview !== null) {
            $data = 'data:image/png;base64,'.base64_encode($preview);
            $html = preg_replace('/(<img\b[^>]*\bsrc=")[^"]*(")/', '$1'.$data.'$2', $html);
        } else {
            $html = preg_replace('/<img\b[^>]*>/', '', $html);
        }
        file_put_contents($dir.'/gallery-'.$estado.'.html', $html);
    }
}
