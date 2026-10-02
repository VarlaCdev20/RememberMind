<?php

namespace Tests\Unit;

use App\Backend\Modulos\Clinica\Servicios\ClasificacionSignosVitalesService;
use App\Models\SignoVital;
use PHPUnit\Framework\TestCase;

class ClasificacionSignosVitalesServiceTest extends TestCase
{
    public function test_sin_mediciones_no_se_clasifica_como_normal(): void
    {
        $niveles = ClasificacionSignosVitalesService::evaluar(null, null, null, null, null, null, null);

        $this->assertSame('sin_dato', $niveles['global']);
        $this->assertSame('sin_dato', $niveles['saturacion']);
    }

    public function test_una_medicion_critica_prevalece_sobre_una_advertencia(): void
    {
        $niveles = ClasificacionSignosVitalesService::evaluar(165, 90, 80, 16, 36.5, 85, 100);

        $this->assertSame('advertencia', $niveles['pa']);
        $this->assertSame('critico', $niveles['saturacion']);
        $this->assertSame('critico', $niveles['global']);
    }

    public function test_usa_las_columnas_canonicas_y_respeta_valores_decimales(): void
    {
        $signo = new SignoVital([
            'frecuencia_cardiaca' => 130.5,
            'saturacion_oxigeno' => 94.5,
            'glucemia' => 100,
        ]);

        $niveles = ClasificacionSignosVitalesService::evaluarRegistro($signo);

        $this->assertSame('critico', $niveles['fc']);
        $this->assertSame('advertencia', $niveles['saturacion']);
        $this->assertSame('normal', $niveles['glucosa']);
        $this->assertSame('critico', $niveles['global']);
    }

    public function test_criterio_preventivo_conserva_sus_limites_independientes(): void
    {
        $this->assertFalse(ClasificacionSignosVitalesService::requiereAlertaPreventiva(
            new SignoVital(['temperatura' => 37.8, 'saturacion_oxigeno' => 92])
        ));
        $this->assertTrue(ClasificacionSignosVitalesService::requiereAlertaPreventiva(
            new SignoVital(['saturacion_oxigeno' => 91.5])
        ));
    }

    public function test_presion_incompleta_no_parece_normal_pero_conserva_un_valor_critico(): void
    {
        $sinDiastolica = ClasificacionSignosVitalesService::evaluar(120, null, null, null, null, null, null);
        $diastolicaCritica = ClasificacionSignosVitalesService::evaluar(null, 110, null, null, null, null, null);

        $this->assertSame('sin_dato', $sinDiastolica['pa']);
        $this->assertSame('critico', $diastolicaCritica['pa']);
    }
}
