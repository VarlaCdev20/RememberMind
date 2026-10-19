# Fase 4 — Registro institucional y verificación en interfaz

Fecha: 19/10/2026, America/La_Paz. Código previo: `e7d2e3ff`. Alcance: registro de la base A–D adoptada y comprobación de su recorrido con Psicología. Esta entrega no implementa las fases 5–8 ni certifica desempeño clínico.

## Registro confirmado

- [x] Sesión de Psicología ya iniciada por la propietaria; navegación por la ruta existente `/admin/sistema-experto/reglas`.
- [x] Registro desde **Registrar base institucional adoptada**, confirmado en su diálogo.
- [x] Mensaje de éxito y selección `ORION-COGNITIVO-1 · ACTIVO` visibles.
- [x] Comprobación SQL posterior de solo lectura: una versión, 17 reglas activas, 17 revisiones PENDIENTES, cero firmas profesionales y un evento `contrato_institucional_registrado`.
- [x] Cinco dominios, 19 componentes, 68 variables y 19 mapeos observacionales conservados en las tablas existentes.
- [x] Paginación de las 17 reglas; las dos visuoespaciales aparecen en la segunda página.

La operación ocurrió a las **17:47:10** mediante la sesión autorizada. No se usó un cambio de identidad en terminal para registrar el conocimiento. No se modificaron registros clínicos, permisos, estructura ni revisiones profesionales. No se archivó ni sustituyó ninguna regla durante la comprobación.

## Recorrido de resultados comprobado

Se abrió una consulta autorizada de Psicología y se expandieron los cinco dominios y sus motivos de no emisión. Se comprobaron 19 componentes, 57 observaciones visibles, las quince G09 y 47 condiciones insuficientes —45 de G09 y dos de Memoria—. La versión utilizada fue la nueva revisión institucional; Memoria mostró EV-CM-1. Las observaciones mantuvieron fechas, autor y referencia original. No había mediciones normalizadas vinculadas a esos 19 componentes.

Esto acredita que el paquete registrado ya participa en la consulta de la instalación. No acredita un nuevo resultado Petersen: las observaciones no satisfacen por sí solas antecedentes instrumentales. La misma pantalla conserva una evaluación de demostración anterior identificada por su procedencia; ese patrón histórico no fue generado por la nueva base y no se convirtió en una valoración clínica. La presentación integral e historial siguen dentro de la fase 7.

No se guardó una evaluación de residente ni se confirmó/rechazó un resultado. La evidencia privada de este recorrido es agregada; las capturas guardadas para QA corresponden al gobierno de conocimiento, sin expedientes clínicos.

## Prueba funcional de condiciones

Desde el gobierno se abrió `R-ATE-S01-P01`:

1. Sin antecedentes: la conjunción no cumple.
2. Con C1, C2 y C3 ACREDITADA en la prueba manual: cumple.
3. El formulario informa que no se creó una evaluación ni una revisión profesional.

La prueba manual utiliza entradas efímeras del gobierno y no valida el origen clínico de un predicado. La admisión de fuentes reales y perfiles continúa en el ejecutor institucional del [contrato A–D](FASE_4_CONTRATO_AD_INTEGRADO_2026_10_19.md).

## Hallazgo visual corregido

La comprobación inicial fue FAIL para los selectores: altura de 40 px, interlineado de 40 px y padding vertical de 8 px recortaban la opción seleccionada. Los textarea utilizaban también la clase de input de una sola línea.

La corrección se limita a `reglas-panel.blade.php`: selectores con el componente CSS existente `rm-select`, interlineado normal y mínimo de 44 px; textarea con `rm-textarea`, que conserva altura multilineal. No se alteran tokens globales, paleta, arquitectura ni acciones del backend. Después de compilar y recargar: selectores de aproximadamente 44 px, interlineado de 21 px, textarea de aproximadamente 96 px, texto completo y foco visible.

## Matriz de verificación visual acotada

Superficie: gobierno, selector de versión, tabla/paginación y prueba de condiciones, tema claro. Referencia: contrato visual institucional existente; sin rediseño. Se preservó el zoom del navegador, compensando las dimensiones de la prueba; los anchos efectivos se midieron en el DOM.

| Ancho objetivo | Ancho CSS efectivo | Reflow, legibilidad y foco de los controles examinados |
| --- | --- | --- |
| 390 | 391 | PASS; la tabla tiene desplazamiento interno y no desborda el documento |
| 768 | 768 | PASS |
| 1024 | 1024 | PASS |
| 1280 | 1281 | PASS |
| 1440 | 1441 | PASS |

No se observaron `null`, `NaN`, `undefined`, `Infinity` ni `[object Object]` en el contenido examinado. La tecla Tab mueve el foco entre los antecedentes y deja un indicador visible. Los selectores mantienen sus labels. El override temporal de viewport se retiró al terminar; no se cambió el zoom ni el tema de la propietaria.

**PASS visual/funcional acotado al recorrido descrito.** No es aceptación global de accesibilidad, contraste, tema oscuro, formularios de edición/archivo/revisión, ni responsive de todos los expedientes. No se provocaron fallos de red ni se ejecutaron mutaciones clínicas para reproducir estados; su comprobación corresponde a las pruebas del backend y al cierre integral de fase 7.

Una pestaña dejó de responder durante una reapertura posterior. Se inspeccionó el diálogo y el inventario, y se abrió una pestaña nueva en el mismo navegador; la base registrada quedó visible. No se repitió a ciegas el envío ni se atribuyó éxito a la interacción que agotó su espera.

## Pruebas y evidencia

- **28 pruebas / 224 aserciones SQLite, PASS**, sobre el candidato aislado sin `.env`: `GobiernoReglasExpertasTest|test_registro_desde_livewire|test_observaciones_de_19_componentes`. Incluye autorización, revisión, sucesión, prueba manual, registro y proyección. No sustituye la verificación visual.
- **Build Vite 8.3.1, PASS**, 107 módulos, después del ajuste de controles. No hubo cambios de persistencia que justificaran repetir PostgreSQL; permanece vigente la evidencia de 231 pruebas del incremento anterior para su alcance.
- Evidencia local privada: `storage/app/private/fase4-evidencia/registro-institucional-20261019.png`, `base-institucional-visible.png`, `gobierno-{390,768,1024,1280,1440}-final.png`, `viewports-gobierno.json`, `consulta-institucional-agregada.json`, `build-registro-ui.log` y `fase4-instalacion-ui-sqlite.xml`.
- Evidencia, credenciales y originales clínicos quedan excluidos del commit. Se preservan los cambios simultáneos de horarios y proveedor modular.

El siguiente trabajo es la **fase 5: integración institucional de Petersen**, con sus fuentes originales y abstenciones. Los perfiles interpretativos no definidos y las medidas no aportadas mantienen su estado faltante. La fase 6 conserva el pendiente de guardar/recuperar íntegro, y la fase 7, el cierre global de presentación y revisiones.
