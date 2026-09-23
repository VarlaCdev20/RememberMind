<?php

namespace App\Livewire\Valoraciones;

use App\Models\Preadmision;
use App\Models\ValoracionEnfermeriaAdmision;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ValoracionInicialModal extends Component
{
    public bool $isOpen = false;
    public $preadmision;
    public ?string $valoracionId = null;

    public $estado_general;
    public $nivel_conciencia;
    public $orientacion;
    public $comunicacion;

    // Orientación desglosada
    public $orientacion_persona = 'ORIENTADO';
    public $orientacion_tiempo = 'ORIENTADO';
    public $orientacion_espacio = 'ORIENTADO';

    public bool $hay_dolor = false;
    public $intensidad_dolor;
    public $ubicacion_dolor;

    public $movilidad;
    public $apoyo_movilidad;
    public $riesgo_caida;

    public $piel_estado;
    public bool $hay_heridas = false;
    public $ubicacion_heridas;

    public $higiene_ingreso;
    public $continencia_basica;
    public $alimentacion_aparente;

    // Signos Vitales
    public $pa_sistolica;
    public $pa_diastolica;
    public $frecuencia_cardiaca;
    public $frecuencia_respiratoria;
    public $temperatura;
    public $saturacion_oxigeno;
    public $peso;
    public $talla;

    // Detalles Clínicos Extendidos
    public $antecedentes_relevantes;
    public $medicacion_referida;
    public $alergias_referidas;
    public $dependencia_funcional = 'INDEPENDIENTE';
    public $riesgo_nutricional = 'SIN RIESGO';
    public $riesgo_cognitivo = 'SIN DETERIORO';
    public $necesidad_apoyo_inmediato = '';
    public $prioridad_sugerida = 'MEDIA';
    public bool $confirmacion_documentacion = false;
    public $comentarios_adicionales;

    public $signos_vitales_iniciales;
    public $observacion;
    public $recomendacion_enfermeria;

    protected $listeners = ['abrirValoracionInicial' => 'open'];

    public function open($codPre): void
    {
        $this->resetForm();
        $this->preadmision = Preadmision::find($codPre);

        if (! $this->preadmision) {
            return;
        }

        $existente = ValoracionEnfermeriaAdmision::where('cod_pre', $this->preadmision->cod_pre)
            ->orderByDesc('fecha_valoracion')
            ->first();

        if ($existente) {
            $this->valoracionId = $existente->cod_val_enf;
            $this->estado_general = $existente->estado_general;
            $this->nivel_conciencia = $existente->nivel_conciencia;
            $this->orientacion = $existente->orientacion;
            $this->comunicacion = $existente->comunicacion;
            $this->hay_dolor = (bool) $existente->hay_dolor;
            $this->intensidad_dolor = $existente->intensidad_dolor;
            $this->ubicacion_dolor = $existente->ubicacion_dolor;
            $this->movilidad = $existente->movilidad;
            $this->apoyo_movilidad = $existente->apoyo_movilidad;
            $this->riesgo_caida = $existente->riesgo_caida;
            $this->piel_estado = $existente->piel_estado;
            $this->hay_heridas = (bool) $existente->hay_heridas;
            $this->ubicacion_heridas = $existente->ubicacion_heridas;
            $this->higiene_ingreso = $existente->higiene_ingreso;
            $this->continencia_basica = $existente->continencia_basica;
            $this->alimentacion_aparente = $existente->alimentacion_aparente;
            $this->recomendacion_enfermeria = $existente->recomendacion_enfermeria;

            // Parse orientacion desglosada
            if (preg_match('/Persona:\s*([A-Z]+)/i', $existente->orientacion, $matches)) {
                $this->orientacion_persona = strtoupper($matches[1]);
            } else {
                $this->orientacion_persona = 'ORIENTADO';
            }
            if (preg_match('/Tiempo:\s*([A-Z]+)/i', $existente->orientacion, $matches)) {
                $this->orientacion_tiempo = strtoupper($matches[1]);
            } else {
                $this->orientacion_tiempo = 'ORIENTADO';
            }
            if (preg_match('/Espacio:\s*([A-Z]+)/i', $existente->orientacion, $matches)) {
                $this->orientacion_espacio = strtoupper($matches[1]);
            } else {
                $this->orientacion_espacio = 'ORIENTADO';
            }

            // Parse signos vitales
            $vitals = json_decode($existente->signos_vitales_iniciales, true);
            if (is_array($vitals)) {
                $this->pa_sistolica = $vitals['pa_sistolica'] ?? '';
                $this->pa_diastolica = $vitals['pa_diastolica'] ?? '';
                $this->frecuencia_cardiaca = $vitals['frecuencia_cardiaca'] ?? '';
                $this->frecuencia_respiratoria = $vitals['frecuencia_respiratoria'] ?? '';
                $this->temperatura = $vitals['temperatura'] ?? '';
                $this->saturacion_oxigeno = $vitals['saturacion_oxigeno'] ?? '';
                $this->peso = $vitals['peso'] ?? '';
                $this->talla = $vitals['talla'] ?? '';
            }

            // Parse observaciones y detalles clínicos extendidos
            $obs = json_decode($existente->observacion, true);
            if (is_array($obs)) {
                $this->antecedentes_relevantes = $obs['antecedentes_relevantes'] ?? '';
                $this->medicacion_referida = $obs['medicacion_referida'] ?? '';
                $this->alergias_referidas = $obs['alergias_referidas'] ?? '';
                $this->dependencia_funcional = $obs['dependencia_funcional'] ?? 'INDEPENDIENTE';
                $this->riesgo_nutricional = $obs['riesgo_nutricional'] ?? 'SIN RIESGO';
                $this->riesgo_cognitivo = $obs['riesgo_cognitivo'] ?? 'SIN DETERIORO';
                $this->necesidad_apoyo_inmediato = $obs['necesidad_apoyo_inmediato'] ?? '';
                $this->prioridad_sugerida = $obs['prioridad_sugerida'] ?? 'MEDIA';
                $this->confirmacion_documentacion = $obs['confirmacion_documentacion'] ?? false;
                $this->comentarios_adicionales = $obs['comentarios'] ?? '';
            } else {
                $this->comentarios_adicionales = $existente->observacion;
            }
        } else {
            // Valores por defecto para nueva valoración
            $this->orientacion_persona = 'ORIENTADO';
            $this->orientacion_tiempo = 'ORIENTADO';
            $this->orientacion_espacio = 'ORIENTADO';
            $this->dependencia_funcional = 'INDEPENDIENTE';
            $this->riesgo_nutricional = 'SIN RIESGO';
            $this->riesgo_cognitivo = 'SIN DETERIORO';
            $this->prioridad_sugerida = $this->preadmision->prioridad ?? 'MEDIA';
            $this->confirmacion_documentacion = false;
        }

        $this->isOpen = true;
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset([
            'valoracionId',
            'estado_general',
            'nivel_conciencia',
            'orientacion',
            'comunicacion',
            'orientacion_persona',
            'orientacion_tiempo',
            'orientacion_espacio',
            'hay_dolor',
            'intensidad_dolor',
            'ubicacion_dolor',
            'movilidad',
            'apoyo_movilidad',
            'riesgo_caida',
            'piel_estado',
            'hay_heridas',
            'ubicacion_heridas',
            'higiene_ingreso',
            'continencia_basica',
            'alimentacion_aparente',
            'pa_sistolica',
            'pa_diastolica',
            'frecuencia_cardiaca',
            'frecuencia_respiratoria',
            'temperatura',
            'saturacion_oxigeno',
            'peso',
            'talla',
            'antecedentes_relevantes',
            'medicacion_referida',
            'alergias_referidas',
            'dependencia_funcional',
            'riesgo_nutricional',
            'riesgo_cognitivo',
            'necesidad_apoyo_inmediato',
            'prioridad_sugerida',
            'confirmacion_documentacion',
            'comentarios_adicionales',
            'signos_vitales_iniciales',
            'observacion',
            'recomendacion_enfermeria',
        ]);
        $this->resetValidation();
    }

    public function updatedEstadoGeneral($value): void
    {
        if ($value === 'CRITICO') {
            $this->dispatch('swal', [
                'title' => 'Estado CRÍTICO detectado',
                'text' => 'Por favor, notifique inmediatamente al equipo médico de guardia.',
                'icon' => 'warning',
            ]);
        }
    }

    public function updatedRiesgoCaida($value): void
    {
        if ($value === 'CRITICO') {
            $this->dispatch('swal', [
                'title' => 'Riesgo de Caída CRÍTICO',
                'text' => 'Debe implementar protocolos de contención y prevención de forma urgente.',
                'icon' => 'warning',
            ]);
        }
    }

    public function guardar(): void
    {
        if (! $this->preadmision) {
            return;
        }

        $rules = [
            'estado_general' => 'required',
            'nivel_conciencia' => 'required',
            'orientacion_persona' => 'required',
            'orientacion_tiempo' => 'required',
            'orientacion_espacio' => 'required',
            'movilidad' => 'required',
            'riesgo_caida' => 'required',
            'pa_sistolica' => 'required|numeric|min:' . \App\Services\Clinica\ValidacionSignosVitalesService::PAS_MIN . '|max:' . \App\Services\Clinica\ValidacionSignosVitalesService::PAS_MAX,
            'pa_diastolica' => 'required|numeric|min:' . \App\Services\Clinica\ValidacionSignosVitalesService::PAD_MIN . '|max:' . \App\Services\Clinica\ValidacionSignosVitalesService::PAD_MAX,
            'frecuencia_cardiaca' => 'required|numeric|min:' . \App\Services\Clinica\ValidacionSignosVitalesService::FC_MIN . '|max:' . \App\Services\Clinica\ValidacionSignosVitalesService::FC_MAX,
            'frecuencia_respiratoria' => 'required|numeric|min:' . \App\Services\Clinica\ValidacionSignosVitalesService::FR_MIN . '|max:' . \App\Services\Clinica\ValidacionSignosVitalesService::FR_MAX,
            'temperatura' => 'required|numeric|min:' . \App\Services\Clinica\ValidacionSignosVitalesService::TEMP_MIN . '|max:' . \App\Services\Clinica\ValidacionSignosVitalesService::TEMP_MAX,
            'saturacion_oxigeno' => 'required|numeric|min:' . \App\Services\Clinica\ValidacionSignosVitalesService::SPO2_MIN . '|max:' . \App\Services\Clinica\ValidacionSignosVitalesService::SPO2_MAX,
            'peso' => 'nullable|numeric|min:' . \App\Services\Clinica\ValidacionSignosVitalesService::PESO_MIN . '|max:' . \App\Services\Clinica\ValidacionSignosVitalesService::PESO_MAX,
            'talla' => 'nullable|numeric|min:0.5|max:' . \App\Services\Clinica\ValidacionSignosVitalesService::TALLA_CM_MAX,
            'dependencia_funcional' => 'required',
            'riesgo_nutricional' => 'required',
            'riesgo_cognitivo' => 'required',
            'prioridad_sugerida' => 'required',
            'recomendacion_enfermeria' => 'required|min:10',
            'confirmacion_documentacion' => 'accepted',
        ];

        if ($this->hay_dolor) {
            $rules['intensidad_dolor'] = 'required|integer|min:1|max:10';
        }

        if ($this->hay_heridas) {
            $rules['ubicacion_heridas'] = 'required|min:5';
        }

                if ((float)$this->pa_sistolica <= (float)$this->pa_diastolica) {
            $this->addError('pa_sistolica', 'La presión sistólica (' . $this->pa_sistolica . ' mmHg) debe ser estrictamente mayor a la diastólica (' . $this->pa_diastolica . ' mmHg).');
            return;
        }

        $this->validate($rules, [
            'estado_general.required' => 'Debe seleccionar un estado general.',
            'nivel_conciencia.required' => 'Debe seleccionar el nivel de conciencia.',
            'orientacion_persona.required' => 'La orientación en persona es obligatoria.',
            'orientacion_tiempo.required' => 'La orientación en tiempo es obligatoria.',
            'orientacion_espacio.required' => 'La orientación en espacio es obligatoria.',
            'movilidad.required' => 'La movilidad es obligatoria.',
            'riesgo_caida.required' => 'El riesgo de caída es obligatorio.',
            'pa_sistolica.required' => 'La PA sistólica es obligatoria.',
            'pa_sistolica.numeric' => 'Debe ser un número.',
            'pa_diastolica.required' => 'La PA diastólica es obligatoria.',
            'pa_diastolica.numeric' => 'Debe ser un número.',
            'frecuencia_cardiaca.required' => 'La FC es obligatoria.',
            'frecuencia_respiratoria.required' => 'La FR es obligatoria.',
            'temperatura.required' => 'La temperatura es obligatoria.',
            'saturacion_oxigeno.required' => 'La saturación de O2 es obligatoria.',
            'dependencia_funcional.required' => 'La dependencia funcional es obligatoria.',
            'riesgo_nutricional.required' => 'El riesgo nutricional es obligatorio.',
            'riesgo_cognitivo.required' => 'El riesgo cognitivo es obligatorio.',
            'prioridad_sugerida.required' => 'La prioridad sugerida es obligatoria.',
            'recomendacion_enfermeria.required' => 'La recomendación es obligatoria para el médico.',
            'intensidad_dolor.required' => 'Si hay dolor, indique la intensidad (1-10).',
            'ubicacion_heridas.required' => 'Si hay heridas, indique la ubicación.',
            'confirmacion_documentacion.accepted' => 'Debe confirmar que revisó la documentación.',
        ]);

        DB::beginTransaction();

        try {
            // Combinar orientación desglosada
            $orientacionCombinada = "Persona: {$this->orientacion_persona}, Tiempo: {$this->orientacion_tiempo}, Espacio: {$this->orientacion_espacio}";

            // Serializar signos vitales
            $signosVitalesData = [
                'pa_sistolica' => $this->pa_sistolica,
                'pa_diastolica' => $this->pa_diastolica,
                'frecuencia_cardiaca' => $this->frecuencia_cardiaca,
                'frecuencia_respiratoria' => $this->frecuencia_respiratoria,
                'temperatura' => $this->temperatura,
                'saturacion_oxigeno' => $this->saturacion_oxigeno,
                'peso' => $this->peso,
                'talla' => $this->talla,
            ];

            // Serializar observaciones y extendidos
            $observacionData = [
                'antecedentes_relevantes' => $this->antecedentes_relevantes,
                'medicacion_referida' => $this->medicacion_referida,
                'alergias_referidas' => $this->alergias_referidas,
                'dependencia_funcional' => $this->dependencia_funcional,
                'riesgo_nutricional' => $this->riesgo_nutricional,
                'riesgo_cognitivo' => $this->riesgo_cognitivo,
            'tipo_evaluacion_cognitiva' => 'CRIBADO_OBSERVACIONAL_ENFERMERIA',
                'necesidad_apoyo_inmediato' => $this->necesidad_apoyo_inmediato,
                'prioridad_sugerida' => $this->prioridad_sugerida,
                'confirmacion_documentacion' => $this->confirmacion_documentacion,
                'comentarios' => $this->comentarios_adicionales,
            ];

            $valoracion = ValoracionEnfermeriaAdmision::updateOrCreate(
                ['cod_pre' => $this->preadmision->cod_pre],
                [
                    'cod_am' => null,
                    'fecha_valoracion' => now()->toDateString(),
                    'hora_valoracion' => now()->format('H:i:s'),
                    'estado_general' => $this->estado_general,
                    'nivel_conciencia' => $this->nivel_conciencia,
                    'orientacion' => $orientacionCombinada,
                    'comunicacion' => $this->comunicacion,
                    'hay_dolor' => (bool) $this->hay_dolor,
                    'intensidad_dolor' => $this->hay_dolor && $this->intensidad_dolor !== '' ? (int) $this->intensidad_dolor : null,
                    'ubicacion_dolor' => $this->hay_dolor ? $this->ubicacion_dolor : null,
                    'movilidad' => $this->movilidad,
                    'apoyo_movilidad' => $this->apoyo_movilidad,
                    'riesgo_caida' => $this->riesgo_caida,
                    'piel_estado' => $this->piel_estado,
                    'hay_heridas' => (bool) $this->hay_heridas,
                    'ubicacion_heridas' => $this->hay_heridas ? $this->ubicacion_heridas : null,
                    'higiene_ingreso' => $this->higiene_ingreso,
                    'continencia_basica' => $this->continencia_basica,
                    'alimentacion_aparente' => $this->alimentacion_aparente,
                    'signos_vitales_iniciales' => json_encode($signosVitalesData),
                    'observacion' => json_encode($observacionData),
                    'recomendacion_enfermeria' => $this->recomendacion_enfermeria,
                    'estado' => 'COMPLETADA',
                    'registrado_por' => auth()->user()?->cod_usu,
                ]
            );

            // Cambiar estado de preadmisión a PENDIENTE_VALORACION_MEDICA y actualizar prioridad sugerida
            $this->preadmision->update([
                'estado' => 'PENDIENTE_VALORACION_MEDICA',
                'prioridad' => $this->prioridad_sugerida,
            ]);

            activity('Enfermeria')
                ->causedBy(auth()->user())
                ->performedOn($this->preadmision)
                ->withProperties([
                    'cod_val_enf' => $valoracion->cod_val_enf,
                    'estado_general' => $this->estado_general,
                    'orientacion' => $orientacionCombinada,
                    'movilidad' => $this->movilidad,
                    'riesgo_caida' => $this->riesgo_caida,
                    'hay_dolor' => $this->hay_dolor,
                    'recomendacion' => $this->recomendacion_enfermeria,
                ])
                ->log('Completo valoracion inicial de enfermeria.');

            DB::commit();

            $this->dispatch('swal', [
                'title' => 'Valoracion completada',
                'text' => 'El paciente ha sido derivado a Valoracion Medica.',
                'icon' => 'success',
            ]);

            $this->dispatch('valoracionCompletada', cod_pre: $this->preadmision->cod_pre, cod_val_enf: $valoracion->cod_val_enf);
            $this->dispatch('refreshDashboard');
            $this->close();
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->dispatch('swal', [
                'title' => 'Error',
                'text' => 'Ocurrio un error al guardar la valoracion: ' . $e->getMessage(),
                'icon' => 'error',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.valoraciones.valoracion-inicial-modal');
    }
}
