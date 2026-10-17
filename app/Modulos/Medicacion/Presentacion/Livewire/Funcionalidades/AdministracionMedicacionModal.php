<?php

namespace App\Modulos\Medicacion\Presentacion\Livewire\Funcionalidades;

use App\Models\Residente;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use App\Modulos\Medicacion\Aplicacion\Acciones\RegistrarAdministracionMedicacionService;
use App\Modulos\Medicacion\Aplicacion\Consultas\AgendaMedicacionService;
use App\Modulos\Medicacion\Aplicacion\Consultas\ConsultarAdministracionMedicacion;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class AdministracionMedicacionModal extends Component
{
    public bool $showModal = false;

    #[Locked]
    public ?string $cod_residente = null;

    #[Locked]
    public ?string $cod_prescripcion = null;

    #[Locked]
    public ?string $cod_horario_prescripcion = null;

    #[Locked]
    public string $fecha = '';

    #[Locked]
    public string $hora_programada = '';

    #[Locked]
    public array $orden = [];

    #[Locked]
    public array $contexto = [];

    #[Locked]
    public string $origen = '';

    #[Locked]
    public string $confirmacion = '';

    #[Locked]
    public ?string $siguienteHorario = null;

    #[Locked]
    public array $guardado = [];

    #[Locked]
    public string $avisoConsulta = '';

    public string $resultado = '';

    public string $hora_real = '';

    public string $dosis_administrada = '';

    public string $motivo_omision = '';

    public string $efecto_observado = '';

    public string $reaccion_adversa = '';

    public string $observacion = '';

    public string $pestana = 'PENDIENTES';

    public bool $mostrarPrescripcion = false;

    #[Locked]
    public bool $mostrarImagen = false;

    public bool $mostrarHistorial = false;

    public bool $mostrarEvolucion = false;

    public string $historiaPeriodo = '30';

    public string $historiaDesde = '';

    public string $historiaHasta = '';

    public string $historiaResultado = 'TODAS';

    public string $historiaVista = 'resumen';

    public int $historiaPagina = 1;

    public string $evolucionPeriodo = '30';

    public string $evolucionDesde = '';

    public string $evolucionHasta = '';

    public string $evolucionResultado = 'TODAS';

    public string $agrupacion = 'dia';

    #[Locked]
    public array $historial = [];

    #[Locked]
    public array $evolucion = [];

    #[Locked]
    public array $detalle = [];

    protected $listeners = ['abrirModalAdministracion'];

    public function abrirModalAdministracion(string $cod_residente, ?string $cod_prescripcion = null, ?string $hora_programada = null, ?string $cod_horario_prescripcion = null, string $origen = ''): void
    {
        $this->autorizarCaptura($cod_residente);
        $this->resetValidation();
        $this->resetExcept('cod_residente');
        $this->cod_residente = $cod_residente;
        $this->fecha = today()->toDateString();
        $this->origen = $origen === 'selector' ? 'selector' : '';
        $puedeVerAlergias = auth()->user()->can('alergias.ver');
        $residente = Residente::with(['ocupacionActiva.cama.habitacion'])->findOrFail($cod_residente);
        if ($puedeVerAlergias) {
            $residente->load('alergias');
        }
        $personal = auth()->user()->personal;
        $turno = app(TurnoEnfermeriaService::class)->obtenerTurnoActivo(auth()->user());
        $this->contexto = ['nombre' => $residente->nombre_completo, 'edad' => $residente->fecha_nacimiento ? Carbon::parse($residente->fecha_nacimiento)->age : null,
            'habitacion' => $residente->ocupacionActiva?->cama?->habitacion?->numero, 'cama' => $residente->ocupacionActiva?->cama?->numero,
            'profesional' => trim(($personal?->nombres ?? '').' '.($personal?->apellido_paterno ?? '').' '.($personal?->apellido_materno ?? '')),
            'categoria' => $personal?->profesion, 'turno' => $turno?->nombre,
            'horario' => $turno ? substr((string) $turno->hora_inicio, 0, 5).' – '.substr((string) $turno->hora_fin, 0, 5) : '',
            'momento' => now()->format('d/m/Y H:i'),
            'puede_ver_alergias' => $puedeVerAlergias,
            'alergias' => $puedeVerAlergias ? $residente->alergias->map(fn ($a) => ['sustancia' => $a->sustancia, 'detalle' => $a->observacion, 'reaccion' => $a->reaccion, 'gravedad' => $a->gravedad, 'estado' => $a->estado])->all() : []];
        $this->showModal = true;
        if ($cod_prescripcion || $cod_horario_prescripcion) {
            $coincidencias = $this->agenda()->filter(fn ($item) => (! $cod_prescripcion || $item['medicacion']->getKey() === $cod_prescripcion)
                && (! $cod_horario_prescripcion || $item['horario']->getKey() === $cod_horario_prescripcion)
                && (! $hora_programada || $item['hora'] === substr($hora_programada, 0, 5)) && $item['registro'] === null);
            if ($coincidencias->count() === 1) {
                $this->seleccionarDosis($coincidencias->first()['horario']->getKey());
            }
        }
        $this->dispatch('medicacion-captura-abierta');
    }

    private function autorizarCaptura(?string $residente = null): void
    {
        abort_unless(auth()->check(), 401);
        app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente($residente ?? $this->cod_residente ?? '', 'administraciones_medicacion.crear', auth()->user()->fresh());
        abort_unless(auth()->user()->can('prescripciones.ver') && auth()->user()->can('horarios_prescripcion.ver'), 403,
            'La captura requiere permiso para consultar la prescripción y sus horarios.');
    }

    private function agenda()
    {
        return $this->fecha === today()->toDateString() ? app(AgendaMedicacionService::class)->paraAdulto($this->cod_residente) : collect();
    }

    public function seleccionarDosis(string $codigo): void
    {
        $this->autorizarCaptura();
        abort_unless($this->showModal && $this->fecha === today()->toDateString(), 409);
        if ($this->cod_horario_prescripcion === $codigo) {
            return;
        }
        if ($this->modificado()) {
            $this->confirmacion = 'dosis';
            $this->siguienteHorario = $codigo;

            return;
        }
        $item = $this->agenda()->first(fn ($item) => $item['horario']->getKey() === $codigo && $item['registro'] === null);
        if (! $item) {
            throw ValidationException::withMessages(['cod_horario_prescripcion' => 'La dosis ya no está pendiente o no corresponde a este residente.']);
        }
        $this->cod_horario_prescripcion = $codigo;
        $this->cod_prescripcion = $item['medicacion']->getKey();
        $this->hora_programada = $item['hora'];
        $item['medicacion']->loadMissing('personal');
        $this->orden = app(ConsultarAdministracionMedicacion::class)->orden($item['medicacion']);
        $this->orden['dosis_programada'] = number_format((float) ($item['horario']->dosis_programada ?? $item['medicacion']->dosis), 3, '.', '');
        $this->mostrarImagen = false;
        $this->limpiarEditables();
        $this->guardado = [];
        $this->resetValidation();
        $this->actualizarConsultasAbiertas();
        $this->dispatch('medicacion-dosis-seleccionada');
    }

    private function modificado(): bool
    {
        return $this->resultado !== '' || $this->hora_real !== '' || $this->dosis_administrada !== '' || trim($this->motivo_omision.$this->efecto_observado.$this->reaccion_adversa.$this->observacion) !== '';
    }

    private function limpiarEditables(): void
    {
        $this->reset('resultado', 'hora_real', 'dosis_administrada', 'motivo_omision', 'efecto_observado', 'reaccion_adversa', 'observacion');
    }

    public function updatedResultado(string $resultado): void
    {
        if ($resultado === 'ADMINISTRADA') {
            $this->motivo_omision = '';
        }
        if ($resultado === 'OMITIDA') {
            $this->reset('hora_real', 'dosis_administrada', 'efecto_observado', 'reaccion_adversa');
        }
    }

    public function solicitarSalida(string $accion = 'cerrar'): void
    {
        abort_unless($this->showModal && in_array($accion, ['cerrar', 'volver', 'limpiar']), 409);
        if ($accion === 'limpiar' || $this->modificado()) {
            $this->confirmacion = $accion;

            return;
        }
        $this->cerrarModal($accion === 'volver');
    }

    public function cancelarConfirmacion(): void
    {
        $this->confirmacion = '';
        $this->siguienteHorario = null;
    }

    public function confirmarDescarte(): void
    {
        abort_unless($this->showModal && $this->confirmacion !== '', 409);
        $accion = $this->confirmacion;
        $siguiente = $this->siguienteHorario;
        $this->cancelarConfirmacion();
        $this->limpiarEditables();
        $this->resetValidation();
        if ($accion === 'dosis') {
            $this->seleccionarDosis($siguiente);
        } elseif ($accion !== 'limpiar') {
            $this->cerrarModal($accion === 'volver');
        }
        $this->dispatch('medicacion-captura-reiniciada');
    }

    public function cerrarModal(bool $volver = false): void
    {
        if ($this->modificado()) {
            $this->confirmacion = $volver ? 'volver' : 'cerrar';

            return;
        }
        $this->showModal = false;
        $this->mostrarHistorial = $this->mostrarEvolucion = $this->mostrarPrescripcion = false;
        $this->mostrarImagen = false;
        $this->dispatch('medicacion-formulario-cerrado', volver: $volver, residente: $this->cod_residente);
    }

    public function cambiarFecha(int $dias): void
    {
        abort_unless($this->showModal && in_array($dias, [-1, 1]), 409);
        if ($this->modificado()) {
            $this->addError('fecha', 'Conserva o descarta la captura antes de cambiar de fecha.');

            return;
        }
        $this->autorizarLectura('administraciones_medicacion.ver');
        $fecha = Carbon::parse($this->fecha)->addDays($dias);
        abort_if($fecha->isAfter(today()) || $fecha->isBefore(today()->subDays(366)), 422);
        $this->fecha = $fecha->toDateString();
        $this->pestana = $fecha->isSameDay(today()) ? 'PENDIENTES' : 'TODAS';
        $this->cod_horario_prescripcion = $this->cod_prescripcion = null;
        $this->orden = [];
        $this->hora_programada = '';
        $this->mostrarImagen = $this->mostrarPrescripcion = false;
        $this->actualizarConsultasAbiertas();
    }

    private function autorizarLectura(string $permiso): void
    {
        abort_unless($this->showModal && $this->cod_residente, 409);
        app(ConsultarAdministracionMedicacion::class)->autorizar(auth()->user(), $this->cod_residente, $permiso);
    }

    public function verPrescripcion(): void
    {
        $this->autorizarLectura('prescripciones.ver');
        $this->orden = [...app(ConsultarAdministracionMedicacion::class)->prescripcion(auth()->user(), $this->cod_residente, $this->cod_prescripcion), 'dosis_programada' => $this->orden['dosis_programada']];
        $this->mostrarPrescripcion = true;
    }

    public function abrirHistorial(): void
    {
        if ($this->fecha !== today()->toDateString()) {
            $this->historiaPeriodo = 'personalizado';
            $this->historiaDesde = $this->historiaHasta = $this->fecha;
        }
        $this->cargarHistorial();
        $this->mostrarHistorial = true;
        $this->dispatch('medicacion-historial-abierto');
    }

    public function abrirEvolucion(): void
    {
        $this->cargarEvolucion();
        $this->mostrarEvolucion = true;
        $this->dispatch('medicacion-evolucion-abierta');
    }

    public function cerrarPrescripcion(): void
    {
        $this->mostrarPrescripcion = false;
    }

    public function verImagen(): void
    {
        $this->autorizarLectura('prescripciones.ver');
        abort_unless($this->cod_prescripcion, 409);
        $orden = app(ConsultarAdministracionMedicacion::class)->prescripcion(auth()->user(), $this->cod_residente, $this->cod_prescripcion);
        $this->orden['imagen'] = $orden['imagen'];
        if (! $orden['imagen']) {
            $this->addError('imagen', 'No hay una fotografía disponible para este medicamento.');

            return;
        }
        $this->mostrarImagen = true;
    }

    public function cerrarImagen(): void
    {
        $this->mostrarImagen = false;
    }

    public function usarHoraActual(): void
    {
        $this->autorizarCaptura();
        abort_unless($this->showModal && $this->fecha === today()->toDateString()
            && $this->cod_horario_prescripcion && $this->resultado === 'ADMINISTRADA' && ! $this->guardado, 409);
        $this->hora_real = now()->format('H:i');
    }

    public function prepararSiguienteDosis(): void
    {
        $this->autorizarCaptura();
        abort_unless($this->showModal && $this->guardado && $this->fecha === today()->toDateString(), 409);
        $this->limpiarEditables();
        $this->reset('cod_horario_prescripcion', 'cod_prescripcion', 'hora_programada', 'orden', 'guardado', 'mostrarImagen');
        $this->pestana = 'PENDIENTES';
        $this->resetValidation();
        $this->actualizarConsultasAbiertas();
        $this->dispatch('medicacion-captura-reiniciada');
        $this->dispatch('medicacion-elegir-dosis');
    }

    public function cerrarDetalle(): void
    {
        $this->detalle = [];
    }

    public function cargarHistorial(int $pagina = 1): void
    {
        $this->autorizarLectura('administraciones_medicacion.ver');
        $this->historiaPagina = max(1, $pagina);
        $this->historial = app(ConsultarAdministracionMedicacion::class)->resultados(auth()->user(), $this->cod_residente, $this->filtros('historia'), $this->historiaPagina);
    }

    public function cargarEvolucion(): void
    {
        $this->autorizarLectura('administraciones_medicacion.ver');
        $this->evolucion = app(ConsultarAdministracionMedicacion::class)->resultados(auth()->user(), $this->cod_residente, $this->filtros('evolucion'), 1, true);
    }

    private function filtros(string $tipo): array
    {
        return ['periodo' => $this->{$tipo.'Periodo'}, 'desde' => $this->{$tipo.'Desde'}, 'hasta' => $this->{$tipo.'Hasta'},
            'resultado' => $this->{$tipo.'Resultado'}, 'agrupacion' => $this->agrupacion, 'prescripcion' => $this->cod_prescripcion];
    }

    public function verDetalle(string $codigo): void
    {
        $this->autorizarLectura('administraciones_medicacion.ver');
        $this->detalle = app(ConsultarAdministracionMedicacion::class)->detalle(auth()->user(), $this->cod_residente, $codigo);
        $this->mostrarHistorial = true;
        $this->dispatch('medicacion-historial-abierto');
    }

    public function verPeriodo(string $periodo, string $resultado = 'TODAS'): void
    {
        $this->autorizarLectura('administraciones_medicacion.ver');
        abort_unless(in_array($periodo, array_column($this->evolucion['series'] ?? [], 'fecha'), true)
            && in_array($resultado, ['TODAS', 'ADMINISTRADA', 'OMITIDA'], true), 422);
        $desde = Carbon::parse($periodo);
        $hasta = match ($this->evolucion['agrupacion'] ?? 'dia') {
            'semana' => $desde->copy()->endOfWeek(), 'mes' => $desde->copy()->endOfMonth(), default => $desde->copy()
        };
        $this->historiaPeriodo = 'personalizado';
        $this->historiaDesde = $desde->max(Carbon::parse($this->evolucion['desde']))->toDateString();
        $this->historiaHasta = $hasta->min(Carbon::parse($this->evolucion['hasta']))->toDateString();
        $this->historiaResultado = $resultado;
        $this->cargarHistorial();
        $this->mostrarHistorial = true;
        $this->dispatch('medicacion-historial-abierto');
    }

    public function guardar(): void
    {
        $this->autorizarCaptura();
        abort_unless($this->showModal && $this->fecha === today()->toDateString(), 409);
        $administrada = $this->resultado === 'ADMINISTRADA';
        $this->motivo_omision = trim($this->motivo_omision);
        $this->validate([
            'cod_horario_prescripcion' => 'required|string', 'resultado' => 'required|in:ADMINISTRADA,OMITIDA',
            'hora_real' => $administrada ? 'required|date_format:H:i' : 'nullable',
            'dosis_administrada' => $administrada ? 'required|numeric|decimal:0,3|between:0.001,9999999.999' : 'nullable',
            'motivo_omision' => $administrada ? 'nullable' : 'required|string|min:5|max:500',
            'efecto_observado' => 'nullable|string|max:2000', 'reaccion_adversa' => 'nullable|string|max:2000', 'observacion' => 'nullable|string|max:2000',
        ], ['resultado.required' => 'Selecciona lo que ocurrió con la dosis.', 'cod_horario_prescripcion.required' => 'Selecciona una dosis programada.']);
        try {
            $registro = app(RegistrarAdministracionMedicacionService::class)->registrarProgramada(auth()->user(), $this->cod_residente,
                $this->cod_prescripcion, $this->hora_programada, $administrada, $administrada ? null : $this->motivo_omision,
                $this->observacion, $administrada ? $this->efecto_observado : null,
                $administrada ? $this->dosis_administrada : null, $administrada ? $this->reaccion_adversa : null,
                $administrada ? $this->fecha.' '.$this->hora_real.':00' : null, $this->cod_horario_prescripcion);
        } catch (ValidationException $error) {
            $mensajes = [];
            foreach ($error->errors() as $campo => $errores) {
                $campo = match ($campo) {
                    'fecha_hora_administracion' => 'hora_real', 'cod_prescripcion', 'medicacion' => 'cod_horario_prescripcion', default => $campo
                };
                $mensajes[$campo] = [...($mensajes[$campo] ?? []), ...$errores];
            }
            throw ValidationException::withMessages($mensajes);
        } catch (\Throwable $error) {
            if ($error instanceof HttpExceptionInterface) {
                throw $error;
            }
            report($error);
            $this->addError('guardado', 'No se pudo confirmar el registro. Conservamos la captura; consulta el historial antes de reintentar.');

            return;
        }
        $this->guardado = ['codigo' => $registro->getKey(), 'resultado' => $registro->resultado, 'momento' => now()->format('d/m/Y H:i'),
            'dosis' => $registro->dosis_administrada, 'hora_real' => $registro->fecha_hora_administracion?->format('H:i'), 'motivo' => $registro->motivo_omision];
        $this->limpiarEditables();
        $this->dispatch('administracion-actualizada');
        $this->dispatch('medicacion-registrada');
        $this->dispatch('medicacion-guardada');
        $this->actualizarConsultasAbiertas(true);
    }

    private function actualizarConsultasAbiertas(bool $trasGuardar = false): void
    {
        $this->detalle = [];
        try {
            if ($this->mostrarHistorial) {
                $this->cargarHistorial();
            }
            if ($this->mostrarEvolucion) {
                $this->cargarEvolucion();
            }
        } catch (\Throwable $error) {
            if (! ($error instanceof ValidationException) && ! ($error instanceof HttpExceptionInterface)) {
                report($error);
            }
            $this->historial = $this->evolucion = $this->detalle = [];
            $this->mostrarHistorial = $this->mostrarEvolucion = false;
            $this->avisoConsulta = $trasGuardar
                ? 'El registro está guardado. No se pudo actualizar la consulta; vuelve a abrirla. No repitas la administración.'
                : 'No se pudo actualizar la consulta. La captura se conserva; vuelve a abrirla.';
        }
    }

    public function render()
    {
        $dosis = [];
        $contadores = ['PENDIENTES' => 0, 'ADMINISTRADA' => 0, 'OMITIDA' => 0, 'TODAS' => 0];
        if ($this->showModal) {
            $this->autorizarCaptura();
            if ($this->mostrarHistorial || $this->mostrarEvolucion || $this->historial || $this->evolucion || $this->detalle) {
                $this->autorizarLectura('administraciones_medicacion.ver');
            }
            if ($this->contexto['alergias'] ?? []) {
                abort_unless(auth()->user()->can('alergias.ver'), 403);
            }
            foreach ($this->agenda() as $item) {
                $pendiente = $item['registro'] === null;
                if (! $pendiente && ! auth()->user()->can('administraciones_medicacion.ver')) {
                    continue;
                }
                $contadores['TODAS']++;
                $contadores[$pendiente ? 'PENDIENTES' : $item['estado']]++;
                if ($this->pestana !== 'TODAS' && $this->pestana !== ($pendiente ? 'PENDIENTES' : $item['estado'])) {
                    continue;
                }
                $dosis[] = ['codigo' => $item['horario']->getKey(), 'hora' => $item['hora'],
                    'medicamento' => $item['medicacion']->medicamento?->nombre_comercial ?: $item['medicacion']->medicamento?->nombre_generico,
                    'presentacion' => $item['medicacion']->medicamento?->concentracion,
                    'dosis' => $item['horario']->dosis_programada ?? $item['medicacion']->dosis,
                    'unidad' => $item['medicacion']->unidad_dosis, 'via' => $item['medicacion']->via_administracion,
                    'estado' => $item['estado'], 'registro' => $item['registro']?->getKey()];
            }
            if ($this->fecha !== today()->toDateString()) {
                $consulta = app(ConsultarAdministracionMedicacion::class)->resultados(auth()->user(), $this->cod_residente,
                    ['periodo' => 'personalizado', 'desde' => $this->fecha, 'hasta' => $this->fecha, 'resultado' => 'TODAS', 'agrupacion' => 'dia']);
                $contadores = ['PENDIENTES' => 0, 'TODAS' => $consulta['total'], 'ADMINISTRADA' => $consulta['contadores']['ADMINISTRADA'] ?? 0, 'OMITIDA' => $consulta['contadores']['OMITIDA'] ?? 0];
                foreach ($consulta['registros'] as $registro) {
                    if (! in_array($this->pestana, ['TODAS', $registro['resultado']])) {
                        continue;
                    }
                    $dosis[] = ['codigo' => $registro['codigo'], 'hora' => $registro['hora'], 'medicamento' => $registro['medicamento'],
                        'presentacion' => 'Resultado guardado · consulta', 'dosis' => $registro['dosis'] ?? '—', 'unidad' => $registro['unidad'],
                        'via' => $registro['via'], 'estado' => $registro['resultado'], 'registro' => $registro['codigo']];
                }
            }
        }

        return view('livewire.medicacion.administracion-medicacion-modal', compact('dosis', 'contadores'));
    }
}
