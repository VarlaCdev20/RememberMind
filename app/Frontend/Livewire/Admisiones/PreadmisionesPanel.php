<?php

namespace App\Frontend\Livewire\Admisiones;

use App\Backend\Modulos\Admisiones\Acciones\FormalizarAdmision;
use App\Models\Cama;
use App\Models\Habitacion;
use App\Models\Preadmision;
use Illuminate\Support\Carbon;
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

    public ?string $codPreRechazo = null;

    public string $motivo_rechazo = '';

    public string $observacion_rechazo = '';

    public bool $modalDetalle = false;

    public string $solicitud = '';

    public string $panelTab = 'resumen';

    public string $panelModo = 'detalle';

    public string $orden = 'recientes';

    public string $vista = 'lista';

    public int $porPagina = 10;

    public ?Preadmision $solicitudDetalle = null;

    public bool $modalAdmision = false;

    public int $pasoAdmision = 1;

    public ?string $codPreAdmision = null;

    public ?string $residenteAdmitidoCodigo = null;

    public string $cama_id = '';

    public string $habitacion_id = '';

    public string $fecha_ingreso = '';

    public string $hora_ingreso = '';

    public string $nivel_educativo = '';

    public string $seguro_entidad = '';

    public string $seguro_plan = '';

    public string $seguro_afiliacion = '';

    public string $seguro_titular = '';

    public string $seguro_cobertura = '';

    public bool $autoriza_informacion_medica = false;

    public bool $consentimiento_datos = false;

    public string $observaciones_admision = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'estado' => ['except' => ''],
        'prioridad' => ['except' => ''],
        'fecha_inicio' => ['except' => ''],
        'fecha_fin' => ['except' => ''],
        'vista' => ['except' => 'lista'],
        'solicitud' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->soloRechazadas = request()->routeIs('admin.admisiones.preadmisiones.rechazadas');

        if ($this->soloRechazadas) {
            $this->estado = 'RECHAZADA';
        }

        $codigo = request()->query('solicitud');
        if (is_string($codigo) && $codigo !== '') {
            $this->verDetalle($codigo);
            if (request()->query('tab') === 'documentos') {
                $this->panelTab = 'documentos';
            }
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

    public function updatedVista(): void
    {
        if (! in_array($this->vista, ['lista', 'tarjetas', 'tabla'], true)) {
            $this->vista = 'lista';
        }
    }

    public function updatedPorPagina(): void
    {
        if (! in_array($this->porPagina, [10, 20, 50], true)) {
            $this->porPagina = 10;
        }
        $this->resetPage();
    }

    public function updatingFechaInicio(): void
    {
        $this->resetPage();
    }

    public function updatingFechaFin(): void
    {
        $this->resetPage();
    }

    public function updatingOrden(): void
    {
        $this->resetPage();
    }

    public function limpiarFechas(): void
    {
        $this->reset('fecha_inicio', 'fecha_fin');
        $this->resetPage();
    }

    public function filtrarMes(string $mes): void
    {
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes)) {
            return;
        }
        $fecha = Carbon::createFromFormat('!Y-m', $mes);
        $this->fecha_inicio = $fecha->toDateString();
        $this->fecha_fin = $fecha->endOfMonth()->toDateString();
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
            'orden',
        ]);

        if ($this->soloRechazadas) {
            $this->estado = 'RECHAZADA';
        }

        $this->resetPage();
    }

    public function verDocumentos(string $codPre): void
    {
        $this->verDetalle($codPre);
        $this->panelTab = 'documentos';
    }

    public function cerrarModalDocumentos(): void
    {
        $this->cerrarDetalle();
    }

    public function verHistorial(string $codPre): void
    {
        $this->verDetalle($codPre);
        $this->panelTab = 'historial';
    }

    public function abrirModalRechazo(string $codPre): void
    {
        $this->verDetalle($codPre);
        $this->codPreRechazo = $codPre;
        $this->motivo_rechazo = '';
        $this->observacion_rechazo = '';
        $this->panelModo = 'rechazo';
    }

    public function cerrarModalRechazo(): void
    {
        $this->panelModo = 'revision';
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
            ->with(['contacto', 'documentos', 'admision.residente'])
            ->findOrFail($codPre);
        $this->modalDetalle = true;
        $this->solicitud = $codPre;
        $this->panelTab = 'resumen';
        $this->panelModo = 'detalle';
    }

    public function cambiarPanelTab(string $tab): void
    {
        abort_unless(in_array($tab, ['resumen', 'documentos', 'historial'], true), 404);
        $this->panelTab = $tab;
        $this->panelModo = 'detalle';
    }

    public function revisarSolicitud(): void
    {
        abort_unless($this->solicitudDetalle && $this->solicitudDetalle->estado === 'PENDIENTE', 404);
        abort_unless(auth()->user()?->hasAnyRole(['MEDICO GENERAL/GERIATRA', 'ADMINISTRADOR', 'SUPERADMINISTRADOR']), 403);
        $this->panelModo = 'revision';
    }

    public function volverAlResumen(): void
    {
        $this->panelModo = 'detalle';
        $this->panelTab = 'resumen';
    }

    public function cerrarDetalle(): void
    {
        $this->modalDetalle = false;
        $this->solicitud = '';
        $this->solicitudDetalle = null;
        $this->panelModo = 'detalle';
        $this->dispatch('preadmision-panel-closed');
    }

    public function aprobar(string $codPre): void
    {
        $preadmision = Preadmision::query()->findOrFail($codPre);

        if ($preadmision->admision()->exists() || in_array($preadmision->estado, ['APROBADA', 'ADMITIDA'], true)) {
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
            'fecha_revision' => now(),
            'cod_usuario_revision' => auth()->id(),
            'motivo_rechazo' => null,
        ]);

        if ($this->solicitudDetalle?->getKey() === $codPre) {
            $this->solicitudDetalle = $preadmision->load(['contacto', 'documentos', 'admision.residente']);
            $this->panelModo = 'detalle';
        }

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

        if ($solicitud->estado !== 'APROBADA' || $solicitud->admision()->exists()) {
            $this->dispatch('swal', ['title' => 'Admisión no disponible', 'text' => 'Solo puede admitirse una solicitud aprobada que todavía no tenga residente asociado.', 'icon' => 'warning']);

            return;
        }

        $this->resetValidation();
        $this->cerrarDetalle();
        $this->residenteAdmitidoCodigo = null;
        $this->codPreAdmision = $solicitud->cod_preadmision;
        $this->habitacion_id = '';
        $this->cama_id = '';
        $this->fecha_ingreso = now()->toDateString();
        $this->hora_ingreso = now()->format('H:i');
        $this->nivel_educativo = '';
        $this->seguro_entidad = '';
        $this->seguro_plan = '';
        $this->seguro_afiliacion = '';
        $this->seguro_titular = '';
        $this->seguro_cobertura = '';
        $this->pasoAdmision = 1;
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

    public function irPasoAdmision(int $paso): void
    {
        abort_unless($this->modalAdmision && $this->codPreAdmision, 404);
        $this->pasoAdmision = max(1, min(7, $paso));
    }

    public function formalizarAdmision(FormalizarAdmision $formalizar): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['ADMINISTRADOR', 'SUPERADMINISTRADOR']), 403);

        try {
            $datos = $this->validate([
                'codPreAdmision' => ['required', 'exists:preadmisiones,cod_preadmision'],
                'habitacion_id' => ['required', 'exists:habitaciones,cod_habitacion'],
                'cama_id' => ['required', 'exists:camas,cod_cama'],
                'fecha_ingreso' => ['required', 'date', 'before_or_equal:today'],
                'hora_ingreso' => ['required', 'date_format:H:i'],
                'nivel_educativo' => ['nullable', 'string', 'max:100'],
                'seguro_entidad' => ['nullable', 'string', 'max:120', 'required_with:seguro_plan,seguro_afiliacion,seguro_titular,seguro_cobertura'],
                'seguro_plan' => ['nullable', 'string', 'max:100'],
                'seguro_afiliacion' => ['nullable', 'string', 'max:80'],
                'seguro_titular' => ['nullable', 'string', 'max:160'],
                'seguro_cobertura' => ['nullable', 'string', 'max:3000'],
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
        } catch (ValidationException $e) {
            $campos = array_keys($e->errors());
            $this->pasoAdmision = count(array_intersect($campos, ['seguro_entidad', 'seguro_plan', 'seguro_afiliacion', 'seguro_titular', 'seguro_cobertura'])) ? 5
                : (count(array_intersect($campos, ['habitacion_id', 'cama_id', 'fecha_ingreso', 'hora_ingreso'])) ? 6
                    : (count(array_intersect($campos, ['autoriza_informacion_medica', 'consentimiento_datos'])) ? 4 : 1));
            throw $e;
        }

        try {
            $solicitud = Preadmision::query()->findOrFail($this->codPreAdmision);
            if (! $solicitud->cod_contacto) {
                $this->pasoAdmision = 2;
                throw ValidationException::withMessages(['contacto' => 'La preadmisión necesita un contacto responsable antes de formalizar.']);
            }
            if (! Cama::query()->whereKey($datos['cama_id'])->where('cod_habitacion', $datos['habitacion_id'])->exists()) {
                $this->pasoAdmision = 6;
                throw ValidationException::withMessages(['cama_id' => 'La cama no corresponde a la habitación seleccionada.']);
            }
            $adulto = $formalizar->ejecutar($solicitud, [
                'cod_cama' => $datos['cama_id'],
                'cod_contacto' => $solicitud->cod_contacto,
                'fecha_hora_admision' => $datos['fecha_ingreso'].' '.$datos['hora_ingreso'],
                'nivel_educativo' => $datos['nivel_educativo'] ?: null,
                'observacion' => $datos['observaciones_admision'] ?: null,
                'autoriza_informacion' => (bool) $datos['autoriza_informacion_medica'],
                'tipo_consentimiento' => 'ADMISION',
                'firma_residente' => false,
                'seguro_entidad' => $datos['seguro_entidad'] ?: null,
                'seguro_plan' => $datos['seguro_plan'] ?: null,
                'seguro_afiliacion' => $datos['seguro_afiliacion'] ?: null,
                'seguro_titular' => $datos['seguro_titular'] ?: null,
                'seguro_cobertura' => $datos['seguro_cobertura'] ?: null,
            ], auth()->user());
            $this->cerrarAdmision();
            $this->residenteAdmitidoCodigo = $adulto->cod_residente;
            $this->dispatch('swal', [
                'title' => 'Admisión completada',
                'text' => trim("{$adulto->nombres} {$adulto->apellido_paterno} {$adulto->apellido_materno}").' ya figura como residente institucional y tiene cama asignada.',
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
            'codPreRechazo' => ['required', 'exists:preadmisiones,cod_preadmision'],
            'motivo_rechazo' => ['required', 'string', 'min:4', 'max:150'],
            'observacion_rechazo' => ['nullable', 'string', 'max:1000'],
        ]);

        $preadmision = Preadmision::query()->findOrFail($this->codPreRechazo);

        if (in_array($preadmision->estado, ['APROBADA', 'ADMITIDA'], true) || $preadmision->admision()->exists()) {
            $this->addError('motivo_rechazo', 'Una solicitud aprobada o admitida no puede rechazarse desde este flujo.');

            return;
        }

        $preadmision->update([
            'estado' => 'RECHAZADA',
            'motivo_rechazo' => mb_strtoupper(trim($this->motivo_rechazo), 'UTF-8'),
            'fecha_revision' => now(),
            'cod_usuario_revision' => auth()->id(),
        ]);

        if ($this->solicitudDetalle?->getKey() === $preadmision->getKey()) {
            $this->solicitudDetalle = $preadmision->load(['contacto', 'documentos', 'admision.residente']);
            $this->panelModo = 'detalle';
        }

        activity('Admisiones')
            ->causedBy(auth()->user())
            ->performedOn($preadmision)
            ->log("Preadmisión {$preadmision->cod_preadmision} rechazada.");

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
            ->with(['contacto', 'admision.residente'])
            ->withCount('documentos');

        if ($this->search !== '') {
            $search = trim($this->search);

            $query->where(function ($q) use ($search) {
                $q->where('cod_preadmision', 'like', "%{$search}%")
                    ->orWhere('nombres', 'like', "%{$search}%")
                    ->orWhere('apellido_paterno', 'like', "%{$search}%")
                    ->orWhere('apellido_materno', 'like', "%{$search}%")
                    ->orWhere('numero_documento', 'like', "%{$search}%")
                    ->orWhereHas('contacto', fn ($contacto) => $contacto
                        ->where('nombres', 'like', "%{$search}%")
                        ->orWhere('apellido_paterno', 'like', "%{$search}%"));
            });
        }

        if ($this->estado === 'ADMITIDA') {
            $query->where(fn ($q) => $q->where('estado', 'ADMITIDA')->orWhereHas('admision'));
        } elseif ($this->estado === 'PENDIENTE') {
            $query->where('estado', 'PENDIENTE')->whereDoesntHave('admision');
        } elseif ($this->estado === 'APROBADA') {
            $query->where('estado', 'APROBADA')->whereDoesntHave('admision');
        } elseif ($this->estado === 'RECHAZADA') {
            $query->where('estado', 'RECHAZADA')->whereDoesntHave('admision');
        } elseif ($this->estado !== '') {
            $query->where('estado', $this->estado);
        }

        if ($this->prioridad === 'SIN_REGISTRAR') {
            $query->where(fn ($q) => $q->whereNull('prioridad')->orWhere('prioridad', ''));
        } elseif ($this->prioridad !== '') {
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
            'pendientes' => Preadmision::where('estado', 'PENDIENTE')->whereDoesntHave('admision')->count(),
            'aprobadas' => Preadmision::where('estado', 'APROBADA')->whereDoesntHave('admision')->count(),
            'admitidas' => Preadmision::where(fn ($q) => $q->where('estado', 'ADMITIDA')->orWhereHas('admision'))->count(),
            'rechazadas' => Preadmision::where('estado', 'RECHAZADA')->whereDoesntHave('admision')->count(),
            'alta_prioridad' => Preadmision::whereIn('prioridad', ['ALTA', 'CRITICA'])->count(),
            'con_documentos' => Preadmision::whereHas('documentos')->count(),
            'sin_enfermero' => 0,
        ];

        // Seis periodos por límites portables, sin inventar solicitudes.
        $tendencia = collect(range(5, 0))->map(function (int $atras): array {
            $inicio = now()->locale('es')->startOfMonth()->subMonths($atras);

            return [
                'mes' => $inicio->format('Y-m'),
                'etiqueta' => $inicio->translatedFormat('M'),
                'descripcion' => $inicio->translatedFormat('F Y'),
                'cantidad' => Preadmision::whereBetween('fecha_solicitud', [$inicio, $inicio->copy()->endOfMonth()])->count(),
            ];
        });
        $prioridadesDisponibles = Preadmision::query()->select('prioridad')
            ->selectRaw('COUNT(*) as cantidad')->groupBy('prioridad')->orderBy('prioridad')->get()
            ->groupBy(fn ($fila) => $fila->prioridad ?: 'SIN_REGISTRAR')
            ->map(fn ($filas) => (int) $filas->sum('cantidad'));

        return view('livewire.admisiones.preadmisiones-panel', [
            'preadmisiones' => $query->orderBy('fecha_solicitud', $this->orden === 'antiguas' ? 'asc' : 'desc')->orderBy('cod_preadmision', $this->orden === 'antiguas' ? 'asc' : 'desc')->paginate(in_array($this->porPagina, [10, 20, 50], true) ? $this->porPagina : 10),
            'metricas' => $metricas,
            'tendencia' => $tendencia,
            'prioridadesDisponibles' => $prioridadesDisponibles,
            'habitacionesAdmision' => Habitacion::query()
                ->withCount([
                    'camas',
                    'camasDisponibles as camas_disponibles_count',
                    'asignacionesActivas as camas_ocupadas_count',
                    'camas as camas_mantenimiento_count' => fn ($q) => $q->where('estado', 'MANTENIMIENTO'),
                    'camas as camas_bloqueadas_count' => fn ($q) => $q->where('estado', 'BLOQUEADA'),
                ])->orderBy('codigo')->get(),
            'camasHabitacion' => $this->habitacion_id
                ? Cama::query()->with('asignacionesActivas.residente')->where('cod_habitacion', $this->habitacion_id)->orderBy('codigo')->get()
                    ->each(fn (Cama $cama) => $cama->estado === 'ACTIVA' && ! $cama->asignacionesActivas->count() ? $cama->setAttribute('estado', 'DISPONIBLE') : null)
                : collect(),
            'solicitudAdmision' => $this->codPreAdmision
                ? Preadmision::query()->with(['contacto', 'documentos'])->find($this->codPreAdmision)
                : null,
        ])->layout('layouts.sistema');
    }
}
