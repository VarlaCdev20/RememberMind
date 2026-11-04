---
title: "Responsabilidad funcional — Valoraciones"
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

# Valoraciones

## Estado localizado al 04/11/2026

El árbol contiene **29 rutas con acción de este módulo, 16 clases en Livewire y 19 plantillas Blade**. Las plantillas incluyen parciales y no equivalen a pantallas aceptadas. [Inventario reproducible](../../../docs/sistema/INVENTARIO_ACTUAL.md) y [estado del producto](../../../docs/ESTADO_ACTUAL.md). Esta actualización no reejecuta las pruebas citadas ni concede permisos.

MoCA 8.1 español, RUDAS-PE 2020 y FAQ B7S capturan puntuaciones mediante el formulario compartido; los totales no producen diagnósticos automáticos. Contexto por atención: escolaridad/lectoescritura, idioma e informante principal, con las extensiones autorizadas del 28–29/10.


**Revisión de estructura y fuentes:04/11/2026.** Esta compilación localiza responsabilidades y comportamiento exigido por contratos existentes; no crea permisos, estados, umbrales o estructura. Las fuentes específicas prevalecen. Documentar el recorrido no significa que todos sus pasos hayan superado aceptación.

## Propósito y actores

Capturar valoraciones profesionales e instrumentos originales con condiciones, mediciones, autoría y procedencia.

Psicología, Medicina y demás profesiones según instrumento, fuente, permiso y competencia; no toda especialidad aplica cualquier prueba.

## Recorrido que debe funcionar

1. Elegir residente, tipo de valoración e instrumento autorizado cuando corresponda.
2. Registrar edición, idioma, método, fecha clínica, autor, contexto y respuestas/tiempos originales exigidos por el contrato.
3. Calcular con método aplicable, conservando ausencia y no interpretabilidad.
4. Rectificar por cadena propia sin transformar observaciones en aplicaciones estandarizadas.
5. Vincular las fuentes al adaptador experto y consultar su admisibilidad.

## Entradas y salidas

- **Entradas:** Valoración o aplicación original identificable y condiciones pertinentes.
- **Salidas:** Registro profesional o aplicación trazable, con resultado o límite de interpretación.

## Invariantes y autorización

- Pregunta y respuesta corresponden a la aplicación/instrumento.
- Una puntuación global no equivale a cinco dominios.
- No incorporar reactivos protegidos ni baremos no autorizados.
- En operaciones sensibles: sesión y cuenta activa + permiso explícito + Policy/contexto + estado válido + relación/competencia. Ocultar un botón no es suficiente.

## Persistencia y continuidad

Usar Models y relaciones existentes conforme al [baseline](../../../docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md) y decisiones aplicables. Mantener autoría, fecha clínica y registro técnico cuando el contrato los diferencia. Corregir por el mecanismo longitudinal del dominio; no borrar historia para ocultar errores. Una operación institucional multientidad conserva atomicidad e idempotencia cuando corresponda.

## Fuentes y ubicación real

- [Instrucciones institucionales](../../../AGENTS.md) y [backend](../../AGENTS.md).
- [docs/sistema-experto/PUNTO_1_EVIDENCIA_COGNITIVA_ORIGINAL_2026_10_21.md](../../../docs/sistema-experto/PUNTO_1_EVIDENCIA_COGNITIVA_ORIGINAL_2026_10_21.md)
- [docs/sistema-experto/CONTRATO_FUNCIONAL_PETERSEN_C01_C05_2026_10_21.md](../../../docs/sistema-experto/CONTRATO_FUNCIONAL_PETERSEN_C01_C05_2026_10_21.md)
- [app/Modulos/Valoraciones/Aplicacion/Acciones/AplicarInstrumento.php](Aplicacion/Acciones/AplicarInstrumento.php)
- [app/Modulos/Valoraciones/Aplicacion/Acciones/RegistrarValoracionProfesional.php](Aplicacion/Acciones/RegistrarValoracionProfesional.php)
- [app/Modulos/Valoraciones/Presentacion/Livewire/Paginas/FichaPsicologicaResidente.php](Presentacion/Livewire/Paginas/FichaPsicologicaResidente.php)
- [Registro de rutas del propietario](Presentacion/Rutas/web.php)

Dominio conserva reglas puras; Aplicacion coordina operaciones/consultas; Infraestructura adapta persistencia e integraciones; Presentacion contiene HTTP, Livewire, Policies y Blade. Modelos Eloquent compartidos mantienen su identidad en app/Models. Una carpeta o clase existente no acredita por sí sola un proceso completo.

## Interfaz y errores

Identificar persona/contexto y acción principal. Diferenciar sin registros, sin resultados de búsqueda, acceso restringido, validación, error técnico y no interpretabilidad. Formularios con errores junto al campo, carga y confirmación real; detalle progresivo y navegación de regreso. Los códigos internos no sustituyen explicaciones clínicas. [Contrato visual compartido](../../../docs/frontend/CONTRATO_VISUAL_UX_UI.md).

## Pruebas localizadas y aceptación

- [tests/Feature/CapturaInstrumentalInstitucionalTest.php](../../../tests/Feature/CapturaInstrumentalInstitucionalTest.php)
- [tests/Feature/FichaPsicologicaResidenteTest.php](../../../tests/Feature/FichaPsicologicaResidenteTest.php)

Estas pruebas están localizadas, no reejecutadas por la limpieza documental. [El estado actual](../../../docs/ESTADO_ACTUAL.md) y [la auditoría](../../../docs/auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) indican lo realmente ejecutado. Cierre: camino positivo y negativos relevantes, permisos revocados, fuentes ajenas, integridad, recuperación de errores y recorrido visual comprobados; PostgreSQL para concurrencia.

## Hallazgos del corte anterior y seguimiento

H01/H05/H14–H22/H29: lectura revocada, fecha, series, etiquetas, Petersen paralelo, acceso confundido con ausencia, puntaje, firma y ficha desconectada. La documentación no los convierte en comportamientos válidos.


## Funcionamiento cotidiano — entrega del 28/10/2026

[Captura descriptiva propia y rectificación verificadas](../../../docs/sistema-experto/FORMULARIO_FUNCIONAMIENTO_COTIDIANO_2026_10_28.md), con Medicina/Psicología expresamente aprobadas. Acceso desde la ficha psicológica y la valoración cognitiva/funcional original. Reutiliza `controles_funcionales_iadl` y sus catálogos, sin DDL, escala nueva ni acreditación automática de C03. El informe enlazado conserva la evidencia y las limitaciones de su corte.
