---
title: "Responsabilidad funcional — ApoyoDecision"
status: CURRENT
version: "2.2"
last_reviewed: 2026-11-04
owner: RememberMind
source_of_truth: false
source_of_truth_scope: [module_responsibility]
verified_against_commit: 57285c7e3988cf5df86cdbd24f03e6a9650807bf
verification_scope: CURRENT_TREE_DOCUMENTATION_AND_REGISTERED_ROUTES
runtime_verified: false
---

# ApoyoDecision

## Estado localizado al 04/11/2026

El árbol contiene **7 rutas con acción de este módulo, 6 clases en Livewire y 37 plantillas Blade**. Las plantillas incluyen parciales y no equivalen a pantallas aceptadas. [Inventario reproducible](../../../docs/sistema/INVENTARIO_ACTUAL.md) y [estado del producto](../../../docs/ESTADO_ACTUAL.md). Esta actualización no reejecuta las pruebas citadas ni concede permisos.

Motor independiente con 24 reglas fuente,26 métodos y tres perfiles iniciales descriptivos activos localmente. El dominio está directamente en `motor-experto/dominio/`; el importador de investigación y el fallback documental están retirados. La conservación conjunta instrumental/Petersen sigue pendiente; [mapa del código](../../../motor-experto/README.md).


[Entrada única del experto](../../../docs/sistema-experto/README.md): mapa de componentes, matriz de datos, instrumentos candidatos e inventario instalado. La [estructura por componentes adoptada el 27/10/2026](../../../docs/sistema-experto/organizacion/ESTRUCTURA_POR_COMPONENTES.md) sigue el diagrama de la propietaria; el [mapa de código](../../../docs/sistema-experto/organizacion/MAPA_COMPONENTES.md) localiza sus implementaciones. El laboratorio fue retirado del circuito operativo.

**Revisión de estructura y fuentes:04/11/2026.** Esta compilación localiza responsabilidades y comportamiento exigido por contratos existentes; no crea permisos, estados, umbrales o estructura. Las fuentes específicas prevalecen. Documentar el recorrido no significa que todos sus pasos hayan superado aceptación.

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

## Hallazgos del corte anterior y seguimiento

La conservación conjunta transitoria continúa pendiente. La auditoría del 25/10 conserva H07–H13 como hallazgos de ese corte; las correcciones posteriores de consulta, explicación, contexto y retirada de laboratorio constan en el índice experto. No trasladar todo ese grupo como fallos actuales sin contrastar la entrega posterior.
