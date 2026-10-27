---
title: "Responsabilidad funcional — Social"
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

# Social

**Revisión documental: 25/10/2026.** Esta compilación localiza responsabilidades y comportamiento exigido por contratos existentes; no crea permisos, estados, umbrales o estructura. Las fuentes específicas prevalecen. Documentar el recorrido no significa que todos sus pasos hayan superado aceptación.

## Propósito y actores

Coordinar actividades, participación, contactos, visitas y red de apoyo dentro de permisos y vínculos institucionales.

Personal y operadores autorizados en cada proceso; familiares solo con vínculos y autorizaciones aplicables.

## Recorrido que debe funcionar

1. Programar o consultar la actividad/visita autorizada.
2. Vincular residentes y participantes del proceso correcto.
3. Registrar asistencia o seguimiento real con fecha y autor.
4. Consultar red de apoyo y restricciones de información.
5. Continuar con el expediente y el siguiente profesional autorizado.

## Entradas y salidas

- **Entradas:** Actividad, visita, contacto, vínculo y participación permitidos.
- **Salidas:** Programación, participación y seguimiento consultables en su contexto.

## Invariantes y autorización

- No duplicar participantes cuando el contrato lo prohíbe.
- Contacto y residente conservan vínculo válido; no son cuentas intercambiables.
- No publicar datos clínicos por disponer de una relación social.
- En operaciones sensibles: sesión y cuenta activa + permiso explícito + Policy/contexto + estado válido + relación/competencia. Ocultar un botón no es suficiente.

## Persistencia y continuidad

Usar Models y relaciones existentes conforme al [baseline](../../../docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md) y decisiones aplicables. Mantener autoría, fecha clínica y registro técnico cuando el contrato los diferencia. Corregir por el mecanismo longitudinal del dominio; no borrar historia para ocultar errores. Una operación institucional multientidad conserva atomicidad e idempotencia cuando corresponda.

## Fuentes y ubicación real

- [Instrucciones institucionales](../../../AGENTS.md) y [backend](../../AGENTS.md).
- [docs/sistema/CONTINUIDAD_ASISTENCIAL.md](../../../docs/sistema/CONTINUIDAD_ASISTENCIAL.md)
- [docs/seguridad/MODELO_AUTORIZACION.md](../../../docs/seguridad/MODELO_AUTORIZACION.md)
- [app/Modulos/Social/Aplicacion/Acciones/GestionarActividades.php](Aplicacion/Acciones/GestionarActividades.php)
- [app/Modulos/Social/Presentacion/Livewire/Paginas/ParticipacionPanel.php](Presentacion/Livewire/Paginas/ParticipacionPanel.php)
- [app/Modulos/Social/Presentacion/Livewire/Paginas/RedApoyoPanel.php](Presentacion/Livewire/Paginas/RedApoyoPanel.php)
- [Registro de rutas del propietario](Presentacion/Rutas/web.php)

Dominio conserva reglas puras; Aplicacion coordina operaciones/consultas; Infraestructura adapta persistencia e integraciones; Presentacion contiene HTTP, Livewire, Policies y Blade. Modelos Eloquent compartidos mantienen su identidad en app/Models. Una carpeta o clase existente no acredita por sí sola un proceso completo.

## Interfaz y errores

Identificar persona/contexto y acción principal. Diferenciar sin registros, sin resultados de búsqueda, acceso restringido, validación, error técnico y no interpretabilidad. Formularios con errores junto al campo, carga y confirmación real; detalle progresivo y navegación de regreso. Los códigos internos no sustituyen explicaciones clínicas. [Contrato visual compartido](../../../docs/frontend/CONTRATO_VISUAL_UX_UI.md).

## Pruebas localizadas y aceptación

- [tests/Feature/BddOperativaV2Test.php](../../../tests/Feature/BddOperativaV2Test.php)
- [tests/Feature/RolesVistasPermisosMatrixTest.php](../../../tests/Feature/RolesVistasPermisosMatrixTest.php)

Estas pruebas están localizadas, no reejecutadas por la limpieza documental. [El estado actual](../../../docs/ESTADO_ACTUAL.md) y [la auditoría](../../../docs/auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) indican lo realmente ejecutado. Cierre: camino positivo y negativos relevantes, permisos revocados, fuentes ajenas, integridad, recuperación de errores y recorrido visual comprobados; PostgreSQL para concurrencia.

## Brechas comprobadas

La auditoría señaló densidad de métricas y filtros sin nombre accesible. Falta recorrer asistencia/seguimiento completo; no se certifica por existencia del listado.
