<?php

namespace App\Livewire\Cuidados;

use App\Models\AdultoMayor;
use App\Models\AlertaAdulto;
use App\Models\AdministracionMedicacion;
use App\Models\MedicacionAdulto;
use App\Models\PaseTurno;
use App\Models\SeguimientoDiario;
use App\Models\SignosVitalesAdulto;
use App\Models\TareaPlanCuidado;
use App\Services\Alertas\DeteccionAlertasService;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\Medicacion\AgendaMedicacionService;
use Livewire\Component;

class FichaPaciente extends Component
{
    public AdultoMayor $adultoMayor;
    public string $tabActivo = 'resumen';

    // Modales de Acción Rápida
    // Filtros y Métricas de Historial 360°
    public string $historialFiltroTipo = 'TODOS';
    public ?string $historialFechaDesde = null;
    public ?string $historialFechaHasta = null;
    public string $metricaSignosSeleccionada = 'PA';

    public function setHistorialFiltro(string $tipo): void
    {
        $this->historialFiltroTipo = $tipo;
    }

    public function setMetricaSignos(string $metrica): void
    {
        $m = strtoupper(trim($metrica));
        if ($m === 'TEMPERATURA') $m = 'TEMP';
        if ($m === 'GLUCEMIA') $m = 'GLUCOSA';
        if ($m === 'SATURACION') $m = 'SPO2';
        $this->metricaSignosSeleccionada = $m;
    }

    public function limpiarFiltrosHistorial(): void
    {
        $this->historialFiltroTipo = 'TODOS';
        $this->historialFechaDesde = null;
        $this->historialFechaHasta = null;
    }

    public bool $modalSignos = false;
    public ?string $signoPA = null, $signoFC = null, $signoFR = null, $signoTemp = null, $signoSat = null, $signoGlucosa = null, $signoDolor = null, $signoObs = null;

    public bool $modalMed = false;
    public ?string $medSeleccionadoId = null;
    public ?string $medHoraProgramada = null;
    public string $medNombre = '', $medDosis = '', $medVia = '';
    public string $medAccion = 'ADMINISTRAR';
    public bool $medAdministrado = true;
    public string $medMotivoOmision = '', $medEfectoObs = '';

    public bool $modalTarea = false;
    public ?string $tareaAccionId = null;
    public string $tareaTitulo = '';
    public string $tareaEstadoAccion = 'REALIZADA';
    public string $tareaResultado = '', $tareaMotivoOmision = '';

    public bool $modalSeguimiento = false;
    public string $segEstado = 'ESTABLE', $segAlimentacion = 'COMPLETA', $segMovilidad = 'INDEPENDIENTE', $segSueno = 'NORMAL';
    public bool $segIncidente = false, $segRequiereMedico = false;
    public string $segObs = '';

    public bool $modalIncidente = false;
    public string $incidenteTipo = 'INCIDENTE', $incidenteNivel = 'ALTO', $incidenteMotivo = '';

    public bool $modalAtenderAlerta = false;
    public ?string $alertaAccionId = null;
    public string $accionTomadaAlerta = '';

    public bool $modalCerrarAlerta = false;
    public string $observacionCierreAlerta = '';

    public function mount(string $adulto)
    {
        $this->cargarAdulto($adulto);

        // Control de acceso unificado vía TurnoEnfermeriaService
        $service = app(TurnoEnfermeriaService::class);
        $service->autorizarAccionPaciente($this->adultoMayor, Auth::user());

        if (request()->query('tab')) {
            $this->tabActivo = request()->query('tab');
        }
    }

    public function cargarAdulto(string $codAm): void
    {
        $this->adultoMayor = AdultoMayor::with([
            'habitacion', 'cama',
            'asignacionTurnoActiva.turno',
            'asignacionTurnoActiva.enfermero',
            'planCuidadoActivo.tareas',
            'valoracionesEnfermeria' => fn($q) => $q->orderByDesc('fecha_valoracion')->orderByDesc('hora_valoracion')->take(10),
            'valoracionesMedicas' => fn($q) => $q->orderByDesc('created_at')->take(10),
            'signosVitales' => fn($q) => $q->orderByDesc('fecha')->orderByDesc('hora')->take(20),
            'medicaciones' => fn($q) => $q->whereIn('estado', ['ACTIVA', 'ACTIVO']),
            'administracionesMedicacion' => fn($q) => $q->with(['medicacion', 'registrador'])->orderByDesc('fecha')->orderByDesc('hora_programada')->take(30),
            'tareasActuales' => fn($q) => $q->with('turno')->orderByDesc('fecha_programada')->orderByDesc('hora_programada')->take(25),
            'alertas' => fn($q) => $q->with(['acciones', 'responsable', 'cerradoPor'])->orderByDesc('created_at')->take(25),
            'seguimientosDiarios' => fn($q) => $q->with('turno')->orderByDesc('fecha')->orderByDesc('hora_inicio')->take(25),
            'pasesTurno' => fn($q) => $q->with(['enfermeroSaliente', 'enfermeroEntrante', 'turnoSaliente', 'turnoEntrante'])->orderByDesc('fecha')->orderByDesc('created_at')->take(15),
            'evaluacionesGeriatricas.evaluador',
            'valoracionesFuncionales.registradoPor',
        ])->findOrFail($codAm);
    }

    public function cambiarTab(string $tab): void
    {
        if ($tab === 'cuidados') {
            $tab = 'cuidado';
        }
        $this->tabActivo = $tab;
    }

    public function abrirModalSignos(): void { $this->abrirRegistrarSignos(); }
    public function cerrarModalSignos(): void { $this->modalSignos = false; $this->resetValidation(); }
    public function abrirModalMedicacion(?string $codMed = null, ?string $horaProgramada = null): void { $this->abrirAdministrarMed($codMed, $horaProgramada); }
    public function cerrarModalMedicacion(): void { $this->modalMed = false; $this->resetValidation(); }
    public function guardarMedicacion(): void { $this->confirmarAdministracionMed(); }
    public function abrirModalTarea(string $codTarea): void { $this->abrirEjecutarTarea($codTarea); }
    public function cerrarModalTarea(): void { $this->modalTarea = false; $this->resetValidation(); }
    public function guardarTarea(): void { $this->guardarAccionTarea(); }
    public function abrirModalSeguimiento(): void { $this->abrirRegistrarSeguimiento(); }
    public function cerrarModalSeguimiento(): void { $this->modalSeguimiento = false; $this->resetValidation(); }
    public function abrirModalIncidente(): void { $this->abrirReportarIncidente(); }
    public function cerrarModalIncidente(): void { $this->modalIncidente = false; $this->resetValidation(); }
    public function abrirModalAtenderAlerta(string $codAlerta): void { $this->abrirAtenderAlerta($codAlerta); }
    public function cerrarModalAtenderAlerta(): void { $this->modalAtenderAlerta = false; $this->resetValidation(); }
    public function guardarAtenderAlerta(): void { $this->confirmarAtencionAlerta(); }
    public function abrirModalCerrarAlerta(string $codAlerta): void { $this->abrirCerrarAlerta($codAlerta); }
    public function cerrarModalCerrarAlerta(): void { $this->modalCerrarAlerta = false; $this->resetValidation(); }
    public function guardarCerrarAlerta(): void { $this->confirmarCierreAlerta(); }

