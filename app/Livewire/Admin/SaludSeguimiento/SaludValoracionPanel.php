<?php

namespace App\Livewire\Admin\SaludSeguimiento;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AdultoMayor;
use App\Models\ValoracionFuncionalAdulto;
use Illuminate\Support\Facades\DB;

class SaludValoracionPanel extends Component
{
    use WithPagination;

    public AdultoMayor $adulto;

    // ── Estado de modales ────────────────────────────────────────────────────
    public bool $modalFormOpen     = false;
    public bool $modalDetalleOpen  = false;
    public bool $modalAnularOpen   = false;

    public ?int $editandoId  = null;
    public ?int $viendoId    = null;
    public ?int $anulandoId  = null;

    // ── Filtros ──────────────────────────────────────────────────────────────
    public string $filtroEstado = '';
    public string $filtroRiesgo = '';
    public string $fechaDesde   = '';
    public string $fechaHasta   = '';

    // ── Campos del formulario ─────────────────────────────────────────────────
    public string $fecha_valoracion = '';

    public bool $come_solo          = false;
    public bool $se_bana_solo       = false;
    public bool $se_viste_solo      = false;
    public bool $va_bano_solo       = false;
    public bool $camina_solo        = false;
    public bool $usa_baston         = false;
    public bool $usa_andador        = false;
    public bool $usa_silla_ruedas   = false;
    public bool $baja_vision        = false;
    public bool $baja_audicion      = false;
    public bool $dificultad_hablar  = false;
    public bool $molestia_luz       = false;
    public bool $molestia_ruido     = false;
    public bool $se_asusta_facil    = false;
    public bool $necesita_supervision = false;

    public string $nivel_dependencia = '';
    public string $riesgo_caida      = '';
    public string $indice_barthel    = '';
    public string $observacion       = '';

    // ── Anulación ────────────────────────────────────────────────────────────
    public string $motivo_anulacion = '';

