<?php

namespace App\Modulos\Alertas\Presentacion\Livewire\Funcionalidades;

use App\Models\Alerta;
use App\Models\Residente;
use App\Modulos\Alertas\Aplicacion\Acciones\GestionarAtencionAlerta;
use App\Modulos\Alertas\Aplicacion\Consultas\BandejaAlertas;
use App\Modulos\Alertas\Aplicacion\Consultas\ConsultarPanelResidenteAlerta;
use App\Modulos\Alertas\Aplicacion\Consultas\OrdenAlertas;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use App\Modulos\Medicacion\Aplicacion\Consultas\AgendaMedicacionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class CampanaNotificaciones extends Component
{
    public int $conteoAbiertas = 0;

    public int $conteoAlertas = 0;

    public int $conteoRecordatorios = 0;

    public int $conteoDocumentales = 0;
    public int $conteoLaborales = 0;

    #[Locked]
    public array $alertaIdsConocidos = [];

    #[Locked]
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

        if (!$user || $user->fresh()?->estado !== 'ACTIVO' || $user->hasRole('FAMILIAR')) {
            return false;
        }
        return $user->canAny(['alertas.ver', 'alertas.gestionar', 'prescripciones.ver', 'administraciones_medicacion.ver'])
            || (app(\App\Modulos\Identidad\Aplicacion\Consultas\CapacidadDocumentalPersonal::class)->disponible()
                && ($user->personal || app(\App\Modulos\Identidad\Aplicacion\Consultas\ConsultarDocumentacionPersonal::class)->esGestor($user)));
    }

    public function togglePanel(): void
    {
        $this->panelAbierto = ! $this->panelAbierto;
    }

    public function cerrarPanel(): void
    {
        $this->panelAbierto = false;
    }

    #[On('alerta-creada')]
    #[On('alerta-atendida')]
    #[On('alerta-cerrada')]
    #[On('alerta-actualizada')]
    #[On('evento-alerta-registrado')]
    #[On('documentacion-personal-actualizada')]
    public function verificarAlertas(): void
    {
        if (! $this->puedeVer()) {
            return;
        }

        $this->actualizarConteoYLista(true);
    }

    public function actualizarConteoYLista(bool $detectarNuevas = true): void
    {
        if (! $this->puedeVer()) {
            $this->conteoAbiertas = 0;

            return;
        }
        $pendientes = $this->consultaPendientes()->with('residente')->get();
        $recordatorios = $this->recordatoriosMedicacion();
        $this->conteoAlertas = $pendientes->count();
        $this->conteoRecordatorios = $recordatorios->count();
        $this->conteoAbiertas = $this->conteoAlertas + $this->conteoRecordatorios;
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
        abort_unless($user->fresh()?->estado === 'ACTIVO' && ! $user->hasRole('FAMILIAR'), 403);

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
            ->tap(fn ($q) => OrdenAlertas::aplicar($q))
            ->take(15)
            ->get();

        $user = auth()->user();
        $puedeAtender = (bool) $user?->canAny(['alertas.gestionar', 'alertas.seguimiento']);
        $puedeCerrar = (bool) $user?->canAny(['alertas.gestionar', 'alertas.cerrar']);
        $avisosDocumentales=app(\App\Modulos\Identidad\Aplicacion\Consultas\ConsultarDocumentacionPersonal::class)->avisos($user);
        $cambiosLaborales=app(\App\Modulos\Identidad\Aplicacion\Consultas\ConsultarCambiosLaboralesPendientes::class)->consultar($user);
        $this->conteoDocumentales=count($avisosDocumentales);
        $this->conteoLaborales=count($cambiosLaborales);
        $this->conteoAbiertas=$this->conteoAlertas+$this->conteoRecordatorios+$this->conteoDocumentales+$this->conteoLaborales;

        return view('livewire.alertas.campana-notificaciones', [
            'alertas' => $alertas,
            'recordatoriosMedicacion' => $this->recordatoriosMedicacion(),
            'puedeAtender' => $puedeAtender && $user->hasRole('ENFERMEROS'),
            'puedeConsultarAlertas' => $user->can('alertas.ver'),
            'puedeCerrar' => $puedeCerrar,
            'avisosDocumentales'=>$avisosDocumentales,
            'cambiosLaborales'=>$cambiosLaborales,
        ]);
    }

    private function consultaPendientes(): Builder
    {
        return app(BandejaAlertas::class)->pendientes(auth()->user())
            ->tap(fn ($query) => OrdenAlertas::aplicar($query));
    }

    private function autorizarAlerta(string $id): void
    {
        abort_unless(app(BandejaAlertas::class)
            ->consulta(auth()->user())->whereKey($id)->exists(), 403);
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

        $codigos = ! $user->hasAnyRole(['SUPERADMINISTRADOR', 'GERENTE', 'ADMINISTRADOR'])
            ? app(TurnoEnfermeriaService::class)->obtenerPacientesAsignadosIds($user)
            : Residente::query()->pluck('cod_residente')->all();

        return app(AgendaMedicacionService::class)->recordatorios($codigos);
    }
}
