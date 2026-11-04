---
title: "Responsabilidad funcional — Medicacion"
status: CURRENT
version: "2.2"
last_reviewed: 2026-11-04
owner: RememberMind
source_of_truth: false
source_of_truth_scope: [module_responsibility]
verified_against_commit: 57285c7e3988cf5df86cdbd24f03e6a9650807bf
verification_scope: CURRENT_TREE_DOCUMENTATION_AND_REGISTERED_ROUTES
runtime_verified: false
---

# Medicacion

## Estado localizado al 04/11/2026

El árbol contiene **15 rutas con acción de este módulo, 4 clases en Livewire y 15 plantillas Blade**. Las plantillas incluyen parciales y no equivalen a pantallas aceptadas. [Inventario reproducible](../../../docs/sistema/INVENTARIO_ACTUAL.md) y [estado del producto](../../../docs/ESTADO_ACTUAL.md). Esta actualización no reejecuta las pruebas citadas ni concede permisos.


**Revisión de estructura y fuentes:04/11/2026.** Esta compilación localiza responsabilidades y comportamiento exigido por contratos existentes; no crea permisos, estados, umbrales o estructura. Las fuentes específicas prevalecen. Documentar el recorrido no significa que todos sus pasos hayan superado aceptación.

## Propósito y actores

Separar medicamento, prescripción, horarios y administración/omisión, manteniendo la orden médica y su trazabilidad.

Medicina prescribe cuando está autorizada; Enfermería consulta y administra/documenta según orden, jornada y alcance.

## Recorrido que debe funcionar

1. Consultar o mantener el catálogo autorizado.
2. Registrar la prescripción médica con sus horarios.
3. Derivar la agenda y seleccionar una ocurrencia concreta vigente.
4. Registrar administración u omisión conforme al contrato y residente.
5. Conservar suspensiones, correcciones, auditoría y seguimiento.

## Entradas y salidas

- **Entradas:** Prescripción válida, horarios, residente y contexto del profesional.
- **Salidas:** Agenda y acto documentado, distinto de la orden original.

## Invariantes y autorización

- Enfermería no modifica fármaco, dosis o frecuencia de la orden.
- Una administración no usa prescripción de otro residente.
- Evitar duplicación de la misma ocurrencia; PRN mantiene su contrato específico sin inventar intervalos.
- En operaciones sensibles: sesión y cuenta activa + permiso explícito + Policy/contexto + estado válido + relación/competencia. Ocultar un botón no es suficiente.

## Persistencia y continuidad

Usar Models y relaciones existentes conforme al [baseline](../../../docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md) y decisiones aplicables. Mantener autoría, fecha clínica y registro técnico cuando el contrato los diferencia. Corregir por el mecanismo longitudinal del dominio; no borrar historia para ocultar errores. Una operación institucional multientidad conserva atomicidad e idempotencia cuando corresponda.

## Fuentes y ubicación real

- [Instrucciones institucionales](../../../AGENTS.md) y [backend](../../AGENTS.md).
- [docs/modulos/medicacion/README.md](../../../docs/modulos/medicacion/README.md)
- [app/Modulos/Medicacion/Aplicacion/Acciones/RegistrarAdministracionMedicacionService.php](Aplicacion/Acciones/RegistrarAdministracionMedicacionService.php)
- [app/Modulos/Medicacion/Aplicacion/Consultas/AgendaMedicacionService.php](Aplicacion/Consultas/AgendaMedicacionService.php)
- [app/Modulos/Medicacion/Presentacion/Policies/PrescripcionPolicy.php](Presentacion/Policies/PrescripcionPolicy.php)
- [Registro de rutas del propietario](Presentacion/Rutas/web.php)

Dominio conserva reglas puras; Aplicacion coordina operaciones/consultas; Infraestructura adapta persistencia e integraciones; Presentacion contiene HTTP, Livewire, Policies y Blade. Modelos Eloquent compartidos mantienen su identidad en app/Models. Una carpeta o clase existente no acredita por sí sola un proceso completo.

## Interfaz y errores

Identificar persona/contexto y acción principal. Diferenciar sin registros, sin resultados de búsqueda, acceso restringido, validación, error técnico y no interpretabilidad. Formularios con errores junto al campo, carga y confirmación real; detalle progresivo y navegación de regreso. Los códigos internos no sustituyen explicaciones clínicas. [Contrato visual compartido](../../../docs/frontend/CONTRATO_VISUAL_UX_UI.md).

## Pruebas localizadas y aceptación

- [tests/Feature/MedicacionV2Test.php](../../../tests/Feature/MedicacionV2Test.php)
- [tests/Feature/AdministracionMedicacionConcurrenciaPostgresTest.php](../../../tests/Feature/AdministracionMedicacionConcurrenciaPostgresTest.php)

Estas pruebas están localizadas, no reejecutadas por la limpieza documental. [El estado actual](../../../docs/ESTADO_ACTUAL.md) y [la auditoría](../../../docs/auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) indican lo realmente ejecutado. Cierre: camino positivo y negativos relevantes, permisos revocados, fuentes ajenas, integridad, recuperación de errores y recorrido visual comprobados; PostgreSQL para concurrencia.

## Hallazgos del corte anterior y seguimiento

H27/H28: fixture temporal inválida y arnés PostgreSQL con error. La garantía concurrente de esa ejecución no quedó acreditada. H25 incluye clases hover inválidas.
