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
        $resultado = $regla->evaluar(['saturacion_oxigeno' => 89]);
        $this->assertNull($resultado->severidad);
        $this->assertSame(ComportamientoAlerta::NINGUNA, $resultado->comportamientoAlerta);
        $this->assertSame('Sin objetivo médico vigente', $resultado->referenciaUtilizada);
        $this->assertStringContainsString('SpO₂ medida: 89 %', $resultado->explicacion);
        $this->assertStringContainsString('no indica que la saturación sea normal', $resultado->explicacion);
    }

    public function test_explicacion_identifica_valor_direccion_y_limite_critico_sin_cambiar_clasificacion(): void
    {
        $pulsoAlto = (new ReglaFrecuenciaCardiaca)->evaluar(['frecuencia_cardiaca' => 135]);
        $pulsoBajo = (new ReglaFrecuenciaCardiaca)->evaluar(['frecuencia_cardiaca' => 40]);
        $respiracion = (new ReglaFrecuenciaRespiratoria)->evaluar(['frecuencia_respiratoria' => 5]);
        $temperatura = (new ReglaTemperatura)->evaluar(['temperatura' => 35]);
        foreach ([$pulsoAlto, $pulsoBajo, $respiracion, $temperatura] as $resultado) {
            $this->assertSame(SeveridadClinica::CRITICO, $resultado->severidad);
            $this->assertSame(ComportamientoAlerta::AUTOMATICA_AL_CONFIRMAR, $resultado->comportamientoAlerta);
        }
        $this->assertStringContainsString('135 latidos por minuto', $pulsoAlto->explicacion);
        $this->assertStringContainsString('>130 lpm', $pulsoAlto->explicacion);
        $this->assertStringContainsString('≤40 lpm', $pulsoBajo->explicacion);
        $this->assertStringContainsString('5 respiraciones por minuto', $respiracion->explicacion);
        $this->assertStringContainsString('≤8 rpm', $respiracion->explicacion);
        $this->assertStringContainsString('límite crítico inferior', $temperatura->explicacion);
        $normal = (new ReglaFrecuenciaCardiaca)->evaluar(['frecuencia_cardiaca' => 72]);
        $this->assertSame(SeveridadClinica::NORMAL, $normal->severidad);
        $this->assertStringContainsString('dentro del intervalo de referencia', $normal->explicacion);
        $this->assertNull($normal->recomendacion);
    }
}
