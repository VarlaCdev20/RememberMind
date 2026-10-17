<?php
declare(strict_types=1);

spl_autoload_register(static function(string $clase):void {
    $prefix='MotorExperto\\';
    if(str_starts_with($clase,$prefix))require dirname(__DIR__).'/src/motor_experto/'.
        str_replace('\\','/',substr($clase,strlen($prefix))).'.php';
});

use MotorExperto\investigacion\g08\ContratoCapturaCrudaG08Investigacion as Captura;
use MotorExperto\investigacion\g08\LocalizadoresAnexosBoliviaG08Investigacion as Normas;
use MotorExperto\investigacion\g08\AuditorCapturaYComparabilidadG08Investigacion as Auditor;

function exigirCaptura(bool $si,string $motivo):void {
    if(!$si)throw new RuntimeException($motivo);
}
function ejemplo(string $componente,string $tarea):array {
    $x=Captura::todos()[$componente][$tarea];
    $crudos=[];
    foreach($x['campos_crudos'] as $nombre=>$tipo) {
        $crudos[$nombre]=$tipo==='NUMERO_POSITIVO'?45:2;
    }
    if($tarea==='ROCF_COPIA')$crudos['elementos_revisados']=18;
    return [
        'uso'=>'REVISION_DOCUMENTAL_TEST_ONLY',
        'residente_ficticio'=>'SINTETICO-77',
        'aplicacion_ficticia'=>'SIM-A-001',
        'instrumento'=>$x['instrumento'],
        'tarea'=>$tarea,
        'metrica'=>$x['metrica'],
        'unidad'=>$x['unidad_esperada'],
        'edicion_declarada'=>'SIM-EDICION-01',
        'codigo_protocolo'=>'SIM-PROT-01',
        'tabla_bolivia'=>$x['tabla_bolivia'],
        'doi'=>$x['doi'],
        'datos_crudos'=>$crudos,
        'perfil_demografico_sintetico'=>[
            'edad_anos'=>69,'escolaridad_anos'=>6,
            'espanol_materno'=>true,
            'alfabetizacion_lectura_escritura'=>true,
            'nacimiento_y_residencia_bolivia'=>true,
            'participacion_institucional'=>false,
            'seleccion_saludable_verificada'=>true,
            'revisados_criterios_exclusion'=>true,
        ],
        'correspondencia_edicion_manual_verificada'=>true,
        'derechos_de_uso_verificados'=>true,
        'algoritmo_normativo_autorizado'=>true,
        'revision_neuropsicologica_validada'=>true,
    ];
}

$auditor=new Auditor;
$contratos=Captura::todos();
$localizadores=Normas::todos();
exigirCaptura(count($contratos)===6,'No se cubren seis componentes');
exigirCaptura(array_keys($contratos)===array_keys($localizadores),'No coinciden códigos G08');
$n=2;
$pares=0;
foreach($contratos as $componente=>$tareas) {
    foreach($tareas as $tarea=>$spec) {
        $pares++;
        exigirCaptura(isset($localizadores[$componente][$tarea]),'Tarea no localizada');
        exigirCaptura($spec['algoritmo_normativo']==='NO_IMPLEMENTADO','Se integró fórmula sin autorizar');
        $bien=ejemplo($componente,$tarea);
        $r=$auditor->revisar($componente,$tarea,$bien);
        exigirCaptura($r['comprobacion_formal_sin_hallazgos']===true,
            $tarea.' completitud formal: '.implode(';',$r['alertas_comparabilidad']));
        exigirCaptura($r['estado_g08']==='NO_INTERPRETABLE_POR_G08' &&
            $r['puntuacion_normativa']===null &&
            $r['percentil']===null &&
            $r['interpretacion_A_P']===null &&
            $r['uso_asistencial_autorizado']===false,
            $tarea.' produjo resultado clínico');
        $n+=4;
        foreach(['instrumento','metrica','unidad','tabla_bolivia','doi'] as $key) {
            $incorrecto=$bien;$incorrecto[$key]='SIM-OTRO';
            $r=$auditor->revisar($componente,$tarea,$incorrecto);
            exigirCaptura($r['comprobacion_formal_sin_hallazgos']===false,
                $tarea.' permite '+$key.' ajeno');
            $n++;
        }
        $incompleto=$bien;unset($incompleto['datos_crudos'][array_key_first($incompleto['datos_crudos'])]);
        $r=$auditor->revisar($componente,$tarea,$incompleto);
        exigirCaptura(in_array('CAMPOS_CRUDOS_INCOMPLETOS_O_AJENOS',$r['alertas_comparabilidad'],true),
            $tarea.' datos crudos incompletos');
        $n++;
        $invalido=$bien;$invalido['datos_crudos'][array_key_first($invalido['datos_crudos'])]='2';
        $r=$auditor->revisar($componente,$tarea,$invalido);
        exigirCaptura(count($r['alertas_comparabilidad'])>=1,$tarea.' admite números como texto');
        $n++;
        $residente=$bien;$residente['perfil_demografico_sintetico']['participacion_institucional']=true;
        $r=$auditor->revisar($componente,$tarea,$residente);
        exigirCaptura(in_array('MUESTRA_COMUNITARIA_NO_EQUIVALE_A_RESIDENCIA_INSTITUCIONAL',$r['alertas_comparabilidad'],true),
            $tarea.' homologó un geriátrico a la muestra comunitaria');
        $n++;
    }
}
exigirCaptura($pares===12,'No hay exactamente 12 capturas G08');
$n++;

