<?php

namespace App\Livewire\Cuidados;

use App\Models\AdultoMayor;
use App\Models\AsignacionTurnoAdulto;
use App\Models\AlertaAdulto;
use App\Models\PaseTurno;
use App\Models\TareaPlanCuidado;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use App\Services\Enfermeria\PaseTurnoService;
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
    public array $pendientesAutomaticos = [];
    public string $resumenAutomatico = '';

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
        $turno = TurnoEnfermeria::find($this->turnoSalienteId);
        if (!$turno) return;
        $this->pendientesAutomaticos = app(PaseTurnoService::class)->pendientes($this->codAm, $turno);
        $conteos = collect($this->pendientesAutomaticos)->countBy('tipo');
        $this->resumenAutomatico = collect(['MEDICACION','ALERTA','CUIDADO','INCIDENTE','LESION','TAREA'])
            ->map(fn ($tipo) => ($conteos[$tipo] ?? 0).' '.strtolower($tipo))->implode(' · ');
        $criticos = collect($this->pendientesAutomaticos)->where('nivel', 'CRITICO')->count();
        if ($criticos > 0) {
            $this->requiereVigilanciaEspecial = true;
            $this->motivoVigilancia = "Existen {$criticos} alerta(s) crítica(s) activa(s).";
        }
    }

    public function generarPase(): void
    {
        $this->resetValidation();
        try {
            app(PaseTurnoService::class)->generar(
                $this->codAm, $this->turnoEntranteId, $this->enfermeroEntranteId,
                [
                    'observaciones' => $this->resumenTurno,
                    'estado_general' => $this->estadoGeneralCierre,
                    'recomendacion' => $this->recomendacionSiguienteTurno,
                    'vigilancia' => $this->requiereVigilanciaEspecial,
                    'motivo_vigilancia' => $this->motivoVigilancia,
                ],
                Auth::user(),
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            $campos = [
                'cod_am' => 'codAm',
                'turno_entrante_id' => 'turnoEntranteId',
                'enfermero_entrante_id' => 'enfermeroEntranteId',
                'motivo_vigilancia' => 'motivoVigilancia',
                'observaciones' => 'resumenTurno',
                'estado_general' => 'estadoGeneralCierre',
                'recomendacion' => 'recomendacionSiguienteTurno',
            ];
            foreach ($e->errors() as $campo => $mensajes) {
                foreach ($mensajes as $mensaje) {
                    $this->addError($campos[$campo] ?? $campo, $mensaje);
                }
            }
            return;
        }
        $this->modalGenerar = false;
        session()->flash('mensaje', 'Pase de turno generado con pendientes reales.');
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Pase de turno generado', 'text' => 'La entrega conserva la fotografía automática del cierre de guardia.']);
    }

    public function recibirPase(string $id): void
    {
        app(PaseTurnoService::class)->recibir(PaseTurno::findOrFail($id), Auth::user());
        session()->flash('mensaje', 'Pase de turno recibido.');
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Pase recibido', 'text' => 'Recepción de guardia confirmada.']);
    }

    public function abrirVer(string $id): void
    {
        abort_unless(auth()->user()?->can('pase_turno.ver'), 403);
        $pase = PaseTurno::findOrFail($id);
        if (! app(TurnoEnfermeriaService::class)->esSuperAdmin(Auth::user())) {
            app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($pase->cod_am, Auth::user());
        }
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

        $detalle = null;
        if ($this->paseId) {
            $detalleQuery = PaseTurno::with(['adultoMayor.habitacion','turnoSaliente','turnoEntrante','enfermeroSaliente','enfermeroEntrante'])
                ->whereKey($this->paseId);
            if (! $esSuperAdmin) {
                $detalleQuery->whereIn('cod_am', $service->obtenerPacientesAsignadosIds(Auth::user()));
            }
            $detalle = $detalleQuery->first();
        }

        return view('livewire.cuidados.pase-turno-panel', [
            'pases'      => $pases,
            'adultos'    => $adultosQuery->get(),
            'turnos'     => TurnoEnfermeria::activos()->get(),
            'enfermeros' => User::role('ENFERMEROS')->where('estado', 'ACTIVO')->orderBy('ap_paterno')->get(['cod_usu','nombres','ap_paterno']),
            'detalle'    => $detalle,
            'statsPases' => [
                'generados' => PaseTurno::where('estado','GENERADO')->whereDate('fecha',today())->count(),
                'recibidos' => PaseTurno::where('estado','RECIBIDO')->whereDate('fecha',today())->count(),
                'pendientes'=> PaseTurno::where('estado','GENERADO')->count(),
            ],
            'esSuperAdmin' => $esSuperAdmin,
        ])->layout('layouts.sistema');
    }
}
