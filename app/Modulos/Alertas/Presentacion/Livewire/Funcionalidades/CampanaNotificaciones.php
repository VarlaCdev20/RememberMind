<?php

namespace App\Modulos\Alertas\Presentacion\Livewire\Funcionalidades;

use App\Models\Alerta;
use App\Models\Residente;
use App\Modulos\Alertas\Aplicacion\Acciones\GestionarAtencionAlerta;
use App\Modulos\Alertas\Aplicacion\Consultas\ConsultarPanelResidenteAlerta;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use App\Modulos\Medicacion\Aplicacion\Consultas\AgendaMedicacionService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Component;

class CampanaNotificaciones extends Component
{
    public int $conteoAbiertas = 0;

    #[\Livewire\Attributes\Locked]
    public array $alertaIdsConocidos = [];

    #[\Livewire\Attributes\Locked]
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
        if (! $this->puedeVer()) {
            return;
        }

        $this->actualizarConteoYLista(false);
    }

    public function puedeVer(): bool
    {
        $user = auth()->user();

        return $user && $user->fresh()?->estado === 'ACTIVO' && ! $user->hasRole('FAMILIAR') && (bool) $user->canAny([
            'alertas.ver', 'alertas.gestionar', 'prescripciones.ver', 'administraciones_medicacion.ver',
        ]);
    }

    public function togglePanel(): void
    {
        $this->panelAbierto = ! $this->panelAbierto;
    }

    public function cerrarPanel(): void
    {
        $this->panelAbierto = false;
    }

    public function verificarAlertas(): void
    {
        if (! $this->puedeVer()) {
            return;
        }

        $this->actualizarConteoYLista(true);
    }

    public function actualizarConteoYLista(bool $detectarNuevas = true): void
    {
        if (! $this->puedeVer()) { $this->conteoAbiertas = 0; return; }
        $pendientes = $this->consultaPendientes()->with('residente')->get();
        $recordatorios = $this->recordatoriosMedicacion();
        $this->conteoAbiertas = $pendientes->count() + $recordatorios->count();
        $nuevas = $pendientes->reject(fn ($a) => in_array($a->cod_alerta.'|'.$a->prioridad, $this->alertaIdsConocidos, true));
        if ($detectarNuevas && $nuevas->isNotEmpty()) {
            $primera = $nuevas->first();
            $this->dispatch('alerta-nueva', [
                'id' => $primera->cod_alerta, 'nivel' => $primera->prioridad,
                'titulo' => $nuevas->count().' aviso(s) clínico(s) nuevo(s)',
                'mensaje' => $primera->prioridad.' · '.$primera->residente?->nombre_completo.' · '.$primera->tipo.' · Registrada '.($primera->fecha_hora?->timezone(config('app.timezone'))->format('d/m/Y H:i:s T') ?? 'sin fecha').'. Revise la lista de alertas; recibir este aviso no registra atención.',
            ]);
        }
        $this->alertaIdsConocidos = $pendientes->map(fn ($a) => $a->cod_alerta.'|'.$a->prioridad)->values()->all();
        $nuevasDosis = $recordatorios->reject(fn ($r) => in_array($r['id'].'|'.$r['estado'], $this->recordatorioIdsConocidos, true));
        if ($detectarNuevas && $nuevasDosis->isNotEmpty()) {
            $primera = $nuevasDosis->first();
            $this->dispatch('alerta-nueva', [
                'id' => $primera['id'], 'nivel' => $primera['estado'],
                'titulo' => $nuevasDosis->count().' aviso(s) de medicación',
                'mensaje' => $primera['estado'].' · '.$primera['adulto']?->nombre_completo.' · '.$primera['medicacion']->nombre_medicamento.' · Programada '.$primera['programada']->format('d/m/Y H:i:s T').'. Compruebe la orden, el residente y el registro antes de administrar. Revisar el aviso no confirma una dosis.',
            ]);
        }
        $this->recordatorioIdsConocidos = $recordatorios->map(fn ($r) => $r['id'].'|'.$r['estado'])->values()->all();
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

        app(GestionarAtencionAlerta::class)->atenderAlerta(auth()->user(), $id, $accion ?? $this->accionTomada);

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

        app(GestionarAtencionAlerta::class)->cerrarAlerta(auth()->user(), $id, $observacion ?? $this->observacionCierre);

        $this->actualizarConteoYLista(false);
        $this->dispatch('alerta-cerrada');
        $this->modalCierre = false;
        $this->alertaIdAccion = null;
        $this->observacionCierre = '';
    }

    public function verGraficos(string $codResidente): void
    {
        $this->adultoDrawerId = $codResidente;
        $this->adultoDrawer = app(ConsultarPanelResidenteAlerta::class)->consultar(auth()->user(), $codResidente, 'graficos');

        $this->signosDrawer = $this->adultoDrawer?->signosVitales ?? collect();
        $this->drawerGrafico = true;
        $this->drawerUbicacion = false;
    }

    public function verUbicacion(string $codResidente): void
    {
        $this->adultoDrawerId = $codResidente;
        $this->adultoDrawer = app(ConsultarPanelResidenteAlerta::class)->consultar(auth()->user(), $codResidente, 'ubicacion');

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

        $permiso = match ($accion) {
            'ver' => 'alertas.ver',
            'cerrar' => 'alertas.cerrar',
            default => 'alertas.seguimiento',
        };
        abort_unless($user->canAny([$permiso, 'alertas.gestionar']), 403);
    }

    public function render()
    {
        if (($this->drawerGrafico || $this->drawerUbicacion) && $this->adultoDrawerId) {
            $this->adultoDrawer = app(ConsultarPanelResidenteAlerta::class)->consultar(auth()->user(), $this->adultoDrawerId, $this->drawerGrafico ? 'graficos' : 'ubicacion');
            if ($this->drawerGrafico) {
                $this->signosDrawer = $this->adultoDrawer->signosVitales;
            }
        }

        if (! $this->puedeVer()) {
            return '<div></div>';
        }

        $alertas = $this->consultaPendientes()->with(['residente.cama.habitacion', 'responsable'])
            ->tap(fn ($q) => \App\Modulos\Alertas\Aplicacion\Consultas\OrdenAlertas::aplicar($q))
            ->take(15)
            ->get();

        $user = auth()->user();
        $puedeAtender = (bool) $user?->canAny(['alertas.gestionar', 'alertas.seguimiento']);
        $puedeCerrar = (bool) $user?->canAny(['alertas.gestionar', 'alertas.cerrar']);

        return view('livewire.alertas.campana-notificaciones', [
            'alertas' => $alertas,
            'recordatoriosMedicacion' => $this->recordatoriosMedicacion(),
            'puedeAtender' => $puedeAtender,
            'puedeCerrar' => $puedeCerrar,
        ]);
    }

    private function consultaPendientes(): Builder
    {
        $query = Alerta::query()->whereIn('estado', ['ABIERTA', 'EN_ATENCION', 'RECONOCIDA', 'ASIGNADA', 'PENDIENTE']);
        \App\Modulos\Alertas\Aplicacion\Consultas\OrdenAlertas::aplicar($query);
        $user = auth()->user();

        if (! $this->puedeVer() || ! $user?->canAny(['alertas.ver', 'alertas.gestionar', 'alertas.seguimiento', 'alertas.cerrar'])) {
            return $query->whereRaw('1 = 0');
        }

        if (! $user?->hasAnyRole(['SUPERADMINISTRADOR','GERENTE','ADMINISTRADOR'])) {
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

        if (! $this->puedeVer() || ! $user || ! $user->canAny(['prescripciones.ver', 'administraciones_medicacion.ver'])) {
            return collect();
        }

        if (! Schema::hasTable('prescripciones')) {
            return collect();
        }

        $codigos = ! $user->hasAnyRole(['SUPERADMINISTRADOR','GERENTE','ADMINISTRADOR'])
            ? app(TurnoEnfermeriaService::class)->obtenerPacientesAsignadosIds($user)
            : Residente::query()->pluck('cod_residente')->all();

        return app(AgendaMedicacionService::class)->recordatorios($codigos);
    }
}
