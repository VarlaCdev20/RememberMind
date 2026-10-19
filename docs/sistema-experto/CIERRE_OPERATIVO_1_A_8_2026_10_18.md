# Cierre operativo de los entregables 1–8

Fecha: 18/10/2026. Instrucción vigente: completar la ingeniería sin exigir dictamen profesional previo. La revisión de conocimiento y las revisiones del resultado se registran después, con su estado real; no se precargan aprobaciones ni puntuaciones ausentes.

La propietaria confirmó: Medicina y Psicología gestionan y aprueban reglas; Superadministración consulta. Esta decisión autoriza implementar permisos específicos y su Policy, sin conceder escritura clínica a los roles administrativos.

## Contrato del incremento

- Motor puro en `motor-experto`; casos de uso, autorización, conservación y UI en `ApoyoDecision`.
- Reutilizar red tipada, reglas declarativas, extracción, conservación observacional y revisiones existentes.
- Evaluación ejecutable antes de la revisión profesional, identificada como pendiente. Pendiente de revisión no significa que existan método, baremo o mediciones no aportados.
- Cada ejecución devuelve cinco dominios y diecinueve componentes, sus fuentes, exclusiones y explicación. Un componente sin contrato de interpretación devuelve la causa concreta y mantiene el bruto.
- Petersen consume soportes identificables y estados derivados; nunca consume la clave de respuestas esperadas. Puede abstenerse.
- Archivo y sustitución crean versiones nuevas; los resultados anteriores conservan las reglas originales.
- Conservar datos completos y versiones antes de confirmar éxito. No reutilizar el orquestador del laboratorio: fabrica fuentes ficticias por contrato.
- Sin nuevas tablas por anticipación. Una revisión institucional coherente puede tener una sola versión real de conocimiento, con todos sus nodos, variables y reglas; no se renombra una mezcla de versiones ajenas.

## Checklist de aceptación

La **[matriz única vigente de E1–E8](CONSOLIDACION_PUNTO_1_2026_10_19.md)** sustituye el resumen de estados anterior. Conserva los requisitos y detalla fuente, componente/archivo, prueba, evidencia, pendiente y aceptación restante. Este documento mantiene el contrato y la evidencia histórica de los incrementos.

Las marcas vigentes se actualizan en esa matriz y en el [plan por fases](PLAN_CIERRE_INTEGRAL_2026_10_19.md), con implementación y evidencia. No se declara cerrado un instrumento por registrar un perfil vacío ni una clasificación por ejecutar solo la abstención. La consolidación del punto 1 está cerrada; la red operativa E1 conserva su estado parcial.

Incremento del 19/10: [captura instrumental y verificación](CAPTURA_INSTRUMENTAL_FORMULARIO_2026_10_19.md). Se reutilizan las cinco tablas operativas de instrumentos y las autorizaciones existentes, sin tablas ni permisos nuevos. El [análisis de conservación instrumental](CONSERVACION_INSTRUMENTAL_ANALISIS_2026_10_19.md) separa la brecha institucional de la RFC del laboratorio; sus alternativas son candidatas sin aprobación ni DDL.

## Diseño del gobierno

Caso de uso: crear revisión, añadir/sustituir/archivar regla y registrar aprobación o rechazo posteriores.

Actor: cuenta activa de Medicina o Psicología, profesional propio activo, permiso explícito y Policy. Superadministración mantiene consulta autorizada.

Entrada: definición declarativa de condición y consecuencia, regla/versión de origen, motivo y decisión. Nunca actor, autor o fecha de firma proporcionados por el cliente.

Capas: Policy de conocimiento → Action transaccional → validación del paquete y editor puro → Models/tablas existentes → Activitylog mínimo. Livewire presenta los formularios y mensajes.

Revisión pendiente: se puede probar y ejecutar el contrato técnico válido. APROBADA/RECHAZADA registra una decisión humana identificable; no convierte por sí misma un algoritmo en clínicamente validado.

Historia: una regla cambia mediante sucesora, con enlace a la anterior. Una regla archivada no participa en la nueva revisión; la anterior sigue explicando los resultados previos.

## Cambios de estructura aislados

La propietaria aprobó las cinco columnas de revisión y enlace, con condición expresa de mantener 4FN: [PROPUESTA_GOBIERNO_REGLAS_2026_10_18.md](PROPUESTA_GOBIERNO_REGLAS_2026_10_18.md). No hay tablas nuevas ni conjuntos multivaluados; cada regla/revisión conserva una antecesora y una decisión. El inventario sigue en 106 tablas de negocio.

## Incremento de gobierno implementado

- [x] Action institucional: crear sucesora, incorporar, sustituir y archivar sin sobrescribir definiciones previas.
- [x] Primera aprobación/rechazo con usuario de sesión, fecha y fundamento; reintento idéntico sin auditoría duplicada.
- [x] Una definición rechazada permanece inactiva al crear otra versión; solo una sustituta explícita vuelve a proponer conocimiento.
- [x] Migración exacta de cinco columnas, FK e índices restrictivos, firma coherente y protección física de la primera decisión y antecedente. Rollback rechaza historia existente; sin historia conserva definiciones y FK, en ambos motores.
- [x] Permisos y Policy MED/PSI con personal propio activo; Superadministración consulta. Observer de permisos y editor de roles respetan esas excepciones.
- [x] Pantalla `/admin/sistema-experto/reglas`, enlaces desde resultados y laboratorio, filtros por versión y reglas paginadas; prueba de condiciones guardadas con entradas explícitas, sin guardar evaluaciones ni aprobar automáticamente.
- [x] SQL PostgreSQL desechable y tres carreras independientes: firma/firma, sucesión/sucesión y firma/sucesión.
- [x] Aplicada la migración aprobada en `remembermind_dev`, con respaldo previo comprobado. Ocho reglas originales idénticas al respaldo; concesión de permisos específica sin resembrar roles completos.
- [ ] QA visual/responsive: intento de navegador rechazado por configuración de Work; las pruebas Livewire verifican funcionamiento, no apariencia.
- [x] Integrar sucesoras observacionales: `OBS-SNAP-2` selecciona la cadena SQL, excluye archivadas/rechazadas y congela las reglas ejecutadas; las historias `OBS-SNAP-1` conservan su lectura original incluso tras retirar la raíz. [Contrato y límites](CONTRATO_OBSERVACIONAL_VERSIONADO_2026_10_18.md).
- [ ] Integrar conocimiento, adquisición y conservación institucional de los cinco dominios. La integración observacional no acredita alteración objetiva ni DCL.

