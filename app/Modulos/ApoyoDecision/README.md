---
title: "Responsabilidad funcional — ApoyoDecision"
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

# ApoyoDecision

**Revisión documental: 25/10/2026.** Esta compilación localiza responsabilidades y comportamiento exigido por contratos existentes; no crea permisos, estados, umbrales o estructura. Las fuentes específicas prevalecen. Documentar el recorrido no significa que todos sus pasos hayan superado aceptación.

## Propósito y actores

Integrar fuentes y motor simbólico existente para explicar cinco dominios, hallazgos y perfiles orientativos Petersen o abstenciones.

Medicina/Psicología capturan y revisan en sus permisos; Enfermería consulta fuentes/resultados permitidos; Medicina/Psicología gestionan reglas y Superadministración consulta técnicamente.

## Recorrido que debe funcionar

1. Adquirir fuentes del mismo residente, corte, autor, estado y procedencia válidos.
2. Evaluar independientemente dominios y condiciones con el conocimiento versionado.
3. Derivar C02 y acreditar C01/C03/C04/C05 según soportes del contrato.
4. Emitir uno de cuatro perfiles orientativos o abstenerse con motivos concurrentes.
5. Explicar fuentes, juicios, condiciones, reglas y resultado; conservar y revisar solo el alcance realmente persistido.

## Entradas y salidas

- **Entradas:** Fuentes tipadas autorizadas, juicios originales, reglas y versión.
- **Salidas:** Hallazgos, evaluabilidad, explicación y perfil orientativo o abstención.

## Invariantes y autorización

- Reutilizar motor-experto; no otro motor o clasificador paralelo.
- No observación como medición, normalidad por NULL ni equivalencias inventadas.
- Regla archivada conserva historia; revisiones no alteran salida automática.
- En operaciones sensibles: sesión y cuenta activa + permiso explícito + Policy/contexto + estado válido + relación/competencia. Ocultar un botón no es suficiente.

## Persistencia y continuidad

Usar Models y relaciones existentes conforme al [baseline](../../../docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md) y decisiones aplicables. Mantener autoría, fecha clínica y registro técnico cuando el contrato los diferencia. Corregir por el mecanismo longitudinal del dominio; no borrar historia para ocultar errores. Una operación institucional multientidad conserva atomicidad e idempotencia cuando corresponda.

## Fuentes y ubicación real

- [Instrucciones institucionales](../../../AGENTS.md) y [backend](../../AGENTS.md).
- [docs/sistema-experto/README.md](../../../docs/sistema-experto/README.md)
- [docs/sistema-experto/CONTRATO_FUNCIONAL_PETERSEN_C01_C05_2026_10_21.md](../../../docs/sistema-experto/CONTRATO_FUNCIONAL_PETERSEN_C01_C05_2026_10_21.md)
- [app/Modulos/ApoyoDecision/Aplicacion/Consultas/ConsultarPerfilInstrumental.php](Aplicacion/Consultas/ConsultarPerfilInstrumental.php)
- [app/Modulos/ApoyoDecision/Presentacion/Livewire/Paginas/ResultadosExpertoResidente.php](Presentacion/Livewire/Paginas/ResultadosExpertoResidente.php)
- [app/Modulos/ApoyoDecision/Presentacion/Policies/EvaluacionExpertaPolicy.php](Presentacion/Policies/EvaluacionExpertaPolicy.php)
- [Registro de rutas del propietario](Presentacion/Rutas/web.php)

Dominio conserva reglas puras; Aplicacion coordina operaciones/consultas; Infraestructura adapta persistencia e integraciones; Presentacion contiene HTTP, Livewire, Policies y Blade. Modelos Eloquent compartidos mantienen su identidad en app/Models. Una carpeta o clase existente no acredita por sí sola un proceso completo.

## Interfaz y errores

Identificar persona/contexto y acción principal. Diferenciar sin registros, sin resultados de búsqueda, acceso restringido, validación, error técnico y no interpretabilidad. Formularios con errores junto al campo, carga y confirmación real; detalle progresivo y navegación de regreso. Los códigos internos no sustituyen explicaciones clínicas. [Contrato visual compartido](../../../docs/frontend/CONTRATO_VISUAL_UX_UI.md).

## Pruebas localizadas y aceptación

- [tests/Feature/CapturaPetersenOriginalTest.php](../../../tests/Feature/CapturaPetersenOriginalTest.php)
- [tests/Feature/CicloObservacionalConservadoTest.php](../../../tests/Feature/CicloObservacionalConservadoTest.php)
- [tests/Feature/ConservacionCompletaSQLTest.php](../../../tests/Feature/ConservacionCompletaSQLTest.php)

Estas pruebas están localizadas, no reejecutadas por la limpieza documental. [El estado actual](../../../docs/ESTADO_ACTUAL.md) y [la auditoría](../../../docs/auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) indican lo realmente ejecutado. Cierre: camino positivo y negativos relevantes, permisos revocados, fuentes ajenas, integridad, recuperación de errores y recorrido visual comprobados; PostgreSQL para concurrencia.

## Brechas comprobadas

H07–H13: DTO conjunto transitorio, prototipo no instalable, consulta duplicada, corte con estado actual, lectura/firma acopladas, traza clínica limitada y manifiesto 104 no localizado. La rama observacional conservada no cierra esas brechas.
