---
title: "Responsabilidad funcional — Clinica"
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

# Clinica

**Revisión documental: 25/10/2026.** Esta compilación localiza responsabilidades y comportamiento exigido por contratos existentes; no crea permisos, estados, umbrales o estructura. Las fuentes específicas prevalecen. Documentar el recorrido no significa que todos sus pasos hayan superado aceptación.

## Propósito y actores

Documentar y consultar actos clínicos, antecedentes, diagnósticos, estudios y seguimientos con su autoría y fecha.

Cada profesional con permiso y competencia sobre la fuente; Medicina conserva las funciones médicas. Familia recibe únicamente información publicada y permitida.

## Recorrido que debe funcionar

1. Seleccionar residente y contexto clínico autorizado.
2. Consultar antecedentes y registros pertinentes sin vaciar silenciosamente fuentes restringidas.
3. Registrar el acto o resultado con fecha clínica, autor y relación válida.
4. Rectificar o anular mediante el mecanismo propio sin borrar historia.
5. Continuar a estudios, medicación, valoraciones, cuidados o alertas cuando el contrato lo contemple.

## Entradas y salidas

- **Entradas:** Acto clínico, antecedentes, observaciones, estudios y documentos originales.
- **Salidas:** Registro longitudinal trazable y consulta clínica autorizada.

## Invariantes y autorización

- Fecha clínica distinta del instante técnico de registro.
- No firmar ni atribuir registros a otro profesional por parámetros manipulados.
- Documento/estudio pertenece al mismo residente y al episodio correspondiente.
- En operaciones sensibles: sesión y cuenta activa + permiso explícito + Policy/contexto + estado válido + relación/competencia. Ocultar un botón no es suficiente.

## Persistencia y continuidad

Usar Models y relaciones existentes conforme al [baseline](../../../docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md) y decisiones aplicables. Mantener autoría, fecha clínica y registro técnico cuando el contrato los diferencia. Corregir por el mecanismo longitudinal del dominio; no borrar historia para ocultar errores. Una operación institucional multientidad conserva atomicidad e idempotencia cuando corresponda.

## Fuentes y ubicación real

- [Instrucciones institucionales](../../../AGENTS.md) y [backend](../../AGENTS.md).
- [docs/modulos/signos-vitales/README.md](../../../docs/modulos/signos-vitales/README.md)
- [docs/sistema/FLUJOS_GERIATRICOS.md](../../../docs/sistema/FLUJOS_GERIATRICOS.md)
- [app/Modulos/Clinica/Presentacion/Http/Controllers/EstudioClinicoController.php](Presentacion/Http/Controllers/EstudioClinicoController.php)
- [app/Modulos/Clinica/Aplicacion/Consultas/ContextoClinicoService.php](Aplicacion/Consultas/ContextoClinicoService.php)
- [app/Modulos/Clinica/Aplicacion/Acciones/RegistrarSignosVitalesAction.php](Aplicacion/Acciones/RegistrarSignosVitalesAction.php)
- [Registro de rutas del propietario](Presentacion/Rutas/web.php)

Dominio conserva reglas puras; Aplicacion coordina operaciones/consultas; Infraestructura adapta persistencia e integraciones; Presentacion contiene HTTP, Livewire, Policies y Blade. Modelos Eloquent compartidos mantienen su identidad en app/Models. Una carpeta o clase existente no acredita por sí sola un proceso completo.

## Interfaz y errores

Identificar persona/contexto y acción principal. Diferenciar sin registros, sin resultados de búsqueda, acceso restringido, validación, error técnico y no interpretabilidad. Formularios con errores junto al campo, carga y confirmación real; detalle progresivo y navegación de regreso. Los códigos internos no sustituyen explicaciones clínicas. [Contrato visual compartido](../../../docs/frontend/CONTRATO_VISUAL_UX_UI.md).

## Pruebas localizadas y aceptación

- [tests/Feature/BddOperativaV2Test.php](../../../tests/Feature/BddOperativaV2Test.php)
- [tests/Feature/Fase3SeguridadNucleoTest.php](../../../tests/Feature/Fase3SeguridadNucleoTest.php)

Estas pruebas están localizadas, no reejecutadas por la limpieza documental. [El estado actual](../../../docs/ESTADO_ACTUAL.md) y [la auditoría](../../../docs/auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) indican lo realmente ejecutado. Cierre: camino positivo y negativos relevantes, permisos revocados, fuentes ajenas, integridad, recuperación de errores y recorrido visual comprobados; PostgreSQL para concurrencia.

## Brechas comprobadas

H23: entrada de Nutrición con placeholder pese a existir valoración. Falta aceptación visual de todas las entradas clínicas; no se documenta como operativa una acción sin destino.
