<?php

namespace App\Livewire\Admin\Medico;

use Livewire\Component;
use App\Models\AdultoMayor;
use Spatie\Activitylog\Models\Activity;
use App\Models\HistorialEstadoAdulto;

class DecisionAdmisionModal extends Component
{
    public $isOpen = false;
    public $adulto;
    
    public $decision;
    public $motivo_decision;
    public $seguimiento_requerido;
    public $cuidado_especial_requerido;
    public $institucion_derivada;
    public $motivo_derivacion;
    public $recomendacion_final;

    protected $listeners = ['abrirDecisionAdmision' => 'open'];

    public function open($cod_am)
    {
        $this->resetForm();
        $this->adulto = AdultoMayor::find($cod_am);
        if($this->adulto && $this->adulto->estado->estado === 'DECISION_ADMISION') {
            $this->isOpen = true;
        } else {
            $this->dispatch('notificar', ['tipo' => 'error', 'mensaje' => 'No se puede decidir sin valoración médica completa.']);
        }
    }
    
    public function close()
    {
        $this->isOpen = false;
        $this->resetForm();
    }
    
    public function resetForm()
    {
        $this->reset([
            'decision', 'motivo_decision', 'seguimiento_requerido',
            'cuidado_especial_requerido', 'institucion_derivada',
            'motivo_derivacion', 'recomendacion_final'
        ]);
        $this->resetValidation();
    }

    public function updatedDecision($value)
    {
        // Limpiar campos condicionales cuando cambia la decisión
        $this->motivo_decision = '';
        $this->seguimiento_requerido = '';
        $this->cuidado_especial_requerido = '';
        $this->institucion_derivada = '';
        $this->motivo_derivacion = '';
    }

    public function guardar()
    {
        $rules = [
            'decision' => 'required',
            'recomendacion_final' => 'nullable|string'
        ];

        if ($this->decision === 'ADMITIDO_NORMAL') {
            $rules['motivo_decision'] = 'required|min:5';
        } elseif ($this->decision === 'ADMITIDO_CON_SEGUIMIENTO') {
            $rules['seguimiento_requerido'] = 'required|min:5';
        } elseif ($this->decision === 'ADMITIDO_CON_CUIDADO_ESPECIAL') {
            $rules['cuidado_especial_requerido'] = 'required|min:5';
        } elseif ($this->decision === 'DERIVADO') {
            $rules['motivo_derivacion'] = 'required|min:5';
            $rules['institucion_derivada'] = 'nullable|string';
        }

        $this->validate($rules, [
            'decision.required' => 'Debe seleccionar una decisión médica.',
            'motivo_decision.required' => 'El motivo de decisión es obligatorio para admisión normal.',
            'seguimiento_requerido.required' => 'El detalle del seguimiento es obligatorio.',
            'cuidado_especial_requerido.required' => 'El detalle de cuidado especial es obligatorio.',
            'motivo_derivacion.required' => 'Debe justificar el motivo de la derivación.',
        ]);

        \DB::beginTransaction();
        try {
            $nuevoEstadoStr = ($this->decision === 'DERIVADO') ? 'DERIVADO' : 'PENDIENTE_ASIGNACION';
            $estadoModel = \App\Models\EstadoAdulto::firstOrCreate(['estado' => $nuevoEstadoStr]);
            
            $this->adulto->update([
                'cod_est_adul' => $estadoModel->cod_est_adul
            ]);
            
            // Guardar historial de estado
            if(class_exists('\App\Models\HistorialEstadoAdulto')) {
                \App\Models\HistorialEstadoAdulto::create([
                    'cod_am' => $this->adulto->cod_am,
                    'estado_anterior' => 'DECISION_ADMISION',
                    'estado_nuevo' => $nuevoEstadoStr,
                    'fecha_cambio' => now(),
                    'motivo' => $this->decision . ' - ' . ($this->motivo_decision ?: ($this->motivo_derivacion ?: 'Decisión de Admisión')),
                    'usuario_id' => auth()->id()
                ]);
            }

            // Registrar Log
            activity('Medico')
                ->causedBy(auth()->user())
                ->performedOn($this->adulto)
                ->withProperties([
                    'decision' => $this->decision,
                    'motivo_decision' => $this->motivo_decision,
                    'seguimiento_requerido' => $this->seguimiento_requerido,
                    'cuidado_especial_requerido' => $this->cuidado_especial_requerido,
                    'motivo_derivacion' => $this->motivo_derivacion,
                    'institucion_derivada' => $this->institucion_derivada,
                    'recomendacion_final' => $this->recomendacion_final,
                ])
                ->log("Tomó la decisión de {$this->decision} para el paciente.");

            \DB::commit();

            $mensajeExito = ($this->decision === 'DERIVADO') 
                ? 'El paciente ha sido marcado como DERIVADO y sale del flujo de admisión.'
                : 'El paciente ha sido admitido y pasa a la etapa de asignación de habitación.';

            $this->dispatch('swal', [
                'title' => 'Decisión Registrada',
                'text' => $mensajeExito,
                'icon' => 'success'
            ]);

            $this->dispatch('valoracionMedicaCompletada');
            $this->close();

        } catch (\Exception $e) {
            \DB::rollBack();
            $this->dispatch('swal', [
                'title' => 'Error',
                'text' => 'Ocurrió un error al guardar la decisión: ' . $e->getMessage(),
                'icon' => 'error'
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.medico.decision-admision-modal');
    }
}
