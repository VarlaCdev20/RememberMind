<?php

namespace App\Modulos\Identidad\Presentacion\Livewire\Paginas;

use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Consultas\HistorialCorreoDocumental;
use App\Modulos\Identidad\Aplicacion\Servicios\EnviosCorreoDocumental;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class CorreosPanel extends Component
{
    use WithPagination;

    public string $busqueda = '';

    public string $estado = '';

    public string $tipo = '';

    public string $desde = '';

    public string $hasta = '';

    #[Locked]
    public string $usuario = '';

    #[Locked]
    public ?string $seleccion = null;

    public bool $mostrarDetalle = false;

    public ?string $confirmacion = null;

    public function mount(): void
    {
        app(HistorialCorreoDocumental::class)->autorizar(auth()->user());
        abort_unless(is_string(request()->query('usuario', '')), 400);
        $entrada = request()->validate(['usuario' => 'nullable|string|max:100']);
        $this->usuario = $entrada['usuario'] ?? '';
        if ($this->usuario !== '') {
            abort_unless(auth()->user()->can('usuarios.ver'), 403);
            User::findOrFail($this->usuario);
        }
    }

    protected function rules(): array
    {
        return [
            'busqueda' => ['present', 'string', 'max:100'],
            'estado' => ['present', Rule::in(['', ...array_keys(EnviosCorreoDocumental::ESTADOS)])],
            'tipo' => ['present', Rule::in(['', 'ficha', 'paquete'])],
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d', $this->desde !== '' ? 'after_or_equal:desde' : 'nullable'],
            'usuario' => ['present', 'string', 'max:100'],
        ];
    }

    public function aplicarFiltros(): void
    {
        $this->validate();
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->reset('busqueda', 'estado', 'tipo', 'desde', 'hasta');
        $this->resetValidation();
        $this->resetPage();
    }

    public function abrirDetalle(string $solicitud): void
    {
        app(HistorialCorreoDocumental::class)->detalle(auth()->user(), $solicitud);
        $this->seleccion = $solicitud;
        $this->mostrarDetalle = true;
        $this->confirmacion = null;
    }

    public function cerrarDetalle(): void
    {
        $this->mostrarDetalle = false;
        $this->seleccion = null;
    }

    public function reenviar(): void
    {
        abort_unless($this->seleccion !== null, 404);
        $actual = app(HistorialCorreoDocumental::class)->detalle(auth()->user(), $this->seleccion)['actual'];
        abort_unless($actual['estado'] === 'FALLIDO', 422, 'Solo puede volver a solicitar un envío que haya fallado.');
        $limite = 'correo:reenvio:'.auth()->id();
        if (RateLimiter::tooManyAttempts($limite, 10)) {
            $this->addError('envio', 'Has realizado varias solicitudes. Espera un minuto y actualiza el historial antes de volver a intentarlo.');

            return;
        }
        RateLimiter::hit($limite, 60);
        try {
            $resultado = app(EnviosCorreoDocumental::class)->solicitar(auth()->user(), User::findOrFail($actual['usuario']), $actual['tipo']);
        } catch (\RuntimeException $e) {
            if ($e instanceof HttpExceptionInterface || $e instanceof ModelNotFoundException) {
                throw $e;
            }
            report($e);
            $this->addError('envio', 'No se pudo registrar el envío. Revisa la configuración del correo y vuelve a consultar el historial antes de intentarlo de nuevo.');

            return;
        }
        $this->seleccion = $resultado['solicitud'];
        $this->confirmacion = $resultado['duplicado'] ? 'Ya existe una solicitud para este documento. Se muestra su estado.' : 'Nueva solicitud registrada. El correo se preparará con los datos actuales.';
    }

    public function render()
    {
        $consulta = app(HistorialCorreoDocumental::class);
        $consulta->autorizar(auth()->user()); // También durante actualizaciones y acciones Livewire.
        // Los filtros inválidos muestran errores sin ejecutar consultas con fechas o estados arbitrarios.
        abort_if($this->usuario !== '' && ! auth()->user()->can('usuarios.ver'), 403);
        $validator = validator($this->only(['busqueda', 'estado', 'tipo', 'desde', 'hasta', 'usuario']), $this->rules());
        $envios = $validator->fails() ? null : $consulta->ultimos(auth()->user(), $validator->validated())->paginate(12);
        $detalle = $this->mostrarDetalle && $this->seleccion ? $consulta->detalle(auth()->user(), $this->seleccion) : null;

        return view('identidad::livewire.identidad.correos-panel', [
            'envios' => $envios, 'filas' => $envios?->getCollection()->map(fn ($a) => $consulta->presentar($a)) ?? collect(),
            'detalle' => $detalle, 'estados' => EnviosCorreoDocumental::ESTADOS,
            'nombreUsuario' => $this->usuario !== '' ? User::findOrFail($this->usuario)->name : null,
        ])->layout('layouts.sistema');
    }
}
