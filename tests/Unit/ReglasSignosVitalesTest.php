<?php

namespace Tests\Unit;

use App\Backend\Modulos\Clinica\SignosVitales\Reglas\ReglaFrecuenciaCardiaca;
use App\Backend\Modulos\Clinica\SignosVitales\Reglas\ReglaFrecuenciaRespiratoria;
use App\Backend\Modulos\Clinica\SignosVitales\Reglas\ReglaGlucemia;
use App\Backend\Modulos\Clinica\SignosVitales\Reglas\ReglaPresionArterial;
use App\Backend\Modulos\Clinica\SignosVitales\Reglas\ReglaSaturacionOxigeno;
use App\Backend\Modulos\Clinica\SignosVitales\Reglas\ReglaTemperatura;
use App\Backend\Modulos\Clinica\SignosVitales\Tipos\ComportamientoAlerta;
use App\Backend\Modulos\Clinica\SignosVitales\Tipos\SeveridadClinica;
use Tests\TestCase;

class ReglasSignosVitalesTest extends TestCase
{
    public function test_presion_usa_ambos_valores_y_distingue_advertencia_de_critico(): void
    {
        $regla = new ReglaPresionArterial;
        $this->assertNull($regla->evaluar([]));
        $this->assertSame(SeveridadClinica::NORMAL, $regla->evaluar(['presion_sistolica' => 119, 'presion_diastolica' => 79])->severidad);
        $this->assertSame(SeveridadClinica::ADVERTENCIA, $regla->evaluar(['presion_sistolica' => 90, 'presion_diastolica' => 70])->severidad);
        $critico = $regla->evaluar(['presion_sistolica' => 181, 'presion_diastolica' => 80]);
        $this->assertSame(SeveridadClinica::CRITICO, $critico->severidad);
        $this->assertStringContainsString('sistólica', $critico->explicacion);
    }

    public function test_pulso_y_respiracion_respetan_los_limites_aprobados(): void
    {
        $pulso = new ReglaFrecuenciaCardiaca;
        $respiracion = new ReglaFrecuenciaRespiratoria;
        $this->assertNull($pulso->evaluar([]));
        $this->assertSame(SeveridadClinica::ADVERTENCIA, $pulso->evaluar(['frecuencia_cardiaca' => 50])->severidad);
        $this->assertSame(SeveridadClinica::ALTO, $pulso->evaluar(['frecuencia_cardiaca' => 130])->severidad);
        $this->assertSame(SeveridadClinica::CRITICO, $pulso->evaluar(['frecuencia_cardiaca' => 131])->severidad);
        $this->assertNull($respiracion->evaluar([]));
        $this->assertSame(SeveridadClinica::NORMAL, $respiracion->evaluar(['frecuencia_respiratoria' => 20])->severidad);
        $this->assertSame(SeveridadClinica::ALTO, $respiracion->evaluar(['frecuencia_respiratoria' => 24])->severidad);
        $this->assertSame(SeveridadClinica::CRITICO, $respiracion->evaluar(['frecuencia_respiratoria' => 25])->severidad);
    }

    public function test_temperatura_y_glucemia_no_convierten_una_advertencia_en_alerta(): void
    {
        $temperatura = new ReglaTemperatura;
        $glucemia = new ReglaGlucemia;
        $this->assertNull($temperatura->evaluar([]));
        $this->assertSame(SeveridadClinica::ADVERTENCIA, $temperatura->evaluar(['temperatura' => 37.8])->severidad);
        $this->assertSame(SeveridadClinica::CRITICO, $temperatura->evaluar(['temperatura' => 39.1])->severidad);
        $this->assertNull($glucemia->evaluar([]));
        $this->assertSame(SeveridadClinica::ADVERTENCIA, $glucemia->evaluar(['glucemia' => 69])->severidad);
        $this->assertSame(SeveridadClinica::CRITICO, $glucemia->evaluar(['glucemia' => 53])->severidad);
        $alta = $glucemia->evaluar(['glucemia' => 251]);
        $this->assertNull($alta->severidad);
        $this->assertSame(ComportamientoAlerta::SUGERIR, $alta->comportamientoAlerta);
    }

    public function test_saturacion_no_recibe_un_diagnostico_sin_objetivo_individual(): void
    {
        $regla = new ReglaSaturacionOxigeno;
        $this->assertNull($regla->evaluar([]));
        $this->assertNull($regla->evaluar(['saturacion_oxigeno' => 89])->severidad);
    }
}
