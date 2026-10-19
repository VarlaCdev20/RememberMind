<?php

declare(strict_types=1);

namespace MotorExperto\dominio\conocimiento;

use MotorExperto\dominio\conocimiento\versiones\PaqueteCognitivoInstitucional;
use MotorExperto\dominio\objetos_valor\EvidenciaExperta;

/** Antecedentes G09 del contrato A–D; una observación nunca acredita un predicado instrumental. */
final class AdmisibilidadCognitivaInstitucional
{
    public function validar(PaqueteConocimiento $p, EvidenciaExperta $e): EvidenciaExperta
    {
        $v = $p->fila('variables_expertas', $e->variable);
        $codigo = $p->red->nodo($v['cod_nodo_semantico'])['codigo_semantico'];
        if (! PaqueteCognitivoInstitucional::reconoce($p)) {
            return $e;
        }
        $memoria = $codigo === 'VAR-MEM-INST-01';
        if (! $memoria && ! preg_match('/^VAR-((?:ATE|EJE|LEN|VIS)-S0[1-5])-(C[123])$/D', $codigo, $coincidencias)) {
            return $e;
        }
        $motivos = $e->motivos;
        $c = $e->fuente->contexto;
        if (($c['instrumento']['tipo'] ?? null) === 'OBSERVACIONAL') {
            $motivos[] = 'OBSERVACION_NO_ACREDITA_PREDICADO_G09';
        }
        $componente = $c['componente_declarado'] ?? null;
        if ($memoria ? ! in_array($componente, ['MEM-S01', 'MEM-S02', 'MEM-S03', 'MEM-S04'], true) : $componente !== $coincidencias[1]) {
            $motivos[] = 'ATRIBUCION_DE_TAREA_NO_VINCULADA';
        }
        $perfil = $c['perfil_interpretativo'] ?? null;
        $predicado = $memoria ? 'MEMORIA' : $coincidencias[2];
        $perfilValido = is_array($perfil);
        foreach (['codigo', 'version', 'metodo', 'fuente'] as $campo) {
            $perfilValido = $perfilValido && is_string($perfil[$campo] ?? null) && trim($perfil[$campo]) !== '';
        }
        $perfilValido = $perfilValido && ($perfil['componente'] ?? null) === $componente
            && ($perfil['instrumento']['codigo'] ?? null) === ($c['instrumento']['codigo'] ?? null)
            && ($perfil['instrumento']['version'] ?? null) === ($c['instrumento']['version'] ?? null)
            && is_string($perfil['instrumento']['codigo'] ?? null) && is_string($perfil['instrumento']['version'] ?? null)
            && in_array($predicado, is_array($perfil['predicados'] ?? null) ? $perfil['predicados'] : [], true);
        if (! $perfilValido) {
            $motivos[] = 'SIN_PERFIL_INTERPRETATIVO_VERSIONADO';
        }
        $requeridas = is_array($perfil) ? ($perfil['mediciones_requeridas'] ?? null) : null;
        $medidasValidas = is_array($requeridas) && $requeridas !== [];
        foreach (is_array($requeridas) ? $requeridas : [] as $nombre => $selector) {
            $medida = $c['mediciones_interpretacion'][$nombre] ?? [];
            $valor = $medida['valor_original'] ?? null;
            $medidasValidas = $medidasValidas && is_array($selector) && is_string($selector['cod_pregunta'] ?? null)
                && ($selector['campo'] ?? null) === 'valor_numero' && ($medida['campo'] ?? null) === 'valor_numero'
                && ($medida['cod_pregunta'] ?? null) === $selector['cod_pregunta']
                && ($medida['cod_aplicacion'] ?? null) === $e->fuente->registro
                && is_string($medida['cod_respuesta'] ?? null) && $medida['cod_respuesta'] !== ''
                && (is_int($valor) || is_float($valor) || (is_string($valor) && is_numeric($valor))) && is_finite((float) $valor);
        }
        if (! $medidasValidas) {
            $motivos[] = 'SIN_MEDICIONES_ORIGINALES_DEL_PERFIL';
        }
        if ($motivos !== $e->motivos) {
            return new EvidenciaExperta($e->id, $e->ejecucion, $e->fuente, $e->mapeoVariable, $e->mapeoValor,
                $e->variable, $e->valor, $e->representacion, 'NO_ADMISIBLE', array_values(array_unique($motivos)));
        }

        return $e;
    }
}
