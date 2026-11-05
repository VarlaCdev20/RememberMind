<?php

namespace Tests\Unit;

use App\Modulos\Identidad\Dominio\Reglas\DatosPersonalesTrabajador;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class DatosPersonalesTrabajadorTest extends TestCase
{
    public function test_limites_incluyen_18_y_50_cumplidos_y_excluyen_17_y_51(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-11-05 14:00:00'));
        $this->assertSame(['min'=>'1975-11-06', 'max'=>'2008-11-05'], DatosPersonalesTrabajador::limitesNacimiento());
        foreach (['2008-11-05'=>true, '2008-11-06'=>false, '1976-11-05'=>true, '1976-11-04'=>true,
            '1975-11-06'=>true, '1975-11-05'=>false, '2026-11-06'=>false, '2000-02-30'=>false] as $fecha=>$valida) {
            $this->assertSame($valida, Validator::make(['fecha'=>$fecha], ['fecha'=>DatosPersonalesTrabajador::reglaNacimiento()])->passes(), $fecha);
        }
    }

    public function test_fecha_invalida_y_nacimiento_bisiesto_no_evitan_la_edad_minima(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-02-28 08:00:00'));
        $this->assertFalse(Validator::make(['fecha'=>'2008-02-29'], ['fecha'=>DatosPersonalesTrabajador::reglaNacimiento()])->passes());
        $this->travelTo(\Carbon\Carbon::parse('2026-03-01 08:00:00'));
        $this->assertTrue(Validator::make(['fecha'=>'2008-02-29'], ['fecha'=>DatosPersonalesTrabajador::reglaNacimiento()])->passes());
    }
}
