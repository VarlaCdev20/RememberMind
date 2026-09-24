<?php

namespace App\Livewire\Valoraciones;

use Livewire\Component;
use App\Models\AdultoMayor;
use App\Models\Atencion;
use App\Models\HistorialEstadoResidente;
use App\Models\NotaClinica;
use App\Models\SignoVital;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

        DB::beginTransaction();
        try {
            $estadoAnterior = $this->adulto->estado;
            $this->adulto->update(['estado' => 'DECISION_ADMISION']);

            HistorialEstadoResidente::create([
                'cod_residente' => $this->adulto->cod_residente,
                'cod_usuario_registro' => auth()->id(),
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => 'DECISION_ADMISION',
                'motivo' => 'Valoración médica general completada.',
            ]);

            $atencion = Atencion::create([
                'cod_residente' => $this->adulto->cod_residente,
                'tipo_atencion' => 'VALORACION_MEDICA_ADMISION',
                'motivo' => 'Valoración médica para decisión de admisión',
                'fecha_hora' => now(),
                'estado' => 'FINALIZADA',
                'observacion' => $this->observacion_medica,
                'registrado_por' => auth()->id(),
            ]);

            NotaClinica::create([
                'cod_nota' => 'NOT_' . strtoupper(Str::random(10)),
                'cod_atencion' => $atencion->cod_atencion,
                'cod_residente' => $this->adulto->cod_residente,
                'cod_personal' => $atencion->cod_personal,
                'tipo_nota' => 'VALORACION_MEDICA_ADMISION',
                'contenido' => $this->observacion_medica,
                'fecha_hora' => now(),
                'estado' => 'VIGENTE',
            ]);

            if ($this->pa_sistolica && $this->fc && $this->temp) {
                SignoVital::create([
                    'cod_residente' => $this->adulto->cod_residente,
                    'cod_personal' => $atencion->cod_personal,
                    'cod_atencion' => $atencion->cod_atencion,
                    'fecha_hora' => now(),
                    'presion_sistolica' => $this->pa_sistolica,
                    'presion_diastolica' => $this->pa_diastolica,
                    'frecuencia_cardiaca' => $this->fc,
                    'frecuencia_respiratoria' => $this->fr,
                    'temperatura' => $this->temp,
                    'saturacion_oxigeno' => $this->sato2,
                    'estado' => 'VIGENTE',
                    'observacion' => 'Signos vitales de valoración médica de admisión.',
                ]);
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

            DB::commit();

            $this->dispatch('swal', [
                'title' => 'Valoración Médica Completada',
                'text' => 'El paciente ha sido derivado a la fase de DECISIÓN ADMINISTRATIVA FINAL.',
                'icon' => 'success'
            ]);

            $this->dispatch('valoracionMedicaCompletada');
            $this->close();

        } catch (\Exception $e) {
            DB::rollBack();
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
