# Conservación institucional: lectura íntegra y replay separados

Fecha: **22/10/2026**. Estado: separación de API implementada y probada; **conservación SQL compuesta todavía pendiente**. Este documento sucede al cierre de las cuatro consolidaciones sin ampliar su autorización estructural.

Continuación: [recuperación SQL completa de referencia](RECUPERACION_SQL_COMPLETA_REFERENCIA_2026_10_22.md). Ya comprueba lectura íntegra independiente y replay sobre 13 casos, pero todavía excede el máximo y no cierra las FK históricas; no se instala ni sustituye el pendiente institucional indicado aquí.

## Cambio implementado

Las tres instantáneas del motor —instrumental, Petersen e institucional compuesta— ofrecen ahora dos operaciones diferentes:

| Operación | Comprueba | Ejecuta inferencia | Acredita persistencia SQL |
|---|---|---|---|
| `verificarIntegridad(contenido, huellaEsperada)` | Forma superior, tipos planos, fechas, huellas, contexto y correspondencia de las ramas; conserva la salida recibida | **No** | **No** |
| `verificar(contenido, huellaEsperada)` | Las comprobaciones anteriores y comparación de la salida con la recalculada desde las fuentes y conocimiento originales | **Sí**, igual que antes | **No** |

`crear()` y `vincularRelease()` continúan utilizando el replay estricto. No se sustituyeron por la comprobación más limitada. La nueva API de lectura no se expuso en un controlador, formulario o permiso, ni se empleó para convertir un resultado transitorio en historia. Su consumidor de SQL sólo podrá integrarse cuando exista el diseño relacional completo y autorizado.

La lectura sin inferencia preserva exactamente ambas salidas; no las sustituye por un resultado calculado hoy. Comprueba:

- Huella canónica SHA-256 esperada, incluyendo diferencias de tipo, NULL, ausencia y orden de listas.
- Contratos y campos superiores exactos; datos planos sin objetos ni números no finitos.
- Identidades de evaluación, residente, versión y usuario no vacías y de hasta 20 caracteres.
- Fecha original con instante, zona y desplazamiento entero compatibles.
- Presencia de las secciones de salida, su identidad y los límites originales de preparación.
- Igualdad de residente, versión instrumental, usuario y corte entre ramas. Las identidades de ejecución pueden ser diferentes: Petersen usa una identidad `PET-*` propia.
- Coincidencia de las capturas de medición compartidas por ambas ramas.
- Huella del manifiesto de release y correspondencia con la identidad del ejecutor capturada, cuando hay manifiesto.

No recalcula aciertos, puntuaciones, normas, admisibilidad, estados cognitivos ni reglas. No consulta una base de datos, configuración clínica, archivos de pacientes ni fuentes actuales para completar huecos. La validación clínica y la comprobación completa de reglas siguen fuera de esta API.

### Frontera de confianza

La `huellaEsperada` deberá proceder del registro histórico inmutable verificado por el backend. **Contenido y huella aportados juntos por un cliente no acreditan autenticidad.** Un atacante que modifica datos y recalcula todas sus huellas puede producir un contenedor internamente consistente; por eso la prueba de integridad y el replay son propiedades distintas. Hay un caso automatizado que demuestra expresamente esta diferencia: la lectura conserva el resultado recibido con su nueva huella, mientras el replay rechaza que ese resultado no corresponde a las entradas.

Las banderas `persistido=false` y `release_retenido=false` permanecen en la preparación original. El futuro repositorio tendrá que informar el estado durable mediante un envoltorio confirmado por SQL, sin reescribir la preparación histórica. No se permiten revisiones de Medicina o Psicología sobre este DTO transitorio.

## Evidencia de independencia del motor

`tests/Support/leer_preparacion_sin_inferencia.php` arranca un proceso PHP limpio y antepone un autoloader que rechaza la carga de los espacios `MotorExperto\aplicacion`, `MotorExperto\dominio` y `MotorExperto\adaptadores`.

Con ese bloqueo:

1. `verificarIntegridad()` recupera el mismo contenido completo y su huella sin intentar cargar inferencia.
2. `verificar()` intenta cargar el motor y falla, demostrando que la prueba distingue ambos recorridos.
3. Una salida alterada frente a la huella original se rechaza sin llegar al motor.

