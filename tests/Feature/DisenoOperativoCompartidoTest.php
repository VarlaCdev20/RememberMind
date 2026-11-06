<?php

namespace Tests\Feature;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class DisenoOperativoCompartidoTest extends TestCase
{
    public function test_componentes_comparten_identificadores_y_conservan_acciones_y_paginacion(): void
    {
        $paginator = new LengthAwarePaginator(collect(range(1, 12)), 56, 12, 2, ['path' => '/consulta', 'pageName' => 'equipoPage']);
        $html = Blade::render(<<<'BLADE'
            <x-ui.collection-header title="Personal" subtitle="Equipo registrado"><x-slot:actions><button type="button">Registrar</button></x-slot:actions></x-ui.collection-header>
            <x-ui.page-header title="Valoraciones" />
            <x-ui.filter-bar aria-label="Filtrar"><label for="filtro">Estado</label><select id="filtro" wire:model.live="estado"><option value="">Todos</option></select></x-ui.filter-bar>
            <x-ui.table-surface><table class="rm-table"><caption>Equipo</caption><thead><tr><th scope="col">Persona</th></tr></thead><tbody><tr><td>Persona de prueba</td></tr></tbody></table><x-slot:footer><x-ui.paginacion :paginator="$paginator" mode="livewire" :show-per-page="false" /></x-slot:footer></x-ui.table-surface>
            BLADE, compact('paginator'));

        $this->assertSame(2, substr_count($html, 'data-rm-ui="page-heading"'));
        foreach (['filters', 'table-surface', 'pagination'] as $component) {
            $this->assertStringContainsString('data-rm-ui="'.$component.'"', $html);
        }
        $this->assertStringContainsString('wire:model.live="estado"', $html);
        $this->assertStringContainsString("gotoPage(3, 'equipoPage')", $html);
        $this->assertStringContainsString('aria-current="page"', $html);
        $this->assertStringContainsString('Registrar', $html);
        $this->assertStringContainsString('scope="col"', $html);
    }
}
