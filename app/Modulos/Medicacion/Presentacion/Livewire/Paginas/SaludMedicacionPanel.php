<?php

namespace App\Modulos\Medicacion\Presentacion\Livewire\Paginas;

use App\Models\Prescripcion;
use App\Models\Residente;
use App\Modulos\Clinica\Aplicacion\Acciones\AccesoClinicoTemporalService;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use App\Modulos\Medicacion\Aplicacion\Acciones\GestionarPrescripciones;
use App\Modulos\Medicacion\Aplicacion\Consultas\AgendaMedicacionService;
use Livewire\Component;
use Livewire\WithPagination;

class SaludMedicacionPanel extends Component
{
    use WithPagination;

    public ?Residente $adulto = null;

    public string $cod_residente = '';

    public string $search = '';

    public string $filtroEstado = '';

    public string $filtroVia = '';

    public bool $mostrarFormularioCrear = false;

    public bool $drawerUbicacion = false;

    public bool $drawerGrafico = false;

    public ?Residente $adultoDrawer = null;

    public mixed $signosDrawer = [];

    public string $nuevo_cod_residente = '';

    public string $nuevo_nombre = '';

    public string $nuevo_dosis = '';

    public string $nuevo_frecuencia = 'CADA 12 HORAS';

    public string $nuevo_via = 'ORAL';

    public string $nuevo_hora = '08:00';

    public string $nuevo_fecha_inicio = '';

    public ?string $nuevo_fecha_fin = null;

    public string $nuevo_medico = '';

    public string $nuevo_observacion = '';

    protected $listeners = [
        'medicacion-actualizada' => '$refresh',
        'administracion-actualizada' => '$refresh',
    ];

    public function mount($adulto = null): void
    {
        $this->autorizarVista();
        if (! $adulto) {
            return;
        }

        $this->adulto = $adulto instanceof Residente ? $adulto : Residente::query()->find($adulto);
        abort_unless($this->adulto, 404);
        $this->cod_residente = $this->adulto->cod_residente;
        $this->autorizarResidente($this->adulto);
    }

    public function toggleFormularioCrear(): void
    {
        $this->autorizarGestionOrden();
        $this->mostrarFormularioCrear = ! $this->mostrarFormularioCrear;
        if ($this->mostrarFormularioCrear) {
            $this->nuevo_cod_residente = $this->adulto?->cod_residente ?? '';
            $this->nuevo_fecha_inicio = now()->toDateString();
            $this->nuevo_hora = '08:00';
            $this->nuevo_medico = '';
            $this->resetValidation();
        }
    }

    public function abrirFormularioPara(string $codResidente): void
    {
        $this->autorizarGestionOrden();
        $this->adulto = Residente::query()->findOrFail($codResidente);
        $this->autorizarResidente($this->adulto);
        $this->cod_residente = $codResidente;
        $this->nuevo_cod_residente = $codResidente;
        $this->nuevo_fecha_inicio = now()->toDateString();
        $this->nuevo_hora = '08:00';
        $this->nuevo_medico = '';
        $this->mostrarFormularioCrear = true;
        $this->resetValidation();
    }

    public function cancelarCreacion(): void
    {
        $this->mostrarFormularioCrear = false;
        $this->resetValidation();
    }

    public function guardarNuevoMedicamento(): void
    {
        $this->autorizarGestionOrden();
        $this->validate([
            'nuevo_cod_residente' => 'required|exists:residentes,cod_residente',
            'nuevo_nombre' => 'required|string|min:2|max:120',
            'nuevo_dosis' => 'required|string|max:60',
            'nuevo_frecuencia' => 'required|string|max:80',
            'nuevo_via' => 'required|string|max:60',
            'nuevo_hora' => 'required|date_format:H:i',
            'nuevo_fecha_inicio' => 'required|date',
            'nuevo_fecha_fin' => 'nullable|date|after_or_equal:nuevo_fecha_inicio',
            'nuevo_observacion' => 'nullable|string|max:1000',
        ]);

        $prescripcion = app(GestionarPrescripciones::class)->guardarDesdePanel(auth()->user(), [
            'nuevo_cod_residente' => $this->nuevo_cod_residente,
            'nuevo_dosis' => $this->nuevo_dosis,
            'nuevo_nombre' => $this->nuevo_nombre,
            'nuevo_via' => $this->nuevo_via,
            'nuevo_observacion' => $this->nuevo_observacion,
            'nuevo_fecha_fin' => $this->nuevo_fecha_fin,
            'nuevo_frecuencia' => $this->nuevo_frecuencia,
            'nuevo_fecha_inicio' => $this->nuevo_fecha_inicio,
            'nuevo_hora' => $this->nuevo_hora,
        ]);
        $residente = $prescripcion->residente;

        $this->adulto = $residente;
        $this->cod_residente = $residente->cod_residente;
        $this->reset(['nuevo_nombre', 'nuevo_dosis', 'nuevo_observacion', 'nuevo_fecha_fin']);
        $this->mostrarFormularioCrear = false;
        session()->flash('mensaje_exito', 'Medicamento '.$prescripcion->nombre_medicamento.' prescrito correctamente.');
        $this->dispatch('medicacion-actualizada');
    }

    public function updatedCodResidente($value): void
    {
        $this->adulto = $value ? Residente::query()->findOrFail($value) : null;
        if ($this->adulto) {
            $this->autorizarResidente($this->adulto);
        }
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
        $this->reset(['search', 'filtroEstado', 'filtroVia']);
        $this->resetPage();
    }

