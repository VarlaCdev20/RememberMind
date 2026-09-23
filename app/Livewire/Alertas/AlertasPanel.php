<?php

namespace App\Livewire\Alertas;

use App\Models\{AdultoMayor, Alerta, TurnoEnfermeria, User};
use App\Services\Alertas\DeteccionAlertasService;
use App\Services\Alertas\AlertasService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class AlertasPanel extends Component
{
    use WithPagination;

    #[\Livewire\Attributes\Url(as: 'adulto')]
    public string $filtroAdulto = '';
    public int $perPage = 15;
    public string $search = '', $filtroEstado = 'ABIERTA', $filtroNivel = '', $filtroOrigen = '';
    public bool $modalCrear = false, $modalAtender = false, $modalCerrar = false, $modalDetalle = false;
    public bool $drawerGrafico = false;
    public bool $drawerUbicacion = false;
    public ?string $adultoDrawerId = null;
    public ?AdultoMayor $adultoDrawer = null;
    public $signosDrawer = [];
    public ?string $alertaId = null;
        public string $codAm = '', $codTurno = '', $origen = 'MANUAL', $tipoAlerta = '', $nivel = 'MEDIO', $motivo = '';
    public string $accionTomada = '', $observacionCierre = '', $accion = '', $responsableId = '';
    public array $conteos = [];
    public array $chartData = [];

    public function mount(): void
    {
        $this->comprobarPermiso('ver');
    }

    private function comprobarPermiso(string $accion): void
    {
        $user = auth()->user();
        abort_unless($user, 401);
        if ($user->hasRole('SUPERADMINISTRADOR') || $user->hasRole('ADMINISTRADOR')) {
            return;
        }
        abort_unless($user->canAny([
            'alertas.'.$accion, 'alertas.gestionar', 'salud.alertas.gestionar',
            ...($accion === 'ver' ? ['salud.alertas.ver'] : []),
        ]), 403);
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
        $this->reset('codAm', 'codTurno', 'tipoAlerta', 'motivo', 'accionTomada');
        $this->origen = 'MANUAL';
        $this->nivel = 'MEDIO';
        $this->modalCrear = true;
    }

    public function guardarAlerta(): void
    {
        $this->comprobarPermiso('crear');

        $this->validate([
            'codAm' => 'required|string|exists:adulto_mayor,cod_am',
            'origen' => 'required|in:SIGNOS,MEDICACION,SEGUIMIENTO,PLAN,INCIDENTE,SOLICITUD_MEDICA,MANUAL,FICHA,VALORACION',
            'tipoAlerta' => 'required|string|min:3|max:80',
            'nivel' => 'required|in:BAJO,MEDIO,ALTO,CRITICO',
            'motivo' => 'required|string|min:10|max:10000',
        ], [
            'codAm.required' => 'Debe seleccionar un residente asignado.',
            'codAm.exists' => 'El residente seleccionado no es válido.',
            'origen.required' => 'Debe seleccionar el origen clínico de la alerta.',
            'tipoAlerta.required' => 'El tipo o diagnóstico clínico es obligatorio.',
            'tipoAlerta.min' => 'El tipo de alerta debe tener al menos 3 caracteres.',
            'tipoAlerta.max' => 'El tipo de alerta no puede superar los 80 caracteres.',
            'nivel.required' => 'Debe seleccionar el nivel de severidad.',
            'motivo.required' => 'Debe detallar el motivo clínico de la alerta.',
            'motivo.min' => 'El motivo clínico debe contener al menos 10 caracteres explicativos.',
        ]);

        if (auth()->user() && auth()->user()->hasRole('ENFERMEROS')) {
            app(\App\Services\Enfermeria\TurnoEnfermeriaService::class)->autorizarAccionPaciente($this->codAm);
        }

        $alerta = Alerta::create([
            'cod_alerta' => 'ALA_' . strtoupper(\Illuminate\Support\Str::random(10)),
            'cod_residente' => $this->codAm,
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
        $alerta = Alerta::findOrFail($id);
        if (auth()->user()?->hasRole('ENFERMEROS')) {
            app(\App\Services\Enfermeria\TurnoEnfermeriaService::class)
                ->autorizarAccionPaciente($alerta->cod_residente, auth()->user());
        }
        $this->cerrarModales();
        $this->alertaId = $id;
        $this->responsableId = $alerta->responsable?->cod_usuario ?? '';
        $this->accion = '';
        $this->modalDetalle = true;
    }

    public function asignarResponsable(): void
    {
        $this->comprobarPermiso('atender');
        $this->validate(['responsableId' => 'required|exists:usuarios,cod_usuario'], [
            'responsableId.required' => 'Debe seleccionar un profesional responsable.',
            'responsableId.exists' => 'El profesional seleccionado no es válido.',
        ]);
        $this->modificarAbierta(function ($alerta) {
            $codPersonal = User::findOrFail($this->responsableId)->personal?->cod_personal;
            abort_unless($codPersonal, 422, 'El responsable seleccionado no tiene un registro de personal.');
            $alerta->update(['cod_personal_responsable' => $codPersonal]);
            $responsable = User::find($this->responsableId);
            $nombre = $responsable ? $responsable->nombres . ' ' . $responsable->ap_paterno : $this->responsableId;
            $this->registrarAccion($alerta, 'Asignación de responsable: ' . $nombre);
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
            $this->registrarAccion($alerta, $this->accion);
        });
        $this->accion = '';
        session()->flash('mensaje', 'Acción registrada.');
    }

    public function atenderAlerta(string $id): void
    {
        $this->comprobarPermiso('atender');
        Alerta::findOrFail($id);
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
            $alerta->update([
                'estado' => 'EN_ATENCION',
                'cod_personal_responsable' => $alerta->cod_personal_responsable
                    ?? auth()->user()?->personal?->cod_personal,
            ]);
            $this->registrarAccion($alerta, 'Atención: ' . $this->accionTomada);
        });

        $this->dispatch('alerta-atendida', alertaId: $this->alertaId);
        $this->cerrarModales();
        session()->flash('mensaje', 'Intervención clínica registrada exitosamente.');
    }

    public function cerrarAlerta(string $id): void
    {
        $this->comprobarPermiso('cerrar');
        Alerta::findOrFail($id);
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
            $alerta->update([
                'estado' => 'CERRADA',
            ]);
            $this->registrarAccion($alerta, 'Cierre: ' . $this->observacionCierre);
        });

        $this->dispatch('alerta-cerrada', alertaId: $this->alertaId);
        $this->cerrarModales();
        session()->flash('mensaje', 'Alerta resuelta y archivada conservando el historial clínico íntegro.');
    }

    private function modificarAbierta(callable $operacion): void
    {
        DB::transaction(function () use ($operacion) {
            $alerta = Alerta::lockForUpdate()->findOrFail($this->alertaId);
            abort_unless($alerta->puedeCerrarse(), 409, 'La alerta está cerrada.');
            $user = auth()->user();
            if ($user && !$user->hasRole('SUPERADMINISTRADOR') && !$user->hasRole('ADMINISTRADOR') && $user->hasRole('ENFERMEROS')) {
                app(\App\Services\Enfermeria\TurnoEnfermeriaService::class)->autorizarAccionPaciente($alerta->cod_residente, $user);
            }
            $operacion($alerta);
        });
    }

    private function registrarAccion(Alerta $alerta, string $texto): void
    {
        $alerta->eventos()->create([
            'cod_evento_alerta' => 'EVA_' . strtoupper(\Illuminate\Support\Str::random(10)),
            'cod_usuario' => auth()->user()->cod_usuario,
            'tipo_evento' => 'SEGUIMIENTO',
            'estado_anterior' => $alerta->getOriginal('estado'),
            'estado_nuevo' => $alerta->estado,
            'fecha_hora' => now(),
            'descripcion' => $texto,
        ]);
    }

    public function verGraficos(string $codAm): void
    {
        $this->adultoDrawerId = $codAm;
        $this->adultoDrawer = AdultoMayor::with([
            'estado',
            'habitacion',
            'cama',
            'signosVitales' => fn ($q) => $q->where('estado', '!=', 'ANULADO')->latest('fecha')->latest('hora')->take(10),
        ])->find($codAm);

        $this->signosDrawer = $this->adultoDrawer?->signosVitales ?? collect();
        $this->drawerGrafico = true;
        $this->drawerUbicacion = false;
    }

    public function verUbicacion(string $codAm): void
    {
        $this->adultoDrawerId = $codAm;
        $this->adultoDrawer = AdultoMayor::with([
            'estado',
            'habitacion',
            'cama',
            'alertas' => fn ($q) => $q->latest()->take(5),
        ])->find($codAm);

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
        $turnoService = app(\App\Services\Enfermeria\TurnoEnfermeriaService::class);
        $alertasQuery = Alerta::query()
            ->when($this->filtroAdulto, fn ($q) => $q->where('cod_residente', $this->filtroAdulto))
            ->with(['adultoMayor.cama.habitacion', 'responsable']);

        if (!$turnoService->esSuperAdmin(auth()->user())) {
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

        return view('livewire.alertas.alertas-panel', [
            'alertas' => $alertas,
            'conteos' => $conteos,
            'chartData' => $chartData,
            'alertaActiva' => $this->alertaId ? Alerta::with([
                'adultoMayor.cama.habitacion',
                'responsable.usuario',
                'eventos' => fn ($q) => $q->with('usuario')->orderByDesc('fecha_hora'),
            ])->find($this->alertaId) : null,
            'detalle' => $this->alertaId ? Alerta::with([
                'adultoMayor.cama.habitacion',
                'responsable.usuario',
                'eventos' => fn ($q) => $q->with('usuario')->orderByDesc('fecha_hora'),
            ])->find($this->alertaId) : null,
            'adultos' => $adultosQuery->get(),
            'turnos' => TurnoEnfermeria::activos()->get(),
            'usuarios' => User::query()->leftJoin('personal', 'usuarios.cod_usuario', '=', 'personal.cod_usuario')->where('usuarios.estado', 'ACTIVO')->orderBy('personal.apellido_paterno')->select('usuarios.*')->with('personal')->get(),
        ])->layout(request()->routeIs('admin.enfermeria.*') ? 'layouts.enfermeria' : 'layouts.sistema');
    }
}
