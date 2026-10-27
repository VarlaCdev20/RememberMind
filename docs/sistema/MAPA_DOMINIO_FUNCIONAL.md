---
title: "Mapa funcional y límites de RememberMind"
status: CURRENT
version: "2.0"
last_reviewed: 2026-10-25
owner: RememberMind
source_of_truth: false
source_of_truth_scope: [domain_mapping]
verified_against_commit: 6d7b8c6d6bc03eecd2ee35add04677f2778272cd
verification_scope: DOCUMENTATION_RECONCILIATION_WITH_LOCAL_AUDIT
runtime_verified: false
---

# Mapa funcional y límites de RememberMind

Revisión de fuentes y localizadores del **25/10/2026**. Doce propietarios de código; residente como identidad central. No se impone un aggregate técnico universal ni una nueva arquitectura. [ADR aprobado](../arquitectura/ADR_ARQUITECTURA_HIBRIDA.md) y [contratos por módulo](../modulos/README.md).

## Identidades y recorrido institucional

Postulante/adulto mayor ≠ residente ≠ usuario ≠ personal ≠ contacto. La aprobación de preadmisión no crea residente.

`Preadmisión PENDIENTE → revisión → APROBADA/RECHAZADA → admisión formal con cama → residente ADMITIDO`

El inventario actual está en [BDD](../base-de-datos/README.md): 100 tablas de negocio, 16 técnicas; el núcleo de 70 y otros cortes no son inventarios actuales.

## Propietarios y continuidad

| Propietario | Responsabilidad y recorrido |
|---|---|
| Admisiones | [Gestionar preadmisión, revisión y admisión formal con cama, sin crear residentes al aprobar la solicitud.](../../app/Modulos/Admisiones/README.md) |
| Residentes | [Mantener la identidad residencial y el acceso longitudinal al expediente, alojamiento, contactos y documentos autorizados.](../../app/Modulos/Residentes/README.md) |
| Identidad | [Gestionar cuentas, autenticación, roles, permisos, documentación de acceso e identidad del personal.](../../app/Modulos/Identidad/README.md) |
| Institucion | [Organizar áreas, habitaciones, camas, contexto laboral, turnos y asignaciones para la operación institucional.](../../app/Modulos/Institucion/README.md) |
| Clinica | [Documentar y consultar actos clínicos, antecedentes, diagnósticos, estudios y seguimientos con su autoría y fecha.](../../app/Modulos/Clinica/README.md) |
| Cuidados | [Dar continuidad al cuidado por turno: residentes asignados, registros, intervenciones, pendientes y pase de turno.](../../app/Modulos/Cuidados/README.md) |
| Medicacion | [Separar medicamento, prescripción, horarios y administración/omisión, manteniendo la orden médica y su trazabilidad.](../../app/Modulos/Medicacion/README.md) |
| Valoraciones | [Capturar valoraciones profesionales e instrumentos originales con condiciones, mediciones, autoría y procedencia.](../../app/Modulos/Valoraciones/README.md) |
| Social | [Coordinar actividades, participación, contactos, visitas y red de apoyo dentro de permisos y vínculos institucionales.](../../app/Modulos/Social/README.md) |
| Alertas | [Mantener detección y ciclo de atención de alertas con fuente verificable, responsables y eventos históricos.](../../app/Modulos/Alertas/README.md) |
| Reportes | [Consultar y exportar información institucional autorizada, conservando filtros, significado y privacidad.](../../app/Modulos/Reportes/README.md) |
| ApoyoDecision | [Integrar fuentes y motor simbólico existente para explicar cinco dominios, hallazgos y perfiles orientativos Petersen o abstenciones.](../../app/Modulos/ApoyoDecision/README.md) |

## Interacción entre procesos

- Identidad autentica y limita acceso; Institución aporta contexto laboral y alojamiento. Un permiso no reemplaza profesión ni relación con el residente.
- Admisiones formaliza residente y ocupación; Residentes integra navegación del expediente sin absorber todas las escrituras clínicas.
- Clínica y Valoraciones conservan actos y fuentes originales; Medicación mantiene orden, horarios y administración separados.
- Cuidados utiliza el contexto autorizado, documenta actuaciones y comunica pendientes en el pase.
- Alertas conserva origen y eventos de atención; un toast o contador no resuelve la alerta.
- Social mantiene actividades y vínculos autorizados; Reportes respeta el mismo alcance de datos al consultar/exportar.
- ApoyoDecision extrae fuentes autorizadas, conserva estados de ausencia/contradicción y aplica el conocimiento versionado en el motor PHP. No crea mediciones para conseguir una salida.

Los eventos persistidos de alerta, Events/Listeners Laravel y Activitylog son conceptos diferentes. No se presupone que una fuente clínica dispare inferencia o alerta automática porque exista un adaptador.

## Reglas compartidas

[Invariantes](INVARIANTES_NEGOCIO.md), [estados por entidad](ESTADOS_Y_CICLOS_DE_VIDA.md), [continuidad](CONTINUIDAD_ASISTENCIAL.md) y [autorización](../seguridad/MODELO_AUTORIZACION.md). Sus inventarios estáticos históricos requieren lectura por fecha y alcance, no nuevos estados por analogía.

## Estado de aceptación

[Estado auditado](../ESTADO_ACTUAL.md) y [hallazgos reproducidos](../auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md). Este mapa reemplaza los localizadores anteriores de app/Backend y app/Http/Controllers, sin declarar corregidos los defectos de producto ni modificar reglas.
