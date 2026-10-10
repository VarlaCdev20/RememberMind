<?php

namespace App\Modulos\Valoraciones\Presentacion\Livewire\Funcionalidades;

use App\Models\Area;
use App\Models\Instrumento;
use App\Models\Residente;
use App\Modulos\Valoraciones\Aplicacion\Acciones\RegistrarEvaluacionGeriatricaArea;
use Livewire\Component;

class EvaluacionGeriatricaAreaModal extends Component
{
    public bool $mostrar = false;

    public ?string $cod_residente = null;

    public ?string $cod_area = null;

    public string $nombreArea = '';

    public ?string $cod_instrumento = null;

    public string $fecha_eval = '';

    public string $hora_eval = '';

    public ?string $puntaje_total = null;

    public ?string $categoria_resultado = null;

    public string $nivel_alerta = 'NORMAL';

    public ?string $nivel_riesgo = null;

    public ?string $observaciones = null;

    public $pacientes = [];

    public $instrumentos = [];

    public $instrumentoSeleccionado = null;

    protected $listeners = [
        'evaluacion-geriatrica-area-abrir' => 'abrir',
    ];

    public function mount(): void
    {
        $this->autorizarCreacion();
        $this->fecha_eval = date('Y-m-d');
        $this->hora_eval = date('H:i');
        $this->pacientes = Residente::whereIn('estado', ['ACTIVO', 'ADMITIDO'])
            ->orderBy('nombres')
            ->get(['cod_residente', 'nombres', 'apellido_paterno', 'numero_documento']);
    }

    public function abrir(array $data): void
    {
        $this->autorizarCreacion();
        $this->resetForm();
        $this->cod_residente = $data['cod_residente'] ?? null;

        $this->cod_area = $data['cod_area'] ?? null;
        $this->fecha_eval = date('Y-m-d');
        $this->hora_eval = date('H:i');

        if ($this->cod_area) {
            $area = Area::find($this->cod_area);
            $this->nombreArea = $area?->nombre ?? '';
            $this->instrumentos = Instrumento::query()->where('estado', 'ACTIVO')->get();
        }

        $this->mostrar = true;
    }

    public function cerrar(): void
    {
        $this->mostrar = false;
        $this->resetForm();
    }

    public function updatedCodInstrumento(?string $value): void
    {
        $this->puntaje_total = null;
        $this->categoria_resultado = null;
        $this->instrumentoSeleccionado = $value
            ? Instrumento::find($value)
            : null;
    }

    protected function rules(): array
    {
        $rules = [
            'cod_residente' => 'required|string|exists:residentes,cod_residente',
            'cod_instrumento' => 'required|string|exists:instrumentos,cod_instrumento',
            'fecha_eval' => 'required|date|before_or_equal:today',
            'hora_eval' => 'nullable|string',
            'categoria_resultado' => 'nullable|string|max:150',
            'nivel_alerta' => 'required|string|in:NORMAL,PREVENTIVO,CRITICO',
            'nivel_riesgo' => 'nullable|string|max:30',
            'observaciones' => 'nullable|string|max:1000',
        ];

        if ($this->instrumentoSeleccionado) {
            $tipo = $this->instrumentoSeleccionado->tipo_resultado;
            $max = $this->instrumentoSeleccionado->puntaje_maximo;

            if (in_array($tipo, ['CUANTITATIVO', 'MIXTO', 'TIEMPO'])) {
                $rule = 'required|numeric|min:0';
                if ($max && $tipo !== 'TIEMPO') {
                    $rule .= '|max:'.$max;
                }
                $rules['puntaje_total'] = $rule;
            } else {
                $rules['puntaje_total'] = 'nullable|numeric|min:0';
            }
        } else {
            $rules['puntaje_total'] = 'nullable|numeric|min:0';
        }

        return $rules;
    }

    protected $messages = [
        'cod_residente.required' => 'Debe seleccionar un adulto mayor.',
        'cod_residente.exists' => 'El adulto mayor seleccionado no existe.',
        'cod_instrumento.required' => 'Debe seleccionar un instrumento de evaluación.',
        'fecha_eval.required' => 'La fecha de evaluación es obligatoria.',
        'fecha_eval.before_or_equal' => 'La fecha no puede ser futura.',
        'puntaje_total.required' => 'El puntaje es obligatorio para este instrumento.',
        'puntaje_total.numeric' => 'El puntaje debe ser un valor numérico.',
        'puntaje_total.max' => 'El puntaje supera el máximo permitido para este instrumento.',
        'nivel_alerta.required' => 'El nivel de alerta es obligatorio.',
    ];

    public function guardar(): void
    {
        $this->autorizarCreacion();
        $this->validate();

        try {
            app(RegistrarEvaluacionGeriatricaArea::class)->ejecutar(auth()->user(), [
                'cod_instrumento' => $this->cod_instrumento,
                'cod_residente' => $this->cod_residente,
                'fecha_eval' => $this->fecha_eval,
                'hora_eval' => $this->hora_eval,
                'puntaje_total' => $this->puntaje_total,
                'categoria_resultado' => $this->categoria_resultado,
                'nivel_alerta' => $this->nivel_alerta,
                'nivel_riesgo' => $this->nivel_riesgo,
                'observaciones' => $this->observaciones,            ]);

            $this->cerrar();
            $this->dispatch('evaluacion-geriatrica-guardada');
            $this->dispatch('swal:alert', [
                'type' => 'success',
                'title' => 'Evaluación Registrada',
                'message' => 'La evaluación geriátrica ha sido guardada correctamente.',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('swal:alert', [
                'type' => 'error',
                'title' => 'Error al guardar',
                'message' => 'Ocurrió un error: '.$e->getMessage(),
            ]);
        }
    }

    private function autorizarCreacion(): void
    {
        abort_unless(auth()->user()?->estado === 'ACTIVO' && auth()->user()->can('aplicaciones_instrumento.crear'), 403);
    }

    private function resetForm(): void
    {
        $this->cod_instrumento = null;
        $this->fecha_eval = date('Y-m-d');
        $this->hora_eval = date('H:i');
        $this->puntaje_total = null;
        $this->categoria_resultado = null;
        $this->nivel_alerta = 'NORMAL';
        $this->nivel_riesgo = null;
        $this->observaciones = null;
        $this->instrumentoSeleccionado = null;
        $this->cod_area = null;
        $this->nombreArea = '';
        $this->instrumentos = [];
    }

    public function render()
    {
        return view('livewire.valoraciones.evaluacion-geriatrica-area-modal');
    }
}
