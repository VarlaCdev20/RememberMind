<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas;

use App\Models\EvaluacionExperta;
use App\Models\Residente;
use App\Models\User;
use App\Models\ValoracionPetersenOriginal;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarValoracionPetersenOriginal;
use App\Modulos\ApoyoDecision\Aplicacion\Servicios\ContratoFuentesPetersen as F;
use DomainException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use MotorExperto\dominio\petersen\ValoracionOriginalPetersen as V;

/** Interacción sobre actos originales; la Action aplica las mismas garantías fuera de esta pantalla. */
final class CapturaPetersenPanel extends Component
{
    use WithPagination;

    #[Locked]
    public string $codResidente;

    #[Locked]
    public ?string $solicitud = null;

    #[Locked]
    public ?string $anterior = null;

    #[Locked]
    public string $tipoRegistro = 'REGISTRO';

    #[Locked]
    public ?string $fechaOriginal = null;

    #[Locked]
    public ?string $mensaje = null;

    public array $form = [];

    public string $tipoSoporte = '';

    public string $campoFuente = '';

    public string $registroFuente = '';

    public string $busquedaFuente = '';

    public const ELEMENTOS = ['COG-MEM' => 'Memoria', 'COG-ATE' => 'Atención', 'COG-EJE' => 'Funciones ejecutivas',
        'COG-LEN' => 'Lenguaje', 'COG-VIS' => 'Función visuoespacial', 'C01' => 'Cambio corroborado',
        'C03' => 'Independencia instrumental relativa', 'C04' => 'Evaluación diferencial', 'C05' => 'Calidad e interpretabilidad del conjunto'];

    public const TIPOS = ['MEDICION' => 'Medición original', 'HISTORIA_PREVIA' => 'Funcionamiento previo', 'CORROBORACION' => 'Corroboración',
        'IADL' => 'Actividad instrumental', 'DIFERENCIAL' => 'Revisión diferencial', 'CALIDAD' => 'Condiciones y calidad', 'DOMINIO_REVISADO' => 'Valoración de dominio revisada'];

    public function mount(Residente $residente): void
    {
        $this->codResidente = $residente->getKey();
        $this->autorizar();
    }

    private function autorizar(): array
    {
        $usuario = auth()->user() ? User::query()->findOrFail(auth()->id()) : null;
        abort_unless($usuario, 403);
        auth()->setUser($usuario);
        $residente = Residente::query()->findOrFail($this->codResidente);
        Gate::forUser($usuario)->authorize('consultarOriginales', [ValoracionPetersenOriginal::class, $residente]);

        return [$usuario, $residente];
    }

    public function nuevo(string $elemento): void
    {
        [$usuario, $residente] = $this->autorizar();
        Gate::forUser($usuario)->authorize('crearOriginal', [ValoracionPetersenOriginal::class, $residente]);
        abort_unless(isset(self::ELEMENTOS[$elemento]), 422);
        $this->limpiar();
        $this->solicitud = (string) Str::uuid();
        $this->form = ['elemento' => $elemento, 'estado_original' => '', 'justificacion' => '', 'fecha_hora' => '', 'soportes' => []];
    }

    public function corregir(string $id, bool $anular = false): void
    {
        [$usuario, $residente] = $this->autorizar();
        $original = ValoracionPetersenOriginal::query()->with('soportes')->where('cod_residente', $residente->getKey())->findOrFail($id);
        Gate::forUser($usuario)->authorize('rectificarOriginal', $original);
        if ($original->tipo_registro === 'ANULACION'
            || ValoracionPetersenOriginal::query()->where('cod_valoracion_anterior', $id)->exists()) {
            $this->addError('historial', 'Este registro ya tiene una sucesora o fue anulado. Consulte la cadena actualizada antes de corregir.');

            return;
        }
        $this->limpiar();
        $this->solicitud = (string) Str::uuid();
        $this->anterior = $id;
        $this->tipoRegistro = $anular ? 'ANULACION' : 'RECTIFICACION';
        $this->fechaOriginal = $original->fecha_hora->format('Y-m-d\TH:i:s.u');
        $this->form = ['elemento' => $original->elemento, 'estado_original' => $anular ? null : $original->estado_original,
            'cod_atencion' => $original->cod_atencion, 'cod_documento_clinico' => $original->cod_documento_clinico,
            'localizador_fuente' => $original->localizador_fuente,
            'justificacion' => '', 'fecha_hora' => $original->fecha_hora->format('Y-m-d\TH:i:s.v'), 'soportes' => $anular ? [] : $original->soportes
                ->map(fn ($s) => array_filter($s->only(['tipo_soporte', ...array_keys(F::FUENTES)]), fn ($v) => $v !== null))->all()];
        if (! $anular) {
            foreach (V::COMPROBACIONES[str_starts_with($original->elemento, 'COG-') ? 'DOMINIO' : $original->elemento] as $campo) {
                $this->form[$campo] = $original->$campo === null ? '' : ($original->$campo ? '1' : '0');
            }
        }
    }

