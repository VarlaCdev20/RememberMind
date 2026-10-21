# Verificación de la cohorte La Paz

Ejecución local del 20/10/2026. Rama `SEEDERS`, creada desde `origin/develop` (`155a1516`). No se modificaron `DatabaseSeeder`, migraciones, Models, permisos ni `.env`. No se realizó push.

## Resultado instalado

Comando ejecutado:

```powershell
php database/seeders/LaPaz/ejecutar.php --base=RememberMind
```

PostgreSQL local: **430.535 filas nuevas**. Se compararon las **2.814 filas preexistentes** con sus huellas originales y conservaron sus valores. El inventario físico sigue teniendo 124 tablas; ese número incluye extensiones ya instaladas y tablas técnicas, no representa un cambio del baseline.

La base pasó de **25 a 73 residentes**: 48 ingresos nuevos mediante admisión formal. Todos tienen cama, familiar vinculado, consentimiento y antecedentes propios.

| Contenido nuevo | Filas |
|---|---:|
| Residentes / admisiones / ocupaciones / contactos | 48 en cada tabla |
| Personal | 23 |
| Usuarios / asignaciones de rol | 71 en cada tabla |
| Preadmisiones | 52: 48 admitidas y 4 solicitudes en otros estados |
| Jornadas / asignaciones de personal | 1.316 / 8.084 |
| Asignaciones de residente a jornada | 37.872 |
| Atenciones / notas clínicas | 21.020 / 18.992 |
| Antecedentes / diagnósticos | 48 / 84 |
| Signos vitales | 18.936 |
| Valoraciones de dolor / registros conductuales / movilidad | 18.936 en cada tabla |
| Sueño / ingesta / hidratación / eliminación | 18.888 / 56.808 / 94.680 / 37.872 |
| Controles cognitivos descriptivos | 2.726 |
| Prescripciones / horarios / administraciones | 16 / 24 / 7.144 |
| Planes / intervenciones / programaciones de cuidado | 52 en cada tabla |
| Ejecuciones de cuidado / pases de turno | 18.988 / 18.936 |
| Valoraciones psicológicas, funcionales y nutricionales | 503 en cada tabla |
| Aplicaciones instrumentales / respuestas | 503 / 5.030 |
| Actividades / participantes / visitas | 373 / 503 / 1.158 |
| Estudios / resultados / informes / documentos clínicos | 4 en cada tabla |
| Expedientes de ingreso / derivaciones | 48 / 12 |
| Incidentes / heridas / curaciones | 8 / 4 / 32 |
| Alertas operativas / eventos de alerta | 8 / 24 |
| Auditoría Activitylog | 49 |
| Evaluaciones, resultados y trazas del experto | **0** |

El resultado completo por tabla y el manifiesto se guardaron en almacenamiento privado. Las filas nuevas se distribuyen entre 70 tablas operativas y dos técnicas (`activity_log` y `model_has_roles`): **430.415 filas operativas** y 120 técnicas. No se precargaron resultados del experto; sus fuentes operativas quedan disponibles para adquisición posterior. El catálogo de medicamentos existente se reutilizó sin duplicarlo.

## Conservación, integridad e idempotencia

Antes de insertar se creó un respaldo PostgreSQL en formato custom, dentro de `storage/app/private/la-paz-2025-2026/respaldo-*/anterior.dump`. No se ejecutaron migraciones ni comandos de borrado. La carga y las verificaciones de conservación se confirmaron en una sola transacción.

Se ejecutó **por segunda vez el mismo comando**: informó «Cohorte existente verificada. No se insertaron ni sobrescribieron filas». No generó otro respaldo, credenciales ni registros de auditoría.

La revisión de persistencia, realizada como fase de lectura con `remembermind-database-audit`, obtuvo **PASS en el alcance de esta cohorte**:

- cero consentimientos de un contacto perteneciente a otro residente;
- cero ejecuciones de un plan de otro residente;
- cero administraciones atribuidas a personal fuera de Enfermería;
- cero prescripciones atribuidas a personal fuera de Medicina;
- cero visitas sin vínculo entre residente y contacto;
- cero FK no validadas en PostgreSQL;
- cero fuentes vinculadas a una atención de otro residente en signos, conducta, movilidad, valoraciones, instrumentos, prescripciones, estudios, documentos, diagnósticos y notas;
- cero cruces de prescripción/horario/residente o de instrumento/pregunta/opción;
- cero resultados de componentes ajenos al tipo de estudio;
- una ocupación activa por residente y sin duplicación de cama en la cohorte;
- cobertura diaria y conteos esperados de las historias, con archivos existentes y hashes correctos;
- cero inserciones fuera de las tablas operativas permitidas; conocimiento y resultados expertos conservados.

No se afirma validación global de todos los datos anteriores, concurrencia de procesos de carga ni aceptación clínica. El bloqueo exclusivo está implementado; esta ejecución no simula dos importaciones simultáneas.

## Pruebas y herramientas

| Verificación | Resultado |
|---|---|
| PHPUnit dirigido, SQLite `:memory:` | **6 pruebas, 37 aserciones, sin fallos**; 1 min 14,827 s |
| PHPUnit dirigido, PostgreSQL desechable | **6 pruebas, 37 aserciones, sin fallos**; 2 min 15,599 s |
| Comando con nombre de base incorrecto | Rechazado antes de escribir |
| Reejecución sobre PostgreSQL local | Cohorte verificada, sin nuevas escrituras |
| Pint en los archivos PHP nuevos | **PASS** |
| Build / QA visual | No aplican: no se cambió frontend |

Las pruebas cubren carga completa, conservación de cuenta anterior, ausencia de salidas expertas, relación familiar, separación de permisos de Medicina/Enfermería, idempotencia, rechazo de producción/base distinta, reversión por fallo documental, detección de una sola fila clínica faltante, detección de cambios en filas anteriores y aislamiento respecto del seeder normal.

Entorno comprobado: PHP 8.4.13, Laravel 13.33.0, Livewire 4.4.6 y Spatie Permission 7.4.2. No se instalaron dependencias ni se modificó `php.ini`; SQLite se habilitó solo para el proceso de pruebas. El cluster PostgreSQL desechable se detuvo y eliminó mediante el runner existente, después de guardar su evidencia JUnit/log temporal.

## Límites para el experto

Los padecimientos consignados son antecedentes de las historias sintéticas. La carga no produce etiquetas objetivo de deterioro cognitivo, demencia ni resultados del motor. Su volumen y coherencia relacional permiten evaluar el funcionamiento del sistema; no acreditan capacidad diagnóstica ni entrenamiento clínicamente validado.
