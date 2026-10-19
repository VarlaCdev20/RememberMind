# PROMPT MAESTRO 01 — IMPLEMENTACIÓN INTEGRAL DEL SISTEMA EXPERTO

## ROL Y MISIÓN

Actúa como arquitecto de software sénior, ingeniero del conocimiento, especialista en sistemas expertos basados en reglas, desarrollador principal de Laravel/PHP y responsable de aseguramiento de calidad.

Tu misión es **completar, integrar, refactorizar y verificar el sistema experto existente** hasta obtener un prototipo integral de Ingeniería de Sistemas, funcional, explicable, escalable, mantenible y reproducible.

NO quiero únicamente análisis, pseudocódigo, documentación, propuestas o pantallas demostrativas. Quiero código ejecutable, pruebas verificables, persistencia coherente, gestión de reglas, base de hechos y resultados reales producidos por el motor sobre datos sintéticos controlados.

El sistema debe funcionar en cinco dominios cognitivos, manejar información incompleta y contradictoria, disponer de gestión del ciclo de vida de reglas y ejecutar el clasificador experimental Petersen cuando sus requisitos se cumplan.

No cambies el nombre de la aplicación ni introduzcas nuevas marcas en archivos, documentación o interfaces. Utiliza la expresión **«sistema experto»** en los textos nuevos orientados al usuario. Conserva las identificaciones técnicas existentes cuando renombrarlas rompa compatibilidad.

## 1. PRIMERA OBLIGACIÓN: AUDITORÍA DEL PROYECTO

Antes de modificar archivos:

1. Lee las instrucciones del repositorio, guías de arquitectura, documentación técnica y pruebas existentes.
2. Consulta el Documento Maestro, las decisiones D-123 a D-144, la enmienda EXP-01, los expedientes G06–G13 y los contratos documentales de clasificación y explicación.
3. Inspecciona las ramas, commits y solicitudes de integración abiertas. Identifica funcionalidades experimentales ya implementadas para reutilizarlas sin duplicar ni sobrescribir avances.
4. Analiza el motor independiente en `motor-experto/` y el adaptador Laravel en `app/Modulos/ApoyoDecision/`.
5. Revisa los modelos, migraciones, permisos, policies, rutas, servicios, pruebas y vistas existentes.
6. Identifica las 71 tablas operativas y las 23 tablas expertas del esquema vigente. Comprueba su inventario en código; no utilices documentación histórica como única fuente.
7. Ejecuta las pruebas iniciales que el entorno permita y registra el resultado de referencia.
8. Clasifica cada funcionalidad como existente y verificada, existente pero incompleta, experimental aislada, defectuosa o pendiente.

Entrega una matriz breve de brechas y después **continúa directamente con la implementación**. No reconstruyas componentes que funcionan correctamente ni confundas una rama de investigación con funcionalidades ya integradas a la rama principal.

## 2. RESTRICCIONES INNEGOCIABLES

- Objetivo: prototipo funcional y verificable de Ingeniería de Sistemas, no software clínico aprobado.
- Trabajar exclusivamente con datos artificiales para la ejecución experimental.
- Nunca activar inferencia asistencial ni escribir conclusiones en historias, cuidados, alertas, prescripciones o tratamientos.
- No alterar las 71 tablas operativas ni las 23 expertas sin autorización estructural expresa.
- No inventar columnas, tablas, roles, permisos, valores de catálogo o estados persistidos.
- Si un requisito no cabe en el contrato congelado, resolverlo con componentes experimentales compatibles o documentar una RFC de cambio. No realizar migraciones por iniciativa propia.
- No introducir umbrales, baremos, porcentajes, probabilidades, pesos, diagnósticos ni equivalencias clínicas sin fundamento autorizado.
- No interpretar `NULL` como `FALSE`, ausencia de evidencia como normalidad, ni resultado negativo de una regla como función conservada.
- No convertir automáticamente un hallazgo descriptivo en alteración objetiva de un dominio.
- Mantener compatibilidad con el motor y las pruebas actuales.
- No eliminar registros históricos ni modificar versiones de conocimiento utilizadas por evaluaciones previas.
- Reutilizar la arquitectura híbrida y las convenciones vigentes.
- No afirmar que una funcionalidad está terminada si únicamente existe una clase, fixture o prueba aislada que no cubre el flujo completo.

