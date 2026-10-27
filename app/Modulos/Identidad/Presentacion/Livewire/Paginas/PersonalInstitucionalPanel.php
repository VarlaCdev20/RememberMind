<?php

namespace App\Modulos\Identidad\Presentacion\Livewire\Paginas;

use App\Models\AsignacionPersonal;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\CambiarEstadoPersonal;
use App\Modulos\Identidad\Aplicacion\Acciones\AutorizarGestionPersonal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Locked;

class PersonalInstitucionalPanel extends Component
{
    use WithPagination;

    public string $tabActiva = 'resumen';

    public int $tabVersion = 0;

    public string $vistaListadoResumen = 'tabla';

    public string $rubroResumen = 'institucional';

    public string $busquedaResumen = '';

    public string $estadoResumen = '';

    public string $turnoResumen = '';

    public string $busqueda = '';

    public string $filtroTipo = '';

    public string $filtroRol = '';

    public string $filtroEstado = '';

    public string $filtroDisponibilidad = '';

    public bool $modalGestionAbierto = false;

    public ?string $usuarioSeleccionadoId = null;

    public string $modalTabInicial = 'informacion';

    public bool $abrirFormularioHorarioInicial = false;

    public bool $modalLaboral = false;
    #[Locked] public ?string $personalLaboral = null;
    #[Locked] public string $estadoLaboralEsperado = '';
    #[Locked] public string $nombreLaboral = '';
    public string $nuevoEstadoLaboral = '';
    public string $fechaEfectoLaboral = '';
    public string $motivoLaboral = '';

    protected $listeners = [
        'cerrarModalGestion' => 'cerrarModal',
        'cerrar-modal-gestion-personal' => 'cerrarModal',
        'actualizarTablaPersonal' => '$refresh',
        'asignacionActualizada' => '$refresh',
        'abrirHorariosPersonal' => 'abrirHorariosPersonal',
        'personalRegistradoParaHorario' => 'continuarHorariosNuevoPersonal',
    ];

    public function render()
    {
        abort_unless(auth()->user()?->fresh()?->estado === 'ACTIVO' && !auth()->user()->hasRole('FAMILIAR') && auth()->user()->can('personal_institucional.ver'), 403);
        if (app(AutorizarGestionPersonal::class)->esGerencia(auth()->user())) {
            $payload = $this->obtenerPersonalResumenPayload();
            return view('livewire.identidad.personal-institucional-panel', [
                'personalResumen' => $payload['items'], 'personalResumenTotal' => $payload['total'],
                'ampliacionDisponible' => app(\App\Modulos\Identidad\Aplicacion\Consultas\CapacidadDocumentalPersonal::class)->disponible(),
            ])->layout('layouts.sistema');
        }
        $estadisticas = $this->obtenerEstadisticas();
        $resumenInstitucional = $this->obtenerResumenInstitucional();

        $personalResumenPayload = $this->obtenerPersonalResumenPayload();
        $personalResumen = $personalResumenPayload['items'];
        $personalResumenTotal = $personalResumenPayload['total'];

        $usuarios = $this->obtenerUsuarios();
        $chartData = $this->obtenerChartData($estadisticas);
        $interpretacionReportes = $this->obtenerInterpretacionReportes($estadisticas);

        return view('livewire.identidad.personal-institucional-panel', [
            'usuarios' => $usuarios,
            'estadisticas' => $estadisticas,
            'resumenInstitucional' => $resumenInstitucional,
            'personalResumen' => $personalResumen,
            'personalResumenTotal' => $personalResumenTotal,
            'personalResumenMostrados' => $personalResumen->count(),
            'vistaListadoResumen' => $this->vistaListadoResumen,
            'rubroResumen' => $this->rubroResumen,
            'chartData' => $chartData,
            'interpretacionReportes' => $interpretacionReportes,
            'tabVersion' => $this->tabVersion,
        ])->layout('layouts.sistema');
    }

    public function setTab(string $tab): void
    {
        $tabsPermitidas = ['resumen', 'salud', 'admin', 'disponibilidad', 'reportes'];

        if (! in_array($tab, $tabsPermitidas, true)) {
            $tab = 'resumen';
        }

        if ($this->tabActiva !== $tab) {
            $this->dispatch('destroy-charts');
            $this->tabVersion++;
        }

        $this->tabActiva = $tab;
        $this->busqueda = '';

        if ($tab === 'salud') {
            $this->filtroTipo = 'salud';
            $this->filtroRol = '';
        } elseif ($tab === 'admin') {
            $this->filtroTipo = 'admin';
            $this->filtroRol = '';
        } else {
            $this->filtroTipo = '';
            $this->filtroRol = '';
        }

        $this->filtroEstado = '';
        $this->filtroDisponibilidad = '';
    }