    public function agregarSoporte(): void
    {
        $this->autorizar();
        abort_unless($this->solicitud !== null && $this->tipoRegistro !== 'ANULACION', 409);
        if (! in_array($this->campoFuente, F::admitidas($this->form['elemento'] ?? '', $this->tipoSoporte), true) || $this->registroFuente === '') {
            $this->addError('soporte', 'Seleccione el tipo, la fuente y el registro original.');

            return;
        }
        $this->form['soportes'][] = ['tipo_soporte' => $this->tipoSoporte, $this->campoFuente => $this->registroFuente];
        $this->registroFuente = '';
        $this->resetErrorBag('soporte');
    }

    public function quitarSoporte(int $orden): void
    {
        $this->autorizar();
        unset($this->form['soportes'][$orden]);
        $this->form['soportes'] = array_values($this->form['soportes'] ?? []);
    }

    public function guardar(): void
    {
        [$usuario, $residente] = $this->autorizar();
        abort_unless($this->solicitud !== null, 409);
        $entrada = $this->form;
        // datetime-local admite milisegundos. La representación sin editar conserva el instante original completo.
        if ($this->fechaOriginal !== null && ($this->tipoRegistro === 'ANULACION'
            || (is_string($entrada['fecha_hora'] ?? null)
                && preg_match('/^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2})(?::(\d{2})(?:\.(\d{1,3}))?)?$/D', $entrada['fecha_hora'], $partes)
                && substr($this->fechaOriginal, 0, 23) === $partes[1].':'.($partes[2] ?? '00').'.'.str_pad($partes[3] ?? '', 3, '0')))) {
            $entrada['fecha_hora'] = $this->fechaOriginal;
        }
        if (array_intersect(array_keys($entrada), ['solicitud_uuid', 'tipo_registro', 'cod_valoracion_anterior'])) {
            $this->addError('registro', 'La identidad de la captura cambió. Recargue antes de registrar la valoración.');

            return;
        }
        foreach (array_merge(...array_values(V::COMPROBACIONES)) as $campo) {
            if (array_key_exists($campo, $entrada) && $entrada[$campo] === '') {
                $entrada[$campo] = null;
            }
        }
        if (($entrada['estado_original'] ?? null) === '') {
            $entrada['estado_original'] = null;
        }
        foreach (['cod_atencion', 'cod_documento_clinico', 'localizador_fuente'] as $campo) {
            if (($entrada[$campo] ?? null) === '') {
                $entrada[$campo] = null;
            }
        }
        try {
            $original = app(RegistrarValoracionPetersenOriginal::class)->ejecutar($usuario, $residente, array_replace($entrada, [
                'solicitud_uuid' => $this->solicitud, 'tipo_registro' => $this->tipoRegistro, 'cod_valoracion_anterior' => $this->anterior]));
            $this->limpiar();
            $this->mensaje = 'Valoración original registrada: '.$original->getKey().'. Sus fuentes permanecen vinculadas; la revisión del resultado se registra por separado.';
            $this->dispatch('petersen-captura-cerrada');
            $this->resetPage();
        } catch (DomainException $e) {
            $this->addError('registro', $e->getMessage());
        }
    }

    public function cancelar(): void
    {
        $this->autorizar();
        $this->limpiar();
        $this->dispatch('petersen-captura-cerrada');
    }

    public function mostrarRegistro(string $id): void
    {
        [, $residente] = $this->autorizar();
        $registro = ValoracionPetersenOriginal::query()->where('cod_residente', $residente->getKey())->findOrFail($id);
        $anteriores = ValoracionPetersenOriginal::query()->where('cod_residente', $residente->getKey())
            ->where(fn ($q) => $q->where('registrado_en', '>', $registro->getRawOriginal('registrado_en'))
                ->orWhere(fn ($q) => $q->where('registrado_en', $registro->getRawOriginal('registrado_en'))->where('cod_valoracion_petersen', '>', $id)))->count();
        $this->setPage(intdiv($anteriores, 15) + 1);
        $this->mensaje = 'Registro consultado: '.$id.'.';
        $this->dispatch('petersen-registro-mostrado', registro: $id);
    }

    private function limpiar(): void
    {
        $this->reset('form', 'solicitud', 'anterior', 'tipoRegistro', 'fechaOriginal', 'tipoSoporte', 'campoFuente', 'registroFuente', 'busquedaFuente');
        $this->resetErrorBag();
    }

