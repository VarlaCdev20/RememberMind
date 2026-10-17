# Conducta V2 — referencia y contrato

Fecha: 13/10/2026. Rama vigente: `feature/institucion-asignaciones-planificadas`. Sin stage, commit ni push.

## Referencia revisada

Imagen de la propietaria: `codex-clipboard-df2b9e44-9324-428e-84f7-94c1305aab32.png`, 1672 × 941 px. Modal centrado, cabecera y contexto horizontales, contenido compacto en dos columnas aproximadas 66/34, fondo cálido, títulos azul tinta, numeración verde, campos pequeños y pie fijo. Izquierda: conductas, contexto, nivel, señales, intervención y respuesta. Derecha: evolución, interpretación, acciones, historial y riesgo. La referencia queda identificada sin copiar sus datos personales a fixtures.

Adaptaciones obligatorias: la BDD no contiene intensidad, riesgo, duración ni frecuencia estructurados. No se replica el score 0–10, las bandas de gravedad, el diagnóstico ni recomendaciones/reevaluación automáticas. El panel derecho muestra conteos de registros reales, resumen factual e historial. Contexto e intervención utilizan ayudas explícitas que añaden texto editable; no códigos ocultos. Una sola fuente para cada booleano, preservando null/false/true. Sin borrador persistido porque no existe el caso de uso.

## Fuentes y conflicto

Vigentes: instrucción actual, baseline congelado y diccionario `registros_conductuales` (sección 40), migración `2026_09_18_001041`, modelo `RegistroConductual` y autorización contextual de Cuidados. El esquema ya contiene participación y cambio de conducta; el registro HTTP omite ambos en su validación (CONTRACT_DRIFT). El seguimiento diario agrupa varios dominios, precarga booleans false y actualiza un registro ligado a atención; no es el contrato de un episodio independiente. Se conserva ese flujo de otros formularios.

La propietaria autorizó explícitamente `registros_conductuales.crear` para Enfermería el 13/10/2026, con turno activo, residente asignado y autoría propia. Se reutiliza el permiso existente; no se agregan catálogos, rutas clínicas ni columnas. No se cambia la competencia de los demás roles. Lectura fuera de turno solo de residentes asignados conforme al alcance vigente; escritura requiere el contexto efectivo.

## Mapa de captura

UI de Conducta / state Livewire `datos` / operación `RegistrarConducta` / modelo `RegistroConductual` / tabla `registros_conductuales`. Estado de ánimo y participación son strings con máximos físicos 40/30; booleanos nullable mantienen tres valores; descripción, intervención y respuesta son texto editable. Cada guardado crea una fila nueva VIGENTE; autor, fecha y jornada proceden del servidor. Atención opcional, cuando se usa la entrada HTTP existente, se valida dentro del residente actual. El modal contextual no pide elegir residente otra vez.

El contexto visual utiliza `residentes.foto`, columna existente del diccionario y modelo, mediante el almacenamiento actual. Si no hay fotografía o no carga, muestra iniciales. No se incorpora una unidad sin fuente. Estado de ánimo presenta radios nativos y «Otro / escribir»; la selección permanece como texto en su columna actual.

La consulta histórica reautoriza el residente, usa exclusivamente registros del residente y devuelve datos planos. Orden fecha DESC/código DESC para desempates. Cero registros: empty state. Un registro: una barra real, sin tendencia. Conteos no son severidad; ausencia de registros no significa ausencia de episodios. La captura actual no aumenta los conteos hasta persistir.

## Implementación y verificación previstas

Reutilizar quick-register-modal, field, callout, componentes de resultado, tokens y protección de cambios. Conducta mantiene una composición propia sin modificar Signos, Dolor, Eliminación, Movilidad o Heridas. Desktop 1440/1280 dos columnas; 1024 compacto; hasta 768 una columna y en 390 historial apilado. Teclado, foco, retorno, limpieza confirmada, doble envío, error conservando captura y reduced motion.

Pruebas por persistencia real y efectos, permisos y contexto, registros ajenos, null/false/true, textos trim, nuevos registros y orden. Build y QA sintética de cinco viewports. Las evidencias anteriores de Heridas no acreditan Conducta. Caveman Cloud: sin MCP/CLI disponible; no se atribuyen costos ni ahorros.
