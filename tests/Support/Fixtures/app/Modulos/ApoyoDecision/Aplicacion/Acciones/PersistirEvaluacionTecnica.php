<?php

namespace App\Modulos\ApoyoDecision\Aplicacion\Acciones;

use App\Models\AplicacionInstrumento;
use App\Models\ControlCognitivo;
use App\Models\Instrumento;
use App\Models\OpcionPregunta;
use App\Models\Personal;
use App\Models\PreguntaInstrumento;
use App\Models\Residente;
use App\Models\RespuestaInstrumento;
use App\Models\User;
use App\Models\VersionModeloExperto;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\ExtraccionFuentesCOGMEM;
use App\Modulos\ApoyoDecision\Dominio\Reglas\BaseTecnicaDesechable;
use DomainException;
use Illuminate\Support\Facades\DB;
use MotorExperto\aplicacion\puertos\EvaluadorExperto;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\inferencia\MemoriaTrabajo;

/** Persistencia real D-133 en BDD desechable; no es una entrada clínica institucional. */
final class PersistirEvaluacionTecnica
{
    public function __construct(private readonly ExtraccionFuentesCOGMEM $fuentes, private readonly EvaluadorExperto $motor) {}

    public function ejecutar(PaqueteConocimiento $p, MemoriaTrabajo $m, string $criterio, array $contexto, User $autor, array $componentesInstrumentales = []): string
    {
        $c = DB::connection();
        $desechable = BaseTecnicaDesechable::permite($c->getDriverName(), $c->getDatabaseName(), $c->getConfig('username'), $c->getConfig('host'));
        if (! app()->environment('testing') || ! $desechable || ! $p->soloPruebasTecnicas) {
            throw new DomainException('La persistencia técnica solo está habilitada en la base desechable de pruebas.');
        }

        return DB::transaction(function () use ($p, $m, $criterio, $contexto, $autor, $componentesInstrumentales): string {
            $inicio = now()->format('Y-m-d H:i:s');
            $usuario = User::query()->lockForUpdate()->find($autor->cod_usuario);
            $personal = Personal::query()->where('cod_usuario', $autor->cod_usuario)->lockForUpdate()->first();
            if ($usuario === null || $usuario->estado !== 'ACTIVO' || $personal === null || $personal->estado !== 'ACTIVO') {
                throw new DomainException('El solicitante técnico requiere cuenta y personal identificables y activos.');
            }
            $residente = Residente::query()->lockForUpdate()->find($m->contexto->residente);
            $version = VersionModeloExperto::query()->lockForUpdate()->find($p->version);
            if ($residente === null || $version === null) {
                throw new DomainException('Residente o versión inexistentes.');
            }
            // Serializa reintentos del mismo residente/versión, también en PostgreSQL.
            $id = $m->contexto->evaluacion;
            if (! preg_match('/^COD_EEX_[0-9]{6,12}$/D', $id)) {
                throw new DomainException('Reserva una identidad correlativa de evaluación antes de construir el contexto.');
            }
            if (DB::table('evaluaciones_expertas')->where('cod_evaluacion_experta', $id)->exists()) {
                throw new DomainException('La evaluación ya fue conservada; una reejecución necesita identidad nueva.');
            }
            app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->registrarExplicito('evaluaciones_expertas', $id);
            $this->validarFuentesYConocimiento($p, $m, $componentesInstrumentales);
            $resultado = $this->motor->evaluarCOGMEM($p, $m, $criterio, $contexto);
            $fecha = now()->format('Y-m-d H:i:s');
            DB::table('evaluaciones_expertas')->insert([
                'cod_evaluacion_experta' => $id, 'cod_residente' => $residente->cod_residente, 'cod_version_modelo' => $p->version,
                'cod_personal_solicitante' => $personal->cod_personal, 'origen_activacion' => 'PRUEBA_TECNICA',
                'fecha_hora_inicio' => $inicio, 'fecha_hora_fin' => $fecha, 'estado_ejecucion' => 'PRUEBA_FINALIZADA',
                'motivo_activacion' => 'Fixture técnico sin activación clínica.',
            ]);
            $identidadesEvidencia = [];
            foreach ($m->todas() as $e) {
                $identidadesEvidencia[$e->id] = app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('evidencias_evaluacion');
                DB::table('evidencias_evaluacion')->insert(['cod_evidencia_evaluacion' => $identidadesEvidencia[$e->id], 'cod_evaluacion_experta' => $id,
                    'cod_mapeo_variable_fuente' => $e->mapeoVariable, 'cod_registro_fuente' => $e->fuente->registro,
                    'estado_representacion' => $e->representacion, 'cod_valor_semantico' => $e->valor,
                    'estado_admisibilidad' => $e->admisibilidad, 'motivo_admisibilidad' => implode('; ', $e->motivos) ?: null,
                    'fecha_hora_incorporacion' => $fecha]);
            }
            foreach ($m->relaciones() as $r) {
                DB::table('relaciones_evidencias_evaluacion')->insert(['cod_relacion_evidencia' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('relaciones_evidencias_evaluacion'),
                    'cod_evidencia_origen' => $identidadesEvidencia[$r['origen']], 'cod_evidencia_destino' => $identidadesEvidencia[$r['destino']], 'tipo_relacion' => $r['tipo'],
                    'estado' => 'PRUEBA_TECNICA', 'justificacion' => $r['justificacion'], 'fecha_hora_creacion' => $fecha]);
            }
            $ec = app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('evaluacion_criterios');
            $gate = $resultado['evaluabilidad'];
            DB::table('evaluacion_criterios')->insert(['cod_evaluacion_criterio' => $ec, 'cod_evaluacion_experta' => $id, 'cod_nodo_criterio' => $criterio,
                'estado_evaluabilidad' => $gate['estado'], 'codigo_modificador_interpretacion' => $gate['modificador'],
                'motivo_evaluabilidad' => implode('; ', $gate['contexto']['faltantes']) ?: null, 'fecha_hora_determinacion' => $fecha]);
            foreach ($m->participaciones()[$criterio] ?? [] as $evidencia => $rol) {
                DB::table('evidencias_criterio_evaluacion')->insert(['cod_evidencia_criterio' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('evidencias_criterio_evaluacion'),
                    'cod_evaluacion_criterio' => $ec, 'cod_evidencia_evaluacion' => $identidadesEvidencia[$evidencia], 'rol_en_criterio' => $rol,
                    'estado_participacion' => 'PRUEBA_TECNICA', 'fecha_hora_vinculacion' => $fecha]);
            }
            if ($resultado['resultado'] !== null) {
                \App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\BloquesExpertos::insertar('resultados_criterio', ['cod_resultado_criterio' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('resultados_criterio'), 'cod_evaluacion_criterio' => $ec,
                    'cod_valor_semantico' => $resultado['resultado']['valor'], 'fecha_hora_determinacion' => $fecha]);
            }
            $traza = app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('trazas_inferencia');
            \App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\BloquesExpertos::insertar('trazas_inferencia', ['cod_traza_inferencia' => $traza, 'cod_evaluacion_criterio' => $ec,
                'estado_inferencia' => 'PRUEBA_FINALIZADA', 'fecha_hora_inicio' => $inicio, 'fecha_hora_fin' => $fecha]);
            foreach ($resultado['traza']['reglas'] as $r) {
                $er = app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('evaluaciones_reglas');
                DB::table('evaluaciones_reglas')->insert(['cod_evaluacion_regla' => $er, 'cod_traza_inferencia' => $traza,
                    'cod_regla_experta' => $r['regla'], 'estado_regla' => $r['estado'], 'fecha_hora_evaluacion' => $fecha]);
                foreach ($r['condiciones'] as $cond) {
                    $ece = app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('evaluaciones_condiciones_regla');
                    DB::table('evaluaciones_condiciones_regla')->insert(['cod_evaluacion_condicion' => $ece, 'cod_evaluacion_regla' => $er,
                        'cod_condicion_regla' => $cond['condicion'], 'estado_condicion' => $cond['estado'], 'fecha_hora_evaluacion' => $fecha]);
                    foreach ($cond['soportes'] as $evidencia) {
                        DB::table('evidencias_soporte_condicion')->insert(['cod_evidencia_soporte_condicion' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('evidencias_soporte_condicion'),
                            'cod_evaluacion_condicion' => $ece, 'cod_evidencia_evaluacion' => $identidadesEvidencia[$evidencia]]);
                    }
                }
            }

            return $id;
        });
    }


