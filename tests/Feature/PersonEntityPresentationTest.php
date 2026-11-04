<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class PersonEntityPresentationTest extends TestCase
{
    public function test_tarjeta_de_enfermeria_conserva_edad_ubicacion_y_contexto(): void
    {
        $html = Blade::render('<x-ui.resident-card code="SINTETICO" name="ÁLVARO PRUEBA" :age="82" location="Habitación de prueba" context-label="Próximo cuidado" context-value="Control programado" :alert-count="0" />');

        $this->assertStringContainsString('Álvaro Prueba', $html);
        foreach (['82 años', 'Habitación de prueba', 'Control programado', 'Sin alertas prioritarias', 'data-entity="resident"'] as $texto) {
            $this->assertStringContainsString($texto, $html);
        }
    }

    public function test_tarjeta_de_personal_reutiliza_identidad_sin_datos_residenciales(): void
    {
        $html = Blade::render('<x-ui.resident-card code="SINTETICO" name="PERSONA PRUEBA" entity="personal" secondary="persona@example.test" :show-location="false"><x-slot:actions><button type="button">Ver ficha</button></x-slot:actions></x-ui.resident-card>');

        foreach (['Persona Prueba', 'persona@example.test', 'data-entity="personal"', 'Ver ficha'] as $texto) {
            $this->assertStringContainsString($texto, $html);
        }
        foreach (['Edad no registrada', 'Sin ubicación asignada', 'rm-resident-compact-card__facts'] as $texto) {
            $this->assertStringNotContainsString($texto, $html);
        }
    }

    public function test_contenido_adicional_y_nota_no_se_pierden_sin_ubicacion(): void
    {
        $html = Blade::render('<x-ui.resident-card code="SINTETICO" name="PERSONA PRUEBA" :show-location="false"><x-slot:details>Detalle autorizado</x-slot:details><x-slot:footerNote>Nota conservada</x-slot:footerNote></x-ui.resident-card>');

        $this->assertStringContainsString('Detalle autorizado', $html);
        $this->assertStringContainsString('Nota conservada', $html);
    }
}
