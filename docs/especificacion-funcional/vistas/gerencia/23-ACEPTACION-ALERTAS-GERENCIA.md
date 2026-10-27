---
title: "Gerencia: aceptación de Alertas"
status: DRAFT
version: "1.0"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: SPECIFIED_ACCEPTANCE_CASES_NOT_EXECUTED
runtime_verified: false
---

# Aceptación de Alertas de Gerencia

Contratos: [bandeja única](21-ALERTAS-GERENCIA.md), [detalle](22-DETALLE-TRAZABILIDAD-ALERTAS.md) y [propuestas visuales](24-DISENO-IMAGENES-ALERTAS.md). Estos **24 casos están especificados, no ejecutados**. Datos sintéticos exclusivamente. No equivalen a aprobación clínica o a una interfaz implementada.

| Caso | Condición/acción | Resultado exigido |
|---|---|---|
| AG01 | Entrar por Inicio, menú o notificación vinculada | Misma bandeja autorizada, destino real y contexto correcto; sin rutas duplicadas o 403 sorteado mediante permisos amplios |
| AG02 | Cuenta inactiva, sin permiso o Familiar con identificador manipulado | Backend deniega antes de enviar datos; no contenido sensible oculto solo con CSS |
| AG03 | Abrir caso de residente fuera del alcance | Denegación contextual y ningún detalle residual del caso anterior |
| AG04 | Ver los tres indicadores | Alertas distintas, ámbito y hora de consulta claros; prioridades registradas, NULL de responsable y fecha límite guardada |
| AG05 | Filtrar lista por nombre, estado y prioridad | Coincidencias autorizadas; página reiniciada, filtros visibles y total filtrado separado del resumen general |
| AG06 | Expandir origen/responsable/fechas y usar Limpiar | No modifica datos; intervalo invertido produce error junto al campo, sin consulta engañosa |
| AG07 | Filtrar oculta una prioridad activa del ámbito | Acceso explícito a prioridades fuera del filtro, sin mezclarlas en el total filtrado |
| AG08 | Responsable NULL frente a FK no resoluble | Sin responsable e inconsistencia diferenciados; no selecciona personal de respaldo |
| AG09 | Fecha límite NULL, futura o superada | Tres situaciones correctas; no calcula plazos clínicos ni toma NULL como vencimiento |
| AG10 | Más de ocho grupos de responsabilidad | Tope y alcance visibles; opción de consultar conjunto permitido, no presenta suma truncada como total |
| AG11 | Seleccionar barra/grupo del gráfico | Filtra por FK real o NULL; tabla alternativa con cantidades, sin reasignación ni ranking de desempeño |
| AG12 | Abrir y cerrar detalle | Estado de alerta intacto, sin evento asistencial artificial; filtros/foco restaurados |
| AG13 | Navegar Resumen/Historial/Origen | Contenido autorizado de la misma alerta; tabs accesibles, sin mezclar fuentes de otros residentes |
| AG14 | Consultar historia con eventos y cambios de responsable | Fechas, autores y secuencia conservados; responsable actual no sustituye autor histórico |
| AG15 | Historia sin eventos o parcialmente paginada | Ausencia/límite explícitos; no fabrica creación, intervención o firma |
| AG16 | Fuente ausente, anulada o vinculada a otro residente | Explicación segura; no inventa origen, elimina alerta ni abre registro ajeno |
| AG17 | Gerencia dispone solo de alertas.ver | Sin intervención clínica ni gráficas clínicas, aun manipulando métodos Livewire o URL |
| AG18 | Intentar asignar/reconocer/atender/cerrar sin autorización específica | Rechazo backend y ninguna mutación/evento; un control oculto no es la única protección |
| AG19 | Marcar notificación como leída o actualizar consulta | No cambia estado de alerta ni crea alertas nuevas; aviso y notificación diferenciados |
| AG20 | Pendiente documental sin residente o sin mapeo de alerta | Se mantiene en fuente/pendientes propios; no inserta una alerta con residente ficticio |
| AG21 | Estado anterior, atendida, cerrada o anulada | Historia conservada y etiqueta legible; no reclasifica automáticamente todo como activo |
| AG22 | Revocar permiso o cambiar estado desde otra sesión | Revalidación en siguiente operación; actualización coherente sin afirmar un guardado no realizado |
| AG23 | Teclado, lector de pantalla, Escape y movimiento reducido | Foco atrapado/restaurado en drawer, tabs/chips anunciados, color acompañado de texto y controles utilizables |
| AG24 | 1440, 1280, 1024, 768 y 390 px; carga/error/vacío y muchos registros | Sin saturación o desbordamiento global, gráficos accesibles, mensajes distintos y consultas sin N+1 |

