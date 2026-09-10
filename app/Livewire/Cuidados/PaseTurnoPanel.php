<?php

namespace App\Livewire\Cuidados;

use App\Models\AdultoMayor;
use App\Models\AlertaAdulto;
use App\Models\PaseTurno;
use App\Models\TareaPlanCuidado;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class PaseTurnoPanel extends Component
{
    use WithPagination;

    public string $search        = '';
    public string $filtroEstado  = '';
    public string $filtroFecha   = '';

    public bool   $modalGenerar  = false;
    public bool   $modalVer      = false;
    public bool   $modalRecibir  = false;
    public ?int   $paseId        = null;

    public string $codAm                      = '';
    public string $turnoSalienteId            = '';
    public string $turnoEntranteId            = '';
    public string $enfermeroEntranteId        = '';
    public string $estadoGeneralCierre        = '';
    public string $resumenTurno               = '';
    public string $recomendacionSiguienteTurno= '';
    public bool   $requiereVigilanciaEspecial = false;
    public string $motivoVigilancia           = '';

    public function abrirGenerar(): void
    {
        $this->reset('paseId','codAm','turnoSalienteId','turnoEntranteId',
                     'enfermeroEntranteId','estadoGeneralCierre','resumenTurno',
                     'recomendacionSiguienteTurno','motivoVigilancia');
        $this->requiereVigilanciaEspecial = false;
        $this->modalGenerar = true;
    }

    public function generarPase(): void
    {
        $this->validate([
            'codAm'             => 'required|exists:adulto_mayor,cod_am',
            'turnoSalienteId'   => 'required|exists:turnos_enfermeria,cod_turno',
            'turnoEntranteId'   => 'required|exists:turnos_enfermeria,cod_turno|different:turnoSalienteId',
            'enfermeroEntranteId'=> 'required|exists:users,cod_usu',
            'resumenTurno'      => 'required|string|min:20',
        ], [
            'codAm.required'              => 'Seleccione un adulto mayor.',
            'turnoSalienteId.required'    => 'Seleccione el turno saliente.',
            'turnoEntranteId.required'    => 'Seleccione el turno entrante.',
            'turnoEntranteId.different'   => 'El turno entrante debe ser diferente al saliente.',
            'enfermeroEntranteId.required'=> 'Seleccione el enfermero que recibe.',
            'resumenTurno.required'       => 'El resumen del turno es obligatorio.',
            'resumenTurno.min'            => 'El resumen debe tener al menos 20 caracteres.',
        ]);

        // Obtener tareas realizadas y pendientes automáticamente
        $tareasRealizadas = TareaPlanCuidado::where('cod_am', $this->codAm)
            ->where('cod_turno', (int) $this->turnoSalienteId)
            ->where('estado', 'REALIZADA')
            ->whereDate('fecha_programada', today())
            ->get(['cod_tarea','titulo','area','resultado'])
            ->toArray();

        $tareasPendientes = TareaPlanCuidado::where('cod_am', $this->codAm)
            ->where('cod_turno', (int) $this->turnoSalienteId)
            ->where('estado', 'PENDIENTE')
            ->whereDate('fecha_programada', today())
            ->get(['cod_tarea','titulo','area','prioridad'])
            ->toArray();

        $alertasActivas = AlertaAdulto::where('cod_am', $this->codAm)
            ->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])
            ->get(['cod_alerta','tipo_alerta','nivel','motivo'])
            ->toArray();

        PaseTurno::create([
            'cod_am'                        => $this->codAm,
            'turno_saliente_id'             => (int) $this->turnoSalienteId,
            'turno_entrante_id'             => (int) $this->turnoEntranteId,
            'enfermero_saliente_id'         => Auth::id(),
            'enfermero_entrante_id'         => $this->enfermeroEntranteId,
            'fecha'                         => today()->toDateString(),
            'estado_general_cierre'         => $this->estadoGeneralCierre ?: null,
            'resumen_turno'                 => $this->resumenTurno,
            'tareas_realizadas_json'        => $tareasRealizadas,
            'tareas_pendientes_json'        => $tareasPendientes,
            'alertas_activas_json'          => $alertasActivas,
            'recomendacion_siguiente_turno' => $this->recomendacionSiguienteTurno ?: null,
            'requiere_vigilancia_especial'  => $this->requiereVigilanciaEspecial,
            'motivo_vigilancia'             => $this->motivoVigilancia ?: null,
            'estado'                        => 'GENERADO',
        ]);

        $this->modalGenerar = false;
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Pase de turno generado correctamente.']);
    }

    public function recibirPase(int $id): void
    {
        $pase = PaseTurno::findOrFail($id);
        if (! $pase->puedeRecibirse()) {
            $this->dispatch('swal', ['icon' => 'warning', 'title' => 'Este pase ya fue recibido o está anulado.']);
            return;
        }
        $pase->update(['estado' => 'RECIBIDO', 'fecha_recibido' => now()]);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Pase de turno recibido.']);
    }

    public function abrirVer(int $id): void
    {
        $this->paseId   = $id;
        $this->modalVer = true;
    }

    public function cerrarModales(): void
    {
        $this->modalGenerar = false;
        $this->modalVer     = false;
        $this->modalRecibir = false;
        $this->paseId       = null;
        $this->resetValidation();
    }

    public function render()
    {
        $pases = PaseTurno::with(['adultoMayor','turnoSaliente','turnoEntrante','enfermeroSaliente','enfermeroEntrante'])
            ->when($this->search, fn($q) =>
                $q->whereHas('adultoMayor', fn($sq) =>
                    $sq->where('nombres','ilike','%'.$this->search.'%')
                      ->orWhere('ap_paterno','ilike','%'.$this->search.'%')
                )
            )
            ->when($this->filtroEstado, fn($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->filtroFecha,  fn($q) => $q->whereDate('fecha', $this->filtroFecha))
            ->orderByDesc('fecha')->orderByDesc('created_at')
            ->paginate(12);

        return view('livewire.cuidados.pase-turno-panel', [
            'pases'      => $pases,
            'adultos'    => AdultoMayor::select('cod_am','nombres','ap_paterno')
                ->whereNull('archivado_en')->orderBy('ap_paterno')->get(),
            'turnos'     => TurnoEnfermeria::activos()->get(),
            'enfermeros' => User::orderBy('ap_paterno')->get(['cod_usu','nombres','ap_paterno']),
            'detalle'    => $this->paseId ? PaseTurno::with(['adultoMayor','turnoSaliente','turnoEntrante','enfermeroSaliente','enfermeroEntrante'])->find($this->paseId) : null,
            'statsPases' => [
                'generados' => PaseTurno::where('estado','GENERADO')->whereDate('fecha',today())->count(),
                'recibidos' => PaseTurno::where('estado','RECIBIDO')->whereDate('fecha',today())->count(),
                'pendientes'=> PaseTurno::where('estado','GENERADO')->count(),
            ],
        ])->layout('layouts.sistema');
    }
}