El proceso recibe sólo fixtures sintéticas por stdin. La serialización utilizada es transporte interno del test, **no persistencia clínica ni un endpoint de la aplicación**. No se guardan pacientes ni valores de respuestas en los artefactos de auditoría.

## Corrección del alcance del mapa anterior

Los 39 modelos originales, 51 ubicaciones y 783 rutas documentados en F02 pertenecen a la proyección de **entradas**. `EntradasCompuestas::proyectar()` elimina las salidas instrumental y Petersen, y retira las extracciones para recalcularlas durante el replay. La fuente conceptual se recupera del release. Por tanto, aquel mapa no es una matriz completa del DTO histórico para lectura independiente.

Se añadió un inventario reproducible de las estructuras que quedaban fuera:

```powershell
php scripts/inventariar-salidas-institucionales.php
php scripts/inventariar-salidas-institucionales.php --check
```

Utiliza los diez casos sintéticos F02 existentes y **no abre SQL ni inicia Laravel**. Produce [6.401 rutas estructurales observadas](../auditorias/conservacion-completa-2026-10-22/rutas-salidas-observadas.csv) y un [resumen por caso y sección](../auditorias/conservacion-completa-2026-10-22/resumen-salidas-observadas.json), sin valores clínicos originales.

El CSV identifica sección, ruta, tipos observados, casos y número de apariciones. Normaliza posiciones de listas mediante `*` y conserva las claves de mapas, incluyendo las claves semánticas. **No son 6.401 columnas ni 6.401 granos SQL.** Tampoco son una cota universal ni una demostración de cobertura de todas las reglas: describen el corpus identificado.

En el caso `PET01` de ese corpus se observan:

| Sección omitida por el mapa anterior | Hojas escalares | Arreglos | NULL |
|---|---:|---:|---:|
| Salida instrumental completa | 6.723 | 1.713 | 406 |
| Salida Petersen completa | 2.815 | 554 | 274 |
| Extracciones instrumentales | 695 | 139 | 17 |
| Fuente conceptual | 626 | 142 | 0 |

Hay repeticiones legítimas entre secciones: una representación relacional mantenible puede reconstruirlas desde datos conservados compartidos, sin volver a inferir. Los conteos no deben sumarse a las 783 rutas como si fueran magnitudes equivalentes.

## Reutilización y presupuesto pendientes de demostrar

La instalación actual mantiene **100 tablas de negocio**: 75 operativas y 25 expertas. El máximo de diseño sigue en 110; hay diez relaciones de margen. La revisión no ejecutó otra migración ni cambió FK, permisos o activación de reglas.

| Área | Reutilización legítima a estudiar | Condición que debe cumplirse |
|---|---|---|
| Conocimiento versionado | Doce entidades lógicas existentes; variable proyectada desde nodo | Fijar membresía y estado al instante, orden y cabeceras. Las aprobaciones y retiradas cambian hoy por SQL; una FK a la versión actual no conserva por sí sola esos valores. |
| Juicios originales Petersen | `valoraciones_petersen_originales` | Conservar pertenencia y orden de la evaluación, procedencia y juicios excluidos. Sus triggers de inmutabilidad no conservan automáticamente las fuentes clínicas mutables consultadas. |
| Soportes originales y dominios revisados | `soportes_valoracion_petersen` | FK terminal exacta y membresía histórica, incluso cuando un juicio es excluido. Los triggers impiden actualizar/borrar una fila existente, pero no prueban que no se añadieran otros soportes después; la membresía utilizada debe conservarse expresamente. |
| Preguntas y respuestas adquiridas | Una captura y snapshots nominales de pregunta/respuesta/selector | Probar identidad compartida entre las ubicaciones, respuesta 0..1 y preservación de pregunta sin respuesta, opción, clave y orden. |
| Ejecuciones de regla/condición | `evaluaciones_reglas`, `evaluaciones_condiciones_regla` | Incorporar la unidad/aplicación y su propietario sin romper las FK ni la unicidad de las ejecuciones globales existentes. Necesita DDL específico. |
| Evidencia extraída | `evidencias_evaluacion` | Respetar mapeo y registro originales; no introducir capturas o juicios sin esos requisitos. |
| Contratos adquiridos | Relación propia o transición equivalente probada | No confundirlos con definiciones en `relaciones_semanticas`. |

