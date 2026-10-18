<?php

namespace App\Modulos\Clinica\Aplicacion\Consultas;

use App\Compartido\Aplicacion\Contratos\ContextoEjecucion;
use App\Models\ControlCognitivo;
use App\Models\EvaluacionExperta;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\LecturaResultadosExperto;
use App\Modulos\Cuidados\Dominio\ObservacionCognitiva as Contrato;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

/** Lectura descriptiva de controles originales; no adapta evidencias ni ejecuta inferencias. */
final class ConsultarResumenCognitivoResidente
{
    public function __construct(
        private readonly ContextoEjecucion $contexto,
        private readonly LecturaResultadosExperto $experto,
    ) {}

    /** Permite distinguir consulta restringida de ausencia de registros en la ficha compartida. */
    public function disponible(Residente $residente): bool
    {
        $usuario = $this->usuarioActual();

        return $usuario && Gate::forUser($usuario)->allows('consultarResultados', [EvaluacionExperta::class, $residente]);
    }

    /** Relee cuenta, permisos y asignación antes de entregar datos, también fuera de Livewire. */
    public function consultar(Residente $residente, string $periodo = '30d'): array
    {
        $usuario = $this->usuarioActual();
        abort_unless($usuario, 403);
        Gate::forUser($usuario)->authorize('consultarResultados', [EvaluacionExperta::class, $residente]);
        Validator::make(['periodoEstado' => $periodo], ['periodoEstado' => 'required|in:7d,30d,90d'])->validate();
        $hasta = CarbonImmutable::now()->startOfSecond();
        $desde = $hasta->subDays(ConsultarResumenIntegralResidente::PERIODOS[$periodo]);
        $controles = ControlCognitivo::query()->where('cod_residente', $residente->getKey())
            ->where('estado', 'VIGENTE')->whereBetween('fecha_hora', [$desde, $hasta])
            ->with('personal:cod_personal,nombres,apellido_paterno,apellido_materno')
            ->orderByDesc('fecha_hora')->orderByDesc('cod_control_cognitivo')->get();
        $registros = $controles->map(fn ($c) => $this->original($c))->all();
        $observados = $controles->filter(fn ($c) => $this->tieneObservacion($c));
        $principales = $observados->filter(fn ($c) => $c->repite_preguntas === true || $c->olvida_indicaciones === true
            || in_array($c->memoria_reciente, ['ALTERACION_LEVE', 'ALTERADA'], true)
            || in_array($c->memoria_remota, ['ALTERACION_LEVE', 'ALTERADA'], true));
        $principal = $principales->first() ?? $observados->first() ?? $controles->first();
        $estado = $controles->isEmpty() ? 'Sin observaciones registradas'
            : ($observados->isEmpty() ? 'Información insuficiente' : 'Observación documentada');
        $titulo = match ($estado) {
            'Sin observaciones registradas' => 'No existen observaciones cognitivas disponibles para el período consultado.',
            'Información insuficiente' => 'Los controles disponibles no contienen observaciones interpretables para este resumen.',
            default => 'Hay observaciones cognitivas documentadas en el período.',
        };
        if ($principales->isNotEmpty()) {
            $estado = 'Señal preliminar';
            // La frase se limita a respuestas explícitas del mismo control, sin unir hechos de fechas distintas.
            $titulo = match (true) {
                $principal->repite_preguntas === true && $principal->olvida_indicaciones === true => 'Se documentó repetición de preguntas y olvido de indicaciones en un control cognitivo.',
                $principal->repite_preguntas === true => 'Se documentó repetición de preguntas en un control cognitivo.',
                $principal->olvida_indicaciones === true => 'Se documentó olvido de indicaciones en un control cognitivo.',
                default => 'Se documentaron dificultades en los campos descriptivos de memoria.',
            };
        }
        $contradicciones = $this->contradicciones($controles);
        if ($contradicciones) {
            $estado = 'Contradicción';
            $titulo = 'Existen respuestas diferentes para el mismo indicador y momento de registro.';
        }
        $resultado = $this->experto->resumenMemoriaEnPeriodo($usuario, $residente, $desde, $hasta);
        $alcance = 'Las observaciones pueden justificar una evaluación específica de memoria, pero no acreditan por sí solas una alteración cognitiva objetiva. La repetición de preguntas y el olvido de indicaciones no determinan por sí solos su causa.';
        if ($estado === 'Contradicción') {
            $alcance = 'Las respuestas discordantes requieren revisión de los controles originales. No se elige una respuesta ni se emite una conclusión cognitiva.';
        }

        return ['desde' => $desde->format('d/m/Y H:i'), 'hasta' => $hasta->format('d/m/Y H:i'),
            'estado' => $estado, 'observacion' => $titulo, 'alcance' => $alcance,
            'principal' => $principal ? $this->original($principal) : null,
            'por_que' => $controles->isEmpty() ? 'No se encontraron controles cognitivos vigentes en este período.'
                : 'Estos datos constan en controles cognitivos del residente, con su fecha y personal responsable. Varios registros pueden corresponder al mismo episodio; su cantidad no demuestra pérdida de memoria ni evidencia independiente.',
            'contradicciones' => $contradicciones, 'registros' => $registros,
            // El resultado experto conserva su propio estado y fecha; no sustituye las observaciones operativas.
            'experto' => $resultado,
            'analisis_url' => route($usuario->hasRole('ENFERMEROS') && ! $usuario->hasAnyRole(['MEDICO GENERAL/GERIATRA', 'SUPERADMINISTRADOR'])
                ? 'admin.enfermeria.pacientes.resultados-experto' : 'admin.medico.residente.resultados-experto', ['residente' => $residente->getKey()])];
    }

