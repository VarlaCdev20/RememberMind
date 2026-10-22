<?php

declare(strict_types=1);

namespace App\Modulos\Valoraciones\Presentacion\Livewire\Paginas;

use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Models\AplicacionInstrumento;
use App\Models\Atencion;
use App\Models\ControlCognitivo;
use App\Models\ControlFuncionalIadl;
use App\Models\Residente;
use App\Models\User;
use App\Models\ValoracionPetersenOriginal;
use App\Models\ValoracionPsicologica;
use App\Modulos\Clinica\Aplicacion\Acciones\AutorizacionClinicaService;
use App\Modulos\Valoraciones\Aplicacion\Acciones\RegistrarValoracionProfesional;
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

    #[Locked]
    public bool $confirmarDescarteAfectiva = false;

    public array $formAfectiva = [
        'estado_animo' => '',
        'afecto' => '',
        'ansiedad' => '',
        'apatia' => '',
        'conducta' => '',
        'conclusion' => '',
        'recomendacion' => '',
    ];

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
        $this->residente = $residente;
        $this->autorizar();
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
        abort_unless($usuario->hasRole('PSICOLOGO/A') && $usuario->checkPermissionTo('valoraciones_psicologicas.crear', 'web'), 403);
        Gate::forUser($usuario)->authorize('view', $this->residente);
        $personal = app(AutorizacionClinicaService::class)->autorizarMutacion($usuario, $this->residente, 'valoraciones_psicologicas.crear', ['PSICOLOGO/A']);

        $validados = $this->validate([
            'formAfectiva.estado_animo' => ['required', 'string', 'max:50'],
            'formAfectiva.afecto' => ['required', 'string', 'max:50'],
            'formAfectiva.ansiedad' => ['required', 'string', 'max:50'],
            'formAfectiva.apatia' => ['required', 'string', 'max:50'],
            'formAfectiva.conducta' => ['nullable', 'string', 'max:500'],
            'formAfectiva.conclusion' => ['required', 'string', 'min:5', 'max:1000'],
            'formAfectiva.recomendacion' => ['nullable', 'string', 'max:1000'],
        ]);

        $codigos = app(CodigosEntidad::class);
        $codArea = app(AutorizacionClinicaService::class)->areaActiva($personal)->getKey();

        DB::transaction(function () use ($codigos, $personal, $codArea, $usuario, $validados): void {
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
            'conducta' => '',
            'conclusion' => '',
            'recomendacion' => '',
        ];
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
    }

    public function guardarSesion(): void
    {
        $usuario = $this->autorizar();
        $personal = app(AutorizacionClinicaService::class)->autorizarMutacion($usuario, $this->residente, 'atenciones.crear', ['PSICOLOGO/A']);

        $this->validate([
            'formSesion.tipo_atencion' => ['required', 'string', 'max:60'],
            'formSesion.motivo' => ['required', 'string', 'min:3', 'max:255'],
            'formSesion.observacion' => ['nullable', 'string', 'max:1000'],
        ]);

        $codigos = app(CodigosEntidad::class);
        $codArea = app(AutorizacionClinicaService::class)->areaActiva($personal)->getKey();

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
        $puedeConsultarIadl = $usuario->checkPermissionTo('controles_funcionales_iadl.ver', 'web');
        $puedeConsultarContactos = $usuario->checkPermissionTo('contactos.ver', 'web')
            && $usuario->checkPermissionTo('residentes_contactos.ver', 'web');

        // 1. Datos del Resumen
        $totalInstrumentos = AplicacionInstrumento::where('cod_residente', $codResidente)->count();
        $totalValoraciones = ValoracionPsicologica::where('cod_residente', $codResidente)->count();
        $totalIadl = $puedeConsultarIadl ? ControlFuncionalIadl::where('cod_residente', $codResidente)->count() : null;
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
                ->when(! $usuario->checkPermissionTo('controles_cognitivos.ver', 'web'), fn ($q) => $q->whereRaw('1 = 0'))
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
                ->when(! $usuario->checkPermissionTo('controles_funcionales_iadl.ver', 'web'), fn ($q) => $q->whereRaw('1 = 0'))
                ->orderByDesc('fecha_hora')
                ->get();
            $instrumentosFuncionales = AplicacionInstrumento::where('cod_residente', $codResidente)
                ->whereHas('instrumento', fn ($q) => $q->whereIn('tipo', ['FUNCIONAL', 'IADL', 'ABVD']))
                ->with('instrumento', 'evaluador.usuario')
                ->orderByDesc('fecha_hora')
                ->get();
        } elseif ($this->tab === 'entorno') {
            if ($puedeConsultarContactos) {
                $contactosFamiliares = $this->residente->familiares()->wherePivot('estado', 'ACTIVO')->where('contactos.estado', 'ACTIVO')->get();
            }
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
                ->when(! $usuario->checkPermissionTo('valoraciones_petersen.ver', 'web'), fn ($q) => $q->whereRaw('1 = 0'))
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

        $puedeConsultarTendencia = $this->metricaTendencia !== 'iadl' || $puedeConsultarIadl;
        $datosTendencia = $puedeConsultarTendencia ? app(ConsultarTendenciaResidente::class)->ejecutar(
            $this->residente,
            $this->metricaTendencia,
            $this->periodoMeses
        ) : null;

        // Gráfica de baterías cognitivas aplicadas
        $chartCognitiva = null;
        if ($this->tab === 'cognitiva' && $evaluacionesCognitivas->isNotEmpty()) {
            $labelsCog = [];
            $puntajesCog = [];
            $maximosCog = [];
            foreach ($evaluacionesCognitivas->take(6) as $ev) {
                $labelsCog[] = $ev->instrumento?->nombre ?: 'Batería';
                $puntajesCog[] = $ev->puntaje_total !== null ? (float) $ev->puntaje_total : null;
                $maximosCog[] = $ev->puntaje_maximo !== null ? (float) $ev->puntaje_maximo : null;
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
            $conteoAf = $evaluacionesAfectivas->groupBy(fn ($valoracion) => filled($valoracion->estado_animo)
                ? $valoracion->estado_animo : 'No documentado')->map->count()->all();
            $paletaAfectiva = ['#4a7054', '#9476b0', '#cb6f60', '#ba9c74'];
            $chartAfectiva = [
                'labels' => array_keys($conteoAf),
                'data' => array_values($conteoAf),
                'colors' => array_map(fn ($indice) => $paletaAfectiva[$indice % count($paletaAfectiva)], array_keys(array_values($conteoAf))),
                'total' => array_sum($conteoAf),
            ];
        }

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
            'puedeConsultarIadl',
            'puedeConsultarContactos',
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
            'chartSeguimiento'
        ))->layout('layouts.sistema');
    }

    private function autorizar(): User
    {
        $usuario = auth()->user() ? User::query()->find(auth()->id()) : null;
        abort_unless($usuario?->estado === 'ACTIVO'
            && $usuario->hasAnyRole(['SUPERADMINISTRADOR', 'PSICOLOGO/A'])
            && ! $usuario->hasRole('FAMILIAR')
            && $usuario->checkPermissionTo('aplicaciones_instrumento.ver', 'web')
            && $usuario->checkPermissionTo('valoraciones_psicologicas.ver', 'web')
            && $usuario->checkPermissionTo('atenciones.ver', 'web')
            && Gate::forUser($usuario)->allows('viewAny', Residente::class)
            && ($usuario->hasRole('SUPERADMINISTRADOR') || $usuario->personal()->where('estado', 'ACTIVO')->where('profesion', 'PSICOLOGIA')->exists()), 403);
        if (isset($this->residente)) {
            Gate::forUser($usuario)->authorize('view', $this->residente);
        }

        return $usuario;
    }
}
