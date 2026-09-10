<?php

namespace App\Livewire\Clinica;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AdultoMayor;
use App\Models\FichaMedicaAdulto;
use Illuminate\Support\Facades\DB;

class SaludFichaPanel extends Component
{
    use WithPagination;

    public ?AdultoMayor $adulto = null;
    public string $adultoSeleccionado = '';
    public string $buscarPaciente = '';
    public string $searchGeneral = '';
    public string $filtroEstado = 'todas';
    public ?FichaMedicaAdulto $fichaActiva = null;
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

    public $modalGeneral = false;
    public $modalAlergias = false;
    public $modalCondiciones = false;
    public $modalAntecedentes = false;
    public $modalObservaciones = false;

    public function mount(?AdultoMayor $adulto = null)
    {
        if ($adulto && $adulto->exists) {
            $this->cargarAdulto($adulto->cod_am);
        }
    }

    public function updatingSearchGeneral()
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }

    private function cargarAdulto(string $codAm): void
    {
        $this->adulto = AdultoMayor::with('estado')->findOrFail($codAm);
        $this->adultoSeleccionado = $this->adulto->cod_am;
        $this->loadData();
    }

    public function loadData()
    {
        if ($this->adulto) {
            $this->fichaActiva = $this->adulto->fichasMedicas()->where('estado', 'ACTIVA')->latest()->first();
            $this->historialFichas = $this->adulto->fichasMedicas()->whereIn('estado', ['ARCHIVADA', 'ANULADA'])->latest()->get();
        } else {
            $this->fichaActiva = null;
            $this->historialFichas = [];
        }
    }

    public function buscarPacienteAction(): void
    {
        if (trim($this->adultoSeleccionado) === '') {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Seleccione un paciente',
                'text' => 'Seleccione un adulto mayor antes de buscar.',
            ]);
            return;
        }

        $adultoExistente = AdultoMayor::find($this->adultoSeleccionado);
        if (!$adultoExistente) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No encontrado',
                'text' => 'El adulto mayor seleccionado no existe.',
            ]);
            return;
        }

        $this->cargarAdulto($this->adultoSeleccionado);
        
        if (!$this->fichaActiva) {
            $this->dispatch('swal', [
                'icon' => 'info',
                'title' => 'Sin ficha médica',
                'text' => 'No existe ficha médica registrada para este adulto mayor.',
            ]);
        }
    }

    public function getPacientesSelectorProperty()
    {
        $query = AdultoMayor::query()
            ->with('estado')
            ->orderBy('nombres')
            ->orderBy('ap_paterno');

        if (trim($this->buscarPaciente) !== '') {
            $busqueda = '%' . trim($this->buscarPaciente) . '%';
            $query->where(function ($subQuery) use ($busqueda) {
                $subQuery->where('cod_am', 'like', $busqueda)
                    ->orWhere('nombres', 'like', $busqueda)
                    ->orWhere('ap_paterno', 'like', $busqueda)
                    ->orWhere('ap_materno', 'like', $busqueda)
                    ->orWhere('ci', 'like', $busqueda);
            });
        }

        return $query->limit(12)->get();
    }

    public function openModalGeneral()
    {
        if ($this->fichaActiva) $this->fillForm($this->fichaActiva);
        else $this->resetForm();
        $this->modalGeneral = true;
    }

    public function openModalAlergias()
    {
        if ($this->fichaActiva) $this->fillForm($this->fichaActiva);
        else $this->resetForm();
        $this->modalAlergias = true;
    }

    public function openModalCondiciones()
    {
        if ($this->fichaActiva) $this->fillForm($this->fichaActiva);
        else $this->resetForm();
        $this->modalCondiciones = true;
    }

    public function openModalAntecedentes()
    {
        if ($this->fichaActiva) $this->fillForm($this->fichaActiva);
        else $this->resetForm();
        $this->modalAntecedentes = true;
    }

    public function openModalObservaciones()
    {
        if ($this->fichaActiva) $this->fillForm($this->fichaActiva);
        else $this->resetForm();
        $this->modalObservaciones = true;
    }

    public function closeModal()
    {
        $this->modalGeneral = false;
        $this->modalAlergias = false;
        $this->modalCondiciones = false;
        $this->modalAntecedentes = false;
        $this->modalObservaciones = false;
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
        if (!$this->adulto) {
            // RENDERING GENERAL DASHBOARD
            $query = AdultoMayor::with(['estado', 'fichasMedicas' => function($q) {
                $q->where('estado', 'ACTIVA');
            }]);

            if (trim($this->searchGeneral) !== '') {
                $busqueda = '%' . trim($this->searchGeneral) . '%';
                $query->where(function ($subQuery) use ($busqueda) {
                    $subQuery->where('nombres', 'like', $busqueda)
                        ->orWhere('ap_paterno', 'like', $busqueda)
                        ->orWhere('ap_materno', 'like', $busqueda)
                        ->orWhere('ci', 'like', $busqueda);
                });
            }

            if ($this->filtroEstado === 'con_ficha') {
                $query->whereHas('fichasMedicas', function($q) {
                    $q->where('estado', 'ACTIVA');
                });
            } elseif ($this->filtroEstado === 'sin_ficha') {
                $query->whereDoesntHave('fichasMedicas', function($q) {
                    $q->where('estado', 'ACTIVA');
                });
            }

            $pacientesGeneral = $query->paginate(12);

            // Calculate stats efficiently
            $totalAdultos = AdultoMayor::count();
            $conFicha = FichaMedicaAdulto::where('estado', 'ACTIVA')->distinct('cod_am')->count('cod_am');
            $sinFicha = $totalAdultos - $conFicha;
            
            // Just for UI cards (approximation or specific)
            $alergiasCount = FichaMedicaAdulto::where('estado', 'ACTIVA')->whereNotNull('alergias')->where('alergias', '!=', '')->count();
            $cuidadosCount = FichaMedicaAdulto::where('estado', 'ACTIVA')->whereNotNull('restricciones_alimentarias')->where('restricciones_alimentarias', '!=', '')->count();
            
            $stats = [
                'total' => $totalAdultos,
                'con_ficha' => $conFicha,
                'sin_ficha' => $sinFicha,
                'alergias' => $alergiasCount,
                'cuidados' => $cuidadosCount,
            ];

            return view('livewire.admin.salud-seguimiento.salud-ficha-general', [
                'pacientes' => $pacientesGeneral,
                'stats' => $stats
            ]);
        }

        // RENDERING INDIVIDUAL FICHA
        return view('livewire.clinica.salud-ficha-panel', [
            'pacientesSelector' => $this->pacientesSelector,
        ])->layout('layouts.sistema');
    }
}
