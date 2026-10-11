<?php

namespace App\Modulos\Valoraciones\Presentacion\Livewire\Paginas;

use App\Models\Residente;
use App\Models\ValoracionFuncional;
use App\Modulos\Valoraciones\Aplicacion\Acciones\GestionarValoracionFuncional;
use Livewire\Component;
use Livewire\WithPagination;

class SaludValoracionPanel extends Component
{
    use WithPagination;

    public Residente $adulto;

    public bool $modalFormOpen = false;

    public bool $modalDetalleOpen = false;

    public bool $modalAnularOpen = false;

    public ?string $editandoId = null;

    public ?string $viendoId = null;

    public ?string $anulandoId = null;

    public string $filtroEstado = '';

    public string $filtroRiesgo = '';

    public string $fechaDesde = '';

    public string $fechaHasta = '';

    public string $fecha_valoracion = '';

    public bool $come_solo = false;

    public bool $se_bana_solo = false;

    public bool $se_viste_solo = false;

    public bool $va_bano_solo = false;

    public bool $camina_solo = false;

    public bool $usa_baston = false;

    public bool $usa_andador = false;

    public bool $usa_silla_ruedas = false;

    public bool $baja_vision = false;

    public bool $baja_audicion = false;

    public bool $dificultad_hablar = false;

    public bool $molestia_luz = false;

    public bool $molestia_ruido = false;

    public bool $se_asusta_facil = false;

    public bool $necesita_supervision = false;

    public string $nivel_dependencia = '';

    public string $riesgo_caida = '';

    public string $indice_barthel = '';

    public string $observacion = '';

    public string $motivo_anulacion = '';

    public function mount(Residente $adulto): void
    {
        $this->adulto = $adulto;
        $this->fecha_valoracion = today()->toDateString();
    }

