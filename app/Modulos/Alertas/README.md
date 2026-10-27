---
title: "Responsabilidad funcional — Alertas"
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

# Alertas

**Revisión documental: 25/10/2026.** Esta compilación localiza responsabilidades y comportamiento exigido por contratos existentes; no crea permisos, estados, umbrales o estructura. Las fuentes específicas prevalecen. Documentar el recorrido no significa que todos sus pasos hayan superado aceptación.

## Propósito y actores

Mantener detección y ciclo de atención de alertas con fuente verificable, responsables y eventos históricos.

Profesional autorizado según origen, competencia, residente y acción; Administración coordina únicamente lo permitido.

## Recorrido que debe funcionar

1. Recibir un registro confirmado o consultar una alerta existente.
2. Aplicar exclusivamente una regla vigente y aprobada para ese origen.
3. Guardar alerta y eventos requeridos de forma coherente e idempotente.
4. Atender, seguir y cerrar únicamente desde estado y competencia permitidos.
5. Conservar origen y continuidad hacia expediente y pase de turno.

## Entradas y salidas

- **Entradas:** Fuente confirmada, regla vigente o acción de atención autorizada.
- **Salidas:** Alerta y eventos consultables, con seguimiento y cierre trazables.

## Invariantes y autorización

- Una notificación o preview no equivale a alerta persistida ni a resolución clínica.
- Estado y eventos de alerta se conservan separados.
- No crear severidades ni umbrales desde colores del frontend.
- En operaciones sensibles: sesión y cuenta activa + permiso explícito + Policy/contexto + estado válido + relación/competencia. Ocultar un botón no es suficiente.

## Persistencia y continuidad

Usar Models y relaciones existentes conforme al [baseline](../../../docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md) y decisiones aplicables. Mantener autoría, fecha clínica y registro técnico cuando el contrato los diferencia. Corregir por el mecanismo longitudinal del dominio; no borrar historia para ocultar errores. Una operación institucional multientidad conserva atomicidad e idempotencia cuando corresponda.

## Fuentes y ubicación real

- [Instrucciones institucionales](../../../AGENTS.md) y [backend](../../AGENTS.md).
- [docs/modulos/alertas/README.md](../../../docs/modulos/alertas/README.md)
- [docs/modulos/alertas/REPRESENTACIONES_FUNCIONALES_2026_10_22.md](../../../docs/modulos/alertas/REPRESENTACIONES_FUNCIONALES_2026_10_22.md)
- [app/Modulos/Alertas/Aplicacion/Acciones/AlertasService.php](Aplicacion/Acciones/AlertasService.php)
- [app/Modulos/Alertas/Aplicacion/Acciones/DeteccionAlertasService.php](Aplicacion/Acciones/DeteccionAlertasService.php)
- [app/Modulos/Alertas/Presentacion/Http/Controllers/AlertaController.php](Presentacion/Http/Controllers/AlertaController.php)
- [Registro de rutas del propietario](Presentacion/Rutas/web.php)

Dominio conserva reglas puras; Aplicacion coordina operaciones/consultas; Infraestructura adapta persistencia e integraciones; Presentacion contiene HTTP, Livewire, Policies y Blade. Modelos Eloquent compartidos mantienen su identidad en app/Models. Una carpeta o clase existente no acredita por sí sola un proceso completo.

## Interfaz y errores

Identificar persona/contexto y acción principal. Diferenciar sin registros, sin resultados de búsqueda, acceso restringido, validación, error técnico y no interpretabilidad. Formularios con errores junto al campo, carga y confirmación real; detalle progresivo y navegación de regreso. Los códigos internos no sustituyen explicaciones clínicas. [Contrato visual compartido](../../../docs/frontend/CONTRATO_VISUAL_UX_UI.md).

## Pruebas localizadas y aceptación

- [tests/Feature/AlertasFlujoTest.php](../../../tests/Feature/AlertasFlujoTest.php)
- [tests/Feature/AlertasConcurrenciaPostgresTest.php](../../../tests/Feature/AlertasConcurrenciaPostgresTest.php)

Estas pruebas están localizadas, no reejecutadas por la limpieza documental. [El estado actual](../../../docs/ESTADO_ACTUAL.md) y [la auditoría](../../../docs/auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) indican lo realmente ejecutado. Cierre: camino positivo y negativos relevantes, permisos revocados, fuentes ajenas, integridad, recuperación de errores y recorrido visual comprobados; PostgreSQL para concurrencia.

## Brechas comprobadas

Pruebas seleccionadas de flujo y concurrencia son evidencia acotada; falta comparar todas las entradas HTTP/Livewire, canales y estados por rol.
