<?php

namespace App\Backend\Modulos\SistemaExperto\Adaptadores;

use App\Backend\Modulos\SistemaExperto\Conocimiento\MapeadorSemantico;
use App\Backend\Modulos\SistemaExperto\Conocimiento\PaqueteConocimiento;
use App\Backend\Modulos\SistemaExperto\DTO\EvidenciaExperta;
use App\Backend\Modulos\SistemaExperto\DTO\FuenteBrutaCOGMEM;
use App\Backend\Modulos\SistemaExperto\MemoriaTrabajo\MemoriaTrabajo;
use App\Models\AplicacionInstrumento;
use App\Models\ControlCognitivo;
use App\Models\OpcionPregunta;
use App\Models\PreguntaInstrumento;
use DateTimeImmutable;
use DomainException;

final class AdaptadorFuentesCOGMEM
{
    private const VARIABLES_POR_CAMPO = [
        'memoria_reciente' => 'VAR-MEM-OBS-01', 'memoria_remota' => 'VAR-MEM-OBS-02',
        'repite_preguntas' => 'VAR-MEM-COR-01', 'olvida_indicaciones' => 'VAR-COG-MULTI-01',
        'cambio_cognitivo' => 'VAR-LON-META-01',
    ];

    public function adaptar(PaqueteConocimiento $p, MemoriaTrabajo $memoria, FuenteBrutaCOGMEM $f, string $mapeo): EvidenciaExperta
    {
        $e = (new MapeadorSemantico)->mapear($p, $memoria->contexto, $f, $mapeo);
        $variable = $p->fila('variables_expertas', $e->variable);
        $codigo = $p->red->nodo($variable['cod_nodo_semantico'])['codigo_semantico'];
        $motivos = $e->motivos;
        if ($f->tabla === 'controles_cognitivos') {
            if (! isset(self::VARIABLES_POR_CAMPO[$f->campo]) || self::VARIABLES_POR_CAMPO[$f->campo] !== $codigo) {
                $motivos[] = 'CAMPO_SIN_ATRIBUCION_AUTORIZADA';
            }
            if (in_array($f->campo, ['repite_preguntas', 'olvida_indicaciones'], true) && $f->valorTipado !== true) {
                $motivos[] = 'AUSENCIA_DE_MANIFESTACION_NO_DEMUESTRA_PRESERVACION';
            }
            if ($f->campo === 'repite_preguntas' && ($f->contexto['contexto_mnesico_verificado'] ?? false) !== true) {
                $motivos[] = 'CONTEXTO_CORROBORATIVO_NO_VERIFICADO';
            }
            if ($f->campo === 'olvida_indicaciones' && (($f->contexto['recepcion_comprension_verificada'] ?? false) !== true
                || ($f->contexto['dificultad_retencion_recuperacion_verificada'] ?? false) !== true)) {
                $motivos[] = 'ATRIBUCION_MNESICA_MULTIDOMINIO_NO_VERIFICADA';
            }
            if ($f->campo === 'cambio_cognitivo') {
                $propietario = $variable['cod_nodo_propietario_primario'];
                if ($propietario === null || $p->red->nodo($propietario)['codigo_semantico'] !== 'BL-LON') {
                    throw new DomainException('La metaevidencia de cambio pertenece a BL-LON.');
                }
                if (($f->contexto['captura_cambio_explicita_verificada'] ?? false) !== true) {
                    $motivos[] = 'SEMANTICA_DE_CAPTURA_DE_CAMBIO_NO_VERIFICADA';
                }
            }
        } elseif ($f->tabla === 'aplicaciones_instrumento') {
            if ($codigo !== 'VAR-MEM-INST-01' || ($f->contexto['componente_instrumental_verificado'] ?? false) !== true) {
                $motivos[] = 'COMPONENTE_INSTRUMENTAL_NO_VERIFICADO';
            }
        } else {
            throw new DomainException('Fuente fuera del adaptador COG-MEM V1.');
        }
        if ($motivos !== $e->motivos) {
            $e = new EvidenciaExperta($e->id, $e->ejecucion, $e->fuente, $e->mapeoVariable, $e->mapeoValor,
                $e->variable, $e->valor, $e->representacion, 'NO_ADMISIBLE', array_values(array_unique($motivos)));
        }

        return $memoria->incorporar($e);
    }

