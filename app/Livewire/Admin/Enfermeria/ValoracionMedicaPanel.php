<?php

namespace App\Livewire\Admin\Enfermeria;

use App\Models\AdultoMayor;
use App\Models\ValoracionEnfermeriaAdmision;
use App\Models\ValoracionMedicaAdmision;
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
        if (!\Illuminate\Support\Facades\Schema::hasTable('valoraciones_medicas_admision')) {
            return;
        }

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
        if (\Illuminate\Support\Facades\Schema::hasTable('valoraciones_enfermeria_admision') &&
            ! ValoracionEnfermeriaAdmision::where('cod_am', $this->codAm)
                ->where('estado', 'COMPLETADA')
                ->where('puede_pasar_valoracion_medica', true)
                ->exists()) {
            $this->addError('codAm', 'No existe valoración de enfermería completada y aprobada para este adulto mayor.');
            return;
        }

        $datos = [
            'cod_am'                      => $this->codAm,
            'cod_val_enf'                 => $this->codValEnf,
            'fecha'                       => $this->fecha,
            'hora'                        => strlen($this->hora) === 5 ? $this->hora . ':00' : $this->hora,
            'diagnosticos_referidos'      => $this->diagnosticosReferidos ?: null,
            'antecedentes_relevantes'     => $this->antecedentesRelevantes ?: null,
            'medicacion_actual_resumen'   => $this->medicacionActualResumen ?: null,
            'alergias_referidas'          => $this->alergiasReferidas ?: null,
            'condicion_medica_general'    => $this->condicionMedicaGeneral ?: null,
            'estado_neurologico_basico'   => $this->estadoNeurologicoBasico ?: null,
            'nivel_dependencia_sugerido'  => $this->nivelDependenciaSugerido ?: null,
            'resultado_admision'          => $this->resultadoAdmision,
            'motivo_decision'             => $this->motivoDecision,
            'recomendacion_medica'        => $this->recomendacionMedica ?: null,
            'requiere_seguimiento_especial'=> $this->requiereSeguimientoEsp,
            'registrado_por'              => Auth::id(),
            'estado'                      => $this->estadoForm,
        ];

        if ($this->editandoId) {
            ValoracionMedicaAdmision::findOrFail($this->editandoId)->update($datos);
        } else {
            ValoracionMedicaAdmision::create($datos);

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
        $valoraciones = \Illuminate\Support\Facades\Schema::hasTable('valoraciones_medicas_admision')
            ? ValoracionMedicaAdmision::with(['adultoMayor', 'registradoPor'])
                ->when($this->search, fn($q) =>
                    $q->whereHas('adultoMayor', fn($sq) =>
                        $sq->where('nombres', 'ilike', '%' . $this->search . '%')
                          ->orWhere('ap_paterno', 'ilike', '%' . $this->search . '%')
                    )
                )
                ->when($this->filtroEstado, fn($q) => $q->where('estado', $this->filtroEstado))
                ->when($this->filtroResult, fn($q) => $q->where('resultado_admision', $this->filtroResult))
                ->orderByDesc('fecha')->orderByDesc('hora')
                ->paginate(12)
            : new \Illuminate\Pagination\LengthAwarePaginator(collect(), 0, 12);

        $detalle = null;
        if ($this->viendoId && \Illuminate\Support\Facades\Schema::hasTable('valoraciones_medicas_admision')) {
            $detalle = ValoracionMedicaAdmision::with('adultoMayor','registradoPor','valoracionEnfermeria')->find($this->viendoId);
        }

        return view('livewire.admin.enfermeria.valoracion-medica-panel', [
            'valoraciones' => $valoraciones,
            'adultos'      => AdultoMayor::select('cod_am','nombres','ap_paterno','ap_materno')
                ->whereNull('archivado_en')->orderBy('ap_paterno')->get(),
            'detalle'      => $detalle,
        ])->layout('layouts.sistema');
    }
}
