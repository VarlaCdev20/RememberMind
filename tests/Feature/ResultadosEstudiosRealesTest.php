<?php

namespace Tests\Feature;

use App\Backend\Modulos\Clinica\Servicios\ResultadosEstudiosService;
use App\Models\Residente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultadosEstudiosRealesTest extends TestCase
{
    use RefreshDatabase;

    public function test_residente_sin_estudios_no_recibe_resultados_profesionales_ni_tendencias_ficticias(): void
    {
        $residente = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_SIN_ESTUDIOS',
            'nombres' => 'Paciente',
            'apellido_paterno' => 'Sin Estudios',
            'fecha_nacimiento' => '1940-01-01',
            'estado' => 'ADMITIDO',
        ]);

        $servicio = app(ResultadosEstudiosService::class);

        $this->assertCount(0, $servicio->obtenerEstudios($residente));
        $this->assertSame([], $servicio->obtenerDatosGrafico($residente, 'glucosa')['data']);
        $this->assertSame('No registrado', $servicio->obtenerDatosGrafico($residente, 'glucosa')['texto_rango']);
        $this->assertSame([], $servicio->obtenerRangosReferencia($residente, 'glucosa')['clasificaciones']);
    }
}
