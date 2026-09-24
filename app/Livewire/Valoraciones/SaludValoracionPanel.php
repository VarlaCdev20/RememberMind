<?php

namespace App\Livewire\Valoraciones;

use App\Models\AdultoMayor;
use App\Models\Atencion;
use App\Models\ValoracionFuncional;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class SaludValoracionPanel extends Component
{
    use WithPagination;

    public AdultoMayor $adulto;
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

    public function mount(AdultoMayor $adulto): void
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

    public function updatedFiltroEstado(): void { $this->resetPage(); }
    public function updatedFiltroRiesgo(): void { $this->resetPage(); }
    public function updatedFechaDesde(): void { $this->resetPage(); }
    public function updatedFechaHasta(): void { $this->resetPage(); }

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

        DB::transaction(function (): void {
            if ($this->editandoId) {
                $valoracion = $this->valoracion($this->editandoId);
                $valoracion->update($this->payload($valoracion->cod_personal, $valoracion->cod_atencion, $valoracion->cod_valoracion_funcional));
                return;
            }

            [$personal, $codArea] = $this->contextoPersonal();
            ValoracionFuncional::query()
                ->where('cod_residente', $this->adulto->cod_residente)
                ->whereIn('estado', ['ACTIVA', 'VIGENTE'])
                ->update(['estado' => 'HISTORICA']);

            $atencion = Atencion::query()->create([
                'cod_atencion' => 'ATN_' . Str::upper(Str::random(10)),
                'cod_residente' => $this->adulto->cod_residente,
                'cod_area' => $codArea,
                'cod_personal' => $personal->cod_personal,
                'tipo_atencion' => 'VALORACION_FUNCIONAL',
                'motivo' => $this->nivel_dependencia,
                'fecha_hora' => $this->fecha_valoracion . ' ' . now()->format('H:i:s'),
                'estado' => 'FINALIZADA',
                'observacion' => $this->observacion ?: null,
            ]);

            ValoracionFuncional::query()->create($this->payload($personal->cod_personal, $atencion->cod_atencion));
        });

        $this->cerrarModales();
        $this->dispatch('swal:success', ['title' => 'Valoración guardada', 'text' => 'La valoración funcional se guardó en BDD V2.']);
    }

    public function confirmarAnular(): void
    {
        $this->autorizar('valoraciones_funcionales.editar');
        $this->validate(['motivo_anulacion' => ['required', 'string', 'min:10', 'max:500']]);
        $valoracion = $this->valoracion((string) $this->anulandoId);
        $valoracion->update([
            'estado' => 'ANULADA',
            'conclusion' => trim((string) $valoracion->conclusion . "\nAnulada: {$this->motivo_anulacion}"),
        ]);
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
        DB::transaction(function () use ($id): void {
            ValoracionFuncional::query()
                ->where('cod_residente', $this->adulto->cod_residente)
                ->whereIn('estado', ['ACTIVA', 'VIGENTE'])
                ->update(['estado' => 'HISTORICA']);
            $this->valoracion($id)->update(['estado' => 'ACTIVA']);
        });
    }

    private function payload(string $codPersonal, string $codAtencion, ?string $id = null): array
    {
        $autonomia = fn (bool $valor): string => $valor ? 'INDEPENDIENTE' : 'REQUIERE_APOYO';
        $apoyo = $this->usa_silla_ruedas ? 'SILLA_RUEDAS' : ($this->usa_andador ? 'ANDADOR' : ($this->usa_baston ? 'BASTON' : 'SIN_APOYO'));

        return [
            'cod_valoracion_funcional' => $id ?? 'VAF_' . Str::upper(Str::random(10)),
            'cod_residente' => $this->adulto->cod_residente,
            'cod_personal' => $codPersonal,
            'cod_atencion' => $codAtencion,
            'fecha_hora' => $this->fecha_valoracion . ' ' . now()->format('H:i:s'),
            'marcha' => $this->camina_solo ? 'INDEPENDIENTE' : 'ASISTIDA',
            'equilibrio' => $apoyo,
            'traslado' => $this->usa_silla_ruedas ? 'SILLA_RUEDAS' : $autonomia($this->camina_solo),
            'alimentacion_autonoma' => $autonomia($this->come_solo),
            'bano_autonomo' => $autonomia($this->se_bana_solo),
            'vestido_autonomo' => $autonomia($this->se_viste_solo),
            'higiene_autonoma' => $autonomia($this->se_bana_solo),
            'continencia' => $autonomia($this->va_bano_solo),
            'movilidad_autonoma' => $autonomia($this->camina_solo),
            'necesita_supervision' => $this->necesita_supervision,
            'nivel_dependencia' => $this->nivel_dependencia,
            'conclusion' => trim(($this->indice_barthel !== '' ? "Barthel {$this->indice_barthel}/100. " : '')
                . "Riesgo de caída: {$this->riesgo_caida}. " . $this->observacion),
            'estado' => 'ACTIVA',
        ];
    }

    private function fillFormDesde(ValoracionFuncional $valoracion): void
    {
        $this->fecha_valoracion = $valoracion->fecha_hora->toDateString();
        foreach (['come_solo','se_bana_solo','se_viste_solo','va_bano_solo','camina_solo','usa_baston','usa_andador','usa_silla_ruedas','necesita_supervision'] as $campo) {
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

    private function contextoPersonal(): array
    {
        $personal = auth()->user()?->personal;
        $codArea = $personal?->asignaciones()->whereIn('estado', ['ACTIVA', 'ACTIVO'])->latest('fecha_asignacion')->value('cod_area');
        abort_unless($personal && $codArea, 422, 'El usuario debe tener personal y área institucional activa.');
        return [$personal, $codArea];
    }

    private function autorizar(string $permiso): void
    {
        abort_unless(auth()->user()?->can($permiso), 403);
    }

    private function resetForm(): void
    {
        $this->reset([
            'come_solo','se_bana_solo','se_viste_solo','va_bano_solo','camina_solo','usa_baston','usa_andador','usa_silla_ruedas',
            'baja_vision','baja_audicion','dificultad_hablar','molestia_luz','molestia_ruido','se_asusta_facil','necesita_supervision',
            'nivel_dependencia','riesgo_caida','indice_barthel','observacion','motivo_anulacion',
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