    // ─── 1. REGISTRAR SIGNOS ──────────────────────────────────────────

    public function abrirRegistrarSignos(): void
    {
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($this->adultoMayor, Auth::user());
        $this->reset(['signoPA', 'signoFC', 'signoFR', 'signoTemp', 'signoSat', 'signoGlucosa', 'signoDolor', 'signoObs']);
        $this->modalSignos = true;
    }

    public function guardarSignos(): void
    {
        abort_unless(auth()->user()?->can('signos_vitales.crear'), 403);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($this->adultoMayor, Auth::user());

        $this->validate([
            'signoPA'      => 'nullable|string|max:20',
            'signoFC'      => 'nullable|integer|min:' . \App\Services\Clinica\ValidacionSignosVitalesService::FC_MIN . '|max:' . \App\Services\Clinica\ValidacionSignosVitalesService::FC_MAX,
            'signoFR'      => 'nullable|integer|min:' . \App\Services\Clinica\ValidacionSignosVitalesService::FR_MIN . '|max:' . \App\Services\Clinica\ValidacionSignosVitalesService::FR_MAX,
            'signoTemp'    => 'nullable|numeric|min:' . \App\Services\Clinica\ValidacionSignosVitalesService::TEMP_MIN . '|max:' . \App\Services\Clinica\ValidacionSignosVitalesService::TEMP_MAX,
            'signoSat'     => 'nullable|integer|min:' . \App\Services\Clinica\ValidacionSignosVitalesService::SPO2_MIN . '|max:' . \App\Services\Clinica\ValidacionSignosVitalesService::SPO2_MAX,
            'signoGlucosa' => 'nullable|numeric|min:' . \App\Services\Clinica\ValidacionSignosVitalesService::GLUCOSA_MIN . '|max:' . \App\Services\Clinica\ValidacionSignosVitalesService::GLUCOSA_MAX,
            'signoDolor'   => 'nullable|integer|min:' . \App\Services\Clinica\ValidacionSignosVitalesService::DOLOR_MIN . '|max:' . \App\Services\Clinica\ValidacionSignosVitalesService::DOLOR_MAX,
            'signoObs'     => 'nullable|string|max:1000',
        ]);

        $sis = null; $dia = null;
        if (!empty($this->signoPA)) {
            if (!str_contains($this->signoPA, '/')) {
                $this->addError('signoPA', 'La presión arterial debe tener formato Sistólica/Diastólica (ej. 120/80).');
                return;
            }
            $partes = explode('/', $this->signoPA);
            $sis = is_numeric(trim($partes[0])) ? (int)trim($partes[0]) : null;
            $dia = isset($partes[1]) && is_numeric(trim($partes[1])) ? (int)trim($partes[1]) : null;

            if ($sis === null || $dia === null) {
                $this->addError('signoPA', 'Valores de presión arterial incompletos.');
                return;
            }
            if ($sis < \App\Services\Clinica\ValidacionSignosVitalesService::PAS_MIN || $sis > \App\Services\Clinica\ValidacionSignosVitalesService::PAS_MAX) {
                $this->addError('signoPA', 'La presión sistólica debe estar entre ' . \App\Services\Clinica\ValidacionSignosVitalesService::PAS_MIN . ' y ' . \App\Services\Clinica\ValidacionSignosVitalesService::PAS_MAX . ' mmHg.');
                return;
            }
            if ($dia < \App\Services\Clinica\ValidacionSignosVitalesService::PAD_MIN || $dia > \App\Services\Clinica\ValidacionSignosVitalesService::PAD_MAX) {
                $this->addError('signoPA', 'La presión diastólica debe estar entre ' . \App\Services\Clinica\ValidacionSignosVitalesService::PAD_MIN . ' y ' . \App\Services\Clinica\ValidacionSignosVitalesService::PAD_MAX . ' mmHg.');
                return;
            }
            if ($sis <= $dia) {
                $this->addError('signoPA', "La presión sistólica ({$sis}) debe ser estrictamente mayor a la diastólica ({$dia}).");
                return;
            }
        }

        if (empty($this->signoPA) && empty($this->signoFC) && empty($this->signoTemp) && empty($this->signoSat) && empty($this->signoGlucosa) && empty($this->signoDolor)) {
            $this->addError('signoPA', 'Registre al menos un parámetro de signos vitales.');
            return;
        }

        SignosVitalesAdulto::create([
            'cod_am'                  => $this->adultoMayor->cod_am,
            'fecha'                   => today()->toDateString(),
            'hora'                    => now()->format('H:i:s'),
            'presion_arterial'        => $this->signoPA ?: null,
            'presion_sistolica'       => $sis,
            'presion_diastolica'      => $dia,
            'frecuencia_cardiaca'     => $this->signoFC ?: null,
            'frecuencia_respiratoria' => $this->signoFR ?: null,
            'temperatura'             => $this->signoTemp ?: null,
            'saturacion'              => $this->signoSat ?: null,
            'glucosa'                 => $this->signoGlucosa ?: null,
            'dolor'                   => $this->signoDolor !== null && $this->signoDolor !== '' ? (int)$this->signoDolor : null,
            'observacion'             => $this->signoObs ?: null,
            'registrado_por'          => Auth::id(),
            'estado'                  => 'VIGENTE',
        ]);

        $this->modalSignos = false;
        $this->cargarAdulto($this->adultoMayor->cod_am);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Signos vitales registrados', 'text' => 'Control hemodinámico guardado correctamente.']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 2. ADMINISTRAR O OMITIR MEDICACIÓN
    // ──────────────────────────────────────────────────────────────────────────

    public function abrirAdministrarMed(?string $codMed = null, ?string $horaProgramada = null): void
    {
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($this->adultoMayor, Auth::user());

        if (empty($codMed)) {
            $primerMed = MedicacionAdulto::where('cod_am', $this->adultoMayor->cod_am)
                ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE'])
                ->orderBy('hora_programada')
                ->first();
            if (!$primerMed) {
                $this->tabActivo = 'medicacion';
                $this->dispatch('swal', [
                    'icon' => 'info',
                    'title' => 'Medicación',
                    'text' => 'El residente no tiene medicamentos activos prescritos para administrar.'
                ]);
                return;
            }
            $codMed = $primerMed->cod_med_adulto;
        }

        $med = MedicacionAdulto::where('cod_am', $this->adultoMayor->cod_am)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE'])
            ->findOrFail($codMed);
        $this->medSeleccionadoId = $med->cod_med_adulto;
        $this->medHoraProgramada = $horaProgramada;
        $this->medNombre = $med->nombre_medicamento;
        $this->medDosis = $med->dosis ?? '';
        $this->medVia = $med->via_administracion ?? 'Oral';
        $this->medAccion = 'ADMINISTRAR';
        $this->medAdministrado = true;
        $this->medMotivoOmision = '';
        $this->medEfectoObs = '';
        $this->modalMed = true;
    }

    public function guardarAdministracionMed(): void
    {
        $this->confirmarAdministracionMed();
    }

    public function confirmarAdministracionMed(): void
    {
        abort_unless(auth()->user()?->can('administracion_medicacion.registrar'), 403);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($this->adultoMayor, Auth::user());

        $med = MedicacionAdulto::where('cod_am', $this->adultoMayor->cod_am)
            ->where('cod_med_adulto', $this->medSeleccionadoId)
            ->firstOrFail();

        if (!in_array($med->estado, ['ACTIVO', 'ACTIVA', 'VIGENTE'])) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Error', 'text' => 'El medicamento no está activo.']);
            return;
        }

        $esAdmin = $this->medAccion === 'ADMINISTRAR' && (bool)$this->medAdministrado;

        if (!$esAdmin) {
            $this->validate(['medMotivoOmision' => 'required|string|min:5|max:500'], ['medMotivoOmision.min' => 'El motivo de omisión debe tener al menos 5 caracteres.']);
        }
        $this->validate(['medEfectoObs' => 'nullable|string|max:1000']);

        $horaProgramada = $this->medHoraProgramada ?: ($med->hora_programada
            ? Carbon::parse($med->hora_programada)->format('H:i')
            : now()->format('H:i'));

        $guardado = DB::transaction(function () use ($horaProgramada, $esAdmin): bool {
            $duplicado = AdministracionMedicacion::where('cod_med_adulto', $this->medSeleccionadoId)
                ->whereDate('fecha', today())
                ->where('hora_programada', 'like', $horaProgramada . '%')
                ->lockForUpdate()
                ->exists();
            if ($duplicado) {
                return false;
            }

            AdministracionMedicacion::create([
                'cod_am'            => $this->adultoMayor->cod_am,
                'cod_med_adulto'    => $this->medSeleccionadoId,
                'fecha'             => today()->toDateString(),
                'hora_programada'   => $horaProgramada,
                'hora_real'         => $esAdmin ? now()->format('H:i') : null,
                'administrado'      => $esAdmin,
                'motivo_omision'    => !$esAdmin ? trim($this->medMotivoOmision) : null,
                'efecto_observado'  => filled($this->medEfectoObs) ? trim($this->medEfectoObs) : null,
                'registrado_por'    => Auth::id(),
            ]);
            return true;
        });

        if (!$guardado) {
            $this->dispatch('swal', ['icon' => 'warning', 'title' => 'Aviso', 'text' => 'Esta dosis ya fue registrada previamente hoy.']);
            return;
        }

        $this->modalMed = false;
        $this->cargarAdulto($this->adultoMayor->cod_am);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Medicación registrada', 'text' => 'Trazabilidad de fármaco registrada con éxito.']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 3. EJECUTAR TAREA DE CUIDADOS
    // ──────────────────────────────────────────────────────────────────────────

    public function abrirEjecutarTarea(string $codTarea, string $estado = 'REALIZADA'): void
    {
        $tarea = TareaPlanCuidado::findOrFail($codTarea);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($tarea->cod_am, Auth::user());

        $this->tareaAccionId = $codTarea;
        $this->tareaTitulo = $tarea->titulo;
        $this->tareaEstadoAccion = $estado;
        $this->tareaResultado = '';
        $this->tareaMotivoOmision = '';
        $this->modalTarea = true;
    }

    public function completarTarea(string $codTarea): void
    {
        abort_unless(auth()->user()?->can('tareas.registrar_resultado'), 403);
        $tarea = TareaPlanCuidado::findOrFail($codTarea);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($tarea->cod_am, Auth::user());
        abort_unless($tarea->puedeCompletarse(), 409, 'La tarea ya no está pendiente de ejecución.');

        $tarea->update([
            'estado' => 'REALIZADA',
            'resultado' => 'Tarea efectuada y confirmada en turno.',
            'ejecutado_por' => Auth::id(),
            'fecha_realizada' => now(),
        ]);

        $this->cargarAdulto($this->adultoMayor->cod_am);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Tarea realizada', 'text' => 'El cuidado fue registrado como completado.']);
    }

    public function omitirTarea(string $codTarea): void
    {
        $this->abrirEjecutarTarea($codTarea, 'OMITIDA');
    }

    public function guardarAccionTarea(): void
    {
        abort_unless(auth()->user()?->can('tareas.registrar_resultado'), 403);
        $tarea = TareaPlanCuidado::findOrFail($this->tareaAccionId);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($tarea->cod_am, Auth::user());
        abort_unless($tarea->puedeCompletarse(), 409, 'La tarea ya no está pendiente de ejecución.');

        $this->validate([
            'tareaEstadoAccion' => 'required|in:REALIZADA,OMITIDA',
        ]);

        if ($this->tareaEstadoAccion === 'REALIZADA') {
            $this->validate(['tareaResultado' => 'required|string|min:5|max:500'], ['tareaResultado.required' => 'El resultado de la tarea es obligatorio.', 'tareaResultado.min' => 'El resultado debe tener al menos 5 caracteres.']);
        } elseif ($this->tareaEstadoAccion === 'OMITIDA') {
            abort_unless(auth()->user()?->can('tareas.omitir'), 403);
            $this->validate(['tareaMotivoOmision' => 'required|string|min:5|max:500'], ['tareaMotivoOmision.required' => 'El motivo de omisión es obligatorio.', 'tareaMotivoOmision.min' => 'El motivo debe tener al menos 5 caracteres.']);
        }

        $tarea->update([
            'estado'          => $this->tareaEstadoAccion === 'REALIZADA' ? 'REALIZADA' : 'OMITIDA',
            'resultado'       => $this->tareaResultado ?: ($this->tareaEstadoAccion === 'REALIZADA' ? 'Completada conforme al plan.' : null),
            'motivo_omision'  => $this->tareaEstadoAccion === 'OMITIDA' ? $this->tareaMotivoOmision : null,
            'registrado_por'  => Auth::id(),
            'fecha_realizada' => $this->tareaEstadoAccion === 'REALIZADA' ? now() : null,
        ]);

        $this->modalTarea = false;
        $this->cargarAdulto($this->adultoMayor->cod_am);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Tarea actualizada', 'text' => 'Estado del plan de cuidados actualizado.']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 4. REGISTRAR SEGUIMIENTO / EVOLUCIÓN
    // ──────────────────────────────────────────────────────────────────────────

    public function abrirRegistrarSeguimiento(): void
    {
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($this->adultoMayor, Auth::user());
        $this->reset(['segObs', 'segIncidente', 'segRequiereMedico']);
        $this->segEstado = 'ESTABLE';
        $this->segAlimentacion = 'COMPLETA';
        $this->segMovilidad = 'INDEPENDIENTE';
        $this->segSueno = 'NORMAL';
        $this->modalSeguimiento = true;
    }

    public function guardarSeguimiento(): void
    {
        abort_unless(auth()->user()?->can('seguimiento.crear'), 403);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($this->adultoMayor, Auth::user());
        $this->validate([
            'segEstado' => 'required|in:ESTABLE,VIGILANCIA,DELICADO,CRITICO',
            'segAlimentacion' => 'required|in:COMPLETA,PARCIAL,RECHAZADA,AYUNO',
            'segMovilidad' => 'required|in:INDEPENDIENTE,ASISTIDA,SILLA_RUEDAS,ENCAMADO',
            'segSueno' => 'required|in:NORMAL,INTERRUMPIDO,INSOMNIO,SOMNOLENCIA',
            'segObs' => 'required|string|min:10|max:1000',
        ], [
            'segObs.required' => 'La nota de seguimiento es obligatoria.',
            'segObs.min'      => 'La nota de seguimiento debe tener al menos 10 caracteres.',
        ]);

        if ($this->segIncidente && mb_strlen(trim($this->segObs)) < 15) {
            $this->addError('segObs', 'Si reporta un incidente, detalle lo ocurrido con al menos 15 caracteres.');
            return;
        }

        if ($this->segRequiereMedico && mb_strlen(trim($this->segObs)) < 15) {
            $this->addError('segObs', 'Si requiere evaluación médica, detalle el motivo con al menos 15 caracteres.');
            return;
        }

        $turnoActivo = app(TurnoEnfermeriaService::class)->obtenerTurnoActivo(Auth::user());
        if (!$turnoActivo) {
            $this->addError('segObs', 'No existe un turno activo para registrar el seguimiento.');
            return;
        }

        if (SeguimientoDiario::where('cod_am', $this->adultoMayor->cod_am)
            ->whereDate('fecha', today())
            ->where('cod_turno', $turnoActivo->cod_turno)
            ->exists()) {
            $this->addError('segObs', 'Ya existe un seguimiento registrado para este residente en el turno actual.');
            return;
        }

        SeguimientoDiario::create([
            'cod_am'                  => $this->adultoMayor->cod_am,
            'cod_turno'               => $turnoActivo?->cod_turno,
            'fecha'                   => today()->toDateString(),
            'hora_inicio'             => now()->format('H:i:s'),
            'hora_fin'                => now()->addHours(1)->format('H:i:s'),
            'estado_general'          => $this->segEstado,
            'alimentacion'            => $this->segAlimentacion,
            'porcentaje_alimentacion' => 100,
            'movilidad'               => $this->segMovilidad,
            'sueno'                   => $this->segSueno,
            'incidente'               => $this->segIncidente,
            'requiere_medico'         => $this->segRequiereMedico,
            'observacion'             => $this->segObs,
            'registrado_por'          => Auth::id(),
        ]);

        $this->modalSeguimiento = false;
        $this->cargarAdulto($this->adultoMayor->cod_am);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Seguimiento guardado', 'text' => 'Nota de evolución clínica registrada.']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 5. REPORTAR INCIDENTE / ALERTA
    // ──────────────────────────────────────────────────────────────────────────

    public function abrirReportarIncidente(): void
    {
        abort_unless(auth()->user()?->can('alertas.crear'), 403);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($this->adultoMayor, Auth::user());
        $this->reset(['incidenteMotivo']);
        $this->incidenteTipo = 'INCIDENTE';
        $this->incidenteNivel = 'ALTO';
        $this->modalIncidente = true;
    }

    public function guardarIncidente(): void
    {
        abort_unless(auth()->user()?->can('alertas.crear'), 403);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($this->adultoMayor, Auth::user());

        $this->validate([
            'incidenteMotivo' => 'required|string|min:15|max:1000',
            'incidenteTipo'   => 'required|in:INCIDENTE,CAIDA,CONDUCTA,DOLOR_AGUDO,DESVIACION_CLINICA',
            'incidenteNivel'  => 'required|in:BAJO,MEDIO,ALTO,CRITICO',
        ], [
            'incidenteMotivo.required' => 'El detalle del incidente es obligatorio.',
            'incidenteMotivo.min'      => 'El detalle del incidente debe tener al menos 15 caracteres.',
        ]);

        $tipo = mb_strtoupper(trim($this->incidenteTipo));
        $duplicada = AlertaAdulto::where('cod_am', $this->adultoMayor->cod_am)
            ->where('tipo_alerta', $tipo)
            ->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])
            ->exists();

        if ($duplicada) {
            $this->addError('incidenteTipo', 'Ya existe una alerta activa para este incidente.');
            return;
        }

        $turnoActivo = app(TurnoEnfermeriaService::class)->obtenerTurnoActivo(Auth::user());

        AlertaAdulto::create([
            'cod_am'         => $this->adultoMayor->cod_am,
            'cod_turno'      => $turnoActivo?->cod_turno,
            'origen'         => 'INCIDENTE',
            'tipo_alerta'    => $tipo,
            'nivel'          => $this->incidenteNivel,
            'motivo'         => $this->incidenteMotivo,
            'responsable_id' => Auth::id(),
            'estado'         => 'ABIERTA',
        ]);

        $this->modalIncidente = false;
        $this->cargarAdulto($this->adultoMayor->cod_am);
        $this->dispatch('swal', ['icon' => 'warning', 'title' => 'Incidente reportado', 'text' => 'Se generó la alerta clínica correspondiente.']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 6. GESTIÓN DE ALERTAS (ATENDER / CERRAR)
    // ──────────────────────────────────────────────────────────────────────────

    public function abrirAtenderAlerta(string $codAlerta): void
    {
        abort_unless(auth()->user()?->can('alertas.atender'), 403);
        $alerta = AlertaAdulto::findOrFail($codAlerta);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($alerta->cod_am, Auth::user());

        $this->alertaAccionId = $codAlerta;
        $this->accionTomadaAlerta = '';
        $this->modalAtenderAlerta = true;
    }

    public function confirmarAtencionAlerta(): void
    {
        abort_unless(auth()->user()?->can('alertas.atender'), 403);
        $this->validate(
            ['accionTomadaAlerta' => 'required|string|min:5|max:1000'],
            ['accionTomadaAlerta.required' => 'La acción realizada es obligatoria.']
        );

        DB::transaction(function () {
            $alerta = AlertaAdulto::lockForUpdate()->findOrFail($this->alertaAccionId);
            app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($alerta->cod_am, Auth::user());
            abort_unless($alerta->estado === 'ABIERTA', 409, 'La alerta ya fue atendida o cerrada.');
            $alerta->update([
                'estado' => 'EN_ATENCION',
                'accion_tomada' => $this->accionTomadaAlerta,
                'fecha_atencion' => now(),
                'atendido_por' => Auth::id(),
            ]);
            $alerta->acciones()->create([
                'accion' => $this->accionTomadaAlerta,
                'responsable_id' => Auth::id(),
                'fecha_accion' => now(),
                'estado' => 'REALIZADA',
            ]);
        });

        $this->modalAtenderAlerta = false;
        $this->cargarAdulto($this->adultoMayor->cod_am);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Alerta en atención', 'text' => 'Acción registrada con éxito.']);
    }

    public function abrirCerrarAlerta(string $codAlerta): void
    {
        abort_unless(auth()->user()?->can('alertas.cerrar'), 403);
        $alerta = AlertaAdulto::findOrFail($codAlerta);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($alerta->cod_am, Auth::user());

        $this->alertaAccionId = $codAlerta;
        $this->observacionCierreAlerta = '';
        $this->modalCerrarAlerta = true;
    }

    public function confirmarCierreAlerta(): void
    {
        abort_unless(auth()->user()?->can('alertas.cerrar'), 403);
        $alerta = AlertaAdulto::findOrFail($this->alertaAccionId);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($alerta->cod_am, Auth::user());
        abort_unless($alerta->puedeCerrarse(), 409, 'La alerta ya está cerrada.');

        $this->validate([
            'observacionCierreAlerta' => 'required|string|min:5|max:1000',
        ], [
            'observacionCierreAlerta.required' => 'La nota de cierre es obligatoria.',
            'observacionCierreAlerta.min'      => 'La nota de cierre debe tener al menos 5 caracteres.',
        ]);

        DB::transaction(function () use ($alerta) {
            $alerta->update([
                'estado'             => 'CERRADA',
                'observacion_cierre' => $this->observacionCierreAlerta,
                'fecha_cierre'       => now(),
                'cerrado_por'        => Auth::id(),
            ]);

            $alerta->acciones()->create([
                'accion' => 'Cierre: ' . $this->observacionCierreAlerta,
                'responsable_id' => Auth::id(),
                'fecha_accion' => now(),
                'estado' => 'REALIZADA',
            ]);
        });

        $this->modalCerrarAlerta = false;
        $this->cargarAdulto($this->adultoMayor->cod_am);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Alerta cerrada', 'text' => 'La alerta fue cerrada con trazabilidad registrada.']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 7. LÓGICA DE HISTORIAL CLÍNICO CRONOLÓGICO INTEGRADO 360°
    // ──────────────────────────────────────────────────────────────────────────

    private function resolverTimestamp($fecha, $hora = null, $createdAt = null): Carbon
    {
        if (!empty($fecha)) {
            $fStr = $fecha instanceof Carbon ? $fecha->format('Y-m-d') : (string) $fecha;
            $hStr = '00:00:00';
            if ($hora instanceof Carbon) {
                $hStr = $hora->format('H:i:s');
            } elseif (is_string($hora) && !empty($hora)) {
                if (strlen($hora) >= 10 && str_contains($hora, '-')) {
                    return Carbon::parse($hora);
                }
                $hStr = strlen($hora) === 5 ? $hora . ':00' : substr($hora, 0, 8);
            }
            if (strlen($fStr) >= 10) {
                return Carbon::parse(substr($fStr, 0, 10) . ' ' . $hStr);
            }
        }

        if ($createdAt instanceof Carbon) {
            return $createdAt;
        }
        if (is_string($createdAt) && !empty($createdAt)) {
            return Carbon::parse($createdAt);
        }

        return now();
    }

    public function getHistorialCronologicoProperty()
    {
        $eventos = collect();

        // 1. Pases de Turno
        foreach ($this->adultoMayor->pasesTurno as $pase) {
            $saliente = $pase->turnoSaliente->nombre ?? 'Turno Saliente';
            $entrante = $pase->turnoEntrante->nombre ?? 'Turno Entrante';
            $resumenPase = "Relevo: {$saliente} → {$entrante}";
            if ($pase->requiere_vigilancia_especial) {
                $resumenPase .= ' · Vigilancia especial: ' . ($pase->motivo_vigilancia ?: 'Indicada');
            } elseif ($pase->resumen_turno) {
                $resumenPase .= ' · ' . $pase->resumen_turno;
            }

            $eventos->push([
                'tipo' => 'PASE_TURNO',
                'tipo_label' => 'Pase de turno',
                'titulo' => 'Pase de Turno (' . $saliente . ' → ' . $entrante . ')',
                'resumen' => $resumenPase,
                'descripcion' => $pase->resumen_turno ?: 'Relevo de guardia efectuado.',
                'fecha' => $pase->fecha ? Carbon::parse($pase->fecha)->format('Y-m-d') : today()->toDateString(),
                'hora' => $pase->created_at ? $pase->created_at->format('H:i') : 'Turno',
                'timestamp' => $this->resolverTimestamp($pase->fecha, null, $pase->created_at),
                'responsable' => $pase->enfermeroSaliente->name ?? 'Enfermero/a',
                'estado_badge' => $pase->estado === 'RECIBIDO' ? 'CONFIRMADO' : 'ENTREGADO',
                'badge' => $pase->estado === 'RECIBIDO' ? 'RECEPCIÓN CONFIRMADA' : 'ENTREGADO',
                'badge_color' => 'indigo',
                'icon' => 'ph-arrows-left-right',
                'es_incidente' => (bool) $pase->requiere_vigilancia_especial,
            ]);
        }

        // 2. Alertas Clínicas
        foreach ($this->adultoMayor->alertas as $alerta) {
            $resumenAlerta = ($alerta->descripcion ?: ($alerta->motivo ?: 'Alerta clínica detectada'));
            if ($alerta->accion_tomada) {
                $resumenAlerta .= ' · Acción: ' . $alerta->accion_tomada;
            }
            if ($alerta->estado === 'CERRADA' && $alerta->observacion_cierre) {
                $resumenAlerta .= ' · Cierre: ' . $alerta->observacion_cierre;
            }

            $esIncidente = in_array($alerta->nivel, ['CRITICO', 'ALTO'])
                || str_contains(strtoupper($alerta->tipo_alerta), 'INCIDENTE')
                || str_contains(strtoupper($alerta->tipo_alerta), 'CAIDA');

            $eventos->push([
                'tipo' => 'ALERTA',
                'tipo_label' => 'Alerta clínica',
                'titulo' => 'Alerta: ' . $alerta->tipo_alerta . ' [' . $alerta->nivel . ']',
                'resumen' => $resumenAlerta,
                'descripcion' => $alerta->motivo . ($alerta->accion_tomada ? ' — Atención: ' . $alerta->accion_tomada : ''),
                'fecha' => $alerta->created_at ? $alerta->created_at->format('Y-m-d') : today()->toDateString(),
                'hora' => $alerta->created_at ? $alerta->created_at->format('H:i') : '',
                'timestamp' => $alerta->created_at,
                'responsable' => $alerta->responsable->name ?? ($alerta->atendidoPor->name ?? 'Sistema Clínico'),
                'estado_badge' => $alerta->estado === 'CERRADA' ? 'CERRADA' : ($alerta->estado === 'EN_ATENCION' ? 'EN ATENCIÓN' : 'ABIERTA'),
                'badge' => $alerta->estado,
                'badge_color' => $alerta->estado === 'CERRADA' ? 'zinc' : ($alerta->nivel === 'CRITICO' ? 'red' : 'amber'),
                'icon' => 'ph-bell-ringing',
                'es_incidente' => $esIncidente,
            ]);
        }

        // 3. Signos Vitales
        foreach ($this->adultoMayor->signosVitales as $signo) {
            $pa = $signo->presion_arterial ? "PA {$signo->presion_arterial}" : '';
            $fc = $signo->frecuencia_cardiaca ? "FC {$signo->frecuencia_cardiaca}" : '';
            $sat = $signo->saturacion ? "SpO2 {$signo->saturacion}%" : '';
            $temp = $signo->temperatura ? "Temp {$signo->temperatura}°C" : '';
            $glu = $signo->glucosa ? "Gluc {$signo->glucosa} mg/dL" : '';
            $resumenSignos = implode(' · ', array_filter([$pa, $fc, $sat, $temp, $glu]));

            $eventos->push([
                'tipo' => 'SIGNOS',
                'tipo_label' => 'Signos vitales',
                'titulo' => 'Control de Signos Vitales',
                'resumen' => $resumenSignos ?: 'Control hemodinámico registrado',
                'descripcion' => $resumenSignos ?: 'Registro de control hemodinámico',
                'fecha' => $signo->fecha ? Carbon::parse($signo->fecha)->format('Y-m-d') : today()->toDateString(),
                'hora' => $signo->hora ? substr($signo->hora, 0, 5) : ($signo->created_at ? $signo->created_at->format('H:i') : ''),
                'timestamp' => $this->resolverTimestamp($signo->fecha, $signo->hora, $signo->created_at),
                'responsable' => $signo->profesional->name ?? 'Enfermero/a',
                'estado_badge' => 'NORMAL',
                'badge' => 'SIGNOS VITALES',
                'badge_color' => 'emerald',
                'icon' => 'ph-heartbeat',
                'es_incidente' => false,
            ]);
        }

        // 4. Medicaciones y Administraciones
        foreach ($this->adultoMayor->administracionesMedicacion as $admin) {
            $adminOk = (bool) $admin->administrado;
            $medNombre = $admin->medicacion->nombre_medicamento ?? 'Fármaco prescrito';
            $dosis = $admin->medicacion?->dosis ? " {$admin->medicacion->dosis}" : '';
            $resumenMed = $adminOk
                ? "{$medNombre}{$dosis} administrado" . ($admin->hora_real ? " a las " . substr($admin->hora_real, 0, 5) : '')
                : "Omisión: " . ($admin->motivo_omision ?: 'Rechazo / no suministrado');

            $eventos->push([
                'tipo' => 'MEDICACION',
                'tipo_label' => 'Medicación',
                'titulo' => ($adminOk ? 'Medicación Administrada: ' : 'Medicación Omitida: ') . $medNombre,
                'resumen' => $resumenMed,
                'descripcion' => $resumenMed,
                'fecha' => $admin->fecha ? Carbon::parse($admin->fecha)->format('Y-m-d') : today()->toDateString(),
                'hora' => ($admin->hora_real ? ($admin->hora_real instanceof Carbon ? $admin->hora_real->format('H:i') : (strlen($admin->hora_real) >= 10 ? Carbon::parse($admin->hora_real)->format('H:i') : substr($admin->hora_real, 0, 5))) : ($admin->hora_programada ? ($admin->hora_programada instanceof Carbon ? $admin->hora_programada->format('H:i') : (strlen($admin->hora_programada) >= 10 ? Carbon::parse($admin->hora_programada)->format('H:i') : substr($admin->hora_programada, 0, 5))) : '')),
                'timestamp' => $this->resolverTimestamp($admin->fecha, $admin->hora_real ?: $admin->hora_programada, $admin->created_at),
                'responsable' => $admin->enfermero->name ?? 'Enfermero/a',
                'estado_badge' => $adminOk ? 'ADMINISTRADA' : 'OMITIDA',
                'badge' => $adminOk ? 'ADMINISTRADA' : 'OMITIDA',
                'badge_color' => $adminOk ? 'teal' : 'amber',
                'icon' => 'ph-pill',
                'es_incidente' => !$adminOk,
            ]);
        }

        // 5. Tareas de Planes de Cuidados
        $tareasHistorial = TareaPlanCuidado::where('cod_am', $this->adultoMayor->cod_am)
            ->whereIn('estado', ['REALIZADA', 'OMITIDA'])
            ->with(['responsable', 'registradoPor'])
            ->orderByDesc('fecha_realizada')
            ->orderByDesc('fecha_programada')
            ->take(40)
            ->get();

        foreach ($tareasHistorial as $tarea) {
            $resumenTarea = $tarea->estado === 'REALIZADA'
                ? "{$tarea->titulo} realizada" . ($tarea->resultado ? " · Resultado: {$tarea->resultado}" : '')
                : "Omisión: {$tarea->titulo} · " . ($tarea->motivo_omision ?: 'No ejecutada');

            $eventos->push([
                'tipo' => 'TAREA',
                'tipo_label' => 'Cuidados',
                'titulo' => "Tarea [{$tarea->area}]: {$tarea->titulo}",
                'resumen' => $resumenTarea,
                'descripcion' => ($tarea->resultado ?: 'Control ejecutado.') . ($tarea->motivo_omision ? " (Motivo omisión: {$tarea->motivo_omision})" : ''),
                'fecha' => ($tarea->fecha_realizada ?: $tarea->fecha_programada) ? Carbon::parse($tarea->fecha_realizada ?: $tarea->fecha_programada)->format('Y-m-d') : today()->toDateString(),
                'hora' => $tarea->hora_programada ? substr($tarea->hora_programada, 0, 5) : '',
                'timestamp' => $this->resolverTimestamp($tarea->fecha_realizada ?: $tarea->fecha_programada, $tarea->hora_programada, $tarea->updated_at),
                'responsable' => $tarea->responsable->name ?? ($tarea->registradoPor->name ?? 'Enfermero/a'),
                'estado_badge' => $tarea->estado,
                'badge' => $tarea->estado,
                'badge_color' => $tarea->estado === 'REALIZADA' ? 'blue' : 'rose',
                'icon' => 'ph-check-circle',
                'es_incidente' => $tarea->estado === 'OMITIDA',
            ]);
        }

        // 6. Seguimientos Diarios
        foreach ($this->adultoMayor->seguimientosDiarios as $seg) {
            $estadoGen = ucfirst(strtolower(str_replace('_', ' ', $seg->estado_general ?? 'Estable')));
            $alim = $seg->alimentacion ? 'apetito ' . strtolower($seg->alimentacion) : 'apetito conservado';
            $incidenteTxt = $seg->incidente
                ? 'Incidente reportado: ' . ($seg->observacion ?: 'Caída o desorientación')
                : 'sin incidente';
            $resumenSeg = "{$estadoGen}, {$alim}, {$incidenteTxt}";

            $eventos->push([
                'tipo' => 'SEGUIMIENTO',
                'tipo_label' => 'Seguimiento',
                'titulo' => 'Evolución de Enfermería' . ($seg->incidente ? ' [INCIDENTE]' : '') . ($seg->requiere_medico ? ' [REVISIÓN MÉDICA]' : ''),
                'resumen' => $resumenSeg,
                'descripcion' => $seg->observacion ?: 'Seguimiento registrado en guardia',
                'fecha' => $seg->fecha ? Carbon::parse($seg->fecha)->format('Y-m-d') : today()->toDateString(),
                'hora' => $seg->hora_inicio ? substr($seg->hora_inicio, 0, 5) : ($seg->created_at ? $seg->created_at->format('H:i') : ''),
                'timestamp' => $this->resolverTimestamp($seg->fecha, $seg->hora_inicio, $seg->created_at),
                'responsable' => $seg->enfermero->name ?? 'Enfermero/a',
                'estado_badge' => $seg->incidente ? 'INCIDENTE' : ($seg->requiere_medico ? 'REVISIÓN MÉDICA' : 'REGISTRADO'),
                'badge' => $seg->incidente ? 'INCIDENTE' : 'EVOLUCION',
                'badge_color' => $seg->incidente ? 'red' : 'purple',
                'icon' => 'ph-notebook',
                'es_incidente' => (bool) $seg->incidente,
            ]);
        }

        // 7. Evaluaciones y Valoraciones
        foreach ($this->adultoMayor->evaluacionesGeriatricas as $eval) {
            $resumenEval = $eval->diagnostico_principal ?: ($eval->observaciones ?: 'Evaluación geriátrica clínica');

            $eventos->push([
                'tipo' => 'VALORACION',
                'tipo_label' => 'Valoraciones',
                'titulo' => 'Evaluación Geriátrica Inicial',
                'resumen' => $resumenEval,
                'descripcion' => $eval->diagnostico_principal ?: ($eval->observaciones ?: 'Valoración clínica de ingreso'),
                'fecha' => $eval->fecha_evaluacion ? Carbon::parse($eval->fecha_evaluacion)->format('Y-m-d') : today()->toDateString(),
                'hora' => '10:00',
                'timestamp' => $this->resolverTimestamp($eval->fecha_evaluacion, null, $eval->created_at),
                'responsable' => $eval->evaluador->name ?? 'Evaluador/a',
                'estado_badge' => 'COMPLETADA',
                'badge' => 'VALORACION MEDICA',
                'badge_color' => 'cyan',
                'icon' => 'ph-clipboard-text',
                'es_incidente' => false,
            ]);
        }

        foreach ($this->adultoMayor->valoracionesFuncionales as $val) {
            $resumenVal = "Barthel: {$val->barthel_total}/100 · Nivel: {$val->nivel_dependencia}";
            if ($val->riesgo_caida) {
                $resumenVal .= " · Riesgo caída: {$val->riesgo_caida}";
            }

            $eventos->push([
                'tipo' => 'VALORACION',
                'tipo_label' => 'Valoraciones',
                'titulo' => "Valoración Funcional (Barthel: {$val->barthel_total}, Katz: {$val->katz_total})",
                'resumen' => $resumenVal,
                'descripcion' => "Dependencia: {$val->nivel_dependencia} | Riesgo caída: {$val->riesgo_caida}",
                'fecha' => $val->fecha_valoracion ? Carbon::parse($val->fecha_valoracion)->format('Y-m-d') : today()->toDateString(),
                'hora' => '11:00',
                'timestamp' => $this->resolverTimestamp($val->fecha_valoracion, null, $val->created_at),
                'responsable' => $val->registradoPor->name ?? ($val->evaluador->name ?? 'Evaluador/a') ?? 'Evaluador/a',
                'estado_badge' => 'COMPLETADA',
                'badge' => 'VALORACION FUNCIONAL',
                'badge_color' => 'sky',
                'icon' => 'ph-gauge',
                'es_incidente' => false,
            ]);
        }

        return $eventos->sortByDesc(fn ($e) => $e['timestamp'] ? Carbon::parse($e['timestamp'])->timestamp : 0)->values();
    }

    public function getHistorialFiltradoProperty()
    {
        $cronologia = $this->historialCronologico;

        if ($this->historialFiltroTipo !== 'TODOS') {
            $cronologia = $cronologia->filter(function ($evento) {
                if ($this->historialFiltroTipo === 'INCIDENTES') {
                    return !empty($evento['es_incidente']);
                }
                if ($this->historialFiltroTipo === 'CUIDADOS') {
                    return in_array($evento['tipo'], ['TAREA', 'CUIDADOS']);
                }
                if ($this->historialFiltroTipo === 'PASES') {
                    return $evento['tipo'] === 'PASE_TURNO';
                }
                if ($this->historialFiltroTipo === 'VALORACIONES') {
                    return $evento['tipo'] === 'VALORACION';
                }
                if ($this->historialFiltroTipo === 'ALERTAS') {
                    return $evento['tipo'] === 'ALERTA';
                }
                return $evento['tipo'] === $this->historialFiltroTipo;
            });
        }

        if ($this->historialFechaDesde) {
            $desde = Carbon::parse($this->historialFechaDesde)->startOfDay();
            $cronologia = $cronologia->filter(fn ($e) => Carbon::parse($e['timestamp'])->gte($desde));
        }

        if ($this->historialFechaHasta) {
            $hasta = Carbon::parse($this->historialFechaHasta)->endOfDay();
            $cronologia = $cronologia->filter(fn ($e) => Carbon::parse($e['timestamp'])->lte($hasta));
        }

        return $cronologia->values();
    }

    public function getResumenLongitudinalProperty(): array
    {
        // 1. Alertas
        $alertasTotal = $this->adultoMayor->alertas->count();
        $alertasActivas = $this->adultoMayor->alertas->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])->count();
        $alertasCerradas = $this->adultoMayor->alertas->where('estado', 'CERRADA')->count();

        // 2. Adherencia medicación
        $totalAdmin = $this->adultoMayor->administracionesMedicacion->count();
        $adminOk = $this->adultoMayor->administracionesMedicacion->where('administrado', true)->count();
        $adminOmitidas = $totalAdmin - $adminOk;
        $adherenciaPct = $totalAdmin > 0 ? (int) round(($adminOk / $totalAdmin) * 100) : 100;

        // 3. Cumplimiento cuidados
        $totalTareas = TareaPlanCuidado::where('cod_am', $this->adultoMayor->cod_am)->count();
        $tareasRealizadas = TareaPlanCuidado::where('cod_am', $this->adultoMayor->cod_am)->where('estado', 'REALIZADA')->count();
        $cumplimientoPct = $totalTareas > 0 ? (int) round(($tareasRealizadas / $totalTareas) * 100) : 100;

        // 4. Seguimientos
        $totalSeguimientos = $this->adultoMayor->seguimientosDiarios->count();
        $totalIncidentes = $this->adultoMayor->seguimientosDiarios->where('incidente', true)->count();

        // 5. Última valoración oficial
        $ultimaFuncional = $this->adultoMayor->valoracionesFuncionales->sortByDesc('fecha_valoracion')->first();
        $ultimaMedica = $this->adultoMayor->valoracionesMedicas->sortByDesc('created_at')->first();
        $ultimaEnfermeria = $this->adultoMayor->valoracionesEnfermeria->sortByDesc('fecha_valoracion')->first();

        $ultimaValoracion = null;
        if ($ultimaFuncional) {
            $ultimaValoracion = [
                'instrumento' => 'Índice de Barthel',
                'resultado' => ($ultimaFuncional->indice_barthel ?? $ultimaFuncional->barthel_total ?? 90) . '/100 (' . ($ultimaFuncional->nivel_dependencia ?? 'Dependencia moderada') . ')',
                'fecha' => $ultimaFuncional->fecha_valoracion ? Carbon::parse($ultimaFuncional->fecha_valoracion)->format('d/m/Y') : 'Reciente',
                'evaluador' => $ultimaFuncional->evaluador->name ?? 'Equipo Asistencial',
            ];
        } elseif ($ultimaMedica) {
            $ultimaValoracion = [
                'instrumento' => 'Ficha Médica',
                'resultado' => $ultimaMedica->observacion_medica ? (strlen($ultimaMedica->observacion_medica) > 40 ? substr($ultimaMedica->observacion_medica, 0, 40) . '...' : $ultimaMedica->observacion_medica) : 'Ficha médica registrada',
                'fecha' => $ultimaMedica->created_at ? $ultimaMedica->created_at->format('d/m/Y') : ($ultimaMedica->fecha ? Carbon::parse($ultimaMedica->fecha)->format('d/m/Y') : 'Reciente'),
                'evaluador' => $ultimaMedica->registrador->name ?? ($ultimaMedica->medico->name ?? 'Equipo Médico'),
            ];
        } elseif ($ultimaEnfermeria) {
            $ultimaValoracion = [
                'instrumento' => 'Valoración de Enfermería',
                'resultado' => 'Cribado inicial completado',
                'fecha' => $ultimaEnfermeria->fecha_valoracion ? Carbon::parse($ultimaEnfermeria->fecha_valoracion)->format('d/m/Y') : 'Reciente',
                'evaluador' => $ultimaEnfermeria->enfermero->name ?? 'Enfermería',
            ];
        }

        // 6. Tendencia longitudinal de signos vitales (hasta 15 registros reales en orden cronológico)
        $signosOrdenados = $this->adultoMayor->signosVitales
            ->sortBy(fn ($s) => ($s->fecha ?: '2000-01-01') . ' ' . ($s->hora ?: '00:00'))
            ->values();

        $labels = [];
        $sistolica = [];
        $diastolica = [];
        $fc = [];
        $spo2 = [];
        $temp = [];
        $glucosa = [];

        foreach ($signosOrdenados->take(-12) as $s) {
            $fechaFmt = $s->fecha ? Carbon::parse($s->fecha)->format('d/m') : '';
            $horaFmt = $s->hora ? substr($s->hora, 0, 5) : '';
            $labels[] = trim("{$fechaFmt} {$horaFmt}");

            $pas = null;
            $pad = null;
            if ($s->presion_arterial && str_contains($s->presion_arterial, '/')) {
                $partes = explode('/', $s->presion_arterial);
                $pas = is_numeric(trim($partes[0])) ? (float) trim($partes[0]) : null;
                $pad = is_numeric(trim($partes[1])) ? (float) trim($partes[1]) : null;
            }
            $sistolica[] = $pas;
            $diastolica[] = $pad;
            $fc[] = $s->frecuencia_cardiaca ? (float) $s->frecuencia_cardiaca : null;
            $spo2[] = $s->saturacion ? (float) $s->saturacion : null;
            $temp[] = $s->temperatura ? (float) $s->temperatura : null;
            $glucosa[] = $s->glucosa ? (float) $s->glucosa : null;
        }

        $ultimoSigno = $signosOrdenados->last();

        return [
            'alertas_total' => $alertasTotal,
            'alertas_activas' => $alertasActivas,
            'alertas_cerradas' => $alertasCerradas,
            'total_admin' => $totalAdmin,
            'admin_ok' => $adminOk,
            'admin_omitidas' => $adminOmitidas,
            'adherencia_pct' => $adherenciaPct,
            'total_tareas' => $totalTareas,
            'tareas_realizadas' => $tareasRealizadas,
            'cumplimiento_pct' => $cumplimientoPct,
            'total_seguimientos' => $totalSeguimientos,
            'total_incidentes' => $totalIncidentes,
            'ultima_valoracion' => $ultimaValoracion,
            'grafica_signos' => [
                'labels' => $labels,
                'sistolica' => $sistolica,
                'diastolica' => $diastolica,
                'fc' => $fc,
                'spo2' => $spo2,
                'temp' => $temp,
                'glucosa' => $glucosa,
                'ultimo' => $ultimoSigno,
            ],
        ];
    }

    public function dehydrate(): void
    {
        if (isset($this->adultoMayor)) {
            $this->adultoMayor->withoutRelations();
        }
    }

    public function render()
    {
        if (isset($this->adultoMayor)) {
            $this->adultoMayor->loadMissing([
                'habitacion', 'cama',
                'asignacionTurnoActiva.turno',
                'asignacionTurnoActiva.enfermero',
                'planCuidadoActivo.tareas',
                'valoracionesEnfermeria' => fn($q) => $q->orderByDesc('fecha_valoracion')->orderByDesc('hora_valoracion')->take(10),
                'valoracionesMedicas' => fn($q) => $q->orderByDesc('created_at')->take(10),
                'signosVitales' => fn($q) => $q->orderByDesc('fecha')->orderByDesc('hora')->take(20),
                'medicaciones' => fn($q) => $q->whereIn('estado', ['ACTIVA', 'ACTIVO']),
                'administracionesMedicacion' => fn($q) => $q->with(['medicacion', 'registrador'])->orderByDesc('fecha')->orderByDesc('hora_programada')->take(30),
                'tareasActuales' => fn($q) => $q->with('turno')->orderByDesc('fecha_programada')->orderByDesc('hora_programada')->take(25),
                'alertas' => fn($q) => $q->with(['acciones', 'responsable'])->orderByDesc('created_at')->take(25),
                'seguimientosDiarios' => fn($q) => $q->with('turno')->orderByDesc('fecha')->orderByDesc('hora_inicio')->take(25),
                'pasesTurno' => fn($q) => $q->with(['enfermeroSaliente', 'enfermeroEntrante', 'turnoSaliente', 'turnoEntrante'])->orderByDesc('fecha')->take(15),
                'evaluacionesGeriatricas.evaluador',
                'valoracionesFuncionales.registradoPor',
            ]);
        }

        return view('livewire.cuidados.ficha-paciente', [
            'agendaMedicacion' => isset($this->adultoMayor)
                ? app(AgendaMedicacionService::class)->paraAdulto($this->adultoMayor->cod_am)
                : collect(),
        ])->layout('layouts.sistema');
    }
}
