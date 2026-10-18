<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\ControlFuncionalIadl;
use App\Models\EvaluacionExperta;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

/** Lee actividades IADL autorizadas; nunca acredita independencia relativa ni C03. */
final class ConsultarAutonomiaDescriptiva
{
    public function consultar(User $usuario, Residente $residente): array
    {
        Gate::forUser($usuario)->authorize('consultarResultados', [EvaluacionExperta::class, $residente]);
        $vacio = ['disponible' => false, 'actividades' => [], 'acredita_independencia' => false];
        if (! Gate::forUser($usuario)->allows('consultarFuncionalidad', [EvaluacionExperta::class, $residente])) {
            return $vacio + ['motivo' => 'La información sobre actividades cotidianas no está disponible con su autorización actual.'];
        }
        if (! Schema::hasTable('controles_funcionales_iadl')) {
            return $vacio + ['motivo' => 'La fuente de actividades cotidianas no está instalada.'];
        }
        $filas = FuentesValoracionOperativa::limitar(ControlFuncionalIadl::query())
            ->where('cod_residente', $residente->getKey())->where('fecha_hora', '<=', now())->where('registrado_en', '<=', now())
            ->whereIn('estado', ['REGISTRADO', 'RECTIFICACION'])
            ->whereNotExists(fn ($q) => $q->selectRaw('1')->from('controles_funcionales_iadl as sucesora')
                ->whereColumn('sucesora.cod_control_anterior', 'controles_funcionales_iadl.cod_control_iadl')
                ->where('sucesora.fecha_hora', '<=', now())->where('sucesora.registrado_en', '<=', now()))
            ->orderByDesc('fecha_hora')->orderByDesc('registrado_en')->orderByDesc('cod_control_iadl')->get();
        $autores = Personal::whereIn('cod_personal', $filas->pluck('cod_personal')->unique())
            ->get(['cod_personal', 'nombres', 'apellido_paterno', 'apellido_materno'])->keyBy('cod_personal');
        $actividades = [];
        foreach ($filas->groupBy('actividad') as $actividad => $grupo) {
            $fila = $grupo->first();
            $autor = $autores->get($fila->cod_personal);
            $simultaneas = $grupo->filter(fn ($otra) => $otra->fecha_hora->equalTo($fila->fecha_hora));
            $campos = ['oportunidad', 'desempeno', 'tipo_ayuda', 'causa_ayuda', 'nivel_previo'];
            $discrepancia = $simultaneas->map(fn ($otra) => implode('|', $otra->only($campos)))->unique()->count() > 1;
            $variantes = $discrepancia ? $simultaneas->map(function ($otra) use ($autores): array {
                $responsable = $autores->get($otra->cod_personal);

                return ['actividad' => $otra->actividad, 'oportunidad' => $otra->oportunidad, 'desempeno' => $otra->desempeno,
                    'ayuda' => $otra->tipo_ayuda, 'causa' => $otra->causa_ayuda, 'nivel_previo' => $otra->nivel_previo,
                    'fecha' => $otra->fecha_hora->format('d/m/Y H:i'), 'autor' => $responsable
                        ? trim(implode(' ', [$responsable->nombres, $responsable->apellido_paterno, $responsable->apellido_materno])) : 'Responsable no disponible',
                    'observacion' => $otra->observacion, 'discrepancia' => false];
            })->values()->all() : [];
            $actividades[] = ['actividad' => $actividad, 'oportunidad' => $fila->oportunidad,
                'desempeno' => $fila->desempeno, 'ayuda' => $fila->tipo_ayuda, 'causa' => $fila->causa_ayuda,
                'nivel_previo' => $fila->nivel_previo, 'fuente' => $fila->fuente, 'fecha' => $fila->fecha_hora->format('d/m/Y H:i'),
                'autor' => $autor ? trim(implode(' ', [$autor->nombres, $autor->apellido_paterno, $autor->apellido_materno])) : 'Responsable no disponible',
                'observacion' => $fila->observacion,
                'discrepancia' => $discrepancia, 'variantes' => $variantes,
                'referencia' => $fila->getKey()];
        }

        return ['disponible' => true, 'actividades' => $actividades, 'acredita_independencia' => false,
            'motivo' => $actividades ? 'Observaciones descriptivas actuales; no equivalen a una valoración funcional integrada.'
                : 'No hay observaciones de actividades cotidianas disponibles para esta consulta.'];
    }
}
