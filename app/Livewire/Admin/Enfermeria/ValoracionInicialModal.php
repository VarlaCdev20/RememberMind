<?php

namespace App\Livewire\Admin\Enfermeria;

use App\Models\AdultoMayor;
use App\Models\EstadoAdulto;
use App\Models\ValoracionEnfermeriaAdmision;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ValoracionInicialModal extends Component
{
    public bool $isOpen = false;
    public $adulto;
    public ?string $valoracionId = null;

    public $estado_general;
    public $nivel_conciencia;
    public $orientacion;
    public $comunicacion;

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

    public $signos_vitales_iniciales;
    public $observacion;
    public $recomendacion_enfermeria;

    protected $listeners = ['abrirValoracionInicial' => 'open'];

    public function open($cod_am): void
    {
        $this->resetForm();
        $this->adulto = AdultoMayor::find($cod_am);

        if (! $this->adulto) {
            return;
        }

        $existente = ValoracionEnfermeriaAdmision::where('cod_am', $this->adulto->cod_am)
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
            $this->signos_vitales_iniciales = $existente->signos_vitales_iniciales;
            $this->observacion = $existente->observacion;
            $this->recomendacion_enfermeria = $existente->recomendacion_enfermeria;
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
                'title' => 'Estado CRITICO detectado',
                'text' => 'Por favor, notifique inmediatamente al equipo medico de guardia.',
                'icon' => 'warning',
            ]);
        }
    }

    public function updatedRiesgoCaida($value): void
    {
        if ($value === 'CRITICO') {
            $this->dispatch('swal', [
                'title' => 'Riesgo de Caida CRITICO',
                'text' => 'Debe implementar protocolos de contencion y prevencion de forma urgente.',
                'icon' => 'warning',
            ]);
        }
    }

    public function guardar(): void
    {
        if (! $this->adulto) {
            return;
        }

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
            'orientacion.required' => 'La orientacion es obligatoria.',
            'movilidad.required' => 'La movilidad es obligatoria.',
            'riesgo_caida.required' => 'El riesgo de caida es obligatorio.',
            'recomendacion_enfermeria.required' => 'La recomendacion es obligatoria para el medico.',
            'intensidad_dolor.required' => 'Si hay dolor, indique la intensidad (1-10).',
            'ubicacion_heridas.required' => 'Si hay heridas, indique la ubicacion.',
        ]);

        DB::beginTransaction();

        try {
            $valoracion = ValoracionEnfermeriaAdmision::updateOrCreate(
                ['cod_am' => $this->adulto->cod_am],
                [
                    'fecha_valoracion' => now()->toDateString(),
                    'hora_valoracion' => now()->format('H:i:s'),
                    'estado_general' => $this->estado_general,
                    'nivel_conciencia' => $this->nivel_conciencia,
                    'orientacion' => $this->orientacion,
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
                    'signos_vitales_iniciales' => $this->signos_vitales_iniciales,
                    'observacion' => $this->observacion,
                    'recomendacion_enfermeria' => $this->recomendacion_enfermeria,
                    'estado' => 'COMPLETADA',
                    'registrado_por' => auth()->user()?->cod_usu,
                ]
            );

            $estadoModel = EstadoAdulto::firstOrCreate(['estado' => 'VALORACION_MEDICA']);

            $this->adulto->update([
                'cod_est_adul' => $estadoModel->cod_est_adul,
            ]);

            $asignacion = $this->adulto->asignacionesTurno()
                ->where('motivo_asignacion', 'VALORACION INICIAL')
                ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
                ->first();

            if ($asignacion) {
                $asignacion->update(['estado' => 'FINALIZADA']);
            }

            activity('Enfermeria')
                ->causedBy(auth()->user())
                ->performedOn($this->adulto)
                ->withProperties([
                    'cod_val_enf' => $valoracion->cod_val_enf,
                    'estado_general' => $this->estado_general,
                    'orientacion' => $this->orientacion,
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

            $this->dispatch('valoracionCompletada', cod_am: $this->adulto->cod_am, cod_val_enf: $valoracion->cod_val_enf);
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
        return view('livewire.admin.enfermeria.valoracion-inicial-modal');
    }
}
