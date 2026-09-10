<?php

namespace App\Livewire\Cuidados;

use App\Models\AdultoMayor;
use App\Models\AsignacionAdultoMayor;
use App\Models\PlanCuidado;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class PlanCuidadoPanel extends Component
{
    use WithPagination;

    public string $search        = '';
    public string $filtroEstado  = '';

    public bool   $modalForm  = false;
    public bool   $modalVer   = false;
    public ?int   $editandoId = null;
    public ?int   $viendoId   = null;

    public string $codAm         = '';
    public string $tipoPlan      = 'INICIAL';
    public string $nivelCuidado  = 'ESTANDAR';
    public string $estadoPlan    = 'BORRADOR';
    public string $origen        = 'ADMISION';
    public string $resumen       = '';
    public string $fechaInicio   = '';
    public string $fechaFin      = '';

    public function abrirCrear(): void
    {
        $this->reset('editandoId','codAm','tipoPlan','nivelCuidado','resumen','fechaFin');
        $this->tipoPlan   = 'INICIAL';
        $this->nivelCuidado = 'ESTANDAR';
        $this->estadoPlan = 'BORRADOR';
        $this->origen     = 'ADMISION';
        $this->fechaInicio= today()->format('Y-m-d');
        $this->modalForm  = true;
    }

    public function guardar(): void
    {
        $this->validate([
            'codAm'      => 'required|exists:adulto_mayor,cod_am',
            'tipoPlan'   => 'required|in:INICIAL,AJUSTE,REEVALUACION',
            'nivelCuidado'=> 'required|in:PREVENTIVO,ESTANDAR,INTENSIVO,PALIATIVO',
            'estadoPlan' => 'required|in:BORRADOR,ACTIVO',
            'fechaInicio'=> 'required|date',
        ], [
            'codAm.required'     => 'Seleccione un adulto mayor.',
            'tipoPlan.required'  => 'Seleccione el tipo de plan.',
            'nivelCuidado.required'=> 'Seleccione el nivel de cuidado.',
            'fechaInicio.required'=> 'La fecha de inicio es obligatoria.',
        ]);

        // Validar que no exista plan ACTIVO
        if ($this->estadoPlan === 'ACTIVO' && ! $this->editandoId) {
            $activoExistente = PlanCuidado::where('cod_am', $this->codAm)
                ->where('estado', 'ACTIVO')->exists();
            if ($activoExistente) {
                $this->addError('estadoPlan', 'Este adulto mayor ya tiene un plan de cuidado ACTIVO. Ciérrelo antes de crear uno nuevo.');
                return;
            }
        }

        // Validar que haya asignación activa para ACTIVO
        if ($this->estadoPlan === 'ACTIVO') {
            $asignado = AsignacionAdultoMayor::where('cod_am', $this->codAm)
                ->whereIn('estado', ['ACTIVO', 'ACTIVA'])->exists();
            if (! $asignado) {
                $this->addError('codAm', 'El adulto mayor debe tener una asignación de turno activa antes de activar el plan.');
                return;
            }
        }

        // Calcular versión
        $version = PlanCuidado::where('cod_am', $this->codAm)->count() + 1;

        $datos = [
            'cod_am'       => $this->codAm,
            'tipo_plan'    => $this->tipoPlan,
            'version'      => $version,
            'nivel_cuidado'=> $this->nivelCuidado,
            'estado'       => $this->estadoPlan,
            'origen'       => $this->origen,
            'resumen'      => $this->resumen ?: null,
            'fecha_inicio' => $this->fechaInicio,
            'fecha_fin'    => $this->fechaFin ?: null,
            'creado_por'   => Auth::id(),
        ];

        if ($this->editandoId) {
            PlanCuidado::findOrFail($this->editandoId)->update($datos);
            $msg = 'Plan de cuidado actualizado.';
        } else {
            PlanCuidado::create($datos);
            $msg = 'Plan de cuidado creado.';
        }

        $this->modalForm = false;
        $this->dispatch('swal', ['icon' => 'success', 'title' => $msg]);
    }

    public function cerrarPlan(string $id): void
    {
        PlanCuidado::findOrFail($id)->update([
            'estado'   => 'CERRADO',
            'fecha_fin'=> today()->toDateString(),
        ]);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Plan de cuidado cerrado.']);
    }

    public function cerrarModales(): void
    {
        $this->modalForm = false;
        $this->modalVer  = false;
        $this->viendoId  = null;
        $this->resetValidation();
    }

    public function render()
    {
        $planes = PlanCuidado::with(['adultoMayor','creadoPor'])
            ->withCount('tareasActivas as tareas_activas_count')
            ->when($this->search, fn($q) =>
                $q->whereHas('adultoMayor', fn($sq) =>
                    $sq->where('nombres','ilike','%'.$this->search.'%')
                      ->orWhere('ap_paterno','ilike','%'.$this->search.'%')
                )
            )
            ->when($this->filtroEstado, fn($q) => $q->where('estado', $this->filtroEstado))
            ->orderByDesc('fecha_inicio')
            ->paginate(12);

        return view('livewire.cuidados.plan-cuidado-panel', [
            'planes'  => $planes,
            'adultos' => AdultoMayor::select('cod_am','nombres','ap_paterno','ap_materno')
                ->whereNull('archivado_en')->orderBy('ap_paterno')->get(),
        ])->layout('layouts.sistema');
    }
}
