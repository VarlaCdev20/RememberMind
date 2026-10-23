<?php

declare(strict_types=1);

namespace App\Modulos\Valoraciones\Presentacion\Livewire\Paginas;

use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Models\AplicacionInstrumento;
<<<<<<< HEAD
use App\Models\Area;
=======
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
use App\Models\Atencion;
use App\Models\ControlCognitivo;
use App\Models\ControlFuncionalIadl;
use App\Models\Residente;
use App\Models\User;
use App\Models\ValoracionPetersenOriginal;
use App\Models\ValoracionPsicologica;
<<<<<<< HEAD
=======
use App\Modulos\Clinica\Aplicacion\Acciones\AutorizacionClinicaService;
use App\Modulos\Valoraciones\Aplicacion\Acciones\RegistrarValoracionProfesional;
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
use App\Modulos\Valoraciones\Aplicacion\Consultas\ConsultarTendenciaResidente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Ficha psicológica unificada del residente:
 * Centraliza la historia psicológica, instrumentos aplicados, valoraciones afectivas,
 * funcionalidad IADL, entorno familiar, sesiones y continuidad con O.R.I.O.N.
 */
final class FichaPsicologicaResidente extends Component
{
    #[Locked]
    public Residente $residente;

    public string $tab = 'resumen';

    public ?string $mensajeExito = null;

    // Tendencias Longitudinales
    public int $periodoMeses = 6;

    public string $metricaTendencia = 'cognitivo';

    public bool $mostrarTablaTendencia = false;

