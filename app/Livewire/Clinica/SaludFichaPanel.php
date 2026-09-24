<?php

namespace App\Livewire\Clinica;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Residente;
use App\Services\Clinica\FichaMedicaService;
use Illuminate\Support\Facades\DB;

class SaludFichaPanel extends Component
{
    use WithPagination;

    public ?Residente $adulto = null;
    public string $adultoSeleccionado = '';
    public string $buscarPaciente = '';
    public string $searchGeneral = '';
    public string $filtroEstado = 'todas';
    public ?object $fichaActiva = null;
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

    public function mount(?Residente $adulto = null)
    {
        if ($adulto && $adulto->exists) {
            $this->cargarAdulto($adulto->cod_residente);
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

    private function cargarAdulto(string $codResidente): void
    {
        $this->adulto = Residente::findOrFail($codResidente);
        $this->adultoSeleccionado = $this->adulto->cod_residente;
        $this->loadData();
    }

    public function loadData()
    {
        if ($this->adulto) {
            $service = app(FichaMedicaService::class);
            $this->fichaActiva = $service->obtenerFichaAgregada($this->adulto->cod_residente);
            $this->historialFichas = [];
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
                'text' => 'Seleccione un residente antes de buscar.',
            ]);
            return;
        }

        $adultoExistente = Residente::find($this->adultoSeleccionado);
        if (!$adultoExistente) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No encontrado',
                'text' => 'El residente seleccionado no existe.',
            ]);
            return;
        }

        $this->cargarAdulto($this->adultoSeleccionado);
        
        if (!$this->fichaActiva) {
            $this->dispatch('swal', [
                'icon' => 'info',
                'title' => 'Sin ficha médica',
                'text' => 'No existe expediente clínico registrado para este residente.',
            ]);
        }
    }

    public function getPacientesSelectorProperty()
    {
        $query = Residente::query()
            ->orderBy('nombres')
            ->orderBy('apellido_paterno');

        if (trim($this->buscarPaciente) !== '') {
            $busqueda = '%' . trim($this->buscarPaciente) . '%';
            $query->where(function ($subQuery) use ($busqueda) {
                $subQuery->where('cod_residente', 'like', $busqueda)
                    ->orWhere('nombres', 'like', $busqueda)
                    ->orWhere('apellido_paterno', 'like', $busqueda)
                    ->orWhere('apellido_materno', 'like', $busqueda)
                    ->orWhere('numero_documento', 'like', $busqueda);
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

    public function fillForm(object $ficha)
    {
        $this->hipertension = (bool) ($ficha->hipertension ?? false);
        $this->diabetes = (bool) ($ficha->diabetes ?? false);
        $this->problemas_cardiacos = (bool) ($ficha->problemas_cardiacos ?? false);
        $this->acv = (bool) ($ficha->acv ?? false);
        $this->parkinson = (bool) ($ficha->parkinson ?? false);
        $this->epilepsia = (bool) ($ficha->epilepsia ?? false);
        $this->alzheimer_diagnosticado = (bool) ($ficha->alzheimer_diagnosticado ?? false);
        $this->depresion = (bool) ($ficha->depresion ?? false);
        $this->ansiedad = (bool) ($ficha->ansiedad ?? false);
        $this->problemas_sueno = (bool) ($ficha->problemas_sueno ?? false);
        $this->problemas_visuales = (bool) ($ficha->problemas_visuales ?? false);
        $this->problemas_auditivos = (bool) ($ficha->problemas_auditivos ?? false);
        $this->dolor_cronico = (bool) ($ficha->dolor_cronico ?? false);
        
        $this->alergias = (string) ($ficha->alergias ?? '');
        $this->restricciones_alimentarias = (string) ($ficha->restricciones_alimentarias ?? '');
        $this->hospitalizaciones = (string) ($ficha->hospitalizaciones ?? '');
        $this->cirugias = (string) ($ficha->cirugias ?? '');
        $this->observacion_medica = (string) ($ficha->observacion_medica ?? '');
    }

    public function save()
    {
        if (!auth()->user()->can('atenciones.crear') && !auth()->user()->can('atenciones.editar')) {
            abort(403);
        }

        try {
            $data = [
                'cod_residente' => $this->adulto->cod_residente,
                'hipertension' => (bool) $this->hipertension,
                'diabetes' => (bool) $this->diabetes,
                'problemas_cardiacos' => (bool) $this->problemas_cardiacos,
                'acv' => (bool) $this->acv,
                'parkinson' => (bool) $this->parkinson,
                'epilepsia' => (bool) $this->epilepsia,
                'alzheimer_diagnosticado' => (bool) $this->alzheimer_diagnosticado,
                'depresion' => (bool) $this->depresion,
                'ansiedad' => (bool) $this->ansiedad,
                'problemas_sueno' => (bool) $this->problemas_sueno,
                'problemas_visuales' => (bool) $this->problemas_visuales,
                'problemas_auditivos' => (bool) $this->problemas_auditivos,
                'dolor_cronico' => (bool) $this->dolor_cronico,
                'alergias' => $this->alergias,
                'restricciones_alimentarias' => $this->restricciones_alimentarias,
                'hospitalizaciones' => $this->hospitalizaciones,
                'cirugias' => $this->cirugias,
                'observacion_medica' => $this->observacion_medica,
                'estado' => 'ACTIVA',
            ];

            $service = app(FichaMedicaService::class);
            $this->fichaActiva = $service->guardarFicha($this->adulto->cod_residente, $data, auth()->user()->cod_usuario);

            $this->closeModal();
            $this->loadData();

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Ficha médica actualizada',
                'text' => 'Los registros clínicos fueron guardados correctamente en la base de datos.',
            ]);

        } catch (\Exception $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error al guardar',
                'text' => 'Ocurrió un error: ' . $e->getMessage(),
            ]);
        }
    }

    public function archivar()
    {
        if (!auth()->user()->can('atenciones.anular')) {
            abort(403);
        }

        $this->dispatch('swal', [
            'icon' => 'info',
            'title' => 'Registro preservado',
            'text' => 'Los registros clínicos en V2 son inmutables para fines de auditoría.',
        ]);
    }

    public function render()
    {
        $service = app(FichaMedicaService::class);

        if (!$this->adulto) {
            $query = Residente::query();

            if (trim($this->searchGeneral) !== '') {
                $busqueda = '%' . trim($this->searchGeneral) . '%';
                $query->where(function ($subQuery) use ($busqueda) {
                    $subQuery->where('nombres', 'like', $busqueda)
                        ->orWhere('apellido_paterno', 'like', $busqueda)
                        ->orWhere('apellido_materno', 'like', $busqueda)
                        ->orWhere('numero_documento', 'like', $busqueda);
                });
            }

            if ($this->filtroEstado === 'con_ficha') {
                $query->where(function ($q) {
                    $q->whereHas('atenciones')
                      ->orWhereHas('diagnosticos')
                      ->orWhereHas('antecedentesClinicos')
                      ->orWhereHas('alergias');
                });
            } elseif ($this->filtroEstado === 'sin_ficha') {
                $query->whereDoesntHave('atenciones')
                      ->whereDoesntHave('diagnosticos')
                      ->whereDoesntHave('antecedentesClinicos')
                      ->whereDoesntHave('alergias');
            }

            $pacientesGeneral = $query->paginate(12);
            $stats = $service->contarEstadisticas();

            return view('livewire.clinica.salud-ficha-general', [
                'pacientes' => $pacientesGeneral,
                'stats' => $stats
            ]);
        }

        return view('livewire.clinica.salud-ficha-panel', [
            'pacientesSelector' => $this->pacientesSelector,
        ])->layout('layouts.sistema');
    }
}
