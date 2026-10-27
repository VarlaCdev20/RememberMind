---
title: "Estado actual de RememberMind"
status: CURRENT
version: "2.1"
last_reviewed: 2026-10-27
owner: RememberMind
source_of_truth: false
source_of_truth_scope: [observed_state]
verified_against_commit: 6d7b8c6d6bc03eecd2ee35add04677f2778272cd
verification_scope: DOCUMENTATION_RECONCILIATION_WITH_LOCAL_AUDIT
runtime_verified: false
---

# Estado actual de RememberMind

**Corte: 25/10/2026, America/La_Paz.** Rama observada: `feature/apoyo-decision-informe-neurocognitivo`; HEAD `6d7b8c6d`, con cambios locales concurrentes. Este documento describe el árbol auditado; no certifica producción ni validación clínica. La limpieza posterior de Markdown no reejecuta ni transforma esas pruebas.

## Dictamen y prioridades

**Revisión para publicación — 27/10/2026:** se conserva el trabajo acumulado
de la rama y se actualiza su [informe de revisión](sistema/REVISION_PUBLICACION_2026_10_27.md).
Frontend final: 276 correctos y 4 omitidos; build correcto. La suite general
detecta fallos y no se presenta como aprobada. El informe distingue las
repeticiones correctas de Reportes, Medicación y acceso/correo, los artefactos
locales excluidos y la publicación de la rama frente a integración o despliegue.

**Cierre de concurrencia P03/P17 — 27/10/2026:** el [informe41](especificacion-funcional/vistas/gerencia/41-ALTAS-Y-SUSTITUCIONES-CONCURRENTES.md) implementa y verifica altas y sustituciones documentales concurrentes. Un único registro ante correo/CI repetidos; formulario documental obsoleto rechazado antes de guardar bytes; permisos revalidados después de esperar. Pasan62/446 SQLite,52/425 PostgreSQL (nueve carreras),15/121 de último ajuste de formulario, navegador de dos pestañas y build. Sin DDL ni datos institucionales nuevos; permanecen106 de negocio. PC14/PC15, reportes, RO18 y aceptación restante siguen pendientes. No se declara culminado todo Gerencia o el experto.

**Instalación autorizada de Gerencia — 27/10/2026:** el [informe40](especificacion-funcional/vistas/gerencia/40-INSTALACION-AUTORIZADA-Y-PRORROGA-EXPEDIENTE.md) acredita la decisión «autorizo», respaldo e instalación local de35/37: **106 de negocio +16 técnicas =122 físicas**. Contenido previo conservado, historia desconocida sin rellenar,90 restricciones validadas y cuatro triggers históricos habilitados. P19 usa una sola concesión por expediente sin duplicar actos; pasan30/229 SQLite,36/265 PostgreSQL, pruebas atómicas y navegador. Quedan los casos de aceptación expresamente listados; no se declara culminado todo Gerencia o el experto. Las entradas siguientes conservan su corte anterior de100 tablas y pendientes entonces vigentes.

**Aceptación adicional de Gerencia — 26/10/2026:** el [informe39](especificacion-funcional/vistas/gerencia/39-ACEPTACION-REGRESIONES-Y-CASOS.md) corrige mora documental retrospectiva, fotografía/sucesión/privacidad y edición obsoleta de Galería. Añade negativos de autorización, archivos inválidos, rollback, concurrencia y teclado real. Existe inventario reproducible de212 casos; el grupo ampliado309/2041 no equivale a aceptarlos todos. Inventario institucional y estado de aprobación35/37 permanecen sin cambios; la prórroga por alta frente a destinatario sigue pendiente de conciliación.

**Continuación acotada — 26/10/2026:** el [informe38 de Gerencia](especificacion-funcional/vistas/gerencia/38-INTEGRACION-CONTACTOS-PLANIFICACION-Y-ACEPTACION.md) integra contactos, cuenta Familiar por enlace y DA01; añade pruebas de historia/concurrencia y revisión independiente. Los cuatro permisos existentes de contactos de17B se aplicaron al rol GERENTE. Diseños35/37 siguen fuera de la base institucional:100 tablas de negocio, sin cambio estructural en este corte. La aceptación parcial ejecutada no acredita los212 escenarios ni modifica el dictamen clínico del experto.

