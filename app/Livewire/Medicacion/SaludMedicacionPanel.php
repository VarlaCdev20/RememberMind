<?php

namespace App\Livewire\Medicacion;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AdultoMayor;
use App\Models\MedicacionAdulto;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use App\Services\Medicacion\AgendaMedicacionService;
use Carbon\Carbon;

class SaludMedicacionPanel extends Component
{
    use WithPagination;

    public ?AdultoMayor $adulto = null;
    public $cod_am = '';

    // Filtros de búsqueda para medicamentos
    public $search = '';
    public $filtroEstado = '';
    public $filtroVia = '';

    // Apartado para agregar medicamentos
    public bool $mostrarFormularioCrear = false;
    public bool $drawerUbicacion = false;
    public bool $drawerGrafico = false;
    public ?AdultoMayor $adultoDrawer = null;
    public $signosDrawer = [];
    public string $nuevo_cod_am = '';
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

    public function mount($adulto = null)
    {
        $this->autorizarVista();

        if ($adulto) {
            if ($adulto instanceof AdultoMayor) {
                $this->adulto = $adulto;
                $this->cod_am = $adulto->cod_am;
            } else {
                $this->adulto = AdultoMayor::find($adulto);
                $this->cod_am = $this->adulto?->cod_am ?? '';
            }

            abort_unless($this->adulto, 404);
            $this->autorizarResidente($this->adulto);
        }
    }

    public function toggleFormularioCrear(): void
    {
        $this->autorizarGestionOrden();
        $this->mostrarFormularioCrear = !$this->mostrarFormularioCrear;
        if ($this->mostrarFormularioCrear) {
            $this->nuevo_cod_am = $this->adulto ? $this->adulto->cod_am : '';
            $this->nuevo_fecha_inicio = now()->toDateString();
            $this->nuevo_hora = '08:00';
            $this->nuevo_medico = '';
            $this->resetValidation();
        }
    }

    public function abrirFormularioPara(string $codAm): void
    {
        $this->autorizarCreacion();
        $this->cod_am = $codAm;
        $this->adulto = AdultoMayor::findOrFail($codAm);
        $this->autorizarResidente($this->adulto);
        $this->mostrarFormularioCrear = true;
        $this->nuevo_cod_am = $codAm;
        $this->nuevo_fecha_inicio = now()->toDateString();
        $this->nuevo_hora = '08:00';
        $this->nuevo_medico = '';
        $this->resetValidation();
    }

    public function cancelarCreacion(): void
    {
        $this->mostrarFormularioCrear = false;
        $this->resetValidation();
    }

    public function guardarNuevoMedicamento(): void
    {
        $this->autorizarCreacion();

        $this->validate([
            'nuevo_cod_am' => 'required|exists:adulto_mayor,cod_am',
            'nuevo_nombre' => 'required|string|min:2|max:100',
            'nuevo_dosis' => 'required|string|max:50',
            'nuevo_frecuencia' => 'required|string|max:50',
            'nuevo_via' => 'required|string|max:50',
            'nuevo_hora' => 'required|date_format:H:i',
            'nuevo_fecha_inicio' => 'required|date',
            'nuevo_fecha_fin' => 'nullable|date|after_or_equal:nuevo_fecha_inicio',
            'nuevo_medico' => 'nullable|string|max:100',
            'nuevo_observacion' => 'nullable|string|max:255',
        ], [
            'nuevo_cod_am.required' => 'Debe seleccionar un residente.',
            'nuevo_nombre.required' => 'El nombre del medicamento es obligatorio.',
            'nuevo_dosis.required' => 'La dosis es obligatoria (ej: 50mg, 1 comprimido).',
            'nuevo_frecuencia.required' => 'La frecuencia de administración es obligatoria.',
            'nuevo_via.required' => 'La vía de administración es obligatoria.',
            'nuevo_hora.required' => 'La hora programada es obligatoria.',
            'nuevo_hora.date_format' => 'La hora programada debe tener el formato HH:MM.',
            'nuevo_fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'nuevo_fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
        ]);

        $this->autorizarResidente(AdultoMayor::findOrFail($this->nuevo_cod_am));

        $med = MedicacionAdulto::create([
            'cod_am' => $this->nuevo_cod_am,
            'nombre_medicamento' => $this->nuevo_nombre,
            'dosis' => $this->nuevo_dosis,
            'frecuencia' => $this->nuevo_frecuencia,
            'via_administracion' => $this->nuevo_via,
            'hora_programada' => $this->nuevo_hora,
            'fecha_inicio' => $this->nuevo_fecha_inicio,
            'fecha_fin' => $this->nuevo_fecha_fin,
            'medico_indica' => filled($this->nuevo_medico) ? trim($this->nuevo_medico) : null,
            'estado' => 'ACTIVA',
            'observacion' => $this->nuevo_observacion,
            'registrado_por' => auth()->id(),
        ]);

        if (!$this->adulto || $this->adulto->cod_am !== $this->nuevo_cod_am) {
            $this->cod_am = $this->nuevo_cod_am;
            $this->adulto = AdultoMayor::find($this->nuevo_cod_am);
        }

        $this->reset([
            'nuevo_nombre', 'nuevo_dosis', 'nuevo_observacion', 'nuevo_fecha_fin'
        ]);
        $this->mostrarFormularioCrear = false;

        session()->flash('mensaje_exito', 'Medicamento '.$med->nombre_medicamento.' prescrito correctamente. La toma quedó programada para las '.$this->nuevo_hora.'.');
        $this->dispatch('medicacion-actualizada');
    }

