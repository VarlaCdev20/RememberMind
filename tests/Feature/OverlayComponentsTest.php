<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

class OverlayComponentsTest extends TestCase
{
    public function test_modal_usa_dialogo_y_no_cierra_por_fondo_por_defecto(): void
    {
        $html = Livewire::test(OverlayModalFixture::class)->html();

        $this->assertStringContainsString('role="dialog"', $html);
        $this->assertStringContainsString('aria-modal="true"', $html);
        $this->assertStringContainsString('x-trap.noscroll="show"', $html);
        $this->assertStringContainsString('rm-modal-shell__overlay', $html);
        preg_match('/<div[^>]*class="rm-modal-shell__overlay"[^>]*>/', $html, $overlay);
        $this->assertNotEmpty($overlay);
        $this->assertStringNotContainsString('@click=', $overlay[0]);
    }

    public function test_drawer_conserva_foco_y_cierre_explicito(): void
    {
        $html = Livewire::test(OverlayDrawerFixture::class)->html();

        $this->assertStringContainsString('role="dialog"', $html);
        $this->assertStringContainsString('x-trap.noscroll="show"', $html);
        $this->assertStringContainsString('rm-drawer--md', $html);
        preg_match('/<div[^>]*class="rm-drawer-backdrop"[^>]*>/', $html, $overlay);
        $this->assertNotEmpty($overlay);
        $this->assertStringNotContainsString('@click=', $overlay[0]);
    }

    public function test_toast_comun_expone_cuatro_variantes_y_cierre_accesible(): void
    {
        $html = Blade::render('<x-ui.sweetalert />');

        $this->assertStringContainsString("closeButtonAriaLabel: 'Cerrar notificación'", $html);
        $this->assertStringContainsString('aria-live', $html);
        $this->assertStringContainsString('success: 4000', $html);
        $this->assertStringContainsString('info: 4000', $html);
        $this->assertStringContainsString('warning: 5500', $html);
        $this->assertStringContainsString('error: 6000', $html);
        $this->assertStringContainsString("window.addEventListener('rm-toast'", $html);
    }
}

class OverlayModalFixture extends Component
{
    public bool $abierto = true;

    public function render(): string
    {
        return '<div><x-ui.modal-livewire wire:model="abierto" title="Registrar observación" :show-validation="false">Contenido</x-ui.modal-livewire></div>';
    }
}

class OverlayDrawerFixture extends Component
{
    public bool $abierto = true;

    public function render(): string
    {
        return '<div><x-ui.drawer-livewire wire:model="abierto" title="Seguimiento" close-method="cerrarDrawer" :dismiss-on-backdrop="false">Contenido</x-ui.drawer-livewire></div>';
    }
}
