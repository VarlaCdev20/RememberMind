<?php

namespace App\Frontend\Livewire\Compartido\Alertas;

use App\Models\Alerta;
use App\Models\EventoAlerta;
use App\Backend\Modulos\Alertas\Servicios\DeteccionAlertasService;
use App\Backend\Modulos\Alertas\Servicios\AlertasService;
use App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService;
use App\Backend\Modulos\Medicacion\Servicios\AgendaMedicacionService;
use App\Models\Residente;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Component;

class CampanaNotificaciones extends Component
{
    public int $conteoAbiertas = 0;
    public array $alertaIdsConocidos = [];
    public array $recordatorioIdsConocidos = [];
    public bool $panelAbierto = false;
    public ?string $alertaIdAccion = null;
    public string $accionTomada = '';
    public string $observacionCierre = '';
    public bool $modalAtencion = false;
    public bool $modalCierre = false;
    public bool $drawerGrafico = false;
    public bool $drawerUbicacion = false;
    public ?string $adultoDrawerId = null;
    public ?Residente $adultoDrawer = null;
    public $signosDrawer = [];

    public function mount(): void
    {
        if (!$this->puedeVer()) {
            return;
        }

        $this->actualizarConteoYLista(false);
    }

    public function puedeVer(): bool
    {
        $user = auth()->user();

        return (bool) $user?->canAny([
            'alertas.ver', 'alertas.gestionar', 'prescripciones.ver', 'administraciones_medicacion.ver',
        ]);
    }

    public function togglePanel(): void
    {
        $this->panelAbierto = !$this->panelAbierto;
    }

    public function cerrarPanel(): void
    {
        $this->panelAbierto = false;
    }

    public function verificarAlertas(): void
    {
        if (!$this->puedeVer()) {
            return;
        }

        $this->actualizarConteoYLista(true);
    }

    public function actualizarConteoYLista(bool $detectarNuevas = true): void
    {
        $pendientes = $this->consultaPendientes()->with(['adultoMayor.cama.habitacion'])
            ->latest('fecha_hora')
            ->get();

        $recordatorios = $this->recordatoriosMedicacion();
        $this->conteoAbiertas = $pendientes->count() + $recordatorios->count();

        $idsActuales = $pendientes->pluck('cod_alerta')->toArray();

        if ($detectarNuevas && !empty($this->alertaIdsConocidos)) {
            $nuevosIds = array_diff($idsActuales, $this->alertaIdsConocidos);
            if (!empty($nuevosIds)) {
                $nuevas = $pendientes->whereIn('cod_alerta', $nuevosIds)->where('estado', 'ABIERTA');
                if ($nuevas->isNotEmpty()) {
                    $primera = $nuevas->first();
                    $nombreResidente = $primera->adultoMayor
                        ? trim("{$primera->adultoMayor->nombres} {$primera->adultoMayor->apellido_paterno}")
                        : 'Residente';

                    $this->dispatch('alerta-nueva', [
                        'id' => $primera->cod_alerta,
                        'titulo' => "Alerta [{$primera->prioridad}] {$primera->tipo}",
                        'mensaje' => "{$nombreResidente}: " . Str::limit($primera->descripcion, 75),
                        'nivel' => $primera->prioridad,
                    ]);
                }
            }
        }

        $this->alertaIdsConocidos = array_values(array_unique(array_merge($this->alertaIdsConocidos, $idsActuales)));

        $recordatorioIds = $recordatorios->pluck('id')->all();
        if ($detectarNuevas && $this->recordatorioIdsConocidos !== []) {
            $nuevo = $recordatorios->first(fn (array $item) => !in_array($item['id'], $this->recordatorioIdsConocidos, true));
            if ($nuevo) {
                $nombre = trim(($nuevo['adulto']?->nombres ?? 'Residente').' '.($nuevo['adulto']?->ap_paterno ?? ''));
                $hora12 = $nuevo['hora_12h'] ?? \Carbon\Carbon::parse("2000-01-01 {$nuevo['hora']}")->format('h:i A');
                $horaCompleta = now()->format('h:i:s A');
                $this->dispatch('alerta-nueva', [
                    'id' => $nuevo['id'],
                    'titulo' => $nuevo['estado'] === 'VENCIDA' ? '⚠️ Se pasó de hora: Dosis vencida' : 'Próxima dosis de medicación',
                    'mensaje' => "{$nombre}: {$nuevo['medicacion']->nombre_medicamento} programada para las {$nuevo['hora']} ({$hora12}) [Hora: {$horaCompleta}]",
                    'nivel' => $nuevo['estado'] === 'VENCIDA' ? 'ALTO' : 'MEDIO',
                ]);
            }
        }
        $this->recordatorioIdsConocidos = array_values(array_unique(array_merge($this->recordatorioIdsConocidos, $recordatorioIds)));
    }

    public function abrirAtender(string $id): void
    {
        $this->comprobarPermiso('atender');
        $this->autorizarAlerta($id);
        Alerta::findOrFail($id);
        $this->alertaIdAccion = $id;
        $this->accionTomada = '';
        $this->modalAtencion = true;
    }

