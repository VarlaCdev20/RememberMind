<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Livewire\Component;
use Livewire\Livewire;
use Livewire\WithPagination;
use Tests\TestCase;

class PaginacionInstitucionalTest extends TestCase
{
    public function test_url_conserva_filtros_arrays_y_otro_paginador_al_navegar_o_cambiar_tamano(): void
    {
        $this->app->instance('request', Request::create('http://localhost/admisiones?search=prueba&estados[]=ACTIVO&otros=2&admisiones=4&por_pagina=20'));
        $paginator = new LengthAwarePaginator(range(61, 80), 320, 20, 4, ['path' => 'http://localhost/admisiones', 'pageName' => 'admisiones']);

        $html = html_entity_decode(Blade::render('<x-ui.paginacion :paginator="$paginator" :per-page="20" label="admisiones" />', compact('paginator')));

        $this->assertStringContainsString('admisiones=3', $html);
        $this->assertStringContainsString('search=prueba', $html);
        $this->assertStringContainsString('otros=2', $html);
        $this->assertStringContainsString('name="estados[0]" value="ACTIVO"', $html);
        $this->assertStringContainsString('name="search" value="prueba"', $html);
        $this->assertStringContainsString('name="otros" value="2"', $html);
        $this->assertStringNotContainsString('name="admisiones"', $html);
        $this->assertStringContainsString('name="por_pagina"', $html);
        $this->assertStringContainsString('value="20" selected', $html);
        $this->assertStringContainsString('aria-current="page"', $html);
        $this->assertStringContainsString('>…</span>', $html);
        $this->assertStringContainsString('wire:navigate', $html);
    }

    public function test_overrides_url_simple_y_cursor_preservan_sus_contratos_sin_selector_inoperante(): void
    {
        $paginator = new Paginator(range(1, 11), 10, 1, ['path' => 'http://localhost/listado']);
        $html = view('pagination::simple-tailwind', compact('paginator'))->render();
        $this->assertStringContainsString('rel="next"', $html);
        $this->assertStringNotContainsString('Por página', $html);
        $this->assertStringContainsString('disabled aria-label="Página anterior"', $html);

        $paginator = new CursorPaginator([], 10, null, ['path' => 'http://localhost/listado']);
        $html = view('pagination::simple-tailwind', compact('paginator'))->render();
        $this->assertStringContainsString('registros en esta página', $html);
        $this->assertStringNotContainsString('Por página', $html);
    }

    public function test_livewire_renderiza_selector_y_acciones_respetando_el_nombre_del_paginador(): void
    {
        Livewire::test(PaginacionInstitucionalFixture::class)
            ->assertSee('Por página')
            ->assertSeeHtml("gotoPage(2, 'solicitudes')")
            ->call('gotoPage', 3, 'solicitudes')
            ->assertSet('paginators.solicitudes', 3)
            ->set('porPagina', 20)
            ->assertSet('porPagina', 20)
            ->call('nextPage', 'solicitudes')
            ->assertSet('paginators.solicitudes', 4);
    }

    public function test_override_livewire_conserva_acciones_y_no_inventa_propiedad_de_tamano(): void
    {
        $component = Livewire::test(PaginacionInstitucionalFixture::class, ['override' => true]);
        $this->assertStringContainsString("nextPage('solicitudes')", html_entity_decode($component->html()));

        $component
            ->assertDontSee('Por página')
            ->call('nextPage', 'solicitudes')
            ->assertSet('paginators.solicitudes', 2);
    }
}

class PaginacionInstitucionalFixture extends Component
{
    use WithPagination;

    public int $porPagina = 10;

    public bool $override = false;

    public function render()
    {
        $paginator = new LengthAwarePaginator(range(1, $this->porPagina), 90, $this->porPagina, $this->getPage('solicitudes'), ['pageName' => 'solicitudes']);

        return $this->override
            ? view('livewire::tailwind', compact('paginator'))
            : view('components.ui.paginacion', ['paginator' => $paginator, 'mode' => 'livewire', 'perPageName' => 'porPagina']);
    }
}
