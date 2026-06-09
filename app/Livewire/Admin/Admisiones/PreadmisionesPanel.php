<?php

namespace App\Livewire\Admin\Admisiones;

use App\Models\AdultoMayor;
use App\Models\DocumentoAdultoMayor;
use App\Models\DocumentoPreadmision;
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
        $this->reset([
            'search',
            'estado',
            'prioridad',
            'enfermero_id',
            'fecha_inicio',
            'fecha_fin',
        ]);

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
        $this->resetValidation([
            'motivo_rechazo',
            'observacion_rechazo',
        ]);
    }

    public function aprobar(string $codPre): void
    {
        $preadmision = Preadmision::query()
            ->with(['documentos'])
            ->findOrFail($codPre);

        if ($preadmision->cod_am_generado) {
            $this->dispatch('swal', [
                'title' => 'Ya convertida',
                'text' => "La preadmisión ya generó el adulto mayor {$preadmision->cod_am_generado}.",
                'icon' => 'info',
            ]);

            return;
        }

        if ($preadmision->estado === 'RECHAZADA') {
            $this->dispatch('swal', [
                'title' => 'Operación no permitida',
                'text' => 'Una preadmisión rechazada no puede aprobarse desde este panel.',
                'icon' => 'warning',
            ]);

            return;
        }

        // Condición obligatoria: valoración médica debe estar finalizada antes de admitir
        if ($preadmision->estado !== 'VALORACION_MEDICA_FINALIZADA') {
            $this->dispatch('swal', [
                'title' => 'No se puede admitir todavía',
                'text' => 'La preadmisión debe completar valoración médica antes de ser admitida. Estado actual: ' . $preadmision->estado,
                'icon' => 'warning',
            ]);

            return;
        }

        // Solo médico o administrador pueden tomar la decisión de admisión
        $rolesPermitidos = ['MEDICO GENERAL/GERIATRA', 'ADMINISTRADOR', 'SUPERADMINISTRADOR'];
        $tieneRolPermitido = collect($rolesPermitidos)->contains(
            fn ($rol) => auth()->user()->hasRole($rol)
        );

        if (! $tieneRolPermitido) {
            $this->dispatch('swal', [
                'title' => 'Sin permiso',
                'text' => 'Solo el médico o un administrador pueden aprobar el ingreso de una preadmisión.',
                'icon' => 'warning',
            ]);

            return;
        }

        try {
            DB::beginTransaction();

            $ciNormalizado = $this->limitarTexto($preadmision->ci, 20);
            $expedicionNormalizada = $this->limitarTexto($preadmision->expedicion_ci, 2);

            if (
                AdultoMayor::where('ci', $ciNormalizado)
                    ->where('expedicion_ci', $expedicionNormalizada)
                    ->exists()
            ) {
                throw new \RuntimeException('Ya existe un adulto mayor registrado con el mismo CI.');
            }

            // Las valoraciones ya ocurrieron en la preadmisión. El adulto nace ADMITIDO.
            $estadoInicial = EstadoAdulto::firstOrCreate([
                'estado' => 'ADMITIDO',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Normalización segura de datos
            |--------------------------------------------------------------------------
            | No tocamos la base de datos. Adaptamos los datos antes de insertar para
            | evitar errores por varchar corto, booleanos mal llenados o teléfonos largos.
            */

            $motivoOriginal = $this->limpiarTexto($preadmision->motivo_ingreso);
            $procedenciaOriginal = $this->limpiarTexto($preadmision->procedencia_ingreso);
            $descripcionCaso = $this->limpiarTexto($preadmision->descripcion_caso);

            $tipoIngreso = $this->limitarTexto($preadmision->tipo_ingreso ?: 'REGULAR', 100) ?: 'REGULAR';
            $permanencia = $this->limitarTexto($preadmision->permanencia ?: 'PERMANENTE', 50) ?: 'PERMANENTE';

            $telefonoAdulto = $this->soloNumeros($preadmision->telefono, 20);
            $celularAdulto = $this->soloNumeros($preadmision->celular, 8);
            $celularFamiliar = $this->soloNumeros($preadmision->familiar_celular, 8);

            $motivoCorto = $this->clasificarMotivoIngreso($motivoOriginal);
            $procedenciaCorta = $this->clasificarProcedenciaIngreso($procedenciaOriginal);

            $observacionesAdulto = trim(implode(' | ', array_filter([
                'Generado desde preadmisión ' . $preadmision->cod_pre,
                $descripcionCaso ?: null,
                $motivoOriginal ? 'Motivo de ingreso original: ' . $motivoOriginal : null,
                $procedenciaOriginal ? 'Procedencia original: ' . $procedenciaOriginal : null,
            ])));

            /*
            |--------------------------------------------------------------------------
            | Crear adulto mayor
            |--------------------------------------------------------------------------
            */

            $adulto = AdultoMayor::create([
                'nombres' => $this->limitarTexto($preadmision->nombres, 100) ?: 'SIN NOMBRE',
                'ap_paterno' => $this->limitarTexto($preadmision->ap_paterno, 80) ?: 'NO REGISTRADO',
                'ap_materno' => $this->limitarTexto($preadmision->ap_materno, 80),

                'ci' => $ciNormalizado,
                'expedicion_ci' => $expedicionNormalizada,

                'fecha_nac' => $preadmision->fecha_nac,
                'genero' => $this->limitarTexto($preadmision->genero, 100) ?: 'NO ESPECIFICADO',
                'estado_civil' => $this->limitarTexto($preadmision->estado_civil, 100),

                'telefono' => $telefonoAdulto,
                'tiene_celular' => filled($celularAdulto),
                'celular' => $celularAdulto,
                'sabe_usar_whatsapp' => false,
                'telefono_fijo' => null,

                'departamento_residencia' => $this->limitarTexto($preadmision->departamento_residencia, 50),
                'ciudad_municipio' => $this->limitarTexto($preadmision->ciudad_municipio, 100),
                'zona' => $this->limitarTexto($preadmision->zona, 100),
                'calle' => $this->limitarTexto($preadmision->calle, 150),

                'fecha_ing' => now()->toDateString(),
                'hora_ing' => now()->format('H:i:s'),

                'tipo_ing' => $tipoIngreso,
                'permanencia' => $permanencia,

                'nivel_educat' => 'NO ESPECIFICADO',

                /*
                 * Campos cortos:
                 * grupo_sanguineo suele ser varchar(3)
                 * factor_rh suele ser varchar(1)
                 * Por eso no se debe guardar "NO ESPECIFICADO".
                 */
                'grupo_sanguineo' => null,
                'factor_rh' => null,

                'alergias' => 'NO ESPECIFICADO',
                'seguro_salud' => 'NO ESPECIFICADO',

                'contacto_emergencia_nombre' => $this->limitarTexto($preadmision->familiar_completo, 150),
                'contacto_emergencia_parentesco' => $this->limitarTexto($preadmision->familiar_parentesco, 80),
                'contacto_emergencia_celular' => $celularFamiliar,
                'contacto_emergencia_direccion' => $this->limitarTexto($preadmision->familiar_direccion, 200),

                /*
                 * Este campo en adulto_mayor es booleano.
                 * No debe guardar el nombre del familiar.
                 */
                'responsable_principal' => true,

                'autorizado_informacion_medica' => true,
                'consentimiento_datos' => true,

                'observaciones' => $observacionesAdulto ?: null,

                'cod_est_adul' => $estadoInicial->cod_est_adul,

                /*
                 * Clasificaciones cortas para evitar errores de varchar.
                 * El texto completo se conserva en observaciones.
                 */
                'motivo_ingreso' => $motivoCorto,
                'procedencia_ingreso' => $procedenciaCorta,

                'cod_pre_origen' => $this->limitarTexto($preadmision->cod_pre, 20),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Crear o actualizar familiar responsable
            |--------------------------------------------------------------------------
            */

            $ciFamiliar = $this->limitarTexto(
                $preadmision->familiar_ci ?: 'PRE-' . $preadmision->cod_pre,
                20
            );

            $familiar = Familiar::firstOrNew([
                'ci' => $ciFamiliar,
            ]);

            $familiar->fill([
                'nombres' => $this->limitarTexto($preadmision->familiar_nombres, 120) ?: 'SIN NOMBRE',
                'ap_paterno' => $this->limitarTexto($preadmision->familiar_ap_paterno ?: 'NO REGISTRADO', 80),
                'ap_materno' => $this->limitarTexto($preadmision->familiar_ap_materno, 80),
                'ci' => $ciFamiliar,
                'parentesco_vinculo' => $this->limitarTexto($preadmision->familiar_parentesco, 80),
                'telefono' => null,
                'celular' => $celularFamiliar,
                'correo' => $this->limitarTexto($preadmision->familiar_correo, 140),
                'direccion' => $this->limitarTexto($preadmision->familiar_direccion, 200),
                'zona' => null,
                'es_responsable' => true,
                'estado' => 'ACTIVO',
                'observaciones' => 'Generado desde preadmisión ' . $preadmision->cod_pre,
                'cod_usu' => auth()->user()?->cod_usu,
            ]);

            $familiar->save();

            if (! $adulto->familiares()->where('familiares.cod_fam', $familiar->cod_fam)->exists()) {
                $adulto->familiares()->attach($familiar->cod_fam, [
                    'parentesco_vinculo' => $this->limitarTexto($preadmision->familiar_parentesco, 80),
                    'es_responsable' => true,
                    'estado' => 'ACTIVO',
                    'observaciones' => 'Vínculo creado desde preadmisión ' . $preadmision->cod_pre,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Migrar documentos de preadmisión hacia documentos del adulto mayor
            |--------------------------------------------------------------------------
            */

            $documentoRespaldo = null;

            foreach ($preadmision->documentos as $documentoPreadmision) {
                $documentoAdulto = DocumentoAdultoMayor::create([
                    'cod_am' => $adulto->cod_am,
                    'nombre' => $this->limitarTexto($documentoPreadmision->nombre_documento, 180) ?: 'Documento de preadmisión',
                    'tipo_documento' => $this->limitarTexto($documentoPreadmision->tipo_documento, 80) ?: 'PREADMISION',
                    'ruta_archivo' => $documentoPreadmision->archivo_path ?: 'PENDIENTE_PREADMISION',
                    'fecha_subida' => optional($documentoPreadmision->created_at)->toDateString() ?: now()->toDateString(),
                    'estado' => in_array($documentoPreadmision->estado, ['PENDIENTE', 'PENDIENTE_48H'], true)
                        ? 'PENDIENTE'
                        : 'ACTIVO',
                    'observaciones' => $documentoPreadmision->observaciones,
                    'modulo_ref' => 'PREADMISION',
                ]);

                $documentoRespaldo ??= $documentoAdulto;
            }

            /*
            |--------------------------------------------------------------------------
            | Registrar historial de estado
            |--------------------------------------------------------------------------
            */

            HistorialEstadoAdulto::create([
                'cod_am' => $adulto->cod_am,
                'estado_anterior' => null,
                'estado_nuevo' => $estadoInicial->cod_est_adul,
                'fecha_cambio' => now(),
                'motivo' => 'Ingreso aprobado desde preadmisión.',
                'documento_respaldo' => $documentoRespaldo?->cod_doc_am,
                'cambiado_por' => auth()->user()?->cod_usu,
                'observacion' => 'Preadmisión origen: ' . $preadmision->cod_pre,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Actualizar preadmisión
            |--------------------------------------------------------------------------
            */

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
                ->log("Preadmisión {$preadmision->cod_pre} aprobada y convertida en adulto {$adulto->cod_am}.");

            DB::commit();

            $this->dispatch('swal', [
                'title' => 'Preadmisión aprobada',
                'text' => "Se generó el adulto mayor {$adulto->cod_am} y se vinculó el familiar {$familiar->cod_fam}.",
                'icon' => 'success',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            $this->dispatch('swal', [
                'title' => 'Error al aprobar',
                'text' => 'No se pudo convertir la preadmisión: ' . $e->getMessage(),
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
            ->log("Preadmisión {$preadmision->cod_pre} rechazada.");

        $this->cerrarModalRechazo();

        $this->dispatch('swal', [
            'title' => 'Preadmisión rechazada',
            'text' => 'El caso fue movido a la sección de rechazadas sin duplicar registros.',
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
            ->log('Solicitó reporte de preadmisiones.');

        $this->dispatch('swal', [
            'title' => 'Reporte en preparación',
            'text' => 'La consulta de preadmisiones está lista. La plantilla PDF queda pendiente de integración.',
            'icon' => 'info',
        ]);
    }

    private function limpiarTexto($valor): string
    {
        return trim((string) $valor);
    }

    private function limitarTexto($valor, int $max): ?string
    {
        $texto = trim((string) $valor);

        if ($texto === '') {
            return null;
        }

        return mb_substr($texto, 0, $max, 'UTF-8');
    }

    private function soloNumeros($valor, int $max): ?string
    {
        $numero = preg_replace('/\D/', '', (string) $valor);

        if ($numero === '') {
            return null;
        }

        return substr($numero, 0, $max);
    }

    private function textoContiene(?string $texto, array $palabras): bool
    {
        $texto = mb_strtolower((string) $texto, 'UTF-8');

        foreach ($palabras as $palabra) {
            if (str_contains($texto, mb_strtolower($palabra, 'UTF-8'))) {
                return true;
            }
        }

        return false;
    }

    private function clasificarMotivoIngreso(?string $motivo): string
    {
        if ($this->textoContiene($motivo, [
            'cardio',
            'cardiopatía',
            'cardiopatia',
            'insuficiencia',
            'médic',
            'medic',
            'salud',
            'tratamiento',
            'control',
            'clínic',
            'clinic',
            'hospital',
        ])) {
            return 'MEDICO';
        }

        if ($this->textoContiene($motivo, [
            'caida',
            'caída',
            'fractura',
            'golpe',
            'accidente',
        ])) {
            return 'CAIDA';
        }

        if ($this->textoContiene($motivo, [
            'cogn',
            'memoria',
            'olvido',
            'desorient',
            'alzheimer',
            'demencia',
        ])) {
            return 'COGNITIVO';
        }

        if ($this->textoContiene($motivo, [
            'famil',
            'abandono',
            'cuidador',
            'responsable',
        ])) {
            return 'FAMILIAR';
        }

        return 'GENERAL';
    }

    private function clasificarProcedenciaIngreso(?string $procedencia): string
    {
        if ($this->textoContiene($procedencia, [
            'médic',
            'medic',
            'cardiólogo',
            'cardiologo',
            'doctor',
            'hospital',
            'clínica',
            'clinica',
            'centro de salud',
            'derivación médica',
            'derivacion medica',
        ])) {
            return 'MEDICA';
        }

        if ($this->textoContiene($procedencia, [
            'famil',
            'hijo',
            'hija',
            'hermano',
            'hermana',
            'sobrino',
            'sobrina',
        ])) {
            return 'FAMILIAR';
        }

        if ($this->textoContiene($procedencia, [
            'social',
            'trabajo social',
            'defensoría',
            'defensoria',
        ])) {
            return 'SOCIAL';
        }

        return 'GENERAL';
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