## 3. ARQUITECTURA FINAL OBLIGATORIA

El sistema debe quedar organizado en componentes con responsabilidades separadas:

1. Extracción de fuentes.
2. Validación de procedencia y admisibilidad.
3. Representación de hechos y mapeo semántico.
4. Base de conocimiento y red semántica.
5. Gestión de versiones y ciclo de vida de reglas.
6. Memoria de trabajo aislada por evaluación.
7. Compuertas de evaluabilidad por dominio.
8. Evaluador de reglas positivas.
9. Control de contradicciones, confusores y duplicidades.
10. Resolución de hallazgos y resultados por dominio.
11. Orquestador de cinco dominios.
12. Integración experimental de los requisitos y patrones Petersen.
13. Constructor de explicaciones y trazabilidad.
14. Persistencia transaccional compatible con el esquema.
15. Contratos de aplicación para administración y presentación.
16. Pruebas automatizadas y documentación operativa.

Mantén el motor de inferencia independiente de Livewire, Blade y HTTP. Los controladores y componentes visuales nunca deberán contener la lógica de las reglas.

## 4. BASE DE HECHOS COMPLETA

Implementa una base de hechos lógica y reconstruible que lea exclusivamente fuentes permitidas.

Incluye, según correspondan al contrato y al caso:

- `controles_cognitivos`;
- `instrumentos`;
- `aplicaciones_instrumento`;
- `preguntas_instrumento`;
- `respuestas_instrumento`;
- `opciones_pregunta`;
- `seguimientos_pedagogicos`;
- `valoraciones_psicologicas`;
- `valoraciones_funcionales`;
- fuentes médicas contextuales autorizadas;
- evaluaciones longitudinales y evidencia sintética independiente de criterios funcionales.

Cada hecho debe conservar:

- identificador de evidencia;
- identificador de caso y evaluación;
- identidad del registro original;
- fuente y campo;
- valor bruto original y tipo;
- valor semántico, cuando exista mapeo válido;
- fecha de origen y fecha de corte;
- autor o procedencia verificada;
- estado operacional;
- versión del mapeo;
- admisibilidad;
- motivos de rechazo o advertencia;
- rol de la evidencia en el criterio;
- relaciones con otras evidencias y episodios.

La misma evidencia no debe duplicarse para aparentar corroboración independiente.

Implementa o completa contratos de extracción explícitos. No uses interpretación libre de notas ni NLP implícito. Los valores booleanos sin oportunidad de observación verificada no deben convertirse en conclusiones negativas.

Construye pruebas con hechos válidos, faltantes, mal tipados, de otro caso, fuera de fecha, duplicados, con fuentes modificadas y con mapeos incompatibles.

## 5. BASE DE CONOCIMIENTO Y RED SEMÁNTICA

Aprovecha la red semántica existente y completa su representación experimental para:

- cinco dominios cognitivos;
- 19 subcomponentes conceptuales;
- variables observacionales e instrumentales;
- valores semánticos;
- fuentes;
- criterios;
- reglas;
- condiciones;
- consecuencias;
- relaciones entre evidencia y dominios;
- confusores;
- relaciones multidominio;
- restricciones de independencia;
- versionado y procedencia documental.

Dominios:

- COG-MEM: aprendizaje y memoria.
- COG-ATE: atención.
- COG-EJE: función ejecutiva.
- COG-LEN: lenguaje.
- COG-VIS: función visuoespacial.

Representa los subcomponentes de memoria, atención, ejecutiva, lenguaje y visuoespacial conforme al Documento Maestro. No conviertas automáticamente cada subcomponente en un criterio independiente, ni asumas que cada subcomponente requiere exactamente una regla.

Conserva las relaciones y nodos históricos. Si una relación candidata es incorrecta, crea una versión corregida, preservando la anterior.

Exige coherencia entre nodos, variables, dominios, reglas, versiones y consecuencias.

## 6. MOTOR DE REGLAS

Conserva y reutiliza la inferencia positiva conjuntiva D-130.

Cada regla ejecutable debe disponer de:

- ID estable;
- versión inmutable publicada;
- descripción comprensible;
- dominio y subcomponente;
- fundamento documental;
- procedencia y estado de investigación;
- condiciones positivas tipadas;
- valores semánticos;
- restricciones y dependencias;
- consecuencia explícita;
- criterios de no emisión;
- explicación generable;
- casos de prueba asociados.

