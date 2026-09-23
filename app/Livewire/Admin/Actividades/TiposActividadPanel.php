<?php

namespace App\Livewire\Admin\Actividades;

use App\Models\Actividad;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class TiposActividadPanel extends Component
{
    use WithPagination;

    public string $search    = '';
    public string $filtroUso = '';

    public bool $modalRegistrar = false;
    public bool $modalEditar    = false;
    public bool $modalDetalle   = false;

    public string $tipo        = '';
    public string $descripcion = '';

    public ?string $editandoId = null;
    public ?string $detalleId  = null;

    protected function rules(): array
    {
        return [
            'tipo' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:1000',
        ];
    }

    protected function messages(): array
    {
        return [
            'tipo.required'      => 'El nombre del tipo es obligatorio.',
            'tipo.max'           => 'El nombre no puede superar los 50 caracteres.',
            'descripcion.max'    => 'La descripción no puede superar los 1000 caracteres.',
        ];
    }

    public function updatingSearch(): void   { $this->resetPage(); }
    public function updatingFiltroUso(): void { $this->resetPage(); }

    public function limpiarFiltros(): void
    {
        $this->search    = '';
        $this->filtroUso = '';
        $this->resetPage();
    }

    public function abrirRegistrar(): void
    {
        $this->resetForm();
        $this->modalRegistrar = true;
    }

    public function abrirEditar(string $id): void
    {
        $this->editandoId  = $id;
        $this->tipo        = Str::headline($id);
        $this->descripcion = $this->catalogoDescripciones()[strtoupper($id)] ?? '';
        $this->resetValidation();
        $this->modalEditar = true;
    }

    public function abrirDetalle(string $id): void
    {
        $this->detalleId    = $id;
        $this->modalDetalle = true;
    }

    public function cerrarModales(): void
    {
        $this->modalRegistrar = false;
        $this->modalEditar    = false;
        $this->modalDetalle   = false;
        $this->resetForm();
    }

    public function guardarTipo(): void
    {
        $this->validate();
        $this->modalRegistrar = false;
        $this->resetForm();
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Tipo de actividad registrado en el catálogo institucional.']);
    }

    public function actualizarTipo(): void
    {
        $this->validate();
        if ($this->editandoId) {
            Actividad::where('tipo', strtoupper(trim($this->editandoId)))
                ->update(['tipo' => strtoupper(trim($this->tipo))]);
        }
        $this->modalEditar = false;
        $this->resetForm();
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Tipo de actividad actualizado correctamente.']);
    }

    public function eliminarTipo(string $id): void
    {
        $count = Actividad::where('tipo', strtoupper(trim($id)))->count();
        if ($count > 0) {
            $this->dispatch('swal', [
                'icon'  => 'warning',
                'title' => 'No es posible eliminar',
                'text'  => "Este tipo tiene {$count} actividad(es) asociada(s). Para eliminarlo del catálogo, primero reasigne o elimine las actividades vinculadas.",
            ]);
            return;
        }
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Tipo eliminado del catálogo institucional.']);
    }

    private function resetForm(): void
    {
        $this->tipo        = '';
        $this->descripcion = '';
        $this->editandoId  = null;
        $this->detalleId   = null;
        $this->resetValidation();
    }

    private function catalogoDescripciones(): array
    {
        return [
            'RECREATIVA'   => 'Actividades de esparcimiento, juegos de mesa, cine, dinámicas y entretenimiento.',
            'COGNITIVA'    => 'Talleres de memoria, estimulación mental, agilidad y lectura reflexiva.',
            'FISICA'       => 'Gimnasia suave, caminatas guiadas, movilidad articular y ejercicios adaptados.',
            'SOCIAL'       => 'Convivencias, celebraciones de fechas conmemorativas e integración grupal.',
            'EDUCATIVA'    => 'Charlas formativas, habilidades prácticas y difusión cultural.',
            'TERAPEUTICA'  => 'Sesiones de rehabilitación motriz, relajación y contención ocupacional.',
            'OCUPACIONAL'  => 'Manualidades, estimulación sensorial, jardinería y talleres artesanales.',
            'ESPIRITUAL'   => 'Momentos de oración, reflexión y acompañamiento espiritual voluntario.',
        ];
    }

    private function getStats(): array
    {
        $counts = Actividad::select('tipo', DB::raw('count(*) as total'))
            ->groupBy('tipo')
            ->pluck('total', 'tipo')
            ->toArray();

        $todosTipos = array_unique(array_merge(array_keys($this->catalogoDescripciones()), array_keys($counts)));
        $totalTipos = count($todosTipos);
        $totalAct   = array_sum($counts);
        $conAct     = count(array_filter($todosTipos, fn($t) => ($counts[$t] ?? 0) > 0));
        $sinAct     = $totalTipos - $conAct;

        $masNombre = '—';
        $masCount  = 0;
        if (!empty($counts)) {
            arsort($counts);
            $masNombre = Str::headline(key($counts));
            $masCount  = current($counts);
        }

        return [
            'total'             => $totalTipos,
            'con_actividades'   => $conAct,
            'sin_actividades'   => $sinAct,
            'total_actividades' => $totalAct,
            'mas_nombre'        => $masNombre,
            'mas_count'         => $masCount,
            'promedio'          => $totalTipos > 0 ? number_format($totalAct / $totalTipos, 1) : '0.0',
        ];
    }

    private function getTiposFiltrados(): LengthAwarePaginator
    {
        $counts = Actividad::select('tipo', DB::raw('count(*) as total'))
            ->groupBy('tipo')
            ->pluck('total', 'tipo')
            ->toArray();

        $catalogo = $this->catalogoDescripciones();
        $todos = array_unique(array_merge(array_keys($catalogo), array_keys($counts)));
        sort($todos);

        $coleccion = collect($todos)->map(function ($slug) use ($counts, $catalogo) {
            $nombre = Str::headline($slug);
            $desc   = $catalogo[$slug] ?? "Actividades institucionales del área de {$nombre}.";
            $cnt    = $counts[$slug] ?? 0;
            return (object) [
                'cod_tipo_act'      => $slug,
                'tipo'              => $nombre,
                'nombre'            => $nombre,
                'descripcion'       => $desc,
                'actividades_count' => $cnt,
                'estado'            => 'ACTIVO',
            ];
        });

        if ($this->search) {
            $s = mb_strtolower(trim($this->search));
            $coleccion = $coleccion->filter(fn($t) =>
                str_contains(mb_strtolower($t->tipo), $s) ||
                str_contains(mb_strtolower($t->descripcion), $s)
            );
        }

        if ($this->filtroUso === 'con') {
            $coleccion = $coleccion->filter(fn($t) => $t->actividades_count > 0);
        } elseif ($this->filtroUso === 'sin') {
            $coleccion = $coleccion->filter(fn($t) => $t->actividades_count === 0);
        }

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 12;
        $items = $coleccion->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $coleccion->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
    }

    private function getDetalle(): ?object
    {
        if (! $this->detalleId) {
            return null;
        }
        $slug = strtoupper(trim($this->detalleId));
        $catalogo = $this->catalogoDescripciones();
        $nombre = Str::headline($slug);
        $actividades = Actividad::where('tipo', $slug)
            ->with('adultoMayor')
            ->orderByDesc('fecha_hora')
            ->limit(5)
            ->get();

        return (object) [
            'cod_tipo_act'      => $slug,
            'tipo'              => $nombre,
            'nombre'            => $nombre,
            'descripcion'       => $catalogo[$slug] ?? "Actividades del área de {$nombre}.",
            'actividades_count' => Actividad::where('tipo', $slug)->count(),
            'actividades'       => $actividades,
        ];
    }

    public function render()
    {
        return view('livewire.actividades.tipos-actividad-panel', [
            'stats'  => $this->getStats(),
            'tipos'  => $this->getTiposFiltrados(),
            'detalle'=> $this->getDetalle(),
            'labelCls'=> 'block text-[10px] font-bold uppercase tracking-[0.15em] text-apoyo mb-1.5',
            'inputCls'=> 'w-full rounded-xl border border-borde-suave bg-fondo-app px-3 py-2 text-xs font-bold text-titulo outline-none transition focus:border-borde-focus',
            'resolverColorIcono' => fn($tipo) => [
                'cls' => 'bg-emerald-50 text-emerald-700',
                'icon' => 'ph-star',
            ],
        ])->layout('layouts.sistema');
    }
}