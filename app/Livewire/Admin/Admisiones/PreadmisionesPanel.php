<?php

namespace App\Livewire\Admin\Admisiones;

use App\Models\AdultoMayor;
use App\Models\DocumentoPreadmision;
use App\Models\DocumentoAdultoMayor;
use App\Models\EstadoAdulto;
use App\Models\Familiar;
use App\Models\HistorialEstadoAdulto;
use App\Models\Preadmision;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class PreadmisionesPanel extends Component
{
    use WithPagination;

    public string $search = '';
    public string $estado = '';
    public string $prioridad = '';
    public string $enfermero_id = '';
    public string $fecha_inicio = '';
    public string $fecha_fin = '';
    public bool $soloRechazadas = false;

    public bool $modalDocumentos = false;
    public ?string $codPreSeleccionada = null;
    public array $documentosModal = [];
    public bool $modalRechazo = false;
    public ?string $codPreRechazo = null;
    public string $motivo_rechazo = '';
    public string $observacion_rechazo = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'estado' => ['except' => ''],
        'prioridad' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->soloRechazadas = request()->routeIs('admin.admisiones.preadmisiones.rechazadas');

        if ($this->soloRechazadas) {
            $this->estado = 'RECHAZADA';
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingEstado(): void
    {
        $this->resetPage();
    }

    public function updatingPrioridad(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['search', 'estado', 'prioridad', 'enfermero_id', 'fecha_inicio', 'fecha_fin']);

        if ($this->soloRechazadas) {
            $this->estado = 'RECHAZADA';
        }

        $this->resetPage();
    }

    public function verDocumentos(string $codPre): void
    {
        $this->codPreSeleccionada = $codPre;
        $this->documentosModal = DocumentoPreadmision::where('cod_pre', $codPre)
            ->orderBy('es_institucional')
            ->orderBy('created_at')
            ->get()
            ->toArray();
        $this->modalDocumentos = true;
    }

    public function cerrarModalDocumentos(): void
    {
        $this->modalDocumentos = false;
        $this->codPreSeleccionada = null;
        $this->documentosModal = [];
    }

    public function abrirModalRechazo(string $codPre): void
    {
        $this->codPreRechazo = $codPre;
        $this->motivo_rechazo = '';
        $this->observacion_rechazo = '';
        $this->modalRechazo = true;
    }

    public function cerrarModalRechazo(): void
    {
        $this->modalRechazo = false;
        $this->codPreRechazo = null;
        $this->motivo_rechazo = '';
        $this->observacion_rechazo = '';
        $this->resetValidation(['motivo_rechazo', 'observacion_rechazo']);
    }

    public function aprobar(string $codPre): void
    {
        $preadmision = Preadmision::query()->findOrFail($codPre);

        if ($preadmision->cod_am_generado) {
            $this->dispatch('swal', [
                'title' => 'Ya convertida',
                'text' => "La preadmision ya genero el adulto mayor {$preadmision->cod_am_generado}.",
                'icon' => 'info',
            ]);
            return;
        }

        if ($preadmision->estado === 'RECHAZADA') {
            $this->dispatch('swal', [
                'title' => 'Operacion no permitida',
                'text' => 'Una preadmision rechazada no se aprueba desde este panel.',
                'icon' => 'warning',
            ]);
            return;
        }

        try {
            DB::beginTransaction();

            if (AdultoMayor::where('ci', $preadmision->ci)->where('expedicion_ci', $preadmision->expedicion_ci)->exists()) {
                throw new \RuntimeException('Ya existe un adulto mayor registrado con el mismo CI.');
            }

            $estadoInicial = EstadoAdulto::firstOrCreate(
                ['estado' => 'PENDIENTE_VALORACION_INICIAL']
            );

            $adulto = AdultoMayor::create([
                'nombres' => $preadmision->nombres,
                'ap_paterno' => $preadmision->ap_paterno,
                'ap_materno' => $preadmision->ap_materno,
                'ci' => $preadmision->ci,
                'expedicion_ci' => $preadmision->expedicion_ci,
                'fecha_nac' => $preadmision->fecha_nac,
                'genero' => $preadmision->genero,
                'estado_civil' => $preadmision->estado_civil,
                'telefono' => $preadmision->telefono,
                'tiene_celular' => filled($preadmision->celular),
                'celular' => $preadmision->celular,
                'sabe_usar_whatsapp' => false,
                'telefono_fijo' => null,
                'departamento_residencia' => $preadmision->departamento_residencia,
                'ciudad_municipio' => $preadmision->ciudad_municipio,
                'zona' => $preadmision->zona,
                'calle' => $preadmision->calle,
                'fecha_ing' => now()->toDateString(),
                'hora_ing' => now()->format('H:i:s'),
                'tipo_ing' => $preadmision->tipo_ingreso ?: 'REGULAR',
                'permanencia' => $preadmision->permanencia ?: 'PERMANENTE',
                'nivel_educat' => 'NO ESPECIFICADO',
                'grupo_sanguineo' => 'NO ESPECIFICADO',
                'factor_rh' => null,
                'alergias' => 'NO ESPECIFICADO',
                'seguro_salud' => 'NO ESPECIFICADO',
                'contacto_emergencia_nombre' => $preadmision->familiar_completo,
                'contacto_emergencia_parentesco' => $preadmision->familiar_parentesco,
                'contacto_emergencia_celular' => $preadmision->familiar_celular,
                'contacto_emergencia_direccion' => $preadmision->familiar_direccion,
                'responsable_principal' => $preadmision->familiar_completo,
                'autorizado_informacion_medica' => true,
                'consentimiento_datos' => true,
                'observaciones' => trim(implode(' | ', array_filter([
                    'Generado desde preadmision ' . $preadmision->cod_pre,
                    $preadmision->descripcion_caso,
                ]))),
                'cod_est_adul' => $estadoInicial->cod_est_adul,
                'motivo_ingreso' => $preadmision->motivo_ingreso,
                'procedencia_ingreso' => $preadmision->procedencia_ingreso,
                'cod_pre_origen' => $preadmision->cod_pre,
            ]);

            $familiar = Familiar::firstOrNew([
                'ci' => $preadmision->familiar_ci ?: 'PRE-' . $preadmision->cod_pre,
            ]);

            $familiar->fill([
                'nombres' => $preadmision->familiar_nombres,
                'ap_paterno' => $preadmision->familiar_ap_paterno ?: 'NO REGISTRADO',
                'ap_materno' => $preadmision->familiar_ap_materno,
                'ci' => $preadmision->familiar_ci ?: 'PRE-' . $preadmision->cod_pre,
                'parentesco_vinculo' => $preadmision->familiar_parentesco,
                'telefono' => null,
                'celular' => $preadmision->familiar_celular,
                'correo' => $preadmision->familiar_correo,
                'direccion' => $preadmision->familiar_direccion,
                'zona' => null,
                'es_responsable' => true,
                'estado' => 'ACTIVO',
                'observaciones' => 'Generado desde preadmision ' . $preadmision->cod_pre,
                'cod_usu' => auth()->user()?->cod_usu,
            ]);
            $familiar->save();

            if (! $adulto->familiares()->where('familiares.cod_fam', $familiar->cod_fam)->exists()) {
                $adulto->familiares()->attach($familiar->cod_fam, [
                    'parentesco_vinculo' => $preadmision->familiar_parentesco,
                    'es_responsable' => true,
                    'estado' => 'ACTIVO',
                    'observaciones' => 'Vinculo creado desde preadmision ' . $preadmision->cod_pre,
                ]);
            }

            $documentoRespaldo = null;
            foreach ($preadmision->documentos as $documentoPreadmision) {
                $documentoAdulto = DocumentoAdultoMayor::create([
                    'cod_am' => $adulto->cod_am,
                    'nombre' => $documentoPreadmision->nombre_documento,
                    'tipo_documento' => $documentoPreadmision->tipo_documento,
                    'ruta_archivo' => $documentoPreadmision->archivo_path ?: 'PENDIENTE_PREADMISION',
                    'fecha_subida' => optional($documentoPreadmision->created_at)->toDateString() ?: now()->toDateString(),
                    'estado' => in_array($documentoPreadmision->estado, ['PENDIENTE', 'PENDIENTE_48H']) ? 'PENDIENTE' : 'ACTIVO',
                    'observaciones' => $documentoPreadmision->observaciones,
                    'modulo_ref' => 'PREADMISION',
                ]);

                $documentoRespaldo ??= $documentoAdulto;
            }

            HistorialEstadoAdulto::create([
                'cod_am' => $adulto->cod_am,
                'estado_anterior' => null,
                'estado_nuevo' => $estadoInicial->cod_est_adul,
                'fecha_cambio' => now(),
                'motivo' => 'Ingreso aprobado desde preadmision.',
                'documento_respaldo' => $documentoRespaldo?->cod_doc_am,
                'cambiado_por' => auth()->user()?->cod_usu,
                'observacion' => 'Preadmision origen: ' . $preadmision->cod_pre,
            ]);

            $preadmision->update([
                'estado' => 'APROBADA',
                'fecha_aprobacion' => now(),
                'aprobado_por' => auth()->user()?->cod_usu,
                'motivo_rechazo' => null,
                'observacion_rechazo' => null,
                'fecha_rechazo' => null,
                'rechazado_por' => null,
                'cod_am_generado' => $adulto->cod_am,
                'cod_fam_generado' => $familiar->cod_fam,
            ]);

            activity('Admisiones')
                ->causedBy(auth()->user())
                ->performedOn($preadmision)
                ->log("Preadmision {$preadmision->cod_pre} aprobada y convertida en adulto {$adulto->cod_am}.");

            DB::commit();

            $this->dispatch('swal', [
                'title' => 'Preadmision aprobada',
                'text' => "Se genero el adulto mayor {$adulto->cod_am} y se vinculo el familiar {$familiar->cod_fam}.",
                'icon' => 'success',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->dispatch('swal', [
                'title' => 'Error al aprobar',
                'text' => 'No se pudo convertir la preadmision: ' . $e->getMessage(),
                'icon' => 'error',
            ]);
        }
    }

    public function rechazar(): void
    {
        $this->validate([
            'codPreRechazo' => ['required', 'exists:preadmisiones,cod_pre'],
            'motivo_rechazo' => ['required', 'string', 'min:4', 'max:150'],
            'observacion_rechazo' => ['nullable', 'string', 'max:1000'],
        ]);

        $preadmision = Preadmision::query()->findOrFail($this->codPreRechazo);

        $preadmision->update([
            'estado' => 'RECHAZADA',
            'motivo_rechazo' => mb_strtoupper(trim($this->motivo_rechazo), 'UTF-8'),
            'observacion_rechazo' => trim($this->observacion_rechazo) ?: null,
            'fecha_rechazo' => now(),
            'rechazado_por' => auth()->user()?->cod_usu,
            'fecha_aprobacion' => null,
            'aprobado_por' => null,
        ]);

        activity('Admisiones')
            ->causedBy(auth()->user())
            ->performedOn($preadmision)
            ->log("Preadmision {$preadmision->cod_pre} rechazada.");

        $this->cerrarModalRechazo();

        $this->dispatch('swal', [
            'title' => 'Preadmision rechazada',
            'text' => 'El caso fue movido a la seccion de rechazadas sin duplicar registros.',
            'icon' => 'success',
        ]);
    }

    public function exportarReportePdf(): void
    {
        if (! auth()->user()->can('admisiones.ver_dashboard') && ! auth()->user()->hasRole('SUPERADMINISTRADOR')) {
            abort(403);
        }

        activity('Admisiones')
            ->causedBy(auth()->user())
            ->log('Solicito reporte de preadmisiones.');

        $this->dispatch('swal', [
            'title' => 'Reporte en preparacion',
            'text' => 'La consulta de preadmisiones esta lista. La plantilla PDF queda pendiente de integracion.',
            'icon' => 'info',
        ]);
    }

    public function render()
    {
        $query = Preadmision::query()
            ->with(['enfermero', 'documentos', 'adultoGenerado', 'familiarGenerado'])
            ->withCount('documentos');

        if ($this->search !== '') {
            $search = trim($this->search);
            $query->where(function ($q) use ($search) {
                $q->where('cod_pre', 'like', "%{$search}%")
                    ->orWhere('nombres', 'like', "%{$search}%")
                    ->orWhere('ap_paterno', 'like', "%{$search}%")
                    ->orWhere('ap_materno', 'like', "%{$search}%")
                    ->orWhere('ci', 'like', "%{$search}%")
                    ->orWhere('familiar_nombres', 'like', "%{$search}%")
                    ->orWhere('familiar_ap_paterno', 'like', "%{$search}%");
            });
        }

        if ($this->estado !== '') {
            $query->where('estado', $this->estado);
        }

        if ($this->prioridad !== '') {
            $query->where('prioridad', $this->prioridad);
        }

        if ($this->enfermero_id !== '') {
            $query->where('enfermero_asignado', $this->enfermero_id);
        }

        if ($this->fecha_inicio !== '') {
            $query->whereDate('fecha_solicitud', '>=', $this->fecha_inicio);
        }

        if ($this->fecha_fin !== '') {
            $query->whereDate('fecha_solicitud', '<=', $this->fecha_fin);
        }

        if ($this->soloRechazadas) {
            $query->where('estado', 'RECHAZADA');
        }

        $metricas = [
            'total' => Preadmision::count(),
            'asignadas' => Preadmision::where('estado', 'PREADMISION_ASIGNADA')->count(),
            'aprobadas' => Preadmision::where('estado', 'APROBADA')->count(),
            'rechazadas' => Preadmision::where('estado', 'RECHAZADA')->count(),
            'alta_prioridad' => Preadmision::whereIn('prioridad', ['ALTA', 'CRITICA'])->count(),
            'con_documentos' => Preadmision::where('documentos_iniciales_completos', true)->count(),
            'sin_enfermero' => Preadmision::whereNull('enfermero_asignado')->count(),
        ];

        return view('livewire.admin.admisiones.preadmisiones-panel', [
            'preadmisiones' => $query->latest('fecha_asignacion')->paginate(10),
            'metricas' => $metricas,
            'enfermeros' => User::role('ENFERMEROS')
                ->where('estado', 'ACTIVO')
                ->orderBy('ap_paterno')
                ->get(['cod_usu', 'nombres', 'ap_paterno', 'ap_materno']),
        ])->layout('layouts.sistema');
    }
}
