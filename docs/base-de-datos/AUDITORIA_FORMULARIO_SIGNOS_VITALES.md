---
title: "Auditoría: Nuevo registro de Enfermería / Signos vitales"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-07
owner: RememberMind
source_of_truth: false
verified_against_commit: 8e9e20325519c5da5a6b25f6fb26568cad78efe1
verification_scope: STATIC_REPOSITORY_REVIEW
runtime_verified: false
supersedes: []
related_docs: []
related_modules: []
---

> Evidencia localizada con partes propuestas: no autoriza nuevas columnas/reglas. Aplicar decisiones aprobadas posteriores en su alcance, incluida V2.2 para objetivos; consultar [índice](README.md).

# Auditoría: Nuevo registro de Enfermería / Signos vitales

Fuente: `REMEMBERMIND_BDD_70_TABLAS.md` (tabla 26), migration
`2026_09_18_001028_create_signos_vitales_table.php`, modelo `SignoVital`,
Livewire `MisPacientes`, servicio `SignosVitalesService` y pruebas de
`MisPacientesRedisenadaTest`.

La tabla real es `signos_vitales`. No se modifica su estructura.

| Campo del formulario | Clasificación | Persistencia real |
| --- | --- | --- |
| Fecha y hora | RELACIONADO | Por instrucción de la propietaria del 2026-10-07, el formulario de Enfermería fija `fecha_hora` desde el servidor al abrir el registro. No es editable y Livewire rechaza su modificación por el cliente. Activitylog registra por separado el momento de guardado y la cuenta autora. Los otros flujos conservan sus contratos de fecha clínica. |
| Turno | RELACIONADO | Se muestra desde el turno activo y se vincula mediante `cod_jornada` a `jornadas.cod_jornada`; no se acepta un identificador del cliente. |
| Registrado por | RELACIONADO | Usuario autenticado → personal activo → `cod_personal`; `cod_usuario` no es FK de esta tabla. |
| Sistólica y diastólica | EXISTENTE | `presion_sistolica`, `presion_diastolica` (`decimal(5,2)`). Captura entera. |
| Pulso y respiración | EXISTENTE | `frecuencia_cardiaca`, `frecuencia_respiratoria` (`decimal(6,2)`). Captura entera. |
| Temperatura | EXISTENTE | `temperatura` (`decimal(4,1)`). |
| SpO₂ | EXISTENTE | `saturacion_oxigeno` (`decimal(5,2)`). |
| Glucemia | EXISTENTE | `glucemia` (`decimal(8,2)`). |
| Observaciones | EXISTENTE | `observacion` (`text`, validación de hasta 5000 caracteres). |
| Posición | PROPUESTO | No hay columna ni relación; no se captura ni guarda. |
| Oxígeno suplementario | PROPUESTO | No hay columna ni relación; no se captura ni guarda. |
| Flujo de oxígeno | PROPUESTO | No hay columna ni relación; no se captura ni guarda. |
| Contexto de glucemia | PROPUESTO | No existe columna ni catálogo persistente por medición. |
| Medición ortostática | PROPUESTO | No hay entidad para la serie de posturas y tiempos. |
| Objetivo individual por parámetro | EXISTENTE V2.2 | `objetivos_signos_vitales` contiene límites, médico, motivo y versiones por vigencia; [decisión aprobada](DECISION_OBJETIVOS_SIGNOS_VITALES_V2_2.md). |
| Umbral crítico institucional | PROPUESTO | No existe regla formal persistida para este formulario. |

Las columnas adicionales de la tabla son `cod_signo` (PK), `cod_residente`
(FK), `cod_atencion` (FK opcional) y `estado`. El formulario resuelve el
residente desde el contexto autorizado, genera la PK y guarda `ACTIVO`;
`cod_atencion` queda nulo porque este flujo no abre una atención.