No escribir reglas clínicas en sentencias `if` dispersas en servicios, controladores o vistas. La evaluación debe utilizar la representación de conocimiento formalizada, con adaptadores cuando corresponda.

Diferencia claramente:

- Regla cumplida.
- Regla no cumplida.
- Regla no evaluada por falta de datos.
- Evidencia no admisible.
- Resultado contradictorio.
- Error de integridad de conocimiento.

No inventes estados físicos nuevos; proyecta estados de presentación desde los contratos existentes o tramita una propuesta estructural.

Reutiliza el resolvedor de memoria D-131 sin extender sus consecuencias por analogía a los otros cuatro dominios.

## 7. GESTIÓN ESCALABLE DE REGLAS: CREAR, VERSIONAR, ARCHIVAR Y REEMPLAZAR

Este punto es prioritario. El conocimiento NO debe quedar fijo en código ni exigir editar manualmente archivos PHP cada vez que cambie una regla.

Diseña e implementa un gestor de conocimiento que utilice el modelo de persistencia existente y un ciclo de vida consistente.

Debe permitir, cuando las autorizaciones vigentes lo permitan:

### Crear regla

- Seleccionar versión de trabajo, dominio y subcomponente.
- Registrar identificador, nombre, propósito y descripción.
- Elegir variables y valores semánticos existentes.
- Construir condiciones positivas AND.
- Definir una consecuencia válida.
- Asociar fuentes y fundamento.
- Establecer restricciones, excepciones y explicación.
- Validar integridad antes de admitirla.
- Ejecutar pruebas sintéticas antes de habilitarla.

### Editar regla

- Una regla publicada e históricamente utilizada nunca debe sobrescribirse.
- Crear una nueva revisión o versión.
- Conservar el contenido exacto de la versión anterior.
- Mostrar diferencias: condiciones añadidas, retiradas, consecuencia y fundamento.
- Comprobar impacto en casos de prueba existentes.
- Impedir referencias incompatibles entre versiones.

### Archivar regla

- Archivar significa retirarla del conjunto elegible de nuevas ejecuciones, no borrarla físicamente.
- Registrar responsable, fecha, motivo y versión afectada, utilizando solo estructuras permitidas.
- Conservar consulta histórica, evidencias y trazas.
- No permitir que el archivo invalide resultados antiguos.
- No mostrar reglas archivadas como activas en nuevas evaluaciones.
- Permitir filtrar reglas vigentes, borradores, experimentales y archivadas según los estados realmente soportados.

### Sustituir regla

- Crear una regla o versión sucesora.
- Documentar su relación con la anterior.
- Ejecutar la misma batería de casos sobre ambas.
- Mostrar las diferencias de salida.
- Habilitar exclusivamente la nueva versión experimental cuando corresponda.
- Mantener posibilidad de regresar a una versión técnica anterior elegible, sin sobrescribir historia ni efectuar activación asistencial.

### Publicación

La habilitación `TEST_ONLY` jamás equivale a aprobación clínica.

No otorgues permisos de modificación al rol médico o a usuarios con solo lectura. Revisa los permisos reales disponibles. Si falta un contrato de autorización de escritura, conserva el gestor en modo técnico autorizado y deja la activación de esas acciones sujeta a decisión explícita.

**Criterio de aceptación obligatorio:** crear una regla nueva, probarla, generar resultados, crear una versión sucesora, archivar la versión anterior y comprobar que sus evaluaciones históricas siguen mostrando exactamente la regla original.

No introduzcas un estado `ARCHIVADA` en columnas restringidas sin autorización. Si el esquema no permite un archivado durable que cumpla este contrato, implementa la parte compatible, deja el flujo protegido y documenta la RFC exacta en lugar de simular persistencia inexistente.

## 8. CINCO DOMINIOS Y REGLAS CANDIDATAS

Integra el piloto de memoria con las quince reglas experimentales de los otros cuatro dominios.

Catálogo G09 existente:

- Atención: 3 reglas.
- Ejecutiva: 5 reglas.
- Lenguaje: 5 reglas.
- Visuoespacial: 2 reglas.

No elimines ni rehagas sin necesidad las implementaciones de la rama experimental. Reutiliza sus suites y su cotejo con el núcleo de inferencia.

Completa el flujo para que un caso sintético pase por:

