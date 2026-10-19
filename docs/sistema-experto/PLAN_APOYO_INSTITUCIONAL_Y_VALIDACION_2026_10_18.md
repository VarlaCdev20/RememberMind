# Sistema experto — transición a apoyo institucional y validación

Fecha: 18/10/2026. Estado: propuesta de ejecución basada en la solicitud actual de la propietaria. Este documento identifica trabajo y criterios de aceptación; no registra una validación clínica ya realizada ni activa conocimiento.

## Objetivo

Obtener una herramienta que ayude a Medicina y Psicología a revisar datos del residente, identificar hallazgos respaldados y documentar sus valoraciones separadas. Cada resultado debe reconstruirse desde registros originales y conocimiento versionado. La utilidad asistencial debe evaluarse para el propósito y la población definidos.

El laboratorio continuará sirviendo para pruebas. La pantalla del residente deberá mostrar el resultado del recorrido institucional cuando este cumpla su contrato. Un cambio de aviso no convierte una entrada sintética en un dato observado ni acredita una interpretación.

## Fuentes de verdad y diagnóstico actual

- Instrucción actual de la propietaria: pasar de una demostración experimental a apoyo real y útil, con revisión profesional.
- Arquitectura vigente: motor independiente en `motor-experto`; integración Laravel en `app/Modulos/ApoyoDecision`. No crear otro motor o módulo.
- [Aceptación Fase 01](FASE01_ACEPTACION_INTEGRAL_2026_10_18.md): demuestra ejecución técnica y explica las brechas institucionales.
- [Auditoría G08](G08_AUDITORIA_INSTRUMENTAL_EXPERIMENTAL.md): **0/15 perfiles interpretativos admitidos**. Las cuatro tareas específicas de Memoria tampoco tienen un contrato instrumental ejecutable completo.
- [Formalización observacional](FORMALIZACION_FUNCIONAL_V0_1_2026_10_17.md): tres reglas literales aprobadas y conservación posterior aprobada mediante dos FK. No acredita recurrencia ni DCL.
- [Revisión profesional](ENTREGA_REVISION_PROFESIONAL_2026_10_17.md): dictámenes separados implementados; su alcance actual rechaza resultados experimentales.
- `config/sistema_experto.php`: `lectura_clinica` está vacío. Es un registro de contratos de publicación; rellenarlo no valida ni activa por sí mismo la inferencia.
- [RFC de conservación](RFC_CONSERVACION_PERFIL_EXPERIMENTAL_2026_10_18.md): el manifiesto compuesto y todas las instantáneas de origen todavía requieren resolver su representación institucional.
- [Índice BDD](../base-de-datos/README.md): reutilizar las extensiones aprobadas. Este plan no propone nuevas tablas ni autoriza cambios estructurales adicionales.

La petición actual cambia el objetivo de la siguiente entrega. Los expedientes experimentales anteriores siguen siendo evidencia de ingeniería; no son una aprobación de baremos, instrumentos o publicación asistencial.

## Entregas y checklist

### 1. Apoyo observacional con el recorrido existente

- [x] Localizar extracción autorizada de `controles_cognitivos`: `ConsultarHallazgosObservacionales`.
- [x] Localizar reglas existentes: `OBS-MEM-01` repetición de preguntas, `OBS-MEM-02` olvido de indicaciones, `OBS-COG-01` cambio cognitivo señalado.
- [x] Localizar guardado y recuperación: `EjecutarObservaciones`, `ConservacionObservacional` y `ConsultarObservacionesConservadas`.
- [x] Localizar autorización: Medicina/Psicología para guardar; Enfermería consulta según su alcance vigente.
- [ ] Acreditar el recorrido completo en la base y sesiones destinadas al piloto: fuente admisible → ejecución explícita → conservación → recuperación → explicación.
- [ ] Acreditar visualmente fecha, autor, dato original, condición y motivo de cada hallazgo; distinguir ausencia de datos y resultado negativo.
- [ ] Comprobar el tratamiento de revisión aplicable a una evaluación observacional. Reutilizar las cadenas profesionales existentes solo después de contrastar su contrato y alcance.

Aceptación: mostrar lo que está documentado, con procedencia y límites. Estas tres observaciones no calculan deterioro, recurrencia, severidad ni Petersen. Los registros ficticios o generados para pruebas mantienen su procedencia.

### 2. Conocimiento clínico y contratos interpretativos

- [ ] Fijar con Medicina/Psicología el propósito, población, decisiones apoyadas y exclusiones de la primera versión.
- [ ] Elaborar una ficha por instrumento/tarea: edición, idioma, aplicación, puntuación, población normativa, edad/escolaridad, derechos de uso, interpretación y confusores.
- [ ] Elaborar una ficha por regla: fuentes exactas, entradas, condiciones, salida, abstenciones y casos esperados.
- [ ] Registrar la revisión profesional real de esas fichas, con responsables, fecha y versión. No precargar aprobaciones.
- [ ] Implementar exclusivamente los perfiles aceptados, conservando puntuación original y transformación aplicada.