    // ── Reglas de validación ─────────────────────────────────────────────────
    protected function rules(): array
    {
        return [
            'fecha_valoracion'  => ['required', 'date'],
            'nivel_dependencia' => ['required', 'in:INDEPENDIENTE,DEPENDENCIA_PARCIAL,ALTA_DEPENDENCIA,SUPERVISION_PERMANENTE'],
            'riesgo_caida'      => ['required', 'in:BAJO,MEDIO,ALTO'],
            'indice_barthel'    => ['nullable', 'integer', 'min:0', 'max:100'],
            'observacion'       => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected $messages = [
        'fecha_valoracion.required'  => 'La fecha de valoración es obligatoria.',
        'nivel_dependencia.required' => 'El nivel de dependencia es obligatorio.',
        'nivel_dependencia.in'       => 'Seleccione un nivel de dependencia válido.',
        'riesgo_caida.required'      => 'El riesgo de caída es obligatorio.',
        'riesgo_caida.in'            => 'Seleccione un riesgo de caída válido.',
        'indice_barthel.integer'     => 'El índice Barthel debe ser un número entero.',
        'indice_barthel.min'         => 'El índice Barthel mínimo es 0.',
        'indice_barthel.max'         => 'El índice Barthel máximo es 100.',
    ];

    // ── Ciclo de vida ─────────────────────────────────────────────────────────
    public function mount(AdultoMayor $adulto): void
    {
        $this->adulto           = $adulto;
        $this->fecha_valoracion = today()->format('Y-m-d');
    }

    public function updatedFiltroEstado(): void { $this->resetPage(); }
    public function updatedFiltroRiesgo(): void { $this->resetPage(); }
    public function updatedFechaDesde(): void   { $this->resetPage(); }
    public function updatedFechaHasta(): void   { $this->resetPage(); }

    // ── Apertura de modales ───────────────────────────────────────────────────
    public function abrirFormNuevo(): void
    {
        if (!auth()->user()->can('salud.valoracion.crear')) abort(403);
        $this->resetForm();
        $this->editandoId   = null;
        $this->modalFormOpen = true;
    }

    public function abrirFormEditar(int $id): void
    {
        if (!auth()->user()->can('salud.valoracion.editar')) abort(403);

        $valoracion = ValoracionFuncionalAdulto::where('cod_am', $this->adulto->cod_am)->findOrFail($id);

        if ($valoracion->estado === 'ANULADA') {
            $this->dispatch('swal:warning', [
                'title' => 'No permitido',
                'text'  => 'No se puede editar una valoración anulada. Use "Restaurar" si necesita reactivarla.',
            ]);
            return;
        }

        $this->fillFormDesde($valoracion);
        $this->editandoId    = $id;
        $this->modalFormOpen = true;
    }

    public function abrirDetalle(int $id): void
    {
        $this->viendoId         = $id;
        $this->modalDetalleOpen = true;
    }

    public function abrirAnular(int $id): void
    {
        if (!auth()->user()->can('salud.valoracion.anular')) abort(403);
        $this->anulandoId      = $id;
        $this->motivo_anulacion = '';
        $this->modalAnularOpen  = true;
    }

    public function cerrarModales(): void
    {
        $this->modalFormOpen    = false;
        $this->modalDetalleOpen = false;
        $this->modalAnularOpen  = false;
        $this->resetForm();
        $this->editandoId  = null;
        $this->viendoId    = null;
        $this->anulandoId  = null;
    }

    // ── CRUD ──────────────────────────────────────────────────────────────────
    public function guardar(): void
    {
        $this->validate();

        if ($this->editandoId) {
            if (!auth()->user()->can('salud.valoracion.editar')) abort(403);
        } else {
            if (!auth()->user()->can('salud.valoracion.crear')) abort(403);
        }

        DB::beginTransaction();
        try {
            $data = [
                'cod_am'               => $this->adulto->cod_am,
                'fecha_valoracion'     => $this->fecha_valoracion,
                'come_solo'            => $this->come_solo,
                'se_bana_solo'         => $this->se_bana_solo,
                'se_viste_solo'        => $this->se_viste_solo,
                'va_bano_solo'         => $this->va_bano_solo,
                'camina_solo'          => $this->camina_solo,
                'usa_baston'           => $this->usa_baston,
                'usa_andador'          => $this->usa_andador,
                'usa_silla_ruedas'     => $this->usa_silla_ruedas,
                'baja_vision'          => $this->baja_vision,
                'baja_audicion'        => $this->baja_audicion,
                'dificultad_hablar'    => $this->dificultad_hablar,
                'molestia_luz'         => $this->molestia_luz,
                'molestia_ruido'       => $this->molestia_ruido,
                'se_asusta_facil'      => $this->se_asusta_facil,
                'necesita_supervision' => $this->necesita_supervision,
                'nivel_dependencia'    => $this->nivel_dependencia,
                'riesgo_caida'         => $this->riesgo_caida,
                'indice_barthel'       => $this->indice_barthel !== '' ? (int) $this->indice_barthel : null,
                'observacion'          => $this->observacion ?: null,
                'registrado_por'       => auth()->user()->cod_usu,
            ];

            if ($this->editandoId) {
                $valoracion = ValoracionFuncionalAdulto::where('cod_am', $this->adulto->cod_am)
                    ->findOrFail($this->editandoId);
                $valoracion->update($data);
                $mensajeBitacora = "Actualizó valoración funcional del adulto mayor {$this->adulto->cod_am}.";
                $titulo = 'Valoración Actualizada';
                $texto  = 'Los cambios fueron guardados correctamente.';
            } else {
                // Pasar todas las valoraciones VIGENTE del adulto a HISTORICA
                ValoracionFuncionalAdulto::where('cod_am', $this->adulto->cod_am)
                    ->where('estado', 'VIGENTE')
                    ->update(['estado' => 'HISTORICA']);

                $data['estado'] = 'VIGENTE';
                $valoracion = ValoracionFuncionalAdulto::create($data);
                $mensajeBitacora = $this->riesgo_caida === 'ALTO'
                    ? "Registró valoración funcional con riesgo de caída ALTO del adulto mayor {$this->adulto->cod_am}."
                    : "Registró valoración funcional del adulto mayor {$this->adulto->cod_am}.";
                $titulo = 'Valoración Registrada';
                $texto  = 'La valoración funcional fue registrada correctamente.';
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($valoracion)
                ->log($mensajeBitacora);

            DB::commit();
            $this->cerrarModales();
            $this->dispatch('swal:success', ['title' => $titulo, 'text' => $texto]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('swal:error', ['title' => 'Error', 'text' => 'No se pudo guardar: ' . $e->getMessage()]);
        }
    }

    public function confirmarAnular(): void
    {
        if (!auth()->user()->can('salud.valoracion.anular')) abort(403);

        $this->validate([
            'motivo_anulacion' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'motivo_anulacion.required' => 'El motivo de anulación es obligatorio.',
            'motivo_anulacion.min'      => 'Describa el motivo con al menos 10 caracteres.',
        ]);

        $valoracion = ValoracionFuncionalAdulto::where('cod_am', $this->adulto->cod_am)
            ->findOrFail($this->anulandoId);

        DB::beginTransaction();
        try {
            $valoracion->update([
                'estado'           => 'ANULADA',
                'motivo_anulacion' => $this->motivo_anulacion,
                'anulado_por'      => auth()->user()->cod_usu,
                'fecha_anulacion'  => now(),
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($valoracion)
                ->log("Anuló valoración funcional del adulto mayor {$this->adulto->cod_am}. Motivo: {$this->motivo_anulacion}");

            DB::commit();
            $this->cerrarModales();
            $this->dispatch('swal:success', [
                'title' => 'Valoración Anulada',
                'text'  => 'La valoración quedó registrada en el historial como anulada.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('swal:error', ['title' => 'Error', 'text' => $e->getMessage()]);
        }
    }

    public function restaurar(int $id): void
    {
        if (!auth()->user()->can('salud.valoracion.editar')) abort(403);

        $valoracion = ValoracionFuncionalAdulto::where('cod_am', $this->adulto->cod_am)->findOrFail($id);

        DB::beginTransaction();
        try {
            ValoracionFuncionalAdulto::where('cod_am', $this->adulto->cod_am)
                ->where('estado', 'VIGENTE')
                ->update(['estado' => 'HISTORICA']);

            $valoracion->update([
                'estado'           => 'VIGENTE',
                'motivo_anulacion' => null,
                'anulado_por'      => null,
                'fecha_anulacion'  => null,
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($valoracion)
                ->log("Restauró valoración funcional del adulto mayor {$this->adulto->cod_am}.");

            DB::commit();
            $this->dispatch('swal:success', [
                'title' => 'Restaurada',
                'text'  => 'La valoración fue restaurada como vigente.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('swal:error', ['title' => 'Error', 'text' => $e->getMessage()]);
        }
    }

    public function marcarVigente(int $id): void
    {
        if (!auth()->user()->can('salud.valoracion.editar')) abort(403);

        DB::beginTransaction();
        try {
            ValoracionFuncionalAdulto::where('cod_am', $this->adulto->cod_am)
                ->where('estado', 'VIGENTE')
                ->update(['estado' => 'HISTORICA']);

            $valoracion = ValoracionFuncionalAdulto::where('cod_am', $this->adulto->cod_am)->findOrFail($id);
            $valoracion->update(['estado' => 'VIGENTE']);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($valoracion)
                ->log("Marcó como vigente la valoración funcional del adulto mayor {$this->adulto->cod_am}.");

            DB::commit();
            $this->dispatch('swal:success', ['title' => 'Vigente marcada', 'text' => 'La valoración seleccionada es ahora la vigente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('swal:error', ['title' => 'Error', 'text' => $e->getMessage()]);
        }
    }

    // ── Helpers privados ──────────────────────────────────────────────────────
    private function fillFormDesde(ValoracionFuncionalAdulto $v): void
    {
        $this->fecha_valoracion    = $v->fecha_valoracion->format('Y-m-d');
        $this->come_solo           = $v->come_solo;
        $this->se_bana_solo        = $v->se_bana_solo;
        $this->se_viste_solo       = $v->se_viste_solo;
        $this->va_bano_solo        = $v->va_bano_solo;
        $this->camina_solo         = $v->camina_solo;
        $this->usa_baston          = $v->usa_baston;
        $this->usa_andador         = $v->usa_andador;
        $this->usa_silla_ruedas    = $v->usa_silla_ruedas;
        $this->baja_vision         = $v->baja_vision;
        $this->baja_audicion       = $v->baja_audicion;
        $this->dificultad_hablar   = $v->dificultad_hablar;
        $this->molestia_luz        = $v->molestia_luz;
        $this->molestia_ruido      = $v->molestia_ruido;
        $this->se_asusta_facil     = $v->se_asusta_facil;
        $this->necesita_supervision = $v->necesita_supervision;
        $this->nivel_dependencia   = $v->nivel_dependencia;
        $this->riesgo_caida        = $v->riesgo_caida ?? '';
        $this->indice_barthel      = $v->indice_barthel !== null ? (string) $v->indice_barthel : '';
        $this->observacion         = $v->observacion ?? '';
    }

    private function resetForm(): void
    {
        $this->reset([
            'come_solo', 'se_bana_solo', 'se_viste_solo', 'va_bano_solo', 'camina_solo',
            'usa_baston', 'usa_andador', 'usa_silla_ruedas',
            'baja_vision', 'baja_audicion', 'dificultad_hablar',
            'molestia_luz', 'molestia_ruido', 'se_asusta_facil', 'necesita_supervision',
            'nivel_dependencia', 'riesgo_caida', 'indice_barthel', 'observacion',
        ]);
        $this->fecha_valoracion = today()->format('Y-m-d');
        $this->resetValidation();
    }

    // ── Render ────────────────────────────────────────────────────────────────
    public function render()
    {
        $valoraciones = $this->adulto->valoracionesFuncionales()
            ->when($this->filtroEstado, fn ($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->filtroRiesgo, fn ($q) => $q->where('riesgo_caida', $this->filtroRiesgo))
            ->when($this->fechaDesde,   fn ($q) => $q->where('fecha_valoracion', '>=', $this->fechaDesde))
            ->when($this->fechaHasta,   fn ($q) => $q->where('fecha_valoracion', '<=', $this->fechaHasta))
            ->with(['registradoPor', 'anuladoPor'])
            ->latest('fecha_valoracion')
            ->paginate(10);

        $vigente = $this->adulto->valoracionesFuncionales()
            ->vigente()
            ->latest('fecha_valoracion')
            ->first();

        $anterior = $this->adulto->valoracionesFuncionales()
            ->historica()
            ->latest('fecha_valoracion')
            ->first();

        $viendoDetalle = $this->viendoId
            ? ValoracionFuncionalAdulto::with(['registradoPor', 'anuladoPor'])->find($this->viendoId)
            : null;

        return view('livewire.admin.salud-seguimiento.salud-valoracion-funcional', [
            'valoraciones'  => $valoraciones,
            'vigente'       => $vigente,
            'anterior'      => $anterior,
            'totalRegistros' => $this->adulto->valoracionesFuncionales()->count(),
            'viendoDetalle' => $viendoDetalle,
        ])->layout('layouts.sistema');
    }
}