    /** Lectura de un registro ya autorizado por el llamador; requiere relaciones cargadas. */
    public function extraerControl(ControlCognitivo $control, PaqueteConocimiento $p, MemoriaTrabajo $m, array $contexto = []): array
    {
        if (! $control->exists || $control->cod_residente !== $m->contexto->residente || ! $control->relationLoaded('personal')) {
            throw new DomainException('Control fuera de ámbito o procedencia sin cargar.');
        }
        $autor = $control->getRelation('personal');
        $verificada = $autor !== null && $autor->exists && $autor->cod_personal === $control->cod_personal;
        $emitidas = [];
        foreach ($p->tabla('mapeos_variables_fuente') as $map) {
            $fuente = $p->fila('fuentes_datos_expertas', $map['cod_fuente_dato_experta']);
            if ($fuente['tabla_raiz'] !== 'controles_cognitivos' || $map['estado'] !== 'ACTIVO') {
                continue;
            }
            if (! isset(self::VARIABLES_POR_CAMPO[$map['campo_valor'] ?? ''])) {
                continue;
            }
            // Campo/PK/procedencia declarados se contrastan con el esquema real conocido.
            if ($fuente['campo_pk_raiz'] !== 'cod_control_cognitivo' || $fuente['campo_residente'] !== 'cod_residente'
                || $fuente['campo_temporal'] !== 'fecha_hora' || $fuente['campo_personal'] !== 'cod_personal') {
                throw new DomainException('Metadatos operacionales incompatibles con controles_cognitivos.');
            }
            $campo = $map['campo_valor'];
            $fecha = $control->fecha_hora ? new DateTimeImmutable($control->fecha_hora->format(DATE_ATOM)) : null;
            $f = new FuenteBrutaCOGMEM($fuente['cod_fuente_dato_experta'], 'controles_cognitivos', $campo,
                $control->cod_control_cognitivo, $control->cod_residente, $fecha, $control->getRawOriginal($campo), $control->getAttribute($campo),
                $control->cod_personal, $verificada, $control->estado === 'VIGENTE', $control->cod_atencion, $control->cod_jornada, $contexto);
            $emitidas[] = $this->adaptar($p, $m, $f, $map['cod_mapeo_variable_fuente']);
        }

        return $emitidas;
    }

    public function extraerAplicacion(AplicacionInstrumento $a, PaqueteConocimiento $p, MemoriaTrabajo $m, array $componente, array $contexto = []): array
    {
        if (! $a->exists || $a->cod_residente !== $m->contexto->residente
            || ! $a->relationLoaded('instrumento') || ! $a->relationLoaded('evaluador') || ! $a->relationLoaded('respuestas')) {
            throw new DomainException('Aplicación fuera de ámbito o procedencia instrumental sin cargar.');
        }
        $instrumento = $a->getRelation('instrumento');
        $autor = $a->getRelation('evaluador');
        if ($instrumento === null || ! $instrumento->exists) {
            throw new DomainException('Instrumento inexistente.');
        }
        $respuestas = $a->getRelation('respuestas')->sortBy('cod_pregunta', SORT_STRING)->values();
        $ids = array_unique([...$respuestas->pluck('cod_pregunta')->all(), ...array_keys($componente['preguntas_requeridas'])]);
        $preguntas = PreguntaInstrumento::query()->whereIn('cod_pregunta', $ids)->get()->keyBy('cod_pregunta')->map(fn ($q) => $q->getAttributes())->all();
        $opciones = OpcionPregunta::query()->whereIn('cod_opcion', $respuestas->pluck('cod_opcion')->filter()->all())->get()->keyBy('cod_opcion')->map(fn ($q) => $q->getAttributes())->all();
        $inspeccion = (new InspectorComponenteInstrumental)->inspeccionar($a->getAttributes(), $instrumento->getAttributes(), $preguntas, $opciones,
            $respuestas->map(fn ($r) => [...$r->getAttributes(), 'valor_logico' => $r->valor_logico])->all(), $componente);
        if (! $inspeccion['verificado']) {
            return ['inspeccion' => $inspeccion, 'evidencia' => null];
        }
        $map = $p->fila('mapeos_variables_fuente', $componente['mapeo_variable']);
        $fuente = $p->fila('fuentes_datos_expertas', $map['cod_fuente_dato_experta']);
        if ($fuente['tabla_raiz'] !== 'aplicaciones_instrumento' || $fuente['campo_pk_raiz'] !== 'cod_aplicacion'
            || $fuente['campo_residente'] !== 'cod_residente' || $fuente['campo_temporal'] !== 'fecha_hora'
            || $fuente['campo_personal'] !== 'cod_personal' || $map['campo_valor'] !== null || $map['clave_selector'] !== $componente['componente']) {
            throw new DomainException('El componente instrumental no coincide con la fuente/selector declarados.');
        }
        $original = $respuestas->filter(fn ($r) => array_key_exists($r->cod_pregunta, $componente['preguntas_requeridas']))
            ->map(fn ($r) => ['respuesta' => $r->cod_respuesta, 'pregunta' => $r->cod_pregunta,
                'valor_original' => $r->getRawOriginal($componente['preguntas_requeridas'][$r->cod_pregunta]['campo'])])->values()->all();
        $f = new FuenteBrutaCOGMEM($fuente['cod_fuente_dato_experta'], 'aplicaciones_instrumento', $componente['componente'], $a->cod_aplicacion,
            $a->cod_residente, $a->fecha_hora ? new DateTimeImmutable($a->fecha_hora->format(DATE_ATOM)) : null, $original, $inspeccion['valor'],
            $a->cod_personal, $autor !== null && $autor->exists && $autor->cod_personal === $a->cod_personal, $a->estado === 'COMPLETA',
            $a->cod_atencion, null, [...$contexto, 'componente_instrumental_verificado' => true, 'instrumental' => $inspeccion['procedencia']]);

        return ['inspeccion' => $inspeccion, 'evidencia' => $this->adaptar($p, $m, $f, $map['cod_mapeo_variable_fuente'])];
    }
}