Ruta de código: `GestionarReglasExpertas`, `GobiernoReglasPanel`, `VersionModeloExpertoPolicy`, `ReglaExperta` y migración `2026_10_18_002000`. Evidencia automatizada: `GobiernoReglasExpertasTest`, `GobiernoReglasConcurrenciaPostgresTest`, `RolesBaselineCongeladoTest` y `EditorPaqueteExperimentalTest`. Los ensayos del formulario prueban conjunción de condiciones; no validan un instrumento ni un diagnóstico.

## Investigación instrumental solicitada y aplicada

La propietaria pidió investigar, documentar y aplicar los métodos el 18/10/2026. [Informe de evidencia y matriz completa](INFORME_INSTRUMENTAL_Y_NORMALIZACION_2026_10_18.md), con 25 referencias primarias/oficiales, sin datos personales. Se implementaron `NormalizadorHvltBolivia2015` y `RegistroMetodosInstrumentales` en el motor: regresiones de total y recuerdo diferido, forma 5, población y demora de 20 a 25 minutos. No se programaron cortes DCL ni se atribuyó ese instrumento a transcripciones nominales.

Pruebas: `NormalizacionInstrumentalInvestigadaTest` y `PreparacionInstrumentalInvestigadaTest`, 16 pruebas y 133 aserciones. El método aparece como disponible en la preparación; se corrigió la lectura de aplicaciones completas (`COMPLETA`, no `VIGENTE`). La transformación de una aplicación real requiere sus medidas y contexto originales; estos requisitos no se sustituyen con una aprobación previa o posterior. La salida conserva también el contexto utilizado, sin acreditar por sí misma la procedencia de ese contexto.

La regresión del informe cognitivo, informe neurocognitivo, resumen y preparación pasó con 71 pruebas y 602 aserciones. La regresión adicional de hallazgos observacionales y preparación pasó con 37 pruebas y 107 aserciones. El build de frontend pasó. La validación independiente del cálculo contrastó las ecuaciones y sus límites contra la publicación original: PASS técnico. El informe PDF de siete páginas se generó y se inspeccionaron visualmente las siete páginas. La inspección de la interfaz en el navegador sigue sin ejecutarse por la configuración de Work; no se deduce del build ni de las pruebas Livewire.

La CI incorpora el gobierno y sus tres carreras en el perfil PostgreSQL desechable; las nuevas pruebas instrumentales pertenecen a la suite SQLite completa. La ejecución remota de ese workflow queda separada de las verificaciones locales.

Incremento instrumental conservado en el commit local `510c97ea`: motor, integración de preparación, pruebas y documento fuente/PDF. No se incluyeron datos privados ni los demás cambios en curso y no se realizó push.

Verificación general local terminada: `php vendor/phpunit/phpunit/phpunit --log-junit=...`, 2644 pruebas descubiertas, 50004 aserciones y 26 omitidas; cero errores y fallos. Esa corrida comenzó antes de añadir las 16 pruebas instrumentales, que se ejecutaron por separado con 133 aserciones. La suite completa acredita el árbol de trabajo local, incluidos cambios anteriores aún no integrados, y no una ejecución remota de CI. La integración PostgreSQL del gobierno conserva su evidencia separada de 47 pruebas y 249 aserciones.

## Pruebas requeridas

### Corte posterior: correcciones por puntos

El [registro de correcciones](CORRECCIONES_POR_PUNTOS_2026_10_18.md) contiene el estado posterior verificable y la [ficha de protocolos solicitada](PROTOCOLOS_CAPTURA_COGNITIVA_V1_2026_10_18.md). Se implementaron adquisición instrumental autorizada con precisión y fecha originales, red documental institucional de cinco dominios y 19 componentes, perfil de mediciones sin interpretación inventada y revisión MED/PSI de instantáneas observacionales.

Se consolidaron las 43 clases puras en `motor-experto/src/motor_experto/dominio/orion`; Application e Infrastructure quedaron en `ApoyoDecision`. No queda PHP ejecutable en `app/SistemaExperto`. Se preservaron las huellas históricas mediante identidad canónica del formato, sin alias, reescritura de datos ni activación clínica.

No se marcan cerradas la interpretación clínica de los diecinueve componentes, Petersen institucional ni la conservación integral del perfil nuevo. Las fichas creadas permiten recoger o transcribir mediciones originales; no contienen aciertos, tiempos o firmas generados para residentes. Los resultados observacionales representables sí se conservan y admiten revisión posterior independiente por disciplina.

Positivos y negativos de permisos/competencia/estado, archivo y sustitución conservando historia, revisión una vez e idempotencia, rollback y aislamiento de residentes, fuentes futuras/anuladas/NULL/contradictorias, preguntas/opciones ajenas, cuatro patrones y abstenciones DCL, replay exacto tras cambiar fuentes y conocimiento. SQLite para pruebas rápidas y PostgreSQL desechable para integración. Build y QA de los controles UI modificados.
