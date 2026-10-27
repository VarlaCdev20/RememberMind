---
title: "Contratos y responsabilidades de los doce módulos"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-25
owner: RememberMind
source_of_truth: false
source_of_truth_scope: [module_routing]
verified_against_commit: 6d7b8c6d6bc03eecd2ee35add04677f2778272cd
verification_scope: DOCUMENTATION_RECONCILIATION_WITH_LOCAL_AUDIT
runtime_verified: false
---

# Módulos de RememberMind

La [especificación funcional detallada](../especificacion-funcional/README.md) se desarrolla por módulo, vista y rol. Esta entrada conserva los contratos y responsabilidades existentes; las nuevas fichas distinguen detalle pendiente e implementación observada.

Los doce propietarios de código se conservan. Roles y procesos no crean módulos nuevos. Cada entrada siguiente compila responsabilidad, recorrido, controles, fuentes, implementación y pruebas localizadas; no certifica las brechas de producto.

| Módulo | Propósito | Documento |
|---|---|---|
| Admisiones | Gestionar preadmisión, revisión y admisión formal con cama, sin crear residentes al aprobar la solicitud. | [Responsabilidad y recorrido](../../app/Modulos/Admisiones/README.md) |
| Residentes | Mantener la identidad residencial y el acceso longitudinal al expediente, alojamiento, contactos y documentos autorizados. | [Responsabilidad y recorrido](../../app/Modulos/Residentes/README.md) |
| Identidad | Gestionar cuentas, autenticación, roles, permisos, documentación de acceso e identidad del personal. | [Responsabilidad y recorrido](../../app/Modulos/Identidad/README.md) |
| Institucion | Organizar áreas, habitaciones, camas, contexto laboral, turnos y asignaciones para la operación institucional. | [Responsabilidad y recorrido](../../app/Modulos/Institucion/README.md) |
| Clinica | Documentar y consultar actos clínicos, antecedentes, diagnósticos, estudios y seguimientos con su autoría y fecha. | [Responsabilidad y recorrido](../../app/Modulos/Clinica/README.md) |
| Cuidados | Dar continuidad al cuidado por turno: residentes asignados, registros, intervenciones, pendientes y pase de turno. | [Responsabilidad y recorrido](../../app/Modulos/Cuidados/README.md) |
| Medicacion | Separar medicamento, prescripción, horarios y administración/omisión, manteniendo la orden médica y su trazabilidad. | [Responsabilidad y recorrido](../../app/Modulos/Medicacion/README.md) |
| Valoraciones | Capturar valoraciones profesionales e instrumentos originales con condiciones, mediciones, autoría y procedencia. | [Responsabilidad y recorrido](../../app/Modulos/Valoraciones/README.md) |
| Social | Coordinar actividades, participación, contactos, visitas y red de apoyo dentro de permisos y vínculos institucionales. | [Responsabilidad y recorrido](../../app/Modulos/Social/README.md) |
| Alertas | Mantener detección y ciclo de atención de alertas con fuente verificable, responsables y eventos históricos. | [Responsabilidad y recorrido](../../app/Modulos/Alertas/README.md) |
| Reportes | Consultar y exportar información institucional autorizada, conservando filtros, significado y privacidad. | [Responsabilidad y recorrido](../../app/Modulos/Reportes/README.md) |
| ApoyoDecision | Integrar fuentes y motor simbólico existente para explicar cinco dominios, hallazgos y perfiles orientativos Petersen o abstenciones. | [Responsabilidad y recorrido](../../app/Modulos/ApoyoDecision/README.md) |

## Contratos de proceso existentes

Un proceso puede cruzar varios propietarios; estos contratos se reutilizan en lugar de copiar sus reglas:

- [ingreso-institucional](ingreso-institucional/README.md)
- [residentes-alojamiento](residentes-alojamiento/README.md)
- [jornadas-asignaciones](jornadas-asignaciones/README.md)
- [medicacion](medicacion/README.md)
- [signos-vitales](signos-vitales/README.md)
- [alertas](alertas/README.md)
- [pase-turno](pase-turno/README.md)

[Plantilla detallada](_PLANTILLA_CONTRATO_MODULO.md) para ampliar un flujo concreto. Antes de añadir secciones, enlazar la fuente vigente y distinguir requisito aprobado, implementación observada, prueba definida/ejecutada y pendiente. No reproducir el diccionario físico ni inventar un catálogo de estados.

## Contrato transversal de pantalla y operación

1. Mostrar objetivo, actor y residente/persona/contexto.
2. Definir entrada y acción permitida; autorizar también rutas directas y llamadas Livewire.
3. Validar datos, relaciones, competencia y estado; informar fallos sin éxito ficticio.
4. Guardar con atomicidad/idempotencia necesarias y conservar autoría/fechas/historia.
5. Recuperar lo guardado y permitir al siguiente actor autorizado continuar.
6. Representar carga, vacío, restricción, error y éxito; tabla alternativa para gráficas.
7. Verificar positivos y negativos, concurrencia pertinente y recorrido visual.

## Estado y decisiones

[Estado actual](../ESTADO_ACTUAL.md) y [auditoría H01–H32](../auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) gobiernan la lectura de evidencia técnica actual. Los contratos clínicos/BDD y competencias mantienen su autoridad propia; esta compilación no amplía permisos ni activa reglas.