    public function cambiarVistaListadoResumen(string $vista): void
    {
        if (! in_array($vista, ['tarjetas', 'tabla'], true)) {
            $vista = 'tarjetas';
        }

        $this->vistaListadoResumen = $vista;
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['busquedaResumen', 'rubroResumen', 'estadoResumen', 'turnoResumen'], true)) {
            $this->resetPage('personalPage');
        }
    }

    public function limpiarFiltrosResumen(): void
    {
        $this->resetPage('personalPage');
        $this->busquedaResumen = '';
        $this->rubroResumen = 'institucional';
        $this->estadoResumen = '';
        $this->turnoResumen = '';
    }

    public function limpiarFiltros(): void
    {
        $this->busqueda = '';
        $this->filtroRol = '';
        $this->filtroEstado = '';
        $this->filtroDisponibilidad = '';

        if ($this->tabActiva === 'salud') {
            $this->filtroTipo = 'salud';
        } elseif ($this->tabActiva === 'admin') {
            $this->filtroTipo = 'admin';
        } else {
            $this->filtroTipo = '';
        }
    }

    public function abrirModalNuevo(): void
    {
        app(AutorizarGestionPersonal::class)->autorizar(auth()->user());
        $this->usuarioSeleccionadoId = null;
        $this->modalTabInicial = 'informacion';
        $this->abrirFormularioHorarioInicial = false;
        $this->modalGestionAbierto = true;
    }

    public function abrirModalEdicion(string $usuarioId): void
    {
        app(AutorizarGestionPersonal::class)->autorizar(auth()->user(), User::findOrFail($usuarioId));
        if (! $this->existeUsuarioInstitucional($usuarioId)) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Personal no disponible',
                'text' => 'No se encontró un registro institucional válido para editar.',
            ]);

            return;
        }

        $this->usuarioSeleccionadoId = $usuarioId;
        $this->modalTabInicial = 'informacion';
        $this->abrirFormularioHorarioInicial = false;
        $this->modalGestionAbierto = true;
    }

    public function abrirHorariosPersonal(string $usuarioId): void
    {
        app(\App\Modulos\Institucion\Aplicacion\Acciones\GestionarHorariosPersonal::class)->autorizar(false);
        if (! $this->existeUsuarioInstitucional($usuarioId)) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Personal no disponible',
                'text' => 'No se encontró un registro institucional válido para asignar horarios.',
            ]);

            return;
        }

        $this->usuarioSeleccionadoId = $usuarioId;
        $this->modalTabInicial = 'horarios';
        $this->abrirFormularioHorarioInicial = false;
        $this->modalGestionAbierto = true;
    }

    public function continuarHorariosNuevoPersonal(string $usuarioId): void
    {
        $this->abrirHorariosPersonal($usuarioId);

        if ($this->modalGestionAbierto) {
            $this->abrirFormularioHorarioInicial = true;
        }
    }

    public function abrirModuloHorarios(): void
    {
        $this->cerrarModal();
        $this->setTab('disponibilidad');
    }

    public function cerrarModal(): void
    {
        $this->modalGestionAbierto = false;
        $this->usuarioSeleccionadoId = null;
        $this->modalTabInicial = 'informacion';
        $this->abrirFormularioHorarioInicial = false;
    }

    public function toggleEstado(string $usuarioId, ?string $estadoEsperado = null): void
    {
        $usuario = $this->baseUsuariosInstitucionales()
            ->where('cod_usuario', $usuarioId)
            ->first();

        if (! $usuario) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Registro no encontrado',
                'text' => 'No se encontró el personal seleccionado.',
            ]);

            return;
        }

        abort_unless(auth()->user(), 403);
        app(CambiarEstadoPersonal::class)->ejecutar(auth()->user(), $usuario, $estadoEsperado);

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Acceso actualizado',
            'text' => 'Se actualizó el acceso de la cuenta. El estado laboral y las asignaciones se conservan.',
        ]);

        $this->dispatch('actualizarTablaPersonal');
    }

    public function enviarRecuperacion(string $usuarioId): void
    {
        $this->resetValidation('recuperacion');
        try {
            app(\App\Modulos\Identidad\Aplicacion\Acciones\EnviarRecuperacionAccesoPersonal::class)->ejecutar(auth()->user(), $usuarioId);
        } catch (\App\Compartido\Infraestructura\Notificaciones\CorreoNoConfigurado $error) {
            $this->addError('recuperacion', \App\Compartido\Infraestructura\Notificaciones\CorreoNoConfigurado::AVISO);
            return;
        }
        $this->dispatch('swal', [
            'icon' => 'success', 'title' => 'Enlace solicitado',
            'text' => 'El servicio de correo recibió la solicitud para el trabajador. Su contraseña y su acceso no se han cambiado.',
        ]);
    }

    public function abrirEstadoLaboral(string $usuarioId): void
    {
        app(\App\Modulos\Identidad\Aplicacion\Consultas\CapacidadDocumentalPersonal::class)->exigir();
        $usuario=User::with('personal')->findOrFail($usuarioId);
        app(AutorizarGestionPersonal::class)->autorizar(auth()->user(),$usuario);
        abort_unless($usuario->personal,422,'El trabajador no tiene ficha laboral.');
        $this->personalLaboral=$usuario->personal->getKey();
        $this->estadoLaboralEsperado=$usuario->personal->estado;
        $this->nombreLaboral=$usuario->name;
        $this->nuevoEstadoLaboral=$this->estadoLaboralEsperado==='ACTIVO' ? 'INACTIVO' : 'ACTIVO';
        $this->fechaEfectoLaboral=now()->format('Y-m-d\TH:i');
        $this->motivoLaboral=''; $this->modalLaboral=true; $this->resetValidation();
    }

    public function cerrarEstadoLaboral(): void { $this->modalLaboral=false; $this->personalLaboral=null; $this->resetValidation(); }

    public function guardarEstadoLaboral(): void
    {
        app(\App\Modulos\Identidad\Aplicacion\Acciones\RegistrarMovimientoLaboral::class)->ejecutar(auth()->user(),$this->personalLaboral ?? '',[
            'estado_esperado'=>$this->estadoLaboralEsperado,'estado_nuevo'=>$this->nuevoEstadoLaboral,
            'fecha_hora_efecto'=>$this->fechaEfectoLaboral,'motivo'=>$this->motivoLaboral]);
        $this->cerrarEstadoLaboral();
        $this->dispatch('swal',['icon'=>'success','title'=>'Situación laboral registrada',
            'text'=>'La cuenta conserva su acceso. Administración debe revisar las asignaciones del trabajador.']);
    }

    private function obtenerUsuarios(): Collection
    {
        if (! in_array($this->tabActiva, ['salud', 'admin'], true)) {
            return collect();
        }

        $query = $this->baseUsuariosInstitucionales()
            ->with([
                'roles',
                'horariosSalud',
                'horariosAdmin',
            ]);

        $this->aplicarTabActiva($query);
        $this->aplicarBusqueda($query);
        $this->aplicarFiltros($query);

        return $query
            ->orderBy('correo')
            ->get();
    }

    private function obtenerPersonalResumenPayload(): array
    {
        if ($this->tabActiva !== 'resumen' && !app(AutorizarGestionPersonal::class)->esGerencia(auth()->user())) {
            return [
                'items' => collect(),
                'total' => 0,
            ];
        }

        $query = $this->baseUsuariosInstitucionales()
            ->with(app(AutorizarGestionPersonal::class)->esGerencia(auth()->user()) ? ['personal', 'roles'] : [
                'roles',
                'horariosSalud',
                'horariosAdmin',
                // En V2 el turno pertenece a la jornada, no directamente a la asignación.
                'asignacionesTurno.jornada.turno',
                'areaInstitucional',
            ]);

        $this->aplicarRubroResumen($query);
        $this->aplicarBusquedaResumen($query);
        $this->aplicarEstadoResumen($query);
        $this->aplicarTurnoResumen($query);

        $items = $query
            ->orderBy('correo')
            ->orderBy('cod_usuario')
            ->paginate(12, pageName: 'personalPage');

        return [
            'items' => $items,
            'total' => $items->total(),
        ];
    }

    private function aplicarRubroResumen(Builder $query): void
    {
        if ($this->rubroResumen === 'salud') {
            $query->whereHas('roles', function (Builder $roleQuery) {
                $roleQuery->whereIn('name', $this->rolesSalud());
            });

            return;
        }

        if ($this->rubroResumen === 'admin') {
            $query->whereHas('roles', function (Builder $roleQuery) {
                $roleQuery->whereIn('name', $this->rolesAdministrativos());
            });

            return;
        }

        if ($this->rubroResumen === 'sistema') {
            $query->whereHas('roles', function (Builder $roleQuery) {
                $roleQuery->whereIn('name', $this->rolesSistema());
            });

            return;
        }

        if ($this->rubroResumen === 'activos') {
            $this->aplicarEstado($query, 'activo');

            return;
        }

        if ($this->rubroResumen === 'en_turno') {
            $query->whereIn('cod_usuario', $this->usuariosConHorarioActivoIds());

            return;
        }

        if ($this->rubroResumen === 'fuera_turno') {
            $this->aplicarEstado($query, 'activo');

            $query->whereNotIn('cod_usuario', $this->usuariosConHorarioActivoIds());

            return;
        }

        if ($this->rubroResumen === 'incidencias') {
            $query->where(function (Builder $subQuery) {
                $subQuery
                    ->where('estado', 'INACTIVO')
                    ->orWhere('estado', 'SUSPENDIDO')
                    ->orWhere('estado', 0)
                    ->orWhere('estado', false)
                    ->orWhere('estado', '0');
            });
        }
    }

    private function aplicarBusquedaResumen(Builder $query): void
    {
        $busqueda = trim($this->busquedaResumen);

        if ($busqueda === '') {
            return;
        }

        $this->aplicarBusquedaFlexible($query, $busqueda);
    }

    private function aplicarEstadoResumen(Builder $query): void
    {
        if ($this->estadoResumen === 'activo') {
            $this->aplicarEstado($query, 'activo');
        } elseif ($this->estadoResumen === 'inactivo') {
            $this->aplicarEstado($query, 'inactivo');
        } elseif ($this->estadoResumen === 'suspendido') {
            $this->aplicarEstado($query, 'suspendido');
        }
    }

    private function aplicarTurnoResumen(Builder $query): void
    {
        if ($this->turnoResumen === '') {
            return;
        }

        if ($this->turnoResumen === 'sin_turno') {
            $query->whereNotIn('cod_usuario', $this->usuariosConHorarioActivoIds());

            return;
        }

        $query->whereIn('cod_usuario', $this->usuariosConTurnoIds($this->turnoResumen));
    }

    private function obtenerResumenInstitucional(): array
    {
        $salud = $this->contarUsuariosPorCategoria('salud');
        $admin = $this->contarUsuariosPorCategoria('admin');
        $sistema = $this->contarUsuariosPorCategoria('sistema');

        $total = [
            'categoria' => 'Total institucional',
            'codigo' => 'total',
            'icono' => 'ph-buildings',
            'total' => $salud['total'] + $admin['total'] + $sistema['total'],
            'activos' => $salud['activos'] + $admin['activos'] + $sistema['activos'],
            'inactivos' => $salud['inactivos'] + $admin['inactivos'] + $sistema['inactivos'],
            'suspendidos' => $salud['suspendidos'] + $admin['suspendidos'] + $sistema['suspendidos'],
            'retirados' => $salud['retirados'] + $admin['retirados'] + $sistema['retirados'],
        ];

        return [
            'filas' => [
                $salud,
                $admin,
                $sistema,
                $total,
            ],
            'total' => $total,
        ];
    }

    private function obtenerEstadisticas(): array
    {
        $baseInstitucional = $this->baseUsuariosInstitucionales();

        $totalUsuarios = (clone $baseInstitucional)->count();
        $totalActivos = $this->contarEstado((clone $baseInstitucional), 'activo');
        $totalInactivos = $this->contarEstado((clone $baseInstitucional), 'inactivo');
        $totalSuspendidos = $this->contarEstado((clone $baseInstitucional), 'suspendido');
        $totalRetirados = $this->contarEstado((clone $baseInstitucional), 'retirado');

        $totalSalud = $this->contarUsuariosPorCategoria('salud')['total'];
        $totalAdmin = $this->contarUsuariosPorCategoria('admin')['total'];
        $totalSistema = $this->contarUsuariosPorCategoria('sistema')['total'];

        $totalEnTurno = $this->contarPersonalEnTurno();
        $totalEnTurno = min($totalActivos, $totalEnTurno);

        $totalFueraTurno = max(0, $totalActivos - $totalEnTurno);
        $incidencias = $totalSuspendidos + $totalInactivos;

        return [
            'total' => $totalUsuarios,
            'activos' => $totalActivos,
            'inactivos' => $totalInactivos,
            'suspendidos' => $totalSuspendidos,
            'retirados' => $totalRetirados,

            'salud' => $totalSalud,
            'admin' => $totalAdmin,
            'sistema' => $totalSistema,

            'medicos' => $this->contarPersonalSalud([
                'MEDICO',
                'MÉDICO',
                'MEDICO GENERAL',
                'MÉDICO GENERAL',
                'MEDICO GENERAL/GERIATRA',
                'MÉDICO GENERAL/GERIATRA',
                'GERIATRA',
            ]),

            'enfermeros' => $this->contarPersonalSalud([
                'ENFERMERO',
                'ENFERMERA',
                'ENFERMEROS',
                'ENFERMERAS',
            ]),

            'psicologos' => $this->contarPersonalSalud([
                'PSICOLOGO',
                'PSICÓLOGO',
                'PSICOLOGA',
                'PSICÓLOGA',
                'PSICOLOGO/A',
                'PSICÓLOGO/A',
            ]),

            'fisioterapeutas' => $this->contarPersonalSalud([
                'FISIOTERAPEUTA',
                'FISIOTERAPIA',
            ]),

            'nutricionistas' => $this->contarPersonalSalud([
                'NUTRICIONISTA',
                'NUTRICION',
                'NUTRICIÓN',
            ]),

            'en_turno' => $totalEnTurno,
            'fuera_turno' => $totalFueraTurno,
            'incidencias' => $incidencias,
        ];
    }

    private function obtenerChartData(array $estadisticas): array
    {
        $empty = [
            'area_labels' => [],
            'area_data' => [],

            'estado_labels' => [],
            'estado_data' => [],

            'turno_labels' => [],
            'turno_data' => [],

            'salud_labels' => [],
            'salud_data' => [],

            'disp_labels' => [],
            'disp_data' => [],
        ];

        if ($this->tabActiva !== 'reportes') {
            return $empty;
        }

        $asignaciones = AsignacionPersonal::query()
            ->with(['jornada.turno', 'personal.usuario.roles'])
            ->where('estado', 'ACTIVA')
            ->get();
        $saludTurnos = $asignaciones
            ->filter(fn (AsignacionPersonal $asignacion) => $asignacion->personal?->usuario?->hasAnyRole($this->rolesSalud()))
            ->map(fn (AsignacionPersonal $asignacion) => (object) ['turno' => $asignacion->jornada?->turno]);
        $adminTurnos = $asignaciones
            ->filter(fn (AsignacionPersonal $asignacion) => $asignacion->personal?->usuario?->hasAnyRole($this->rolesAdministrativos()))
            ->map(fn (AsignacionPersonal $asignacion) => (object) ['turno' => $asignacion->jornada?->turno]);

        $turnosGrouped = $saludTurnos->concat($adminTurnos)
            ->groupBy(fn ($asignacion) => $asignacion->turno?->nombre ?? 'Sin turno');

        return [
            'area_labels' => ['Salud', 'Administrativo', 'Administrador del sistema'],
            'area_data' => [
                $estadisticas['salud'],
                $estadisticas['admin'],
                $estadisticas['sistema'],
            ],

            'estado_labels' => ['Activo', 'Inactivo', 'Suspendido', 'Retirado'],
            'estado_data' => [
                $estadisticas['activos'],
                $estadisticas['inactivos'],
                $estadisticas['suspendidos'],
                $estadisticas['retirados'],
            ],

            'turno_labels' => $turnosGrouped->keys()->values()->toArray(),
            'turno_data' => $turnosGrouped->map(fn ($grupo) => $grupo->count())->values()->toArray(),

            'salud_labels' => ['Médicos', 'Enfermeros', 'Psicólogos', 'Fisioterapeuta', 'Nutricionistas'],
            'salud_data' => [
                $estadisticas['medicos'],
                $estadisticas['enfermeros'],
                $estadisticas['psicologos'],
                $estadisticas['fisioterapeutas'],
                $estadisticas['nutricionistas'],
            ],

            'disp_labels' => ['Disponible', 'En turno', 'Fuera de turno', 'No disponible'],
            'disp_data' => [
                max(0, $estadisticas['activos'] - $estadisticas['en_turno']),
                $estadisticas['en_turno'],
                $estadisticas['fuera_turno'],
                $estadisticas['inactivos'] + $estadisticas['suspendidos'],
            ],
        ];
    }

    private function obtenerInterpretacionReportes(array $estadisticas): array
    {
        if ($this->tabActiva !== 'reportes') {
            return [];
        }

        $total = max(1, $estadisticas['total']);
        $porcentajeEnTurno = round(($estadisticas['en_turno'] / $total) * 100, 1);
        $porcentajeActivos = round(($estadisticas['activos'] / $total) * 100, 1);
        $porcentajeSalud = round(($estadisticas['salud'] / $total) * 100, 1);

        return [
            [
                'icono' => 'ph-clock-user',
                'indicador' => 'Cobertura operativa',
                'resultado' => "{$porcentajeEnTurno}%",
                'interpretacion' => $estadisticas['en_turno'] > 0
                    ? 'Existe personal asignado actualmente a turnos institucionales.'
                    : 'No se registra personal asignado a turnos activos.',
                'accion' => $estadisticas['en_turno'] > 0
                    ? 'Mantener monitoreo de carga por turno.'
                    : 'Revisar y asignar horarios activos.',
                'estado' => $estadisticas['en_turno'] > 0 ? 'positivo' : 'alerta',
            ],
            [
                'icono' => 'ph-check-circle',
                'indicador' => 'Disponibilidad institucional',
                'resultado' => "{$porcentajeActivos}%",
                'interpretacion' => 'Porcentaje de personal habilitado dentro del módulo institucional.',
                'accion' => 'Revisar estados de personal inactivo o suspendido.',
                'estado' => $estadisticas['activos'] > 0 ? 'positivo' : 'alerta',
            ],
            [
                'icono' => 'ph-warning-circle',
                'indicador' => 'Incidencias registradas',
                'resultado' => $estadisticas['incidencias'].' caso(s)',
                'interpretacion' => $estadisticas['incidencias'] > 0
                    ? 'Existen registros que requieren revisión administrativa.'
                    : 'No se detectan incidencias activas en el personal.',
                'accion' => $estadisticas['incidencias'] > 0
                    ? 'Dar seguimiento a estados inactivos o suspendidos.'
                    : 'Mantener control periódico.',
                'estado' => $estadisticas['incidencias'] > 0 ? 'advertencia' : 'positivo',
            ],
            [
                'icono' => 'ph-stethoscope',
                'indicador' => 'Distribución por áreas',
                'resultado' => "Salud {$porcentajeSalud}%",
                'interpretacion' => 'Participación del área de salud sobre el total institucional.',
                'accion' => 'Evaluar equilibrio entre salud, administración y sistema.',
                'estado' => 'info',
            ],
        ];
    }

    private function baseUsuariosInstitucionales(): Builder
    {
        return User::query()
            ->where(function (Builder $query) {
                $this->aplicarFiltroInstitucional($query);
            })
            ->whereDoesntHave('roles', function (Builder $query) {
                $query->whereIn('name', $this->rolesExcluidos());
            });
    }

    private function aplicarFiltroInstitucional(Builder $query): void
    {
        $query->whereHas('roles', function (Builder $roleQuery) {
            $roleQuery->whereIn('name', $this->rolesInstitucionales());
        });
    }

    private function contarUsuariosPorCategoria(string $categoria): array
    {
        $query = $this->baseUsuariosInstitucionales();

        if ($categoria === 'salud') {
            $query->whereHas('roles', function (Builder $roleQuery) {
                $roleQuery->whereIn('name', $this->rolesSalud());
            });

            $titulo = 'Salud';
            $icono = 'ph-stethoscope';
        } elseif ($categoria === 'admin') {
            $query->whereHas('roles', function (Builder $roleQuery) {
                $roleQuery->whereIn('name', $this->rolesAdministrativos());
            });

            $titulo = 'Administrativo';
            $icono = 'ph-desktop';
        } else {
            $query->whereHas('roles', function (Builder $roleQuery) {
                $roleQuery->whereIn('name', $this->rolesSistema());
            });

            $titulo = 'Administrador del sistema';
            $icono = 'ph-shield-star';
        }

        $total = (clone $query)->count();

        return [
            'categoria' => $titulo,
            'codigo' => $categoria,
            'icono' => $icono,
            'total' => $total,
            'activos' => $this->contarEstado((clone $query), 'activo'),
            'inactivos' => $this->contarEstado((clone $query), 'inactivo'),
            'suspendidos' => $this->contarEstado((clone $query), 'suspendido'),
            'retirados' => $this->contarEstado((clone $query), 'retirado'),
        ];
    }

    private function aplicarTabActiva(Builder $query): void
    {
        if ($this->tabActiva === 'salud') {
            $query->whereHas('roles', function (Builder $roleQuery) {
                $roleQuery->whereIn('name', $this->rolesSalud());
            });

            return;
        }

        if ($this->tabActiva === 'admin') {
            $query->whereHas('roles', function (Builder $roleQuery) {
                $roleQuery->whereIn('name', array_merge(
                    $this->rolesAdministrativos(),
                    $this->rolesSistema()
                ));
            });
        }
    }

    private function aplicarBusqueda(Builder $query): void
    {
        $busqueda = trim($this->busqueda);

        if ($busqueda === '') {
            return;
        }

        $this->aplicarBusquedaFlexible($query, $busqueda);
    }

    private function aplicarBusquedaFlexible(Builder $query, string $busqueda): void
    {
        $query->where(function (Builder $subQuery) use ($busqueda) {
            $subQuery->whereLike('correo', "%{$busqueda}%")
                ->orWhereLike('cod_usuario', "%{$busqueda}%")
                ->orWhereHas('personal', function (Builder $personal) use ($busqueda) {
                    $personal->whereLike('nombres', "%{$busqueda}%")
                        ->orWhereLike('apellido_paterno', "%{$busqueda}%")
                        ->orWhereLike('apellido_materno', "%{$busqueda}%")
                        ->orWhereLike('numero_documento', "%{$busqueda}%");
                });
        });
    }

    private function aplicarFiltros(Builder $query): void
    {
        if ($this->filtroTipo === 'salud') {
            $query->whereHas('roles', function (Builder $roleQuery) {
                $roleQuery->whereIn('name', $this->rolesSalud());
            });
        }

        if ($this->filtroTipo === 'admin') {
            $query->whereHas('roles', function (Builder $roleQuery) {
                $roleQuery->whereIn('name', array_merge(
                    $this->rolesAdministrativos(),
                    $this->rolesSistema()
                ));
            });
        }

        if ($this->filtroRol !== '') {
            $rol = match ($this->filtroRol) {
                'medico' => 'MEDICO GENERAL/GERIATRA',
                'enfermero' => 'ENFERMEROS',
                'psicologo' => 'PSICOLOGO/A',
                'fisioterapeuta' => 'FISIOTERAPEUTA',
                'nutricionista' => 'NUTRICIONISTA',
                default => strtoupper($this->filtroRol),
            };

            $query->whereHas('roles', function (Builder $subQuery) use ($rol) {
                $subQuery->where('name', $rol);
            });
        }

        if ($this->filtroEstado === 'activo') {
            $this->aplicarEstado($query, 'activo');
        } elseif ($this->filtroEstado === 'inactivo') {
            $this->aplicarEstado($query, 'inactivo');
        } elseif ($this->filtroEstado === 'suspendido') {
            $this->aplicarEstado($query, 'suspendido');
        }

        if ($this->filtroDisponibilidad === 'ocupado') {
            $query->whereIn('cod_usuario', $this->usuariosConHorarioActivoIds());
        }

        if ($this->filtroDisponibilidad === 'libre') {
            $query->whereNotIn('cod_usuario', $this->usuariosConHorarioActivoIds());
        }
    }

    private function aplicarEstado(Builder $query, string $estado): void
    {
        if ($estado === 'activo') {
            $query->where(function (Builder $subQuery) {
                $subQuery
                    ->where('estado', 'ACTIVO')
                    ->orWhere('estado', 1)
                    ->orWhere('estado', true)
                    ->orWhere('estado', '1');
            });

            return;
        }

        if ($estado === 'inactivo') {
            $query->where(function (Builder $subQuery) {
                $subQuery
                    ->where('estado', 'INACTIVO')
                    ->orWhere('estado', 0)
                    ->orWhere('estado', false)
                    ->orWhere('estado', '0');
            });

            return;
        }

        if ($estado === 'suspendido') {
            $query->where('estado', 'SUSPENDIDO');

            return;
        }

        if ($estado === 'retirado') {
            $query->where('estado', 'RETIRADO');
        }
    }

    private function contarEstado(Builder $query, string $estado): int
    {
        $this->aplicarEstado($query, $estado);

        return $query->count();
    }

    private function contarPersonalEnTurno(): int
    {
        return count($this->usuariosConHorarioActivoIds());
    }

    private function usuariosConHorarioActivoIds(): array
    {
        return AsignacionPersonal::query()
            ->where('estado', 'ACTIVA')
            ->whereHas('jornada', fn (Builder $jornada) => $jornada->where('estado', 'ABIERTA'))
            ->with('personal:cod_personal,cod_usuario')
            ->get()
            ->pluck('personal.cod_usuario')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function usuariosConTurnoIds(string $turno): array
    {
        return AsignacionPersonal::query()
            ->where('estado', 'ACTIVA')
            ->whereHas('jornada.turno', fn (Builder $query) => $query->whereLike('nombre', "%{$turno}%"))
            ->with('personal:cod_personal,cod_usuario')
            ->get()
            ->pluck('personal.cod_usuario')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function existeUsuarioInstitucional(string $usuarioId): bool
    {
        return $this->baseUsuariosInstitucionales()
            ->where('cod_usuario', $usuarioId)
            ->exists();
    }

    private function contarPersonalSalud(array $tipos): int
    {
        $hasMedico = false;
        $hasEnfermero = false;
        $hasPsicologo = false;
        $hasFisio = false;
        $hasNutri = false;

        foreach ($tipos as $tipo) {
            $t = strtoupper($tipo);
            if (str_contains($t, 'MEDIC') || str_contains($t, 'GERIATR')) {
                $hasMedico = true;
            }
            if (str_contains($t, 'ENFERM')) {
                $hasEnfermero = true;
            }
            if (str_contains($t, 'PSICOL')) {
                $hasPsicologo = true;
            }
            if (str_contains($t, 'FISIO')) {
                $hasFisio = true;
            }
            if (str_contains($t, 'NUTRI')) {
                $hasNutri = true;
            }
        }

        $roles = [];
        if ($hasMedico) {
            $roles[] = 'MEDICO GENERAL/GERIATRA';
        }
        if ($hasEnfermero) {
            $roles[] = 'ENFERMEROS';
        }
        if ($hasPsicologo) {
            $roles[] = 'PSICOLOGO/A';
        }
        if ($hasFisio) {
            $roles[] = 'FISIOTERAPEUTA';
        }
        if ($hasNutri) {
            $roles[] = 'NUTRICIONISTA';
        }

        if (empty($roles)) {
            return 0;
        }

        return User::role($roles)->where('estado', 'ACTIVO')->count();
    }

    private function tiposSaludPorFiltro(string $filtro): array
    {
        return match ($filtro) {
            'medico' => [
                'MEDICO',
                'MÉDICO',
                'MEDICO GENERAL',
                'MÉDICO GENERAL',
                'MEDICO GENERAL/GERIATRA',
                'MÉDICO GENERAL/GERIATRA',
                'GERIATRA',
            ],
            'enfermero' => [
                'ENFERMERO',
                'ENFERMERA',
                'ENFERMEROS',
                'ENFERMERAS',
            ],
            'psicologo' => [
                'PSICOLOGO',
                'PSICÓLOGO',
                'PSICOLOGA',
                'PSICÓLOGA',
                'PSICOLOGO/A',
                'PSICÓLOGO/A',
            ],
            'fisioterapeuta' => [
                'FISIOTERAPEUTA',
                'FISIOTERAPIA',
            ],
            'nutricionista' => [
                'NUTRICIONISTA',
                'NUTRICION',
                'NUTRICIÓN',
            ],
            default => [strtoupper($filtro)],
        };
    }

    private function rolesInstitucionales(): array
    {
        return array_merge(
            $this->rolesSistema(),
            $this->rolesSalud(),
            $this->rolesAdministrativos()
        );
    }

    private function rolesSistema(): array
    {
        return [
            'admin',
            'ADMIN',
            'Administrador',
            'ADMINISTRADOR',
            'Super Administrador',
            'SUPERADMINISTRADOR',
            'superadmin',
            'SuperAdmin',
        ];
    }

    private function rolesSalud(): array
    {
        return [
            'personal_salud',
            'PERSONAL_SALUD',
            'PERSONAL SALUD',
            'MEDICO GENERAL/GERIATRA',
            'MÉDICO GENERAL/GERIATRA',
            'MEDICO',
            'MÉDICO',
            'ENFERMEROS',
            'ENFERMERO',
            'ENFERMERA',
            'PSICOLOGO/A',
            'PSICÓLOGO/A',
            'NUTRICIONISTA',
            'FISIOTERAPEUTA',
            'PEDAGOGO',
        ];
    }

    private function rolesAdministrativos(): array
    {
        return [
            'GERENTE',
            'personal_admin',
            'PERSONAL_ADMIN',
            'PERSONAL ADMIN',
            'ADMINISTRATIVO',
            'PERSONAL ADMINISTRATIVO',
        ];
    }

    private function rolesExcluidos(): array
    {
        return [
            'familiar',
            'FAMILIAR',
        ];
    }

    private function esEstadoActivo(mixed $estado): bool
    {
        return $estado === 'ACTIVO'
            || $estado === 1
            || $estado === true
            || $estado === '1';
    }
}