1. Fuentes originales.
2. Admisibilidad.
3. Interpretación instrumental sintética explícita.
4. Hechos semánticos.
5. Compuerta del dominio.
6. Reglas del subcomponente.
7. Resolución de soportes.
8. Resultado descriptivo del dominio.
9. Explicación.
10. Integración del perfil.

Produce resultados diferentes y comprobables para:

- Caso con soporte válido de dificultad específica.
- Caso con evaluación válida sin soporte de una regla de dificultad.
- Caso sin datos.
- Caso con fuente inválida.
- Caso con confusor relevante.
- Caso con hallazgos múltiples.
- Caso con contradicción.
- Caso con solapamiento de evidencias.

No presentar `EVALUABLE_SIN_SOPORTE` como desempeño conservado.

El resultado integrado de memoria conserva sus propios estados y su propia compuerta.

## 9. INTERPRETACIÓN INSTRUMENTAL EXPERIMENTAL

Analiza la auditoría G08 y conserva su clasificación de perfiles pendientes.

Para cada componente con fuente experimental:

- Identificar tarea e instrumento.
- Conservar edición, versión, idioma, población y condiciones de aplicación.
- Conservar resultado bruto sin modificar.
- Asociar perfil interpretativo versionado y tipado.
- Verificar correspondencia exacta de tarea y subcomponente.
- Documentar si el perfil es exclusivamente ficticio `TEST_ONLY`.
- Bloquear resultados no interpretables fuera de pruebas.
- No inventar equivalencias ni puntos de corte.

Construye perfiles sintéticos suficientemente diferenciados para verificar condiciones positivas y negativas del software.

La prueba debe demostrar que el motor produce la salida adecuada al cambiar un hecho o una condición, no porque un constructor de informes tenga escrita la respuesta esperada.

## 10. EVALUABILIDAD, CONFUSORES Y CONTRADICCIONES

Completa la lógica de compuertas y restricciones por dominio.

Conservar para memoria EV-CM-0, EV-CM-1, EV-CM-2 y el modificador MOD-CM-1.

Para los otros dominios, implementar sus contratos experimentales específicos.

Distinguir:

- información ausente;
- señal contextual;
- evidencia admisible;
- evidencia instrumental interpretable;
- soporte específico de regla;
- prueba válida sin soporte de hallazgo;
- resultado de evidencia contradictoria;
- evidencia excluida por fuente o contexto;
- evaluación no comparable longitudinalmente.

Controlar:

- episodios repetidos;
- doble conteo multidominio;
- audición, visión, idioma, escolaridad y fatiga cuando se documenten;
- confusores que bloqueen o limiten tareas;
- procedencia temporal;
- incompatibilidades de instrumento, norma y versión;
- contradicciones no resueltas.

Ante contradicciones o información insuficiente, el motor debe abstenerse cuando el contrato lo exija. No escoger arbitrariamente una conclusión para rellenar la pantalla.

## 11. SEGUIMIENTO LONGITUDINAL

Implementa o completa un comparador experimental basado en registros fechados.

Debe comprobar:

- misma identidad de caso;
- fuente y componente equivalentes o comparabilidad explícita;
- fechas de medición;
- versiones y protocolos;
- oportunidades reales de observación;
- estado previo y actual;
- posibles diferencias de contexto;
- duplicidad de episodios.

Salidas mínimas:

- información comparable;
- comparación no disponible;
- comparación no válida;
- cambio señalado pero no corroborado;
- diferencia interpretable únicamente cuando exista contrato específico.

No inventes ventanas temporales, progresión, severidad ni cambios objetivos a partir de dos puntajes heterogéneos.

## 12. CLASIFICADOR EXPERIMENTAL PETERSEN

Reutiliza el clasificador existente y sus pruebas.

Implementa la integración funcional mediante evidencias sintéticas explícitas e independientes para:

- C01: cambio cognitivo;
- C02: alteración objetiva;
- C03: funcionalidad y autonomía/IADL;
- C04: evaluación diferencial;
- C05: calidad y evaluabilidad.

Cada criterio general debe conservar sus estados y soportes conforme al contrato.

Cada dominio debe conservar estados A, P, U, I y X.

Está prohibido convertir:

- observación positiva en A;
- ausencia de regla positiva en P;
- EV-2 en A o P;
- hallazgo descriptivo de memoria en A;
- hallazgo G09 en A automáticamente;
- un campo funcional general en acreditación IADL completa.

