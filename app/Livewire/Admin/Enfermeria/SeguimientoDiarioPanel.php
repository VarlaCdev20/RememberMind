<?php

namespace App\Livewire\Admin\Enfermeria;

use App\Models\AdultoMayor;
use App\Models\PlanCuidado;
use App\Models\SeguimientoDiario;
use App\Models\TurnoEnfermeria;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class SeguimientoDiarioPanel extends Component
{
    use WithPagination;

    public string $search       = '';
    public string $filtroTurno  = '';
    public string $filtroFecha  = '';

    public bool   $modalForm = false;
    public ?string $editandoId= null;

    public string $codAm                = '';
    public string $codTurno             = '';
    public string $codPlan              = '';
    public string $fecha                = '';
    public string $horaInicio           = '';
    public string $horaFin              = '';
    public string $estadoGeneral        = '';
    public string $alimentacion         = '';
    public string $porcentajeAlimentacion = '';
    public string $hidratacion          = '';
    public string $movilidad            = '';
    public bool   $intentoCaminarSolo   = false;
    public string $higiene              = '';
    public string $sueno                = '';
    public string $orientacion          = '';
    public bool   $repitePreguntas      = false;
    public bool   $confusionObservable  = false;
    public string $conducta             = '';
    public string $participacion        = '';
    public bool   $incidente            = false;
    public bool   $requiereMedico       = false;
    public string $observacion          = '';

    public function mount(): void
    {
        $this->filtroFecha = today()->toDateString();

        $horaActual = now()->format('H:i:s');
        $turnoActual = TurnoEnfermeria::whereTime('hora_inicio', '<=', $horaActual)
            ->whereTime('hora_fin', '>=', $horaActual)
            ->first() ?? TurnoEnfermeria::first();

        if ($turnoActual) {
            $this->filtroTurno = (string) $turnoActual->cod_turno;
            $this->codTurno = (string) $turnoActual->cod_turno;
        }
    }

    public function abrirCrear(): void
    {
        $this->reset();
        $this->fecha     = today()->format('Y-m-d');
        $this->horaInicio= now()->format('H:i');
        $this->filtroFecha = today()->toDateString();
        if ($this->filtroTurno !== '') {
            $this->codTurno = $this->filtroTurno;
        }
        $this->modalForm = true;
    }

    public function guardar(): void
    {
        $this->validate([
            'codAm'   => 'required|exists:adulto_mayor,cod_am',
            'codTurno'=> 'required|exists:turnos_enfermeria,cod_turno',
            'fecha'   => 'required|date',
        ], [
            'codAm.required'   => 'Seleccione un adulto mayor.',
            'codTurno.required'=> 'Seleccione el turno.',
            'fecha.required'   => 'La fecha es obligatoria.',
        ]);

        // Verificar duplicado
        $duplicado = SeguimientoDiario::where('cod_am', $this->codAm)
            ->where('fecha', $this->fecha)
            ->where('cod_turno', $this->codTurno)
            ->when($this->editandoId, fn($q) => $q->where('cod_seg_diario', '!=', $this->editandoId))
            ->exists();

        if ($duplicado) {
            $this->addError('codTurno', 'Ya existe un seguimiento para este adulto en este turno y fecha.');
            return;
        }

        $datos = [
            'cod_am'                   => $this->codAm,
            'cod_turno'                => $this->codTurno,
            'cod_plan'                 => $this->codPlan ?: null,
            'registrado_por'           => Auth::id(),
            'fecha'                    => $this->fecha,
            'hora_inicio'              => $this->horaInicio ? $this->horaInicio . ':00' : null,
            'hora_fin'                 => $this->horaFin ? $this->horaFin . ':00' : null,
            'estado_general'           => $this->estadoGeneral ?: null,
            'alimentacion'             => $this->alimentacion ?: null,
            'porcentaje_alimentacion'  => $this->porcentajeAlimentacion !== '' ? (int) $this->porcentajeAlimentacion : null,
            'hidratacion'              => $this->hidratacion ?: null,
            'movilidad'                => $this->movilidad ?: null,
            'intento_caminar_solo'     => $this->intentoCaminarSolo,
            'higiene'                  => $this->higiene ?: null,
            'sueno'                    => $this->sueno ?: null,
            'orientacion'              => $this->orientacion ?: null,
            'repite_preguntas'         => $this->repitePreguntas,
            'confusion_observable'     => $this->confusionObservable,
            'conducta'                 => $this->conducta ?: null,
            'participacion'            => $this->participacion ?: null,
            'incidente'                => $this->incidente,
            'requiere_medico'          => $this->requiereMedico,
            'observacion'              => $this->observacion ?: null,
        ];

        if ($this->editandoId) {
            SeguimientoDiario::findOrFail($this->editandoId)->update($datos);
            $msg = 'Seguimiento actualizado.';
        } else {
            SeguimientoDiario::create($datos);
            $msg = 'Seguimiento diario registrado.';
        }

        $this->modalForm = false;
        $this->dispatch('swal', ['icon' => 'success', 'title' => $msg]);
    }

    public function cerrarModales(): void
    {
        $this->modalForm  = false;
        $this->editandoId = null;
        $this->resetValidation();
    }

    public function render()
    {
        $seguimientos = SeguimientoDiario::with(['adultoMayor','turno','registradoPor'])
            ->when($this->search, fn($q) =>
                $q->whereHas('adultoMayor', fn($sq) =>
                    $sq->where('nombres','ilike','%'.$this->search.'%')
                      ->orWhere('ap_paterno','ilike','%'.$this->search.'%')
                )
            )
            ->when($this->filtroTurno, fn($q) => $q->where('cod_turno', $this->filtroTurno))
            ->when($this->filtroFecha, fn($q) => $q->whereDate('fecha', $this->filtroFecha))
            ->orderByDesc('fecha')
            ->paginate(12);

        return view('livewire.admin.enfermeria.seguimiento-diario-panel', [
            'seguimientos' => $seguimientos,
            'adultos'      => AdultoMayor::select('cod_am','nombres','ap_paterno')
                ->whereNull('archivado_en')->orderBy('ap_paterno')->get(),
            'turnos'       => TurnoEnfermeria::activos()->get(),
            'planes'       => PlanCuidado::activos()->with('adultoMayor')->get(),
        ])->layout('layouts.sistema');
    }
}
