<?php

namespace App\Livewire\Medicacion;

use App\Models\AdultoMayor;
use App\Models\Atencion;
use App\Models\HorarioPrescripcion;
use App\Models\Medicamento;
use App\Models\Prescripcion;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use App\Services\Medicacion\AgendaMedicacionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class SaludMedicacionPanel extends Component
{
    use WithPagination;

    public ?AdultoMayor $adulto = null;
    public string $cod_am = '';
    public string $search = '';
    public string $filtroEstado = '';
    public string $filtroVia = '';
    public bool $mostrarFormularioCrear = false;
    public bool $drawerUbicacion = false;
    public bool $drawerGrafico = false;
    public ?AdultoMayor $adultoDrawer = null;
    public mixed $signosDrawer = [];
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

    public function mount($adulto = null): void
    {
        $this->autorizarVista();
        if (! $adulto) {
            return;
        }

        $this->adulto = $adulto instanceof AdultoMayor ? $adulto : AdultoMayor::query()->find($adulto);
        abort_unless($this->adulto, 404);
        $this->cod_am = $this->adulto->cod_residente;
        $this->autorizarResidente($this->adulto);
    }

    public function toggleFormularioCrear(): void
    {
        $this->autorizarGestionOrden();
        $this->mostrarFormularioCrear = ! $this->mostrarFormularioCrear;
        if ($this->mostrarFormularioCrear) {
            $this->nuevo_cod_am = $this->adulto?->cod_residente ?? '';
            $this->nuevo_fecha_inicio = now()->toDateString();
            $this->nuevo_hora = '08:00';
            $this->nuevo_medico = '';
            $this->resetValidation();
        }
    }

    public function abrirFormularioPara(string $codResidente): void
    {
        $this->autorizarGestionOrden();
        $this->adulto = AdultoMayor::query()->findOrFail($codResidente);
        $this->autorizarResidente($this->adulto);
        $this->cod_am = $codResidente;
        $this->nuevo_cod_am = $codResidente;
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
            'nuevo_cod_am' => 'required|exists:residentes,cod_residente',
            'nuevo_nombre' => 'required|string|min:2|max:120',
            'nuevo_dosis' => 'required|string|max:60',
            'nuevo_frecuencia' => 'required|string|max:80',
            'nuevo_via' => 'required|string|max:60',
            'nuevo_hora' => 'required|date_format:H:i',
            'nuevo_fecha_inicio' => 'required|date',
            'nuevo_fecha_fin' => 'nullable|date|after_or_equal:nuevo_fecha_inicio',
            'nuevo_observacion' => 'nullable|string|max:1000',
        ]);

        $residente = AdultoMayor::query()->findOrFail($this->nuevo_cod_am);
        $this->autorizarResidente($residente);
        $personal = auth()->user()?->personal()->where('estado', 'ACTIVO')->first();
        if (! $personal) {
            throw ValidationException::withMessages(['nuevo_medico' => 'El usuario médico no tiene un registro de personal activo.']);
        }

        $atencion = Atencion::query()
            ->where('cod_residente', $residente->cod_residente)
            ->where('cod_personal', $personal->cod_personal)
            ->whereNotIn('estado', ['ANULADA', 'CANCELADA'])
            ->latest('fecha_hora')
            ->first();
        if (! $atencion) {
            throw ValidationException::withMessages([
                'nuevo_cod_am' => 'Registre primero una atención médica del residente; la BDD V2 exige vincular cada prescripción a una atención.',
            ]);
        }

        [$dosis, $unidad] = $this->separarDosis($this->nuevo_dosis);
        $prescripcion = DB::transaction(function () use ($residente, $personal, $atencion, $dosis, $unidad): Prescripcion {
            $medicamento = Medicamento::query()->firstOrCreate(
                ['nombre_generico' => Str::upper(trim($this->nuevo_nombre))],
                [
                    'cod_medicamento' => $this->codigo('MED'),
                    'via_predeterminada' => Str::upper(trim($this->nuevo_via)),
                    'control_especial' => false,
                    'estado' => 'ACTIVO',
                ],
            );

            $observacion = trim($this->nuevo_observacion);
            if (filled($this->nuevo_fecha_fin)) {
                $observacion = trim($observacion."\nFecha de finalización prevista: {$this->nuevo_fecha_fin}");
            }

            $prescripcion = Prescripcion::query()->create([
                'cod_prescripcion' => $this->codigo('PRE'),
                'cod_residente' => $residente->cod_residente,
                'cod_atencion' => $atencion->cod_atencion,
                'cod_medicamento' => $medicamento->cod_medicamento,
                'cod_personal' => $personal->cod_personal,
                'dosis' => $dosis,
                'unidad_dosis' => $unidad,
                'via_administracion' => Str::upper(trim($this->nuevo_via)),
                'frecuencia' => trim($this->nuevo_frecuencia),
                'indicacion' => null,
                'segun_necesidad' => false,
                'fecha_hora_prescripcion' => Carbon::parse($this->nuevo_fecha_inicio.' '.now()->format('H:i:s')),
                'estado' => 'ACTIVA',
                'observacion' => $observacion !== '' ? $observacion : null,
            ]);

            HorarioPrescripcion::query()->create([
                'cod_horario_prescripcion' => $this->codigo('HPR'),
                'cod_prescripcion' => $prescripcion->cod_prescripcion,
                'hora_programada' => $this->nuevo_hora,
                'dosis_programada' => $dosis,
                'estado' => 'ACTIVO',
            ]);

            return $prescripcion;
        });

        $this->adulto = $residente;
        $this->cod_am = $residente->cod_residente;
        $this->reset(['nuevo_nombre', 'nuevo_dosis', 'nuevo_observacion', 'nuevo_fecha_fin']);
        $this->mostrarFormularioCrear = false;
        session()->flash('mensaje_exito', 'Medicamento '.$prescripcion->nombre_medicamento.' prescrito correctamente.');
        $this->dispatch('medicacion-actualizada');
    }

    public function updatedCodAm($value): void
    {
        $this->adulto = $value ? AdultoMayor::query()->findOrFail($value) : null;
        if ($this->adulto) {
            $this->autorizarResidente($this->adulto);
        }
        $this->resetPage();
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFiltroEstado(): void { $this->resetPage(); }
    public function updatingFiltroVia(): void { $this->resetPage(); }

    public function limpiarFiltros(): void
    {
        $this->reset(['search', 'filtroEstado', 'filtroVia']);
        $this->resetPage();
    }

    public function suspenderMedicamento(string $id): void
    {
        $this->autorizarGestionOrden();
        $prescripcion = Prescripcion::query()->with(['adultoMayor', 'medicamento'])->findOrFail($id);
        $this->autorizarResidente($prescripcion->adultoMayor);
        abort_unless($prescripcion->estado === 'ACTIVA', 409, 'Solo puede suspender una prescripción activa.');
        $prescripcion->update([
            'cod_personal_suspension' => auth()->user()->personal->cod_personal,
            'fecha_hora_suspension' => now(),
            'motivo_suspension' => 'Suspendida desde el panel clínico.',
            'estado' => 'SUSPENDIDA',
        ]);
        session()->flash('mensaje_exito', "Medicación '{$prescripcion->nombre_medicamento}' suspendida correctamente.");
        $this->dispatch('medicacion-actualizada');
    }

    public function finalizarMedicamento(string $id): void
    {
        $this->autorizarGestionOrden();
        $prescripcion = Prescripcion::query()->with(['adultoMayor', 'medicamento'])->findOrFail($id);
        $this->autorizarResidente($prescripcion->adultoMayor);
        abort_unless($prescripcion->estado === 'ACTIVA', 409, 'Solo puede finalizar una prescripción activa.');
        $prescripcion->update(['estado' => 'FINALIZADA']);
        session()->flash('mensaje_exito', "Tratamiento de '{$prescripcion->nombre_medicamento}' finalizado con éxito.");
        $this->dispatch('medicacion-actualizada');
    }

    public function verUbicacion(string $codResidente): void
    {
        $this->adultoDrawer = AdultoMayor::query()->with(['ocupacionActiva.cama.habitacion', 'alertas'])->find($codResidente);
        $this->drawerUbicacion = true;
        $this->drawerGrafico = false;
    }

    public function verGraficos(string $codResidente): void
    {
        $this->adultoDrawer = AdultoMayor::query()->with([
            'ocupacionActiva.cama.habitacion',
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
        $adultosQuery = AdultoMayor::query()->where('estado', 'ADMITIDO');
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
        abort_unless(auth()->user()?->hasRole('MEDICO GENERAL/GERIATRA')
            && auth()->user()?->can('prescripciones.crear'), 403,
            'Las prescripciones solo pueden ser creadas o modificadas por personal médico autorizado.');
    }

    private function autorizarResidente(AdultoMayor $adulto): void
    {
        if (auth()->user()?->hasRole('ENFERMEROS')) {
            app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($adulto, auth()->user());
        }
    }

    private function codigo(string $prefijo): string
    {
        return $prefijo.'_'.Str::upper(Str::random(12));
    }

    private function separarDosis(string $texto): array
    {
        preg_match('/(\d+(?:[.,]\d+)?)/', $texto, $coincidencia);
        $dosis = isset($coincidencia[1]) ? (float) str_replace(',', '.', $coincidencia[1]) : 1;
        $unidad = trim(preg_replace('/^\s*'.preg_quote($coincidencia[1] ?? '', '/').'\s*/', '', $texto));

        return [$dosis, Str::limit($unidad !== '' ? $unidad : 'DOSIS', 30, '')];
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
