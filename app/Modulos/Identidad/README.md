---
title: "Responsabilidad funcional — Identidad"
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

# Identidad

**Revisión documental: 25/10/2026.** Esta compilación localiza responsabilidades y comportamiento exigido por contratos existentes; no crea permisos, estados, umbrales o estructura. Las fuentes específicas prevalecen. Documentar el recorrido no significa que todos sus pasos hayan superado aceptación.

## Propósito y actores

Gestionar cuentas, autenticación, roles, permisos, documentación de acceso e identidad del personal.

Superadministración gestiona acceso dentro de permisos aprobados; Gerencia gestiona personal en su alcance. Un rol administrativo no concede escritura clínica.

## Recorrido que debe funcionar

1. Autenticar con correo y cuenta ACTIVO; mantener registro público deshabilitado.
2. Consultar cuentas y personal con sus permisos específicos.
3. Crear o actualizar exclusivamente campos autorizados en el flujo existente.
4. Mantener roles, permisos y vínculo usuario/personal sin atribuir competencia por nombre de rol.
5. Revocar acceso y comprobar sus efectos en siguientes consultas y acciones.

## Entradas y salidas

- **Entradas:** Credenciales, cuenta, roles/permisos y documentación permitida.
- **Salidas:** Sesión o denegación, identidad y cambios de acceso auditables.

## Invariantes y autorización

- No contraseñas hardcodeadas ni creación como fallback.
- Cuenta activa, permiso, Policy y alcance en backend.
- Documentos sensibles privados y descarga reautorizada.
- En operaciones sensibles: sesión y cuenta activa + permiso explícito + Policy/contexto + estado válido + relación/competencia. Ocultar un botón no es suficiente.

## Persistencia y continuidad

Usar Models y relaciones existentes conforme al [baseline](../../../docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md) y decisiones aplicables. Mantener autoría, fecha clínica y registro técnico cuando el contrato los diferencia. Corregir por el mecanismo longitudinal del dominio; no borrar historia para ocultar errores. Una operación institucional multientidad conserva atomicidad e idempotencia cuando corresponda.

## Fuentes y ubicación real

- [Instrucciones institucionales](../../../AGENTS.md) y [backend](../../AGENTS.md).
- [docs/seguridad/MODELO_AUTORIZACION.md](../../../docs/seguridad/MODELO_AUTORIZACION.md)
- [docs/seguridad/MATRIZ_ROLES_PERMISOS.md](../../../docs/seguridad/MATRIZ_ROLES_PERMISOS.md)
- [app/Modulos/Identidad/Presentacion/Http/Controllers/UsuarioController.php](Presentacion/Http/Controllers/UsuarioController.php)
- [app/Modulos/Identidad/Presentacion/Livewire/Paginas/UsuariosPanel.php](Presentacion/Livewire/Paginas/UsuariosPanel.php)
- [app/Modulos/Identidad/Presentacion/Livewire/Paginas/PersonalInstitucionalPanel.php](Presentacion/Livewire/Paginas/PersonalInstitucionalPanel.php)
- [Registro de rutas del propietario](Presentacion/Rutas/web.php)

Dominio conserva reglas puras; Aplicacion coordina operaciones/consultas; Infraestructura adapta persistencia e integraciones; Presentacion contiene HTTP, Livewire, Policies y Blade. Modelos Eloquent compartidos mantienen su identidad en app/Models. Una carpeta o clase existente no acredita por sí sola un proceso completo.

## Interfaz y errores

Identificar persona/contexto y acción principal. Diferenciar sin registros, sin resultados de búsqueda, acceso restringido, validación, error técnico y no interpretabilidad. Formularios con errores junto al campo, carga y confirmación real; detalle progresivo y navegación de regreso. Los códigos internos no sustituyen explicaciones clínicas. [Contrato visual compartido](../../../docs/frontend/CONTRATO_VISUAL_UX_UI.md).

## Pruebas localizadas y aceptación

- [tests/Feature/UsuariosRoutesPermissionsTest.php](../../../tests/Feature/UsuariosRoutesPermissionsTest.php)
- [tests/Feature/RedireccionLoginAutorizadaTest.php](../../../tests/Feature/RedireccionLoginAutorizadaTest.php)

Estas pruebas están localizadas, no reejecutadas por la limpieza documental. [El estado actual](../../../docs/ESTADO_ACTUAL.md) y [la auditoría](../../../docs/auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) indican lo realmente ejecutado. Cierre: camino positivo y negativos relevantes, permisos revocados, fuentes ajenas, integridad, recuperación de errores y recorrido visual comprobados; PostgreSQL para concurrencia.

## Brechas comprobadas

H01/H02 demuestran que revocar permisos no siempre se propaga a consumidores de otros módulos. No se consideran corregidos al actualizar este README.