La revisión de salidas identificó siete responsabilidades que deben tener destino explícito, aunque **no equivalen necesariamente a siete tablas nuevas**:

1. Regla evaluada por unidad/aplicación.
2. Condición evaluada dentro de esa unidad.
3. Unidades válidas por componente de regla.
4. Conclusiones candidatas por criterio y valor.
5. Membresías de reglas, evidencias y soportes efectivos de cada conclusión.
6. Contradicciones por criterio/unidad/variable con sus alternativas y evidencias.
7. Motivos, pendientes, exclusiones y descartes ordenados por su propietario real.

Las tablas actuales conservan parte de esos granos, no todos. No se relajaron sus FK para aparentar cobertura.

### Consolidaciones de capturas que sí tienen fundamento

La consulta compuesta entrega a Petersen las mismas capturas adquiridas por la rama instrumental. `ConsultarPetersenInstitucional` obtiene `captura_original` de esa lista por `cod_aplicacion`. Esto permite una FK a **una misma captura conservada**, con igualdad canónica comprobada, en lugar de duplicar todos sus datos bajo cada soporte. La posición del soporte y su pertenencia al juicio siguen siendo hechos propios.

Asimismo, la adquisición consulta una sola versión de cada pregunta y opción y las referencia desde respuestas, declaraciones y selectores. Compartir snapshots nominales de esas definiciones exige comprobar identidad y contenido; compartirlos sólo por semejanza de campos no sería correcto. Una pregunta declarada sin respuesta debe seguir existiendo aunque no haya fila de respuesta.

El submodelo instrumental normalizado estudiado requiere destinos explícitos para captura, instrumento retenido, pregunta retenida, opción retenida, pertenencia de pregunta/respuesta a captura, perfil, selector, motivo, vínculo interpretativo y predicado. Son **diez relaciones candidatas de ese submodelo**, suponiendo un supertipo cerrado y justificado para ambos perfiles; sin esa justificación serían doce. No están incluidas todavía las fuentes/constancias Petersen, contratos adquiridos, gobierno histórico ni las salidas. No se presenta ese submodelo como solución de diez tablas para todo el sistema ni como cota mínima universal.

Para la membresía Petersen, el grano candidato es `(evaluación, soporte original)`, con FK al juicio y soporte terminal, posición de membresía y posición de adquisición cuando exista. Una FK opcional a la captura compartida conservaría la medición. Un miembro excluido debe conservar su pertenencia sin inventar datos adquiridos. Estas decisiones aún necesitan diccionario y prueba SQL; no son DDL aprobado.

### Cuenta del candidato anterior, no cota mínima

El candidato nominal anterior tenía 34 relaciones adicionales sólo para entradas. La cuenta hipotética más directa de reutilización es:

`34 − 12 copias de conocimiento − 1 cabecera − 5 relaciones de juicio/procedencia/membresía + 1 membresía de evaluación = 17 adicionales`.

Eso daría **117 tablas**, todavía fuera del máximo y sin incorporar toda la salida independiente. Además, la reutilización del conocimiento necesita probar estados históricos y protección SQL. El número no demuestra imposibilidad matemática de otro diseño bajo 110; demuestra que **esa reducción aritmética no basta**.

Se descarta instalar ese candidato o afirmar que las diez relaciones disponibles resuelven todo. La siguiente propuesta física debe normalizar snapshots compartidos, cerrar las FK y mapear también salidas/extracciones. No se pide aprobar un diseño incompleto ni se atribuye a la propietaria la falta de ese diseño.

## Trazabilidad de código y aceptación

