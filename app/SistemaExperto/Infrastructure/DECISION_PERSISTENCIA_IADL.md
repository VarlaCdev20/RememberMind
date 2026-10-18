# Antecedente de conservación e IADL

**Actualización posterior:** la propietaria aprobó incorporar nueve tablas. La
[decisión V2](../../../docs/base-de-datos/DECISION_CONSERVACION_EXPERTA_V2_2026_10_17.md)
sustituye las restricciones estructurales pendientes de este antecedente. El
repositorio ya implementa conservación V2; la fuente IADL conserva su alcance
descriptivo aprobado. El texto siguiente documenta el estado anterior.

# Conservación completa e IADL — decisión pendiente

Fecha: 17/10/2026. Conservación: PROPUESTA. Fuente IADL: APROBADA POR LA PROPIETARIA.

La propietaria aprobó incorporar la extensión IADL y su adaptador; decisión
registrada en `docs/base-de-datos/DECISION_IADL_FUENTE_EXPERTA_2026_10_17.md`.
También definió que el diseño de conservación debe incluir fuentes institucionales.
Eso no aprueba aún el diccionario de conservación completo.

La solicitud actual autoriza completar los servicios, casos de uso y resolver
las brechas; no identifica una ampliación concreta de la BDD congelada.
`database/AGENTS.md` exige aprobar esa ampliación antes de modificarla.

## Conservación completa

Las 23 tablas D-137 permiten conservar ejecuciones técnicas, representaciones
semánticas, reglas y soportes. No representan toda la instantánea del nuevo Domain:

| Objeto | Información que falta conservar exactamente |
|---|---|
| MemoriaTrabajo | Corte inclusivo con microsegundos/zona y su contrato de conservación |
| HechoExperto | Valor original tipado (incluido null/false/0), episodio y fecha clínica |
| EvidenciaCognitiva | Alcance, justificación y siete metadatos SIM independientes |
| ResultadoDominio/Subcomponente | Justificaciones exactas, subresultados y sus soportes originales |
| VectorFuncionalidad | Declaración externa, revisor, justificación y contexto de cada IADL |
| PerfilRequisitosDcl | C01–C05, sus justificaciones/soportes y versión del contrato |
| ResultadoClasificacionDcl | Patrón nullable, abstención, motivos, diferencial y reglas aplicadas |

Fuentes físicas: migraciones `2026_10_08_120014_create_evaluaciones_expertas_table.php`
y `2026_10_08_120015_create_evidencias_evaluacion_table.php`; contratos de Domain.

Releer una fuente mutable después no restaura su valor original. Guardar objetos
en JSON, `observacion`, serialización PHP, caché o archivos no resuelve esta
brecha: introduciría un almacén paralelo sin el contrato relacional aprobado.

Se necesita aprobar primero un diccionario relacional específico que separe:

1. Contexto original 1:1 con la evaluación y referencias temporales exactas.
2. Proyecciones inmutables tipadas por fuente concreta, con FK a su origen;
   referencias sintéticas en estructuras propias con columnas fijas.
3. Evidencias cognitivas, resultados/subresultados y sus relaciones de soporte.
4. Perfil funcional, cinco requisitos DCL y relaciones explícitas a sus soportes.
5. Clasificación experimental, motivos y reglas efectivamente aplicadas.

No se propone una tabla clínica universal campo/valor ni convertir las variables
del motor en una sustitución EAV de las fuentes operativas. Hay una decisión aún
necesaria sobre el alcance de conservación: solo casos sintéticos o también
proyecciones de fuentes institucionales. La solicitud actual no habilita esa
segunda opción ni convierte identificadores SINTETICO-* en residentes reales.

Hasta aprobar ese diccionario, `guardar`/`guardarClasificacion` continúan
rechazando explícitamente la operación. Las pruebas del motor no acreditan
persistencia durable ni se añaden residentes ficticios para satisfacer FK.

Aceptación de una futura implementación: transacción y rollback, reintento
idéntico idempotente, rechazo de sobrescritura divergente, reconstrucción tras
reiniciar proceso, identidad compartida de originales y equivalencia exacta
null/false/0 en SQLite y PostgreSQL.

## Fuente IADL aprobada e implementada

Reutilizar el contrato de PR #16 en el alcance expresamente aprobado:
https://github.com/VarlaCdev20/RememberMind/pull/16

Referencia inspeccionada: `195a39cdbe805580a11691e51c9403c04768308c`,
`origin/feature/iadl-c03-captura-20261017`.
La rama original sigue en borrador. Esta tarea incorpora únicamente tabla,
Model y lectura descriptiva con la aprobación posterior, sin fusionar aquel PR.

Tabla aprobada `controles_funcionales_iadl`:

| Campo | Tipo y relación |
|---|---|
| cod_control_iadl | string(20), PK |
| cod_residente | string(20), FK obligatoria a residentes |
| cod_personal | string(20), FK obligatoria a personal |
| cod_usuario_registro | string(20), FK obligatoria a usuarios |
| cod_atencion | string(20), FK nullable a atenciones del mismo residente |
| cod_control_anterior | string(20), self-FK nullable, corrección sin borrar original |
| fecha_hora | datetime, fecha de la observación |
| actividad | string(30) |
| oportunidad | string(25) |
| desempeno | string(25) |
| tipo_ayuda | string(30) |
| causa_ayuda | string(30) |
| nivel_previo | string(25) |
| fuente | string(25) |
| estado | string(20), REGISTRADO/RECTIFICACION |
| observacion | text nullable |
| registrado_en | datetime técnico |

FK restrictivas; índices por FK, residente/fecha y residente/actividad/fecha.
Catálogos exactos e invariantes: diccionario de PR #16 incorporado en la
decisión IADL aprobada de esta tarea. El inventario pasó de 72 a 73 tablas
operativas, sin cambiar las otras tablas ni las 23 expertas.

Alcance aprobado e implementado en esta tarea: incorporar exclusivamente
la migración/Model y el adaptador descriptivo con pruebas desechables, sin
fusionar la rama completa, añadir pantallas/permisos o activar clasificación.

El adaptador conserva oportunidad, desempeño, causa, nivel previo y fuente
originales, hasta el corte y para el mismo residente. OBSERVADA/REFERIDA no se
traducen automáticamente a REAL_Y_ADECUADA; COGNITIVA no se convierte en
atribucionCognitivaSimulada=true. La revisión C03 permanece pendiente.

Una aplicación instrumental existente sigue siendo otra fuente únicamente
cuando tenga versión/definición autorizadas. BADL y texto libre no sustituyen
la fuente IADL. La aprobación de esta tabla no resuelve por sí sola la
conservación completa de memoria y clasificación.
