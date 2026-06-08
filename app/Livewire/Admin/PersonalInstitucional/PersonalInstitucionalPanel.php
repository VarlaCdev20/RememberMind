<?php

namespace App\Livewire\Admin\PersonalInstitucional;

use App\Models\AsignacionTurno;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class PersonalInstitucionalPanel extends Component
{
    public string $tabActiva = 'resumen';
    public int $tabVersion = 0;

    public string $vistaListadoResumen = 'tarjetas';
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

    protected $listeners = [
        'cerrarModalGestion' => 'cerrarModal',
        'actualizarTablaPersonal' => '$refresh',
        'asignacionActualizada' => '$refresh',
        'abrirHorariosPersonal' => 'abrirHorariosPersonal',
        'personalRegistradoParaHorario' => 'continuarHorariosNuevoPersonal',
    ];

    public function render()
    {
        $estadisticas = $this->obtenerEstadisticas();
        $resumenInstitucional = $this->obtenerResumenInstitucional();

        $personalResumenPayload = $this->obtenerPersonalResumenPayload();
        $personalResumen = $personalResumenPayload['items'];
        $personalResumenTotal = $personalResumenPayload['total'];

        $usuarios = $this->obtenerUsuarios();
        $chartData = $this->obtenerChartData($estadisticas);
        $interpretacionReportes = $this->obtenerInterpretacionReportes($estadisticas);

        return view('livewire.admin.personal-institucional.personal-institucional-panel', [
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

        if (!in_array($tab, $tabsPermitidas, true)) {
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
        if (!in_array($vista, ['tarjetas', 'tabla'], true)) {
            $vista = 'tarjetas';
        }

        $this->vistaListadoResumen = $vista;
    }

    public function limpiarFiltrosResumen(): void
    {
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
        $this->usuarioSeleccionadoId = null;
        $this->modalTabInicial = 'informacion';
        $this->abrirFormularioHorarioInicial = false;
        $this->modalGestionAbierto = true;
    }

    public function abrirModalEdicion(string $usuarioId): void
    {
        if (!$this->existeUsuarioInstitucional($usuarioId)) {
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
        if (!$this->existeUsuarioInstitucional($usuarioId)) {
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

    public function toggleEstado(string $usuarioId): void
    {
        $usuario = $this->baseUsuariosInstitucionales()
            ->where('cod_usu', $usuarioId)
            ->first();

        if (!$usuario) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Registro no encontrado',
                'text' => 'No se encontró el personal seleccionado.',
            ]);

            return;
        }

        $usuario->estado = $this->esEstadoActivo($usuario->estado)
            ? 'SUSPENDIDO'
            : 'ACTIVO';

        $usuario->save();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Estado actualizado',
            'text' => 'El estado del personal fue actualizado correctamente.',
        ]);

        $this->dispatch('actualizarTablaPersonal');
    }

    private function obtenerUsuarios(): Collection
    {
        if (!in_array($this->tabActiva, ['salud', 'admin'], true)) {
            return collect();
        }

        $query = $this->baseUsuariosInstitucionales()
            ->with([
                'areaInstitucional',
                'roles',
                'asignacionesTurno.turno',
            ]);

        $this->aplicarTabActiva($query);
        $this->aplicarBusqueda($query);
        $this->aplicarFiltros($query);

        return $query
            ->orderByDesc('created_at')
            ->get();
    }

    private function obtenerPersonalResumenPayload(): array
    {
        if ($this->tabActiva !== 'resumen') {
            return [
                'items' => collect(),
                'total' => 0,
            ];
        }

        $query = $this->baseUsuariosInstitucionales()
            ->with([
                'roles',
                'asignacionesTurno.turno',
            ]);

        $this->aplicarRubroResumen($query);
        $this->aplicarBusquedaResumen($query);
        $this->aplicarEstadoResumen($query);
        $this->aplicarTurnoResumen($query);

        $total = (clone $query)->count();

        $items = $query
            ->orderByDesc('created_at')
            ->limit($this->vistaListadoResumen === 'tarjetas' ? 8 : 12)
            ->get();

        return [
            'items' => $items,
            'total' => $total,
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
            $query->whereHas('asignacionesTurno', function (Builder $subQuery) {
                $subQuery->whereIn('estado', ['ACTIVO', 'ACTIVA']);
            });

            return;
        }

        if ($this->rubroResumen === 'fuera_turno') {
            $this->aplicarEstado($query, 'activo');

            $query->whereDoesntHave('asignacionesTurno', function (Builder $subQuery) {
                $subQuery->whereIn('estado', ['ACTIVO', 'ACTIVA']);
            });

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
            $query->whereDoesntHave('asignacionesTurno', function (Builder $subQuery) {
                $subQuery->whereIn('estado', ['ACTIVO', 'ACTIVA']);
            });

            return;
        }

        $query->whereHas('asignacionesTurno.turno', function (Builder $subQuery) {
            $subQuery->where('nombre', 'ilike', "%{$this->turnoResumen}%");
        });
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

        $turnosGrouped = AsignacionTurno::whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->whereHas('usuario', function (Builder $query) {
                $query
                    ->where(fn (Builder $subQuery) => $this->aplicarFiltroInstitucional($subQuery))
                    ->whereDoesntHave('roles', function (Builder $roleQuery) {
                        $roleQuery->whereIn('name', $this->rolesExcluidos());
                    });
            })
            ->with('turno')
            ->get()
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
                'resultado' => $estadisticas['incidencias'] . ' caso(s)',
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
            $columnas = [
                'name',
                'nombres',
                'ap_paterno',
                'ap_materno',
                'correo',
                'email',
                'ci',
                'carnet',
                'cod_usu',
            ];

            foreach ($columnas as $columna) {
                if (Schema::hasColumn('users', $columna)) {
                    $subQuery->orWhere($columna, 'ilike', "%{$busqueda}%");
                }
            }
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
            $tipos = $this->tiposSaludPorFiltro($this->filtroRol);

            if (!empty($tipos)) {
                $query->whereHas('roles', function (Builder $subQuery) use ($tipos) {
                    $subQuery->whereIn('name', $tipos);
                });
            }
        }

        if ($this->filtroEstado === 'activo') {
            $this->aplicarEstado($query, 'activo');
        } elseif ($this->filtroEstado === 'inactivo') {
            $this->aplicarEstado($query, 'inactivo');
        } elseif ($this->filtroEstado === 'suspendido') {
            $this->aplicarEstado($query, 'suspendido');
        }

        if ($this->filtroDisponibilidad === 'ocupado') {
            $query->whereHas('asignacionesTurno', function (Builder $subQuery) {
                $subQuery->whereIn('estado', ['ACTIVO', 'ACTIVA']);
            });
        }

        if ($this->filtroDisponibilidad === 'libre') {
            $query->whereDoesntHave('asignacionesTurno', function (Builder $subQuery) {
                $subQuery->whereIn('estado', ['ACTIVO', 'ACTIVA']);
            });
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
        return AsignacionTurno::whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->whereNotNull('cod_usu')
            ->whereHas('usuario', function (Builder $query) {
                $query
                    ->where(fn (Builder $subQuery) => $this->aplicarFiltroInstitucional($subQuery))
                    ->whereDoesntHave('roles', function (Builder $roleQuery) {
                        $roleQuery->whereIn('name', $this->rolesExcluidos());
                    });
            })
            ->distinct()
            ->count('cod_usu');
    }

    private function existeUsuarioInstitucional(string $usuarioId): bool
    {
        return $this->baseUsuariosInstitucionales()
            ->where('cod_usu', $usuarioId)
            ->exists();
    }

    private function contarPersonalSalud(array $tipos): int
    {
        return User::whereHas('roles', fn (Builder $q) => $q->whereIn('name', $tipos))->count();
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
            'voluntario',
            'VOLUNTARIO',
            'VOLUNTARIOS',
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