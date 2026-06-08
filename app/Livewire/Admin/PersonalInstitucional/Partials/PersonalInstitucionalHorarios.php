<?php

namespace App\Livewire\Admin\PersonalInstitucional\Partials;

use App\Models\AreaInstitucional;
use App\Models\AsignacionTurno;
use App\Models\TurnoInstitucional;
use App\Models\User;
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

    // Campos del formulario (prefijo f_ para evitar colisiones con propiedades de listas)
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

        $rolesSalud = [
            'MEDICO GENERAL/GERIATRA', 'MÉDICO GENERAL/GERIATRA', 'MEDICO', 'MÉDICO',
            'ENFERMEROS', 'ENFERMERO', 'ENFERMERA',
            'PSICOLOGO/A', 'PSICÓLOGO/A', 'NUTRICIONISTA', 'FISIOTERAPEUTA', 'PEDAGOGO',
        ];
        $rolesAdmin = ['SUPERADMINISTRADOR', 'ADMINISTRADOR', 'ADMINISTRATIVO', 'PERSONAL ADMIN'];

        if ($usuario->hasAnyRole($rolesSalud)) {
            $this->tipoPersonal = 'salud';
            $rolNombre = $usuario->roles->whereIn('name', $rolesSalud)->first()?->name ?? '';
            $this->subtipoSalud = strtoupper(str_replace(['/', ' ', '-'], '_', $rolNombre));
        } elseif ($usuario->hasAnyRole($rolesAdmin)) {
            $this->tipoPersonal = 'admin';
        } else {
            $this->tipoPersonal = 'ninguno';
        }

        $this->asignaciones = AsignacionTurno::with(['turno', 'area'])
            ->where('cod_usu', $this->usuarioId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($a) => [
                'cod_asignacion'  => $a->cod_asignacion,
                'area_nombre'     => $a->area?->nombre ?? '—',
                'turno_nombre'    => $a->turno?->nombre ?? '—',
                'turno_color'     => $a->turno?->color,
                'dias_semana'     => $a->dias_semana ?? [],
                'fecha_inicio'    => $a->fecha_inicio?->format('d/m/Y') ?? '—',
                'fecha_fin'       => $a->fecha_fin?->format('d/m/Y'),
                'estado'          => $a->estado,
                'tipo_asignacion' => $a->tipo_asignacion ?? '—',
                'observaciones'   => $a->observaciones,
            ])
            ->toArray();

        $this->areas = AreaInstitucional::where('estado', 'ACTIVA')
            ->orderBy('nombre')
            ->get(['cod_area', 'nombre', 'tipo_area'])
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
        if ($this->subtipoSalud === 'ENFERMERO') {
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

        $asig = AsignacionTurno::where('cod_asignacion', $cod)->first();

        if (!$asig || $asig->estado !== 'ACTIVA') {
            $this->dispatch('mostrarAlerta', [
                'type'    => 'warning',
                'title'   => 'No editable',
                'message' => 'Solo se pueden editar asignaciones con estado ACTIVA.',
            ]);
            return;
        }

        $this->codAsignacionEditar = $cod;
        $this->f_cod_area          = $asig->cod_area;
        $this->f_cod_turno         = $asig->cod_turno;
        $this->f_dias_semana       = $asig->dias_semana ?? [];
        $this->f_fecha_inicio      = $asig->fecha_inicio?->format('Y-m-d') ?? now()->format('Y-m-d');
        $this->f_fecha_fin         = $asig->fecha_fin?->format('Y-m-d') ?? '';
        $this->f_tipo_asignacion   = $asig->tipo_asignacion ?? 'FIJO';
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
            'f_cod_area'        => 'required|string|exists:areas_institucionales,cod_area',
            'f_cod_turno'       => 'required|string|exists:turnos_institucionales,cod_turno',
            'f_dias_semana'     => 'required|array|min:1',
            'f_dias_semana.*'   => 'in:LUNES,MARTES,MIERCOLES,JUEVES,VIERNES,SABADO,DOMINGO',
            'f_fecha_inicio'    => 'required|date',
            'f_fecha_fin'       => 'nullable|date|after_or_equal:f_fecha_inicio',
            'f_tipo_asignacion' => 'required|in:FIJO,ROTATIVO,TEMPORAL,EVENTUAL',
        ], [
            'f_cod_area.required'        => 'Debe seleccionar un área.',
            'f_cod_turno.required'       => 'Debe seleccionar un turno.',
            'f_dias_semana.required'     => 'Seleccione al menos un día laboral.',
            'f_dias_semana.min'          => 'Seleccione al menos un día laboral.',
            'f_fecha_inicio.required'    => 'La fecha de inicio es obligatoria.',
            'f_tipo_asignacion.required' => 'El tipo de asignación es obligatorio.',
        ]);

        $errorSolapamiento = $this->verificarSolapamiento();
        if ($errorSolapamiento) {
            $this->addError('f_dias_semana', $errorSolapamiento);
            return;
        }

        $isEdit = $this->modoEdicion;

        DB::beginTransaction();
        try {
            if ($isEdit && $this->codAsignacionEditar) {
                // Regla de historial: finalizar la asignación activa anterior
                $anterior = AsignacionTurno::where('cod_asignacion', $this->codAsignacionEditar)->first();
                if ($anterior) {
                    $anterior->estado          = 'FINALIZADA';
                    $anterior->fecha_fin       = $anterior->fecha_inicio?->isFuture()
                        ? $anterior->fecha_inicio->toDateString()
                        : now()->toDateString();
                    $anterior->actualizado_por = auth()->user()?->cod_usu;
                    $anterior->save();
                }
            }

            AsignacionTurno::create([
                'cod_usu'         => $this->usuarioId,
                'cod_area'        => $this->f_cod_area,
                'cod_turno'       => $this->f_cod_turno,
                'dias_semana'     => $this->f_dias_semana,
                'fecha_inicio'    => $this->f_fecha_inicio,
                'fecha_fin'       => $this->f_fecha_fin ?: null,
                'estado'          => 'ACTIVA',
                'tipo_asignacion' => $this->f_tipo_asignacion,
                'observaciones'   => $this->f_observaciones ?: null,
                'creado_por'      => auth()->user()?->cod_usu,
            ]);

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
                ? 'La asignación anterior quedó FINALIZADA y se creó una nueva asignación activa.'
                : 'Asignación de turno registrada correctamente.',
        ]);
        $this->dispatch('asignacionActualizada');
    }

    // ─── Verificación de solapamiento ────────────────────────────────────────

    private function verificarSolapamiento(): ?string
    {
        $turnoNuevo = TurnoInstitucional::where('cod_turno', $this->f_cod_turno)->first();

        if (!$turnoNuevo) {
            return 'El turno seleccionado ya no está disponible.';
        }

        $query = AsignacionTurno::where('cod_usu', $this->usuarioId)
            ->where('estado', 'ACTIVA')
            ->with('turno');

        if ($this->modoEdicion && $this->codAsignacionEditar) {
            $query->where('cod_asignacion', '!=', $this->codAsignacionEditar);
        }

        $activas = $query->get();

        foreach ($activas as $asig) {
            if (!$this->periodosSeCruzan($asig)) {
                continue;
            }

            $diasExistente = $asig->dias_semana ?? [];
            $interseccion  = array_values(array_intersect($this->f_dias_semana, $diasExistente));

            if (empty($interseccion)) continue;

            if (!$asig->turno || !$this->rangosHoraSeCruzan(
                $turnoNuevo->hora_inicio,
                $turnoNuevo->hora_fin,
                $asig->turno->hora_inicio,
                $asig->turno->hora_fin,
            )) {
                continue;
            }

            $diasStr = implode(', ', $interseccion);

            return "Solapamiento detectado con {$asig->cod_asignacion}: coincide la vigencia, el horario y los días {$diasStr}.";
        }

        return null;
    }

    private function periodosSeCruzan(AsignacionTurno $asignacion): bool
    {
        $inicioNuevo = Carbon::parse($this->f_fecha_inicio)->startOfDay();
        $finNuevo = $this->f_fecha_fin
            ? Carbon::parse($this->f_fecha_fin)->endOfDay()
            : Carbon::create(9999, 12, 31)->endOfDay();

        $inicioExistente = $asignacion->fecha_inicio?->copy()->startOfDay() ?? Carbon::minValue();
        $finExistente = $asignacion->fecha_fin?->copy()->endOfDay() ?? Carbon::create(9999, 12, 31)->endOfDay();

        return $inicioNuevo->lte($finExistente) && $inicioExistente->lte($finNuevo);
    }

    private function rangosHoraSeCruzan(?string $inicioA, ?string $finA, ?string $inicioB, ?string $finB): bool
    {
        if (!$inicioA || !$finA || !$inicioB || !$finB) {
            return true;
        }

        [$inicioAMin, $finAMin] = $this->rangoEnMinutos($inicioA, $finA);
        [$inicioBMin, $finBMin] = $this->rangoEnMinutos($inicioB, $finB);

        foreach ([-1440, 0, 1440] as $desplazamiento) {
            $inicioBDesplazado = $inicioBMin + $desplazamiento;
            $finBDesplazado = $finBMin + $desplazamiento;

            if ($inicioAMin < $finBDesplazado && $inicioBDesplazado < $finAMin) {
                return true;
            }
        }

        return false;
    }

    private function rangoEnMinutos(string $inicio, string $fin): array
    {
        $inicioHora = Carbon::parse($inicio);
        $finHora = Carbon::parse($fin);
        $inicioMinutos = $inicioHora->hour * 60 + $inicioHora->minute;
        $finMinutos = $finHora->hour * 60 + $finHora->minute;

        if ($finMinutos <= $inicioMinutos) {
            $finMinutos += 1440;
        }

        return [$inicioMinutos, $finMinutos];
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
        $this->validate(
            ['f_fecha_fin_finalizar' => 'required|date'],
            ['f_fecha_fin_finalizar.required' => 'Ingrese la fecha de fin.']
        );

        $asig = AsignacionTurno::where('cod_asignacion', $this->codAsignacionFinalizar)->first();
        if (!$asig) return;

        if ($asig->estado !== 'ACTIVA') {
            $this->addError('f_fecha_fin_finalizar', 'Solo se pueden finalizar asignaciones activas.');
            return;
        }

        if ($asig->fecha_inicio && Carbon::parse($this->f_fecha_fin_finalizar)->lt($asig->fecha_inicio)) {
            $this->addError('f_fecha_fin_finalizar', 'La fecha de fin no puede ser anterior a la fecha de inicio.');
            return;
        }

        $asig->estado          = 'FINALIZADA';
        $asig->fecha_fin       = $this->f_fecha_fin_finalizar;
        $asig->actualizado_por = auth()->user()?->cod_usu;
        $asig->save();

        $this->modalFinalizarAbierto  = false;
        $this->codAsignacionFinalizar = null;
        $this->cargarDatos();

        $this->dispatch('mostrarAlerta', [
            'type'    => 'success',
            'title'   => 'Asignación finalizada',
            'message' => 'La asignación fue marcada como FINALIZADA.',
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
        $asig = AsignacionTurno::with(['turno', 'area', 'creador'])
            ->where('cod_asignacion', $cod)
            ->first();

        if (!$asig) return;

        $this->detalleAsignacion = [
            'cod_asignacion'  => $asig->cod_asignacion,
            'area'            => $asig->area?->nombre ?? '—',
            'turno'           => $asig->turno?->nombre ?? '—',
            'hora_inicio'     => $asig->turno?->hora_inicio
                                    ? \Carbon\Carbon::parse($asig->turno->hora_inicio)->format('H:i') : '—',
            'hora_fin'        => $asig->turno?->hora_fin
                                    ? \Carbon\Carbon::parse($asig->turno->hora_fin)->format('H:i') : '—',
            'dias_semana'     => $asig->dias_semana ?? [],
            'fecha_inicio'    => $asig->fecha_inicio?->format('d/m/Y') ?? '—',
            'fecha_fin'       => $asig->fecha_fin?->format('d/m/Y') ?? 'Sin fecha fin',
            'estado'          => $asig->estado,
            'tipo_asignacion' => $asig->tipo_asignacion ?? '—',
            'observaciones'   => $asig->observaciones ?? '—',
            'creado_por'      => $asig->creador?->name ?? '—',
            'created_at'      => $asig->created_at?->format('d/m/Y H:i') ?? '—',
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
        $this->f_tipo_asignacion   = $this->subtipoSalud === 'ENFERMERO' ? 'ROTATIVO' : 'FIJO';
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
