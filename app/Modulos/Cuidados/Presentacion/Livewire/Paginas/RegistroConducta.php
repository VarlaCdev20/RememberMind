<?php

namespace App\Modulos\Cuidados\Presentacion\Livewire\Paginas;

use App\Models\Residente;
use App\Modulos\Cuidados\Aplicacion\Acciones\RegistrarConducta;
use App\Modulos\Cuidados\Aplicacion\Consultas\ConsultarConducta;
use App\Modulos\Cuidados\Aplicacion\Consultas\MiTurnoService;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class RegistroConducta extends Component
{
    #[Locked]
    public string $codResidente = '';

    #[Locked]
    public bool $emergente = false;

    public bool $mostrarFormulario = false;

    public array $datos = [];

    #[Locked]
    public array $resultado = [];

    public int $dias = 7;

    public string $filtroHistorial = '';

    public int $paginaHistorial = 1;

    public function mount(string $codResidente = '', bool $emergente = false): void
    {
        abort_unless(Auth::user()?->estado === 'ACTIVO' && Auth::user()->can('registros_conductuales.ver'), 403);
        $this->emergente = $emergente;
        $this->codResidente = $codResidente;
        $this->reiniciarCaptura();
        if ($codResidente !== '') {
            app(TurnoEnfermeriaService::class)->autorizarConsultaResidente($codResidente, Auth::user());
            $this->mostrarFormulario = $emergente;
        }
    }

    public function seleccionarResidente(string $codigo): void
    {
        abort_unless(! $this->emergente && ! $this->mostrarFormulario, 409);
        app(TurnoEnfermeriaService::class)->autorizarConsultaResidente($codigo, Auth::user());
        $this->codResidente = $codigo;
        $this->paginaHistorial = 1;
        $this->resultado = [];
        $this->reiniciarCaptura();
    }

    public function abrirRegistro(): void
    {
        app(TurnoEnfermeriaService::class)->autorizarMutacionEnfermeria($this->codResidente, 'registros_conductuales.crear', Auth::user());
        $this->reiniciarCaptura();
        $this->resultado = [];
        $this->mostrarFormulario = true;
        $this->dispatch('conducta-captura-reiniciada');
    }

    private function reiniciarCaptura(): void
    {
        $this->datos = array_fill_keys(['estado_animo', 'participacion', 'descripcion', 'intervencion', 'respuesta', ...RegistrarConducta::INDICADORES], null);
        $this->resetValidation();
    }

    public function limpiarCaptura(): void
    {
        app(TurnoEnfermeriaService::class)->autorizarConsultaResidente($this->codResidente, Auth::user());
        $this->reiniciarCaptura();
        $this->dispatch('conducta-captura-reiniciada');
    }

    public function guardar(): void
    {
        abort_unless($this->mostrarFormulario && $this->resultado === [], 409);
        $this->resetValidation();
        try {
            $registro = app(RegistrarConducta::class)->ejecutar(Auth::user(), Residente::findOrFail($this->codResidente), $this->datos);
        } catch (ValidationException $error) {
            foreach ($error->errors() as $campo => $mensajes) {
                $this->addError('datos.'.$campo, $mensajes[0]);
            }

            return;
        } catch (HttpExceptionInterface|AuthorizationException $error) {
            throw $error;
        } catch (Throwable $error) {
            report($error);
            $this->addError('persistencia', 'No se pudo guardar el registro. Tus datos siguen aquí; vuelve a intentarlo.');

            return;
        }
        $registro->load('personal');
        $this->resultado = app(ConsultarConducta::class)->fila($registro);
        $this->reiniciarCaptura();
        $this->paginaHistorial = 1;
        $this->dispatch('conducta-registrada');
    }

    public function cerrarModal(bool $volverAlResidente = false): void
    {
        $this->reiniciarCaptura();
        $this->mostrarFormulario = false;
        $this->resultado = [];
        if ($this->emergente) {
            $this->dispatch('conducta-popup-cerrado', volverAlResidente: $volverAlResidente);
        }
    }

    public function updatedFiltroHistorial(): void
    {
        $this->paginaHistorial = 1;
    }

    public function cambiarPagina(int $pagina): void
    {
        abort_unless($pagina > 0 && $pagina <= 1000000, 422);
        $this->paginaHistorial = $pagina;
    }

    public function render()
    {
        abort_unless(Auth::user()?->estado === 'ACTIVO' && Auth::user()->can('registros_conductuales.ver'), 403);
        $contexto = $this->codResidente !== '' ? app(ConsultarConducta::class)->ejecutar(Auth::user(), $this->codResidente, $this->dias, $this->filtroHistorial, $this->paginaHistorial) : null;
        $personal = Auth::user()->personal;
        $jornada = $personal ? app(MiTurnoService::class)->resolverJornadaActual($personal, now()) : null;
        $puedeRegistrar = $personal?->estado === 'ACTIVO' && Auth::user()->hasRole('ENFERMEROS') && Auth::user()->can('registros_conductuales.crear') && $jornada
            && $this->codResidente !== '' && app(TurnoEnfermeriaService::class)->esPacienteAsignado($this->codResidente, Auth::user(), $jornada->cod_turno);

        return view('livewire.cuidados.registro-conducta', [
            'contexto' => $contexto, 'jornada' => $jornada, 'puedeRegistrar' => $puedeRegistrar,
            'residentes' => $this->codResidente === '' ? app(TurnoEnfermeriaService::class)->obtenerPacientesAsignadosQuery(Auth::user())->orderBy('apellido_paterno')->get(['cod_residente', 'nombres', 'apellido_paterno']) : collect(),
        ]);
    }
}
