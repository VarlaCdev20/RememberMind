<?php

namespace App\Livewire\Valoraciones;

use App\Models\Atencion;
use App\Models\HistorialEstadoResidente;
use App\Models\NotaClinica;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class ValoracionMedicaPanel extends Component
{
    use WithPagination;

    public string $search       = '';
    public string $filtroEstado = '';
    public string $filtroResult = '';

    public bool   $modalForm = false;
    public bool   $modalVer  = false;
    public ?string   $editandoId = null;
    public ?string   $viendoId  = null;

    public string $codAm                    = '';
    public ?string   $codValEnf                = null;
    public string $fecha                    = '';
    public string $hora                     = '';
    public string $diagnosticosReferidos    = '';
    public string $antecedentesRelevantes   = '';
    public string $medicacionActualResumen  = '';
    public string $alergiasReferidas        = '';
    public string $condicionMedicaGeneral   = '';
    public string $estadoNeurologicoBasico  = '';
    public string $nivelDependenciaSugerido = '';
    public string $resultadoAdmision        = '';
    public string $motivoDecision           = '';
    public string $recomendacionMedica      = '';
    public bool   $requiereSeguimientoEsp   = false;
    public string $estadoForm               = 'BORRADOR';

    protected $queryString = [
        'search' => ['except' => ''],
        'filtroEstado' => ['except' => ''],
        'filtroResult' => ['except' => ''],
    ];

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingFiltroEstado(): void { $this->resetPage(); }
    public function updatingFiltroResult(): void { $this->resetPage(); }

    public function limpiarFiltros(): void
    {
        $this->search = '';
        $this->filtroEstado = '';
        $this->filtroResult = '';
        $this->resetPage();
    }

    public function limpiarFiltro(string $campo): void
    {
        if ($campo === 'search') {
            $this->search = '';
        } elseif ($campo === 'filtroEstado') {
            $this->filtroEstado = '';
        } elseif ($campo === 'filtroResult') {
            $this->filtroResult = '';
        }
        $this->resetPage();
    }

    public function abrirCrear(): void
    {
        $this->reset('editandoId', 'codAm', 'diagnosticosReferidos', 'antecedentesRelevantes', 'medicacionActualResumen', 'alergiasReferidas', 'condicionMedicaGeneral', 'estadoNeurologicoBasico', 'nivelDependenciaSugerido', 'resultadoAdmision', 'motivoDecision', 'recomendacionMedica');
        $this->fecha     = today()->format('Y-m-d');
        $this->hora      = now()->format('H:i');
        $this->estadoForm= 'BORRADOR';
        $this->modalForm = true;
    }

    public function abrirVer(string $id): void
    {
        $this->viendoId = $id;
        $this->modalVer = true;
    }

    public function guardar(): void
    {
        $this->validate([
            'codAm'             => 'required|exists:residentes,cod_residente',
            'fecha'             => 'required|date',
            'hora'              => 'required',
            'resultadoAdmision' => 'required|in:ADMITIDO,NO_ADMITIDO,DERIVADO,OBSERVADO,CANCELADO',
            'motivoDecision'    => 'required|string|min:10',
            'estadoForm'        => 'required|in:BORRADOR,COMPLETADA',
        ], [
            'codAm.required'             => 'Seleccione un residente.',
            'codAm.exists'               => 'El residente seleccionado no existe.',
            'resultadoAdmision.required' => 'El resultado de admisión es obligatorio.',
            'motivoDecision.required'    => 'El motivo de la decisión es obligatorio.',
            'motivoDecision.min'         => 'Describa el motivo con al menos 10 caracteres.',
        ]);

        $observacion = trim(implode("\n", array_filter([
            $this->diagnosticosReferidos ? 'Diagnósticos referidos: ' . $this->diagnosticosReferidos : null,
            $this->antecedentesRelevantes ? 'Antecedentes: ' . $this->antecedentesRelevantes : null,
            $this->medicacionActualResumen ? 'Medicación actual: ' . $this->medicacionActualResumen : null,
            $this->condicionMedicaGeneral ? 'Condición general: ' . $this->condicionMedicaGeneral : null,
            $this->estadoNeurologicoBasico ? 'Estado neurológico: ' . $this->estadoNeurologicoBasico : null,
            $this->nivelDependenciaSugerido ? 'Dependencia sugerida: ' . $this->nivelDependenciaSugerido : null,
            $this->resultadoAdmision ? 'Resultado admisión: ' . $this->resultadoAdmision : null,
            $this->motivoDecision ? 'Motivo: ' . $this->motivoDecision : null,
            $this->recomendacionMedica ? 'Recomendación: ' . $this->recomendacionMedica : null,
        ])));

        $codPersonal = Personal::where('cod_usuario', Auth::user()?->cod_usuario)->value('cod_personal') 
                     ?? Personal::value('cod_personal') 
                     ?? 'PER_0001';

        $fechaHora = Carbon::parse($this->fecha . ' ' . $this->hora);
        $estadoAtencion = $this->estadoForm === 'COMPLETADA' ? 'COMPLETADA' : 'BORRADOR';

        DB::transaction(function () use ($observacion, $codPersonal, $fechaHora, $estadoAtencion) {
            if ($this->editandoId) {
                $atencion = Atencion::findOrFail($this->editandoId);
                $atencion->update([
                    'fecha_hora'  => $fechaHora,
                    'estado'      => $estadoAtencion,
                    'observacion' => $observacion,
                ]);
            } else {
                $codAtencion = 'ATN_' . strtoupper(Str::random(10));
                $atencion = Atencion::create([
                    'cod_atencion'  => $codAtencion,
                    'cod_residente' => $this->codAm,
                    'cod_area'      => 'ARE_0001',
                    'cod_personal'  => $codPersonal,
                    'tipo_atencion' => 'VALORACION_MEDICA',
                    'motivo'        => 'Valoración médica de admisión (' . $this->resultadoAdmision . ')',
                    'fecha_hora'    => $fechaHora,
                    'estado'        => $estadoAtencion,
                    'observacion'   => $observacion,
                ]);

                NotaClinica::create([
                    'cod_nota'      => 'NOT_' . strtoupper(Str::random(10)),
                    'cod_atencion'  => $codAtencion,
                    'cod_residente' => $this->codAm,
                    'cod_personal'  => $codPersonal,
                    'tipo_nota'     => 'VALORACION_MEDICA',
                    'contenido'     => $observacion,
                    'fecha_hora'    => $fechaHora,
                    'estado'        => 'REGISTRADA',
                ]);

                if ($this->estadoForm === 'COMPLETADA' && $this->resultadoAdmision === 'ADMITIDO') {
                    $residente = Residente::find($this->codAm);
                    if ($residente) {
                        $estadoAnterior = $residente->estado;
                        $residente->update(['estado' => 'ACTIVO']);
                        HistorialEstadoResidente::create([
                            'cod_historial_estado' => 'HER_' . strtoupper(Str::random(10)),
                            'cod_residente'        => $this->codAm,
                            'cod_usuario_registro' => Auth::user()?->cod_usuario ?? User::value('cod_usuario'),
                            'estado_anterior'      => $estadoAnterior,
                            'estado_nuevo'         => 'ACTIVO',
                            'fecha_hora'           => now(),
                            'motivo'               => 'Admisión aprobada en valoración médica: ' . $this->motivoDecision,
                        ]);
                    }
                }
            }
        });

        $this->modalForm = false;
        $this->reset('editandoId','codAm','fecha','hora','resultadoAdmision','motivoDecision','estadoForm');
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Valoración médica guardada correctamente.']);
    }

    public function cerrarModales(): void
    {
        $this->modalForm = false;
        $this->modalVer  = false;
        $this->viendoId  = null;
        $this->resetValidation();
    }

    public function render()
    {
        $valoraciones = Atencion::with(['residente', 'personal'])
            ->whereIn('tipo_atencion', ['VALORACION_MEDICA', 'MEDICA'])
            ->when($this->search, fn($q) =>
                $q->whereHas('residente', fn($sq) =>
                    $sq->where('nombres', 'like', '%' . $this->search . '%')
                      ->orWhere('apellido_paterno', 'like', '%' . $this->search . '%')
                      ->orWhere('apellido_materno', 'like', '%' . $this->search . '%')
                      ->orWhere('numero_documento', 'like', '%' . $this->search . '%')
                )
            )
            ->when($this->filtroEstado, fn($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->filtroResult, fn($q) => $q->where('motivo', 'like', '%' . $this->filtroResult . '%'))
            ->orderByDesc('fecha_hora')
            ->paginate(12);

        return view('livewire.valoraciones.valoracion-medica-panel', [
            'valoraciones' => $valoraciones,
            'adultos'      => Residente::select('cod_residente', 'nombres', 'apellido_paterno', 'apellido_materno')
                ->where('estado', '!=', 'ARCHIVADO')
                ->orderBy('apellido_paterno')
                ->get(),
            'detalle'      => $this->viendoId
                ? Atencion::with(['residente', 'personal'])->find($this->viendoId)
                : null,
        ])->layout('layouts.sistema');
    }
}
