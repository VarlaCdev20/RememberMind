<?php

namespace App\Livewire\Admin\TurnosAsignaciones;

use App\Exports\AsignacionesPorAreaExport;
use App\Exports\TurnosAsignacionesExport;
use App\Exports\UsuariosSinTurnoExport;
use App\Models\AreaInstitucional;
use App\Models\AsignacionTurno;
use App\Models\HorarioPersonalAdmin;
use App\Models\HorarioPersonalSalud;
use App\Models\PersonalAdmin;
use App\Models\PersonalSalud;
use App\Models\TurnoInstitucional;
use App\Models\User;
use App\Services\Reports\ReportExportService;
use App\Services\Reports\ReportFileNameService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TurnosAsignacionesPanel extends Component
{
    private const DIAS = ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO', 'DOMINGO'];

    private const DIA_LABELS = [
        'LUNES' => 'Lunes',
        'MARTES' => 'Martes',
        'MIERCOLES' => 'Miercoles',
        'JUEVES' => 'Jueves',
        'VIERNES' => 'Viernes',
        'SABADO' => 'Sabado',
        'DOMINGO' => 'Domingo',
    ];

    public string $tabActiva = 'resumen';
    public string $modoCalendario = 'semana';
    public string $fechaCalendario = '';

    public $search = '';
    public $filtroArea = '';
    public $filtroTurno = '';
    public $filtroEstado = '';
    public $filtroDia = '';
    public $filtroTipo = '';
    public $filtroTipoPersonal = '';

    public $horarioSearch = '';
    public $horarioTipoPersonal = '';
    public $horarioDia = '';
    public $horarioTurno = '';
    public $horarioEstado = '';

    public $vistaActual = 'calendario';
    public $mostrarFormularioHorario = false;
    public $mostrarFormularioTurno = false;
    public $mostrarFormularioAsignacion = false;
    public $mostrarFichaAsignacion = false;
    public $mostrarReportes = false;
    public $isEdit = false;

    public $turnoId;
    public $asignacionId;
    public $horarioId;
    public $horarioTipoEdicion = '';

    public $horario_tipo_personal = 'admin';
    public $horario_personal_id = '';
    public $horario_dias_semana = [];
    public $horario_hora_inicio = '';
    public $horario_hora_fin = '';
    public $horario_turno = 'Mañana';
    public $horario_estado = 'ACTIVO';
    public $horario_observaciones = '';

    public $turno_nombre;
    public $turno_hora_inicio;
    public $turno_hora_fin;
    public $turno_descripcion;
    public $turno_color = '#3B82F6';
    public $turno_estado = 'ACTIVO';
    public $turno_observaciones;

    public $asig_cod_usu;
    public $asig_cod_area;
    public $asig_cod_turno;
    public $asig_dias_semana = [];
    public $asig_fecha_inicio;
    public $asig_fecha_fin;
    public $asig_tipo_asignacion = 'REGULAR';
    public $asig_estado = 'ACTIVA';
    public $asig_observaciones;
    public $asig_apoyo_temporal = false;

    public $asignacionSeleccionada = null;
    public ?array $detalleEvento = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'filtroArea' => ['except' => ''],
        'filtroTurno' => ['except' => ''],
        'filtroEstado' => ['except' => ''],
        'filtroDia' => ['except' => ''],
        'filtroTipo' => ['except' => ''],
        'vistaActual' => ['except' => 'calendario'],
        'tabActiva' => ['except' => 'resumen'],
        'modoCalendario' => ['except' => 'semana'],
        'fechaCalendario' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->asig_fecha_inicio = now()->format('Y-m-d');
        $this->fechaCalendario = $this->fechaCalendario ?: now()->format('Y-m-d');
    }

    public function render()
    {
        $areasDisponibles = AreaInstitucional::activas()->orderBy('nombre')->get();
        $turnosDisponibles = TurnoInstitucional::activos()->orderBy('nombre')->get();
        $turnosLista = TurnoInstitucional::orderBy('nombre')->get();
        $usuariosDisponibles = $this->usuariosActivosQuery()
            ->with(['areaInstitucional', 'personalAdmin.cargoAdmin', 'personalSalud.especialidad'])
            ->orderBy('nombres')
            ->get();

        $asignaciones = $this->asignacionesFiltradasQuery()
            ->orderByRaw("CASE WHEN estado = 'ACTIVA' THEN 0 WHEN estado = 'INACTIVA' THEN 1 ELSE 2 END")
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        $horarios = $this->horariosColeccion();
        $alertas = $this->alertasOperativas($usuariosDisponibles, $asignaciones, $horarios, $areasDisponibles);
        $calendario = $this->calendarioData($asignaciones, $horarios);
        $metricas = $this->metricasOperativas($usuariosDisponibles, $asignaciones, $horarios, $turnosLista, $areasDisponibles, $alertas);

        return view('livewire.admin.turnos-asignaciones.turnos-asignaciones-panel', [
            'asignaciones' => $asignaciones,
            'areasDisponibles' => $areasDisponibles,
            'turnosDisponibles' => $turnosDisponibles,
            'usuariosDisponibles' => $usuariosDisponibles,
            'personalHorarioOpciones' => $this->personalHorarioOpciones($this->horario_tipo_personal),
            'horarios' => $horarios,
            'metricas' => $metricas,
            'alertas' => $alertas,
            'calendario' => $calendario,
            'proximasAsignaciones' => $this->proximasAsignaciones($asignaciones),
            'asignacionesHoyLista' => $this->asignacionesHoyLista($asignaciones),
            'resumenSemanal' => $this->resumenSemanal($asignaciones, $horarios),
            'distribucionAreas' => $this->distribucionAreas($areasDisponibles),
            'distribucionTurnos' => $this->distribucionTurnos($turnosLista),
            'turnosLista' => $turnosLista,
            'diasSemana' => self::DIAS,
            'diaLabels' => self::DIA_LABELS,
            'tabActiva' => $this->tabActiva,
            'modoCalendario' => $this->modoCalendario,
            'fechaCalendario' => $this->fechaCalendario,
            'mostrarFormularioHorario' => $this->mostrarFormularioHorario,
            'mostrarFormularioTurno' => $this->mostrarFormularioTurno,
            'mostrarFormularioAsignacion' => $this->mostrarFormularioAsignacion,
            'mostrarFichaAsignacion' => $this->mostrarFichaAsignacion,
            'mostrarReportes' => $this->mostrarReportes,
            'isEdit' => $this->isEdit,
            'asignacionSeleccionada' => $this->asignacionSeleccionada,
            'detalleEvento' => $this->detalleEvento,
            'horario_tipo_personal' => $this->horario_tipo_personal,
            'horario_personal_id' => $this->horario_personal_id,
            'horario_dias_semana' => $this->horario_dias_semana,
            'horario_hora_inicio' => $this->horario_hora_inicio,
            'horario_hora_fin' => $this->horario_hora_fin,
            'horario_turno' => $this->horario_turno,
            'horario_estado' => $this->horario_estado,
            'horario_observaciones' => $this->horario_observaciones,
            'turno_nombre' => $this->turno_nombre,
            'turno_hora_inicio' => $this->turno_hora_inicio,
            'turno_hora_fin' => $this->turno_hora_fin,
            'turno_descripcion' => $this->turno_descripcion,
            'turno_color' => $this->turno_color,
            'turno_estado' => $this->turno_estado,
            'turno_observaciones' => $this->turno_observaciones,
            'asig_cod_usu' => $this->asig_cod_usu,
            'asig_cod_area' => $this->asig_cod_area,
            'asig_cod_turno' => $this->asig_cod_turno,
            'asig_dias_semana' => $this->asig_dias_semana,
            'asig_fecha_inicio' => $this->asig_fecha_inicio,
            'asig_fecha_fin' => $this->asig_fecha_fin,
            'asig_tipo_asignacion' => $this->asig_tipo_asignacion,
            'asig_estado' => $this->asig_estado,
            'asig_observaciones' => $this->asig_observaciones,
            'asig_apoyo_temporal' => $this->asig_apoyo_temporal,
        ]);
    }

    public function cambiarTab(string $tab): void
    {
        if (in_array($tab, ['resumen', 'horarios', 'turnos', 'asignaciones', 'calendario', 'alertas', 'reportes'], true)) {
            $this->tabActiva = $tab;
            $this->detalleEvento = null;
        }
    }

    public function cambiarVista($vista): void
    {
        $this->vistaActual = $vista;
    }

    public function cambiarModoCalendario(string $modo): void
    {
        if (in_array($modo, ['dia', 'semana', 'mes'], true)) {
            $this->modoCalendario = $modo;
            $this->detalleEvento = null;
        }
    }

    public function calendarioAnterior(): void
    {
        $fecha = Carbon::parse($this->fechaCalendario ?: now());
        $this->fechaCalendario = match ($this->modoCalendario) {
            'dia' => $fecha->subDay()->format('Y-m-d'),
            'mes' => $fecha->subMonthNoOverflow()->format('Y-m-d'),
            default => $fecha->subWeek()->format('Y-m-d'),
        };
    }

    public function calendarioSiguiente(): void
    {
        $fecha = Carbon::parse($this->fechaCalendario ?: now());
        $this->fechaCalendario = match ($this->modoCalendario) {
            'dia' => $fecha->addDay()->format('Y-m-d'),
            'mes' => $fecha->addMonthNoOverflow()->format('Y-m-d'),
            default => $fecha->addWeek()->format('Y-m-d'),
        };
    }

    public function calendarioHoy(): void
    {
        $this->fechaCalendario = now()->format('Y-m-d');
        $this->detalleEvento = null;
    }

    public function limpiarFiltros(): void
    {
        $this->search = '';
        $this->filtroArea = '';
        $this->filtroTurno = '';
        $this->filtroEstado = '';
        $this->filtroDia = '';
        $this->filtroTipo = '';
        $this->filtroTipoPersonal = '';
    }

    public function limpiarFiltrosHorario(): void
    {
        $this->horarioSearch = '';
        $this->horarioTipoPersonal = '';
        $this->horarioDia = '';
        $this->horarioTurno = '';
        $this->horarioEstado = '';
    }

    public function updatedHorarioTipoPersonal(): void
    {
        $this->horario_personal_id = '';
    }

    public function abrirModalHorario(): void
    {
        if (! $this->puedeGestionarHorarios()) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para gestionar horarios.']);
            return;
        }

        $this->resetErrorBag();
        $this->resetFormHorario();
        $this->isEdit = false;
        $this->mostrarFormularioHorario = true;
    }

    public function cargarHorario(string $tipo, int $id): void
    {
        if (! $this->puedeGestionarHorarios()) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para editar horarios.']);
            return;
        }

        $this->resetErrorBag();
        $horario = $tipo === 'salud'
            ? HorarioPersonalSalud::findOrFail($id)
            : HorarioPersonalAdmin::findOrFail($id);

        $this->horarioId = $id;
        $this->horarioTipoEdicion = $tipo;
        $this->horario_tipo_personal = $tipo;
        $this->horario_personal_id = $tipo === 'salud' ? $horario->cod_per_sal : $horario->cod_per_adm;
        $this->horario_dias_semana = [$horario->dia_semana];
        $this->horario_hora_inicio = $this->formatoHoraInput($horario->hora_inicio);
        $this->horario_hora_fin = $this->formatoHoraInput($horario->hora_fin);
        $this->horario_turno = $horario->turno ?: $this->turnoDesdeHora($horario->hora_inicio);
        $this->horario_estado = $horario->estado ?: 'ACTIVO';
        $this->horario_observaciones = $horario->observaciones;

        $this->isEdit = true;
        $this->mostrarFormularioHorario = true;
    }

    public function guardarHorario(): void
    {
        if (! $this->puedeGestionarHorarios()) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para guardar horarios.']);
            return;
        }

        $this->validate([
            'horario_tipo_personal' => 'required|in:admin,salud',
            'horario_personal_id' => 'required|integer',
            'horario_dias_semana' => 'required|array|min:1',
            'horario_hora_inicio' => 'required|date_format:H:i',
            'horario_hora_fin' => 'required|date_format:H:i|after:horario_hora_inicio',
            'horario_turno' => 'required|string|max:50',
            'horario_estado' => 'required|in:ACTIVO,INACTIVO',
            'horario_observaciones' => 'nullable|string|max:500',
        ], $this->mensajesValidacion());

        foreach ($this->horario_dias_semana as $dia) {
            if (! in_array($dia, self::DIAS, true)) {
                $this->addError('horario_dias_semana', 'Seleccione dias de la semana validos.');
                return;
            }

            if ($this->horarioDuplicado($dia)) {
                $this->dispatch('swal', [
                    'icon' => 'warning',
                    'title' => 'Horario duplicado',
                    'text' => 'Ya existe un horario exacto para ese personal, dia y rango horario.',
                ]);
                return;
            }

            if ($this->horarioSolapado($dia)) {
                $this->dispatch('swal', [
                    'icon' => 'warning',
                    'title' => 'Conflicto de horario detectado',
                    'text' => 'El personal seleccionado ya tiene un horario que se solapa con el rango indicado.',
                ]);
                return;
            }
        }

        if ($this->isEdit) {
            $horario = $this->horarioTipoEdicion === 'salud'
                ? HorarioPersonalSalud::findOrFail($this->horarioId)
                : HorarioPersonalAdmin::findOrFail($this->horarioId);

            $horario->update($this->payloadHorario($this->horario_dias_semana[0]));

            $this->dispatch('swal', ['icon' => 'success', 'title' => 'Horario actualizado', 'text' => 'El horario del personal se actualizo correctamente.']);
        } else {
            foreach ($this->horario_dias_semana as $dia) {
                $model = $this->horario_tipo_personal === 'salud' ? new HorarioPersonalSalud() : new HorarioPersonalAdmin();
                $model->fill($this->payloadHorario($dia));
                $model->save();
            }

            $this->dispatch('swal', ['icon' => 'success', 'title' => 'Horario registrado', 'text' => 'El horario fue asociado al personal seleccionado.']);
        }

        $this->mostrarFormularioHorario = false;
        $this->resetFormHorario();
    }

    public function verDetalleHorario(string $tipo, int $id): void
    {
        $horario = $this->horariosColeccion()->first(fn ($item) => $item['tipo'] === $tipo && (int) $item['id'] === $id);

        if (! $horario) {
            return;
        }

        $this->detalleEvento = [
            'tipo' => 'Horario',
            'titulo' => $horario['persona'],
            'subtitulo' => $horario['cargo'],
            'fecha' => $horario['dia_label'],
            'horario' => $horario['hora_inicio'] . ' - ' . $horario['hora_fin'],
            'estado' => $horario['estado'],
            'descripcion' => $horario['turno'] . ' / ' . $horario['tipo_label'],
            'color' => $horario['color'],
            'accion' => 'Editar horario',
            'tipo_horario' => $tipo,
            'id' => $id,
        ];
    }

    public function abrirModalTurno(): void
    {
        if (! Auth::user()->can('turnos.crear')) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para registrar turnos.']);
            return;
        }

        $this->resetErrorBag();
        $this->resetFormTurno();
        $this->isEdit = false;
        $this->mostrarFormularioTurno = true;
    }

    public function cargarTurno($codTurno): void
    {
        if (! Auth::user()->can('turnos.editar')) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para editar turnos.']);
            return;
        }

        $this->resetErrorBag();
        $turno = TurnoInstitucional::findOrFail($codTurno);
        $this->turnoId = $turno->cod_turno;
        $this->turno_nombre = $turno->nombre;
        $this->turno_hora_inicio = $this->formatoHoraInput($turno->hora_inicio);
        $this->turno_hora_fin = $this->formatoHoraInput($turno->hora_fin);
        $this->turno_descripcion = $turno->descripcion;
        $this->turno_color = $turno->color ?: '#3B82F6';
        $this->turno_estado = $turno->estado;
        $this->turno_observaciones = $turno->observaciones;

        $this->isEdit = true;
        $this->mostrarFormularioTurno = true;
    }

    public function guardarTurno(): void
    {
        if ($this->isEdit && ! Auth::user()->can('turnos.editar')) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para editar turnos.']);
            return;
        }

        if (! $this->isEdit && ! Auth::user()->can('turnos.crear')) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para registrar turnos.']);
            return;
        }

        $this->validate([
            'turno_nombre' => 'required|string|max:100',
            'turno_hora_inicio' => 'required|date_format:H:i',
            'turno_hora_fin' => 'required|date_format:H:i|after:turno_hora_inicio',
            'turno_descripcion' => 'nullable|string|max:250',
            'turno_color' => 'required|string|max:20',
            'turno_estado' => 'required|string|in:ACTIVO,INACTIVO',
            'turno_observaciones' => 'nullable|string|max:500',
        ], $this->mensajesValidacion());

        $payload = [
            'nombre' => $this->turno_nombre,
            'hora_inicio' => $this->turno_hora_inicio,
            'hora_fin' => $this->turno_hora_fin,
            'descripcion' => $this->turno_descripcion,
            'color' => $this->turno_color,
            'estado' => $this->turno_estado,
            'observaciones' => $this->turno_observaciones,
            'actualizado_por' => Auth::user()->cod_usu,
        ];

        if ($this->isEdit) {
            TurnoInstitucional::findOrFail($this->turnoId)->update($payload);
            $this->dispatch('swal', ['icon' => 'success', 'title' => 'Turno actualizado', 'text' => 'El turno institucional se modifico correctamente.']);
        } else {
            $payload['creado_por'] = Auth::user()->cod_usu;
            TurnoInstitucional::create($payload);
            $this->dispatch('swal', ['icon' => 'success', 'title' => 'Turno registrado', 'text' => 'El turno institucional fue creado correctamente.']);
        }

        $this->mostrarFormularioTurno = false;
        $this->resetFormTurno();
    }

    public function eliminarTurno($codTurno): void
    {
        if (! Auth::user()->can('turnos.cambiar_estado')) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para archivar turnos.']);
            return;
        }

        TurnoInstitucional::findOrFail($codTurno)->delete();
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Turno archivado', 'text' => 'El turno fue archivado sin eliminar su historial.']);
    }

    public function abrirModalAsignacion(): void
    {
        if (! Auth::user()->can('turnos.asignar')) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para asignar turnos.']);
            return;
        }

        $this->resetErrorBag();
        $this->resetFormAsignacion();
        $this->isEdit = false;
        $this->mostrarFormularioAsignacion = true;
    }

    public function cargarAsignacion($codAsignacion): void
    {
        if (! Auth::user()->can('turnos.asignar')) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para editar asignaciones.']);
            return;
        }

        $this->resetErrorBag();
        $asig = AsignacionTurno::with(['usuario', 'area', 'turno'])->findOrFail($codAsignacion);
        $this->asignacionId = $asig->cod_asignacion;
        $this->asig_cod_usu = $asig->cod_usu;
        $this->asig_cod_area = $asig->cod_area;
        $this->asig_cod_turno = $asig->cod_turno;
        $this->asig_dias_semana = $asig->dias_semana ?: [];
        $this->asig_fecha_inicio = $asig->fecha_inicio ? $asig->fecha_inicio->format('Y-m-d') : '';
        $this->asig_fecha_fin = $asig->fecha_fin ? $asig->fecha_fin->format('Y-m-d') : '';
        $this->asig_tipo_asignacion = $asig->tipo_asignacion ?: 'REGULAR';
        $this->asig_estado = $asig->estado;
        $this->asig_observaciones = $asig->observaciones;
        $this->asig_apoyo_temporal = (bool) ($asig->usuario && $asig->usuario->cod_area !== $asig->cod_area);

        $this->isEdit = true;
        $this->mostrarFormularioAsignacion = true;
    }

    public function verFichaAsignacion($codAsignacion): void
    {
        $this->asignacionSeleccionada = AsignacionTurno::with(['usuario.areaInstitucional', 'area', 'turno', 'creador', 'editor'])
            ->findOrFail($codAsignacion);
        $this->mostrarFichaAsignacion = true;
    }

    public function guardarAsignacion(): void
    {
        if (! Auth::user()->can('turnos.asignar')) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para realizar asignaciones.']);
            return;
        }

        $this->validate([
            'asig_cod_usu' => 'required|string|exists:users,cod_usu',
            'asig_cod_area' => 'required|string|exists:areas_institucionales,cod_area',
            'asig_cod_turno' => 'required|string|exists:turnos_institucionales,cod_turno',
            'asig_dias_semana' => 'required|array|min:1',
            'asig_fecha_inicio' => 'required|date',
            'asig_fecha_fin' => 'nullable|date|after_or_equal:asig_fecha_inicio',
            'asig_tipo_asignacion' => 'required|string|in:REGULAR,APOYO,COBERTURA,VOLUNTARIADO',
            'asig_estado' => 'required|string|in:ACTIVA,INACTIVA,FINALIZADA',
            'asig_observaciones' => 'nullable|string|max:500',
        ], $this->mensajesValidacion());

        foreach ($this->asig_dias_semana as $dia) {
            if (! in_array($dia, self::DIAS, true)) {
                $this->addError('asig_dias_semana', 'Seleccione dias de la semana validos.');
                return;
            }
        }

        $usuario = User::findOrFail($this->asig_cod_usu);
        $turno = TurnoInstitucional::findOrFail($this->asig_cod_turno);
        $cruzandoArea = $usuario->cod_area !== $this->asig_cod_area;

        if (! $this->usuarioActivo($usuario)) {
            $this->dispatch('swal', ['icon' => 'warning', 'title' => 'Personal inactivo', 'text' => 'No se puede asignar un usuario inactivo.']);
            return;
        }

        if ($cruzandoArea && ! $this->asig_apoyo_temporal) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Asignacion no permitida',
                'text' => 'El personal pertenece a otra area. Marque apoyo temporal para registrar esta cobertura.',
            ]);
            return;
        }

        if ($cruzandoArea && $this->asig_tipo_asignacion === 'REGULAR') {
            $this->asig_tipo_asignacion = 'APOYO';
        }

        if ($this->asignacionDuplicada()) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Asignacion duplicada',
                'text' => 'Ya existe una asignacion exacta para el personal, area, turno y periodo seleccionado.',
            ]);
            return;
        }

        if ($this->asignacionConConflicto($turno)) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Conflicto de horario detectado',
                'text' => 'El personal seleccionado ya tiene una asignacion activa que se cruza con este turno.',
            ]);
            return;
        }

        if (! $this->asignacionTieneHorarioCompatible($this->asig_cod_usu, $turno, $this->asig_dias_semana)) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Horario incompatible',
                'text' => 'El personal seleccionado no tiene horario registrado compatible con esta asignacion.',
            ]);
            return;
        }

        $payload = [
            'cod_usu' => $this->asig_cod_usu,
            'cod_area' => $this->asig_cod_area,
            'cod_turno' => $this->asig_cod_turno,
            'dias_semana' => $this->asig_dias_semana,
            'fecha_inicio' => $this->asig_fecha_inicio,
            'fecha_fin' => $this->asig_fecha_fin ?: null,
            'tipo_asignacion' => $this->asig_tipo_asignacion,
            'estado' => $this->asig_estado,
            'observaciones' => $this->asig_observaciones,
            'actualizado_por' => Auth::user()->cod_usu,
        ];

        if ($this->isEdit) {
            AsignacionTurno::findOrFail($this->asignacionId)->update($payload);
            $this->dispatch('swal', ['icon' => 'success', 'title' => 'Asignacion actualizada', 'text' => 'La asignacion se actualizo correctamente.']);
        } else {
            $payload['creado_por'] = Auth::user()->cod_usu;
            AsignacionTurno::create($payload);
            $this->dispatch('swal', ['icon' => 'success', 'title' => 'Asignacion registrada', 'text' => 'El personal fue asignado correctamente.']);
        }

        $this->mostrarFormularioAsignacion = false;
        $this->resetFormAsignacion();
    }

    public function finalizarAsignacion($codAsignacion): void
    {
        if (! Auth::user()->can('turnos.finalizar')) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para finalizar asignaciones.']);
            return;
        }

        $asig = AsignacionTurno::findOrFail($codAsignacion);
        $asig->update([
            'estado' => 'FINALIZADA',
            'fecha_fin' => now()->format('Y-m-d'),
            'actualizado_por' => Auth::user()->cod_usu,
        ]);

        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Asignacion finalizada', 'text' => 'La asignacion quedo finalizada y conservada en el historial.']);

        if ($this->mostrarFichaAsignacion && $this->asignacionSeleccionada && $this->asignacionSeleccionada->cod_asignacion === $codAsignacion) {
            $this->verFichaAsignacion($codAsignacion);
        }
    }

    public function eliminarAsignacion($codAsignacion): void
    {
        if (! Auth::user()->can('turnos.finalizar')) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para archivar asignaciones.']);
            return;
        }

        AsignacionTurno::findOrFail($codAsignacion)->delete();
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Asignacion archivada', 'text' => 'El registro fue archivado sin eliminacion fisica.']);
        $this->mostrarFichaAsignacion = false;
    }

    public function exportarReporteGeneralPdf()
    {
        if (! Auth::user()->can('turnos.reportes')) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para exportar reportes.']);
            return;
        }

        $asignaciones = AsignacionTurno::with(['usuario', 'area', 'turno'])
            ->where('estado', 'ACTIVA')
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        $areasSinCobertura = AreaInstitucional::activas()
            ->whereDoesntHave('asignacionesTurno', fn ($q) => $q->where('estado', 'ACTIVA'))
            ->count();

        $data = [
            'asignaciones' => $asignaciones,
            'totalTurnos' => TurnoInstitucional::count(),
            'totalAsignados' => AsignacionTurno::where('estado', 'ACTIVA')->distinct()->count('cod_usu'),
            'areasCubiertas' => AsignacionTurno::where('estado', 'ACTIVA')->distinct()->count('cod_area'),
            'areasSinCobertura' => $areasSinCobertura,
            'nombresAreasSinCobertura' => AreaInstitucional::activas()
                ->whereDoesntHave('asignacionesTurno', fn ($q) => $q->where('estado', 'ACTIVA'))
                ->pluck('nombre')
                ->toArray(),
            'asignacionesActivas' => AsignacionTurno::where('estado', 'ACTIVA')->count(),
            'usuariosSinTurno' => $this->usuariosActivosQuery()
                ->whereDoesntHave('asignacionesTurno', fn ($q) => $q->where('estado', 'ACTIVA'))
                ->count(),
            'fecha' => now()->format('d/m/Y H:i'),
            'usuario' => Auth::user()->name,
        ];

        $filename = app(ReportFileNameService::class)->generate('turnos_y_asignaciones_general', 'pdf');

        activity('TurnosAsignaciones')
            ->causedBy(Auth::user())
            ->withProperties(['formato' => 'PDF', 'tipo_reporte' => 'general'])
            ->log('Se exporto el reporte general de turnos y asignaciones en formato PDF.');

        return app(ReportExportService::class)->exportPdf('reports.turnos.general', $data, $filename);
    }

    public function exportarReporteAreaPdf($codArea)
    {
        if (! Auth::user()->can('turnos.reportes')) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para exportar reportes.']);
            return;
        }

        $area = AreaInstitucional::findOrFail($codArea);
        $data = [
            'area' => $area,
            'asignaciones' => AsignacionTurno::with(['usuario', 'turno'])->where('cod_area', $codArea)->orderBy('fecha_inicio', 'desc')->get(),
            'fecha' => now()->format('d/m/Y H:i'),
            'usuario' => Auth::user()->name,
        ];

        $filename = app(ReportFileNameService::class)->generate('cobertura_area_' . str_replace(' ', '_', strtolower($area->nombre)), 'pdf');

        activity('TurnosAsignaciones')
            ->causedBy(Auth::user())
            ->withProperties(['formato' => 'PDF', 'tipo_reporte' => 'especifico_area', 'area' => $area->nombre])
            ->log("Se exporto el reporte de asignaciones de turnos para el area: {$area->nombre}.");

        return app(ReportExportService::class)->exportPdf('reports.turnos.area', $data, $filename);
    }

    public function exportarCoberturaSemanalPdf()
    {
        if (! Auth::user()->can('turnos.reportes')) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para exportar reportes.']);
            return;
        }

        $data = [
            'asignaciones' => AsignacionTurno::with(['usuario', 'area', 'turno'])->where('estado', 'ACTIVA')->orderBy('fecha_inicio', 'desc')->get(),
            'totalAsignaciones' => AsignacionTurno::where('estado', 'ACTIVA')->count(),
            'totalColaboradores' => AsignacionTurno::where('estado', 'ACTIVA')->distinct()->count('cod_usu'),
            'totalAreas' => AsignacionTurno::where('estado', 'ACTIVA')->distinct()->count('cod_area'),
            'areasSinCobertura' => AreaInstitucional::activas()->whereDoesntHave('asignacionesTurno', fn ($q) => $q->where('estado', 'ACTIVA'))->count(),
            'fecha' => now()->format('d/m/Y H:i'),
            'usuario' => Auth::user()->name,
        ];

        $filename = app(ReportFileNameService::class)->generate('cobertura_semanal_turnos', 'pdf');

        activity('TurnosAsignaciones')
            ->causedBy(Auth::user())
            ->withProperties(['formato' => 'PDF', 'tipo_reporte' => 'cobertura_semanal'])
            ->log('Se exporto la grilla de cobertura semanal de turnos.');

        return app(ReportExportService::class)->exportPdf('reports.turnos.cobertura-semanal', $data, $filename);
    }

    public function exportarReporteGeneralExcel()
    {
        if (! Auth::user()->can('turnos.reportes')) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para exportar reportes.']);
            return;
        }

        $filename = app(ReportFileNameService::class)->generate('turnos_y_asignaciones_general', 'xlsx');

        activity('TurnosAsignaciones')
            ->causedBy(Auth::user())
            ->withProperties(['formato' => 'Excel', 'tipo_reporte' => 'general'])
            ->log('Se exporto el reporte general de turnos y asignaciones en formato Excel.');

        return app(ReportExportService::class)->exportExcel(new TurnosAsignacionesExport(), $filename);
    }

    public function exportarReporteAreaExcel($codArea)
    {
        if (! Auth::user()->can('turnos.reportes')) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para exportar reportes.']);
            return;
        }

        $area = AreaInstitucional::findOrFail($codArea);
        $filename = app(ReportFileNameService::class)->generate('cobertura_area_' . str_replace(' ', '_', strtolower($area->nombre)), 'xlsx');

        activity('TurnosAsignaciones')
            ->causedBy(Auth::user())
            ->withProperties(['formato' => 'Excel', 'tipo_reporte' => 'especifico_area', 'area' => $area->nombre])
            ->log("Se exporto el reporte de asignaciones para el area {$area->nombre}.");

        return app(ReportExportService::class)->exportExcel(new AsignacionesPorAreaExport($codArea), $filename);
    }

    public function exportarPersonalSinTurnoExcel()
    {
        if (! Auth::user()->can('turnos.reportes')) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para exportar reportes.']);
            return;
        }

        $filename = app(ReportFileNameService::class)->generate('personal_sin_turno_asignado', 'xlsx');

        activity('TurnosAsignaciones')
            ->causedBy(Auth::user())
            ->withProperties(['formato' => 'Excel', 'tipo_reporte' => 'personal_sin_turno'])
            ->log('Se exporto la lista de personal activo sin turno asignado.');

        return app(ReportExportService::class)->exportExcel(new UsuariosSinTurnoExport(), $filename);
    }

    private function usuariosActivosQuery()
    {
        return User::where(function ($q) {
            $q->where('estado', 'ACTIVO')->orWhere('estado', 1)->orWhere('estado', '1');
        });
    }

    private function asignacionesFiltradasQuery()
    {
        $query = AsignacionTurno::with(['usuario.personalAdmin', 'usuario.personalSalud', 'area', 'turno']);

        if ($this->search) {
            $query->whereHas('usuario', function ($q) {
                $q->where('nombres', 'like', '%' . $this->search . '%')
                    ->orWhere('ap_paterno', 'like', '%' . $this->search . '%')
                    ->orWhere('ap_materno', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filtroArea) {
            $query->where('cod_area', $this->filtroArea);
        }

        if ($this->filtroTurno) {
            $query->where('cod_turno', $this->filtroTurno);
        }

        if ($this->filtroEstado) {
            $query->where('estado', $this->filtroEstado);
        }

        if ($this->filtroTipo) {
            $query->where('tipo_asignacion', $this->filtroTipo);
        }

        if ($this->filtroDia) {
            $query->whereJsonContains('dias_semana', strtoupper($this->filtroDia));
        }

        if ($this->filtroTipoPersonal === 'admin') {
            $query->whereHas('usuario.personalAdmin');
        }

        if ($this->filtroTipoPersonal === 'salud') {
            $query->whereHas('usuario.personalSalud');
        }

        return $query;
    }

    private function horariosColeccion(): Collection
    {
        $admin = HorarioPersonalAdmin::with(['personalAdmin.usuario', 'personalAdmin.cargoAdmin'])
            ->get()
            ->map(fn ($h) => $this->mapHorarioAdmin($h));

        $salud = HorarioPersonalSalud::with(['personalSalud.usuario', 'personalSalud.especialidad'])
            ->get()
            ->map(fn ($h) => $this->mapHorarioSalud($h));

        return $admin
            ->merge($salud)
            ->filter(fn ($h) => $this->filtraHorario($h))
            ->sortBy(fn ($h) => [$this->diaIndice($h['dia']), $h['hora_inicio'], $h['persona']])
            ->values();
    }

    private function mapHorarioAdmin(HorarioPersonalAdmin $h): array
    {
        $usuario = $h->personalAdmin?->usuario;

        return [
            'id' => $h->cod_hor_per_admin,
            'tipo' => 'admin',
            'tipo_label' => 'Administrativo',
            'persona' => $usuario?->name ?: 'Sin usuario',
            'cod_usu' => $usuario?->cod_usu,
            'cargo' => $h->personalAdmin?->cargoAdmin?->nombre ?: ($h->personalAdmin?->cargo ?: 'Personal administrativo'),
            'dia' => $h->dia_semana,
            'dia_label' => self::DIA_LABELS[$h->dia_semana] ?? $h->dia_semana,
            'hora_inicio' => $this->formatoHora($h->hora_inicio),
            'hora_fin' => $this->formatoHora($h->hora_fin),
            'turno' => $h->turno ?: $this->turnoDesdeHora($h->hora_inicio),
            'estado' => $h->estado ?: 'ACTIVO',
            'observaciones' => $h->observaciones ?: 'Sin observaciones',
            'color' => $this->colorTurno($h->turno ?: $this->turnoDesdeHora($h->hora_inicio)),
        ];
    }

    private function mapHorarioSalud(HorarioPersonalSalud $h): array
    {
        $usuario = $h->personalSalud?->usuario;

        return [
            'id' => $h->cod_hor_per_sal,
            'tipo' => 'salud',
            'tipo_label' => 'Salud',
            'persona' => $usuario?->name ?: 'Sin usuario',
            'cod_usu' => $usuario?->cod_usu,
            'cargo' => $h->personalSalud?->especialidad?->nombre ?: 'Personal de salud',
            'dia' => $h->dia_semana,
            'dia_label' => self::DIA_LABELS[$h->dia_semana] ?? $h->dia_semana,
            'hora_inicio' => $this->formatoHora($h->hora_inicio),
            'hora_fin' => $this->formatoHora($h->hora_fin),
            'turno' => $h->turno ?: $this->turnoDesdeHora($h->hora_inicio),
            'estado' => $h->estado ?: 'ACTIVO',
            'observaciones' => $h->observaciones ?: 'Sin observaciones',
            'color' => $this->colorTurno($h->turno ?: $this->turnoDesdeHora($h->hora_inicio)),
        ];
    }

    private function filtraHorario(array $h): bool
    {
        if ($this->horarioSearch && ! str_contains(mb_strtolower($h['persona'] . ' ' . $h['cargo']), mb_strtolower($this->horarioSearch))) {
            return false;
        }

        if ($this->horarioTipoPersonal && $h['tipo'] !== $this->horarioTipoPersonal) {
            return false;
        }

        if ($this->horarioDia && $h['dia'] !== $this->horarioDia) {
            return false;
        }

        if ($this->horarioTurno && mb_strtolower($h['turno']) !== mb_strtolower($this->horarioTurno)) {
            return false;
        }

        if ($this->horarioEstado && $h['estado'] !== $this->horarioEstado) {
            return false;
        }

        return true;
    }

    private function personalHorarioOpciones(string $tipo): Collection
    {
        if ($tipo === 'salud') {
            return PersonalSalud::with(['usuario', 'especialidad'])
                ->orderBy('cod_per_sal')
                ->get()
                ->map(fn ($p) => [
                    'id' => $p->cod_per_sal,
                    'nombre' => $p->usuario?->name ?: 'Sin usuario',
                    'cargo' => $p->especialidad?->nombre ?: 'Personal de salud',
                    'estado' => $p->estado_laboral ?: 'Sin estado',
                ]);
        }

        return PersonalAdmin::with(['usuario', 'cargoAdmin'])
            ->orderBy('cod_per_adm')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->cod_per_adm,
                'nombre' => $p->usuario?->name ?: 'Sin usuario',
                'cargo' => $p->cargoAdmin?->nombre ?: ($p->cargo ?: 'Personal administrativo'),
                'estado' => $p->estado_laboral ?: 'Sin estado',
            ]);
    }

    private function metricasOperativas(Collection $usuarios, Collection $asignaciones, Collection $horarios, Collection $turnos, Collection $areas, Collection $alertas): array
    {
        $hoy = now();
        $diaHoy = $this->diaKey($hoy);

        return [
            'personal_activo' => $usuarios->count(),
            'horarios_registrados' => $horarios->count(),
            'turnos_activos' => $turnos->where('estado', 'ACTIVO')->count(),
            'asignaciones_hoy' => $asignaciones->filter(fn ($a) => $this->asignacionActivaEnFecha($a, $hoy, $diaHoy))->count(),
            'turnos_sin_cubrir' => $areas->filter(fn ($area) => AsignacionTurno::where('estado', 'ACTIVA')->where('cod_area', $area->cod_area)->doesntExist())->count(),
            'conflictos_detectados' => $alertas->whereIn('prioridad', ['Alta', 'Media'])->count(),
            'personal_sobrecarga' => AsignacionTurno::where('estado', 'ACTIVA')->selectRaw('cod_usu, COUNT(*) as total')->groupBy('cod_usu')->havingRaw('COUNT(*) >= 4')->get()->count(),
            'proximas_asignaciones' => $this->proximasAsignaciones($asignaciones)->count(),
            'horarios_activos' => $horarios->where('estado', 'ACTIVO')->count(),
            'horarios_inactivos' => $horarios->where('estado', 'INACTIVO')->count(),
        ];
    }

    private function alertasOperativas(Collection $usuarios, Collection $asignaciones, Collection $horarios, Collection $areas): Collection
    {
        $alertas = collect();
        $usuariosConHorario = $horarios->pluck('cod_usu')->filter()->unique();

        foreach ($usuarios->whereNotIn('cod_usu', $usuariosConHorario)->take(8) as $usuario) {
            $alertas->push($this->alerta('Personal sin horario', $usuario->name, 'Sin fecha', 'Sin horario', 'No tiene horario registrado en administracion ni salud.', 'Media', 'Registrar horario'));
        }

        foreach ($areas as $area) {
            if (AsignacionTurno::where('estado', 'ACTIVA')->where('cod_area', $area->cod_area)->doesntExist()) {
                $alertas->push($this->alerta('Turno sin cubrir', $area->nombre, 'Semana actual', 'Cobertura', 'El area no tiene asignaciones activas.', 'Alta', 'Asignar personal'));
            }
        }

        foreach ($this->conflictosAsignaciones($asignaciones) as $conflicto) {
            $alertas->push($conflicto);
        }

        foreach ($this->conflictosHorarios($horarios) as $conflicto) {
            $alertas->push($conflicto);
        }

        foreach ($asignaciones->where('estado', 'ACTIVA') as $asig) {
            if (! $this->asignacionTieneHorarioCompatible($asig->cod_usu, $asig->turno, $asig->dias_semana ?: [])) {
                $alertas->push($this->alerta('Horario incompatible', $asig->usuario?->name ?: 'Sin usuario', $this->periodoAsignacion($asig), $asig->turno?->nombre ?: 'Sin turno', 'La asignacion no coincide con un horario registrado.', 'Alta', 'Revisar horario'));
            }
        }

        return $alertas->take(30)->values();
    }

    private function conflictosAsignaciones(Collection $asignaciones): Collection
    {
        $conflictos = collect();

        foreach ($asignaciones->where('estado', 'ACTIVA')->groupBy('cod_usu') as $items) {
            $items = $items->values();

            for ($i = 0; $i < $items->count(); $i++) {
                for ($j = $i + 1; $j < $items->count(); $j++) {
                    $a = $items[$i];
                    $b = $items[$j];

                    if ($this->asignacionesSeCruzan($a, $b)) {
                        $conflictos->push($this->alerta('Asignaciones solapadas', $a->usuario?->name ?: 'Sin usuario', $this->periodoAsignacion($a), $a->turno?->nombre . ' / ' . $b->turno?->nombre, 'Tiene dos asignaciones activas que se cruzan.', 'Alta', 'Reprogramar'));
                    }
                }
            }
        }

        return $conflictos;
    }

    private function conflictosHorarios(Collection $horarios): Collection
    {
        $conflictos = collect();

        foreach ($horarios->where('estado', 'ACTIVO')->groupBy(fn ($h) => $h['tipo'] . '-' . $h['cod_usu'] . '-' . $h['dia']) as $items) {
            $items = $items->values();

            for ($i = 0; $i < $items->count(); $i++) {
                for ($j = $i + 1; $j < $items->count(); $j++) {
                    if ($this->rangosHoraSeCruzan($items[$i]['hora_inicio'], $items[$i]['hora_fin'], $items[$j]['hora_inicio'], $items[$j]['hora_fin'])) {
                        $conflictos->push($this->alerta('Horarios solapados', $items[$i]['persona'], $items[$i]['dia_label'], $items[$i]['hora_inicio'] . ' - ' . $items[$j]['hora_fin'], 'Tiene horarios registrados que se cruzan.', 'Media', 'Editar horario'));
                    }
                }
            }
        }

        return $conflictos;
    }

    private function calendarioData(Collection $asignaciones, Collection $horarios): array
    {
        $base = Carbon::parse($this->fechaCalendario ?: now());
        $inicio = match ($this->modoCalendario) {
            'dia' => $base->copy()->startOfDay(),
            'mes' => $base->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY),
            default => $base->copy()->startOfWeek(Carbon::MONDAY),
        };
        $fin = match ($this->modoCalendario) {
            'dia' => $base->copy()->endOfDay(),
            'mes' => $base->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY),
            default => $base->copy()->endOfWeek(Carbon::SUNDAY),
        };

        $dias = collect();
        $cursor = $inicio->copy();

        while ($cursor <= $fin) {
            $diaKey = $this->diaKey($cursor);
            $eventos = collect();

            foreach ($asignaciones as $asig) {
                if ($this->asignacionActivaEnFecha($asig, $cursor, $diaKey)) {
                    $eventos->push([
                        'tipo' => 'asignacion',
                        'id' => $asig->cod_asignacion,
                        'titulo' => $asig->usuario?->name ?: 'Sin usuario',
                        'subtitulo' => $asig->area?->nombre ?: 'Sin area',
                        'hora' => $asig->turno ? $this->formatoHora($asig->turno->hora_inicio) . ' - ' . $this->formatoHora($asig->turno->hora_fin) : 'Sin horario',
                        'estado' => $asig->estado,
                        'color' => $asig->turno?->color ?: '#8DA280',
                    ]);
                }
            }

            foreach ($horarios as $horario) {
                if ($horario['dia'] === $diaKey && $horario['estado'] === 'ACTIVO') {
                    $eventos->push([
                        'tipo' => 'horario',
                        'id' => $horario['id'],
                        'tipo_horario' => $horario['tipo'],
                        'titulo' => $horario['persona'],
                        'subtitulo' => $horario['tipo_label'],
                        'hora' => $horario['hora_inicio'] . ' - ' . $horario['hora_fin'],
                        'estado' => $horario['turno'],
                        'color' => $horario['color'],
                    ]);
                }
            }

            $dias->push([
                'fecha' => $cursor->format('Y-m-d'),
                'numero' => $cursor->format('d'),
                'mes' => $cursor->translatedFormat('M'),
                'dia' => $diaKey,
                'dia_label' => self::DIA_LABELS[$diaKey],
                'es_hoy' => $cursor->isToday(),
                'es_mes_actual' => $cursor->month === $base->month,
                'eventos' => $eventos->take($this->modoCalendario === 'mes' ? 4 : 12)->values(),
                'total_eventos' => $eventos->count(),
            ]);

            $cursor->addDay();
        }

        return [
            'titulo' => match ($this->modoCalendario) {
                'dia' => $base->translatedFormat('d F Y'),
                'mes' => $base->translatedFormat('F Y'),
                default => $inicio->translatedFormat('d M') . ' - ' . $fin->translatedFormat('d M Y'),
            },
            'dias' => $dias,
        ];
    }

    private function proximasAsignaciones(Collection $asignaciones): Collection
    {
        return $asignaciones
            ->filter(fn ($a) => $a->estado === 'ACTIVA' && (! $a->fecha_fin || $a->fecha_fin->gte(now()->startOfDay())))
            ->sortBy('fecha_inicio')
            ->take(8)
            ->values();
    }

    private function asignacionesHoyLista(Collection $asignaciones): Collection
    {
        $hoy = now();
        $diaHoy = $this->diaKey($hoy);

        return $asignaciones
            ->filter(fn ($a) => $this->asignacionActivaEnFecha($a, $hoy, $diaHoy))
            ->take(8)
            ->values();
    }

    private function resumenSemanal(Collection $asignaciones, Collection $horarios): array
    {
        return collect(self::DIAS)
            ->mapWithKeys(fn ($dia) => [
                $dia => [
                    'label' => self::DIA_LABELS[$dia],
                    'asignaciones' => $asignaciones->filter(fn ($a) => $a->estado === 'ACTIVA' && in_array($dia, $a->dias_semana ?: [], true))->count(),
                    'horarios' => $horarios->where('dia', $dia)->where('estado', 'ACTIVO')->count(),
                ],
            ])
            ->toArray();
    }

    private function distribucionAreas(Collection $areas): array
    {
        return $areas
            ->mapWithKeys(fn ($area) => [$area->nombre => AsignacionTurno::activas()->where('cod_area', $area->cod_area)->count()])
            ->filter(fn ($count) => $count > 0)
            ->toArray();
    }

    private function distribucionTurnos(Collection $turnos): array
    {
        return $turnos
            ->mapWithKeys(fn ($turno) => [$turno->nombre => AsignacionTurno::activas()->where('cod_turno', $turno->cod_turno)->count()])
            ->filter(fn ($count) => $count > 0)
            ->toArray();
    }

    private function resetFormHorario(): void
    {
        $this->horarioId = null;
        $this->horarioTipoEdicion = '';
        $this->horario_tipo_personal = 'admin';
        $this->horario_personal_id = '';
        $this->horario_dias_semana = [];
        $this->horario_hora_inicio = '';
        $this->horario_hora_fin = '';
        $this->horario_turno = 'Mañana';
        $this->horario_estado = 'ACTIVO';
        $this->horario_observaciones = '';
    }

    private function resetFormTurno(): void
    {
        $this->turnoId = null;
        $this->turno_nombre = '';
        $this->turno_hora_inicio = '';
        $this->turno_hora_fin = '';
        $this->turno_descripcion = '';
        $this->turno_color = '#3B82F6';
        $this->turno_estado = 'ACTIVO';
        $this->turno_observaciones = '';
    }

    private function resetFormAsignacion(): void
    {
        $this->asignacionId = null;
        $this->asig_cod_usu = '';
        $this->asig_cod_area = '';
        $this->asig_cod_turno = '';
        $this->asig_dias_semana = [];
        $this->asig_fecha_inicio = now()->format('Y-m-d');
        $this->asig_fecha_fin = '';
        $this->asig_tipo_asignacion = 'REGULAR';
        $this->asig_estado = 'ACTIVA';
        $this->asig_observaciones = '';
        $this->asig_apoyo_temporal = false;
    }

    private function payloadHorario(string $dia): array
    {
        $payload = [
            'dia_semana' => $dia,
            'hora_inicio' => $this->horario_hora_inicio,
            'hora_fin' => $this->horario_hora_fin,
            'turno' => $this->horario_turno,
            'estado' => $this->horario_estado,
            'observaciones' => $this->horario_observaciones,
        ];

        if ($this->horario_tipo_personal === 'salud') {
            $payload['cod_per_sal'] = $this->horario_personal_id;
        } else {
            $payload['cod_per_adm'] = $this->horario_personal_id;
        }

        return $payload;
    }

    private function horarioDuplicado(string $dia): bool
    {
        $query = $this->horario_tipo_personal === 'salud'
            ? HorarioPersonalSalud::where('cod_per_sal', $this->horario_personal_id)
            : HorarioPersonalAdmin::where('cod_per_adm', $this->horario_personal_id);

        $query->where('dia_semana', $dia)
            ->where('hora_inicio', $this->horario_hora_inicio)
            ->where('hora_fin', $this->horario_hora_fin);

        if ($this->isEdit && $this->horarioId) {
            $key = $this->horario_tipo_personal === 'salud' ? 'cod_hor_per_sal' : 'cod_hor_per_admin';
            $query->where($key, '<>', $this->horarioId);
        }

        return $query->exists();
    }

    private function horarioSolapado(string $dia): bool
    {
        $items = $this->horario_tipo_personal === 'salud'
            ? HorarioPersonalSalud::where('cod_per_sal', $this->horario_personal_id)->where('dia_semana', $dia)->get()
            : HorarioPersonalAdmin::where('cod_per_adm', $this->horario_personal_id)->where('dia_semana', $dia)->get();

        foreach ($items as $item) {
            $key = $this->horario_tipo_personal === 'salud' ? $item->cod_hor_per_sal : $item->cod_hor_per_admin;
            if ($this->isEdit && (int) $key === (int) $this->horarioId) {
                continue;
            }

            if ($this->rangosHoraSeCruzan($item->hora_inicio, $item->hora_fin, $this->horario_hora_inicio, $this->horario_hora_fin)) {
                return true;
            }
        }

        return false;
    }

    private function asignacionDuplicada(): bool
    {
        $items = AsignacionTurno::where('cod_usu', $this->asig_cod_usu)
            ->where('cod_area', $this->asig_cod_area)
            ->where('cod_turno', $this->asig_cod_turno)
            ->where('fecha_inicio', $this->asig_fecha_inicio)
            ->where(function ($q) {
                $this->asig_fecha_fin ? $q->where('fecha_fin', $this->asig_fecha_fin) : $q->whereNull('fecha_fin');
            })
            ->when($this->isEdit && $this->asignacionId, fn ($q) => $q->where('cod_asignacion', '<>', $this->asignacionId))
            ->get();

        $dias = collect($this->asig_dias_semana)->sort()->values()->all();

        return $items->contains(fn ($item) => collect($item->dias_semana ?: [])->sort()->values()->all() === $dias);
    }

    private function asignacionConConflicto(TurnoInstitucional $turno): bool
    {
        $items = AsignacionTurno::with('turno')
            ->where('cod_usu', $this->asig_cod_usu)
            ->where('estado', 'ACTIVA')
            ->when($this->isEdit && $this->asignacionId, fn ($q) => $q->where('cod_asignacion', '<>', $this->asignacionId))
            ->get();

        foreach ($items as $item) {
            if (! $this->rangosFechaSeCruzan($item->fecha_inicio, $item->fecha_fin, $this->asig_fecha_inicio, $this->asig_fecha_fin)) {
                continue;
            }

            if (empty(array_intersect($item->dias_semana ?: [], $this->asig_dias_semana))) {
                continue;
            }

            if ($this->turnosSeCruzan($item->turno, $turno)) {
                return true;
            }
        }

        return false;
    }

    private function asignacionTieneHorarioCompatible(?string $codUsu, ?TurnoInstitucional $turno, array $dias): bool
    {
        if (! $codUsu || ! $turno) {
            return false;
        }

        $horarios = $this->horariosColeccion()
            ->where('cod_usu', $codUsu)
            ->where('estado', 'ACTIVO')
            ->filter(fn ($h) => in_array($h['dia'], $dias, true));

        if ($horarios->isEmpty()) {
            return false;
        }

        foreach ($horarios as $horario) {
            if ($turno->hora_inicio && $turno->hora_fin) {
                if ($this->rangoContiene($horario['hora_inicio'], $horario['hora_fin'], $this->formatoHora($turno->hora_inicio), $this->formatoHora($turno->hora_fin))) {
                    return true;
                }
            }

            if (mb_strtolower($horario['turno']) === mb_strtolower($turno->nombre)) {
                return true;
            }
        }

        return false;
    }

    private function asignacionesSeCruzan(AsignacionTurno $a, AsignacionTurno $b): bool
    {
        return $this->rangosFechaSeCruzan($a->fecha_inicio, $a->fecha_fin, $b->fecha_inicio, $b->fecha_fin)
            && ! empty(array_intersect($a->dias_semana ?: [], $b->dias_semana ?: []))
            && $this->turnosSeCruzan($a->turno, $b->turno);
    }

    private function turnosSeCruzan(?TurnoInstitucional $a, ?TurnoInstitucional $b): bool
    {
        if (! $a || ! $b || ! $a->hora_inicio || ! $a->hora_fin || ! $b->hora_inicio || ! $b->hora_fin) {
            return false;
        }

        return $this->rangosHoraSeCruzan($a->hora_inicio, $a->hora_fin, $b->hora_inicio, $b->hora_fin);
    }

    private function rangosFechaSeCruzan($aInicio, $aFin, $bInicio, $bFin): bool
    {
        $aInicio = Carbon::parse($aInicio)->startOfDay();
        $aFin = $aFin ? Carbon::parse($aFin)->endOfDay() : Carbon::parse('2999-12-31');
        $bInicio = Carbon::parse($bInicio)->startOfDay();
        $bFin = $bFin ? Carbon::parse($bFin)->endOfDay() : Carbon::parse('2999-12-31');

        return $aInicio <= $bFin && $bInicio <= $aFin;
    }

    private function rangosHoraSeCruzan($aInicio, $aFin, $bInicio, $bFin): bool
    {
        if (! $aInicio || ! $aFin || ! $bInicio || ! $bFin) {
            return false;
        }

        $aInicio = Carbon::createFromFormat('H:i', $this->formatoHora($aInicio));
        $aFin = Carbon::createFromFormat('H:i', $this->formatoHora($aFin));
        $bInicio = Carbon::createFromFormat('H:i', $this->formatoHora($bInicio));
        $bFin = Carbon::createFromFormat('H:i', $this->formatoHora($bFin));

        return $aInicio < $bFin && $bInicio < $aFin;
    }

    private function rangoContiene($contenedorInicio, $contenedorFin, $inicio, $fin): bool
    {
        $contenedorInicio = Carbon::createFromFormat('H:i', $this->formatoHora($contenedorInicio));
        $contenedorFin = Carbon::createFromFormat('H:i', $this->formatoHora($contenedorFin));
        $inicio = Carbon::createFromFormat('H:i', $this->formatoHora($inicio));
        $fin = Carbon::createFromFormat('H:i', $this->formatoHora($fin));

        return $contenedorInicio <= $inicio && $contenedorFin >= $fin;
    }

    private function asignacionActivaEnFecha(AsignacionTurno $asig, Carbon $fecha, string $diaKey): bool
    {
        if (! in_array($diaKey, $asig->dias_semana ?: [], true)) {
            return false;
        }

        $inicio = $asig->fecha_inicio ? Carbon::parse($asig->fecha_inicio)->startOfDay() : null;
        $fin = $asig->fecha_fin ? Carbon::parse($asig->fecha_fin)->endOfDay() : null;

        return (! $inicio || $fecha->copy()->endOfDay() >= $inicio)
            && (! $fin || $fecha->copy()->startOfDay() <= $fin);
    }

    private function alerta(string $tipo, string $persona, string $fecha, string $horario, string $descripcion, string $prioridad, string $accion): array
    {
        return [
            'tipo' => $tipo,
            'persona' => $persona,
            'fecha' => $fecha,
            'horario' => $horario,
            'descripcion' => $descripcion,
            'prioridad' => $prioridad,
            'accion' => $accion,
            'estado' => 'Pendiente',
        ];
    }

    private function usuarioActivo(User $usuario): bool
    {
        return in_array((string) $usuario->estado, ['ACTIVO', '1'], true);
    }

    private function puedeGestionarHorarios(): bool
    {
        return Auth::user()?->can('turnos.asignar') || Auth::user()?->can('turnos.crear');
    }

    private function diaKey(Carbon $fecha): string
    {
        return self::DIAS[$fecha->dayOfWeekIso - 1] ?? 'LUNES';
    }

    private function diaIndice(string $dia): int
    {
        $indice = array_search($dia, self::DIAS, true);
        return $indice === false ? 99 : $indice;
    }

    private function periodoAsignacion(AsignacionTurno $asig): string
    {
        $inicio = $asig->fecha_inicio ? Carbon::parse($asig->fecha_inicio)->format('d/m/Y') : 'Sin inicio';
        $fin = $asig->fecha_fin ? Carbon::parse($asig->fecha_fin)->format('d/m/Y') : 'Vigente';

        return $inicio . ' - ' . $fin;
    }

    private function formatoHora($hora): string
    {
        if (! $hora) {
            return '--:--';
        }

        return substr((string) $hora, 0, 5);
    }

    private function formatoHoraInput($hora): string
    {
        return $hora ? substr((string) $hora, 0, 5) : '';
    }

    private function turnoDesdeHora($hora): string
    {
        $hora = (int) substr((string) $hora, 0, 2);

        return match (true) {
            $hora < 12 => 'Mañana',
            $hora < 18 => 'Tarde',
            default => 'Noche',
        };
    }

    private function colorTurno(?string $turno): string
    {
        $turno = mb_strtolower($turno ?: '');

        return match (true) {
            str_contains($turno, 'mañ') || str_contains($turno, 'man') => '#6C8FB1',
            str_contains($turno, 'tarde') => '#D9A05B',
            str_contains($turno, 'noche') => '#7C6AA6',
            str_contains($turno, 'guardia') => '#C65D5D',
            str_contains($turno, 'evento') => '#E27D60',
            str_contains($turno, 'apoyo') => '#8DA280',
            default => '#2F3E5C',
        };
    }

    private function mensajesValidacion(): array
    {
        return [
            'required' => 'Este campo es obligatorio.',
            'date' => 'Ingrese una fecha valida.',
            'date_format' => 'Ingrese una hora valida.',
            'after' => 'La hora de fin debe ser mayor que la hora de inicio.',
            'after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
            'array' => 'Seleccione al menos una opcion valida.',
            'min' => 'Seleccione al menos una opcion.',
            'exists' => 'El registro seleccionado no existe.',
            'in' => 'Seleccione una opcion valida.',
            'max' => 'El texto es demasiado largo.',
        ];
    }
}
