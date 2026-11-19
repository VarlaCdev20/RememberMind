<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\EvaluacionExperta;
use App\Models\Residente;
use App\Models\User;
use App\Models\ValoracionPetersenOriginal;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\ResultadoInstitucionalConservado as R;
use App\Modulos\ApoyoDecision\Aplicacion\Servicios\ContratoFuentesPetersen;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\ResultadoInstitucionalSQL;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

/** Historial del resultado aprobado, reautorizado por residente y por las fuentes conservadas. */
final readonly class ConsultarResultadoInstitucional
{
    public function __construct(private ResultadoInstitucionalSQL $sql) {}

    public function instalado(): bool { return Schema::hasColumn('evaluaciones_expertas', 'institucional_resultado'); }

    public function historial(User $usuario, Residente $residente, int $pagina = 1): array
    {
        $usuario = User::query()->findOrFail($usuario->getKey());
        Gate::forUser($usuario)->authorize('consultarResultados', [EvaluacionExperta::class, $residente]);
        if (! $this->instalado()) { return ['items' => [], 'pagina' => 1, 'ultima' => 1, 'total' => 0]; }
        $historia = EvaluacionExperta::query()->where('cod_residente', $residente->getKey())->where('version_conservacion', R::VERSION)
            ->where('estado_ejecucion', 'FINALIZADA')->orderByDesc('fecha_hora_inicio')->orderByDesc('cod_evaluacion_experta')
            ->paginate(10, ['cod_evaluacion_experta', 'fecha_hora_inicio'], 'historiaInstitucional', max(1, $pagina));
        return ['items' => $historia->map(fn ($e) => ['id' => $e->getKey(), 'fecha' => $e->fecha_hora_inicio->format('d/m/Y H:i'),
            'resultado' => 'Resultado original y revisiones'])->all(), 'pagina' => $historia->currentPage(), 'ultima' => $historia->lastPage(), 'total' => $historia->total()];
    }

    public function consultar(User $usuario, Residente $residente, string $id): array
    {
        $usuario = User::query()->findOrFail($usuario->getKey());
        $residente = Residente::query()->findOrFail($residente->getKey());
        Gate::forUser($usuario)->authorize('consultarResultados', [EvaluacionExperta::class, $residente]);
        EvaluacionExperta::query()->where('cod_residente', $residente->getKey())->where('version_conservacion', R::VERSION)->whereKey($id)->firstOrFail();
        Gate::forUser($usuario)->authorize('consultarOriginales', [ValoracionPetersenOriginal::class, $residente]);
        $d = $this->sql->leer($id);
        $enfermeria = Gate::forUser($usuario)->allows('consultarFuentesParaResultado', [EvaluacionExperta::class, $residente]);
        foreach ($d['contenido']['hechos'] as $h) {
            if (in_array($h['tipo'], ['APLICACION_INSTRUMENTAL', 'RESPUESTA_INSTRUMENTAL'], true)) {
                foreach (CapturasInstrumentalesResidente::PERMISOS_LECTURA as $permiso) { abort_unless($enfermeria || $usuario->checkPermissionTo($permiso, 'web'), 403); }
            }
            if (str_starts_with($h['concepto'], 'lectura:')) {
                $fk = substr($h['concepto'], 8);
                abort_unless(isset(ContratoFuentesPetersen::FUENTES[$fk]) && ($enfermeria || $usuario->checkPermissionTo(ContratoFuentesPetersen::FUENTES[$fk][1], 'web')), 403);
            }
        }
        // Solo denominaciones del conocimiento referenciado; las decisiones siguen siendo las conservadas.
        $d['nombres_criterios'] = \Illuminate\Support\Facades\DB::table('nodos_semanticos')->whereIn('cod_nodo_semantico', array_column($d['contenido']['criterios'], 'criterio'))->orderBy('cod_nodo_semantico')->pluck('nombre', 'cod_nodo_semantico')->all();
        $reglas = array_merge(...array_column($d['contenido']['criterios'], 'reglas'));
        $d['nombres_reglas'] = \Illuminate\Support\Facades\DB::table('reglas_expertas')->whereIn('cod_regla_experta', array_column($reglas, 'regla'))->orderBy('cod_regla_experta')->pluck('nombre', 'cod_regla_experta')->all();
        return $d;
    }
}
