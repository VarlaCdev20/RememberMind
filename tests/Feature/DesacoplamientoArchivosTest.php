<?php

namespace Tests\Feature;

use App\Compartido\Aplicacion\Contratos\ArchivosAplicacion;
use App\Compartido\Aplicacion\Contratos\GeneradorPdf;
use App\Compartido\Aplicacion\Contratos\NotificacionesRegistro;
use App\Compartido\Infraestructura\Documentos\ArchivosLaravel;
use App\Models\User;
use App\Modulos\Admisiones\Aplicacion\Acciones\RegistrarSolicitudPreadmision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DesacoplamientoArchivosTest extends TestCase
{
    use RefreshDatabase;

    public static function resultadosDisco(): array
    {
        return ['directorio fallido' => [false, false, false], 'escritura fallida' => [true, false, false], 'documento guardado' => [true, true, true]];
    }

    #[DataProvider('resultadosDisco')]
    public function test_documento_solo_se_declara_generado_si_se_guarda(bool $directorio, bool $escritura, bool $generado): void
    {
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $ficheros = Mockery::mock(ArchivosAplicacion::class);
        $ficheros->shouldReceive('crearDirectorio')->once()->andReturn($directorio);
        if ($directorio) {
            $ficheros->shouldReceive('escribir')->once()->andReturn($escritura);
        }
        if ($generado) {
            $ficheros->shouldReceive('hash')->once()->andReturn(str_repeat('a', 64));
        }
        $pdf = Mockery::mock(GeneradorPdf::class);
        if ($directorio) {
            $pdf->shouldReceive('contenido')->once()->andReturn('%PDF-fixture');
        }
        $notificaciones = Mockery::mock(NotificacionesRegistro::class);
        $datos = array_fill_keys(['familiar_ap_materno', 'familiar_ci', 'familiar_correo', 'ap_materno', 'celular', 'telefono', 'departamento_residencia', 'ciudad_municipio', 'zona', 'calle', 'direccion_referencia'], '');
        $datos += [
            'familiar_nombres' => 'ANA', 'familiar_ap_paterno' => 'PEREZ', 'familiar_celular' => '70011223', 'familiar_direccion' => 'LA PAZ',
            'nombres' => 'MARIO', 'ap_paterno' => 'PEREZ', 'ci' => '1234567', 'expedicion_ci' => 'LP', 'fecha_nac' => '1940-01-01', 'genero' => 'MASCULINO', 'estado_civil' => 'SOLTERO',
            'motivo_ingreso' => 'CUIDADO_INTEGRAL', 'procedencia_ingreso' => 'FAMILIAR', 'tipo_ingreso' => 'REGULAR', 'permanencia' => 'PERMANENTE', 'prioridad' => 'MEDIA', 'descripcion_caso' => 'PRUEBA DE ALMACENAMIENTO',
            'docs_pendientes_48h' => [], 'archivos' => [],
            'configuracion_documentos' => [['es_generado_sistema' => true, 'vista_pdf' => 'fixture', 'archivo_pdf' => 'solicitud.pdf', 'tipo' => 'SOLICITUD', 'nombre' => 'Solicitud']],
        ];

        $resultado = (new RegistrarSolicitudPreadmision($notificaciones, $pdf, $ficheros))->ejecutar($usuario, $datos);

        $this->assertSame($generado, $resultado['todosGenerados']);
        $this->assertDatabaseCount('documentos', $generado ? 1 : 0);
        $this->assertDatabaseHas('preadmisiones', ['estado' => 'PENDIENTE', 'numero_documento' => '1234567']);
        $this->assertDatabaseCount('residentes', 0);
        if ($generado) {
            $this->assertDatabaseHas('documentos', ['tipo_documento' => 'SOLICITUD', 'estado' => 'VIGENTE']);
        }
    }

    public function test_subidas_fallidas_no_devuelven_una_ruta_persistible(): void
    {
        $archivo = new class
        {
            public function store(...$argumentos): false
            {
                return false;
            }

            public function storeAs(...$argumentos): false
            {
                return false;
            }
        };
        $adaptador = new ArchivosLaravel;
        foreach (['recibir', 'recibirComo'] as $metodo) {
            try {
                $metodo === 'recibir' ? $adaptador->recibir($archivo, 'fixture', 'public') : $adaptador->recibirComo($archivo, 'fixture', 'archivo.pdf', 'public');
                $this->fail('Una subida fallida debe lanzar una excepción.');
            } catch (\RuntimeException $excepcion) {
                $this->assertSame('No se pudo guardar el archivo.', $excepcion->getMessage());
            }
        }
    }
}