    public function updatedCodAm($value)
    {
        if ($value) {
            $this->adulto = AdultoMayor::where('cod_am', $value)->firstOrFail();
            $this->autorizarResidente($this->adulto);
            $this->resetPage();
        } else {
            $this->adulto = null;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }

    public function updatingFiltroVia()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'filtroEstado', 'filtroVia']);
        $this->resetPage();
    }

    public function suspenderMedicamento($id): void
    {
        $this->autorizarGestionOrden();
        $medicacion = MedicacionAdulto::findOrFail($id);
        $this->autorizarResidente($medicacion->adultoMayor);
        abort_unless(in_array($medicacion->estado, ['ACTIVO', 'ACTIVA'], true), 409, 'Solo puede suspender una orden activa.');
        $medicacion->update(['estado' => 'SUSPENDIDO']);

        activity('Medicación')
            ->causedBy(auth()->user())
            ->performedOn($medicacion)
            ->event('suspended')
            ->withProperties(['nombre_medicamento' => $medicacion->nombre_medicamento])
            ->log("Se suspendió la medicación '{$medicacion->nombre_medicamento}' del adulto mayor {$medicacion->cod_am}.");

        session()->flash('mensaje_exito', "Medicación '{$medicacion->nombre_medicamento}' suspendida correctamente.");
        $this->dispatch('medicacion-actualizada');
    }

    public function finalizarMedicamento($id): void
    {
        $this->autorizarGestionOrden();
        $medicacion = MedicacionAdulto::findOrFail($id);
        $this->autorizarResidente($medicacion->adultoMayor);
        abort_unless(in_array($medicacion->estado, ['ACTIVO', 'ACTIVA'], true), 409, 'Solo puede finalizar una orden activa.');
        $medicacion->update([
            'estado' => 'FINALIZADO',
            'fecha_fin' => now()->toDateString(),
        ]);

        activity('Medicación')
            ->causedBy(auth()->user())
            ->performedOn($medicacion)
            ->event('finalized')
            ->withProperties(['nombre_medicamento' => $medicacion->nombre_medicamento])
            ->log("Se finalizó el tratamiento de '{$medicacion->nombre_medicamento}' del adulto mayor {$medicacion->cod_am}.");

        session()->flash('mensaje_exito', "Tratamiento de '{$medicacion->nombre_medicamento}' finalizado con éxito.");
        $this->dispatch('medicacion-actualizada');
    }

    public function verUbicacion(string $codAm): void
    {
        $this->adultoDrawer = AdultoMayor::with([
            'habitacion',
            'cama',
            'alertas' => fn ($q) => $q->latest()->take(5),
        ])->find($codAm);

        $this->drawerUbicacion = true;
        $this->drawerGrafico = false;
    }

    public function verGraficos(string $codAm): void
    {
        $this->adultoDrawer = AdultoMayor::with([
            'habitacion',
            'cama',
            'signosVitales' => fn ($q) => $q->where('estado', '!=', 'ANULADO')->latest('fecha')->latest('hora')->take(10),
        ])->find($codAm);

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
        $adultosQuery = AdultoMayor::whereHas('estado', function ($q) {
            $q->whereIn('estado', ['ACTIVO', 'ACTIVA']);
        });

        if (auth()->user()?->hasRole('ENFERMEROS')) {
            $adultosQuery->whereIn('cod_am', app(TurnoEnfermeriaService::class)->obtenerPacientesAsignadosIds(auth()->user()));
        }

        $adultosDisponibles = $adultosQuery->orderBy('nombres')->get();

        $stats = [
            'activas' => 0,
            'suspendidas' => 0,
            'finalizadas' => 0,
            'archivadas' => 0,
        ];
        
        $medicaciones = collect();
        $viasDisponibles = collect();
        $agenda = collect();

        if ($this->adulto) {
            $allMedicaciones = MedicacionAdulto::where('cod_am', $this->adulto->cod_am)->get();
            $stats = [
                'activas' => $allMedicaciones->whereIn('estado', ['ACTIVO', 'ACTIVA'])->count(),
                'suspendidas' => $allMedicaciones->where('estado', 'SUSPENDIDO')->count(),
                'finalizadas' => $allMedicaciones->where('estado', 'FINALIZADO')->count(),
                'archivadas' => $allMedicaciones->where('estado', 'ARCHIVADO')->count(),
            ];

            $query = MedicacionAdulto::with(['registrador', 'administraciones' => function($q) {
                $q->whereDate('fecha', Carbon::today());
            }])->where('cod_am', $this->adulto->cod_am);

            if (!empty($this->search)) {
                $query->where('nombre_medicamento', 'like', '%' . $this->search . '%');
            }

            if (!empty($this->filtroEstado)) {
                $query->where('estado', $this->filtroEstado);
            }

            if (!empty($this->filtroVia)) {
                $query->where('via_administracion', $this->filtroVia);
            }

            $medicaciones = $query->latest()->paginate(10);

            $viasDisponibles = MedicacionAdulto::where('cod_am', $this->adulto->cod_am)
                ->whereNotNull('via_administracion')
                ->select('via_administracion')
                ->distinct()
                ->pluck('via_administracion');
            $agenda = app(AgendaMedicacionService::class)->paraAdulto($this->adulto->cod_am);
        } else {
            $allGlobal = MedicacionAdulto::all();
            $stats = [
                'activas' => $allGlobal->whereIn('estado', ['ACTIVO', 'ACTIVA'])->count(),
                'suspendidas' => $allGlobal->where('estado', 'SUSPENDIDO')->count(),
                'finalizadas' => $allGlobal->where('estado', 'FINALIZADO')->count(),
                'archivadas' => $allGlobal->where('estado', 'ARCHIVADO')->count(),
            ];
            
            $query = MedicacionAdulto::with(['registrador', 'adultoMayor']);
            
            if (!empty($this->search)) {
                $query->where('nombre_medicamento', 'like', '%' . $this->search . '%');
            }
            if (!empty($this->filtroEstado)) {
                $query->where('estado', $this->filtroEstado);
            } else {
                $query->whereIn('estado', ['ACTIVO', 'ACTIVA']);
            }
            if (!empty($this->filtroVia)) {
                $query->where('via_administracion', $this->filtroVia);
            }
            
            $medicaciones = $query->latest()->paginate(10);
            
            $viasDisponibles = MedicacionAdulto::whereNotNull('via_administracion')
                ->select('via_administracion')
                ->distinct()
                ->pluck('via_administracion');
        }

        return view('livewire.medicacion.salud-medicacion', [
            'adultosDisponibles' => $adultosDisponibles,
            'adultos' => $adultosDisponibles,
            'medicaciones' => $medicaciones,
            'stats' => $stats,
            'viasDisponibles' => $viasDisponibles,
            'agenda' => $agenda,
        ])->layout('layouts.sistema');
    }

    private function autorizarVista(): void
    {
        $user = auth()->user();
        abort_unless($user, 401);
        abort_unless($user->hasRole('SUPERADMINISTRADOR') || $user->canAny([
            'medicacion.ver', 'salud.medicacion.ver', 'medicacion.crear', 'salud.medicacion.crear',
        ]), 403);
    }

    private function autorizarCreacion(): void
    {
        $this->autorizarGestionOrden();
    }

    private function autorizarGestionOrden(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['SUPERADMINISTRADOR', 'MEDICO GENERAL/GERIATRA']), 403,
            'Las prescripciones solo pueden ser creadas o modificadas por personal médico autorizado.');
    }

    private function autorizarResidente(AdultoMayor $adulto): void
    {
        if (auth()->user()?->hasRole('ENFERMEROS')) {
            app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($adulto, auth()->user());
        }
    }
}
