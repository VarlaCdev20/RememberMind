<?php

namespace App\Livewire\Admin\AdultosMayores\Salud;

use Livewire\Component;
use App\Models\AdultoMayor;
use App\Models\AdministracionMedicacion;
use App\Models\MedicacionAdulto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

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
            'fecha' => 'required|date',
            'hora_programada' => 'required',
            'hora_real' => 'nullable',
            'administrado' => 'required|boolean',
            'motivo_omision' => 'required_if:administrado,false|max:255',
            'efecto_observado' => 'nullable|string|max:255',
            'observacion' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'fecha.required' => 'La fecha es obligatoria.',
            'hora_programada.required' => 'La hora programada es obligatoria.',
            'motivo_omision.required_if' => 'Debe indicar el motivo de la omisión.',
        ];
    }

    public function abrirModalAdministracion($cod_am, $cod_med_adulto)
    {
        $adulto = AdultoMayor::findOrFail($cod_am);
        Gate::authorize('viewClinicalData', $adulto);

        $this->resetValidation();
        $this->cod_am = $cod_am;
        $this->cod_med_adulto = $cod_med_adulto;
        
        $medicacion = MedicacionAdulto::findOrFail($cod_med_adulto);
        abort_if($medicacion->cod_am !== $cod_am, 403);
        $this->medicamento_nombre = $medicacion->nombre_medicamento;
        $this->hora_programada = $medicacion->hora_programada ? \Carbon\Carbon::parse($medicacion->hora_programada)->format('H:i') : now()->format('H:i');
        
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
        if ($value) {
            $this->motivo_omision = '';
            $this->hora_real = now()->format('H:i');
        } else {
            $this->hora_real = null;
        }
    }

    public function guardar()
    {
        $this->validate();
        $adulto = AdultoMayor::findOrFail($this->cod_am);
        Gate::authorize('viewClinicalData', $adulto);

        $medicacion = MedicacionAdulto::findOrFail($this->cod_med_adulto);
        abort_if($medicacion->cod_am !== $this->cod_am, 403);

        $datos = [
            'cod_med_adulto' => $this->cod_med_adulto,
            'cod_am' => $this->cod_am,
            'fecha' => $this->fecha,
            'hora_programada' => $this->hora_programada,
            'hora_real' => $this->administrado ? $this->hora_real : null,
            'administrado' => $this->administrado,
            'motivo_omision' => !$this->administrado ? $this->motivo_omision : null,
            'efecto_observado' => $this->efecto_observado,
            'observacion' => $this->observacion,
            'registrado_por' => Auth::id() ?? \App\Models\User::first()->cod_usu,
        ];

        AdministracionMedicacion::create($datos);

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

        return view('livewire.admin.adultos-mayores.salud.administracion-medicacion-modal', [
            'administracionList' => $administracionList
        ]);
    }
}