    /** Obtiene una identidad fresca para no conservar autorización tras una revocación. */
    private function usuarioActual(): ?User
    {
        $actor = $this->contexto->usuario();

        return $actor ? User::query()->find($actor->getKey()) : null;
    }

    /** NULL, vacío y «No valorable» no prueban desempeño; el texto se muestra sin analizarlo. */
    private function tieneObservacion(ControlCognitivo $control): bool
    {
        return filled(trim((string) $control->observacion)) || collect(array_keys(Contrato::campos()))
            ->contains(fn ($campo) => in_array($campo, Contrato::SENALES, true) ? is_bool($control->{$campo})
                : ($control->{$campo} !== 'NO_VALORABLE' && isset(Contrato::opciones($campo)[$control->{$campo} ?? ''])));
    }

    /** Solo detecta discordancia literal del mismo campo y momento; cambios entre fechas no son contradicción. */
    private function contradicciones(Collection $controles): array
    {
        $discordantes = [];
        foreach ($controles->groupBy(fn ($c) => $c->fecha_hora->format('Y-m-d H:i:s')) as $grupo) {
            foreach (Contrato::campos() as $campo => $definicion) {
                $valores = $grupo->pluck($campo)->filter(fn ($v) => $v !== null && $v !== '' && $v !== 'NO_VALORABLE')->uniqueStrict();
                if ($valores->count() > 1) {
                    $discordantes[] = $definicion[0].' · '.$grupo->first()->fecha_hora->format('d/m/Y H:i');
                }
            }
        }

        return $discordantes;
    }

    /** Conserva todos los campos del control sin mostrar identificadores o valores técnicos al médico. */
    private function original(ControlCognitivo $control): array
    {
        $campos = [];
        foreach (Contrato::campos() as $campo => $definicion) {
            $valor = $control->{$campo};
            $campos[$definicion[0]] = in_array($campo, Contrato::SENALES, true) || $valor === null || $valor === ''
                || isset(Contrato::opciones($campo)[$valor]) ? Contrato::etiqueta($campo, $valor) : 'Valor sin descripción disponible';
        }

        return ['ancla' => 'control-cognitivo-'.hash('xxh64', $control->getKey()),
            'fecha' => $control->fecha_hora->format('d/m/Y H:i'), 'estado' => 'Vigente',
            'autor' => $control->personal ? trim(implode(' ', [$control->personal->nombres, $control->personal->apellido_paterno,
                $control->personal->apellido_materno])) : 'Autor no disponible',
            'campos' => $campos, 'observacion' => $control->observacion];
    }
}
