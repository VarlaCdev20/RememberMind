<?php

namespace Tests\Feature;

use App\Backend\Modulos\Documentos\Servicios\DocumentacionResidenteService;
use App\Frontend\Livewire\Compartido\Clinica\FichaPaciente;
use App\Models\AdultoMayor;
use App\Models\Documento;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentacionResidenteDatosRealesTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->usuario = User::factory()->create([
            'estado' => 'ACTIVO',
            'nombres' => 'Elena',
            'ap_paterno' => 'Rojas',
        ]);
        $this->usuario->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($this->usuario);
    }

    public function test_residente_sin_documentos_no_recibe_expediente_de_demostracion(): void
    {
        $residente = AdultoMayor::factory()->create();
        $servicio = app(DocumentacionResidenteService::class);

        $documentos = $servicio->obtenerDocumentos($residente);

        $this->assertCount(0, $documentos);
        $this->assertSame([
            'total' => 0,
            'clinicos' => 0,
            'administrativos' => 0,
            'imagenes' => 0,
            'legales' => 0,
        ], $servicio->obtenerMetricas($documentos));

        Livewire::test(FichaPaciente::class, ['adulto' => $residente->cod_residente])
            ->call('cambiarTab', 'documentos')
            ->assertSee('No encontramos documentos con estos filtros')
            ->assertDontSee('Informe médico geriátrico')
            ->assertDontSee('Dr. Carlos Méndez')
            ->assertDontSee('Laura González');
    }

    public function test_documento_sin_archivo_disponible_no_inventa_metadatos(): void
    {
        $residente = AdultoMayor::factory()->create();

        Documento::create([
            'cod_documento' => 'DOC_REAL_001',
            'cod_residente' => $residente->cod_residente,
            'cod_usuario' => $this->usuario->cod_usuario,
            'tipo_documento' => 'INFORME_CLINICO',
            'nombre' => 'Informe real de control',
            'ruta_archivo' => 'documentos/archivo-inexistente.pdf',
            'tipo_archivo' => 'application/pdf',
            'hash_archivo' => hash('sha256', 'archivo-inexistente'),
            'estado' => 'PENDIENTE',
            'observacion' => null,
        ]);

        $documento = app(DocumentacionResidenteService::class)->obtenerDocumentos($residente)->sole();

        $this->assertSame('Informe real de control', $documento['nombre']);
        $this->assertSame('No disponible', $documento['tamano']);
        $this->assertSame('No registrada', $documento['fecha_formateada']);
        $this->assertSame('', $documento['hora_formateada']);
        $this->assertSame($this->usuario->name, $documento['subido_por']);
        $this->assertFalse($documento['es_verificado']);
        $this->assertEmpty($documento['trazabilidad']);
    }
}
