<?php

namespace App\Livewire\Admin\Admisiones;

use App\Mail\PreadmisionDocumentosPendientesMail;
use App\Models\AsignacionPlazaEnfermeria;
use App\Models\DocumentoPreadmision;
use App\Models\Preadmision;
use App\Models\User;
use App\Services\Enfermeria\GeneradorPlanillaEnfermeriaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class PreadmisionWizard extends Component
{
    use WithFileUploads;

    public int $paso = 1;
    public int $totalPasos = 6;

    // Paso 1: Identidad
    public string $nombres = '';
    public string $ap_paterno = '';
    public string $ap_materno = '';
    public string $ci = '';
    public string $expedicion_ci = '';
    public string $fecha_nac = '';
    public string $genero = '';
    public string $estado_civil = 'NO ESPECIFICADO';
    public string $telefono = '';
    public string $celular = '';

    // Paso 2: Dirección
    public string $departamento_residencia = '';
    public string $ciudad_municipio = '';
    public string $zona = '';
    public string $calle = '';
    public string $direccion_referencia = '';

    // Paso 3: Familiar
    public string $familiar_nombres = '';
    public string $familiar_ap_paterno = '';
    public string $familiar_ap_materno = '';
    public string $familiar_ci = '';
    public string $familiar_parentesco = '';
    public string $familiar_celular = '';
    public string $familiar_correo = '';
    public string $familiar_direccion = '';

    // Paso 4: Caso
    public string $motivo_ingreso = '';
    public string $procedencia_ingreso = '';
    public string $tipo_ingreso = 'REGULAR';
    public string $permanencia = 'PERMANENTE';
    public string $prioridad = 'MEDIA';
    public string $descripcion_caso = '';

    // Paso 5: Documentos subidos
    public $doc_ci_adulto;
    public $doc_ci_familiar;
    public $doc_solicitud_ingreso;

    // Documentos marcados para entregar en 48h
    public array $docs_pendientes_48h = [];

    // Paso 6: Asignación
    public string $enfermero_id = '';
    public bool $guardadoExitoso = false;
    public ?string $codigoGenerado = null;

    // ─────────────────────────────────────────────────────────────────────────────
    // CONFIGURACIÓN DE DOCUMENTOS
    // ─────────────────────────────────────────────────────────────────────────────

    public function configuracionDocumentos(): array
    {
        return [
            [
                'tipo' => 'CI_ADULTO',
                'nombre' => 'CI del adulto mayor',
                'descripcion' => 'Cédula de identidad vigente',
                'grupo' => 'solicitante',
                'bloquea_avance' => true,
                'permite_48h' => false,
                'requiere_firma' => false,
                'es_generado_sistema' => false,
                'obligatorio' => true,
                'propiedad' => 'doc_ci_adulto',
            ],
            [
                'tipo' => 'CI_FAMILIAR',
                'nombre' => 'CI del familiar responsable',
                'descripcion' => 'Documento del tutor o responsable',
                'grupo' => 'solicitante',
                'bloquea_avance' => true,
                'permite_48h' => false,
                'requiere_firma' => false,
                'es_generado_sistema' => false,
                'obligatorio' => true,
                'propiedad' => 'doc_ci_familiar',
            ],
            [
                'tipo' => 'SOLICITUD_INGRESO',
                'nombre' => 'Solicitud inicial de ingreso',
                'descripcion' => 'Formulario de solicitud firmado',
                'grupo' => 'solicitante',
                'bloquea_avance' => false,
                'permite_48h' => true,
                'requiere_firma' => true,
                'es_generado_sistema' => false,
                'obligatorio' => true,
                'propiedad' => 'doc_solicitud_ingreso',
            ],
            [
                'tipo' => 'FICHA_PREADMISION',
                'nombre' => 'Ficha institucional de preadmisión',
                'descripcion' => 'Ficha con datos completos del caso',
                'grupo' => 'institucional',
                'bloquea_avance' => false,
                'permite_48h' => false,
                'requiere_firma' => true,
                'es_generado_sistema' => true,
                'obligatorio' => true,
                'vista_pdf' => 'pdf.preadmision.ficha',
                'archivo_pdf' => 'ficha-preadmision.pdf',
            ],
            [
                'tipo' => 'AUTORIZACION_VALORACION',
                'nombre' => 'Autorización de valoración inicial',
                'descripcion' => 'Autorización para evaluación médica y de enfermería',
                'grupo' => 'institucional',
                'bloquea_avance' => false,
                'permite_48h' => false,
                'requiere_firma' => true,
                'es_generado_sistema' => true,
                'obligatorio' => true,
                'vista_pdf' => 'pdf.preadmision.autorizacion-valoracion',
                'archivo_pdf' => 'autorizacion-valoracion.pdf',
            ],
            [
                'tipo' => 'CONSENTIMIENTO_DATOS',
                'nombre' => 'Consentimiento de tratamiento de datos',
                'descripcion' => 'Autorización de tratamiento de datos personales',
                'grupo' => 'institucional',
                'bloquea_avance' => false,
                'permite_48h' => false,
                'requiere_firma' => true,
                'es_generado_sistema' => true,
                'obligatorio' => true,
                'vista_pdf' => 'pdf.preadmision.consentimiento-datos',
                'archivo_pdf' => 'consentimiento-datos.pdf',
            ],
            [
                'tipo' => 'ACTA_RECEPCION_DOCUMENTOS',
                'nombre' => 'Acta de recepción de documentos',
                'descripcion' => 'Constancia de documentos recibidos',
                'grupo' => 'institucional',
                'bloquea_avance' => false,
                'permite_48h' => false,
                'requiere_firma' => false,
                'es_generado_sistema' => true,
                'obligatorio' => true,
                'vista_pdf' => 'pdf.preadmision.acta-recepcion',
                'archivo_pdf' => 'acta-recepcion.pdf',
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // NAVEGACIÓN
    // ─────────────────────────────────────────────────────────────────────────────

    public function siguiente(): void
    {
        if ($this->paso === 5) {
            $this->validarPaso5();
            return;
        }

        $this->validarPasoActual();

        if ($this->paso < $this->totalPasos) {
            $this->paso++;
        }
    }

    public function anterior(): void
    {
        if ($this->paso > 1) {
            $this->paso--;
        }
    }

    public function updatedEnfermeroId(): void
    {
        $this->resetValidation('enfermero_id');
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // VALIDACIÓN PASO 5
    // ─────────────────────────────────────────────────────────────────────────────

    private function validarPaso5(): void
    {
        $bloqueantes = [];

        foreach ($this->configuracionDocumentos() as $doc) {
            if (! $doc['bloquea_avance'] || $doc['es_generado_sistema']) {
                continue;
            }

            $propiedad = $doc['propiedad'];

            if (empty($this->$propiedad)) {
                $bloqueantes[] = $doc['nombre'];
            }
        }

        if (! empty($bloqueantes)) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Documentación obligatoria pendiente',
                'text' => 'Debe subir los siguientes documentos para continuar: ' . implode(', ', $bloqueantes) . '.',
            ]);

            return;
        }

        foreach ($this->configuracionDocumentos() as $doc) {
            if (! $doc['permite_48h'] || $doc['es_generado_sistema']) {
                continue;
            }

            $propiedad = $doc['propiedad'];
            $tipo = $doc['tipo'];

            if (empty($this->$propiedad)) {
                if (! in_array($tipo, $this->docs_pendientes_48h, true)) {
                    $this->docs_pendientes_48h[] = $tipo;
                }
            } else {
                $this->docs_pendientes_48h = array_values(
                    array_filter($this->docs_pendientes_48h, fn ($t) => $t !== $tipo)
                );
            }
        }

        $this->paso++;
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // CONFIRMACIÓN FINAL
    // ─────────────────────────────────────────────────────────────────────────────

    public function confirmarPreadmision(): void
    {
        if ($this->guardadoExitoso) {
            return;
        }

        $this->validate($this->rules(), $this->mensajesValidacion());

        if (! $this->validarEnfermeroSeleccionadoParaPreadmision()) {
            $this->dispatch('swal', [
                'title' => 'Asignación de enfermería inválida',
                'text' => $this->primerErrorEnfermero(),
                'icon' => 'warning',
            ]);

            return;
        }

        try {
            DB::beginTransaction();

            $hayPendientes48h = ! empty($this->docs_pendientes_48h);
            $fechaLimite48h = now()->addHours(48);

            $preadmision = Preadmision::create([
                'estado' => 'PREADMISION_ASIGNADA',
                'fecha_solicitud' => today()->toDateString(),
                'fecha_asignacion' => now(),

                'nombres' => $this->normalizar($this->nombres),
                'ap_paterno' => $this->normalizar($this->ap_paterno),
                'ap_materno' => $this->normalizar($this->ap_materno),
                'ci' => trim($this->ci),
                'expedicion_ci' => $this->expedicion_ci,
                'fecha_nac' => $this->fecha_nac,
                'genero' => $this->genero,
                'estado_civil' => $this->estado_civil,
                'telefono' => trim($this->telefono) ?: null,
                'celular' => trim($this->celular) ?: null,

                'departamento_residencia' => $this->departamento_residencia,
                'ciudad_municipio' => $this->normalizar($this->ciudad_municipio),
                'zona' => $this->normalizar($this->zona),
                'calle' => $this->normalizar($this->calle),
                'direccion_referencia' => $this->normalizar($this->direccion_referencia),

                'familiar_nombres' => $this->normalizar($this->familiar_nombres),
                'familiar_ap_paterno' => $this->normalizar($this->familiar_ap_paterno),
                'familiar_ap_materno' => $this->normalizar($this->familiar_ap_materno),
                'familiar_ci' => trim($this->familiar_ci) ?: null,
                'familiar_parentesco' => $this->familiar_parentesco,
                'familiar_celular' => trim($this->familiar_celular),
                'familiar_correo' => trim($this->familiar_correo) ?: null,
                'familiar_direccion' => $this->normalizar($this->familiar_direccion),

                'motivo_ingreso' => $this->motivo_ingreso,
                'procedencia_ingreso' => $this->procedencia_ingreso,
                'tipo_ingreso' => $this->tipo_ingreso,
                'permanencia' => $this->permanencia,
                'prioridad' => $this->prioridad,
                'descripcion_caso' => $this->normalizar($this->descripcion_caso),

                'documentos_iniciales_completos' => ! $hayPendientes48h,
                'documentos_institucionales_generados' => false,

                'enfermero_asignado' => $this->enfermero_id,
                'creado_por' => auth()->user()?->cod_usu,
                'observaciones' => 'Preadmisión registrada y asignada para valoración inicial de enfermería.',
            ]);

            $docsPendientesNombres = [];

            foreach ($this->configuracionDocumentos() as $docConfig) {
                if ($docConfig['es_generado_sistema']) {
                    continue;
                }

                $tipo = $docConfig['tipo'];
                $propiedad = $docConfig['propiedad'];
                $archivo = $this->$propiedad;
                $esPendiente48h = in_array($tipo, $this->docs_pendientes_48h, true) && empty($archivo);

                if (! empty($archivo) && ! is_string($archivo)) {
                    $path = $archivo->store("preadmisiones/{$preadmision->cod_pre}", 'public');

                    DocumentoPreadmision::create([
                        'cod_pre' => $preadmision->cod_pre,
                        'tipo_documento' => $tipo,
                        'nombre_documento' => $docConfig['nombre'],
                        'grupo_documento' => $docConfig['grupo'],
                        'archivo_path' => $path,
                        'nombre_original' => $archivo->getClientOriginalName(),
                        'es_institucional' => false,
                        'es_generado_sistema' => false,
                        'obligatorio' => $docConfig['obligatorio'],
                        'bloquea_avance' => $docConfig['bloquea_avance'],
                        'permite_48h' => $docConfig['permite_48h'],
                        'estado' => 'RECIBIDO',
                    ]);
                } elseif ($esPendiente48h) {
                    DocumentoPreadmision::create([
                        'cod_pre' => $preadmision->cod_pre,
                        'tipo_documento' => $tipo,
                        'nombre_documento' => $docConfig['nombre'],
                        'grupo_documento' => $docConfig['grupo'],
                        'es_institucional' => false,
                        'es_generado_sistema' => false,
                        'obligatorio' => $docConfig['obligatorio'],
                        'bloquea_avance' => $docConfig['bloquea_avance'],
                        'permite_48h' => $docConfig['permite_48h'],
                        'estado' => 'PENDIENTE_48H',
                        'fecha_limite_entrega' => $fechaLimite48h,
                        'observaciones' => 'Pendiente de entrega en plazo de 48 horas.',
                    ]);

                    $docsPendientesNombres[] = $docConfig['nombre'];
                }
            }

            $dirPdf = "preadmisiones/{$preadmision->cod_pre}/institucionales";
            Storage::disk('public')->makeDirectory($dirPdf);

            $todosGenerados = true;

            foreach ($this->configuracionDocumentos() as $docConfig) {
                if (! $docConfig['es_generado_sistema']) {
                    continue;
                }

                $archivoPdf = null;
                $estadoDoc = 'PENDIENTE_FIRMA';

                try {
                    $pdf = Pdf::loadView($docConfig['vista_pdf'], [
                        'preadmision' => $preadmision,
                        'fecha' => now()->format('d/m/Y H:i'),
                    ]);

                    $filePath = "{$dirPdf}/{$docConfig['archivo_pdf']}";
                    Storage::disk('public')->put($filePath, $pdf->output());

                    $archivoPdf = $filePath;
                    $estadoDoc = 'GENERADO';
                } catch (\Throwable) {
                    $todosGenerados = false;
                }

                DocumentoPreadmision::create([
                    'cod_pre' => $preadmision->cod_pre,
                    'tipo_documento' => $docConfig['tipo'],
                    'nombre_documento' => $docConfig['nombre'],
                    'grupo_documento' => 'institucional',
                    'archivo_path' => $archivoPdf,
                    'es_institucional' => true,
                    'es_generado_sistema' => true,
                    'obligatorio' => true,
                    'bloquea_avance' => false,
                    'permite_48h' => false,
                    'estado' => $estadoDoc,
                    'fecha_generacion' => $archivoPdf ? now() : null,
                    'observaciones' => 'Generado por el sistema al confirmar la preadmisión.',
                ]);
            }

            $preadmision->update([
                'documentos_institucionales_generados' => $todosGenerados,
            ]);

            if (! empty($docsPendientesNombres) && ! empty($preadmision->familiar_correo)) {
                try {
                    Mail::to($preadmision->familiar_correo)
                        ->send(new PreadmisionDocumentosPendientesMail(
                            $preadmision,
                            $docsPendientesNombres,
                            $fechaLimite48h->format('d/m/Y H:i')
                        ));
                } catch (\Throwable) {
                    // El correo no bloquea el flujo de preadmisión.
                }
            }

            activity('Admisiones')
                ->causedBy(auth()->user())
                ->performedOn($preadmision)
                ->log("Preadmisión {$preadmision->cod_pre} asignada para valoración inicial de enfermería.");

            DB::commit();

            $textoExito = 'El caso quedó preparado para valoración inicial de enfermería.';

            if (! empty($docsPendientesNombres)) {
                $textoExito .= ' Se notificó al familiar sobre los documentos pendientes en 48 horas.';
            }

            $this->dispatch('swal', [
                'title' => 'Preadmisión asignada',
                'text' => $textoExito,
                'icon' => 'success',
            ]);

            $this->guardadoExitoso = true;
            $this->codigoGenerado = $preadmision->cod_pre;
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->dispatch('swal', [
                'title' => 'Error al guardar',
                'text' => 'No se pudo registrar la preadmisión: ' . $e->getMessage(),
                'icon' => 'error',
            ]);
        }
    }

    public function nuevaPreadmision(): void
    {
        $this->reset([
            'paso',
            'nombres',
            'ap_paterno',
            'ap_materno',
            'ci',
            'expedicion_ci',
            'fecha_nac',
            'genero',
            'estado_civil',
            'telefono',
            'celular',
            'departamento_residencia',
            'ciudad_municipio',
            'zona',
            'calle',
            'direccion_referencia',
            'familiar_nombres',
            'familiar_ap_paterno',
            'familiar_ap_materno',
            'familiar_ci',
            'familiar_parentesco',
            'familiar_celular',
            'familiar_correo',
            'familiar_direccion',
            'motivo_ingreso',
            'procedencia_ingreso',
            'tipo_ingreso',
            'permanencia',
            'prioridad',
            'descripcion_caso',
            'doc_ci_adulto',
            'doc_ci_familiar',
            'doc_solicitud_ingreso',
            'docs_pendientes_48h',
            'enfermero_id',
            'guardadoExitoso',
            'codigoGenerado',
        ]);

        $this->paso = 1;
        $this->estado_civil = 'NO ESPECIFICADO';
        $this->tipo_ingreso = 'REGULAR';
        $this->permanencia = 'PERMANENTE';
        $this->prioridad = 'MEDIA';

        $this->resetValidation();
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // RENDER
    // ─────────────────────────────────────────────────────────────────────────────

    public function render()
    {
        $docsConfig = $this->configuracionDocumentos();

        $docsSolicitante = array_filter(
            $docsConfig,
            fn ($d) => ! $d['es_generado_sistema']
        );

        $docsInstitucionales = array_filter(
            $docsConfig,
            fn ($d) => $d['es_generado_sistema']
        );

        return view('livewire.admin.admisiones.preadmision-wizard', [
            'enfermeros' => $this->obtenerEnfermerosDisponiblesParaValoracion(),
            'docsSolicitante' => array_values($docsSolicitante),
            'docsInstitucionales' => array_values($docsInstitucionales),
        ])->layout('layouts.sistema');
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // PLANILLA OPERATIVA DE ENFERMERÍA E01–E12
    // ─────────────────────────────────────────────────────────────────────────────

    private function obtenerEnfermerosDisponiblesParaValoracion(): Collection
    {
        $fechaHora = now();

        $turnosVigentes = $this->obtenerTurnosVigentesDesdePlanilla($fechaHora);

        if ($turnosVigentes->isEmpty()) {
            return collect();
        }

        $codigosDisponibles = $turnosVigentes
            ->pluck('cod_usu')
            ->filter()
            ->unique()
            ->values();

        if ($codigosDisponibles->isEmpty()) {
            return collect();
        }

        $enfermerosOcupados = Preadmision::query()
            ->whereIn('estado', ['PREADMISION_ASIGNADA', 'EN_VALORACION_ENFERMERIA'])
            ->whereNotNull('enfermero_asignado')
            ->pluck('enfermero_asignado')
            ->filter()
            ->unique()
            ->values();

        $enfermeros = User::role('ENFERMEROS')
            ->where('estado', 'ACTIVO')
            ->whereIn('cod_usu', $codigosDisponibles)
            ->whereNotIn('cod_usu', $enfermerosOcupados)
            ->orderBy('ap_paterno')
            ->orderBy('ap_materno')
            ->orderBy('nombres')
            ->get(['cod_usu', 'nombres', 'ap_paterno', 'ap_materno', 'estado']);

        return $enfermeros
            ->map(function (User $enfermero) use ($turnosVigentes) {
                $turno = $turnosVigentes->firstWhere('cod_usu', $enfermero->cod_usu);

                $enfermero->plaza_operativa = $turno['plaza'] ?? null;
                $enfermero->tipo_asignacion_plaza = $turno['tipo_asignacion'] ?? null;
                $enfermero->turno_nombre = $turno['turno_nombre'] ?? 'Turno según planilla';
                $enfermero->turno_hora_inicio = $turno['hora_inicio'] ?? null;
                $enfermero->turno_hora_fin = $turno['hora_fin'] ?? null;

                if ($enfermero->turno_hora_inicio && $enfermero->turno_hora_fin) {
                    $enfermero->turno_label = "{$enfermero->turno_nombre}: {$enfermero->turno_hora_inicio} - {$enfermero->turno_hora_fin}";
                } else {
                    $enfermero->turno_label = $enfermero->turno_nombre;
                }

                $enfermero->setRelation('horariosPersonalSalud', collect([
                    (object) [
                        'turno' => $enfermero->turno_nombre,
                        'hora_inicio' => $enfermero->turno_hora_inicio,
                        'hora_fin' => $enfermero->turno_hora_fin,
                        'plaza' => $enfermero->plaza_operativa,
                        'tipo_asignacion' => $enfermero->tipo_asignacion_plaza,
                    ],
                ]));

                return $enfermero;
            })
            ->values();
    }

    private function obtenerTurnosVigentesDesdePlanilla(?Carbon $fechaHora = null): Collection
    {
        $fechaHora = $fechaHora ?: now();
        $fechaDia = $fechaHora->toDateString();

        $servicio = app(GeneradorPlanillaEnfermeriaService::class);

        $resultado = $servicio->generar([
            'fecha_inicio' => $fechaHora->copy()->startOfWeek(Carbon::MONDAY),
            'cantidad_semanas' => 1,
            'usar_usuarios_reales' => true,
        ]);

        $semana = $resultado['planilla'][0] ?? null;

        if (! $semana || empty($semana['dias'])) {
            return collect();
        }

        $turnosVigentes = collect();

        foreach ($semana['dias'] as $dia) {
            if (($dia['fecha'] ?? null) !== $fechaDia) {
                continue;
            }

            foreach (($dia['turnos'] ?? []) as $turno) {
                if (($turno['codigo'] ?? null) === 'DESCANSO') {
                    continue;
                }

                $horaInicio = $turno['hora_inicio'] ?? null;
                $horaFin = $turno['hora_fin'] ?? null;

                if (! $horaInicio || ! $horaFin) {
                    continue;
                }

                if (! $this->horaEstaDentroDelTurno($horaInicio, $horaFin, $fechaHora)) {
                    continue;
                }

                foreach (($turno['asignaciones'] ?? []) as $asignacionTurno) {
                    $codUsu = $asignacionTurno['cod_usu'] ?? null;

                    if (! $codUsu) {
                        continue;
                    }

                    $asignacionPlaza = $this->obtenerAsignacionPlazaVigente($codUsu, $fechaHora);

                    if (! $asignacionPlaza) {
                        continue;
                    }

                    if ($this->enfermeroTieneDescansoEnFecha($codUsu, $fechaHora)) {
                        continue;
                    }

                    $turnosVigentes->push([
                        'cod_usu' => $codUsu,
                        'plaza' => $asignacionPlaza->plaza,
                        'tipo_asignacion' => $asignacionPlaza->tipo,
                        'turno_codigo' => $turno['codigo'] ?? null,
                        'turno_nombre' => $turno['nombre'] ?? $turno['turno_nombre'] ?? $asignacionTurno['turno_nombre'] ?? 'Turno',
                        'hora_inicio' => $horaInicio,
                        'hora_fin' => $horaFin,
                    ]);
                }
            }
        }

        return $turnosVigentes
            ->unique('cod_usu')
            ->values();
    }

    private function obtenerAsignacionPlazaVigente(string $codUsu, ?Carbon $fecha = null): ?AsignacionPlazaEnfermeria
    {
        $fecha = $fecha ?: now();
        $fechaDia = $fecha->toDateString();

        return AsignacionPlazaEnfermeria::query()
            ->where('cod_usu', $codUsu)
            ->whereIn('tipo', ['TITULAR', 'REEMPLAZO', 'APOYO'])
            ->where(function ($query) use ($fechaDia) {
                $query
                    ->where(function ($q) {
                        $q->where('tipo', 'TITULAR')
                            ->whereNull('fecha');
                    })
                    ->orWhere(function ($q) use ($fechaDia) {
                        $q->whereIn('tipo', ['TITULAR', 'REEMPLAZO', 'APOYO'])
                            ->whereDate('fecha', $fechaDia);
                    });
            })
            ->orderByRaw("
                CASE tipo
                    WHEN 'REEMPLAZO' THEN 1
                    WHEN 'APOYO' THEN 2
                    WHEN 'TITULAR' THEN 3
                    ELSE 4
                END
            ")
            ->latest('updated_at')
            ->first();
    }

    private function enfermeroTieneDescansoEnFecha(string $codUsu, ?Carbon $fecha = null): bool
    {
        $fecha = $fecha ?: now();

        return AsignacionPlazaEnfermeria::query()
            ->where('cod_usu', $codUsu)
            ->where('tipo', 'DESCANSO')
            ->whereDate('fecha', $fecha->toDateString())
            ->exists();
    }

    private function horaEstaDentroDelTurno(string $horaInicio, string $horaFin, ?Carbon $momento = null): bool
    {
        $momento = $momento ?: now();

        $inicio = Carbon::parse($momento->toDateString() . ' ' . $horaInicio);
        $fin = Carbon::parse($momento->toDateString() . ' ' . $horaFin);
        $actual = $momento->copy();

        if ($fin->lessThanOrEqualTo($inicio)) {
            $fin->addDay();

            if ($actual->lessThan($inicio)) {
                $actual->addDay();
            }
        }

        return $actual->greaterThanOrEqualTo($inicio)
            && $actual->lessThan($fin);
    }

    private function validarEnfermeroSeleccionadoParaPreadmision(): bool
    {
        if (blank($this->enfermero_id)) {
            $this->addError('enfermero_id', 'Debe seleccionar un enfermero para la valoración inicial.');
            return false;
        }

        $enfermero = User::role('ENFERMEROS')
            ->where('estado', 'ACTIVO')
            ->where('cod_usu', $this->enfermero_id)
            ->first();

        if (! $enfermero) {
            $this->addError('enfermero_id', 'El usuario seleccionado no es un enfermero activo.');
            return false;
        }

        $asignacion = $this->obtenerAsignacionPlazaVigente($this->enfermero_id, now());

        if (! $asignacion) {
            $this->addError('enfermero_id', 'El enfermero no tiene una plaza activa en la planilla de enfermería.');
            return false;
        }

        if ($this->enfermeroTieneDescansoEnFecha($this->enfermero_id, now())) {
            $this->addError('enfermero_id', 'El enfermero tiene descanso registrado para el día de hoy.');
            return false;
        }

        $turnosVigentes = $this->obtenerTurnosVigentesDesdePlanilla(now());

        if (! $turnosVigentes->contains('cod_usu', $this->enfermero_id)) {
            $this->addError('enfermero_id', 'El enfermero no tiene un turno activo según la planilla rotativa para este horario.');
            return false;
        }

        $solapamiento = Preadmision::query()
            ->where('enfermero_asignado', $this->enfermero_id)
            ->whereIn('estado', ['PREADMISION_ASIGNADA', 'EN_VALORACION_ENFERMERIA'])
            ->exists();

        if ($solapamiento) {
            $this->addError('enfermero_id', 'El enfermero ya tiene una preadmisión asignada en proceso.');
            return false;
        }

        return true;
    }

    private function primerErrorEnfermero(): string
    {
        return $this->getErrorBag()->first('enfermero_id')
            ?: 'El enfermero seleccionado no cumple las condiciones de la planilla.';
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // VALIDACIONES
    // ─────────────────────────────────────────────────────────────────────────────

    private function validarPasoActual(): void
    {
        $this->validate(match ($this->paso) {
            1 => [
                'nombres' => ['required', 'string', 'min:2', 'max:100'],
                'ap_paterno' => ['required', 'string', 'min:2', 'max:80'],
                'ci' => ['required', 'string', 'max:20', 'unique:preadmisiones,ci'],
                'expedicion_ci' => ['required', 'string', 'max:10'],
                'fecha_nac' => ['required', 'date', 'before_or_equal:' . now()->subYears(60)->format('Y-m-d')],
                'genero' => ['required', 'string'],
            ],
            2 => [
                'departamento_residencia' => ['required', 'string'],
                'ciudad_municipio' => ['required', 'string', 'min:2'],
                'zona' => ['required', 'string', 'min:2'],
                'calle' => ['required', 'string', 'min:2'],
            ],
            3 => [
                'familiar_nombres' => ['required', 'string', 'min:2'],
                'familiar_parentesco' => ['required', 'string'],
                'familiar_celular' => ['required', 'string', 'max:30'],
            ],
            4 => [
                'motivo_ingreso' => ['required', 'string'],
                'procedencia_ingreso' => ['required', 'string'],
                'tipo_ingreso' => ['required', 'string'],
                'permanencia' => ['required', 'string'],
                'prioridad' => ['required', 'string'],
            ],
            6 => [
                'enfermero_id' => $this->getEnfermeroRules(),
            ],
            default => [],
        }, $this->mensajesValidacion());
    }

    private function rules(): array
    {
        $solicitudRule = in_array('SOLICITUD_INGRESO', $this->docs_pendientes_48h, true)
            ? ['nullable']
            : ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'];

        return [
            'nombres' => ['required', 'string', 'min:2', 'max:100'],
            'ap_paterno' => ['required', 'string', 'min:2', 'max:80'],
            'ci' => ['required', 'string', 'max:20', 'unique:preadmisiones,ci'],
            'expedicion_ci' => ['required', 'string', 'max:10'],
            'fecha_nac' => ['required', 'date', 'before_or_equal:' . now()->subYears(60)->format('Y-m-d')],
            'genero' => ['required', 'string'],
            'departamento_residencia' => ['required', 'string'],
            'ciudad_municipio' => ['required', 'string'],
            'zona' => ['required', 'string'],
            'calle' => ['required', 'string'],
            'familiar_nombres' => ['required', 'string', 'min:2'],
            'familiar_parentesco' => ['required', 'string'],
            'familiar_celular' => ['required', 'string', 'max:30'],
            'motivo_ingreso' => ['required', 'string'],
            'procedencia_ingreso' => ['required', 'string'],
            'tipo_ingreso' => ['required', 'string'],
            'permanencia' => ['required', 'string'],
            'prioridad' => ['required', 'string'],
            'doc_ci_adulto' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'doc_ci_familiar' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'doc_solicitud_ingreso' => $solicitudRule,
            'enfermero_id' => $this->getEnfermeroRules(),
        ];
    }

    private function getEnfermeroRules(): array
    {
        return [
            'required',
            'exists:users,cod_usu',
            function ($attribute, $value, $fail) {
                $enfermero = User::role('ENFERMEROS')
                    ->where('estado', 'ACTIVO')
                    ->where('cod_usu', $value)
                    ->first();

                if (! $enfermero) {
                    $fail('El usuario seleccionado debe ser un enfermero activo.');
                    return;
                }

                $asignacion = $this->obtenerAsignacionPlazaVigente((string) $value, now());

                if (! $asignacion) {
                    $fail('El enfermero no tiene una plaza activa en la planilla de enfermería.');
                    return;
                }

                if ($this->enfermeroTieneDescansoEnFecha((string) $value, now())) {
                    $fail('El enfermero tiene descanso registrado para el día de hoy.');
                    return;
                }

                $turnosVigentes = $this->obtenerTurnosVigentesDesdePlanilla(now());

                if (! $turnosVigentes->contains('cod_usu', $value)) {
                    $fail('El enfermero no tiene un turno activo según la planilla rotativa para este horario.');
                    return;
                }

                $solapamiento = Preadmision::query()
                    ->where('enfermero_asignado', $value)
                    ->whereIn('estado', ['PREADMISION_ASIGNADA', 'EN_VALORACION_ENFERMERIA'])
                    ->exists();

                if ($solapamiento) {
                    $fail('El enfermero ya tiene una preadmisión asignada en proceso.');
                }
            },
        ];
    }

    private function mensajesValidacion(): array
    {
        return [
            'required' => 'Este campo es obligatorio.',
            'string' => 'El formato ingresado no es válido.',
            'min' => 'Debe contener al menos :min caracteres.',
            'max' => 'No debe exceder los :max caracteres.',
            'unique' => 'Este valor ya se encuentra registrado.',
            'date' => 'Debe ser una fecha válida.',
            'before_or_equal' => 'La fecha no cumple con el requisito de edad mínima.',
            'file' => 'Debe seleccionar un archivo válido.',
            'mimes' => 'El archivo debe ser de tipo: :values.',
            'exists' => 'El registro seleccionado no es válido.',
        ];
    }

    private function normalizar(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : mb_strtoupper($value, 'UTF-8');
    }
}