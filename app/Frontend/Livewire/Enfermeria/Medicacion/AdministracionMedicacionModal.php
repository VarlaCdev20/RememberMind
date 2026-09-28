<?php

namespace App\Frontend\Livewire\Enfermeria\Medicacion;

use App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService;
use App\Backend\Modulos\Medicacion\Servicios\RegistrarAdministracionMedicacionService;
use App\Models\AdministracionMedicacion;
use App\Models\Prescripcion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AdministracionMedicacionModal extends Component
{
    public $showModal = false;

    public $cod_prescripcion = null;

    public $cod_residente;

    public $medicamento_nombre = '';

    public $dosis_prescrita = '';

    public $unidad_dosis = '';

    public $via_administracion = '';

    public $frecuencia = '';

    public $indicacion = '';

    public $medico_prescriptor = '';

    public $fecha = '';

    public $hora_programada = '';

    public $hora_real = '';

    public $administrado = true;

    public $motivo_omision = '';

    public $efecto_observado = '';

    public $dosis_administrada = '';

    public $reaccion_adversa = '';

    public $observacion = '';

    protected $listeners = ['abrirModalAdministracion'];

    public function rules()
    {
        return [
            'cod_residente' => 'required|exists:residentes,cod_residente',
            'cod_prescripcion' => 'required|exists:prescripciones,cod_prescripcion',
            'fecha' => 'required|date|date_equals:today',
            'hora_programada' => 'required|date_format:H:i',
            'hora_real' => $this->administrado ? 'required|date_format:H:i' : 'nullable|date_format:H:i',
            'administrado' => 'required|boolean',
            'dosis_administrada' => $this->administrado ? 'required|numeric|min:0.001|max:9999999.999' : 'nullable|numeric|min:0.001|max:9999999.999',
            'motivo_omision' => ! $this->administrado ? 'required|string|min:5|max:255' : 'nullable|string|max:255',
            'efecto_observado' => 'nullable|string|max:2000',
            'reaccion_adversa' => 'nullable|string|max:2000',
            'observacion' => 'nullable|string|max:2000',
        ];
    }

    public function messages()
    {
        return [
            'fecha.required' => 'La fecha es obligatoria.',
            'cod_residente.required' => 'El adulto mayor es obligatorio.',
            'cod_residente.exists' => 'El adulto mayor seleccionado no existe.',
            'cod_prescripcion.required' => 'La medicación es obligatoria.',
            'cod_prescripcion.exists' => 'La medicación seleccionada no existe.',
            'fecha.date_equals' => 'La administración programada corresponde al turno de hoy.',
            'hora_programada.required' => 'La hora programada es obligatoria.',
            'hora_programada.date_format' => 'La hora programada debe tener formato HH:MM.',
            'hora_real.required' => 'La hora real de administración es obligatoria.',
            'hora_real.date_format' => 'La hora real debe tener formato HH:MM.',
            'dosis_administrada.required' => 'Registre la dosis efectivamente administrada.',
            'dosis_administrada.numeric' => 'La dosis administrada debe ser numérica.',
            'dosis_administrada.min' => 'La dosis administrada debe ser mayor que cero.',
            'motivo_omision.required' => 'Debe indicar el motivo de la omisión (mínimo 5 caracteres).',
            'motivo_omision.min' => 'El motivo de la omisión debe tener al menos 5 caracteres.',
        ];
    }

    public function abrirModalAdministracion($cod_residente, $cod_prescripcion, ?string $hora_programada = null)
    {
        abort_unless(auth()->user()?->can('administraciones_medicacion.crear'), 403);
        $this->resetValidation();
        $this->cod_residente = $cod_residente;
        $this->cod_prescripcion = $cod_prescripcion;

        abort_unless(Auth::check(), 401);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($cod_residente, Auth::user());

        $medicacion = Prescripcion::query()->with(['medicamento', 'horarios'])
            ->where('cod_prescripcion', $this->cod_prescripcion)
            ->where('cod_residente', $cod_residente)
            ->first();

        if (! $medicacion) {
            $this->addError('cod_prescripcion', 'El medicamento no pertenece al paciente.');
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'El medicamento no pertenece al paciente.',
            ]);

            return;
        }

        if ($medicacion->estado !== 'ACTIVA') {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Medicamento no activo',
                'text' => 'No se puede administrar un medicamento inactivo o suspendido.',
            ]);

            return;
        }

        $this->medicamento_nombre = $medicacion->nombre_medicamento;
        $this->dosis_prescrita = (string) ($medicacion->dosis ?? '');
        $this->unidad_dosis = (string) ($medicacion->unidad_dosis ?? '');
        $this->via_administracion = (string) ($medicacion->via_administracion ?? '');
        $this->frecuencia = (string) ($medicacion->frecuencia ?? '');
        $this->indicacion = (string) ($medicacion->indicacion ?? '');
        $this->medico_prescriptor = (string) ($medicacion->medico_indica ?? 'Profesional prescriptor');
        $this->hora_programada = $hora_programada
            ?: ($medicacion->hora_programada ? Carbon::parse($medicacion->hora_programada)->format('H:i') : now()->format('H:i'));

        $this->fecha = now()->format('Y-m-d');
        $this->hora_real = now()->format('H:i');
        $this->administrado = true;
        $this->motivo_omision = '';
        $this->efecto_observado = '';
        $this->dosis_administrada = (string) ($medicacion->horarios->firstWhere('estado', 'ACTIVO')?->dosis_programada ?? $medicacion->dosis ?? '');
        $this->reaccion_adversa = '';
        $this->observacion = '';

        $this->showModal = true;
    }

    public function cerrarModal()
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function updatedAdministrado($value)
    {
        $this->administrado = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        if ($this->administrado) {
            $this->motivo_omision = '';
            $this->hora_real = now()->format('H:i');
        } else {
            $this->hora_real = '';
        }
    }

    public function guardar()
    {
        abort_unless(Auth::check(), 401);
        abort_unless(auth()->user()?->can('administraciones_medicacion.crear'), 403);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($this->cod_residente, Auth::user());

        $this->validate();

        if ($this->administrado && Carbon::createFromFormat('Y-m-d H:i', $this->fecha.' '.$this->hora_real)->isFuture()) {
            $this->addError('hora_real', 'La fecha y hora de administración no pueden estar en el futuro.');

            return;
        }

        app(RegistrarAdministracionMedicacionService::class)->registrarProgramada(
            Auth::user(),
            ($this->cod_residente),
            $this->cod_prescripcion,
            $this->hora_programada,
            (bool) $this->administrado,
            $this->motivo_omision,
            $this->observacion,
            $this->efecto_observado,
            $this->dosis_administrada !== '' ? $this->dosis_administrada : null,
            $this->reaccion_adversa,
            $this->administrado ? $this->fecha.' '.$this->hora_real : null,
        );

        $mensaje = $this->administrado ? 'Administración registrada correctamente.' : 'Omisión registrada correctamente.';
        $icono = $this->administrado ? 'success' : 'warning';

        $this->cerrarModal();

        $this->dispatch('administracion-actualizada');
        $this->dispatch('swal', [
            'icon' => $icono,
            'title' => 'Éxito',
            'text' => $mensaje,
        ]);
    }

    public function render()
    {
        $administracionList = collect();
        if (($this->cod_residente) && app(TurnoEnfermeriaService::class)->esPacienteAsignado($this->cod_residente, Auth::user())) {
            $administracionList = AdministracionMedicacion::with('medicacion.medicamento')
                ->where('cod_residente', $this->cod_residente)
                ->latest('fecha_hora_programada')->take(5)->get();
        }

        return view('livewire.medicacion.administracion-medicacion-modal', [
            'administracionList' => $administracionList,
        ]);
    }
}
