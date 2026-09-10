<?php

namespace App\Livewire\Valoraciones;

use Livewire\Component;
use App\Models\AdultoMayor;

class ValoracionMedicaModal extends Component
{
    public $isOpen = false;
    public $adulto;
    
    // Campos
    public $condicion_medica_general;
    public $diagnosticos_referidos;
    public $antecedentes_medicos;
    public $alergias;
    public $medicacion_actual;
    
    // Signos vitales numéricos
    public $pa_sistolica;
    public $pa_diastolica;
    public $fc;
    public $fr;
    public $temp;
    public $sato2;

    public $examen_fisico_general;
    public $estado_neurologico_basico;
    public $estado_cognitivo_aparente;
    public $nivel_dependencia;
    public $riesgo_caida;
    public $estado_nutricional_aparente;
    
    public $requiere_control_medicacion = false;
    public $requiere_control_signos = false;
    public $requiere_seguimiento_cognitivo = false;
    public $requiere_cuidado_especial = false;
    public $detalle_cuidado_especial;
    
    public $observacion_medica;

    protected $listeners = ['abrirValoracionMedica' => 'open'];

    public function open($cod_am)
    {
        $this->resetForm();
        $this->adulto = AdultoMayor::find($cod_am);
        if($this->adulto) {
            // Autocompletar alergias conocidas desde la preadmision si existen
            $this->alergias = $this->adulto->alergias;
            $this->isOpen = true;
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
            'condicion_medica_general', 'diagnosticos_referidos', 'antecedentes_medicos',
            'alergias', 'medicacion_actual', 
            'pa_sistolica', 'pa_diastolica', 'fc', 'fr', 'temp', 'sato2',
            'examen_fisico_general', 'estado_neurologico_basico', 'estado_cognitivo_aparente',
            'nivel_dependencia', 'riesgo_caida', 'estado_nutricional_aparente',
            'requiere_control_medicacion', 'requiere_control_signos', 'requiere_seguimiento_cognitivo',
            'requiere_cuidado_especial', 'detalle_cuidado_especial', 'observacion_medica'
        ]);
        $this->resetValidation();
    }

    public function guardar()
    {
        $rules = [
            'condicion_medica_general' => 'required',
            'estado_cognitivo_aparente' => 'required',
            'nivel_dependencia' => 'required',
            'observacion_medica' => 'required|min:10',
        ];

        if ($this->requiere_cuidado_especial) {
            $rules['detalle_cuidado_especial'] = 'required|min:5';
        }

        $this->validate($rules, [
            'condicion_medica_general.required' => 'La condición médica es obligatoria.',
            'estado_cognitivo_aparente.required' => 'El estado cognitivo es obligatorio.',
            'nivel_dependencia.required' => 'El nivel de dependencia es obligatorio.',
            'observacion_medica.required' => 'La observación médica es obligatoria.',
            'detalle_cuidado_especial.required' => 'Si requiere cuidado especial, debe detallarlo.',
        ]);

        \DB::beginTransaction();
        try {
            // Actualizar estado a DECISION_ADMISION
            $estadoModel = \App\Models\EstadoAdulto::firstOrCreate(['estado' => 'DECISION_ADMISION']);
            
            $this->adulto->update([
                'cod_est_adul' => $estadoModel->cod_est_adul
            ]);
            
            // Opcional: Registrar Signos Vitales Iniciales (Si existen)
            if ($this->pa_sistolica && $this->fc && $this->temp) {
                // Si existe el módulo de salud se puede guardar aquí, por ahora lo enviaremos a properties
            }

            // Registrar Log
            activity('Medico')
                ->causedBy(auth()->user())
                ->performedOn($this->adulto)
                ->withProperties([
                    'condicion_medica' => $this->condicion_medica_general,
                    'estado_cognitivo' => $this->estado_cognitivo_aparente,
                    'nivel_dependencia' => $this->nivel_dependencia,
                    'signos_vitales' => [
                        'PA' => $this->pa_sistolica . '/' . $this->pa_diastolica,
                        'FC' => $this->fc,
                        'FR' => $this->fr,
                        'Temp' => $this->temp,
                        'SatO2' => $this->sato2
                    ],
                    'observacion' => $this->observacion_medica
                ])
                ->log("Completó valoración médica general.");

            \DB::commit();

            $this->dispatch('swal', [
                'title' => 'Valoración Médica Completada',
                'text' => 'El paciente ha sido derivado a la fase de DECISIÓN ADMINISTRATIVA FINAL.',
                'icon' => 'success'
            ]);

            $this->dispatch('valoracionMedicaCompletada');
            $this->close();

        } catch (\Exception $e) {
            \DB::rollBack();
            $this->dispatch('swal', [
                'title' => 'Error',
                'text' => 'Ocurrió un error al guardar la valoración: ' . $e->getMessage(),
                'icon' => 'error'
            ]);
        }
    }

    public function render()
    {
        return view('livewire.valoraciones.valoracion-medica-modal');
    }
}
