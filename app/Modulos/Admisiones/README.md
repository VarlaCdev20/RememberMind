---
title: "Responsabilidad funcional — Admisiones"
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

# Admisiones

**Revisión documental: 25/10/2026.** Esta compilación localiza responsabilidades y comportamiento exigido por contratos existentes; no crea permisos, estados, umbrales o estructura. Las fuentes específicas prevalecen. Documentar el recorrido no significa que todos sus pasos hayan superado aceptación.

## Propósito y actores

Gestionar preadmisión, revisión y admisión formal con cama, sin crear residentes al aprobar la solicitud.

Administración u operador con permiso; profesionales competentes aportan las valoraciones autorizadas. Gerencia/Superadministración consultan según su alcance.

## Recorrido que debe funcionar

1. Registrar y consultar la preadmisión y sus documentos.
2. Revisar y resolver APROBADA/RECHAZADA desde el estado permitido.
3. Formalizar solo la solicitud aprobada, comprobando disponibilidad de cama dentro de la transacción.
4. Crear residente, admisión, vínculos, ocupación e historia como una unidad institucional.
5. Recuperar el expediente y continuar en Residentes/Institución.

## Entradas y salidas

- **Entradas:** Solicitud, identificación del postulante, revisión, documentación y cama apta.
- **Salidas:** Resolución de preadmisión o admisión formal con residente y ocupación coherentes.

## Invariantes y autorización

- Aprobar no crea residente; impedir alta directa alternativa.
- Residente sin dos ocupaciones activas; cama ocupada no asignable.
- Fallo crítico revierte todas las escrituras; doble envío no duplica admisión.
- En operaciones sensibles: sesión y cuenta activa + permiso explícito + Policy/contexto + estado válido + relación/competencia. Ocultar un botón no es suficiente.

## Persistencia y continuidad

Usar Models y relaciones existentes conforme al [baseline](../../../docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md) y decisiones aplicables. Mantener autoría, fecha clínica y registro técnico cuando el contrato los diferencia. Corregir por el mecanismo longitudinal del dominio; no borrar historia para ocultar errores. Una operación institucional multientidad conserva atomicidad e idempotencia cuando corresponda.

## Fuentes y ubicación real

- [Instrucciones institucionales](../../../AGENTS.md) y [backend](../../AGENTS.md).
- [docs/modulos/ingreso-institucional/README.md](../../../docs/modulos/ingreso-institucional/README.md)
- [app/Modulos/Admisiones/Aplicacion/Acciones/FormalizarAdmision.php](Aplicacion/Acciones/FormalizarAdmision.php)
- [app/Modulos/Admisiones/Presentacion/Http/Controllers/PreadmisionController.php](Presentacion/Http/Controllers/PreadmisionController.php)
- [app/Modulos/Admisiones/Presentacion/Http/Controllers/AdmisionController.php](Presentacion/Http/Controllers/AdmisionController.php)
- [Registro de rutas del propietario](Presentacion/Rutas/web.php)

Dominio conserva reglas puras; Aplicacion coordina operaciones/consultas; Infraestructura adapta persistencia e integraciones; Presentacion contiene HTTP, Livewire, Policies y Blade. Modelos Eloquent compartidos mantienen su identidad en app/Models. Una carpeta o clase existente no acredita por sí sola un proceso completo.

## Interfaz y errores

Identificar persona/contexto y acción principal. Diferenciar sin registros, sin resultados de búsqueda, acceso restringido, validación, error técnico y no interpretabilidad. Formularios con errores junto al campo, carga y confirmación real; detalle progresivo y navegación de regreso. Los códigos internos no sustituyen explicaciones clínicas. [Contrato visual compartido](../../../docs/frontend/CONTRATO_VISUAL_UX_UI.md).

## Pruebas localizadas y aceptación

- [tests/Feature/Fase3AdmisionAtomicidadTest.php](../../../tests/Feature/Fase3AdmisionAtomicidadTest.php)
- [tests/Feature/Fase3ConcurrenciaCamaPostgresTest.php](../../../tests/Feature/Fase3ConcurrenciaCamaPostgresTest.php)

Estas pruebas están localizadas, no reejecutadas por la limpieza documental. [El estado actual](../../../docs/ESTADO_ACTUAL.md) y [la auditoría](../../../docs/auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) indican lo realmente ejecutado. Cierre: camino positivo y negativos relevantes, permisos revocados, fuentes ajenas, integridad, recuperación de errores y recorrido visual comprobados; PostgreSQL para concurrencia.

## Brechas comprobadas

La auditoría comprobó casos de atomicidad; falta aceptación visual completa del ingreso y recuperación ante cada error. No se declara cierre de todos los campos del wizard.
