<?php

namespace App\Livewire\Clinica;

use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AdultoMayor;
use App\Models\SignosVitalesAdulto;

class SaludSignosPanel extends Component
{
    use WithPagination;

    public ?AdultoMayor $adulto = null;
    public string $adultoSeleccionado = '';
    public string $buscarPaciente = '';

    public bool $modalFormulario = false;
    public bool $modalPresion = false;
    public bool $modalCardiaca = false;
    public bool $modalTemperatura = false;
    public bool $modalPeso = false;
    public bool $modalObservacion = false;

    public bool $modalDetalle = false;
    public bool $modalAnular = false;

    public ?string $signoId = null;
    public string $fecha = '';
    public string $hora = '';
    public ?int $presion_sistolica = null;
    public ?int $presion_diastolica = null;
    public ?int $frecuencia_cardiaca = null;
    public ?int $frecuencia_respiratoria = null;
    public mixed $temperatura = null;
    public ?int $saturacion = null;
    public mixed $glucosa = null;
    public mixed $peso = null;
    public mixed $talla = null;
    public mixed $imc = null;
    public ?int $dolor = null;
    public string $observacion = '';

    public ?string $signoIdAnular = null;
    public string $motivoAnulacion = '';
    public ?string $signoDetalleId = null;

    public string $fechaDesde = '';
    public string $fechaHasta = '';
    public string $parametro = 'todos';
    public string $estadoClinico = 'todos';
    public string $responsable = 'todos';
    public string $grafica = 'presion';
    public int $perPage = 10;

    public bool $guardarConfirmado = false;

    protected $paginationTheme = 'tailwind';

    public function mount(?AdultoMayor $adulto = null): void
    {
        if ($adulto && $adulto->exists) {
            $this->cargarAdulto($adulto->cod_am);
        }
    }

    public function updated($property): void
    {
        if (in_array($property, ['peso', 'talla'], true)) {
            $this->calcularImcVisual();
        }

        if (in_array($property, ['fechaDesde', 'fechaHasta', 'parametro', 'estadoClinico', 'responsable', 'perPage'], true)) {
            $this->resetPage();
        }

        // Removed automatic update on adultoSeleccionado. It will be handled by buscarPacienteAction().
    }

