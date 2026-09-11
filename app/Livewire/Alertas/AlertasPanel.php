<?php

namespace App\Livewire\Alertas;

use App\Models\{AdultoMayor, AlertaAdulto, TurnoEnfermeria, User};
use App\Services\Alertas\DeteccionAlertasService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class AlertasPanel extends Component
{
    use WithPagination;

    #[\Livewire\Attributes\Url(as: 'adulto')]
    public string $filtroAdulto = '';
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
        abort_unless(auth()->user()?->canAny([
            'alertas.'.$accion, 'alertas.gestionar', 'salud.alertas.gestionar',
            ...($accion === 'ver' ? ['salud.alertas.ver'] : []),
        ]), 403);
    }

    public function updated($campo): void
    {
        if (in_array($campo, ['search', 'filtroEstado', 'filtroNivel', 'filtroOrigen', 'filtroAdulto'])) {
            $this->resetPage();
        }
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['search', 'filtroEstado', 'filtroNivel', 'filtroOrigen', 'filtroAdulto']);
        $this->resetPage();
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
        session()->flash('mensaje', "Detección completada: {$cantidad} alertas nuevas registradas o actualizadas.");
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
        abort_unless(auth()->check(), 401);
        if (!auth()->user()->hasRole('SUPERADMINISTRADOR')
            && !auth()->user()->hasRole('ADMINISTRADOR')
            && !auth()->user()->can('alertas.gestionar')
            && !auth()->user()->can('salud.alertas.gestionar')
        ) {
            app(\App\Services\Enfermeria\TurnoEnfermeriaService::class)->autorizarAccionPaciente($this->codAm, auth()->user());
        }

        $this->validate([
            'codAm' => 'required|exists:adulto_mayor,cod_am',
            'codTurno' => 'nullable|exists:turnos_enfermeria,cod_turno',
            'origen' => 'required|in:SIGNOS,MEDICACION,SEGUIMIENTO,PLAN,INCIDENTE,SOLICITUD_MEDICA,MANUAL,FICHA,VALORACION',
            'tipoAlerta' => 'required|string|min:3|max:50',
            'nivel' => 'required|in:BAJO,MEDIO,ALTO,CRITICO',
            'motivo' => 'required|string|min:10|max:10000',
        ], [
            'tipoAlerta.in' => 'Seleccione un tipo de alerta clínica válido.',
            'motivo.min'    => 'El motivo de la alerta debe tener al menos 10 caracteres.',
        ]);

        $tipoNorm = mb_strtoupper(trim($this->tipoAlerta));
        $duplicada = AlertaAdulto::where('cod_am', $this->codAm)
            ->where('tipo_alerta', $tipoNorm)
            ->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])
            ->exists();

        if ($duplicada) {
            $this->addError('tipoAlerta', 'Ya existe una alerta activa equivalente (ABIERTA o EN ATENCIÓN) para este residente.');
            return;
        }

        AlertaAdulto::create([
            'cod_am' => $this->codAm,
            'cod_turno' => $this->codTurno ?: null,
            'origen' => $this->origen,
            'tipo_alerta' => $tipoNorm,
            'nivel' => $this->nivel,
            'motivo' => $this->motivo,
            'responsable_id' => auth()->id(),
            'estado' => 'ABIERTA',
        ]);
        $this->cerrarModales();
        session()->flash('mensaje', 'Alerta registrada correctamente.');
    }

    public function verDetalle(string $id): void
    {
        $this->comprobarPermiso('ver');
        $alerta = AlertaAdulto::findOrFail($id);
        $this->alertaId = $id;
        $this->responsableId = $alerta->responsable_id ?? '';
        $this->accion = '';
        $this->modalDetalle = true;
    }

    public function asignarResponsable(): void
    {
        $this->comprobarPermiso('atender');
        $this->validate(['responsableId' => 'required|exists:users,cod_usu']);
        $this->modificarAbierta(function ($alerta) {
            $alerta->update(['responsable_id' => $this->responsableId]);
            $this->registrarAccion($alerta, 'Responsable asignado: '.$this->responsableId);
        });
        session()->flash('mensaje', 'Responsable asignado.');
    }

    public function guardarAccion(): void
    {
        $this->comprobarPermiso('atender');
        $this->validate(['accion' => 'required|string|min:5|max:10000']);
        $this->modificarAbierta(fn ($alerta) => $this->registrarAccion($alerta, $this->accion));
        $this->accion = '';
        session()->flash('mensaje', 'Acción registrada en el historial.');
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
        $this->validate(['accionTomada' => 'required|string|min:5|max:10000']);
        $this->modificarAbierta(function ($alerta) {
            $alerta->update([
                'estado' => 'EN_ATENCION',
                'accion_tomada' => $this->accionTomada,
                'fecha_atencion' => $alerta->fecha_atencion ?? now(),
                'atendido_por' => auth()->id(),
                'responsable_id' => $alerta->responsable_id ?? auth()->id(),
            ]);
            $this->registrarAccion($alerta, $this->accionTomada);
        });
        $this->cerrarModales();
        session()->flash('mensaje', 'Atención registrada.');
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
        $this->validate(['observacionCierre' => 'required|string|min:5|max:10000']);
        $this->modificarAbierta(function ($alerta) {
            $alerta->update([
                'estado' => 'CERRADA',
                'fecha_cierre' => now(),
                'cerrado_por' => auth()->id(),
                'observacion_cierre' => $this->observacionCierre,
            ]);
            $this->registrarAccion($alerta, 'Cierre: '.$this->observacionCierre);
        });
        $this->cerrarModales();
        session()->flash('mensaje', 'Alerta cerrada; historial conservado.');
    }

    private function modificarAbierta(callable $operacion): void
    {
        DB::transaction(function () use ($operacion) {
            $alerta = AlertaAdulto::lockForUpdate()->findOrFail($this->alertaId);
            abort_unless($alerta->puedeCerrarse(), 409, 'La alerta está cerrada.');
            if (auth()->user() && auth()->user()->hasRole('ENFERMEROS')) {
                app(\App\Services\Enfermeria\TurnoEnfermeriaService::class)->autorizarAccionPaciente($alerta->cod_am);
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
        $this->modalCrear = $this->modalAtender = $this->modalCerrar = $this->modalDetalle = false;
        $this->alertaId = null;
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

        $chartData = [
            'niveles' => [
                'CRITICO' => (int) ($statsNivel['CRITICO'] ?? 0),
                'ALTO' => (int) ($statsNivel['ALTO'] ?? 0),
                'MEDIO' => (int) ($statsNivel['MEDIO'] ?? 0),
                'BAJO' => (int) ($statsNivel['BAJO'] ?? 0),
            ],
            'origenes' => array_map('intval', $statsOrigen),
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
            ->paginate(15);

        $adultosQuery = $turnoService->obtenerPacientesAsignadosQuery(auth()->user())
            ->whereNull('archivado_en')
            ->orderBy('ap_paterno');

        $this->conteos = $conteos;
        $this->chartData = $chartData;

        return view('livewire.alertas.alertas-panel', [
            'alertas' => $alertas,
            'conteos' => $conteos,
            'chartData' => $chartData,
            'detalle' => $this->modalDetalle ? AlertaAdulto::with(['adultoMayor.habitacion', 'adultoMayor.cama',
                'acciones' => fn ($q) => $q->with('responsable')->orderBy('fecha_accion')->orderBy('cod_accion_alerta'),
            ])->findOrFail($this->alertaId) : null,
            'adultos' => $adultosQuery->get(),
            'turnos' => TurnoEnfermeria::activos()->get(),
            'usuarios' => User::where('estado', 'ACTIVO')->orderBy('ap_paterno')->get(),
        ])->layout('layouts.sistema');
    }
}