    public function suspenderMedicamento(string $id): void
    {
        $this->autorizarGestionOrden();
        $prescripcion = app(GestionarPrescripciones::class)->suspenderMedicamento(auth()->user(), $id);
        session()->flash('mensaje_exito', "Medicación '{$prescripcion->nombre_medicamento}' suspendida correctamente.");
        $this->dispatch('medicacion-actualizada');
    }

    public function finalizarMedicamento(string $id): void
    {
        $this->autorizarGestionOrden();
        $prescripcion = app(GestionarPrescripciones::class)->finalizarMedicamento(auth()->user(), $id);
        session()->flash('mensaje_exito', "Tratamiento de '{$prescripcion->nombre_medicamento}' finalizado con éxito.");
        $this->dispatch('medicacion-actualizada');
    }

    public function verUbicacion(string $codResidente): void
    {
        $this->adultoDrawer = Residente::query()->with(['ocupacionActiva.cama.habitacion', 'alertas'])->find($codResidente);
        $this->drawerUbicacion = true;
        $this->drawerGrafico = false;
    }

    public function verGraficos(string $codResidente): void
    {
        $this->adultoDrawer = Residente::query()->with([
            'ocupacionActiva.cama.habitacion',
            'alergias' => fn ($query) => $query->whereIn('estado', ['ACTIVA', 'ACTIVO']),
            'diagnosticos' => fn ($query) => $query->whereIn('estado', ['ACTIVO', 'CONFIRMADO']),
            'planCuidadoActivo',
            'signosVitales' => fn ($query) => $query->where('estado', '!=', 'ANULADO')->latest('fecha_hora')->take(10),
        ])->find($codResidente);
        $this->signosDrawer = $this->adultoDrawer?->signosVitales ?? collect();
        $this->drawerGrafico = true;
        $this->drawerUbicacion = false;
    }

    public function cerrarDrawer(): void
    {
        $this->drawerUbicacion = false;
        $this->drawerGrafico = false;
        $this->adultoDrawer = null;
        $this->signosDrawer = [];
    }

    public function render()
    {
        $adultosQuery = Residente::query()->where('estado', 'ADMITIDO');
        if (auth()->user()?->hasRole('ENFERMEROS')) {
            $adultosQuery->whereIn('cod_residente', app(TurnoEnfermeriaService::class)->obtenerPacientesAsignadosIds(auth()->user()));
        }
        $adultosDisponibles = $adultosQuery->orderBy('nombres')->get();

        $base = Prescripcion::query()->with(['medicamento', 'adultoMayor.ocupacionActiva.cama.habitacion', 'personal', 'horarios',
            'administraciones' => fn ($query) => $query->whereDate('fecha_hora_programada', today()),
        ]);
        if ($this->adulto) {
            $base->where('cod_residente', $this->adulto->cod_residente);
        }
        $todas = (clone $base)->get();
        $stats = [
            'activas' => $todas->where('estado', 'ACTIVA')->count(),
            'suspendidas' => $todas->where('estado', 'SUSPENDIDA')->count(),
            'finalizadas' => $todas->where('estado', 'FINALIZADA')->count(),
            'archivadas' => $todas->where('estado', 'ARCHIVADA')->count(),
        ];

        $query = clone $base;
        $query->when($this->search, fn ($q) => $q->whereHas('medicamento', fn ($medicamento) => $medicamento
            ->whereLike('nombre_generico', '%'.$this->search.'%')
            ->orWhereLike('nombre_comercial', '%'.$this->search.'%')))
            ->when($this->filtroEstado, fn ($q) => $q->where('estado', $this->estadoV2($this->filtroEstado)))
            ->when(! $this->filtroEstado && ! $this->adulto, fn ($q) => $q->where('estado', 'ACTIVA'))
            ->when($this->filtroVia, fn ($q) => $q->where('via_administracion', $this->filtroVia));

        return view('livewire.medicacion.salud-medicacion', [
            'adultosDisponibles' => $adultosDisponibles,
            'adultos' => $adultosDisponibles,
            'medicaciones' => $query->latest('fecha_hora_prescripcion')->paginate(10),
            'stats' => $stats,
            'viasDisponibles' => (clone $base)->whereNotNull('via_administracion')->distinct()->pluck('via_administracion'),
            'agenda' => $this->adulto ? app(AgendaMedicacionService::class)->paraAdulto($this->adulto->cod_residente) : collect(),
        ])->layout('layouts.sistema');
    }

    private function autorizarVista(): void
    {
        abort_unless(auth()->user()?->can('prescripciones.ver'), 403);
    }

    private function autorizarGestionOrden(): void
    {
        abort_unless(app(AccesoClinicoTemporalService::class)
            ->tieneRol(auth()->user(), ['MEDICO GENERAL/GERIATRA'])
            && auth()->user()?->can('prescripciones.crear'), 403,
            'Las prescripciones solo pueden ser creadas o modificadas por personal médico autorizado.');
    }

    private function autorizarResidente(Residente $adulto): void
    {
        if (auth()->user()?->hasRole('ENFERMEROS')) {
            app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($adulto, auth()->user());
        }
    }

    private function estadoV2(string $estado): string
    {
        return match ($estado) {
            'ACTIVO' => 'ACTIVA',
            'SUSPENDIDO' => 'SUSPENDIDA',
            'FINALIZADO' => 'FINALIZADA',
            'ARCHIVADO' => 'ARCHIVADA',
            default => $estado,
        };
    }
}
