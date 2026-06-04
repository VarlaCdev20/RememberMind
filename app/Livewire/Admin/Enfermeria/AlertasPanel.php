<?php

namespace App\Livewire\Admin\Enfermeria;

use App\Models\AdultoMayor;
use App\Models\AccionAlerta;
use App\Models\AlertaAdulto;
use App\Models\TurnoEnfermeria;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class AlertasPanel extends Component
{
    use WithPagination;

    public string $search        = '';
    public string $filtroEstado  = 'ABIERTA';
    public string $filtroNivel   = '';
    public string $filtroOrigen  = '';

    public bool   $modalCrear    = false;
    public bool   $modalAtender  = false;
    public bool   $modalCerrar   = false;
    public ?int   $alertaId      = null;

    // Crear alerta
    public string $codAm      = '';
    public string $codTurno   = '';
    public string $origen     = 'MANUAL';
    public string $tipoAlerta = '';
    public string $nivel      = 'MEDIO';
    public string $motivo     = '';

    // Atender / Cerrar
    public string $accionTomada      = '';
    public string $observacionCierre = '';

    // Acción individual
    public string $accion    = '';

    public function abrirCrear(): void
    {
        $this->reset('alertaId','codAm','codTurno','tipoAlerta','motivo','accionTomada');
        $this->origen = 'MANUAL';
        $this->nivel  = 'MEDIO';
        $this->modalCrear = true;
    }

    public function guardarAlerta(): void
    {
        $this->validate([
            'codAm'     => 'required|exists:adulto_mayor,cod_am',
            'origen'    => 'required|in:SIGNOS,MEDICACION,SEGUIMIENTO,PLAN,INCIDENTE,SOLICITUD_MEDICA,MANUAL',
            'tipoAlerta'=> 'required|string|max:100',
            'nivel'     => 'required|in:BAJO,MEDIO,ALTO,CRITICO',
            'motivo'    => 'required|string|min:10',
        ], [
            'codAm.required'     => 'Seleccione un adulto mayor.',
            'tipoAlerta.required'=> 'Escriba el tipo de alerta.',
            'motivo.required'    => 'Describa el motivo de la alerta.',
            'motivo.min'         => 'El motivo debe tener al menos 10 caracteres.',
        ]);

        AlertaAdulto::create([
            'cod_am'       => $this->codAm,
            'cod_turno'    => $this->codTurno ?: null,
            'origen'       => $this->origen,
            'tipo_alerta'  => strtoupper(trim($this->tipoAlerta)),
            'nivel'        => $this->nivel,
            'motivo'       => $this->motivo,
            'responsable_id'=> Auth::id(),
            'estado'       => 'ABIERTA',
        ]);

        $this->modalCrear = false;
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Alerta registrada correctamente.']);
    }

    public function atenderAlerta(int $id): void
    {
        $this->alertaId    = $id;
        $this->accionTomada = '';
        $this->modalAtender = true;
    }

    public function guardarAtencion(): void
    {
        $this->validate([
            'accionTomada' => 'required|string|min:5',
        ], ['accionTomada.required' => 'Describa la acción tomada.']);

        $alerta = AlertaAdulto::findOrFail($this->alertaId);
        $alerta->update([
            'estado'         => 'EN_ATENCION',
            'accion_tomada'  => $this->accionTomada,
            'fecha_atencion' => now(),
            'atendido_por'   => Auth::id(),
        ]);

        AccionAlerta::create([
            'cod_alerta'    => $this->alertaId,
            'accion'        => $this->accionTomada,
            'responsable_id'=> Auth::id(),
            'fecha_accion'  => now(),
            'estado'        => 'REALIZADA',
        ]);

        $this->modalAtender = false;
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Alerta marcada en atención.']);
    }

    public function cerrarAlerta(int $id): void
    {
        $this->alertaId         = $id;
        $this->observacionCierre= '';
        $this->modalCerrar      = true;
    }

    public function confirmarCierre(): void
    {
        $this->validate([
            'observacionCierre' => 'required|string|min:5',
        ], ['observacionCierre.required' => 'La observación de cierre es obligatoria.']);

        AlertaAdulto::findOrFail($this->alertaId)->update([
            'estado'             => 'CERRADA',
            'fecha_cierre'       => now(),
            'cerrado_por'        => Auth::id(),
            'observacion_cierre' => $this->observacionCierre,
        ]);

        $this->modalCerrar = false;
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Alerta cerrada correctamente.']);
    }

    public function cerrarModales(): void
    {
        $this->modalCrear   = false;
        $this->modalAtender = false;
        $this->modalCerrar  = false;
        $this->alertaId     = null;
        $this->resetValidation();
    }

    public function render()
    {
        $alertas = AlertaAdulto::with(['adultoMayor','turno','responsable'])
            ->when($this->search, fn($q) =>
                $q->whereHas('adultoMayor', fn($sq) =>
                    $sq->where('nombres','ilike','%'.$this->search.'%')
                      ->orWhere('ap_paterno','ilike','%'.$this->search.'%')
                )
                ->orWhere('tipo_alerta','ilike','%'.$this->search.'%')
            )
            ->when($this->filtroEstado, fn($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->filtroNivel,  fn($q) => $q->where('nivel',  $this->filtroNivel))
            ->when($this->filtroOrigen, fn($q) => $q->where('origen', $this->filtroOrigen))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.admin.enfermeria.alertas-panel', [
            'alertas'  => $alertas,
            'adultos'  => AdultoMayor::select('cod_am','nombres','ap_paterno')
                ->whereNull('archivado_en')->orderBy('ap_paterno')->get(),
            'turnos'   => TurnoEnfermeria::activos()->get(),
            'statsAlertas' => [
                'abiertas'     => AlertaAdulto::where('estado','ABIERTA')->count(),
                'en_atencion'  => AlertaAdulto::where('estado','EN_ATENCION')->count(),
                'criticas'     => AlertaAdulto::whereIn('estado',['ABIERTA','EN_ATENCION'])->where('nivel','CRITICO')->count(),
                'hoy'          => AlertaAdulto::whereDate('created_at', today())->count(),
            ],
        ])->layout('layouts.sistema');
    }
}
