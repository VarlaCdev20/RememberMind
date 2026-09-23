<?php

namespace App\Livewire\Valoraciones;

use Livewire\Component;
use App\Models\AplicacionInstrumento;
use App\Models\Atencion;
use App\Models\Instrumento;
use App\Models\Personal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EvaluacionGeriatricaModal extends Component
{
    public $mostrar = false;
    public $cod_am = null;
    
    // Campos del formulario
    public $cod_area;
    public $cod_instrumento;
    public $fecha_eval;
    public $hora_eval;
    public $puntaje_total;
    public $categoria_resultado;
    public $nivel_alerta = 'NORMAL';
    public $nivel_riesgo;
    public $observaciones;

    // Colecciones para renderizar selectores
    public $areas = [];
    public $instrumentos = [];
    public $instrumentoSeleccionado = null;

    protected $listeners = [
        'evaluacion-geriatrica-abrir' => 'abrir',
    ];

    public function mount()
    {
        $this->fecha_eval = date('Y-m-d');
        $this->hora_eval = date('H:i');
        // La V2 no define áreas geriátricas independientes: los instrumentos
        // pertenecen al catálogo operativo único.
        $this->areas = collect([(object) [
            'cod_area' => 'INSTRUMENTOS_V2',
            'nombre' => 'Instrumentos de valoración',
        ]]);
    }

    public function abrir($cod_am)
    {
        $this->resetForm();
        $this->cod_am = $cod_am;
        $this->fecha_eval = date('Y-m-d');
        $this->hora_eval = date('H:i');
        $this->mostrar = true;
    }

    public function cerrar()
    {
        $this->mostrar = false;
        $this->resetForm();
    }

    public function updatedCodArea($value)
    {
        $this->cod_instrumento = null;
        $this->instrumentoSeleccionado = null;
        $this->puntaje_total = null;
        $this->categoria_resultado = null;
        
        if ($value) {
            $this->instrumentos = Instrumento::query()
                ->where('estado', 'ACTIVO')
                ->orderBy('nombre')
                ->get();
        } else {
            $this->instrumentos = [];
        }
    }

    public function updatedCodInstrumento($value)
    {
        $this->puntaje_total = null;
        $this->categoria_resultado = null;
        
        if ($value) {
            $this->instrumentoSeleccionado = Instrumento::find($value);
        } else {
            $this->instrumentoSeleccionado = null;
        }
    }

    protected function rules()
    {
        $rules = [
            'cod_area' => 'required|string|in:INSTRUMENTOS_V2',
            'cod_instrumento' => 'required|string|exists:instrumentos,cod_instrumento',
            'fecha_eval' => 'required|date|before_or_equal:today',
            'hora_eval' => 'nullable',
            'categoria_resultado' => 'nullable|string|max:150',
            'nivel_alerta' => 'required|string|in:NORMAL,PREVENTIVO,CRITICO',
            'nivel_riesgo' => 'nullable|string|max:30',
            'observaciones' => 'nullable|string|max:1000',
        ];

        if ($this->instrumentoSeleccionado) {
            $tipo = $this->instrumentoSeleccionado->tipo_resultado;
            $max = $this->instrumentoSeleccionado->puntaje_maximo;

            if ($tipo === 'CUANTITATIVO' || $tipo === 'MIXTO') {
                $rules['puntaje_total'] = 'required|numeric|min:0';
                if ($max) {
                    $rules['puntaje_total'] .= '|max:' . $max;
                }
            } elseif ($tipo === 'TIEMPO') {
                $rules['puntaje_total'] = 'required|numeric|min:0';
            } else {
                $rules['puntaje_total'] = 'nullable|numeric|min:0';
            }
        } else {
            $rules['puntaje_total'] = 'nullable|numeric|min:0';
        }

        return $rules;
    }

    protected $messages = [
        'cod_instrumento.required' => 'Debe seleccionar un instrumento de evaluación.',
        'fecha_eval.required' => 'La fecha es obligatoria.',
        'fecha_eval.before_or_equal' => 'La fecha no puede ser futura.',
        'puntaje_total.required' => 'El puntaje es obligatorio para este tipo de instrumento.',
        'puntaje_total.numeric' => 'El puntaje debe ser un valor numérico.',
        'puntaje_total.min' => 'El puntaje no puede ser menor a 0.',
        'puntaje_total.max' => 'El puntaje no puede superar el límite máximo del instrumento.',
        'nivel_alerta.required' => 'El nivel de alerta es obligatorio.',
    ];

    public function guardar()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $personal = Personal::query()
                    ->where('cod_usuario', auth()->user()->cod_usuario)
                    ->where('estado', 'ACTIVO')
                    ->firstOrFail();

                $atencion = Atencion::query()
                    ->where('cod_residente', $this->cod_am)
                    ->where('cod_personal', $personal->cod_personal)
                    ->latest('fecha_hora')
                    ->first();

                $instrumento = Instrumento::findOrFail($this->cod_instrumento);
                $interpretacion = collect([$this->categoria_resultado, $this->nivel_riesgo])
                    ->filter()
                    ->implode(' · ');

                AplicacionInstrumento::create([
                    'cod_aplicacion' => 'API_' . Str::upper(Str::random(12)),
                    'cod_instrumento' => $this->cod_instrumento,
                    'cod_residente' => $this->cod_am,
                    'cod_personal' => $personal->cod_personal,
                    'cod_atencion' => $atencion?->cod_atencion,
                    'fecha_hora' => $this->fecha_eval . ' ' . ($this->hora_eval ?: '00:00'),
                    'puntaje_total' => $this->puntaje_total,
                    'puntaje_maximo' => $instrumento->puntaje_maximo,
                    'clasificacion' => $this->nivel_alerta,
                    'interpretacion' => $interpretacion ?: null,
                    'estado' => 'ACTIVA',
                    'observacion' => $this->observaciones,
                ]);
            });

            $this->cerrar();
            $this->dispatch('evaluacion-geriatrica-guardada');
            $this->dispatch('swal:alert', [
                'type'    => 'success',
                'title'   => 'Evaluación Registrada',
                'message' => 'La evaluación geriátrica ha sido guardada correctamente.'
            ]);

        } catch (\Exception $e) {
            $this->dispatch('swal:alert', [
                'type'    => 'error',
                'title'   => 'Error',
                'message' => 'Ocurrió un error al guardar: ' . $e->getMessage()
            ]);
        }
    }

    private function resetForm()
    {
        $this->cod_area = null;
        $this->cod_instrumento = null;
        $this->fecha_eval = date('Y-m-d');
        $this->hora_eval = date('H:i');
        $this->puntaje_total = null;
        $this->categoria_resultado = null;
        $this->nivel_alerta = 'NORMAL';
        $this->nivel_riesgo = null;
        $this->observaciones = null;
        $this->instrumentoSeleccionado = null;
        $this->instrumentos = [];
    }

    public function render()
    {
        return view('livewire.valoraciones.evaluacion-geriatrica-modal');
    }
}
