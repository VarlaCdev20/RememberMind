param([string]$Salida = 'output/auditoria-integral-20261025/gerencia-casos-aceptacion.csv')

$ErrorActionPreference = 'Stop'
$repo = [IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$carpeta = Join-Path $repo 'docs/especificacion-funcional/vistas/gerencia'
# Inventario documental: no ejecuta Laravel, no consulta ni cambia ninguna BDD.
# Los métodos de una suite no se convierten automáticamente en escenarios aceptados.
$fuentes = @(
    @('06-ACEPTACION-PERSONAL-DOCUMENTOS.md','P',36,'ContratoPersonalInstitucionalTest;GerenciaPersonalRenovacionTest;GerenciaSolicitudesDocumentalesTest;GerenciaAltaSustitucionConcurrentePostgresTest;GerenciaConciliacionDocumentalTest'),
    @('08-SOLICITUD-DOCUMENTAL-POR-ROL.md','S',8,'GerenciaSolicitudesDocumentalesTest;GerenciaDocumentacionConcurrenciaPostgresTest'),
    @('11-ACEPTACION-FAMILIARES-CONTACTOS.md','FA',24,'GerenciaContactosTest;GerenciaAccesoFamiliarTest;GerenciaHistoriaNominalTest'),
    @('13-ACEPTACION-AREAS-EQUIPO.md','AE',20,'GerenciaAreasEquipoTest;AreasInstitucionalesV2Test'),
    @('16-ACEPTACION-PLANIFICACION-COBERTURA.md','PC',28,'GerenciaPlanificacionSeguridadTest;GerenciaConcurrenciaPlanificacionPostgresTest'),
    @('20-ACEPTACION-RESIDENTES-OCUPACION.md','RO',24,'GerenciaConsultaResidentesTest;GerenciaOcupacionAccesoTest;MapaHabitacionesTest'),
    @('23-ACEPTACION-ALERTAS-GERENCIA.md','AG',24,'AlertasFlujosPorRolTest;AlertasRolesConsistenciaTest;AlertasHistorialFeatureTest'),
    @('27-ACEPTACION-REPORTES-GERENCIA.md','RG-T',24,'ReportesGerenciaRenovacionTest'),
    @('31-ACEPTACION-GALERIA-GERENCIA.md','GL-T',24,'GaleriaInstitucionalTest;GaleriaInstitucionalConcurrenciaPostgresTest')
)
$brechas = @{
    'P03'='Cerrado en alcance informe41: correo/CI/doble envío concurrentes, rollback y permisos revalidados tras espera; nueve carreras compartidas conP17.'
    'P17'='Cerrado en alcance informe41: original/primera carga concurrentes, referenciaLocked, conflicto Livewire y reapertura explícita; sin constraintSQL nuevo.'
    'P19'='Resuelto por expediente en informe40: concesión original única y efecto compartido sin copiar actos; no se afirma UNIQUE SQL global.'
    'P30'='Regresión de entrega puntual observada tarde y primera entrega tardía; ver informe39.'
    'P31'='Regresiones de lectura FOTO/FOTO_DOC, duplicidad, sucesión, privacidad y procedencia; ver informe39.'
    'AE07'='Regresión de rollback de auditoría y reintento normalizado; ver informe39.'
    'AE08'='Revocación posterior elimina equipo abierto; faltan las demás combinaciones del caso.'
    'PC12'='Regresión de dos altas concurrentes del mismo intervalo; no equivale a carrera de estado.'
    'PC13'='Regresión de fallo en segunda ocurrencia y rollback de jornada/eventos/auditoría.'
    'PC14'='Falta aceptación específica de avisos de cambios de planificación.'
    'PC15'='Falta aceptación específica de reintentos de avisos de planificación.'
    'PC17'='Regresión de horas de turno referenciado y corrección de nombre sin alterar la jornada.'
    'RO18'='Falta aceptación del origen por cada acto, permisos históricos y límite visible superior a doce.'
    'AG10'='Falta fixture de más de ocho grupos y comprobación del alcance truncado.'
    'RG-T13'='Falta comparación completa de filas/valores PDF frente a CSV.'
    'RG-T17'='Faltan regresiones de acentos, comillas, saltos y prefijos de fórmulas CSV.'
    'GL-T16'='Regresión de dos formularios sobre el mismo original, rechazo de edición obsoleta y conservación de cambios; ver informe39.'
}
$regresiones = @{
    'P03'=@('GerenciaAltaSustitucionConcurrentePostgresTest','test_solicitudes_simultaneas_conservan_una_alta_o_un_sucesor with data set "alta_identica"','gerencia-concurrencia-12-aceptacion-postgres.xml')
    'P17'=@('GerenciaAltaSustitucionConcurrentePostgresTest','test_solicitudes_simultaneas_conservan_una_alta_o_un_sucesor with data set "sustitucion_original"','gerencia-concurrencia-12-aceptacion-postgres.xml')
    'P19'=@('GerenciaSolicitudesDocumentalesTest','test_prorroga_del_expediente_se_consulta_en_otras_solicitudes_sin_copiar_el_acto','gerencia-prorroga-expediente-postgres.xml')
    'P30'=@('GerenciaSolicitudesDocumentalesTest','test_entrega_en_plazo_observada_despues_no_genera_mora_retroactiva_y_conserva_aviso','gerencia-procedencia-documental-final.xml')
    'P31'=@('GerenciaConciliacionDocumentalTest','test_reemplazo_desde_ficha_conserva_tipo_original_y_cadena_foto_doc','gerencia-procedencia-documental-final.xml')
    'AE07'=@('GerenciaAreasEquipoTest','test_fallo_de_auditoria_revierte_area_y_reintento_no_duplica','gerencia-verificacion-aislada.xml')
    'AE08'=@('GerenciaAreasEquipoTest','test_revocar_lectura_de_equipo_abierto_no_mantiene_datos_residuales','gerencia-verificacion-aislada.xml')
    'PC12'=@('GerenciaDocumentacionConcurrenciaPostgresTest','test_confirmaciones_solapadas_no_duplican_solicitud_prorroga_o_historia with data set #4','gerencia-adicional-postgres-final.xml')
    'PC13'=@('GerenciaPlanificacionSeguridadTest','test_fallo_en_segunda_ocurrencia_revierte_primera_jornada_evento_y_auditoria','gerencia-verificacion-aislada.xml')
    'PC17'=@('GerenciaPlanificacionSeguridadTest','test_turno_referenciado_conserva_horas_historicas_pero_permite_corregir_nombre','gerencia-verificacion-aislada.xml')
    'GL-T16'=@('GaleriaInstitucionalTest','test_dos_editores_del_mismo_instante_no_sobrescriben_cambios_y_conservan_entrada_obsoleta','gerencia-cierre-vacios-sqlite.xml')
}
$filas = @()
foreach ($fuente in $fuentes) {
    $archivo,$prefijo,$esperados,$pruebas = $fuente
    $ruta = Join-Path $carpeta $archivo
    $lineas = Get-Content -LiteralPath $ruta
    $localizadas = @()
    for ($indice=0; $indice -lt $lineas.Count; $indice++) {
        if ($lineas[$indice] -match ('^\|\s*(' + [regex]::Escape($prefijo) + '\d{2})\s*\|')) {
            $caso = $Matches[1]
            # Las secciones de evidencia posteriores vuelven a citar códigos ya definidos.
            # El contrato se toma de la primera fila normativa, no de esa referencia derivada.
            if ($localizadas -contains $caso) { continue }
            $celdas = $lineas[$indice].Split('|')
            $localizadas += $caso
            $metodo = ''
            $evidencia = ''
            if ($regresiones.ContainsKey($caso)) {
                $clase,$nombre,$junit = $regresiones[$caso]
                $rutaJUnit = Join-Path $repo "output/auditoria-integral-20261025/$junit"
                if (!(Test-Path -LiteralPath $rutaJUnit)) { throw "Falta evidencia de regresión: $junit" }
                [xml]$resultado = Get-Content -LiteralPath $rutaJUnit -Raw
                $prueba = @($resultado.SelectNodes('//testcase') | Where-Object { $_.class -eq "Tests\Feature\$clase" -and $_.name -eq $nombre })
                if ($prueba.Count -ne 1 -or $prueba[0].SelectNodes('failure|error|skipped').Count -ne 0) {
                    throw "Regresión no acreditada: $clase::$nombre en $junit"
                }
                $metodo = "tests/Feature/$clase.php::$nombre"
                $evidencia = "output/auditoria-integral-20261025/$junit"
            }
            $filas += [pscustomobject]@{
                Caso=$caso; Fuente="docs/especificacion-funcional/vistas/gerencia/$archivo"; Linea=$indice+1
                Contrato=($celdas[2..($celdas.Count-2)] | ForEach-Object { $_.Trim() }) -join ' | '
                Estado=$(if($caso -in @('P03','P17')){'VERIFICADO_EN_ALCANCE_INFORME_41'}else{'ACEPTACION_COMPLETA_NO_DECLARADA'})
                Pruebas_base_localizadas=$pruebas
                Regresion_especifica_verificada=$metodo
                Evidencia_regresion=$evidencia
                Limite=$(if($brechas.ContainsKey($caso)){$brechas[$caso]}else{'Cobertura parcial: contrastar todas las condiciones con las aserciones y el entorno ejecutado; consultar informes38/39.'})
            }
        }
    }
    if ($localizadas.Count -ne $esperados) { throw "Inventario distinto en $archivo`: $($localizadas.Count), esperado $esperados" }
}
if ($filas.Count -ne 212 -or ($filas.Caso | Select-Object -Unique).Count -ne 212) { throw 'La matriz debe conservar212 casos únicos.' }
$destino = [IO.Path]::GetFullPath((Join-Path $repo $Salida))
if (!$destino.StartsWith($repo+[IO.Path]::DirectorySeparatorChar,[StringComparison]::OrdinalIgnoreCase)) { throw 'El informe debe quedar dentro del repositorio.' }
$filas | Export-Csv -LiteralPath $destino -NoTypeInformation -Encoding utf8BOM
Write-Output "212 casos únicos, nueve contratos. Aceptación completa no inferida. Informe: $Salida"
