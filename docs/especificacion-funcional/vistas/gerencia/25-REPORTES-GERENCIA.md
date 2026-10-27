---
title: "Gerencia: Reportes e indicadores agregados"
status: DRAFT
version: "1.0"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: OWNER_DECISION_AND_STATIC_REPORT_REVIEW
runtime_verified: false
---

# Reportes de Gerencia

## 1. Fuentes y contrato aplicable

La decisión **26B y C** permite consulta y PDF/CSV de los **mismos indicadores gerenciales agregados**, con autorización y auditoría. No incluye nombres, expedientes clínicos, documentos de identidad, archivos laborales, Excel o envío automático a terceros. [Decisión adoptada](../../roles/GERENCIA_CUESTIONARIO_PENDIENTE.md), [rol Gerencia](../../roles/gerencia.md), [responsabilidad de Reportes](../../../../app/Modulos/Reportes/README.md) y [baseline BDD](../../../base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md) gobiernan su alcance.

**Evidencia, no aprobación:** [ReporteInstitucionalController](../../../../app/Modulos/Reportes/Presentacion/Http/Controllers/ReporteInstitucionalController.php), [DashboardService](../../../../app/Modulos/Reportes/Aplicacion/Consultas/DashboardService.php), [ReportesInstitucionalesPanel](../../../../app/Modulos/Reportes/Presentacion/Livewire/Paginas/ReportesInstitucionalesPanel.php) y [ReportExportService](../../../../app/Modulos/Reportes/Infraestructura/Exportaciones/ReportExportService.php). Sus datos, grants y nombres no son automáticamente el contrato gerencial vigente.

Las imágenes de [28](28-DISENO-IMAGENES-REPORTES.md) son propuestas con datos ficticios; no acreditan integración ni generación real de archivos. Las exportaciones existentes de residentes, salud o usuarios no sustituyen el reporte agregado.

## 2. UX FLOW

Entrada: Gerencia → Reportes, o un acceso contextual autorizado desde Personal/Ocupación/Alertas. Objetivo: comprender situación, fuente y límites de los indicadores; revisar el conjunto antes de descargarlo. Recorrido: elegir sección → revisar referencia y alcance → consultar tabla/gráficas → abrir definición o detalle agregado → preparar exportación → revisar formato/contenido → descargar o cancelar → volver conservando contexto.

La acción principal es «Preparar exportación» cuando la consulta es válida y el usuario está autorizado. No se llama «Generar diagnóstico», «Validar» o «Cerrar período» a una consulta. La aplicación no certifica un cierre histórico al crear un PDF.

## 3. Vista general y distribución

| Zona | Ubicación | Contenido y acción |
|---|---|---|
| Cabecera | Superior | «Reportes de Gerencia», descripción y Preparar exportación |
| Contexto | Bajo título | Situación actual, hora de consulta legible y ámbito; explicar si no es cierre histórico |
| Secciones | Una fila compacta | Resumen / Personal / Ocupación / Alertas; mismas responsabilidades que sus fuentes |
| Indicadores | Tres tarjetas | Personal activo, Camas ocupadas respecto a registradas, Alertas activas; detalle agregado |
| Gráficas | Debajo | Dos barras horizontales como máximo en Resumen: situación de camas y estados de alertas; cantidad y unidad |
| Tabla | Bajo las gráficas | Indicador, Valor, Unidad y Referencia; alternativa accesible y contenido exportable |
| Definiciones | Acción secundaria | Fórmula, fuente, inclusión/exclusión, fecha y límite; sin IDs técnicos |

Personal distingue trabajadores de cuentas. Ocupación distingue camas físicas, ocupaciones y habitaciones habilitadas. Alertas distingue estado actual, prioridad y eventos; no representa enfermedades. El detalle de cada sección sigue agregado: los permisos de lectura nominal de otros módulos no se transfieren a la exportación por implicación.

## 4. Datos y filtros de presentación

