<?php

namespace MotorExperto\adaptadores\entrada;

use DomainException;
use MotorExperto\dominio\conocimiento\MapeadorSemantico;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\inferencia\MemoriaTrabajo;
use MotorExperto\dominio\objetos_valor\EvidenciaExperta;
use MotorExperto\dominio\objetos_valor\FuenteBrutaCOGMEM;

final class AdaptadorHechosCOGMEM
{
    public const VARIABLES_POR_CAMPO = [
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
}
