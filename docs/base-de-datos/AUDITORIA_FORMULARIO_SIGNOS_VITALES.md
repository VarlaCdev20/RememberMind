# Auditoría: Nuevo registro de Enfermería / Signos vitales

Fuente: `REMEMBERMIND_BDD_70_TABLAS.md` (tabla 26), migration
`2026_09_18_001028_create_signos_vitales_table.php`, modelo `SignoVital`,
Livewire `MisPacientes`, servicio `SignosVitalesService` y pruebas de
`MisPacientesRedisenadaTest`.

La tabla real es `signos_vitales`. No se modifica su estructura.

| Campo del formulario | Clasificación | Persistencia real |
| --- | --- | --- |
| Fecha y hora | RELACIONADO | `fecha_hora` (`datetime`) se fija con `now()` del servidor al guardar; no se aceptan campos editables. |
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
| Objetivo individual por parámetro | PROPUESTO | No existe entidad estructurada de mínimo/máximo, vigencia, autor y versión para signos vitales. |
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
o confirmaciones de desviación. El formulario muestra el estado NEUTRAL y
compara con los últimos registros vigentes del mismo residente. Cualquier
objetivo estructurado requiere diseño clínico y aprobación de la BDD congelada
antes de su incorporación.

## Validación y seguridad del flujo Nuevo registro

- PA: dos enteros o ambos vacíos; máximo técnico 999.
- Pulso y respiración: enteros no negativos, máximo técnico 9999.
- Temperatura: decimal con una cifra como máximo y capacidad -99.9 a 999.9.
- SpO₂: decimal con dos cifras como máximo, 0–100.
- Glucemia: decimal con dos cifras como máximo, no negativa y hasta 999999.99.
- Observación: trim, máximo 5000 caracteres por validación vigente.
- Al menos una medición.
- Residente, permiso, profesional activo y asignación a jornada se revalidan
  en el servicio. `cod_personal` se resuelve del usuario autenticado.
  `fecha_hora` se toma al confirmar; un `fecha` o `hora` enviado por cliente
  no sustituye la hora del servidor.
