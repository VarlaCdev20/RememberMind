<?php

namespace App\Livewire\Medicacion;

use Livewire\Component;
use App\Models\AdministracionMedicacion;
use App\Models\MedicacionAdulto;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdministracionMedicacionModal extends Component
{
    public $showModal = false;
    public $cod_med_adulto = null;
    public $cod_am;
    public $medicamento_nombre = '';

    public $fecha = '';
    public $hora_programada = '';
    public $hora_real = '';
    public $administrado = true;
    public $motivo_omision = '';
    public $efecto_observado = '';
    public $observacion = '';

    protected $listeners = ['abrirModalAdministracion'];

    public function rules()
    {
        return [
            'cod_am'          => 'required|exists:adulto_mayor,cod_am',
            'cod_med_adulto'  => 'required|exists:medicacion_adulto,cod_med_adulto',
            'fecha'           => 'required|date|before_or_equal:today',
            'hora_programada' => 'required|date_format:H:i',
            'hora_real'       => $this->administrado ? 'required|date_format:H:i' : 'nullable|date_format:H:i',
            'administrado'    => 'required|boolean',
            'motivo_omision'  => !$this->administrado ? 'required|string|min:5|max:255' : 'nullable|string|max:255',
            'efecto_observado'=> 'nullable|string|max:255',
            'observacion'     => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'fecha.required'             => 'La fecha es obligatoria.',
            'cod_am.required'            => 'El adulto mayor es obligatorio.',
            'cod_am.exists'              => 'El adulto mayor seleccionado no existe.',
            'cod_med_adulto.required'    => 'La medicación es obligatoria.',
            'cod_med_adulto.exists'      => 'La medicación seleccionada no existe.',
            'fecha.before_or_equal'      => 'La fecha no puede ser futura.',
            'hora_programada.required'   => 'La hora programada es obligatoria.',
            'hora_programada.date_format'=> 'La hora programada debe tener formato HH:MM.',
            'hora_real.required'         => 'La hora real de administración es obligatoria.',
            'hora_real.date_format'      => 'La hora real debe tener formato HH:MM.',
            'motivo_omision.required'    => 'Debe indicar el motivo de la omisión (mínimo 5 caracteres).',
            'motivo_omision.min'         => 'El motivo de la omisión debe tener al menos 5 caracteres.',
        ];
    }

    public function abrirModalAdministracion($cod_am, $cod_med_adulto, ?string $hora_programada = null)
    {
        abort_unless(auth()->user()?->can('administracion_medicacion.registrar'), 403);
        $this->resetValidation();
        $this->cod_am = $cod_am;
        $this->cod_med_adulto = $cod_med_adulto;

        abort_unless(Auth::check(), 401);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($cod_am, Auth::user());
        
        $medicacion = MedicacionAdulto::where('cod_med_adulto', $cod_med_adulto)
            ->where('cod_am', $cod_am)
            ->first();

        if (!$medicacion) {
            $this->addError('cod_med_adulto', 'El medicamento no pertenece al paciente.');
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'El medicamento no pertenece al paciente.',
            ]);
            return;
        }

        if (!in_array($medicacion->estado, ['ACTIVO', 'ACTIVA', 'VIGENTE'])) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Medicamento no activo',
                'text' => 'No se puede administrar un medicamento inactivo o suspendido.',
            ]);
            return;
        }

        $this->medicamento_nombre = $medicacion->nombre_medicamento;
        $this->hora_programada = $hora_programada
            ?: ($medicacion->hora_programada ? \Carbon\Carbon::parse($medicacion->hora_programada)->format('H:i') : now()->format('H:i'));
        
        $this->fecha = now()->format('Y-m-d');
        $this->hora_real = now()->format('H:i');
        $this->administrado = true;
        $this->motivo_omision = '';
        $this->efecto_observado = '';
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
        abort_unless(auth()->user()?->can('administracion_medicacion.registrar'), 403);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($this->cod_am, Auth::user());

        $this->validate();

        // Verificar que el medicamento pertenece al paciente y está vigente
        $medicacion = MedicacionAdulto::where('cod_med_adulto', $this->cod_med_adulto)
            ->where('cod_am', $this->cod_am)
            ->first();

        if (!$medicacion || !in_array($medicacion->estado, ['ACTIVO', 'ACTIVA', 'VIGENTE'])) {
            $this->addError('cod_med_adulto', 'El medicamento seleccionado no pertenece al paciente o no está activo.');
            return;
        }

        // Evitar doble administración para la misma ocurrencia programada
        $h = substr($this->hora_programada, 0, 5);
        $yaRegistrado = AdministracionMedicacion::where('cod_med_adulto', $this->cod_med_adulto)
            ->whereDate('fecha', $this->fecha)
            ->where('hora_programada', 'like', '%' . $h . '%')
            ->exists();

        if ($yaRegistrado) {
            $this->addError('cod_med_adulto', 'Ya existe un registro para esta dosis en la fecha y hora programada.');
            return;
        }

        $datos = [
            'cod_med_adulto'  => $this->cod_med_adulto,
            'cod_am'          => $this->cod_am,
            'fecha'           => $this->fecha,
            'hora_programada' => $this->hora_programada,
            'hora_real'       => $this->administrado ? $this->hora_real : null,
            'administrado'    => $this->administrado,
            'motivo_omision'  => !$this->administrado ? $this->motivo_omision : null,
            'efecto_observado'=> $this->efecto_observado ?: null,
            'observacion'     => $this->observacion ?: null,
            'registrado_por'  => Auth::id(),
        ];

        DB::transaction(function () use ($datos) {
            $duplicado = AdministracionMedicacion::where('cod_med_adulto', $this->cod_med_adulto)
                ->whereDate('fecha', $this->fecha)
                ->where('hora_programada', 'like', substr($this->hora_programada, 0, 5) . '%')
                ->lockForUpdate()
                ->exists();

            if ($duplicado) {
                $this->addError('cod_med_adulto', 'Esta dosis ya tiene una administración u omisión registrada.');
                return;
            }

            AdministracionMedicacion::create($datos);
        });

        if ($this->getErrorBag()->has('cod_med_adulto')) {
            return;
        }

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
        $administracionList = [];
        if ($this->cod_am) {
            $administracionList = AdministracionMedicacion::with('medicacion')
                ->where('cod_am', $this->cod_am)
                ->orderBy('fecha', 'desc')
                ->orderBy('hora_programada', 'desc')
                ->take(5)
                ->get();
        }

        return view('livewire.medicacion.administracion-medicacion-modal', [
            'administracionList' => $administracionList
        ]);
    }
}
