---
title: "Responsabilidad funcional — Institucion"
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

# Institucion

**Revisión documental: 25/10/2026.** Esta compilación localiza responsabilidades y comportamiento exigido por contratos existentes; no crea permisos, estados, umbrales o estructura. Las fuentes específicas prevalecen. Documentar el recorrido no significa que todos sus pasos hayan superado aceptación.

## Propósito y actores

Organizar áreas, habitaciones, camas, contexto laboral, turnos y asignaciones para la operación institucional.

Gerencia en planificación y personal; Administración en operación diaria; profesionales consultan su contexto autorizado. No se concede escritura clínica por administrar turnos.

## Recorrido que debe funcionar

1. Consultar áreas, alojamiento y disponibilidad.
2. Planificar el horario autorizado del trabajador.
3. Validar ventana completa, incluidas guardias que cruzan medianoche.
4. Asignar el contexto operativo/asistencial aprobado.
5. Continuar con Cuidados y consultas de ocupación permitidas al rol.

## Entradas y salidas

- **Entradas:** Áreas, habitaciones, camas, personal y ventanas laborales.
- **Salidas:** Disponibilidad y contexto de trabajo coherentes para procesos consumidores.

## Invariantes y autorización

- Permiso de consulta no equivale a permiso de asignación.
- Comprobar conflictos temporales, estado del personal y relaciones.
- La cama disponible se vuelve a comprobar al ocuparla.
- En operaciones sensibles: sesión y cuenta activa + permiso explícito + Policy/contexto + estado válido + relación/competencia. Ocultar un botón no es suficiente.

## Persistencia y continuidad

Usar Models y relaciones existentes conforme al [baseline](../../../docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md) y decisiones aplicables. Mantener autoría, fecha clínica y registro técnico cuando el contrato los diferencia. Corregir por el mecanismo longitudinal del dominio; no borrar historia para ocultar errores. Una operación institucional multientidad conserva atomicidad e idempotencia cuando corresponda.

## Fuentes y ubicación real

- [Instrucciones institucionales](../../../AGENTS.md) y [backend](../../AGENTS.md).
- [docs/modulos/jornadas-asignaciones/README.md](../../../docs/modulos/jornadas-asignaciones/README.md)
- [docs/modulos/residentes-alojamiento/README.md](../../../docs/modulos/residentes-alojamiento/README.md)
- [app/Modulos/Institucion/Aplicacion/Acciones/GestionarHorariosPersonal.php](Aplicacion/Acciones/GestionarHorariosPersonal.php)
- [app/Modulos/Institucion/Presentacion/Livewire/Funcionalidades/PersonalInstitucionalHorarios.php](Presentacion/Livewire/Funcionalidades/PersonalInstitucionalHorarios.php)
- [app/Modulos/Institucion/Aplicacion/Consultas/ContextoLaboralService.php](Aplicacion/Consultas/ContextoLaboralService.php)
- [Registro de rutas del propietario](Presentacion/Rutas/web.php)

Dominio conserva reglas puras; Aplicacion coordina operaciones/consultas; Infraestructura adapta persistencia e integraciones; Presentacion contiene HTTP, Livewire, Policies y Blade. Modelos Eloquent compartidos mantienen su identidad en app/Models. Una carpeta o clase existente no acredita por sí sola un proceso completo.

## Interfaz y errores

Identificar persona/contexto y acción principal. Diferenciar sin registros, sin resultados de búsqueda, acceso restringido, validación, error técnico y no interpretabilidad. Formularios con errores junto al campo, carga y confirmación real; detalle progresivo y navegación de regreso. Los códigos internos no sustituyen explicaciones clínicas. [Contrato visual compartido](../../../docs/frontend/CONTRATO_VISUAL_UX_UI.md).

## Pruebas localizadas y aceptación

- [tests/Feature/ContratoPersonalInstitucionalTest.php](../../../tests/Feature/ContratoPersonalInstitucionalTest.php)
- [tests/Feature/RolesVistasPermisosMatrixTest.php](../../../tests/Feature/RolesVistasPermisosMatrixTest.php)

Estas pruebas están localizadas, no reejecutadas por la limpieza documental. [El estado actual](../../../docs/ESTADO_ACTUAL.md) y [la auditoría](../../../docs/auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) indican lo realmente ejecutado. Cierre: camino positivo y negativos relevantes, permisos revocados, fuentes ajenas, integridad, recuperación de errores y recorrido visual comprobados; PostgreSQL para concurrencia.

## Brechas comprobadas

H02/H03/H04/H24: asignación sin permiso, solapamiento nocturno, Ocupación de Gerencia con 403 y drawer de Áreas. Deben cerrarse con correcciones de producto y pruebas.
