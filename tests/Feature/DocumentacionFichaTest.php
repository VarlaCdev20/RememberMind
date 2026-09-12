<?php

namespace Tests\Feature;

use App\Livewire\Cuidados\FichaPaciente;
use App\Models\AdultoMayor;
use App\Models\AsignacionAdultoMayor;
use App\Models\AsignacionTurnoAdulto;
use App\Models\Cama;
use App\Models\DocumentoAdultoMayor;
use App\Models\EstadoAdulto;
use App\Models\Habitacion;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use Database\Seeders\EstadoAdultoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentacionFichaTest extends TestCase
{
    use RefreshDatabase;

    protected User $enfermero;
    protected AdultoMayor $adulto;
    protected Habitacion $habitacion;
    protected Cama $cama;
    protected TurnoEnfermeria $turno;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            EstadoAdultoSeeder::class,
            RolesAndPermissionsSeeder::class,
        ]);

        $this->enfermero = User::factory()->create([
            'cod_usu' => 'USU_TEST_DOC1',
            'nombres' => 'Laura',
            'ap_paterno' => 'González',
            'ap_materno' => 'Pérez',
            'correo' => 'laura.doc@test.com',
            'estado' => 'ACTIVO',
        ]);
        $this->enfermero->assignRole('ENFERMEROS');

        $this->turno = TurnoEnfermeria::create([
            'cod_turno' => 'TUR_DOC_01',
            'nombre' => 'Mañana Asistencial',
            'hora_inicio' => '07:00:00',
            'hora_fin' => '15:00:00',
            'orden' => 1,
            'estado' => 'ACTIVO',
        ]);

        $this->habitacion = Habitacion::create([
            'cod_habitacion' => 'HAB_DOC_01',
            'nombre' => 'Habitación 204',
            'codigo' => 'H-204',
            'numero' => '204',
            'tipo' => 'DOBLE',
            'capacidad' => 2,
            'estado' => 'ACTIVA',
        ]);

        $this->cama = Cama::create([
            'cod_cama' => 'CAM_DOC_01',
            'cod_habitacion' => $this->habitacion->cod_habitacion,
            'codigo' => 'C-204-A',
            'numero' => 'A',
            'estado' => 'OCUPADA',
        ]);

        $this->adulto = AdultoMayor::factory()->create([
            'cod_am' => 'AM_DOC_01',
            'nombres' => 'Carmen',
            'ap_paterno' => 'Mendoza',
            'ap_materno' => 'Ramos',
            'ci' => '4455667',
            'fecha_nac' => '1942-03-15',
            'genero' => 'FEMENINO',
            'alergias' => 'Sin alergias conocidas',
            'grupo_sanguineo' => 'A',
            'factor_rh' => '+',
            'seguro_salud' => 'Caja Nacional',
            'contacto_emergencia_nombre' => 'Carlos Mendoza',
            'contacto_emergencia_celular' => '78901234',
            'cod_habitacion' => $this->habitacion->cod_habitacion,
            'cod_cama' => $this->cama->cod_cama,
            'cod_est_adul' => EstadoAdulto::first()->cod_est_adul,
        ]);

        AsignacionAdultoMayor::create([
            'cod_am' => $this->adulto->cod_am,
            'cod_habitacion' => $this->habitacion->cod_habitacion,
            'cod_cama' => $this->cama->cod_cama,
            'fecha_asignacion' => today()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        AsignacionTurnoAdulto::create([
            'cod_am' => $this->adulto->cod_am,
            'cod_turno' => $this->turno->cod_turno,
            'cod_usu_enfermero' => $this->enfermero->cod_usu,
            'cod_usu' => $this->enfermero->cod_usu,
            'fecha_inicio' => today()->toDateString(),
            'motivo_asignacion' => 'Asignación asistencial',
            'asignado_por' => $this->enfermero->cod_usu,
            'fecha' => today()->toDateString(),
            'nivel_supervision' => 'ALTO',
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_pestana_documentacion_renderiza_correctamente(): void
    {
        $this->actingAs($this->enfermero);

        $component = Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
            ->set('tabActivo', 'documentos')
            ->assertSee('Documentación')
            ->assertSee('Gestión y consulta de documentos clínicos, administrativos y personales del residente.')
            ->assertSee('+ Subir documento')
            // KPIs
            ->assertSee('Total de documentos')
            ->assertSee('Clínicos')
            ->assertSee('Administrativos')
            ->assertSee('Imágenes')
            ->assertSee('Legales')
            // Filtros
            ->assertSee('Buscar documentos...')
            ->assertSee('Todos los tipos')
            ->assertSee('Todas las categorías')
            ->assertSee('Más recientes primero')
            ->assertSee('Filtrar')
            // Tabla
            ->assertSee('Lista de documentos')
            ->assertSee('Nombre del documento')
            ->assertSee('Tipo')
            ->assertSee('Categoría')
            ->assertSee('Fecha')
            ->assertSee('Tamaño')
            ->assertSee('Acciones')
            // Vista previa
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
        $this->actingAs($this->enfermero);

        $component = Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
            ->set('tabActivo', 'documentos');

        // Verificar presencia de documentos por defecto
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
        $this->actingAs($this->enfermero);

        $component = Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
            ->set('tabActivo', 'documentos');

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
        $this->actingAs($this->enfermero);

        $component = Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
            ->set('tabActivo', 'documentos');

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
        $this->actingAs($this->enfermero);

        $file = UploadedFile::fake()->create('prueba_clinica.pdf', 200, 'application/pdf');

        $component = Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
            ->set('tabActivo', 'documentos')
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

        $this->assertDatabaseHas('documentos_adulto_mayor', [
            'cod_am' => $this->adulto->cod_am,
            'nombre' => 'Evaluación Psicológica Trimestral',
        ]);
    }

    public function test_controlador_archivo_con_preview(): void
    {
        Storage::fake('local');
        $this->actingAs($this->enfermero);

        $path = 'documentos/' . $this->adulto->cod_am . '/test.pdf';
        Storage::disk('local')->put($path, '%PDF-1.4 test content');

        $doc = DocumentoAdultoMayor::create([
            'cod_am' => $this->adulto->cod_am,
            'nombre' => 'PDF de Prueba',
            'tipo_documento' => 'Informe',
            'ruta_archivo' => $path,
            'fecha_subida' => today()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        // Preview inline
        $responsePreview = $this->get(route('admin.adultos-mayores.documentos.archivo', [
            'adulto_mayor' => $this->adulto->cod_am,
            'documento' => $doc->cod_doc_am,
            'preview' => 1,
        ]));

        $responsePreview->assertOk();

        // Download normal
        $responseDownload = $this->get(route('admin.adultos-mayores.documentos.archivo', [
            'adulto_mayor' => $this->adulto->cod_am,
            'documento' => $doc->cod_doc_am,
        ]));

        $responseDownload->assertDownload();
    }
}
