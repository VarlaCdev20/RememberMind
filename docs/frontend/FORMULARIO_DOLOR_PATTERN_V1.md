---
title: "Dolor — implementación Clinical Form Pattern V1 y QA parcial"
status: HISTORICAL
version: "1.0"
last_reviewed: 2026-10-09
owner: RememberMind
source_of_truth: false
verification_scope: TARGETED_TESTS_AND_PARTIAL_RUNTIME_QA
runtime_verified: false
related_modules: [Enfermería]
related_docs: [../base-de-datos/AUDITORIA_FORMULARIO_DOLOR.md]
---

# DOLOR REDISEÑADO — RESULTADO

> Antecedente del lote inicial, conservado como historia. El contrato vigente
> de valoración inicial y reevaluación es [Dolor V2](FORMULARIO_DOLOR_V2_RESULTADO.md)
> y su [decisión estructural](../base-de-datos/DECISION_DOLOR_V2.md).
> Las clasificaciones y limitaciones descritas abajo no son el contrato actual.

## Alcance y fuente

Implementación del brief de la propietaria del 09/10/2026. Referencia: Signos Vitales aprobado y Clinical Form Pattern V1. Se reemplazó el formulario de Dolor de Mis residentes. Ingesta y los archivos de presentación/JS/CSS de Signos no se modificaron. Se conservaron los cambios ajenos que ya estaban en el checkout.

## Archivos

| Archivo | Cambio de este lote |
| --- | --- |
| `resources/views/livewire/cuidados/partials/mis-residentes-dolor.blade.php` | Captura EVA, localización, duración, desencadenante, intervención y continuidad |
| `resources/views/livewire/cuidados/partials/mis-residentes-dolor-grafica.blade.php` | Gráfica, comparación e historial únicamente en popup |
| `resources/views/components/ui/pain-body-map.blade.php` | SVG frontal/posterior y alternativa accesible por nombre |
| `resources/views/components/ui/clinical-trend-window.blade.php` | Ventana reusable con ocho bordes y controles accesibles |
| `resources/frontend/scripts/modules/dolor-registro.js` | Estado local, mapa, preview, normalización y geometría heredada de Signos |
| `resources/frontend/styles/design-system/patterns/dolor.css` | Superficies, composición y adaptación responsive de Dolor |
| `resources/frontend/scripts/app.js` | Import del módulo |
| `resources/frontend/styles/design-system/index.css` | Import del patrón |
| `resources/views/livewire/cuidados/mis-residentes-directorio.blade.php` | Contexto/modal/footer existentes, resultado persistido y captura estable durante descarte |
| `app/Frontend/Livewire/Enfermeria/Cuidados/MisPacientes.php` | Fecha bloqueada, DTO autorizado, resultado y error recuperable |
| `app/Backend/Modulos/Enfermeria/Servicios/CuidadosEnfermeriaService.php` | Fecha de servidor y rechazo de campos no soportados en registrarValoracionDolor |
| `tests/Feature/MisPacientesRedisenadaTest.php` | Persistencia, negativos, integridad, permisos y descarte |
| `tests/Frontend/dolor-registro.test.js` | Normalización, mapa, cardinalidades, delta, geometría y cierres durante guardado |
| `docs/base-de-datos/AUDITORIA_FORMULARIO_DOLOR.md` | Contrato del formulario y fecha automática |

## Campos y persistencia

- EVA inicial entera 0–10: selección obligatoria y vacía al abrir. Cero es una selección válida.
- Ubicación: texto existente, opcional, máximo 120 caracteres. El mapa añade/quita etiquetas en ese texto; no crea persistencia anatómica ni interpreta el texto manual.
- Duración: valor positivo + unidad libre; se almacenan juntos en `duracion`, máximo 80 caracteres.
- Desencadenante e intervención: texto opcional existente.
- Fecha/hora: banda sin input, propiedad Livewire bloqueada. El servicio rechaza fecha enviada y persiste `now()`; el resultado muestra la fecha realmente guardada, que puede ser posterior a la apertura.
- Autor: personal activo del usuario autenticado; residente conservado desde el contexto autorizado.
- Cada guardado crea una nueva valoración VIGENTE. No sobrescribe el historial.
- Sin tipo de dolor, EVA posterior, respuesta, reevaluación, observación general, prescripción ni reglas de severidad o alertas automáticas derivadas solo de EVA.

