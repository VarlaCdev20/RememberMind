<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\ControlCognitivo;
use App\Models\EvaluacionExperta;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\ConocimientoObservacional;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use MotorExperto\dominio\conocimiento\reglas\EvaluadorObservaciones;

/**
 * Consulta autorizada del primer nivel observacional sobre controles originales.
 * Cada página declara su alcance; no cambia una evaluación histórica ni guarda una traza ficticia.
 * El motor recibe una instantánea escalar, no Models, SQL o dependencias de Laravel.
 */
final class ConsultarHallazgosObservacionales
{
    public function consultar(User $usuario, Residente $residente, int $pagina = 1): array
    {
        $usuario = User::query()->findOrFail($usuario->getKey());
        Gate::forUser($usuario)->authorize('consultarResultados', [EvaluacionExperta::class, $residente]);
        $corte = CarbonImmutable::now();
        $registros = $this->seleccionar($residente, $corte)->paginate(10, ['*'], 'observaciones', max(1, $pagina));

        return $this->proyectar($registros, $residente, $corte);
    }

    /** La operación de guardado captura todas las fuentes anteriores al corte, no solo la página visible. */
    public function instantaneaCompleta(User $usuario, Residente $residente, CarbonImmutable $corte): array
    {
        $usuario = User::query()->findOrFail($usuario->getKey());
        Gate::forUser($usuario)->authorize('ejecutarObservaciones', [EvaluacionExperta::class, $residente]);

        return $this->proyectar($this->seleccionar($residente, $corte)->lockForUpdate()->get(), $residente, $corte);
    }

    private function seleccionar(Residente $residente, CarbonImmutable $corte): Builder
    {
        return FuentesValoracionOperativa::limitar(ControlCognitivo::query())
            ->where('cod_residente', $residente->getKey())->where('fecha_hora', '<=', $corte)
            ->with(['personal:cod_personal,nombres,apellido_paterno,apellido_materno', 'atencion:cod_atencion,cod_residente'])
            ->orderByDesc('fecha_hora')->orderByDesc('cod_control_cognitivo');
    }

    private function proyectar(iterable $registros, Residente $residente, CarbonImmutable $corte): array
    {
        $instantanea = [];
        $autores = [];
        $fechas = [];
        foreach ($registros as $r) {
            $valores = [];
            foreach (EvaluadorObservaciones::REGLAS as $regla) {
                // Se validan los escalares originales: el cast booleano no debe convertir texto inválido en true.
                $original = $r->getRawOriginal($regla['campo']);
                $valores[$regla['campo']] = match (true) {
                    $original === true || $original === 1 || $original === '1' => true,
                    $original === false || $original === 0 || $original === '0' => false,
                    default => null,
                };
            }
            $autor = $r->personal;
            $autores[$r->getKey()] = $autor ? trim(implode(' ', [$autor->nombres, $autor->apellido_paterno, $autor->apellido_materno])) : 'Responsable no disponible';
            $fechas[$r->getKey()] = $r->fecha_hora?->format('d/m/Y H:i') ?? 'Fecha no disponible';
            $instantanea[] = ['id' => $r->getKey(), 'residente' => $r->cod_residente,
                'autor' => $autor?->getKey(), 'fecha' => $r->fecha_hora?->toDateTimeImmutable(), 'estado' => $r->estado,
                'contexto_coherente' => $r->cod_atencion === null || $r->getRelation('atencion')?->cod_residente === $r->cod_residente,
                'episodio' => $r->cod_atencion ? 'atencion:'.$r->cod_atencion : 'control:'.$r->getKey(), 'valores' => $valores];
        }
        $conocimiento = app(ConocimientoObservacional::class)->vigente();
        $informe = (new EvaluadorObservaciones)->evaluar($residente->getKey(), $corte->toDateTimeImmutable(), $instantanea, $conocimiento);
        foreach ($informe['resultados'] as &$resultado) {
            $resultado['fuente']['nombre_autor'] = $autores[$resultado['fuente']['registro']];
            $resultado['fuente']['fecha_visible'] = $fechas[$resultado['fuente']['registro']];
        }
        unset($resultado);

        return $informe + ['registros' => $registros, 'instantanea' => $instantanea];
    }
}