**Actualización acotada — 26/10/2026:** la [renovación de Gerencia](especificacion-funcional/vistas/gerencia/36-RENOVACION-GERENCIA-IMPLEMENTACION-Y-PRUEBAS.md) implementa nuevos recorridos y corrige H02/H03 de horarios, además del H04 de Ocupación. La extensión aprobada de Personal suma cuatro tablas únicamente en bases desechables; el inventario institucional no se modificó. El informe enlaza pruebas SQLite/PostgreSQL, navegador y build, con Familiares/contactos y otras brechas aún abiertas. La tabla y los resultados siguientes conservan el corte de auditoría del 25/10; no se reafirman como fallos actuales los que el informe posterior demuestra corregidos.

[Auditoría integral: 32 hallazgos y brechas](auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md). No está aceptada la culminación institucional. Deben corregirse primero autorización, interpretación paralela en la ficha y coherencia de navegación.

| Área | Evidencia actual | Límite pendiente |
|---|---|---|
| Arquitectura | Doce módulos, cuatro capas y motor PHP independiente | No implica aceptación de todos los procesos |
| BDD | 75 operativas + 25 expertas = **100 de negocio**; 16 técnicas | Máximo de diseño 110; cualquier DDL concreto sigue su decisión específica |
| Seguridad | Dos defectos reproducidos: lectura revocada y asignación de horarios sin permiso | H01/H02 abiertos; sin corrección de producto en la auditoría o limpieza documental |
| Inferencia | Cinco dominios/19 componentes, reglas G09 y Petersen con abstención | Datos originales, métodos aplicables y trazabilidad condicionan cada resultado |
| Historia experta | Conservación observacional y revisión posterior comprobadas | La evaluación conjunta instrumental/Petersen continúa transitoria |
| Frontend | Componentes y pruebas parciales existentes | Ficha con integración divergente; revisión visual actual no ejecutada por bloqueo del navegador |

## Pruebas realmente ejecutadas en la auditoría del 25/10

| Grupo | Resultado |
|---|---|
| Unit completa | 1.616 casos, 19.845 aserciones; 1 fallo, 4 omitidos |
| Operación seleccionada | 109 casos, 1.047 aserciones; 1 error |
| Experto seleccionado | 146 casos, 1.967 aserciones; 0 fallos/errores, 5 omitidos |
| Navegación/HTTP | 11 pruebas, 14 aserciones; 89 enlaces observados, uno termina en 403 |
| Reproducción de defectos | 3 pruebas, 11 aserciones; pasan porque demuestran los fallos |
| PostgreSQL temporal | 8 casos, 114 aserciones; un error del arnés concurrente de medicación |
| JavaScript sin navegador | 234 pruebas satisfactorias en 31 archivos |
| Build Vite | Satisfactorio; advertencia de tamaño de chunks |

No sumar filtros solapados como cobertura única. No convertir omisiones en PASS. [Resultados locales JUnit](../output/auditoria-integral-20261025/RESUMEN_PRUEBAS.md) y [comandos exactos](../output/auditoria-integral-20261025/COMANDOS.md) acompañan el informe; esos archivos de output son evidencia local, no garantía de disponibilidad en otra copia.

## Fuentes vigentes y continuidad

- [Responsabilidades y recorrido por los doce módulos](modulos/README.md).
- [Inventario y decisiones estructurales](base-de-datos/README.md).
- [Contrato experto, integración y conservación](sistema-experto/README.md).
- [Modelo de autorización](seguridad/MODELO_AUTORIZACION.md); [contrato visual](frontend/CONTRATO_VISUAL_UX_UI.md).
- [Cortes anteriores hasta el 11/10](historico/ESTADO_CORTES_HASTA_2026_10_11.md): historia conservada, sin aceptar nuevamente el árbol actual por sus pruebas.

Las correcciones de software compatibles con contratos aprobados continúan sin exigir una nueva firma clínica para programar. No se fabrican mediciones ni aprobaciones. Producción y validación clínica requieren evidencias propias.
