<?php

namespace App\Livewire\Admin\Enfermeria;

use App\Models\AdultoMayor;
use App\Models\FichaMedicaAdulto;
use App\Models\HistorialEstadoAdulto;
use Illuminate\Support\Facades\Auth;
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
    public ?int   $editandoId = null;
    public ?int   $viendoId  = null;

    public string $codAm                    = '';
    public ?int   $codValEnf                = null;
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

    public function abrirCrear(): void
    {
        $this->reset();
        $this->fecha     = today()->format('Y-m-d');
        $this->hora      = now()->format('H:i');
        $this->estadoForm= 'BORRADOR';
        $this->modalForm = true;
    }

    public function abrirVer(int $id): void
    {
        $this->viendoId = $id;
        $this->modalVer = true;
    }

    public function guardar(): void
    {
        $this->validate([
            'codAm'             => 'required|exists:adulto_mayor,cod_am',
            'fecha'             => 'required|date',
            'hora'              => 'required',
            'resultadoAdmision' => 'required|in:ADMITIDO,NO_ADMITIDO,DERIVADO,OBSERVADO,CANCELADO',
            'motivoDecision'    => 'required|string|min:10',
            'estadoForm'        => 'required|in:BORRADOR,COMPLETADA',
        ], [
            'codAm.required'             => 'Seleccione un adulto mayor.',
            'resultadoAdmision.required' => 'El resultado de admisión es obligatorio.',
            'motivoDecision.required'    => 'El motivo de la decisión es obligatorio.',
            'motivoDecision.min'         => 'Describa el motivo con al menos 10 caracteres.',
        ]);

        // Validar que exista valoración enfermería completada
        $datos = [
            'cod_am'                      => $this->codAm,
            'alergias'                    => $this->alergiasReferidas ?: null,
            'observacion_medica'          => trim(implode("\n", array_filter([
                $this->diagnosticosReferidos ? 'Diagnósticos referidos: ' . $this->diagnosticosReferidos : null,
                $this->antecedentesRelevantes ? 'Antecedentes: ' . $this->antecedentesRelevantes : null,
                $this->medicacionActualResumen ? 'Medicación actual: ' . $this->medicacionActualResumen : null,
                $this->condicionMedicaGeneral ? 'Condición general: ' . $this->condicionMedicaGeneral : null,
                $this->estadoNeurologicoBasico ? 'Estado neurológico: ' . $this->estadoNeurologicoBasico : null,
                $this->nivelDependenciaSugerido ? 'Dependencia sugerida: ' . $this->nivelDependenciaSugerido : null,
                $this->resultadoAdmision ? 'Resultado admisión: ' . $this->resultadoAdmision : null,
                $this->motivoDecision ? 'Motivo: ' . $this->motivoDecision : null,
                $this->recomendacionMedica ? 'Recomendación: ' . $this->recomendacionMedica : null,
            ]))) ?: null,
            'registrado_por'              => Auth::id(),
            'estado'                      => $this->estadoForm === 'COMPLETADA' ? 'ACTIVO' : 'BORRADOR',
        ];

        if ($this->editandoId) {
            FichaMedicaAdulto::findOrFail($this->editandoId)->update($datos);
        } else {
            FichaMedicaAdulto::create($datos);

            // Si es COMPLETADA y resultado ADMITIDO → cambiar estado adulto
            if ($this->estadoForm === 'COMPLETADA' && $this->resultadoAdmision === 'ADMITIDO') {
                $adulto = AdultoMayor::find($this->codAm);
                $estadoAdmitidoId = \DB::table('estado_adulto')->where('estado', 'ADMITIDO')->value('cod_est_adul');
                if ($adulto && $estadoAdmitidoId) {
                    $estadoAnterior = $adulto->cod_est_adul;
                    $adulto->update(['cod_est_adul' => $estadoAdmitidoId]);
                    HistorialEstadoAdulto::create([
                        'cod_am'         => $this->codAm,
                        'estado_anterior'=> $estadoAnterior,
                        'estado_nuevo'   => $estadoAdmitidoId,
                        'fecha_cambio'   => now(),
                        'motivo'         => 'Admisión aprobada en valoración médica.',
                        'cambiado_por'   => Auth::id(),
                    ]);
                }
            }
        }

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
        $valoraciones = FichaMedicaAdulto::with(['adultoMayor', 'registrador'])
            ->when($this->search, fn($q) =>
                $q->whereHas('adultoMayor', fn($sq) =>
                    $sq->where('nombres', 'ilike', '%' . $this->search . '%')
                      ->orWhere('ap_paterno', 'ilike', '%' . $this->search . '%')
                )
            )
            ->when($this->filtroEstado, fn($q) => $q->where('estado', $this->filtroEstado))
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('livewire.admin.enfermeria.valoracion-medica-panel', [
            'valoraciones' => $valoraciones,
            'adultos'      => AdultoMayor::select('cod_am','nombres','ap_paterno','ap_materno')
                ->whereNull('archivado_en')->orderBy('ap_paterno')->get(),
            'detalle'      => $this->viendoId
                ? FichaMedicaAdulto::with('adultoMayor','registrador')->find($this->viendoId)
                : null,
        ])->layout('layouts.sistema');
    }
}
