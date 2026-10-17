<?php
declare(strict_types=1);

namespace MotorExperto\investigacion\g08;

use DomainException;

/**
 * Verifica la FORMA de una captura numérica sintética y compara los metadatos
 * básicos con el método de los artículos multicéntricos. Nunca calcula baremos.
 * Este auditor NO lee una persona real ni sustituye juicio profesional.
 */
final class AuditorCapturaYComparabilidadG08Investigacion
{
    public const VERSION = 'G08-CAPTURA-COMP-20261017-01';

    public function revisar(string $componente, string $tarea, array $captura): array
    {
        $contrato = ContratoCapturaCrudaG08Investigacion::todos()[$componente][$tarea]
            ?? throw new DomainException('La tarea no dispone de contrato G08 de captura cruda.');
        $motivos = [];
        if (($captura['uso'] ?? null) !== 'REVISION_DOCUMENTAL_TEST_ONLY'
            || !is_string($captura['residente_ficticio'] ?? null)
            || !str_starts_with($captura['residente_ficticio'],'SINTETICO-')
            || !is_string($captura['aplicacion_ficticia'] ?? null)
            || !str_starts_with($captura['aplicacion_ficticia'],'SIM-')) {
            $motivos[] = 'CAPTURA_FUERA_DE_SIMULACION';
        }
        if (($captura['instrumento'] ?? null)!==$contrato['instrumento']
            || ($captura['tarea'] ?? null)!==$tarea
            || ($captura['metrica'] ?? null)!==$contrato['metrica']) {
            $motivos[] = 'IDENTIDAD_INSTRUMENTO_TAREA_METRICA_DIVERGENTE';
        }
        if (($captura['unidad'] ?? null)!==$contrato['unidad_esperada']) {
            $motivos[] = 'UNIDAD_DE_MEDICION_INCOMPATIBLE';
        }
        if (!is_string($captura['edicion_declarada'] ?? null)
            || !str_starts_with($captura['edicion_declarada'],'SIM-')) {
            $motivos[] = 'EDICION_REAL_NO_VERIFICADA';
        }
        if (!is_string($captura['codigo_protocolo'] ?? null)
            || !str_starts_with($captura['codigo_protocolo'],'SIM-')) {
            $motivos[] = 'PROTOCOLO_REAL_NO_VERIFICADO';
        }
        if (($captura['tabla_bolivia'] ?? null)!==$contrato['tabla_bolivia']
            || ($captura['doi'] ?? null)!==$contrato['doi']) {
            $motivos[] = 'ANEXO_NORMATIVO_AJENO_A_LA_TAREA';
        }

        $crudos = $captura['datos_crudos'] ?? null;
        $esperados = $contrato['campos_crudos'];
        if (!is_array($crudos) || array_diff(array_keys($crudos),array_keys($esperados))
            || array_diff(array_keys($esperados),array_keys($crudos))) {
            $motivos[] = 'CAMPOS_CRUDOS_INCOMPLETOS_O_AJENOS';
        } else {
            foreach ($esperados as $campo=>$tipo) {
                $valor=$crudos[$campo];
                $correcto=match($tipo) {
                    'ENTERO_NO_NEGATIVO' => is_int($valor) && $valor>=0,
                    'NUMERO_NO_NEGATIVO' => (is_int($valor)||is_float($valor)) && is_finite((float)$valor) && $valor>=0,
                    'NUMERO_POSITIVO' => (is_int($valor)||is_float($valor)) && is_finite((float)$valor) && $valor>0,
                    default=>false,
                };
                if (!$correcto) $motivos[]='DATO_CRUDO_INVALIDO_'.$campo;
            }
            if ($tarea==='BTA_AUDIO'
                && is_int($crudos['forma_N_aciertos']) && is_int($crudos['forma_L_aciertos'])
                && ($crudos['forma_N_aciertos']>10 || $crudos['forma_L_aciertos']>10)) {
                $motivos[]='FORMAS_BTA_FUERA_DE_10_SERIES';
            }
            if ($tarea==='ROCF_COPIA'
                && is_int($crudos['elementos_revisados'])
                && ($crudos['elementos_revisados']!==18
                    || (!is_numeric($crudos['puntaje_copia']) || $crudos['puntaje_copia']>36))) {
                $motivos[]='ROCF_COPIA_DIFIERE_DE_18_ELEMENTOS_36_PUNTOS';
            }
        }

        $perfil=$captura['perfil_demografico_sintetico']??[];
        if (!is_array($perfil)) $perfil=[];
        $edad=$perfil['edad_anos']??null;
        if (!is_int($edad)) {
            $motivos[]='EDAD_NO_DOCUMENTADA';
        } elseif ($edad<18 || $edad>95) {
            // Cobertura publicada de inclusión no es garantía para mayores de 77.
            $motivos[]='EDAD_FUERA_DE_18_A_95_EN_MUESTRA_PUBLICADA';
        }
        $escolaridad=$perfil['escolaridad_anos']??null;
        if (!is_int($escolaridad)) {
            $motivos[]='ESCOLARIDAD_NO_DOCUMENTADA';
        } elseif ($escolaridad<1) {
            $motivos[]='MUESTRA_PUBLICADA_EXCLUYO_CERO_ESCOLARIDAD_FORMAL';
        }
        if (($perfil['espanol_materno'] ?? null)!==true) {
            $motivos[]='IDIOMA_MATERNO_NO_COINCIDE_CON_COHORTE';
        }
        if (($perfil['alfabetizacion_lectura_escritura'] ?? null)!==true) {
            $motivos[]='LECTURA_ESCRITURA_NO_COINCIDE_CON_COHORTE';
        }
        if (($perfil['nacimiento_y_residencia_bolivia'] ?? null)!==true) {
            $motivos[]='CRITERIO_GEOGRAFICO_DE_COHORTE_NO_DOCUMENTADO';
        }
        if (($perfil['participacion_institucional'] ?? null)===true) {
            $motivos[]='MUESTRA_COMUNITARIA_NO_EQUIVALE_A_RESIDENCIA_INSTITUCIONAL';
        }
        if (($perfil['seleccion_saludable_verificada'] ?? null)!==true) {
            $motivos[]='CRITERIOS_MUESTRA_SALUDABLE_NO_DOCUMENTADOS';
        }
        if (($perfil['revisados_criterios_exclusion']??null)!==true) {
            $motivos[]='EXCLUSIONES_DE_ARTICULO_NO_COMPROBADAS';
        }
        if (($captura['correspondencia_edicion_manual_verificada']??null)!==true) {
            $motivos[]='CORRESPONDENCIA_EDICION_MANUAL_NO_DEMOSTRADA';
        }
        if (($captura['derechos_de_uso_verificados']??null)!==true) {
            $motivos[]='DERECHOS_DE_APLICACION_NO_VERIFICADOS';
        }
        if (($captura['algoritmo_normativo_autorizado']??null)!==true) {
            $motivos[]='ALGORITMO_NORMATIVO_AUN_NO_AUTORIZADO';
        }
        if (($captura['revision_neuropsicologica_validada']??null)!==true) {
            $motivos[]='DICTAMEN_PROFESIONAL_PENDIENTE';
        }
        $motivos=array_values(array_unique($motivos));
        sort($motivos,SORT_STRING);
        return [
            'uso'=>'AUDITORIA_CAPTURA_NO_CLINICA',
            'version'=>self::VERSION,
            'componente'=>$componente,'tarea'=>$tarea,
            'referencia_fuente'=>$contrato['doi'],
            'tabla_bolivia'=>$contrato['tabla_bolivia'],
            'campos_crudos_esperados'=>array_keys($esperados),
            'comprobacion_formal_sin_hallazgos'=>($motivos===[]),
            'alertas_comparabilidad'=>$motivos,
            'estado_g08'=>'NO_INTERPRETABLE_POR_G08',
            'estado_poblacion'=>'NO_HOMOLOGADO_A_GERIATRICO',
            'puntuacion_normativa'=>null,
            'percentil'=>null,
            'interpretacion_A_P'=>null,
            'uso_asistencial_autorizado'=>false,
            'advertencia'=>'Los datos y revisiones son declaraciones de ensayo y no acreditan muestra, norma, permiso ni dictamen.',
        ];
    }
}
