<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\FotografiaInstitucional;
use App\Models\User;
use App\Modulos\Institucion\Aplicacion\Acciones\GestionarAreasInstitucionales;
use App\Modulos\Institucion\Aplicacion\Acciones\GestionarFotografiasInstitucionales;
use App\Modulos\Institucion\Presentacion\Livewire\Paginas\AreasInstitucionalesPanel;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AreasFotografiaTest extends TestCase
{
    use RefreshDatabase;

    private User $gerente;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->gerente = User::factory()->create(['estado' => 'ACTIVO']);
        $this->gerente->assignRole('GERENTE');
        $this->actingAs($this->gerente);
    }

    private function foto(int $ancho = 320): FotografiaInstitucional
    {
        return app(GestionarFotografiasInstitucionales::class)->subir($this->gerente,
            UploadedFile::fake()->image('jardin.png', $ancho, 180),
            ['titulo' => 'Jardín de prueba', 'descripcion' => null, 'fecha_fotografia' => null,
                'texto_alternativo' => 'Jardín sintético', 'usar_en_bienvenida' => false])['foto'];
    }

    private function guardar(array $datos, ?User $actor = null): void
    {
        app(GestionarAreasInstitucionales::class)->guardarArea($actor ?? $this->gerente,
            [...['nombre' => 'Área de prueba', 'descripcion' => 'Funciones de prueba', 'estado' => 'ACTIVO', 'isEdit' => false], ...$datos]);
    }

    public function test_seleccion_persistente_y_tarjeta_utilizan_la_ruta_privada(): void
    {
        $foto = $this->foto();
        Livewire::test(AreasInstitucionalesPanel::class)->call('crearArea')
            ->set('nombre', 'Espacio de prueba')->call('abrirGaleriaArea')->assertSee('Jardín de prueba')
            ->call('seleccionarFotografia', $foto->getKey())->call('guardarArea')->assertHasNoErrors()
            ->assertSee(route('admin.galeria.archivo', [$foto->getKey(), 'imagen']), false)
            ->assertDontSee($foto->ruta_archivo, false);
        $area = Area::where('nombre', 'ESPACIO DE PRUEBA')->firstOrFail();
        $this->assertSame($foto->getKey(), $area->fresh()->fotografiaPrincipal->getKey());
        Livewire::test(AreasInstitucionalesPanel::class)->call('editarArea', $area->getKey())
            ->assertSet('cod_fotografia', $foto->getKey())->assertSee('Jardín de prueba');
        $this->get(route('admin.galeria.archivo', [$foto->getKey(), 'miniatura']))->assertOk();
    }

    public function test_subida_del_formulario_guarda_area_foto_miniatura_y_autor_sin_publicar_en_bienvenida(): void
    {
        Livewire::test(AreasInstitucionalesPanel::class)->call('crearArea')->set('nombre', 'Jardín del centro')
            ->set('archivo_fotografia', UploadedFile::fake()->image('area.png', 400, 250))
            ->set('texto_alternativo', 'Bancos y sombra')->call('guardarArea')->assertHasNoErrors()->assertSet('mostrarFormulario', false);
        $area = Area::where('nombre', 'JARDÍN DEL CENTRO')->firstOrFail();
        $foto = $area->fotografiaPrincipal;
        $this->assertSame($this->gerente->getKey(), $foto->cod_usuario);
        $this->assertSame('Bancos y sombra', $foto->texto_alternativo);
        $this->assertFalse($foto->usar_en_bienvenida);
        $this->assertSame('image/webp', $foto->tipo_archivo);
        Storage::disk('local')->assertExists([$foto->ruta_archivo, $foto->ruta_miniatura]);
        $this->assertSame(1, Activity::where('event', 'fotografia_subida')->count());
        $this->assertSame(1, Activity::where('log_name', 'AreasInstitucionales')->count());
    }

    public function test_reemplazar_y_quitar_no_borra_archivos_ni_cambia_el_estado_gerencial(): void
    {
        $primera = $this->foto();
        $segunda = $this->foto(321);
        $area = Area::create(['nombre' => 'Área anterior', 'estado' => 'ACTIVA', 'cod_fotografia' => $primera->getKey()]);
        $panel = Livewire::test(AreasInstitucionalesPanel::class)->call('editarArea', $area->getKey())
            ->call('seleccionarFotografia', $segunda->getKey())->set('estado', 'INACTIVO')->call('guardarArea')->assertHasNoErrors();
        $this->assertSame('ACTIVA', $area->fresh()->estado);
        $this->assertSame($segunda->getKey(), $area->fresh()->cod_fotografia);
        $auditoria = Activity::where('log_name', 'AreasInstitucionales')->latest('id')->firstOrFail();
        $this->assertSame($primera->getKey(), $auditoria->properties['cod_fotografia_anterior']);
        $this->assertSame($segunda->getKey(), $auditoria->properties['cod_fotografia']);
        $panel->call('editarArea', $area->getKey())->call('quitarFotografia')->call('guardarArea')->assertHasNoErrors();
        $this->assertNull($area->fresh()->cod_fotografia);
        $this->assertDatabaseCount('fotografias_institucionales', 2);
        $this->assertCount(4, Storage::disk('local')->allFiles());
    }

    public function test_cancelar_una_subida_temporal_no_crea_area_ni_fotografia(): void
    {
        Livewire::test(AreasInstitucionalesPanel::class)->call('crearArea')->set('nombre', 'Sin guardar')
            ->set('archivo_fotografia', UploadedFile::fake()->image('temporal.png'))->call('cerrarFormulario')
            ->assertSet('archivo_fotografia', null)->assertSet('cod_fotografia', null);
        $this->assertDatabaseCount('areas', 0);
        $this->assertDatabaseCount('fotografias_institucionales', 0);
        $this->assertSame([], Storage::disk('local')->allFiles('galeria-institucional'));
    }

    public function test_imagen_falsa_excesiva_o_entrada_ambigua_no_deja_persistencia(): void
    {
        foreach ([UploadedFile::fake()->create('falsa.jpg', 20, 'text/plain'), UploadedFile::fake()->image('grande.png')->size(2049)] as $archivo) {
            try {
                $this->guardar(['archivo_fotografia' => $archivo]);
                $this->fail('Debe rechazar el archivo.');
            } catch (ValidationException $error) {
                $this->assertArrayHasKey('archivo_fotografia', $error->errors());
            }
        }
        try {
            $this->guardar(['archivo_fotografia' => UploadedFile::fake()->image('area.png'), 'cod_fotografia' => 'COD_FGI_999999']);
            $this->fail('Debe rechazar dos fuentes.');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('archivo_fotografia', $error->errors());
        }
        $this->assertDatabaseCount('areas', 0);
        $this->assertDatabaseCount('fotografias_institucionales', 0);
        $this->assertSame([], Storage::disk('local')->allFiles('galeria-institucional'));
    }

    public function test_archivada_o_inexistente_no_admite_vinculo_nuevo_y_la_edicion_conserva_el_anterior(): void
    {
        $foto = $this->foto();
        $area = Area::create(['nombre' => 'Área existente', 'estado' => 'ACTIVO', 'cod_fotografia' => $foto->getKey()]);
        app(GestionarFotografiasInstitucionales::class)->estado($this->gerente, $foto->getKey(), 'ARCHIVADA');
        foreach ([$foto->getKey(), 'COD_FGI_999999'] as $codigo) {
            try {
                $this->guardar(['cod_fotografia' => $codigo]);
                $this->fail('Debe rechazar fotografía no disponible.');
            } catch (ValidationException $error) {
                $this->assertArrayHasKey('cod_fotografia', $error->errors());
            }
        }
        Livewire::test(AreasInstitucionalesPanel::class)->call('editarArea', $area->getKey())->assertSee('Fotografía archivada')
            ->set('descripcion', 'Texto actualizado')->call('guardarArea')->assertHasNoErrors()
            ->assertDontSee(route('admin.galeria.archivo', [$foto->getKey(), 'imagen']), false);
        $this->assertSame($foto->getKey(), $area->fresh()->cod_fotografia);
        $this->assertSame('Texto actualizado', $area->fresh()->descripcion);
        $this->assertDatabaseCount('areas', 1);
        $this->get(route('admin.galeria.archivo', [$foto->getKey(), 'imagen']))->assertForbidden();
    }

    public function test_superadmin_puede_elegir_pero_no_subir_sin_gestion_de_galeria(): void
    {
        $foto = $this->foto();
        $superadmin = User::factory()->create(['estado' => 'ACTIVO']);
        $superadmin->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($superadmin);
        Livewire::test(AreasInstitucionalesPanel::class)->call('crearArea')->assertSee('Elegir de Galería')->assertDontSee('Subir fotografía')
            ->set('nombre', 'Área elegida')->call('seleccionarFotografia', $foto->getKey())->call('guardarArea')->assertHasNoErrors();
        try {
            $this->guardar(['archivo_fotografia' => UploadedFile::fake()->image('area.png', 450, 240)], $superadmin);
            $this->fail('No debe ampliar la gestión.');
        } catch (HttpException $error) {
            $this->assertSame(403, $error->getStatusCode());
        }
        $this->assertDatabaseCount('areas', 1);
        $this->assertDatabaseCount('fotografias_institucionales', 1);
        $this->assertCount(2, Storage::disk('local')->allFiles());
    }

    public function test_revalidar_cuenta_y_permisos_impide_escritura_y_filtracion_de_fotografias(): void
    {
        $foto = $this->foto();
        $panel = Livewire::test(AreasInstitucionalesPanel::class)->call('crearArea')->set('nombre', 'Área rechazada')->set('cod_fotografia', $foto->getKey());
        Role::findByName('GERENTE')->revokePermissionTo('fotografias_institucionales.ver');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $panel->call('guardarArea')->assertForbidden();
        $this->get(route('admin.galeria.archivo', [$foto->getKey(), 'imagen']))->assertForbidden();
        $this->gerente->update(['estado' => 'INACTIVO']);
        try {
            $this->guardar(['archivo_fotografia' => UploadedFile::fake()->image('area.png')]);
            $this->fail('La cuenta inactiva no guarda.');
        } catch (HttpException $error) {
            $this->assertSame(403, $error->getStatusCode());
        }
        $this->assertDatabaseCount('areas', 0);
        $this->assertDatabaseCount('fotografias_institucionales', 1);
        $this->assertCount(2, Storage::disk('local')->allFiles());
    }

    public function test_permiso_de_galeria_no_concede_creacion_de_areas(): void
    {
        $actor = User::factory()->create(['estado' => 'ACTIVO']);
        $actor->assignRole('ADMINISTRADOR');
        try {
            $this->guardar(['archivo_fotografia' => UploadedFile::fake()->image('area.png')], $actor);
            $this->fail('Debe requerir el permiso de áreas.');
        } catch (HttpException $error) {
            $this->assertSame(403, $error->getStatusCode());
        }
        $this->assertDatabaseCount('areas', 0);
        $this->assertDatabaseCount('fotografias_institucionales', 0);
        $this->assertSame([], Storage::disk('local')->allFiles('galeria-institucional'));
    }

    public function test_fallo_posterior_a_la_subida_revierte_sql_y_compensa_solo_archivos_nuevos(): void
    {
        $original = $this->foto();
        Exceptions::fake();
        Activity::creating(function ($evento): void {
            if ($evento->log_name === 'AreasInstitucionales') {
                throw new \RuntimeException('Fallo sintético de auditoría');
            }
        });
        try {
            Livewire::test(AreasInstitucionalesPanel::class)->call('crearArea')->set('nombre', 'Área fallida')
                ->set('archivo_fotografia', UploadedFile::fake()->image('nueva.png', 451, 243))->call('guardarArea')
                ->assertHasErrors('guardadoArea')->assertSet('mostrarFormulario', true)->assertSet('nombre', 'Área fallida');
        } finally {
            Activity::flushEventListeners();
        }
        $this->assertDatabaseCount('areas', 0);
        $this->assertDatabaseCount('fotografias_institucionales', 1);
        Storage::disk('local')->assertExists([$original->ruta_archivo, $original->ruta_miniatura]);
        $this->assertCount(2, Storage::disk('local')->allFiles());
        Exceptions::assertReported(\RuntimeException::class);
    }

    public function test_duplicado_reutiliza_foto_sin_sobrescribir_su_contenido(): void
    {
        $foto = $this->foto();
        $this->guardar(['archivo_fotografia' => UploadedFile::fake()->image('mismo.png', 320, 180), 'texto_alternativo' => 'No sobrescribir']);
        $this->assertSame($foto->getKey(), Area::sole()->cod_fotografia);
        $this->assertSame('Jardín sintético', $foto->fresh()->texto_alternativo);
        $this->assertCount(2, Storage::disk('local')->allFiles());
        $this->assertDatabaseCount('fotografias_institucionales', 1);
    }

    public function test_fk_restrictiva_y_rollback_poblado_preservan_vinculos(): void
    {
        $foto = $this->foto();
        $area = Area::create(['nombre' => 'Área vinculada', 'estado' => 'ACTIVO', 'cod_fotografia' => $foto->getKey()]);
        foreach ([fn () => DB::table('areas')->where('cod_area', $area->getKey())->update(['cod_fotografia' => 'NO_EXISTE']),
            fn () => DB::table('fotografias_institucionales')->where('cod_fotografia', $foto->getKey())->delete(),
            fn () => DB::table('fotografias_institucionales')->where('cod_fotografia', $foto->getKey())->update(['cod_fotografia' => 'CAMBIO_PK'])] as $operacion) {
            try {
                DB::transaction($operacion);
                $this->fail('La FK debe impedirlo.');
            } catch (QueryException) {
                $this->addToAssertionCount(1);
            }
        }
        try {
            (require database_path('migrations/2026_11_08_000001_add_fotografia_principal_to_areas_table.php'))->down();
            $this->fail('No perder vínculos.');
        } catch (\LogicException) {
            $this->addToAssertionCount(1);
        }
        $this->assertSame($foto->getKey(), $area->fresh()->cod_fotografia);
    }

    public function test_extension_y_reversion_sin_vinculos_preservan_filas_y_tablas(): void
    {
        $area = Area::create(['nombre' => 'Área conservada', 'descripcion' => 'Original', 'estado' => 'ACTIVO']);
        $columnas = ['cod_area', 'nombre', 'descripcion', 'estado'];
        $original = (array) DB::table('areas')->where('cod_area', $area->getKey())->first($columnas);
        $tablas = Schema::getTableListing();
        sort($tablas);
        $migracion = require database_path('migrations/2026_11_08_000001_add_fotografia_principal_to_areas_table.php');
        $migracion->down();
        $this->assertSame($columnas, Schema::getColumnListing('areas'));
        $migracion->up();
        $this->assertNull($area->fresh()->cod_fotografia);
        $this->assertSame($original, (array) DB::table('areas')->where('cod_area', $area->getKey())->first($columnas));
        $despues = Schema::getTableListing();
        sort($despues);
        $this->assertSame($tablas, $despues);
    }
}