| ID | Dato/control de contexto | Origen y validación |
|---|---|---|
| RP01 | Sección del reporte | Conjunto permitido Resumen/Personal/Ocupación/Alertas; entrada validada en backend |
| RP02 | Tipo de referencia | Situación actual o movimientos de un período realmente soportado; no intercambiables |
| RP03 | Fecha/hora de consulta | Reloj técnico y zona de la institución, legible; no se introduce como fecha clínica |
| RP04 | Desde/hasta cuando proceda | Solo en un reporte temporal sustentado; intervalo validado y frontera temporal definida |
| RP05 | Ámbito | Institucional autorizado y filtros pertinentes; no mostrar instituciones/residentes fuera de alcance |
| RP06 | Área/piso/estado pertinentes | Catálogo real según sección; no aplicar un filtro a unas cifras y aparentar que afecta todas |
| RP07 | Nombre del indicador | Etiqueta humana y definición estable, sin PK ni nombre de tabla visible |
| RP08 | Valor | Número finito con tipo/unidad; ausencia explícita, sin convertir desconocido en cero |
| RP09 | Unidad | Personas, camas, alertas o movimientos; no sumarlas entre sí |
| RP10 | Fuente y definición | Consulta existente autorizada, reglas de inclusión, versión técnica de cálculo cuando esté disponible |
| RP11 | Calidad y exclusiones | Datos incompletos, estado no interpretado o vínculos inconsistentes; no ocultarlos al exportar |
| RP12 | Formato de salida | PDF/CSV del mismo conjunto, con permiso revalidado; no activa XLSX por disponibilidad técnica |

En Resumen actual no se presenta un filtro de fechas que aparente modificar stock pasado. Si hay sección temporal válida, los controles desde/hasta se muestran solo allí. Pertenencia actual a un área por asignación no demuestra pertenencia laboral histórica. La falta de filtro aplicable se explica; no se aplica silenciosamente una fecha distinta a cada bloque.

## 5. Matriz de indicadores y significado

| Indicador | Grano/unidad | Inclusión y exclusión | Representación y límites |
|---|---|---|---|
| Personal activo | Trabajador distinto, personas | personal en estado laboral activo conforme a contrato; no contar roles o cuentas como personas | Tarjeta/tabla. Cuenta suspendida y trabajador inactivo son estados diferentes |
| Camas registradas | Cama física distinta, camas | Inventario real vinculado a habitación; no capacidad declarada transformada en filas ficticias | Denominador visible, no confundir con habitaciones o camas habilitadas |
| Camas ocupadas | Cama distinta con ocupación vigente | Relación válida residente/admisión/cama; una ocupación activa por cama/residente | No inferir estancia actual de una ocupación antigua |
| Camas disponibles | Cama distinta disponible, camas | Regla canónica de admisión: cama y habitación habilitadas, sin ocupación vigente | Vacía no equivale a disponible |
| Camas no habilitadas | Cama sin ocupación vigente y no disponible, camas | Partición técnica solo con integridad confirmada | Inconsistencias se señalan; no ajustar cifras para hacerlas sumar |
| Alertas activas | Alerta distinta, alertas | Estados activos del contrato actual, cuenta/ámbito autorizados | Conteo, no cantidad de residentes con enfermedad |
| Alertas por estado | Alerta distinta por estado excluyente | Agrupar valores originales con traducción documentada | «Por atender» puede incluir reconocidas: no sumarlo otra vez con ellas |

No añadir semáforos de «Al día» por existir una atención, «Normal» por tener una aplicación instrumental o riesgo individual a partir de conteos. No hay pesos, umbrales ni nuevos criterios médicos en este reporte.

## 6. Botones, gráficas y acceso

