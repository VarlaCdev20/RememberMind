# Acta técnica: cohorte cognitiva investigada

Ejecución final: 22/10/2026. PHP 8.3.33, PHPUnit 12.5.36. Referencias y alcance en [documentación del seeder](../../sistema-experto/COHORTE_INVESTIGACION_SEEDER_2026_10_22.md).

## Resultados conservados

| Ejecución | Pruebas | Aserciones | Fallos/errores/omitidas | Tiempo CLI | Evidencia |
|---|---:|---:|---|---|---|
| SQLite `:memory:` | 7 | 3.009 | 0 / 0 / 0 | 51,753 s | [JUnit](sqlite.junit.xml), [resultados por caso](sqlite-resultados.json) |
| PostgreSQL desechable | 7 | 3.009 | 0 / 0 / 0 | 90,012 s | [JUnit](postgres.junit.xml), [log](postgres.log), [resultados por caso](postgres-resultados.json) |
| Comparación entre motores | 30 casos | — | Sin discrepancias | — | `php scripts/comparar-cohorte30.php …/sqlite-resultados.json …/postgres-resultados.json` |
| Estilo PHP | 6 archivos | — | PASS | — | `php vendor/bin/pint` sobre seeder, manifiesto, dos pruebas y dos scripts |
| Sintaxis PHP | Seeder, manifiesto y exportador | — | PASS | — | `php -l` por archivo |

Los comandos completos están en el documento del seeder. El cluster PostgreSQL se detuvo y eliminó al finalizar. No se conectó una base institucional ni se ejecutaron migraciones sobre ella.

La aceptación invocó el comando Artisan `db:seed` con el seeder real; no sustituye la carga mediante un mock. Se ejecutaron ambas ramas de `PrepararEvaluacionInstitucional` y la presentación Livewire para los cuatro perfiles. Las salidas esperadas del manifiesto nunca se insertaron como resultados clínicos.

## Cobertura y resultados del motor

- Treinta admisiones formales con cama y contacto propios.
- Doce perfiles orientativos: tres de cada una de las cuatro categorías.
- Seis casos con los cinco dominios P y sin patrón clasificable.
- Doce contrastes de requisito negativo, evaluación diferencial, insuficiencia, contradicción y revisión de calidad desactualizada.
- C02 derivado, sin filas originales C02.
- 149 aplicaciones, 1.431 respuestas, 480 controles cognitivos, 30 IADL, 271 juicios originales y 449 soportes, todos de ingeniería.
- Quince caídas y treinta antecedentes/diagnósticos contextuales; no se utilizan para deducir automáticamente DCL.
- Rechazo de fuente ajena, entorno local, destino no confirmado y carga repetida, sin efectos indebidos.
- Fallo parcial con rollback de fuentes, admisiones, auditoría y correlativos; reintento correcto con el mismo objeto y sin selectores huérfanos.
- Consulta/replay y vista sin escrituras clínicas ni resultados finales insertados.
- Retroalimentación que detecta dominio incoherente aunque el patrón declarado coincida, clave ausente con NULL esperado, duplicación y caso no ejecutado.

`comparar-cohorte30.php` devolvió:

```text
PASS: 30 casos, estados, motivos y cantidades SQL equivalentes en SQLite/PostgreSQL. Replay individual idéntico; guardado conjunto pendiente.
```

Las huellas completas se verifican dentro de cada ejecución. No se exige que sean iguales entre dos cargas independientes con distintas identidades y UUID. La comparación entre motores exige igualdad de datos semánticos, cantidades y manifiesto.

## Procedencia y límites

[Matriz CSV de 30 casos](matriz-casos.csv) y [manifiesto versionado](manifiesto.json). Se documentan siete publicaciones primarias y la revisión de condiciones de ADNI/OASIS. No se importaron filas de participantes ni se asumió que una observación de Elena procedía de esos datasets.

PASS técnico para este seeder y su aceptación. No demuestra sensibilidad, especificidad, concordancia con expertos o seguridad clínica en una población real. Los originales A/P/I son entradas sintéticas identificadas, no firmas profesionales fabricadas ni clasificaciones inferidas automáticamente de una z.

El ensayo usa cinco métodos y seis subcomponentes del catálogo existente. No equivale a cubrir mediante estas capturas todos los 19 subcomponentes ni a validar los 23 métodos disponibles. La revisión profesional de los métodos conserva su estado pendiente.

Se recuperan fuentes SQL para ejecutar las inferencias y se verifica replay del DTO en memoria. **No** se declara resuelta la persistencia SQL completa de la evaluación conjunta. `persistido=false`, cero filas nuevas en `evaluaciones_expertas` y ausencia de revisiones sobre resultados transitorios.

## Revisión final de código, estructura y Git

La revisión del alcance cambió una sola implementación de fixture a seeder; no añadió motor, esquema ni permiso. No hay dependencia de `Tests` en `database/seeders/CohorteCognitivaInvestigacionSeeder.php`. El seeder es optativo y no está registrado en `DatabaseSeeder`. Correos de ensayo en `example.invalid`; sin contraseñas nuevas escritas en código ni credenciales publicadas.

No se modificaron interfaces, por lo que build y QA visual no aplican a esta entrega. Livewire acredita integración funcional, no inspección visual.

Se preservó el árbol compartido. Al cierre, `git diff --stat` global mostraba 129 archivos rastreados modificados, 4.795 inserciones y 1.031 eliminaciones; **no son el diff exclusivo de esta tarea** y no incluye sus archivos nuevos sin seguimiento. `git diff --check` global detectó espacios finales en la agenda de Enfermería, ajena a este cambio; no se editó ese trabajo. Pint pasó en los seis archivos PHP del alcance. No se ejecutó `git add`, commit, push ni despliegue.
