<?php

namespace App\Livewire\Admisiones;

use App\Actions\Admisiones\FormalizarAdmision;
use App\Models\Cama;
use App\Models\DocumentoPreadmision;
use App\Models\Habitacion;
use App\Models\Preadmision;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class PreadmisionesPanel extends Component
{
    use WithPagination;

    public string $search = '';

    public string $estado = '';

    public string $prioridad = '';

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

    public bool $modalDetalle = false;

    public ?Preadmision $solicitudDetalle = null;

    public bool $modalAdmision = false;

    public ?string $codPreAdmision = null;

    public string $cama_id = '';

    public string $habitacion_id = '';

    public string $fecha_ingreso = '';

    public string $hora_ingreso = '';

    public string $nivel_educativo = '';

    public string $grupo_sanguineo = '';

    public string $factor_rh = '';

    public string $alergias = '';

    public string $seguro_salud = '';

    public array $antecedentes = [];

    public string $restricciones_alimentarias = '';

    public string $hospitalizaciones = '';

    public string $cirugias = '';

    public string $observacion_medica = '';

    public bool $usa_whatsapp = false;

    public bool $autoriza_informacion_medica = false;

    public bool $consentimiento_datos = false;

    public string $observaciones_admision = '';

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

    public function verDetalle(string $codPre): void
    {
        $this->solicitudDetalle = Preadmision::query()
            ->with(['enfermero', 'documentos', 'aprobador', 'adultoGenerado.estado', 'adultoGenerado.habitacion', 'adultoGenerado.cama'])
            ->findOrFail($codPre);
        $this->modalDetalle = true;
    }

    public function cerrarDetalle(): void
    {
        $this->modalDetalle = false;
        $this->solicitudDetalle = null;
    }

    public function aprobar(string $codPre): void
    {
        $preadmision = Preadmision::query()->findOrFail($codPre);

        if ($preadmision->cod_am_generado || in_array($preadmision->estado, ['APROBADA', 'ADMITIDA'], true)) {
            $this->dispatch('swal', ['title' => 'Solicitud ya revisada', 'text' => 'Esta solicitud ya fue aprobada o admitida.', 'icon' => 'info']);

            return;
        }

        if ($preadmision->estado === 'RECHAZADA') {
            $this->dispatch('swal', ['title' => 'Operación no permitida', 'text' => 'Una solicitud rechazada conserva su decisión y no puede aprobarse directamente.', 'icon' => 'warning']);

            return;
        }

        abort_unless(auth()->user()?->hasAnyRole(['MEDICO GENERAL/GERIATRA', 'ADMINISTRADOR', 'SUPERADMINISTRADOR']), 403);

        $preadmision->update([
            'estado' => 'APROBADA',
            'fecha_aprobacion' => now(),
            'aprobado_por' => auth()->id(),
            'motivo_rechazo' => null,
            'observacion_rechazo' => null,
            'fecha_rechazo' => null,
            'rechazado_por' => null,
        ]);

        activity('Admisiones')->causedBy(auth()->user())->performedOn($preadmision)
            ->log('Solicitud de preadmisión aprobada; queda pendiente formalizar el ingreso.');

        $this->dispatch('swal', [
            'title' => 'Preadmisión aprobada',
            'text' => 'La solicitud fue aprobada. Aún no existe un residente activo; debe formalizar la admisión y asignar una cama.',
            'icon' => 'success',
        ]);
    }

    public function abrirAdmision(string $codPre): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['ADMINISTRADOR', 'SUPERADMINISTRADOR']), 403);
        $solicitud = Preadmision::query()->findOrFail($codPre);

        if ($solicitud->estado !== 'APROBADA' || $solicitud->cod_am_generado) {
            $this->dispatch('swal', ['title' => 'Admisión no disponible', 'text' => 'Solo puede admitirse una solicitud aprobada que todavía no tenga residente asociado.', 'icon' => 'warning']);

            return;
        }

        $this->resetValidation();
        $this->codPreAdmision = $solicitud->cod_pre;
        $this->habitacion_id = '';
        $this->cama_id = '';
        $this->fecha_ingreso = now()->toDateString();
        $this->hora_ingreso = now()->format('H:i');
        $this->nivel_educativo = '';
        $this->grupo_sanguineo = '';
        $this->factor_rh = '';
        $this->alergias = '';
        $this->seguro_salud = '';
        $this->antecedentes = [];
        $this->restricciones_alimentarias = '';
        $this->hospitalizaciones = '';
        $this->cirugias = '';
        $this->observacion_medica = '';
        $this->usa_whatsapp = false;
        $this->autoriza_informacion_medica = false;
        $this->consentimiento_datos = false;
        $this->observaciones_admision = '';
        $this->modalAdmision = true;
    }

    public function cerrarAdmision(): void
    {
        $this->modalAdmision = false;
        $this->codPreAdmision = null;
        $this->resetValidation();
    }

    public function updatedHabitacionId(): void
    {
        $this->cama_id = '';
        $this->resetValidation(['habitacion_id', 'cama_id']);
    }

    public function formalizarAdmision(FormalizarAdmision $formalizar): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['ADMINISTRADOR', 'SUPERADMINISTRADOR']), 403);

        $datos = $this->validate([
            'codPreAdmision' => ['required', 'exists:preadmisiones,cod_pre'],
            'habitacion_id' => ['required', 'exists:habitaciones,cod_habitacion'],
            'cama_id' => ['required', 'exists:camas,cod_cama'],
            'fecha_ingreso' => ['required', 'date', 'before_or_equal:today'],
            'hora_ingreso' => ['required', 'date_format:H:i'],
            'nivel_educativo' => ['nullable', 'string', 'max:100'],
            'grupo_sanguineo' => ['nullable', 'in:A,B,AB,O,DESCONOCIDO'],
            'factor_rh' => ['nullable', 'in:+,-,DESCONOCIDO'],
            'alergias' => ['required', 'string', 'min:3', 'max:2000'],
            'seguro_salud' => ['required', 'string', 'min:2', 'max:100'],
            'antecedentes' => ['array'],
            'antecedentes.*' => ['in:HIPERTENSION,DIABETES,PROBLEMAS_CARDIACOS,ACV,PARKINSON,EPILEPSIA,ALZHEIMER,DEPRESION,ANSIEDAD,PROBLEMAS_SUENO,PROBLEMAS_VISUALES,PROBLEMAS_AUDITIVOS,DOLOR_CRONICO'],
            'restricciones_alimentarias' => ['nullable', 'string', 'max:2000'],
            'hospitalizaciones' => ['nullable', 'string', 'max:3000'],
            'cirugias' => ['nullable', 'string', 'max:3000'],
            'observacion_medica' => ['nullable', 'string', 'max:3000'],
            'usa_whatsapp' => ['boolean'],
            'autoriza_informacion_medica' => ['accepted'],
            'consentimiento_datos' => ['accepted'],
            'observaciones_admision' => ['nullable', 'string', 'max:3000'],
        ], [
            'required' => 'Este campo es obligatorio para formalizar la admisión.',
            'exists' => 'La opción seleccionada ya no está disponible.',
            'before_or_equal' => 'La fecha de ingreso no puede ser posterior a hoy.',
            'date_format' => 'Ingrese una hora válida.',
            'accepted' => 'Debe confirmar esta autorización antes de admitir.',
            'in' => 'Seleccione una opción válida.',
            'min' => 'Ingrese al menos :min caracteres.',
            'max' => 'No exceda los :max caracteres.',
        ]);

        try {
            $solicitud = Preadmision::query()->findOrFail($this->codPreAdmision);
            $adulto = $formalizar->ejecutar($solicitud, $datos, auth()->user());
            $this->cerrarAdmision();
            $this->dispatch('swal', [
                'title' => 'Admisión completada',
                'text' => trim("{$adulto->nombres} {$adulto->ap_paterno} {$adulto->ap_materno}").' ya figura como residente institucional y tiene cama asignada.',
                'icon' => 'success',
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->addError('admision', 'No se pudo completar la admisión. Revise los datos e inténtelo nuevamente.');
        }
    }

    public function rechazar(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['MEDICO GENERAL/GERIATRA', 'ADMINISTRADOR', 'SUPERADMINISTRADOR']), 403);

        $this->validate([
            'codPreRechazo' => ['required', 'exists:preadmisiones,cod_pre'],
            'motivo_rechazo' => ['required', 'string', 'min:4', 'max:150'],
            'observacion_rechazo' => ['nullable', 'string', 'max:1000'],
        ]);

        $preadmision = Preadmision::query()->findOrFail($this->codPreRechazo);

        if (in_array($preadmision->estado, ['APROBADA', 'ADMITIDA'], true) || $preadmision->cod_am_generado) {
            $this->addError('motivo_rechazo', 'Una solicitud aprobada o admitida no puede rechazarse desde este flujo.');

            return;
        }

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
            'pendientes' => Preadmision::whereNotIn('estado', ['APROBADA', 'ADMITIDA', 'RECHAZADA'])->count(),
            'aprobadas' => Preadmision::where('estado', 'APROBADA')->whereNull('cod_am_generado')->count(),
            'admitidas' => Preadmision::where(fn ($q) => $q->where('estado', 'ADMITIDA')->orWhereNotNull('cod_am_generado'))->count(),
            'rechazadas' => Preadmision::where('estado', 'RECHAZADA')->count(),
            'alta_prioridad' => Preadmision::whereIn('prioridad', ['ALTA', 'CRITICA'])->count(),
            'con_documentos' => Preadmision::where('documentos_iniciales_completos', true)->count(),
            'sin_enfermero' => Preadmision::whereNull('enfermero_asignado')->count(),
        ];

        return view('livewire.admisiones.preadmisiones-panel', [
            'preadmisiones' => $query->orderByDesc('fecha_solicitud')->orderByDesc('created_at')->paginate(10),
            'metricas' => $metricas,
            'habitacionesAdmision' => Habitacion::query()
                ->withCount([
                    'camas',
                    'camasDisponibles as camas_disponibles_count',
                    'asignacionesActivas as camas_ocupadas_count',
                    'camas as camas_mantenimiento_count' => fn ($q) => $q->where('estado', 'MANTENIMIENTO'),
                    'camas as camas_bloqueadas_count' => fn ($q) => $q->where('estado', 'BLOQUEADA'),
                ])->orderBy('codigo')->get(),
            'camasHabitacion' => $this->habitacion_id
                ? Cama::query()->with('asignacionesActivas.adultoMayor')->where('cod_habitacion', $this->habitacion_id)->orderBy('codigo')->get()
                : collect(),
            'solicitudAdmision' => $this->codPreAdmision
                ? Preadmision::query()->find($this->codPreAdmision)
                : null,
        ])->layout('layouts.sistema');
    }
}
