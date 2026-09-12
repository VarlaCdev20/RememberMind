<?php

namespace App\Livewire\Medicacion;

use App\Models\AdultoMayor;
use App\Models\MedicacionAdulto;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use App\Services\Medicacion\AgendaMedicacionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class SaludMedicacionPanel extends Component
{
    use WithPagination;

    private const ESTADOS_ACTIVOS = ['ACTIVO', 'ACTIVA', 'VIGENTE'];
    private const ESTADOS_EN_CURSO = ['ACTIVO', 'ACTIVA', 'VIGENTE', 'PAUSADO', 'EN REVISION'];
    private const ESTADOS_RESIDENTE_NO_MODIFICABLES = [
        'ARCHIVADO',
        'EGRESADO',
        'FALLECIDO',
        'INACTIVO',
        'INACTIVA',
        'NO_ADMITIDO',
        'RETIRADO',
        'DERIVADO',
        'TRASLADADO',
    ];

    public ?AdultoMayor $adulto = null;
    public string $cod_am = '';

    public string $search = '';
    public string $filtroEstado = 'EN_CURSO';
    public string $filtroVia = '';

    public bool $drawerUbicacion = false;
    public ?AdultoMayor $adultoDrawer = null;

    protected $listeners = [
        'medicacion-actualizada' => 'refrescarPanel',
        'administracion-actualizada' => 'refrescarPanel',
    ];

    public function mount($adulto = null): void
    {
        $this->autorizarVista();

        if ($adulto === null || $adulto === '') {
            return;
        }

        $codAm = $adulto instanceof AdultoMayor
            ? $adulto->cod_am
            : (string) $adulto;

        $this->adulto = $this->resolverResidenteVisible($codAm);
        $this->cod_am = $this->adulto->cod_am;
    }

    public function refrescarPanel(): void
    {
        if ($this->cod_am !== '') {
            $this->adulto = $this->resolverResidenteVisible($this->cod_am);
        }

        $this->resetPage();
    }

    public function updatedCodAm($value): void
    {
        $this->autorizarVista();
        $this->cerrarDrawer();

        $codAm = trim((string) $value);

        if ($codAm === '') {
            $this->cod_am = '';
            $this->adulto = null;
            $this->resetPage();
            return;
        }

        $this->adulto = $this->resolverResidenteVisible($codAm);
        $this->cod_am = $this->adulto->cod_am;
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroVia(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->search = '';
        $this->filtroEstado = 'EN_CURSO';
        $this->filtroVia = '';
        $this->resetPage();
    }

    public function abrirNuevaPrescripcion(?string $codAm = null): void
    {
        $this->autorizarGestionOrden('medicacion.crear');

        $codigo = filled($codAm)
            ? trim((string) $codAm)
            : ($this->adulto?->cod_am ?? null);

        if ($codigo) {
            $adulto = $this->resolverResidenteVisible($codigo);
            $this->autorizarResidenteModificable($adulto);
            $codigo = $adulto->cod_am;
        }

        $this->dispatch('abrirModalMedicacion', cod_am: $codigo, id_med: null);
    }

    public function editarMedicacion(string $id): void
    {
        $this->autorizarGestionOrden('medicacion.editar');

        $medicacion = MedicacionAdulto::query()
            ->with('adultoMayor.estado')
            ->findOrFail($id);

        abort_unless($medicacion->adultoMayor, 404);

        $this->autorizarResidenteVisible($medicacion->adultoMayor);
        $this->autorizarResidenteModificable($medicacion->adultoMayor);

        abort_unless(
            in_array($medicacion->estado, self::ESTADOS_EN_CURSO, true),
            409,
            'Una orden suspendida, finalizada o archivada ya no puede editarse.'
        );

        $this->dispatch(
            'abrirModalMedicacion',
            cod_am: $medicacion->cod_am,
            id_med: $medicacion->cod_med_adulto
        );
    }

    public function suspenderMedicamento(string $id): void
    {
        $this->cambiarEstadoTerminal($id, 'SUSPENDIDO');
    }

    public function finalizarMedicamento(string $id): void
    {
        $this->cambiarEstadoTerminal($id, 'FINALIZADO');
    }

    public function verUbicacion(string $codAm): void
    {
        $this->autorizarVista();

        $adulto = $this->resolverResidenteVisible($codAm);

        $this->adultoDrawer = AdultoMayor::query()
            ->with([
                'habitacion',
                'cama',
                'alertas' => fn($query) => $query->latest()->take(5),
            ])
            ->findOrFail($adulto->cod_am);

        $this->drawerUbicacion = true;
    }

    public function cerrarDrawer(): void
    {
        $this->drawerUbicacion = false;
        $this->adultoDrawer = null;
    }

    public function render()
    {
        $this->autorizarVista();

        $adultosDisponibles = $this->residentesVisiblesQuery()
            ->with(['estado', 'habitacion', 'cama'])
            ->orderBy('nombres')
            ->orderBy('ap_paterno')
            ->get();

        $codigosVisibles = $adultosDisponibles->pluck('cod_am')->values()->all();

        /**
         * Si se abrió la vista directamente con un residente histórico,
         * se conserva su expediente aunque no aparezca en el selector operativo.
         */
        if ($this->adulto && !in_array($this->adulto->cod_am, $codigosVisibles, true)) {
            $codigosVisibles[] = $this->adulto->cod_am;
        }

        $base = MedicacionAdulto::query();

        if ($this->adulto) {
            $base->where('cod_am', $this->adulto->cod_am);
        } else {
            $base->whereIn('cod_am', $codigosVisibles);
        }

        $stats = [
            'activas' => (clone $base)->whereIn('estado', self::ESTADOS_ACTIVOS)->count(),
            'revision' => (clone $base)->whereIn('estado', ['PAUSADO', 'EN REVISION'])->count(),
            'suspendidas' => (clone $base)->where('estado', 'SUSPENDIDO')->count(),
            'finalizadas' => (clone $base)->whereIn('estado', ['FINALIZADO', 'ARCHIVADO'])->count(),
        ];

        $query = MedicacionAdulto::query()
            ->with([
                'registrador',
                'adultoMayor.habitacion',
                'adultoMayor.cama',
                'administraciones' => fn($q) => $q->whereDate('fecha', Carbon::today()),
            ]);

        if ($this->adulto) {
            $query->where('cod_am', $this->adulto->cod_am);
        } else {
            $query->whereIn('cod_am', $codigosVisibles);
        }

        $busqueda = trim($this->search);

        if ($busqueda !== '') {
            $query->where(function ($q) use ($busqueda) {
                $q->where('nombre_medicamento', 'like', "%{$busqueda}%")
                    ->orWhere('dosis', 'like', "%{$busqueda}%")
                    ->orWhere('frecuencia', 'like', "%{$busqueda}%")
                    ->orWhere('medico_indica', 'like', "%{$busqueda}%");
            });
        }

        if ($this->filtroEstado === 'EN_CURSO') {
            $query->whereIn('estado', self::ESTADOS_EN_CURSO);
        } elseif ($this->filtroEstado !== '' && $this->filtroEstado !== 'TODOS') {
            $query->where('estado', $this->filtroEstado);
        }

        if ($this->filtroVia !== '') {
            $query->where('via_administracion', $this->filtroVia);
        }

        $medicaciones = $query
            ->orderByRaw("
                CASE
                    WHEN estado IN ('ACTIVO', 'ACTIVA', 'VIGENTE') THEN 1
                    WHEN estado IN ('PAUSADO', 'EN REVISION') THEN 2
                    WHEN estado = 'SUSPENDIDO' THEN 3
                    WHEN estado = 'FINALIZADO' THEN 4
                    ELSE 5
                END
            ")
            ->latest('updated_at')
            ->paginate(10);

        $viasQuery = MedicacionAdulto::query()
            ->whereNotNull('via_administracion')
            ->where('via_administracion', '!=', '');

        if ($this->adulto) {
            $viasQuery->where('cod_am', $this->adulto->cod_am);
        } else {
            $viasQuery->whereIn('cod_am', $codigosVisibles);
        }

        $viasDisponibles = $viasQuery
            ->distinct()
            ->orderBy('via_administracion')
            ->pluck('via_administracion');

        $agenda = $this->adulto
            ? app(AgendaMedicacionService::class)->paraAdulto($this->adulto->cod_am)
            : collect();

        $agendaStats = [
            'pendientes' => $agenda->whereIn('estado', ['PENDIENTE', 'PROXIMA'])->count(),
            'vencidas' => $agenda->where('estado', 'VENCIDA')->count(),
            'administradas' => $agenda->where('estado', 'ADMINISTRADA')->count(),
            'omitidas' => $agenda->where('estado', 'OMITIDA')->count(),
        ];

        return view('livewire.medicacion.salud-medicacion', [
            'adultosDisponibles' => $adultosDisponibles,
            'adultos' => $adultosDisponibles,
            'medicaciones' => $medicaciones,
            'stats' => $stats,
            'viasDisponibles' => $viasDisponibles,
            'agenda' => $agenda,
            'agendaStats' => $agendaStats,
        ])->layout('layouts.sistema');
    }

    private function cambiarEstadoTerminal(string $id, string $nuevoEstado): void
    {
        abort_unless(
            in_array($nuevoEstado, ['SUSPENDIDO', 'FINALIZADO'], true),
            422,
            'El estado solicitado no es válido.'
        );

        $this->autorizarGestionOrden('medicacion.suspender');

        try {
            $medicacion = DB::transaction(function () use ($id, $nuevoEstado) {
                $medicacion = MedicacionAdulto::query()
                    ->with('adultoMayor.estado')
                    ->lockForUpdate()
                    ->findOrFail($id);

                abort_unless($medicacion->adultoMayor, 404);

                $this->autorizarResidenteVisible($medicacion->adultoMayor);
                $this->autorizarResidenteModificable($medicacion->adultoMayor);

                abort_unless(
                    in_array($medicacion->estado, self::ESTADOS_EN_CURSO, true),
                    409,
                    'Solo puede modificar una orden que todavía se encuentre en curso.'
                );

                if ($nuevoEstado === 'FINALIZADO') {
                    abort_if(
                        $medicacion->fecha_inicio && $medicacion->fecha_inicio->isFuture(),
                        409,
                        'No puede finalizar un tratamiento que todavía no ha iniciado. Puede suspenderlo si corresponde.'
                    );
                }

                $cambios = ['estado' => $nuevoEstado];

                if ($nuevoEstado === 'FINALIZADO') {
                    $cambios['fecha_fin'] = today()->toDateString();
                }

                $medicacion->update($cambios);

                return $medicacion->fresh();
            });

            $mensaje = $nuevoEstado === 'SUSPENDIDO'
                ? "La medicación '{$medicacion->nombre_medicamento}' fue suspendida."
                : "El tratamiento '{$medicacion->nombre_medicamento}' fue finalizado.";

            $this->dispatch('medicacion-actualizada');

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => $nuevoEstado === 'SUSPENDIDO' ? 'Medicación suspendida' : 'Tratamiento finalizado',
                'text' => $mensaje,
            ]);
        } catch (HttpExceptionInterface $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se pudo actualizar',
                'text' => 'Ocurrió un problema al actualizar el tratamiento. Intente nuevamente.',
            ]);
        }
    }

    private function autorizarVista(): void
    {
        $user = Auth::user();

        abort_unless($user, 401);

        abort_unless(
            $user->can('salud.ver') && $user->can('medicacion.ver'),
            403,
            'No cuenta con permiso para consultar la medicación.'
        );
    }

    private function autorizarGestionOrden(string $permiso): void
    {
        $user = Auth::user();

        abort_unless($user, 401);

        abort_unless(
            $user->hasAnyRole(['SUPERADMINISTRADOR', 'MEDICO GENERAL/GERIATRA']),
            403,
            'Las órdenes médicas solo pueden ser gestionadas por personal médico autorizado.'
        );

        abort_unless(
            $user->can('salud.ver')
                && $user->can('adultos.ver_expediente')
                && $user->can($permiso),
            403,
            'No cuenta con los permisos necesarios para esta acción.'
        );
    }

    private function resolverResidenteVisible(string $codAm): AdultoMayor
    {
        $adulto = AdultoMayor::query()
            ->with(['estado', 'habitacion', 'cama'])
            ->findOrFail($codAm);

        $this->autorizarResidenteVisible($adulto);

        return $adulto;
    }

    private function autorizarResidenteVisible(AdultoMayor $adulto): void
    {
        $this->autorizarVista();

        $user = Auth::user();

        if ($user?->hasRole('ENFERMEROS')) {
            app(TurnoEnfermeriaService::class)
                ->autorizarAccionPaciente($adulto, $user);
        }
    }

    private function autorizarResidenteModificable(AdultoMayor $adulto): void
    {
        abort_if(
            $adulto->archivado_en !== null,
            409,
            'El expediente del residente está archivado y solo puede consultarse.'
        );

        $estadoInstitucional = strtoupper(trim((string) ($adulto->estado?->estado ?? '')));
        $estadoOperativo = strtoupper(trim((string) ($adulto->estado_operativo ?? '')));

        abort_if(
            in_array($estadoInstitucional, self::ESTADOS_RESIDENTE_NO_MODIFICABLES, true)
                || in_array($estadoOperativo, self::ESTADOS_RESIDENTE_NO_MODIFICABLES, true),
            409,
            'El estado actual del residente no permite modificar órdenes médicas.'
        );
    }

    private function residentesVisiblesQuery()
    {
        $query = AdultoMayor::query()
            ->whereNull('archivado_en')
            ->whereHas('estado', function ($q) {
                $q->whereIn('estado', ['ACTIVO', 'ACTIVA']);
            });

        $user = Auth::user();

        if ($user?->hasRole('ENFERMEROS')) {
            $ids = app(TurnoEnfermeriaService::class)
                ->obtenerPacientesAsignadosIds($user);

            $query->whereIn('cod_am', $ids);
        }

        return $query;
    }
}
