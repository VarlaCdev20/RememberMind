# Alertas identificables por especialidad y área de origen

Fecha: 22/10/2026. Alcance: organización de consulta y grafos, sin cambios de esquema, permisos ni reglas de detección clínica.

## Problema y fuentes

La bandeja autorizaba por residente y presentaba distinta composición por rol, pero conservaba la misma lista de alertas para todas las especialidades. Además, la mera existencia de una valoración del residente podía acompañar un caso sin relación profesional verificable.

Se revisaron el baseline de BDD y sus extensiones, el baseline de roles, los productores de alertas, `BandejaAlertas`, las relaciones de personal/usuario/roles, atenciones, incidentes, signos, administraciones y planes de cuidado. `alertas` no contiene `cod_area`. El rol de una cuenta, el área guardada en una atención/plan y el ámbito de residentes son conceptos distintos. No se crea una relación estructural para sustituir esa distinción.

Referencias externas consultadas el 22/10/2026:

- [OMS, ICOPE, segunda edición, 2025](https://www.who.int/publications/i/item/9789240103726): evaluación centrada en la persona, itinerarios adaptados al contexto y plan personalizado.
- [OMS, intervenciones sobre capacidad intrínseca](https://www.who.int/publications-detail-redirect/9789241550109): nutrición/vitalidad, movilidad, cognición y bienestar psicológico como dimensiones relacionadas del cuidado.
- [HCPC, dietistas](https://www.hcpc-uk.org/standards/standards-of-proficiency/dietitians/), apartado 13: evaluación de necesidades, planificación y seguimiento nutricional.
- [HCPC, fisioterapeutas](https://www.hcpc-uk.org/standards/standards-of-proficiency/physiotherapists/) y [psicólogos](https://www.hcpc-uk.org/standards/standards-of-proficiency/practitioner-psychologists/): evaluación, planificación, seguimiento y límites de competencia profesional.

Las referencias internacionales orientan la presentación; no se adoptan como normativa boliviana ni como nuevos protocolos de RememberMind. Pedagogía se mantiene según su flujo y campos institucionales vigentes, sin equipararla a psicología educativa ni a diagnóstico cognitivo.

## Necesidad que resuelve cada grafo

| Especialidad | Pregunta operativa y contexto autorizado existente |
|---|---|
| Enfermería | ¿Qué ocurrió, qué se hizo y qué continuidad quedó pendiente? Incidente/medición/administración/ejecución referenciada, medidas documentadas y seguimiento. |
| Medicina y geriatría | ¿Qué caso fue asignado o proviene de una actuación médica y qué decisión está documentada? Fuente, atención, nota clínica y continuidad médica autorizada. |
| Psicología | ¿Qué caso tiene vínculo con Psicología y qué observaciones vigentes ayudan a revisarlo? Ánimo, afectividad, conducta, comunicación y continuidad. |
| Nutrición | ¿Qué caso está vinculado a Nutrición y qué valoración alimentaria e hídrica está guardada? Apetito, deglución, asistencia, restricciones y requerimiento hídrico en mL por día. |
| Fisioterapia | ¿Qué caso está relacionado con Fisioterapia y qué plan/capacidad funcional consta? Movilidad, equilibrio, traslado, autonomía, supervisión y objetivos del plan referenciado. |
| Pedagogía | ¿Qué caso está relacionado con el acompañamiento pedagógico y qué se observó en la actividad? Atención, comprensión, ejecución, participación y cambios documentados. |

Administración, Gerencia y Superadministración conservan la visión transversal autorizada y pueden seleccionar una especialidad. No adquieren escritura clínica. Familia mantiene su portal y las restricciones de información compartida.

## Contrato implementado

1. Entrada de un rol profesional: selección inicial de su especialidad. Entrada de supervisión/coordinación: todas las autorizadas. La previsualización utiliza el rol visual seleccionado y conserva el ámbito autorizado de la cuenta real.
2. Un vínculo profesional se acredita por el rol actual de la cuenta del responsable de la alerta o por el profesional de una fuente referenciada del mismo residente. La tarjeta y el grafo explican el motivo concreto. No se certifica titulación ni se reconstruye el rol histórico del autor.
3. Fuentes tipadas: incidente, atención solicitada, medición, administración de medicación y ejecución de cuidado. En esta última, también el responsable del plan cuando pueden consultarse intervención y plan. El tipo exacto `INCIDENTE EN SEGUIMIENTO` del detector puede referenciar atención; un incidente real coincidente mantiene la precedencia del lector de procedencia.
4. La lectura de la fuente requiere su permiso específico. Los vínculos pueden variar según las fuentes que la cuenta puede consultar. «Sin vínculo verificable» significa que no se acreditó relación con esas facultades de consulta; no implica ausencia de necesidad clínica.
5. No se clasifican descripciones por palabras, nombres de áreas, existencia de una valoración ni proximidad temporal. El área guardada en la atención o plan aparece como nodo propio, separado de la especialidad del profesional.
6. Una alerta puede tener varias especialidades documentadas. Los conteos de especialidad se solapan y no deben sumarse como casos únicos.
7. La selección se aplica en servidor antes de conteos de especialidad, filtros de lista, gráficas y paginación. La lista y el grafo seleccionado permanecen coherentes. Los resúmenes institucionales de equipo/procedencia conservan su denominador transversal explícito.
8. La franja de prioridades activas conserva el ámbito autorizado anterior al filtro de especialidad y ofrece consultar todas esas prioridades. Abrir su detalle no falla por estar fuera de la especialidad visual seleccionada. No se cambian prioridades ni estados históricos.
9. Solo se adjunta la última valoración de una especialidad si la alerta tiene vínculo verificable con ella. Se sigue dibujando como contexto del residente, sin afirmar que haya originado el aviso.
10. Cero vínculos produce un estado vacío con explicación y acceso a todas las alertas autorizadas. No se fabrican avisos, profesionales ni valoraciones.

El vínculo es una ayuda de organización de lectura, no una derivación, triaje, nueva responsabilidad clínica ni frontera de autorización. La base original de `BandejaAlertas` sigue protegiendo la cuenta, el permiso y los residentes consultables. Formularios, autoría y bloqueos de escritura mantienen sus validaciones anteriores.

## Comprobación de la base actual

Consulta PostgreSQL dentro de transacción de solo lectura, finalmente revertida: 33 alertas autorizadas; 8 con vínculo de Enfermería y 8 de Medicina (son casos compartidos); ninguna con vínculo acreditado de las otras cuatro especialidades; 25 sin vínculo profesional verificable. No se publican nombres ni contenido clínico en esta evidencia. No se repararon datos ni se ejecutaron seeders.

Esta ausencia no autoriza a generar alertas nutricionales, psicológicas, funcionales o pedagógicas automáticamente. Nuevos criterios de detección, umbrales, destinatarios obligatorios o una asignación persistida de área requieren su contrato institucional aprobado. El cambio actual organiza y explica los vínculos que ya existen.

## Verificación y límites

| Corte de verificación | Resultado y evidencia |
|---|---|
| SQLite: vínculos, fuentes, flujos por rol y transporte HTTP de previsualización | 12 pruebas / 308 aserciones, PASS; `areas-final-sqlite.xml`. |
| SQLite: medición y administración omitida | 1 prueba / 7 aserciones, PASS; `areas-medicacion-sqlite.xml`. |
| SQLite: ámbito, cuenta inactiva, familia y prioridad transversal | 2 pruebas / 15 aserciones, PASS; `areas-alcance-sqlite.xml`. |
| SQLite: valoración sin vínculo, bloqueo de formularios y registro real por especialidad | 3 pruebas / 94 aserciones, PASS; `areas-regresion-sqlite.xml`. |
| SQLite: cierre del selector, prioridad y referencia de atención con tipo exacto | 2 pruebas / 17 aserciones, PASS; `areas-cierre-sqlite.xml`. |
| PostgreSQL desechable: vínculos por especialidad, permisos, residente, preview, áreas, planes, medición y administración | 8 pruebas / 76 aserciones, PASS; `areas-postgres.xml`; clúster temporal detenido al terminar. |
| JavaScript: consulta, capas, foco, zoom y formularios | 8 pruebas, PASS; `js-areas.log`. |
| Build | PASS; 110 módulos / 10,58 s; `build-areas.log`. |
| Pint y revisión de diff | PASS en archivos afectados; sin errores de espacios en diff. |

Evidencia en `output/alertas-20261021/`. Los cortes se solapan: no se suman como pruebas únicas. El corte PostgreSQL precede a la aserción adicional de ámbito/familia y a la comprobación adicional del tipo exacto de atención; se verifica esa ampliación en SQLite por separado.

Revisión de seguridad acotada, sin edición durante la lectura: no se encontraron ampliaciones de permiso en el filtro. `BandejaAlertas` conserva el límite base, el filtro añade una conjunción, los registros fuente exigen permiso propio y coincidencia de residente, y el transporte de preview continúa verificando el snapshot y rechazando guardados/lotes mixtos. Los negativos comprueban ausencia de eventos y conservación de estados. No se presenta esta revisión del implementador como auditoría externa ni como aceptación clínica.

No se introdujo dependencia ejecutable de V1 en el nuevo clasificador. La revisión visual en navegador sigue pendiente porque el control local permanece desactivado en Work; los tests de Livewire y el build no acreditan responsive, contraste o interacción visual real. Se solicitó abrir la página con la herramienta de paneles de Codex; devolvió `queued`, sin confirmar navegación ni sesión.

Se conservan los cambios locales anteriores. Sin migraciones, dependencias, cambios de permisos, escrituras clínicas ni operaciones de staging/commit/push.