## Pruebas existentes localizadas

| Archivo | Cobertura localizada en código | Límite de esta entrega |
|---|---|---|
| [AlertasRolesConsistenciaTest](../../../../tests/Feature/AlertasRolesConsistenciaTest.php) | Lectura por rol, cuenta revocada, prohibición de intervención/cierre con permiso aislado, filtros, gráficas restringidas, traza y origen cruzado | No reejecutado; no acredita por sí solo composición nueva de Gerencia |
| [AlertasFlujosPorRolTest](../../../../tests/Feature/AlertasFlujosPorRolTest.php) | Representaciones por especialidad y resumen de responsabilidad para Gerencia | No valida medición de desempeño ni nuevos permisos |
| [AlertasHistorialFeatureTest](../../../../tests/Feature/AlertasHistorialFeatureTest.php) | Permisos de historia y timeline cronológico | No demuestra una instantánea histórica de ubicación/roles actuales |
| [AlertasExperienciaVisualTest](../../../../tests/Feature/AlertasExperienciaVisualTest.php) | Composición por roles y render de experiencia existente | No es revisión visual de los cinco anchos de esta propuesta |
| [GraficasAlertasTest](../../../../tests/Feature/GraficasAlertasTest.php) | Prioridad crítica y cero registros en las gráficas existentes | No acredita el gráfico tabular propuesto ni sus bindings |
| [AlertasConcurrenciaPostgresTest](../../../../tests/Feature/AlertasConcurrenciaPostgresTest.php) | Dos cierres simultáneos: evento único y conflicto, según escenario del test | No ejecutado aquí; cierre no es acción gerencial aprobada por esta ficha |

## Qué debe entregarse al implementar

Mapear cada AG a requisito, archivo y evidencia real. Ejecutar pruebas focalizadas de consulta, permisos/payload, estado y fuente; QA visual y funcional con datos sintéticos en los cinco anchos, teclado y contraste medido; build si cambia frontend. Cualquier mutación autorizada de otros roles conserva su propia suite y sus pruebas de transacción/concurrencia en base desechable. No usar resultados históricos de una suite como si se hubieran ejecutado en esta tarea.

El incremento documental es de 24 escenarios: Gerencia suma **164 escenarios especificados**, no ejecutados durante estas tareas de especificación. La verificación actual comprueba archivos, enlaces, IDs y PNG; no certifica funcionamiento productivo.

## Verificación documental del 26/10/2026

- `node output/auditoria-integral-20261025/verificar-alertas-gerencia.mjs`: PASS; cuatro documentos, doce datos, diez controles, 24 casos únicos, 55 enlaces locales y dos PNG de 1536 × 1024 con SHA-256 verificado.
- `node output/auditoria-integral-20261025/auditar-enlaces-documentales.mjs alertas-gerencia`: 539 documentos, 4148 enlaces locales, cero destinos inexistentes y cero duplicados exactos en este corte.
- `node output/auditoria-integral-20261025/verificar-residentes-ocupacion.mjs`: PASS de la documentación anterior; el comprobador de ese bloque valida su subtotal de 24 casos y enlace de aceptación, sin fijar el total acumulado que cambia al documentar otro bloque.
- `git diff --check -- docs/especificacion-funcional docs/CHANGELOG.md`: sin errores. No se ejecutaron suites backend/frontend, build o pruebas de BDD porque no se modificó producto. No se realizaron commit, push, despliegue ni envío de notificaciones en esta tarea.