| Cambio | Archivo | Evidencia |
|---|---|---|
| Forma/huella/contexto sin inferencia | `motor-experto/src/motor_experto/soporte/conservacion/IntegridadPreparacion.php` | Fechas tipadas, identidad, objetos y huellas inválidas |
| Dos API instrumentales | `.../InstantaneaInstrumental.php` | Replay de normalización, salida y hash histórico sin cambios |
| Dos API Petersen | `.../InstantaneaPetersen.php` | Recuperación de salida y replay de cuatro perfiles/abstenciones |
| Dos recorridos de evaluación compuesta | `.../InstantaneaEvaluacionInstitucional.php` | Huellas internas, contexto y capturas de ambas ramas |
| Proceso con inferencia prohibida | `tests/Support/leer_preparacion_sin_inferencia.php` | Autoload bloqueado; lectura idéntica y replay rechazado |
| Positivos y corrupciones | `tests/Unit/InstantaneaEvaluacionInstitucionalTest.php` | Salida ausente, forma inválida, cruces, objeto, identidad larga, `persistido=true` y huellas |
| Inventario que faltaba | `scripts/inventariar-salidas-institucionales.php` | Diez casos; CSV y JSON agregados reproducibles con `--check` |

No cambia la interfaz visual, los contratos C01–C05, la decisión clínica ni el gobierno de reglas. No se hizo commit, push o despliegue. Se preservan los cambios simultáneos del árbol. Las pruebas de integración y sus resultados finales se registran en la sección siguiente.

## Verificación

Comandos ejecutados:

```powershell
php artisan test --filter='InstantaneaEvaluacionInstitucionalTest|InstantaneaInstrumentalCompletaTest|ReproduccionFuenteNominalTest' --compact --log-junit=work/conservacion-lectura-integridad-unit.xml
php artisan test --filter='PreparacionCompuestaCoherenteTest|EjecucionConsultaCognitivaTest|SistemaExpertoOrionNivel1Test' --compact --log-junit=work/conservacion-lectura-integridad-sqlite.xml
& scripts/probar-postgres.ps1 -Filtro 'PreparacionCompuestaCoherenteTest|InstantaneaEvaluacionInstitucionalTest|InstantaneaInstrumentalCompletaTest|ReproduccionFuenteNominalTest'
php scripts/inventariar-salidas-institucionales.php --check
php scripts/verificar-consolidacion-experta.php
```

| Comprobación final | Resultado | Evidencia |
|---|---|---|
| Instantáneas, replay y negativos de integridad | **52 pruebas, 140 aserciones; cero fallos** | [JUnit unitario](../auditorias/conservacion-completa-2026-10-22/integridad-replay-unit.xml) |
| Preparación compuesta, consulta y recorrido Livewire sobre SQLite desechable | **20 satisfactorias, 132 aserciones; tres omitidas** por requerir PostgreSQL | [JUnit SQLite: 23 casos totales](../auditorias/conservacion-completa-2026-10-22/integracion-sqlite.xml) |
| Instantáneas y preparación compuesta sobre PostgreSQL desechable | **57 pruebas, 173 aserciones; cero fallos u omisiones** | [JUnit PostgreSQL](../auditorias/conservacion-completa-2026-10-22/integracion-postgres.xml) |
| Inventario estructural reproducible | **Diez casos / 6.401 rutas; `--check` satisfactorio** | CSV y resumen enlazados arriba |
| Estilo PHP y revisión independiente | Pint satisfactorio; sin defectos bloqueantes en el cambio revisado | Revisión de la separación de API, comprobaciones y proceso aislado; no auditoría de un SQL compuesto inexistente |

Las baterías se solapan; **no se suman como escenarios independientes**. La ejecución PostgreSQL usa el cluster temporal creado y destruido por `scripts/probar-postgres.ps1`; su ejecución no verifica almacenamiento compuesto ni una carrera de cierre compuesto, que siguen pendientes. Las pruebas de concurrencia de las cuatro consolidaciones pertenecen al [cierre anterior](CIERRE_REFACTOR_BLOQUES_2026_10_22.md).

La comprobación instalada es de **sólo lectura**: mantuvo 116 tablas físicas, 25 expertas, las 257 entidades trasladadas y las cinco clasificaciones sintéticas recuperables. La nueva API no acredita que esas clasificaciones sintéticas sean clínicas ni que el DTO compuesto esté guardado. No se repite build por este cambio exclusivamente PHP y de documentación; el build satisfactorio de la refactorización anterior no se presenta como QA visual nuevo.

**Cierre de este cambio:** separación de integridad y replay implementada y probada. **Cierre integral pendiente:** diseño físico completo bajo 110, prueba SQL de ambas ramas/salidas, aprobación específica del DDL y posterior integración de historial/revisiones. El recorrido compuesto continúa con `persistido=false`.
