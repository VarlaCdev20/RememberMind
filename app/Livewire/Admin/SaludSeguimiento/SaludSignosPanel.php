<?php

namespace App\Livewire\Admin\SaludSeguimiento;

use Livewire\Component;
use App\Models\AdultoMayor;
use App\Models\SignosVitalesAdulto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SaludSignosPanel extends Component
{
    public AdultoMayor $adulto;
    public $historialSignos = [];

    // Form fields
    public $fecha;
    public $hora;
    public $presion_sistolica;
    public $presion_diastolica;
    public $frecuencia_cardiaca;
    public $saturacion_oxigeno;
    public $temperatura;
    public $peso;
    public $observaciones = '';

    public $modalOpen = false;

    public function mount(AdultoMayor $adulto)
    {
        $this->adulto = $adulto;
        $this->loadData();
    }

    public function loadData()
    {
        $this->historialSignos = $this->adulto->signosVitales()->where('estado', 'VIGENTE')->latest('fecha')->latest('hora')->get();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->modalOpen = true;
    }

    public function closeModal()
    {
        $this->modalOpen = false;
    }

    public function resetForm()
    {
        $this->fecha = now()->toDateString();
        $this->hora = now()->format('H:i');
        $this->reset([
            'presion_sistolica', 'presion_diastolica', 'frecuencia_cardiaca', 
            'saturacion_oxigeno', 'temperatura', 'peso', 'observaciones'
        ]);
    }

    public function save()
    {
        if (!auth()->user()->can('salud.signos.crear')) abort(403);

        $this->validate([
            'fecha' => 'required|date',
            'hora' => 'required',
            'presion_sistolica' => 'nullable|integer',
            'presion_diastolica' => 'nullable|integer',
            'frecuencia_cardiaca' => 'nullable|integer',
            'saturacion_oxigeno' => 'nullable|integer|min:0|max:100',
            'temperatura' => 'nullable|numeric',
            'peso' => 'nullable|numeric',
        ]);

        DB::beginTransaction();
        try {
            SignosVitalesAdulto::create([
                'cod_am' => $this->adulto->cod_am,
                'fecha' => $this->fecha,
                'hora' => $this->hora,
                'presion_sistolica' => $this->presion_sistolica,
                'presion_diastolica' => $this->presion_diastolica,
                'frecuencia_cardiaca' => $this->frecuencia_cardiaca,
                'saturacion_oxigeno' => $this->saturacion_oxigeno,
                'temperatura' => $this->temperatura,
                'peso' => $this->peso,
                'observaciones' => $this->observaciones,
                'estado' => 'VIGENTE',
                'registrado_por' => auth()->user()->cod_usu,
            ]);

            DB::commit();

            $this->closeModal();
            $this->loadData();
            
            // Check for warnings
            if ($this->temperatura > 37.8 || $this->saturacion_oxigeno < 92) {
                $this->dispatch('swal:warning', [
                    'title' => 'Valor fuera de rango',
                    'text' => 'Se registraron valores fuera del rango orientativo. Este aviso no constituye diagnóstico médico.',
                ]);
            } else {
                $this->dispatch('swal:success', [
                    'title' => 'Signos Guardados',
                    'text' => 'Los signos vitales se registraron correctamente.',
                ]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('swal:error', [
                'title' => 'Error',
                'text' => 'Ocurrió un error al guardar: ' . $e->getMessage(),
            ]);
        }
    }

    public function anularRegistro($id)
    {
        if (!auth()->user()->can('salud.signos.anular')) abort(403);

        $signo = SignosVitalesAdulto::find($id);
        if ($signo) {
            $signo->update(['estado' => 'ANULADO', 'motivo_anulacion' => 'Corrección de registro']);
            $this->loadData();
            $this->dispatch('swal:success', [
                'title' => 'Anulado',
                'text' => 'El registro ha sido anulado.',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.salud-seguimiento.salud-signos-panel')->layout('layouts.sistema');
    }
}