Para casos sintéticos completamente especificados, admite soportes ficticios independientes que acrediten expresamente A/P y C01–C05. Su procedencia y condición de simulación deben ser verificables.

Demuestra:

- PET-01: patrón amnésico de dominio único.
- PET-02: patrón amnésico multidominio.
- PET-03: patrón no amnésico de dominio único.
- PET-04: patrón no amnésico multidominio.
- Abstención por datos insuficientes.
- Abstención por contradicción.
- Abstención por diferencial.
- Abstención por requisitos no acreditados.
- Rechazo por incoherencia de contrato.

No alteres los 30 casos y 32 configuraciones documentales de referencia para forzar coincidencia. Conserva pruebas de regresión.

El clasificador debe recibir resultados estructurados y soportes verificables, no textos interpretativos preparados manualmente.

## 13. RESULTADOS QUE EL MOTOR DEBE ENTREGAR

Define un contrato de salida estable, versionado y consumible por Laravel/Livewire, sin acoplar el motor a la interfaz.

Una evaluación experimental debe proporcionar:

- identidad del caso;
- identificador de evaluación;
- fecha de corte;
- manifiesto de versiones;
- estado técnico de ejecución;
- fuentes examinadas;
- evidencias admitidas, excluidas y faltantes;
- perfil de cinco dominios;
- resultados de cada subcomponente evaluable;
- reglas examinadas y cumplidas;
- condiciones y soportes;
- contradicciones y confusores;
- comparaciones longitudinales, cuando sean válidas;
- estados DCL C01–C05, cuando se suministren;
- patrón Petersen o código de abstención;
- resumen en lenguaje comprensible;
- limitaciones;
- traza técnica reproducible;
- indicación visible `SIMULACIÓN TÉCNICA / NO VÁLIDO PARA DECISIONES ASISTENCIALES`.

La narrativa debe construirse desde el resultado estructurado. No escribir un informe fijo que diga PET-02 independientemente de los datos.

Permitir consultar tanto un hallazgo como el motivo exacto de que no exista hallazgo.

## 14. PERSISTENCIA Y REPRODUCIBILIDAD

Reutiliza las 23 tablas expertas vigentes en la medida en que soporten cada operación.

Completa el registro de:

- versión y regla utilizada;
- evaluación;
- hechos y soportes;
- relaciones de evidencias;
- condiciones;
- resultado;
- traza;
- fecha de corte;
- estado;
- causas de no emisión;
- manifiesto de versiones.

Las pruebas de integración utilizarán exclusivamente SQLite en memoria o bases PostgreSQL desechables expresamente destinadas a pruebas.

No habilites ejecución o escritura sobre residentes reales.

Cuando un detalle histórico no pueda conservarse en el contrato de base de datos actual, no reconstruyas falsamente el valor original a partir de la fuente actual. Utiliza instantáneas experimentales reproducibles y documenta la brecha de persistencia que requiere autorización.

## 15. SEGURIDAD Y AUDITORÍA

Verifica autenticación, autorización, alcance de casos y aislamiento de datos en cada acción.

Protege:

- ejecución experimental;
- lectura de evidencias;
- administración de versiones;
- creación de reglas;
- comparación de versiones;
- archivado;
- consulta de historial;
- exportación técnica.

No concedas permisos existentes a otros roles por conveniencia.

Registrar acciones administrativas de acuerdo con los contratos de auditoría disponibles. Evitar el borrado destructivo de conocimiento histórico y el acceso a fuentes de otros casos.

La capacidad de ejecutar pruebas no habilita funciones asistenciales.

## 16. PRUEBAS OBLIGATORIAS

Construye una batería automatizada de pruebas unitarias, integración, seguridad, regresión y aceptación.

Como mínimo:

1. Construcción correcta de hechos.
2. Mapeos tipados.
3. Rechazo de fuentes inválidas.
4. Control de versiones.
5. Integridad del grafo.
6. Ejecución de reglas positivas.
7. No emisión de reglas.
8. Evaluabilidad por dominio.
9. Confusores.
10. Contradicciones.
11. Doble conteo.
12. Cinco dominios.
13. Casos Petersen PET-01, PET-02, PET-03 y PET-04.
14. Abstenciones Petersen.
15. Comparación longitudinal.
16. Creación de reglas.
17. Nueva versión de regla.
18. Archivado y preservación histórica.
19. Resultado reproducible.
20. Rechazo de acceso no autorizado.
21. Imposibilidad de uso asistencial.
22. Integridad de persistencia.
23. Generación de explicaciones desde trazas reales.
24. Regresión de todas las pruebas existentes.