No se han identificado umbrales institucionales de advertencia para SpO₂ en
la tabla o en la documentación de la BDD operativa. La validación del nuevo
formulario comprueba el límite estructural 0–100 sin rechazar valores
clínicamente anormales que estén dentro de ese intervalo.

## Objetivos y comparaciones

`planes_cuidado.objetivo_general` e `intervenciones_cuidado.objetivo_especifico`
son texto libre. No identifican un parámetro vital ni contienen intervalos
numéricos verificables. Por ello no se interpretan como objetivos individuales
ni se usan para generar barras de rango, estados TARGET/ATTENTION/CRITICAL
o confirmaciones de desviación. Los objetivos estructurados provienen exclusivamente de la extensión V2.2 aprobada y se seleccionan según el momento clínico, incluso para versiones reemplazadas o retiradas cuyo intervalo incluye la medición. La comparación es descriptiva: última lectura anterior o simultánea del mismo parámetro; no representa un basal ni un diagnóstico.

La presentación de evolución muestra hasta cinco lecturas, con eje numérico y unidades. Las fechas válidas determinan la separación temporal; cuando faltan, se indica que la distribución es por orden de registro. Una sola lectura se presenta como punto, sin sugerir tendencia. Por instrucción de la propietaria, todas las series usan líneas continuas; la vista previa se distingue con marcador hueco y etiqueta «Sin guardar». PA conserva series separadas para sistólica y diastólica. Los puntos permiten consultar valor, fecha, clasificación y estado de alerta por teclado o pulsación; los colores clínicos proceden del backend.

La comparación muestra anterior, actual sin guardar y diferencia numérica, con ambas presiones por separado. La diferencia de SpO₂ se expresa en puntos porcentuales. Subir o bajar no equivale a mejoría o deterioro clínico. La escala se ajusta a los valores mostrados; las bandas representan únicamente objetivos médicos estructurados recibidos para esta lectura.

La referencia visual aprobada el 2026-10-07 se adapta al tono claro del sistema: una tarjeta integra valores destacados, gráfica y comparación. Las áreas degradadas turquesa/violeta identifican series independientes desde la misma base; no suman valores ni representan un porcentaje de cumplimiento. Se usan segmentos lineales entre observaciones, sin suavizar ni inferir valores intermedios. La selección muestra guía vertical y etiquetas de valor por serie; el detalle conserva fecha, gravedad y estado de alerta. SVG y Alpine existentes cubren este comportamiento sin nuevas dependencias.

El ajuste de UX suaviza las áreas. La clasificación actual acompaña al valor sin guardar; la comparación usa una tabla compacta con columnas Medición, Anterior, Actual y Cambio. La ayuda de lectura es desplegable y el detalle del punto puede cerrarse sin modificar la captura.

Por instrucción de la propietaria, enfocar una medición abre su gráfica y comparación al lado del formulario, sin bloquear la captura ni quitar el foco al campo; cambiar de campo actualiza la misma ventana. «Ver gráfica» ofrece otra entrada. El encabezado permite moverla mediante arrastre o flechas; los cuatro bordes y las esquinas permiten ajustar el tamaño mediante arrastre o flechas, manteniendo fijo el borde opuesto. Los botones de ampliar, reducir y restablecer ofrecen una alternativa; la ventana se mantiene dentro del viewport y se adapta al cambiar entre escritorio y móvil. Cerrar con el botón o Escape conserva valores, evaluación e historia y devuelve el foco al control de origen, sin reabrirse automáticamente. En escritorio amplio, la interpretación y la acción permanecen visibles en una columna junto a las mediciones; en pantallas pequeñas se apilan. Los criterios y la explicación completa pueden desplegarse. Solo después de la persistencia confirmada se muestra un popup de resultado con las mediciones, fecha y profesional; si hay valores críticos o advertencias, conserva su aviso de atención. Un fallo mantiene la captura y no muestra éxito. La cabecera distingue residente, ubicación, profesional, jornada y alertas existentes. La fecha y hora siguen automáticas y bloqueadas.

