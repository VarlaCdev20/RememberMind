<?php

namespace Tests\Feature;

use App\Frontend\Livewire\Compartido\Clinica\FichaPaciente;
use App\Models\Residente;
use App\Models\Documento;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentacionFichaTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;
    private Residente $residente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->usuario = User::factory()->create([
            'estado' => 'ACTIVO',
            'nombres' => 'Elena',
            'ap_paterno' => 'Rojas',
        ]);
        $this->usuario->assignRole('ADMINISTRADOR');
        $this->actingAs($this->usuario);

        $this->residente = Residente::factory()->create([
            'nombres' => 'Carmen',
            'ap_paterno' => 'Mendoza',
            'ap_materno' => 'Ramos',
        ]);
    }

    public function test_pestana_documentacion_renderiza_correctamente(): void
    {
        Livewire::test(FichaPaciente::class, ['adulto' => $this->residente->cod_residente])
            ->call('cambiarTab', 'documentos')
            ->assertSee('Documentación')
            ->assertSee('+ Subir documento')
            ->assertSee('Total de documentos')
            ->assertSee('Clínicos')
            ->assertSee('Administrativos')
            ->assertSee('Imágenes')
            ->assertSee('Legales')
            ->assertSee('Buscar documentos...')
            ->assertSee('Todos los tipos')
            ->assertSee('Todas las categorías')
            ->assertSee('Más recientes primero')
            ->assertSee('Lista de documentos')
            ->assertSee('Vista previa del documento')
            ->assertSee('Vista previa')
            ->assertSee('Información')
            ->assertSee('Historial')
            ->assertSee('Descargar')
            ->assertSee('Compartir')
            ->assertSee('Imprimir');
    }

    public function test_filtrado_por_categoria_y_tipo(): void
    {
        $component = Livewire::test(FichaPaciente::class, ['adulto' => $this->residente->cod_residente])
            ->call('cambiarTab', 'documentos');

        $component->assertSee('Informe médico geriátrico')
            ->assertSee('Radiografía de tórax PA');

        // Filtrar por categoría LEGAL
        $component->set('filtroCategoriaDoc', 'LEGAL')
            ->assertSee('Consentimiento informado institucional')
            ->assertDontSee('Radiografía de tórax PA');

        // Filtrar por categoría CLINICO
        $component->set('filtroCategoriaDoc', 'CLINICO')
            ->assertSee('Informe médico geriátrico')
            ->assertDontSee('Consentimiento informado institucional');
    }

    public function test_busqueda_de_documentos(): void
    {
        $component = Livewire::test(FichaPaciente::class, ['adulto' => $this->residente->cod_residente])
            ->call('cambiarTab', 'documentos');

        // Buscar Hemograma
        $component->set('filtroBusquedaDoc', 'Hemograma')
            ->assertSee('Hemograma completo con plaquetas')
            ->assertDontSee('Radiografía de tórax PA');

        // Limpiar búsqueda
        $component->set('filtroBusquedaDoc', '')
            ->assertSee('Radiografía de tórax PA');
    }

    public function test_seleccion_de_documento_y_tabs_de_detalle(): void
    {
        $component = Livewire::test(FichaPaciente::class, ['adulto' => $this->residente->cod_residente])
            ->call('cambiarTab', 'documentos');

        // Cambiar tab a Información
        $component->call('setTabDetalleDoc', 'informacion')
            ->assertSet('tabDetalleDoc', 'informacion')
            ->assertSee('Tipo de documento')
            ->assertSee('Subido por');

        // Cambiar tab a Historial
        $component->call('setTabDetalleDoc', 'historial')
            ->assertSet('tabDetalleDoc', 'historial')
            ->assertSee('Trazabilidad documental')
            ->assertSee('Documento cargado');

        // Ajustar zoom
        $component->call('ajustarZoomDoc', 25)
            ->assertSet('zoomDoc', 125);
    }

    public function test_subir_nuevo_documento(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('prueba_clinica.pdf', 200, 'application/pdf');

        Livewire::test(FichaPaciente::class, ['adulto' => $this->residente->cod_residente])
            ->call('cambiarTab', 'documentos')
            ->call('abrirModalSubirDoc')
            ->assertSet('modalSubirDoc', true)
            ->set('nuevoDocArchivo', $file)
            ->set('nuevoDocNombre', 'Evaluación Psicológica Trimestral')
            ->set('nuevoDocTipo', 'Informe')
            ->set('nuevoDocCategoria', 'CLINICO')
            ->set('nuevoDocFecha', today()->toDateString())
            ->set('nuevoDocDescripcion', 'Evaluación de funciones cognitivas')
            ->call('guardarNuevoDocumento')
            ->assertSet('modalSubirDoc', false)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('documentos', [
            'cod_residente' => $this->residente->cod_residente,
            'nombre' => 'Evaluación Psicológica Trimestral',
        ]);
    }
}
