<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\User;
use App\Models\VersionModeloExperto;
use App\Modulos\ApoyoDecision\Aplicacion\Servicios\EscenariosPerfilExperimental;
use DomainException;
use Illuminate\Support\Facades\Gate;
use MotorExperto\aplicacion\casos_uso\EjecutarPerfilExperimental;
use MotorExperto\investigacion\dcl\CatalogoPerfilCincoDominios;

/** Consulta técnica de entradas predeterminadas: no recibe residentes, paquetes ni fuentes del cliente. */
final class ConsultarLaboratorioExperimental
{
    public function ejecutar(User $autor, string $escenario): array
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new DomainException('El laboratorio está disponible únicamente en desarrollo o pruebas.');
        }
        $usuario = User::query()->findOrFail($autor->getKey());
        Gate::forUser($usuario)->authorize('consultar', VersionModeloExperto::class);
        $r = (new EjecutarPerfilExperimental)->ejecutar(...EscenariosPerfilExperimental::entrada($escenario));
        $dominios = [];
        foreach (CatalogoPerfilCincoDominios::DOMINIOS as $codigo => $nombre) {
            $salida = $r['perfil_cinco_dominios'][$codigo]['salida'];
            $dominios[$codigo] = ['nombre' => $nombre,
                'evaluabilidad' => $codigo === 'COG-MEM' ? $salida['evaluabilidad']['estado'] : $salida['resultado']['evaluabilidad'],
                'resultado' => $codigo === 'COG-MEM' ? ($salida['resultado']['codigo'] ?? 'SIN_RESULTADO') : $salida['resultado']['estado_tecnico'],
                'referencia' => $r['resumen']['petersen']['dominios'][$codigo],
                'subcomponentes' => array_filter($r['subcomponentes'], static fn ($s) => $s['dominio'] === $codigo),
                'reglas' => array_filter($r['reglas_examinadas']['g09'], static fn ($regla) => $regla['dominio'] === $codigo),
                'fuentes' => array_values(array_filter($r['hechos_instrumentales'], static fn ($h) => $h['criterio'] === $codigo)),
                'informe' => $r['resumen']['g09'][$codigo] ?? null];
        }

        return ['escenario' => EscenariosPerfilExperimental::NOMBRES[$escenario], 'resultado' => $r, 'dominios' => $dominios,
            'requisitos' => ['DCL-C01' => 'Cambio cognitivo', 'DCL-C02' => 'Alteración objetiva',
                'DCL-C03' => 'Autonomía e IADL', 'DCL-C04' => 'Evaluación diferencial', 'DCL-C05' => 'Calidad y evaluabilidad']];
    }
}
