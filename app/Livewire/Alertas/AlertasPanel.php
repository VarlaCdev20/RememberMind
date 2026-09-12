<?php

namespace App\Livewire\Alertas;

use App\Models\{AdultoMayor, AlertaAdulto, TurnoEnfermeria, User};
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

        $alerta = AlertaAdulto::create([
            'cod_am' => $this->codAm,
            'cod_turno' => $this->codTurno ?: null,
            'origen' => $this->origen,
            'tipo_alerta' => mb_strtoupper(trim($this->tipoAlerta)),
            'nivel' => $this->nivel,
            'motivo' => trim($this->motivo),
            'estado' => 'ABIERTA',
            'fecha_alerta' => now(),
        ]);

        $this->dispatch('alerta-creada', alertaId: $alerta->cod_alerta);
        $this->cerrarModales();
        session()->flash('mensaje', 'Alerta clínica registrada exitosamente.');
    }

    public function verDetalle(string $id): void
    {
        $this->comprobarPermiso('ver');
        $alerta = AlertaAdulto::findOrFail($id);
        if (auth()->user()?->hasRole('ENFERMEROS')) {
            app(\App\Services\Enfermeria\TurnoEnfermeriaService::class)
                ->autorizarAccionPaciente($alerta->cod_am, auth()->user());
        }
        $this->cerrarModales();
        $this->alertaId = $id;
        $this->responsableId = $alerta->responsable_id ?? '';
        $this->accion = '';
        $this->modalDetalle = true;
    }

    public function asignarResponsable(): void
    {
        $this->comprobarPermiso('atender');
        $this->validate(['responsableId' => 'required|exists:users,cod_usu'], [
            'responsableId.required' => 'Debe seleccionar un profesional responsable.',
            'responsableId.exists' => 'El profesional seleccionado no es válido.',
        ]);
        $this->modificarAbierta(function ($alerta) {
            $alerta->update(['responsable_id' => $this->responsableId]);
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
        AlertaAdulto::findOrFail($id);
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
                'accion_tomada' => $this->accionTomada,
                'fecha_atencion' => $alerta->fecha_atencion ?? now(),
                'atendido_por' => auth()->id(),
                'responsable_id' => $alerta->responsable_id ?? auth()->id(),
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
        AlertaAdulto::findOrFail($id);
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
                'fecha_cierre' => now(),
                'cerrado_por' => auth()->id(),
                'observacion_cierre' => $this->observacionCierre,
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
            $alerta = AlertaAdulto::lockForUpdate()->findOrFail($this->alertaId);
            abort_unless($alerta->puedeCerrarse(), 409, 'La alerta está cerrada.');
            $user = auth()->user();
            if ($user && !$user->hasRole('SUPERADMINISTRADOR') && !$user->hasRole('ADMINISTRADOR') && $user->hasRole('ENFERMEROS')) {
                app(\App\Services\Enfermeria\TurnoEnfermeriaService::class)->autorizarAccionPaciente($alerta->cod_am, $user);
            }
            $operacion($alerta);
        });
    }

    private function registrarAccion(AlertaAdulto $alerta, string $texto): void
    {
        $alerta->acciones()->create([
            'accion' => $texto,
            'responsable_id' => auth()->id(),
            'fecha_accion' => now(),
            'estado' => 'REALIZADA',
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
        $alertasQuery = AlertaAdulto::query()
            ->when($this->filtroAdulto, fn ($q) => $q->where('cod_am', $this->filtroAdulto))
            ->with(['adultoMayor.habitacion', 'adultoMayor.cama', 'turno', 'responsable']);

        if (!$turnoService->esSuperAdmin(auth()->user())) {
            $alertasQuery = $turnoService->acotarAlertasQuery($alertasQuery, auth()->user());
        }

        // Consultas para tarjetas KPI y gráficos sin filtros de paginación
        $baseStatsQuery = clone $alertasQuery;

        $conteos = [
            'total' => (clone $baseStatsQuery)->count(),
            'criticas' => (clone $baseStatsQuery)->where('nivel', 'CRITICO')->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])->count(),
            'altas' => (clone $baseStatsQuery)->where('nivel', 'ALTO')->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])->count(),
            'abiertas' => (clone $baseStatsQuery)->where('estado', 'ABIERTA')->count(),
            'en_atencion' => (clone $baseStatsQuery)->where('estado', 'EN_ATENCION')->count(),
            'cerradas' => (clone $baseStatsQuery)->where('estado', 'CERRADA')->count(),
        ];

        // Datos para los gráficos dinámicos con Chart.js
        $statsNivel = (clone $baseStatsQuery)
            ->select('nivel', DB::raw('count(*) as total'))
            ->groupBy('nivel')
            ->pluck('total', 'nivel')
            ->toArray();

        $statsOrigen = (clone $baseStatsQuery)
            ->select('origen', DB::raw('count(*) as total'))
            ->groupBy('origen')
            ->pluck('total', 'origen')
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
                'CRITICO' => (int) ($statsNivel['CRITICO'] ?? 0),
                'ALTO' => (int) ($statsNivel['ALTO'] ?? 0),
                'MEDIO' => (int) ($statsNivel['MEDIO'] ?? 0),
                'BAJO' => (int) ($statsNivel['BAJO'] ?? 0),
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
                    ->orWhereLike('ap_paterno', '%'.$this->search.'%'))
                  ->orWhereLike('tipo_alerta', '%'.$this->search.'%')
                  ->orWhereLike('motivo', '%'.$this->search.'%')
            ))
            ->when($this->filtroEstado, fn ($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->filtroNivel, fn ($q) => $q->where('nivel', $this->filtroNivel))
            ->when($this->filtroOrigen, fn ($q) => $q->where('origen', $this->filtroOrigen))
            ->latest()
            ->paginate($this->perPage);

        $adultosQuery = $turnoService->obtenerPacientesAsignadosQuery(auth()->user())
            ->whereNull('archivado_en')
            ->orderBy('ap_paterno');

        $this->conteos = $conteos;
        $this->chartData = $chartData;

        return view('livewire.alertas.alertas-panel', [
            'alertas' => $alertas,
            'conteos' => $conteos,
            'chartData' => $chartData,
            'alertaActiva' => $this->alertaId ? AlertaAdulto::with([
                'adultoMayor.habitacion',
                'adultoMayor.cama',
                'responsable',
                'atendidoPor',
                'cerradoPor',
                'turno',
                'acciones' => fn ($q) => $q->with('responsable.roles')->orderByDesc('fecha_accion')->orderByDesc('cod_accion_alerta'),
            ])->find($this->alertaId) : null,
            'detalle' => $this->alertaId ? AlertaAdulto::with([
                'adultoMayor.habitacion',
                'adultoMayor.cama',
                'responsable',
                'atendidoPor',
                'cerradoPor',
                'turno',
                'acciones' => fn ($q) => $q->with('responsable.roles')->orderByDesc('fecha_accion')->orderByDesc('cod_accion_alerta'),
            ])->find($this->alertaId) : null,
            'adultos' => $adultosQuery->get(),
            'turnos' => TurnoEnfermeria::activos()->get(),
            'usuarios' => User::where('estado', 'ACTIVO')->orderBy('ap_paterno')->get(),
        ])->layout('layouts.sistema');
    }
}