    public function render()
    {
        $baseSignos = collect();
        if ($this->adulto) {
            $baseSignos = $this->adulto->signosVitales()
                ->with('registradoPor', 'anuladoPor')
                ->orderByDesc('fecha')
                ->orderByDesc('hora')
                ->get();
        }

        $signosFiltrados = $this->aplicarFiltros($baseSignos);
        $signosVigentesFiltrados = $signosFiltrados
            ->where('estado', 'VIGENTE')
            ->values();

        $ultimoVigente = $baseSignos->firstWhere('estado', 'VIGENTE');
        $totalVigentes = $baseSignos->where('estado', 'VIGENTE')->count();
        $totalAnulados = $baseSignos->where('estado', 'ANULADO')->count();
        $signoDetalle = $this->signoDetalleId
            ? $baseSignos->firstWhere('cod_signo', $this->signoDetalleId)
            : null;

        $analisisPorRegistro = $signosFiltrados
            ->mapWithKeys(fn ($signo) => [$signo->cod_signo => $this->analizarRegistro($signo)])
            ->all();

        $chartData = $this->construirChartData($signosVigentesFiltrados);

        return view('livewire.clinica.salud-signos-panel', [
            'adulto' => $this->adulto,
            'historial' => $this->adulto ? $this->paginarColeccion($signosFiltrados) : new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage),
            'signosFiltrados' => $signosFiltrados,
            'ultimoVigente' => $ultimoVigente,
            'totalVigentes' => $totalVigentes,
            'totalAnulados' => $totalAnulados,
            'signoDetalle' => $signoDetalle,
            'alertasFormulario' => $this->modalFormulario ? $this->detectarAlertas() : [],
            'metricas' => $this->adulto ? $this->construirMetricas($signosVigentesFiltrados) : [],
            'resumenPeriodo' => $this->construirResumenPeriodo($signosVigentesFiltrados, $baseSignos),
            'interpretacion' => $this->construirInterpretacion($signosVigentesFiltrados),
            'chartData' => $chartData,
            'chartKey' => md5(json_encode($chartData)),
            'responsables' => $this->obtenerResponsables($baseSignos),
            'analisisPorRegistro' => $analisisPorRegistro,
            'pacientesSelector' => $this->obtenerPacientesSelector(),
            'pacienteResumen' => $this->adulto ? $this->construirPacienteResumen($baseSignos, $ultimoVigente) : null,
            'reporteClinico' => $this->adulto ? $this->construirReporteClinico($signosVigentesFiltrados, $ultimoVigente) : null,
        ]); // Removed ->layout('layouts.sistema') as it's a subcomponent now
    }

    public function buscarPacienteAction(): void
    {
        if (trim($this->adultoSeleccionado) === '') {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Seleccione un paciente',
                'text' => 'Seleccione un adulto mayor antes de buscar.',
            ]);
            return;
        }

        $adultoExistente = AdultoMayor::find($this->adultoSeleccionado);
        if (!$adultoExistente) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No encontrado',
                'text' => 'El adulto mayor seleccionado no existe.',
            ]);
            return;
        }

        $this->cargarAdulto($this->adultoSeleccionado);
        
        if ($this->adulto->signosVitales()->count() === 0) {
            $this->dispatch('swal', [
                'icon' => 'info',
                'title' => 'Sin registros',
                'text' => 'No existen registros de signos vitales para este adulto mayor.',
            ]);
        }
    }

    public function actualizarPanel(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->fechaDesde = '';
        $this->fechaHasta = '';
        $this->parametro = 'todos';
        $this->estadoClinico = 'todos';
        $this->responsable = 'todos';
        $this->perPage = 10;
        $this->resetPage();
    }

    private function cargarAdulto(string $codAm): void
    {
        $this->adulto = AdultoMayor::with('estado')->findOrFail($codAm);
        $this->adultoSeleccionado = $this->adulto->cod_am;
        $this->signoDetalleId = null;
        $this->limpiarFiltros();
    }

    private function obtenerPacientesSelector()
    {
        $query = AdultoMayor::query()
            ->with('estado')
            ->withCount('signosVitales')
            ->orderBy('nombres')
            ->orderBy('ap_paterno');

        if (trim($this->buscarPaciente) !== '') {
            $busqueda = '%' . trim($this->buscarPaciente) . '%';
            $query->where(function ($subQuery) use ($busqueda) {
                $subQuery->where('cod_am', 'like', $busqueda)
                    ->orWhere('nombres', 'like', $busqueda)
                    ->orWhere('ap_paterno', 'like', $busqueda)
                    ->orWhere('ap_materno', 'like', $busqueda)
                    ->orWhere('ci', 'like', $busqueda);
            });
        }

        return $query->limit(12)->get();
    }

    private function construirPacienteResumen(Collection $baseSignos, ?SignosVitalesAdulto $ultimoVigente): array
    {
        $nombre = trim("{$this->adulto->nombres} {$this->adulto->ap_paterno} {$this->adulto->ap_materno}");

        return [
            'nombre' => $nombre !== '' ? $nombre : 'Adulto mayor',
            'codigo' => $this->adulto->cod_am,
            'estado' => optional($this->adulto->estado)->estado ?? 'Sin estado',
            'edad' => $this->adulto->fecha_nac ? $this->adulto->fecha_nac->age . ' años' : 'Edad no registrada',
            'ci' => $this->adulto->ci ?: 'S/D',
            'ultimo_control' => $ultimoVigente ? $this->formatearFechaHora($ultimoVigente) : 'Sin controles',
            'registros' => $baseSignos->count(),
            'vigentes' => $baseSignos->where('estado', 'VIGENTE')->count(),
        ];
    }

    private function construirReporteClinico(Collection $signos, ?SignosVitalesAdulto $ultimoVigente): array
    {
        $analisisUltimo = $ultimoVigente
            ? $this->analizarRegistro($ultimoVigente)
            : [
                'key' => 'sin_datos',
                'label' => 'Sin datos',
                'message' => 'No existen mediciones registradas para este adulto mayor.',
                'motivos' => [],
            ];

        $metricas = $this->construirMetricas($signos);
        $principal = collect($metricas)->firstWhere('key', $this->grafica) ?? $metricas[0];

        $observaciones = $signos
            ->filter(fn ($signo) => filled($signo->observacion))
            ->take(3)
            ->map(fn ($signo) => [
                'fecha' => $this->formatearFechaHora($signo),
                'texto' => $signo->observacion,
            ])
            ->values()
            ->all();

        return [
            'estado' => $analisisUltimo,
            'metrica' => $principal,
            'observaciones' => $observaciones,
            'total_periodo' => $signos->count(),
            'mensaje' => $analisisUltimo['message'],
        ];
    }

    public function abrirFormularioNuevo(): void
    {
        abort_if(!$this->adulto, 403, 'Debe seleccionar un paciente antes de registrar signos vitales.');
        abort_if(!auth()->user()->can('salud.signos.crear'), 403);
        $this->limpiarFormulario();
        $this->modalFormulario = true;
    }

    public function abrirFormularioEditar(string $id): void
    {
        abort_if(!auth()->user()->can('salud.signos.editar'), 403);

        $signo = SignosVitalesAdulto::findOrFail($id);
        abort_if(!$this->adulto || $signo->cod_am !== $this->adulto->cod_am, 403);

        if ($signo->estado === 'ANULADO') {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No permitido',
                'text' => 'No se puede editar un registro anulado.',
            ]);
            return;
        }

        $this->signoId = $signo->cod_signo;
        $this->fecha = $signo->fecha->format('Y-m-d');
        $this->hora = $signo->hora_formateada;
        $this->presion_sistolica = $signo->presion_sistolica;
        $this->presion_diastolica = $signo->presion_diastolica;
        $this->frecuencia_cardiaca = $signo->frecuencia_cardiaca;
        $this->frecuencia_respiratoria = $signo->frecuencia_respiratoria;
        $this->temperatura = $signo->temperatura;
        $this->saturacion = $signo->saturacion;
        $this->glucosa = $signo->glucosa;
        $this->peso = $signo->peso;
        $this->talla = $signo->talla;
        $this->imc = $signo->imc;
        $this->dolor = $signo->dolor;
        $this->observacion = $signo->observacion ?? '';
        $this->guardarConfirmado = false;
        $this->modalFormulario = true;
    }

    public function abrirDetalle(string $id): void
    {
        $this->signoDetalleId = $id;
        $this->modalDetalle = true;
    }

    public function guardar(): void
    {
        abort_if(!$this->adulto, 403, 'Paciente no seleccionado.');
        $esEdicion = $this->signoId !== null;
        abort_if(!auth()->user()->can($esEdicion ? 'salud.signos.editar' : 'salud.signos.crear'), 403);

        $alertas = $this->detectarAlertas();

        $this->validate($this->rules(), $this->messages());

        $this->validarMomentoMedicion();
        $this->validarCoherenciaClinica();

        if ($this->getErrorBag()->count() > 0) {
            return;
        }

        $valoresIngresados = array_filter([
            $this->presion_sistolica,
            $this->presion_diastolica,
            $this->frecuencia_cardiaca,
            $this->frecuencia_respiratoria,
            $this->temperatura,
            $this->saturacion,
            $this->glucosa,
            $this->peso,
            $this->talla,
            $this->dolor,
            trim($this->observacion),
        ], fn ($valor) => $valor !== null && $valor !== '');

        if (empty($valoresIngresados)) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Datos insuficientes',
                'text' => 'Debe registrar al menos un signo vital o una observación para continuar.',
            ]);
            return;
        }

        if (!empty($alertas) && !$this->guardarConfirmado) {
            $this->dispatch('signos-confirmar-alertas', [
                'titulo' => 'Valores fuera del rango referencial',
                'texto' => 'Se detectaron valores que requieren revisión del personal de salud. Verifique la información antes de guardar.',
            ]);
            return;
        }

        $this->guardarConfirmado = false;
        $this->calcularImcVisual();

        $presionArterial = ($this->presion_sistolica && $this->presion_diastolica)
            ? "{$this->presion_sistolica}/{$this->presion_diastolica}"
            : null;

        $datos = [
            'cod_am' => $this->adulto->cod_am,
            'fecha' => $this->fecha,
            'hora' => $this->hora,
            'presion_arterial' => $presionArterial,
            'presion_sistolica' => $this->presion_sistolica ?: null,
            'presion_diastolica' => $this->presion_diastolica ?: null,
            'frecuencia_cardiaca' => $this->frecuencia_cardiaca ?: null,
            'frecuencia_respiratoria' => $this->frecuencia_respiratoria ?: null,
            'temperatura' => $this->temperatura ?: null,
            'saturacion' => $this->saturacion !== null && $this->saturacion !== '' ? $this->saturacion : null,
            'glucosa' => $this->glucosa ?: null,
            'peso' => $this->peso ?: null,
            'talla' => $this->talla ?: null,
            'imc' => $this->imc ?: null,
            'dolor' => $this->dolor !== null && $this->dolor !== '' ? $this->dolor : null,
            'observacion' => $this->observacion ?: null,
            'estado' => 'VIGENTE',
            'registrado_por' => auth()->user()->cod_usu,
        ];

        if ($esEdicion) {
            $signo = SignosVitalesAdulto::findOrFail($this->signoId);
            abort_if($signo->cod_am !== $this->adulto->cod_am, 403);
            abort_if($signo->estado === 'ANULADO', 422);

            $signo->descripcionLog = "Actualizó signos vitales del adulto mayor {$this->adulto->cod_am}.";
            $signo->update($datos);
        } else {
            $signo = new SignosVitalesAdulto($datos);
            $signo->descripcionLog = !empty($alertas)
                ? "Registró signos vitales con alerta orientativa del adulto mayor {$this->adulto->cod_am}."
                : "Registró signos vitales del adulto mayor {$this->adulto->cod_am}.";
            $signo->save();
        }

        $this->cerrarFormulario();
        $this->resetPage();

        $this->dispatch('swal', [
            'icon' => !empty($alertas) ? 'warning' : 'success',
            'title' => !empty($alertas) ? 'Guardado con alerta referencial' : 'Registro guardado',
            'text' => !empty($alertas)
                ? 'Los signos vitales fueron guardados. La alerta es orientativa y no constituye diagnóstico médico.'
                : 'Los signos vitales se guardaron correctamente.',
        ]);
    }

    public function confirmarGuardarConAlertas(): void
    {
        abort_if(!$this->adulto, 403, 'Paciente no seleccionado.');
        $this->guardarConfirmado = true;
        $this->guardar();
    }

    public function abrirAnular(string $id): void
    {
        abort_if(!auth()->user()->can('salud.signos.anular'), 403);
        $this->dispatch('signos-confirmar-anulacion', ['id' => $id]);
    }

    public function anularConMotivo(string $id, string $motivo): void
    {
        abort_if(!$this->adulto, 403, 'Paciente no seleccionado.');
        abort_if(!auth()->user()->can('salud.signos.anular'), 403);

        $motivo = trim($motivo);

        if (mb_strlen($motivo) < 10) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Motivo requerido',
                'text' => 'El motivo de anulación debe tener al menos 10 caracteres.',
            ]);
            return;
        }

        $signo = SignosVitalesAdulto::findOrFail($id);
        abort_if($signo->cod_am !== $this->adulto->cod_am, 403);

        $signo->descripcionLog = "Anuló signos vitales del adulto mayor {$this->adulto->cod_am}.";
        $signo->update([
            'estado' => 'ANULADO',
            'motivo_anulacion' => $motivo,
            'anulado_por' => auth()->user()->cod_usu,
            'fecha_anulacion' => now(),
        ]);

        $this->signoDetalleId = null;

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Registro anulado',
            'text' => 'El control de signos vitales fue anulado correctamente. No se eliminaron datos clínicos.',
        ]);
    }

    public function confirmarAnular(): void
    {
        abort_if(!$this->adulto, 403, 'Paciente no seleccionado.');
        abort_if(!auth()->user()->can('salud.signos.anular'), 403);

        $this->validateOnly('motivoAnulacion', [
            'motivoAnulacion' => 'required|string|min:10|max:500',
        ], [
            'motivoAnulacion.required' => 'El motivo de anulación es obligatorio.',
            'motivoAnulacion.min' => 'El motivo debe tener al menos 10 caracteres.',
        ]);

        $signo = SignosVitalesAdulto::findOrFail($this->signoIdAnular);
        abort_if($signo->cod_am !== $this->adulto->cod_am, 403);

        $signo->descripcionLog = "Anuló signos vitales del adulto mayor {$this->adulto->cod_am}.";
        $signo->update([
            'estado' => 'ANULADO',
            'motivo_anulacion' => $this->motivoAnulacion,
            'anulado_por' => auth()->user()->cod_usu,
            'fecha_anulacion' => now(),
        ]);

        $this->modalAnular = false;
        $this->signoIdAnular = null;
        $this->motivoAnulacion = '';

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Registro anulado',
            'text' => 'El control de signos vitales fue anulado correctamente. No se eliminaron datos clínicos.',
        ]);
    }

    public function restaurarRegistro(string $id): void
    {
        abort_if(!$this->adulto, 403, 'Paciente no seleccionado.');
        abort_if(!auth()->user()->can('salud.signos.anular'), 403);

        $signo = SignosVitalesAdulto::findOrFail($id);
        abort_if($signo->cod_am !== $this->adulto->cod_am, 403);

        $signo->descripcionLog = "Restauró signos vitales del adulto mayor {$this->adulto->cod_am}.";
        $signo->update([
            'estado' => 'VIGENTE',
            'motivo_anulacion' => null,
            'anulado_por' => null,
            'fecha_anulacion' => null,
        ]);

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Registro restaurado',
            'text' => 'El control de signos vitales fue restaurado correctamente.',
        ]);
    }

    public function abrirFormularioPresion(): void
    {
        abort_if(!$this->adulto, 403, 'Debe seleccionar un paciente antes de registrar signos vitales.');
        abort_if(!auth()->user()->can('salud.signos.crear'), 403);
        $this->limpiarFormulario();
        $this->modalPresion = true;
    }

    public function abrirFormularioCardiaca(): void
    {
        abort_if(!$this->adulto, 403, 'Debe seleccionar un paciente antes de registrar signos vitales.');
        abort_if(!auth()->user()->can('salud.signos.crear'), 403);
        $this->limpiarFormulario();
        $this->modalCardiaca = true;
    }

    public function abrirFormularioTemperatura(): void
    {
        abort_if(!$this->adulto, 403, 'Debe seleccionar un paciente antes de registrar signos vitales.');
        abort_if(!auth()->user()->can('salud.signos.crear'), 403);
        $this->limpiarFormulario();
        $this->modalTemperatura = true;
    }

    public function abrirFormularioPeso(): void
    {
        abort_if(!$this->adulto, 403, 'Debe seleccionar un paciente antes de registrar signos vitales.');
        abort_if(!auth()->user()->can('salud.signos.crear'), 403);
        $this->limpiarFormulario();
        $this->modalPeso = true;
    }

    public function abrirFormularioObservacion(): void
    {
        abort_if(!$this->adulto, 403, 'Debe seleccionar un paciente antes de registrar signos vitales.');
        abort_if(!auth()->user()->can('salud.signos.crear'), 403);
        $this->limpiarFormulario();
        $this->modalObservacion = true;
    }

    public function cerrarModal(): void
    {
        $this->cerrarFormulario();
        $this->cerrarDetalle();
        $this->cerrarAnular();
    }

    public function cerrarFormulario(): void
    {
        $this->modalFormulario = false;
        $this->modalPresion = false;
        $this->modalCardiaca = false;
        $this->modalTemperatura = false;
        $this->modalPeso = false;
        $this->modalObservacion = false;
        $this->guardarConfirmado = false;
        $this->limpiarFormulario();
    }

    public function cerrarDetalle(): void
    {
        $this->modalDetalle = false;
        $this->signoDetalleId = null;
    }

    public function cerrarAnular(): void
    {
        $this->modalAnular = false;
        $this->signoIdAnular = null;
        $this->motivoAnulacion = '';
    }

    public function limpiarFormulario(): void
    {
        $this->signoId = null;
        $this->fecha = now()->toDateString();
        $this->hora = now()->format('H:i');
        $this->presion_sistolica = null;
        $this->presion_diastolica = null;
        $this->frecuencia_cardiaca = null;
        $this->frecuencia_respiratoria = null;
        $this->temperatura = null;
        $this->saturacion = null;
        $this->glucosa = null;
        $this->peso = null;
        $this->talla = null;
        $this->imc = null;
        $this->dolor = null;
        $this->observacion = '';
        $this->guardarConfirmado = false;
        $this->resetErrorBag();
    }

    public function detectarAlertas(): array
    {
        $alertas = [];

        $this->agregarAlerta($alertas, $this->clasificarPresion($this->presion_sistolica, $this->presion_diastolica), 'Presión arterial');
        $this->agregarAlerta($alertas, $this->clasificarNumero($this->frecuencia_cardiaca, 60, 100, null, null, 'lpm'), 'Frecuencia cardíaca');
        $this->agregarAlerta($alertas, $this->clasificarNumero($this->saturacion, 95, null, 92, 94, '%'), 'Saturación de oxígeno');
        $this->agregarAlerta($alertas, $this->clasificarNumero($this->temperatura, 36.0, 37.5, null, null, '°C'), 'Temperatura');
        $this->agregarAlerta($alertas, $this->clasificarNumero($this->frecuencia_respiratoria, 12, 20, null, null, 'rpm'), 'Frecuencia respiratoria');

        if ($this->glucosa !== null && $this->glucosa !== '') {
            $glucosa = (float) $this->glucosa;
            if ($glucosa < 70 || $glucosa >= 180) {
                $alertas[] = [
                    'campo' => 'Glucosa',
                    'mensaje' => 'Valor fuera del rango referencial interno. Verifique antes de guardar.',
                ];
            }
        }

        return $alertas;
    }

    private function rules(): array
    {
        return [
            'fecha' => 'required|date|before_or_equal:today',
            'hora' => 'required',
            'presion_sistolica' => 'nullable|integer|min:60|max:250',
            'presion_diastolica' => 'nullable|integer|min:40|max:160',
            'frecuencia_cardiaca' => 'nullable|integer|min:30|max:220',
            'frecuencia_respiratoria' => 'nullable|integer|min:5|max:60',
            'temperatura' => 'nullable|numeric|min:30|max:45',
            'saturacion' => 'nullable|integer|min:0|max:100',
            'glucosa' => 'nullable|numeric|min:20|max:600',
            'peso' => 'nullable|numeric|min:20|max:250',
            'talla' => 'nullable|numeric|min:0.5|max:250',
            'imc' => 'nullable|numeric|min:0|max:100',
            'dolor' => 'nullable|integer|min:0|max:10',
            'observacion' => 'nullable|string|max:1000',
        ];
    }

    private function messages(): array
    {
        return [
            'fecha.required' => 'La fecha es obligatoria.',
            'fecha.before_or_equal' => 'La fecha de medición no puede ser futura.',
            'hora.required' => 'La hora es obligatoria.',
            'presion_sistolica.min' => 'La presión sistólica mínima aceptada es 60 mmHg.',
            'presion_sistolica.max' => 'La presión sistólica máxima aceptada es 250 mmHg.',
            'presion_diastolica.min' => 'La presión diastólica mínima aceptada es 40 mmHg.',
            'presion_diastolica.max' => 'La presión diastólica máxima aceptada es 160 mmHg.',
            'frecuencia_cardiaca.min' => 'La frecuencia cardíaca debe ser mayor a cero y clínicamente posible.',
            'frecuencia_cardiaca.max' => 'La frecuencia cardíaca máxima aceptada es 220 lpm.',
            'frecuencia_respiratoria.min' => 'La frecuencia respiratoria mínima aceptada es 5 rpm.',
            'frecuencia_respiratoria.max' => 'La frecuencia respiratoria máxima aceptada es 60 rpm.',
            'temperatura.min' => 'La temperatura mínima aceptada es 30 °C.',
            'temperatura.max' => 'La temperatura máxima aceptada es 45 °C.',
            'saturacion.min' => 'La saturación mínima es 0%.',
            'saturacion.max' => 'La saturación no puede superar 100%.',
            'peso.min' => 'El peso debe ser positivo y clínicamente posible.',
            'peso.max' => 'El peso máximo aceptado es 250 kg.',
            'talla.min' => 'La talla debe ser positiva.',
            'talla.max' => 'La talla máxima aceptada es 250 cm.',
            'dolor.min' => 'La escala de dolor va de 0 a 10.',
            'dolor.max' => 'La escala de dolor va de 0 a 10.',
        ];
    }

    private function validarMomentoMedicion(): void
    {
        if (!$this->fecha || !$this->hora) {
            return;
        }

        $momento = Carbon::parse("{$this->fecha} {$this->hora}");

        if ($momento->isFuture()) {
            $this->addError('hora', 'La fecha y hora de medición no pueden estar en el futuro.');
        }
    }

    private function validarCoherenciaClinica(): void
    {
        if ($this->presion_sistolica && $this->presion_diastolica && $this->presion_sistolica <= $this->presion_diastolica) {
            $this->addError('presion_sistolica', 'La presión sistólica debe ser mayor que la diastólica.');
        }
    }

    private function calcularImcVisual(): void
    {
        if (!$this->peso || !$this->talla || (float) $this->peso <= 0 || (float) $this->talla <= 0) {
            $this->imc = null;
            return;
        }

        $tallaMetros = (float) $this->talla > 3 ? (float) $this->talla / 100 : (float) $this->talla;
        $this->imc = $tallaMetros > 0 ? round((float) $this->peso / ($tallaMetros * $tallaMetros), 2) : null;
    }

    private function aplicarFiltros(Collection $signos): Collection
    {
        return $signos
            ->filter(function ($signo) {
                if ($this->fechaDesde && $signo->fecha->lt(Carbon::parse($this->fechaDesde)->startOfDay())) {
                    return false;
                }

                if ($this->fechaHasta && $signo->fecha->gt(Carbon::parse($this->fechaHasta)->endOfDay())) {
                    return false;
                }

                if ($this->responsable !== 'todos' && (string) $signo->registrado_por !== (string) $this->responsable) {
                    return false;
                }

                if (!$this->cumpleFiltroParametro($signo)) {
                    return false;
                }

                if ($this->estadoClinico !== 'todos') {
                    return $this->analizarRegistro($signo)['key'] === $this->estadoClinico;
                }

                return true;
            })
            ->values();
    }

    private function cumpleFiltroParametro(SignosVitalesAdulto $signo): bool
    {
        return match ($this->parametro) {
            'presion' => $signo->presion_sistolica !== null || $signo->presion_diastolica !== null || $signo->presion_arterial,
            'frecuencia_cardiaca' => $signo->frecuencia_cardiaca !== null,
            'saturacion' => $signo->saturacion !== null,
            'temperatura' => $signo->temperatura !== null,
            'frecuencia_respiratoria' => $signo->frecuencia_respiratoria !== null,
            'peso' => $signo->peso !== null,
            'glucosa' => $signo->glucosa !== null,
            'dolor' => $signo->dolor !== null,
            default => true,
        };
    }

    private function paginarColeccion(Collection $items): LengthAwarePaginator
    {
        $page = $this->getPage();
        $perPage = max(5, min(25, $this->perPage));

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
    }

    private function obtenerResponsables(Collection $signos): array
    {
        return $signos
            ->filter(fn ($signo) => $signo->registrado_por)
            ->mapWithKeys(fn ($signo) => [
                $signo->registrado_por => optional($signo->registradoPor)->name ?? "Usuario {$signo->registrado_por}",
            ])
            ->sort()
            ->all();
    }

    private function construirMetricas(Collection $signos): array
    {
        return [
            $this->metricaPresion($signos),
            $this->metricaNumerica($signos, 'frecuencia_cardiaca', 'Frecuencia cardíaca', 'lpm', 'ph-pulse', 3, fn ($valor) => $this->clasificarNumero($valor, 60, 100, null, null, 'lpm')),
            $this->metricaNumerica($signos, 'saturacion', 'Saturación de oxígeno', '%', 'ph-wave-sine', 1, fn ($valor) => $this->clasificarNumero($valor, 95, null, 92, 94, '%')),
            $this->metricaNumerica($signos, 'temperatura', 'Temperatura', '°C', 'ph-thermometer', 0.2, fn ($valor) => $this->clasificarNumero($valor, 36.0, 37.5, null, null, '°C'), 1),
            $this->metricaNumerica($signos, 'frecuencia_respiratoria', 'Frecuencia respiratoria', 'rpm', 'ph-wind', 1, fn ($valor) => $this->clasificarNumero($valor, 12, 20, null, null, 'rpm')),
            $this->metricaNumerica($signos, 'peso', 'Peso registrado', 'kg', 'ph-scales', 0.3, fn () => ['key' => 'normal', 'label' => 'Evolución', 'message' => 'Seguimiento antropométrico'], 1),
            $this->metricaNumerica($signos, 'imc', 'IMC calculado', 'kg/m²', 'ph-chart-line-up', 0.3, fn () => ['key' => 'normal', 'label' => 'Referencial', 'message' => 'Interpretar con contexto clínico'], 1),
        ];
    }

    private function metricaPresion(Collection $signos): array
    {
        $registros = $signos
            ->filter(fn ($signo) => $signo->presion_sistolica !== null && $signo->presion_diastolica !== null)
            ->values();

        $ultimo = $registros->first();
        $previo = $registros->get(1);

        if (!$ultimo) {
            return $this->metricaSinDatos('Presión arterial', 'mmHg', 'ph-heartbeat');
        }

        $estado = $this->clasificarPresion($ultimo->presion_sistolica, $ultimo->presion_diastolica);

        return [
            'key' => 'presion',
            'label' => 'Presión arterial',
            'value' => "{$ultimo->presion_sistolica}/{$ultimo->presion_diastolica}",
            'unit' => 'mmHg',
            'icon' => 'ph-heartbeat',
            'date' => $this->formatearFechaHora($ultimo),
            'status_key' => $estado['key'],
            'status_label' => $estado['label'],
            'variation' => $this->variacionPresion($ultimo, $previo),
            'avg' => $this->promedioPresion($registros),
            'min' => $this->minMaxPresion($registros, 'min'),
            'max' => $this->minMaxPresion($registros, 'max'),
        ];
    }

    private function metricaNumerica(Collection $signos, string $campo, string $label, string $unidad, string $icono, float $tolerancia, callable $clasificador, int $decimales = 0): array
    {
        $registros = $signos
            ->filter(fn ($signo) => $signo->{$campo} !== null && $signo->{$campo} !== '')
            ->values();

        $ultimo = $registros->first();
        $previo = $registros->get(1);

        if (!$ultimo) {
            return $this->metricaSinDatos($label, $unidad, $icono);
        }

        $valor = (float) $ultimo->{$campo};
        $estado = $clasificador($valor);
        $valores = $registros->pluck($campo)->map(fn ($item) => (float) $item);

        return [
            'key' => $campo,
            'label' => $label,
            'value' => number_format($valor, $decimales),
            'unit' => $unidad,
            'icon' => $icono,
            'date' => $this->formatearFechaHora($ultimo),
            'status_key' => $estado['key'],
            'status_label' => $estado['label'],
            'variation' => $this->variacionNumerica($ultimo->{$campo}, $previo?->{$campo}, $tolerancia, $unidad, $decimales),
            'avg' => $valores->isNotEmpty() ? number_format($valores->avg(), $decimales) . " {$unidad}" : 'S/D',
            'min' => $valores->isNotEmpty() ? number_format($valores->min(), $decimales) . " {$unidad}" : 'S/D',
            'max' => $valores->isNotEmpty() ? number_format($valores->max(), $decimales) . " {$unidad}" : 'S/D',
        ];
    }

    private function metricaSinDatos(string $label, string $unidad, string $icono): array
    {
        return [
            'key' => str($label)->slug('_')->toString(),
            'label' => $label,
            'value' => 'S/D',
            'unit' => $unidad,
            'icon' => $icono,
            'date' => 'Sin mediciones',
            'status_key' => 'sin_datos',
            'status_label' => 'Sin datos',
            'variation' => 'Información insuficiente',
            'avg' => 'S/D',
            'min' => 'S/D',
            'max' => 'S/D',
        ];
    }

    private function construirResumenPeriodo(Collection $signos, Collection $baseSignos): array
    {
        $analisis = $signos->map(fn ($signo) => $this->analizarRegistro($signo));
        $normales = $analisis->where('key', 'normal')->count();
        $fueraRango = $analisis->whereIn('key', ['fuera_rango', 'requiere_revision'])->count();
        $mesActual = $baseSignos
            ->where('estado', 'VIGENTE')
            ->filter(fn ($signo) => $signo->fecha && $signo->fecha->isSameMonth(now()))
            ->count();

        return [
            'mediciones_periodo' => $signos->count(),
            'mediciones_mes' => $mesActual,
            'fuera_rango' => $fueraRango,
            'porcentaje_normales' => $signos->count() > 0 ? round(($normales / $signos->count()) * 100) : 0,
            'ultima_medicion' => $baseSignos->firstWhere('estado', 'VIGENTE')
                ? $this->formatearFechaHora($baseSignos->firstWhere('estado', 'VIGENTE'))
                : 'Sin registros',
            'sin_medicion_reciente' => !$baseSignos->firstWhere('estado', 'VIGENTE')
                || $baseSignos->firstWhere('estado', 'VIGENTE')->fecha->lt(now()->subDays(7)),
        ];
    }

    private function construirInterpretacion(Collection $signos): array
    {
        $conteos = [
            'normal' => 0,
            'observacion' => 0,
            'fuera_rango' => 0,
            'requiere_revision' => 0,
            'sin_datos' => 0,
        ];

        foreach ($signos as $signo) {
            $estado = $this->analizarRegistro($signo)['key'];
            if (array_key_exists($estado, $conteos)) {
                $conteos[$estado]++;
            }
        }

        $prioridad = collect(['requiere_revision', 'fuera_rango', 'observacion', 'normal', 'sin_datos'])
            ->first(fn ($key) => ($conteos[$key] ?? 0) > 0) ?? 'sin_datos';

        $mensajes = [
            'normal' => 'Los registros filtrados se mantienen dentro de rangos referenciales normales.',
            'observacion' => 'Existen valores en observación. Conviene confirmar tendencia con próximos controles.',
            'fuera_rango' => 'Hay valores fuera del rango referencial. Requieren revisión del personal de salud.',
            'requiere_revision' => 'Se detectan controles que requieren revisión prioritaria del personal de salud.',
            'sin_datos' => 'Información insuficiente para análisis estadístico.',
        ];

        return [
            'conteos' => $conteos,
            'estado_principal' => $prioridad,
            'mensaje' => $mensajes[$prioridad],
        ];
    }

    private function construirChartData(Collection $signos): array
    {
        $ordenados = $signos
            ->sortBy(fn ($signo) => $signo->fecha->format('Ymd') . $signo->hora_formateada)
            ->values();

        $serie = $ordenados->slice(max(0, $ordenados->count() - 24))->values();
        $labels = $serie->map(fn ($signo) => $signo->fecha->format('d/m') . ' ' . $signo->hora_formateada)->all();
        $estados = $signos->map(fn ($signo) => $this->analizarRegistro($signo)['key']);

        return [
            'labels' => $labels,
            'active' => $this->grafica,
            'main' => $this->construirGraficaPrincipal($serie, $labels),
            'pressure' => [
                'sistolica' => $serie->map(fn ($signo) => $signo->presion_sistolica)->all(),
                'diastolica' => $serie->map(fn ($signo) => $signo->presion_diastolica)->all(),
            ],
            'heart' => $serie->map(fn ($signo) => $signo->frecuencia_cardiaca)->all(),
            'saturation' => $serie->map(fn ($signo) => $signo->saturacion)->all(),
            'temperature' => $serie->map(fn ($signo) => $signo->temperatura ? (float) $signo->temperatura : null)->all(),
            'respiration' => $serie->map(fn ($signo) => $signo->frecuencia_respiratoria)->all(),
            'weight' => $serie->map(fn ($signo) => $signo->peso ? (float) $signo->peso : null)->all(),
            'imc' => $serie->map(fn ($signo) => $signo->imc ? (float) $signo->imc : null)->all(),
            'monthly' => $this->construirResumenSemanal($signos),
            'states' => [
                'labels' => ['Normal', 'En observación', 'Fuera de rango', 'Requiere revisión', 'Sin datos'],
                'data' => [
                    $estados->filter(fn ($key) => $key === 'normal')->count(),
                    $estados->filter(fn ($key) => $key === 'observacion')->count(),
                    $estados->filter(fn ($key) => $key === 'fuera_rango')->count(),
                    $estados->filter(fn ($key) => $key === 'requiere_revision')->count(),
                    $estados->filter(fn ($key) => $key === 'sin_datos')->count(),
                ],
            ],
            'hasEnough' => [
                'pressure' => $serie->whereNotNull('presion_sistolica')->count() >= 2 && $serie->whereNotNull('presion_diastolica')->count() >= 2,
                'heart' => $serie->whereNotNull('frecuencia_cardiaca')->count() >= 2,
                'saturation' => $serie->whereNotNull('saturacion')->count() >= 2,
                'temperature' => $serie->whereNotNull('temperatura')->count() >= 2,
                'respiration' => $serie->whereNotNull('frecuencia_respiratoria')->count() >= 2,
                'weight' => $serie->whereNotNull('peso')->count() >= 2,
                'imc' => $serie->whereNotNull('imc')->count() >= 2,
                'monthly' => $signos->count() >= 1,
                'states' => $signos->count() >= 1,
            ],
        ];
    }

    private function construirGraficaPrincipal(Collection $serie, array $labels): array
    {
        $configuraciones = [
            'presion' => [
                'title' => 'Evolución de presión arterial',
                'subtitle' => 'Sistólica y diastólica en mmHg.',
                'unit' => 'mmHg',
                'hasEnough' => $serie->whereNotNull('presion_sistolica')->count() >= 2 && $serie->whereNotNull('presion_diastolica')->count() >= 2,
                'datasets' => [
                    ['label' => 'Sistólica', 'data' => $serie->map(fn ($signo) => $signo->presion_sistolica)->all(), 'color' => '#E27D60', 'fill' => true],
                    ['label' => 'Diastólica', 'data' => $serie->map(fn ($signo) => $signo->presion_diastolica)->all(), 'color' => '#5B5F97', 'fill' => false],
                ],
            ],
            'frecuencia_cardiaca' => [
                'title' => 'Evolución de frecuencia cardíaca',
                'subtitle' => 'Tendencia temporal en lpm.',
                'unit' => 'lpm',
                'hasEnough' => $serie->whereNotNull('frecuencia_cardiaca')->count() >= 2,
                'datasets' => [
                    ['label' => 'Frecuencia cardíaca', 'data' => $serie->map(fn ($signo) => $signo->frecuencia_cardiaca)->all(), 'color' => '#C45F4B', 'fill' => true],
                ],
            ],
            'saturacion' => [
                'title' => 'Evolución de saturación de oxígeno',
                'subtitle' => 'Seguimiento de SpO2 en porcentaje.',
                'unit' => '%',
                'hasEnough' => $serie->whereNotNull('saturacion')->count() >= 2,
                'datasets' => [
                    ['label' => 'Saturación O2', 'data' => $serie->map(fn ($signo) => $signo->saturacion)->all(), 'color' => '#63775B', 'fill' => true],
                ],
            ],
            'temperatura' => [
                'title' => 'Evolución de temperatura',
                'subtitle' => 'Últimos controles registrados en °C.',
                'unit' => '°C',
                'hasEnough' => $serie->whereNotNull('temperatura')->count() >= 2,
                'datasets' => [
                    ['label' => 'Temperatura', 'data' => $serie->map(fn ($signo) => $signo->temperatura ? (float) $signo->temperatura : null)->all(), 'color' => '#D9A05B', 'fill' => true],
                ],
            ],
            'frecuencia_respiratoria' => [
                'title' => 'Evolución de frecuencia respiratoria',
                'subtitle' => 'Tendencia respiratoria en rpm.',
                'unit' => 'rpm',
                'hasEnough' => $serie->whereNotNull('frecuencia_respiratoria')->count() >= 2,
                'datasets' => [
                    ['label' => 'Frecuencia respiratoria', 'data' => $serie->map(fn ($signo) => $signo->frecuencia_respiratoria)->all(), 'color' => '#5B5F97', 'fill' => true],
                ],
            ],
            'peso' => [
                'title' => 'Evolución de peso e IMC',
                'subtitle' => 'Seguimiento antropométrico referencial.',
                'unit' => 'kg / kg/m²',
                'hasEnough' => $serie->whereNotNull('peso')->count() >= 2,
                'datasets' => [
                    ['label' => 'Peso', 'data' => $serie->map(fn ($signo) => $signo->peso ? (float) $signo->peso : null)->all(), 'color' => '#8DA280', 'fill' => true],
                    ['label' => 'IMC', 'data' => $serie->map(fn ($signo) => $signo->imc ? (float) $signo->imc : null)->all(), 'color' => '#2F3E5C', 'fill' => false],
                ],
            ],
        ];

        return [
            'labels' => $labels,
            ...($configuraciones[$this->grafica] ?? $configuraciones['presion']),
        ];
    }

    private function construirResumenSemanal(Collection $signos): array
    {
        $labels = [];
        $data = [];
        $inicio = now()->subWeeks(5)->startOfWeek();

        for ($i = 0; $i < 6; $i++) {
            $desde = $inicio->copy()->addWeeks($i);
            $hasta = $desde->copy()->endOfWeek();
            $labels[] = $desde->format('d/m') . ' - ' . $hasta->format('d/m');
            $data[] = $signos
                ->filter(fn ($signo) => $signo->fecha->betweenIncluded($desde, $hasta))
                ->count();
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function analizarRegistro(SignosVitalesAdulto $signo): array
    {
        if ($signo->estado === 'ANULADO') {
            return [
                'key' => 'anulado',
                'label' => 'Anulado',
                'message' => 'Registro clínico anulado, conservado por trazabilidad.',
                'motivos' => ['Registro anulado'],
            ];
        }

        $evaluaciones = [];

        $this->agregarEvaluacion($evaluaciones, $this->clasificarPresion($signo->presion_sistolica, $signo->presion_diastolica), 'Presión arterial');
        $this->agregarEvaluacion($evaluaciones, $this->clasificarNumero($signo->frecuencia_cardiaca, 60, 100, null, null, 'lpm'), 'Frecuencia cardíaca');
        $this->agregarEvaluacion($evaluaciones, $this->clasificarNumero($signo->saturacion, 95, null, 92, 94, '%'), 'Saturación de oxígeno');
        $this->agregarEvaluacion($evaluaciones, $this->clasificarNumero($signo->temperatura, 36.0, 37.5, null, null, '°C'), 'Temperatura');
        $this->agregarEvaluacion($evaluaciones, $this->clasificarNumero($signo->frecuencia_respiratoria, 12, 20, null, null, 'rpm'), 'Frecuencia respiratoria');

        if (empty($evaluaciones)) {
            return [
                'key' => 'sin_datos',
                'label' => 'Sin datos',
                'message' => 'Sin parámetros clasificables en este registro.',
                'motivos' => [],
            ];
        }

        $motivos = collect($evaluaciones)
            ->filter(fn ($item) => in_array($item['key'], ['observacion', 'fuera_rango', 'requiere_revision'], true))
            ->map(fn ($item) => "{$item['campo']}: {$item['message']}")
            ->values()
            ->all();

        if (collect($evaluaciones)->contains('key', 'requiere_revision')) {
            return [
                'key' => 'requiere_revision',
                'label' => 'Requiere revisión',
                'message' => 'Valor fuera del rango referencial con prioridad de revisión.',
                'motivos' => $motivos,
            ];
        }

        if (collect($evaluaciones)->contains('key', 'fuera_rango')) {
            return [
                'key' => 'fuera_rango',
                'label' => 'Fuera de rango',
                'message' => 'Uno o más parámetros están fuera del rango referencial.',
                'motivos' => $motivos,
            ];
        }

        if (collect($evaluaciones)->contains('key', 'observacion')) {
            return [
                'key' => 'observacion',
                'label' => 'En observación',
                'message' => 'Uno o más parámetros requieren seguimiento.',
                'motivos' => $motivos,
            ];
        }

        return [
            'key' => 'normal',
            'label' => 'Normal',
            'message' => 'Parámetros dentro de rangos referenciales.',
            'motivos' => [],
        ];
    }

    private function clasificarPresion($sistolica, $diastolica): array
    {
        if ($sistolica === null || $sistolica === '' || $diastolica === null || $diastolica === '') {
            return ['key' => 'sin_datos', 'label' => 'Sin datos', 'message' => 'Presión arterial no registrada.'];
        }

        $sis = (int) $sistolica;
        $dia = (int) $diastolica;

        if ($sis >= 160 || $dia >= 100 || $sis < 90 || $dia < 60) {
            return ['key' => 'requiere_revision', 'label' => 'Requiere revisión', 'message' => 'Valor bajo o elevado. Requiere revisión del personal de salud.'];
        }

        if ($sis >= 140 || $dia >= 90) {
            return ['key' => 'fuera_rango', 'label' => 'Fuera de rango', 'message' => 'Valor elevado según rango referencial.'];
        }

        if (($sis >= 130 && $sis <= 139) || ($dia >= 85 && $dia <= 89)) {
            return ['key' => 'observacion', 'label' => 'En observación', 'message' => 'Valor en observación. Confirmar tendencia.'];
        }

        if ($sis >= 90 && $sis <= 129 && $dia >= 60 && $dia <= 84) {
            return ['key' => 'normal', 'label' => 'Normal', 'message' => 'Presión arterial dentro de rango referencial.'];
        }

        return ['key' => 'fuera_rango', 'label' => 'Fuera de rango', 'message' => 'Valor fuera del rango referencial.'];
    }

    private function clasificarNumero($valor, ?float $normalMin, ?float $normalMax, ?float $observacionMin, ?float $observacionMax, string $unidad): array
    {
        if ($valor === null || $valor === '') {
            return ['key' => 'sin_datos', 'label' => 'Sin datos', 'message' => 'Parámetro no registrado.'];
        }

        $numero = (float) $valor;

        if ($unidad === '%' && $numero < 92) {
            return ['key' => 'requiere_revision', 'label' => 'Requiere revisión', 'message' => 'Saturación baja. Requiere revisión del personal de salud.'];
        }

        if ($unidad === '°C' && ($numero < 36.0 || $numero > 37.5)) {
            return [
                'key' => $numero >= 38.0 ? 'requiere_revision' : 'fuera_rango',
                'label' => $numero >= 38.0 ? 'Requiere revisión' : 'Fuera de rango',
                'message' => 'Temperatura fuera del rango referencial.',
            ];
        }

        if ($unidad === 'lpm' && ($numero < 60 || $numero > 100)) {
            return [
                'key' => ($numero < 50 || $numero > 120) ? 'requiere_revision' : 'fuera_rango',
                'label' => ($numero < 50 || $numero > 120) ? 'Requiere revisión' : 'Fuera de rango',
                'message' => 'Frecuencia cardíaca fuera del rango referencial.',
            ];
        }

        if ($unidad === 'rpm' && ($numero < 12 || $numero > 20)) {
            return [
                'key' => ($numero < 10 || $numero > 24) ? 'requiere_revision' : 'fuera_rango',
                'label' => ($numero < 10 || $numero > 24) ? 'Requiere revisión' : 'Fuera de rango',
                'message' => 'Frecuencia respiratoria fuera del rango referencial.',
            ];
        }

        if ($normalMin !== null && $normalMax !== null && $numero >= $normalMin && $numero <= $normalMax) {
            return ['key' => 'normal', 'label' => 'Normal', 'message' => 'Valor dentro del rango referencial.'];
        }

        if ($normalMin !== null && $normalMax === null && $numero >= $normalMin) {
            return ['key' => 'normal', 'label' => 'Normal', 'message' => 'Valor dentro del rango referencial.'];
        }

        if ($observacionMin !== null && $observacionMax !== null && $numero >= $observacionMin && $numero <= $observacionMax) {
            return ['key' => 'observacion', 'label' => 'En observación', 'message' => 'Valor en observación. Confirmar tendencia.'];
        }

        return ['key' => 'fuera_rango', 'label' => 'Fuera de rango', 'message' => 'Valor fuera del rango referencial.'];
    }

    private function agregarEvaluacion(array &$evaluaciones, array $estado, string $campo): void
    {
        if ($estado['key'] === 'sin_datos') {
            return;
        }

        $estado['campo'] = $campo;
        $evaluaciones[] = $estado;
    }

    private function agregarAlerta(array &$alertas, array $estado, string $campo): void
    {
        if (in_array($estado['key'], ['observacion', 'fuera_rango', 'requiere_revision'], true)) {
            $alertas[] = [
                'campo' => $campo,
                'mensaje' => $estado['message'],
            ];
        }
    }

    private function variacionPresion(?SignosVitalesAdulto $ultimo, ?SignosVitalesAdulto $previo): string
    {
        if (!$ultimo || !$previo || $previo->presion_sistolica === null) {
            return 'Sin dato previo';
        }

        $delta = $ultimo->presion_sistolica - $previo->presion_sistolica;

        if (abs($delta) <= 3) {
            return 'Estable respecto al último registro';
        }

        return $delta > 0
            ? "Subió {$delta} mmHg en sistólica"
            : 'Bajó ' . abs($delta) . ' mmHg en sistólica';
    }

    private function variacionNumerica($actual, $previo, float $tolerancia, string $unidad, int $decimales): string
    {
        if ($previo === null || $previo === '') {
            return 'Sin dato previo';
        }

        $delta = (float) $actual - (float) $previo;

        if (abs($delta) <= $tolerancia) {
            return 'Estable respecto al último registro';
        }

        $deltaFormateado = number_format(abs($delta), $decimales);

        return $delta > 0
            ? "Subió {$deltaFormateado} {$unidad}"
            : "Bajó {$deltaFormateado} {$unidad}";
    }

    private function promedioPresion(Collection $registros): string
    {
        if ($registros->isEmpty()) {
            return 'S/D';
        }

        return round($registros->avg('presion_sistolica')) . '/' . round($registros->avg('presion_diastolica')) . ' mmHg';
    }

    private function minMaxPresion(Collection $registros, string $modo): string
    {
        if ($registros->isEmpty()) {
            return 'S/D';
        }

        $sistolica = $modo === 'min' ? $registros->min('presion_sistolica') : $registros->max('presion_sistolica');
        $diastolica = $modo === 'min' ? $registros->min('presion_diastolica') : $registros->max('presion_diastolica');

        return "{$sistolica}/{$diastolica} mmHg";
    }

    private function formatearFechaHora(SignosVitalesAdulto $signo): string
    {
        return $signo->fecha->format('d/m/Y') . ' ' . $signo->hora_formateada;
    }
}
