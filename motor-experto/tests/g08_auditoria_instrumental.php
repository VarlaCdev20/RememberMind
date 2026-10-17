<?php
declare(strict_types=1);

/**
 * G08: correspondencia del inventario 15 componentes G09, bibliografía candidata,
 * brechas por edición/tarea/edad y bloqueo absoluto de interpretación clínica.
 */
spl_autoload_register(static function(string $clase): void {
    $prefix='MotorExperto\\';
    if (str_starts_with($clase,$prefix)) {
        require dirname(__DIR__).'/src/motor_experto/'.
            str_replace('\\','/',substr($clase,strlen($prefix))).'.php';
    }
});
use MotorExperto\investigacion\g08\CatalogoExpedientesG08Investigacion as G08;
use MotorExperto\investigacion\g08\AuditorAplicabilidadG08Investigacion as Auditor;
use MotorExperto\investigacion\g09\CatalogoReglasG09Experimental as G09;

function exigeG08(bool $ok, string $mensaje): void
{
    if (!$ok) throw new RuntimeException($mensaje);
}

$auditor = new Auditor;
$inventario = G08::todos();
$componentesG09 = array_values(array_unique(array_column(G09::todas(),'componente')));
sort($componentesG09);
$componentesG08 = array_keys($inventario);
sort($componentesG08);
exigeG08(count($inventario) === 15,'No hay exactamente 15 componentes');
exigeG08($componentesG08 === $componentesG09,'G08 y G09 discrepan sobre inventario');
$n=2;
$candidatas = 0;
$publicaciones = [];
foreach ($inventario as $codigo=>$ficha) {
    $r=$auditor->auditar($codigo,['uso'=>'REVISION_DOCUMENTAL_TEST_ONLY']);
    exigeG08($r['estado_interpretacion']==='NO_INTERPRETABLE_POR_G08',$codigo.' se activó');
    exigeG08($r['puede_emitir_A_o_P']===false && $r['evidencia_clinica']===null,$codigo.' generó evidencia');
    exigeG08($r['faltantes_declarados']!==[],$codigo.' no identificó brechas');
    exigeG08($ficha['perfiles_normativos_aprobados']===0 && $ficha['uso_autorizado']===false,
        $codigo.' declara autorización');
    $n+=4;
    if($ficha['estado']==='FUENTE_NORMATIVA_CANDIDATA') ++$candidatas;
    foreach($ficha['bibliografia'] as $ref) {
        exigeG08(preg_match('/^\d{8}$/D',$ref['pmid'])===1,$codigo.' PMID incorrecto');
        exigeG08(str_starts_with($ref['doi'],'10.3233/NRE-'),$codigo.' DOI incorrecto');
        $publicaciones[$ref['pmid']]=true; $n+=2;
    }
}
exigeG08($candidatas===6,'Se esperaba seis subcomponentes con bibliografía candidata');
exigeG08(count($publicaciones)===7,'No corresponden siete artículos científicos distintos');
$n+=2;

