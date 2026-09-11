<?php

namespace App\Livewire\Cuidados;

use App\Models\RecepcionTurno;
use App\Services\Enfermeria\AgendaTurnoService;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AgendaEnfermeria extends Component
{
    public string $filtro = 'PENDIENTES';
    public string $buscar = '';
    public string $observacionRecepcion = '';

    public function mount(): void
    {
        abort_unless(Auth::user()?->can('enfermeria.ver_dashboard'), 403);
    }

    public function recibirTurno(): void
    {
        $turnos = app(TurnoEnfermeriaService::class);
        abort_if($turnos->esSuperAdmin(Auth::user()), 403, 'La recepción corresponde al personal operativo de Enfermería.');
        $turno = $turnos->obtenerTurnoActivo(Auth::user(), today()->toDateString());
        abort_unless($turno, 409, 'No tiene un turno activo asignado para recibir.');

        $existente = RecepcionTurno::where('cod_turno', $turno->cod_turno)
            ->where('cod_usuario', Auth::id())->whereDate('fecha_hora_recepcion', today())->first();

        if (!$existente) {
            RecepcionTurno::create([
                'cod_turno' => $turno->cod_turno,
                'cod_usuario' => Auth::id(),
                'fecha_hora_recepcion' => now(),
                'observacion' => $this->observacionRecepcion ?: null,
            ]);
        }

        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Turno recibido', 'text' => 'La recepción quedó registrada con fecha, hora y responsable.']);
    }

    public function render(AgendaTurnoService $agenda)
    {
        $items = $agenda->generar(Auth::user());

        if ($this->filtro !== 'TODOS') {
            $items = match ($this->filtro) {
                'VENCIDOS' => $items->where('estado', 'VENCIDA'),
                'PROXIMOS' => $items->whereIn('estado', ['PROXIMA', 'PENDIENTE']),
                'MEDICACION' => $items->where('tipo', 'MEDICACION'),
                'ALERTAS' => $items->where('tipo', 'ALERTA'),
                'TAREAS' => $items->where('tipo', 'TAREA'),
                default => $items->whereNotIn('estado', ['ADMINISTRADA', 'REALIZADA', 'CERRADA']),
            };
        }

        if ($texto = trim($this->buscar)) {
            $items = $items->filter(function (array $item) use ($texto) {
                $paciente = $item['paciente'];
                $contenido = ($paciente?->nombres.' '.$paciente?->ap_paterno.' '.$item['titulo'].' '.$item['detalle']);
                return str_contains(mb_strtolower($contenido), mb_strtolower($texto));
            });
        }

        $turnos = app(TurnoEnfermeriaService::class);
        $esSuperAdmin = $turnos->esSuperAdmin(Auth::user());
        $turno = $turnos->obtenerTurnoActivo(Auth::user(), today()->toDateString());
        $recepcion = !$esSuperAdmin && $turno ? RecepcionTurno::where('cod_turno', $turno->cod_turno)
            ->where('cod_usuario', Auth::id())->whereDate('fecha_hora_recepcion', today())->first() : null;

        return view('livewire.cuidados.agenda-enfermeria', compact('items', 'turno', 'recepcion', 'esSuperAdmin'))
            ->layout('layouts.sistema');
    }
}
