<?php

namespace App\Frontend\Livewire\Admisiones;

use App\Backend\Modulos\Admisiones\Acciones\CambiarAlojamientoResidente;
use App\Backend\Modulos\Admisiones\Acciones\FormalizarAdmision;
use App\Models\Cama;
use App\Models\Residente;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class AlojamientoResidente extends Component
{
    public bool $modalAbierto = false;

    #[Locked]
    public ?string $codResidente = null;

    #[Locked]
    public ?string $codOcupacionAnterior = null;

    public string $codCama = '';

    public string $fecha = '';

    public string $hora = '';

    public string $motivo = '';

    #[On('abrir-alojamiento-residente')]
    public function abrir(string $codResidente, ?string $codCama = null): void
    {
        $residente = Residente::query()->with('ocupacionActiva')->findOrFail($codResidente);
        app(CambiarAlojamientoResidente::class)->autorizar($residente, auth()->user());
        $this->resetValidation();
        $this->codResidente = $residente->getKey();
        $this->codOcupacionAnterior = $residente->ocupacionActiva?->getKey();
        $this->codCama = $codCama ?? '';
        $this->fecha = today()->toDateString();
        $this->hora = now()->format('H:i:s');
        $this->motivo = '';
        $this->modalAbierto = true;
    }

    public function guardar(CambiarAlojamientoResidente $cambiar): void
    {
        abort_unless($this->modalAbierto && $this->codResidente, 403);
        $residente = Residente::query()->findOrFail($this->codResidente);
        $cambiar->autorizar($residente, auth()->user());
        $this->validate([
            'codCama' => ['required', 'string', 'exists:camas,cod_cama'],
            'fecha' => ['required', 'date_format:Y-m-d'],
            'hora' => ['required', 'date_format:H:i,H:i:s'],
            'motivo' => ['nullable', 'string', 'max:1000'],
        ], ['codCama.required' => 'Seleccione una cama disponible.', 'fecha.required' => 'Indique la fecha del cambio.', 'hora.required' => 'Indique la hora del cambio.']);
        $ocupacion = $cambiar->ejecutar($residente, [
            'cod_cama' => $this->codCama, 'cod_ocupacion_anterior' => $this->codOcupacionAnterior,
            'fecha_hora' => $this->fecha.' '.$this->hora, 'motivo' => $this->motivo,
        ], auth()->user());
        $this->cerrarModal();
        $this->dispatch('alojamiento-residente-actualizado', codResidente: $ocupacion->cod_residente);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Alojamiento actualizado', 'text' => 'La nueva ubicación y la ocupación anterior quedaron registradas.']);
    }

    public function cerrarModal(): void
    {
        $this->reset(['modalAbierto', 'codResidente', 'codOcupacionAnterior', 'codCama', 'fecha', 'hora', 'motivo']);
        $this->resetValidation();
    }

    public function render()
    {
        $residente = null;
        $camas = collect();
        $impedimento = null;
        if ($this->modalAbierto && $this->codResidente) {
            $residente = Residente::query()->with(['ocupacionActiva.cama.habitacion', 'admisiones' => fn ($query) => $query
                ->whereIn('estado', ['ACTIVA', 'ACTIVO'])->withCount('ocupacionesCama')])->findOrFail($this->codResidente);
            app(CambiarAlojamientoResidente::class)->autorizar($residente, auth()->user());
            if (! in_array($residente->estado, ['ACTIVO', 'ADMITIDO'], true)) {
                $impedimento = 'El residente no tiene una estancia activa.';
            } elseif ($residente->admisiones->count() !== 1 || $residente->admisiones->first()->ocupaciones_cama_count === 0) {
                $impedimento = 'Revise la admisión formal y su cama inicial antes de asignar otro alojamiento.';
            } elseif ($residente->ocupacionActiva?->getKey() !== $this->codOcupacionAnterior) {
                $impedimento = 'La ubicación cambió. Cierre y vuelva a abrir el formulario para revisar el alojamiento actual.';
            }
            if (! $impedimento) {
                $camas = FormalizarAdmision::filtrarCamasDisponibles(Cama::query())->with('habitacion')
                    ->orderBy('cod_habitacion')->orderBy('codigo')->get();
            }
        }

        return view('livewire.admisiones.alojamiento-residente', [
            'residente' => $residente, 'camas' => $camas,
            'camaSeleccionada' => $camas->firstWhere('cod_cama', $this->codCama), 'impedimento' => $impedimento,
        ]);
    }
}