    private function validarFuentesYConocimiento(PaqueteConocimiento $p, MemoriaTrabajo $m, array $componentesInstrumentales): void
    {
        foreach (PaqueteConocimiento::CLAVES as $tabla => $pk) {
            foreach ($p->tabla($tabla) as $fila) {
                $guardada = \App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\BloquesExpertos::consulta($tabla)->where($pk, $fila[$pk])->lockForUpdate()->first();
                if ($guardada === null) {
                    throw new DomainException('Conocimiento no persistido: '.$tabla);
                }
                foreach ($fila as $campo => $valor) {
                    if (! property_exists($guardada, $campo) || $valor !== $guardada->$campo) {
                        throw new DomainException('La instantánea no coincide con el conocimiento persistido: '.$tabla);
                    }
                }
            }
        }
        $reconstruida = new MemoriaTrabajo($m->contexto);
        $adaptador = $this->fuentes;
        foreach ($m->todas() as $e) {
            $fuente = $p->fila('fuentes_datos_expertas', $e->fuente->fuente);
            if (! in_array($fuente['tabla_raiz'], ['controles_cognitivos', 'aplicaciones_instrumento'], true)) {
                throw new DomainException('Fuente no autorizada.');
            }
            $raiz = $fuente['tabla_raiz'];
            $pk = $raiz === 'controles_cognitivos' ? 'cod_control_cognitivo' : 'cod_aplicacion';
            if ($fuente['campo_pk_raiz'] !== $pk || $fuente['campo_residente'] !== 'cod_residente'
                || $fuente['campo_temporal'] !== 'fecha_hora' || $fuente['campo_personal'] !== 'cod_personal') {
                throw new DomainException('Metadatos de fuente no autorizados.');
            }
            $registro = DB::table($raiz)->where($pk, $e->fuente->registro)->lockForUpdate()->first();
            if ($registro === null || $registro->cod_residente !== $m->contexto->residente || $registro->cod_personal !== $e->fuente->personal) {
                throw new DomainException('Registro fuente inexistente o ajeno a esta ejecución.');
            }
            $fecha = new \DateTimeImmutable($registro->fecha_hora, $m->contexto->fechaCorte->getTimezone());
            if ($e->fuente->fecha === null || $fecha != $e->fuente->fecha || $fecha > $m->contexto->fechaCorte) {
                throw new DomainException('Fecha fuente incompatible con la instantánea.');
            }
            $personalFuente = Personal::query()->lockForUpdate()->find($registro->cod_personal);
            if ($raiz === 'controles_cognitivos') {
                $control = ControlCognitivo::query()->lockForUpdate()->findOrFail($e->fuente->registro);
                $control->setRelation('personal', $personalFuente);
                $adaptador->extraerControl($control, $p, $reconstruida, $e->fuente->contexto);
            } else {
                $componente = $componentesInstrumentales[$e->mapeoVariable] ?? null;
                if (! is_array($componente) || ($componente['mapeo_variable'] ?? null) !== $e->mapeoVariable) {
                    throw new DomainException('Falta el contrato explícito del componente instrumental.');
                }
                $aplicacion = AplicacionInstrumento::query()->lockForUpdate()->findOrFail($e->fuente->registro);
                $instrumento = Instrumento::query()->lockForUpdate()->find($aplicacion->cod_instrumento);
                $respuestas = RespuestaInstrumento::query()->where('cod_aplicacion', $aplicacion->cod_aplicacion)->lockForUpdate()->get();
                PreguntaInstrumento::query()->where('cod_instrumento', $aplicacion->cod_instrumento)->lockForUpdate()->get();
                OpcionPregunta::query()->whereIn('cod_opcion', $respuestas->pluck('cod_opcion')->filter()->all())->lockForUpdate()->get();
                $aplicacion->setRelation('instrumento', $instrumento);
                $aplicacion->setRelation('evaluador', $personalFuente);
                $aplicacion->setRelation('respuestas', $respuestas);
                $adaptador->extraerAplicacion($aplicacion, $p, $reconstruida, $componente, $e->fuente->contexto);
            }
            $relectura = collect($reconstruida->todas())->first(fn ($actual) => $actual->id === $e->id);
            if ($relectura === null || serialize($relectura) !== serialize($e)) {
                throw new DomainException('La instantánea de evidencia no coincide con la fuente y el mapeo reconstruidos.');
            }
        }
    }
}
