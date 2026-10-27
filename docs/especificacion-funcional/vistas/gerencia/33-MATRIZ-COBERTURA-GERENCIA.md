---
title: "Gerencia: matriz de cobertura documental y pendientes"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: DOCUMENTATION_COVERAGE_AND_STATIC_IMPLEMENTATION_MAPPING
runtime_verified: false
---

# Cobertura y pendientes de Gerencia

Esta matriz reúne las fichas de Gerencia documentadas hasta el 26/10/2026. **Cerrar una ficha documental no significa terminar el módulo de aplicación.** Las decisiones de la propietaria gobiernan su alcance; DRAFT en las fichas de diseño distingue propuesta de presentación/implementación de decisión funcional adoptada. Esta matriz no crea facultades, reglas clínicas o DDL.

**Sucesión de cortes:** la matriz siguiente conserva la planificación inicial. El [informe 36](36-RENOVACION-GERENCIA-IMPLEMENTACION-Y-PRUEBAS.md) acredita la renovación y el [informe 38](38-INTEGRACION-CONTACTOS-PLANIFICACION-Y-ACEPTACION.md) añade Familiares/contactos, cuenta por enlace, DA01 y las protecciones históricas/concurrentes. La instalación institucional de los diseños 35/37 y la aceptación completa se distinguen del código probado en bases desechables. No interpretar las celdas históricas «implementar» como ausencia del código posterior.

## 1. Fuentes y lectura de estados

**Cierre posterior — 27/10/2026:** [informe40](40-INSTALACION-AUTORIZADA-Y-PRORROGA-EXPEDIENTE.md) acredita instalación35/37 y P19; [informe41](41-ALTAS-Y-SUSTITUCIONES-CONCURRENTES.md) cierra P03/P17 en su alcance probado. Las celdas de planificación inicial conservan su corte y no prevalecen sobre estas evidencias.

[Rol](../../roles/gerencia.md), [plan y registro](../../roles/GERENCIA_PLAN_VISTAS.md), [decisiones 15–29](../../roles/GERENCIA_CUESTIONARIO_PENDIENTE.md), [R01–R03 adoptadas](07-DECISIONES-RESIDUALES-PERSONAL.md), [baseline de roles](../../../arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md) y [auditoría crítica](../../../auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md). Las pruebas localizadas son evidencia de código, no ejecución nueva. Un informe anterior solo acredita su fecha/alcance.

«Especificado» significa campos, acciones, fuentes y límites documentados. «Código localizado» significa que existe una pieza reutilizable, no que cumple todo el contrato. «Pendiente de implementación/verificación» no implica reconstruir lo ya existente. No asignar un porcentaje global de avance sumando archivos o casos no ejecutados.

## 2. Matriz por flujo

