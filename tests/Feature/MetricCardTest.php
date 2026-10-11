<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class MetricCardTest extends TestCase
{
    public function test_renderiza_valor_real_y_progreso_semantico(): void
    {
        $html = Blade::render('<x-ui.metric-card icon="ph-bed" label="Camas ocupadas" value="28 / 32" description="88% de ocupación" :progress="87.5" />');

        $this->assertStringContainsString('28 / 32', $html);
        $this->assertStringContainsString('role="progressbar"', $html);
        $this->assertStringContainsString('aria-valuenow="88"', $html);
        $this->assertStringContainsString('width: 87.5%', $html);
        $this->assertStringNotContainsString('<a ', $html);
    }

    public function test_sin_dato_no_fabrica_cero_ni_progreso(): void
    {
        $html = Blade::render('<x-ui.metric-card icon="ph-shield-warning" label="Alto riesgo" :value="null" :progress="null" />');

        $this->assertStringContainsString('>—</strong>', $html);
        $this->assertStringContainsString('Sin datos disponibles', $html);
        $this->assertStringNotContainsString('role="progressbar"', $html);
        $this->assertStringNotContainsString('<a ', $html);
    }

    public function test_solo_es_enlace_interactivo_si_tiene_destino_real(): void
    {
        $html = Blade::render('<x-ui.metric-card icon="ph-users-three" label="Pacientes" :value="7" href="/admin/enfermeria/pacientes" />');

        $this->assertStringContainsString('<a href="/admin/enfermeria/pacientes"', $html);
        $this->assertStringContainsString('rm-card--interactive', $html);
        $this->assertStringNotContainsString('href="#"', $html);
    }

    public function test_loading_muestra_skeleton_y_no_un_cero_temporal(): void
    {
        $html = Blade::render('<x-ui.metric-card icon="ph-pill" label="Medicamentos pendientes" :loading="true" />');

        $this->assertStringContainsString('rm-skeleton', $html);
        $this->assertStringContainsString('aria-busy="true"', $html);
        $this->assertStringNotContainsString('>0</strong>', $html);
    }
}
