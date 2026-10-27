---
title: "Responsabilidad funcional — Reportes"
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

# Reportes

**Revisión documental: 25/10/2026.** Esta compilación localiza responsabilidades y comportamiento exigido por contratos existentes; no crea permisos, estados, umbrales o estructura. Las fuentes específicas prevalecen. Documentar el recorrido no significa que todos sus pasos hayan superado aceptación.

## Propósito y actores

Consultar y exportar información institucional autorizada, conservando filtros, significado y privacidad.

Cada rol por permiso y alcance; Familiar solo contenido autorizado de vinculados; Superadministración no firma clínicamente por consultar.

## Recorrido que debe funcionar

1. Elegir reporte y filtros permitidos.
2. Consultar datos con el mismo alcance del usuario y residente.
3. Mostrar tabla/resumen y gráfica solo si la comparación es válida.
4. Exportar mediante el flujo existente, preservando permisos y privacidad.
5. Comunicar errores, ausencia y límites sin presentar un reporte vacío como éxito íntegro.

## Entradas y salidas

- **Entradas:** Tipo de reporte, filtros y alcance autorizado.
- **Salidas:** Consulta/exportación coherente con la fuente y los permisos.

## Invariantes y autorización

- Descarga y consulta se reautorizan en backend.
- No enviar datos clínicos a visualización externa.
- Cada gráfica tiene alternativa textual y no inventa interpretación.
- En operaciones sensibles: sesión y cuenta activa + permiso explícito + Policy/contexto + estado válido + relación/competencia. Ocultar un botón no es suficiente.

## Persistencia y continuidad

Usar Models y relaciones existentes conforme al [baseline](../../../docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md) y decisiones aplicables. Mantener autoría, fecha clínica y registro técnico cuando el contrato los diferencia. Corregir por el mecanismo longitudinal del dominio; no borrar historia para ocultar errores. Una operación institucional multientidad conserva atomicidad e idempotencia cuando corresponda.

## Fuentes y ubicación real

- [Instrucciones institucionales](../../../AGENTS.md) y [backend](../../AGENTS.md).
- [docs/seguridad/MODELO_AUTORIZACION.md](../../../docs/seguridad/MODELO_AUTORIZACION.md)
- [docs/frontend/CONTRATO_VISUAL_UX_UI.md](../../../docs/frontend/CONTRATO_VISUAL_UX_UI.md)
- [app/Modulos/Reportes/Presentacion/Http/Controllers/ReporteV2Controller.php](Presentacion/Http/Controllers/ReporteV2Controller.php)
- [app/Modulos/Reportes/Infraestructura/Exportaciones/ReportExportService.php](Infraestructura/Exportaciones/ReportExportService.php)
- [app/Modulos/Reportes/Presentacion/Http/Controllers/AreaReporteController.php](Presentacion/Http/Controllers/AreaReporteController.php)
- [Registro de rutas del propietario](Presentacion/Rutas/web.php)

Dominio conserva reglas puras; Aplicacion coordina operaciones/consultas; Infraestructura adapta persistencia e integraciones; Presentacion contiene HTTP, Livewire, Policies y Blade. Modelos Eloquent compartidos mantienen su identidad en app/Models. Una carpeta o clase existente no acredita por sí sola un proceso completo.

## Interfaz y errores

Identificar persona/contexto y acción principal. Diferenciar sin registros, sin resultados de búsqueda, acceso restringido, validación, error técnico y no interpretabilidad. Formularios con errores junto al campo, carga y confirmación real; detalle progresivo y navegación de regreso. Los códigos internos no sustituyen explicaciones clínicas. [Contrato visual compartido](../../../docs/frontend/CONTRATO_VISUAL_UX_UI.md).

## Pruebas localizadas y aceptación

- [tests/Feature/RolesVistasPermisosMatrixTest.php](../../../tests/Feature/RolesVistasPermisosMatrixTest.php)
- [tests/Feature/Fase3SeguridadNucleoTest.php](../../../tests/Feature/Fase3SeguridadNucleoTest.php)

Estas pruebas están localizadas, no reejecutadas por la limpieza documental. [El estado actual](../../../docs/ESTADO_ACTUAL.md) y [la auditoría](../../../docs/auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) indican lo realmente ejecutado. Cierre: camino positivo y negativos relevantes, permisos revocados, fuentes ajenas, integridad, recuperación de errores y recorrido visual comprobados; PostgreSQL para concurrencia.

## Brechas comprobadas

Entrega acotada del 27/10/2026: [resumen de Enfermería por período/residente con vista previa y PDF](../../../docs/frontend/REPORTES_ENFERMERIA_PERIODO_2026_10_27.md). Consulta y exportación comparten filtros y fuentes; el informe conserva comandos, pruebas y límites. No equivale a aceptar todas las exportaciones del módulo.

H24/H31: labels y canvas sin alternativa suficiente, bundles grandes sin medición de rendimiento por recorrido. No hay aceptación visual/global de todas las exportaciones.