    // Formulario Afectivo
    public bool $mostrarFormAfectiva = false;

<<<<<<< HEAD
    public array $formAfectiva = [
        'estado_animo' => 'EUTIMICO',
        'afecto' => 'ADECUADO',
        'ansiedad' => 'AUSENTE',
        'apatia' => 'AUSENTE',
=======
    #[Locked]
    public bool $confirmarDescarteAfectiva = false;

    public array $formAfectiva = [
        'estado_animo' => '',
        'afecto' => '',
        'ansiedad' => '',
        'apatia' => '',
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
        'conducta' => '',
        'conclusion' => '',
        'recomendacion' => '',
    ];

<<<<<<< HEAD
    // Formulario IADL
    public bool $mostrarFormIadl = false;

    public array $formIadl = [
        'actividad' => 'MEDICACION',
        'oportunidad' => 'OBSERVADA',
        'desempeno' => 'INDEPENDIENTE',
        'tipo_ayuda' => 'NINGUNA',
        'causa_ayuda' => 'NINGUNA',
        'nivel_previo' => 'INDEPENDIENTE',
        'fuente' => 'OBSERVACION_DIRECTA',
    ];

=======
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
    // Formulario Sesión
    public bool $mostrarFormSesion = false;

    public array $formSesion = [
        'tipo_atencion' => 'SEGUIMIENTO',
        'motivo' => '',
        'observacion' => '',
    ];

    protected $queryString = ['tab'];

    protected $listeners = [
        'evaluacion-geriatrica-guardada' => '$refresh',
        'evaluacion-geriatrica-area-cerrada' => '$refresh',
    ];

    public function mount(Residente $residente): void
    {
<<<<<<< HEAD
        $this->autorizar();
        $this->residente = $residente;
=======
        $this->residente = $residente;
        $this->autorizar();
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
        $validTabs = ['resumen', 'cognitiva', 'afectiva', 'funcionamiento', 'entorno', 'seguimiento', 'sesiones', 'informes', 'orion'];
        if (! in_array($this->tab, $validTabs, true)) {
            $this->tab = 'resumen';
        }
        if ($this->tab === 'sesiones') {
            $this->tab = 'seguimiento';
        }
    }

    public function cambiarTab(string $tab): void
    {
        $validTabs = ['resumen', 'cognitiva', 'afectiva', 'funcionamiento', 'entorno', 'seguimiento', 'sesiones', 'informes', 'orion'];
        if (in_array($tab, $validTabs, true)) {
            $this->tab = ($tab === 'sesiones') ? 'seguimiento' : $tab;
            $this->mensajeExito = null;
        }
    }

    public function abrirEvaluacion(string $codArea): void
    {
        $this->autorizar();
        $this->dispatch('evaluacion-geriatrica-area-abrir', [
            'cod_residente' => $this->residente->getKey(),
            'cod_area' => $codArea,
        ]);
    }

    public function guardarValoracionAfectiva(): void
    {
        $usuario = $this->autorizar();
<<<<<<< HEAD
        abort_unless($usuario->can('valoraciones_psicologicas.crear') || $usuario->hasRole('PSICOLOGO/A'), 403);

        $this->validate([
=======
        abort_unless($usuario->hasRole('PSICOLOGO/A') && $usuario->checkPermissionTo('valoraciones_psicologicas.crear', 'web'), 403);
        Gate::forUser($usuario)->authorize('view', $this->residente);
        $personal = app(AutorizacionClinicaService::class)->autorizarMutacion($usuario, $this->residente, 'valoraciones_psicologicas.crear', ['PSICOLOGO/A']);

        $validados = $this->validate([
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
            'formAfectiva.estado_animo' => ['required', 'string', 'max:50'],
            'formAfectiva.afecto' => ['required', 'string', 'max:50'],
            'formAfectiva.ansiedad' => ['required', 'string', 'max:50'],
            'formAfectiva.apatia' => ['required', 'string', 'max:50'],
            'formAfectiva.conducta' => ['nullable', 'string', 'max:500'],
            'formAfectiva.conclusion' => ['required', 'string', 'min:5', 'max:1000'],
            'formAfectiva.recomendacion' => ['nullable', 'string', 'max:1000'],
        ]);

<<<<<<< HEAD
        $personal = $usuario->personal;
        abort_unless($personal, 403, 'El usuario no tiene personal activo asociado.');

        $codigos = app(CodigosEntidad::class);
        $codArea = Area::where('cod_area', 'ARE_AFE')->value('cod_area')
            ?? Area::firstOrCreate(['cod_area' => 'ARE_AFE'], ['nombre' => 'Afectivo y Emocional', 'estado' => 'ACTIVA'])->getKey();

        DB::transaction(function () use ($codigos, $personal, $codArea): void {
=======
        $codigos = app(CodigosEntidad::class);
        $codArea = app(AutorizacionClinicaService::class)->areaActiva($personal)->getKey();

        DB::transaction(function () use ($codigos, $personal, $codArea, $usuario, $validados): void {
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
            $atencion = Atencion::create([
                'cod_atencion' => $codigos->siguiente('atenciones'),
                'cod_residente' => $this->residente->getKey(),
                'cod_area' => $codArea,
                'cod_personal' => $personal->getKey(),
                'tipo_atencion' => 'VALORACION_AFECTIVA',
                'motivo' => 'Valoración del estado afectivo y emocional',
                'fecha_hora' => now(),
                'estado' => 'FINALIZADA',
                'observacion' => $this->formAfectiva['conclusion'],
            ]);

<<<<<<< HEAD
            ValoracionPsicologica::create([
                'cod_valoracion_psicologica' => $codigos->siguiente('valoraciones_psicologicas'),
                'cod_residente' => $this->residente->getKey(),
                'cod_personal' => $personal->getKey(),
                'cod_atencion' => $atencion->getKey(),
                'fecha_hora' => now(),
                'estado_animo' => $this->formAfectiva['estado_animo'],
                'afecto' => $this->formAfectiva['afecto'],
                'ansiedad' => $this->formAfectiva['ansiedad'],
                'apatia' => $this->formAfectiva['apatia'],
                'conducta' => $this->formAfectiva['conducta'] ?: null,
                'conclusion' => $this->formAfectiva['conclusion'],
                'recomendacion' => $this->formAfectiva['recomendacion'] ?: null,
                'estado' => 'VIGENTE',
            ]);
        });

        $this->mostrarFormAfectiva = false;
        $this->reset('formAfectiva');
        $this->formAfectiva = [
            'estado_animo' => 'EUTIMICO',
            'afecto' => 'ADECUADO',
            'ansiedad' => 'AUSENTE',
            'apatia' => 'AUSENTE',
=======
            $registro = app(RegistrarValoracionProfesional::class)->registrar($usuario,
                [...$validados['formAfectiva'], 'cod_atencion' => $atencion->getKey()], $this->residente, 'psicologia');
            activity('valoraciones')->causedBy($usuario)->performedOn($registro)->event('valoracion_psicologica_registrada')
                ->log('Valoración psicológica registrada por su profesional responsable.');
        });

        $this->mostrarFormAfectiva = false;
        $this->confirmarDescarteAfectiva = false;
        $this->reset('formAfectiva');
        $this->formAfectiva = [
            'estado_animo' => '',
            'afecto' => '',
            'ansiedad' => '',
            'apatia' => '',
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
            'conducta' => '',
            'conclusion' => '',
            'recomendacion' => '',
        ];
<<<<<<< HEAD
        $this->mensajeExito = 'Valoración afectiva registrada correctamente. Queda vinculada como soporte diferencial (C04) para O.R.I.O.N.';
    }

    public function guardarControlIadl(): void
    {
        $usuario = $this->autorizar();
        $personal = $usuario->personal;
        abort_unless($personal, 403, 'El usuario no tiene personal activo asociado.');

        $this->validate([
            'formIadl.actividad' => ['required', 'string', 'in:'.implode(',', ControlFuncionalIadl::ACTIVIDADES)],
            'formIadl.oportunidad' => ['required', 'string', 'in:'.implode(',', ControlFuncionalIadl::OPORTUNIDADES)],
            'formIadl.desempeno' => ['required', 'string', 'in:'.implode(',', ControlFuncionalIadl::DESEMPENOS)],
            'formIadl.tipo_ayuda' => ['required', 'string', 'in:'.implode(',', ControlFuncionalIadl::AYUDAS)],
            'formIadl.causa_ayuda' => ['required', 'string', 'in:'.implode(',', ControlFuncionalIadl::CAUSAS)],
            'formIadl.nivel_previo' => ['required', 'string', 'in:'.implode(',', ControlFuncionalIadl::NIVELES_PREVIOS)],
            'formIadl.fuente' => ['required', 'string', 'in:'.implode(',', ControlFuncionalIadl::FUENTES)],
        ]);

        $codigos = app(CodigosEntidad::class);

        ControlFuncionalIadl::create([
            'cod_control_iadl' => $codigos->siguiente('controles_funcionales_iadl'),
            'cod_residente' => $this->residente->getKey(),
            'cod_personal' => $personal->getKey(),
            'cod_usuario_registro' => $usuario->getKey(),
            'fecha_hora' => now(),
            'registrado_en' => now(),
            'actividad' => $this->formIadl['actividad'],
            'oportunidad' => $this->formIadl['oportunidad'],
            'desempeno' => $this->formIadl['desempeno'],
            'tipo_ayuda' => $this->formIadl['tipo_ayuda'],
            'causa_ayuda' => $this->formIadl['causa_ayuda'],
            'nivel_previo' => $this->formIadl['nivel_previo'],
            'fuente' => $this->formIadl['fuente'],
            'estado' => 'REGISTRADO',
        ]);

        $this->mostrarFormIadl = false;
        $this->formIadl = [
            'actividad' => 'MEDICACION',
            'oportunidad' => 'OBSERVADA',
            'desempeno' => 'INDEPENDIENTE',
            'tipo_ayuda' => 'NINGUNA',
            'causa_ayuda' => 'NINGUNA',
            'nivel_previo' => 'INDEPENDIENTE',
            'fuente' => 'OBSERVACION_DIRECTA',
        ];
        $this->mensajeExito = 'Observación funcional IADL guardada. Queda disponible como evidencia de autonomía (C03) para O.R.I.O.N.';
=======
        $this->mensajeExito = 'Valoración afectiva registrada correctamente. Se conservaron la atención y su autoría profesional.';
    }

    public function alternarFormularioAfectivo(): void
    {
        $this->autorizar();
        if ($this->mostrarFormAfectiva && collect($this->formAfectiva)->contains(fn ($valor) => $valor !== '' && $valor !== null)) {
            $this->confirmarDescarteAfectiva = true;

            return;
        }
        $this->mostrarFormAfectiva = ! $this->mostrarFormAfectiva;
    }

    public function descartarValoracionAfectiva(): void
    {
        $this->autorizar();
        $this->reset('formAfectiva');
        $this->mostrarFormAfectiva = $this->confirmarDescarteAfectiva = false;
        $this->resetValidation();
    }

    public function conservarValoracionAfectiva(): void
    {
        $this->autorizar();
        $this->confirmarDescarteAfectiva = false;
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
    }

    public function guardarSesion(): void
    {
        $usuario = $this->autorizar();
<<<<<<< HEAD
        $personal = $usuario->personal;
        abort_unless($personal, 403, 'El usuario no tiene personal activo asociado.');
=======
        $personal = app(AutorizacionClinicaService::class)->autorizarMutacion($usuario, $this->residente, 'atenciones.crear', ['PSICOLOGO/A']);
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo

        $this->validate([
            'formSesion.tipo_atencion' => ['required', 'string', 'max:60'],
            'formSesion.motivo' => ['required', 'string', 'min:3', 'max:255'],
            'formSesion.observacion' => ['nullable', 'string', 'max:1000'],
        ]);

        $codigos = app(CodigosEntidad::class);
<<<<<<< HEAD
        $codArea = Area::where('cod_area', 'ARE_PSI')->value('cod_area')
            ?? Area::firstOrCreate(['cod_area' => 'ARE_PSI'], ['nombre' => 'Psicología', 'estado' => 'ACTIVA'])->getKey();
=======
        $codArea = app(AutorizacionClinicaService::class)->areaActiva($personal)->getKey();
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo

        Atencion::create([
            'cod_atencion' => $codigos->siguiente('atenciones'),
            'cod_residente' => $this->residente->getKey(),
            'cod_area' => $codArea,
            'cod_personal' => $personal->getKey(),
            'tipo_atencion' => $this->formSesion['tipo_atencion'],
            'motivo' => $this->formSesion['motivo'],
            'fecha_hora' => now(),
            'estado' => 'FINALIZADA',
            'observacion' => $this->formSesion['observacion'] ?: null,
        ]);

        $this->mostrarFormSesion = false;
        $this->formSesion = [
            'tipo_atencion' => 'SEGUIMIENTO',
            'motivo' => '',
            'observacion' => '',
        ];
        $this->mensajeExito = 'Sesión de atención psicológica registrada exitosamente en el expediente.';
    }

    public function cambiarPeriodoTendencia(int $meses): void
    {
        $this->periodoMeses = in_array($meses, [3, 6, 12, 0], true) ? $meses : 6;
    }

    public function cambiarMetricaTendencia(string $metrica): void
    {
        $this->metricaTendencia = in_array($metrica, ['cognitivo', 'afectivo', 'iadl'], true) ? $metrica : 'cognitivo';
    }

    public function toggleTablaTendencia(): void
    {
        $this->mostrarTablaTendencia = ! $this->mostrarTablaTendencia;
    }

    public function render()
    {
        $usuario = $this->autorizar();
        $codResidente = $this->residente->getKey();
<<<<<<< HEAD
=======
        $puedeConsultarIadl = $usuario->checkPermissionTo('controles_funcionales_iadl.ver', 'web');
        $puedeConsultarContactos = $usuario->checkPermissionTo('contactos.ver', 'web')
            && $usuario->checkPermissionTo('residentes_contactos.ver', 'web');
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo

        // 1. Datos del Resumen
        $totalInstrumentos = AplicacionInstrumento::where('cod_residente', $codResidente)->count();
        $totalValoraciones = ValoracionPsicologica::where('cod_residente', $codResidente)->count();
<<<<<<< HEAD
        $totalIadl = ControlFuncionalIadl::where('cod_residente', $codResidente)->count();
=======
        $totalIadl = $puedeConsultarIadl ? ControlFuncionalIadl::where('cod_residente', $codResidente)->count() : null;
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
        $totalSesiones = Atencion::where('cod_residente', $codResidente)
            ->whereHas('personal', fn ($q) => $q->where('profesion', 'PSICOLOGIA'))
            ->count();

        $ultimaValoracionPsicologica = ValoracionPsicologica::where('cod_residente', $codResidente)
            ->with('personal')
            ->orderByDesc('fecha_hora')
            ->first();

        $ultimaEvaluacionCognitiva = AplicacionInstrumento::where('cod_residente', $codResidente)
            ->whereHas('instrumento', fn ($q) => $q->whereIn('tipo', ['COGNITIVO', 'NEUROPSICOLOGICO', 'OBSERVACIONAL']))
            ->with('instrumento', 'evaluador.usuario')
            ->orderByDesc('fecha_hora')
            ->first();

        // 2. Datos por Pestaña
        $evaluacionesCognitivas = collect();
        $controlesCognitivos = collect();
        $evaluacionesAfectivas = collect();
        $instrumentosAfectivos = collect();
        $controlesIadl = collect();
        $instrumentosFuncionales = collect();
        $contactosFamiliares = collect();
        $notasEntorno = collect();
        $sesionesPsicologia = collect();
        $informesPsicologicos = collect();
        $juiciosPetersen = collect();
        $instrumentosCognitivosOrion = collect();

        if ($this->tab === 'cognitiva') {
            $evaluacionesCognitivas = AplicacionInstrumento::where('cod_residente', $codResidente)
                ->whereHas('instrumento', fn ($q) => $q->whereIn('tipo', ['COGNITIVO', 'NEUROPSICOLOGICO', 'OBSERVACIONAL']))
                ->with('instrumento', 'evaluador.usuario')
                ->orderByDesc('fecha_hora')
                ->get();
            $controlesCognitivos = ControlCognitivo::where('cod_residente', $codResidente)
<<<<<<< HEAD
=======
                ->when(! $usuario->checkPermissionTo('controles_cognitivos.ver', 'web'), fn ($q) => $q->whereRaw('1 = 0'))
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
                ->orderByDesc('fecha_hora')
                ->get();
        } elseif ($this->tab === 'afectiva') {
            $evaluacionesAfectivas = ValoracionPsicologica::where('cod_residente', $codResidente)
                ->with('personal')
                ->orderByDesc('fecha_hora')
                ->get();
            $instrumentosAfectivos = AplicacionInstrumento::where('cod_residente', $codResidente)
                ->whereHas('instrumento', fn ($q) => $q->whereIn('tipo', ['AFECTIVO', 'EMOCIONAL', 'CONDUCTUAL']))
                ->with('instrumento', 'evaluador.usuario')
                ->orderByDesc('fecha_hora')
                ->get();
        } elseif ($this->tab === 'funcionamiento') {
            $controlesIadl = ControlFuncionalIadl::where('cod_residente', $codResidente)
<<<<<<< HEAD
=======
                ->when(! $usuario->checkPermissionTo('controles_funcionales_iadl.ver', 'web'), fn ($q) => $q->whereRaw('1 = 0'))
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
                ->orderByDesc('fecha_hora')
                ->get();
            $instrumentosFuncionales = AplicacionInstrumento::where('cod_residente', $codResidente)
                ->whereHas('instrumento', fn ($q) => $q->whereIn('tipo', ['FUNCIONAL', 'IADL', 'ABVD']))
                ->with('instrumento', 'evaluador.usuario')
                ->orderByDesc('fecha_hora')
                ->get();
        } elseif ($this->tab === 'entorno') {
<<<<<<< HEAD
            $contactosFamiliares = $this->residente->familiares()->get();
=======
            if ($puedeConsultarContactos) {
                $contactosFamiliares = $this->residente->familiares()->wherePivot('estado', 'ACTIVO')->where('contactos.estado', 'ACTIVO')->get();
            }
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
            $notasEntorno = ValoracionPsicologica::where('cod_residente', $codResidente)
                ->where(fn ($q) => $q->whereNotNull('comunicacion')->orWhereNotNull('interaccion_social'))
                ->with('personal')
                ->orderByDesc('fecha_hora')
                ->get();
        } elseif ($this->tab === 'seguimiento' || $this->tab === 'sesiones') {
            $sesionesPsicologia = Atencion::where('cod_residente', $codResidente)
                ->whereHas('personal', fn ($q) => $q->where('profesion', 'PSICOLOGIA'))
                ->with('personal')
                ->orderByDesc('fecha_hora')
                ->get();
        } elseif ($this->tab === 'informes') {
            $informesPsicologicos = ValoracionPsicologica::where('cod_residente', $codResidente)
                ->with('personal')
                ->orderByDesc('fecha_hora')
                ->get();
        } elseif ($this->tab === 'orion') {
            $juiciosPetersen = ValoracionPetersenOriginal::query()
<<<<<<< HEAD
=======
                ->when(! $usuario->checkPermissionTo('valoraciones_petersen.ver', 'web'), fn ($q) => $q->whereRaw('1 = 0'))
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
                ->where('cod_residente', $codResidente)
                ->whereDoesntHave('sucesora')
                ->with(['personal.usuario', 'soportes'])
                ->orderBy('elemento')
                ->orderByDesc('fecha_hora')
                ->get();

            $instrumentosCognitivosOrion = AplicacionInstrumento::where('cod_residente', $codResidente)
                ->whereHas('instrumento', fn ($q) => $q->whereIn('tipo', ['COGNITIVO', 'NEUROPSICOLOGICO', 'OBSERVACIONAL']))
                ->with('instrumento', 'evaluador.usuario')
                ->orderByDesc('fecha_hora')
                ->take(10)
                ->get();
        }

        $puedeValorarPetersen = Gate::forUser($usuario)
            ->allows('crearOriginal', [ValoracionPetersenOriginal::class, $this->residente]);

<<<<<<< HEAD
        $datosTendencia = app(ConsultarTendenciaResidente::class)->ejecutar(
            $this->residente,
            $this->metricaTendencia,
            $this->periodoMeses
        );
=======
        $puedeConsultarTendencia = $this->metricaTendencia !== 'iadl' || $puedeConsultarIadl;
        $datosTendencia = $puedeConsultarTendencia ? app(ConsultarTendenciaResidente::class)->ejecutar(
            $this->residente,
            $this->metricaTendencia,
            $this->periodoMeses
        ) : null;
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo

        // Gráfica de baterías cognitivas aplicadas
        $chartCognitiva = null;
        if ($this->tab === 'cognitiva' && $evaluacionesCognitivas->isNotEmpty()) {
            $labelsCog = [];
            $puntajesCog = [];
            $maximosCog = [];
            foreach ($evaluacionesCognitivas->take(6) as $ev) {
                $labelsCog[] = $ev->instrumento?->nombre ?: 'Batería';
<<<<<<< HEAD
                $puntajesCog[] = (float) ($ev->puntaje_total ?? 0);
                $maximosCog[] = (float) ($ev->puntaje_maximo ?? 30);
=======
                $puntajesCog[] = $ev->puntaje_total !== null ? (float) $ev->puntaje_total : null;
                $maximosCog[] = $ev->puntaje_maximo !== null ? (float) $ev->puntaje_maximo : null;
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
            }
            $chartCognitiva = [
                'labels' => $labelsCog,
                'puntajes' => $puntajesCog,
                'maximos' => $maximosCog,
            ];
        }

        // Gráfica de distribución afectiva y emocional
        $chartAfectiva = null;
        if ($this->tab === 'afectiva' && $evaluacionesAfectivas->isNotEmpty()) {
<<<<<<< HEAD
            $conteoAf = ['Estable / Eutímico' => 0, 'Ansiedad' => 0, 'Tristeza / Depresivo' => 0, 'Apatía' => 0];
            foreach ($evaluacionesAfectivas as $va) {
                $st = strtoupper(trim((string) $va->estado_animo));
                if (str_contains($st, 'EUT') || str_contains($st, 'ESTABLE')) {
                    $conteoAf['Estable / Eutímico']++;
                } elseif (str_contains($st, 'ANS')) {
                    $conteoAf['Ansiedad']++;
                } elseif (str_contains($st, 'DEP') || str_contains($st, 'TRIST')) {
                    $conteoAf['Tristeza / Depresivo']++;
                } elseif (str_contains($st, 'APA')) {
                    $conteoAf['Apatía']++;
                } else {
                    $conteoAf['Estable / Eutímico']++;
                }
            }
            $chartAfectiva = [
                'labels' => array_keys($conteoAf),
                'data' => array_values($conteoAf),
                'colors' => ['#4a7054', '#9476b0', '#cb6f60', '#ba9c74'],
=======
            $conteoAf = $evaluacionesAfectivas->groupBy(fn ($valoracion) => filled($valoracion->estado_animo)
                ? $valoracion->estado_animo : 'No documentado')->map->count()->all();
            $paletaAfectiva = ['#4a7054', '#9476b0', '#cb6f60', '#ba9c74'];
            $chartAfectiva = [
                'labels' => array_keys($conteoAf),
                'data' => array_values($conteoAf),
                'colors' => array_map(fn ($indice) => $paletaAfectiva[$indice % count($paletaAfectiva)], array_keys(array_values($conteoAf))),
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
                'total' => array_sum($conteoAf),
            ];
        }

<<<<<<< HEAD
        // Gráfica de autonomía en actividades cotidianas IADL
        $chartIadl = null;
        if ($this->tab === 'funcionamiento') {
            $actividadesMap = [
                'MEDICACION' => 'Medicación',
                'PERTENENCIAS' => 'Pertenencias',
                'RUTINAS' => 'Rutinas',
                'CITAS' => 'Citas',
                'COMUNICACION' => 'Comunicación',
                'FINANZAS' => 'Finanzas',
                'COMPRAS' => 'Compras',
                'TRANSPORTE' => 'Transporte',
            ];
            $labelsIadl = [];
            $valoresIadl = [];
            $coloresIadl = [];
            foreach ($actividadesMap as $key => $lbl) {
                $labelsIadl[] = $lbl;
                $ultimo = $controlesIadl->firstWhere('actividad', $key);
                if (! $ultimo) {
                    $valoresIadl[] = 0;
                    $coloresIadl[] = '#ded8cb';
                } elseif ($ultimo->desempeno === 'INDEPENDIENTE') {
                    $valoresIadl[] = 100;
                    $coloresIadl[] = '#4a7054';
                } elseif ($ultimo->desempeno === 'CON_APOYO') {
                    $valoresIadl[] = 50;
                    $coloresIadl[] = '#cb6f60';
                } else {
                    $valoresIadl[] = 15;
                    $coloresIadl[] = '#ba9c74';
                }
            }
            $chartIadl = [
                'labels' => $labelsIadl,
                'valores' => $valoresIadl,
                'colores' => $coloresIadl,
                'tieneDatos' => $controlesIadl->isNotEmpty(),
            ];
        }

=======
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
        // Gráfica de sesiones por mes
        $chartSeguimiento = null;
        if (($this->tab === 'seguimiento' || $this->tab === 'sesiones') && $sesionesPsicologia->isNotEmpty()) {
            $mesesSesiones = [];
            foreach ($sesionesPsicologia as $ses) {
                $m = $ses->fecha_hora?->translatedFormat('M Y') ?? 'S/F';
                $mesesSesiones[$m] = ($mesesSesiones[$m] ?? 0) + 1;
            }
            $chartSeguimiento = [
                'labels' => array_keys($mesesSesiones),
                'data' => array_values($mesesSesiones),
            ];
        }

        return view('livewire.valoraciones.ficha-psicologica-residente', compact(
            'totalInstrumentos',
            'totalValoraciones',
            'totalIadl',
<<<<<<< HEAD
=======
            'puedeConsultarIadl',
            'puedeConsultarContactos',
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
            'totalSesiones',
            'ultimaValoracionPsicologica',
            'ultimaEvaluacionCognitiva',
            'evaluacionesCognitivas',
            'controlesCognitivos',
            'evaluacionesAfectivas',
            'instrumentosAfectivos',
            'controlesIadl',
            'instrumentosFuncionales',
            'contactosFamiliares',
            'notasEntorno',
            'sesionesPsicologia',
            'informesPsicologicos',
            'juiciosPetersen',
            'instrumentosCognitivosOrion',
            'puedeValorarPetersen',
            'datosTendencia',
            'chartCognitiva',
            'chartAfectiva',
<<<<<<< HEAD
            'chartIadl',
=======
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
            'chartSeguimiento'
        ))->layout('layouts.sistema');
    }

    private function autorizar(): User
    {
        $usuario = auth()->user() ? User::query()->find(auth()->id()) : null;
        abort_unless($usuario?->estado === 'ACTIVO'
            && $usuario->hasAnyRole(['SUPERADMINISTRADOR', 'PSICOLOGO/A'])
            && ! $usuario->hasRole('FAMILIAR')
<<<<<<< HEAD
            && ($usuario->hasRole('SUPERADMINISTRADOR') || $usuario->personal()->where('estado', 'ACTIVO')->exists()), 403);
=======
            && $usuario->checkPermissionTo('aplicaciones_instrumento.ver', 'web')
            && $usuario->checkPermissionTo('valoraciones_psicologicas.ver', 'web')
            && $usuario->checkPermissionTo('atenciones.ver', 'web')
            && Gate::forUser($usuario)->allows('viewAny', Residente::class)
            && ($usuario->hasRole('SUPERADMINISTRADOR') || $usuario->personal()->where('estado', 'ACTIVO')->where('profesion', 'PSICOLOGIA')->exists()), 403);
        if (isset($this->residente)) {
            Gate::forUser($usuario)->authorize('view', $this->residente);
        }
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo

        return $usuario;
    }
}
