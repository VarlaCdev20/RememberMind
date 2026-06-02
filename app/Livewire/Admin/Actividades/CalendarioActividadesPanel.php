<?php

namespace App\Livewire\Admin\Actividades;

use App\Models\ActividadAdulto;
use App\Models\TipoActividadAdulto;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CalendarioActividadesPanel extends Component
{
    // ── Filtros del calendario ─────────────────────────────────────────────────
    public string $filtroTipo   = '';
    public string $filtroEstado = '';
    public string $buscar       = '';

    // ── Modal: nueva actividad desde dateClick ────────────────────────────────
    public bool   $modalNueva  = false;
    public string $nuevaFecha  = '';
    public string $nuevaHora   = '';
    public string $nuevaHoraFin= '';
    public string $nuevaNombre = '';
    public string $nuevaTipo   = '';
    public string $nuevaLugar  = '';
    public string $nuevaObjetivo = '';
    public string $nuevaCupo   = '';
    public string $nuevaMateriales = '';
    public string $nuevaEstado = 'PROGRAMADA';
    public string $nuevaObs    = '';

    // ── Modal: detalle de evento ──────────────────────────────────────────────
    public bool  $modalDetalle = false;
    public array $detalle      = [];

    // ── Validaciones formulario nueva actividad ───────────────────────────────

    protected function rulesNueva(): array
    {
        return [
            'nuevaTipo'    => 'required|exists:tipo_actividades_adulto,cod_tipo_act',
            'nuevaFecha'   => 'required|date',
            'nuevaHora'    => 'required',
            'nuevaHoraFin' => 'nullable|after:nuevaHora',
            'nuevaNombre'  => 'nullable|string|max:200',
            'nuevaLugar'   => 'nullable|string|max:200',
            'nuevaObjetivo'=> 'nullable|string|max:1000',
            'nuevaCupo'    => 'nullable|integer|min:1|max:500',
            'nuevaMateriales' => 'nullable|string|max:500',
            'nuevaObs'     => 'nullable|string|max:2000',
            'nuevaEstado'  => 'required|string|max:50',
        ];
    }

    protected function messagesNueva(): array
    {
        return [
            'nuevaTipo.required'  => 'Seleccione el tipo de actividad.',
            'nuevaTipo.exists'    => 'Tipo de actividad no válido.',
            'nuevaFecha.required' => 'La fecha es obligatoria.',
            'nuevaHora.required'  => 'La hora de inicio es obligatoria.',
            'nuevaHoraFin.after'  => 'La hora de fin debe ser posterior a la de inicio.',
            'nuevaCupo.integer'   => 'El cupo máximo debe ser un número entero.',
        ];
    }

    // ── Apertura de modal nueva actividad desde JS (dateClick) ────────────────

    #[\Livewire\Attributes\On('calendar-date-click')]
    public function abrirNuevaDesdeCalendario(array $params): void
    {
        $this->resetNuevaForm();
        $this->nuevaFecha  = $params['fecha'] ?? today()->format('Y-m-d');
        $this->nuevaHora   = $params['hora']  ?? '09:00';
        $this->nuevaEstado = 'PROGRAMADA';
        $this->modalNueva  = true;
    }

    // También escuchar desde ventana (para compatibilidad con el JS puro)
    public function abrirModalNueva(string $fecha = '', string $hora = ''): void
    {
        $this->resetNuevaForm();
        $this->nuevaFecha  = $fecha ?: today()->format('Y-m-d');
        $this->nuevaHora   = $hora  ?: '09:00';
        $this->nuevaEstado = 'PROGRAMADA';
        $this->modalNueva  = true;
    }

    // ── Guardar nueva actividad ───────────────────────────────────────────────

    public function guardarNuevaActividad(): void
    {
        $this->validate(
            $this->rulesNueva(),
            $this->messagesNueva()
        );

        $formatHora = fn(string $h) => strlen(trim($h)) === 5 ? $h . ':00' : $h;

        ActividadAdulto::create([
            'cod_tipo_act' => (int) $this->nuevaTipo,
            'nombre'       => $this->nuevaNombre ?: null,
            'fecha'        => $this->nuevaFecha,
            'hora'         => $formatHora($this->nuevaHora),
            'hora_fin'     => $this->nuevaHoraFin ? $formatHora($this->nuevaHoraFin) : null,
            'lugar'        => $this->nuevaLugar ?: null,
            'objetivo'     => $this->nuevaObjetivo ?: null,
            'cupo_maximo'  => $this->nuevaCupo ? (int) $this->nuevaCupo : null,
            'materiales'   => $this->nuevaMateriales ?: null,
            'estado'       => strtoupper(trim($this->nuevaEstado)),
            'obs'          => $this->nuevaObs ?: null,
            'created_by'   => Auth::id(),
            'updated_by'   => Auth::id(),
        ]);

        $this->modalNueva = false;
        $this->resetNuevaForm();

        // Disparar evento para que el JS refresque el calendario
        $this->dispatch('refetch-calendar-events');
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Actividad creada correctamente.']);
    }

    // ── Abrir modal detalle ───────────────────────────────────────────────────

    #[\Livewire\Attributes\On('calendar-event-detail')]
    public function abrirDetalle(array $data): void
    {
        $this->detalle     = $data;
        $this->modalDetalle = true;
    }

    // También para ser llamado desde el JS vía window event
    public function setDetalle(array $data): void
    {
        $this->detalle     = $data;
        $this->modalDetalle = true;
    }

    // ── Cancelar actividad desde detalle ──────────────────────────────────────

    public function cancelarActividad(int $id): void
    {
        $act = ActividadAdulto::find($id);
        if ($act && ! in_array(strtoupper($act->estado), ['CANCELADA', 'ANULADA', 'EVALUADA'])) {
            $act->update(['estado' => 'CANCELADA']);
        }
        $this->modalDetalle = false;
        $this->dispatch('refetch-calendar-events');
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Actividad cancelada. El historial se conserva.']);
    }

    // ── Cerrar modales ────────────────────────────────────────────────────────

    public function cerrarModales(): void
    {
        $this->modalNueva   = false;
        $this->modalDetalle = false;
        $this->detalle      = [];
        $this->resetNuevaForm();
        $this->resetValidation();
    }

    // ── Actualizar filtros (dispara refetch en JS) ────────────────────────────

    public function updatedFiltroTipo(): void    { $this->dispatch('filtros-actualizados', filtros: $this->getFiltros()); }
    public function updatedFiltroEstado(): void  { $this->dispatch('filtros-actualizados', filtros: $this->getFiltros()); }
    public function updatedBuscar(): void        { $this->dispatch('filtros-actualizados', filtros: $this->getFiltros()); }

    public function limpiarFiltros(): void
    {
        $this->filtroTipo   = '';
        $this->filtroEstado = '';
        $this->buscar       = '';
        $this->dispatch('filtros-actualizados', filtros: $this->getFiltros());
    }

    // ── Helpers privados ──────────────────────────────────────────────────────

    private function resetNuevaForm(): void
    {
        $this->nuevaFecha      = '';
        $this->nuevaHora       = '';
        $this->nuevaHoraFin    = '';
        $this->nuevaNombre     = '';
        $this->nuevaTipo       = '';
        $this->nuevaLugar      = '';
        $this->nuevaObjetivo   = '';
        $this->nuevaCupo       = '';
        $this->nuevaMateriales = '';
        $this->nuevaEstado     = 'PROGRAMADA';
        $this->nuevaObs        = '';
        $this->resetValidation();
    }

    private function getFiltros(): array
    {
        return [
            'tipo'   => $this->filtroTipo,
            'estado' => $this->filtroEstado,
            'buscar' => $this->buscar,
        ];
    }

    private function getStats(): array
    {
        return [
            'hoy'               => ActividadAdulto::whereDate('fecha', today())->count(),
            'programadas'       => ActividadAdulto::whereIn('estado', ['PROGRAMADA', 'EN_CURSO', 'PENDIENTE'])->count(),
            'en_curso'          => ActividadAdulto::where('estado', 'EN_CURSO')->count(),
            'evaluadas'         => ActividadAdulto::where('estado', 'EVALUADA')->count(),
            'con_seguimiento'   => \App\Models\ActividadParticipante::whereNull('deleted_at')
                ->where('requiere_seguimiento', true)->count(),
        ];
    }

    public function render()
    {
        return view('livewire.admin.actividades.calendario-actividades-panel', [
            'tipos'  => TipoActividadAdulto::orderBy('tipo')->get(),
            'stats'  => $this->getStats(),
        ])->layout('layouts.sistema');
    }
}
