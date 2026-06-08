<?php

namespace App\Livewire\Admin\Enfermeria;

use App\Models\AdultoMayor;
use App\Models\ValoracionEnfermeriaAdmision;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ValoracionEnfermeriaPanel extends Component
{
    use WithPagination;

    // ── Filtros ───────────────────────────────────────────────────────────────
    public string $search        = '';
    public string $filtroEstado  = '';

    // ── Modal ─────────────────────────────────────────────────────────────────
    public bool   $modalForm  = false;
    public bool   $modalVer   = false;
    public ?int   $editandoId = null;
    public ?int   $viendoId   = null;

    // ── Campos ────────────────────────────────────────────────────────────────
    public string $codAm                     = '';
    public string $fecha                     = '';
    public string $hora                      = '';
    public string $estadoGeneral             = '';
    public string $nivelConciencia           = '';
    public string $orientacion               = '';
    public string $comunicacion              = '';
    public bool   $dolorActual               = false;
    public string $intensidadDolor           = '';
    public string $ubicacionDolor            = '';
    public string $movilidad                 = '';
    public string $usaApoyoMovilidad         = '';
    public string $riesgoCaida               = '';
    public string $pielEstado                = '';
    public bool   $presentaHeridas           = false;
    public string $ubicacionHeridas          = '';
    public string $higieneIngreso            = '';
    public bool   $requiereAtencionInmediata = false;
    public bool   $puedePasarValMedica       = false;
    public string $recomendacionEnfermeria   = '';
    public string $observacion               = '';
    public string $estadoForm                = 'BORRADOR';

    public function updatingSearch(): void   { $this->resetPage(); }
    public function updatingFiltroEstado(): void { $this->resetPage(); }

    public function abrirCrear(): void
    {
        $this->resetForm();
        $this->fecha     = today()->format('Y-m-d');
        $this->hora      = now()->format('H:i');
        $this->estadoForm= 'BORRADOR';
        $this->modalForm = true;
    }

    public function abrirVer(int $id): void
    {
        $this->viendoId  = $id;
        $this->modalVer  = true;
    }

    public function guardar(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('valoraciones_enfermeria_admision')) {
            return;
        }

        $this->validate([
            'codAm'   => 'required|exists:adulto_mayor,cod_am',
            'fecha'   => 'required|date',
            'hora'    => 'required',
            'estadoGeneral'    => 'nullable|in:BUENO,REGULAR,MALO,CRITICO',
            'nivelConciencia'  => 'nullable|in:ALERTA,SOMNOLIENTO,ESTUPOROSO,COMATOSO',
            'riesgoCaida'      => 'nullable|in:BAJO,MEDIO,ALTO',
            'estadoForm'       => 'required|in:BORRADOR,COMPLETADA',
        ], [
            'codAm.required' => 'Seleccione un adulto mayor.',
            'codAm.exists'   => 'El adulto mayor no es válido.',
            'fecha.required' => 'La fecha es obligatoria.',
            'hora.required'  => 'La hora es obligatoria.',
        ]);

        $datos = [
            'cod_am'                        => $this->codAm,
            'fecha'                         => $this->fecha,
            'hora'                          => strlen($this->hora) === 5 ? $this->hora . ':00' : $this->hora,
            'estado_general'                => $this->estadoGeneral ?: null,
            'nivel_conciencia'              => $this->nivelConciencia ?: null,
            'orientacion'                   => $this->orientacion ?: null,
            'comunicacion'                  => $this->comunicacion ?: null,
            'dolor_actual'                  => $this->dolorActual,
            'intensidad_dolor'              => $this->intensidadDolor !== '' ? (int) $this->intensidadDolor : null,
            'ubicacion_dolor'               => $this->ubicacionDolor ?: null,
            'movilidad'                     => $this->movilidad ?: null,
            'usa_apoyo_movilidad'           => $this->usaApoyoMovilidad ?: null,
            'riesgo_caida'                  => $this->riesgoCaida ?: null,
            'piel_estado'                   => $this->pielEstado ?: null,
            'presenta_heridas'              => $this->presentaHeridas,
            'ubicacion_heridas'             => $this->ubicacionHeridas ?: null,
            'higiene_ingreso'               => $this->higieneIngreso ?: null,
            'requiere_atencion_inmediata'   => $this->requiereAtencionInmediata,
            'puede_pasar_valoracion_medica' => $this->puedePasarValMedica,
            'recomendacion_enfermeria'      => $this->recomendacionEnfermeria ?: null,
            'observacion'                   => $this->observacion ?: null,
            'registrado_por'                => Auth::id(),
            'estado'                        => $this->estadoForm,
        ];

        if ($this->editandoId) {
            ValoracionEnfermeriaAdmision::findOrFail($this->editandoId)->update($datos);
            $msg = 'Valoración actualizada.';
        } else {
            ValoracionEnfermeriaAdmision::create($datos);
            $msg = 'Valoración de enfermería registrada.';
        }

        $this->modalForm = false;
        $this->resetForm();
        $this->dispatch('swal', ['icon' => 'success', 'title' => $msg]);
    }

    public function cerrarModales(): void
    {
        $this->modalForm = false;
        $this->modalVer  = false;
        $this->viendoId  = null;
        $this->resetForm();
        $this->resetValidation();
    }

    private function resetForm(): void
    {
        $this->codAm                     = '';
        $this->fecha                     = '';
        $this->hora                      = '';
        $this->estadoGeneral             = '';
        $this->nivelConciencia           = '';
        $this->orientacion               = '';
        $this->comunicacion              = '';
        $this->dolorActual               = false;
        $this->intensidadDolor           = '';
        $this->ubicacionDolor            = '';
        $this->movilidad                 = '';
        $this->usaApoyoMovilidad         = '';
        $this->riesgoCaida               = '';
        $this->pielEstado                = '';
        $this->presentaHeridas           = false;
        $this->ubicacionHeridas          = '';
        $this->higieneIngreso            = '';
        $this->requiereAtencionInmediata = false;
        $this->puedePasarValMedica       = false;
        $this->recomendacionEnfermeria   = '';
        $this->observacion               = '';
        $this->estadoForm                = 'BORRADOR';
        $this->editandoId                = null;
        $this->resetValidation();
    }

    public function render()
    {
        $valoraciones = \Illuminate\Support\Facades\Schema::hasTable('valoraciones_enfermeria_admision')
            ? ValoracionEnfermeriaAdmision::with(['adultoMayor', 'registradoPor'])
                ->when($this->search, fn($q) =>
                    $q->whereHas('adultoMayor', fn($sq) =>
                        $sq->where('nombres', 'ilike', '%' . $this->search . '%')
                          ->orWhere('ap_paterno', 'ilike', '%' . $this->search . '%')
                    )
                )
                ->when($this->filtroEstado, fn($q) => $q->where('estado', $this->filtroEstado))
                ->orderByDesc('fecha')
                ->orderByDesc('hora')
                ->paginate(12)
            : new \Illuminate\Pagination\LengthAwarePaginator(collect(), 0, 12);

        $detalle = null;
        if ($this->viendoId && \Illuminate\Support\Facades\Schema::hasTable('valoraciones_enfermeria_admision')) {
            $detalle = ValoracionEnfermeriaAdmision::with('adultoMayor','registradoPor')->find($this->viendoId);
        }

        return view('livewire.admin.enfermeria.valoracion-enfermeria-panel', [
            'valoraciones' => $valoraciones,
            'adultos'      => AdultoMayor::select('cod_am','nombres','ap_paterno','ap_materno')
                ->whereNull('archivado_en')->orderBy('ap_paterno')->get(),
            'detalle'      => $detalle,
        ])->layout('layouts.sistema');
    }
}