## Bodymap

Frontal/posterior, selección múltiple, chips removibles y limpieza. Enter/Espacio funcionan en las zonas SVG. La selección manual no colorea zonas por inferencia. No se añade una zona que exceda 120 caracteres. En móvil solo aparece la vista seleccionada; los botones por nombre ofrecen targets de 44 px cuando una región anatómica es pequeña.

## Popup

| Criterio | Resultado / evidencia |
| --- | --- |
| Gráfica fija dentro del formulario | Eliminada; se abre con Ver evolución |
| Historial | Hasta siete valoraciones reales VIGENTE del residente, solo con permiso de consulta; DTO plano |
| Ejes | Y fijo 0–10; X conserva los intervalos de fecha/hora |
| Preview | Punto hueco «Actual · Sin guardar» y conexión discontinua al último guardado |
| Diferencia | Solo cambio numérico, sin interpretación clínica |
| 0 / 1 / 1 + preview / 2+ | PASS en JS; navegador ejercitado con siete guardadas + preview |
| Drag | PASS con puntero: x836/y110 → x868/y131 aproximadamente |
| Resize | PASS con teclado en los ocho bordes; límites del viewport reutilizados |
| Reset | PASS con botón y Home; 560×680 desktop y altura inicial 280 en modo compacto |
| Tooltip | PASS con teclado en el punto actual; contiene fecha/condición sin guardar y EVA |
| Cierre / Escape | PASS: cierra únicamente popup, devuelve foco y conserva captura |
| Captura preservada | PASS: Cancelar → Seguir editando conserva EVA y chips; wrapper estable |
| Valores inválidos | Sin null/NaN/undefined/Infinity/[object Object] visibles en los estados observados; normalización estricta probada |

## Responsive y accesibilidad

Revisión en navegador real, modo claro, usuario de Enfermería autenticado y datos sintéticos documentados en SEED_HISTORICO_2026. No se guardaron las capturas de prueba en la BDD de desarrollo.

| Ancho objetivo | Ancho CSS observado | Resultado |
| --- | --- | --- |
| 1440 | 1440 | Formulario dos columnas; con popup abierto una columna y espacio lateral. Sin scroll horizontal |
| 1280 | 1279 y 1281 | Ambas medidas rodean el objetivo por el zoom del navegador. Sin scroll horizontal |
| 1024 | 1024 | Dos columnas cerrado / una abierto; controles y popup dentro del viewport |
| 768 | 768 | Una columna; popup compacto inferior con scroll propio |
| 390 | 390 | Una columna, tabs frontal/posterior, footer apilado; popup x8, ancho374, altura280; sin scroll horizontal |

- EVA, mapa, controles del popup y Escape ejercitados mediante teclado.
- Foco visible y labels/descripciones en campos; contador y delta con anuncio discreto.
- Icono del header y controles principales de 44 px; `min-height:72px` en header. El contenido del header puede crecer al envolver texto.
- Contraste observado de EVA seleccionada: texto rgb(247,243,239) sobre rgb(152,89,79), aproximadamente 4.9:1.
- Reduced motion: CSS desactiva transiciones/animaciones del formulario y popup; contrato comprobado en test de fuente. No se emuló esa preferencia en navegador porque la capacidad disponible solo permite cambiar viewport.
- Warning/high/critical de Signos: N/A. La card EVA representa los rangos de intensidad aprobados el 09/10/2026: 0 verde muy suave, 1–3 verde, 4–6 amarillo, 7–10 rojo; sin selección neutro. Son presentación de intensidad, sin alertas automáticas ni nuevas columnas. Rangos centralizados en `config/enfermeria.php`, documentados en la auditoría de Dolor.