$base=ejemplo('ATE-S03','BTA_AUDIO');
$base['datos_crudos']['forma_N_aciertos']=11;
$r=$auditor->revisar('ATE-S03','BTA_AUDIO',$base);
exigirCaptura(in_array('FORMAS_BTA_FUERA_DE_10_SERIES',$r['alertas_comparabilidad'],true),
    'BTA aceptó más respuestas por forma que las documentadas');
$n++;

$base=ejemplo('VIS-S02','ROCF_COPIA');
$base['datos_crudos']['elementos_revisados']=17;
$r=$auditor->revisar('VIS-S02','ROCF_COPIA',$base);
exigirCaptura(in_array('ROCF_COPIA_DIFIERE_DE_18_ELEMENTOS_36_PUNTOS',$r['alertas_comparabilidad'],true),
    'ROCF copia sin los 18 elementos');
$n++;
$base['datos_crudos']['elementos_revisados']=18;
$base['datos_crudos']['puntaje_copia']=37;
$r=$auditor->revisar('VIS-S02','ROCF_COPIA',$base);
exigirCaptura(in_array('ROCF_COPIA_DIFIERE_DE_18_ELEMENTOS_36_PUNTOS',$r['alertas_comparabilidad'],true),
    'ROCF copia puntaje no permitido');
$n++;

$base=ejemplo('EJE-S02','TMT_B');
$base['perfil_demografico_sintetico']['edad_anos']=97;
$r=$auditor->revisar('EJE-S02','TMT_B',$base);
exigirCaptura(in_array('EDAD_FUERA_DE_18_A_95_EN_MUESTRA_PUBLICADA',$r['alertas_comparabilidad'],true),
    'Se extrapoló edad >95');
$n++;
$base['perfil_demografico_sintetico']['edad_anos']=85;
$base['perfil_demografico_sintetico']['escolaridad_anos']=0;
$r=$auditor->revisar('EJE-S02','TMT_B',$base);
exigirCaptura(in_array('MUESTRA_PUBLICADA_EXCLUYO_CERO_ESCOLARIDAD_FORMAL',$r['alertas_comparabilidad'],true),
    'Se extrapoló muestra a 0 años de estudio');
$n++;
$base['perfil_demografico_sintetico']['escolaridad_anos']=6;
$base['perfil_demografico_sintetico']['espanol_materno']=false;
$r=$auditor->revisar('EJE-S02','TMT_B',$base);
exigirCaptura(in_array('IDIOMA_MATERNO_NO_COINCIDE_CON_COHORTE',$r['alertas_comparabilidad'],true),
    'No se detectó problema de idioma materno');
$n++;
$base=ejemplo('LEN-S02','FLUIDEZ_LETRA_F');
$base['datos_crudos']['segundos_administrados']=0;
$r=$auditor->revisar('LEN-S02','FLUIDEZ_LETRA_F',$base);
exigirCaptura(in_array('DATO_CRUDO_INVALIDO_segundos_administrados',$r['alertas_comparabilidad'],true),
    'Tiempo de fluidez no positivo');
$n++;
$base=ejemplo('LEN-S01','BNT_FORMA_ESTANDAR');
$base['uso']='ASISTENCIAL';
$r=$auditor->revisar('LEN-S01','BNT_FORMA_ESTANDAR',$base);
exigirCaptura(in_array('CAPTURA_FUERA_DE_SIMULACION',$r['alertas_comparabilidad'],true)
    && $r['uso_asistencial_autorizado']===false,'Se aceptó uso asistencial');
$n++;
$base=ejemplo('EJE-S01','STROOP_INTERFERENCIA');
$base['datos_crudos']['palabras_correctas']=3;
$r=$auditor->revisar('EJE-S01','STROOP_INTERFERENCIA',$base);
exigirCaptura($r['percentil']===null && $r['interpretacion_A_P']===null,
    'Stroop calculó interferencia o diagnóstico con fórmula aún pendiente');
$n++;
try {
    $auditor->revisar('VIS-S02','ROCF_RECUERDO',ejemplo('VIS-S02','ROCF_COPIA'));
    throw new RuntimeException('Se aceptó tarea no existente para visuoconstrucción');
} catch (DomainException $expected) {$n++;}
echo 'PASS: '.$n." aserciones, 12 protocolos sintéticos, 0 percentiles y 0 estados DCL A/P.\n";
