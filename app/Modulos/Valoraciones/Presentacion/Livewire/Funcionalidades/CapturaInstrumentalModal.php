<?php

declare(strict_types=1);

namespace App\Modulos\Valoraciones\Presentacion\Livewire\Funcionalidades;

use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Models\Instrumento;
use App\Models\Residente;
use App\Modulos\Valoraciones\Aplicacion\Acciones\AplicarInstrumento;
use App\Modulos\Valoraciones\Aplicacion\Consultas\PrepararCapturaInstrumental;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

/** Interacción compartida por residente y área; AplicarInstrumento protege la operación institucional. */
abstract class CapturaInstrumentalModal extends Component
{
    #[Locked]
    public bool $mostrar = false;

    #[Locked]
    public bool $confirmando = false;

    #[Locked]
    public bool $confirmarDescarte = false;

    #[Locked]
    public ?string $residenteFijo = null;

    #[Locked]
    public ?string $residenteSeleccionado = null;

    #[Locked]
    public ?string $instrumentoSeleccionado = null;

    #[Locked]
    public ?string $codigoReservado = null;

    #[Locked]
    public ?string $aplicacionGuardada = null;

    public ?string $cod_residente = null;

    public ?string $cod_instrumento = null;

    public string $fecha_eval = '';

    public string $hora_eval = '';

    public string $busquedaResidente = '';

    public string $observaciones = '';

    public array $valores = [];

    #[Locked]
    public array $preguntas = [];

    #[Locked]
    public array $contexto = [];

    #[Locked]
    public ?string $huellaCatalogo = null;

    #[Locked]
    public ?string $huellaConfirmacion = null;

    #[Locked]
    public array $fechaInicial = [];

    public function mount(?string $cod_residente = null): void
    {
        $this->residenteFijo = $cod_residente;
    }

    protected function preparar(?string $codigoResidente): void
    {
        if ($this->mostrar && $this->tieneCambios()) {
            $this->addError('registro', 'Conserva o descarta la captura actual antes de abrir otra.');

            return;
        }
        $consulta = app(PrepararCapturaInstrumental::class);
        $consulta->usuario(auth()->user());
        abort_if($this->residenteFijo !== null && $codigoResidente !== $this->residenteFijo, 403);
        $contexto = $codigoResidente !== null ? $consulta->contexto(auth()->user(), $codigoResidente) : [];
        $this->reset(['cod_instrumento', 'instrumentoSeleccionado', 'codigoReservado', 'aplicacionGuardada', 'valores', 'preguntas',
            'observaciones', 'confirmando', 'confirmarDescarte', 'busquedaResidente', 'huellaCatalogo', 'huellaConfirmacion']);
        $this->resetValidation();
        $this->cod_residente = $this->residenteSeleccionado = $codigoResidente;
        $this->contexto = $contexto;
        $this->fecha_eval = now()->format('Y-m-d');
        $this->hora_eval = now()->format('H:i:s');
        $this->fechaInicial = [$this->fecha_eval, $this->hora_eval];
        $this->mostrar = true;
    }

    public function updatedCodResidente(?string $value): void
    {
        if ($this->hayRespuestas()) {
            $this->cod_residente = $this->residenteSeleccionado;
            $this->addError('cod_residente', 'Descarta o registra las respuestas antes de cambiar de residente.');

            return;
        }
        abort_if($this->residenteFijo !== null && $value !== $this->residenteFijo, 403);
        app(PrepararCapturaInstrumental::class)->usuario(auth()->user());
        $this->contexto = $value ? app(PrepararCapturaInstrumental::class)->contexto(auth()->user(), $value) : [];
        $this->residenteSeleccionado = $value;
        $this->reset(['cod_instrumento', 'instrumentoSeleccionado', 'preguntas', 'confirmando']);
    }