Aceptación: cada interpretación tiene fundamento y aplicabilidad demostrables. Las tareas sin perfil suficiente producen una abstención explícita. La entrega puede avanzar por capacidades verificables sin fingir cobertura completa.

### 3. Integración de cinco dominios y Petersen

- [ ] Sustituir las entradas artificiales del recorrido institucional por adaptadores de fuentes admisibles, sin modificar los fixtures del laboratorio.
- [ ] Obtener soporte independiente para C01 cambio, C02 alteración objetiva, C03 autonomía/IADL, C04 diferencial y C05 calidad/evaluabilidad.
- [ ] Verificar que los estados de dominio se derivan de sus perfiles aceptados; un hallazgo observacional o una puntuación global no acredita automáticamente A/P.
- [ ] Mantener desconocidos, contradicciones y exclusiones; no completar campos clínicos mediante valores predeterminados.

Aceptación: cada candidato o abstención puede explicarse desde sus fuentes. La clasificación solo se publica dentro del propósito profesional aprobado.

### 4. Conservación institucional completa

- [ ] Resolver el manifiesto de versiones y la matriz de instantáneas por fuente, reutilizando el esquema actual.
- [ ] Si aparece un cambio estructural indispensable, presentar el diccionario mínimo concreto y obtener la aprobación exigida por `database/AGENTS.md` antes de aplicarlo.
- [ ] Probar guardado/recuperación exactos, inmutabilidad histórica, idempotencia, rollback y aislamiento entre residentes en SQLite/PostgreSQL desechables.

Aceptación: el resultado y su explicación permanecen iguales aunque después cambien una fuente o la versión vigente. El historial temporal de sesión del laboratorio no satisface esta entrega.

### 5. Validación con referencia profesional independiente

- [ ] Diseñar con los profesionales los casos autorizados, criterios de inclusión, resultados de referencia y criterios de aceptación antes de medir.
- [ ] Obtener las valoraciones de referencia antes de mostrar la respuesta del sistema a sus evaluadores.
- [ ] Separar los casos usados para ajustar reglas de aquellos usados para evaluar su funcionamiento.
- [ ] Registrar coincidencias, omisiones, falsos positivos, abstenciones, errores y sus causas; calcular métricas solo cuando la referencia y la muestra lo permitan.
- [ ] Hacer un piloto autorizado con supervisión y seguimiento, con criterios de suspensión y revisión de versiones.

Aceptación: evidencia de desempeño para el uso y población definidos. La confirmación/rechazo después de ver la salida sirve para revisar un caso; no sustituye la referencia independiente utilizada para validar el sistema.

### 6. Publicación y presentación profesional

- [ ] Registrar la decisión institucional de publicación de la versión y sus límites, a partir de la evidencia obtenida.
- [ ] Configurar el contrato de lectura, ejecución y revisión correspondiente; comprobar permisos y competencia en backend.
- [ ] Publicar la pantalla del residente con resultado, evidencias, limitaciones, historial y revisiones separadas.
- [ ] Verificar navegación, accesibilidad, errores y estados con Medicina, Psicología y Enfermería dentro de sus permisos.

Texto propuesto para una salida institucional que cumpla el alcance aprobado:

> Apoyo a la valoración profesional.
> Resultado generado a partir de registros del residente.
> Revisión de Psicología: pendiente / confirmada / rechazada.
> Revisión de Medicina: pendiente / confirmada / rechazada.

La interfaz reflejará la capacidad realmente habilitada: hallazgos observacionales o interpretación cognitiva aprobada. No declarará validación clínica de una capacidad que todavía no la tenga.

## Fundamento externo consultado

- [IMDRF N41, evaluación clínica de software](https://www.imdrf.org/documents/software-medical-device-samd-clinical-evaluation): distingue asociación clínica, validación técnica y validación clínica para el uso y la población previstos. Se utiliza como marco metodológico; no se afirma que determine por sí mismo obligaciones regulatorias bolivianas.
- [NICE NG97, evaluación y diagnóstico](https://www.nice.org.uk/guidance/ng97/chapter/Recommendations/): incorpora historia, efecto funcional, evaluación cognitiva y consideración de causas reversibles. No convierte estas recomendaciones en reglas de RememberMind ni selecciona automáticamente instrumentos o puntos de corte.

## Cambios de esta entrega

Auditoría estática de los contratos anteriores y este plan. No se modificaron reglas, permisos, BDD, datos, avisos ni configuración de publicación. No se ejecutaron pruebas para acreditar una validación clínica; la verificación técnica previa conserva únicamente el alcance documentado de su fase.
