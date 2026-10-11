<?php

namespace App\Compartido\Presentacion\Componentes;

use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Consultas\ConsultarEncabezadoUsuario;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class DashboardWelcomeHeader extends Component
{
    public function __construct(
        public ?User $usuario = null,
        public string $image = 'images/FOTOS CENTRO DE ADULTOS MAYORES/621786801_1404497435021508_7880315777607437580_n.jpg',
        public string $secondaryImage = 'images/FOTOS CENTRO DE ADULTOS MAYORES/558487013_1337134818424437_2282337776297854403_n.jpg',
        public ?string $estado = null,
        public ?string $modo = null,
        public bool $compact = false,
        public ?string $scope = null,
    ) {}

    public function render(): View
    {
        return view('components.ui.dashboard-welcome-header',
            app(ConsultarEncabezadoUsuario::class)->preparar($this->usuario, $this->estado, $this->modo, request()->routeIs('admin.enfermeria.*')));
    }
}
