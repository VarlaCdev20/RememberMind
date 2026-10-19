<?php

declare(strict_types=1);

/**
 * Verifica localizadores de TABLAS bolivianas sin extraer datos normativos.
 * Una coincidencia bibliográfica NUNCA permite interpretación clínica.
 */
spl_autoload_register(static function (string $clase): void {
    $prefix = 'MotorExperto\\';
    if (str_starts_with($clase, $prefix)) {
        require dirname(__DIR__).'/src/motor_experto/'.
            str_replace('\\', '/', substr($clase, strlen($prefix))).'.php';
    }
});
use MotorExperto\investigacion\g08\CatalogoExpedientesG08Investigacion as G08;
use MotorExperto\investigacion\g08\LocalizadoresAnexosBoliviaG08Investigacion as Anexos;

function exigeAnexo(bool $si, string $mensaje): void
{
    if (! $si) {
        throw new RuntimeException($mensaje);
    }
}
$inventario = Anexos::todos();
exigeAnexo(count($inventario) === 6, 'No hay seis componentes con tablas localizadas');
$pmids = [];
$total = 0;
$aserciones = 1;
$auditor = new Anexos;
foreach ($inventario as $componente => $tareas) {
    $ficha = G08::obtener($componente);
    exigeAnexo($ficha['estado'] === 'FUENTE_NORMATIVA_CANDIDATA',
        $componente.' no corresponde a candidato G08');
    $aserciones++;
    foreach ($tareas as $tarea => $fila) {
        $total++;
        $pmids[$fila['pmid']] = true;
        $solicitud = [
            'uso' => 'REVISION_DOCUMENTAL_TEST_ONLY',
            'pais' => 'BOLIVIA',
            'tabla' => $fila['tabla'],
            'instrumento' => $fila['instrumento'],
            'metrica' => $fila['metrica'],
            'pmid' => $fila['pmid'],
            'doi' => $fila['doi'],
        ];
        exigeAnexo(isset($ficha['bibliografia']) &&
            in_array($fila['pmid'], array_column($ficha['bibliografia'], 'pmid'), true),
            'Fuente no reconocida por G08: '.$componente);
        $r = $auditor->contrastar($componente, $tarea, $solicitud);
        exigeAnexo($r['cita_identificada'] === true, 'No reconoce localizador exacto '.$tarea);
        exigeAnexo($r['puede_interpretar_puntuacion'] === false &&
            $r['perfil_g08_autorizado'] === false && $r['puede_emitir_A_P'] === false,
            $componente.' activó norma por localizar la tabla');
        $aserciones += 3;
        foreach (['pais', 'instrumento', 'metrica', 'pmid', 'doi', 'tabla'] as $campo) {
            $incorrecta = $solicitud;
            $incorrecta[$campo] = 'SIM-VALOR-DISTINTO';
            $r = $auditor->contrastar($componente, $tarea, $incorrecta);
            exigeAnexo($r['cita_identificada'] === false &&
                in_array('DISCREPANCIA_'.strtoupper($campo), $r['faltantes'], true),
                $componente.'/'.$tarea.' aceptó '.$campo.' ajeno');
            $aserciones++;
        }
    }
}
exigeAnexo($total === 12, 'Se esperaban 12 pares tarea/métrica de seis componentes');
exigeAnexo(count($pmids) === 7, 'Se esperaban siete artículos diferentes');
$aserciones += 2;
$stroop = Anexos::buscar('EJE-S01', 'STROOP_INTERFERENCIA');
exigeAnexo($stroop['tabla'] === 'A35' &&
    $stroop['ajuste_publicado'] === 'SIN_ESTRATIFICACION_EN_TABLA_BOLIVIA',
    'Se inventó estratificación de interferencia Stroop para Bolivia');
$aserciones++;
$bnt = Anexos::buscar('LEN-S01', 'BNT_FORMA_ESTANDAR');
exigeAnexo($bnt['tabla'] === 'A2' &&
    $bnt['ajuste_publicado'] === 'ESCOLARIDAD',
    'Se sustituyó la tabla boliviana BNT por norma distinta');
$aserciones++;
$eje = Anexos::buscar('EJE-S02', 'TMT_B');
exigeAnexo($eje['tabla'] === 'A14', 'TMT-A confundido con TMT-B');
$aserciones++;
$rocf = Anexos::buscar('VIS-S02', 'ROCF_COPIA');
exigeAnexo($rocf['tabla'] === 'A2', 'ROCF copia usa otra tabla');
$aserciones++;
try {
    Anexos::buscar('EJE-S05', 'MWCST_RAZONAMIENTO');
    throw new RuntimeException('EJE-S05 aceptado sin constructo justificado');
} catch (DomainException $esperada) {
    $aserciones++;
}
try {
    Anexos::buscar('VIS-S02', 'ROCF_RECUERDO');
    throw new RuntimeException('Recordatorio Rey usado como visuoconstrucción');
} catch (DomainException $esperada) {
    $aserciones++;
}
try {
    Anexos::buscar('EJE-S02', 'TMT_A');
    throw new RuntimeException('TMT-A usado como flexibilidad');
} catch (DomainException $esperada) {
    $aserciones++;
}
$r = $auditor->contrastar('EJE-S01', 'STROOP_INTERFERENCIA', [
    'uso' => 'ASISTENCIAL', 'tabla' => 'A35', 'pais' => 'BOLIVIA',
    'instrumento' => 'STROOP', 'metrica' => 'STROOP_INTERFERENCIA',
    'pmid' => '26639926', 'doi' => '10.3233/NRE-151281',
]);
exigeAnexo(in_array('FUERA_DE_REVISION_DOCUMENTAL', $r['faltantes'], true) &&
    $r['puede_emitir_A_P'] === false, 'Se permitió uso asistencial de tabla bibliográfica');
$aserciones++;
echo 'PASS: '.$aserciones." aserciones; 6 componentes / 12 tareas-tabla / 7 publicaciones; 0 perfiles G08 activos.\n";
