<?php
namespace Tests\Feature;

use App\Models\{AdultoMayor, User, DocumentoAdultoMayor};
use Database\Seeders\{EstadoAdultoSeeder, RolesAndPermissionsSeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentosFlujoTest extends TestCase
{
    use RefreshDatabase;

    public function test_subir_consultar_editar_archivar_restaurar_y_descargar_documento_privado(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $this->seed([EstadoAdultoSeeder::class, RolesAndPermissionsSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($user);
        $adulto = AdultoMayor::factory()->create(['cod_est_adul' => 'EST_001']);
        $otro = AdultoMayor::factory()->create(['cod_est_adul' => 'EST_001']);
        $this->post(route('admin.adultos-mayores.documentos.store', $adulto), [
            'nombre' => 'Documento de prueba', 'tipo_documento' => 'MEDICO',
            'fecha_subida' => today()->format('Y-m-d'),
            'archivo' => UploadedFile::fake()->create('documento.pdf', 10, 'application/pdf'),
        ])->assertSessionHasNoErrors()->assertSessionHas('success');
        $doc = DocumentoAdultoMayor::sole();
        Storage::disk('local')->assertExists($doc->ruta_archivo);
        Storage::disk('public')->assertMissing($doc->ruta_archivo);
        $this->get(route('admin.adultos-mayores.documentos.index', $adulto))->assertOk()->assertSee('Documento de prueba');
        $this->get(route('admin.adultos-mayores.show', $adulto))->assertOk()
            ->assertSee('Documento de prueba')
            ->assertSee(route('admin.adultos-mayores.documentos.archivo', [$adulto, $doc]), false);
        $this->get(route('admin.adultos-mayores.documentos.archivo', [$otro, $doc]))->assertNotFound();
        $this->get(route('admin.adultos-mayores.documentos.archivo', [$adulto, $doc]))->assertOk()->assertDownload();
        $this->patch(route('admin.adultos-mayores.documentos.update', [$adulto, $doc]), ['nombre' => 'Documento revisado', 'tipo_documento' => 'MEDICO'])->assertSessionHasNoErrors();
        $this->delete(route('admin.adultos-mayores.documentos.destroy', [$adulto, $doc]))->assertRedirect();
        $this->assertSame('ARCHIVADO', $doc->fresh()->estado);
        Storage::disk('local')->assertExists($doc->ruta_archivo);
        $this->patch(route('admin.adultos-mayores.documentos.restore', [$adulto, $doc]))->assertRedirect();
        $this->assertSame('ACTIVO', $doc->fresh()->estado);
        $this->assertDatabaseCount('documentos_adulto_mayor', 1);
    }
}