    protected function rules(): array
    {
        return [
            'fecha_valoracion' => ['required', 'date', 'before_or_equal:today'],
            'nivel_dependencia' => ['required', 'in:INDEPENDIENTE,DEPENDENCIA_PARCIAL,ALTA_DEPENDENCIA,SUPERVISION_PERMANENTE'],
            'riesgo_caida' => ['required', 'in:BAJO,MEDIO,ALTO'],
            'indice_barthel' => ['nullable', 'integer', 'min:0', 'max:100'],
            'observacion' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function updatedFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroRiesgo(): void
    {
        $this->resetPage();
    }

    public function updatedFechaDesde(): void
    {
        $this->resetPage();
    }

    public function updatedFechaHasta(): void
    {
        $this->resetPage();
    }

    public function abrirFormNuevo(): void
    {
        $this->autorizar('valoraciones_funcionales.crear');
        $this->resetForm();
        $this->modalFormOpen = true;
    }

    public function abrirFormEditar(string $id): void
    {
        $this->autorizar('valoraciones_funcionales.editar');
        $valoracion = $this->valoracion($id);
        abort_if($valoracion->estado === 'ANULADA', 422, 'No se puede editar una valoración anulada.');
        $this->fillFormDesde($valoracion);
        $this->editandoId = $id;
        $this->modalFormOpen = true;
    }

    public function abrirDetalle(string $id): void
    {
        $this->viendoId = $id;
        $this->modalDetalleOpen = true;
    }

    public function abrirAnular(string $id): void
    {
        $this->autorizar('valoraciones_funcionales.editar');
        $this->anulandoId = $id;
        $this->motivo_anulacion = '';
        $this->modalAnularOpen = true;
    }

    public function cerrarModales(): void
    {
        $this->modalFormOpen = false;
        $this->modalDetalleOpen = false;
        $this->modalAnularOpen = false;
        $this->editandoId = $this->viendoId = $this->anulandoId = null;
        $this->resetForm();
    }

    public function guardar(): void
    {
        $this->validate();
        $this->autorizar($this->editandoId ? 'valoraciones_funcionales.editar' : 'valoraciones_funcionales.crear');

        app(GestionarValoracionFuncional::class)->guardar(auth()->user(), [
            'editandoId' => $this->editandoId,
            'adulto' => $this->adulto,
            'nivel_dependencia' => $this->nivel_dependencia,
            'fecha_valoracion' => $this->fecha_valoracion,
            'observacion' => $this->observacion,
            'usa_silla_ruedas' => $this->usa_silla_ruedas,
            'usa_andador' => $this->usa_andador,
            'usa_baston' => $this->usa_baston,
            'camina_solo' => $this->camina_solo,
            'come_solo' => $this->come_solo,
            'se_bana_solo' => $this->se_bana_solo,
            'se_viste_solo' => $this->se_viste_solo,
            'va_bano_solo' => $this->va_bano_solo,
            'necesita_supervision' => $this->necesita_supervision,
            'indice_barthel' => $this->indice_barthel,
            'riesgo_caida' => $this->riesgo_caida,
        ]);

        $this->cerrarModales();
        $this->dispatch('swal:success', ['title' => 'Valoración guardada', 'text' => 'La valoración funcional se guardó en BDD V2.']);
    }

    public function confirmarAnular(): void
    {
        $this->autorizar('valoraciones_funcionales.editar');
        $this->validate(['motivo_anulacion' => ['required', 'string', 'min:10', 'max:500']]);
        app(GestionarValoracionFuncional::class)->anular(auth()->user(), ['adulto' => $this->adulto, 'anulandoId' => $this->anulandoId, 'motivo_anulacion' => $this->motivo_anulacion]);
        $this->cerrarModales();
    }

    public function restaurar(string $id): void
    {
        $this->marcarComoVigente($id);
    }

    public function marcarVigente(string $id): void
    {
        $this->marcarComoVigente($id);
    }

    private function marcarComoVigente(string $id): void
    {
        $this->autorizar('valoraciones_funcionales.editar');
        app(GestionarValoracionFuncional::class)->marcarVigente(auth()->user(), $this->adulto, $id);
    }

    private function fillFormDesde(ValoracionFuncional $valoracion): void
    {
        $this->fecha_valoracion = $valoracion->fecha_hora->toDateString();
        foreach (['come_solo', 'se_bana_solo', 'se_viste_solo', 'va_bano_solo', 'camina_solo', 'usa_baston', 'usa_andador', 'usa_silla_ruedas', 'necesita_supervision'] as $campo) {
            $this->{$campo} = (bool) $valoracion->{$campo};
        }
        $this->nivel_dependencia = (string) $valoracion->nivel_dependencia;
        $this->riesgo_caida = $valoracion->riesgo_caida;
        $this->indice_barthel = $valoracion->indice_barthel !== null ? (string) $valoracion->indice_barthel : '';
        $this->observacion = (string) $valoracion->observacion;
    }

    private function valoracion(string $id): ValoracionFuncional
    {
        return ValoracionFuncional::query()
            ->where('cod_residente', $this->adulto->cod_residente)
            ->findOrFail($id);
    }

    private function autorizar(string $permiso): void
    {
        abort_unless(auth()->user()?->can($permiso), 403);
    }

    private function resetForm(): void
    {
        $this->reset([
            'come_solo', 'se_bana_solo', 'se_viste_solo', 'va_bano_solo', 'camina_solo', 'usa_baston', 'usa_andador', 'usa_silla_ruedas',
            'baja_vision', 'baja_audicion', 'dificultad_hablar', 'molestia_luz', 'molestia_ruido', 'se_asusta_facil', 'necesita_supervision',
            'nivel_dependencia', 'riesgo_caida', 'indice_barthel', 'observacion', 'motivo_anulacion',
        ]);
        $this->fecha_valoracion = today()->toDateString();
        $this->resetValidation();
    }

    public function render()
    {
        $query = $this->adulto->valoracionesFuncionales()
            ->when($this->filtroEstado, fn ($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->filtroRiesgo, fn ($q) => $q->where('conclusion', 'like', "%Riesgo de caída: {$this->filtroRiesgo}%"))
            ->when($this->fechaDesde, fn ($q) => $q->whereDate('fecha_hora', '>=', $this->fechaDesde))
            ->when($this->fechaHasta, fn ($q) => $q->whereDate('fecha_hora', '<=', $this->fechaHasta));

        return view('livewire.valoraciones.salud-valoracion-funcional', [
            'valoraciones' => (clone $query)->with('registradoPor')->latest('fecha_hora')->paginate(10),
            'vigente' => $this->adulto->valoracionesFuncionales()->vigente()->latest('fecha_hora')->first(),
            'anterior' => $this->adulto->valoracionesFuncionales()->historica()->latest('fecha_hora')->first(),
            'totalRegistros' => $this->adulto->valoracionesFuncionales()->count(),
            'viendoDetalle' => $this->viendoId ? ValoracionFuncional::with('registradoPor')->find($this->viendoId) : null,
        ])->layout('layouts.sistema');
    }
}