| Flujo | Decisión o límite vigente | Ficha/propietario | Estado y cierre necesario |
|---|---|---|---|
| Inicio | Personal/Ocupación/Alertas equilibrados y acceso directo, pendientes compactos 28A | [01](01-INICIO.md), Reportes con fuentes de otros módulos | Composición especificada; integrar destinos/datos y verificar ausencia/error, roles y tres ámbitos |
| Personal/listado | Separado de Familiares; trabajador no equivale a usuario | [02](02-PERSONAL.md), [04](04-FICHA-Y-OPERACIONES-PERSONAL.md), Identidad | Componentes/consultas localizados; implementar operaciones acordadas y alcance efectivo |
| Alta de trabajador | Ficha+cuenta atómicas; cuatro pasos, mínimo documental y cuenta activa al completar | [03](03-ALTA-TRABAJADOR.md), Identidad | Completar circuito, validación, rollback y primer acceso; adjuntar no acredita aprobación del documento |
| Cuentas y recuperación | Ocho roles destino, excluye Gerencia/Superadministración; no cambia rol/permisos | [02](02-PERSONAL.md), [04](04-FICHA-Y-OPERACIONES-PERSONAL.md), Identidad | Alta/mantenimiento/recuperación con backend y negativos por multirrol; usar evidencia actual del flujo, no asumir por botón |
| Documentación laboral | Siete días, una prórroga de siete, avisos y revisión; vencimiento no suspende automáticamente | [05](05-DOCUMENTACION-LABORAL.md), [07](07-DECISIONES-RESIDUALES-PERSONAL.md), Identidad | Resolver fecha técnica, plazos/autorías y catálogos; verificar representación SQL antes de DDL |
| Solicitud por rol | R01A con petición expresa, sin convertir todos los documentos en obligatorios | [08](08-SOLICITUD-DOCUMENTAL-POR-ROL.md), Identidad | Formulario especificado; propuesta física concreta si modelo no representa petición/destinatarios/entregas |
| Familiares/contactos | Vínculo, autorización de información/salida y cuenta son distintos; presentaciones por contacto/residente | [09](09-FAMILIARES-Y-CONTACTOS.md), [10](10-FORMULARIOS-FAMILIARES-PRIVACIDAD.md), Residentes/Identidad | Conciliar entradas, historia y defaults; no publicar clínica por activar cuenta Familiar |
| Áreas y equipo | Entrada única 20A; equipo por asignación contextual | [12](12-AREAS-Y-EQUIPO.md), Institucion | Consolidar navegación y fuentes; DA01/DA02 delimitan decisiones que no se presuponen |
| Planificación | Semana inicial/mes alternativo, revisión, avisos internos 21A/23A | [14](14-PLANIFICACION-DEL-EQUIPO.md), Institucion | Implementar alcance aprobado con seguridad, vigencias, cruces de medianoche e historia, sin confundir plan con asistencia |
| Cobertura | Tabla por fecha/área/turno 22A, personas distintas | [15](15-COBERTURA-DEL-EQUIPO.md), Institucion | Destino propio de consulta; catálogo de turnos no acredita cobertura, cero filas no declara déficit |
| Residentes | Tarjetas iniciales y tabla 29B, consulta administrativa | [18](18-RESIDENTES-GERENCIA.md), Residentes | Reutilizar directorio sin PK visibles, verificar scope/contactos contradictorios y detalle autorizado |
| Ocupación | Mapa+tabla 24A/B; disponible requiere habitación/cama habilitadas | [19](19-OCUPACION-HABITACIONES-CAMAS.md), Institucion/Admisiones | [H04 corregido y probado](34-CORRECCION-ACCESO-OCUPACION-H04.md), sin grants nuevos; siguen historia/estados y QA visual pendientes |
| Alertas | Bandeja única 25A, supervisión sin intervención por implicación | [21](21-ALERTAS-GERENCIA.md), [22](22-DETALLE-TRAZABILIDAD-ALERTAS.md), Alertas | Integrar composición y ámbitos de resumen/filtros; proteger fuentes, notificaciones y eventos distintos |
| Reportes | PDF/CSV de los mismos agregados 26B/C | [25](25-REPORTES-GERENCIA.md), [26](26-EXPORTACION-REPORTES-GERENCIA.md), Reportes | Proyección gerencial/CSV equivalentes, privacidad, stock/período, auditoría y coherencia consulta/descarga |
| Galería | Entrada directa 27B, gestión privada ya aprobada el 17/10 | [29](29-GALERIA-GERENCIA.md), [30](30-CARGA-PRIVACIDAD-GALERIA.md), Institucion | Código y pruebas existentes; ajustes de diseño/confirmaciones, conflicto de edición y QA no reejecutados |

Gerencia no recibe diagnóstico, prescripción, valoración cognitiva, firma clínica o gestión experta por supervisar. No se amplía a los doce módulos mediante una entrada de menú. Administración conserva operación diaria; Superadministración lectura global y responsabilidades técnicas con límites vigentes.

## 3. Casos de aceptación especificados

| Bloque | Documento | Casos |
|---|---|---|
| Personal/documentación | [06](06-ACEPTACION-PERSONAL-DOCUMENTOS.md) y [08](08-SOLICITUD-DOCUMENTAL-POR-ROL.md) | 36 + 8 = 44 |
| Familiares/contactos | [11](11-ACEPTACION-FAMILIARES-CONTACTOS.md) | 24 |
| Áreas/equipo | [13](13-ACEPTACION-AREAS-EQUIPO.md) | 20 |
| Planificación/cobertura | [16](16-ACEPTACION-PLANIFICACION-COBERTURA.md) | 28 |
| Residentes/ocupación | [20](20-ACEPTACION-RESIDENTES-OCUPACION.md) | 24 |
| Alertas | [23](23-ACEPTACION-ALERTAS-GERENCIA.md) | 24 |
| Reportes | [27](27-ACEPTACION-REPORTES-GERENCIA.md) | 24 |
| Galería | [31](31-ACEPTACION-GALERIA-GERENCIA.md) | 24 |
| Total | Casos especificados, no ejecutados durante estas tareas documentales | **212** |

