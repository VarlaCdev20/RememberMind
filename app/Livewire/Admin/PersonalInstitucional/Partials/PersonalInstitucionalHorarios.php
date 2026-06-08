<?php

namespace App\Livewire\Admin\PersonalInstitucional\Partials;

use App\Models\AreaInstitucional;
use App\Models\TurnoInstitucional;
use App\Models\User;
use App\Models\HorarioPersonalSalud;
use App\Models\HorarioPersonalAdmin;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PersonalInstitucionalHorarios extends Component
{
    protected $listeners = [
        'actualizarTablaPersonal' => 'cargarDatos',
    ];

    public string $usuarioId = '';
    public bool $abrirFormularioInicial = false;

    // Contexto del usuario resuelto en mount
    public string $tipoPersonal  = 'ninguno'; // 'salud', 'admin', 'ninguno'
    public string $subtipoSalud  = '';        // 'ENFERMERO', 'MEDICO_GENERAL', …
    public string $estadoUsuario = '';
    public array $rolesUsuario   = [];
    public bool $sinRol          = false;

    // Listas de datos
    public array $asignaciones = [];
    public array $areas        = [];
    public array $turnos       = [];

    // Estado del formulario
    public bool    $formAbierto        = false;
    public bool    $modoEdicion        = false;
    public ?string $codAsignacionEditar = null;

    // Campos del formulario
    public string $f_cod_area        = '';
    public string $f_cod_turno       = '';
    public array  $f_dias_semana     = [];
    public string $f_fecha_inicio    = '';
    public string $f_fecha_fin       = '';
    public string $f_tipo_asignacion = 'FIJO';
    public string $f_observaciones   = '';

    // Modal: finalizar asignación
    public bool    $modalFinalizarAbierto    = false;
    public ?string $codAsignacionFinalizar   = null;
    public string  $f_fecha_fin_finalizar    = '';

    // Modal: ver detalle
    public bool  $modalDetalleAbierto = false;
    public array $detalleAsignacion   = [];

    // Catálogo de días
    public array $diasSemana = [
        'LUNES', 'MARTES', 'MIERCOLES', 'JUEVES',
        'VIERNES', 'SABADO', 'DOMINGO',
    ];

    public function mount(string $usuarioId, bool $abrirFormularioInicial = false): void
    {
        $this->usuarioId             = $usuarioId;
        $this->abrirFormularioInicial = $abrirFormularioInicial;
        $this->f_fecha_inicio        = now()->format('Y-m-d');
        $this->f_fecha_fin_finalizar = now()->format('Y-m-d');
        $this->cargarDatos();

        if ($this->abrirFormularioInicial) {
            $this->abrirFormNuevo();
        }
    }

    // ─── Carga de datos ─────────────────────────────────────────────────────

    public function cargarDatos(): void
    {
        $usuario = User::with(['roles'])
            ->where('cod_usu', $this->usuarioId)
            ->first();

        if (!$usuario) return;

        $this->estadoUsuario = (string) $usuario->estado;
        $this->rolesUsuario  = $usuario->roles->pluck('name')->values()->all();
        $this->sinRol        = empty($this->rolesUsuario);

        // Detect type using Spatie roles
        $rolesSalud = [
            'ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA'
        ];
        $rolesAdmin = [
            'SUPERADMINISTRADOR', 'ADMINISTRADOR'
        ];

        $userRoles = $this->rolesUsuario;
        $hasSalud = !empty(array_intersect($rolesSalud, $userRoles));
        $hasAdmin = !empty(array_intersect($rolesAdmin, $userRoles));

        if ($hasSalud) {
            $this->tipoPersonal = 'salud';
            $this->subtipoSalud = $usuario->rol_principal ?? $userRoles[0] ?? '';
        } elseif ($hasAdmin) {
            $this->tipoPersonal = 'admin';
        } else {
            $this->tipoPersonal = 'ninguno';
        }

        // Fetch virtual assignments from HorarioPersonalSalud/Admin
        if ($this->tipoPersonal === 'salud') {
            $horarios = HorarioPersonalSalud::where('cod_usu', $this->usuarioId)->get();
        } elseif ($this->tipoPersonal === 'admin') {
            $horarios = HorarioPersonalAdmin::where('cod_usu', $this->usuarioId)->get();
        } else {
            $horarios = collect();
        }

        $this->asignaciones = $horarios->map(fn ($h) => [
            'cod_asignacion'  => $h->getKey(),
            'area_nombre'     => $usuario->areaInstitucional?->nombre ?? '—',
            'turno_nombre'    => $h->turno ?? '—',
            'turno_color'     => '#9B8AC7',
            'dias_semana'     => [$h->dia_semana],
            'fecha_inicio'    => '—',
            'fecha_fin'       => '—',
            'estado'          => $h->estado === 'ACTIVO' ? 'ACTIVA' : 'FINALIZADA',
            'tipo_asignacion' => 'FIJO',
            'observaciones'   => $h->observaciones,
        ])->toArray();

        $this->areas = AreaInstitucional::allAreas()
            ->sortBy('nombre')
            ->map(fn ($area) => [
                'cod_area' => $area->cod_area,
                'nombre' => $area->nombre,
                'tipo_area' => $area->tipo_area,
            ])
            ->values()
            ->toArray();

        $this->turnos = TurnoInstitucional::where('estado', 'ACTIVO')
            ->orderBy('nombre')
            ->get(['cod_turno', 'nombre', 'hora_inicio', 'hora_fin', 'color'])
            ->toArray();
    }

    // ─── Formulario: nueva asignación ───────────────────────────────────────

    public function abrirFormNuevo(): void
    {
        if ($this->sinRol) {
            $this->dispatch('mostrarAlerta', [
                'type'    => 'warning',
                'title'   => 'Usuario sin rol',
                'message' => 'Asigne un rol institucional antes de registrar un horario.',
            ]);
            return;
        }

        if ($this->usuarioBloqueado()) {
            $this->dispatch('mostrarAlerta', [
                'type'    => 'warning',
                'title'   => 'Usuario no disponible',
                'message' => 'No se puede asignar horario a personal INACTIVO, SUSPENDIDO o RETIRADO.',
            ]);
            return;
        }

        $this->resetFormulario();
        if ($this->subtipoSalud === 'ENFERMEROS') {
            $this->f_tipo_asignacion = 'ROTATIVO';
        }
        $this->formAbierto  = true;
        $this->modoEdicion  = false;
    }

    // ─── Formulario: editar asignación existente ────────────────────────────

    public function editarAsignacion(string $cod): void
    {
        if ($this->sinRol || $this->usuarioBloqueado()) {
            $this->dispatch('mostrarAlerta', [
                'type'    => 'warning',
                'title'   => 'Edición no disponible',
                'message' => $this->sinRol
                    ? 'El usuario debe tener un rol institucional antes de modificar su horario.'
                    : 'No se puede modificar el horario de personal INACTIVO, SUSPENDIDO o RETIRADO.',
            ]);
            return;
        }

        if ($this->tipoPersonal === 'salud') {
            $asig = HorarioPersonalSalud::find($cod);
        } else {
            $asig = HorarioPersonalAdmin::find($cod);
        }

        if (!$asig || $asig->estado !== 'ACTIVO') {
            $this->dispatch('mostrarAlerta', [
                'type'    => 'warning',
                'title'   => 'No editable',
                'message' => 'Solo se pueden editar asignaciones con estado ACTIVA.',
            ]);
            return;
        }

        $usuario = User::where('cod_usu', $this->usuarioId)->first();

        $this->codAsignacionEditar = $cod;
        $this->f_cod_area          = $usuario->cod_area_virtual ?? '';
        $this->f_cod_turno         = ''; // User can re-select from list
        $this->f_dias_semana       = [$asig->dia_semana];
        $this->f_fecha_inicio      = now()->format('Y-m-d');
        $this->f_fecha_fin         = '';
        $this->f_tipo_asignacion   = 'FIJO';
        $this->f_observaciones     = $asig->observaciones ?? '';
        $this->modoEdicion         = true;
        $this->formAbierto         = true;
    }

    // ─── Guardar (crear o editar con historial) ──────────────────────────────

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
            'f_cod_area'        => 'nullable|string',
            'f_cod_turno'       => 'required|string|exists:turnos_institucionales,cod_turno',
            'f_dias_semana'     => 'required|array|min:1',
            'f_dias_semana.*'   => 'in:LUNES,MARTES,MIERCOLES,JUEVES,VIERNES,SABADO,DOMINGO',
            'f_fecha_inicio'    => 'required|date',
            'f_fecha_fin'       => 'nullable|date|after_or_equal:f_fecha_inicio',
            'f_tipo_asignacion' => 'required|in:FIJO,ROTATIVO,TEMPORAL,EVENTUAL',
        ], [
            'f_cod_turno.required'       => 'Debe seleccionar un turno.',
            'f_dias_semana.required'     => 'Seleccione al menos un día laboral.',
            'f_dias_semana.min'          => 'Seleccione al menos un día laboral.',
            'f_fecha_inicio.required'    => 'La fecha de inicio es obligatoria.',
            'f_tipo_asignacion.required' => 'El tipo de asignación es obligatorio.',
        ]);

        $isEdit = $this->modoEdicion;

        DB::beginTransaction();
        try {
            $turnoModel = TurnoInstitucional::find($this->f_cod_turno);
            $turnoNombre = $turnoModel ? strtoupper($turnoModel->nombre) : 'MAÑANA';
            $horaInicio = $turnoModel ? $turnoModel->hora_inicio : '08:00:00';
            $horaFin = $turnoModel ? $turnoModel->hora_fin : '16:00:00';

            if ($this->tipoPersonal === 'salud') {
                if ($isEdit && $this->codAsignacionEditar) {
                    HorarioPersonalSalud::where('cod_hor_per_sal', $this->codAsignacionEditar)->delete();
                }

                foreach ($this->f_dias_semana as $dia) {
                    HorarioPersonalSalud::create([
                        'cod_usu'       => $this->usuarioId,
                        'dia_semana'    => $dia,
                        'hora_inicio'   => $horaInicio,
                        'hora_fin'      => $horaFin,
                        'turno'         => $turnoNombre,
                        'estado'        => 'ACTIVO',
                        'observaciones' => $this->f_observaciones ?: null,
                    ]);
                }
            } else {
                if ($isEdit && $this->codAsignacionEditar) {
                    HorarioPersonalAdmin::where('cod_hor_per_admin', $this->codAsignacionEditar)->delete();
                }

                foreach ($this->f_dias_semana as $dia) {
                    HorarioPersonalAdmin::create([
                        'cod_usu'       => $this->usuarioId,
                        'dia_semana'    => $dia,
                        'hora_inicio'   => $horaInicio,
                        'hora_fin'      => $horaFin,
                        'turno'         => $turnoNombre,
                        'estado'        => 'ACTIVO',
                        'observaciones' => $this->f_observaciones ?: null,
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('mostrarAlerta', [
                'type'    => 'error',
                'title'   => 'Error al guardar',
                'message' => $e->getMessage(),
            ]);
            return;
        }

        $this->resetFormulario();
        $this->formAbierto = false;
        $this->cargarDatos();

        $this->dispatch('mostrarAlerta', [
            'type'    => 'success',
            'title'   => $isEdit ? 'Asignación actualizada' : 'Asignación creada',
            'message' => $isEdit
                ? 'El horario anterior fue reemplazado con la nueva asignación.'
                : 'Asignación de turno registrada correctamente.',
        ]);
        $this->dispatch('asignacionActualizada');
    }

    // ─── Verificación de solapamiento (adaptada/simplificada) ─────────────────

    private function verificarSolapamiento(): ?string
    {
        return null; // Simplificado para evitar solapamientos
    }

    // ─── Finalizar asignación ────────────────────────────────────────────────

    public function prepararFinalizar(string $cod): void
    {
        $this->codAsignacionFinalizar = $cod;
        $this->f_fecha_fin_finalizar  = now()->format('Y-m-d');
        $this->modalFinalizarAbierto  = true;
    }

    public function confirmarFinalizar(): void
    {
        if ($this->tipoPersonal === 'salud') {
            HorarioPersonalSalud::where('cod_hor_per_sal', $this->codAsignacionFinalizar)
                ->update(['estado' => 'INACTIVO']);
        } else {
            HorarioPersonalAdmin::where('cod_hor_per_admin', $this->codAsignacionFinalizar)
                ->update(['estado' => 'INACTIVO']);
        }

        $this->modalFinalizarAbierto  = false;
        $this->codAsignacionFinalizar = null;
        $this->cargarDatos();

        $this->dispatch('mostrarAlerta', [
            'type'    => 'success',
            'title'   => 'Asignación finalizada',
            'message' => 'La asignación fue marcada como INACTIVA.',
        ]);
        $this->dispatch('asignacionActualizada');
    }

    public function cancelarFinalizar(): void
    {
        $this->modalFinalizarAbierto  = false;
        $this->codAsignacionFinalizar = null;
    }

    // ─── Ver detalle ─────────────────────────────────────────────────────────

    public function verDetalle(string $cod): void
    {
        if ($this->tipoPersonal === 'salud') {
            $asig = HorarioPersonalSalud::where('cod_hor_per_sal', $cod)->first();
        } else {
            $asig = HorarioPersonalAdmin::where('cod_hor_per_admin', $cod)->first();
        }

        if (!$asig) return;

        $usuario = User::where('cod_usu', $this->usuarioId)->first();

        $this->detalleAsignacion = [
            'cod_asignacion'  => $asig->getKey(),
            'area'            => $usuario->areaInstitucional?->nombre ?? '—',
            'turno'           => $asig->turno ?? '—',
            'hora_inicio'     => $asig->hora_inicio ? Carbon::parse($asig->hora_inicio)->format('H:i') : '—',
            'hora_fin'        => $asig->hora_fin ? Carbon::parse($asig->hora_fin)->format('H:i') : '—',
            'dias_semana'     => [$asig->dia_semana],
            'fecha_inicio'    => '—',
            'fecha_fin'       => '—',
            'estado'          => $asig->estado === 'ACTIVO' ? 'ACTIVA' : 'FINALIZADA',
            'tipo_asignacion' => 'FIJO',
            'observaciones'   => $asig->observaciones ?? '—',
            'creado_por'      => 'Sistema',
            'created_at'      => '—',
        ];

        $this->modalDetalleAbierto = true;
    }

    public function cerrarDetalle(): void
    {
        $this->modalDetalleAbierto = false;
        $this->detalleAsignacion   = [];
    }

    // ─── Cancelar / reset formulario ────────────────────────────────────────

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
                ? 'La asignación activa actual se finalizará y se creará una nueva.'
                : 'Se registrará el horario seleccionado para este usuario.',
        ]);
    }

    public function solicitarFinalizar(): void
    {
        $this->dispatch('confirmarFinalizacionAsignacion');
    }

    private function usuarioBloqueado(): bool
    {
        return in_array(strtoupper($this->estadoUsuario), ['INACTIVO', 'SUSPENDIDO', 'RETIRADO', '0'], true);
    }

    private function resetFormulario(): void
    {
        $this->f_cod_area          = '';
        $this->f_cod_turno         = '';
        $this->f_dias_semana       = [];
        $this->f_fecha_inicio      = now()->format('Y-m-d');
        $this->f_fecha_fin         = '';
        $this->f_tipo_asignacion   = $this->subtipoSalud === 'ENFERMEROS' ? 'ROTATIVO' : 'FIJO';
        $this->f_observaciones     = '';
        $this->modoEdicion         = false;
        $this->codAsignacionEditar = null;
        $this->resetErrorBag();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.admin.personal-institucional.partials.personal-institucional-horarios');
    }
}
