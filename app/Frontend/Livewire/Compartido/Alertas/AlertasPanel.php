<?php

namespace App\Frontend\Livewire\Compartido\Alertas;

use App\Models\{Residente, Alerta, SignoVital, Turno, User};
use App\Backend\Modulos\Alertas\Servicios\DeteccionAlertasService;
use App\Backend\Modulos\Alertas\Servicios\AlertasService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Locked;
use Livewire\WithPagination;

class AlertasPanel extends Component
{
    use WithPagination;

    #[\Livewire\Attributes\Url(as: 'adulto')]
    public string $filtroAdulto = '';
    public int $perPage = 15;
    public string $search = '', $filtroEstado = 'ABIERTA', $filtroNivel = '', $filtroOrigen = '';
    public bool $modalCrear = false, $modalAtender = false, $modalCerrar = false, $modalDetalle = false;
    public bool $modalResultadoCierre = false;

    #[Locked]
    public array $resultadoCierre = [];
    public bool $drawerGrafico = false;
    public bool $drawerUbicacion = false;
    public ?string $adultoDrawerId = null;
    public ?Residente $adultoDrawer = null;
    public $signosDrawer = [];
    #[Locked]
    public ?string $alertaId = null;
        public string $codResidente = '', $codTurno = '', $origen = 'MANUAL', $tipoAlerta = '', $nivel = 'MEDIO', $motivo = '';
    public string $accionTomada = '', $observacionCierre = '', $accion = '', $responsableId = '';
    public array $conteos = [];
    public array $chartData = [];

    public function mount(): void
    {
        $this->comprobarPermiso('ver');
        $alerta = request()->query('alerta');
        if (is_string($alerta) && $alerta !== '') {
            $this->verDetalle($alerta);
        }
    }

    private function comprobarPermiso(string $accion): void
    {
        $user = auth()->user();
        abort_unless($user, 401);
        $permiso = match ($accion) {
            'ver' => 'alertas.ver',
            'asignar' => 'alertas.asignar',
            'atender' => 'alertas.seguimiento',
            'cerrar' => 'alertas.cerrar',
            default => 'alertas.gestionar',
        };
        abort_unless($user->canAny([$permiso, 'alertas.gestionar']), 403);
    }

