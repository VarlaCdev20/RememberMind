<?php

namespace App\Modulos\Valoraciones\Presentacion\Livewire\Paginas;

use App\Models\Atencion;
use App\Models\Residente;
use App\Modulos\Valoraciones\Aplicacion\Acciones\RegistrarValoracionMedica;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ValoracionMedicaPanel extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filtroEstado = '';

    public string $filtroResult = '';

    public bool $modalForm = false;

    public bool $modalVer = false;

    public ?string $editandoId = null;

    public ?string $viendoId = null;

    public string $codResidente = '';

    public ?string $codValEnf = null;

    public string $fecha = '';

    public string $hora = '';

    public string $diagnosticosReferidos = '';

    public string $antecedentesRelevantes = '';

    public string $medicacionActualResumen = '';

    public string $alergiasReferidas = '';

    public string $condicionMedicaGeneral = '';

    public string $estadoNeurologicoBasico = '';

    public string $nivelDependenciaSugerido = '';

    public string $resultadoAdmision = '';

    public string $motivoDecision = '';

    public string $recomendacionMedica = '';

    public bool $requiereSeguimientoEsp = false;

    public string $estadoForm = 'BORRADOR';

    protected $queryString = [
        'search' => ['except' => ''],
        'filtroEstado' => ['except' => ''],
        'filtroResult' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroResult(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->search = '';
        $this->filtroEstado = '';
        $this->filtroResult = '';
        $this->resetPage();
    }

    public function limpiarFiltro(string $campo): void
    {
        if ($campo === 'search') {
            $this->search = '';
        } elseif ($campo === 'filtroEstado') {
            $this->filtroEstado = '';
        } elseif ($campo === 'filtroResult') {
            $this->filtroResult = '';
        }
        $this->resetPage();
    }

    public function abrirCrear(): void
    {
        abort_unless($this->puedeRegistrar(), 403);
        $this->reset('editandoId', 'codResidente', 'diagnosticosReferidos', 'antecedentesRelevantes', 'medicacionActualResumen', 'alergiasReferidas', 'condicionMedicaGeneral', 'estadoNeurologicoBasico', 'nivelDependenciaSugerido', 'resultadoAdmision', 'motivoDecision', 'recomendacionMedica');
        $this->fecha = today()->format('Y-m-d');
        $this->hora = now()->format('H:i');
        $this->estadoForm = 'BORRADOR';
        $this->modalForm = true;
    }

    public function abrirVer(string $id): void
    {
        $this->viendoId = $id;
        $this->modalVer = true;
    }

    public function guardar(): void
    {
        abort_unless($this->puedeRegistrar(), 403);

        $this->validate([
            'codResidente' => 'required|exists:residentes,cod_residente',
            'fecha' => 'required|date',
            'hora' => 'required',
            'resultadoAdmision' => 'required|in:ADMITIDO,NO_ADMITIDO,DERIVADO,OBSERVADO,CANCELADO',
            'motivoDecision' => 'required|string|min:10',
            'estadoForm' => 'required|in:BORRADOR,COMPLETADA',
        ], [
            'codResidente.required' => 'Seleccione un residente.',
            'codResidente.exists' => 'El residente seleccionado no existe.',
            'resultadoAdmision.required' => 'El resultado de admisión es obligatorio.',
            'motivoDecision.required' => 'El motivo de la decisión es obligatorio.',
            'motivoDecision.min' => 'Describa el motivo con al menos 10 caracteres.',
        ]);

        app(RegistrarValoracionMedica::class)->ejecutar(auth()->user(), [
            'diagnosticosReferidos' => $this->diagnosticosReferidos,
            'antecedentesRelevantes' => $this->antecedentesRelevantes,
            'medicacionActualResumen' => $this->medicacionActualResumen,
            'condicionMedicaGeneral' => $this->condicionMedicaGeneral,
            'estadoNeurologicoBasico' => $this->estadoNeurologicoBasico,
            'nivelDependenciaSugerido' => $this->nivelDependenciaSugerido,
            'resultadoAdmision' => $this->resultadoAdmision,
            'motivoDecision' => $this->motivoDecision,
            'recomendacionMedica' => $this->recomendacionMedica,
            'fecha' => $this->fecha,
            'hora' => $this->hora,
            'estadoForm' => $this->estadoForm,
            'editandoId' => $this->editandoId,
            'codResidente' => $this->codResidente,            ]);

        $this->modalForm = false;
        $this->reset('editandoId', 'codResidente', 'fecha', 'hora', 'resultadoAdmision', 'motivoDecision', 'estadoForm');
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Valoración médica guardada correctamente.']);
    }

    public function puedeRegistrar(): bool
    {
        return app(RegistrarValoracionMedica::class)->puedeRegistrar(Auth::user());
    }

    public function cerrarModales(): void
    {
        $this->modalForm = false;
        $this->modalVer = false;
        $this->viendoId = null;
        $this->resetValidation();
    }

    public function render()
    {
        $valoraciones = Atencion::with(['residente', 'personal'])
            ->whereIn('tipo_atencion', ['VALORACION_MEDICA', 'MEDICA'])
            ->when($this->search, fn ($q) => $q->whereHas('residente', fn ($sq) => $sq->where('nombres', 'like', '%'.$this->search.'%')
                ->orWhere('apellido_paterno', 'like', '%'.$this->search.'%')
                ->orWhere('apellido_materno', 'like', '%'.$this->search.'%')
                ->orWhere('numero_documento', 'like', '%'.$this->search.'%')
            )
            )
            ->when($this->filtroEstado, fn ($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->filtroResult, fn ($q) => $q->where('motivo', 'like', '%'.$this->filtroResult.'%'))
            ->orderByDesc('fecha_hora')
            ->paginate(12);

        return view('livewire.valoraciones.valoracion-medica-panel', [
            'valoraciones' => $valoraciones,
            'adultos' => Residente::select('cod_residente', 'nombres', 'apellido_paterno', 'apellido_materno')
                ->where('estado', '!=', 'ARCHIVADO')
                ->orderBy('apellido_paterno')
                ->get(),
            'detalle' => $this->viendoId
                ? Atencion::with(['residente', 'personal'])->find($this->viendoId)
                : null,
        ])->layout('layouts.sistema');
    }
}
