<?php

namespace App\Livewire\Admin\PersonalInstitucional\Partials;

use Livewire\Component;
use App\Models\User;
use App\Models\HorarioPersonalSalud;
use App\Models\HorarioPersonalAdmin;
use App\Models\TurnoInstitucional;

class PersonalInstitucionalHorarios extends Component
{
    public $usuarioId;
    public $horarios = [];
    public $tipoPersonal;

    // Form
    public $dia_semana, $hora_inicio, $hora_fin, $estado = 'ACTIVO';
    public $cod_turno_inst; // For admin

    public $dias = ['LUNES', 'MARTES', 'MIÉRCOLES', 'JUEVES', 'VIERNES', 'SÁBADO', 'DOMINGO'];
    public $turnos = [];

    public function mount($usuarioId)
    {
        $this->usuarioId = $usuarioId;
        $this->turnos = TurnoInstitucional::all();
        $this->cargarHorarios();
    }

    public function cargarHorarios()
    {
        $usuario = User::with(['personalSalud', 'personalAdmin'])->find($this->usuarioId);
        
        if ($usuario->personalSalud) {
            $this->tipoPersonal = 'salud';
            $this->horarios = HorarioPersonalSalud::where('cod_per_sal', $usuario->personalSalud->cod_per_sal)->get();
        } elseif ($usuario->personalAdmin) {
            $this->tipoPersonal = 'admin';
            $this->horarios = HorarioPersonalAdmin::with('turno')->where('cod_per_adm', $usuario->personalAdmin->cod_per_adm)->get();
        } else {
            $this->tipoPersonal = 'ninguno';
            $this->horarios = collect();
        }
    }

    public function agregarHorario()
    {
        $this->validate([
            'dia_semana' => 'required',
            'hora_inicio' => 'required',
            'hora_fin' => 'required|after:hora_inicio',
        ]);

        $usuario = User::with(['personalSalud', 'personalAdmin'])->find($this->usuarioId);

        if ($this->tipoPersonal === 'salud') {
            HorarioPersonalSalud::create([
                'dia_semana' => $this->dia_semana,
                'hora_inicio' => $this->hora_inicio,
                'hora_fin' => $this->hora_fin,
                'estado' => $this->estado,
                'cod_per_sal' => $usuario->personalSalud->cod_per_sal,
            ]);
        } elseif ($this->tipoPersonal === 'admin') {
            HorarioPersonalAdmin::create([
                'dia_semana' => $this->dia_semana,
                'hora_inicio' => $this->hora_inicio,
                'hora_fin' => $this->hora_fin,
                'estado' => $this->estado,
                'cod_per_adm' => $usuario->personalAdmin->cod_per_adm,
                'cod_turno_inst' => $this->cod_turno_inst ?: null,
            ]);
        }

        $this->reset(['dia_semana', 'hora_inicio', 'hora_fin', 'cod_turno_inst']);
        $this->cargarHorarios();

        $this->dispatch('mostrarAlerta', [
            'type' => 'success',
            'title' => 'Horario Agregado',
            'message' => 'Se ha programado el horario correctamente.'
        ]);
    }

    public function eliminarHorario($id)
    {
        if ($this->tipoPersonal === 'salud') {
            HorarioPersonalSalud::find($id)->delete();
        } elseif ($this->tipoPersonal === 'admin') {
            HorarioPersonalAdmin::find($id)->delete();
        }

        $this->cargarHorarios();
        $this->dispatch('mostrarAlerta', [
            'type' => 'success',
            'title' => 'Eliminado',
            'message' => 'Horario removido.'
        ]);
    }

    public function render()
    {
        return view('livewire.admin.personal-institucional.partials.personal-institucional-horarios');
    }
}