    public function updated($campo): void
    {
        if (in_array($campo, ['search', 'filtroEstado', 'filtroNivel', 'filtroOrigen', 'filtroAdulto', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function limpiarFiltros(): void
    {
        $this->search = '';
        $this->filtroEstado = 'ABIERTA';
        $this->filtroNivel = '';
        $this->filtroOrigen = '';
        $this->filtroAdulto = '';
        $this->resetPage();
    }

    public function limpiarFiltro(string $campo): void
    {
        if ($campo === 'filtroEstado') {
            $this->filtroEstado = 'ABIERTA';
            $this->resetPage();
        } elseif (in_array($campo, ['search', 'filtroNivel', 'filtroOrigen', 'filtroAdulto'])) {
            $this->$campo = '';
            $this->resetPage();
        }
    }

    public function setFiltroRapido(string $campo, string $valor): void
    {
        if (property_exists($this, $campo)) {
            if ($this->{$campo} === $valor) {
                $this->{$campo} = '';
            } else {
                $this->{$campo} = $valor;
            }
            $this->resetPage();
        }
    }

    public function detectarAlertas(): void
    {
        $this->comprobarPermiso('crear');
        $cantidad = app(DeteccionAlertasService::class)->detectar();
        if ($cantidad > 0) {
            session()->flash('mensaje', "Detección completada: {$cantidad} " . ($cantidad === 1 ? 'nueva alerta clínica detectada.' : 'nuevas alertas clínicas detectadas.'));
        } else {
            session()->flash('mensaje', 'Detección completada: No se detectaron nuevas alertas.');
        }
    }

    public function abrirCrear(): void
    {
        $this->comprobarPermiso('crear');
        $this->cerrarModales();
        $this->reset('codResidente', 'codTurno', 'tipoAlerta', 'motivo', 'accionTomada');
        $this->origen = 'MANUAL';
        $this->nivel = 'MEDIO';
        $this->modalCrear = true;
    }

    public function guardarAlerta(): void
    {
        $this->comprobarPermiso('crear');

        $this->validate([
            'codResidente' => 'required|string|exists:residentes,cod_residente',
            'origen' => 'required|in:SIGNOS,MEDICACION,SEGUIMIENTO,PLAN,INCIDENTE,SOLICITUD_MEDICA,MANUAL,FICHA,VALORACION',
            'tipoAlerta' => 'required|string|min:3|max:80',
            'nivel' => 'required|in:BAJO,MEDIO,ALTO,CRITICO',
            'motivo' => 'required|string|min:10|max:10000',
        ], [
            'codResidente.required' => 'Debe seleccionar un residente asignado.',
            'codResidente.exists' => 'El residente seleccionado no es válido.',
            'origen.required' => 'Debe seleccionar el origen clínico de la alerta.',
            'tipoAlerta.required' => 'El tipo o diagnóstico clínico es obligatorio.',
            'tipoAlerta.min' => 'El tipo de alerta debe tener al menos 3 caracteres.',
            'tipoAlerta.max' => 'El tipo de alerta no puede superar los 80 caracteres.',
            'nivel.required' => 'Debe seleccionar el nivel de severidad.',
            'motivo.required' => 'Debe detallar el motivo clínico de la alerta.',
            'motivo.min' => 'El motivo clínico debe contener al menos 10 caracteres explicativos.',
        ]);

        if (auth()->user() && auth()->user()->hasRole('ENFERMEROS')) {
            app(\App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService::class)->autorizarAccionPaciente($this->codResidente);
        }

        $alerta = Alerta::create([
            'cod_alerta' => 'ALA_' . strtoupper(\Illuminate\Support\Str::random(10)),
            'cod_residente' => $this->codResidente,
            'cod_personal_responsable' => auth()->user()?->personal?->cod_personal,
            'tipo' => mb_strtoupper(trim($this->tipoAlerta)),
            'prioridad' => $this->nivel,
            'modulo' => $this->origen,
            'titulo' => mb_substr(trim($this->motivo), 0, 100),
            'descripcion' => trim($this->motivo),
            'fecha_hora' => now(),
            'generacion' => 'MANUAL',
            'estado' => 'ABIERTA',
        ]);

        $this->dispatch('alerta-creada', alertaId: $alerta->cod_alerta);
        $this->cerrarModales();
        session()->flash('mensaje', 'Alerta clínica registrada exitosamente.');
    }

    public function verDetalle(string $id): void
    {
        $this->comprobarPermiso('ver');
        $alerta = $this->obtenerAlertaAutorizada($id);
        $this->cerrarModales();
        $this->alertaId = $id;
        $this->responsableId = $alerta->responsable?->cod_usuario ?? '';
        $this->accion = '';
        $this->modalDetalle = true;
    }

    public function asignarResponsable(): void
    {
        $this->comprobarPermiso('asignar');
        $this->validate(['responsableId' => 'required|exists:usuarios,cod_usuario'], [
            'responsableId.required' => 'Debe seleccionar un profesional responsable.',
            'responsableId.exists' => 'El profesional seleccionado no es válido.',
        ]);
        $this->modificarAbierta(function ($alerta) {
            $responsable = User::findOrFail($this->responsableId);
            abort_unless($responsable->estado === 'ACTIVO', 422, 'El responsable seleccionado debe estar activo.');
            $codPersonal = $responsable->personal?->cod_personal;
            abort_unless($codPersonal, 422, 'El responsable seleccionado no tiene un registro de personal.');
            $alerta->update(['cod_personal_responsable' => $codPersonal]);
            $nombre = $responsable ? $responsable->nombres . ' ' . $responsable->ap_paterno : $this->responsableId;
            $this->registrarAccion($alerta, 'Asignación de responsable: ' . $nombre, 'ASIGNACION');
        });
        session()->flash('mensaje', 'Responsable asignado.');
    }

    public function guardarAccion(): void
    {
        $this->comprobarPermiso('atender');
        $this->validate(['accion' => 'required|string|min:3|max:10000'], [
            'accion.required' => 'Debe ingresar el detalle de la intervención asistencial.',
            'accion.min' => 'La nota de intervención debe contener al menos 3 caracteres.',
            'accion.max' => 'La nota de intervención no puede superar los 10.000 caracteres.',
        ]);
        $this->modificarAbierta(function ($alerta) {
            $estadoAnterior = $alerta->estado;
            $tipo = $alerta->estado === 'ABIERTA' ? 'INTERVENCION' : 'SEGUIMIENTO';
            if ($tipo === 'INTERVENCION') {
                $alerta->update(['estado' => 'EN_ATENCION']);
            }
            $this->registrarAccion($alerta, $this->accion, $tipo, $estadoAnterior);
        });
        $this->accion = '';
        session()->flash('mensaje', 'Registro de la alerta añadido.');
    }

    public function atenderAlerta(string $id): void
    {
        $this->comprobarPermiso('atender');
        $this->obtenerAlertaAutorizada($id);
        $this->cerrarModales();
        $this->alertaId = $id;
        $this->accionTomada = '';
        $this->modalAtender = true;
    }

    public function guardarAtencion(): void
    {
        $this->comprobarPermiso('atender');

        $this->validate([
            'accionTomada' => 'required|string|min:5|max:10000',
        ], [
            'accionTomada.required' => 'Debe describir la intervención clínica asistencial realizada.',
            'accionTomada.min' => 'La nota de intervención debe contener al menos 5 caracteres descriptivos.',
            'accionTomada.max' => 'La nota de intervención no puede superar los 10.000 caracteres.',
        ]);

        $this->modificarAbierta(function ($alerta) {
            $estadoAnterior = $alerta->estado;
            $alerta->update([
                'estado' => 'EN_ATENCION',
                'cod_personal_responsable' => $alerta->cod_personal_responsable
                    ?? auth()->user()?->personal?->cod_personal,
            ]);
            $this->registrarAccion($alerta, 'Atención: ' . $this->accionTomada, 'INTERVENCION', $estadoAnterior);
        });

        $this->dispatch('alerta-atendida', alertaId: $this->alertaId);
        $this->cerrarModales();
        session()->flash('mensaje', 'Intervención clínica registrada exitosamente.');
    }

    public function cerrarAlerta(string $id): void
    {
        $this->comprobarPermiso('cerrar');
        $this->obtenerAlertaAutorizada($id);
        $this->cerrarModales();
        $this->alertaId = $id;
        $this->observacionCierre = '';
        $this->modalCerrar = true;
    }

    public function confirmarCierre(): void
    {
        $this->comprobarPermiso('cerrar');

        $this->validate([
            'observacionCierre' => 'required|string|min:5|max:10000',
        ], [
            'observacionCierre.required' => 'Debe ingresar la justificación clínica del cierre.',
            'observacionCierre.min' => 'La justificación clínica debe contener al menos 5 caracteres explicativos.',
            'observacionCierre.max' => 'La justificación clínica no puede superar los 10.000 caracteres.',
        ]);

        $this->modificarAbierta(function ($alerta) {
            $estadoAnterior = $alerta->estado;
            $alerta->update([
                'estado' => 'CERRADA',
            ]);
            $this->registrarAccion($alerta, 'Cierre: ' . $this->observacionCierre, 'CIERRE', $estadoAnterior);
        });

        $alertaCerrada = Alerta::with('adultoMayor')->findOrFail($this->alertaId);
        $this->dispatch('alerta-cerrada', alertaId: $this->alertaId);
        $this->cerrarModales();
        $this->resultadoCierre = [
            'residente' => trim(($alertaCerrada->adultoMayor?->nombres ?? '').' '.($alertaCerrada->adultoMayor?->ap_paterno ?? '')),
            'cod_residente' => $alertaCerrada->cod_residente,
            'fecha_hora' => now()->format('d/m/Y H:i'),
        ];
        $this->modalResultadoCierre = true;
    }

    public function cerrarResultadoCierre(): void
    {
        $this->modalResultadoCierre = false;
        $this->resultadoCierre = [];
    }

    private function modificarAbierta(callable $operacion): void
    {
        DB::transaction(function () use ($operacion) {
            $alerta = Alerta::lockForUpdate()->findOrFail($this->alertaId);
            abort_unless($alerta->puedeCerrarse(), 409, 'La alerta está cerrada.');
            $this->autorizarLecturaResidente($alerta->cod_residente);
            $operacion($alerta);
        });
    }

    private function obtenerAlertaAutorizada(string $id): Alerta
    {
        $alerta = Alerta::findOrFail($id);
        $this->autorizarLecturaResidente($alerta->cod_residente);

        return $alerta;
    }

    private function autorizarLecturaResidente(string $codResidente): void
    {
        $usuario = auth()->user();
        $turnos = app(\App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService::class);
        if ($usuario?->hasRole('ENFERMEROS')) {
            $turnos->autorizarAccionPaciente($codResidente, $usuario);

            return;
        }
        if (! $usuario?->hasAnyRole(['SUPERADMINISTRADOR', 'GERENTE', 'ADMINISTRADOR'])) {
            abort_unless($turnos->obtenerPacientesAsignadosQuery($usuario)
                ->whereKey($codResidente)->exists(), 403);
        }
    }

    private function registrarAccion(Alerta $alerta, string $texto, string $tipo = 'SEGUIMIENTO', ?string $estadoAnterior = null): void
    {
        $alerta->eventos()->create([
            'cod_evento_alerta' => 'EVA_' . strtoupper(\Illuminate\Support\Str::random(10)),
            'cod_usuario' => auth()->user()->cod_usuario,
            'tipo_evento' => $tipo,
            'estado_anterior' => $estadoAnterior ?? $alerta->estado,
            'estado_nuevo' => $alerta->estado,
            'fecha_hora' => now(),
            'descripcion' => $texto,
        ]);
    }

    public function verGraficos(string $codResidente): void
    {
        $this->comprobarPermiso('ver');
        $this->autorizarLecturaResidente($codResidente);
        $this->adultoDrawerId = $codResidente;
        $this->adultoDrawer = Residente::with([
            'cama.habitacion',
            'alergias' => fn ($q) => $q->whereIn('estado', ['ACTIVA', 'ACTIVO']),
            'diagnosticos' => fn ($q) => $q->whereIn('estado', ['ACTIVO', 'CONFIRMADO']),
            'planCuidadoActivo',
            'signosVitales' => fn ($q) => $q->where('estado', '!=', 'ANULADO')->latest('fecha_hora')->take(10),
        ])->find($codResidente);

        $this->signosDrawer = $this->adultoDrawer?->signosVitales ?? collect();
        $this->drawerGrafico = true;
        $this->drawerUbicacion = false;
    }

    public function verUbicacion(string $codResidente): void
    {
        $this->comprobarPermiso('ver');
        $this->autorizarLecturaResidente($codResidente);
        $this->adultoDrawerId = $codResidente;
        $this->adultoDrawer = Residente::with([
            'cama.habitacion',
            'alertas' => fn ($q) => $q->orderByDesc('fecha_hora')->take(5),
        ])->find($codResidente);

        $this->drawerUbicacion = true;
        $this->drawerGrafico = false;
    }

    public function cerrarDrawer(): void
    {
        $this->drawerGrafico = false;
        $this->drawerUbicacion = false;
        $this->adultoDrawerId = null;
        $this->adultoDrawer = null;
        $this->signosDrawer = [];
    }

    public function cerrarModales(): void
    {
        $this->modalCrear = false;
        $this->modalAtender = false;
        $this->modalCerrar = false;
        $this->modalDetalle = false;
        $this->modalResultadoCierre = false;
        $this->resultadoCierre = [];
        $this->alertaId = null;
        $this->accionTomada = '';
        $this->observacionCierre = '';
        $this->accion = '';
        $this->responsableId = '';
        $this->resetValidation();
    }

    public function render()
    {
        $this->comprobarPermiso('ver');
        $turnoService = app(\App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService::class);
        $alertasQuery = Alerta::query()
            ->when($this->filtroAdulto, fn ($q) => $q->where('cod_residente', $this->filtroAdulto))
            ->with(['adultoMayor.cama.habitacion', 'responsable']);

        if (!auth()->user()?->hasAnyRole(['SUPERADMINISTRADOR', 'GERENTE', 'ADMINISTRADOR'])) {
            $alertasQuery = $turnoService->acotarAlertasQuery($alertasQuery, auth()->user());
        }

        // Consultas para tarjetas KPI y gráficos sin filtros de paginación
        $baseStatsQuery = clone $alertasQuery;

        $conteos = [
            'total' => (clone $baseStatsQuery)->count(),
            'criticas' => (clone $baseStatsQuery)->whereIn('prioridad', ['CRITICO', 'CRITICA'])->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])->count(),
            'altas' => (clone $baseStatsQuery)->whereIn('prioridad', ['ALTO', 'ALTA'])->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])->count(),
            'abiertas' => (clone $baseStatsQuery)->where('estado', 'ABIERTA')->count(),
            'en_atencion' => (clone $baseStatsQuery)->where('estado', 'EN_ATENCION')->count(),
            'cerradas' => (clone $baseStatsQuery)->where('estado', 'CERRADA')->count(),
        ];

        // Datos para los gráficos dinámicos con Chart.js
        $statsNivel = (clone $baseStatsQuery)
            ->select('prioridad', DB::raw('count(*) as total'))
            ->groupBy('prioridad')
            ->pluck('total', 'prioridad')
            ->toArray();

        $statsOrigen = (clone $baseStatsQuery)
            ->select('modulo', DB::raw('count(*) as total'))
            ->groupBy('modulo')
            ->pluck('total', 'modulo')
            ->toArray();

        $statsEstado = (clone $baseStatsQuery)
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();

        $jerarquiaOrigenes = [
            'SIGNOS' => 0,
            'INCIDENTE' => 0,
            'MEDICACION' => 0,
            'SOLICITUD_MEDICA' => 0,
            'PLAN' => 0,
            'SEGUIMIENTO' => 0,
            'VALORACION' => 0,
            'FICHA' => 0,
            'MANUAL' => 0,
        ];
        foreach ($statsOrigen as $k => $v) {
            $jerarquiaOrigenes[$k] = (int) $v;
        }

        $chartData = [
            'niveles' => [
                'CRITICO' => (int) (($statsNivel['CRITICO'] ?? 0) + ($statsNivel['CRITICA'] ?? 0)),
                'ALTO' => (int) (($statsNivel['ALTO'] ?? 0) + ($statsNivel['ALTA'] ?? 0)),
                'MEDIO' => (int) (($statsNivel['MEDIO'] ?? 0) + ($statsNivel['MEDIA'] ?? 0)),
                'BAJO' => (int) (($statsNivel['BAJO'] ?? 0) + ($statsNivel['BAJA'] ?? 0)),
            ],
            'origenes' => $jerarquiaOrigenes,
            'estados' => [
                'ABIERTA' => (int) ($statsEstado['ABIERTA'] ?? 0),
                'EN_ATENCION' => (int) ($statsEstado['EN_ATENCION'] ?? 0),
                'CERRADA' => (int) ($statsEstado['CERRADA'] ?? 0),
            ],
        ];

        $alertas = $alertasQuery
            ->when($this->search, fn ($q) => $q->where(fn ($q) =>
                $q->whereHas('adultoMayor', fn ($sq) => $sq->whereLike('nombres', '%'.$this->search.'%')
                    ->orWhereLike('apellido_paterno', '%'.$this->search.'%'))
                  ->orWhereLike('tipo', '%'.$this->search.'%')
                  ->orWhereLike('titulo', '%'.$this->search.'%')
                  ->orWhereLike('descripcion', '%'.$this->search.'%')
            ))
            ->when($this->filtroEstado, fn ($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->filtroNivel, fn ($q) => $q->where('prioridad', $this->filtroNivel))
            ->when($this->filtroOrigen, fn ($q) => $q->where('modulo', $this->filtroOrigen))
            ->orderByDesc('fecha_hora')
            ->paginate($this->perPage);

        $adultosQuery = $turnoService->obtenerPacientesAsignadosQuery(auth()->user())
            ->orderBy('apellido_paterno');

        $this->conteos = $conteos;
        $this->chartData = $chartData;

        $detalle = $this->alertaId ? Alerta::with([
            'adultoMayor.cama.habitacion',
            'responsable.usuario',
            'eventos' => fn ($q) => $q->with('usuario')->orderByDesc('fecha_hora'),
        ])->find($this->alertaId) : null;
        if ($detalle) {
            $this->autorizarLecturaResidente($detalle->cod_residente);
        }
        $signoOrigen = $detalle?->modulo === 'SIGNOS' && filled($detalle->cod_registro)
            ? SignoVital::query()->where('cod_residente', $detalle->cod_residente)->find($detalle->cod_registro)
            : null;
        $evolucionSignos = $signoOrigen ? SignoVital::query()
            ->where('cod_residente', $detalle->cod_residente)
            ->whereIn('estado', ['VIGENTE', 'ACTIVO'])
            ->where('fecha_hora', '>=', $signoOrigen->fecha_hora)
            ->orderBy('fecha_hora')
            ->orderBy('cod_signo')
            ->limit(6)
            ->get() : collect();
        $usuario = auth()->user();
        $turnoActivo = $detalle && $usuario?->hasRole('ENFERMEROS')
            ? $turnoService->obtenerTurnoActivo($usuario) : null;
        $puedeRegistrarNuevaMedicion = $detalle?->estado === 'EN_ATENCION'
            && $turnoActivo !== null
            && $usuario?->can('signos_vitales.crear')
            && $usuario?->can('enfermeria.ver_pacientes_asignados')
            && $turnoService->esPacienteAsignado($detalle->cod_residente, $usuario, $turnoActivo->cod_turno);

        return view('livewire.alertas.alertas-panel', [
            'alertas' => $alertas,
            'conteos' => $conteos,
            'chartData' => $chartData,
            'alertaActiva' => $detalle,
            'detalle' => $detalle,
            'signoOrigen' => $signoOrigen,
            'evolucionSignos' => $evolucionSignos,
            'puedeRegistrarNuevaMedicion' => $puedeRegistrarNuevaMedicion,
            'adultos' => $adultosQuery->get(),
            'turnos' => Turno::activos()->orderBy('orden')->get(),
            'usuarios' => User::query()->leftJoin('personal', 'usuarios.cod_usuario', '=', 'personal.cod_usuario')->where('usuarios.estado', 'ACTIVO')->orderBy('personal.apellido_paterno')->select('usuarios.*')->with('personal')->get(),
        ])->layout(request()->routeIs('admin.enfermeria.*') ? 'layouts.enfermeria' : 'layouts.sistema');
    }
}
