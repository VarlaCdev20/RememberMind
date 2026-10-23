<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class GraficasAlertasTest extends TestCase
{
    public function test_la_prioridad_critica_conserva_su_color_y_valor_en_anillo_y_leyenda(): void
    {
        $html = Blade::render('<x-ui.dashboard-data-panel :panel="$panel" />', ['panel' => [
            'title' => 'Prioridad registrada', 'type' => 'donut',
            'items' => [['label' => 'CRITICO', 'value' => 1, 'tone' => 'danger']],
        ]]);

        $this->assertStringContainsString('conic-gradient(color-mix(in srgb, var(--rm-danger-strong)', $html);
        $this->assertStringContainsString('--rm-chart-item-color: var(--rm-danger-strong)', $html);
        $this->assertStringContainsString('CRITICO: 1', $html);
        $this->assertStringContainsString('<strong>1</strong>', $html);
        $this->assertStringNotContainsString('Sin datos por mostrar', $html);
    }

    public function test_cero_registros_no_produce_un_anillo_vacio_ni_porcentajes_invalidos(): void
    {
        $html = Blade::render('<x-ui.dashboard-data-panel :panel="$panel" />', ['panel' => [
            'title' => 'Prioridad registrada', 'type' => 'donut',
            'emptyTitle' => 'Ninguna alerta en esta consulta',
            'items' => [['label' => 'CRITICO', 'value' => 0, 'tone' => 'danger']],
        ]]);

        $this->assertStringContainsString('Ninguna alerta en esta consulta', $html);
        $this->assertStringNotContainsString('conic-gradient(', $html);
        $this->assertStringNotContainsString('NaN', $html);
        $this->assertStringNotContainsString('Infinity', $html);
    }
}