    public function updatedCodInstrumento(?string $value): void
    {
        if ($this->hayRespuestas()) {
            $this->cod_instrumento = $this->instrumentoSeleccionado;
            $this->addError('cod_instrumento', 'Descarta o registra las respuestas antes de cambiar de instrumento.');

            return;
        }
        $this->comprobarResidente();
        $catalogo = $value ? app(PrepararCapturaInstrumental::class)->catalogo(auth()->user(), $this->cod_residente, $value) : [];
        $this->preguntas = $catalogo['preguntas'] ?? [];
        $this->huellaCatalogo = $catalogo['huella'] ?? null;
        $this->instrumentoSeleccionado = $value;
        $this->confirmando = false;
        $this->resetValidation();
    }

    public function updated(string $property): void
    {
        if (str_starts_with($property, 'valores.') || in_array($property, ['valores', 'cod_residente', 'cod_instrumento', 'observaciones', 'fecha_eval', 'hora_eval'], true)) {
            $this->confirmando = false;
            $this->huellaConfirmacion = null;
        }
    }

    public function revisar(): void
    {
        $entrada = $this->entrada();
        // Reserva interna: la PK proporciona una identidad única para los reenvíos del formulario confirmado.
        $this->codigoReservado ??= app(CodigosEntidad::class)->siguiente('aplicaciones_instrumento');
        $this->huellaConfirmacion = $this->huellaEntrada($entrada);
        $this->confirmando = true;
    }

    public function volver(): void
    {
        $this->confirmando = $this->confirmarDescarte = false;
    }

    public function guardar(): void
    {
        if ($this->aplicacionGuardada !== null) {
            return;
        }
        $entrada = $this->entrada();
        if (! $this->confirmando || $this->codigoReservado === null || $this->huellaConfirmacion === null
            || ! hash_equals($this->huellaConfirmacion, $this->huellaEntrada($entrada))) {
            $this->addError('registro', 'Revisa las respuestas y confirma la captura antes de registrarla.');

            return;
        }
        try {
            $aplicacion = app(AplicarInstrumento::class)->aplicar(auth()->user(), $entrada,
                Instrumento::query()->findOrFail($this->cod_instrumento), Residente::query()->findOrFail($this->cod_residente), $this->codigoReservado, $this->huellaCatalogo);
        } catch (ValidationException $e) {
            foreach ($e->errors() as $campo => $mensajes) {
                $destino = preg_match('/^respuestas\.(\d+)\./', $campo, $m)
                    ? 'valores.'.$entrada['respuestas'][(int) $m[1]]['cod_pregunta'] : 'registro';
                $this->addError($destino, implode(' ', $mensajes));
            }

            return;
        } catch (QueryException $e) {
            report($e);
            $this->addError('registro', 'No se pudo registrar la captura. Conservamos tus respuestas para que puedas volver a intentarlo.');

            return;
        }
        $this->aplicacionGuardada = $aplicacion->getKey();
        $this->mostrar = $this->confirmando = false;
        $this->valores = [];
        $this->observaciones = '';
        $this->dispatch('evaluacion-geriatrica-guardada', codAplicacion: $aplicacion->getKey());
        $this->dispatch('swal:alert', ['type' => 'success', 'title' => 'Captura registrada',
            'message' => 'Se guardaron las respuestas originales con su fecha y profesional. Consulta el perfil para ver los cálculos disponibles.']);
    }

    private function comprobarResidente(): void
    {
        abort_unless($this->cod_residente !== null && ($this->residenteFijo === null || $this->cod_residente === $this->residenteFijo), 403);
        $this->contexto = app(PrepararCapturaInstrumental::class)->contexto(auth()->user(), $this->cod_residente);
    }

    private function huellaEntrada(array $entrada): string
    {
        return hash('sha256', json_encode([$this->cod_residente, $this->cod_instrumento, $this->huellaCatalogo, $entrada], JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION));
    }

