<?php

namespace App\Livewire\Identidad;

use App\Models\AreaInstitucional;
use App\Models\AsignacionPersonal;
use App\Models\Jornada;
use App\Models\TurnoInstitucional;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class PersonalInstitucionalHorarios extends Component
{
    private const DIAS = [
        'LUNES',
        'MARTES',
        'MIERCOLES',
        'JUEVES',
        'VIERNES',
        'SABADO',
        'DOMINGO',
    ];

    private const ROLES_SALUD = [
        'ENFERMEROS',
        'MEDICO GENERAL/GERIATRA',
        'PSICOLOGO/A',
        'PEDAGOGO',
        'NUTRICIONISTA',
        'FISIOTERAPEUTA',
    ];

    private const ROLES_ADMIN = [
        'SUPERADMINISTRADOR',
        'ADMINISTRADOR',
    ];

    protected $listeners = [
        'actualizarTablaPersonal' => 'cargarDatos',
    ];

    public string $usuarioId = '';
    public bool $abrirFormularioInicial = false;

    public string $tipoPersonal = 'ninguno';
    public string $subtipoSalud = '';
    public string $estadoUsuario = '';
    public array $rolesUsuario = [];
    public bool $sinRol = false;
    public bool $esEnfermeria = false;

    public array $asignaciones = [];
    public array $areas = [];
    public array $turnos = [];

    public bool $formAbierto = false;
    public bool $modoEdicion = false;
    public ?string $codAsignacionEditar = null;

    public string $f_cod_area = '';
    public string $f_cod_turno = '';
    public array $f_dias_semana = [];
    public string $f_fecha_inicio = '';
    public string $f_fecha_fin = '';
    public string $f_tipo_asignacion = 'FIJO';
    public string $f_observaciones = '';

    public bool $modalFinalizarAbierto = false;
    public ?string $codAsignacionFinalizar = null;
    public string $f_fecha_fin_finalizar = '';

    public bool $modalDetalleAbierto = false;
    public array $detalleAsignacion = [];

    public array $diasSemana = self::DIAS;

    public function mount(string $usuarioId, bool $abrirFormularioInicial = false): void
    {
        $this->usuarioId = $usuarioId;
        $this->abrirFormularioInicial = $abrirFormularioInicial;
        $this->f_fecha_inicio = now()->format('Y-m-d');
        $this->f_fecha_fin_finalizar = now()->format('Y-m-d');

        $this->cargarDatos();

        if ($this->abrirFormularioInicial) {
            $this->abrirFormNuevo();
        }
    }

    public function cargarDatos(): void
    {
        $usuario = $this->usuario();

        if (! $usuario) {
            return;
        }

        $this->estadoUsuario = (string) $usuario->estado;
        $this->rolesUsuario = $usuario->roles->pluck('name')->values()->all();
        $this->sinRol = $this->rolesUsuario === [];

        $this->resolverTipoPersonal($usuario);
        $this->areas = $this->cargarAreas();
        $this->turnos = $this->cargarTurnos();
        $this->asignaciones = $this->cargarAsignacionesAgrupadas($usuario)->toArray();
    }

    public function abrirFormNuevo(): void
    {
        if ($this->sinRol) {
            $this->dispatch('mostrarAlerta', [
                'type' => 'warning',
                'title' => 'Usuario sin rol',
                'message' => 'Asigne un rol institucional antes de registrar un horario.',
            ]);
            return;
        }

        if ($this->usuarioBloqueado()) {
            $this->dispatch('mostrarAlerta', [
                'type' => 'warning',
                'title' => 'Usuario no disponible',
                'message' => 'No se puede asignar horario a personal INACTIVO, SUSPENDIDO o RETIRADO.',
            ]);
            return;
        }

        $this->resetFormulario();
        $this->f_cod_area = $this->codAreaUsuario($this->usuario());
        $this->formAbierto = true;
        $this->modoEdicion = false;
    }

    public function editarAsignacion(string $cod): void
    {
        if ($this->sinRol || $this->usuarioBloqueado()) {
            $this->dispatch('mostrarAlerta', [
                'type' => 'warning',
                'title' => 'Edición no disponible',
                'message' => $this->sinRol
                    ? 'El usuario debe tener un rol institucional antes de modificar su horario.'
                    : 'No se puede modificar el horario de personal INACTIVO, SUSPENDIDO o RETIRADO.',
            ]);
            return;
        }

        $asignacion = collect($this->asignaciones)->firstWhere('cod_asignacion', $cod);

        if (! $asignacion || ($asignacion['estado'] ?? null) !== 'ACTIVA') {
            $this->dispatch('mostrarAlerta', [
                'type' => 'warning',
                'title' => 'No editable',
                'message' => 'Solo se pueden editar asignaciones activas.',
            ]);
            return;
        }

        $usuario = $this->usuario();
        $turno = $this->buscarTurnoCompatible(
            $asignacion['turno_nombre'] ?? '',
            $asignacion['hora_inicio_raw'] ?? null,
            $asignacion['hora_fin_raw'] ?? null
        );

        $this->codAsignacionEditar = $cod;
        $this->f_cod_area = $this->codAreaUsuario($usuario);
        $this->f_cod_turno = $turno['cod_turno'] ?? '';
        $this->f_dias_semana = $asignacion['dias_semana_full'] ?? [];
        $this->f_fecha_inicio = $this->fechaParaInput($asignacion['fecha_inicio_raw'] ?? null, now());
        $this->f_fecha_fin = $this->fechaParaInput($asignacion['fecha_fin_raw'] ?? null);
        $this->f_tipo_asignacion = $asignacion['tipo_asignacion'] ?? ($this->esEnfermeria ? 'ROTATIVO' : 'FIJO');
        $this->f_observaciones = $asignacion['observaciones'] ?? '';
        $this->modoEdicion = true;
        $this->formAbierto = true;
        $this->resetErrorBag();
    }

    public function guardar(): void
    {
        if ($this->sinRol) {
            $this->addError('f_cod_area', 'El usuario no tiene un rol institucional asignado.');
            return;
        }

        if ($this->usuarioBloqueado()) {
            $this->addError('f_cod_area', 'No se puede asignar horario a personal INACTIVO, SUSPENDIDO o RETIRADO.');
            return;
        }

        $this->validate([
            'f_cod_area' => 'required|string|exists:areas,cod_area',
            'f_cod_turno' => 'required|string|exists:turnos,cod_turno',
            'f_dias_semana' => 'required|array|min:1',
            'f_dias_semana.*' => 'required|in:LUNES,MARTES,MIERCOLES,JUEVES,VIERNES,SABADO,DOMINGO',
            'f_fecha_inicio' => 'required|date',
            'f_fecha_fin' => 'nullable|date|after_or_equal:f_fecha_inicio',
            'f_tipo_asignacion' => 'required|in:FIJO,ROTATIVO,TEMPORAL,EVENTUAL',
            'f_observaciones' => 'nullable|string|max:1000',
        ], [
            'f_cod_area.required' => 'Debe seleccionar un área.',
            'f_cod_area.exists' => 'Seleccione un área institucional válida.',
            'f_cod_turno.required' => 'Debe seleccionar un turno.',
            'f_dias_semana.required' => 'Seleccione al menos un día laboral.',
            'f_dias_semana.min' => 'Seleccione al menos un día laboral.',
            'f_fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'f_tipo_asignacion.required' => 'El tipo de asignación es obligatorio.',
        ]);

        $turno = TurnoInstitucional::query()
            ->where('cod_turno', $this->f_cod_turno)
            ->where('estado', 'ACTIVO')
            ->first();

        if (! $turno) {
            $this->addError('f_cod_turno', 'El turno seleccionado no está disponible.');
            return;
        }

        $mensajeSolapamiento = $this->verificarSolapamiento($turno);

        if ($mensajeSolapamiento) {
            $this->addError('f_dias_semana', $mensajeSolapamiento);
            return;
        }

        $usuario = $this->usuario();

        if (! $usuario) {
            $this->dispatch('mostrarAlerta', [
                'type' => 'error',
                'title' => 'Usuario no encontrado',
                'message' => 'No se pudo cargar el colaborador seleccionado.',
            ]);
            return;
        }

        $fechaInicio = Carbon::parse($this->f_fecha_inicio)->startOfDay();
        $isEdit = $this->modoEdicion;

        DB::beginTransaction();

        try {
            $personal = $usuario->personal;
            if (! $personal) {
                $this->addError('f_cod_area', 'El usuario no tiene un registro de personal V2 asociado.');
                DB::rollBack();
                return;
            }

            if ($isEdit && $this->codAsignacionEditar) {
                $this->actualizarEstadoAsignacionGrupo(
                    $this->codAsignacionEditar,
                    'INACTIVO',
                    $fechaInicio->copy()->subSecond()
                );
            }

            $grupo = 'GRP_'.Str::upper(Str::random(10));
            foreach ($this->f_dias_semana as $dia) {
                $fechaJornada = $this->fechaParaDia($fechaInicio, $dia);
                $jornada = Jornada::query()->firstOrCreate(
                    ['cod_turno' => $turno->cod_turno, 'fecha_jornada' => $fechaJornada->toDateString()],
                    ['cod_jornada' => 'JOR_'.Str::upper(Str::random(12)), 'estado' => 'ABIERTA']
                );
                AsignacionPersonal::query()->create([
                    'cod_asignacion_personal' => 'ASP_'.Str::upper(Str::random(12)),
                    'cod_jornada' => $jornada->cod_jornada,
                    'cod_personal' => $personal->cod_personal,
                    'cod_area' => $this->f_cod_area,
                    'funcion' => $personal->profesion,
                    'tipo_asignacion' => $this->f_tipo_asignacion,
                    'fecha_asignacion' => now(),
                    'estado' => 'ACTIVO',
                    'observacion' => $this->observacionesSerializadas($grupo),
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            $this->dispatch('mostrarAlerta', [
                'type' => 'error',
                'title' => 'Error al guardar',
                'message' => 'No se pudo registrar la asignación de horario.',
            ]);
            return;
        }

        $this->resetFormulario();
        $this->formAbierto = false;
        $this->cargarDatos();

        $this->dispatch('mostrarAlerta', [
            'type' => 'success',
            'title' => $isEdit ? 'Asignación actualizada' : 'Asignación creada',
            'message' => $isEdit
                ? 'El horario anterior se finalizó y la nueva asignación quedó registrada.'
                : 'Asignación de turno registrada correctamente.',
        ]);
        $this->dispatch('asignacionActualizada');
    }

    public function prepararFinalizar(string $cod): void
    {
        $asignacion = collect($this->asignaciones)->firstWhere('cod_asignacion', $cod);

        if (! $asignacion || ($asignacion['estado'] ?? null) !== 'ACTIVA') {
            return;
        }

        $this->codAsignacionFinalizar = $cod;
        $this->f_fecha_fin_finalizar = now()->format('Y-m-d');
        $this->modalFinalizarAbierto = true;
        $this->resetErrorBag();
    }

    public function confirmarFinalizar(): void
    {
        $this->validate([
            'f_fecha_fin_finalizar' => 'required|date',
        ], [
            'f_fecha_fin_finalizar.required' => 'Debe indicar la fecha de finalización.',
        ]);

        if (! $this->codAsignacionFinalizar) {
            return;
        }

        $fechaFin = Carbon::parse($this->f_fecha_fin_finalizar)->endOfDay();

        DB::beginTransaction();

        try {
            $this->actualizarEstadoAsignacionGrupo($this->codAsignacionFinalizar, 'INACTIVO', $fechaFin);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            $this->dispatch('mostrarAlerta', [
                'type' => 'error',
                'title' => 'No se pudo finalizar',
                'message' => 'La asignación no pudo marcarse como inactiva.',
            ]);
            return;
        }

        $this->modalFinalizarAbierto = false;
        $this->codAsignacionFinalizar = null;
        $this->cargarDatos();

        $this->dispatch('mostrarAlerta', [
            'type' => 'success',
            'title' => 'Asignación finalizada',
            'message' => 'La asignación fue marcada como inactiva.',
        ]);
        $this->dispatch('asignacionActualizada');
    }

    public function cancelarFinalizar(): void
    {
        $this->modalFinalizarAbierto = false;
        $this->codAsignacionFinalizar = null;
        $this->resetErrorBag();
    }

    public function verDetalle(string $cod): void
    {
        $asignacion = collect($this->asignaciones)->firstWhere('cod_asignacion', $cod);

        if (! $asignacion) {
            return;
        }

        $usuario = $this->usuario();

        $this->detalleAsignacion = [
            'cod_asignacion' => $asignacion['cod_asignacion'],
            'area' => $asignacion['area_nombre'] ?? ($usuario?->areaInstitucional?->nombre ?? '—'),
            'turno' => $asignacion['turno_nombre'] ?? '—',
            'hora_inicio' => $asignacion['hora_inicio'] ?? '—',
            'hora_fin' => $asignacion['hora_fin'] ?? '—',
            'dias_semana' => $asignacion['dias_semana_full'] ?? [],
            'fecha_inicio' => $asignacion['fecha_inicio'] ?? '—',
            'fecha_fin' => $asignacion['fecha_fin'] ?? 'Vigente',
            'estado' => $asignacion['estado'] ?? '—',
            'tipo_asignacion' => $asignacion['tipo_asignacion'] ?? 'FIJO',
            'observaciones' => $asignacion['observaciones'] !== '' ? $asignacion['observaciones'] : '—',
            'creado_por' => 'Sistema',
            'created_at' => $asignacion['fecha_inicio'] ?? '—',
        ];

        $this->modalDetalleAbierto = true;
    }

    public function cerrarDetalle(): void
    {
        $this->modalDetalleAbierto = false;
        $this->detalleAsignacion = [];
    }

    public function cancelarFormulario(): void
    {
        $this->resetFormulario();
        $this->formAbierto = false;
    }

    public function solicitarGuardar(): void
    {
        $this->dispatch('confirmarGuardadoAsignacion', [
            'title' => $this->modoEdicion ? 'Guardar nueva versión del horario' : 'Crear asignación',
            'message' => $this->modoEdicion
                ? 'La asignación activa actual se finalizará y se registrará una nueva versión.'
                : 'Se registrará el horario seleccionado para este usuario.',
        ]);
    }

    public function solicitarFinalizar(): void
    {
        $this->dispatch('confirmarFinalizacionAsignacion');
    }

    public function render(): View
    {
        return view('livewire.identidad.personal-institucional-horarios');
    }

    private function usuario(): ?User
    {
        return User::with(['roles', 'personal.asignaciones.area'])
            ->where('cod_usuario', $this->usuarioId)
            ->first();
    }

    private function resolverTipoPersonal(User $usuario): void
    {
        $userRoles = $usuario->roles->pluck('name')->all();
        $hasSalud = ! empty(array_intersect(self::ROLES_SALUD, $userRoles));
        $hasAdmin = ! empty(array_intersect(self::ROLES_ADMIN, $userRoles));

        if ($hasSalud) {
            $this->tipoPersonal = 'salud';
            $this->subtipoSalud = $usuario->rol_principal ?? ($userRoles[0] ?? '');
        } elseif ($hasAdmin) {
            $this->tipoPersonal = 'admin';
            $this->subtipoSalud = '';
        } else {
            $this->tipoPersonal = 'ninguno';
            $this->subtipoSalud = '';
        }

        $this->esEnfermeria = str_contains(mb_strtoupper($this->subtipoSalud), 'ENFERM');
    }

    private function cargarAreas(): array
    {
        return AreaInstitucional::query()
            ->whereIn('estado', ['ACTIVA', 'ACTIVO'])
            ->orderBy('nombre')
            ->get()
            ->map(fn (AreaInstitucional $area) => [
                'cod_area' => $area->cod_area,
                'nombre' => $area->nombre,
                'tipo_area' => $area->tipo_area,
            ])
            ->values()
            ->toArray();
    }

    private function cargarTurnos(): array
    {
        return TurnoInstitucional::query()
            ->where('estado', 'ACTIVO')
            ->orderBy('hora_inicio')
            ->get(['cod_turno', 'nombre', 'hora_inicio', 'hora_cierre', 'orden', 'estado'])
            ->map(fn (TurnoInstitucional $turno) => [
                'cod_turno' => $turno->cod_turno,
                'nombre' => $turno->nombre,
                'hora_inicio' => $turno->hora_inicio,
                'hora_fin' => $turno->hora_fin,
                'color' => $turno->color,
            ])
            ->toArray();
    }

    private function cargarAsignacionesAgrupadas(User $usuario): Collection
    {
        $horarios = $this->queryHorarios()
            ->with(['jornada.turno', 'area'])
            ->orderBy('fecha_asignacion')
            ->get();

        return $horarios
            ->groupBy(fn ($horario) => $this->claveAgrupacion($horario))
            ->map(function (Collection $grupo) {
                $primero = $grupo->first();
                $turnoV2 = $primero->jornada?->turno;
                $turno = $this->buscarTurnoCompatible(
                    (string) $turnoV2?->nombre,
                    $turnoV2?->hora_inicio,
                    $turnoV2?->hora_cierre
                );
                $meta = $this->parsearObservaciones((string) ($primero->observacion ?? ''));
                $fechas = $grupo->map(fn ($item) => $item->jornada?->fecha_jornada)->filter();

                return [
                    'cod_asignacion' => (string) $primero->getKey(),
                    'codigos_registro' => $grupo->map(fn ($item) => (string) $item->getKey())->values()->all(),
                    'area_nombre' => $primero->area?->nombre ?? 'Sin área asignada',
                    'turno_nombre' => (string) $turnoV2?->nombre,
                    'turno_color' => $turno['color'] ?? 'var(--color-boton-acento)',
                    'dias_semana' => $fechas
                        ->map(fn ($fecha) => mb_substr($this->diaDesdeFecha($fecha), 0, 3))
                        ->values()
                        ->all(),
                    'dias_semana_full' => $fechas
                        ->map(fn ($fecha) => $this->diaDesdeFecha($fecha))
                        ->values()
                        ->all(),
                    'fecha_inicio' => $fechas->min()?->format('d/m/Y') ?? '—',
                    'fecha_inicio_raw' => $fechas->min()?->format('Y-m-d'),
                    'fecha_fin' => null,
                    'fecha_fin_raw' => null,
                    'estado' => $primero->estado === 'ACTIVO' ? 'ACTIVA' : 'FINALIZADA',
                    'tipo_asignacion' => $primero->tipo_asignacion,
                    'observaciones' => $meta['observaciones'] ?? '',
                    'hora_inicio' => $turnoV2?->hora_inicio ? Carbon::parse($turnoV2->hora_inicio)->format('H:i') : '—',
                    'hora_fin' => $turnoV2?->hora_cierre ? Carbon::parse($turnoV2->hora_cierre)->format('H:i') : '—',
                    'hora_inicio_raw' => $turnoV2?->hora_inicio,
                    'hora_fin_raw' => $turnoV2?->hora_cierre,
                ];
            })
            ->values();
    }

    private function claveAgrupacion(object $horario): string
    {
        $meta = $this->parsearObservaciones((string) ($horario->observacion ?? ''));

        return (string) ($meta['grupo'] ?? $horario->cod_asignacion_personal);
    }

    private function buscarTurnoCompatible(string $nombre, ?string $horaInicio, ?string $horaFin): ?array
    {
        $nombre = mb_strtoupper(trim($nombre));

        foreach ($this->turnos as $turno) {
            if (
                mb_strtoupper((string) $turno['nombre']) === $nombre
                || (
                    (string) $turno['hora_inicio'] === (string) $horaInicio
                    && (string) $turno['hora_fin'] === (string) $horaFin
                )
            ) {
                return $turno;
            }
        }

        return null;
    }

    private function parsearObservaciones(string $valor): array
    {
        $decoded = json_decode($valor, true);

        if (is_array($decoded) && array_key_exists('observaciones', $decoded)) {
            return [
                'tipo_asignacion' => (string) ($decoded['tipo_asignacion'] ?? ($this->esEnfermeria ? 'ROTATIVO' : 'FIJO')),
                'observaciones' => trim((string) ($decoded['observaciones'] ?? '')),
                'grupo' => $decoded['grupo'] ?? null,
            ];
        }

        return [
            'tipo_asignacion' => $this->esEnfermeria ? 'ROTATIVO' : 'FIJO',
            'observaciones' => trim($valor),
            'grupo' => null,
        ];
    }

    private function observacionesSerializadas(string $grupo): string
    {
        $payload = [
            'grupo' => $grupo,
            'tipo_asignacion' => $this->f_tipo_asignacion,
            'observaciones' => trim($this->f_observaciones),
        ];

        return json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    private function verificarSolapamiento(TurnoInstitucional $turno): ?string
    {
        $idsExcluir = $this->modoEdicion && $this->codAsignacionEditar
            ? collect(collect($this->asignaciones)->firstWhere('cod_asignacion', $this->codAsignacionEditar)['codigos_registro'] ?? [])->all()
            : [];

        $fechaBase = Carbon::parse($this->f_fecha_inicio)->startOfDay();
        $fechas = collect($this->f_dias_semana)
            ->map(fn (string $dia) => $this->fechaParaDia($fechaBase, $dia)->toDateString())
            ->all();
        $existentes = $this->queryHorarios()
            ->with('jornada.turno')
            ->where('estado', 'ACTIVO')
            ->whereHas('jornada', fn ($query) => $query->whereIn('fecha_jornada', $fechas))
            ->when($idsExcluir !== [], function ($query) use ($idsExcluir) {
                $query->whereNotIn($this->primaryKeyHorario(), $idsExcluir);
            })
            ->get();

        foreach ($existentes as $existente) {
            $turnoExistente = $existente->jornada?->turno;
            if ($this->rangosHoraSeCruzan(
                (string) $turno->hora_inicio,
                (string) $turno->hora_fin,
                (string) $turnoExistente?->hora_inicio,
                (string) $turnoExistente?->hora_cierre
            )) {
                $dia = $this->diaDesdeFecha($existente->jornada->fecha_jornada);

                return "Ya existe un horario activo que se cruza el día {$dia} con el turno {$turnoExistente?->nombre}.";
            }
        }

        return null;
    }

    private function rangosHoraSeCruzan(string $inicioA, string $finA, string $inicioB, string $finB): bool
    {
        [$aInicio, $aFin] = $this->rangoMinutos($inicioA, $finA);
        [$bInicio, $bFin] = $this->rangoMinutos($inicioB, $finB);

        foreach ([-1440, 0, 1440] as $desplazamiento) {
            if ($aInicio < $bFin + $desplazamiento && $bInicio + $desplazamiento < $aFin) {
                return true;
            }
        }

        return false;
    }

    private function rangoMinutos(string $inicio, string $fin): array
    {
        $inicioCarbon = Carbon::parse($inicio);
        $finCarbon = Carbon::parse($fin);

        $inicioMinutos = $inicioCarbon->hour * 60 + $inicioCarbon->minute;
        $finMinutos = $finCarbon->hour * 60 + $finCarbon->minute;

        if ($finMinutos <= $inicioMinutos) {
            $finMinutos += 1440;
        }

        return [$inicioMinutos, $finMinutos];
    }

    private function actualizarEstadoAsignacionGrupo(string $codAsignacion, string $estado, Carbon $fecha): void
    {
        $ids = collect(collect($this->asignaciones)->firstWhere('cod_asignacion', $codAsignacion)['codigos_registro'] ?? [])->all();

        if ($ids === []) {
            return;
        }

        $this->queryHorarios()
            ->whereIn($this->primaryKeyHorario(), $ids)
            ->update(['estado' => $estado]);
    }

    private function usuarioBloqueado(): bool
    {
        return in_array(strtoupper($this->estadoUsuario), ['INACTIVO', 'SUSPENDIDO', 'RETIRADO', '0'], true);
    }

    private function resetFormulario(): void
    {
        $codAreaUsuario = $this->codAreaUsuario($this->usuario());
        $areasValidas = array_column($this->areas, 'cod_area');
        $this->f_cod_area = in_array($codAreaUsuario, $areasValidas, true) ? $codAreaUsuario : '';
        $this->f_cod_turno = '';
        $this->f_dias_semana = [];
        $this->f_fecha_inicio = now()->format('Y-m-d');
        $this->f_fecha_fin = '';
        $this->f_tipo_asignacion = $this->esEnfermeria ? 'ROTATIVO' : 'FIJO';
        $this->f_observaciones = '';
        $this->modoEdicion = false;
        $this->codAsignacionEditar = null;
        $this->resetErrorBag();
    }

    private function normalizarDiaSemana(string $dia): string
    {
        $dia = mb_strtoupper(trim($dia));
        $dia = str_replace(['Á', 'É', 'Í', 'Ó', 'Ú'], ['A', 'E', 'I', 'O', 'U'], $dia);

        return in_array($dia, self::DIAS, true) ? $dia : 'LUNES';
    }

    private function fechaParaInput(?string $fecha, ?Carbon $fallback = null): string
    {
        if ($fecha) {
            return Carbon::parse($fecha)->format('Y-m-d');
        }

        return ($fallback ?? now())->format('Y-m-d');
    }

    private function queryHorarios()
    {
        return AsignacionPersonal::query()
            ->whereHas('personal', fn ($query) => $query->where('cod_usuario', $this->usuarioId));
    }

    private function primaryKeyHorario(): string
    {
        return 'cod_asignacion_personal';
    }

    private function codAreaUsuario(?User $usuario): string
    {
        return (string) ($usuario?->personal?->asignaciones?->sortByDesc('fecha_asignacion')->first()?->cod_area ?? '');
    }

    private function fechaParaDia(Carbon $base, string $dia): Carbon
    {
        $indice = array_search($this->normalizarDiaSemana($dia), self::DIAS, true);
        $objetivoIso = ($indice === false ? 0 : $indice) + 1;
        $diferencia = ($objetivoIso - $base->dayOfWeekIso + 7) % 7;

        return $base->copy()->addDays($diferencia);
    }

    private function diaDesdeFecha(mixed $fecha): string
    {
        return self::DIAS[Carbon::parse($fecha)->dayOfWeekIso - 1];
    }
}
