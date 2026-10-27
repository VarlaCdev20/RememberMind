---
title: "Gerencia: aceptación de Reportes"
status: DRAFT
version: "1.0"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: SPECIFIED_ACCEPTANCE_CASES_NOT_EXECUTED
runtime_verified: false
---

# Aceptación de Reportes de Gerencia

Contratos: [indicadores](25-REPORTES-GERENCIA.md), [exportación](26-EXPORTACION-REPORTES-GERENCIA.md) e [imágenes](28-DISENO-IMAGENES-REPORTES.md). **24 casos especificados, no ejecutados** en esta tarea. Usar datos sintéticos y base desechable cuando se implemente; nunca exportar información real para una captura de ejemplo.

| Caso | Entrada/acción | Resultado requerido |
|---|---|---|
| RG-T01 | Entrar por menú de Gerencia | Reportes agregado autorizado, ruta existente/integrada, sin payload clínico o nominal oculto |
| RG-T02 | Cuenta inactiva o sin permiso en consulta | Denegación backend; no informe recortado presentado como completo |
| RG-T03 | Revocar permiso después de consultar y solicitar descarga | Revalidación independiente; no exporta por tener una vista previa anterior |
| RG-T04 | Consultar personal con multirrol/cuentas suspendidas | Cuenta trabajadores distintos y estado laboral real; no duplica ni sustituye por usuarios |
| RG-T05 | Camas vacías con habitación no habilitada | Disponible según regla canónica; no confundir vacante con disponible |
| RG-T06 | Duplicidad de ocupaciones o relación huérfana | Incidencia/limitación explícita; no ajusta totales o porcentajes para esconderla |
| RG-T07 | Alertas con varios eventos y estados reconocidos | Cuenta alertas distintas; categorías de gráfico excluyentes, sin doble conteo de Por atender |
| RG-T08 | Cero comprobado, NULL, fuente inaccesible y fallo | Cuatro situaciones diferenciadas; no convierte ausencia/error en cero |
| RG-T09 | Fecha actual frente a período de movimientos | Controles y etiquetas corresponden a la fuente; no reconstruye stock pasado sin historia |
| RG-T10 | Filtrar intervalo y varias admisiones por residente | Límites sobre el mismo hecho cuando corresponde; no acepta extremos de dos admisiones distintas |
| RG-T11 | Comparar filtros de las tres secciones | Alcance efectivo explícito, filtro incompatible retirado o explicado; no mezcla población y fechas |
| RG-T12 | Ver detalle/definición | Desglose agregado y fórmula/fuente legibles; no documentos o nombres por implicación |
| RG-T13 | Preparar PDF y CSV con mismo conjunto | Filas, valores, unidades, filtros y límites iguales; sin truncamiento por paginación de pantalla |
| RG-T14 | Actualizar fuentes entre preparación y descarga | Mismo conjunto legítimamente retenido o preparación invalidada con nueva revisión; nunca cambio silencioso |
| RG-T15 | Manipular formato, plantilla, ámbito o valores enviados | Validación/derivación servidor; sin XLSX, archivos arbitrarios o CSV nominal |
| RG-T16 | PDF de varias páginas | Tabla completa, encabezados/paginación, fuentes locales, sin botones, recortes o recursos remotos no permitidos |
| RG-T17 | CSV con acentos, comillas, saltos y prefijos de fórmula | Codificación y escape correctos, protección contra fórmulas, numéricos intactos |
| RG-T18 | Forzar fallo del renderizador o fallback | Error seguro o fallback verificado con mismo contenido; ningún éxito/archivo vacío ficticio |
| RG-T19 | Generación correcta, cancelación y reintento | Auditoría veraz de acciones/resultados; no afirma guardado en dispositivo ni envía correo |
| RG-T20 | Revisar descarga y recursos en frontend | Sin nombres, diagnósticos, documentos, PK sensibles o relaciones completas fuera del alcance agregado |
| RG-T21 | Gráfica con 0/1/2+ datos válidos y fechas incompletas | Empty/valor aislado/serie válidos; sin tendencia o interpolación inventada, tabla alternativa |
| RG-T22 | Navegación, tabs, radios PDF/CSV, teclado y foco | Selección anunciada, foco visible, Cancelar/Volver preservan contexto, disabled/loading con razón |
| RG-T23 | 1440/1280/1024/768/390 px y temas | Sin desbordamiento global, tabla legible, gráficos accesibles, contraste medido y movimiento reducido |
| RG-T24 | Muchos registros y doble clic en descargar | Consultas sin N+1 ni límite oculto, recurso controlado y prevención de doble envío; sin mutaciones de fuentes |

## Pruebas localizadas y límites

| Archivo | Qué cubre según código localizado | Qué falta para este alcance |
|---|---|---|
| [ReportesPortabilidadTest](../../../../tests/Feature/ReportesPortabilidadTest.php) | Listas, orden, límite y portabilidad SQLite sin CONCAT | No prueba PDF/CSV gerenciales, privacidad o igualdad del conjunto |
| [GerenciaDashboardTest](../../../../tests/Feature/GerenciaDashboardTest.php) | Personas únicas en cobertura, accesos sin escritura clínica y preview | No certifica reportes/exportación del nuevo contrato |
| [ResidentesEspaciosTest](../../../../tests/Feature/ResidentesEspaciosTest.php) | Inventario, métricas y filtros de residentes/camas | Consumir mismos criterios en Reportes y exportaciones |
| [AlertasRolesConsistenciaTest](../../../../tests/Feature/AlertasRolesConsistenciaTest.php) | Ámbito, filtros, estados y fuentes de Alertas | Agregación sin doble conteo y ausencia de payload nominal en Reportes |

No se reejecutaron estas suites al documentar. Implementación futura: pruebas unitarias de agregación y presentación; feature de permisos, preparación/descarga y auditoría; integración de fuentes y cambios concurrentes; inspección del PDF/CSV realmente generado con datos sintéticos; build y recorrido visual si cambia frontend. SQLite no acredita bloqueos o semántica propia de PostgreSQL.

## Cierre documental frente a cierre de producto

Esta entrega incorpora 24 casos; el índice acumula **188 escenarios especificados**, no ejecutados en estas tareas documentales. PNG, referencias, frontmatter y cantidades se comprueban por scripts documentales. Pendientes: código de proyección agregada y CSV equivalente, seguridad/privacidad, coherencia temporal, generación real, accesibilidad y recorrido completo.

## Verificación documental del 26/10/2026

- `node output/auditoria-integral-20261025/verificar-reportes-gerencia.mjs`: PASS; cuatro documentos, doce datos/contextos, diez controles, 24 casos únicos, 52 enlaces locales y dos PNG de 1536 × 1024 con huellas verificadas.
- `node output/auditoria-integral-20261025/auditar-enlaces-documentales.mjs reportes-gerencia`: 544 documentos, 4214 enlaces locales, cero destinos inexistentes y cero duplicados exactos en ese corte del árbol compartido.
- `node output/auditoria-integral-20261025/verificar-alertas-gerencia.mjs` y `node output/auditoria-integral-20261025/verificar-residentes-ocupacion.mjs`: PASS documental de los bloques anteriores. El comprobador de Alertas valida su subtotal de 24 casos y enlace de aceptación, sin congelar el total acumulado del rol.
- `git diff --check -- docs/especificacion-funcional docs/CHANGELOG.md`: sin errores. Sin suites backend/frontend, build o pruebas de BDD: no cambió aplicación. Los resultados no prueban exportación productiva, accesibilidad o validación clínica. Sin commit, push, migraciones ni despliegue.
