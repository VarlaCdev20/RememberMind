<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\AplicacionInstrumento;
use App\Models\EvaluacionExperta;
use App\Models\OpcionPregunta;
use App\Models\PreguntaInstrumento;
use App\Models\Residente;
use App\Models\User;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Facades\Gate;
use MotorExperto\dominio\instrumentos\ExtractorCapturaInstrumental;

/** Lectura institucional por lotes; captura originales sin escribir resultados ni identificar pruebas por similitud. */
final class CapturasInstrumentalesResidente
{
    public function consultar(User $usuario, Residente $residente, CarbonImmutable $corte): array
    {
        $usuario = User::query()->findOrFail($usuario->getKey());
        $residente = Residente::query()->findOrFail($residente->getKey());
        Gate::forUser($usuario)->authorize('consultarResultados', [EvaluacionExperta::class, $residente]);
        foreach (['aplicaciones_instrumento.ver', 'respuestas_instrumento.ver', 'instrumentos.ver', 'preguntas_instrumento.ver', 'opciones_pregunta.ver'] as $permiso) {
            if (! $usuario->checkPermissionTo($permiso, 'web')) {
                return ['disponible' => false, 'motivo' => 'ACCESO_A_FUENTES_NO_DISPONIBLE', 'capturas' => []];
            }
        }
        $aplicaciones = FuentesValoracionOperativa::limitar(AplicacionInstrumento::query())->where('cod_residente', $residente->getKey())
            ->where('fecha_hora', '<=', $corte)->with(['instrumento', 'evaluador', 'atencion', 'respuestas'])->orderBy('fecha_hora')->orderBy('cod_aplicacion')->get();
        $respuestas = $aplicaciones->flatMap(fn ($a) => $a->respuestas);
        $preguntas = PreguntaInstrumento::query()->whereIn('cod_pregunta', $respuestas->pluck('cod_pregunta')->unique())->get()->keyBy('cod_pregunta');
        $opciones = OpcionPregunta::query()->whereIn('cod_opcion', $respuestas->pluck('cod_opcion')->filter()->unique())->get()->keyBy('cod_opcion');
        $perfiles = config('sistema_experto.perfiles_instrumentales', []);
        $capturas = [];
        foreach ($aplicaciones as $a) {
            $instrumento = $a->instrumento;
            $autor = $a->evaluador;
            $motivos = [];
            if ($a->estado !== 'COMPLETA') {
                $motivos[] = 'APLICACION_NO_COMPLETA';
            }
            if (! $instrumento || $instrumento->getKey() !== $a->cod_instrumento || ! $autor || $autor->getKey() !== $a->cod_personal
                || ($a->cod_atencion !== null && $a->atencion?->cod_residente !== $residente->getKey())) {
                $motivos[] = 'PROCEDENCIA_INCOMPATIBLE';
            }
            $brutos = [];
            foreach ($a->respuestas->sortBy('cod_respuesta', SORT_STRING) as $r) {
                $q = $preguntas->get($r->cod_pregunta);
                $o = $r->cod_opcion === null ? null : $opciones->get($r->cod_opcion);
                $coherente = $r->cod_aplicacion === $a->getKey() && $q?->cod_instrumento === $a->cod_instrumento
                    && ($r->cod_opcion === null || $o?->cod_pregunta === $r->cod_pregunta);
                $brutos[] = ['cod_respuesta' => $r->getKey(), 'cod_aplicacion' => $r->cod_aplicacion, 'cod_pregunta' => $r->cod_pregunta,
                    'cod_opcion' => $r->cod_opcion, 'valor_numero' => $r->getRawOriginal('valor_numero'), 'valor_logico' => $r->getRawOriginal('valor_logico'),
                    'valor_texto' => $r->getRawOriginal('valor_texto'), 'puntaje' => $r->getRawOriginal('puntaje'),
                    'observacion' => $r->getRawOriginal('observacion'), 'opcion_actual' => $o?->getRawOriginal(), 'pregunta_actual' => $q?->getRawOriginal(),
                    'codigo_pregunta' => $q?->codigo, 'pregunta' => $q?->enunciado, 'dominio_original' => $q?->dominio, 'tipo_respuesta' => $q?->tipo_respuesta,
                    'estado_pregunta_actual' => $q?->estado, 'coherente' => $coherente];
                if (! $coherente) {
                    $motivos[] = 'RESPUESTA_CON_REFERENCIAS_INCOMPATIBLES';
                }
            }
            $perfilesAplicables = array_values(array_filter($perfiles, fn ($p) => ($p['cod_instrumento'] ?? null) === $a->cod_instrumento
                && ($p['version_instrumento'] ?? null) === $instrumento?->version));
            if (count($perfilesAplicables) > 1) {
                throw new DomainException('Hay más de un perfil para la misma identidad instrumental.');
            }
            if ($perfilesAplicables === []) {
                $motivos[] = 'SIN_PERFIL_EXPLICITO';
            }
            $salida = ['cod_aplicacion' => $a->getKey(), 'cod_residente' => $a->cod_residente, 'cod_instrumento' => $a->cod_instrumento,
                'codigo_instrumento' => $instrumento?->codigo, 'nombre_instrumento' => $instrumento?->nombre, 'version_instrumento' => $instrumento?->version,
                'cod_personal' => $a->cod_personal, 'autor' => trim(implode(' ', [$autor?->nombres, $autor?->apellido_paterno, $autor?->apellido_materno])),
                'fecha_hora' => $a->fecha_hora->format('Y-m-d\TH:i:s.uP'), 'estado_original' => $a->estado,
                'aplicacion_actual' => $a->getRawOriginal(), 'instrumento_actual' => $instrumento?->getRawOriginal(),
                'respuestas_originales' => $brutos, 'motivos' => array_values(array_unique($motivos)), 'extraccion' => null];
            if ($motivos === []) {
                $nacimiento = $residente->fecha_nacimiento;
                $fecha = CarbonImmutable::instance($a->fecha_hora);
                $edad = $nacimiento && $nacimiento <= $fecha ? (int) $nacimiento->diffInYears($fecha) : null;
                $salida['extraccion'] = (new ExtractorCapturaInstrumental)->extraer($perfilesAplicables[0], $salida, $brutos, $edad);
                $salida['fuente_edad'] = ['tabla' => 'residentes', 'campo' => 'fecha_nacimiento', 'registro' => $residente->getKey(),
                    'fecha_nacimiento' => $nacimiento?->format('Y-m-d'), 'fecha_aplicacion' => $salida['fecha_hora']];
            }
            $capturas[] = $salida;
        }

        return ['disponible' => true, 'fecha_corte' => $corte->format('Y-m-d\TH:i:s.uP'), 'capturas' => $capturas, 'persistido' => false];
    }
}
