<?php

namespace App\Livewire\Admin\SaludSeguimiento;

use Livewire\Component;
use App\Models\AdultoMayor;
use App\Models\FichaMedicaAdulto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SaludFichaPanel extends Component
{
    public AdultoMayor $adulto;
    public $fichaActiva;
    public $historialFichas = [];

    // Form fields
    public $hipertension = false;
    public $diabetes = false;
    public $problemas_cardiacos = false;
    public $acv = false;
    public $parkinson = false;
    public $epilepsia = false;
    public $alzheimer_diagnosticado = false;
    public $depresion = false;
    public $ansiedad = false;
    public $problemas_sueno = false;
    public $problemas_visuales = false;
    public $problemas_auditivos = false;
    public $dolor_cronico = false;
    
    public $alergias = '';
    public $restricciones_alimentarias = '';
    public $hospitalizaciones = '';
    public $cirugias = '';
    public $observacion_medica = '';

    public $modalOpen = false;

    public function mount(AdultoMayor $adulto)
    {
        $this->adulto = $adulto;
        $this->loadData();
    }

    public function loadData()
    {
        $this->fichaActiva = $this->adulto->fichasMedicas()->where('estado', 'ACTIVA')->latest()->first();
        $this->historialFichas = $this->adulto->fichasMedicas()->whereIn('estado', ['ARCHIVADA', 'ANULADA'])->latest()->get();
    }

    public function openModal()
    {
        if ($this->fichaActiva) {
            $this->fillForm($this->fichaActiva);
        } else {
            $this->resetForm();
        }
        $this->modalOpen = true;
    }

    public function closeModal()
    {
        $this->modalOpen = false;
    }

    public function resetForm()
    {
        $this->reset([
            'hipertension', 'diabetes', 'problemas_cardiacos', 'acv', 'parkinson', 
            'epilepsia', 'alzheimer_diagnosticado', 'depresion', 'ansiedad', 
            'problemas_sueno', 'problemas_visuales', 'problemas_auditivos', 'dolor_cronico',
            'alergias', 'restricciones_alimentarias', 'hospitalizaciones', 'cirugias', 'observacion_medica'
        ]);
    }

    public function fillForm(FichaMedicaAdulto $ficha)
    {
        $this->hipertension = $ficha->hipertension;
        $this->diabetes = $ficha->diabetes;
        $this->problemas_cardiacos = $ficha->problemas_cardiacos;
        $this->acv = $ficha->acv;
        $this->parkinson = $ficha->parkinson;
        $this->epilepsia = $ficha->epilepsia;
        $this->alzheimer_diagnosticado = $ficha->alzheimer_diagnosticado;
        $this->depresion = $ficha->depresion;
        $this->ansiedad = $ficha->ansiedad;
        $this->problemas_sueno = $ficha->problemas_sueno;
        $this->problemas_visuales = $ficha->problemas_visuales;
        $this->problemas_auditivos = $ficha->problemas_auditivos;
        $this->dolor_cronico = $ficha->dolor_cronico;
        
        $this->alergias = $ficha->alergias;
        $this->restricciones_alimentarias = $ficha->restricciones_alimentarias;
        $this->hospitalizaciones = $ficha->hospitalizaciones;
        $this->cirugias = $ficha->cirugias;
        $this->observacion_medica = $ficha->observacion_medica;
    }

    public function save()
    {
        if (!auth()->user()->can('salud.ficha.crear') && !auth()->user()->can('salud.ficha.editar')) {
            abort(403);
        }

        DB::beginTransaction();
        try {
            $data = [
                'cod_am' => $this->adulto->cod_am,
                'hipertension' => $this->hipertension,
                'diabetes' => $this->diabetes,
                'problemas_cardiacos' => $this->problemas_cardiacos,
                'acv' => $this->acv,
                'parkinson' => $this->parkinson,
                'epilepsia' => $this->epilepsia,
                'alzheimer_diagnosticado' => $this->alzheimer_diagnosticado,
                'depresion' => $this->depresion,
                'ansiedad' => $this->ansiedad,
                'problemas_sueno' => $this->problemas_sueno,
                'problemas_visuales' => $this->problemas_visuales,
                'problemas_auditivos' => $this->problemas_auditivos,
                'dolor_cronico' => $this->dolor_cronico,
                'alergias' => $this->alergias,
                'restricciones_alimentarias' => $this->restricciones_alimentarias,
                'hospitalizaciones' => $this->hospitalizaciones,
                'cirugias' => $this->cirugias,
                'observacion_medica' => $this->observacion_medica,
                'estado' => 'ACTIVA',
                'registrado_por' => auth()->user()->cod_usu,
            ];

            if ($this->fichaActiva) {
                // Check if changes exist, if so, we can optionally archive old and create new to maintain strict immutable history, or just update.
                // In phase 3 controller, it updates it.
                $this->fichaActiva->update($data);
            } else {
                FichaMedicaAdulto::create($data);
            }

            DB::commit();

            $this->closeModal();
            $this->loadData();
            
            $this->dispatch('swal:success', [
                'title' => 'Ficha Guardada',
                'text' => 'La ficha médica se actualizó correctamente.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('swal:error', [
                'title' => 'Error',
                'text' => 'Ocurrió un error al guardar: ' . $e->getMessage(),
            ]);
        }
    }

    public function archivarFicha()
    {
        if (!auth()->user()->can('salud.ficha.archivar')) abort(403);

        if ($this->fichaActiva) {
            $this->fichaActiva->update(['estado' => 'ARCHIVADA']);
            $this->loadData();
            $this->dispatch('swal:success', [
                'title' => 'Archivada',
                'text' => 'La ficha ha sido movida al historial.',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.salud-seguimiento.salud-ficha-panel')->layout('layouts.sistema');
    }
}