    public function render()
    {
        [$usuario, $residente] = $this->autorizar();
        abort_unless($this->form === [] || isset(self::ELEMENTOS[$this->form['elemento'] ?? '']), 422);
        $registros = ValoracionPetersenOriginal::query()->where('cod_residente', $residente->getKey())->with(['personal', 'sucesora'])
            ->orderByDesc('registrado_en')->orderByDesc('cod_valoracion_petersen')->paginate(15);
        $vigentes = ValoracionPetersenOriginal::query()->where('cod_residente', $residente->getKey())
            ->where('tipo_registro', '!=', 'ANULACION')->whereDoesntHave('sucesora')
            ->get(['elemento', 'estado_original'])->groupBy('elemento');
        $cobertura = collect(array_keys(self::ELEMENTOS))->mapWithKeys(static fn (string $elemento) => [$elemento => [
            'cantidad' => $vigentes->get($elemento, collect())->count(),
            'estados' => $vigentes->get($elemento, collect())->pluck('estado_original')->unique()->values()->all(),
        ]])->all();
        $registrados = count(array_filter($cobertura, static fn (array $estado) => $estado['cantidad'] > 0));
        $tipos = array_filter(self::TIPOS, fn ($nombre, $tipo) => F::admitidas($this->form['elemento'] ?? '', $tipo) !== [], ARRAY_FILTER_USE_BOTH);
        $fuentes = array_values(array_filter(F::admitidas($this->form['elemento'] ?? '', $this->tipoSoporte), fn ($fk) => $usuario->checkPermissionTo(F::FUENTES[$fk][1], 'web')
            && ($fk !== 'cod_control_iadl' || Gate::forUser($usuario)->allows('consultarFuncionalidad', [EvaluacionExperta::class, $residente]))));
        $opciones = [];
        if (in_array($this->campoFuente, $fuentes, true)) {
            [$modelo] = F::FUENTES[$this->campoFuente];
            $q = $modelo::query()->where('cod_residente', $residente->getKey());
            if ($this->busquedaFuente !== '') {
                $q->where((new $modelo)->getKeyName(), 'like', '%'.mb_substr($this->busquedaFuente, 0, 40).'%');
            }
            $opciones = $q->orderByDesc((new $modelo)->getKeyName())->limit(50)->get()->map(fn ($r) => ['id' => $r->getKey(),
                'etiqueta' => $r->getKey().' · '.($r->fecha_hora?->format('d/m/Y H:i') ?? $r->fecha_referencia?->format('d/m/Y') ?? 'Fecha no identificada')
                    .' · Autor: '.($r->cod_personal ?? 'No identificado')
                    .' · Episodio: '.($r->cod_atencion ?? 'No identificado')
                    .' · Estado: '.($r->estado ?? 'No identificado')
                    .($r->elemento ? ' · '.(self::ELEMENTOS[$r->elemento] ?? $r->elemento) : '')])->all();
        }
        $comprobaciones = $this->form === [] ? [] : V::COMPROBACIONES[str_starts_with($this->form['elemento'], 'COG-') ? 'DOMINIO' : $this->form['elemento']];
        $puedeCrear = Gate::forUser($usuario)->allows('crearOriginal', [ValoracionPetersenOriginal::class, $residente]);
        $profesional = $usuario->personal()->first();
        $urlRetorno = match (true) {
            $usuario->hasRole('PSICOLOGO/A') => route('admin.psicologia.evaluacion.cognitiva'),
            $usuario->hasRole('MEDICO GENERAL/GERIATRA') => route('admin.medico.residente.ficha', ['adulto' => $residente]),
            default => route('admin.sistema-experto.index'),
        };
        $textoRetorno = match (true) {
            $usuario->hasRole('PSICOLOGO/A') => 'Volver a Valoraciones cognitivas',
            $usuario->hasRole('MEDICO GENERAL/GERIATRA') => 'Volver a la ficha clínica',
            default => 'Volver al sistema experto',
        };
        $labelsFuentes = ['cod_aplicacion' => 'Aplicación de instrumento', 'cod_control_cognitivo' => 'Control cognitivo',
            'cod_control_iadl' => 'Control de actividad instrumental (IADL)', 'cod_valoracion_psicologica' => 'Valoración psicológica',
            'cod_antecedente' => 'Antecedente clínico', 'cod_diagnostico' => 'Diagnóstico', 'cod_documento_clinico' => 'Documento clínico original',
            'cod_valoracion_revisada' => 'Valoración de dominio revisada'];
        $labelsComprobaciones = ['alcance_dominio' => 'Alcance suficiente del dominio', 'contexto_interpretado' => 'Contexto interpretado',
            'cambio_respecto_previo' => 'Cambio respecto al funcionamiento previo', 'corroboracion_independiente' => 'Corroboración independiente',
            'revision_integrada' => 'Revisión integrada de actividades instrumentales', 'oportunidad_adecuada' => 'Oportunidad adecuada para la actividad',
            'atribucion_revisada' => 'Causa de la ayuda revisada', 'revision_explicita' => 'Revisión diferencial explícita',
            'factores_pertinentes_revisados' => 'Factores pertinentes revisados', 'impedimento_no_resuelto' => 'Impedimento aún sin resolver',
            'suficiencia_conjunto' => 'Suficiencia del conjunto de valoraciones', 'condiciones_revisadas' => 'Condiciones de aplicación revisadas',
            'exclusiones_revisadas' => 'Exclusiones revisadas'];

        return view('apoyodecision::livewire.petersen.captura-original', compact('residente', 'registros', 'tipos', 'fuentes', 'opciones', 'comprobaciones', 'puedeCrear', 'profesional', 'urlRetorno', 'textoRetorno', 'labelsFuentes', 'labelsComprobaciones', 'cobertura', 'registrados'))
            ->layout('layouts.sistema');
    }
}
