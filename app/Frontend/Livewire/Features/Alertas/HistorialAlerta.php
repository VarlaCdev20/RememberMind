<?php

namespace App\Frontend\Livewire\Features\Alertas;

use App\Backend\Modulos\Alertas\Servicios\AlertasService;
use App\Models\Alerta;
use Livewire\Component;

class HistorialAlerta extends Component
{
    public string $alertaId = '';
    public string $accion = '';

    public function mount(string $alertaId): void
    {
        $this->alertaId = $alertaId;
        abort_unless(auth()->user()?->can('alertas.ver'), 403);
    }

    public function guardarAccion(AlertasService $service): void
    {
        abort_unless(auth()->user()?->canAny(['alertas.gestionar', 'alertas.seguimiento']), 403);

        $this->validate([
            'accion' => 'required|string|min:5|max:10000',
        ], [
            'accion.required' => 'Debe ingresar el detalle de la intervención asistencial.',
            'accion.min' => 'La nota de intervención debe contener al menos 5 caracteres.',
            'accion.max' => 'La nota de intervención no puede superar los 10.000 caracteres.',
        ]);

        $service->registrarSeguimiento(
            Alerta::query()->findOrFail($this->alertaId),
            $this->accion,
            auth()->user(),
        );

        $this->accion = '';
        $this->dispatch('evento-alerta-registrado', alertaId: $this->alertaId);
        session()->flash('mensaje', 'Intervención registrada en el historial.');
    }

    public function render()
    {
        $alerta = Alerta::with([
            'adultoMayor',
            'responsable.usuario',
            'eventos.usuario',
        ])->findOrFail($this->alertaId);

        // Prepara los eventos cronológicos para el Timeline pattern
        $timelineItems = $alerta->eventos
            ->sortBy('fecha_hora')
            ->values()
            ->map(function ($ev) {
                $nombreActor = $ev->usuario ? ($ev->usuario->nombres . ' ' . $ev->usuario->ap_paterno) : 'Profesional / Sistema';
                $fechaFormat = $ev->fecha_hora ? $ev->fecha_hora->translatedFormat('d M Y, H:i') : 'Sin fecha';
                $cambioEstado = ($ev->estado_anterior && $ev->estado_nuevo && $ev->estado_anterior !== $ev->estado_nuevo)
                    ? "{$ev->estado_anterior} → {$ev->estado_nuevo}"
                    : null;

                $variant = match($ev->tipo_evento) {
                    'CREACION' => 'info',
                    'ATENCION', 'SEGUIMIENTO' => 'terracota',
                    'CIERRE' => 'success',
                    default => 'default',
                };

                return [
                    'id' => $ev->cod_evento_alerta,
                    'actor' => $nombreActor,
                    'tipo' => $ev->tipo_evento,
                    'estado' => $cambioEstado,
                    'fecha' => $fechaFormat,
                    'descripcion' => $ev->descripcion,
                    'variant' => $variant,
                    'icon' => match($ev->tipo_evento) {
                        'CREACION' => 'ph ph-plus-circle',
                        'ATENCION' => 'ph ph-first-aid',
                        'CIERRE' => 'ph ph-check-circle',
                        default => 'ph ph-chat-circle-dots',
                    },
                ];
            })
            ->toArray();

        return view('livewire.features.alertas.historial-alerta', [
            'alerta' => $alerta,
            'timelineItems' => $timelineItems,
        ]);
    }
}
