---
title: "Responsabilidad funcional — Cuidados"
status: CURRENT
version: "2.0"
last_reviewed: 2026-10-25
owner: RememberMind
source_of_truth: false
source_of_truth_scope: [module_responsibility]
verified_against_commit: 6d7b8c6d6bc03eecd2ee35add04677f2778272cd
verification_scope: DOCUMENTATION_RECONCILIATION_WITH_LOCAL_AUDIT
runtime_verified: false
---

# Cuidados

**Revisión documental: 25/10/2026.** Esta compilación localiza responsabilidades y comportamiento exigido por contratos existentes; no crea permisos, estados, umbrales o estructura. Las fuentes específicas prevalecen. Documentar el recorrido no significa que todos sus pasos hayan superado aceptación.

## Propósito y actores

Dar continuidad al cuidado por turno: residentes asignados, registros, intervenciones, pendientes y pase de turno.

Enfermería dentro de guardia y residentes autorizados; otros profesionales ejecutan solo intervenciones permitidas por competencia.

## Recorrido que debe funcionar

1. Abrir jornada y comprobar personal, horario y asignación.
2. Consultar Mis residentes y resumen autorizado.
3. Registrar observaciones y actuaciones con fecha y contexto.
4. Documentar administración desde Medicación, sin alterar la orden.
5. Conservar pendientes, incidentes y continuidad en el pase de turno.

## Entradas y salidas

- **Entradas:** Jornada, asignaciones, indicaciones, observaciones y actuaciones.
- **Salidas:** Registros de cuidado, ejecución y pase longitudinal sin duplicados indebidos.

## Invariantes y autorización

- Acceso limitado por residente/turno cuando aplica.
- Orden, programación y ejecución son hechos distintos.
- El siguiente lector autorizado recibe pendientes reales, no éxito ficticio.
- En operaciones sensibles: sesión y cuenta activa + permiso explícito + Policy/contexto + estado válido + relación/competencia. Ocultar un botón no es suficiente.

## Persistencia y continuidad

Usar Models y relaciones existentes conforme al [baseline](../../../docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md) y decisiones aplicables. Mantener autoría, fecha clínica y registro técnico cuando el contrato los diferencia. Corregir por el mecanismo longitudinal del dominio; no borrar historia para ocultar errores. Una operación institucional multientidad conserva atomicidad e idempotencia cuando corresponda.

## Fuentes y ubicación real

- [Instrucciones institucionales](../../../AGENTS.md) y [backend](../../AGENTS.md).
- [docs/modulos/pase-turno/README.md](../../../docs/modulos/pase-turno/README.md)
- [docs/modulos/jornadas-asignaciones/README.md](../../../docs/modulos/jornadas-asignaciones/README.md)
- [app/Modulos/Cuidados/Aplicacion/Consultas/TurnoEnfermeriaService.php](Aplicacion/Consultas/TurnoEnfermeriaService.php)
- [app/Modulos/Cuidados/Aplicacion/Acciones/PaseTurnoService.php](Aplicacion/Acciones/PaseTurnoService.php)
- [app/Modulos/Cuidados/Presentacion/Http/Controllers/CuidadoController.php](Presentacion/Http/Controllers/CuidadoController.php)
- [Registro de rutas del propietario](Presentacion/Rutas/web.php)

Dominio conserva reglas puras; Aplicacion coordina operaciones/consultas; Infraestructura adapta persistencia e integraciones; Presentacion contiene HTTP, Livewire, Policies y Blade. Modelos Eloquent compartidos mantienen su identidad en app/Models. Una carpeta o clase existente no acredita por sí sola un proceso completo.

## Interfaz y errores

Identificar persona/contexto y acción principal. Diferenciar sin registros, sin resultados de búsqueda, acceso restringido, validación, error técnico y no interpretabilidad. Formularios con errores junto al campo, carga y confirmación real; detalle progresivo y navegación de regreso. Los códigos internos no sustituyen explicaciones clínicas. [Contrato visual compartido](../../../docs/frontend/CONTRATO_VISUAL_UX_UI.md).

## Pruebas localizadas y aceptación

- [tests/Feature/FichaCuidadosTest.php](../../../tests/Feature/FichaCuidadosTest.php)
- [tests/Feature/DashboardEnfermeriaIntegracionVisualTest.php](../../../tests/Feature/DashboardEnfermeriaIntegracionVisualTest.php)

Estas pruebas están localizadas, no reejecutadas por la limpieza documental. [El estado actual](../../../docs/ESTADO_ACTUAL.md) y [la auditoría](../../../docs/auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) indican lo realmente ejecutado. Cierre: camino positivo y negativos relevantes, permisos revocados, fuentes ajenas, integridad, recuperación de errores y recorrido visual comprobados; PostgreSQL para concurrencia.

## Brechas comprobadas

El contexto puede verse afectado por H03. La navegación auditada sin guardia no acredita todas las actuaciones clínicas del turno.
