<?php

namespace App\Livewire\Alertas;

use App\Models\AlertaAdulto;
use App\Models\AccionAlerta;
use App\Services\Alertas\DeteccionAlertasService;
use App\Services\Alertas\AlertasService;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use App\Services\Medicacion\AgendaMedicacionService;
use App\Models\AdultoMayor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
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
    public ?AdultoMayor $adultoDrawer = null;
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

        return (bool) ($user?->hasRole('SUPERADMINISTRADOR') || $user?->canAny([
            'alertas.ver', 'alertas.gestionar', 'salud.alertas.ver', 'salud.alertas.gestionar',
            'medicacion.ver', 'salud.medicacion.ver', 'administracion_medicacion.registrar',
        ]));
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
        $pendientes = $this->consultaPendientes()->with(['adultoMayor.habitacion', 'adultoMayor.cama'])
            ->latest()
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
                        ? trim("{$primera->adultoMayor->nombres} {$primera->adultoMayor->ap_paterno}")
                        : 'Residente';

                    $this->dispatch('alerta-nueva', [
                        'id' => $primera->cod_alerta,
                        'titulo' => "Alerta [{$primera->nivel}] {$primera->tipo_alerta}",
                        'mensaje' => "{$nombreResidente}: " . Str::limit($primera->motivo, 75),
                        'nivel' => $primera->nivel,
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
                $this->dispatch('alerta-nueva', [
                    'id' => $nuevo['id'],
                    'titulo' => $nuevo['estado'] === 'VENCIDA' ? 'Dosis de medicación vencida' : 'Próxima dosis de medicación',
                    'mensaje' => "{$nombre}: {$nuevo['medicacion']->nombre_medicamento} a las {$nuevo['hora']}",
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
        AlertaAdulto::findOrFail($id);
        $this->alertaIdAccion = $id;
        $this->accionTomada = '';
        $this->modalAtencion = true;
    }

    public function atenderAlerta(string $id, ?string $accion = null): void
    {
        $this->comprobarPermiso('atender');
        $this->autorizarAlerta($id);

        DB::transaction(function () use ($id, $accion) {
            $alerta = AlertaAdulto::lockForUpdate()->findOrFail($id);
            abort_unless($alerta->puedeCerrarse(), 409, 'La alerta no está abierta para atención.');

            $textoAccion = $accion ?: ($this->accionTomada ?: 'Atención iniciada desde campana de notificaciones');

            $alerta->update([
                'estado' => 'EN_ATENCION',
                'accion_tomada' => $textoAccion,
                'fecha_atencion' => $alerta->fecha_atencion ?? now(),
                'atendido_por' => auth()->id(),
                'responsable_id' => $alerta->responsable_id ?? auth()->id(),
            ]);

            $alerta->acciones()->create([
                'accion' => $textoAccion,
                'responsable_id' => auth()->id(),
                'fecha_accion' => now(),
                'estado' => 'REALIZADA',
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
        AlertaAdulto::findOrFail($id);
        $this->alertaIdAccion = $id;
        $this->observacionCierre = '';
        $this->modalCierre = true;
    }

    public function cerrarAlerta(string $id, ?string $observacion = null): void
    {
        $this->comprobarPermiso('cerrar');
        $this->autorizarAlerta($id);

        DB::transaction(function () use ($id, $observacion) {
            $alerta = AlertaAdulto::lockForUpdate()->findOrFail($id);
            abort_unless($alerta->puedeCerrarse(), 409, 'La alerta ya está cerrada.');

            $textoObs = $observacion ?: ($this->observacionCierre ?: 'Cierre registrado desde campana de notificaciones');

            $alerta->update([
                'estado' => 'CERRADA',
                'fecha_cierre' => now(),
                'cerrado_por' => auth()->id(),
                'observacion_cierre' => $textoObs,
            ]);

            $alerta->acciones()->create([
                'accion' => 'Cierre: ' . $textoObs,
                'responsable_id' => auth()->id(),
                'fecha_accion' => now(),
                'estado' => 'REALIZADA',
            ]);
        });

        $this->actualizarConteoYLista(false);
        $this->dispatch('alerta-cerrada');
        $this->modalCierre = false;
        $this->alertaIdAccion = null;
        $this->observacionCierre = '';
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
        $this->modalAtencion = false;
        $this->modalCierre = false;
        $this->alertaIdAccion = null;
    }

    private function comprobarPermiso(string $accion): void
    {
        $user = auth()->user();
        abort_unless($user, 401);

        if ($user->hasRole('SUPERADMINISTRADOR')) {
            return;
        }

        abort_unless($user->canAny([
            'alertas.'.$accion, 'alertas.gestionar', 'salud.alertas.gestionar',
            ...($accion === 'ver' ? ['salud.alertas.ver'] : []),
        ]), 403);
    }

    public function render()
    {
        if (!$this->puedeVer()) {
            return '<div></div>';
        }

        $alertas = $this->consultaPendientes()->with(['adultoMayor.habitacion', 'adultoMayor.cama', 'responsable'])
            ->latest()
            ->take(15)
            ->get();

        $user = auth()->user();
        $puedeAtender = (bool) ($user?->hasRole('SUPERADMINISTRADOR') || $user?->canAny(['alertas.atender', 'alertas.gestionar', 'salud.alertas.gestionar']));
        $puedeCerrar = (bool) ($user?->hasRole('SUPERADMINISTRADOR') || $user?->canAny(['alertas.cerrar', 'alertas.gestionar', 'salud.alertas.gestionar']));

        return view('livewire.alertas.campana-notificaciones', [
            'alertas' => $alertas,
            'recordatoriosMedicacion' => $this->recordatoriosMedicacion(),
            'puedeAtender' => $puedeAtender,
            'puedeCerrar' => $puedeCerrar,
        ]);
    }

    private function consultaPendientes(): Builder
    {
        $query = AlertaAdulto::query()->whereIn('estado', ['ABIERTA', 'EN_ATENCION']);
        $user = auth()->user();

        if (!$user?->hasRole('SUPERADMINISTRADOR') && !$user?->canAny([
            'alertas.ver', 'alertas.gestionar', 'salud.alertas.ver', 'salud.alertas.gestionar',
        ])) {
            return $query->whereRaw('1 = 0');
        }

        if ($user?->hasRole('ENFERMEROS')) {
            $query->whereIn('cod_am', app(TurnoEnfermeriaService::class)->obtenerPacientesAsignadosIds($user));
        }

        return $query;
    }

    private function autorizarAlerta(string $id): void
    {
        $user = auth()->user();

        if ($user?->hasRole('ENFERMEROS')) {
            $pacientes = app(TurnoEnfermeriaService::class)->obtenerPacientesAsignadosIds($user);
            abort_unless(AlertaAdulto::whereKey($id)->whereIn('cod_am', $pacientes)->exists(), 403);
        }
    }

    private function recordatoriosMedicacion()
    {
        $user = auth()->user();

        if (!$user || (!$user->hasRole('SUPERADMINISTRADOR') && !$user->canAny([
            'medicacion.ver', 'salud.medicacion.ver', 'administracion_medicacion.registrar',
        ]))) {
            return collect();
        }

        $codigos = $user->hasRole('ENFERMEROS')
            ? app(TurnoEnfermeriaService::class)->obtenerPacientesAsignadosIds($user)
            : AdultoMayor::query()->pluck('cod_am')->all();

        return app(AgendaMedicacionService::class)->recordatorios($codigos);
    }
}
