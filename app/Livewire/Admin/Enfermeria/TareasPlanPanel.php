<?php

namespace App\Livewire\Admin\Enfermeria;

use App\Models\PlanCuidado;
use App\Models\TareaPlanCuidado;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class TareasPlanPanel extends Component
{
    use WithPagination;

    public string $search        = '';
    public string $filtroEstado  = 'PENDIENTE';
    public string $filtroArea    = '';
    public string $filtroPlan    = '';
    public string $filtroTurno   = '';

    public bool   $modalForm     = false;
    public bool   $modalResultado= false;
    public ?int   $editandoId    = null;
    public ?int   $resultandoId  = null;

    // Crear tarea
    public string $codPlan         = '';
    public string $codAm           = '';
    public string $codTurno        = '';
    public string $responsableId   = '';
    public string $area            = '';
    public string $titulo          = '';
    public string $descripcion     = '';
    public string $frecuencia      = '';
    public string $fechaProgramada = '';
    public string $horaProgramada  = '';
    public string $prioridad       = 'NORMAL';
    public string $estadoTarea     = 'PENDIENTE';

    // Registrar resultado
    public string $resultado       = '';
    public string $observacion     = '';
    public string $motivoOmision   = '';

    public function abrirCrear(?int $planId = null): void
    {
        $this->reset('editandoId','titulo','descripcion','frecuencia','horaProgramada','responsableId');
        $this->codPlan         = $planId ? (string) $planId : '';
        $this->area            = '';
        $this->prioridad       = 'NORMAL';
        $this->estadoTarea     = 'PENDIENTE';
        $this->fechaProgramada = today()->format('Y-m-d');
        $this->modalForm       = true;
    }

    public function guardarTarea(): void
    {
        $this->validate([
            'codPlan'        => 'required|exists:planes_cuidado,cod_plan',
            'codTurno'       => 'required|exists:turnos_enfermeria,cod_turno',
            'area'           => 'required|in:SIGNOS,MEDICACION,MOVILIDAD,COGNITIVO,ALIMENTACION,HIDRATACION,HIGIENE,SUEÑO,SEGURIDAD,EMOCIONAL,FAMILIAR,REEVALUACION',
            'titulo'         => 'required|string|max:200',
            'fechaProgramada'=> 'required|date',
            'prioridad'      => 'required|in:BAJA,NORMAL,ALTA,URGENTE',
        ], [
            'codPlan.required'        => 'Seleccione un plan de cuidado.',
            'codTurno.required'       => 'Seleccione un turno.',
            'area.required'           => 'Seleccione el área de la tarea.',
            'titulo.required'         => 'El título es obligatorio.',
            'fechaProgramada.required'=> 'La fecha es obligatoria.',
        ]);

        $plan = PlanCuidado::find($this->codPlan);

        TareaPlanCuidado::create([
            'cod_plan'         => (int) $this->codPlan,
            'cod_am'           => $plan?->cod_am ?? '',
            'cod_turno'        => (int) $this->codTurno,
            'responsable_id'   => $this->responsableId ?: null,
            'area'             => $this->area,
            'titulo'           => $this->titulo,
            'descripcion'      => $this->descripcion ?: null,
            'frecuencia'       => $this->frecuencia ?: null,
            'fecha_programada' => $this->fechaProgramada,
            'hora_programada'  => $this->horaProgramada ? $this->horaProgramada . ':00' : null,
            'prioridad'        => $this->prioridad,
            'estado'           => 'PENDIENTE',
            'registrado_por'   => Auth::id(),
        ]);

        $this->modalForm = false;
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Tarea creada correctamente.']);
    }

    public function abrirResultado(int $id): void
    {
        $t = TareaPlanCuidado::findOrFail($id);
        $this->resultandoId  = $id;
        $this->resultado     = $t->resultado ?? '';
        $this->observacion   = $t->observacion ?? '';
        $this->motivoOmision = $t->motivo_omision ?? '';
        $this->estadoTarea   = $t->estado;
        $this->modalResultado= true;
    }

    public function guardarResultado(): void
    {
        $this->validate([
            'estadoTarea'  => 'required|in:REALIZADA,OMITIDA,REPROGRAMADA',
            'resultado'    => $this->estadoTarea === 'REALIZADA' ? 'required|string|min:5' : 'nullable',
            'motivoOmision'=> $this->estadoTarea === 'OMITIDA'  ? 'required|string|min:5' : 'nullable',
        ], [
            'resultado.required'    => 'Registre el resultado de la tarea.',
            'motivoOmision.required'=> 'Registre el motivo de omisión.',
        ]);

        TareaPlanCuidado::findOrFail($this->resultandoId)->update([
            'estado'         => $this->estadoTarea,
            'resultado'      => $this->resultado ?: null,
            'observacion'    => $this->observacion ?: null,
            'motivo_omision' => $this->motivoOmision ?: null,
            'fecha_realizada'=> $this->estadoTarea === 'REALIZADA' ? now() : null,
            'registrado_por' => Auth::id(),
        ]);

        $this->modalResultado = false;
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Resultado registrado.']);
    }

    public function cerrarModales(): void
    {
        $this->modalForm      = false;
        $this->modalResultado = false;
        $this->resultandoId   = null;
        $this->resetValidation();
    }

    public function render()
    {
        $tareas = TareaPlanCuidado::with(['adultoMayor','turno','responsable','plan'])
            ->when($this->search, fn($q) =>
                $q->where('titulo','ilike','%'.$this->search.'%')
                  ->orWhereHas('adultoMayor', fn($sq) =>
                      $sq->where('nombres','ilike','%'.$this->search.'%')
                        ->orWhere('ap_paterno','ilike','%'.$this->search.'%')
                  )
            )
            ->when($this->filtroEstado, fn($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->filtroArea,   fn($q) => $q->where('area',   $this->filtroArea))
            ->when($this->filtroPlan,   fn($q) => $q->where('cod_plan', (int) $this->filtroPlan))
            ->when($this->filtroTurno,  fn($q) => $q->where('cod_turno', (int) $this->filtroTurno))
            ->orderBy('fecha_programada')->orderBy('hora_programada')
            ->paginate(15);

        return view('livewire.admin.enfermeria.tareas-plan-panel', [
            'tareas'  => $tareas,
            'planes'  => PlanCuidado::activos()->with('adultoMayor')->get(),
            'turnos'  => TurnoEnfermeria::activos()->get(),
            'usuarios'=> User::orderBy('ap_paterno')->get(['cod_usu','nombres','ap_paterno']),
        ])->layout('layouts.sistema');
    }
}
