---
title: "Responsabilidad funcional — Residentes"
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

# Residentes

**Revisión documental: 25/10/2026.** Esta compilación localiza responsabilidades y comportamiento exigido por contratos existentes; no crea permisos, estados, umbrales o estructura. Las fuentes específicas prevalecen. Documentar el recorrido no significa que todos sus pasos hayan superado aceptación.

## Propósito y actores

Mantener la identidad residencial y el acceso longitudinal al expediente, alojamiento, contactos y documentos autorizados.

Administración en operaciones institucionales; profesionales dentro de competencia; Familiar solo por vínculo y contenido permitido; Superadministración consulta.

## Recorrido que debe funcionar

1. Entrar al directorio filtrado por alcance.
2. Abrir el resumen del residente formalmente admitido.
3. Consultar historia, ubicación, contactos y documentos permitidos.
4. Continuar al módulo propietario de cada registro o corrección.
5. Conservar la trazabilidad de cambios y el contexto del residente.

## Entradas y salidas

- **Entradas:** Residente creado en admisión y relaciones institucionales aprobadas.
- **Salidas:** Expediente consultable y navegación autorizada a fuentes longitudinales.

## Invariantes y autorización

- No crear residentes por CRUD independiente de Admisiones.
- Reautorizar acceso directo, archivos y datos de otro residente.
- No confundir residente con usuario, postulante, personal o contacto.
- En operaciones sensibles: sesión y cuenta activa + permiso explícito + Policy/contexto + estado válido + relación/competencia. Ocultar un botón no es suficiente.

## Persistencia y continuidad

Usar Models y relaciones existentes conforme al [baseline](../../../docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md) y decisiones aplicables. Mantener autoría, fecha clínica y registro técnico cuando el contrato los diferencia. Corregir por el mecanismo longitudinal del dominio; no borrar historia para ocultar errores. Una operación institucional multientidad conserva atomicidad e idempotencia cuando corresponda.

## Fuentes y ubicación real

- [Instrucciones institucionales](../../../AGENTS.md) y [backend](../../AGENTS.md).
- [docs/modulos/residentes-alojamiento/README.md](../../../docs/modulos/residentes-alojamiento/README.md)
- [app/Modulos/Residentes/Presentacion/Http/Controllers/ResidenteController.php](Presentacion/Http/Controllers/ResidenteController.php)
- [app/Modulos/Residentes/Presentacion/Policies/ResidentePolicy.php](Presentacion/Policies/ResidentePolicy.php)
- [app/Modulos/Residentes/Presentacion/Http/Controllers/RelacionResidenteController.php](Presentacion/Http/Controllers/RelacionResidenteController.php)
- [Registro de rutas del propietario](Presentacion/Rutas/web.php)

Dominio conserva reglas puras; Aplicacion coordina operaciones/consultas; Infraestructura adapta persistencia e integraciones; Presentacion contiene HTTP, Livewire, Policies y Blade. Modelos Eloquent compartidos mantienen su identidad en app/Models. Una carpeta o clase existente no acredita por sí sola un proceso completo.

## Interfaz y errores

Identificar persona/contexto y acción principal. Diferenciar sin registros, sin resultados de búsqueda, acceso restringido, validación, error técnico y no interpretabilidad. Formularios con errores junto al campo, carga y confirmación real; detalle progresivo y navegación de regreso. Los códigos internos no sustituyen explicaciones clínicas. [Contrato visual compartido](../../../docs/frontend/CONTRATO_VISUAL_UX_UI.md).

## Pruebas localizadas y aceptación

- [tests/Feature/RolesVistasPermisosMatrixTest.php](../../../tests/Feature/RolesVistasPermisosMatrixTest.php)

Estas pruebas están localizadas, no reejecutadas por la limpieza documental. [El estado actual](../../../docs/ESTADO_ACTUAL.md) y [la auditoría](../../../docs/auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) indican lo realmente ejecutado. Cierre: camino positivo y negativos relevantes, permisos revocados, fuentes ajenas, integridad, recuperación de errores y recorrido visual comprobados; PostgreSQL para concurrencia.

## Brechas comprobadas

Falta recorrer todas las carpetas con datos, ausencia, fuente restringida y acceso directo por rol. El GET de directorio no acredita cada acción interna.