    public function atenderAlerta(string $id, ?string $accion = null): void
    {
        $this->comprobarPermiso('atender');
        $this->autorizarAlerta($id);

        DB::transaction(function () use ($id, $accion) {
            $alerta = Alerta::lockForUpdate()->findOrFail($id);
            abort_unless($alerta->puedeCerrarse(), 409, 'La alerta no está abierta para atención.');

            $textoAccion = $accion ?: ($this->accionTomada ?: 'Atención iniciada desde campana de notificaciones');

            $alerta->update(['estado' => 'EN_ATENCION']);

            EventoAlerta::query()->create([
                'cod_evento_alerta' => 'EVA_'.Str::upper(Str::random(16)),
                'cod_alerta' => $alerta->cod_alerta,
                'cod_usuario' => auth()->id(),
                'tipo_evento' => 'ATENCION',
                'estado_anterior' => 'ABIERTA',
                'estado_nuevo' => 'EN_ATENCION',
                'fecha_hora' => now(),
                'descripcion' => $textoAccion,
            ]);
        });

        $this->actualizarConteoYLista(false);
        $this->dispatch('alerta-atendida');
        $this->modalAtencion = false;
        $this->alertaIdAccion = null;
        $this->accionTomada = '';
    }

    public function abrirCerrar(string $id): void
    {
        $this->comprobarPermiso('cerrar');
        $this->autorizarAlerta($id);
        Alerta::findOrFail($id);
        $this->alertaIdAccion = $id;
        $this->observacionCierre = '';
        $this->modalCierre = true;
    }

    public function cerrarAlerta(string $id, ?string $observacion = null): void
    {
        $this->comprobarPermiso('cerrar');
        $this->autorizarAlerta($id);

        DB::transaction(function () use ($id, $observacion) {
            $alerta = Alerta::lockForUpdate()->findOrFail($id);
            abort_unless($alerta->puedeCerrarse(), 409, 'La alerta ya está cerrada.');

            $textoObs = $observacion ?: ($this->observacionCierre ?: 'Cierre registrado desde campana de notificaciones');

            $estadoAnterior = $alerta->estado;
            $alerta->update(['estado' => 'CERRADA']);

            EventoAlerta::query()->create([
                'cod_evento_alerta' => 'EVA_'.Str::upper(Str::random(16)),
                'cod_alerta' => $alerta->cod_alerta,
                'cod_usuario' => auth()->id(),
                'tipo_evento' => 'CIERRE',
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => 'CERRADA',
                'fecha_hora' => now(),
                'descripcion' => $textoObs,
            ]);
        });

        $this->actualizarConteoYLista(false);
        $this->dispatch('alerta-cerrada');
        $this->modalCierre = false;
        $this->alertaIdAccion = null;
        $this->observacionCierre = '';
    }

    public function verGraficos(string $codResidente): void
    {
        $this->adultoDrawerId = $codResidente;
        $this->adultoDrawer = Residente::with([
            'cama.habitacion',
            'signosVitales' => fn ($q) => $q->where('estado', '!=', 'ANULADO')->latest('fecha_hora')->take(10),
        ])->find($codResidente);

        $this->signosDrawer = $this->adultoDrawer?->signosVitales ?? collect();
        $this->drawerGrafico = true;
        $this->drawerUbicacion = false;
    }

    public function verUbicacion(string $codResidente): void
    {
        $this->adultoDrawerId = $codResidente;
        $this->adultoDrawer = Residente::with([
            'cama.habitacion',
            'alertas' => fn ($q) => $q->latest('fecha_hora')->take(5),
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
        $this->modalAtencion = false;
        $this->modalCierre = false;
        $this->alertaIdAccion = null;
    }

    private function comprobarPermiso(string $accion): void
    {
        $user = auth()->user();
        abort_unless($user, 401);

        abort_unless($user->can($accion === 'ver' ? 'alertas.ver' : 'alertas.gestionar'), 403);
    }

    public function render()
    {
        if (!$this->puedeVer()) {
            return '<div></div>';
        }

        $alertas = $this->consultaPendientes()->with(['adultoMayor.cama.habitacion', 'responsable'])
            ->latest('fecha_hora')
            ->take(15)
            ->get();

        $user = auth()->user();
        $puedeAtender = (bool) $user?->can('alertas.gestionar');
        $puedeCerrar = (bool) $user?->can('alertas.gestionar');

        return view('livewire.alertas.campana-notificaciones', [
            'alertas' => $alertas,
            'recordatoriosMedicacion' => $this->recordatoriosMedicacion(),
            'puedeAtender' => $puedeAtender,
            'puedeCerrar' => $puedeCerrar,
        ]);
    }

    private function consultaPendientes(): Builder
    {
        $query = Alerta::query()->whereIn('estado', ['ABIERTA', 'EN_ATENCION']);
        $user = auth()->user();

        if (!$user?->canAny(['alertas.ver', 'alertas.gestionar'])) {
            return $query->whereRaw('1 = 0');
        }

        if ($user?->hasRole('ENFERMEROS')) {
            $query->whereIn('cod_residente', app(TurnoEnfermeriaService::class)->obtenerPacientesAsignadosIds($user));
        }

        return $query;
    }

    private function autorizarAlerta(string $id): void
    {
        $user = auth()->user();

        if ($user?->hasRole('ENFERMEROS')) {
            $pacientes = app(TurnoEnfermeriaService::class)->obtenerPacientesAsignadosIds($user);
            abort_unless(Alerta::whereKey($id)->whereIn('cod_residente', $pacientes)->exists(), 403);
        }
    }

    private function recordatoriosMedicacion()
    {
        $user = auth()->user();

        if (!$user || !$user->canAny(['prescripciones.ver', 'administraciones_medicacion.ver'])) {
            return collect();
        }

        if (!Schema::hasTable('prescripciones')) {
            return collect();
        }

        $codigos = $user->hasRole('ENFERMEROS')
            ? app(TurnoEnfermeriaService::class)->obtenerPacientesAsignadosIds($user)
            : Residente::query()->pluck('cod_residente')->all();

        return app(AgendaMedicacionService::class)->recordatorios($codigos);
    }
}
