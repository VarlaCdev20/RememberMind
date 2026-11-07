<?php

namespace App\Modulos\Alertas\Presentacion\Livewire\Funcionalidades;

use App\Models\Alerta;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\Alertas\Aplicacion\Consultas\ConsultarDerivacionesRecibidas;
use App\Modulos\Identidad\Aplicacion\Consultas\HistorialCorreoDocumental;
use App\Modulos\Social\Aplicacion\Consultas\ConsultarAvisosVisitasFamiliar;
use App\Modulos\Alertas\Aplicacion\Acciones\GestionarAtencionAlerta;
use App\Modulos\Alertas\Aplicacion\Consultas\BandejaAlertas;
use App\Modulos\Alertas\Aplicacion\Consultas\ConsultarPanelResidenteAlerta;
use App\Modulos\Alertas\Aplicacion\Consultas\OrdenAlertas;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use App\Modulos\Institucion\Aplicacion\Consultas\ConsultarAvisosPlanificacion;
use App\Modulos\Medicacion\Aplicacion\Consultas\AgendaMedicacionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
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
    public array $avisosConocidos = [];

    private bool $previsualizacion = false;

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

    public function puedeVer(): bool
    {
        $user = auth()->user();

        if (! $user || $user->fresh()?->estado !== 'ACTIVO') {
            return false;
        }
        if ($user->hasRole('FAMILIAR')) {
            return $user->can('visitas.ver');
        }
        return $user->canAny(['alertas.ver', 'alertas.gestionar', 'prescripciones.ver', 'administraciones_medicacion.ver', 'derivaciones.ver'])
            || ($user->hasRole('SUPERADMINISTRADOR') && $user->can('usuarios.ver'))
            || (app(\App\Modulos\Identidad\Aplicacion\Consultas\CapacidadDocumentalPersonal::class)->disponible()
                && ($user->personal
                    || ($user->hasRole('ADMINISTRADOR') && $user->can('asignaciones_personal.ver'))
                    || app(\App\Modulos\Identidad\Aplicacion\Consultas\ConsultarDocumentacionPersonal::class)->esGestor($user)))
            || $this->consultaPlanificacion($user) !== null;
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
    #[On('avisos-planificacion-actualizados')]
    public function verificarAlertas(): void
    {
        // Livewire vuelve a consultar y sincronizar las fuentes al renderizar.
    }

    private function sincronizarAvisos(string $fuente, Collection $avisos, callable $clave, callable $presentar): void
    {
        if ($this->previsualizacion) {
            return;
        }

        $actuales = $avisos->toBase()->keyBy(fn ($aviso) => $fuente.':'.hash('sha256', (string) $clave($aviso)));
        $vigentes = $actuales->keys()->all();
        $anteriores = $this->avisosConocidos[$fuente] ?? $vigentes;
        $retirados = array_values(array_diff($anteriores, $vigentes));
        if ($retirados) {
            $this->dispatch('avisos-retirados', ids: $retirados);
        }
        $this->avisosConocidos[$fuente] = $vigentes;

        $nuevos = $actuales->except($anteriores);
        foreach ($nuevos as $id => $aviso) {
            $this->dispatch('alerta-nueva', ['id' => $id, ...$presentar($aviso)]);
        }
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
            $this->conteoAbiertas = 0;
            $this->conteoAlertas = 0;
            $this->conteoRecordatorios = 0;
            $this->conteoDocumentales = 0;
            $this->conteoLaborales = 0;
            $this->avisosConocidos = [];

            return '<div></div>';
        }

        $actor = auth()->user();
        $this->previsualizacion = app(RolePreviewService::class)->isActive($actor);
        $previsualizacion = $this->previsualizacion;
        if ($actor->hasRole('FAMILIAR')) {
            $visitas = app(ConsultarAvisosVisitasFamiliar::class)->consultar($actor);
            $this->conteoAbiertas = $visitas->count();
            $this->sincronizarAvisos('visitas', $visitas,
                fn ($v) => $v->cod_visita.'|'.$v->estado.'|'.$v->fecha_hora_programada,
                fn ($v) => [
                    'categoria' => 'Visitas', 'icono' => 'ph-calendar-check', 'nivel' => 'INFORMATIVO',
                    'titulo' => $v->estado === 'AUTORIZADA' ? 'Visita autorizada' : 'Visita programada',
                    'mensaje' => Carbon::parse($v->fecha_hora_programada)->format('d/m/Y · H:i'),
                    'accion' => 'Ver próximas visitas', 'seccion' => 'visitas',
                ]);

            return view('livewire.alertas.campana-notificaciones', [
                'visitasFamiliares' => $visitas->take(10),
                'previsualizacion' => $previsualizacion,
            ]);
        }

        $derivaciones = app(ConsultarDerivacionesRecibidas::class)->consultar($actor);
        $fallosCorreo = $actor->hasRole('SUPERADMINISTRADOR') && $actor->can('usuarios.ver')
            && ! $previsualizacion
            ? app(HistorialCorreoDocumental::class)->ultimos($actor, ['estado' => 'FALLIDO'])->pluck('id') : collect();

        $alertas = $this->consultaPendientes()->with('residente')->get();
        $recordatorios = $this->recordatoriosMedicacion();
        $this->conteoAlertas = $alertas->count();
        $this->conteoRecordatorios = $recordatorios->count();

        $rutaAlertas = $actor->hasRole('ENFERMEROS') ? 'admin.enfermeria.alertas' : 'admin.alertas-clinicas.index';
        $this->sincronizarAvisos('alertas', $alertas, fn ($a) => $a->getKey().'|'.$a->prioridad, fn ($a) => [
            'categoria' => 'Alertas', 'icono' => 'ph-warning', 'nivel' => $a->prioridad,
            'titulo' => $a->tipo, 'mensaje' => $a->prioridad.' · '.$a->residente?->nombre_completo,
            'accion' => 'Ver alerta', 'url' => route($rutaAlertas, ['alerta' => $a->getKey()]),
        ]);
        $this->sincronizarAvisos('medicacion', $recordatorios, fn ($r) => $r['id'].'|'.$r['estado'], fn ($r) => [
            'categoria' => 'Medicación', 'icono' => 'ph-clock', 'nivel' => $r['estado'],
            'titulo' => $r['estado'] === 'VENCIDA' ? 'Dosis vencida' : 'Próxima dosis',
            'mensaje' => $r['adulto']?->nombre_completo.' · '.$r['medicacion']->nombre_medicamento.' · '.$r['programada']->format('H:i'),
            'accion' => 'Ver administración',
            'url' => route('admin.enfermeria.medicacion', ['adulto' => $r['medicacion']->cod_residente]),
        ]);
        $this->sincronizarAvisos('derivaciones', $derivaciones, fn ($d) => $d->getKey().'|'.$d->prioridad, fn ($d) => [
            'categoria' => 'Derivaciones', 'icono' => 'ph-arrows-left-right', 'nivel' => $d->prioridad,
            'titulo' => 'Derivación recibida', 'mensaje' => 'Prioridad '.mb_strtolower($d->prioridad),
            'accion' => 'Ver derivaciones', 'seccion' => 'derivaciones',
        ]);
        $this->sincronizarAvisos('correo', $fallosCorreo, fn ($id) => $id, fn () => [
            'categoria' => 'Correo documental', 'icono' => 'ph-envelope-simple', 'nivel' => 'REVISION',
            'titulo' => 'Envío documental fallido', 'mensaje' => 'Consulta el estado del envío en tu historial.',
            'accion' => 'Ver historial de correo', 'url' => route('admin.usuarios.correos.index'),
        ]);

        $user = auth()->user();
        $puedeAtender = (bool) $user?->canAny(['alertas.gestionar', 'alertas.seguimiento']);
        $puedeCerrar = (bool) $user?->canAny(['alertas.gestionar', 'alertas.cerrar']);
        $avisosDocumentales=app(\App\Modulos\Identidad\Aplicacion\Consultas\ConsultarDocumentacionPersonal::class)->avisos($user);
        $cambiosLaborales=app(\App\Modulos\Identidad\Aplicacion\Consultas\ConsultarCambiosLaboralesPendientes::class)->consultar($user);
        $this->sincronizarAvisos('documentacion', collect($avisosDocumentales),
            fn ($a) => $a['clave'].'|'.$a['mensaje'].'|'.$a['fecha'],
            fn ($a) => [
                'categoria' => 'Documentación', 'icono' => 'ph-file-text', 'nivel' => 'REVISION',
                'titulo' => $a['titulo'],
                'mensaje' => implode(' · ', array_filter([$a['persona'], $a['mensaje'], $a['fecha']])),
                'accion' => 'Ver documentación', 'url' => $a['url'],
            ]);
        $this->sincronizarAvisos('laboral', collect($cambiosLaborales), fn ($a) => $a['clave'], fn ($a) => [
            'categoria' => 'Personal', 'icono' => 'ph-identification-card', 'nivel' => 'REVISION',
            'titulo' => 'Cambio laboral para revisar', 'mensaje' => $a['persona'].' · '.$a['mensaje'],
            'accion' => 'Ver asignaciones', 'url' => $a['url'],
        ]);
        $consultaPlanificacion = $this->consultaPlanificacion($user);
        $eventosPlanificacion = $consultaPlanificacion?->consulta($user)->get() ?? collect();
        $totalPlanificacion = $eventosPlanificacion->count();
        $avisosPlanificacion = $eventosPlanificacion->take(5)->map(fn ($e) => $consultaPlanificacion->presentar($e))->all();
        $this->sincronizarAvisos('planificacion', $eventosPlanificacion,
            fn ($e) => $e->getKey().'|'.$e->estado_aviso_planificacion,
            function ($e) use ($consultaPlanificacion) {
                $aviso = $consultaPlanificacion->presentar($e);

                return [
                    'categoria' => 'Planificación', 'icono' => 'ph-calendar', 'nivel' => $aviso['estado'] === 'DISPONIBLE' ? 'INFORMATIVO' : 'REVISION',
                    'titulo' => $aviso['titulo'], 'mensaje' => $aviso['contexto'].' · '.$aviso['estado_texto'],
                    'accion' => 'Ver planificación', 'url' => route('admin.avisos-planificacion.index'),
                ];
            });
        $this->conteoDocumentales = count($avisosDocumentales);
        $this->conteoLaborales = count($cambiosLaborales);
        $this->conteoAbiertas = $this->conteoAlertas + $this->conteoRecordatorios + $this->conteoDocumentales
            + $this->conteoLaborales + $totalPlanificacion + $derivaciones->count() + $fallosCorreo->count();

        return view('livewire.alertas.campana-notificaciones', [
            'alertas' => $alertas->take(15),
            'derivacionesRecibidas' => $derivaciones->take(10),
            'fallosCorreo' => $fallosCorreo->count(),
            'recordatoriosMedicacion' => $recordatorios,
            'puedeAtender' => $puedeAtender && $user->hasRole('ENFERMEROS'),
            'puedeConsultarAlertas' => $user->can('alertas.ver'),
            'puedeConsultarMedicacion' => $this->puedeConsultarMedicacion(),
            'puedeCerrar' => $puedeCerrar,
            'avisosDocumentales'=>$avisosDocumentales,
            'cambiosLaborales'=>$cambiosLaborales,
            'avisosPlanificacion'=>$avisosPlanificacion,
            'totalPlanificacion'=>$totalPlanificacion,
            'previsualizacion' => $previsualizacion,
        ]);
    }

    private function consultaPlanificacion(User $actor): ?ConsultarAvisosPlanificacion
    {
        if (! class_exists(ConsultarAvisosPlanificacion::class) || ! Route::has('admin.avisos-planificacion.index')) {
            return null;
        }

        $consulta = app(ConsultarAvisosPlanificacion::class);

        return $consulta->disponible() && $consulta->puedeConsultar($actor) ? $consulta : null;
    }

    private function consultaPendientes(): Builder
    {
        return app(BandejaAlertas::class)->notificacionesPendientes(auth()->user())
            ->tap(fn ($query) => OrdenAlertas::aplicar($query));
    }

    private function autorizarAlerta(string $id): void
    {
        abort_unless(app(BandejaAlertas::class)
            ->consulta(auth()->user())->whereKey($id)->exists(), 403);
    }

    private function recordatoriosMedicacion()
    {
        if (! $this->puedeConsultarMedicacion() || ! Schema::hasTable('prescripciones')) {
            return collect();
        }

        $codigos = app(TurnoEnfermeriaService::class)->obtenerAsignacionesConsulta(auth()->user())
            ->distinct()->pluck('cod_residente')->all();

        return app(AgendaMedicacionService::class)->recordatorios($codigos);
    }

    private function puedeConsultarMedicacion(): bool
    {
        $user = auth()->user();

        return $user->hasRole('ENFERMEROS') && $user->can('administraciones_medicacion.ver')
            && app(\App\Modulos\Alertas\Aplicacion\Consultas\ExperienciaAlertas::class)->paraUsuario($user)['foco'] === 'turno';
    }
}
