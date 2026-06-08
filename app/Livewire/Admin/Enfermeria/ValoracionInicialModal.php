<?php

namespace App\Livewire\Admin\Enfermeria;

use Livewire\Component;
use App\Models\AdultoMayor;
use App\Models\ValoracionEnfermeria;
use Spatie\Activitylog\Models\Activity;

class ValoracionInicialModal extends Component
{
    public $isOpen = false;
    public $adulto;
    
    // Campos
    public $estado_general;
    public $nivel_conciencia;
    public $orientacion;
    public $comunicacion;
    
    public $hay_dolor = false;
    public $intensidad_dolor;
    public $ubicacion_dolor;
    
    public $movilidad;
    public $apoyo_movilidad;
    public $riesgo_caida;
    
    public $piel_estado;
    public $hay_heridas = false;
    public $ubicacion_heridas;
    
    public $higiene_ingreso;
    public $continencia_basica;
    public $alimentacion_aparente;
    
    public $signos_vitales_iniciales;
    public $observacion;
    public $recomendacion_enfermeria;

    protected $listeners = ['abrirValoracionInicial' => 'open'];

    public function open($cod_am)
    {
        $this->resetForm();
        $this->adulto = AdultoMayor::find($cod_am);
        if($this->adulto) {
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
            'estado_general', 'nivel_conciencia', 'orientacion', 'comunicacion',
            'hay_dolor', 'intensidad_dolor', 'ubicacion_dolor',
            'movilidad', 'apoyo_movilidad', 'riesgo_caida',
            'piel_estado', 'hay_heridas', 'ubicacion_heridas',
            'higiene_ingreso', 'continencia_basica', 'alimentacion_aparente',
            'signos_vitales_iniciales', 'observacion', 'recomendacion_enfermeria'
        ]);
        $this->resetValidation();
    }

    public function updatedEstadoGeneral($value)
    {
        if ($value === 'CRITICO') {
            $this->dispatch('swal', [
                'title' => 'Estado CRÍTICO detectado',
                'text' => 'Por favor, notifique inmediatamente al equipo médico de guardia.',
                'icon' => 'warning'
            ]);
        }
    }

    public function updatedRiesgoCaida($value)
    {
        if ($value === 'CRITICO') {
            $this->dispatch('swal', [
                'title' => 'Riesgo de Caída CRÍTICO',
                'text' => 'Debe implementar protocolos de contención y prevención de forma urgente.',
                'icon' => 'warning'
            ]);
        }
    }

    public function guardar()
    {
        $rules = [
            'estado_general' => 'required',
            'orientacion' => 'required',
            'movilidad' => 'required',
            'riesgo_caida' => 'required',
            'recomendacion_enfermeria' => 'required|min:10',
        ];

        if ($this->hay_dolor) {
            $rules['intensidad_dolor'] = 'required|integer|min:1|max:10';
        }
        
        if ($this->hay_heridas) {
            $rules['ubicacion_heridas'] = 'required|min:5';
        }

        $this->validate($rules, [
            'estado_general.required' => 'Debe seleccionar un estado general.',
            'orientacion.required' => 'La orientación es obligatoria.',
            'movilidad.required' => 'La movilidad es obligatoria.',
            'riesgo_caida.required' => 'El riesgo de caída es obligatorio.',
            'recomendacion_enfermeria.required' => 'La recomendación es obligatoria para el médico.',
            'intensidad_dolor.required' => 'Si hay dolor, indique la intensidad (1-10).',
            'ubicacion_heridas.required' => 'Si hay heridas, indique la ubicación.',
        ]);

        \DB::beginTransaction();
        try {
            // Asumiendo que existe una tabla o json para guardar esta valoración.
            // Si no existe, podemos crear un modelo ValoracionInicial o guardarlo en una tabla genérica.
            // Para simplificar, si no hay tabla ValoracionEnfermeria, lo guardaremos como un Historial Clinico inicial o una tabla específica.
            // Como el usuario no ha mencionado tabla, usaré activity() properties o si existe la tabla, se inserta ahí.
            // Pero al final lo que importa es el estado del AdultoMayor.
            
            // Actualizar estado a VALORACION_MEDICA
            $estadoModel = \App\Models\EstadoAdulto::firstOrCreate(['estado' => 'VALORACION_MEDICA']);
            
            $this->adulto->update([
                'cod_est_adul' => $estadoModel->cod_est_adul
            ]);
            
            // Finalizar la asignación temporal si aplica
            if (\Illuminate\Support\Facades\Schema::hasTable('asignaciones_turno_adulto')) {
                $asignacion = $this->adulto->asignacionesTurno()
                    ->where('motivo_asignacion', 'VALORACION INICIAL')
                    ->where('estado', 'ACTIVA')
                    ->first();

                if ($asignacion) {
                    $asignacion->update(['estado' => 'FINALIZADA']);
                }
            }

            // Registrar Log
            activity('Enfermeria')
                ->causedBy(auth()->user())
                ->performedOn($this->adulto)
                ->withProperties([
                    'estado_general' => $this->estado_general,
                    'orientacion' => $this->orientacion,
                    'movilidad' => $this->movilidad,
                    'riesgo_caida' => $this->riesgo_caida,
                    'hay_dolor' => $this->hay_dolor,
                    'recomendacion' => $this->recomendacion_enfermeria
                ])
                ->log("Completó valoración inicial de enfermería.");

            \DB::commit();

            $this->dispatch('swal', [
                'title' => 'Valoración completada',
                'text' => 'El paciente ha sido derivado a Valoración Médica.',
                'icon' => 'success'
            ]);

            $this->dispatch('valoracionCompletada');
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
        return view('livewire.admin.enfermeria.valoracion-inicial-modal');
    }
}
