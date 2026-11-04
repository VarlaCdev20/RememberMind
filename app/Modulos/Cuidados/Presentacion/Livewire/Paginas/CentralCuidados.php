<?php

namespace App\Modulos\Cuidados\Presentacion\Livewire\Paginas;

use App\Modulos\Cuidados\Aplicacion\Consultas\ConsultaCentralCuidados;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use App\Modulos\Cuidados\Presentacion\Navegacion\NavegacionCuidadosService;
use App\Modulos\Identidad\Presentacion\Navegacion\VisibilidadNavegacion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class CentralCuidados extends Component
{
    use WithPagination;

    #[Url]
    public string $vista = 'resumen';

    #[Url]
    public string $tipo = '';

    #[Url]
    public string $residente = '';

    #[Url]
    public string $desde = '';

    #[Url]
    public string $hasta = '';

    public bool $mostrarDetalle = false;

    #[Locked]
    public array $detalle = [];

    public bool $mostrarRegistro = false;

    public string $registroTipo = '';

    public string $registroResidente = '';

    public function mount(): void
    {
        app(ConsultaCentralCuidados::class)->tiposDisponibles(Auth::user());
        $this->desde = $this->desde ?: today()->subDays(6)->toDateString();
        $this->hasta = $this->hasta ?: today()->toDateString();
    }

    public function updated(string $propiedad): void
    {
        if (in_array($propiedad, ['tipo', 'residente', 'desde', 'hasta', 'vista'], true)) {
            $this->resetPage();
            $this->cerrarDetalle();
        }
    }

    public function verRegistros(string $tipo): void
    {
        abort_unless(isset(app(ConsultaCentralCuidados::class)->tiposDisponibles(Auth::user())[$tipo]), 403);
        $this->tipo = $tipo;
        $this->vista = 'registros';
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->reset('tipo', 'residente');
        $this->desde = today()->subDays(6)->toDateString();
        $this->hasta = today()->toDateString();
        $this->resetPage();
        $this->cerrarDetalle();
    }

    public function abrirDetalle(string $origen, string $codigo): void
    {
        $this->detalle = app(ConsultaCentralCuidados::class)->detalle(Auth::user(), $origen, $codigo);
        $this->mostrarDetalle = true;
    }

    public function cerrarDetalle(): void
    {
        $this->reset('mostrarDetalle', 'detalle');
    }

    private function opcionesRegistro(): array
    {
        $usuario = Auth::user();
        $turnos = app(TurnoEnfermeriaService::class);
        if (! $usuario?->hasRole('ENFERMEROS') || ! $usuario->personal
            || ! in_array($usuario->personal->estado, ['ACTIVO', 'ACTIVA'], true) || ! $turnos->obtenerTurnoActivo($usuario)) {
            return [];
        }
        $opciones = [];
        foreach (NavegacionCuidadosService::opciones() as $tipo => $opcion) {
            $ruta = match ($tipo) {
                'cognicion', 'conducta', 'sueno' => 'admin.enfermeria.seguimiento',
                'heridas' => 'admin.enfermeria.registros',
                default => 'admin.enfermeria.pacientes',
            };
            $permiso = match ($tipo) {
                'cognicion', 'conducta', 'sueno' => 'atenciones.crear',
                'heridas' => 'heridas.crear',
                default => $opcion['create'] ?? null,
            };
            if ($permiso && app(VisibilidadNavegacion::class)->puedeVerRuta($ruta)
                && $usuario->can($opcion['permission']) && $usuario->can($permiso)
                && (! in_array($tipo, ['cognicion', 'conducta', 'sueno', 'heridas'], true) || $usuario->can('atenciones.ver'))) {
                $opciones[$tipo] = ['label' => $opcion['label'], 'permiso' => $permiso];
            }
        }

        return $opciones;
    }

    public function abrirRegistro(): void
    {
        abort_unless($this->opcionesRegistro() !== [], 403);
        $this->resetValidation();
        $this->registroTipo = isset($this->opcionesRegistro()[$this->tipo]) ? $this->tipo : '';
        $this->registroResidente = $this->residente;
        $this->mostrarRegistro = true;
    }

    public function continuarRegistro(): void
    {
        $this->validate(['registroTipo' => 'required|string', 'registroResidente' => 'required|string|max:20'], [
            'registroTipo.required' => 'Selecciona el cuidado que necesitas registrar.',
            'registroResidente.required' => 'Selecciona al residente antes de continuar.',
        ]);
        $opcion = $this->opcionesRegistro()[$this->registroTipo] ?? null;
        abort_unless($opcion, 403);
        $turnos = app(TurnoEnfermeriaService::class);
        $turnos->autorizarConsultaResidente($this->registroResidente, Auth::user());
        $turnos->autorizarMutacionPaciente($this->registroResidente, $opcion['permiso'], Auth::user());
        if (in_array($this->registroTipo, ['cognicion', 'conducta', 'sueno'], true)) {
            $url = route('admin.enfermeria.seguimiento', ['adulto' => $this->registroResidente,
                'cuidado' => $this->registroTipo === 'sueno' ? null : $this->registroTipo]);
        } elseif ($this->registroTipo === 'heridas') {
            $url = route('admin.enfermeria.registros', ['adulto' => $this->registroResidente, 'seccion' => 'HERIDAS', 'cuidado' => 'heridas']);
        } else {
            $url = route('admin.enfermeria.pacientes', ['residente' => $this->registroResidente, 'cuidado' => $this->registroTipo, 'registrar' => 'cuidado']);
        }
        $this->redirect($url, navigate: true);
    }

    public function cerrarRegistro(): void
    {
        $this->reset('mostrarRegistro', 'registroTipo', 'registroResidente');
        $this->resetValidation();
    }

    public function render()
    {
        abort_unless(in_array($this->vista, ['resumen', 'registros'], true), 404);
        $consulta = app(ConsultaCentralCuidados::class);
        $tipos = $consulta->tiposDisponibles(Auth::user());
        if ($this->mostrarDetalle && $this->detalle !== []) {
            $this->detalle = $consulta->detalle(Auth::user(), $this->detalle['origen'], $this->detalle['codigo']);
        }
        $datos = null;
        try {
            $datos = $consulta->consultar(Auth::user(), ['tipo' => $this->tipo, 'residente' => $this->residente, 'desde' => $this->desde, 'hasta' => $this->hasta], $this->getPage());
            $this->resetValidation(['desde', 'hasta', 'tipo', 'residente']);
        } catch (ValidationException $error) {
            $this->setErrorBag($error->validator->errors());
        }
        $residentes = app(TurnoEnfermeriaService::class)->obtenerPacientesAsignadosQuery(Auth::user())
            ->orderBy('apellido_paterno')->get(['cod_residente', 'nombres', 'apellido_paterno', 'apellido_materno']);

        return view('livewire.cuidados.central-cuidados', [
            'datos' => $datos, 'tipos' => $tipos, 'residentes' => $residentes,
            'opcionesRegistro' => $this->opcionesRegistro(),
        ])->layout('layouts.enfermeria');
    }
}