La acción recomendada conserva el texto clínico recibido de la regla. Para alertas automáticas ordena el flujo aprobado: verificar la lectura, confirmar el registro y documentar la intervención en la alerta para habilitar otros controles. Los textos para lecturas normales o sin recomendación automática explican cómo registrar y conservar contexto; no incorporan umbrales, tratamientos ni mecanismos de aviso nuevos.

## Validación y seguridad del flujo Nuevo registro

- PA: dos enteros o ambos vacíos; intervalo técnico 1–400 mmHg.
- Pulso: entero 1–300 lpm; respiración: entero 1–100 rpm.
- Temperatura: decimal con una cifra como máximo, 25–45 °C.
- SpO₂: decimal con dos cifras como máximo, mayor que 0 y hasta 100 %.
- Glucemia: decimal con dos cifras como máximo, mayor que 0 y hasta 999999.99.
- Observación: trim, máximo 5000 caracteres por validación vigente.
- Al menos una medición.
- Residente, permiso, profesional activo y asignación a jornada se revalidan
  en el servicio. `cod_personal` se resuelve del usuario autenticado.
  `fecha_hora` guarda el momento real de medición; no sustituye la jornada ni la autoría actuales autorizadas. La hora técnica queda en Activitylog.

## Continuidad aprobada el 2026-10-07

Una lectura crítica válida no se descarta para cambiar de formulario; se puede corregir una transcripción errónea. Tras confirmar, una alerta crítica de SIGNOS pendiente impide iniciar otros controles del residente en UI y backend. Registrar intervención deja la alerta EN_ATENCION y permite continuar; cierre/atención/anulación válidos también liberan el pendiente. Atención, reevaluación e indicaciones de medicación permanecen disponibles. Gravedad de la lectura y estado de la alerta se muestran por separado; el rojo histórico no pasa a verde por atender.

No se crean umbrales ni alertas automáticas adicionales para ALTO/ADVERTENCIA o SpO₂ sin objetivo. El contexto estructurado de técnica, oxígeno, síntomas y comidas continúa pendiente de decisión específica de BDD; la nota narrativa voluntaria existente no se interpreta como variables normalizadas.

La explicación muestra valor, unidad, intervalo y dirección de la alteración; los umbrales y la conducta clínica aprobada permanecen iguales. El verde expresa NORMAL u OBJETIVO_PERSONALIZADO, nunca ausencia de datos o de regla aplicable.

El botón «Limpiar campos» pide confirmación dentro del formulario y vacía únicamente las mediciones y la observación sin guardar. Conserva residente, autor, jornada, fecha/hora automática, objetivos e historia. El backend revalida contexto y permiso, y vuelve a evaluar antes de limpiar: una lectura crítica válida impide la limpieza aunque otro campo sea inválido. La corrección de una transcripción errónea sigue disponible en su campo.

SpO₂ sin objetivo médico individual vigente muestra «Sin objetivo médico» y «Clasificación pendiente» con un indicador azul informativo; mantiene severidad nula y no genera alerta automática. Con objetivo vigente, prevalece la evaluación médica V2.2 y su color correspondiente. La ausencia de objetivo nunca se representa como normal.

Investigación externa solicitada el 2026-10-07: [NICE CG50](https://www.nice.org.uk/guidance/CG50/chapter/recommendations) y [RCP NEWS2](https://www.rcp.ac.uk/media/a4ibkkbf/news2-final-report_0_0.pdf) distinguen revisión clínica urgente de respuesta de emergencia. Son fuentes de práctica en atención aguda, no un protocolo aprobado del centro ni equivalencia entre los colores de RememberMind y puntuaciones NEWS2. La definición local de destinatario, medio de aviso y activación de emergencia requiere decisión institucional; esta revisión no añade puntuaciones, tratamientos, llamadas ni notificaciones externas. Generar una alerta interna no acredita que se haya comunicado a un médico.
