<?php

declare(strict_types=1);

namespace MotorExperto\adaptadores\entrada;

use DateTimeImmutable;
use DomainException;
use MotorExperto\dominio\conocimiento\AdmisibilidadFuenteCOGMEM;
use MotorExperto\dominio\conocimiento\MapeadorSemantico;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\inferencia\MemoriaTrabajo;
use MotorExperto\dominio\objetos_valor\ContextoEjecucion;
use MotorExperto\dominio\objetos_valor\FuenteBrutaCOGMEM;

/** Traduce respuestas originales identificadas a hechos; nunca usa z, clasificación ni texto libre como diagnóstico. */
final class ConstructorMemoriaInstrumental
{
    public function construir(PaqueteConocimiento $p, ContextoEjecucion $contexto, array $capturas, array $vinculos): array
    {
        if (($capturas['disponible'] ?? null) !== true || $contexto->version !== $p->version) {
            throw new DomainException('Se requieren fuentes autorizadas y la misma versión de conocimiento.');
        }
        $m = new MemoriaTrabajo($contexto);
        $excluidas = [];
        foreach ($vinculos as $mapeo => $vinculo) {
            if (! is_string($mapeo) || ! is_array($vinculo) || array_diff(array_keys($vinculo), ['cod_instrumento', 'codigo_instrumento', 'version_instrumento', 'cod_pregunta', 'campo'])) {
                throw new DomainException('Vínculo instrumental fuera del contrato.');
            }
            foreach (['cod_instrumento', 'codigo_instrumento', 'version_instrumento', 'cod_pregunta', 'campo'] as $campo) {
                if (! is_string($vinculo[$campo] ?? null) || $vinculo[$campo] === '') {
                    throw new DomainException('El vínculo requiere identidad, versión, pregunta y campo originales.');
                }
            }
            $mapa = $p->fila('mapeos_variables_fuente', $mapeo);
            $fuente = $p->fila('fuentes_datos_expertas', $mapa['cod_fuente_dato_experta']);
            foreach (['campo_pk_raiz' => 'cod_aplicacion', 'campo_residente' => 'cod_residente', 'campo_temporal' => 'fecha_hora', 'campo_personal' => 'cod_personal'] as $campoFuente => $esperado) {
                if (($fuente[$campoFuente] ?? null) !== $esperado) {
                    throw new DomainException('La fuente no declara las columnas originales de aplicación, residente, fecha y autor.');
                }
            }
            if ($fuente['tabla_raiz'] !== 'aplicaciones_instrumento'
                || ($p->contratosExtraccion[$mapa['tipo_extraccion']] ?? null) !== 'SELECTOR_COMPONENTE'
                || $mapa['clave_selector'] !== $vinculo['cod_pregunta']
                || ! in_array($vinculo['campo'], ['valor_logico', 'valor_texto'], true)) {
                throw new DomainException('El mapeo exige selector de pregunta y un literal lógico/textual original; no admite umbrales implícitos.');
            }
            foreach ($capturas['capturas'] as $a) {
                if ($a['cod_residente'] !== $contexto->residente) {
                    throw new DomainException('La captura pertenece a otro residente.');
                }
                if ($a['cod_instrumento'] !== $vinculo['cod_instrumento'] || $a['codigo_instrumento'] !== $vinculo['codigo_instrumento']
                    || $a['version_instrumento'] !== $vinculo['version_instrumento']) {
                    continue;
                }
                if (! is_int($a['edad_en_aplicacion'] ?? null) || $a['edad_en_aplicacion'] < 60) {
                    $excluidas[] = ['aplicacion' => $a['cod_aplicacion'], 'mapeo' => $mapeo,
                        'motivos' => ['FUERA_DE_COHORTE_60_O_MAS_O_EDAD_DESCONOCIDA']];

                    continue;
                }
                // La falta de perfil numérico no invalida un literal original. Ningún otro rechazo se omite.
                $motivos = array_values(array_diff($a['motivos'], ['SIN_PERFIL_EXPLICITO']));
                if ($motivos !== []) {
                    $excluidas[] = ['aplicacion' => $a['cod_aplicacion'], 'mapeo' => $mapeo, 'motivos' => $motivos];

                    continue;
                }
                $filas = array_values(array_filter($a['respuestas_originales'], static fn ($r) => $r['cod_pregunta'] === $vinculo['cod_pregunta']));
                if (count($filas) > 1) {
                    throw new DomainException('Más de una respuesta original para la misma pregunta y aplicación.');
                }
                $r = $filas[0] ?? null;
                $pregunta = $r['pregunta_actual'] ?? $a['preguntas_declarativas_originales'][$vinculo['cod_pregunta']] ?? null;
                if ($pregunta !== null && ($pregunta['cod_instrumento'] ?? null) !== $a['cod_instrumento']) {
                    throw new DomainException('La pregunta declarada pertenece a otro instrumento.');
                }
                $campo = $vinculo['campo'];
                $original = $r[$campo] ?? null;
                $tipoCorrecto = $r === null || ($campo === 'valor_logico' ? in_array($r['tipo_respuesta'], ['LOGICO', 'BOOLEANO'], true)
                    : in_array($r['tipo_respuesta'], ['TEXTO', 'TEXTO_CORTO', 'TEXTO_LARGO'], true));
                $otros = array_diff(['cod_opcion', 'valor_numero', 'valor_logico', 'valor_texto'], [$campo]);
                $ambiguo = $r !== null && count(array_filter($otros, static fn ($c) => ($r[$c] ?? null) !== null && $r[$c] !== '')) > 0;
                if (! $tipoCorrecto || $ambiguo || ($r !== null && (! $r['coherente'] || $r['cod_aplicacion'] !== $a['cod_aplicacion']))) {
                    throw new DomainException('Respuesta incompatible con su pregunta, campo o aplicación.');
                }
                $tipado = $campo === 'valor_logico' ? match (true) {
                    $original === true || $original === 1 || $original === '1' => true,
                    $original === false || $original === 0 || $original === '0' => false,
                    default => null,
                } : (is_string($original) ? $original : null);
                $variable = $p->fila('variables_expertas', $mapa['cod_variable_experta']);
                $esMemoriaInstrumental = $p->red->nodo($variable['cod_nodo_semantico'])['codigo_semantico'] === 'VAR-MEM-INST-01';
                $f = new FuenteBrutaCOGMEM($fuente['cod_fuente_dato_experta'], 'aplicaciones_instrumento', $vinculo['cod_pregunta'],
                    $a['cod_aplicacion'], $a['cod_residente'], new DateTimeImmutable($a['fecha_hora']), $original, $tipado,
                    $a['cod_personal'], true, $a['estado_original'] === 'COMPLETA', contexto: [
                        'unidad_soporte' => $a['cod_aplicacion'], 'cod_respuesta' => $r['cod_respuesta'] ?? null,
                        'componente_instrumental_verificado' => $esMemoriaInstrumental && $r !== null
                            && ($a['instrumento_actual']['tipo'] ?? null) !== 'OBSERVACIONAL',
                        'cod_pregunta' => $vinculo['cod_pregunta'], 'campo_original' => $campo,
                        'procedencia' => $a['procedencia'], 'instrumento' => $a['instrumento_actual'],
                        'pregunta_original' => $pregunta, 'fuente_edad' => $a['fuente_edad'],
                    ]);
                $e = (new MapeadorSemantico)->mapear($p, $contexto, $f, $mapeo);
                $criterios = [];
                foreach ($p->tabla('criterios_dominios_resultado') as $criterio) {
                    $id = $criterio['cod_nodo_criterio'];
                    if ($p->variableParticipa($e->variable, $id)) {
                        $criterios[] = $id;
                        if ($p->red->nodo($id)['codigo_semantico'] === 'COG-MEM') {
                            $e = (new AdmisibilidadFuenteCOGMEM)->validar($p, $e);
                        }
                    }
                }
                $m->incorporar($e);
                foreach ($criterios as $criterio) {
                    $m->vincular($criterio, $e->id, 'RESPUESTA_INSTRUMENTAL_ORIGINAL');
                }
            }
        }

        return ['memoria' => $m, 'fuentes_excluidas' => $excluidas];
    }
}