No se incorporan los «104 escenarios» del sistema experto a este total: tienen otro alcance. Al implementar, cada caso necesita archivo/prueba/evidencia y positivos/negativos significativos, sin escribir pruebas que solo repitan el código.

## 4. Diseño y accesibilidad

Ocho imágenes de propuesta en cuatro pares: [Planificación/cobertura](17-DISENO-PLANIFICACION-COBERTURA.md), [Alertas](24-DISENO-IMAGENES-ALERTAS.md), [Reportes](28-DISENO-IMAGENES-REPORTES.md), [Galería](32-DISENO-IMAGENES-GALERIA.md). Son imágenes estáticas ficticias, no evidencia de funcionamiento o WCAG.

Inicio, Personal, Familiares/contactos, Áreas y Residentes/ocupación tienen distribución documentada, pero este conjunto aún no tiene imágenes dedicadas equivalentes para todas esas superficies. No se declara cobertura visual total. Los componentes existentes son primera opción; no copiar las divergencias de marca/colores de una imagen generada.

Revisión real pendiente por pantalla y rol: 1440/1280/1024/768/390 px, teclado/foco, labels, contraste, temas, cargas/errores/ausencias, overlays y datos extensos. No esconder faltantes con espacios grandes o etiquetas técnicas; tampoco ocultar una limitación real cambiando únicamente el texto.

## 5. Decisiones y dependencias materiales

- **DA01 resuelta:** la propietaria adoptó bloqueo de desactivación con asignaciones o planes activos el 26/10/2026; consultar ficha 12 e informe 38. No altera lectura/edición segura ni crea cierres implícitos.
- **DA02:** pertenencia/jefatura permanente solo si la institución la requiere, no fabricarla desde asignaciones. La consulta contextual puede avanzar sin crear esa entidad.
- **Representación documental/cuentas:** los requisitos funcionales están adoptados; si faltan columnas/relaciones para fechas, plazos, peticiones o historia, presentar diccionario, impacto/reversión y solicitar aprobación DDL específica. No reabrir las decisiones 1–29 o R01–R03 ya registradas.
- **Estados y catálogos:** discrepancias observadas se resuelven por el contrato de mayor autoridad; una normalización material no se instala unilateralmente. Los nombres viejos no se introducen como dependencia nueva por citarlos en un hallazgo.
- **Mediciones/validación clínica:** no son requisito para diseñar estas vistas gerenciales ni habilitan un diagnóstico por exportar un indicador. Los límites clínicos del sistema experto conservan su propia documentación.

## 6. Secuencia recomendada de implementación y prueba

1. Convertir hallazgos de autorización/navegación directamente relacionados en cambios acotados: alcance de cuentas, H02/H03 de planificación y H04 de Ocupación, con sus pruebas negativas.
2. Completar Personal/cuentas/documentos y Familiares, preparando solo las propuestas estructurales necesarias y preservando fuentes/historia.
3. Integrar Áreas/planificación/cobertura y directorio/ocupación reutilizando consultas y Actions reales; no duplicar calendario o admisión.
4. Aplicar composiciones de Alertas y Reportes con proyecciones autorizadas, tablas/gráficas coherentes y exportación comprobada.
5. Ajustar Galería existente; luego integrar Inicio con los destinos y fuentes ya verificados.
6. Cerrar pruebas transversales por permisos/estado/rol y consumidores afectados, PostgreSQL desechable cuando proceda, build y QA visual real; actualizar esta matriz con comandos/resultados y pendientes residuales.

Esta secuencia es planificación de desarrollo, **no trabajo de producto ejecutado ni autorización de migraciones**. No exige firma profesional previa para programar comportamiento ya autorizado. Las nuevas decisiones materiales se aíslan y el resto continúa.

## 7. Estado al entregar esta documentación

Fichas principales del rol e imágenes de Galería incorporadas, cobertura y brechas reunidas. Implementación existente se conserva; no se corrigieron aplicaciones, datos, tablas o permisos durante esta tarea. No se ejecutaron suites backend/frontend, build o QA con sesión por documentar estas propuestas. Sin commit, push, migraciones o despliegue. Los cambios simultáneos del árbol se preservan.

**Avance de producto posterior, mismo 26/10:** [34](34-CORRECCION-ACCESO-OCUPACION-H04.md) implementa el cierre técnico del acceso H04 con 56 pruebas PHP/361 aserciones, dos JS y build. El párrafo anterior conserva el alcance de la entrega documental; no describe este hito de código. H02/H03, restantes implementaciones y QA con sesión continúan pendientes.
