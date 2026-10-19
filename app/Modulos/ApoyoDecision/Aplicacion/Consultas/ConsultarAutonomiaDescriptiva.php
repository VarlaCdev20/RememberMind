<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\ControlFuncionalIadl;
use App\Models\EvaluacionExperta;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

/** Lee actividades IADL autorizadas; nunca acredita independencia relativa ni C03. */
final class ConsultarAutonomiaDescriptiva
{
    public function consultar(User $usuario, Residente $residente): array
    {
        $usuario = User::query()->findOrFail($usuario->getKey());
        $residente = Residente::query()->findOrFail($residente->getKey());
        $corte = CarbonImmutable::now();
        Gate::forUser($usuario)->authorize('consultarResultados', [EvaluacionExperta::class, $residente]);
        $vacio = ['disponible' => false, 'actividades' => [], 'acredita_independencia' => false];
        if (! Gate::forUser($usuario)->allows('consultarFuncionalidad', [EvaluacionExperta::class, $residente])) {
            return $vacio + ['motivo' => 'La información sobre actividades cotidianas no está disponible con su autorización actual.'];
        }
        if (! Schema::hasTable('controles_funcionales_iadl')) {
            return $vacio + ['motivo' => 'La fuente de actividades cotidianas no está instalada.'];
        }
        $filas = FuentesValoracionOperativa::limitar(ControlFuncionalIadl::query())
            ->where('cod_residente', $residente->getKey())->where('fecha_hora', '<=', $corte)->where('registrado_en', '<=', $corte)
            ->whereIn('estado', ['REGISTRADO', 'RECTIFICACION'])
            ->whereNotExists(fn ($q) => $q->selectRaw('1')->from('controles_funcionales_iadl as sucesora')
                ->whereColumn('sucesora.cod_control_anterior', 'controles_funcionales_iadl.cod_control_iadl')
                ->where('sucesora.fecha_hora', '<=', $corte)->where('sucesora.registrado_en', '<=', $corte))
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

    /**
     * Captura longitudinal al corte para preparar soportes institucionales.
     * Conserva originales y rectificaciones; no elige la última actividad para acreditar C03.
     * No guarda datos ni completa una evaluación histórica con información actual.
     */
    public function capturar(User $usuario, Residente $residente, CarbonImmutable $corte): array
    {
        $usuario = User::query()->findOrFail($usuario->getKey());
        $residente = Residente::query()->findOrFail($residente->getKey());
        Gate::forUser($usuario)->authorize('consultarResultados', [EvaluacionExperta::class, $residente]);
        $base = ['contrato' => 'IADL-DESCRIPTIVA-1', 'residente' => $residente->getKey(),
            'fecha_corte' => $corte->format('Y-m-d\TH:i:s.uP'), 'acredita_independencia' => false, 'persistido' => false];
        if ($corte->greaterThan(CarbonImmutable::now())) {
            throw new DomainException('La captura funcional no admite un corte futuro.');
        }
        if (! Gate::forUser($usuario)->allows('consultarFuncionalidad', [EvaluacionExperta::class, $residente])) {
            return $base + ['disponible' => false, 'motivo' => 'ACCESO_A_FUENTE_IADL_NO_DISPONIBLE', 'registros' => []];
        }
        if (! Schema::hasTable('controles_funcionales_iadl')) {
            return $base + ['disponible' => false, 'motivo' => 'FUENTE_IADL_NO_INSTALADA', 'registros' => []];
        }
        $filas = FuentesValoracionOperativa::limitar(ControlFuncionalIadl::query())
            ->where('cod_residente', $residente->getKey())->where('fecha_hora', '<=', $corte)->where('registrado_en', '<=', $corte)
            ->whereIn('estado', ['REGISTRADO', 'RECTIFICACION'])->with(['personal', 'atencion'])
            ->orderBy('fecha_hora')->orderBy('registrado_en')->orderBy('cod_control_iadl')->get();
        $rectificados = $filas->pluck('cod_control_anterior')->filter()->all();
        $registros = [];
        foreach ($filas as $f) {
            $original = $f->only(['cod_control_iadl', 'cod_residente', 'cod_personal', 'cod_usuario_registro', 'cod_atencion',
                'cod_control_anterior', 'actividad', 'oportunidad', 'desempeno', 'tipo_ayuda', 'causa_ayuda', 'nivel_previo', 'fuente', 'estado', 'observacion']);
            $original['fecha_hora'] = $f->fecha_hora->format('Y-m-d\TH:i:s.uP');
            $original['registrado_en'] = $f->registrado_en->format('Y-m-d\TH:i:s.uP');
            $original['autor_original'] = $f->personal?->only(['cod_personal', 'nombres', 'apellido_paterno', 'apellido_materno']);
            $original['atencion_original'] = $f->atencion?->only(['cod_atencion', 'cod_residente']);
            $motivos = [];
            if (in_array($f->getKey(), $rectificados, true)) {
                $motivos[] = 'ORIGINAL_RECTIFICADO_AL_CORTE';
            }
            if ($f->personal === null || ($f->cod_atencion !== null && $f->atencion?->cod_residente !== $residente->getKey())) {
                $motivos[] = 'PROCEDENCIA_IADL_INCOMPATIBLE';
            }
            $registros[] = ['original' => $original, 'admisible_como_fuente_descriptiva' => $motivos === [], 'motivos' => $motivos];
        }

        return $base + ['disponible' => true, 'registros' => $registros];
    }
}
