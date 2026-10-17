# CONDUCTA V2 — RESULTADO

Fecha: 13/10/2026. Estado: **IMPLEMENTED / VERIFIED_TESTING**. Alcance: Conducta independiente de Enfermería. Sin stage, commit ni push. La referencia y sus adaptaciones están en [CONDUCTA_V2_REFERENCE_ANALYSIS.md](CONDUCTA_V2_REFERENCE_ANALYSIS.md).

## Integración

Mis residentes → + Registrar → Conducta abre `RegistroConducta` con el residente actual. La ruta existente de Seguimiento con `cuidado=conducta` muestra la misma implementación; Cognición y Seguimiento diario general conservan sus flujos. El HTTP existente delega a `RegistrarConducta`, eliminando la validación duplicada que omitía participación y cambio de conducta.

Modal con cabecera/contexto/pie fijos, cuerpo desplazable, distribución 66/34 y 64/36, controles de tres estados y campos de texto con «Otro / escribir». Estado de ánimo utiliza un grupo de radios nativos. Las ayudas añaden frases visibles y editables a columnas existentes. Se reutilizan modal, field, selector, validación, resultado y protección de captura. Contexto móvil conserva acceso al profesional y fecha mediante disclosure. La fotografía utiliza la columna existente `residentes.foto` y el mecanismo de almacenamiento actual, con iniciales como fallback cuando falta o falla la imagen. No se inventa unidad de residencia.

Al volver al residente se recarga el resumen autorizado. Los registros vigentes de Conducta se incorporan al seguimiento reciente con su fecha y texto reales cuando existe permiso de lectura; no se modifica la historia de otros registros.

## Visual fidelity

| Parte | Resultado |
|---|---|
| modal | PASS |
| header | PASS |
| resident | PASS — contexto real; iniciales como fallback |
| conduct section | PASS — seis indicadores, sin score ficticio |
| mood | PASS — radios nativos / Otro con texto libre |
| description | PASS |
| intervention | PASS |
| response | PASS |
| chart | PASS — conteos confirmados; 0/1/múltiples fechas |
| history | PASS — datos originales, filtro y paginación |
| footer | PASS — limpiar, cancelar y confirmar; fijo |

PASS de composición adaptada al esquema, no copia de diagnósticos, intensidad, riesgo ni recomendaciones de la referencia. QA sobre Blade de producción renderizado por pruebas, CSS compilado y Alpine real. El transporte del harness es sintético; no acredita navegación en la sesión operativa del navegador de Codex. No se reintentó el acceso a la pestaña operativa previamente bloqueado por revisión automática.

## Persistence

| Campo | Resultado |
|---|---|
| estado_animo | PASS |
| apatia | PASS |
| agitacion | PASS |
| agresividad | PASS |
| ansiedad | PASS |
| aislamiento | PASS |
| deambulacion | PASS |
| participacion | PASS |
| cambio_conducta | PASS |
| descripcion | PASS |
| intervencion | PASS |
| respuesta | PASS |

`null`, `false` y `true` se conservan distintos. Textos vacíos/espacios se normalizan a null; límites físicos 40/30, sin catálogo cerrado inventado. Cada confirmación crea una fila VIGENTE nueva y auditoría dentro de la misma transacción. Residente, autor, jornada y fecha proceden del servidor. Atención opcional: mismo residente. Captura mínima y completa verificadas; nada se sobrescribe ni elimina.

## Seguridad

Permiso existente `registros_conductuales.crear` para ENFERMEROS aprobado explícitamente por la propietaria el 13/10/2026. Seeder actualizado; base local `remembermind_dev` comprobada: permiso ya presente, 73 grants antes/después, otros roles sin cambios, cero registros clínicos creados. No se ejecutó el seeder completo contra la base operativa.

Se reutiliza autorización clínica y alcance de Turno: cuenta/personal activos, permiso, competencia, residente admitido, turno y asignación vigentes y continuidad existente de Signos. Fuera de turno: consulta de los residentes propios y sin escritura. Probados permisos revocados, personal/cuenta inactivos, residente ajeno/egresado, atención ajena y Superadministración sin escritura ordinaria. Metadatos manipulados no cambian autoría/contexto. No se cambian las capacidades de Psicología ya permitidas por el HTTP existente.

## Schema changes

NONE — este lote no modifica schema ni migraciones. El árbol contiene cambios previos de otros lotes, conservados.

## Unsupported fields added

NONE

## Derived chart