    private function entrada(): array
    {
        $this->comprobarResidente();
        $this->validate(['cod_instrumento' => 'required|string', 'fecha_eval' => 'required|date_format:Y-m-d|before_or_equal:today',
            'hora_eval' => 'required|date_format:H:i:s,H:i', 'observaciones' => 'nullable|string|max:4000', 'valores' => 'array']);
        $fechaOriginal = $this->fecha_eval.' '.$this->hora_eval.(strlen($this->hora_eval) === 5 ? ':00' : '');
        Validator::make(['hora_eval' => $fechaOriginal], ['hora_eval' => 'before_or_equal:now'],
            ['hora_eval.before_or_equal' => 'La fecha y hora originales no pueden ser futuras.'])->validate();
        $catalogo = app(PrepararCapturaInstrumental::class)->catalogo(auth()->user(), $this->cod_residente, $this->cod_instrumento);
        if ($this->huellaCatalogo === null || ! hash_equals($this->huellaCatalogo, $catalogo['huella'])) {
            throw ValidationException::withMessages(['cod_instrumento' => 'El catálogo cambió. Revisa el instrumento actualizado antes de registrar la captura.']);
        }
        $preguntas = $catalogo['preguntas'];
        $porCodigo = array_column($preguntas, null, 'codigo');
        $respuestas = [];
        foreach ($this->valores as $codigo => $valor) {
            if (! isset($porCodigo[$codigo]) || $porCodigo[$codigo]['campo'] === null) {
                throw ValidationException::withMessages(['valores.'.$codigo => 'La pregunta no pertenece al instrumento activo.']);
            }
            if ($valor !== null && $valor !== '') {
                $campo = $porCodigo[$codigo]['campo'];
                Validator::make(['valores' => [$codigo => $valor]], ['valores.'.$codigo => match ($campo) {
                    'valor_numero' => 'required|numeric|between:-99999999.9999,99999999.9999|decimal:0,4',
                    'valor_logico' => 'required|boolean', 'valor_texto' => 'required|string', 'cod_opcion' => 'required|string',
                }])->validate();
                if ($campo === 'cod_opcion' && ! in_array($valor, array_column($porCodigo[$codigo]['opciones'], 'codigo'), true)) {
                    throw ValidationException::withMessages(['valores.'.$codigo => 'Selecciona una opción activa de esta pregunta.']);
                }
                $respuestas[] = ['cod_pregunta' => $codigo, $campo => $valor];
            }
        }
        if ($respuestas === []) {
            throw ValidationException::withMessages(['registro' => 'Registra al menos una respuesta original. Las preguntas sin dato se mantienen sin respuesta.']);
        }

        return ['fecha_hora' => $fechaOriginal,
            'observacion' => $this->observaciones === '' ? null : $this->observaciones, 'respuestas' => $respuestas];
    }

    private function tieneCambios(): bool
    {
        return $this->hayRespuestas() || $this->fechaInicial !== [$this->fecha_eval, $this->hora_eval];
    }

    private function hayRespuestas(): bool
    {
        return $this->observaciones !== '' || count(array_filter($this->valores, fn ($v) => $v !== null && $v !== '')) > 0;
    }

    public function cerrar(): void
    {
        if ($this->tieneCambios()) {
            $this->confirmarDescarte = true;

            return;
        }
        $this->mostrar = false;
    }

    public function descartar(): void
    {
        $this->mostrar = $this->confirmando = $this->confirmarDescarte = false;
        $this->valores = [];
        $this->observaciones = '';
        $this->resetValidation();
    }

    public function render()
    {
        $consulta = app(PrepararCapturaInstrumental::class);
        $instrumentos = $residentes = collect();
        if ($this->mostrar) {
            $instrumentos = $consulta->instrumentos(auth()->user());
            if ($this->cod_residente !== null) {
                $this->comprobarResidente();
            }
            if ($this->residenteFijo === null) {
                $residentes = $consulta->residentes(auth()->user(), mb_substr($this->busquedaResidente, 0, 100));
            }
        }

        return view('livewire.valoraciones.evaluacion-geriatrica-modal', compact('instrumentos', 'residentes'));
    }
}