$campos=[
    'edicion_identificada','administracion_identificada','norma_tabla_identificada',
    'pais_norma_verificado','edad_compatible_verificada','escolaridad_compatible_verificada',
    'idioma_equivalente_verificado','poblacion_institucional_justificada',
    'licencia_y_derechos_verificados','metodo_calculo_documentado',
    'confusores_revisados','revision_profesional_documentada',
    'audicion_verificada','lectura_color_verificados','vision_motricidad_verificadas',
    'atribucion_constructo_verificada','forma_estandar_verificada',
    'consigna_duracion_verificadas','fase_copia_verificada',
];
$relleno=['uso'=>'REVISION_DOCUMENTAL_TEST_ONLY','edad_anos'=>68];
foreach($campos as $campo)$relleno[$campo]=true;
foreach($inventario as $codigo=>$ficha) {
    $data=$relleno;
    $data['instrumento']=$ficha['instrumentos'][0];
    $data['tarea']=$ficha['tareas'][0];
    $data['pmid_norma']=$ficha['bibliografia']
        ? reset($ficha['bibliografia'])['pmid'] : '';
    $r=$auditor->auditar($codigo,$data);
    exigeG08($r['puede_emitir_A_o_P']===false,$codigo.' checklist desbloqueó clínica');
    exigeG08($r['perfil_normativo_activado']===false,$codigo.' perfil clínico activado');
    if($ficha['estado']==='FUENTE_NORMATIVA_CANDIDATA') {
        exigeG08($r['declaracion_completa']===true,
            $codigo.' checklist completo de candidato sigue incompleto: '.implode(',',$r['faltantes_declarados']));
        $n++;
    }
    $n+=2;
}
$primer = $relleno;
$primer['instrumento']='BNT_15_ITEMS';$primer['tarea']='BNT_FORMA_ESTANDAR';$primer['pmid_norma']='26639925';
$r=$auditor->auditar('LEN-S01',$primer);
exigeG08(in_array('INSTRUMENTO_AJENO_A_COMPONENTE',$r['faltantes_declarados'],true),'Aceptó BNT abreviado como forma estándar');
$n++;
$primer['instrumento']='BNT_ESTANDAR';$primer['tarea']='BNT_FORMA_ESTANDAR';
$primer['forma_estandar_verificada']=false;
$r=$auditor->auditar('LEN-S01',$primer);
exigeG08(in_array('FORMA_ESTANDAR_NO_VERIFICADA',$r['faltantes_declarados'],true),'No detectó forma no verificada');
$n++;
$rocf=$relleno; $rocf['instrumento']='REY_OSTERRIETH';$rocf['tarea']='ROCF_RECUERDO';
$rocf['pmid_norma']='26639929';
$r=$auditor->auditar('VIS-S02',$rocf);
exigeG08(in_array('TAREA_NO_CORRESPONDE_A_INSTRUMENTO_Y_COMPONENTE',$r['faltantes_declarados'],true),'Usó recuerdo como copia');
$n++;
$tmt=$relleno;$tmt['instrumento']='TMT';$tmt['tarea']='TMT_A';$tmt['pmid_norma']='26639932';
$r=$auditor->auditar('EJE-S02',$tmt);
exigeG08(in_array('TAREA_NO_CORRESPONDE_A_INSTRUMENTO_Y_COMPONENTE',$r['faltantes_declarados'],true),'Usó TMT-A como TMT-B');
$n++;
$bta=$relleno;$bta['instrumento']='BTA';$bta['tarea']='BTA_AUDIO';$bta['pmid_norma']='26639928';
$bta['audicion_verificada']=false;
$r=$auditor->auditar('ATE-S03',$bta);
exigeG08(in_array('AUDICION_NO_VERIFICADA',$r['faltantes_declarados'],true),'Ignoró audición en BTA');
$n++;
$ruff=$relleno;$ruff['instrumento']='RUFF_2_7';$ruff['tarea']='RUFF_SOSTENIDA';$ruff['edad_anos']=82;
$r=$auditor->auditar('ATE-S01',$ruff);
exigeG08(in_array('RUFF_EDAD_MAYOR_70_EXIGE_EVIDENCIA_NORMATIVA_ESPECIFICA',$r['faltantes_declarados'],true),
    'Ignoró brecha de edad del expediente Ruff');
$n++;
$mw=$relleno;$mw['instrumento']='MWCST';$mw['tarea']='MWCST_RAZONAMIENTO';$mw['pmid_norma']='26639931';
$r=$auditor->auditar('EJE-S05',$mw);
exigeG08(in_array('M_WCST_NO_ACREDITA_POR_SI_SOLO_RAZONAMIENTO_GENERAL',$r['faltantes_declarados'],true),
    'Validó razonamiento general desde M-WCST');
$n++;
$r=$auditor->auditar('LEN-S02',['uso'=>'ATENCION_ASISTENCIAL']);
exigeG08(in_array('USO_FUERA_DEL_EXPEDIENTE_DE_INVESTIGACION',$r['faltantes_declarados'],true)
    && $r['evidencia_clinica']===null,'Se emitió evidencia en contexto asistencial');
$n++;
try {
    G08::obtener('COG-MEM');
    throw new RuntimeException('Admitió dominio fuera del inventario');
} catch (DomainException $ex) {$n++;}
echo 'PASS: '.$n." comprobaciones G08, 15 componentes/7 artículos/6 candidatos; cero perfiles clínicos activos.\n";