| ID | Control | Resultado |
|---|---|---|
| RP-A01 | Resumen/Personal/Ocupación/Alertas | Cambia conjunto agregado, conserva únicamente filtros compatibles y anuncia el cambio |
| RP-A02 | Aplicar filtros | Valida y consulta; sin crear registros o cambiar estado de fuentes |
| RP-A03 | Limpiar | Restablece filtros de sección, no borra datos |
| RP-A04 | Actualizar | Consulta estado actual, revalida autorización y cambia hora visible; no actualiza un cierre histórico inexistente |
| RP-A05 | Ver detalle | Abre desglose agregado con su fuente; no listado nominal por defecto |
| RP-A06 | Consultar definición y fuente | Abre panel de lectura con fórmula/alcance/exclusiones; sin SQL o códigos internos |
| RP-A07 | Preparar exportación | Abre [preparación](26-EXPORTACION-REPORTES-GERENCIA.md) del conjunto consultado válido |
| RP-A08 | PDF/CSV | Selección de formato, radio accesible; no ejecuta descarga por seleccionar |
| RP-A09 | Descargar formato | Reautoriza y genera respuesta con el mismo alcance y contenido confirmado |
| RP-A10 | Cancelar/Volver al reporte | Retorna a consulta conservando filtros; no crea archivo público ni registro de cierre |

Usar [RMCharts](../../../../resources/frontend/styles/design-system/charts/README.md), no otra librería/CDN ni servicios externos. Barras con cero visible solo si la fuente comprobó cero; ausencia con mensaje. Un dato aislado no es tendencia. Las series temporales requieren hechos históricos conservados, no stock actual repetido hacia atrás. Todas las gráficas tienen tabla de valores y etiquetas/unidad; no radar, metas o porcentajes inventados.

## 7. Brechas concretas localizadas

1. [ReporteInstitucionalController](../../../../app/Modulos/Reportes/Presentacion/Http/Controllers/ReporteInstitucionalController.php) incluye salud, red familiar, descripciones de alertas y bitácora; exporta PDF/XLSX, sin método CSV agregado institucional equivalente en esa clase. Se requiere proyección gerencial permitida; no reutilizar el payload completo ocultando bloques en Blade.
2. [DashboardService](../../../../app/Modulos/Reportes/Aplicacion/Consultas/DashboardService.php) mezcla conteos de personal con usuarios por rol y trae solo cuatro alertas a una lista. Esa lista limitada no puede ser el total. Pedagogía tampoco debe quedar excluida por un listado parcial de profesiones cuando se afirma contar todo el equipo.
3. [ReportesInstitucionalesPanel](../../../../app/Modulos/Reportes/Presentacion/Livewire/Paginas/ReportesInstitucionalesPanel.php) filtra población por admisiones y consulta otras fuentes sin el mismo período; usa dos `whereHas` separados para extremos del intervalo. Una misma admisión debe cumplir ambos límites si ese es el filtro declarado. Además inicializa todas las aplicaciones como nivel NORMAL: esa equivalencia no está autorizada y no se adopta.
4. [ReporteDataService](../../../../app/Modulos/Reportes/Aplicacion/Consultas/ReporteDataService.php) deriva una fecha de ingreso desde primera asignación; esa fecha no sustituye una fecha laboral original. [AreasReportDataService](../../../../app/Modulos/Reportes/Aplicacion/Consultas/AreasReportDataService.php) agrupa usuarios vinculados, no necesariamente trabajadores únicos con pertenencia histórica al área.
5. [ReporteV2Controller](../../../../app/Modulos/Reportes/Presentacion/Http/Controllers/ReporteV2Controller.php) ofrece CSV nominal de residentes con documento y código: no es el CSV agregado autorizado por 26B/C.

Son hallazgos estáticos. No se corrigió código, estructura, permisos o datos en esta tarea. Las rutas actuales de [Reportes](../../../../app/Modulos/Reportes/Presentacion/Rutas/web.php) y el [sidebar](../../../../app/Modulos/Identidad/Presentacion/Navegacion/SidebarService.php) llevan a `admin.reportes.institucional.preview`; integrar el alcance nuevo requiere comprobar todos sus consumidores, no reemplazar los reportes de otros roles indiscriminadamente.
