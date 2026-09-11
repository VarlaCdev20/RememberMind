<?php

namespace App\Livewire\Cuidados;

use App\Models\AdultoMayor;
use App\Models\AlertaAdulto;
use App\Models\PaseTurno;
use App\Models\TareaPlanCuidado;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class PaseTurnoPanel extends Component
{
    use WithPagination;
    #[\Livewire\Attributes\Url(as: 'adulto')]
    public string $filtroAdulto = '';

    public string $search        = '';
    public string $filtroEstado  = '';
    public string $filtroFecha   = '';

    public bool   $modalGenerar  = false;
    public bool   $modalVer      = false;
    public bool   $modalRecibir  = false;
    public ?string   $paseId        = null;

    public string $codAm                      = '';
    public string $turnoSalienteId            = '';
    public string $turnoEntranteId            = '';
    public string $enfermeroEntranteId        = '';
    public string $estadoGeneralCierre        = '';
    public string $resumenTurno               = '';
    public string $recomendacionSiguienteTurno= '';
    public bool   $requiereVigilanciaEspecial = false;
    public string $motivoVigilancia           = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('pase_turno.ver'), 403);
    }

    public function abrirGenerar(?string $codAm = null): void
    {
        abort_unless(auth()->user()?->can('pase_turno.generar'), 403);
        if ($codAm) {
            app(\App\Services\Enfermeria\TurnoEnfermeriaService::class)->autorizarAccionPaciente($codAm);
        }

        $service = app(TurnoEnfermeriaService::class);
        $turnoActivo = $service->obtenerTurnoActivo(Auth::user());

        $this->reset('paseId','codAm','turnoSalienteId','turnoEntranteId',
                     'enfermeroEntranteId','estadoGeneralCierre','resumenTurno',
                     'recomendacionSiguienteTurno','motivoVigilancia');
        $this->requiereVigilanciaEspecial = false;

        if ($codAm) {
            $this->codAm = $codAm;
        }
        if ($turnoActivo) {
            $this->turnoSalienteId = (string) $turnoActivo->cod_turno;
            // Sugerir turno entrante siguiente por orden
            $siguiente = TurnoEnfermeria::activos()->where('orden', '>', $turnoActivo->orden)->orderBy('orden')->first()
                ?: TurnoEnfermeria::activos()->orderBy('orden')->first();
            if ($siguiente && $siguiente->cod_turno !== $turnoActivo->cod_turno) {
                $this->turnoEntranteId = (string) $siguiente->cod_turno;
            }
        }

        if ($this->codAm) {
            $this->autoCompletarResumen();
        }

        $this->modalGenerar = true;
    }

    public function updatedCodAm(): void
    {
        $this->autoCompletarResumen();
    }

    public function updatedTurnoSalienteId(): void
    {
        $this->autoCompletarResumen();
    }

    public function autoCompletarResumen(): void
    {
        if (!$this->codAm) return;

        $tareasQuery = TareaPlanCuidado::where('cod_am', $this->codAm)
            ->whereDate('fecha_programada', today());
        if ($this->turnoSalienteId) {
            $tareasQuery->where('cod_turno', $this->turnoSalienteId);
        }
        $todas = $tareasQuery->get();
        $realizadas = $todas->where('estado', 'REALIZADA')->count();
        $pendientes = $todas->whereIn('estado', ['PENDIENTE', 'EN_PROCESO'])->count();
        $omitidas = $todas->where('estado', 'OMITIDA')->count();

        $alertas = AlertaAdulto::where('cod_am', $this->codAm)
            ->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])
            ->get();
        $criticas = $alertas->where('nivel', 'CRITICO')->count();

        if ($criticas > 0 || $omitidas > 0) {
            $this->requiereVigilanciaEspecial = true;
            $this->motivoVigilancia = $criticas > 0
                ? "Residente con {$criticas} alerta(s) crítica(s) activa(s) durante la guardia."
                : "Residente con {$omitidas} tarea(s) omitida(s) que requieren verificación.";
        }

        if (empty($this->resumenTurno)) {
            $this->resumenTurno = "Pase de guardia generado automáticamente: {$realizadas} tareas realizadas, {$pendientes} pendientes, {$omitidas} omitidas y {$alertas->count()} alertas clínicas activas.";
        }
    }

    public function generarPase(): void
    {
        abort_unless(auth()->user()?->can('pase_turno.generar'), 403);
        abort_unless(Auth::check(), 401);
        app(\App\Services\Enfermeria\TurnoEnfermeriaService::class)->autorizarAccionPaciente($this->codAm, Auth::user());

        $this->validate([
            'codAm'               => 'required|exists:adulto_mayor,cod_am',
            'turnoSalienteId'     => 'required|exists:turnos_enfermeria,cod_turno',
            'turnoEntranteId'     => 'required|exists:turnos_enfermeria,cod_turno|different:turnoSalienteId',
            'enfermeroEntranteId' => [
                'required',
                'exists:users,cod_usu',
                auth()->user()->hasRole('SUPERADMINISTRADOR') ? 'nullable' : \Illuminate\Validation\Rule::notIn([Auth::id()]),
            ],
            'resumenTurno'        => 'required|string|min:20',
            'motivoVigilancia'    => $this->requiereVigilanciaEspecial ? 'required|string|min:10|max:1000' : 'nullable|string',
        ], [
            'codAm.required'               => 'Seleccione un adulto mayor.',
            'turnoSalienteId.required'     => 'Seleccione el turno saliente.',
            'turnoEntranteId.required'     => 'Seleccione el turno entrante.',
            'turnoEntranteId.different'    => 'El turno entrante debe ser diferente al saliente.',
            'enfermeroEntranteId.required' => 'Seleccione el enfermero que recibe.',
            'enfermeroEntranteId.not_in'   => 'El enfermero receptor debe ser diferente al enfermero saliente.',
            'resumenTurno.required'        => 'El resumen del turno es obligatorio.',
            'resumenTurno.min'             => 'El resumen debe tener al menos 20 caracteres.',
            'motivoVigilancia.required'    => 'Si se requiere vigilancia especial, debe indicar el motivo detallado (mínimo 10 caracteres).',
            'motivoVigilancia.min'         => 'El motivo de vigilancia debe tener al menos 10 caracteres.',
        ]);

        $receptor = User::findOrFail($this->enfermeroEntranteId);
        if ($receptor->estado !== 'ACTIVO') {
            $this->addError('enfermeroEntranteId', 'El enfermero receptor no está activo en el sistema.');
            return;
        }

        // Evitar duplicados de pase para el mismo paciente, turno y fecha
        $duplicado = PaseTurno::where('cod_am', $this->codAm)
            ->where('turno_saliente_id', $this->turnoSalienteId)
            ->whereDate('fecha', today())
            ->whereIn('estado', ['GENERADO', 'RECIBIDO'])
            ->exists();

        if ($duplicado) {
            $this->addError('codAm', 'Ya se ha generado un pase de turno para este paciente en el turno saliente hoy.');
            return;
        }

        // Obtener tareas realizadas, pendientes y omitidas automáticamente
        $tareasRealizadas = TareaPlanCuidado::where('cod_am', $this->codAm)
            ->where('cod_turno', $this->turnoSalienteId)
            ->where('estado', 'REALIZADA')
            ->whereDate('fecha_programada', today())
            ->get(['cod_tarea','titulo','area','resultado'])
            ->toArray();

        $tareasPendientes = TareaPlanCuidado::where('cod_am', $this->codAm)
            ->where('cod_turno', $this->turnoSalienteId)
            ->whereIn('estado', ['PENDIENTE', 'EN_PROCESO'])
            ->whereDate('fecha_programada', today())
            ->get(['cod_tarea','titulo','area','prioridad'])
            ->toArray();

        $alertasActivas = AlertaAdulto::where('cod_am', $this->codAm)
            ->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])
            ->get(['cod_alerta','tipo_alerta','nivel','motivo'])
            ->toArray();

        PaseTurno::create([
            'cod_am'                        => $this->codAm,
            'turno_saliente_id'             => $this->turnoSalienteId,
            'turno_entrante_id'             => $this->turnoEntranteId,
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
        session()->flash('mensaje', 'Pase de turno generado correctamente.');
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Pase de turno generado', 'text' => 'La entrega de guardia quedó registrada.']);
    }

    public function recibirPase(string $id): void
    {
        abort_unless(auth()->user()?->can('pase_turno.recibir'), 403);

        $pase = PaseTurno::findOrFail($id);
        abort_unless($pase->enfermero_entrante_id === Auth::id() || auth()->user()->hasRole('SUPERADMINISTRADOR'), 403);
        if (! $pase->puedeRecibirse()) {
            $this->dispatch('swal', ['icon' => 'warning', 'title' => 'Este pase ya fue recibido o está cerrado.']);
            return;
        }
        $pase->update(['estado' => 'RECIBIDO', 'fecha_recibido' => now()]);
        session()->flash('mensaje', 'Pase de turno recibido.');
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Pase recibido', 'text' => 'Recepción de guardia confirmada.']);
    }

    public function abrirVer(string $id): void
    {
        abort_unless(auth()->user()?->can('pase_turno.ver'), 403);

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
        $service = app(TurnoEnfermeriaService::class);
        $esSuperAdmin = $service->esSuperAdmin(Auth::user());

        $pasesQuery = PaseTurno::query()->when($this->filtroAdulto, fn ($q) => $q->where('cod_am', $this->filtroAdulto))->with(['adultoMayor.habitacion', 'turnoSaliente', 'turnoEntrante', 'enfermeroSaliente', 'enfermeroEntrante']);

        if (!$esSuperAdmin) {
            $pacientesIds = $service->obtenerPacientesAsignadosIds(Auth::user());
            $pasesQuery->where(function ($q) use ($pacientesIds) {
                $q->whereIn('cod_am', $pacientesIds)
                  ->orWhere('enfermero_saliente_id', Auth::id())
                  ->orWhere('enfermero_entrante_id', Auth::id());
            });
        }

        $pases = $pasesQuery
            ->when($this->search, fn($q) =>
                $q->whereHas('adultoMayor', fn($sq) =>
                    $sq->whereLike('nombres','%'.$this->search.'%')
                      ->orWhereLike('ap_paterno','%'.$this->search.'%')
                )
            )
            ->when($this->filtroEstado, fn($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->filtroFecha,  fn($q) => $q->whereDate('fecha', $this->filtroFecha))
            ->orderByDesc('fecha')->orderByDesc('created_at')
            ->paginate(12);

        $adultosQuery = $service->obtenerPacientesAsignadosQuery(Auth::user())
            ->select('cod_am','nombres','ap_paterno')
            ->whereNull('archivado_en')
            ->orderBy('ap_paterno');

        return view('livewire.cuidados.pase-turno-panel', [
            'pases'      => $pases,
            'adultos'    => $adultosQuery->get(),
            'turnos'     => TurnoEnfermeria::activos()->get(),
            'enfermeros' => User::where('estado', 'ACTIVO')->orderBy('ap_paterno')->get(['cod_usu','nombres','ap_paterno']),
            'detalle'    => $this->paseId ? PaseTurno::with(['adultoMayor.habitacion','turnoSaliente','turnoEntrante','enfermeroSaliente','enfermeroEntrante'])->find($this->paseId) : null,
            'statsPases' => [
                'generados' => PaseTurno::where('estado','GENERADO')->whereDate('fecha',today())->count(),
                'recibidos' => PaseTurno::where('estado','RECIBIDO')->whereDate('fecha',today())->count(),
                'pendientes'=> PaseTurno::where('estado','GENERADO')->count(),
            ],
            'esSuperAdmin' => $esSuperAdmin,
        ])->layout('layouts.sistema');
    }
}