FACTUAL. Frecuencia de registros por fecha, nunca gravedad clínica. Solo fechas con registros: sin ceros ni tendencias rellenadas. Los campos actuales no incrementan la gráfica hasta persistir. Se excluyen registros anulados, otros residentes y fechas fuera del periodo; el historial mantiene los vigentes antiguos. La representación vigente es una línea con puntos y área tenue entre fechas guardadas, con espaciado temporal real. Un punto no genera línea ni área. El detalle ofrece fecha/conteo/conductas marcadas mediante cursor, selección y botones de fechas accesibles de 48 px. La ausencia de datos no significa ausencia de episodios.

## Clinical diagnosis generated

NONE

## Responsive

| Ancho CSS exacto | Resultado |
|---|---|
| 1440 | PASS |
| 1280 | PASS |
| 1024 | PASS |
| 768 | PASS |
| 390 | PASS |

Sin overflow horizontal; dos columnas desktop, una hasta 768; historial en cards y pie fijo. Revisadas capturas superiores, inferiores, historial, selección/Otro y densidad de 15 fechas. A 1440 el modal mide 1400 px de ancho; viewport de 900 px de alto.

## Accessibility

PASS del alcance comprobado: radio groups nativos con fieldset/legend, estados textuales además de color, labels y errores asociados, cierre/limpieza confirmados sin justificativo, tab trap del historial, Escape y retorno de foco, «Otro» enfoca el campo habilitado, tooltip con foco, movimiento reducido real mediante emulación del navegador. No se afirma certificación WCAG.

## Tests

- SQLite final: 25 casos de Conducta + 9 de sidebar + 1 regresión del resumen del residente; 35 PASS / 181 assertions. Incluye regreso al residente y recarga del seguimiento reciente.
- Regresión cercana: 60 PASS / 3582 assertions (Conducta, sidebar, Seguimiento diario, navegación, matriz de roles y organización de carpetas).
- PostgreSQL final: 25 PASS / 121 assertions en cluster y base desechables propios. Prueba de fallo real mediante trigger: captura permanece, error seguro, sin evento de éxito, sin registro ni auditoría parcial.
- JS: 177 PASS, 0 FAIL, 0 skips.
- QA sintética: cinco tamaños exactos, reduced motion, cierre con cambios, limpieza confirmada, Otro con foco y texto, historial y retorno de foco, tooltip, 0/1/15 fechas: PASS. Sin `null`, `NaN`, `undefined`, `Infinity` ni `[object Object]` generados en contenido visible.
- Pint: PASS en archivos nuevos de PHP. Diff check: sin errores.

**PHP full: FAIL, fuera de alcance.** Se detuvo en `ApplicationBoundaryTest::test_aplicacion_no_depende_de_http_sesion_ni_implementaciones_externas`: `ConsultaCentralCuidados.php:134` utiliza `request()` dentro de Aplicación. Resultado hasta detenerse: 179 tests, 2756 assertions, 1 fallo y 2 skips previos de gates PostgreSQL. Ese archivo ya estaba presente sin commit al iniciar Conducta; no se modifica en este lote. No se declara aceptación global del repositorio.

## Build

PASS. Vite completado. Aviso existente de bundle mayor de 500 kB; sin error de build. Importación JS/CSS y registro de Livewire/vistas conectados; cache Blade local limpiada.

## Ajuste de selección solicitado

Opciones seleccionadas más visibles: menta, azul, lavanda, turquesa y terracota procedentes de tokens existentes, texto más grueso, borde reforzado y relieve sutil. Los tonos distinguen grupos de captura y no significan severidad clínica. «Sin registrar» mantiene una superficie neutral. Las ayudas de texto se resaltan mientras su frase permanezca en el campo; si se elimina, desaparece el resaltado. Participación también destaca su selección.

Verificado en el harness sintético: cinco viewports PASS, varias familias de color distinguibles, contraste de texto seleccionado ≥4.5:1 y limpieza/cierre intactos. Dos pruebas de render/consulta PASS (12 assertions), build PASS. Evidencia adicional: `%TEMP%/remembermind-conducta-qa/colores/conducta-selected-colors.png` y `selected-colors.json`. No se modifica backend, esquema ni permisos. Sin stage, commit o push.

## Evidencia reproducible

### Evolución en popup separado

La gráfica sale del panel lateral: «Ver evolución» abre un popover independiente, reutilizando exclusivamente la geometría y los gestos de la ventana de Signos Vitales. Se puede mover, ampliar, reducir y restablecer; los límites mantienen la ventana dentro del viewport. En desktop ≥1280 el formulario se acomoda a la izquierda; por debajo el popup se superpone sin reducir el formulario. En móvil usa el ancho disponible y una altura ajustada a la cantidad de fechas.

