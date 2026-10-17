<?php

namespace App\Modulos\Cuidados\Presentacion\Livewire\Paginas;

use App\Models\Residente;
use App\Modulos\Cuidados\Aplicacion\Acciones\RegistrarObservacionCognitiva;
use App\Modulos\Cuidados\Aplicacion\Consultas\ConsultarObservacionCognitiva;
use App\Modulos\Cuidados\Aplicacion\Consultas\MiTurnoService;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use App\Modulos\Cuidados\Dominio\ObservacionCognitiva as Contrato;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ObservacionCognitiva extends Component
{
    #[Locked]
    public string $codResidente = '';
    #[Locked]
    public string $codigoCaptura = '';
    #[Locked]
    public array $resultado = [];
    public bool $mostrarFormulario = true;
    public array $datos = [];
    public int $dias = 30;
    public ?string $desde = null;
    public ?string $hasta = null;
    public string $dominioHistorial = '';
    public string $ordenHistorial = 'desc';
    public int $paginaHistorial = 1;

    public function mount(string $codResidente): void
    {
        $this->codResidente = $codResidente;
        $this->autorizarConsulta();
        $this->desde = now()->subDays(29)->toDateString();
        $this->hasta = today()->toDateString();
        $this->reiniciar();
    }

    private function autorizarConsulta(): void
    {
        abort_unless(Auth::user()?->estado === 'ACTIVO' && Auth::user()->can('controles_cognitivos.ver') && ! Auth::user()->hasRole('FAMILIAR'), 403);
        app(TurnoEnfermeriaService::class)->autorizarConsultaResidente($this->codResidente, Auth::user());
    }

    private function reiniciar(): void
    {
        $this->datos = array_fill_keys([...Contrato::TEXTOS, ...Contrato::SENALES, 'observacion'], null);
        $this->codigoCaptura = app(\App\Compartido\Infraestructura\Persistencia\GeneradorCodigo::class)->siguiente('controles_cognitivos');
        $this->resetValidation();
    }

    public function limpiarCaptura(): void
    {
        $this->autorizarConsulta();
        abort_unless($this->mostrarFormulario && $this->resultado === [], 409);
        $this->reiniciar();
        $this->dispatch('cognicion-captura-reiniciada');
    }

    public function guardar(): void
    {
        $this->autorizarConsulta();
        abort_unless($this->mostrarFormulario && $this->resultado === [], 409);
        $this->resetValidation();
        try {
            $registro = app(RegistrarObservacionCognitiva::class)->ejecutar(Auth::user(), Residente::findOrFail($this->codResidente), $this->datos, $this->codigoCaptura);
        } catch (ValidationException $error) {
            foreach ($error->errors() as $campo => $mensajes) {
                $this->addError('datos.'.$campo, $mensajes[0]);
            }
            $this->dispatch('cognicion-validacion');

            return;
        } catch (AuthorizationException|HttpExceptionInterface $error) {
            throw $error;
        } catch (Throwable $error) {
            report($error);
            $this->addError('persistencia', 'No se pudo guardar. Tus respuestas se conservan; vuelve a intentarlo.');
            $this->dispatch('cognicion-validacion');

            return;
        }
        $this->resultado = app(ConsultarObservacionCognitiva::class)->fila($registro->load('personal'));
        $this->paginaHistorial = 1;
        $this->dispatch('cognicion-registrada');
    }

    public function nuevoRegistro(): void
    {
        app(TurnoEnfermeriaService::class)->autorizarMutacionEnfermeria($this->codResidente, 'controles_cognitivos.crear', Auth::user());
        $this->reiniciar();
        $this->resultado = [];
        $this->dispatch('cognicion-captura-reiniciada');
    }

    public function cerrarModal(bool $volverAlResidente = false): void
    {
        $this->autorizarConsulta();
        $this->mostrarFormulario = false;
        $this->dispatch('cognicion-popup-cerrado', volverAlResidente: $volverAlResidente);
    }

    public function updated(string $campo): void
    {
        if (in_array($campo, ['dias', 'desde', 'hasta', 'dominioHistorial', 'ordenHistorial'], true)) {
            $this->paginaHistorial = 1;
            foreach (['dias', 'desde', 'hasta', 'dominio', 'orden', 'pagina'] as $filtro) {
                $this->resetValidation($filtro);
            }
        }
    }

    public function cambiarPagina(int $pagina): void
    {
        abort_unless($pagina >= 1 && $pagina <= 1000000, 422);
        $this->paginaHistorial = $pagina;
    }

    public function reintentarConsulta(): void
    {
        $this->autorizarConsulta();
    }

    public function render()
    {
        $this->autorizarConsulta();
        $contexto = null;
        $errorConsulta = null;
        try {
            $contexto = app(ConsultarObservacionCognitiva::class)->ejecutar(Auth::user(), $this->codResidente, [
                'dias' => $this->dias, 'desde' => $this->desde, 'hasta' => $this->hasta, 'dominio' => $this->dominioHistorial,
                'orden' => $this->ordenHistorial, 'pagina' => $this->paginaHistorial]);
        } catch (ValidationException $error) {
            foreach ($error->errors() as $campo => $mensajes) {
                $this->addError($campo, $mensajes[0]);
            }
            $errorConsulta = 'Revisa los filtros del historial.';
        } catch (AuthorizationException|HttpExceptionInterface $error) {
            throw $error;
        } catch (Throwable $error) {
            report($error);
            $errorConsulta = 'No se pudo consultar el historial. La captura se conserva.';
        }
        $personal = Auth::user()->personal;
        $jornada = $personal ? app(MiTurnoService::class)->resolverJornadaActual($personal, now()) : null;
        $puedeRegistrar = Auth::user()->hasRole('ENFERMEROS') && $personal?->estado === 'ACTIVO' && Auth::user()->can('controles_cognitivos.crear') && $jornada
            && app(TurnoEnfermeriaService::class)->esPacienteAsignado($this->codResidente, Auth::user(), $jornada->cod_turno);

        return view('livewire.cuidados.observacion-cognitiva', [
            'residente' => $contexto['residente'] ?? Residente::with('ocupacionActiva.cama.habitacion')->findOrFail($this->codResidente),
            'contexto' => $contexto, 'errorConsulta' => $errorConsulta, 'personal' => $personal, 'jornada' => $jornada,
            'nombreProfesional' => trim(implode(' ', array_filter([$personal?->nombres, $personal?->apellido_paterno, $personal?->apellido_materno]))) ?: 'Sin personal activo vinculado',
            'puedeRegistrar' => $puedeRegistrar, 'campos' => Contrato::campos(), 'dominios' => Contrato::DOMINIOS,
            'opciones' => collect(Contrato::TEXTOS)->mapWithKeys(fn ($campo) => [$campo => Contrato::opciones($campo)])->all(),
        ]);
    }
}