## Pruebas y build

- PHP final: **29/29, 282 assertions**; incluye los 16 casos dirigidos de Dolor y regresiones de Signos/continuidad. SQLite en memoria.
- JS final: **55/55** (Dolor, Signos y feedback clínico).
- Build: `npm run build` PASS, Vite 8.3.1.
- Diff del lote sin errores de whitespace; index de Git vacío. Sin commit, staging ni push.
- No migraciones ni modificaciones de Models, esquema, permisos, Policies o reglas clínicas.
- No se introduce dependencia ni compatibilidad V1. El antiguo formulario de esta superficie fue reemplazado; otros flujos quedan fuera de este lote.

## Estados y límites de la verificación

Empty/focus/filled/validation-error/loading/disabled/popup/descarte revisados en navegador. Backend-error y success se verificaron en PHP con captura conservada y resultado persistido; no se forzó una caída del servidor ni se guardó una valoración de prueba en el navegador de desarrollo.

Evidencia local ignorada por Git: `storage/app/qa/dolor/desktop.jpg`, `popup-desktop.jpg`, `mobile.jpg`, `popup-mobile.jpg`. Informes PHPUnit: `storage/logs/dolor-pattern-final.xml` y cohortes anteriores.

**Visual QA: FAIL por cobertura incompleta, sin defecto restante observado en los estados examinados.** Pendientes: reproducir visualmente las cardinalidades 0/1 histórico, backend-error/success, preferencia de movimiento reducido y medida CSS exactamente 1280. La revisión de la propietaria del diseño también queda pendiente antes de continuar con Ingesta. Las pruebas de JS/PHP prueban comportamiento de esos estados, pero no sustituyen su aceptación visual.

La suite completa del archivo, ejecutada antes de este lote, tenía tres fallos ajenos: selector conecta controles y acciones con pantallas existentes; ficha rápida muestra acciones en footer y un solo drawer; hidratación clínica sucesiva conserva historial y autoría. No se alteraron pruebas o flujos ajenos para ocultarlos. No se afirma que toda la suite del repositorio esté verde ni que el módulo esté listo para liberar.

## Commit

**NONE**, por instrucción explícita. Detener este lote en Dolor para revisión visual.

## Actualización aprobada: color por intensidad EVA — 09/10/2026

La propietaria aprobó los límites investigados: sin selección neutro; 0 verde muy suave; 1–3 verde; 4–6 amarillo; 7–10 rojo. Se añaden descripción textual y anuncio accesible, además de cambiar fondo, borde, valor y opción seleccionada. `config/enfermeria.php` es la fuente única de los rangos de presentación; el navegador no define cortes propios. No se generan alertas, no cambia el guardado ni se persiste una nueva clasificación.

Verificación de esta actualización: 56/56 pruebas JS y 13/13 pruebas PHP de Dolor/EVA (90 assertions); build PASS. En navegador se comprobaron vacío, 0, 1, 3, 4, 6, 7, 10 y regreso a 2; todos muestran la descripción correspondiente. Teclado: flecha derecha de 3 a 4 actualiza texto y color. Móvil 390 y escritorio 1440 sin overflow horizontal; targets EVA de 44px. No se guardaron valoraciones de prueba. Capturas: `storage/app/qa/dolor/intensidad-verde.jpg`, `intensidad-amarillo.jpg`, `intensidad-rojo.jpg`. Se renovó la sesión de Enfermería con intervención de la propietaria; la pestaña antigua conservaba una captura de la mañana. No se altera el estado pendiente de la QA completa del patrón descrito arriba. Sin staging, commit ni push.