La gráfica muestra valores y fechas visibles, barras más anchas y un área de detalle reservada que no tapa las barras ni el selector. El periodo continúa consultando la fuente autorizada existente. Cierre/Escape devuelve foco y conserva la captura; abrir historial cierra primero la gráfica. No se hereda evaluación clínica de Signos ni se modifica su código.

Pruebas de este ajuste: Conducta PHP 25 PASS / 121 assertions; render final 2 PASS / 12 assertions; JavaScript Conducta + Signos 37 PASS; build PASS. QA sintética en los cinco viewports PASS, incluyendo abrir/cerrar, mover/redimensionar con teclado, captura modificada conservada, 0/1/15 fechas y foco. Capturas del viewport exacto (sin full-page, que redimensionaba temporalmente la ventana flotante durante la captura) en `%TEMP%/remembermind-conducta-qa/popup/`. No se acredita navegación de la sesión operativa. Stage/commit/push NONE.

### Cambio solicitado a línea, sin barras

La línea SVG reutiliza el patrón de render de Signos, sin añadir una dependencia ni cambiar ese formulario. Se retiraron las reglas y el markup de barras. Puntos con halo y detalle al pasar el cursor; relleno menta al 13%; leyenda y ejes legibles. Las fechas de los puntos se posicionan por sus intervalos reales, sin inventar días ni valores intermedios. Para densidad alta, los valores aparecen en el punto seleccionado y todos siguen disponibles en «Consultar fechas y valores». Solo se muestra línea/área con al menos dos fechas válidas. Datos no representables se excluyen sin coerción de null a cero.

Verificación final de este cambio: 2 pruebas PHP de fuente/render PASS (13 assertions), 39 pruebas JS Conducta + regresión Signos PASS y build PASS. QA sintética de 1440/1280/1024/768/390 PASS: gráfica visible, popup/foco/captura conservados, 0/1/15 fechas, selección con teclado, alternativa táctil ≥44 px, línea sin NaN y ausencia de línea ficticia con un único registro. Capturas revisadas en `%TEMP%/remembermind-conducta-qa/linea/`. Backend, BDD y permisos sin cambios. Stage/commit/push NONE.

`RegistroConductaTest`, `conducta-registro.test.js` y `tests/Support/conducta-visual-qa.mjs`. Exportación sintética mediante `RM_CONDUCTA_QA_DIR`. Evidencias locales en `%TEMP%/remembermind-conducta-qa/evidence/`: `result.json`, capturas de cinco viewports, captura, historial, empty state y densidad. JUnit/logs: `remembermind-conducta-regression`, `remembermind-conducta-final`, `remembermind-conducta-pg-final`, `remembermind-conducta-full`, `remembermind-conducta-js`, `remembermind-conducta-build`.

Caveman Cloud no disponible por MCP/CLI en esta sesión; no se atribuyen métricas de costo ni ahorro.

## Legacy

No nuevos nombres V1 ejecutables. Eliminada la definición HTTP duplicada de Conducta. El registro combinado de Seguimiento diario sigue siendo un contrato distinto: no se reescriben su historia ni los otros formularios.

## Commit

NONE. Stage NONE. Push NONE. Cognición no modificada.

## Pendientes

Resolver el fallo global de `ConsultaCentralCuidados` en su lote y comprobar la navegación de la sesión operativa cuando el acceso de navegador esté disponible. La evidencia visual sintética y el backend real de testing no se presentan como certificación de producción.

## Claridad de la gráfica — 13/10/2026

Por indicación de la propietaria, la ventana y su acceso ahora se llaman **Registros de conducta por fecha / Ver gráfica**. Muestran cantidad de registros confirmados por día, no intensidad, gravedad, estabilidad ni mejoría. La aclaración permanece visible junto al gráfico.

Línea menta más definida (4 px), puntos de 18 px, área translúcida de 0.20, fechas/valores con mayor peso y total destacado. Altura inicial ajustada dentro del viewport para conservar la gráfica completa. No cambia el cálculo ni la persistencia.

Verificación: 3 pruebas PHP / 26 assertions (cero/uno, conteos y consulta); 11 pruebas JS de Conducta/Cognición; build PASS; QA con Blade/Alpine y transporte sintético PASS en 1440/1280/1024/768/390, incluyendo movimiento, resize, foco, tooltip y ausencia de tendencia con un punto. Evidencia: `%TEMP%/remembermind-conducta-qa/conteos-claros/`. Sin commit, stage ni push.