Ejecuta comandos reales, no pruebas simuladas verbalmente.

Registra comando, entorno, resultado, cantidad de pruebas y fallos.

No alteres los casos esperados para ocultar defectos de implementación.

## 17. ORGANIZACIÓN DEL TRABAJO

Trabaja en incrementos funcionales, conservando siempre un sistema verificable.

Orden sugerido:

1. Auditoría y baseline.
2. Contratos del caso, hechos y resultados.
3. Compuertas e integración de los cinco dominios.
4. Motor y resolución de evidencia.
5. Conjunto completo de casos sintéticos.
6. Gestión versionada de reglas.
7. Creación y archivado controlado.
8. Integración de criterios Petersen.
9. Trazabilidad, persistencia y explicaciones.
10. Contratos para el frontend.
11. Pruebas de extremo a extremo y documentación.

No te detengas al crear las clases. En cada incremento, ejecuta las pruebas relacionadas.

No hagas cambios destructivos ni fusiones de ramas sin revisar las diferencias. Mantén commits pequeños y trazables cuando el entorno lo permita.

## 18. ENTREGABLES FINALES

Al terminar, proporciona:

A. Código funcional integrado.

B. Catálogo de hechos y mapeos.

C. Red semántica y catálogo de reglas.

D. Gestor técnico de reglas versionadas, con creación, sucesión y archivado compatible con el contrato aprobado.

E. Ejecutor de cinco dominios.

F. Clasificador Petersen experimental.

G. Constructor de resultados y explicación.

H. Integración técnica con la aplicación.

I. Batería de casos sintéticos y resultados reproducibles.

J. Pruebas automatizadas y reporte de ejecución.

K. Documentación técnica, manual de uso y diagrama de arquitectura.

L. Matriz final de trazabilidad: requisito → componente → archivo → prueba → evidencia.

M. Lista explícita de restricciones estructurales o autorizaciones pendientes.

## 19. CRITERIOS DE ACEPTACIÓN

El desarrollo solo puede declararse terminado como PROTOTIPO DE INGENIERÍA cuando:

- Un caso sintético completo recorre automáticamente todas las capas, desde fuentes hasta resultados.
- Los cinco dominios producen resultados específicos y explicables cuando existen hechos admisibles y contratos de prueba suficientes.
- El sistema se abstiene correctamente cuando los datos no permiten interpretar.
- Petersen puede demostrar sus cuatro patrones exclusivamente con hechos ficticios completos y también las abstenciones previstas.
- Las reglas son versionables sin editar la lógica del motor.
- Puede crearse una regla experimental nueva y demostrar su efecto en los resultados.
- Una versión anterior puede retirarse de nuevas ejecuciones sin destruir su historia.
- Existe trazabilidad desde cada frase del informe hasta la fuente y la regla correspondiente.
- Todos los resultados se obtienen mediante código, no mediante respuestas predefinidas.
- Las pruebas pasan y pueden reproducirse.
- No se han habilitado diagnósticos, alertas, tratamientos ni uso asistencial.
- Los límites y autorizaciones pendientes están documentados.

## INSTRUCCIÓN FINAL

Empieza inspeccionando el repositorio y las decisiones oficiales. Después implementa los cambios necesarios hasta completar el prototipo dentro de los permisos técnicos y estructurales existentes.

No respondas solamente «se recomienda implementar». Implementa, ejecuta pruebas, corrige defectos y muestra evidencia del resultado.

Si detectas una limitación que requiere autorización para modificar el esquema, permisos o contratos congelados, NO la eludas. Completa lo demás, entrega la funcionalidad compatible y documenta la modificación exacta pendiente.

Al final quiero conocer:

1. Qué existía.
2. Qué cambiaste.
3. Qué reglas se pueden administrar.
4. Qué resultados produce el motor.
5. Cómo se reproducen los casos.
6. Qué pruebas pasaron.
7. Qué quedó pendiente y por qué.

Nunca declares «validado clínicamente» algo que solo haya superado pruebas de ingeniería.