# Correcciones seleccionadas de formularios — 14/10/2026

## Alcance

La propietaria pide conservar «Otro» en Eliminación y corregir los hallazgos visuales/UX de la auditoría. Se mantiene el diseño café grisáceo claro, la trama y los acentos existentes, con responsividad obligatoria. No se amplía este lote a los fallos de acceso, jornada HTTP o corrección histórica de Seguimiento.

## Implementación

- **Eliminación:** Otro permanece en color de orina, aspecto de orina y color de heces. `elimination-choice-group` reutiliza `ui.field` para la descripción condicional. Input requerido/habilitado solo cuando corresponde, límite de 250 caracteres y error cercano. Cambiar opción limpia su precisión; cambiar tipo limpia las precisiones incompatibles.
- **Backend de la captura:** `CuidadosEnfermeriaService::registrarEliminacion` valida textos, blancos, longitud, incompatibilidad y límite conjunto de narrativa. Conserva las precisiones etiquetadas en `observacion` junto con el texto manual. No añade columnas, catálogos ni relaciones. Residente/autor/jornada siguen derivados por el contrato existente; no se modifica historia anterior.
- **Cognición confirmada:** el encabezado pasa a Registro confirmado y se retira el bloque de revisión pendiente. La captura sin confirmar mantiene su identificación propia; un error no produce éxito.
- **Cognición móvil:** progreso/resumen por dominio desplegable, contexto compacto y navegación corta A–D con nombres accesibles completos. La primera respuesta queda visible por encima del footer a 390×900, protegida por una aserción del harness. Sin alturas vacías artificiales en las ayudas de la columna móvil.
- **Jerarquía:** captura con relieve principal; apoyo e historial con tinte discreto y sombra menor. Se conservan colores clínicos y de error.
- **Genéricos de Registros:** siete formularios reutilizan `ui.card`, `ui.section-header`, estilos canónicos de input/select/textarea, labels y botones. Se mantienen handlers, validaciones y protección de captura existentes.
- **Dashboards:** las superficies neutrales y la cabecera se resuelven desde la composición compartida. Se conservan los acentos de navegación por rol. El gate de consistencia se corrige en producto, sin cambiar expectativas.
- **Override antiguo:** `nursing-module.css` imponía fondos/sombras con `!important` a cards dentro de formularios clínicos. Esa regla deja de invadir los componentes de captura; el modo oscuro genérico también usa las superficies oscuras existentes.
- **Heridas:** el icono de cabecera deja de estar posicionado absolutamente sobre el texto, y utiliza el flujo del encabezado compartido. Sin cambios de registro/curación ni persistencia.

## Mapa de reutilización

| Función | Implementación existente utilizada |
|---|---|
| Descripción de Otro y errores | `ui.field`, `rm-input`, captura estructurada diferida |
| Card / encabezado de sección | `ui.card`, `ui.section-card`, `ui.section-header` |
| Modal y pie | `modal-livewire`, `quick-register-modal` |
| Apoyo e historial | `care-surfaces.css` y tokens de superficie/sombra |
| Navegación A–D | `focusSection` existente, conserva respuestas y foco |
| Confirmación / limpieza | Resultado del servidor y protección de captura existentes |

No se crea una biblioteca paralela ni se cambia la funcionalidad clínica para acomodar el diseño.

## Verificación

- `npm test`: **190 PASS**, cero fallos, incluido `shell-visual-consistency` en claro/oscuro.
- Build: **PASS**, aviso existente de bundle JS >500 kB.
- Regresiones nuevas de Eliminación: texto obligatorio, trim, tipos/longitudes inválidos, detalle residual, límite conjunto, texto manual preservado, ausencia de columnas nuevas y captura conservada al fallar.
- Regresión de Cognición: resultado persistido sin etiqueta pendiente y rechazo del segundo guardado de la misma confirmación.
- Selección PHP relacionada: **78 PASS / 625 assertions**, incluyendo Eliminación, Cognición, Seguimiento, Incidentes y Administración de medicación. No se declara ejecutada la suite PHP completa en este lote.
- PostgreSQL desechable: **3 PASS / 60 assertions** para las dos pruebas de Otro y la confirmación de Cognición. Runner crea un cluster propio y lo detiene/elimina al terminar; no usa la DB operativa.
- Harness general: **12 vistas × cinco anchos = 60 casos** en tema claro; **24 casos** adicionales en oscuro (1440/390). Incluye Signos, Dolor, Ingesta, Hidratación, Eliminación, Movilidad, nueva Herida, Seguimiento y cuatro secciones genéricas. Sin overflow horizontal ni errores JS. En Otro se comprueban visibilidad, required/disabled, sincronización y limpieza.
- Cognición y Conducta: harnesses Blade/Alpine PASS en **1440/1280/1024/768/390**, con movimiento reducido, foco, conservación de captura y popups.
- Medicación: harness actual **50 casos / 10 estados / cinco anchos**, sin errores. Se conserva el disclosure móvil ya presente en ese módulo.
- Galería CSS: 14 familias × cinco anchos PASS; estados clínicos preservados y contraste de pares muestreados aprobado. No certificación exhaustiva de contraste compuesto ni WCAG.
- Encabezado de Heridas, después del último ajuste CSS: cinco anchos PASS; captura móvil revisada sin solapamiento entre icono, título y subtítulo. El harness no acredita la carga del CDN de iconos en la sesión operativa.

Los harnesses usan Blade/Alpine y datos sintéticos con transporte simulado; no sustituyen las pruebas backend ni demuestran navegación de una sesión operativa. Se revisan las capturas de composición, Otro y móvil/oscuro. El primer lote PHP tuvo un fallo del nuevo fixture por no preparar jornada; se corrigió la preparación y se repitió el caso con 41 aserciones PASS. La selección relacionada completa se reejecutó después: 78 PASS / 625 assertions.

## Evidencias y límites

Evidencias locales: `%TEMP%/remembermind-forms-ux-qa/`, `%TEMP%/remembermind-cognicion-qa/correcciones-ux/`, `%TEMP%/remembermind-conducta-qa/correcciones-ux/`, `%TEMP%/remembermind-medicacion-ux-qa/evidence/`. Sondas/harness general conservados bajo `storage/app/qa/formularios-correcciones-ux/` (ignorado por Git). PG: `remembermind-ux-fix-pg.xml` y `.txt` en TEMP.

La auditoría integral mantiene pendientes B1–B4, B6 y el mínimo documental de Conducta. En particular, la corrección de Otro corresponde al escritor canónico de la captura; no declara resuelta la paridad de todos los endpoints HTTP (B3). Sin cambios nuevos de BDD, permisos ni reglas clínicas. Se retira el override visual directamente relacionado; no se declara limpieza global de legacy. Sin stage/commit/push por trabajo previo mezclado en el checkout.
